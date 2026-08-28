# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last synchronized: 2026-08-28

This register contains unresolved implementation/evidence only. Accepted decisions are preserved in ADRs through **ADR-0122**.

All executable work remains blocked by ADR-0014 until explicit owner consent.

## A. Platform executable blockers

| ID | Related ADR | Remaining evidence |
|---|---|---|
| D-001 | ADR-0002/0069/0075 | WP/PHP/DB compatibility + Multisite/site lifecycle — P-001 |
| D-002 | ADR-0005 | UI runtime/externalization/accessibility/RTL/bundle — P-002 |
| D-003 | ADR-0059/0068/0083/0119 | Job physical/backend/Action Scheduler/Cron/DST/fairness/claims/Multisite — JS-01…JS-106 / P-003 |
| D-004 | ADR-0073/0092 | Definition D1/D2/D3/D4 exact DDL/index/locking/migration — P-004 |
| D-005 | ADR-0048/0085 | Vault crypto/envelope/DDL/rotation/recovery/security review — P-005 |
| D-006 | ADR-0070/0072/0076/0091/0101 | Free↔Pro/Product License/OAuth runtime/service — P-006 + OA protocol |
| D-007 | ADR-0011 | executable CI matrix — P-007 |
| D-008 | ADR-0012 | build/externalization/toolchain comparison — P-008 |
| D-009 | ADR-0086 | Query compiler/cost/cache/security/storage-adapter evidence — P-009 |
| D-010 | ADR-0074/0093 | Relations DDL/cardinality/concurrency/scale — P-010 |
| D-011 | ADR-0082/0118 | Workflow runtime/revision/dedupe/concurrency/waits/approvals/recovery/scale — WF-01…WF-116 / P-011 |
| D-012 | ADR-0078/0090 | Membership runtime/cache/files/provider evidence — P-012 |
| D-013 | ADR-0084/0100 | Backup physical/artifact/provider/restore evidence — P-013 |
| D-014 | ADR-0044/0102 | Pro updater TUF verifier/key custody/metadata/package staging — TU-01…TU-44 |
| D-015 | ADR-0031/0108 | Frontend Dashboard routing/IDOR/cache/assets/permalink/Multisite — FD-01…FD-48 |
| D-016 | ADR-0035/0109 | Builder adapter registration/render/version/upgrade certification — BW-01…BW-50 |
| D-017 | ADR-0038/0110 | Post Status + generic state-machine execution/concurrency/history/migration — SM-01…SM-48 |
| D-018 | ADR-0052/0111 | XML-RPC method/parser/rate/compatibility/Multisite evidence — XR-01…XR-48 |
| D-019 | ADR-0036/0089/0112 | Settings site/network/inheritance/Vault/REST/cache/import evidence — ST-01…ST-48 |
| D-020 | ADR-0030/0096/0113 | User Profile identity/protected-binding/email/session/privacy/Multisite evidence — UP-01…UP-48 |
| D-021 | ADR-0032/0097/0114 | Role/capability mutation/anti-lockout/recovery/Super Admin/cache evidence — RA-01…RA-48 |
| D-022 | ADR-0028/0094/0115 | REST route/auth/scope/schema/idempotency/rate/cache/CORS/fuzz evidence — REST-01…REST-52 |
| D-023 | ADR-0041/0095/0116 | Import/Export source/archive/map/checkpoint/rollback/export/scale evidence — IM-01…IM-56 |
| D-024 | ADR-0025/0077/0117 | Forms revision/access/storage/idempotency/files/actions/Workflow/privacy/FRT topology evidence — FM-01…FM-92 |
| D-025 | ADR-0026/0079/0120 | Notification rule/fan-out/dedupe/preferences/inbox/channel truth/NE topology evidence — NT-01…NT-142 |
| D-026 | ADR-0027/0077/0121 | Chat authorization/revocation/idempotency/private-assets/search/realtime/privacy/CRT topology evidence — CH-01…CH-142 |
| D-027 | ADR-0040/0055/0080/0122 | Connection/Vault/OAuth/Safe-HTTP/webhook signature/replay/Event Inbox/provider I0–I5/EI topology evidence — WC-01…WC-156 |

## B. Current accepted paper baselines

- Definition D1/PT-C; D2/D3/D4 comparisons.
- Relations R1/PT-D; R2/PT-E mandatory.
- Query QP1 native-WP; QP2 Custom Table; QP3 Relations; QP4 remote.
- Field Storage FS1–FS6.
- Custom Tables CT1/PT-E vs CT2/PT-D; CT3 network-owned.
- Settings ST1/PT-A; ST2/PT-B; ST3 inheritance.
- Forms FRT1/PT-D vs FRT2/PT-E; FM-01…FM-92 runtime/submission evidence protocol.
- Chat CRT1/PT-D vs CRT2/PT-E; CH-01…CH-142 executable evidence protocol.
- Membership M1/PT-D vs M2/PT-E.
- Notification/Email NE1/PT-D vs NE2/PT-E; Notification NT-01…NT-142 evidence protocol.
- Event Inbox EI1/PT-D vs EI2/PT-E; WC-01…WC-156 Webhooks/Connections/Event Inbox protocol.
- Audit AU1/PT-D.
- Workflow WF1/PT-D vs WF2/PT-E; WF-01…WF-116 evidence protocol.
- JobService J1/J2/J3; JS-01…JS-106 evidence protocol; Action Scheduler remains candidate only.
- REST RE1 + RI1/RI2.
- Import IR1/PT-D vs IR2/PT-E.
- Backup Remote Copy BR1/BR2/BR3.
- Vault V1/PT-C vs V2.
- User/Profile native WordPress identity authority + WPE security workflows.
- Role/Capability native WordPress authorization authority + WPE anti-lockout/recovery.
- Admin Columns AC1 whole-request batching.
- Dynamic Listings DL1 auth-aware Query + batched hydration + SSR.
- Backup artifact H-B1 SHA-256; CMP0 fallback; CMP1 gzip comparison; ZIP convenience only.
- OAuth Account Link fixed callback + one-time site return + PKCE S256 evidence protocol.
- Pro updater TUF Root/Targets/Snapshot/Timestamp evidence protocol.
- Dashboard Widgets DW-01…DW-36 evidence protocol.
- Admin Menu AM-01…AM-40 evidence protocol.
- Protector PR-01…PR-44 evidence protocol.
- Reset Manager RM-01…RM-48 evidence protocol.
- Watermarker/Media WM-01…WM-48 evidence protocol.
- Frontend Dashboard FD-01…FD-48 route/navigation/authorization/cache evidence protocol.
- Builder Widgets BW-01…BW-50 adapter certification protocol with BC0…BC4 levels.
- Status Manager SM-01…SM-48 split-engine execution protocol.
- XML-RPC XR-01…XR-48 layered method/parser/compatibility protocol.
- Settings Page ST-01…ST-48 scope/value/Vault/REST evidence protocol.
- User Profile UP-01…UP-48 identity/security/privacy evidence protocol.
- Role & Capability RA-01…RA-48 native-authority/anti-lockout evidence protocol.
- REST API Builder REST-01…REST-52 operational/security evidence protocol.
- Import / Export IM-01…IM-56 recovery/privacy/scale evidence protocol.

All remain paper-only unless separately certified.

## C. Admin Columns / Listings

ADR-0098/0099 resolve static semantics. Remaining:
- WordPress/third-party list-table hooks;
- exact batch/query budgets;
- backend sorting/filtering adapters;
- inline/bulk edit concurrency/authorization;
- export/lazy mode;
- Listing cursor/count/refill semantics;
- protected cache storage/invalidation;
- nested-list budgets;
- SSR/client parity;
- SEO/builder adapters.

Admin Columns runtime: **0**. Listings runtime: **0**.

## D. Backup — ADR-0084/0100 / P-013

Accepted artifact semantics:
- manifest-first multipart bundle;
- SHA-256 stored-byte integrity;
- AEAD distinct from object hash;
- CMP0 no-compression fallback;
- CMP1 gzip streaming first compression comparison;
- ZIP convenience only;
- FR1 vs FR2 file stream;
- DB1/DB2/DB3 DB payload comparison;
- provider multipart below WPE Part identity.

Open:
- exact byte format/chunk sizes/compression levels/parser limits;
- archive bomb/path/symlink safety;
- encrypted disaster restore;
- crash/final-manifest windows;
- exact runtime DDL/indexes;
- provider C0–C4 certification.

Backup: **34 targets / 0 C-certified / 0 C3 Supported**. P-013 runtime: **0**.

## E. OAuth Account Link — ADR-0034/0101

OA-01…OA-32 are fixed future fixtures.

Open evidence:
- exact client/service endpoints and transaction store;
- PKCE/state/issuer binding;
- return artifact redemption;
- refresh token rotation/replay behavior;
- token lifetimes/scopes;
- proxy/callback canonicalization;
- clone/domain migration;
- disconnect/outage;
- Vault integration;
- privacy/log leakage.

**OA executed: 0/32.**

## F. Pro updater TUF — ADR-0044/0102

TU-01…TU-44 are fixed future fixtures.

Open evidence:
- production-grade PHP verifier or audited equivalent;
- official TUF conformance;
- Root/Targets thresholds/custody operations;
- Snapshot/Timestamp online isolation;
- metadata expiry/rollback/freeze/mix-and-match;
- consistent snapshots;
- target hash/length/custom compatibility;
- key compromise/rotation runbooks;
- ZIP staging/path/bomb/recovery;
- Free↔Pro update order/schema recovery.

**TU executed: 0/44.**

Automated Pro updates stay blocked if this evidence cannot meet the accepted bar.

## G. Admin/security/media protocols — ADR-0103…0107

### Dashboard Widgets — ADR-0103
DW-01…DW-36 cover Site/Network contexts, content trust, XSS/SSRF, remote structured data, cache isolation, async refresh, iframe/CSP, assets and failure isolation.

**DW executed: 0/36.**

### Admin Menu — ADR-0104
AM-01…AM-40 cover WordPress ordering composition, late/third-party menu items, rename/reorder/hide/move/add/link, direct URL authority independence, conflicts, safe mode, Multisite and request overhead.

**AM executed: 0/40.**

### Protector — ADR-0105
PR-01…PR-44 cover trusted proxies, spoof resistance, atomic rate limits, login/password/XML-RPC/REST gates, paths/redirects/headers/recovery/Multisite/privacy.

**PR executed: 0/44.**

### Reset Manager — ADR-0106
RM-01…RM-48 cover impact fingerprint, recovery principal, verified restore point, destructive lock, durable journal, duplicate Jobs, crash/recovery/post-health and Multisite.

**RM executed: 0/48.**

### Watermarker / Media — ADR-0107
WM-01…WM-48 cover original checksum immutability, actual image-editor/MIME capability, alpha/orientation/font/SVG safety, deterministic derivative identity, Jobs/concurrency, offload/CDN/private media and Multisite.

**WM executed: 0/48.**

## H. Frontend Dashboard — ADR-0031/0108

FD-01…FD-48 are fixed future fixtures.

Open evidence:
- actual rewrite/router strategy under supported permalink modes;
- path normalization/collision behavior;
- direct-route IDOR and resource authorization;
- login intended-return safety;
- navigation count/title leakage prevention;
- Listing/Form/CRUD/Profile component action boundaries;
- principal/site/revision/access-generation cache isolation;
- server/client navigation parity;
- asset dependency scoping;
- noindex/sitemap behavior;
- accessibility/mobile/RTL;
- Multisite/network floors;
- large route-graph performance.

**FD executed: 0/48.**

## I. Builder Widgets adapters — ADR-0035/0109

BW-01…BW-50 are fixed future fixtures. BC0…BC4 certification is version/capability scoped.

Open evidence:
- Gutenberg registration/save/server-render/version regression;
- Elementor Free widget + edition-gated native Dynamic Tags bridge;
- Bricks element/control/dynamic data/version behavior;
- WPBakery shortcode + editor mapping compatibility;
- Visual Composer Website Builder manifest/editor/prebuilt-runtime prototype;
- asset isolation;
- stored-document upgrade/regression behavior;
- nested/container/repeater advanced certification;
- cross-builder semantic/security parity.

**BW executed: 0/50. Builder runtime certifications: 0.**

## J. Status Manager — ADR-0038/0110

SM-01…SM-48 are fixed future fixtures.

Open evidence:
- real WordPress custom status registration/editor/quick/bulk/list behavior;
- machine-key constraints and recoverable migrations;
- direct third-party write enforcement coverage;
- generic Data Source state storage classes;
- concurrent transitions and stale-state rejection;
- state/history transaction or reconciliation boundary;
- duplicate request/Job idempotency;
- Workflow/timed transition semantics;
- import/history truth;
- Multisite and large-history indexes.

**SM executed: 0/48.**

## K. XML-RPC Manager — ADR-0052/0111

XR-01…XR-48 are fixed future fixtures.

Open evidence:
- effective core/plugin method inventory and filter ordering;
- `xmlrpc_enabled=false` exact tested behavior;
- Complete Deny against discovered/late-added methods;
- Protector endpoint deny + trusted-proxy atomic rate limiting;
- pingback behavior;
- `xmlrpc_element_limit` and parser failure behavior;
- host/PHP request limits as separate environment controls;
- Jetpack and remote/mobile publishing version-scoped compatibility;
- Multisite network floors;
- logging redaction and observability coverage;
- method inventory drift on plugin/version changes.

**XR executed: 0/48.**

## L. Settings Page — ADR-0036/0089/0112

ST-01…ST-48 are fixed future fixtures.

Open evidence:
- actual Options/Network Options/autoload behavior under supported WordPress versions;
- ST1/ST2/ST3 site/network/default+override storage behavior;
- stale/concurrent writes and typed validation;
- Vault secret reference/redaction;
- external/native setting write adapters;
- REST read/write field projection;
- cache inheritance invalidation;
- import/export scope/remap;
- Multisite lifecycle/isolation and scale.

**ST executed: 0/48.**

## M. User Profile — ADR-0030/0096/0113

UP-01…UP-48 are fixed future fixtures.

Open evidence:
- protected-meta binding registry and mass-assignment denial;
- self/admin target authority;
- Field Storage site/global routing;
- email confirmation/replay/races;
- recent-auth purpose/expiry;
- password/session/Application Password actions;
- public/REST/listing projection leakage;
- site removal vs network deletion/Super Admin;
- privacy exporter/eraser;
- Multisite isolation/scale.

**UP executed: 0/48.**

## N. Role & Capability — ADR-0032/0097/0114

RA-01…RA-48 are fixed future fixtures.

Open evidence:
- native custom/third-party role mutation compatibility;
- Change Plan fingerprint/effective-capability simulation;
- recovery-principal/self-lockout analysis;
- stale/partial/ambiguous mutation reconciliation;
- bounded snapshot/reverse-diff recovery;
- Site vs Network/Super Admin boundaries;
- capability-dependent cache invalidation;
- Audit redaction and large-network/bulk behavior.

**RA executed: 0/48.**

## O. REST API Builder — ADR-0028/0094/0115

REST-01…REST-52 are fixed future fixtures.

Open evidence:
- route registration/conflicts and published-descriptor fail-closed behavior;
- cookie/nonce, Application Password, anonymous/auth adapter behavior;
- IDOR/wrong-site/mass-assignment/schema/query fuzzing;
- RI same-key concurrency/crash/unknown-outcome/degradation;
- atomic rate-limit/proxy-spoof/site isolation;
- cache principal/site/revision/revocation safety;
- exact CORS/error redaction behavior;
- bounded network operations and load/scale.

**REST executed: 0/52.**

## P. Import / Export — ADR-0041/0095/0116

IM-01…IM-56 are fixed future fixtures.

Open evidence:
- Dry Run/Plan/source fingerprint execution gates;
- private source staging/archive traversal/symlink/bomb limits;
- target mapping/authorization and stable Identity Map;
- crash windows across target commit/Map/Checkpoint/Job;
- concurrent same-source duplicate prevention;
- pause/resume/cancel/lifecycle behavior;
- R0–R3 rollback truth + Backup separation;
- Restore revalidation;
- Safe HTTP/media/offload;
- authorized/redacted site-scoped export;
- IR1/IR2 retention/scale/Multisite evidence.

**IM executed: 0/56.**

## Q. Forms Runtime — ADR-0025/0077/0117

FM-01…FM-92 are fixed future fixtures.

Open evidence:
- exact Entry/core/canonical-value/projection DDL and migration profile;
- schema/render/submit Policy and revision pinning under real WordPress requests;
- save/resume token lifecycle and draft concurrency;
- capacity/rate/idempotency atomicity and crash reconciliation;
- spam/CAPTCHA provider failure behavior;
- private upload/finalize/download/cleanup safety;
- CRUD/relation/user/membership action authorization;
- Workflow handoff, duplicate dispatch and no-long-term-storage reconciliation;
- retention/privacy/admin/export behavior;
- FRT1/PT-D wrong-site/noisy-neighbor/large-table evidence;
- FRT2/PT-E provisioning/migration/fan-out/lifecycle evidence;
- 10k/100k/1M Entry and 100/1k/10k-site scale comparison.

**FM executed: 0/92. Forms runtime certifications: 0. Final FRT topology: open.**

FRT1/PT-D remains first future benchmark baseline. FRT2/PT-E remains mandatory comparison; ADR-0117 does not select a final physical topology.

## R. Workflow Runtime — ADR-0082/0118 / P-011

WF-01…WF-116 are fixed future fixtures.

Open evidence:
- published revision pinning and historical revision retention under real runtime;
- trigger/event idempotency and concurrent Run-start admission;
- Run/Step CAS transitions, duplicate/out-of-order workers and crash windows;
- typed condition/branch behavior and concurrent joins;
- durable waits/timers and lost-enqueue reconciliation;
- approval authorization, expiry, quorum and decision races;
- JobService lease/retry/backpressure integration;
- action-specific authorization/idempotency and external unknown-outcome reconciliation;
- cancellation/intervention/compensation truth;
- security/privacy/log redaction;
- restore/clone/site lifecycle revalidation;
- WF1/PT-D vs WF2/PT-E physical/Multisite/scale evidence.

**WF executed: 0/116. Workflow runtime certifications: 0. Final Workflow topology: open.**

## S. JobService / Cron — ADR-0059/0068/0083/0119 / P-003

JS-01…JS-106 are fixed future fixtures.

Open evidence:
- candidate Action Scheduler version/load-order/coexistence and ownership isolation;
- backend-neutral Job/Attempt mapping and physical persistence boundaries;
- one-time/interval/calendar recurrence plus timezone/DST/missed/overlap semantics;
- enqueue/commit crash ambiguity and reconciliation;
- at-least-once/idempotency/unknown-outcome behavior;
- claim/lease expiry and stale-worker races;
- fairness/starvation/resource-key/backpressure behavior;
- request-driven/loopback/system-cron/WP-CLI runner modes;
- current authorization/resource/secret revalidation;
- retention/observability/log redaction;
- Multisite/lifecycle/clone/restore safety;
- J1/J2/J3 physical and large workload/site-count evidence.

**JS executed: 0/106. JobService backend certifications: 0. Cron/DST certifications: 0.**

Action Scheduler remains a preferred candidate adapter only; ADR-0119 does not certify it or select final J1/J2/J3 topology.

## T. Notification System — ADR-0026/0079/0120

NT-01…NT-142 are fixed future fixtures.

Open evidence:
- Rule publish/revision/trigger behavior and recursive event protection;
- Occurrence durability, dedupe and concurrent trigger admission;
- recipient resolution across users/roles/capabilities/Query/relations/Membership/team/external endpoints;
- trigger-snapshot vs delivery-time eligibility and access revalidation;
- preference classification, opt-out, frequency caps and required/security controls;
- quiet hours/timezones/DST/delays/expiry;
- digest grouping/caps/retries/current eligibility;
- in-app inbox authorization/read/dismiss/revoke/unread-count/cache correctness;
- safe tokens/localization/action-target reauthorization;
- channel fallback and provider acceptance/delivery/unknown-outcome truth;
- fan-out/JobService crash/backpressure/100k audience behavior;
- privacy/export/erase/redaction;
- restore/clone/site-lifecycle and wrong-site provider-reference protection;
- NE1/PT-D vs NE2/PT-E physical/Multisite/scale evidence.

**NT executed: 0/142. Notification runtime certifications: 0. Final NE topology: open.**

Notification certification cannot upgrade an Email/Connection provider beyond its own ET/adapter certification.

## U. Message & Chat — ADR-0027/0077/0121

CH-01…CH-142 are fixed future fixtures.

Open evidence:
- exact Conversation/Participant/Message/Moderation/Protected Asset DDL and index profile;
- conversation creation and participant lifecycle authorization under real WordPress requests;
- Membership/team/resource revoke races against send/read/search/attachment/realtime operations;
- server-authoritative per-conversation ordering and concurrent idempotent send admission;
- edit/delete/tombstone/reply/reaction/mention semantics and user-enumeration boundaries;
- private attachment MIME/origin/download/finalization/orphan-cleanup safety;
- last-read/unread-count concurrency and principal/site/access-generation cache isolation;
- SQL/FULLTEXT/rebuildable search projection behavior with request-time reauthorization and stale-index cleanup;
- polling/SSE/WebSocket/managed transport comparison, reconnect duplication and long-lived authorization refresh;
- Notification integration without private-body leakage or rollback of accepted messages;
- moderation/report/block/rate-limit scope and abuse resistance;
- privacy export/erase/anonymization and retention/moderation exceptions;
- clone/restore/Site Lifecycle revalidation;
- CRT1/PT-D vs CRT2/PT-E wrong-site/noisy-neighbor/provisioning/migration/Backup/scale evidence;
- 100k-conversation, million-message, 1k-participant, hot-conversation and 100/1k/10k-site workloads.

**CH executed: 0/142. Chat runtime certifications: 0. Realtime transport certifications: 0. Search adapter certifications: 0. Final CRT topology: open.**

Canonical Chat state remains transport-independent. Search/provider ACL is never sole authorization and private attachments remain Protected Assets.

## V. Webhooks, Connections & Event Inbox — ADR-0040/0055/0080/0122

WC-01…WC-156 are fixed future fixtures.

Open evidence:
- Connection publish/revision/dependency and capability drift behavior;
- site/network ownership and trusted endpoint/Connection scope derivation;
- Vault secret storage/rotation/revoke and OAuth state/PKCE/issuer/refresh race behavior;
- I0–I5 provider capability certification by adapter/provider/API/profile/environment;
- Safe HTTP SSRF/private/link-local/metadata/DNS-rebinding/redirect/TLS/proxy/request-response bounds;
- exact raw-body webhook signature/key rotation/timestamp/skew/nonce/event-ID replay behavior;
- unsigned-provider alternative authenticity profiles;
- Event Inbox typed normalization, dedupe/conflicting-payload/out-of-order/schema-drift semantics;
- claim/crash/manual-replay/reconciliation and consumer-specific idempotency;
- Workflow/Membership/Email/Notification/domain integration boundaries;
- outbound typed payload/signing/idempotency/Retry-After/unknown-outcome/dead-letter behavior;
- pagination/rate limits/Protected Asset transfer;
- logging/privacy/raw payload retention;
- site archive/delete/clone/restore safety;
- EI1/PT-D vs EI2/PT-E wrong-site/noisy-neighbor/migration/Backup/scale evidence;
- 100k/1M/10M retained-event, burst/provider-hotspot and 100/1k/10k-site workloads.

**WC executed: 0/156. Connection provider I4/I5 certifications: 0. Event Inbox runtime certifications: 0. Safe HTTP runtime certification: none. Final EI topology: open.**

Event Inbox remains accepted ingress/source-fact truth, not owning domain state; provider payload cannot choose WPE site/network scope before trusted mapping.

## W. Other current evidence state

- Definition P-004: **0 executed**.
- Relations P-010: **0 executed**.
- Query P-009: **0 executed**.
- Job P-003: **0/106 JS**.
- Vault P-005: **0 executed**.
- Workflow P-011: **0/116 WF**.
- Notification: **0/142 NT**.
- Message & Chat: **0/142 CH**; runtime/realtime/search certifications **0**.
- Webhooks/Connections/Event Inbox: **0/156 WC**; I4/I5 **0**; Event Inbox/Safe HTTP runtime unverified.
- Membership P-012: **0 executed**; billing **4 BE3 / 0 MB-certified**; protected file **0 PC1+**.
- Forms Runtime: **0/92 FM fixtures / 0 runtime certifications**.
- Email: **6 EE3 / 0 ET-certified**.
- Site Lifecycle: **0/40**.
- Multisite: **0 MS1+**.
- Remote privacy: **0/30**.
- Product License API/service: **0**.

## X. Accepted architecture no longer open semantically

ADRs **0035–0122** preserve accepted core semantics. Evidence can refine exact implementation/version facts but cannot silently redesign them.

## Decision-processing rule

1. Inspect repository and authoritative evidence.
2. Resolve static semantics in ADR when sufficient.
3. Predefine bounded executable protocol when proof is required.
4. **Do not install, compile, migrate, benchmark, test, contact services/providers, send mail, run queues, generate signing keys/TUF metadata, execute OAuth, create/extract archives, mutate options/users/roles/media/status/XML-RPC/REST/import/forms/workflow/jobs/notifications/chat/connections/webhook/Event-Inbox runtime or transfer data before explicit owner consent.**
5. Keep governance/Draft PR synchronized.

## Next planning-only priorities

1. **P-001 Compatibility Floor evidence refinement** — convert the existing generic P-001 spike into a fixed, adversarial, environment/adoption-aware executable evidence protocol without executing it.
2. Then reassess P-002/P-005/P-007/P-008/P-012/P-013 and other remaining blockers by critical-path/dependency value.
3. Keep P-001…P-013 + OA/TU/DW/AM/PR/RM/WM/FD/BW/SM/XR/ST/UP/RA/REST/IM/FM/WF/JS/NT/CH/WC gates intact.