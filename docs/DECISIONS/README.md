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
7. An ADR may accept stable product/security semantics while leaving exact implementation/profile blocked for executable evidence. Those boundaries must remain explicit.

## ADR set

| ADR | Status | Current decision / recommendation |
|---|---|---|
| `ADR-0001-free-pro-distribution.md` | **Accepted** | WordPress.org Free + separately distributed Pro add-on; trial belongs to Pro entitlement |
| `ADR-0002-compatibility-floor.md` | **Proposed / Phase 0 blocker** | WordPress 6.9 minimum candidate; PHP 8.3 minimum candidate; executable compatibility matrix pending |
| `ADR-0003-abilities-action-contract.md` | **Accepted** | WordPress Abilities as typed reusable action contract |
| `ADR-0004-arbitrary-code-and-sql.md` | **Accepted** | No standard arbitrary PHP eval or unrestricted destructive raw-SQL primitive |
| `ADR-0005-ui-design-system.md` | **Proposed / Phase 0 blocker** | WPE wrappers + stable WordPress Design System/components/DataViews; Untitled UI visual reference/compatible MIT pieces only; UI spike pending |
| `ADR-0006-background-jobs.md` | **Proposed / Phase 0 blocker** | WPE Job Service contract; Action Scheduler preferred adapter candidate; coexistence/load evidence pending |
| `ADR-0007-license-expiry-runtime.md` | **Accepted** | Preserve data and safe deployed runtime; restrict editing/creation/unsafe operations rather than break/expose site |
| `ADR-0008-definition-storage.md` | **Proposed / Phase 0 blocker** | Stable UUID identity + immutable revisions + current/published pointers + dependency edges paper model; physical schema benchmark pending |
| `ADR-0009-secrets-vault.md` | **Proposed / Phase 0 blocker** | Centralized Vault references; external key separation preferred; AEAD/envelope candidate; prototype/security review pending |
| `ADR-0010-free-pro-compatibility.md` | **Proposed / Phase 0 blocker** | Explicit Platform API compatibility range and fail-safe degraded boot; executable mismatch/update-order matrix pending |
| `ADR-0011-ci-test-matrix.md` | **Proposed / Phase 0 blocker** | Layered PR/main/nightly/release matrix paper-specified; executable CI prototype pending |
| `ADR-0012-build-toolchain.md` | **Proposed / Phase 0 blocker** | `@wordpress/build` first candidate, `@wordpress/scripts` fallback/comparison, Vite only for proven unmet need; executable comparison pending |
| `ADR-0013-membership-entitlement-model.md` | **Accepted product architecture** | WordPress Role, Membership Plan/Enrollment, billing Subscription/Purchase and Entitlement are separate domains |
| `ADR-0014-development-consent-gate.md` | **Accepted governance rule** | Production development and executable research spikes require explicit owner consent; `continue` never implies implementation permission |
| `ADR-0015-membership-access-precedence.md` | **Accepted product semantics** | Outer security denial cannot be bypassed; resource/action specificity; same-specificity deny wins; valid entitlements union; explainability mandatory |
| `ADR-0016-membership-enrollment-lifecycle.md` | **Accepted product semantics** | Canonical pending/trialing/active/grace/paused/expired/revoked lifecycle; cancellation is intent; provider payment statuses remain source facts |
| `ADR-0017-product-entitlement-verification.md` | **Accepted architecture / profile pending** | WPE commercial entitlement is signed, site-bound and freshness-aware; outage != expiry; grace is service-signed; product license separate from Membership access |
| `ADR-0018-pro-update-supply-chain.md` | **Accepted security architecture / protocol pending** | Pro updates require signed supply-chain trust with rollback/freeze/key-rotation defenses; Free never acts as external Pro updater; TUF-compatible profile preferred for evaluation |
| `ADR-0019-membership-plan-revisions-changes.md` | **Accepted product semantics** | Draft Plan edits do not change live access; published benefit changes choose follow-current/grandfather/scheduled semantics; billing math remains provider responsibility |
| `ADR-0020-membership-teams-seats-role-sync.md` | **Accepted product semantics** | Team owner/manager/member roles are Membership-domain roles; seat changes are concurrency-sensitive; WordPress role sync is optional/off by default and provenance-safe |
| `ADR-0021-backup-encryption-recovery.md` | **Accepted security architecture / crypto profile pending** | Encrypted backups use per-backup data keys with independent disaster-recovery wrapping; WordPress salts are not the sole recovery root; key loss semantics explicit |

---

# Phase 0 implementation rule

Production implementation does not begin until the relevant Phase 0 technical blockers are accepted/superseded **and** explicit owner development consent exists.

Documentation-only research/planning may continue without development consent. Any spike that writes or executes implementation/runtime/build/migration/test code is development under ADR-0014.

---

# Current product-option planning status

`docs/MODULES/OPTION-COVERAGE-MATURITY.md` records **31/31 module/platform surfaces at Exhaustive product-option maturity**.

This is a product-spec milestone, not an implementation-ready claim.

---

# Supporting detailed planning

Key supporting documents include:
- `docs/RESEARCH/COMPATIBILITY-UI-TOOLCHAIN-STATIC-RESEARCH.md`
- `docs/ARCHITECTURE/DEFINITION-REPOSITORY-CANDIDATE-SCHEMA.md`
- `docs/ARCHITECTURE/FREE-PRO-COMPATIBILITY-STATE-MACHINE.md`
- `docs/ARCHITECTURE/JOB-SERVICE-CONTRACT.md`
- `docs/SECURITY/SECRETS-VAULT-THREAT-MODEL.md`
- `docs/SECURITY/PRODUCT-ENTITLEMENT-SIGNING-OFFLINE-GRACE.md`
- `docs/SECURITY/PRO-UPDATE-SUPPLY-CHAIN-TRUST-MODEL.md`
- `docs/SECURITY/BACKUP-ARCHIVE-ENCRYPTION-KEY-RECOVERY.md`
- `docs/QUALITY/CI-TEST-MATRIX-PLAN.md`
- `docs/MODULES/MEMBERSHIP-ACCESS-POLICY.md`
- `docs/MODULES/MEMBERSHIP-ENROLLMENT-STATE-MACHINE.md`
- `docs/MODULES/MEMBERSHIP-PLAN-VERSIONING-UPGRADE-SEMANTICS.md`
- `docs/MODULES/MEMBERSHIP-TEAMS-SEATS-ROLE-SYNC.md`
- `docs/ARCHITECTURE/MEMBERSHIP-RUNTIME-DATA-CANDIDATE.md`
- `docs/ARCHITECTURE/MEMBERSHIP-PROTECTED-FILE-ARCHITECTURE.md`
- `docs/PLATFORM/REMOTE-SERVICE-API-CONTRACT.md`

---

# Remaining Membership technical blockers

Accepted semantics do **not** mean Membership is implementation-ready.

Still blocked on:
- entitlement/runtime physical schema/index benchmark;
- cache/invalidation implementation and revoke-to-deny proof;
- protected-file delivery across supported environments;
- initial billing adapter implementation/reconciliation certification;
- team/seat concurrency implementation evidence;
- provider migration fidelity fixtures;
- operational retention defaults and exporter/eraser verification.

# Remaining remote-service / Pro updater blockers

Still blocked on:
- exact entitlement signature/canonicalization/library profile;
- key rotation/compromise fixtures;
- exact product-license freshness/offline windows;
- OAuth callback-registration profile;
- exact Pro updater metadata/client/dependency choice;
- release key custody/threshold/incident runbook;
- executable tamper/rollback/freeze/update-order tests.

# Remaining Backup encryption blockers

Still blocked on:
- exact AEAD/container/KDF profile;
- passphrase/recovery-key UX and strength thresholds;
- recovery-key export/rotation procedure;
- archive streaming/chunk format;
- restore certification with original server/DB unavailable;
- optional KMS adapter contracts.
