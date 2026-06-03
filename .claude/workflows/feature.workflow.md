# Workflow: feature from idea to knowledge

Mirrors the loop in `CLAUDE.md`. Every feature goes through 6 phases.

```
Analyze ─► Plan ─► Explain ─► Implement ─► Review ─► Learn
   │         │        │           │           │        │
backend-  backend-  (you with   main        code-    learning-
architect architect the user)   agent       reviewer mentor
```

## Phases

| # | Phase | Who | Artifact |
|---|-------|-----|----------|
| 1 | **Analyze** | `backend-architect` | `specs/<f>.spec.md` |
| 2 | **Plan** | `backend-architect` | `tasks/<f>.tasks.md` + `decisions/NNNN-*.md` |
| 3 | **Explain** | you + the user | verbal approval of the plan (settle the "Open questions") |
| 4 | **Implement** | main agent | code in `src/`, routes, config |
| 5 | **Review** | `code-reviewer` | `reviews/<f>-<date>.md` |
| 6 | **Learn** | `learning-mentor` | `learning/<f>.md` |

## Transition rules
- Do **not** move from phase 2 to 4 without plan approval (phase 3) — this is the essence of learning-mode.
- If Review = *Request changes* → back to Implement, then re-review.
- Learn is mandatory, never skip it: without it the learning value is lost.

## Start command (example)
> "Run backend-architect for the task in `specs/api-versioning.spec.md`".
