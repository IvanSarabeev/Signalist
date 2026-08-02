<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Stocks;

final readonly class MarketNews
{
    public function __construct(
        public int     $id,
        public string  $category,
        public string  $datetime,
        public string  $headline,
        public ?string $image,
        public string  $related,
        public string  $source,
        public string  $summary,
        public ?string $url,
    )
    { }
}
