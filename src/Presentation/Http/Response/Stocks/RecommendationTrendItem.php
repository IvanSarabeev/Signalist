<?php

namespace App\Presentation\Http\Response\Stocks;

final readonly class RecommendationTrendItem
{
    public function __construct(
        public int $buy,
        public int $hold,
        public string $period,
        public int $sell,
        public int $strongBuy,
        public int $strongSell,
        public string $symbol,
    ) { }
}
