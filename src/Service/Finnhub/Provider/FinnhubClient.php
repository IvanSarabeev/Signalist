<?php

declare(strict_types=1);

namespace App\Service\Finhub\Provider;

use DateTimeInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
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
     * @throws DecodingExceptionInterface
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
     * @param string $symbol
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCompanyProfile(string $symbol): array
    {
        return $this->requestGet(self::GET_STOCK_PROFILE, [
            'symbol' => $symbol
        ]);
    }
}
