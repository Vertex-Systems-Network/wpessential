# WPEssential — WP121 Platform Foundation

Status: **DONE / PASS — shared foundation ready for module handoff**  
Date: **2026-08-30**  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE  
Predecessors: WP119 PASS / ADR-0214; WP120 PASS / ADR-0215  
Accepted implementation decisions: **ADR-0216 through ADR-0222**

## Goal

Establish the reusable production-source Platform foundation required before business modules can safely exist. WP121 owns kernel/security/persistence/jobs/integrations/WordPress bridges/release-security conventions/observability primitives; it is not itself a business-feature milestone.

## Accepted architecture tranches

- **ADR-0216 — Engineering contract:** canonical namespace/root, public hooks/filters/globals/constants, one typed AJAX gateway, centralized nonce operations, compile-on-write registrations, bounded observability, `ABSPATH` security and machine enforcement.
- **ADR-0217 — Atomic compiled registrations:** site/network scope, immutable generations, transactional CAS publication, deterministic integrity checks, corruption quarantine, last-known-good recovery and high-watermark sequencing.
- **ADR-0218 — Definition + Audit persistence:** Definition/dependency persistence, revision CAS, migration ledger, non-destructive migrations and append-oriented Audit storage.
- **ADR-0219 — WordPress.org/release/direct-access contract:** public metadata/readme/contribution/GPL alignment and direct-access guards.
- **ADR-0220 — Real WordPress AJAX/nonce/Policy:** typed routes, centralized nonces, UI-channel Ability execution and stable Policy denial mapping.
- **ADR-0221 — Action Scheduler backend/coexistence:** exact WPE hook/group/Job UUID ownership using public Action Scheduler APIs while leaving third-party actions untouched.
- **ADR-0222 — Durable Job persistence/leases/checkpoints:** explicit network/site scope, stable-key digest idempotency, revision CAS, persisted attempts, hash-only worker lease tokens, heartbeat, checkpoints and stale-worker rejection.

## Additional accepted foundation

- minimal wp-admin Platform shell + server-rendered Runtime Observatory;
- progressive TypeScript admin enhancement and deterministic `@wordpress/scripts` artifacts;
- canonical locked Composer/PHP quality graph and Node 24/npm admin graph;
- dedicated PHPUnit suite;
- 10K/100K compiled-registration scale evidence;
- 10-combination WordPress/PHP/MySQL/MariaDB compatibility baseline;
- deterministic runtime-only plugin ZIP + GPL/package validation;
- packaged Chromium Playwright/Axe Runtime Observatory baseline;
- real WordPress two-site Multisite AJAX/job/Action Scheduler isolation.

## Bounded readiness closures

### WP121.1 — deterministic package/license gate — PASS

Source `019f496e10e04455cd939c75383fc41661dd26f7` passed Distributable Package #3, Architecture Guards #303 and Compatibility #46. Verified package SHA-256 `a61257866088f5bde5a421cef27f9cf8302062eb74eac7a2ee17171415cbe929`, 156 files, 137,667 bytes, single `wpessential/` root and 0 runtime Composer packages.

### WP121.2 — browser E2E/accessibility baseline — PASS

Source `9e1039a697db44b6102377eafdf667afdfc79817` passed Browser E2E #11, Architecture #317, Package #17 and Compatibility #60. 2/2 Playwright tests passed; WPE-owned Axe scope reported 0 violations / 15 passes. Artifact `9731346638`, digest `sha256:65ec1d2e7ea41e3e4a6f0165a94d6e5a2aa1dcc09b1558c291b6fac2a247b748`.

### WP121.3 — Multisite AJAX & queue-worker isolation — PASS

Exact implementation source `49abadec09676780680e705ae14f9f092609b348` passed Multisite #4, Architecture #321, Package #21, Browser #14 and Compatibility #64.

Real WordPress 7.1 / PHP 8.2.33 / MySQL 8.4.11 two-site evidence proves cross-site nonce replay rejection, active AJAX site/network context, durable Job/idempotency/read/mutation/lease/checkpoint isolation, network-table naming and Action Scheduler 4.1.0 site-store isolation. Artifact `9731964919`, digest `sha256:7da43e78c5248cbcf3219b4eef24e0abc6aba312d639ce26e026e433796a4a7b`.

### WP121.4 — aggregate shared-foundation readiness — PASS

The executable `WP121 Shared Foundation Readiness` workflow and `tools/quality/wp121-readiness.json` fail closed unless all required canonical hosted runs belong to the certified source SHA, expected workflow IDs, PR #2 and successful completed conclusions.

Canonical aggregate run `33311289489 / #2` on readiness head `5007de9c84b2b154743b6e50f76cc73e65e6019b` passed. Evidence artifact `9732050946`, digest `sha256:3cc9cffbb159d90d2fbda4274223ffb0ec708dfd56a2707eb59bf418410cf547`.

The gate also proves no implementation/test/tooling drift after certified source `49abadec09676780680e705ae14f9f092609b348`; only the readiness workflow/manifest and canonical readiness/checkpoint documentation are allowlisted. Any other drift requires recertification.

## Readiness result

`PASS / MODULE_HANDOFF_AUTHORIZED_FOR_SOURCE_DEVELOPMENT`

WP121 now satisfies the shared-foundation prerequisites for the first business-module tranche under `GOV-OWNER-CONSENT-001`.

This does **not** authorize merge/release/deployment or live/privileged operations.

## Staged later capability/release work

These remain planned but are not automatic blockers for first module source implementation:
- WordPress.org stable submission/release;
- live production DB migration/rollback;
- final Action Scheduler distribution/vendoring mechanism;
- automatic Action Scheduler dispatch → Ability → durable-attempt lifecycle wiring;
- high-concurrency fairness/resource admission/backpressure evidence;
- Job checkpoint privacy/retention implementation;
- Audit viewer/retention/privacy/export/legal-hold product surfaces;
- browser/accessibility evidence for future critical interactive WPE admin workflows;
- future Free/Pro package separation and release-specific boundary checks.

No production deployment, live provider call, destructive live-site/customer-data mutation, live production DB migration, release or irreversible external operation was performed.

## Next work

Resolve the first business-module tranche from canonical repository planning/dependency order, create/update its Linear work package, then implement it without bypassing the accepted shared Platform contracts.

Repository evidence overrides conversational memory.
