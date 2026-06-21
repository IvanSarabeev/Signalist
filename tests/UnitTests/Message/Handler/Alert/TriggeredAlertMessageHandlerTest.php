<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Message\Handler\Alert;

use App\Entity\Alert;
use App\Entity\Stock;
use App\Entity\User;
use App\Enum\Alert\AlertCondition;
use App\Enum\Alert\AlertType;
use App\Message\Alert\TriggeredAlertMessage;
use App\Message\Handler\Alert\TriggeredAlertMessageHandler;
use App\Repository\AlertRepository;
use App\Service\Mailer\EmailFactoryInterface;
use App\Service\Mailer\EmailServiceInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Email;

final class TriggeredAlertMessageHandlerTest extends TestCase
{
    private AlertRepository&MockObject $alertRepository;
    private EmailFactoryInterface&MockObject $emailFactory;
    private EmailServiceInterface&MockObject $emailService;
    private LoggerInterface&MockObject $logger;

    private TriggeredAlertMessageHandler $handler;

    protected function setUp(): void
    {
        $this->alertRepository = $this->createMock(AlertRepository::class);
        $this->emailFactory = $this->createMock(EmailFactoryInterface::class);
        $this->emailService = $this->createMock(EmailServiceInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new TriggeredAlertMessageHandler(
            alertRepository: $this->alertRepository,
            emailFactory: $this->emailFactory,
            emailService: $this->emailService,
            logger: $this->logger,
        );
    }

    // -------------------------------------------------------------------------
    // Guard: alert deleted between trigger and email delivery
    // -------------------------------------------------------------------------

    public function test_logs_warning_and_skips_when_alert_not_found(): void
    {
        $this->alertRepository
            ->method('find')
            ->willReturn(null);

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('Alert no longer exists'));

        $this->emailFactory->expects($this->never())->method('createAlertEmail');
        $this->emailService->expects($this->never())->method('send');

        ($this->handler)($this->createMessage());
    }

    // -------------------------------------------------------------------------
    // Happy path — upper direction (GT, GTE, CROSSES_ABOVE, EQ)
    // -------------------------------------------------------------------------

    #[DataProvider('upperConditionProvider')]
    public function test_sends_upper_template_for_upper_conditions(AlertCondition $condition): void
    {
        $alert = $this->createMockAlert(condition: $condition);
        $email = $this->createMock(Email::class);

        $this->alertRepository->method('find')->willReturn($alert);

        $this->emailFactory
            ->expects($this->once())
            ->method('createAlertEmail')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                true, // isUpper
            )
            ->willReturn($email);

        $this->emailService->expects($this->once())->method('send')->with($email);

        ($this->handler)($this->createMessage());
    }

    public static function upperConditionProvider(): array
    {
        return [
            'greater than' => [AlertCondition::GREATER_THAN],
            'greater or equal' => [AlertCondition::GREATER_THAN_OR_EQUAL],
            'crosses above' => [AlertCondition::CROSSES_ABOVE],
            'equals' => [AlertCondition::EQUALS],
        ];
    }

    // -------------------------------------------------------------------------
    // Happy path — lower direction (LT, LTE, CROSSES_BELOW)
    // -------------------------------------------------------------------------

    #[DataProvider('lowerConditionProvider')]
    public function test_sends_lower_template_for_lower_conditions(AlertCondition $condition): void
    {
        $alert = $this->createMockAlert(condition: $condition);
        $email = $this->createMock(Email::class);

        $this->alertRepository->method('find')->willReturn($alert);

        $this->emailFactory
            ->expects($this->once())
            ->method('createAlertEmail')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                false, // isUpper
            )
            ->willReturn($email);

        $this->emailService->expects($this->once())->method('send')->with($email);

        ($this->handler)($this->createMessage());
    }

    public static function lowerConditionProvider(): array
    {
        return [
            'less than'     => [AlertCondition::LESS_THAN],
            'less or equal' => [AlertCondition::LESS_THAN_OR_EQUAL],
            'crosses below' => [AlertCondition::CROSSES_BELOW],
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createMessage(
        int    $alertId       = 1,
        float  $currentMetric = 150.0,
        string $triggeredAt   = '21 Jun 2026, 15:00',
    ): TriggeredAlertMessage {
        return new TriggeredAlertMessage(
            alertId:       $alertId,
            currentMetric: $currentMetric,
            triggeredAt:   $triggeredAt,
        );
    }

    private function createMockAlert(
        AlertCondition $condition     = AlertCondition::GREATER_THAN,
        string         $email         = 'user@example.com',
        string         $symbol        = 'AAPL',
        string         $company       = 'Apple Inc.',
        string         $currency      = '$',
        string         $threshold     = '130.00',
        float          $changePercent = 0.0,
    ): Alert&MockObject {
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn($email);

        $stock = $this->createMock(Stock::class);
        $stock->method('getSymbol')->willReturn($symbol);
        $stock->method('getName')->willReturn($company);
        $stock->method('getCurrency')->willReturn($currency);
        $stock->method('getCachedChangePercent')->willReturn((string) $changePercent);

        $alert = $this->createMock(Alert::class);
        $alert->method('getUser')->willReturn($user);
        $alert->method('getStock')->willReturn($stock);
        $alert->method('getConditionQuality')->willReturn($condition);
        $alert->method('getThresholdValue')->willReturn($threshold);
        $alert->method('getAlertType')->willReturn(AlertType::PRICE);

        return $alert;
    }
}
