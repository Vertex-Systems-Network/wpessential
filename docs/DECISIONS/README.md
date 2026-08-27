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
6. Even after Phase 0 blockers are resolved, production development cannot begin without the explicit owner consent required by ADR-0014 and `/DEVELOPMENT-CONSENT.md`.

## ADR set

| ADR | Status | Decision |
|---|---|---|
| `ADR-0001-free-pro-distribution.md` | **Accepted** | WordPress.org Free + separately distributed Pro add-on; trial belongs to Pro entitlement |
| `ADR-0002-compatibility-floor.md` | **Proposed / Phase 0 blocker** | WP 6.9 minimum recommended; PHP 8.2 development/beta proposal with 8.3 launch review |
| `ADR-0003-abilities-action-contract.md` | **Accepted** | WordPress Abilities as typed action contract for reusable operations |
| `ADR-0004-arbitrary-code-and-sql.md` | **Accepted** | No standard arbitrary PHP eval or destructive raw-SQL product primitive |
| `ADR-0005-ui-design-system.md` | **Proposed / Phase 0 blocker** | React/TypeScript hybrid UI behind WPEssential wrappers; MIT Untitled UI + Lucide + stable WP packages |
| `ADR-0006-background-jobs.md` | **Proposed / Phase 0 blocker** | JobService abstraction; Action Scheduler preferred candidate after acceptance tests |
| `ADR-0007-license-expiry-runtime.md` | **Accepted** | preserve data and safe deployed runtime; lock editing/creation/unsafe operations rather than break site |
| `ADR-0008-definition-storage.md` | **Proposed / Phase 0 blocker** | versioned shared Definition Repository for configuration, not universal EAV runtime data |
| `ADR-0009-secrets-vault.md` | **Proposed / Phase 0 blocker** | centralized Vault references; exact encryption/key/recovery design pending |
| `ADR-0010-free-pro-compatibility.md` | **Proposed / Phase 0 blocker** | explicit Platform API compatibility protocol and safe degraded boot |
| `ADR-0011-ci-test-matrix.md` | **Proposed / Phase 0 blocker** | layered PR/main/nightly/provider CI matrix |
| `ADR-0012-build-toolchain.md` | **Proposed / Phase 0 blocker** | Composer + React/TS; Vite preferred pending WordPress externals/tooling spike |
| `ADR-0013-membership-entitlement-model.md` | **Accepted product architecture** | WordPress Role, Membership Plan/Enrollment, billing Subscription and Entitlement are separate domains; billing providers are adapters, not membership source of truth |
| `ADR-0014-development-consent-gate.md` | **Accepted governance rule** | production development and executable research spikes require explicit owner consent; `continue`/planning approval never implies implementation permission |

## Phase 0 rule

Production feature implementation does not begin until the Phase 0 blockers relevant to the platform skeleton are accepted or explicitly superseded **and** explicit owner development consent has been obtained.

Documentation-only research may continue without development consent. Any research spike that writes or executes implementation code is development under ADR-0014 and requires separate authorization.

Membership implementation additionally remains blocked on the follow-up technical decisions named by ADR-0013: entitlement schema/cache, access-rule precedence, runtime storage/indexes, protected-file delivery, initial billing adapters, privacy/retention and role-sync conflict semantics.
