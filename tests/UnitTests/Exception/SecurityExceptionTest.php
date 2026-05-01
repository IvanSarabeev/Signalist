<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Exception;

use App\Exception\Security\EmailExistsException;
use App\Exception\Security\ExpiredOtpException;
use App\Exception\Security\InvalidCredentialsException;
use App\Exception\Security\InvalidOtpException;
use App\Exception\Security\UserNotFoundException;
use App\Exception\Security\UserRegistrationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(InvalidCredentialsException::class)]
#[CoversClass(EmailExistsException::class)]
#[CoversClass(UserNotFoundException::class)]
#[CoversClass(ExpiredOtpException::class)]
#[CoversClass(InvalidOtpException::class)]
#[CoversClass(UserRegistrationException::class)]
final class SecurityExceptionTest extends TestCase
{
    // ── InvalidCredentialsException ───────────────────────────────────────────

    #[Test]
    public function getStatusCode_InvalidCredentialsException_Returns400(): void
    {
        $exception = new InvalidCredentialsException();

        self::assertSame(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());
    }

    #[Test]
    public function getErrorMessage_InvalidCredentialsException_ReturnsExpectedMessage(): void
    {
        $exception = new InvalidCredentialsException();

        self::assertSame('Invalid credentials.', $exception->getErrorMessage());
    }

    // ── EmailExistsException ──────────────────────────────────────────────────

    #[Test]
    public function getStatusCode_EmailExistsException_Returns409(): void
    {
        $exception = new EmailExistsException();

        self::assertSame(Response::HTTP_CONFLICT, $exception->getStatusCode());
    }

    #[Test]
    public function getErrorMessage_EmailExistsException_ReturnsExpectedMessage(): void
    {
        $exception = new EmailExistsException();

        self::assertSame('Invalid email address', $exception->getErrorMessage());
    }

    // ── UserNotFoundException ─────────────────────────────────────────────────

    #[Test]
    public function getStatusCode_UserNotFoundException_Returns404(): void
    {
        $exception = new UserNotFoundException();

        self::assertSame(Response::HTTP_NOT_FOUND, $exception->getStatusCode());
    }

    #[Test]
    public function getErrorMessage_UserNotFoundException_ReturnsExpectedMessage(): void
    {
        $exception = new UserNotFoundException();

        self::assertSame('Invalid user.', $exception->getErrorMessage());
    }

    // ── ExpiredOtpException ───────────────────────────────────────────────────

    #[Test]
    public function getStatusCode_ExpiredOtpException_Returns400(): void
    {
        $exception = new ExpiredOtpException();

        self::assertSame(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());
    }

    #[Test]
    public function getErrorMessage_ExpiredOtpException_ReturnsExpectedMessage(): void
    {
        $exception = new ExpiredOtpException();

        self::assertSame('Invalid or expired verification code.', $exception->getErrorMessage());
    }

    // ── InvalidOtpException ───────────────────────────────────────────────────

    #[Test]
    public function getStatusCode_InvalidOtpException_Returns400(): void
    {
        $exception = new InvalidOtpException();

        self::assertSame(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());
    }

    #[Test]
    public function getErrorMessage_InvalidOtpException_ReturnsExpectedMessage(): void
    {
        $exception = new InvalidOtpException();

        self::assertSame('Invalid or expired verification code.', $exception->getErrorMessage());
    }

    // ── UserRegistrationException ─────────────────────────────────────────────

    #[Test]
    public function getStatusCode_UserRegistrationException_Returns400(): void
    {
        $exception = new UserRegistrationException();

        self::assertSame(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());
    }

    #[Test]
    public function getErrorMessage_UserRegistrationException_ReturnsExpectedMessage(): void
    {
        $exception = new UserRegistrationException();

        self::assertSame(
            'Something went wrong while registering an account. Please try again later.',
            $exception->getErrorMessage()
        );
    }
}
