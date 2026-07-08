<?php

namespace App\Infrastructure\Finnhub;

use App\Presentation\Http\Response\Stocks\CompanyProfileItem;

final class FinnhubCompanyProfileMapper
{
    public function toDTO(array $stockProfile): CompanyProfileItem
    {
        return new CompanyProfileItem(
            $stockProfile['name'],
            $stockProfile['exchange'],
            $stockProfile['country'],
            $stockProfile['currency'],
            $stockProfile['finnhubIndustry'],
            $stockProfile['marketCapitalization'],
            $stockProfile['logo'],
            $stockProfile['shareOutstanding'],
            $stockProfile['ticker'],
            $stockProfile['weburl'] ?? null,
            $stockProfile['floatingShare']
        );
    }
}
