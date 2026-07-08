<?php

declare(strict_types=1);

namespace App\Service\Finnhub;

use App\Presentation\Http\Response\Stocks\CompanyProfileItem;
use App\Presentation\Http\Response\Stocks\QuoteItem;

interface FinnhubServiceInterface
{
    public function getCompanyNews(string $symbol): array;

    public function getCompanyProfile(string $symbol): CompanyProfileItem;

    public function getPopularStocks(int $limit = 10): array;

    public function getQuote(string $symbol): QuoteItem;
}
