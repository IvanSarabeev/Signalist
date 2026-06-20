<?php

declare(strict_types=1);

namespace App\Message\Handler\Alert;

use App\Entity\Alert;
use App\Message\Alert\CheckAlertMessage;
use App\Message\Alert\ProcessAlertByFrequencyMessage;
use App\Repository\AlertRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

#[AsMessageHandler]
final readonly class ProcessAlertByFrequencyMessageHandler
{
    private const PROCESS_ALERT_PREFIX = 'Process Alert Handler: ';

    public function __construct(
        private AlertRepository     $alertRepository,
        private LoggerInterface     $logger,
        private MessageBusInterface $bus,
    )
    { }

    /**
     * @param ProcessAlertByFrequencyMessage $byFrequencyMessage
     * @return void
     */
    public function __invoke(ProcessAlertByFrequencyMessage $byFrequencyMessage): void
    {
        $alerts = $this->alertRepository->findActiveAlertsByFrequency($byFrequencyMessage->frequency);

        if (empty($alerts)) {
            $this->logger->info(self::PROCESS_ALERT_PREFIX . 'No active alerts found - nothing to dispatch.', [
                'frequency'       => $byFrequencyMessage->frequency->value,
                'frequency_label' => $byFrequencyMessage->frequency->label(),
            ]);

            return;
        }

        try {
            /** @var Alert $alert */
            foreach ($alerts as $alert) {
                $this->bus->dispatch(new CheckAlertMessage($alert->getId()));
            }
        } catch (Throwable $exception) {
            $this->logger->critical(self::PROCESS_ALERT_PREFIX . 'Dispatch failure', [
                'frequency'       => $byFrequencyMessage->frequency->value,
                'frequency_label' => $byFrequencyMessage->frequency->label(),
                'message'         => $exception->getMessage(),
            ]);
        }
    }
}
