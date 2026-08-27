# WPEssential Architecture Decision Records

Status: **Phase 0 planning / no development authorization**  
Last synchronized: 2026-08-28

ADRs preserve long-lived product, architecture, security, data, compatibility and distribution decisions. Accepted ADRs are never silently changed; a reversal requires a superseding ADR. Exact implementation profiles can remain evidence-gated even when architecture is Accepted.

**Hard rule:** technical acceptance never grants development permission. `/DEVELOPMENT-CONSENT.md` and ADR-0014 require explicit owner consent before source/build/migration/test/benchmark/provider implementation.

## ADR index

| ADR | Status | Decision |
|---|---|---|
| ADR-0001 | Accepted | WordPress.org Free + separately distributed Pro; Pro trial belongs to Pro entitlement |
| ADR-0002 | Proposed blocker | WP 6.9 / PHP 8.3 current minimum candidates; executable compatibility matrix pending |
| ADR-0003 | Accepted | WordPress Abilities as typed reusable action contract |
| ADR-0004 | Accepted | No standard arbitrary PHP eval or unrestricted destructive raw-SQL primitive |
| ADR-0005 | Proposed blocker | WPE UI wrappers + stable WordPress components/DataViews; Untitled visual reference/compatible MIT only |
| ADR-0006 | Proposed adapter blocker | WPE Job Service abstraction; Action Scheduler preferred adapter candidate; semantics later accepted in ADR-0059/0068/0083 |
| ADR-0007 | Accepted | Pro expiry preserves data and safe deployed runtime; editing/unsafe operations can lock |
| ADR-0008 | Proposed physical-evidence blocker | Definition Repository shape later accepted in ADR-0049/0071/0073; exact DDL/index/locking evidence pending |
| ADR-0009 | Proposed physical-evidence blocker | Secrets Vault hierarchy later accepted in ADR-0048/0085; crypto/runtime evidence pending |
| ADR-0010 | Proposed blocker | Explicit Free↔Pro Platform API compatibility + degraded safe boot |
| ADR-0011 | Proposed blocker | Layered PR/main/nightly/release CI matrix |
| ADR-0012 | Proposed blocker | `@wordpress/build` first candidate; `@wordpress/scripts` fallback; Vite only for proven unmet need |
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
| ADR-0044 | Accepted protocol profile / client pending | Pro automated updates target TUF 1.0-compatible Root/Targets/Snapshot/Timestamp semantics; current PHP-TUF not production-selected |
| ADR-0045 | Accepted security architecture / evidence pending | Protector uses trusted-proxy-aware request gating + shared atomic Rate Limit service + non-authenticating recovery mode |
| ADR-0046 | Accepted media architecture / evidence pending | Watermarker is non-destructive derivative pipeline keyed by source fingerprint + Rule revision + output/engine profile |
| ADR-0047 | Accepted destructive-workflow architecture / evidence pending | Reset uses reviewed Plan + verified restore point + durable journal + recovery-principal invariant + post-health verification |
| ADR-0048 | Accepted security architecture / evidence pending | Vault uses random VRK → per-secret DEKs → external/WP-derived/recovery/KMS key slots; no plaintext fallback |
| ADR-0049 | Accepted architecture / exact DDL evidence pending | Definition Repository relational shape is Definitions + immutable Revisions + revision-aware Dependencies |
| ADR-0050 | Accepted platform architecture / service evidence pending | Support Ticket authority lives on WPE service; local WP is minimal secure client/cache |
| ADR-0051 | Accepted security architecture / adapter evidence pending | Dashboard Widgets render trusted/local structured content; remote response is data, not arbitrary admin HTML/JS |
| ADR-0052 | Accepted security/compatibility architecture / runtime evidence pending | XML-RPC is layered: host/Protector → method registry → authenticated-method policy → native WP auth |
| ADR-0053 | Accepted backup architecture / provider evidence pending | Backup support is protocol-family adapter + provider capability profile with C0–C4 restore-first certification; normal Support label requires C3 |
| ADR-0054 | Accepted platform architecture / service evidence pending | Account/site/entitlement/catalog/support/docs/release domains are separate trust resources; RFC 9457 errors; TUF remains separate update authority |
| ADR-0055 | Accepted integration architecture / provider evidence pending | Connections are certified by adapter + provider + capability + API version using I0–I5; Connected does not imply read/write/event support |
| ADR-0056 | Accepted backup lifecycle / provider evidence pending | Each destination has durable Remote Copy commit/verify/retention/delete/restore states; manifest-last and truthful deletion semantics |
| ADR-0057 | Accepted membership integration architecture / provider evidence pending | Billing integrations emit verified commercial source facts; reconciliation + WPE policy own Enrollment/Entitlement transitions; provider lifecycle certification uses MB0–MB5 |
| ADR-0058 | Accepted email delivery architecture / provider evidence pending | Email submission, receiving-server delivery, failures/complaints/suppression and engagement are separate evidence; provider profiles use ET0–ET5 and never infer inbox/read truth |
| ADR-0059 | Accepted JobService semantics / adapter evidence pending | Backend-neutral Job Type/Schedule/Job/Attempt/Runner policy; at-least-once, explicit idempotency, fairness, resource/concurrency keys, backpressure and cooperative cancellation |
| ADR-0060 | Accepted platform/privacy architecture / service evidence pending | Remote-service transmission is purpose-scoped/minimized; Free activation sends nothing; account link ≠ telemetry consent; diagnostics require separate approval |
| ADR-0061 | Accepted backup identity/capability architecture / provider evidence pending | Semantic `bf.*` family keys are canonical; numeric PF aliases legacy/ambiguous; static evidence never implies C0–C4 certification |
| ADR-0062 | Accepted Membership billing provider profiles / executable evidence pending | Manual/Woo order/Woo Subscriptions/SureCart source-truth profiles are version-scoped; reconciliation remains WPE-owned |
| ADR-0063 | Accepted Email provider profiles / executable evidence pending | wp_mail/SMTP/SES/SendGrid/Mailgun/Postmark profiles normalize only verified provider facts; no inbox/read inference |
| ADR-0064 | Accepted Backup evidence-governance architecture / runtime evidence pending | Provider static research may use versioned SE overlays; overlays never produce C0–C4 certification |
| ADR-0065 | Accepted Backup transport/security profiles / runtime evidence pending | Local/browser/FTP/FTPS/SFTP semantics explicit; FTP legacy/insecure; SFTP host-key trust mandatory |
| ADR-0066 | Accepted Membership provider-version architecture / executable evidence pending | Billing certification scoped by provider/profile/plugin/API/adapter/environment; HPOS first-class compatibility dimension |
| ADR-0067 | Accepted Email provider-version architecture / executable evidence pending | Email certification records transport/API/event schema/security/region/account scope; send/event certification can degrade independently |
| ADR-0068 | Accepted Action Scheduler packaging/coexistence architecture / P-003 pending | WPE Platform/Free owns one bundled candidate if selected; Pro/modules do not duplicate; only JobService adapter calls AS |
| ADR-0069 | Accepted Multisite logical/security architecture / physical evidence pending | Every scope-aware resource has explicit site/network coordinates; site scope default; target-site capability + WPE Policy required |
| ADR-0070 | Accepted commercial/platform architecture / service evidence pending | Product licensing uses opaque installation/network/site-allocation identities and explicit environment classes; clone/migration/transfer reconcile safely |
| ADR-0071 | Accepted Multisite physical-topology paper architecture / exact DDL evidence pending | WPE uses PT-A…PT-F storage classes; control-plane prefers scoped PT-C; high-volume runtime can use PT-D/PT-E by evidence |
| ADR-0072 | Accepted Product License remote-resource paper architecture / service evidence pending | Account/Contract/Installation/Network/Site Allocation/Signed Entitlement separate; retries/concurrency/unknown outcomes reconcile safely |
| ADR-0073 | Accepted Definition Repository PT-C benchmark baseline / exact DDL evidence pending | D1 uses numeric IDs, textual UUID, explicit scope, bounded identity keys, immutable text payload, minimal indexes |
| ADR-0074 | Accepted Relations physical benchmark baseline / P-010 pending | R1 PT-D shared scoped universal edge table first; R2 PT-E mandatory; R3 per-relation exceptional |
| ADR-0075 | Accepted Multisite lifecycle architecture / runtime evidence pending | Site Lifecycle Coordinator plans/provisions/drains/reconciles create/update/uninitialize/delete/clone/transfer across PT-C/PT-D/PT-E/PT-F |
| ADR-0076 | Accepted Product License HTTP/OpenAPI paper architecture / service evidence pending | Resource-oriented versioned API; signed entitlement separate; ETag/If-Match, retry-safe idempotency, Problem Details, bounded pagination |
| ADR-0077 | Accepted Forms & Chat benchmark baselines / physical evidence pending | Forms FRT1/PT-D and Chat CRT1/PT-D first; FRT2/CRT2 PT-E mandatory comparisons |
| ADR-0078 | Accepted Membership benchmark baseline / P-012 pending | M1/PT-D first; M2/PT-E mandatory; Enrollment authoritative, Entitlements derived, principal access generation supports revoke-safe cache/version semantics |
| ADR-0079 | Accepted Notification/Email operational benchmark baseline / provider evidence pending | NE1/PT-D first; NE2/PT-E mandatory; Occurrence/Recipient/Delivery/Attempt/Evidence truth boundaries explicit |
| ADR-0080 | Accepted Event Inbox benchmark baseline / provider evidence pending | EI1/PT-D first; EI2/PT-E mandatory; trusted endpoint/Connection determines scope; dedupe + consumer idempotency required |
| ADR-0081 | Accepted Audit PT-D retention/integrity profile / exact evidence pending | AU1/PT-D favored; Audit separate from domain history/diagnostics; append-only app semantics; local DB not claimed tamper-proof |
| ADR-0082 | Accepted Workflow benchmark baseline / P-011 pending | WF1/PT-D shared scoped Workflow Runtime first; WF2/PT-E mandatory; Run/Step/Wait/Approval durable truth remains separate from Job backend |
| ADR-0083 | Accepted JobService physical mapping baseline / P-003 pending | J1 PT-D Jobs+Attempts first; J2 PT-C current + PT-D history mandatory; J3 PT-C low-volume control; backend rows never WPE truth |
| ADR-0084 | Accepted Backup Remote Copy physical baseline / P-013 pending | BR1/PT-D first; BR2 PT-C-current + PT-D history mandatory; BR3 PT-E isolation comparison; commit/verify/delete truth preserved |
| ADR-0085 | Accepted Vault PT-C physical envelope profile / P-005 pending | V1/PT-C favored shared scoped Vault; V2 per-site + network Vault mandatory; Secret versions/VRK generations/key slots/use grants separated; no plaintext fallback |
| ADR-0086 | Accepted Query compiler benchmark baseline / P-009 pending | QP1 WordPress-native first; QP2 Custom Table + QP3 Relations-assisted required for owned workloads; QP4 remote separately certified; security/cache isolation override speed |
| ADR-0087 | Accepted Field Storage physical routing profile / evidence pending | FS1 native WP default; FS2 typed Custom Table escalation; FS3 child rows; FS4 Relations; FS5 Vault refs; no universal field store |
| ADR-0088 | Accepted Custom Tables PT-D/PT-E physical baseline / exact DDL evidence pending | CT1/PT-E first for site-owned tables; CT2/PT-D mandatory large-network comparison; CT3 only for genuinely network-owned data |
| ADR-0089 | Accepted Settings PT-A/PT-B runtime profile / evidence pending | ST1/PT-A grouped site document; ST2/PT-B network document; ST3 inheritance; non-autoload default; stale edits require visible conflict semantics |
| ADR-0090 | Accepted Membership protected-file delivery profile / security evidence pending | PD1 private local correctness baseline; PD2 accelerated; PD3 private object signed delivery; PC0–PC4 origin-bypass/authorization certification |
| ADR-0091 | Accepted Product License API component schema profile / service evidence pending | Field-level resource schemas, server-owned state, Idempotency-Key, ETag/If-Match, Problem Details and cursor components fixed on paper |
| ADR-0092 | Accepted Definition P-004 evidence protocol / execution pending | Deterministic Definition datasets, Q1–Q10 workloads, C1–C7 concurrency races, query-plan/storage/migration/scope-security gates fixed before benchmarking |
| ADR-0093 | Accepted Relations P-010 evidence protocol / execution pending | RF datasets, RQ1–RQ11 reads, RC1–RC8 cardinality races, endpoint/pivot subtests, N+1/lifecycle/wrong-scope gates fixed before benchmarking |
| ADR-0094 | Accepted REST operational runtime profile / executable evidence pending | RE1 WP REST + compiled descriptor first; idempotency/rate/cache operational state separate; CORS/auth/projection never replace authorization |
| ADR-0095 | Accepted Import runtime physical/recovery profile / executable evidence pending | IR1/PT-D first; IR2/PT-E mandatory; Run/Checkpoint/Identity Map/Journal durable truth; crash-after-write reconciles before retry; rollback truthful R0–R3 |
| ADR-0096 | Accepted User Profile runtime authority profile / executable evidence pending | UP1 native WP identity/auth authority; UP2 Field Storage custom data; UP3 minimal security-action state only when needed; protected meta/roles/credentials never generic fields |
| ADR-0097 | Accepted Role/Capability runtime mutation profile / executable evidence pending | RA1 native WP authorization authority; Change Plan/effective-cap simulation/anti-lockout/recovery around native mutation; no parallel auth DB or anonymous backdoor |
| ADR-0098 | Accepted Admin Columns operational profile / executable evidence pending | AC1 whole-request Column Execution Plan + batch hydration; real sort/filter before pagination; inline writes use owning API/Policy; N+1 per-row work rejected |
| ADR-0099 | Accepted Dynamic Listings operational profile / executable evidence pending | DL1 authorization-aware Query + batched hydration + Component Blueprint SSR; protected pagination/count/cache semantics explicit |
| ADR-0100 | Accepted Backup artifact/container profile / P-013 pending | Manifest-first multipart remains canonical; SHA-256 stored-byte integrity; CMP0 fallback/CMP1 gzip comparison; ZIP convenience only; FR/DB/chunk profiles evidence-gated |
| ADR-0101 | Accepted OAuth Account-Link evidence protocol / execution pending | OA-01…OA-32 fix PKCE S256, replay, mix-up, refresh rotation, clone/privacy/outage evidence before account-link production readiness |
| ADR-0102 | Accepted Pro updater TUF evidence protocol / execution pending | TU-01…TU-44 fix Root/Targets/Snapshot/Timestamp, rollback/freeze/key-custody/package-staging evidence before automated Pro updates |
| ADR-0103 | Accepted Dashboard Widgets evidence protocol / execution pending | DW-01…DW-36 fix Site/Network registration, content trust, XSS/SSRF, cache, async/iframe/assets/Multisite evidence |
| ADR-0104 | Accepted Admin Menu evidence protocol / execution pending | AM-01…AM-40 fix menu composition/conflict/direct-URL/safe-mode/Multisite/performance evidence |
| ADR-0105 | Accepted Protector evidence protocol / execution pending | PR-01…PR-44 fix trusted-proxy/rate/login/XML-RPC/REST/path/header/recovery/Multisite/privacy evidence |
| ADR-0106 | Accepted Reset Manager evidence protocol / execution pending | RM-01…RM-48 fix restore-point/journal/crash/duplicate-Job/recovery/post-health/Multisite evidence |
| ADR-0107 | Accepted Watermarker/Media evidence protocol / execution pending | WM-01…WM-48 fix source immutability/image capability/format/SVG/Job/offload/private-media/Multisite evidence |
| ADR-0108 | Accepted Frontend Dashboard evidence protocol / execution pending | FD-01…FD-48 fix route/IDOR/navigation/cache/assets/permalink/SEO/accessibility/Multisite evidence |
| ADR-0109 | Accepted Builder Widgets adapter certification protocol / execution pending | BW-01…BW-50 + BC0…BC4 fix builder identity/registration/render/dynamic-data/assets/version/upgrade evidence |
| ADR-0110 | Accepted Status Manager evidence protocol / execution pending | SM-01…SM-48 fix Post Status/domain-state migration/concurrency/history/Workflow/Job/Multisite evidence |
| ADR-0111 | Accepted XML-RPC Manager evidence protocol / execution pending | XR-01…XR-48 fix layered endpoint/method/auth/rate/parser/compatibility/logging/Multisite evidence |

## Product specification milestone

- `docs/MODULES/OPTION-COVERAGE-MATURITY.md`: **31/31 Exhaustive, 0/31 Authorized**.
- `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`: **31/31 surfaces mapped to explicit Multisite scope behavior**.

## Major supporting architecture/security docs

- Definition/Relations evidence: corresponding `docs/ARCHITECTURE/` + `docs/QUALITY/` P-004/P-010 files.
- Query/Fields/Custom Tables/Settings/Admin Columns/Listings: corresponding operational/physical profiles under `docs/ARCHITECTURE/`.
- Membership/Protected Files/Workflow/Job/Notification/Email/Event Inbox/Audit/REST/Import/Backup/Vault: corresponding architecture/security profiles.
- User Profile: `docs/SECURITY/USER-PROFILE-RUNTIME-AUTHORITY-EVIDENCE-PROFILE.md`.
- Role & Capability: `docs/SECURITY/ROLE-CAPABILITY-RUNTIME-MUTATION-EVIDENCE-PROFILE.md`.
- OAuth evidence: `docs/QUALITY/OAUTH-ACCOUNT-LINK-EXECUTABLE-EVIDENCE-PROTOCOL.md`.
- TUF updater evidence: `docs/QUALITY/PRO-UPDATE-TUF-EXECUTABLE-EVIDENCE-PROTOCOL.md`.
- Dashboard Widgets/Admin Menu/Protector/Reset/Watermarker evidence: corresponding ADR-0103…0107 `docs/QUALITY/` protocols.
- Frontend Dashboard evidence: `docs/QUALITY/FRONTEND-DASHBOARD-EXECUTABLE-EVIDENCE-PROTOCOL.md`.
- Builder adapter evidence: `docs/QUALITY/BUILDER-WIDGETS-ADAPTER-CERTIFICATION-EVIDENCE-PROTOCOL.md`.
- Status Manager evidence: `docs/QUALITY/STATUS-MANAGER-EXECUTABLE-EVIDENCE-PROTOCOL.md`.
- XML-RPC evidence: `docs/QUALITY/XML-RPC-MANAGER-EXECUTABLE-EVIDENCE-PROTOCOL.md`.
- Product License remote/API: corresponding files under `docs/PLATFORM/`.

## Remaining evidence blockers

P-001…P-013 remain executable gates. ADR-0101…0111 add bounded surface-specific evidence gates but do not authorize or verify any runtime, network/service/update, builder, status or XML-RPC execution.

Current certification/evidence remains:
- Membership: **0 MB-certified**;
- Email: **0 ET-certified**;
- Event adapters: **0 I4/I5**;
- Backup: **0 C-certified / 34 targets**;
- Protected files: **0 PC1+**;
- Multisite: **0 MS1+**;
- Site lifecycle: **0/40 fixtures**;
- Remote privacy: **0/30 fixtures**;
- OAuth Account Link: **0/32 OA fixtures**;
- Pro updater TUF: **0/44 TU fixtures**;
- Dashboard Widgets: **0/36 DW fixtures**;
- Admin Menu: **0/40 AM fixtures**;
- Protector: **0/44 PR fixtures**;
- Reset Manager: **0/48 RM fixtures**;
- Watermarker / Media: **0/48 WM fixtures**;
- Frontend Dashboard: **0/48 FD fixtures**;
- Builder Widgets adapters: **0/50 BW fixtures / 0 runtime certifications**;
- Status Manager: **0/48 SM fixtures**;
- XML-RPC Manager: **0/48 XR fixtures**;
- Product License API/service: **0 fixtures**.

No executable evidence may run before explicit owner consent.