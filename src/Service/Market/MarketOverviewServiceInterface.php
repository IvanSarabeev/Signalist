<?php

declare(strict_types=1);

namespace App\Service\Market;

interface MarketOverviewServiceInterface
{
    /** @return array<int, array{label: string, value: float, changePercent: float}> */
    public function getIndexQuotes(): array;

    /** @return array<int, array{symbol: string, name: string, logoUrl: ?string, articleCount: int, changePercent: float}> */
    public function getTrendingCompanies(int $limit = 5): array;

    /** @return array<int, array{symbol: string, name: string, logoUrl: ?string, date: string, session: string, epsEstimate: ?float, revenueEstimate: ?float}> */
    public function getUpcomingEarnings(?string $from = null, ?string $to = null, ?string $symbol = null): array;
}
