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
7. An ADR may accept stable product/security semantics while leaving the exact implementation/profile blocked for executable evidence. Those boundaries must be explicit.

## ADR set

| ADR | Status | Current decision / recommendation |
|---|---|---|
| `ADR-0001-free-pro-distribution.md` | **Accepted** | WordPress.org Free + separately distributed Pro add-on; trial belongs to Pro entitlement |
| `ADR-0002-compatibility-floor.md` | **Proposed / Phase 0 blocker** | WordPress 6.9 minimum candidate; PHP **8.3** minimum candidate after current static lifecycle/platform research; executable matrix pending |
| `ADR-0003-abilities-action-contract.md` | **Accepted** | WordPress Abilities as typed action contract for reusable operations |
| `ADR-0004-arbitrary-code-and-sql.md` | **Accepted** | No standard arbitrary PHP eval or unrestricted destructive raw-SQL product primitive |
| `ADR-0005-ui-design-system.md` | **Proposed / Phase 0 blocker** | WPEssential wrappers + WordPress 7.1 public Design System/components/DataViews; Untitled UI as visual reference and compatibility-reviewed MIT source only; UI spike pending |
| `ADR-0006-background-jobs.md` | **Proposed / Phase 0 blocker** | WPEssential Job Service contract; Action Scheduler preferred adapter candidate; coexistence/load evidence pending |
| `ADR-0007-license-expiry-runtime.md` | **Accepted** | preserve data and safe deployed runtime; lock editing/creation/unsafe operations rather than break/expose site |
| `ADR-0008-definition-storage.md` | **Proposed / Phase 0 blocker** | stable UUID identity + immutable revisions + current/published pointers + dependency edges paper model; physical schema benchmark pending |
| `ADR-0009-secrets-vault.md` | **Proposed / Phase 0 blocker** | centralized Vault references; external key separation preferred; Sodium AEAD/envelope candidate; prototype/security review pending |
| `ADR-0010-free-pro-compatibility.md` | **Proposed / Phase 0 blocker** | explicit Platform API compatibility range and fail-safe degraded boot state machine; executable mismatch/update-order matrix pending |
| `ADR-0011-ci-test-matrix.md` | **Proposed / Phase 0 blocker** | layered PR/main/nightly/release matrix now paper-specified; executable CI prototype pending |
| `ADR-0012-build-toolchain.md` | **Proposed / Phase 0 blocker** | `@wordpress/build` first candidate, `@wordpress/scripts` comparison/fallback, Vite only for proven unmet need; executable comparison pending |
| `ADR-0013-membership-entitlement-model.md` | **Accepted product architecture** | WordPress Role, Membership Plan/Enrollment, billing Subscription and Entitlement are separate domains; billing providers are adapters, not access source of truth |
| `ADR-0014-development-consent-gate.md` | **Accepted governance rule** | production development and executable research spikes require explicit owner consent; `continue`/planning approval never implies implementation permission |
| `ADR-0015-membership-access-precedence.md` | **Accepted product semantics** | outer WordPress/security denial cannot be bypassed; resource+action specificity; same-specificity deny wins; valid Membership entitlements union; explainability mandatory |
| `ADR-0016-membership-enrollment-lifecycle.md` | **Accepted product semantics** | canonical pending/trialing/active/grace/paused/expired/revoked lifecycle; cancellation is intent; provider payment statuses remain source facts |
| `ADR-0017-product-entitlement-verification.md` | **Accepted architecture / profile pending** | WPE commercial entitlement is signed, site-bound and freshness-aware; outage != expiry; grace is service-signed; product licensing is separate from Membership access |
| `ADR-0018-pro-update-supply-chain.md` | **Accepted security architecture / protocol pending** | Pro updates require signed supply-chain trust with rollback/freeze/key-rotation defenses; Free never acts as external Pro updater; TUF-compatible profile preferred for evaluation |

## Phase 0 rule

Production feature implementation does not begin until the Phase 0 blockers relevant to the platform skeleton are accepted or explicitly superseded **and** explicit owner development consent has been obtained.

Documentation-only research may continue without development consent. Any research spike that writes or executes implementation code is development under ADR-0014 and requires separate authorization.

## Supporting detailed planning

Current detailed evidence/contracts include:

- `docs/RESEARCH/COMPATIBILITY-UI-TOOLCHAIN-STATIC-RESEARCH.md`
- `docs/ARCHITECTURE/DEFINITION-REPOSITORY-CANDIDATE-SCHEMA.md`
- `docs/ARCHITECTURE/FREE-PRO-COMPATIBILITY-STATE-MACHINE.md`
- `docs/ARCHITECTURE/JOB-SERVICE-CONTRACT.md`
- `docs/SECURITY/SECRETS-VAULT-THREAT-MODEL.md`
- `docs/SECURITY/PRODUCT-ENTITLEMENT-SIGNING-OFFLINE-GRACE.md`
- `docs/SECURITY/PRO-UPDATE-SUPPLY-CHAIN-TRUST-MODEL.md`
- `docs/QUALITY/CI-TEST-MATRIX-PLAN.md`
- `docs/MODULES/MEMBERSHIP-ACCESS-POLICY.md`
- `docs/MODULES/MEMBERSHIP-ENROLLMENT-STATE-MACHINE.md`
- `docs/MODULES/MEMBERSHIP-SEMANTIC-STATUS.md`
- `docs/ARCHITECTURE/MEMBERSHIP-RUNTIME-DATA-CANDIDATE.md`
- `docs/PLATFORM/REMOTE-SERVICE-API-CONTRACT.md`

## Membership remaining implementation blockers

Accepted semantics do **not** mean Membership is implementation-ready.

Still blocked on:
- entitlement physical schema/index benchmark;
- cache/invalidation implementation and revocation latency proof;
- protected-file delivery environments;
- Plan benefit/version pinning/follow-current details;
- initial billing adapter implementations and reconciliation;
- team/seat concurrency;
- role-sync conflict semantics;
- provider migration fidelity;
- privacy/retention operational defaults.

## Remote service / Pro updater remaining blockers

Accepted architecture does **not** select a cryptographic/updater library yet.

Still blocked on:
- exact product-entitlement signature profile/canonicalizer/library;
- signing/public-key rotation fixtures;
- exact offline-grace/token lifetimes;
- OAuth callback-registration profile;
- Pro updater TUF/client/dependency choice;
- release key custody/threshold/incident runbook;
- executable tamper/rollback/freeze/update-order tests.
