<?php

namespace App\Service\Logger;

final readonly class ApplicationLogger
{

    public function __construct(
        private ApplicationClient $applicationClient
    ) {
    }
}
