<?php

declare(strict_types=1);

namespace App\Service\Market;

use App\Enum\Finnhub\CategoryNews;
use App\Enum\Finnhub\MarketHour;
use App\Service\Finnhub\FinnhubServiceInterface;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

final readonly class MarketOverviewServiceService implements MarketOverviewServiceInterface
{
    private const MARKET_PREFIX = 'MarketOverview :';

    private const NEWS_SAMPLE_SIZE = 50;

    private const TRACKED_INDICES = [
        'Nvidia'         => 'NVDA',
        'Airbnb'         => 'ABNB',
        'Oracle'         => 'ORCL',
        'Salesforce Inc' => 'CRM',
    ];

    public function __construct(
        private FinnhubServiceInterface $finnhubService,
        private LoggerInterface         $logger
    )
    { }

    /**
     * @inheritDoc
     */
    public function getIndexQuotes(): array
    {
        $quotes = [];

        foreach (self::TRACKED_INDICES as $label => $symbol) {
            $quote = $this->finnhubService->getQuote($symbol);

            $quotes[] = [
                'label'         => $label,
                'value'         => $quote->currentPrice,
                'changePercent' => $quote->percentChange,
            ];
        }

        return $quotes;
    }

    /**
     * @inheritDoc
     */
    public function getTrendingCompanies(int $limit = 5): array
    {
        $recentNews = $this->finnhubService->getMarketNews(
            CategoryNews::GENERAL,
            page: 1,
            limit: self::NEWS_SAMPLE_SIZE
        );

        $mentionCounts = [];

        foreach ($recentNews->items as $article) {
            $tickers = array_filter(
                array_map('trim', explode(', ', $article->related ?? ''))
            );

            foreach ($tickers as $ticker) {
                $mentionCounts[$ticker] = ($mentionCounts[$ticker] ?? 0) + 1;
            }
        }

        arsort($mentionCounts);
        $topTickers = array_slice(array_keys($mentionCounts), 0, $limit);

        $trending = [];

        foreach ($topTickers as $symbol) {
            $profile = $this->finnhubService->getCompanyProfile($symbol);
            $quote   = $this->finnhubService->getQuote($symbol);

            if ($profile === null || $quote === null) {
                continue;
            }

            $trending[] = [
                'symbol'        => $symbol,
                'name'          => $profile->name,
                'logoUrl'       => $profile->logo,
                'articleCount'  => $mentionCounts[$symbol],
                'changePercent' => $quote->percentChange,
            ];
        }

        return $trending;
    }

    /**
     * Get historical market earnings
     *
     * @inheritDoc
     * @param string|null $from
     * @param string|null $to
     * @param string|null $symbol - Specific stock symbol
     */
    public function getUpcomingEarnings(?string $from = null, ?string $to = null, ?string $symbol = null): array
    {
        $from ??= (new DateTimeImmutable())->format('Y-m-d');
        $to ??= (new DateTimeImmutable())->modify('+14 days')->format('Y-m-d');

        $rows = $this->finnhubService->getEarningsCalendar($from, $to, $symbol);

        if ($rows === null) {
            $this->logger->notice(self::MARKET_PREFIX . 'missing earning records', [
                $from, $to, $symbol
            ]);

            return [];
        }

        $profileCache = [];
        $events = [];

        foreach ($rows as $row) {
            $symbol = $row->symbol ?? null;

            if ($symbol === null) {
                continue;
            }

            if (!array_key_exists($symbol, $profileCache)) {
                $profileCache[$symbol] = $this->finnhubService->getCompanyProfile($symbol);
            }

            $profile = $profileCache[$symbol];

            $events[] = [
                'symbol'           => $symbol,
                'name'             => $profile?->name ?? $symbol,
                'logoUrl'          => $profile?->logo,
                'date'             => $row?->date ?? $from,
                'session'          => MarketHour::earningsHour($row?->hour ?? null),
                'eps_estimate'     => $row?->epsEstimate ?? null,
                'revenue_estimate' => $row?->revenueEstimate ?? null,
            ];
        }

        return $events;
    }
}
