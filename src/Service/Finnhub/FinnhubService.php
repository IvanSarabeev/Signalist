<?php

namespace App\Service\Finhub;

use App\Service\Finhub\Provider\FinnhubClient;

final readonly class FinnhubService
{
    public function __construct(
        private FinnhubClient $finhubClient
    ) { }

    public function getNewsFeed(string $symbol): array
    {
        $to = new \DateTimeImmutable();
        $from = $to->modify('-7 days');

        return $this->finhubClient->getCompanyNews($symbol, $from, $to);
    }

    public function getCompanyOverview(string $symbol): array
    {
        return $this->finhubClient->getCompanyProfile($symbol);
    }
}
