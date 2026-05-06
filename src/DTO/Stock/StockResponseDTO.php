<?php

namespace App\DTO\Stock;

final class StockResponseDTO
{
    public function __construct(
        public ?string $name,
        public ?string $exchange,
        public ?string $country,
        public ?string $currency,
        public ?string $finnhubIndustry,
        public ?string $logo,
        public ?int    $shareOutstanding,
        public ?string $ticker,
        public ?string $weburl,
    ) { }
}
