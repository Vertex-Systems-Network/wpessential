# WPEssential Architecture Decision Records

Status: **Phase 0 planning / no development authorization**  
Last synchronized: 2026-08-28

ADRs preserve long-lived product, architecture, security, data, compatibility and distribution decisions. Accepted ADRs are never silently changed; a reversal requires a superseding ADR. Exact implementation profiles can remain evidence-gated even when architecture is Accepted.

**Hard rule:** technical acceptance never grants development permission. `/DEVELOPMENT-CONSENT.md` and ADR-0014 require explicit owner consent before source/build/migration/test/benchmark/provider implementation.

## ADR index

| ADR | Status | Decision |
|---|---|---|
| ADR-0001 | Accepted | WordPress.org Free + separately distributed Pro; Pro trial belongs to Pro entitlement |
| ADR-0002 | Proposed blocker | WP 6.9 / PHP 8.3 current minimum candidates; WP 7.1 planning reference; DB floor evidence-gated; CF execution pending |
| ADR-0003 | Accepted | WordPress Abilities as typed reusable action contract |
| ADR-0004 | Accepted | No standard arbitrary PHP eval or unrestricted destructive raw-SQL primitive |
| ADR-0005 | Proposed blocker | WPE UI wrappers + compatible WordPress public primitives; WordPress-provided React; minimum WP cannot hard-depend on newer-only UI capability |
| ADR-0006 | Proposed adapter blocker | WPE Job Service abstraction; Action Scheduler preferred adapter candidate; later semantics in ADR-0059/0068/0083 |
| ADR-0007 | Accepted | Pro expiry preserves data and safe deployed runtime; editing/unsafe operations can lock |
| ADR-0008 | Proposed physical-evidence blocker | Definition Repository shape later accepted; exact DDL/index/locking evidence pending |
| ADR-0009 | Proposed physical-evidence blocker | Secrets Vault hierarchy later accepted; exact crypto/runtime evidence pending |
| ADR-0010 | Proposed blocker | Free↔Pro Platform API/schema/package compatibility + safe degraded boot; fixed FP protocol in ADR-0128 |
| ADR-0011 | Proposed blocker | Layered provider-neutral PR/main/nightly/release CI; fixed CI protocol in ADR-0127 |
| ADR-0012 | Proposed blocker | `@wordpress/build` first candidate; `@wordpress/scripts` comparison/fallback; Vite only for proven unmet need; fixed BT protocol in ADR-0126 |
| ADR-0013 | Accepted | Role ≠ Membership ≠ billing Subscription/Purchase ≠ Entitlement |
| ADR-0014 | Accepted governance | Production development/executable spikes require explicit owner consent; `continue` never authorizes code |
| ADR-0015 | Accepted | Membership outer security denial cannot be bypassed; specificity + same-specificity deny-wins |
| ADR-0016 | Accepted | Enrollment states pending/trialing/active/grace/paused/expired/revoked; cancellation is intent |
| ADR-0017 | Accepted architecture | WPE product entitlement signed, site-bound and freshness-aware; outage ≠ expiry |
| ADR-0018 | Accepted architecture | Pro updates use signed anti-rollback/freeze/key-rotation trust; Free is not external Pro updater |
| ADR-0019 | Accepted | Draft Plan edits do not alter live access; published changes choose follow-current/grandfather/scheduled |
| ADR-0020 | Accepted | Membership team roles separate from WP roles; role sync optional/off by default and provenance-safe |
| ADR-0021 | Accepted architecture | Per-backup DEK + independent disaster-recovery wrapping; WP salts not sole recovery root |
| ADR-0022 | Accepted architecture | Native WP meta/options where natural; Custom Tables for scale/constraints; Relations for relationships; Vault for secrets |
| ADR-0023 | Accepted architecture | Desired schema + typed Migration Plan; `dbDelta()` is one compiler tool, not universal migration language |
| ADR-0024 | Accepted defaults | Membership category-level retention; minimize raw provider/log data; detailed download/IP logging off by default |
| ADR-0025 | Accepted architecture | Form Definitions separate from runtime Entries; pinned revision + typed values + explicit projections |
| ADR-0026 | Accepted architecture | Notification occurrence, recipient/read state and channel delivery attempts are separate domains |
| ADR-0027 | Accepted architecture | Chat canonical runtime independent of transport; private assets; search reauthorizes; membership/team revoke affects access |
| ADR-0028 | Accepted architecture | REST definitions compile to validated runtime descriptors using WP REST + Policy + Query/Data Source/Abilities |
| ADR-0029 | Accepted architecture | Email Definition → compiled descriptor → authorized context → Email IR → HTML/plaintext → delivery attempt |
| ADR-0030 | Accepted security architecture | Profile data, identity changes, credentials, authorization and protected user internals are separate action classes |
| ADR-0031 | Accepted architecture | Dashboard Definition → compiled route/component descriptor → server route resolution → Policy → renderer |
| ADR-0032 | Accepted security architecture | Role mutations require impact/anti-lockout recovery invariant; break-glass uses WordPress/CLI authority, no anonymous backdoor |
| ADR-0033 | Accepted logical architecture | Backup canonical model is manifest-first independently verifiable multipart logical bundle |
| ADR-0034 | Accepted security profile | Account link uses fixed WPE OAuth callback + one-time site-bound completion artifact + PKCE S256; Device flow fallback |
| ADR-0035 | Accepted architecture | One shared Component Blueprint for Builder Widgets, Dashboards and Listings; builders are adapters |
| ADR-0036 | Accepted architecture | Settings Definition separate from site/network scoped runtime value documents; Vault-backed secrets; explicit inheritance |
| ADR-0037 | Accepted architecture | Admin Menu uses runtime discovery + stable transformation rules; menu hiding ≠ authorization; safe mode restores native navigation |
| ADR-0038 | Accepted architecture | Status Manager separates WordPress Post Status adapter from generic domain state machine |
| ADR-0039 | Accepted architecture | Listings compile to authorized Query → visible result set → Component Blueprint SSR; cache safety derived from dependencies |
| ADR-0040 | Accepted security architecture | Centralized Safe HTTP + verified Webhook Gateway + durable normalized Event Inbox for external I/O |
| ADR-0041 | Accepted architecture | Imports use reviewed Plan/Dry Run fingerprint + durable checkpoints + identity map + change journal + truthful rollback classes |
| ADR-0042 | Accepted crypto profile / evidence pending | Product entitlement uses Ed25519 + RFC 8785 JCS + domain separation + root-authorized signer keysets |
| ADR-0043 | Accepted crypto profile / evidence pending | Backup encryption uses Sodium secretstream XChaCha20-Poly1305, XChaCha20 DEK wrapping and Argon2id; native ext-sodium required |
| ADR-0044 | Accepted protocol profile / client pending | Pro automated updates target TUF 1.0-compatible Root/Targets/Snapshot/Timestamp semantics |
| ADR-0045 | Accepted security architecture / evidence pending | Protector uses trusted-proxy-aware request gating + shared atomic Rate Limit service + non-authenticating recovery mode |
| ADR-0046 | Accepted media architecture / evidence pending | Watermarker is non-destructive derivative pipeline keyed by source fingerprint + Rule revision + output/engine profile |
| ADR-0047 | Accepted destructive-workflow architecture / evidence pending | Reset uses reviewed Plan + verified restore point + durable journal + recovery-principal invariant + post-health verification |
| ADR-0048 | Accepted security architecture / evidence pending | Vault uses random VRK → per-secret DEKs → external/WP-derived/recovery/KMS key slots; no plaintext fallback |
| ADR-0049 | Accepted architecture / exact DDL evidence pending | Definition Repository is Definitions + immutable Revisions + revision-aware Dependencies |
| ADR-0050 | Accepted platform architecture / service evidence pending | Support Ticket authority lives on WPE service; local WP is minimal secure client/cache |
| ADR-0051 | Accepted security architecture / adapter evidence pending | Dashboard Widgets render trusted/local structured content; remote response is data, not arbitrary admin HTML/JS |
| ADR-0052 | Accepted security/compatibility architecture / runtime evidence pending | XML-RPC is layered host/Protector → method registry → method policy → native WP auth |
| ADR-0053 | Accepted backup architecture / provider evidence pending | Backup support is protocol-family adapter + provider capability profile with C0–C4 restore-first certification; normal Support label requires C3 |
| ADR-0054 | Accepted platform architecture / service evidence pending | Account/site/entitlement/catalog/support/docs/release domains are separate trust resources; TUF remains separate update authority |
| ADR-0055 | Accepted integration architecture / provider evidence pending | Connections certified by adapter + provider + capability + API version using I0–I5 |
| ADR-0056 | Accepted backup lifecycle / provider evidence pending | Each destination has durable Remote Copy commit/verify/retention/delete/restore states; manifest-last and truthful deletion semantics |
| ADR-0057 | Accepted membership integration architecture / provider evidence pending | Billing integrations emit verified commercial source facts; reconciliation + WPE policy own Enrollment/Entitlement transitions; MB0–MB5 |
| ADR-0058 | Accepted email delivery architecture / provider evidence pending | Submission, receiving-server delivery, complaints/suppression and engagement remain separate evidence; ET0–ET5 |
| ADR-0059 | Accepted JobService semantics / adapter evidence pending | Backend-neutral Job/Schedule/Attempt/Runner; at-least-once, idempotency, fairness, resource keys, backpressure, cooperative cancellation |
| ADR-0060 | Accepted platform/privacy architecture / service evidence pending | Remote transmission purpose-scoped/minimized; Free activation sends nothing; account link ≠ telemetry consent |
| ADR-0061 | Accepted backup identity/capability architecture / provider evidence pending | Semantic `bf.*` family keys canonical; static evidence never implies C0–C4 certification |
| ADR-0062 | Accepted Membership billing provider profiles / executable evidence pending | Manual/Woo order/Woo Subscriptions/SureCart source-truth profiles version-scoped; reconciliation remains WPE-owned |
| ADR-0063 | Accepted Email provider profiles / executable evidence pending | wp_mail/SMTP/SES/SendGrid/Mailgun/Postmark normalize verified provider facts only |
| ADR-0064 | Accepted Backup evidence-governance architecture / runtime evidence pending | Versioned static SE overlays never produce C0–C4 certification |
| ADR-0065 | Accepted Backup transport/security profiles / runtime evidence pending | Local/browser/FTP/FTPS/SFTP semantics explicit; FTP insecure legacy; SFTP host-key trust mandatory |
| ADR-0066 | Accepted Membership provider-version architecture / executable evidence pending | Certification scoped by provider/profile/plugin/API/adapter/environment; HPOS first-class dimension |
| ADR-0067 | Accepted Email provider-version architecture / executable evidence pending | Email certification records transport/API/event schema/security/region/account scope |
| ADR-0068 | Accepted Action Scheduler packaging/coexistence architecture / P-003 pending | Platform/Free owns one bundled candidate if selected; modules call only JobService adapter |
| ADR-0069 | Accepted Multisite logical/security architecture / physical evidence pending | Every scope-aware resource has explicit site/network coordinates; target-site capability + WPE Policy required |
| ADR-0069 | Accepted Multisite scope ownership model | Explicit site/network ownership and target-site authorization remain mandatory across scope-aware resources |
| ADR-0070 | Accepted commercial/platform architecture / service evidence pending | Product licensing uses opaque installation/network/site-allocation identities + explicit environment classes |
| ADR-0071 | Accepted Multisite physical-topology paper architecture / exact DDL evidence pending | PT-A…PT-F storage classes; control-plane favors scoped PT-C; high-volume may use PT-D/PT-E by evidence |
| ADR-0072 | Accepted Product License remote-resource architecture / service evidence pending | Account/Contract/Installation/Network/Site Allocation/Signed Entitlement separate; retries/unknown outcomes reconcile |
| ADR-0073 | Accepted Definition Repository PT-C benchmark baseline / exact DDL evidence pending | D1 numeric IDs + textual UUID + explicit scope + immutable payload + minimal indexes |
| ADR-0074 | Accepted Relations physical benchmark baseline / P-010 pending | R1 PT-D first; R2 PT-E mandatory; R3 per-relation exceptional |
| ADR-0075 | Accepted Multisite lifecycle architecture / runtime evidence pending | Site Lifecycle Coordinator manages create/update/uninitialize/delete/clone/transfer across storage classes |
| ADR-0076 | Accepted Product License HTTP/OpenAPI paper architecture / service evidence pending | Resource-oriented API; signed entitlement separate; ETag/If-Match, idempotency, Problem Details, bounded pagination |
| ADR-0077 | Accepted Forms & Chat benchmark baselines / physical evidence pending | Forms FRT1/PT-D and Chat CRT1/PT-D first; PT-E mandatory comparisons |
| ADR-0078 | Accepted Membership benchmark baseline / P-012 pending | M1/PT-D first; M2/PT-E mandatory; Enrollment authoritative, Entitlements derived, access generation revoke-safe |
| ADR-0079 | Accepted Notification/Email operational benchmark baseline / provider evidence pending | NE1/PT-D first; NE2/PT-E mandatory; truth boundaries explicit |
| ADR-0080 | Accepted Event Inbox benchmark baseline / provider evidence pending | EI1/PT-D first; EI2/PT-E mandatory; trusted endpoint determines scope; dedupe + consumer idempotency required |
| ADR-0081 | Accepted Audit PT-D retention/integrity profile / exact evidence pending | AU1/PT-D favored; Audit separate from domain history/diagnostics; local DB not claimed tamper-proof |
| ADR-0082 | Accepted Workflow benchmark baseline / P-011 pending | WF1/PT-D first; WF2/PT-E mandatory; Workflow durable truth separate from Job backend |
| ADR-0083 | Accepted JobService physical mapping baseline / P-003 pending | J1 PT-D first; J2 PT-C current + PT-D history; J3 PT-C low volume; backend rows not WPE truth |
| ADR-0084 | Accepted Backup Remote Copy physical baseline / P-013 pending | BR1/PT-D first; BR2 PT-C-current + PT-D history; BR3 PT-E; commit/verify/delete truth preserved |
| ADR-0085 | Accepted Vault PT-C physical envelope profile / P-005 pending | V1/PT-C favored; V2 per-site + network Vault mandatory; no plaintext fallback |
| ADR-0086 | Accepted Query compiler benchmark baseline / P-009 pending | QP1 WP-native first; QP2 Custom Table + QP3 Relations required for owned workloads; QP4 remote separately certified |
| ADR-0087 | Accepted Field Storage physical routing profile / evidence pending | FS1 native WP default; FS2 Custom Table escalation; FS3 child rows; FS4 Relations; FS5 Vault refs |
| ADR-0088 | Accepted Custom Tables PT-D/PT-E physical baseline / exact DDL evidence pending | CT1/PT-E first for site-owned; CT2/PT-D mandatory; CT3 only network-owned |
| ADR-0089 | Accepted Settings PT-A/PT-B runtime profile / evidence pending | ST1 site doc; ST2 network doc; ST3 inheritance; non-autoload default; stale edits conflict visibly |
| ADR-0090 | Accepted Membership protected-file delivery profile / security evidence pending | PD1 private local correctness; PD2 accelerated; PD3 private object signed delivery; PC0–PC4 certification |
| ADR-0091 | Accepted Product License API component schema profile / service evidence pending | Field-level schemas, server-owned state, idempotency, ETag/If-Match, Problem Details, cursor components |
| ADR-0092 | Accepted Definition P-004 evidence protocol / execution pending | Canonical protocol refined by ADR-0132 to DEF-01…DEF-144; original Q1–Q10/C1–C7 mappings preserved |
| ADR-0093 | Accepted Relations P-010 evidence protocol / execution pending | Canonical protocol refined by ADR-0133 to REL-01…REL-160; original RQ1–RQ11/RC1–RC8 mappings preserved |
| ADR-0094 | Accepted REST operational profile / execution pending | RE1 WP REST + compiled descriptor; idempotency/rate/cache state separate; CORS/auth never replace authorization |
| ADR-0095 | Accepted Import runtime physical/recovery profile / execution pending | IR1/PT-D first; IR2/PT-E mandatory; checkpoints/map/journal durable; rollback truthful R0–R3 |
| ADR-0096 | Accepted User Profile runtime authority profile / execution pending | native WP identity/auth authority + Field Storage custom data + minimal security-action state |
| ADR-0097 | Accepted Role/Capability runtime mutation profile / execution pending | native WP authorization authority + Change Plan/effective-cap simulation/anti-lockout/recovery |
| ADR-0098 | Accepted Admin Columns operational profile / execution pending | AC1 whole-request execution plan + batch hydration; real sort/filter; owning API/Policy writes; reject N+1 |
| ADR-0099 | Accepted Dynamic Listings operational profile / execution pending | authorized Query + batched hydration + Component Blueprint SSR; protected pagination/count/cache explicit |
| ADR-0100 | Accepted Backup artifact/container profile / P-013 pending | Manifest-first multipart canonical; SHA-256 stored-byte integrity; CMP0/CMP1; ZIP convenience only |
| ADR-0101 | Accepted OAuth Account-Link evidence protocol / execution pending | OA-01…OA-32 |
| ADR-0102 | Accepted Pro updater TUF evidence protocol / execution pending | TU-01…TU-44 |
| ADR-0103 | Accepted Dashboard Widgets evidence protocol / execution pending | DW-01…DW-36 |
| ADR-0104 | Accepted Admin Menu evidence protocol / execution pending | AM-01…AM-40 |
| ADR-0105 | Accepted Protector evidence protocol / execution pending | PR-01…PR-44 |
| ADR-0106 | Accepted Reset Manager evidence protocol / execution pending | RM-01…RM-48 |
| ADR-0107 | Accepted Watermarker/Media evidence protocol / execution pending | WM-01…WM-48 |
| ADR-0108 | Accepted Frontend Dashboard evidence protocol / execution pending | FD-01…FD-48 |
| ADR-0109 | Accepted Builder Widgets adapter certification protocol / execution pending | BW-01…BW-50 + BC0…BC4 |
| ADR-0110 | Accepted Status Manager evidence protocol / execution pending | SM-01…SM-48 |
| ADR-0111 | Accepted XML-RPC Manager evidence protocol / execution pending | XR-01…XR-48 |
| ADR-0112 | Accepted Settings Page evidence protocol / execution pending | ST-01…ST-48 |
| ADR-0113 | Accepted User Profile evidence protocol / execution pending | UP-01…UP-48 |
| ADR-0114 | Accepted Role & Capability evidence protocol / execution pending | RA-01…RA-48 |
| ADR-0115 | Accepted REST API Builder evidence protocol / execution pending | REST-01…REST-52 |
| ADR-0116 | Accepted Import / Export evidence protocol / execution pending | IM-01…IM-56 |
| ADR-0117 | Accepted Forms Runtime & Submission evidence protocol / execution pending | FM-01…FM-92 |
| ADR-0118 | Accepted Workflow Runtime evidence protocol / execution pending | WF-01…WF-116 |
| ADR-0119 | Accepted JobService/Cron evidence protocol / execution pending | JS-01…JS-106 |
| ADR-0120 | Accepted Notification System evidence protocol / execution pending | NT-01…NT-142 |
| ADR-0121 | Accepted Message & Chat evidence protocol / execution pending | CH-01…CH-142 |
| ADR-0122 | Accepted Webhooks/Connections/Event Inbox evidence protocol / execution pending | WC-01…WC-156 |
| ADR-0123 | Accepted P-001 Compatibility evidence protocol / execution pending | CF-01…CF-112; ADR-0002 remains Proposed |
| ADR-0124 | Accepted P-005 Secrets Vault evidence protocol / execution pending | VT-01…VT-128; V1/V2 final selection open |
| ADR-0125 | Accepted P-002 UI/Design System evidence protocol / execution pending | UI-01…UI-104 |
| ADR-0126 | Accepted P-008 Build Toolchain evidence protocol / execution pending | BT-01…BT-112 |
| ADR-0127 | Accepted P-007 CI/Quality Matrix evidence protocol / execution pending | CI-01…CI-120 |
| ADR-0128 | Accepted P-006 Free↔Pro compatibility evidence protocol / execution pending | FP-01…FP-144 |
| ADR-0129 | Accepted P-012 Membership evidence protocol / execution pending | MBR-01…MBR-160; MB/PC certifications separate |
| ADR-0130 | Accepted P-013 Backup/Restore evidence protocol / execution pending | BK-01…BK-180; C0–C4/V3 certifications separate |
| ADR-0131 | Accepted P-009 Query evidence protocol / execution pending | QRY-01…QRY-168; QP1–QP4 certifications separate |
| ADR-0132 | Accepted P-004 Definition evidence refinement / execution pending | Canonical ADR-0092 protocol refined in place to DEF-01…DEF-144; D1/PT-C remains first benchmark baseline only |
| ADR-0133 | Accepted P-010 Relations evidence refinement / execution pending | Canonical ADR-0093 protocol refined in place to REL-01…REL-160; R1/PT-D first baseline; R2 mandatory; final R/E/PV/DDL open |
| ADR-0134 | Accepted Field Storage / Custom Fields evidence protocol / execution pending | FST-01…FST-176; FS1–FS6 routing and migration/security evidence; runtime/profile certifications 0 |
| ADR-0135 | Accepted Custom Tables evidence protocol / execution pending | CTB-01…CTB-184; CT1–CT3 + CM1–CM4; exact DDL/types/indexes/constraints open |

## Product specification milestone

- `docs/MODULES/OPTION-COVERAGE-MATURITY.md`: **31/31 Exhaustive, 0/31 Authorized**.
- `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`: **31/31 surfaces mapped to explicit Multisite scope behavior**.

## Major fixed evidence protocols

- Compatibility: `docs/QUALITY/P001-COMPATIBILITY-FLOOR-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- UI: `docs/QUALITY/P002-UI-DESIGN-SYSTEM-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- Build: `docs/QUALITY/P008-BUILD-TOOLCHAIN-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- CI: `docs/QUALITY/P007-CI-QUALITY-MATRIX-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- Free↔Pro: `docs/QUALITY/P006-FREE-PRO-COMPATIBILITY-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- Vault: `docs/QUALITY/P005-SECRETS-VAULT-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- Definition: `docs/QUALITY/DEFINITION-P004-EXECUTABLE-EVIDENCE-PROTOCOL.md` — DEF-01…DEF-144
- Query: `docs/QUALITY/P009-QUERY-EXECUTABLE-EVIDENCE-PROTOCOL.md` — QRY-01…QRY-168
- Relations: `docs/QUALITY/RELATIONS-P010-EXECUTABLE-EVIDENCE-PROTOCOL.md` — REL-01…REL-160
- Field Storage: `docs/QUALITY/FIELD-STORAGE-EXECUTABLE-EVIDENCE-PROTOCOL.md` — FST-01…FST-176
- Custom Tables: `docs/QUALITY/CUSTOM-TABLES-EXECUTABLE-EVIDENCE-PROTOCOL.md` — CTB-01…CTB-184
- Membership: `docs/QUALITY/P012-MEMBERSHIP-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- Backup/Restore: `docs/QUALITY/P013-BACKUP-RESTORE-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- Forms: `docs/QUALITY/FORMS-RUNTIME-SUBMISSION-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- Workflow: `docs/QUALITY/WORKFLOW-RUNTIME-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- JobService/Cron: `docs/QUALITY/JOB-SERVICE-ACTION-SCHEDULER-EVIDENCE-PROTOCOL.md`
- Notification: `docs/QUALITY/NOTIFICATION-SYSTEM-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- Message & Chat: `docs/QUALITY/MESSAGE-CHAT-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- Webhooks/Connections/Event Inbox: `docs/QUALITY/WEBHOOKS-CONNECTIONS-EVENT-INBOX-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- OAuth/TUF/DW/AM/PR/RM/WM/FD/BW/SM/XR/ST/UP/RA/REST/IM: corresponding `docs/QUALITY/` protocols.

## Current execution/certification truth

- CF **0/112**; UI **0/104**; JS **0/106**; DEF **0/144**; VT **0/128**; FP **0/144**; CI **0/120**; BT **0/112**.
- QRY **0/168**, QP1–QP4 certifications 0.
- REL **0/160**, final R/E/PV/DDL open.
- WF **0/116**; MBR **0/160**; BK **0/180**.
- FST **0/176**, all Field Storage runtime/profile certifications 0.
- CTB **0/184**, CT1/CT2/CT3 + CM1/CM2/CM3/CM4 certifications 0; exact DDL open.
- FM **0/92**; NT **0/142**; CH **0/142**; WC **0/156**.
- OA **0/32**; TU **0/44**; DW **0/36**; AM **0/40**; PR **0/44**; RM **0/48**; WM **0/48**; FD **0/48**; BW **0/50**; SM **0/48**; XR **0/48**; ST **0/48**; UP **0/48**; RA **0/48**; REST **0/52**; IM **0/56**.
- Membership providers: **4 BE3 / 0 MB-certified**; protected files **0 PC1+**.
- Backup: **34 targets / 0 C-certified / 0 C3 Supported; V3 0**.
- Email: **6 EE3 / 0 ET-certified**; Connection adapters **0 I4/I5**.
- Site Lifecycle **0/40**; Multisite **0 MS1+**; Remote privacy **0/30**.
- CI workflow implementation remains unverified; direct branch reads showed `main` + `planning/master-architecture` unprotected; repository-wide rulesets remain UNKNOWN due 403/plan restriction.

## Current planning work

**`P0-M00-WP19` — Admin Columns operational executable-evidence refinement — SPECIFICATION.**

No executable evidence may run before explicit owner consent.