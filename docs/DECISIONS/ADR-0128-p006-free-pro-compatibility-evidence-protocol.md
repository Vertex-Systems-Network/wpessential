# ADR-0128 — P-006 Free ↔ Pro Compatibility / Boot Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP11`

## Context

ADR-0010 defines the proposed Free↔Pro compatibility protocol: Free owns the platform/kernel, Pro registers premium modules only after a lightweight compatibility preflight, Platform API versioning is separate from plugin marketing versioning, and incompatible pairs must degrade safely before premium module or migration bootstrap.

Additional accepted architecture makes the boundary stricter:

- ADR-0007: expiry preserves local data/configuration and safe deployed runtime where technically possible;
- product entitlement is separate from package compatibility and Membership authorization;
- signed entitlement is separate from ordinary authenticated licensing API JSON;
- Product License remote resources/allocation/clone/transfer state are separate from local package compatibility;
- Pro updates/trust and OAuth account linking have their own protocols and cannot be inferred from P-006.

The existing generic P-006 spike listed useful pairings but did not fix enough adversarial evidence around pre-autoload fatals, update interruption, schema generations, entitlement/outage separation, Multisite allocation, clone/restore and rollback.

## Decision

Accept `docs/QUALITY/P006-FREE-PRO-COMPATIBILITY-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the fixed executable evidence contract for P-006.

It defines **FP-01…FP-144** covering:

- artifact identity, plugin/Platform API/schema metadata and distribution boundaries;
- Free-only, compatible, absent, older/newer and unsupported mismatch boot combinations;
- compatibility preflight before premium autoload/service/module/migration execution;
- plugin load-order, activation, cache and request-context safety;
- Free-first/Pro-first update windows, partial/interrupted replacement and rollback;
- Platform API range parsing, optional/required capability negotiation and deprecation windows;
- schema ahead/behind, migration ordering, crash/retry/concurrency and restore semantics;
- binary compatibility vs signed entitlement vs remote service/allocation vs Membership truth separation;
- expiry, revocation, offline cache and service-outage behavior;
- Multisite network/site activation and commercial allocation boundaries;
- clone/staging/domain migration/network transfer/backup-restore reconciliation;
- privacy, redaction, diagnostics, XSS/fuzz resistance, performance and final adversarial review.

## Preserved invariants

1. Free remains the platform/kernel and Pro remains a separately distributed add-on.
2. Platform API version is not the plugin marketing version.
3. Pro must not load incompatible premium runtime or start Pro migrations before compatibility is proven.
4. Known mismatch states should degrade safely rather than intentionally fatal.
5. License/entitlement state never substitutes for binary or schema compatibility.
6. Binary compatibility never manufactures product entitlement.
7. Product licensing never becomes Membership authorization.
8. Service unavailability is not expiry.
9. Expiry/revocation does not automatically delete local user/site data.
10. Restore/clone/update/rollback ambiguity is reconciled rather than guessed.
11. A remote Product License response does not substitute for signed entitlement verification.
12. A P-006 pass cannot certify OAuth, TUF/updater, Vault, CI, build, compatibility floor, Membership or provider adapters.

## Evidence state

- FP fixtures documented: **144**
- FP fixtures executed: **0/144**
- P-006 runtime certifications: **0**
- certified Free↔Pro artifact pairs: **0**
- Product License remote service executions under P-006: **0**
- migrations executed under P-006: **0**

ADR-0010 remains a **Proposed Phase-0 runtime blocker** until authorized execution provides sufficient evidence to accept the concrete runtime profile.

## Stop-the-line examples

P-006 cannot certify if any supported/known mismatch path causes an avoidable fatal; incompatible Pro runtime/migration executes before preflight; a local value/API response forges entitlement; service outage becomes immediate expiry; expiry deletes local state; product licensing bypasses Membership; clone/restore silently creates unauthorized production allocation; unsupported newer schema is written by old code; or secrets/tokens leak through diagnostics.

## Development gate

This ADR authorizes no source code, package/bootstrap implementation, plugin activation, migration, database mutation, licensing service call, entitlement signing/verification, update, rollback, restore, build, CI workflow or runtime test.

ADR-0014 explicit scoped owner consent remains required for every executable P-006 action.