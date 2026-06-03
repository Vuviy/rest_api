# .claude — learning & development environment

This is the Claude Code working environment for the **framework-free PHP REST API** project.
It is designed to serve two goals at once:

1. **Ship the task** (current: *API Versioning System*, see `specs/api-versioning.spec.md`).
2. **Teach** backend engineering, architecture and problem-solving (Learning Mode from `CLAUDE.md`).

## Directory map

| Directory | Purpose | Who writes there |
|---|---|---|
| `agents/` | Specialized sub-agents (architect, reviewer, mentor) | engineer / Claude |
| `skills/` | Reusable knowledge & rules (code conventions, versioning theory) | engineer |
| `templates/` | Document templates (ADR, spec, review, learning note, task) | engineer |
| `workflows/` | Process descriptions: feature, review, learning | engineer |
| `specs/` | Feature specifications (requirements, criteria, edge-cases) — **not code** | architect |
| `tasks/` | Breakdown of a spec into steps | architect |
| `decisions/` | ADRs — recorded architectural decisions | architect |
| `architecture/` | Diagrams/notes about the current architecture | engineer |
| `reviews/` | Code-review reports per iteration | reviewer |
| `learning/` | Learning notes after each feature | mentor |

## Main loop (from `CLAUDE.md`)

`Analyze → Plan → Explain → Implement → Review → Learning notes`

Full description — in `workflows/feature.workflow.md`.

> Documentation and explanations are in English. Keep technical terms as-is.
