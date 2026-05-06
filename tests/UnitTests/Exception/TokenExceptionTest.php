<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Exception;

use App\Presentation\Http\Exception\Token\TokenNotFoundException;
use App\Presentation\Http\Exception\Token\UnexpectedTokenException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(TokenNotFoundException::class)]
#[CoversClass(UnexpectedTokenException::class)]
final class TokenExceptionTest extends TestCase
{
    // ── TokenNotFoundException ────────────────────────────────────────────────

    #[Test]
    public function getStatusCode_TokenNotFoundException_Returns401(): void
    {
        $exception = new TokenNotFoundException();

        self::assertSame(Response::HTTP_UNAUTHORIZED, $exception->getStatusCode());
    }

    #[Test]
    public function getErrorMessage_TokenNotFoundException_ReturnsExpectedMessage(): void
    {
        $exception = new TokenNotFoundException();

        self::assertSame('Invalid token.', $exception->getErrorMessage());
    }

    // ── UnexpectedTokenException ──────────────────────────────────────────────

    #[Test]
    public function getStatusCode_UnexpectedTokenException_Returns401(): void
    {
        $exception = new UnexpectedTokenException();

        self::assertSame(Response::HTTP_UNAUTHORIZED, $exception->getStatusCode());
    }

    #[Test]
    public function getErrorMessage_UnexpectedTokenException_ReturnsExpectedMessage(): void
    {
        $exception = new UnexpectedTokenException();

        self::assertSame('Token not found or expired', $exception->getErrorMessage());
    }
}
