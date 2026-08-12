<?php

namespace App\Service\Finnhub\Configuration;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class FinnhubConfig
{
    /**
     * @param array<int, string> $popularSymbols
     */
    public function __construct(
        #[Autowire(param: 'finnhub.popular_symbols')]
        private array $popularSymbols
    )
    {}

    public function getPopularSymbols(): array
    {
        return $this->popularSymbols;
    }
}
