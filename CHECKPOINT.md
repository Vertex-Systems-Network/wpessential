# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-28**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, dependencies, executable spikes/benchmarks, queue execution, provider/API interactions, service transmission, SMTP/email sends, Backup/Restore operations or release packaging.

`continue` and planning acceptance do **not** authorize development.

Source of truth: `/DEVELOPMENT-CONSENT.md`, `AGENTS.md`, ADR-0014.

## Product milestone

- **31/31 Exhaustive product-option maturity**
- **31/31 Multisite scope behavior mapped**
- **0/31 Authorized**
- **0 surfaces Multisite runtime-certified at MS1+**
- Implemented: none
- Runtime verified: none

## Accepted architecture

Accepted decisions now extend through **ADR-0069**.

Latest planning milestones:
- ADR-0061 — stable semantic Backup family/provider identity registry.
- ADR-0062 — Manual/Woo Core/Woo Subscriptions/SureCart billing source-truth profiles.
- ADR-0063 — wp_mail/SMTP/SES/SendGrid/Mailgun/Postmark Email source-truth profiles.
- ADR-0064 — versioned Backup static-evidence overlays.
- ADR-0065 — Local/browser/FTP/FTPS/SFTP Backup product/security semantics.
- ADR-0066 — Membership provider/plugin/API/environment version registry.
- ADR-0067 — Email send/event/security/region profile version registry.
- ADR-0068 — Action Scheduler packaging/load-order/coexistence profile.
- **ADR-0069 — unified WordPress Multisite site/network scope, ownership, authorization and isolation architecture.**

## Current Multisite state

Authoritative docs:
- `docs/ARCHITECTURE/MULTISITE-SCOPE-OWNERSHIP-MODEL.md`;
- `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`;
- `docs/QUALITY/MULTISITE-SCOPE-ISOLATION-EVIDENCE-PROTOCOL.md`;
- ADR-0069.

Accepted logical rules:
- site scope is default for user-created application definitions;
- network scope is explicit/privileged;
- network activation does not make WPE data/network permissions global;
- durable resources store explicit site/network coordinates; current blog context is not ownership;
- `switch_to_blog()` is context only and must be paired/restored; it is not authorization or code loading;
- cross-site Relations/Queries are off by default unless a dedicated scope-aware profile exists;
- target-site WordPress capability + WPE Policy authorize cross-site actions;
- Super Admin does not bypass high-risk WPE Policy/audit/recovery contracts;
- cache/jobs/events/Abilities/Vault/Membership/Backup/Reset/Import are scope-isolated;
- network fan-out uses bounded JobService coordination, not unbounded interactive site loops;
- network/shared credentials can be usable without becoming site-readable plaintext;
- physical global-vs-per-site table topology remains evidence-gated.

Future Multisite certification levels:
- MS0 Static Compatible;
- MS1 Activation & Site Isolation;
- MS2 Scope Runtime Certified;
- MS3 intentional Cross-Site/Network Operations Certified;
- MS4 Large-Network & Disaster Certified.

**0 Multisite runtime fixtures have been executed.**

## Current JobService / Action Scheduler state

JobService semantics remain backend-neutral: explicit idempotency, urgency/fairness, resource/concurrency keys, chunks/checkpoints, backpressure and cooperative cancellation.

Static Action Scheduler profile:
- current reviewed candidate: **4.1.0**;
- if selected, WPE Platform/Free owns one bundled candidate; Pro/modules do not bundle WPE duplicates;
- third-party/Woo copies are expected; newest registered shared runtime may win;
- only JobService adapter calls `as_*` APIs;
- WPE secrets/large payloads do not live in Action Scheduler args;
- WPE business idempotency and Job/Audit retention do not depend on AS uniqueness/cleanup.

**P-003 remains unexecuted and Action Scheduler is not a Verified backend.**

## Current Remote Service state

Accepted purpose-scoped privacy/retention. Future executable verification is bounded by the 30-fixture Remote Service protocol. **0 fixtures executed.**

## Current Backup state

- **34 target destinations / 34 stable provider profiles**;
- **0 C-certified profiles / 0 normal Supported Backup Destinations**;
- versioned static provider overlays are evidence only;
- Local SE2; browser export SE3 product semantics; FTP SE2 legacy/insecure; FTPS SE3 with TLS 1.2+/protected data requirement; SFTP SE2 with mandatory host-key trust;
- site vs selected-sites vs full-network Backup/Restore are distinct ADR-0069/P-013 profiles;
- runtime resume/finalization/restore certification remains P-013.

## Current Membership billing state

Canonical path: `verified source facts → adapter → reconciliation → Membership policy → Enrollment → Entitlement`.

Current static snapshot:
- Manual — WPE-owned profile;
- WooCommerce **11.0.1**;
- Woo Subscriptions **9.1.0** with HPOS first-class;
- SureCart WP **4.7.0** + separate hosted API/event profile.

Static maturity: **4 BE3 profiles; 0 MB-certified**.

Membership is site-scoped by default in Multisite. Network Membership is not implied and requires a separate explicit profile/evidence set.

## Current Email provider state

Current paper identities:
- `wp_mail` → WordPress/P-001 runtime profile;
- generic SMTP → negotiated capability/security profile;
- SES → API v2 + region/account/event profile;
- SendGrid → Web API v3 + dated Event Webhook/security profile;
- Mailgun → endpoint-specific path-version family + dated webhook/security + region;
- Postmark → dated REST/webhook profile.

Static maturity: **6 EE3 profiles; 0 ET-certified**.

Email delivery/correlation remains site-aware under ADR-0069.

## Platform evidence blockers

P-001 compatibility/Multisite; P-002 UI; P-003 Job backend; P-004 Definition DDL; P-005 Vault implementation; P-006 Free↔Pro; P-007 CI; P-008 build; P-009 Query; P-010 Relations; P-011 Workflow; P-012 Membership; P-013 Backup.

Additional Email/Remote Service/Multisite runtime evidence remains separately tracked. **None executed.**

## Verification state

Verified planning/documentation only:
- planning branch isolated from `main`;
- **31/31 Exhaustive, 0/31 Authorized**;
- **31/31 Multisite product scopes mapped**;
- ADR index/Open Decisions/Readiness/Checkpoint synchronized through ADR-0069;
- Multisite MS0–MS4 future evidence protocol committed; **0 runtime fixtures executed**;
- duplicate Multisite draft removed so canonical model remains singular;
- Membership provider source/version registries + ADR-0062/0066 committed;
- Email provider source/version registries + ADR-0063/0067 committed;
- Action Scheduler static packaging/coexistence profile + ADR-0068 committed;
- Backup provider/transport planning committed, 0 certified;
- Remote Service privacy protocol committed, 0 executed;
- no implementation/test/provider-certification success claimed.

Not performed: dependency/package installation, Multisite test-network setup, Action Scheduler bootstrap, production source, DB migrations, queue runs, crypto execution, PHPUnit/Playwright, provider/API/webhook/SMTP calls, commerce transactions, Email sends, WPE service transmission, Backup transfer/restore, performance benchmarks, release/deployment.

## Next allowed planning-only priorities

1. Remaining physical/runtime paper models where static decisions are useful.
2. Definition Repository/Custom Tables/Relations physical Multisite alternatives without executing DDL.
3. Network license/site-allocation semantics and clone/transfer paper contract.
4. Keep P-003/P-012/P-013 executable gates intact.
5. Keep governance/Draft PR synchronized.

Before any executable work, explicit owner consent is required.

## Resume order
1. `/DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
5. `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`
6. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
7. `docs/OPEN-DECISIONS-REGISTER.md`
8. `docs/DECISIONS/README.md`
9. relevant architecture/security/module/provider docs

Repository evidence overrides conversational memory.
