# WPEssential Architecture Decision Records

Architecture Decision Records (ADRs) preserve decisions that materially affect long-lived architecture, security, compatibility, licensing, data ownership, dependencies, runtime behavior or distribution.

## Status meanings

- **Proposed** — researched recommendation; implementation must not treat it as final yet.
- **Accepted** — source-of-truth decision for new work.
- **Superseded** — replaced by a later ADR; historical reasoning remains valuable.
- **Rejected** — considered but intentionally not adopted.

## Rules

1. Accepted ADRs are not silently changed.
2. To reverse an accepted ADR, create a new ADR that supersedes it and explains migration/impact.
3. An ADR records **why**, alternatives, consequences, migration and review triggers—not only the chosen technology.
4. If external facts may change, include a review trigger/date rather than assuming permanence.
5. A proposed ADR blocking Phase 0 must be resolved before production implementation begins.

## Initial ADR set

- `ADR-0001-free-pro-distribution.md` — **Accepted**
- `ADR-0002-compatibility-floor.md` — **Proposed / Phase 0 blocker**
- `ADR-0003-abilities-action-contract.md` — **Accepted**
- `ADR-0004-arbitrary-code-and-sql.md` — **Accepted**
- `ADR-0005-ui-design-system.md` — **Proposed / Phase 0 blocker**
- `ADR-0006-background-jobs.md` — **Proposed / Phase 0 blocker**
- `ADR-0007-license-expiry-runtime.md` — **Accepted product architecture**

Further Phase 0 ADRs are required for definition storage, secrets/key strategy, Free↔Pro compatibility protocol and CI/test matrix before source implementation.
