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

Accepted decisions now extend through **ADR-0071**.

Latest planning milestones:
- ADR-0068 — Action Scheduler packaging/load-order/coexistence profile.
- ADR-0069 — unified WordPress Multisite scope/ownership/isolation.
- ADR-0070 — Product License installation/network/site-allocation identity + clone/staging/migration/transfer semantics.
- **ADR-0071 — Multisite physical data topology classes PT-A…PT-F.**

## Current Multisite logical state

Authoritative docs:
- `docs/ARCHITECTURE/MULTISITE-SCOPE-OWNERSHIP-MODEL.md`;
- `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`;
- `docs/QUALITY/MULTISITE-SCOPE-ISOLATION-EVIDENCE-PROTOCOL.md`;
- ADR-0069.

Core accepted rules:
- site scope default; network scope explicit/privileged;
- network activation does not make WPE data or permissions network-global;
- current blog context is not durable ownership;
- target-site capability + WPE Policy required;
- caches/jobs/events/Abilities/Vault/Membership/Backup/Reset/Import are scope-isolated;
- cross-site Relations/Queries off by default;
- network fan-out bounded through JobService.

Future Multisite certification: MS0 Static → MS1 Site Isolation → MS2 Scope Runtime → MS3 intentional Network Ops → MS4 Large-Network/Disaster.

**0 Multisite runtime fixtures executed.**

## Current physical topology state — ADR-0071

Authoritative paper model: `docs/ARCHITECTURE/MULTISITE-PHYSICAL-DATA-TOPOLOGY-CLASSES.md`.

Topology classes:
- **PT-A** native WordPress site/blog storage;
- **PT-B** native WordPress network/global primitives;
- **PT-C** WPE global scoped control-plane tables;
- **PT-D** WPE global scoped high-volume runtime tables;
- **PT-E** WPE per-site custom runtime tables;
- **PT-F** external authoritative state + local scoped references/cache.

Current preferences/candidates:
- Definition Repository → **PT-C**;
- WPE Job logical history → **PT-C/PT-D**;
- Audit → **PT-D**;
- Relations → **PT-D candidate, P-010 pending**;
- Membership Enrollment/Entitlement → **PT-D candidate, P-012 pending**;
- Workflow runtime → **PT-D candidate, P-011 pending**;
- Notification/Email operational state → **PT-D candidate**;
- Event Inbox → **PT-D candidate**;
- Form Entries → **PT-D vs PT-E evidence-gated**;
- Chat Messages → **PT-D vs PT-E evidence-gated**;
- user-created Custom Tables → explicit PT-D/PT-E according to scope/product contract;
- Support/commercial remote authority → **PT-F**.

Important safety consequences:
- native WordPress content stays native rather than duplicated into WPE control tables;
- physical topology never overrides logical site/network ownership;
- Site Backup extracts only target-site rows from shared PT-C/PT-D tables;
- PT-E is not default for control-plane because large networks could create extreme table/migration fan-out;
- no universal `all shared tables` or `all per-site tables` rule exists.

**No DDL, migration, index, table or database benchmark has been executed.**

## Current Product License / allocation state

ADR-0070 accepts opaque Installation/Network/Site Allocation identities; hostname is metadata rather than sole identity. Product Entitlement is separate from Membership authorization. Network-wide/selected-site/hybrid commercial modes, environment classes, clone review, migration/transfer and service-outage-vs-expiry semantics are defined.

**0 product-license service/allocation/clone/transfer fixtures executed.**

## Current JobService / Action Scheduler state

Action Scheduler 4.1.0 remains a reviewed candidate only. WPE JobService semantics/idempotency/history/fairness stay WPE-owned; Pro/modules do not duplicate the WPE bundle if backend is eventually selected.

ADR-0071 currently prefers WPE logical Job history in PT-C/PT-D independent of Action Scheduler physical tables.

**P-003 remains unexecuted.**

## Current Remote Service state

Purpose-scoped privacy/retention accepted. 30 future fixtures documented; **0 executed**. Product License resource/allocation implementation remains open.

## Current Backup state

- **34 target destinations / 34 stable provider profiles**;
- **0 C-certified / 0 C3 Supported destinations**;
- site/selected-site/network Backup profiles remain separate;
- shared WPE-table Site Backup requires scoped row extraction under ADR-0071;
- runtime resume/finalization/restore remains P-013.

## Current Membership billing state

Manual, WooCommerce 11.0.1, Woo Subscriptions 9.1.0, SureCart WP 4.7.0 + hosted API profile remain paper profiles.

Static maturity: **4 BE3 / 0 MB-certified**.

Membership remains site-scoped by default; PT-D is only the current runtime topology candidate, not verified schema.

## Current Email provider state

`wp_mail`, generic SMTP, SES API v2, SendGrid Web API v3, endpoint-specific Mailgun and dated Postmark profiles remain paper profiles.

Static maturity: **6 EE3 / 0 ET-certified**. Delivery operational state currently prefers PT-D, exact schema/indexes unverified.

## Platform evidence blockers

P-001 compatibility/Multisite; P-002 UI; P-003 Job backend; P-004 Definition PT-C DDL; P-005 Vault; P-006 Free↔Pro/site-allocation; P-007 CI; P-008 build; P-009 Query; P-010 Relations PT-D comparison; P-011 Workflow; P-012 Membership PT-D; P-013 Backup.

Forms/Chat PT-D-vs-PT-E evidence is also open. **No executable evidence has run.**

## Verification state

Verified planning/documentation only:
- planning branch isolated from `main`;
- **31/31 Exhaustive, 0/31 Authorized**;
- **31/31 Multisite scopes mapped; 0 MS1+ certified**;
- ADR index/Open Decisions/Readiness/Checkpoint synchronized through **ADR-0071**;
- Multisite physical topology paper model + ADR-0071 committed;
- Product License ADR-0070 committed, 0 service fixtures;
- Action Scheduler P-003 unexecuted;
- Membership 0 MB-certified;
- Email 0 ET-certified;
- Backup 0 C-certified;
- Remote Service privacy fixtures 0 executed;
- no implementation/test/provider-certification success claimed.

Not performed: dependency installation, Multisite runtime setup, Action Scheduler bootstrap, production source, DB tables/migrations/indexes, queue runs, crypto execution, PHPUnit/Playwright, provider/API/webhook/SMTP calls, commerce transactions, Email sends, WPE account/license calls, Backup/Restore, DB/performance benchmarks, release/deployment.

## Next allowed planning-only priorities

1. Product License remote resource + allocation conflict-state paper schema.
2. Definition Repository PT-C exact DDL/index alternatives without executing DDL.
3. Relations PT-D vs per-site physical comparison on paper.
4. Site lifecycle coordinator across PT-C/PT-D/PT-E.
5. Keep P-003/P-012/P-013 executable gates intact.
6. Keep governance/Draft PR synchronized.

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
