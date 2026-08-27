# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, package/dependency setup, crypto key generation, executable benchmark/spike, queue/runner execution, provider/API integration, email/SMTP sends, Backup/Restore execution or release packaging.

`continue`, `proceed`, planning approval, ADR acceptance, readiness or Phase 0 completion do **not** authorize development.

Source of truth:
- `/DEVELOPMENT-CONSENT.md`
- `AGENTS.md`
- `docs/DECISIONS/ADR-0014-development-consent-gate.md`

No production PHP/React source, plugin bootstrap, DB migration/table, package scaffold, executable test/benchmark, queue execution, provider integration or cryptographic implementation has been created/run.

## Product specification milestone

- **31/31** surfaces have screen/option inventory.
- **31/31** have behavioral specification.
- **31/31** are at **Exhaustive product-option maturity**.
- **0/31** are Authorized for development.

Exhaustive product specification is not a runtime/security/performance/provider verification claim.

## Accepted ADR state

Accepted decisions now extend through **ADR-0059**.

Latest additions:
- **ADR-0053** — Backup provider C0–C4 restore-first certification.
- **ADR-0054** — Remote-service resource/trust separation + RFC 9457 baseline.
- **ADR-0055** — Connection/provider I0–I5 capability certification.
- **ADR-0056** — Backup Remote Copy commit/verify/retention/delete/restore lifecycle.
- **ADR-0057** — Membership billing source facts + reconciliation + MB0–MB5 certification.
- **ADR-0058** — Email delivery truth + ET0–ET5 provider certification.
- **ADR-0059** — backend-neutral JobService semantics: at-least-once, explicit idempotency, reviewed urgency/fairness, resource/concurrency keys, backpressure/chunking and cooperative cancellation.

ADR-0006 remains Proposed/P-003 only for the concrete Action Scheduler backend evidence; ADR-0059 fixes the semantics any backend must satisfy.

## Current architecture snapshot

### Platform/Data
- Definition Repository: stable Definitions + immutable Revisions + revision-aware Dependencies.
- Query AST typed/provider-neutral; raw SQL/PHP is not a normal node.
- Relations typed edge model candidate; reverse lookup/cardinality first-class.
- Workflow runs pin published revision and execute through JobService.
- Fields use plural storage families: native meta/options, Custom Tables, Relations, Vault.
- Custom Tables use desired-vs-observed schema + typed Migration Plan.
- Component Blueprint shared across Listings/Dashboard/Builder Widgets; builders are adapters.

### Job Service
- Logical model: `Job Type Registration + Schedule Definition + Job Record + Attempt Record + Runner + Execution Policy`.
- Mutating work assumes at-least-once execution opportunity, never magical exactly-once external effects.
- Backend `unique` scheduling is not business idempotency.
- Urgency classes: restricted system control, security/transactional, interactive, normal, bulk, maintenance.
- Strict priority alone is insufficient; fairness/age protection prevents sustained starvation.
- Resource classes + concurrency keys bound DB/CPU/filesystem/network/provider/destructive work.
- Large workloads use checkpointed chunks/continuations rather than one enormous PHP process.
- Backpressure/admission throttles bulk producers instead of allowing unbounded queue fan-out.
- Cancellation/pause are cooperative and truthful about safe checkpoint boundaries.
- Queue insertion order/timestamps/priority/groups are not business dependency guarantees.
- Request-driven WP-Cron is compatible but not exact wall-clock scheduling; WP-CLI/system cron is a future certified runner mode.
- Action Scheduler remains preferred backend candidate; its raw priority/batch/concurrency/claim/retention values are not stable WPE product semantics.
- Current Action Scheduler multisite behavior does not provide special network-wide queue orchestration; P-003 must prove WPE site/network isolation/fairness.

### Security/Identity
- Generic Profile fields cannot mutate passwords/sessions/Application Passwords/roles/Membership authority.
- Role mutations preserve a real recovery-principal invariant.
- Vault: random VRK → per-secret DEKs → versioned key slots; no plaintext fallback.
- Protector uses trusted-proxy-aware identity + atomic Rate Limit service.
- XML-RPC complete-deny/authenticated-off/pingback/per-method states are separate.

### Integrations/Remote Service
- Connections: Safe HTTP + verified webhooks + Event Inbox.
- Integration certification: I0 Configurable → I1 Auth → I2 Read → I3 Write/Action → I4 Events/Reconciliation → I5 Production Profile.
- Remote service Account/Site/Entitlement/Catalog/Support/Docs/Release resources have separate trust semantics; TUF update metadata is separate authority.

### Membership billing
- Billing provider → verified source fact → adapter → reconciliation → Membership policy → Enrollment → Entitlement.
- Cancellation intent is not automatic immediate access revocation.
- Billing provider certification uses MB0–MB5.

### Email delivery
- Notification → Recipient Delivery → Rendered Message → Transport Attempt → Provider Message Reference → verified Provider Event Ledger → derived outcome.
- `wp_mail()` success = local processing only.
- provider/API/SMTP acceptance is not automatically receiving-server delivery.
- receiving-server delivery is not inbox placement/read proof.
- complaints/suppression/asynchronous failures remain source facts.
- ET0–ET5 provider certification governs claims.

### Backup/Operations
- manifest-first multipart backup bundle;
- Sodium/Argon2id accepted encryption profile;
- **34 provider targets; 0 certified**;
- C0–C4 restore-first provider certification;
- durable Remote Copy commit/finalization lifecycle;
- Reset and Watermark accepted safety architectures.

## Current Job/Action Scheduler research snapshot

Official documentation currently establishes:
- Action Scheduler supports async/single/recurring/cron actions with groups, uniqueness hints and priority 0–255 where lower values take precedence.
- default web runner claims bounded batches and limits processing by time/memory; concurrency increases can substantially increase server load.
- WP-CLI can process selected hooks/groups, but official docs warn separate focused runners can violate implicit schedule dependencies.
- Action Scheduler provides trace/log records and current cleanup/failed-retention behavior, but those backend defaults are not WPE retention promises.
- Action Scheduler is designed around a single site and documents no special network-wide multisite queue management.
- WordPress recurring/single cron work fires when a request visits the site after the due time, so nominal schedule time is not exact execution time.

Paper source:
- `docs/ARCHITECTURE/JOB-SERVICE-EXECUTION-FAIRNESS-BACKPRESSURE.md`
- `docs/DECISIONS/ADR-0059-job-service-execution-fairness-backpressure.md`

## Platform blockers still requiring executable evidence

- P-001 compatibility;
- P-002 UI/design system;
- **P-003 Action Scheduler/JobService concrete backend**;
- P-004 Definition Repository exact DDL/indexes;
- P-005 Vault exact envelope/interoperability;
- P-006 Free↔Pro runtime compatibility;
- P-007 CI;
- P-008 build toolchain;
- P-009 Query;
- P-010 Relations;
- P-011 Workflow;
- P-012 Membership;
- P-013 Backup/provider certification.

**None has been executed.**

## P-003 future evidence highlights

- Action Scheduler load-order/coexistence/version/features;
- logical Job/Attempt ↔ backend action mapping;
- claim/crash/reclaim/lease ambiguity;
- retry/idempotency/unknown external outcome;
- mixed urgency fairness/starvation;
- resource/concurrency keys;
- backpressure/admission/high backlog;
- cancellation/chunk checkpoints;
- WP-Cron stalled/low-traffic behavior;
- WP-CLI/system cron and group-specific dependency behavior;
- representative hosting load limits;
- multisite isolation/fairness;
- retention/cleanup/upgrade/uninstall;
- sensitive payload/log redaction.

## Verification state

### Verified
- planning branch isolated from `main`;
- 31/31 Exhaustive;
- 0/31 Authorized;
- ADR index/Open Decisions/Readiness/Checkpoint synchronized through ADR-0059;
- JobService semantic architecture + ADR-0059 committed;
- Email ET0–ET5 architecture/evidence protocol committed;
- Membership MB0–MB5 contract committed;
- Backup matrix remains 34 targets / 0 certified;
- no implementation/build/test success claimed.

### Not performed / intentionally blocked
- Composer/npm install;
- Action Scheduler package setup/migration;
- queue action creation/runner execution;
- production PHP/React source;
- DB migrations/tables;
- crypto key generation/encryption/signing code;
- PHPUnit/Playwright;
- P-001…P-013 execution;
- provider/API/webhook/SMTP execution;
- performance benchmarks;
- Backup/Restore/Reset execution;
- release packaging/deployment.

Reason: explicit owner development/executable-spike consent has not been granted.

## Next allowed planning-only priorities

1. Remote Service field-level privacy/retention matrix.
2. Backup family-specific capability overrides for the 34 target destinations.
3. Membership billing provider-specific capability/evidence profiles.
4. Email provider-specific capability matrix.
5. Formalize bounded P-003 consent-gated evidence protocol without executing it.
6. Keep governance and Draft PR synchronized.

Before **any executable work**, obtain explicit owner consent.

## Resume order
1. `/DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
5. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
6. `docs/OPEN-DECISIONS-REGISTER.md`
7. `docs/DECISIONS/README.md`
8. relevant architecture/security/module docs

Repository evidence overrides conversational memory.
