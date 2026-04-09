# Command: Review

## Purpose
Perform a full codebase or feature review before merge or deployment.

## Trigger
- "review this feature"
- "run review"
- "pre-PR review"

## Execution Flow

1. Agent 1 (Orchestrator) coordinates:
    - Agent 2 → Backend review
    - Agent 3 → DB review
    - Agent 4 → Frontend review
    - Agent 5 → Test coverage review

2. Apply rules:
    - rules/code-style.md
    - rules/api-conventions.md
    - rules/testing.md

3. Run skills:
    - skills/security-review.md

## Output Format

- Summary (PASS / FAIL / WARN)
- Issues grouped by:
    - Critical
    - Major
    - Minor
- Suggested fixes
- Affected files
