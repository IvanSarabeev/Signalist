<?php

namespace App\DTO\Stock;

final class QuoteResponseDTO
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
