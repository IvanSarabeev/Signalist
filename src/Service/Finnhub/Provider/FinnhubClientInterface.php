<?php

namespace App\Service\Finhub\Provider;

use DateTimeInterface;

interface FinnhubClientInterface
{
    public function getCompanyProfile(string $symbol): array;

    public function getCompanyNews(string $symbol, DateTimeInterface $from, DateTimeInterface $to): array;
}
