<?php

namespace App\Service\Finhub;

use App\Service\Finhub\Provider\FinhubClient;

final readonly class Finhub
{
    public function __construct(
        private FinhubClient $finhubClient
    ) {
    }
}
