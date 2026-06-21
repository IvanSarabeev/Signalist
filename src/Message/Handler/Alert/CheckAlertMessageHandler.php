<?php

declare(strict_types=1);

namespace App\Message\Handler\Alert;

use App\Message\Alert\CheckAlertMessage;
use App\Message\Alert\TriggeredAlertMessage;
use App\Presentation\Http\Exception\Services\Alert\UnsupportedAlertTypeException;
use App\Repository\AlertRepository;
use App\Service\Alert\AlertEvaluationServiceInterface;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * Evaluates a single Alert against a live Finnhub quote.
 *
 * Decision flow:
 *   1. Load alert — skip if missing or already inactive
 *   2. Check cooldown  — skip if not enough time has passed since last trigger
 *   3. Fetch metric    — skip gracefully if AlertType not yet implemented
 *   4. Evaluate condition — skip if condition is not satisfied
 *   5. Handle trigger  — stamp lastTriggeredAt, deactivate if ONCE
 *   6. (Future) Dispatch user notification
 *
 * Any Finnhub transport error is re-thrown so Messenger can retry the job.
 */
#[AsMessageHandler]
final readonly class CheckAlertMessageHandler
{
    private const CHECK_ALERT_PREFIX = 'Check Alert Message: ';

    public function __construct(
        private AlertRepository                 $alertRepository,
        private MessageBusInterface             $bus,
        private LoggerInterface                 $logger,
        private AlertEvaluationServiceInterface $alertEvaluationService,
    ) {}

    /**
     * @param CheckAlertMessage $checkAlertMessage
     * @return void
     * @throws Throwable
     */
    public function __invoke(CheckAlertMessage $checkAlertMessage): void
    {
        $alert = $this->alertRepository->find($checkAlertMessage->alertId);

        // Guard: alert may have been deleted or deactivated between dispatch and execution
        if ($alert === null || !$alert->isActive()) {
            $this->logger->debug(self::CHECK_ALERT_PREFIX . 'Alert not found or inactive — skipping', [
                'alertId' => $checkAlertMessage->alertId,
            ]);

            return;
        }

        // Guard: cooldown must have elapsed since the last trigger
        if (!$this->alertEvaluationService->isCooldownExpired($alert)) {
            $this->logger->debug(self::CHECK_ALERT_PREFIX . 'Alert cooldown not yet expired — skipping', [
                'alertId'         => $alert->getId(),
                'frequency'       => $alert->getFrequency()->value,
                'lastTriggeredAt' => $alert->getLastTriggeredAt()?->format('Y-m-d\TH:i:s'),
            ]);

            return;
        }

        // Fetch live metric from Finnhub
        try {
            $currentMetric = $this->alertEvaluationService->getCurrentMetric($alert);
        } catch (UnsupportedAlertTypeException $e) {
            // Not a failure — just a feature gap. Log and skip without retrying.
            $this->logger->error(self::CHECK_ALERT_PREFIX . 'Unsupported type — skipping evaluation', [
                'alertId'   => $alert->getId(),
                'alertType' => $alert->getAlertType()->value,
                'reason'    => $e->getMessage(),
            ]);

            return;
        } catch (Throwable $e) {
            // Network / API failure — rethrow so Messenger retries the job
            $this->logger->error(self::CHECK_ALERT_PREFIX . 'Failed to fetch Finnhub metric for alert', [
                'alertId' => $alert->getId(),
                'symbol'  => $alert->getStock()->getSymbol(),
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }

        // Evaluate condition (including crossover logic)
        $conditionMet = $this->alertEvaluationService->isAlertConditionCorrect($alert, $currentMetric);

        $this->logger->debug(self::CHECK_ALERT_PREFIX . 'Alert evaluated', [
            'alertId'       => $alert->getId(),
            'alertName'     => $alert->getAlertName(),
            'symbol'        => $alert->getStock()->getSymbol(),
            'alertType'     => $alert->getAlertType()->value,
            'condition'     => $alert->getConditionQuality()->symbol(),
            'threshold'     => $alert->getThresholdValue(),
            'currentMetric' => $currentMetric,
            'conditionMet'  => $conditionMet,
        ]);

        if (!$conditionMet) {
            return;
        }

        // Condition satisfied — stamp and (optionally) deactivate
        $this->logger->info(self::CHECK_ALERT_PREFIX . 'Alert triggered', [
            'alertId'       => $alert->getId(),
            'alertName'     => $alert->getAlertName(),
            'userId'        => $alert->getUser()->getId(),
            'symbol'        => $alert->getStock()->getSymbol(),
            'currentMetric' => $currentMetric,
            'threshold'     => $alert->getThresholdValue(),
        ]);

        $this->alertEvaluationService->handleTrigger($alert);

        // Dispatch the notification email asynchronously so a mailer failure
        // never rolls back the DB trigger stamp.
        $this->bus->dispatch(new TriggeredAlertMessage(
            alertId:       $alert->getId(),
            currentMetric: $currentMetric,
            triggeredAt:   (new DateTimeImmutable())->format('Y-m-d\TH:i:s'),
        ));
    }
}
