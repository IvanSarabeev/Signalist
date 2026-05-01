# Command: Generate Test

## Purpose
Generate a fully structured PHPUnit test for a given PHP class, following the Signalist
test architecture — correct layer, correct mocks, shared DataProvider, naming convention enforced.

## Trigger
- `/generate-test <path>`
- "generate test for src/Security/Authentication.php"
- "write unit test for TokenManager"
- "add tests for StockController"

## Input
`$ARGUMENTS` — relative path to the PHP class under test (e.g. `src/Security/Authentication.php`)

## Execution Flow

### Step 1 — Read and classify the target class
- Read the full file at `$ARGUMENTS`
- Identify the class type:
    - **Controller** → `src/Controller/Api/**`
    - **Service** → `src/Service/**` or `src/Security/**`
    - **Exception** → `src/Exception/**`
    - **Repository** → `src/Repository/**`
    - **Mapper / DTO** → `src/Mapper/**`, `src/DTO/**`
- List every public method — these are the test targets
- Identify all constructor dependencies — these become mocks

### Step 2 — Determine test file location
Map source path → test path:

| Source | Test location |
|---|---|
| `src/Controller/Api/Foo/BarController.php` | `tests/UnitTests/Controller/Foo/BarControllerTest.php` |
| `src/Security/Authentication.php` | `tests/UnitTests/Service/Security/AuthenticationTest.php` |
| `src/Security/TokenManager.php` | `tests/UnitTests/Service/Security/TokenManagerTest.php` |
| `src/Service/Finnhub/FinnhubService.php` | `tests/UnitTests/Service/Finnhub/FinnhubServiceTest.php` |
| `src/Repository/UserRepository.php` | `tests/UnitTests/Repository/UserRepositoryTest.php` |
| `src/Exception/**/*.php` | `tests/UnitTests/Exception/**/*Test.php` |

### Step 3 — Check for a shared DataProvider
- Look in `tests/DataProviders/` for an existing provider matching the domain
  (e.g. `Auth/SignInDataProvider.php` for auth-related tests)
- If one exists: reference it with `#[DataProvider('methodName')]`
- If none exists: create the DataProvider class first in `tests/DataProviders/<Domain>/`

### Step 4 — Generate the test class
Apply all of the following rules (see `rules/testing.md` for full detail):

**Naming:**
- Test method naming: `MethodUnderTest_StateUnderTest_ExpectedBehaviour`
- Test class naming: `<ClassName>Test`

**Attributes (PHPUnit 11):**
- `#[CoversClass(TargetClass::class)]` on the test class
- `#[Test]` on every test method
- `#[DataProvider('providerMethod')]` when using a DataProvider

**Mock strategy by layer:**
- Controller → mock all injected services; use `$this->createMock()`
- Service → mock Repository, EntityManagerInterface, LoggerInterface, external clients
- Repository → use `KernelTestCase` + in-memory SQLite or mock `EntityManagerInterface`
- Exception → no mocks; instantiate directly and assert `getStatusCode()` / `getErrorMessage()`

**Assertions:**
- One logical assertion per test method
- Use `self::assertSame()` over `assertEquals()` for type-strict checks
- For exceptions: `$this->expectException(SomeException::class)` before the call

**Controller-specific:**
- Extend `tests/UnitTests/AbstractConfiguration` (WebTestCase) for HTTP-level tests
- Assert response status code, Content-Type, and JSON body shape
- Do not assert business logic — that belongs in the service test

**Service-specific:**
- Extend `PHPUnit\Framework\TestCase` directly
- Set up mocks in `setUp()`, assign to `private` properties
- Test every branch: happy path, each exception path, edge cases

### Step 5 — Output
- Write the DataProvider file (if new)
- Write the test file
- Run `composer test:unit` and confirm green
- Report: test count, assertions, coverage delta for the target class

## Output Format
```
## Test generated: <ClassName>Test

Layer        : Unit / Controller / Service / Integration
Test file    : tests/UnitTests/.../...Test.php
DataProvider : tests/DataProviders/...DataProvider.php (new|existing)
Methods covered:
  ✅ methodOne — N tests
  ✅ methodTwo — N tests

Run result   : X tests, X assertions — PASS
Coverage     : <ClassName> 100% methods / 100% lines
```
