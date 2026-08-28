# WPEssential Architecture Decision Records

Status: **Phase 0 planning / no development authorization**  
Last synchronized: 2026-08-28

ADRs preserve long-lived product, architecture, security, data, compatibility and distribution decisions. Accepted ADRs are never silently changed; a reversal requires a superseding ADR. Exact implementation profiles can remain evidence-gated even when architecture is Accepted.

**Hard rule:** technical acceptance never grants development permission. `/DEVELOPMENT-CONSENT.md` and ADR-0014 require explicit owner consent before source/build/migration/test/benchmark/provider implementation.

> ADR-0069 previously appeared twice as a summary row in this index; that duplicate summary was normalized without changing the ADR source or semantics.

## ADR index

| ADR | Status | Decision |
|---|---|---|
| ADR-0001 | Accepted | WordPress.org Free + separately distributed Pro; Pro trial belongs to Pro entitlement |
| ADR-0002 | Proposed blocker | WP 6.9 / PHP 8.3 minimum candidates; WP 7.1 planning reference; DB floor evidence-gated; CF pending |
| ADR-0003 | Accepted | WordPress Abilities as typed reusable action contract |
| ADR-0004 | Accepted | No standard arbitrary PHP eval or unrestricted destructive raw-SQL primitive |
| ADR-0005 | Proposed blocker | WPE UI wrappers + compatible WordPress public primitives; WordPress-provided React; minimum WP cannot hard-depend on newer-only UI capability |
| ADR-0006 | Proposed adapter blocker | WPE Job Service abstraction; Action Scheduler preferred adapter candidate |
| ADR-0007 | Accepted | Pro expiry preserves data and safe deployed runtime; editing/unsafe operations can lock |
| ADR-0008 | Proposed physical blocker | Definition Repository shape later accepted; exact DDL/index/locking evidence pending |
| ADR-0009 | Proposed physical blocker | Secrets Vault hierarchy later accepted; exact crypto/runtime evidence pending |
| ADR-0010 | Proposed blocker | Free↔Pro Platform API/schema/package compatibility + safe degraded boot; FP protocol in ADR-0128 |
| ADR-0011 | Proposed blocker | Layered provider-neutral CI; fixed CI protocol in ADR-0127 |
| ADR-0012 | Proposed blocker | `@wordpress/build` first candidate; scripts comparison/fallback; Vite only for proven unmet need |
| ADR-0013 | Accepted | Role ≠ Membership ≠ billing Subscription/Purchase ≠ Entitlement |
| ADR-0014 | Accepted governance | Production development/executable spikes require explicit owner consent; `continue` never authorizes code |
| ADR-0015 | Accepted | Membership outer security denial cannot be bypassed; specificity + same-specificity deny-wins |
| ADR-0016 | Accepted | Enrollment states pending/trialing/active/grace/paused/expired/revoked; cancellation is intent |
| ADR-0017 | Accepted architecture | Product entitlement signed, site-bound and freshness-aware; outage ≠ expiry |
| ADR-0018 | Accepted architecture | Pro updates use signed anti-rollback/freeze/key-rotation trust; Free is not external Pro updater |
| ADR-0019 | Accepted | Draft Plan edits do not alter live access; published changes choose follow-current/grandfather/scheduled |
| ADR-0020 | Accepted | Membership team roles separate from WP roles; role sync optional/off by default and provenance-safe |
| ADR-0021 | Accepted architecture | Per-backup DEK + independent disaster-recovery wrapping; WP salts not sole recovery root |
| ADR-0022 | Accepted architecture | Native WP meta/options where natural; Custom Tables for scale/constraints; Relations for relationships; Vault for secrets |
| ADR-0023 | Accepted architecture | Desired schema + typed Migration Plan; `dbDelta()` is one compiler tool, not universal migration language |
| ADR-0024 | Accepted defaults | Membership category-level retention; minimize raw provider/log data; detailed download/IP logging off by default |
| ADR-0025 | Accepted architecture | Form Definitions separate from Entries; pinned revision + typed values + projections |
| ADR-0026 | Accepted architecture | Notification occurrence, recipient/read state and channel delivery attempts are separate domains |
| ADR-0027 | Accepted architecture | Chat runtime independent of transport; private assets; search reauthorizes; Membership/team revoke affects access |
| ADR-0028 | Accepted architecture | REST definitions compile to validated runtime descriptors using WP REST + Policy + Query/Data Source/Abilities |
| ADR-0029 | Accepted architecture | Email Definition → compiled descriptor → authorized context → Email IR → HTML/plaintext → delivery attempt |
| ADR-0030 | Accepted security | Profile data, identity changes, credentials, authorization and protected user internals are separate action classes |
| ADR-0031 | Accepted architecture | Dashboard Definition → compiled route/component descriptor → server route resolution → Policy → renderer |
| ADR-0032 | Accepted security | Role mutations require impact/anti-lockout recovery invariant; native/CLI break-glass, no anonymous backdoor |
| ADR-0033 | Accepted logical architecture | Backup canonical model is manifest-first independently verifiable multipart bundle |
| ADR-0034 | Accepted security profile | Account link uses fixed WPE OAuth callback + one-time site-bound artifact + PKCE S256; Device flow fallback |
| ADR-0035 | Accepted architecture | One shared Component Blueprint for Builder Widgets, Dashboards and Listings; builders are adapters |
| ADR-0036 | Accepted architecture | Settings Definition separate from site/network scoped runtime documents; Vault-backed secrets; explicit inheritance |
| ADR-0037 | Accepted architecture | Admin Menu uses runtime discovery + stable transforms; hiding ≠ authorization; safe mode restores native navigation |
| ADR-0038 | Accepted architecture | Status Manager separates WP Post Status adapter from generic domain state machine |
| ADR-0039 | Accepted architecture | Listings compile to authorized Query → visible result set → Component Blueprint SSR; cache safety dependency-derived |
| ADR-0040 | Accepted security | Central Safe HTTP + verified Webhook Gateway + durable normalized Event Inbox |
| ADR-0041 | Accepted architecture | Imports use reviewed Plan/Dry Run fingerprint + durable checkpoints + identity map + journal + truthful rollback classes |
| ADR-0042 | Accepted crypto / evidence pending | Product entitlement uses Ed25519 + RFC 8785 JCS + domain separation + root-authorized signer keysets |
| ADR-0043 | Accepted crypto / evidence pending | Backup encryption uses Sodium secretstream XChaCha20-Poly1305, XChaCha20 wrapping and Argon2id; ext-sodium required |
| ADR-0044 | Accepted protocol / client pending | Pro automated updates target TUF 1.0-compatible Root/Targets/Snapshot/Timestamp semantics |
| ADR-0045 | Accepted security / evidence pending | Protector uses trusted-proxy-aware request gating + shared atomic Rate Limit service + non-authenticating recovery mode |
| ADR-0046 | Accepted media / evidence pending | Watermarker is non-destructive derivative pipeline keyed by source fingerprint + Rule revision + engine/output profile |
| ADR-0047 | Accepted destructive-workflow / evidence pending | Reset uses reviewed Plan + verified restore point + journal + recovery-principal invariant + post-health verification |
| ADR-0048 | Accepted security / evidence pending | Vault uses random VRK → per-secret DEKs → external/WP-derived/recovery/KMS key slots; no plaintext fallback |
| ADR-0049 | Accepted architecture / DDL pending | Definition Repository = Definitions + immutable Revisions + revision-aware Dependencies |
| ADR-0050 | Accepted platform / service evidence pending | Support Ticket authority lives on WPE service; local WP is minimal secure client/cache |
| ADR-0051 | Accepted security / adapter evidence pending | Dashboard Widgets render trusted/local structured content; remote response is data, not arbitrary admin HTML/JS |
| ADR-0052 | Accepted security/compatibility | XML-RPC layered host/Protector → method registry → method policy → native WP auth |
| ADR-0053 | Accepted backup/provider architecture | Backup support uses protocol-family adapters + C0–C4 restore-first certification; normal Support requires C3 |
| ADR-0054 | Accepted platform/service architecture | Account/site/entitlement/catalog/support/docs/release domains separate; TUF separate update authority |
| ADR-0055 | Accepted integration architecture | Connections certified by adapter + provider + capability + API version using I0–I5 |
| ADR-0056 | Accepted backup lifecycle | Remote Copy commit/verify/retention/delete/restore states; manifest-last and truthful deletion |
| ADR-0057 | Accepted membership integration | Billing integrations emit commercial source facts; reconciliation + WPE policy own Enrollment/Entitlement; MB0–MB5 |
| ADR-0058 | Accepted email delivery | Submission, receiving-server delivery, complaints/suppression and engagement remain separate; ET0–ET5 |
| ADR-0059 | Accepted JobService semantics | Backend-neutral Job/Schedule/Attempt/Runner; at-least-once, idempotency, fairness, resource keys, backpressure, cancellation |
| ADR-0060 | Accepted platform/privacy | Remote transmission purpose-scoped/minimized; Free activation sends nothing; account link ≠ telemetry consent |
| ADR-0061 | Accepted backup identity | Semantic `bf.*` family keys canonical; static evidence never implies C0–C4 certification |
| ADR-0062 | Accepted Membership provider profiles | Manual/Woo order/Woo Subscriptions/SureCart source-truth profiles; reconciliation WPE-owned |
| ADR-0063 | Accepted Email provider profiles | wp_mail/SMTP/SES/SendGrid/Mailgun/Postmark normalize verified provider facts only |
| ADR-0064 | Accepted Backup evidence governance | Static SE overlays never produce C0–C4 certification |
| ADR-0065 | Accepted Backup transport/security | Local/browser/FTP/FTPS/SFTP explicit; FTP insecure legacy; SFTP host-key trust mandatory |
| ADR-0066 | Accepted Membership provider-version | Certification scoped by provider/profile/plugin/API/adapter/environment; HPOS first-class |
| ADR-0067 | Accepted Email provider-version | Certification records transport/API/event schema/security/region/account scope |
| ADR-0068 | Accepted Action Scheduler packaging | Platform/Free owns one bundled candidate if selected; modules call JobService adapter only |
| ADR-0069 | Accepted Multisite logical/security | Explicit site/network ownership + target authorization mandatory; physical topology evidence-gated |
| ADR-0070 | Accepted commercial/platform | Opaque installation/network/site-allocation identities + explicit environment classes |
| ADR-0071 | Accepted Multisite topology | PT-A…PT-F storage classes; control plane favors scoped PT-C; high-volume PT-D/PT-E by evidence |
| ADR-0072 | Accepted Product License resource model | Account/Contract/Installation/Network/Site Allocation/Signed Entitlement separate; unknown outcomes reconcile |
| ADR-0073 | Accepted Definition PT-C baseline | D1 numeric IDs + textual UUID + explicit scope + immutable payload + minimal indexes |
| ADR-0074 | Accepted Relations baseline | R1 PT-D first; R2 PT-E mandatory; R3 exceptional |
| ADR-0075 | Accepted Multisite lifecycle | Site Lifecycle Coordinator manages create/update/uninitialize/delete/clone/transfer across storage classes |
| ADR-0076 | Accepted Product License HTTP/OpenAPI | Resource-oriented API; signed entitlement separate; ETag/If-Match, idempotency, Problem Details, bounded pagination |
| ADR-0077 | Accepted Forms/Chat baselines | Forms FRT1/PT-D and Chat CRT1/PT-D first; PT-E mandatory comparisons |
| ADR-0078 | Accepted Membership baseline | M1/PT-D first; M2/PT-E mandatory; Enrollment authoritative, Entitlements derived, revoke-safe generation |
| ADR-0079 | Accepted Notification/Email baseline | NE1/PT-D first; NE2/PT-E mandatory; truth boundaries explicit |
| ADR-0080 | Accepted Event Inbox baseline | EI1/PT-D first; EI2/PT-E mandatory; trusted endpoint determines scope; dedupe + consumer idempotency |
| ADR-0081 | Accepted Audit profile | AU1/PT-D favored; Audit separate from domain history/diagnostics; local DB not claimed tamper-proof |
| ADR-0082 | Accepted Workflow baseline | WF1/PT-D first; WF2/PT-E mandatory; Workflow durable truth separate from Job backend |
| ADR-0083 | Accepted JobService physical baseline | J1 PT-D; J2 PT-C current + PT-D history; J3 PT-C low volume; backend rows not WPE truth |
| ADR-0084 | Accepted Backup Remote Copy baseline | BR1/PT-D first; BR2 PT-C-current + PT-D history; BR3 PT-E |
| ADR-0085 | Accepted Vault PT-C profile | V1/PT-C favored; V2 per-site + network Vault mandatory; no plaintext fallback |
| ADR-0086 | Accepted Query compiler baseline | QP1 WP-native first; QP2 Custom Table + QP3 Relations required; QP4 remote separately certified |
| ADR-0087 | Accepted Field Storage routing | FS1 WP native; FS2 Custom Table; FS3 child rows; FS4 Relations; FS5 Vault refs |
| ADR-0088 | Accepted Custom Tables baseline | CT1/PT-E first site-owned; CT2/PT-D mandatory; CT3 network-owned only |
| ADR-0089 | Accepted Settings runtime profile | ST1 site doc; ST2 network doc; ST3 inheritance; non-autoload default; stale edits conflict visibly |
| ADR-0090 | Accepted Membership protected-file profile | PD1 private local; PD2 accelerated; PD3 private object signed delivery; PC0–PC4 |
| ADR-0091 | Accepted Product License API schemas | Field schemas, server-owned state, idempotency, ETag/If-Match, Problem Details, cursors |
| ADR-0092 | Accepted Definition evidence | Canonical protocol later refined to DEF-01…DEF-144 |
| ADR-0093 | Accepted Relations evidence | Canonical protocol later refined to REL-01…REL-160 |
| ADR-0094 | Accepted REST operational profile | RE1 WP REST + compiled descriptor; idempotency/rate/cache separate; CORS/auth ≠ authorization |
| ADR-0095 | Accepted Import runtime profile | IR1/PT-D first; IR2/PT-E mandatory; checkpoints/map/journal durable; rollback R0–R3 |
| ADR-0096 | Accepted User Profile authority profile | Native WP identity/auth + Field Storage custom data + minimal security-action state |
| ADR-0097 | Accepted Role/Capability mutation profile | Native WP authority + Change Plan/effective-cap simulation/anti-lockout/recovery |
| ADR-0098 | Accepted Admin Columns profile | Whole-request plan + batch hydration; real sort/filter; owning API/Policy writes; reject N+1 |
| ADR-0099 | Accepted Dynamic Listings profile | Authorized Query + batched hydration + Blueprint SSR; protected pagination/count/cache explicit |
| ADR-0100 | Accepted Backup container | Manifest-first multipart; SHA-256 stored-byte integrity; CMP0/CMP1; ZIP convenience only |
| ADR-0101 | Accepted OAuth Account-Link evidence | OA-01…OA-32 |
| ADR-0102 | Accepted Pro updater TUF evidence | TU-01…TU-44 |
| ADR-0103 | Accepted Dashboard Widgets evidence | DW-01…DW-36 |
| ADR-0104 | Accepted Admin Menu evidence | AM-01…AM-40 |
| ADR-0105 | Accepted Protector evidence | PR-01…PR-44; refined by ADR-0159 to PR-01…PR-176 |
| ADR-0106 | Accepted Reset Manager evidence | RM-01…RM-48; refined by ADR-0161 to RM-01…RM-176 |
| ADR-0107 | Accepted Watermarker/Media evidence | WM-01…WM-48; refined by ADR-0168 to WM-01…WM-176 |
| ADR-0108 | Accepted Frontend Dashboard evidence | FD-01…FD-48; refined by ADR-0163 to FD-01…FD-176 |
| ADR-0109 | Accepted Builder Widgets adapter evidence | BW-01…BW-50 + BC0…BC4; refined by ADR-0167 to BW-01…BW-176 |
| ADR-0110 | Accepted Status Manager evidence | SM-01…SM-48; refined by ADR-0166 to SM-01…SM-176 |
| ADR-0111 | Accepted XML-RPC evidence | XR-01…XR-48; refined by ADR-0160 to XR-01…XR-176 |
| ADR-0112 | Accepted Settings Page evidence | ST-01…ST-48; refined by ADR-0162 to ST-01…ST-176 |
| ADR-0113 | Accepted User Profile evidence | Originally UP-01…UP-48; refined by ADR-0158 to UP-01…UP-176 |
| ADR-0114 | Accepted Role & Capability evidence | Originally RA-01…RA-48; refined by ADR-0157 to RA-01…RA-176 |
| ADR-0115 | Accepted REST API Builder evidence | Originally REST-01…REST-52; refined by ADR-0155 to REST-01…REST-176 |
| ADR-0116 | Accepted Import / Export evidence | Originally IM-01…IM-56; refined by ADR-0156 to IM-01…IM-176 |
| ADR-0117 | Accepted Forms Runtime evidence | FM-01…FM-92 |
| ADR-0118 | Accepted Workflow Runtime evidence | WF-01…WF-116 |
| ADR-0119 | Accepted JobService/Cron evidence | JS-01…JS-106 |
| ADR-0120 | Accepted Notification evidence | NT-01…NT-142 |
| ADR-0121 | Accepted Message & Chat evidence | CH-01…CH-142 |
| ADR-0122 | Accepted Webhooks/Connections/Event Inbox evidence | WC-01…WC-156 |
| ADR-0123 | Accepted Compatibility evidence | CF-01…CF-112; ADR-0002 remains Proposed |
| ADR-0124 | Accepted Vault evidence | VT-01…VT-128; V1/V2 final selection open |
| ADR-0125 | Accepted UI evidence | UI-01…UI-104 |
| ADR-0126 | Accepted Build evidence | BT-01…BT-112 |
| ADR-0127 | Accepted CI evidence | CI-01…CI-120 |
| ADR-0128 | Accepted Free↔Pro evidence | FP-01…FP-144 |
| ADR-0129 | Accepted Membership evidence | MBR-01…MBR-160; MB/PC separate |
| ADR-0130 | Accepted Backup/Restore evidence | BK-01…BK-180; C0–C4/V3 separate |
| ADR-0131 | Accepted Query evidence | QRY-01…QRY-168; QP1–QP4 separate |
| ADR-0132 | Accepted Definition refinement | DEF-01…DEF-144; D1/PT-C first baseline only |
| ADR-0133 | Accepted Relations refinement | REL-01…REL-160; R1 first/R2 mandatory; final R/E/PV/DDL open |
| ADR-0134 | Accepted Field Storage evidence | FST-01…FST-176 |
| ADR-0135 | Accepted Custom Tables evidence | CTB-01…CTB-184; CT1–CT3 + CM1–CM4; DDL open |
| ADR-0136 | Accepted Admin Columns evidence | AC-01…AC-176 |
| ADR-0137 | Accepted Dynamic Listings evidence | DL-01…DL-176 |
| ADR-0138 | Accepted Free CPT + Taxonomy evidence | CPTX-01…CPTX-176 |
| ADR-0139 | Accepted Emails Builder evidence | EBR-01…EBR-176; ET separate |
| ADR-0140 | Accepted Platform surfaces evidence | PLT-01…PLT-176 |
| ADR-0141 | Accepted Multisite/Lifecycle refinement | MSI-01…MSI-160 + LC-01…LC-96 |
| ADR-0142 | Accepted Audit & Observability evidence | AUD-01…AUD-176 |
| ADR-0143 | Accepted Kernel/Policy/Abilities/Events/SDK evidence | KPA-01…KPA-176 |
| ADR-0144 | Accepted Local Privacy/Data Lifecycle evidence | PDL-01…PDL-176; RS separate |
| ADR-0145 | Accepted Error Taxonomy/Failure UX evidence | ERR-01…ERR-176 |
| ADR-0146 | Accepted Component Blueprint Core evidence | CBP-01…CBP-176; BW/BC separate |
| ADR-0147 | Accepted Contract Versioning/Deprecation evidence | VER-01…VER-176 |
| ADR-0148 | Accepted Module Lifecycle/Recovery evidence | MLC-01…MLC-176 |
| ADR-0149 | Accepted Entity/Data Source Registry evidence | DSR-01…DSR-176 |
| ADR-0150 | Accepted Asset Registry/Scoped Loader evidence | ASR-01…ASR-176 |
| ADR-0151 | Accepted Conditional Logic evidence | CLG-01…CLG-176 |
| ADR-0152 | Accepted Dynamic Value Resolver evidence | DVR-01…DVR-176 |
| ADR-0153 | Accepted Shared Rate Limit/Abuse evidence | RLT-01…RLT-176; consumer certification separate |
| ADR-0154 | Accepted Shared Cache/Invalidation evidence | CAC-01…CAC-176; consumer certification separate |
| ADR-0155 | Accepted REST API Builder refinement | REST-01…REST-176; original 01…52 preserved |
| ADR-0156 | Accepted Import/Export refinement | IM-01…IM-176; original 01…56 preserved |
| ADR-0157 | Accepted Role & Capability refinement | RA-01…RA-176; original 01…48 preserved |
| ADR-0158 | Accepted User Profile refinement | UP-01…UP-176; original 01…48 preserved |
| ADR-0159 | Accepted Protector refinement | PR-01…PR-176; original 01…44 preserved |
| ADR-0160 | Accepted XML-RPC Manager refinement | XR-01…XR-176; original 01…48 preserved |
| ADR-0161 | Accepted Reset Manager refinement | RM-01…RM-176; original 01…48 preserved |
| ADR-0162 | Accepted Settings Page refinement | ST-01…ST-176; original 01…48 preserved |
| ADR-0163 | Accepted Frontend Dashboard refinement | FD-01…FD-176; original 01…48 preserved |
| ADR-0164 | Accepted Admin Menu refinement | AM-01…AM-176; original 01…40 preserved |
| ADR-0165 | Accepted Dashboard Widgets refinement | DW-01…DW-176; original 01…36 preserved |
| ADR-0166 | Accepted Status Manager refinement | SM-01…SM-176; original 01…48 preserved |
| ADR-0167 | Accepted Builder Widgets adapter refinement | BW-01…BW-176; BC0…BC4 remain separate certifications |
| ADR-0168 | Accepted Watermarker / Media refinement | WM-01…WM-176; original 01…48 preserved |
| ADR-0169 | Accepted Pro Updater TUF refinement | TU-01…TU-176; original 01…44 preserved |
| ADR-0170 | Accepted OAuth Account-Link refinement | OA-01…OA-176; original 01…32 preserved |
| ADR-0171 | Accepted Remote Service Privacy / Retention refinement | RS-01…RS-176; original 01…30 preserved |
| ADR-0172 | Accepted Email Transport / Provider Certification refinement | ET-F001…ET-F176; ET0–ET5 preserved; 6 EE3 / 0 ET-certified |
| ADR-0173 | Accepted Membership Billing Provider Certification refinement | MB-F001…MB-F176; MB0–MB5 preserved; 4 BE3 / 0 MB-certified |
| ADR-0174 | Accepted Membership Protected File Delivery Certification refinement | PC-F001…PC-F176; PC0–PC4 + PD1–PD4 preserved; 0 PC1+ runtime-certified |

## Product specification milestone

- `docs/MODULES/OPTION-COVERAGE-MATURITY.md`: **31/31 Exhaustive, 0/31 Authorized**.
- `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`: **31/31 surfaces mapped**.

## Major fixed evidence protocols / current truth

- CF **0/112**; UI **0/104**; JS **0/106**; DEF **0/144**; VT **0/128**; FP **0/144**; CI **0/120**; BT **0/112**.
- QRY **0/168**; REL **0/160**; WF **0/116**; MBR **0/160**; BK **0/180**.
- FST **0/176**; CTB **0/184**; AC **0/176**; DL **0/176**; CPTX **0/176**; EBR **0/176**; PLT **0/176**.
- MSI **0/160**; LC **0/96**; AUD **0/176**; KPA **0/176**; PDL **0/176**; ERR **0/176**; CBP **0/176**.
- VER **0/176**; MLC **0/176**; DSR **0/176**; ASR **0/176**; CLG **0/176**; DVR **0/176**; RLT **0/176**; CAC **0/176**.
- REST/IM/RA/UP/PR/XR/RM/ST/FD/AM/DW/SM/BW/WM/TU/OA/RS are all **0/176**.
- FM **0/92**; NT **0/142**; CH **0/142**; WC **0/156**.
- Email transport ET-F **0/176**; provider profiles **6 EE3 / 0 ET-certified**; ET0…ET5 certified profiles **0 each**.
- Membership billing MB-F **0/176**; provider profiles **4 BE3 / 0 MB-certified**; MB0…MB5 certified profiles **0 each**.
- Membership protected files PC-F **0/176**; PC1+ runtime-certified profiles **0**; PD1…PD4 runtime certifications **0**.
- Backup providers **34 targets / 0 C-certified / 0 C3; V3 0**.
- Connection adapters **0 I4/I5**.

Canonical protocol paths for ADR-0117 onward are under `docs/QUALITY/` or the architecture/provider contract named by the refinement ADR. No fixed evidence matrix has been executed unless explicitly stated otherwise (currently none).

## Current planning work

**`P0-M00-WP58` — Backup provider certification reassessment — SPECIFICATION.**

Current objective: audit existing C0–C4/V3/provider-family Backup certification contracts against BK-01…BK-180, Vault/key custody, JobService/at-least-once execution, Remote Copy lifecycle, manifest/integrity/encryption, privacy, ERR, VER, Multisite, Site Lifecycle and restore-first recovery semantics. Preserve successful upload ≠ restorable Backup, static provider evidence ≠ runtime certification, and never promote provider marketing/documentation to Supported Backup status.