<?php

namespace App\Service\Finnhub;

use App\Service\Finnhub\Provider\FinnhubClientInterface;
use DateTimeImmutable;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class FinnhubService
{
    private const COMPANY_NEWS_TTL = 300; // 5 minutes
    private const COMPANY_PROFILE_TTL = 86400; // 24 hours

    public function __construct(
        private FinnhubClientInterface $finnhubClient,
        private CacheInterface         $cache,
    ) { }

    /**
     * Remember the Company news for 5 minutes
     *
     * @param string $symbol
     * @return array
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
     * Remember the Company Profile for 24 hours
     *
     * @param string $symbol
     * @return array
     * @throws InvalidArgumentException
     */
    public function getCompanyProfile(string $symbol): array
    {
        return $this->cache->get(
            "finnhub.profile.$symbol",
            function (ItemInterface $item) use ($symbol) {
                $item->expiresAfter(self::COMPANY_PROFILE_TTL);

                return $this->finnhubClient->getCompanyProfile($symbol);
            }
        );
    }
}
