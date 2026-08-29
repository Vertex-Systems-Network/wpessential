# WPEssential — WP121 Platform Foundation

Status: **CURRENT / IMPLEMENTING — shared platform foundation active**  
Date: **2026-08-29**  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE  
Predecessors: WP119 PASS / ADR-0214; WP120 PASS / ADR-0215  
Accepted implementation decisions: **ADR-0216 through ADR-0220**

## Goal

Establish the shared production-source platform foundation required before business modules can safely exist. WP121 owns reusable kernel, security, persistence, jobs, integrations, WordPress bridges, release/security conventions and observability primitives; it is not a business-feature milestone.

## Tranches 1–8 — Shared foundation + engineering contract

Implemented and retained:
- Bootstrap / Kernel / Service Registry / Module lifecycle;
- immutable Definition / ExecutionContext / Policy / Ability / Event core;
- Audit logical foundation + secret-safe metadata sanitizer;
- backend-neutral JobService logical contracts;
- bounded Action Scheduler capability probe;
- Secrets Vault reference contract, Asset Registry and Integration Registry;
- persistence/migration abstractions;
- WordPress Capability + Abilities API bridge;
- ADR-0216 engineering contract.

ADR-0216 requires `WPEssential`, canonical `frameworks/`, `wpessential_*`, `WPE_*`, exact `wpesential/apply_*` filters, `wpessential/hook_*` actions, one typed AJAX gateway, centralized nonce operations, compile-on-write registrations, bounded/redacted observability and machine enforcement.

## Tranche 9 — Atomic compiled registrations / ADR-0217

Implemented:
- site/network scope;
- immutable generation history;
- separate active/fallback pointers;
- InnoDB transaction + CAS publication;
- deterministic checksum verification;
- corruption quarantine + last-known-good recovery;
- historical high-watermark `MAX(generation)+1` independent of pointer rollback;
- no reuse of corrupt/quarantined IDs;
- active compiled projection consumed at runtime rather than historical Definition scans.

## Tranche 10 — Definition + Audit persistence / ADR-0218

Implemented:
- explicit `DefinitionScope` network/site identity;
- PT-D Definition + dependency tables;
- canonical `PersistentDefinitionRepository` backed by `WpdbDefinitionTableGateway`;
- transactional Definition/dependency writes + stale revision CAS rejection;
- persistent `${base_prefix}wpe_migrations` ledger;
- non-destructive migrations 007/008;
- append-oriented PT-D Audit event table with scope/time/action/actor/resource/outcome/correlation/retention indexes;
- secret-safe metadata persistence;
- deterministic local Audit `content_hash` diagnostic fingerprint.

Audit hashes do not make the database tamper-proof and are not a non-repudiation claim.

Hosted run `33263291359` / #123 proved compiled-registration MySQL plus Definition/Audit MySQL integration on MySQL 8.4.

## Tranche 11 — WordPress.org release metadata + direct-access security / ADR-0219

Implemented:
- main plugin URI/author identity: `https://wpessential.org` / `VSN Team`;
- clear platform header description;
- GPL metadata aligned to repository GPL v3;
- WordPress.org-format `readme.txt`;
- `CONTRIBUTING.md` with engineering and directory-release checklist;
- direct-access `ABSPATH` guard across all production `frameworks/**/*.php` source;
- test/integration entrypoint `ABSPATH` bootstrap;
- permanent engineering guard for plugin metadata, release docs and every shipped-source ABSPATH invariant.

The one-time transformer run `33265809474` succeeded and produced `a8e758a70fbdc0f3cf58206bc61350b9cb80f66d`; its temporary write-enabled workflow was removed afterward.

Permanent hosted validation run `33265874634` / #138 passed all existing gates.

ADR-0219 is release preparedness, not WordPress.org publication/approval.

## Tranche 12 — Real WordPress AJAX / nonce / Policy / ADR-0220

Implemented:
- `AbilityAjaxHandler` bridging AJAX operations to canonical `AbilityRegistry` / `PolicyEngine`;
- trusted current WordPress context rebound to `ExecutionChannel::Ui` for AJAX Ability execution;
- typed `AjaxAuthorizationException` and stable 403 `policy_denied` mapping;
- canonical gateway remains sole WPE `wp_ajax_*` / `wp_ajax_nopriv_*` owner;
- route registry remains explicit typed allowlist;
- centralized `NonceManager` remains operation/scope-bound CSRF owner.

### Real WordPress fixture

CI downloads pinned WordPress 7.1 core and boots it against the ephemeral MySQL 8.4 service.

Executable evidence verifies:
- actual WordPress `add_action` registration for authenticated + nopriv canonical AJAX hooks;
- only the registered route type resolves;
- unknown type fails closed;
- missing nonce fails before handler execution;
- real `wp_create_nonce` / `wp_verify_nonce` operation/scope binding;
- actual administrator current user/site context passes `manage_options` Policy;
- Ability executes on `ui` channel;
- actual low-privilege subscriber with a valid nonce is denied by canonical Policy/capability;
- guest is rejected before nonce/handler execution.

Initial run `33266156181` / #151 failed only because the generated fixture `wp-config.php` omitted `wp-settings.php`. The fixture bootstrap was corrected without weakening authorization.

Corrected source commit:
`fdee1aaffe026745283ce03fb63a14af7a7862ba`

Corrected hosted run **33266232577 / #153 SUCCESS**.

Job-level PASS:
- WordPress 7.1 fixture bootstrap;
- Composer metadata;
- architecture validator;
- engineering validator including ADR-0219 security/release invariants;
- PHP 8.2 syntax;
- **9/9 smoke suites**;
- compiled-registration MySQL integration;
- Definition/Audit MySQL integration;
- **real WordPress AJAX/nonce/Policy integration**.

## Current exclusions / not yet certified

- WordPress.org submission or stable directory release;
- live production WordPress DB migration/rollback;
- complete production service-container wiring for all adapters;
- Multisite AJAX site-switching/network-admin combinations;
- Action Scheduler real coexistence/packaging/Multisite/backend certification;
- durable Job attempt journal, leases/claims/heartbeat/fairness/backpressure/checkpoint persistence;
- Audit viewer/retention/privacy/export/legal-hold surfaces;
- Runtime Observatory admin graph/Policy/retention UI;
- minimal Platform admin shell;
- executable 10K/100K compiled-registration performance certification;
- business-facing module implementation.

No production deployment, live provider call, destructive live-site/customer-data mutation, live production DB migration or irreversible external operation was performed.

## Next WP121 work

1. **execute bounded Action Scheduler coexistence/packaging/backend evidence**;
2. deepen durable Job attempts/leases/checkpoints after backend evidence;
3. build minimal Platform admin shell + Runtime Observatory graph/diagnostic surface;
4. add executable 10K/100K compiled-registration scale evidence;
5. run shared-foundation readiness gate;
6. begin first business-module tranche only after that gate passes.

Every next tranche extends executable evidence and keeps separately privileged production/live-provider boundaries intact.
