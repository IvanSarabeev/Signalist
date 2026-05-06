<?php

namespace App\DTO\Stock;

final class QuoteResponseDTO
{
    public function __construct(
        private float $currentPrice,
        private float $change,
        private float $percentChange,
        private float $highPriceDay,
        private float $lowPriceDay,
        private float $openPriceDay,
        private float $closePriceDay,
    )
    { }
}
