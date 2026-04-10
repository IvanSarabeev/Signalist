<?php

namespace App\Service\Finnhub\DTO;

readonly class CompanyNewsDTO
{
    public function __construct(
        public string $headline,
        public string $source,
        public int    $timestamp,
        public string $url,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['headline'],
            $data['source'],
            $data['datetime'],
            $data['url'],
        );
    }
}
