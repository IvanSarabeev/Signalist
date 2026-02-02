<?php

namespace App\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class RateLimit
{
    public function __construct(
        public string  $limiter,
        public bool    $byIpAddress = true,
        public ?string $identifierField = null,
    ) { }
}
