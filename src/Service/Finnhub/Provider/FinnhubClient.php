<?php

declare(strict_types=1);

namespace App\Service\Finnhub\Provider;

use App\Enum\Finnhub\CategoryNews;
use DateTimeInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final readonly class FinnhubClient extends AbstractFinnhubClient implements FinnhubClientInterface
{
    /**
     * Get company news
     *
     * @param string $symbol
     * @param DateTimeInterface $from
     * @param DateTimeInterface $to
     * @return array
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCompanyNews(string $symbol, DateTimeInterface $from, DateTimeInterface $to): array
    {
        return $this->requestGet(self::GET_COMPANY_NEWS, [
            'symbol' => $symbol,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
        ]);
    }

    /**
     * Get company profile information.
     *
     * @param string|null $symbol
     * @return array
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCompanyProfile(?string $symbol): array
    {
        return $this->requestGet(self::GET_STOCK_PROFILE, ['symbol' => $symbol]);
    }

    /**
     * Get real-time stock prices for international markets
     *
     * @param string $symbol
     * @return array
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getQuote(string $symbol): array
    {
        return $this->requestGet(self::GET_STOCK_QUOTE, ['symbol' => $symbol]);
    }

    /**
     * Get specific market news based on provided category
     *
     * @param CategoryNews $categoryNews
     * @return array
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getMarketNews(CategoryNews $categoryNews): array
    {
        return $this->requestGet(self::GET_MARKET_NEWS, ['category' => $categoryNews->value]);
    }

    /**
     * Get recommendation trends per symbol
     *
     * @param string $symbol
     * @return array
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getRecommendationTrends(string $symbol): array
    {
        return $this->requestGet(self::GET_RECOMMENDATION_TRENDS, ['symbol' => $symbol]);
    }

    /**
     * Get historical and coming earnings release.
     *
     * @param string|null $from
     * @param string|null $to
     * @param string|null $symbol
     * @return array
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getEarningsCalendar(?string $from = null, ?string $to = null, ?string $symbol = null): array
    {
        $query = ['from' => $from, 'to' => $to, 'symbol' => $symbol];

        return $this->requestGet(self::GET_EARNINGS_CALENDAR, $query);
    }
}
