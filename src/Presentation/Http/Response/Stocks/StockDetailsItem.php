<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Stocks;

final readonly class StockDetailsItem
{
    public function __construct(
        public string $name,
        public string $exchange,
        public string $country,
        public string $currency,
        public string $finnhubIndustry,
        public string $logo,
        public int    $shareOutstanding,
        public string $ticker,
        public string $webUrl,
    )
    { }
}
