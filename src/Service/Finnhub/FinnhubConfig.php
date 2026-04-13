<?php

namespace App\Service\Finnhub;

final readonly class FinnhubConfig
{
    /**
     * @param array<int, string> $popularSymbols
     */
    public function __construct(private array $popularSymbols)
    {}

    public function getPopularSymbols(): array
    {
        return $this->popularSymbols;
    }
}
