<?php

declare(strict_types=1);

namespace App\Service\Finnhub;

use App\DTO\Stock\QuoteResponseDTO;
use App\DTO\Stock\StockResponseDTO;

interface FinnhubServiceInterface
{
    public function getCompanyNews(string $symbol): array;

    public function getCompanyProfile(string $symbol): StockResponseDTO;

    public function getPopularStocks(int $limit = 10): array;

    public function getQuote(string $symbol): QuoteResponseDTO;
}
