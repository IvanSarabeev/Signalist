<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Exception;

use App\Presentation\Http\Exception\Common\InvalidPaginationArgumentException;
use App\Presentation\Http\Exception\Services\RateLimitExceedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(InvalidPaginationArgumentException::class)]
#[CoversClass(RateLimitExceedException::class)]
final class CommonExceptionTest extends TestCase
{
    // ── InvalidPaginationArgumentException ───────────────────────────────────

    #[Test]
    public function getStatusCode_InvalidPaginationArgumentException_Returns400(): void
    {
        $exception = new InvalidPaginationArgumentException();

        self::assertSame(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());
    }

    #[Test]
    public function getErrorMessage_InvalidPaginationArgumentException_ReturnsExpectedMessage(): void
    {
        $exception = new InvalidPaginationArgumentException();

        self::assertSame('Invalid pagination arguments.', $exception->getErrorMessage());
    }

    // ── RateLimitExceedException ──────────────────────────────────────────────

    #[Test]
    public function getRetryAfter_RateLimitExceedException_WithRetryAfterValue_ReturnsValue(): void
    {
        $exception = new RateLimitExceedException(60);

        self::assertSame(60, $exception->getRetryAfter());
    }

    #[Test]
    public function getRetryAfter_RateLimitExceedException_WithNoRetryAfter_ReturnsNull(): void
    {
        $exception = new RateLimitExceedException();

        self::assertNull($exception->getRetryAfter());
    }

    #[Test]
    public function getMessage_RateLimitExceedException_WithRetryAfter_ReturnsRetryAfterAsString(): void
    {
        $exception = new RateLimitExceedException(120);

        self::assertSame('120', $exception->getMessage());
    }

    #[Test]
    public function getMessage_RateLimitExceedException_WithNoRetryAfter_ReturnsEmptyString(): void
    {
        $exception = new RateLimitExceedException();

        self::assertSame('', $exception->getMessage());
    }
}
