<?php

namespace App\Presentation\Http\Request\Stock;

use App\Infrastructure\Routing\RouteRequirements;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class StockListRequest
{
    public function __construct(
        #[Assert\Regex(
            pattern: '/^[A-Z]{1,5}$/',
            message: 'Invalid stock symbol format'
        )]
        public ?string $symbol = null,
    ) {}
}
