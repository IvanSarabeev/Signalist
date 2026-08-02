<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Stocks;

final readonly class CompanyProfileItem
{
    public function __construct(
        public ?string $name,
        public ?string $exchange,
        public ?string $country,
        public ?string $currency,
        public ?string $finnhubIndustry,
        public float   $marketCapitalization,
        public ?string $logo,
        public ?int    $shareOutstanding,
        public ?string $ticker,
        public ?string $webUrl,
        public ?float  $floatingShare,
    )
    { }
}
