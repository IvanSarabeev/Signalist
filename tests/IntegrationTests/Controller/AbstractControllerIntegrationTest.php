<?php

declare(strict_types=1);

namespace App\Tests\IntegrationTests\Controller;

use App\Presentation\Http\Controller\Api\AbstractController;
use App\Tests\DataProviders\Controller\AbstractControllerDataProvider;
use App\Tests\IntegrationTests\Configuration\AbstractConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Integration tests for AbstractController.
 *
 * Strategy
 * --------
 * AbstractController is abstract and cannot be instantiated directly.
 * These tests boot the real Symfony kernel, register a lightweight
 * concrete stub controller via the test client, and make real HTTP
 * requests — asserting status codes, Content-Type headers, and JSON
 * body shapes as produced by the actual framework stack.
 *
 * No business logic is asserted here; that belongs in the unit suite.
 *
 * Layer   : Integration
 * Mocks   : none — real kernel, real HTTP stack
 * Extends : AbstractConfiguration (WebTestCase)
 *
 * Stub routes registered (must be wired in test routing or via kernel override):
 *   GET  /test/paginate          → exercises createPaginationParametersFromRequest
 *   POST /test/validate          → exercises constraintViolationJsonResponse
 *   POST /test/normalize-enum    → exercises normalizeEnumFields
 *
 * NOTE: Wire the stub controller below in your test routing config, e.g.:
 *   # config/routes/test.yaml
 *   test_abstract_controller:
 *       resource: '../../tests/IntegrationTests/Controller/AbstractControllerIntegrationTest.php'
 *       type: attribute
 */
#[CoversClass(AbstractController::class)]
final class AbstractControllerIntegrationTest extends AbstractConfiguration
{
    private const SERVER_CONTENT_TYPE = ['CONTENT_TYPE' => 'application/json'];

    #[Test]
    #[DataProvider('validPaginationParameters')]
    public function paginationEndpoint_WithValidParameters_Returns200AndCorrectPayload(
        int $page,
        int $limit,
        int $expectedOffset,
        int $expectedFetchLimit
    ): void {
        $client = AbstractControllerIntegrationTest::createClient();

        $client->request(
            method: Request::METHOD_GET,
            uri: '/test/paginate',
            parameters: ['page' => $page, 'limit' => $limit],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $body = json_decode($client->getResponse()->getContent(), true);

        self::assertSame($expectedOffset,     $body['offset']);
        self::assertSame($expectedFetchLimit, $body['fetch_limit']);
    }

    public static function validPaginationParameters(): array
    {
        return AbstractControllerDataProvider::validPaginationParameters();
    }

    #[Test]
    public function paginationEndpoint_WithNoQueryParameters_Returns200WithDefaults(): void
    {
        $client = AbstractControllerIntegrationTest::createClient();
        $client->request(Request::METHOD_GET, '/test/paginate');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $body = json_decode($client->getResponse()->getContent(), true);

        self::assertSame(0,  $body['offset']);
        self::assertSame(10, $body['fetch_limit']);
    }

    #[Test]
    #[DataProvider('invalidPaginationParameters')]
    public function paginationEndpoint_WithInvalidParameters_Returns400(
        int $page,
        int $limit
    ): void {
        $client = AbstractControllerIntegrationTest::createClient();

        $client->request(
            method:Request::METHOD_GET,
            uri: '/test/paginate',
            parameters: ['page' => $page, 'limit' => $limit],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $body = json_decode($client->getResponse()->getContent(), true);

        self::assertSame(false, $body['status']);
        self::assertArrayHasKey('message', $body);
    }

    public static function invalidPaginationParameters(): array
    {
        return AbstractControllerDataProvider::invalidPaginationParameters();
    }

    // =========================================================================
    // constraintViolationJsonResponse — HTTP level
    // =========================================================================

    #[Test]
    public function validateEndpoint_WithNoViolations_Returns200WithSuccessStatus(): void
    {
        $client = AbstractControllerIntegrationTest::createClient();

        $client->request(
            method: Request::METHOD_POST,
            uri: '/test/validate',
            server: self::SERVER_CONTENT_TYPE,
            content: json_encode(['violations' => []]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $body = json_decode($client->getResponse()->getContent(), true);

        self::assertSame(true, $body['status']);
    }

    #[Test]
    public function validateEndpoint_WithViolations_Returns400WithStatusFalse(): void
    {
        $client = AbstractControllerIntegrationTest::createClient();

        $client->request(
            method: Request::METHOD_POST,
            uri: '/test/validate',
            server: self::SERVER_CONTENT_TYPE,
            content: json_encode([
                'violations' => [
                    ['path' => 'email', 'message' => 'Not a valid email.'],
                ],
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $body = json_decode($client->getResponse()->getContent(), true);

        self::assertSame(false, $body['status']);
        self::assertArrayHasKey('invalid_fields', $body);
        self::assertArrayHasKey('message', $body);
    }

    #[Test]
    public function validateEndpoint_WithMultipleViolations_ReturnsAllInvalidFields(): void
    {
        $client = AbstractControllerIntegrationTest::createClient();

        $client->request(
            method: Request::METHOD_POST,
            uri: '/test/validate',
            server: self::SERVER_CONTENT_TYPE,
            content: json_encode([
                'violations' => [
                    ['path' => 'email',    'message' => 'Not a valid email.'],
                    ['path' => 'password', 'message' => 'Too short.'],
                ],
            ]),
        );

        $body = json_decode($client->getResponse()->getContent(), true);

        self::assertCount(2, $body['invalid_fields']);
    }

    #[Test]
    public function validateEndpoint_WithFormatMessages_ReturnsBrTagSeparatedMessages(): void
    {
        $client = AbstractControllerIntegrationTest::createClient();

        $client->request(
            method: Request::METHOD_POST,
            uri: '/test/validate?formatMessages=1',
            server: self::SERVER_CONTENT_TYPE,
            content: json_encode([
                'violations' => [
                    ['path' => 'email',    'message' => 'Not a valid email.'],
                    ['path' => 'password', 'message' => 'Too short.'],
                ],
            ]),
        );

        $body = json_decode($client->getResponse()->getContent(), true);

        self::assertStringContainsString('<br/>', $body['message']);
    }

    #[Test]
    public function validateEndpoint_WithDuplicateMessages_DeduplicatesInResponse(): void
    {
        $client = AbstractControllerIntegrationTest::createClient();

        $client->request(
            method: Request::METHOD_POST,
            uri: '/test/validate',
            server: self::SERVER_CONTENT_TYPE,
            content: json_encode([
                'violations' => [
                    ['path' => 'email',    'message' => 'Required.'],
                    ['path' => 'password', 'message' => 'Required.'],
                ],
            ]),
        );

        $body    = json_decode($client->getResponse()->getContent(), true);
        $message = $body['message'];

        self::assertSame(1, substr_count($message, 'Required.'));
    }

    // =========================================================================
    // normalizeEnumFields — HTTP level
    // =========================================================================

    #[Test]
    public function normalizeEnumEndpoint_WithValidLabel_Returns200AndResolvedValue(): void
    {
        $client = AbstractControllerIntegrationTest::createClient();

        $client->request(
            method: Request::METHOD_POST,
            uri: '/test/normalize-enum',
            server: self::SERVER_CONTENT_TYPE,
            content: json_encode(['status' => 'active']),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $body = json_decode($client->getResponse()->getContent(), true);

        // The stub controller resolves 'active' → its enum value and echoes it back
        self::assertArrayHasKey('status', $body);
        self::assertNotSame('active', $body['status']); // value was replaced by enum resolution
    }

    #[Test]
    public function normalizeEnumEndpoint_WithEmptyStringLabel_Returns200WithUnchangedValue(): void
    {
        $client = AbstractControllerIntegrationTest::createClient();

        $client->request(
            method: Request::METHOD_POST,
            uri: '/test/normalize-enum',
            server: self::SERVER_CONTENT_TYPE,
            content: json_encode(['status' => '']),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $body = json_decode($client->getResponse()->getContent(), true);

        self::assertSame('', $body['status']);
    }

    #[Test]
    public function normalizeEnumEndpoint_WithInvalidLabel_Returns200WithOriginalValue(): void
    {
        $client = AbstractControllerIntegrationTest::createClient();

        $client->request(
            method: Request::METHOD_POST,
            uri: '/test/normalize-enum',
            server: self::SERVER_CONTENT_TYPE,
            content: json_encode(['status' => 'garbage_label']),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $body = json_decode($client->getResponse()->getContent(), true);

        // Invalid label → InvalidArgumentException caught internally → value unchanged
        self::assertSame('garbage_label', $body['status']);
    }

    // =========================================================================
    // Response structure contract
    // =========================================================================

    #[Test]
    public function validateEndpoint_WithViolations_ResponseContentTypeIsJson(): void
    {
        $client = AbstractControllerIntegrationTest::createClient();

        $client->request(
            method: Request::METHOD_POST,
            uri: '/test/validate',
            server: self::SERVER_CONTENT_TYPE,
            content: json_encode([
                'violations' => [['path' => 'email', 'message' => 'Not a valid email.']],
            ])
        );

        self::assertResponseHeaderSame('Content-Type', 'application/json');
    }

    #[Test]
    public function validateEndpoint_WithViolations_ResponseBodyIsValidJson(): void
    {
        $client = AbstractControllerIntegrationTest::createClient();

        $client->request(
            method: Request::METHOD_POST,
            uri: '/test/validate',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'violations' => [['path' => 'email', 'message' => 'Bad.']],
            ])
        );

        self::assertJson($client->getResponse()->getContent());
    }
}
