<?php

declare(strict_types=1);

namespace App\Service\Finnhub;

use App\Enum\Finnhub\CategoryNews;
use App\Infrastructure\Finnhub\FinnhubCompanyProfileMapper;
use App\Infrastructure\Finnhub\FinnhubQuoteMapper;
use App\Presentation\Http\Response\PaginatedResponse;
use App\Presentation\Http\Response\Stocks\CompanyNewsItem;
use App\Presentation\Http\Response\Stocks\CompanyProfileItem;
use App\Presentation\Http\Response\Stocks\MarketNews;
use App\Presentation\Http\Response\Stocks\QuoteItem;
use App\Service\Finnhub\Configuration\FinnhubConfig;
use App\Service\Finnhub\Enum\FinnhubCache;
use App\Service\Finnhub\Provider\FinnhubClientInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Throwable;

/**
 * Service responsible for interacting with Finnhub API and providing cached financial data for stocks.
 *
 * Acts as an application-level abstraction over the API client, handling caching, logging, and DTO mapping.
 */
final readonly class FinnhubService implements FinnhubServiceInterface
{
    private const FINHUB_LOG_PREFIX = 'Finnhub :';

    /**
     * @param FinnhubClientInterface $finnhubClient Low-level API client
     * @param CacheInterface $cache Application cache layer
     * @param LoggerInterface $logger Error and system logger
     * @param FinnhubCompanyProfileMapper $stockProfileMapper Maps API data to DTOs
     */
    public function __construct(
        private FinnhubClientInterface      $finnhubClient,
        private CacheInterface              $cache,
        private LoggerInterface             $logger,
        private FinnhubCompanyProfileMapper $stockProfileMapper,
        private FinnhubConfig               $finnhubConfig,
        private FinnhubQuoteMapper          $quoteMapper,
    )
    { }

    /**
     * Retrieves company news for a given stock symbol.
     *
     * Results are cached for 5 minutes to reduce API usage.
     *
     * @param string $symbol Stock ticker symbol (e.g., AAPL)
     * @return array<int, mixed> Raw news data from Finnhub
     * @throws InvalidArgumentException
     */
    public function getCompanyNews(string $symbol): array
    {
        $response = $this->cache->get(
            FinnhubCache::COMPANY_NEWS->key($symbol),
            function (ItemInterface $item) use ($symbol) {
                $item->expiresAfter(FinnhubCache::COMPANY_NEWS->ttl());

                $to = new DateTimeImmutable();
                $from = $to->modify('-7 days');

                return $this->finnhubClient->getCompanyNews($symbol, $from, $to);
            }
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
     * @throws InvalidArgumentException
     */
    public function getCompanyProfile(string $symbol): CompanyProfileItem
    {
        $data = $this->cache->get(
            FinnhubCache::COMPANY_PROFILE->key($symbol),
            function (ItemInterface $item) use ($symbol) {
                $item->expiresAfter(FinnhubCache::COMPANY_PROFILE->ttl());

                return $this->finnhubClient->getCompanyProfile($symbol);
            }
        );

        return $this->stockProfileMapper->toDTO($data);
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
        $symbols = array_slice($this->finnhubConfig->getPopularSymbols(), 0, $limit);

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
     * @throws InvalidArgumentException
     */
    public function getQuote(string $symbol): QuoteItem
    {
        $data = $this->cache->get(
            FinnhubCache::QUOTE->key($symbol),
            function (ItemInterface $item) use ($symbol) {
                $item->expiresAfter(FinnhubCache::QUOTE->ttl());

                return $this->finnhubClient->getQuote($symbol);
            }
        );

        return $this->quoteMapper->toDTO($data);
    }

    /**
     * @param CategoryNews $categoryNews
     * @param int $page
     * @param int $limit
     * @return PaginatedResponse
     * @throws InvalidArgumentException
     */
    public function getMarketNews(CategoryNews $categoryNews, int $page, int $limit): PaginatedResponse
    {
        /** @var MarketNews[] $response */
        $response = $this->cache->get(
            FinnhubCache::NEWS->key($categoryNews->value),
            function (ItemInterface $item) use ($categoryNews) {
                $item->expiresAfter(FinnhubCache::NEWS->ttl());

                return $this->finnhubClient->getMarketNews($categoryNews);
            }
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
}
