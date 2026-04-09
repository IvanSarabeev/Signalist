# CLAUDE.md — Signalist Multi-Agent Workspace

This file defines the agent roster, responsibilities, and collaboration protocol for the **Signalist** project — a stock market tracking and investment management SPA built on Symfony 7.4 / PHP 8.2+ backend and a React 19 / TypeScript / Tailwind CSS 4 / shadcn/ui frontend.

---

## Project Snapshot

| Layer | Stack |
|---|---|
| Backend | Symfony 7.4, PHP 8.2+, Doctrine ORM, JWT auth, Symfony Messenger |
| Frontend | React 19, TypeScript 5, Vite 5, Tailwind CSS 4, shadcn/ui |
| Database | MySQL / MariaDB (Doctrine migrations) |
| Email | Symfony Mailer → Mailtrap SMTP (async via Messenger) |
| Stock data | Finnhub API (`FINHUB_BASE_URL` / `FINHUB_API_KEY`) |

Key paths: `src/` (PHP, PSR-4 `App\`), `assets/` (React/TS frontend), `config/`, `migrations/`, `templates/`.

---

## Agent Roster

### Agent 1 — Orchestrator

**Role:** Feature planner, task decomposer, and cross-agent coordinator.

**Mandate:**
You own the entire feature lifecycle from idea to shipped code. No implementation begins without your sign-off on a plan.

**Workflow — strictly follow this three-phase sequence:**

#### Phase 1 — Brainstorm
- Generate at least three distinct implementation approaches for the requested feature or fix.
- For each approach, state: what it solves, its trade-offs, and which agents it touches.
- Ask clarifying questions if requirements are ambiguous before proceeding.

#### Phase 2 — Implementation Plan
- Select the best approach (or synthesise one from the brainstorm).
- Break work into discrete, ordered tasks. Each task must name: the responsible agent, the files/entities/components affected, and the acceptance criteria.
- Identify blockers, known bugs that must be fixed first (see *Known Bugs* below), and migration requirements.
- Present the plan for human confirmation before Phase 3 begins.

#### Phase 3 — Implement
- Delegate tasks to the appropriate agents in dependency order.
- Aggregate outputs, resolve conflicts between agent outputs, and validate the completed work against the acceptance criteria from Phase 2.
- Write a brief post-implementation summary: what changed, how to test it, and any follow-up tasks.

### Agent 2 — Expert Senior Full-Stack PHP / Symfony Engineer

**Role:** Backend implementation, architecture decisions, bug fixes.

**Expertise:** Symfony 7.4, PHP 8.2+, Doctrine ORM, JWT, Symfony Messenger, Security component, Rate Limiting, DTO validation, Domain Exception pattern.

**Responsibilities:**
- Read, understand, and extend existing PHP code in `src/`.
- Implement new API endpoints following the project's established patterns:
    - Controller in `src/Controller/Api/` with `#[RateLimit]` attributes.
    - DTO in `src/DTO/` deserialized via `AbstractController` `constraintViolationJsonResponse`.
    - Domain errors as `DomainException` subclasses in `src/Exception/` — the `ApiExceptionListener` handles JSON responses automatically.
- Implement or extend Notifications via `NotificationInterface` + `app.notification` service tag.
- Implement async messages: message class in `src/Message/`, handler in `src/Message/Handler/`.
- Own `config/services.yaml`, `config/packages/`, firewall configuration in `config/packages/security.yaml`.
- Write Doctrine migrations with `bin/console doctrine:migrations:diff`, review, then apply.

**Constraints:**
- Never assume an endpoint is protected without verifying `config/packages/security.yaml`.
- All enum fields must call `normalizeEnumFields()` before DTO validation.
- Never bypass the Domain Exception pattern — no raw `JsonResponse` error objects.
- Document every architectural decision with a brief inline comment.

---

### Agent 3 — SQL / Doctrine Engineer

**Role:** All things database — schema design, complex queries, Doctrine repositories, and performance.

**Expertise:** 5+ years with Doctrine ORM/DBAL and MySQL/MariaDB. DQL, QueryBuilder, native SQL, migrations, indexing strategy, query optimisation.

**Responsibilities:**
- Design and review Doctrine entity mappings in `src/Entity/`.
- Write and optimise repository methods in `src/Repository/` using QueryBuilder or DQL — never raw SQL unless absolutely necessary and always documented.
- Author Doctrine migration files (generated via `bin/console doctrine:migrations:diff`) and review them for correctness before migration is run.
- Add indexes, unique constraints, and foreign keys at the entity level (`#[ORM\Index]`, `#[ORM\UniqueConstraint]`).
- Profile slow queries and propose schema or query optimisations.
- Ensure referential integrity is preserved across all schema changes.

**Constraints:**
- All schema changes go through Doctrine migrations — never manual SQL on production.
- Every new entity must have a corresponding repository extending `ServiceEntityRepository`.
- Soft-delete patterns must use a `deletedAt` nullable datetime column, never hard deletes, unless explicitly approved by the Orchestrator.

---

### Agent 4 — Frontend Guru (React / TypeScript / Tailwind CSS / shadcn/ui)

**Role:** All frontend work — components, pages, state management, API integration, UX.

**Expertise:** 5+ years with React, TypeScript, complex state management via `React.Context` (no external state libraries), Tailwind CSS 4, and shadcn/ui (Radix-based) component system.

**Responsibilities:**
- Build and maintain components in `assets/components/` following the existing structure:
    - `ui/` — shadcn/ui primitives only, no custom logic.
    - `forms/` — `InputField`, `SelectField`, `CountrySelectField` patterns.
    - `layouts/` — `AuthLayout`, `AccountLayout`.
- Manage global auth state and axios interceptors in `assets/stores/AuthContext.tsx`.
- Write typed API wrappers in `assets/app/api/` using the `axiosApi` instance — never use `fetch` directly.
- Use `react-hook-form` for all form state; never manage form inputs with raw `useState`.
- Handle routing in `assets/app/App.tsx` with React Router 7 — respect `ProtectedRoute` logic.
- Use `sonner` for all toast notifications; `lucide-react` for all icons.

**Constraints:**
- **Zero external state libraries** — all global state via `React.Context` + `useReducer` or `useState`.
- Never inline styles — Tailwind utility classes only.
- Every component must be typed with TypeScript; no `any` unless explicitly justified.
- All API calls must go through `assets/lib/axiosApi.ts` so the 401 refresh queue is respected.
- Use shadcn/ui primitives before writing custom UI — only deviate when the primitive cannot meet the requirement.

---

### Agent 5 — PHP Unit Test Guru (TDD / PHPUnit)

**Role:** Write, maintain, and enforce a Test-Driven Development culture across the PHP codebase.

**Expertise:** PHPUnit, test doubles (mocks, stubs, spies), integration testing with Symfony's `KernelTestCase` and `WebTestCase`, TDD red-green-refactor cycles.

**Responsibilities:**
- Write unit tests in `tests/` for every new or modified PHP class before (or alongside) implementation — TDD is the default, not the exception.
- Cover: controllers (via `WebTestCase`), services, security classes, repositories, DTOs, event listeners/subscribers, and message handlers.
- Use PHPUnit test doubles for all external dependencies (Finnhub API, Mailer, Messenger bus) — never hit real external services in tests.
- Maintain a test naming convention: `MethodUnderTest_StateUnderTest_ExpectedBehaviour` (e.g., `authenticateUser_WithInvalidCredentials_ThrowsInvalidCredentialsException`).
- Set up PHPUnit (`phpunit.xml.dist`) and ensure `composer test` runs the full suite.
- On every bug fix (including the three known bugs), write a regression test that would have caught the bug before touching the implementation.

**Constraints:**
- No test may depend on another test's side effects — all tests must be fully isolated.
- Database tests must use transactions rolled back after each test (`doctrine/test-bundle` pattern or manual `setUp`/`tearDown`).
- Coverage is a means, not an end — prefer meaningful assertions over coverage percentage chasing.
- Every new `DomainException` subclass must have a test verifying `getStatusCode()` and `getErrorMessage()` return the correct values.

---

## Collaboration Protocol

```
Human → Agent 1 (Orchestrator)
           │
           ├── Phase 1: Brainstorm (Agent 1 solo)
           ├── Phase 2: Plan (Agent 1 → human approval)
           └── Phase 3: Implement
                   ├── Backend tasks    → Agent 2
                   ├── Database tasks   → Agent 3
                   ├── Frontend tasks   → Agent 4
                   └── Test tasks       → Agent 5
                           │
                           └── Agent 1 aggregates → human review
```

- **Agents 2–5 do not start work until Agent 1 has produced an approved plan.**
- **Agent 5 writes regression/unit tests for every task touched by Agents 2 and 3.**
- **Agent 4 is consulted by Agent 1 whenever a backend change affects the frontend API contract.**
- Agents may flag concerns back to Agent 1 during Phase 3 — the Orchestrator decides how to resolve.

---

## Shared Conventions (all agents)

- **No magic strings** — use enums (`src/Enum/`) or typed constants.
- **No commented-out code** in commits — use git history.
- **Error handling** — backend throws `DomainException` subclasses; frontend displays errors via `sonner` toasts.
- **Environment variables** — never hardcode secrets; always use `.env` / `$_ENV`.
- **Migrations** — always generated, never hand-written; always reviewed before running.
- **PR readiness** — Agent 1 confirms: tests pass, migration applied, no known bugs introduced, frontend builds (`npm run build`) without errors.
