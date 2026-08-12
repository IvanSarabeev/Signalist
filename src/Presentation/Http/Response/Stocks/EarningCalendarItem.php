<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Stocks;

final readonly class EarningCalendarItem
{
    public function __construct(
        public string $date,
        public ?float $epsActual,
        public ?float $epsEstimate,
        public string $hour,
        public int $quarter,
        public ?float $revenueActual,
        public ?float $revenueEstimate,
        public string $symbol,
        public int $year,
    ) { }
}
