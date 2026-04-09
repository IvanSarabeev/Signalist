# Command: Deploy

## Purpose
Prepare and deploy application safely.

## Trigger
- "deploy"
- "prepare for production"
- "release"

## Execution Flow

1. Preconditions:
    - All tests pass
    - review command = PASS

2. Agent Responsibilities:
    - Agent 2: Verify backend config, env, security
    - Agent 3: Validate migrations
    - Agent 4: Build frontend
    - Agent 5: Ensure test coverage

3. Run:
    - skills/deployment.md

## Output

- Deployment checklist
- Migration confirmation
- Rollback plan
