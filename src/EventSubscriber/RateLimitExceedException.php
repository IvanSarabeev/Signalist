<?php

namespace App\EventSubscriber;

use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

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
