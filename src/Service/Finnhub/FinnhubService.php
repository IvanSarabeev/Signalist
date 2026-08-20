<?php

declare(strict_types=1);

namespace App\Service\Finnhub;

use App\Enum\Finnhub\CategoryNews;
use App\Infrastructure\Finnhub\FinnhubCompanyProfileMapper;
use App\Infrastructure\Finnhub\FinnhubQuoteMapper;
use App\Presentation\Http\Response\PaginatedResponse;
use App\Presentation\Http\Response\Stocks\CompanyNewsItem;
use App\Presentation\Http\Response\Stocks\CompanyProfileItem;
use App\Presentation\Http\Response\Stocks\EarningCalendarItem;
use App\Presentation\Http\Response\Stocks\MarketNews;
use App\Presentation\Http\Response\Stocks\QuoteItem;
use App\Presentation\Http\Response\Stocks\RecommendationTrendItem;
use App\Service\Cache\CacheManagerInterface;
use App\Service\Cache\CacheProfile;
use App\Service\Cache\CacheTag;
use App\Service\Finnhub\Configuration\FinnhubConfig;
use App\Service\Finnhub\Provider\FinnhubClientInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

/**
 * Service responsible for interacting with Finnhub API and providing cached financial data for stocks.
 *
 * Acts as an application-level abstraction over the API client, handling caching, logging, and DTO mapping.
 */
final readonly class FinnhubService implements FinnhubServiceInterface
{
    private const FINHUB_LOG_PREFIX = 'Finnhub :';

    public function __construct(
        #[Autowire(param: 'finnhub.popular_symbols')]
        private array                       $popularSymbols,
        private FinnhubClientInterface      $finnhubClient,
        private LoggerInterface             $logger,
        private FinnhubCompanyProfileMapper $stockProfileMapper,
        private FinnhubQuoteMapper          $quoteMapper,
        private CacheManagerInterface       $cacheManager,
    )
    { }

    /**
     * Retrieves company news for a given stock symbol.
     *
     * Results are cached for 5 minutes to reduce API usage.
     *
     * @param string $symbol Stock ticker symbol (e.g., AAPL)
     * @return array<int, mixed> Raw news data from Finnhub
     */
    public function getCompanyNews(string $symbol): array
    {
        $response = $this->cacheManager->get(
            CacheProfile::COMPANY_NEWS,
            [$symbol],
            function () use ($symbol) {
                $to = new DateTimeImmutable();
                $from = $to->modify('-7 days');

                return $this->finnhubClient->getCompanyNews($symbol, $from, $to);
            },
            [CacheTag::finnhub(), CacheTag::stock($symbol)],
        );

        return array_map(function ($item) {
            return new CompanyNewsItem(
                $item['id'],
                $item['category'],
                $item['datetime'],
                $item['headline'],
                $item['image'],
                $item['source'],
                $item['summary'],
                $item['url'],
            );
        }, $response);
    }

    /**
     * Retrieves company profile data for a stock symbol.
     *
     * Cached for 24 hours due to low update frequency.
     *
     * @param string $symbol Stock ticker symbol
     * @return CompanyProfileItem - return specific Company profile
     */
    public function getCompanyProfile(string $symbol): CompanyProfileItem
    {
        $response = $this->cacheManager->get(
            CacheProfile::COMPANY_PROFILE,
            [$symbol],
            function () use ($symbol) {
                return $this->finnhubClient->getCompanyProfile($symbol);
            },
            [CacheTag::finnhub(), CacheTag::stock($symbol)],
        );

        return $this->stockProfileMapper->toDTO($response);
    }

    /**
     * Returns a list of popular stocks with mapped DTO output.
     *
     * Each stock profile is fetched via cached API calls and transformed
     * into a StockProfileDTO using FinnhubCompanyProfileMapper.
     *
     * @param int $limit Maximum number of stocks to return
     * @return CompanyProfileItem[] List of company profile data
     */
    public function getPopularStocks(int $limit = 10): array
    {
        $symbols = array_slice($this->popularSymbols, 0, $limit);

        $results = [];

        foreach ($symbols as $symbol) {
            try {
                $results[] = $this->getCompanyProfile($symbol);
            } catch (Throwable $throwable) {
                $this->logger->error(self::FINHUB_LOG_PREFIX . $throwable->getMessage());

                continue;
            }
        }

        return $results;
    }

    /**
     * @param string $symbol Stock ticker symbol
     */
    public function getQuote(string $symbol): QuoteItem
    {
        $response = $this->cacheManager->get(
            CacheProfile::QUOTE,
            [$symbol],
            function () use ($symbol) {
                return $this->finnhubClient->getQuote($symbol);
            },
            [CacheTag::finnhub(), CacheTag::stock($symbol)],
        );

        return $this->quoteMapper->toDTO($response);
    }

    /**
     * @param CategoryNews $categoryNews
     * @param int $page
     * @param int $limit
     * @return PaginatedResponse
     * @throws Exception
     */
    public function getMarketNews(CategoryNews $categoryNews, int $page, int $limit): PaginatedResponse
    {
        /** @var MarketNews[] $response */
        $response = $this->cacheManager->get(
            CacheProfile::MARKET_NEWS,
            [$categoryNews->value],
            function () use ($categoryNews) {
                return $this->finnhubClient->getMarketNews($categoryNews);
            },
            [CacheTag::finnhub(), CacheTag::stock($categoryNews->name)],
        );

        $mappedNews = array_map(fn ($item) => new MarketNews(
                id:       $item['id'],
                category: $item['category'],
                datetime: (new DateTimeImmutable('@' . $item['datetime']))->format(DateTimeInterface::ATOM),
                headline: $item['headline'],
                image:    isset($item['image']) ? (string) $item['image'] : null,
                related:  $item['related'],
                source:   $item['source'],
                summary:  $item['summary'],
                url:      $item['url'],
        ), $response);

        return PaginatedResponse::fromArray($mappedNews, $page, $limit);
    }

    /**
     * Upcoming earnings calendar, from Finnhub's /calendar/earnings.
     * Free tier, confirmed. Cached per from/to range since results depend
     * on the date window requested.
     *
     * @param string|null $from ISO date; defaults to today if null
     * @param string|null $to ISO date; defaults to $from + 14 days if null
     * @param string|null $symbol stock symbol
     * @return array|null
     */
    public function getEarningsCalendar(?string $from = null, ?string $to = null, ?string $symbol = null): ?array
    {
        $from ??= (new DateTimeImmutable())->format('Y-m-d');
        $to ??= (new DateTimeImmutable())->modify('+14 days')->format('Y-m-d');

        $response = $this->cacheManager->get(
            CacheProfile::EARNINGS_CALENDAR,
            [$from, $to, $symbol],
            function () use ($from, $to, $symbol) {
                return $this->finnhubClient->getEarningsCalendar($from, $to, $symbol);
            },
            [CacheTag::finnhub(), CacheTag::stock('earning_symbol_' . $symbol)],
        );

        if (empty($response['earningsCalendar'])) {
            $this->logger->error(self::FINHUB_LOG_PREFIX . 'Error empty earnings-calendar data', [
                'from'   => $from,
                'to'     => $to,
                'symbol' => $symbol,
            ]);

            return null;
        }

        return array_map(fn ($item) => new EarningCalendarItem(
            date:            $item['date'],
            epsActual:       isset($item['epsActual']) ? (float) ($item['epsActual']) : null,
            epsEstimate:     isset($item['epsEstimate']) ? (float) ($item['epsEstimate']) : null,
            hour:            $item['hour'],
            quarter:         $item['quarter'],
            revenueActual:   isset($item['revenueActual']) ? (float) ($item['revenueActual']) : null,
            revenueEstimate: isset($item['revenueEstimate']) ? (float) ($item['revenueEstimate']) : null,
            symbol:          $item['symbol'],
            year:            $item['year'],
        ), $response['earningsCalendar']);
    }

    /**
     * @param string $symbol
     * @return RecommendationTrendItem[]
     */
    public function getRecommendationTrends(string $symbol): RecommendationTrendItem
    {
        $response = $this->cacheManager->get(
            CacheProfile::RECOMMENDATION_TRENDS,
            [$symbol],
            function () use ($symbol) {
                return $this->finnhubClient->getRecommendationTrends($symbol);
            },
            [CacheTag::finnhub(), CacheTag::stock('recommendation_' . $symbol)],
        );

        return array_map(fn ($row) => new RecommendationTrendItem(
            buy:        $row['buy'],
            hold:       $row['hold'],
            period:     $row['period'],
            sell:       $row['sell'],
            strongBuy:  $row['strongBuy'],
            strongSell: $row['strongSell'],
            symbol:     $row['symbol'],
        ), $response);
    }
}
