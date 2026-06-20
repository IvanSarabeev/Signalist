<?php

declare(strict_types=1);

namespace App\Scheduler;

use App\Enum\Alert\AlertFrequency;
use App\Message\Alert\ProcessAlertByFrequencyMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule]
final class AlertScheduleProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())->add(
            RecurringMessage::cron(
                '* * * * *',
                new ProcessAlertByFrequencyMessage(AlertFrequency::ONCE)
            ),

            RecurringMessage::cron(
                '0 9 * * *',
                new ProcessAlertByFrequencyMessage(AlertFrequency::ONCE_PER_DAY)
            ),

            RecurringMessage::cron(
                '0 9 * * 1',
                new ProcessAlertByFrequencyMessage(AlertFrequency::ONCE_PER_WEEK)
            ),

            RecurringMessage::cron(
                '0 9 1 * *',
                new ProcessAlertByFrequencyMessage(AlertFrequency::ONCE_PER_MONTH)
            ),

            // NYSE market open — 09:30 EST = 14:30 UTC, Monday–Friday
            RecurringMessage::cron(
                '30 14 * * 1-5',
                new ProcessAlertByFrequencyMessage(AlertFrequency::MARKET_OPEN)
            ),

            // NYSE market close — 16:00 EST = 21:00 UTC, Monday–Friday
            RecurringMessage::cron(
                '0 21 * * 1-5',
                new ProcessAlertByFrequencyMessage(AlertFrequency::MARKET_CLOSE)
            )
        );
    }
}
