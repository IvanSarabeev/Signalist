# Testing Rules — Signalist

## Stack
- PHPUnit 11 (`vendor/bin/phpunit`)
- Symfony `WebTestCase` / `KernelTestCase` for kernel-dependent tests
- Xdebug 3 for code coverage
- Bootstrap: `tests/bootstrap.php`
- Config: `phpunit.xml.dist`

## Run Commands
```bash
composer test               # all suites + coverage (HTML → /html, Clover → /html/clover.xml)
composer test:unit          # Unit suite only
composer test:integration   # Integration suite only
```

---

## Directory Structure

```
tests/
├── bootstrap.php
├── DataProviders/                  # Shared static input fixtures — no assertions here
│   └── Auth/
│       ├── SignInDataProvider.php
│       └── RegisterDataProvider.php
├── UnitTests/
│   ├── AbstractConfiguration.php   # Extends WebTestCase — base for functional unit tests
│   ├── Controller/                 # HTTP contract tests — mock all services
│   │   └── Auth/
│   │       └── AuthenticationControllerTest.php
│   ├── Service/                    # Business logic tests — mock repos and external deps
│   │   ├── Security/
│   │   │   ├── AuthenticationTest.php
│   │   │   └── TokenManagerTest.php
│   │   └── Finnhub/
│   │       └── FinnhubServiceTest.php
│   └── Exception/                  # DomainException contract tests — no mocks
│       ├── SecurityExceptionTest.php
│       ├── TokenExceptionTest.php
│       └── CommonExceptionTest.php
└── IntegrationTests/               # Full-stack: real kernel + test DB + HTTP client
    └── Auth/
        └── AuthFlowTest.php
```

---

## Naming Convention

### Test methods
`MethodUnderTest_StateUnderTest_ExpectedBehaviour`

```php
// ✅ correct
public function authenticateUser_WithInvalidCredentials_ThrowsInvalidCredentialsException(): void
public function getStatusCode_InvalidCredentialsException_Returns400(): void
public function register_WithDuplicateEmail_Returns409(): void  // controller layer

// ❌ wrong
public function testLogin(): void
public function test_it_throws_when_wrong(): void
```

### Test classes
`<ClassName>Test` in the same namespace structure as the source.

```
src/Security/Authentication.php  →  tests/UnitTests/Service/Security/AuthenticationTest.php
App\Security\Authentication      →  App\Tests\UnitTests\Service\Security\AuthenticationTest
```

---

## PHPUnit 11 Attributes

Always use attributes — never docblock annotations.

```php
#[CoversClass(Authentication::class)]       // on the test class
#[Test]                                     // on every test method
#[DataProvider('providerMethodName')]       // when using a DataProvider
```

---

## DataProvider Pattern

DataProviders live in `tests/DataProviders/<Domain>/` and are **pure static input fixtures**.
They contain no assertions and no layer-specific logic — only the raw data that both
controller and service tests consume.

```php
// tests/DataProviders/Auth/SignInDataProvider.php
namespace App\Tests\DataProviders\Auth;

final class SignInDataProvider
{
    /** @return array<string, array{email: string, password: string}> */
    public static function validCredentials(): array
    {
        return [
            'standard user' => ['email' => 'user@example.com', 'password' => 'Secret1!'],
        ];
    }

    /** @return array<string, array{email: string, password: string}> */
    public static function invalidCredentials(): array
    {
        return [
            'wrong password'  => ['email' => 'user@example.com', 'password' => 'wrong'],
            'unknown email'   => ['email' => 'ghost@example.com', 'password' => 'Secret1!'],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    public static function missingFields(): array
    {
        return [
            'no email'    => ['password' => 'Secret1!'],
            'no password' => ['email' => 'user@example.com'],
            'empty body'  => [],
        ];
    }
}
```

**Referencing in tests:**
```php
#[DataProvider('invalidCredentials')]
// Note: reference the DataProvider class method as a static callable
// PHPUnit 11: use the fully-qualified method reference via #[DataProvider]
```

---

## Mock Strategy by Layer

### Exception tests — no mocks
```php
// Extend: TestCase
// Just instantiate and assert the contract
$exception = new InvalidCredentialsException();
self::assertSame(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());
self::assertSame('Invalid credentials.', $exception->getErrorMessage());
```

### Service tests — mock infrastructure only
```php
// Extend: TestCase
// Mock: Repository, EntityManagerInterface, LoggerInterface, external HTTP clients
// Never mock: the class under test, DTOs, Entities, Enums, value objects

private Authentication $authentication;
private MockObject&UserRepository $userRepository;
private MockObject&UserPasswordHasherInterface $passwordHasher;

protected function setUp(): void
{
    $this->userRepository = $this->createMock(UserRepository::class);
    $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
    // ...
    $this->authentication = new Authentication(
        $this->createMock(EntityManagerInterface::class),
        $this->userRepository,
        $this->passwordHasher,
        $this->createMock(LoggerInterface::class),
        $this->createMock(NotificationDispatcher::class),
    );
}
```

### Controller tests — mock all services, assert HTTP shape
```php
// Extend: AbstractConfiguration (WebTestCase)
// Mock: all services injected into the controller
// Assert: HTTP status code, Content-Type: application/json, JSON body structure

$client = static::createClient();
$client->request('POST', '/api/v1/authentication/login', [], [], [
    'CONTENT_TYPE' => 'application/json',
], json_encode(['email' => 'x@x.com', 'password' => 'wrong']));

self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
self::assertJson($client->getResponse()->getContent());
```

### Integration tests — real kernel, real DB
```php
// Extend: AbstractConfiguration (WebTestCase)
// No mocks — real services, real DB (test schema, transaction-rolled-back)
// Assert: full HTTP response including DB side-effects
```

---

## Assertion Rules

- Use `self::assertSame()` — strict type + value equality (preferred over `assertEquals`)
- Use `self::assertSame()` for status codes, strings, counts
- Use `self::assertJson()` + `self::assertJsonStringEqualsJsonString()` for response bodies
- Use `$this->expectException(SomeException::class)` **before** the method call that throws
- One logical assertion per test (multiple `assertSame` on one object is fine if it's one concept)
- Never assert implementation details — assert observable outcomes

---

## Integration Test Setup

Integration tests require a dedicated test database. Configure in `phpunit.xml.dist`:

```xml
<server name="DATABASE_URL" value="mysql://root:@127.0.0.1:3306/signalist_test" force="true"/>
```

Use transaction rollback to keep tests isolated:

```php
protected function setUp(): void
{
    parent::setUp();
    self::bootKernel();
    $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    $this->entityManager->beginTransaction();
}

protected function tearDown(): void
{
    $this->entityManager->rollback();
    parent::tearDown();
}
```

---

## Coverage Rules

- Every `DomainException` subclass → 100% methods and lines (enforced)
- Every new service method → at least happy path + every exception path covered
- Every new controller action → HTTP contract covered (status code + body shape)
- Coverage is a by-product of meaningful tests — do not write tests purely to hit a number
- HTML report: `html/index.html` (generated on every `composer test` run, gitignored)

---

## Hard Rules

- No test may depend on another test's state — every test must be fully isolated
- No test may hit a real external service (Finnhub API, Mailtrap) — mock the client
- No `@` annotation style — use `#[Attribute]` syntax (PHPUnit 11)
- No `any` types in DataProviders — every dataset must be typed with a `@return` docblock
- Every bug fix → regression test written first (red), then fix (green)
- TDD is the default workflow for new features, not optional
