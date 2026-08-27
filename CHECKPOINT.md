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

Accepted decisions now extend through **ADR-0070**.

Latest planning milestones:
- ADR-0061 — stable semantic Backup family/provider identity registry.
- ADR-0062 — Manual/Woo Core/Woo Subscriptions/SureCart billing source-truth profiles.
- ADR-0063 — wp_mail/SMTP/SES/SendGrid/Mailgun/Postmark Email source-truth profiles.
- ADR-0064 — versioned Backup static-evidence overlays.
- ADR-0065 — Local/browser/FTP/FTPS/SFTP Backup product/security semantics.
- ADR-0066 — Membership provider/plugin/API/environment version registry.
- ADR-0067 — Email send/event/security/region profile version registry.
- ADR-0068 — Action Scheduler packaging/load-order/coexistence profile.
- ADR-0069 — unified WordPress Multisite site/network scope, ownership, authorization and isolation architecture.
- **ADR-0070 — product-license installation/network/site-allocation identity + clone/staging/migration/transfer semantics.**

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
- target-site capability + WPE Policy required;
- cache/jobs/events/Abilities/Vault/Membership/Backup/Reset/Import are scope-isolated;
- cross-site Relations/Queries are off by default;
- network fan-out uses bounded JobService coordination;
- physical global-vs-per-site table topology remains evidence-gated.

Future Multisite certification: MS0 Static → MS1 Site Isolation → MS2 Scope Runtime → MS3 intentional Network Ops → MS4 Large-Network/Disaster.

**0 Multisite runtime fixtures executed.**

## Current Product License / allocation state

Authoritative docs:
- `docs/ARCHITECTURE/PRODUCT-LICENSE-SITE-ALLOCATION-CLONE-TRANSFER.md`;
- ADR-0070.

Accepted rules:
- product-license identity uses opaque installation/network/site-allocation identifiers; hostname is metadata, not sole identity;
- WPE Product Entitlement remains separate from WordPress authority and WPE Membership Entitlement;
- Multisite commercial mode can be network-wide, selected-site allocation or hybrid according to signed plan contract;
- account connection alone does not auto-allocate all sites;
- site-count policy is explicit and is not covertly inferred by crawling every site;
- environment classes include production/staging/development/temporary-migration/disaster-recovery;
- cloned DB/license state enters review/revalidation/staging/migration states rather than becoming automatic second production activation;
- legitimate domain/host migration preserves allocation identity when continuity is verified;
- site transfer between networks remaps scope, shared connection/Vault references and allocation explicitly;
- service outage ≠ expiry; signed cached entitlement/offline-grace rules remain separate;
- expiry/revocation never disables Membership protection or deletes WPE data.

**0 product-license service/allocation/clone/transfer fixtures executed.**

## Current JobService / Action Scheduler state

JobService semantics remain backend-neutral: explicit idempotency, urgency/fairness, resource/concurrency keys, chunks/checkpoints, backpressure and cooperative cancellation.

Static Action Scheduler profile:
- current reviewed candidate: **4.1.0**;
- WPE Platform/Free owns one bundled candidate if selected; Pro/modules do not duplicate it;
- third-party/Woo copies expected; newest registered shared runtime may win;
- only JobService adapter calls `as_*` APIs;
- secrets/large payloads excluded from AS args;
- WPE business idempotency and Job/Audit retention independent from AS uniqueness/cleanup.

**P-003 remains unexecuted.**

## Current Remote Service state

Purpose-scoped privacy/retention accepted. 30-fixture future protocol documented; **0 executed**.

ADR-0070 adds open service evidence for Installation/Network/Site Allocation resources, site-count policy, clone reconciliation, transfer, offline grace and ownership transfer.

## Current Backup state

- **34 target destinations / 34 stable provider profiles**;
- **0 C-certified profiles / 0 normal Supported Backup Destinations**;
- versioned static provider overlays are evidence only;
- Local SE2; browser export SE3 product semantics; FTP SE2 legacy/insecure; FTPS SE3 TLS profile; SFTP SE2 host-key trust;
- site vs selected-sites vs full-network Backup/Restore are distinct ADR-0069/P-013 profiles.

## Current Membership billing state

Canonical path: `verified source facts → adapter → reconciliation → Membership policy → Enrollment → Entitlement`.

Current paper snapshot:
- Manual WPE source;
- WooCommerce 11.0.1;
- Woo Subscriptions 9.1.0 with HPOS first-class;
- SureCart WP 4.7.0 + hosted API/event profile.

Static maturity: **4 BE3 / 0 MB-certified**.

Membership remains site-scoped by default in Multisite. Product-license site allocation never becomes member access authority.

## Current Email provider state

Current paper profiles: `wp_mail`, generic SMTP, SES API v2, SendGrid Web API v3, endpoint-specific Mailgun profile, dated Postmark REST/webhook profile.

Static maturity: **6 EE3 / 0 ET-certified**.

## Platform evidence blockers

P-001 compatibility/Multisite; P-002 UI; P-003 Job backend; P-004 Definition DDL; P-005 Vault implementation; P-006 Free↔Pro/site-allocation runtime; P-007 CI; P-008 build; P-009 Query; P-010 Relations; P-011 Workflow; P-012 Membership; P-013 Backup.

Additional Email/Remote Service/Multisite/Product-License runtime evidence remains tracked. **None executed.**

## Verification state

Verified planning/documentation only:
- planning branch isolated from `main`;
- **31/31 Exhaustive, 0/31 Authorized**;
- **31/31 Multisite product scopes mapped**;
- ADR index/Open Decisions/Readiness/Checkpoint synchronized through ADR-0070;
- Multisite MS0–MS4 protocol committed; 0 runtime fixtures;
- Product License allocation/clone/transfer architecture + ADR-0070 committed; 0 runtime/service fixtures;
- duplicate Multisite draft removed; one canonical scope model remains;
- Membership provider source/version registries committed; 0 MB-certified;
- Email provider source/version registries committed; 0 ET-certified;
- Action Scheduler static packaging/coexistence committed; P-003 unexecuted;
- Backup planning committed; 0 certified;
- Remote Service privacy protocol committed; 0 executed;
- no implementation/test/provider-certification success claimed.

Not performed: dependency/package installation, Multisite test-network setup, Action Scheduler bootstrap, production source, DB migrations, queue runs, crypto execution, PHPUnit/Playwright, provider/API/webhook/SMTP calls, commerce transactions, Email sends, WPE service/account/license calls, Backup transfer/restore, performance benchmarks, release/deployment.

## Next allowed planning-only priorities

1. Physical Multisite data topology alternatives for Definition Repository/Relations/Jobs without DDL execution.
2. Product-license allocation remote resource/state-machine paper schema without service calls.
3. Remaining physical/runtime paper models where static decisions are useful.
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
