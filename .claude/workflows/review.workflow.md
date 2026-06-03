# Workflow: code-review process

Runs AFTER implementation, executed by the `code-reviewer` agent.

## Steps
1. Collect the diff: `git diff master...HEAD`.
2. Pull up the feature spec and `skills/php-conventions`.
3. Run the tools:
   ```bash
   docker exec rest_api_php vendor/bin/psalm
   docker exec rest_api_php vendor/bin/phpcs src/
   ```
4. Go through the checklist (spec → correctness → architecture → conventions → simplicity).
5. Write the report to `reviews/` using `templates/review.template.md`.
6. Issue a verdict.

## Severity
- **BLOCKER** — breaks prod / backward compatibility / acceptance criteria. Merge forbidden.
- **MAJOR** — serious defect, fix before merge.
- **MINOR** — worth fixing, not blocking.
- **NIT** — style/taste.

## Specific to versioning
- Verify that **v1 is not broken** (backward compatibility — a separate must-have).
- Verify all 3 resolution strategies and the correct priority (see `skills/api-versioning`).
- Verify presence of deprecation/sunset headers and the migration guide.

## Alternative/supplement
For a quick automated pass you can additionally invoke the built-in `/code-review`
(local diff) — it hunts for bug/efficiency issues. This is a **supplement**, not a replacement
for substantive review against the spec.
