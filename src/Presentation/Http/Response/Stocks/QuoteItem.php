<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Stocks;

final readonly class QuoteItem
{
    public function __construct(
        public float $currentPrice,
        public float $change,
        public float $percentChange,
        public float $highPriceDay,
        public float $lowPriceDay,
        public float $openPriceDay,
        public float $closePriceDay,
    )
    { }
}
