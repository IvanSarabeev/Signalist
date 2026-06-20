<?php

declare(strict_types=1);

namespace App\Message\Handler\Alert;

use App\Enum\Alert\AlertCondition;
use App\Message\Alert\TriggeredAlertMessage;
use App\Repository\AlertRepository;
use App\Service\Mailer\EmailFactory;
use App\Service\Mailer\EmailService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

/**
 * Sends the alert-triggered email to the user.
 *
 * Receives a lightweight SendAlertTriggeredMessage (primitive values only),
 * reloads the full Alert entity to access the user's email, stock name, and
 * alert configuration, then picks the correct template based on the condition
 * direction and delegates rendering + delivery to EmailFactory / EmailService.
 *
 * Template selection:
 *   Upper (green 📈): GT, GTE, CROSSES_ABOVE, EQ  → stock-alert-upper.html.twig
 *   Lower (red  📉): LT, LTE, CROSSES_BELOW       → stock-alert-lower.html.twig
 */
#[AsMessageHandler]
final readonly class TriggeredAlertMessageHandler
{
    private const TRIGGERED_ALERT_PREFIX = 'Triggered Alert Handler: ';

    public function __construct(
        private AlertRepository $alertRepository,
        private EmailFactory    $emailFactory,
        private EmailService    $emailService,
        private LoggerInterface $logger,
    )
    { }

    /**
     * @throws TransportExceptionInterface
     * @throws Throwable
     */
    public function __invoke(TriggeredAlertMessage $triggeredAlertMessage): void
    {
        try {
            $alert = $this->alertRepository->find($triggeredAlertMessage->alertId);

            if ($alert === null) {
                // Alert deleted between trigger and email delivery — skip silently
                $this->logger->warning(self::TRIGGERED_ALERT_PREFIX . 'Alert no longer exists', [
                    'alertId'     => $triggeredAlertMessage->alertId,
                    'triggeredAt' => $triggeredAlertMessage->triggeredAt,
                ]);

                return;
            }

            $user  = $alert->getUser();
            $stock = $alert->getStock();

            $email = $this->emailFactory->createAlertEmail(
                email:         $user->getEmail(),
                symbol:        $stock->getSymbol(),
                company:       $stock->getName(),
                currentMetric: $triggeredAlertMessage->currentMetric,
                threshold:     (float) $alert->getThresholdValue(),
                triggeredAt:   $triggeredAlertMessage->triggeredAt,
                isUpper:       $this->isUpperDirection($alert->getConditionQuality()),
            );

            $this->emailService->send($email);
        } catch (Throwable $e) {
            $this->logger->error('Failed to send alert-triggered email', [
                'alertId' => $triggeredAlertMessage->alertId,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Map an AlertCondition to a visual direction so the correct template is chosen.
     *
     * - Upper (green): the metric moved UP and crossed the threshold → good news / sell signal
     * - Lower (red):   the metric moved DOWN and fell below the threshold → warning / buy dip signal
     *
     * EQUALS and the upper-direction conditions all use the upper (positive) template
     * because the threshold was reached from below.
     */
    private function isUpperDirection(AlertCondition $condition): bool
    {
        return match ($condition) {
            AlertCondition::LESS_THAN,
            AlertCondition::LESS_THAN_OR_EQUAL,
            AlertCondition::CROSSES_BELOW => false,
            default                       => true,
        };
    }
}
