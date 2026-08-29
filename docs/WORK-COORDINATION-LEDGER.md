# WPEssential — Work Coordination Ledger

Status: **Active implementation governance ledger**  
Last reviewed: **2026-08-29**

Current classification: `GREENFIELD_IMPLEMENTATION_WITH_EXISTING_ACCEPTED_PLAN`; execution **`IMPLEMENTATION_GATED`**; lifecycle **`IMPLEMENTING_PLATFORM_FOUNDATION`**; accepted scope **56/56**; source development approval **GOV-OWNER-CONSENT-001 ACTIVE / 56/56 milestone-gated**.

## Planning closure retained

- WP117 / ADR-0212 — final planning closure PASS.
- WP118 / ADR-0213 — Module/Option/UI/System structural-integrity audit PASS after remediation.
- Known planning/semantic-owner gap: **none known**.

## Implementation sequence

### WP119 — Implementation Baseline / Adoption Gate
**DONE / PASS / ADR-0214**.

### WP120 — Machine-enforced Architecture Guards
**DONE / PASS / ADR-0215**.

### WP121 — Milestone 1 Platform Foundation
**CURRENT / IMPLEMENTING through ADR-0220**.

Implemented shared foundation now includes:
- Kernel / Modules / Service Registry;
- Definitions / Context / Policy / Abilities / Events;
- Audit logical foundation;
- backend-neutral JobService logical contracts;
- bounded Action Scheduler capability probe;
- Vault / Assets / Integrations;
- WordPress Capability + Abilities bridge;
- ADR-0216 engineering conventions;
- ADR-0217 atomic compiled registrations;
- ADR-0218 Definition + Audit MySQL persistence;
- **ADR-0219 WordPress.org release metadata + direct-access ABSPATH security contract**;
- **ADR-0220 real WordPress AJAX/nonce/Policy + Ability-backed AJAX integration**.

## ADR-0216 — Engineering contract

Canonical `WPEssential`/`frameworks/` architecture, public hook/filter/global/constant conventions, one typed AJAX front door, centralized nonce operations, compile-on-write registrations, bounded/redacted Runtime Observatory tracing and machine enforcement remain mandatory.

The asymmetric `wpesential/apply_*` spelling is intentional public API.

## ADR-0217 — Atomic compiled registrations

Accepted immutable per-scope generation history, transactional CAS publication, checksum/corruption recovery and historical high-watermark sequencing with no corrupt generation ID reuse.

## ADR-0218 — Definition + Audit persistence

Accepted scoped PT-D Definition/dependency storage, revision CAS, persistent migration state, additive migrations 007/008 and append-oriented Audit PT-D storage with secret-safe metadata and diagnostic content fingerprints.

Audit hashes are not tamper-proof/non-repudiation claims.

## ADR-0219 — WordPress.org + direct-access security

Accepted:
- `https://wpessential.org` Plugin/Author URI;
- Author `VSN Team`;
- clear plugin description;
- repository/plugin/Composer/readme GPL v3 metadata alignment;
- WordPress.org `readme.txt`;
- `CONTRIBUTING.md` release/contribution checklist;
- `ABSPATH` fail-closed guard on every production `frameworks/**/*.php` source file;
- test entrypoint ABSPATH bootstrap;
- permanent engineering validation of these invariants.

One-time transformer run `33265809474` succeeded and generated source commit `a8e758a70fbdc0f3cf58206bc61350b9cb80f66d`. The temporary write-enabled transformer workflow was removed.

Permanent validation run **33265874634 / #138 SUCCESS**.

## ADR-0220 — Real WordPress AJAX / nonce / Policy

Accepted:
- canonical AJAX gateway remains sole WPE owner of `wp_ajax_*`/`wp_ajax_nopriv_*` registration;
- routes remain explicit typed allowlist;
- `AbilityAjaxHandler` routes AJAX mutations through canonical AbilityRegistry/PolicyEngine;
- actual AJAX Ability context is `ExecutionChannel::Ui`;
- Policy denials map to stable 403 `policy_denied` rather than generic handler failure;
- native WordPress nonce API remains operation/scope-bound CSRF layer, not authorization.

Pinned WordPress 7.1 + MySQL 8.4 fixture executes real core APIs.

Initial run #151 failed due incomplete test `wp-config.php` bootstrap. The fixture was corrected; authorization behavior was not weakened.

Corrected source commit:
`fdee1aaffe026745283ce03fb63a14af7a7862ba`

Corrected GitHub Actions run **33266232577 / #153 SUCCESS**.

PASS evidence:
- pinned WordPress 7.1 core bootstrap;
- Composer metadata;
- architecture + engineering validators;
- PHP 8.2 syntax;
- **9/9 smoke suites**;
- compiled-registration MySQL integration;
- Definition/Audit MySQL integration;
- **real WordPress AJAX/nonce/Policy integration**.

## Current non-certifications

Do not overclaim:
- WordPress.org submission/stable release not performed;
- live production DB migration/rollback not performed;
- Multisite AJAX site-switch/network-admin matrix pending;
- Action Scheduler capability-ready does not equal coexistence/Multisite/backend certification;
- durable Job attempt/lease/checkpoint persistence pending;
- Audit UI/retention/privacy/export/legal-hold workflows pending;
- Runtime Observatory admin graph/Policy/retention UI pending;
- 10K/100K compiled-registration performance evidence pending;
- business modules remain downstream of foundation readiness.

## Next WP121 bounded sequence

1. **Action Scheduler coexistence/packaging/backend evidence**;
2. durable Job attempt/lease/checkpoint contracts after backend evidence;
3. minimal Platform admin shell + Runtime Observatory graph/diagnostics UI;
4. executable 10K/100K compiled-registration scale evidence;
5. shared-foundation readiness gate;
6. first business-module tranche after that gate.

## Privileged exclusions

Current source-development approval does not authorize production deployment/release, destructive live-site/customer-data mutation, chargeable/irreversible provider operations, live payment/communication side effects, or destructive production reset/restore/migration/rescue.
