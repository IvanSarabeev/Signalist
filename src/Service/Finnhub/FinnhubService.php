<?php

namespace App\Service\Finnhub;

use App\DTO\Stock\QuoteResponseDTO;
use App\DTO\Stock\StockResponseDTO;
use App\Mapper\Stock\QuoteMapper;
use App\Mapper\Stock\StockProfileMapper;
use App\Service\Finnhub\Provider\FinnhubClientInterface;
use DateTimeImmutable;
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

    private const COMPANY_NEWS_TTL = 300; // 5 minutes
    private const COMPANY_PROFILE_TTL = 86400; // 24 hours

    /**
     * @param FinnhubClientInterface $finnhubClient Low-level API client
     * @param CacheInterface $cache Application cache layer
     * @param LoggerInterface $logger Error and system logger
     * @param StockProfileMapper $stockProfileMapper Maps API data to DTOs
     */
    public function __construct(
        private FinnhubClientInterface $finnhubClient,
        private CacheInterface         $cache,
        private LoggerInterface        $logger,
        private StockProfileMapper     $stockProfileMapper,
        private FinnhubConfig          $finnhubConfig,
        private QuoteMapper            $quoteMapper,
    ) { }

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
        return $this->cache->get(
            "finhub.news.$symbol",
            function (ItemInterface $item) use ($symbol) {
                $item->expiresAfter(self::COMPANY_NEWS_TTL);

                $to = new DateTimeImmutable();
                $from = $to->modify('-7 days');

                return $this->finnhubClient->getCompanyNews($symbol, $from, $to);
            }
        );
    }

    /**
     * Retrieves company profile data for a stock symbol.
     *
     * Cached for 24 hours due to low update frequency.
     *
     * @param string $symbol Stock ticker symbol
     * @return StockResponseDTO Company profile data
     * @throws InvalidArgumentException
     */
    public function getCompanyProfile(string $symbol): StockResponseDTO
    {
        $data = $this->cache->get(
            "finnhub.profile.$symbol",
            function (ItemInterface $item) use ($symbol) {
                $item->expiresAfter(self::COMPANY_PROFILE_TTL);

                return $this->finnhubClient->getCompanyProfile($symbol);
            }
        );

        return $this->stockProfileMapper->toDTO($data);
    }

    /**
     * Returns a list of popular stocks with mapped DTO output.
     *
     * Each stock profile is fetched via cached API calls and transformed
     * into a StockProfileDTO using StockProfileMapper.
     *
     * @param int $limit Maximum number of stocks to return
     * @return StockResponseDTO[] List of StockProfileDTOs
     */
    public function getPopularStocks(int $limit = 10): array
    {
        $symbols = array_slice($this->finnhubConfig->getPopularSymbols(), 0, $limit);

        $results = [];

        foreach ($symbols as $symbol) {
            try {
                $profile = $this->getCompanyProfile($symbol);

                $results[] = $this->stockProfileMapper->toDTO((array)$profile);
            } catch (Throwable $throwable) {
                $this->logger->error(self::FINHUB_LOG_PREFIX . $throwable->getMessage());

                continue;
            }
        }

        return $results;
    }

    /**
     * @param string $symbol Stock ticker symbol
     * @return QuoteResponseDTO stock prices for international markets
     * @throws InvalidArgumentException
     */
    public function getQuote(string $symbol): QuoteResponseDTO
    {
        $data = $this->cache->get(
            "finhub.quote.$symbol",
            function (ItemInterface $item) use ($symbol) {
                $item->expiresAfter(self::COMPANY_NEWS_TTL);

                return $this->finnhubClient->getQuote($symbol);
            }
        );

        return $this->quoteMapper->toDTO($data);
    }
}
