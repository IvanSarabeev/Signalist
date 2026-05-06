<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Controller\Api;

use App\Controller\Api\Authentication\AuthenticationController;
use App\Entity\User;
use App\Presentation\Http\Exception\Security\InvalidCredentialsException;
use App\Security\Auth\AuthenticationInterface;
use App\Security\Token\TokenManagerInterface;
use App\Tests\DataProviders\Auth\RegisterDataProvider;
use App\Tests\DataProviders\Auth\SignInDataProvider;
use App\Tests\UnitTests\Configuration\AbstractConfiguration;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(AuthenticationController::class)]
final class AuthenticationControllerTest extends AbstractConfiguration
{
    private AuthenticationController $controller;
    private MockObject&AuthenticationInterface $authentication;
    private MockObject&TokenManagerInterface $tokenManager;

    protected function setUp(): void
    {
        parent::setUp(); // boots kernel via AbstractConfiguration

        $container = AuthenticationControllerTest::getContainer();

        // Mock only business services — real serializer + validator from kernel
        $this->authentication = $this->createMock(AuthenticationInterface::class);
        $this->tokenManager   = $this->createMock(TokenManagerInterface::class);

        $this->controller = new AuthenticationController(
            $this->authentication,
            $container->get(SerializerInterface::class),
            $container->get('App\Notification\NotificationDispatcher'),
            $this->tokenManager,
            $container->get(ValidatorInterface::class),
        );

        // Wire the DI container so that $this->json() resolves the serializer service
        $this->wireContainer($this->controller);
    }

    // =========================================================================
    // authenticateUser — POST /api/v1/authentication/login
    // =========================================================================

    #[Test]
    public function authenticateUser_WithInvalidJsonPayload_Returns422(): void
    {
        $response = $this->controller->authenticateUser($this->buildRawRequest('{invalid-json'));

        $body = $this->assertApiFailure($response, Response::HTTP_UNPROCESSABLE_ENTITY);

        self::assertSame('Invalid JSON payload', $body['message']);
    }

    #[Test]
    #[DataProviderExternal(SignInDataProvider::class, 'missingFields')]
    public function authenticateUser_WithMissingFields_Returns400(array $payload): void
    {
        $response = $this->controller->authenticateUser($this->buildJsonRequest($payload));

        $body = $this->assertApiFailure($response);

        self::assertNotEmpty($body['invalid_fields']);
    }

    #[Test]
    #[DataProviderExternal(SignInDataProvider::class, 'invalidEmailFormat')]
    public function authenticateUser_WithInvalidEmailFormat_Returns400(array $payload): void
    {
        $response = $this->controller->authenticateUser($this->buildJsonRequest($payload));

        $this->assertApiFailure($response);
    }

    #[Test]
    #[DataProviderExternal(SignInDataProvider::class, 'passwordTooShort')]
    public function authenticateUser_WithPasswordTooShort_Returns400(array $payload): void
    {
        $response = $this->controller->authenticateUser($this->buildJsonRequest($payload));

        $this->assertApiFailure($response);
    }

    #[Test]
    public function authenticateUser_WithInvalidCredentials_Returns401(): void
    {
        $this->authentication->method('authenticateUser')->willThrowException(new InvalidCredentialsException());

        $response = $this->controller->authenticateUser(
            $this->buildJsonRequest(['email' => 'user@example.com', 'password' => 'WrongPass1!'])
        );

        $this->assertApiFailure($response, Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    public function authenticateUser_WithValidCredentials_Returns200(): void
    {
        $mockUser = $this->createMock(User::class);
        $this->authentication->method('authenticateUser')->willReturn($mockUser);
        $this->tokenManager->method('generateAccessToken')->with($mockUser)->willReturn('test-jwt-token');

        $response = $this->controller->authenticateUser(
            $this->buildJsonRequest(['email' => 'user@example.com', 'password' => 'Secret1!'])
        );

        $body = $this->assertApiSuccess($response);

        self::assertSame('test-jwt-token', $body['token']);
    }

    // =========================================================================
    // registerUser — POST /api/v1/authentication/register
    // =========================================================================

    #[Test]
    public function registerUser_WithInvalidJsonPayload_Returns422(): void
    {
        $response = $this->controller->registerUser($this->buildRawRequest('{invalid-json'));

        $body = $this->assertApiFailure($response, Response::HTTP_UNPROCESSABLE_ENTITY);

        self::assertSame('Invalid JSON payload', $body['message']);
    }

    #[Test]
    #[DataProviderExternal(RegisterDataProvider::class, 'missingFields')]
    public function registerUser_WithMissingFields_Returns400(array $payload): void
    {
        $response = $this->controller->registerUser($this->buildJsonRequest($payload));

        $body = $this->assertApiFailure($response);

        self::assertNotEmpty($body['invalid_fields']);
    }

    #[Test]
    #[DataProviderExternal(RegisterDataProvider::class, 'invalidEnumValues')]
    public function registerUser_WithInvalidEnumValues_Returns400(array $payload): void
    {
        $response = $this->controller->registerUser($this->buildJsonRequest($payload));

        $this->assertApiFailure($response);
    }

    #[Test]
    public function registerUser_WhenPersistFails_Returns500(): void
    {
        $this->authentication->method('persistUserRegistration')->willThrowException(new Exception('Unexpected DB failure'));

        $payload  = RegisterDataProvider::validPayload()['complete registration'];
        $response = $this->controller->registerUser($this->buildJsonRequest($payload));

        $body = $this->assertApiFailure($response, Response::HTTP_INTERNAL_SERVER_ERROR);

        self::assertSame('Unexpected DB failure', $body['message']);
    }

    #[Test]
    public function registerUser_WithValidPayload_Returns201(): void
    {
        $this->authentication
            ->expects(self::once())
            ->method('persistUserRegistration');

        $payload  = RegisterDataProvider::validPayload()['complete registration'];
        $response = $this->controller->registerUser($this->buildJsonRequest($payload));

        $this->assertApiSuccess($response, Response::HTTP_CREATED);
    }
}
