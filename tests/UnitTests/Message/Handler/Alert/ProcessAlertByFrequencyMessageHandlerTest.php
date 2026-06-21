<?php

namespace App\Tests\UnitTests\Message\Handler\Alert;

use App\Entity\Alert;
use App\Enum\Alert\AlertFrequency;
use App\Message\Alert\CheckAlertMessage;
use App\Message\Alert\ProcessAlertByFrequencyMessage;
use App\Message\Handler\Alert\ProcessAlertByFrequencyMessageHandler;
use App\Repository\AlertRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ProcessAlertByFrequencyMessageHandlerTest extends TestCase
{
    private AlertRepository&MockObject     $alertRepository;
    private LoggerInterface&MockObject     $logger;
    private MessageBusInterface&MockObject $bus;

    private ProcessAlertByFrequencyMessageHandler $handler;

    protected function setUp(): void
    {
        $this->alertRepository = $this->createMock(AlertRepository::class);
        $this->logger          = $this->createMock(LoggerInterface::class);
        $this->bus             = $this->createMock(MessageBusInterface::class);

        $this->handler = new ProcessAlertByFrequencyMessageHandler(
            alertRepository: $this->alertRepository,
            logger:          $this->logger,
            bus:             $this->bus,
        );
    }


    /**
     * @throws ExceptionInterface
     */
    public function test_logs_and_skips_when_no_active_alerts_found(): void
    {
        $this->alertRepository->method('findActiveAlertsByFrequency')->willReturn([]);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with($this->stringContains('No active alerts found'));

        $this->bus->expects($this->never())->method('dispatch');

        ($this->handler)(new ProcessAlertByFrequencyMessage(AlertFrequency::ONCE_PER_DAY));
    }

    /**
     * @throws ExceptionInterface
     */
    public function test_dispatches_single_check_message_for_one_alert(): void
    {
        $alert = $this->createMockAlert(id: 5);

        $this->alertRepository->method('findActiveAlertsByFrequency')->willReturn([$alert]);

        $this->bus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                fn(mixed $message) => $message instanceof CheckAlertMessage
                    && $message->alertId === 5
            ))
            ->willReturn(new Envelope(new stdClass()));

        ($this->handler)(new ProcessAlertByFrequencyMessage(AlertFrequency::ONCE_PER_HOUR));
    }

    /**
     * @throws ExceptionInterface
     */
    public function test_dispatches_one_check_message_per_alert(): void
    {
        $alerts = [
            $this->createMockAlert(id: 1),
            $this->createMockAlert(id: 2),
            $this->createMockAlert(id: 3),
        ];

        $this->alertRepository
            ->method('findActiveAlertsByFrequency')
            ->willReturn($alerts);

        $this->bus
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturn(new Envelope(new stdClass()));

        ($this->handler)(new ProcessAlertByFrequencyMessage(AlertFrequency::ONCE_PER_MONTH));
    }


    /**
     * @throws ExceptionInterface
     */
    public function test_dispatches_correct_alert_ids_for_each_alert(): void
    {
        $alerts = [
            $this->createMockAlert(id: 10),
            $this->createMockAlert(id: 20),
        ];

        $this->alertRepository
            ->method('findActiveAlertsByFrequency')
            ->willReturn($alerts);

        $dispatched = [];

        $this->bus
            ->method('dispatch')
            ->willReturnCallback(function (mixed $message) use (&$dispatched) {
                $dispatched[] = $message;
                return new Envelope(new stdClass());
            });

        ($this->handler)(new ProcessAlertByFrequencyMessage(AlertFrequency::ONCE));

        $this->assertCount(2, $dispatched);
        $this->assertSame(10, $dispatched[0]->alertId);
        $this->assertSame(20, $dispatched[1]->alertId);
    }

    /**
     * @throws ExceptionInterface
     */
    public function test_queries_repository_with_correct_frequency(): void
    {
        $this->alertRepository
            ->expects($this->once())
            ->method('findActiveAlertsByFrequency')
            ->with(AlertFrequency::ONCE_PER_DAY)
            ->willReturn([]);

        ($this->handler)(new ProcessAlertByFrequencyMessage(AlertFrequency::ONCE_PER_DAY));
    }

    /**
     * @throws ExceptionInterface
     */
    public function test_propagates_exception_when_bus_dispatch_fails(): void
    {
        $alert = $this->createMockAlert(id: 1);

        $this->alertRepository
            ->method('findActiveAlertsByFrequency')
            ->willReturn([$alert]);

        $this->bus
            ->method('dispatch')
            ->willThrowException(new RuntimeException('Transport unavailable'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Transport unavailable');

        ($this->handler)(new ProcessAlertByFrequencyMessage(AlertFrequency::MARKET_CLOSE));
    }

    private function createMockAlert(int $id): Alert&MockObject
    {
        $alert = $this->createMock(Alert::class);
        $alert->method('getId')->willReturn($id);

        return $alert;
    }
}
