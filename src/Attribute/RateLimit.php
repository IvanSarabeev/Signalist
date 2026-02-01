<?php

namespace App\Service\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
#[Attribute(Attribute::IS_REPEATABLE)]
final class RateLimit
{
    public function __construct(
        public readonly string $limiter,
        public readonly bool $byIpAddress = true,
        public readonly ?string $identifierField = null,
    ) { }
}
