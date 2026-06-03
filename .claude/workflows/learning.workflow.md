# Workflow: learning process

Runs AFTER review, executed by `learning-mentor`. Implements the Learning Mode from `CLAUDE.md`.

## Principle
Learning is built into the process, not "someday later". Every feature leaves a trace in `learning/`.

## Before implementation (already in the Plan/Explain phase)
The architect/you talk through with the user:
- what the problem is;
- which approaches are possible;
- why this one was chosen (trade-offs).

## After implementation
`learning-mentor` creates `learning/<feature>.md`:
1. The problem in plain words.
2. Execution flow after the change.
3. Design decisions + rejected alternatives (link to the ADR).
4. Drawbacks / technical debt.
5. Patterns to remember.
6. Self-check questions (without answers).

## How to use the notes
- Re-read before a similar task.
- Try to answer the self-check questions "from memory".
- Come back after a week and check whether the decision still looks right.

## Anti-pattern
Generating a note "for show" and never reading it. A note without reflection = useless.
