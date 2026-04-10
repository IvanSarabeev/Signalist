<?php

namespace App\Service\Finnhub\DTO;

readonly class CompanyProfileDTO
{
    public function __construct(
        public string  $name,
        public string  $ticker,
        public ?string $logo,
        public ?string $industry,
    ) { }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? '',
            $data['ticker'] ?? '',
            $data['logo'] ?? '',
            $data['industry'] ?? '',
        );
    }
}
