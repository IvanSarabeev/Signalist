<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Controller;

use App\Presentation\Http\Controller\Api\AbstractController;
use App\Presentation\Http\Exception\Common\InvalidPaginationArgumentException;
use App\Tests\DataProviders\Controller\AbstractControllerDataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Layer   : Unit
 * Mocks   : none — only the SUT anonymous class and lightweight Symfony ConstraintViolation stubs
 * Extends : TestCase (no kernel required)
 */
#[CoversClass(AbstractController::class)]
final class AbstractControllerTest extends TestCase
{
    /**
     * Returns a concrete anonymous subclass that promotes every protected
     * method we want to test to public visibility.
     */
    private function makeSut(): object
    {
        return new class extends AbstractController {
            public function exposePagination(
                Request $request,
                int     $defaultPage = 1,
                int     $defaultPageLimit = 10
            ): array
            {
                return $this->createPaginationParametersFromRequest($request, $defaultPage, $defaultPageLimit);
            }

            public function exposeConstraintViolation(
                ConstraintViolationListInterface $list,
                bool                             $formatMessages = false,
                bool                             $formatToArray = false,
                int                              $statusCode = Response::HTTP_BAD_REQUEST,
            ): ?JsonResponse
            {
                return $this->constraintViolationJsonResponse($list, $formatMessages, $formatToArray, $statusCode);
            }

            public function exposeNormalizeEnumFields(object $dto, array $map): void
            {
                $this->normalizeEnumFields($dto, $map);
            }
        };
    }

    #[Test]
    #[DataProvider('validPaginationParameters')]
    public function createPaginationParametersFromRequest_WithValidPageAndLimit_ReturnsCorrectOffsetAndFetchLimit(
        int $page,
        int $limit,
        int $expectedOffset,
        int $expectedFetchLimit
    ): void
    {
        $sut     = $this->makeSut();
        $request = Request::create('/', Request::METHOD_GET, ['page' => $page, 'limit' => $limit]);

        $result = $sut->exposePagination($request);

        self::assertSame($expectedOffset,     $result['offset']);
        self::assertSame($expectedFetchLimit, $result['fetch_limit']);
    }

    public static function validPaginationParameters(): array
    {
        return AbstractControllerDataProvider::validPaginationParameters();
    }

    #[Test]
    public function createPaginationParametersFromRequest_WithNoQueryParameters_UsesDefaultPageAndLimit(): void
    {
        $sut     = $this->makeSut();
        $request = Request::create('/');

        $result = $sut->exposePagination($request);

        self::assertSame(0,  $result['offset']);
        self::assertSame(10, $result['fetch_limit']);
    }

    #[Test]
    public function createPaginationParametersFromRequest_WithCustomDefaults_AppliesThemWhenQueryIsMissing(): void
    {
        $sut     = $this->makeSut();
        $request = Request::create('/');

        $result = $sut->exposePagination($request, defaultPage: 3, defaultPageLimit: 20);

        self::assertSame(40, $result['offset']);      // (3-1)*20
        self::assertSame(20, $result['fetch_limit']);
    }

    #[Test]
    #[DataProvider('invalidPaginationParameters')]
    public function createPaginationParametersFromRequest_WithInvalidPageOrLimit_ThrowsInvalidPaginationArgumentException(
        int $page,
        int $limit
    ): void
    {
        $sut     = $this->makeSut();
        $request = Request::create('/', Request::METHOD_GET, ['page' => $page, 'limit' => $limit]);

        $this->expectException(InvalidPaginationArgumentException::class);

        $sut->exposePagination($request);
    }

    public static function invalidPaginationParameters(): array
    {
        return AbstractControllerDataProvider::invalidPaginationParameters();
    }

    #[Test]
    public function constraintViolationJsonResponse_WithNoViolations_ReturnsNull(): void
    {
        $sut    = $this->makeSut();
        $result = $sut->exposeConstraintViolation(new ConstraintViolationList());

        self::assertNull($result);
    }

    // -----------------------------------------------------------------------
    // constraintViolationJsonResponse — single violation
    // -----------------------------------------------------------------------

    #[Test]
    #[DataProvider('singleViolation')]
    public function constraintViolationJsonResponse_WithSingleViolation_ReturnsJsonResponseWithCorrectStatusCode(
        array $violations,
        bool  $formatMessages,
        bool  $formatToArray,
        int   $statusCode
    ): void {
        $sut  = $this->makeSut();
        $list = $this->buildViolationList($violations);

        $response = $sut->exposeConstraintViolation($list, $formatMessages, $formatToArray, $statusCode);

        self::assertNotNull($response);
        self::assertSame($statusCode, $response->getStatusCode());
    }

    public static function singleViolation(): array
    {
        return AbstractControllerDataProvider::singleViolation();
    }

    #[Test]
    public function constraintViolationJsonResponse_WithSingleViolation_ReturnsStatusFalseInBody(): void
    {
        $sut  = $this->makeSut();
        $list = $this->buildViolationList([['path' => 'email', 'message' => 'Not valid.']]);

        $response = $sut->exposeConstraintViolation($list);
        $body     = json_decode($response->getContent(), true);

        self::assertSame(false, $body['status']);
    }

    #[Test]
    public function constraintViolationJsonResponse_WithSingleViolation_ReturnsInvalidFieldsInBody(): void
    {
        $sut  = $this->makeSut();
        $list = $this->buildViolationList([['path' => 'email', 'message' => 'Not valid.']]);

        $response = $sut->exposeConstraintViolation($list);
        $body     = json_decode($response->getContent(), true);

        self::assertContains('email', $body['invalid_fields']);
    }

    #[Test]
    public function constraintViolationJsonResponse_WithSingleViolation_ReturnsMessageInBody(): void
    {
        $sut  = $this->makeSut();
        $list = $this->buildViolationList([['path' => 'email', 'message' => 'Not valid.']]);

        $response = $sut->exposeConstraintViolation($list);
        $body     = json_decode($response->getContent(), true);

        self::assertStringContainsString('Not valid.', $body['message']);
    }

    // -----------------------------------------------------------------------
    // constraintViolationJsonResponse — multiple violations
    // -----------------------------------------------------------------------

    #[Test]
    #[DataProvider('multipleViolations')]
    public function constraintViolationJsonResponse_WithMultipleViolations_ReturnsAllInvalidFields(
        array $violations,
        bool  $formatMessages,
        bool  $formatToArray
    ): void
    {
        $sut  = $this->makeSut();
        $list = $this->buildViolationList($violations);

        $response = $sut->exposeConstraintViolation($list, $formatMessages, $formatToArray);
        $body     = json_decode($response->getContent(), true);

        self::assertCount(count($violations), $body['invalid_fields']);
    }

    public static function multipleViolations(): array
    {
        return AbstractControllerDataProvider::multipleViolations();
    }

    #[Test]
    public function constraintViolationJsonResponse_WithFormatMessagesTrue_JoinsMessagesWithBrTag(): void
    {
        $sut  = $this->makeSut();
        $list = $this->buildViolationList([
            ['path' => 'email',    'message' => 'Invalid email.'],
            ['path' => 'password', 'message' => 'Too short.'],
        ]);

        $response = $sut->exposeConstraintViolation($list, formatMessages: true);
        $body     = json_decode($response->getContent(), true);

        self::assertStringContainsString('<br/>', $body['message']);
    }

    #[Test]
    public function constraintViolationJsonResponse_WithFormatMessagesFalse_JoinsMessagesWithComma(): void
    {
        $sut  = $this->makeSut();
        $list = $this->buildViolationList([
            ['path' => 'email',    'message' => 'Invalid email.'],
            ['path' => 'password', 'message' => 'Too short.'],
        ]);

        $response = $sut->exposeConstraintViolation($list, formatMessages: false);
        $body     = json_decode($response->getContent(), true);

        self::assertStringContainsString(', ', $body['message']);
    }

    // -----------------------------------------------------------------------
    // constraintViolationJsonResponse — duplicate messages deduplication
    // -----------------------------------------------------------------------

    #[Test]
    #[DataProvider('duplicateMessages')]
    public function constraintViolationJsonResponse_WithDuplicateMessages_DeduplicatesMessageInBody(
        array $violations
    ): void
    {
        $sut  = $this->makeSut();
        $list = $this->buildViolationList($violations);

        $response = $sut->exposeConstraintViolation($list);
        $body     = json_decode($response->getContent(), true);

        // The same message string must appear only once in the concatenated output
        $message = $body['message'];
        $needle  = 'This value should not be blank.';
        self::assertSame(1, substr_count($message, $needle));
    }

    public static function duplicateMessages(): array
    {
        return AbstractControllerDataProvider::duplicateMessages();
    }

    #[Test]
    public function constraintViolationJsonResponse_WithBracketPath_StripsSquareBrackets(): void
    {
        $sut  = $this->makeSut();
        $list = $this->buildViolationList([['path' => '[email]', 'message' => 'Bad value.']]);

        $response = $sut->exposeConstraintViolation($list, formatToArray: false);
        $body     = json_decode($response->getContent(), true);

        self::assertNotContains('[email]', $body['invalid_fields']);
        self::assertContains('email', $body['invalid_fields']);
    }

    #[Test]
    public function constraintViolationJsonResponse_WithArrayStylePath_NormalizesWithFormatToArray(): void
    {
        $sut  = $this->makeSut();
        $list = $this->buildViolationList([['path' => '[0]email', 'message' => 'Bad value.']]);

        $response = $sut->exposeConstraintViolation($list, formatToArray: true);
        $body     = json_decode($response->getContent(), true);

        // Leading [N] prefix is removed; only the field name remains
        self::assertContains('0email', $body['invalid_fields']);
    }

    // -----------------------------------------------------------------------
    // normalizeEnumFields — valid enum label is resolved
    // -----------------------------------------------------------------------

    #[Test]
    public function normalizeEnumFields_WithValidEnumLabel_SetsResolvedValueOnDto(): void
    {
        $sut = $this->makeSut();

        // Anonymous enum stub whose fromLabel() returns a known value
        $enumStub = new class {
            public string $value = 'active_value';
            public static function fromLabel(string $label): static
            {
                $i        = new static();
                $i->value = strtolower($label) . '_value';
                return $i;
            }
        };

        $dto           = new \stdClass();
        $dto->status   = 'Active';

        $sut->exposeNormalizeEnumFields($dto, ['status' => $enumStub::class]);

        self::assertSame('active_value', $dto->status);
    }

    // -----------------------------------------------------------------------
    // normalizeEnumFields — non-string / empty values are skipped
    // -----------------------------------------------------------------------

    #[Test]
    #[DataProvider('skippedEnumValues')]
    public function normalizeEnumFields_WithNonStringOrEmptyValue_LeavesPropertyUnchanged(
        mixed $propertyValue
    ): void
    {
        $sut = $this->makeSut();

        // The enum stub should never be called for skipped values
        $enumStub = new class {
            public static function fromLabel(string $label): static
            {
                throw new \LogicException('fromLabel must not be called for non-string values');
            }
        };

        $dto         = new \stdClass();
        $dto->status = $propertyValue;
        $original    = $dto->status;

        $sut->exposeNormalizeEnumFields($dto, ['status' => $enumStub::class]);

        self::assertSame($original, $dto->status);
    }

    public static function skippedEnumValues(): array
    {
        return AbstractControllerDataProvider::skippedEnumValues();
    }

    // -----------------------------------------------------------------------
    // normalizeEnumFields — unknown property is silently ignored
    // -----------------------------------------------------------------------

    #[Test]
    public function normalizeEnumFields_WithUnknownProperty_DoesNotThrow(): void
    {
        $sut = $this->makeSut();
        $dto = new \stdClass(); // has no 'nonExistent' property

        // No exception expected — reflection check gates the call
        $sut->exposeNormalizeEnumFields($dto, ['nonExistent' => \stdClass::class]);

        // If we got here the method handled the missing property gracefully
        self::assertTrue(true);
    }

    // -----------------------------------------------------------------------
    // normalizeEnumFields — invalid enum label leaves value unchanged
    // -----------------------------------------------------------------------

    #[Test]
    public function normalizeEnumFields_WithInvalidEnumLabel_LeavesOriginalValueUnchanged(): void
    {
        $sut = $this->makeSut();

        $enumStub = new class {
            public static function fromLabel(string $label): static
            {
                throw new \InvalidArgumentException("Unknown label: $label");
            }
        };

        $dto         = new \stdClass();
        $dto->status = 'garbage';

        $sut->exposeNormalizeEnumFields($dto, ['status' => $enumStub::class]);

        self::assertSame('garbage', $dto->status);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * @param array<array{path: string, message: string}> $violations
     */
    private function buildViolationList(array $violations): ConstraintViolationList
    {
        $list = new ConstraintViolationList();

        foreach ($violations as ['path' => $path, 'message' => $message]) {
            $list->add(new ConstraintViolation(
                message:           $message,
                messageTemplate:   $message,
                parameters:        [],
                root:              null,
                propertyPath:      $path,
                invalidValue:      null,
            ));
        }

        return $list;
    }
}
