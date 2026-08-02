<?php

namespace App\Presentation\Http\Response\Stocks;

final readonly class CompanyNewsItem
{
    public function __construct(
        public ?int    $id,
        public ?string $category,
        public ?int    $datetime,
        public ?string $headline,
        public ?string $image,
        public ?string $source,
        public ?string $summary,
        public ?string $url,
    )
    { }
}
