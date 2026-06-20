<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Scheduler;

use App\Enum\Alert\AlertFrequency;
use App\Message\Alert\ProcessAlertByFrequencyMessage;
use App\Scheduler\AlertScheduleProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;

/**
 * Requires symfony/scheduler to be installed.
 * Run: composer require symfony/scheduler:7.4.*
 */
#[CoversClass(AlertScheduleProvider::class)]
final class AlertScheduleProviderTest extends TestCase
{
    private AlertScheduleProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new AlertScheduleProvider();
    }

    #[Test]
    public function getSchedule_ReturnsAScheduleInstance(): void
    {
        $schedule = $this->provider->getSchedule();

        self::assertInstanceOf(Schedule::class, $schedule);
    }

    #[Test]
    public function getSchedule_ContainsExactlySixRecurringMessages(): void
    {
        $messages = $this->provider->getSchedule()->getRecurringMessages();

        // One RecurringMessage per AlertFrequency case: ONCE, ONCE_PER_HOUR,
        // ONCE_PER_DAY, ONCE_PER_WEEK, ONCE_PER_MONTH, MARKET_OPEN, MARKET_CLOSE
        self::assertCount(6, $messages);
    }
}
