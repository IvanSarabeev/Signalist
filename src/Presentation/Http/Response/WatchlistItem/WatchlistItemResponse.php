<?php

namespace App\Presentation\Http\Response\WatchlistItem;

readonly class WatchlistItemResponse
{
    public function __construct(
        public int    $id,
        public string $symbol,
        public string $name,
        public string $exchange,
        public string $currency,
        public float  $price,
        public float  $change_percent,
        public string $market_cap,
        public float  $pe_ratio,
        public string $added_at,
        public int    $sort_order,
    )
    {}
}
