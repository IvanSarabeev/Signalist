<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Message\Handler\Alert;

use App\Entity\Alert;
use App\Entity\Stock;
use App\Entity\User;
use App\Enum\Alert\AlertCondition;
use App\Enum\Alert\AlertFrequency;
use App\Enum\Alert\AlertType;
use App\Message\Alert\CheckAlertMessage;
use App\Message\Alert\TriggeredAlertMessage;
use App\Message\Handler\Alert\CheckAlertMessageHandler;
use App\Presentation\Http\Exception\Services\Alert\UnsupportedAlertTypeException;
use App\Repository\AlertRepository;
use App\Service\Alert\AlertEvaluationServiceInterface;
use App\Service\Alert\AlertTriggerServiceInterface;
use App\Service\Alert\Metric\AlertMetricProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final class CheckAlertMessageHandlerTest extends TestCase
{
    private AlertRepository|MockObject                 $alertRepository;
    private MessageBusInterface|MockObject             $messageBus;
    private AlertEvaluationServiceInterface|MockObject $alertEvaluationService;
    private AlertMetricProviderInterface|MockObject    $alertMetricProvider;
    private AlertTriggerServiceInterface|MockObject    $alertTriggerService;

    private CheckAlertMessageHandler $handler;

    protected function setUp(): void
    {
        $this->alertRepository = $this->createMock(AlertRepository::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $this->alertEvaluationService = $this->createMock(AlertEvaluationServiceInterface::class);
        $this->alertMetricProvider = $this->createMock(AlertMetricProviderInterface::class);
        $this->alertTriggerService = $this->createMock(AlertTriggerServiceInterface::class);

        $this->handler = new CheckAlertMessageHandler(
            $this->alertRepository,
            $this->messageBus,
            $logger,
            $this->alertEvaluationService,
            $this->alertMetricProvider,
            $this->alertTriggerService,
        );
    }

    /**
     * @throws Throwable
     */
    public function test_Skip_Alert_When_Not_Found(): void
    {
        $this->alertRepository->method('find')->willReturn(null);

        $this->alertMetricProvider->expects($this->never())->method('getCurrentMetric');
        $this->messageBus->expects($this->never())->method('dispatch');

        ($this->handler)(new CheckAlertMessage(99));
    }

    /**
     * @throws Throwable
     */
    public function test_Skips_When_Alert_Is_Inactive(): void
    {
        $alert = $this->createMockAlert(isActive: false);

        $this->alertRepository->method('find')->willReturn($alert);

        $this->alertMetricProvider->expects($this->never())->method('getCurrentMetric');
        $this->messageBus->expects($this->never())->method('dispatch');

        ($this->handler)(new CheckAlertMessage(1));
    }

    /**
     * @throws Throwable
     */
    public function test_Skips_When_Cooldown_Not_Expired(): void
    {
        $alert = $this->createMockAlert();

        $this->alertRepository->method('find')->willReturn($alert);
        $this->alertEvaluationService->method('isCooldownExpired')->willReturn(false);

        $this->alertMetricProvider->expects($this->never())->method('getCurrentMetric');
        $this->messageBus->expects($this->never())->method('dispatch');

        ($this->handler)(new CheckAlertMessage(1));
    }

    /**
     * @throws Throwable
     */
    public function test_Skips_Gracefully_When_Alert_Type_Is_Unsupported(): void
    {
        $alert = $this->createMockAlert();

        $this->alertRepository->method('find')->willReturn($alert);
        $this->alertEvaluationService->method('isCooldownExpired')->willReturn(true);

        $this->alertMetricProvider
            ->method('getCurrentMetric')
            ->willThrowException(new UnsupportedAlertTypeException(AlertType::MOVING_AVERAGE));

        $this->messageBus->expects($this->never())->method('dispatch');

        ($this->handler)(new CheckAlertMessage(1));
    }

    /**
     * @throws Throwable
     */
    public function test_Rethrows_When_Finnhub_Fetch_Fails(): void
    {
        $alert = $this->createMockAlert();

        $this->alertRepository->method('find')->willReturn($alert);
        $this->alertEvaluationService->method('isCooldownExpired')->willReturn(true);
        $this->alertMetricProvider
            ->method('getCurrentMetric')
            ->willThrowException(new RuntimeException('Finnhub timeout'));

        $this->expectException(RuntimeException::class);

        ($this->handler)(new CheckAlertMessage(1));
    }

    /**
     * @throws Throwable
     */
    public function test_Skips_Trigger_When_Condition_Not_Met(): void
    {
        $alert = $this->createMockAlert();

        $this->alertRepository->method('find')->willReturn($alert);
        $this->alertEvaluationService->method('isCooldownExpired')->willReturn(true);
        $this->alertMetricProvider->method('getCurrentMetric')->willReturn(100.0);
        $this->alertEvaluationService->method('isAlertConditionCorrect')->willReturn(false);

        $this->alertTriggerService->expects($this->never())->method('handleTrigger');
        $this->messageBus->expects($this->never())->method('dispatch');

        ($this->handler)(new CheckAlertMessage(1));
    }

    /**
     * @throws Throwable
     */
    public function test_Triggers_And_Dispatches_When_Condition_Is_Met(): void
    {
        $alert = $this->createMockAlert();

        $this->alertRepository->method('find')->willReturn($alert);
        $this->alertEvaluationService->method('isCooldownExpired')->willReturn(true);
        $this->alertMetricProvider->method('getCurrentMetric')->willReturn(150.0);
        $this->alertEvaluationService->method('isAlertConditionCorrect')->willReturn(true);

        $this->alertTriggerService->expects($this->once())->method('handleTrigger')->with($alert);

        // Verify a TriggeredAlertMessage is dispatched with correct alertId
        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                fn(mixed $message) => $message instanceof TriggeredAlertMessage
                    && $message->alertId === 1
                    && $message->currentMetric === 150.0
            ))
            ->willReturn(new Envelope(new stdClass()));

        ($this->handler)(new CheckAlertMessage(1));
    }


    private function createMockAlert(bool $isActive = true): Alert&MockObject
    {
        $stock = $this->createMock(Stock::class);
        $stock->method('getSymbol')->willReturn('AAPL');

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(42);

        $alert = $this->createMock(Alert::class);
        $alert->method('getId')->willReturn(1);
        $alert->method('isActive')->willReturn($isActive);
        $alert->method('getAlertName')->willReturn('Test Alert');
        $alert->method('getStock')->willReturn($stock);
        $alert->method('getUser')->willReturn($user);
        $alert->method('getAlertType')->willReturn(AlertType::PRICE);
        $alert->method('getFrequency')->willReturn(AlertFrequency::ONCE);
        $alert->method('getConditionQuality')->willReturn(AlertCondition::GREATER_THAN);
        $alert->method('getThresholdValue')->willReturn('120.00');

        return $alert;
    }
}
