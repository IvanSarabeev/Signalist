<?php

namespace App\Exception\Services;

use RuntimeException;

final class RateLimitExceedException extends RuntimeException
{
    private ?int $retryAfter;

    public function __construct(?int $retryAfter = null)
    {
        $this->retryAfter = $retryAfter;
        $message = $retryAfter !== null ? (string)$retryAfter : '';
        parent::__construct($message);
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
