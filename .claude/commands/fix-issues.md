# Command: Fix Issues

## Purpose
Resolve issues identified during review or runtime bugs.

## Trigger
- "fix issues"
- "resolve review feedback"
- "bug fix"

## Execution Flow

1. Agent 1 parses issue list
2. Categorize:
    - Backend → Agent 2
    - DB → Agent 3
    - Frontend → Agent 4
    - Tests → Agent 5

3. Requirements:
    - Every bug must have a regression test
    - Follow rules/testing.md

4. Re-run review command after fixes

## Output

- Fixed issues list
- Added/updated tests
- Remaining risks
