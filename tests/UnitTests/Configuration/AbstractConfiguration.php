<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Configuration;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base class for controller unit tests that require a booted Symfony kernel
 * but do not need the full HTTP client stack.
 *
 * Boots the kernel automatically before each test via setUp() so subclasses
 * only need to call parent::setUp() — no manual self::bootKernel() required.
 *
 * Provides wireContainer() to connect a directly-instantiated controller to
 * the DI container so that AbstractController helpers (json(), getParameter(),
 * etc.) work without routing a request through the HTTP kernel.
 *
 * For integration tests that require an HTTP client, extend WebTestCase directly.
 */
abstract class AbstractConfiguration extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    /**
     * Wire the Symfony DI container into a directly-instantiated controller.
     *
     * Without this, AbstractController::json() and other container-dependent
     * helpers throw "Typed property ::$container must not be accessed before
     * initialization" when the controller is constructed manually in tests.
     *
     * Must be called after bootKernel() — guaranteed when invoked from setUp()
     * or any test method, since the kernel is booted in parent::setUp().
     */
    protected function wireContainer(AbstractController $controller): void
    {
        $controller->setContainer(static::getContainer());
    }

    // =========================================================================
    // JSON response assertion helpers
    // =========================================================================

    /**
     * Assert that a JSON response represents a successful API outcome.
     *
     * Checks HTTP status code and that the response body contains `status: true`.
     * Returns the decoded body so callers can assert additional fields inline:
     *
     *   $body = $this->assertApiSuccess($response, Response::HTTP_CREATED);
     *   self::assertSame('test-jwt-token', $body['token']);
     *
     * @return array<string, mixed> Decoded JSON body
     */
    protected function assertApiSuccess(JsonResponse $response, int $expectedStatus = Response::HTTP_OK): array
    {
        $body = json_decode($response->getContent(), true);
        self::assertSame($expectedStatus, $response->getStatusCode());
        self::assertTrue($body['status']);
        return $body;
    }

    /**
     * Assert that a JSON response represents a failed API outcome.
     *
     * Checks HTTP status code and that the response body contains `status: false`.
     * Returns the decoded body so callers can assert additional fields inline:
     *
     *   $body = $this->assertApiFailure($response, Response::HTTP_BAD_REQUEST);
     *   self::assertNotEmpty($body['invalid_fields']);
     *
     * @return array<string, mixed> Decoded JSON body
     */
    protected function assertApiFailure(JsonResponse $response, int $expectedStatus = Response::HTTP_BAD_REQUEST): array
    {
        $body = json_decode($response->getContent(), true);
        self::assertSame($expectedStatus, $response->getStatusCode());
        self::assertFalse($body['status']);
        return $body;
    }

    // =========================================================================
    // Request builder helpers
    // =========================================================================

    /**
     * Build a Request with a JSON-encoded body and the correct Content-Type header.
     * Use this when the controller expects well-formed JSON input.
     *
     * @param array<string, mixed> $data
     */
    protected function buildJsonRequest(array $data): Request
    {
        $request = new Request(content: json_encode($data));
        $request->headers->set('Content-Type', 'application/json');

        return $request;
    }

    /**
     * Build a Request with a raw (potentially malformed) body.
     * Use this to test how controllers handle invalid JSON payloads.
     */
    protected function buildRawRequest(string $content): Request
    {
        $request = new Request(content: $content);
        $request->headers->set('Content-Type', 'application/json');

        return $request;
    }
}
