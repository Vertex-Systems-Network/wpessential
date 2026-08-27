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
- **0/31 Authorized**
- Implemented: none
- Runtime verified: none

## Accepted architecture

Accepted decisions now extend through **ADR-0068**.

Latest planning milestones:
- ADR-0061 — stable semantic Backup family/provider identity registry.
- ADR-0062 — Manual/Woo Core/Woo Subscriptions/SureCart billing source-truth profiles.
- ADR-0063 — wp_mail/SMTP/SES/SendGrid/Mailgun/Postmark Email source-truth profiles.
- ADR-0064 — versioned Backup static-evidence overlays.
- ADR-0065 — Local/browser/FTP/FTPS/SFTP Backup product/security semantics.
- ADR-0066 — Membership provider/plugin/API/environment version registry.
- ADR-0067 — Email send/event/security/region profile version registry.
- ADR-0068 — Action Scheduler packaging/load-order/coexistence profile.

## Current JobService / Action Scheduler state

JobService semantics remain backend-neutral: explicit idempotency, urgency/fairness, resource/concurrency keys, chunks/checkpoints, backpressure and cooperative cancellation.

Static Action Scheduler profile:
- current reviewed candidate: **4.1.0**;
- if selected, WPE Platform/Free owns one bundled candidate; Pro/modules do not bundle WPE duplicates;
- third-party/Woo copies are expected; Action Scheduler newest registered plugin runtime may win;
- WPE does not force its vendored copy over a newer runtime;
- registration must occur before `plugins_loaded` priority 0 according to upstream load-order guidance;
- adapter use waits for AS initialization;
- only JobService adapter calls `as_*` APIs;
- WPE secrets/large payloads do not live in Action Scheduler args;
- WPE business idempotency does not depend on AS unique scheduling;
- WPE Job/Attempt/Audit retention does not depend on AS action cleanup.

**P-003 remains unexecuted and Action Scheduler is not a Verified backend.**

## Current Remote Service state

Accepted purpose-scoped privacy/retention. Future executable verification is bounded by the 30-fixture Remote Service protocol. **0 fixtures executed.**

## Current Backup state

- **34 target destinations / 34 stable provider profiles**;
- **0 C-certified profiles / 0 normal Supported Backup Destinations**;
- versioned static provider overlays are evidence only;
- Local SE2; browser export SE3 product semantics; FTP SE2 legacy/insecure; FTPS SE3 with TLS 1.2+/protected data requirement; SFTP SE2 with mandatory host-key trust;
- runtime resume/finalization/restore certification remains P-013.

## Current Membership billing state

Canonical path: `verified source facts → adapter → reconciliation → Membership policy → Enrollment → Entitlement`.

Provider/profile version identity now includes source plugin/API/adapter/environment.

Current 2026-08-28 static snapshot:
- Manual — WPE-owned profile;
- WooCommerce **11.0.1**;
- Woo Subscriptions **9.1.0**, with Woo 11.0 current compatibility snapshot and HPOS first-class;
- SureCart WP **4.7.0** + separate hosted API/event profile.

Static maturity: **4 BE3 profiles; 0 MB-certified**.

Newer major provider versions default to unverified rather than automatically Supported. Known vulnerable versions can be security-blocked rather than recommended for compatibility.

## Current Email provider state

Provider version identity now separates send API/transport, event schema, security profile, adapter and region/account scope.

Current paper identities:
- `wp_mail` → WordPress/P-001 runtime profile;
- generic SMTP → negotiated capability/security profile;
- SES → API v2 + region/account/event profile;
- SendGrid → Web API v3 + dated Event Webhook/security profile;
- Mailgun → endpoint-specific path-version family + dated webhook/security + region;
- Postmark → dated REST/webhook profile; no invented provider-wide v1.

Static maturity: **6 EE3 profiles; 0 ET-certified**.

## Platform evidence blockers

P-001 compatibility; P-002 UI; P-003 Job backend; P-004 Definition DDL; P-005 Vault implementation; P-006 Free↔Pro; P-007 CI; P-008 build; P-009 Query; P-010 Relations; P-011 Workflow; P-012 Membership; P-013 Backup.

Additional Email/Remote Service runtime evidence remains separately tracked. **None executed.**

## Verification state

Verified planning/documentation only:
- planning branch isolated from `main`;
- **31/31 Exhaustive, 0/31 Authorized**;
- ADR index/Open Decisions/Readiness/Checkpoint synchronized through ADR-0068;
- Membership provider source/version registries + ADR-0062/0066 committed;
- Email provider source/version registries + ADR-0063/0067 committed;
- Action Scheduler static packaging/coexistence profile + ADR-0068 committed;
- Backup provider/transport planning committed, 0 certified;
- Remote Service privacy protocol committed, 0 executed;
- no implementation/test/provider-certification success claimed.

Not performed: dependency/package installation, Action Scheduler bootstrap, production source, DB migrations, queue runs, crypto execution, PHPUnit/Playwright, provider/API/webhook/SMTP calls, commerce transactions, Email sends, WPE service transmission, Backup transfer/restore, performance benchmarks, release/deployment.

## Next allowed planning-only priorities

1. Unified WordPress Multisite scope/ownership architecture.
2. Remaining physical/runtime paper models where static decisions are useful.
3. Provider/version refresh only when current official facts materially affect architecture.
4. Keep P-003/P-012/P-013 executable gates intact.
5. Keep governance/Draft PR synchronized.

Before any executable work, explicit owner consent is required.

## Resume order
1. `/DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
5. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
6. `docs/OPEN-DECISIONS-REGISTER.md`
7. `docs/DECISIONS/README.md`
8. relevant architecture/security/module/provider docs

Repository evidence overrides conversational memory.
