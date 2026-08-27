# ADR-0014 — Explicit Owner Consent Before Development

Status: **Accepted**  
Date: 2026-08-27

## Context

WPEssential is intentionally completing product, architecture, module-option, security and engineering planning before implementation. The project owner explicitly requires that production development must not begin automatically after planning and that the AI/engineering system must obtain explicit consent first.

Without a durable rule, a future AI/engineer could incorrectly interpret phrases such as `continue`, acceptance of a planning document, or completion of Phase 0 as permission to scaffold or implement code.

## Decision

Production development requires explicit owner consent in the active request/conversation before the first implementation action.

The detailed operational boundary is defined in `/DEVELOPMENT-CONSENT.md` and is part of the engineering source of truth.

### Consent is not implied by

- `continue`;
- `proceed`;
- approval of research or documentation;
- acceptance of an ADR;
- Phase 0 readiness;
- a PR becoming mergeable;
- an engineer/AI concluding implementation is the logical next step.

### Explicit consent

Consent must clearly authorize implementation, for example an instruction equivalent to:

- `development start karo`;
- `implementation start karo`;
- `code development shuru karo`.

### Pre-consent work

Research, specifications, architecture, threat models, ADRs, acceptance criteria, test plans, benchmark plans, UX flows and documentation-only changes may continue.

Executable research spikes are considered development and need explicit authorization before code is written or run.

## Consequences

### Positive

- Owner retains deliberate control over the transition from planning to code.
- Future AI sessions cannot infer development permission from context.
- Architecture can be reviewed without accidental implementation drift.
- Research spikes cannot quietly become production architecture.

### Cost

- Even when Phase 0 is technically ready, engineering must stop at the gate until consent is received.
- Some evidence-based ADRs may remain Proposed if they require executable spikes and the owner has not authorized those spikes.

## Enforcement

Before any implementation action, every session must verify:

1. current `DEVELOPMENT-CONSENT.md`;
2. latest `CHECKPOINT.md`;
3. relevant ADRs/specifications;
4. explicit owner consent exists for the intended implementation scope.

If consent is absent, remain in planning/documentation mode.

## Reversal

This ADR must not be weakened, superseded or removed without explicit owner instruction.
