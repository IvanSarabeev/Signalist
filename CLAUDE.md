# CLAUDE.md — Signalist Multi-Agent Workspace

This file defines the agent roster, responsibilities, and collaboration protocol for the **Signalist** project — a stock-market tracking and price-alert application built on a **Symfony 7.4 / PHP 8.2+** backend with a **React 19 / TypeScript / Tailwind CSS 4 / shadcn/ui** frontend.

---

## Project Snapshot

| Layer | Stack |
|---|---|
| Backend | Symfony 7.4, PHP 8.2+, Doctrine ORM 3 / DBAL 3 |
| Auth | Stateless JWT via `firebase/php-jwt` (custom `ApiKeyAuthenticator`) + refresh-token rotation; email OTP 2FA via `scheb/2fa-email` |
| Async | Symfony Messenger (Doctrine transport) + Symfony Scheduler (cron) |
| Frontend | React 19, TypeScript 5, Vite, Tailwind CSS 4, shadcn/ui (loaded via `pentatrion/vite-bundle`) |
| Database | MySQL / MariaDB (Doctrine migrations) |
| Email | Symfony Mailer → Mailtrap (`symfony/mailtrap-mailer`), sent async via Messenger |
| Stock data | Finnhub API (`finnhub/client`), config in `config/packages/finnhub.yaml` |
| Observability | Sentry (`sentry/sentry-symfony`) |
| API docs | Nelmio API Doc — Swagger UI at `/api/doc`, JSON at `/api/doc.json` |

**PSR-4:** `App\` → `src/`. All API routes are prefixed `/api/v1`.

---

## Architecture Map (authoritative — verify against source, not memory)

```
src/
├── Entity/                 Doctrine entities: User, Stock, Alert, WatchlistItem, RefreshTokens
├── Enum/                   Behavioural enums (label/symbol/evaluate/cooldown), not just constants
│   ├── Alert/              AlertCondition, AlertFrequency, AlertType
│   ├── Finnhub/            CategoryNews
│   ├── Security/           RateLimiter
│   └── User/               InvestmentGoal, PreferredIndustry, RiskTolerance
│   └── NotificationType
├── Repository/             One ServiceEntityRepository per entity
├── Security/
│   ├── ApiKeyAuthenticator.php   Stateless JWT bearer auth for ^/api/v1 (skips /authentication)
│   ├── Auth/               Authentication(+Interface)
│   ├── Otp/                OtpService, OtpGenerator(+Interface)
│   └── Token/              TokenManager(+Interface) — access + refresh token issue/rotate/revoke
├── Service/                Business logic; every service has an *Interface
│   ├── Alert/              AlertService, AlertEvaluationService, AlertTriggerService, Metric/AlertMetricProvider
│   ├── Finnhub/            FinnhubService + Provider/ (AbstractFinnhubClient, FinnhubClient) + Configuration/ + Enum/FinnhubCache
│   ├── Mailer/             EmailService, EmailFactory
│   ├── Stock/              StockService
│   └── Watchlist/          WatchlistService
├── Infrastructure/
│   ├── Finnhub/            Response mappers (Quote, CompanyProfile)
│   └── Routing/            RouteRequirements
├── Message/                Messenger message DTOs
│   ├── Alert/              CheckAlertMessage, ProcessAlertByFrequencyMessage, TriggeredAlertMessage
│   └── Auth/               SendOtpMessage, SendWelcomeEmailMessage
├── Message/Handler/        #[AsMessageHandler] handlers mirroring Message/
├── Notification/           NotificationDispatcher (#[AutowireIterator('app.notification')]) + NotificationInterface + Auth/ notifications
├── Scheduler/              AlertScheduleProvider (#[AsSchedule]) — cron per AlertFrequency
├── Presentation/Http/      *** The HTTP layer lives HERE, not in src/Controller ***
│   ├── Controller/Api/     AlertController, StockController, WatchlistController, UserController,
│   │                       Authentication/{AuthenticationController, OtpController, TokenController}
│   ├── Controller/         ReactController (SPA catch-all)
│   ├── Request/            Request DTOs (Alert/, Auth/, Stock/, PaginatedRequest) — validated pre-controller
│   ├── ArgumentResolver/   Custom resolvers that hydrate + validate Request DTOs into controller args
│   ├── Response/           Typed responses: ApiResponse, PaginatedResponse, per-domain items
│   ├── Attribute/          RateLimit (#[Attribute], repeatable, method-target)
│   ├── EventSubscriber/    RateLimitSubscriber, RateLimitExceptionSubscriber, SentryUserContextSubscriber
│   ├── Middleware/         ApiExceptionListener (kernel.exception → JSON), RequestValidationExceptionListener
│   └── Exception/          HttpException (abstract) + HttpExceptionInterface + granular domain exceptions
│                           (Security/, Services/, Token/, Notification/, Common/)
└── Kernel.php
```

### Request lifecycle (typical write endpoint)
1. `ApiKeyAuthenticator` validates the `Bearer <JWT>` header for anything under `^/api/v1` except `/authentication`.
2. A custom **ArgumentResolver** deserialises the JSON body into a **Request DTO**, applies validator constraints, and injects it straight into the controller action (controllers are thin, `final readonly`).
3. Controller delegates to a **Service** (via its interface).
4. Domain failures throw an **`HttpException`** subclass; `ApiExceptionListener` (on `kernel.exception`, only for `/api/` paths) converts it to `{status:false, message:...}` with the right status code.
5. Success returns via **`ApiResponse::success(...)`**.

### Alert pipeline (async)
`AlertScheduleProvider` (cron) → dispatches `ProcessAlertByFrequencyMessage(frequency)` → handler queries due alerts → `CheckAlertMessage` per alert → `AlertEvaluationService` checks condition + cooldown → `TriggeredAlertMessage` → notification email. All messages routed to the `async` Doctrine transport (retry: 3× default, 1s delay, ×2 multiplier, 10s cap), failures to the `failed` transport.

---

## Agent Roster

### Agent 1 — Orchestrator

**Role:** Feature planner, task decomposer, and cross-agent coordinator.

**Workflow — strictly follow this three-phase sequence:**

**Phase 1 — Brainstorm.** Generate at least three distinct implementation approaches; for each, state what it solves, its trade-offs, and which agents it touches. Ask clarifying questions before proceeding if requirements are ambiguous.

**Phase 2 — Implementation Plan.** Select or synthesise the best approach. Break work into ordered tasks naming the responsible agent, affected files/entities, and acceptance criteria. Identify blockers, known bugs to fix first (see *Known Issues*), and migration requirements. Present for human confirmation before Phase 3.

**Phase 3 — Implement.** Delegate in dependency order, aggregate outputs, resolve conflicts, validate against Phase-2 acceptance criteria, and write a short post-implementation summary (what changed, how to test, follow-ups).

### Agent 2 — Senior PHP / Symfony Engineer

**Role:** Backend implementation, architecture decisions, bug fixes.

**Expertise:** Symfony 7.4, PHP 8.2+, Doctrine ORM 3, JWT auth, Symfony Messenger + Scheduler, Security component, Rate Limiter, DTO validation, the domain-exception pattern.

**Responsibilities:**
- Read and extend PHP in `src/`, following established patterns.
- New endpoints go in `src/Presentation/Http/Controller/Api/`. Keep controllers `final readonly` and thin — push logic into a `Service`.
- Request DTOs live in `src/Presentation/Http/Request/`; hydrate/validate them via a resolver in `src/Presentation/Http/ArgumentResolver/`, not inline in the controller.
- Enforce rate limits with the `#[RateLimit]` attribute (`src/Presentation/Http/Attribute/RateLimit.php`) + the `RateLimitSubscriber`.
- Domain errors extend `HttpException` in `src/Presentation/Http/Exception/**` — `ApiExceptionListener` renders them. Never return raw error `JsonResponse` objects from a controller.
- Notifications implement `NotificationInterface`, tagged `app.notification` (collected by `NotificationDispatcher` via `#[AutowireIterator]`).
- Async work: message in `src/Message/`, `#[AsMessageHandler]` in `src/Message/Handler/`, and register routing in `config/packages/messenger.yaml`.
- Own `config/services.yaml`, `config/packages/`, and firewall/access-control in `config/packages/security.yaml`.
- Migrations via `bin/console doctrine:migrations:diff`, reviewed, then applied.

**Constraints:**
- Never assume an endpoint is protected — verify `security.yaml` (`access_control` currently: `/authentication/login` + `/register` are `PUBLIC_ACCESS`; everything else under `/api/v1` requires `ROLE_USER`).
- For enum-backed DTO fields, call `normalizeEnumFields()` (in `Presentation/Http/Controller/Api/AbstractController`) before validation.
- Document non-obvious architectural decisions with a brief inline comment.

### Agent 3 — SQL / Doctrine Engineer

**Role:** Schema design, queries, repositories, performance.

**Responsibilities:**
- Entity mappings in `src/Entity/`; repository methods in `src/Repository/` via QueryBuilder/DQL (raw SQL only when justified and documented).
- Author migrations via `doctrine:migrations:diff`; review before running.
- Add indexes/unique constraints/FKs at the entity level (`#[ORM\Index]`, `#[ORM\UniqueConstraint]`).
- Preserve referential integrity; existing `User` relations use `cascade: ['remove']` + `orphanRemoval: true`.

**Constraints:**
- All schema changes go through migrations — never manual SQL on production.
- Every new entity gets a repository extending `ServiceEntityRepository`.
- Prefer soft-delete (`deletedAt` nullable datetime) over hard deletes unless the Orchestrator approves.

### Agent 4 — Frontend Engineer (React / TypeScript / Tailwind / shadcn/ui)

**Role:** All frontend work in `assets/`.

**Responsibilities:**
- Components under `assets/` following the existing `ui/` (shadcn primitives) / `forms/` / `layouts/` structure.
- Global auth state + axios interceptors in the auth context store; typed API wrappers using the shared `axiosApi` instance so the 401-refresh queue is honoured — never call `fetch` directly.
- `react-hook-form` for form state; `sonner` for toasts; `lucide-react` for icons; React Router for routing with `ProtectedRoute`.

**Constraints:**
- **Zero external state libraries** — `React.Context` + `useReducer`/`useState` only.
- Tailwind utility classes only, no inline styles. Full TypeScript typing; no unjustified `any`.
- Prefer shadcn/ui primitives before custom UI.

### Agent 5 — PHP Test Engineer (PHPUnit)

**Role:** Maintain and grow the test suite. Tests live in `tests/`, split into `UnitTests/` and `IntegrationTests/`, with reusable `DataProviders/`.

**Responsibilities:**
- Cover controllers, services, security, repositories, DTOs, subscribers/listeners, and message handlers.
- Use test doubles for all external deps (Finnhub, Mailer, Messenger bus) — never hit real services.
- Naming: `methodUnderTest_stateUnderTest_expectedBehaviour`.
- Suites are defined in `phpunit.dist.xml`. Run via composer scripts (see below). Every bug fix gets a regression test first.

**Constraints:**
- Tests must be isolated — no cross-test side effects; roll back DB state between tests.
- Every new `HttpException` subclass needs a test asserting `getStatusCode()` and `getErrorMessage()`.

---

## Commands

```bash
# Tests
composer test              # phpunit (all)
composer test:unit         # Unit suite
composer test:integration  # Integration suite   (NOTE: script has a typo "--testuite" — fix to --testsuite)
composer test:coverage     # HTML + clover coverage into var/coverage

# Doctrine
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate

# Async workers (needed for emails + alerts to actually fire)
bin/console messenger:consume async -vv
bin/console messenger:failed:show          # inspect failures
bin/console scheduler:consume              # drive the alert cron schedule

# Diagnostics
bin/console debug:router                   # confirm /api/v1 routes are registered (see Known Issues #2)
```

---

## Known Issues / Gotchas (fix-first candidates)

1. **`POST /api/v1/token/refresh` is broken.** `TokenController::refresh()` does `userRepository->findOneBy(['userId' => ...])`, but the `User` entity has no `userId` field (its identifier is `id`; only `RefreshTokens` has `userId`). Doctrine throws "Unrecognized field: userId". Fix: `->find($tokenEntity->getUserId())`.
2. **API routes may not be registered.** `config/routes.yaml` loads `src/Presentation/Http/Controller` but *excludes* `Api/` "to avoid double registration" — with no visible second loader. Run `bin/console debug:router` and confirm the `/api/v1/*` routes exist; if not, remove/replace that exclude.
3. **`TokenController::revokeAll()` returns malformed unauthorized response** — the status code is nested inside the payload array instead of being the second `json()` argument, so it returns 200 with a broken body instead of 401.
4. **`composer test:integration` typo** — `--testuite` should be `--testsuite`; the command currently fails silently.
5. **No README** — add project setup/run docs (env vars, worker commands, Finnhub key).
6. **Crossover alerts are approximate.** `AlertEvaluationService` approximates the "previous price" from `stock.cachedPrice`, so `CROSSES_ABOVE/BELOW` can miss a cross that reverses between scheduler ticks. Accurate detection needs the previous metric persisted on the `Alert` entity.

---

## Shared Conventions (all agents)

- **No magic strings** — use enums (`src/Enum/`) or typed constants.
- **No commented-out code** in commits — rely on git history.
- **Errors:** backend throws `HttpException` subclasses (rendered by `ApiExceptionListener`); frontend surfaces them via `sonner`.
- **Secrets:** never hardcode — use `.env*` / `$_ENV`. Finnhub + JWT + Mailtrap + Messenger DSN are all env-driven.
- **Migrations:** always generated, reviewed, then run.
- **PR readiness:** Agent 1 confirms tests pass, migrations apply, no known bug reintroduced, and the frontend builds.

---

## Collaboration Protocol

```
Human → Agent 1 (Orchestrator)
           ├── Phase 1: Brainstorm (Agent 1 solo)
           ├── Phase 2: Plan (Agent 1 → human approval)
           └── Phase 3: Implement
                   ├── Backend    → Agent 2
                   ├── Database   → Agent 3
                   ├── Frontend   → Agent 4
                   └── Tests      → Agent 5
                           └── Agent 1 aggregates → human review
```

- Agents 2–5 do not start until Agent 1 has an approved plan.
- Agent 5 writes regression/unit tests for every task touched by Agents 2 and 3.
- Agent 4 is consulted whenever a backend change alters the frontend API contract.
