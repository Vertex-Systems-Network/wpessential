# WPEssential Architecture Decision Records

Status: **Phase 0 planning / no development authorization**  
Last synchronized: 2026-08-27

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
| ADR-0006 | Proposed adapter blocker | WPE Job Service abstraction; Action Scheduler preferred adapter candidate; WPE execution semantics later accepted in ADR-0059 |
| ADR-0007 | Accepted | Pro expiry preserves data and safe deployed runtime; editing/unsafe operations can lock |
| ADR-0008 | Proposed physical-evidence blocker | Definition Repository shape later accepted in ADR-0049; exact DDL/index/locking evidence pending |
| ADR-0009 | Proposed physical-evidence blocker | Secrets Vault hierarchy later accepted in ADR-0048; storage/rotation/interoperability evidence pending |
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
| ADR-0059 | Accepted JobService semantics / adapter evidence pending | Backend-neutral Job Type/Schedule/Job/Attempt/Runner policy; at-least-once, explicit idempotency, reviewed urgency, fairness, resource/concurrency keys, backpressure and cooperative cancellation |
| ADR-0060 | Accepted platform/privacy architecture / service evidence pending | Remote-service transmission is purpose-scoped/minimized; Free activation sends nothing; account link ≠ telemetry consent; diagnostics require separate approval; retention/disconnect/deletion boundaries are explicit |
| ADR-0061 | Accepted backup identity/capability architecture / provider evidence pending | Semantic `bf.*` family keys are canonical; numeric PF aliases are legacy/ambiguous; provider profiles are separately versioned; SE0–SE3 static evidence never implies C0–C4 certification |
| ADR-0062 | Accepted Membership billing provider profiles / executable evidence pending | Manual/Woo order/Woo Subscriptions/SureCart source-truth profiles are version-scoped; Woo paid truth uses supported APIs not `Completed` alone; pending cancellation/failure/refund/switch/webhook semantics remain reconciliation-driven |
| ADR-0063 | Accepted Email provider profiles / executable evidence pending | wp_mail/SMTP/SES/SendGrid/Mailgun/Postmark source-truth profiles normalize only verified provider facts; EE0–EE3 static evidence never implies ET0–ET5 certification; no inbox/read inference |

## Product specification milestone

`docs/MODULES/OPTION-COVERAGE-MATURITY.md` records **31/31 Exhaustive** and **0/31 Authorized**.

## Major supporting architecture/security docs

- Definition Repository: `docs/ARCHITECTURE/DEFINITION-REPOSITORY-PHYSICAL-SCHEMA-CANDIDATE.md`
- Query AST: `docs/ARCHITECTURE/QUERY-AST-V1-CANDIDATE-SCHEMA.md`
- Job Service semantics: `docs/ARCHITECTURE/JOB-SERVICE-EXECUTION-FAIRNESS-BACKPRESSURE.md`
- Backup provider contract: `docs/ARCHITECTURE/BACKUP-PROVIDER-CERTIFICATION-CONTRACT.md`
- Backup family/provider registry: `docs/ARCHITECTURE/BACKUP-PROVIDER-FAMILY-CAPABILITY-REGISTRY.md`
- Backup remote lifecycle: `docs/ARCHITECTURE/BACKUP-REMOTE-COPY-LIFECYCLE.md`
- Backup named matrix: `docs/MODULES/BACKUP-PROVIDER-CERTIFICATION-MATRIX.md`
- Membership billing certification: `docs/ARCHITECTURE/MEMBERSHIP-BILLING-ADAPTER-CERTIFICATION.md`
- Membership billing provider profiles: `docs/ARCHITECTURE/MEMBERSHIP-BILLING-PROVIDER-PROFILES.md`
- Email transport certification: `docs/ARCHITECTURE/EMAIL-TRANSPORT-PROVIDER-CERTIFICATION.md`
- Email provider profiles: `docs/ARCHITECTURE/EMAIL-PROVIDER-CAPABILITY-MATRIX.md`
- Remote service schemas: `docs/PLATFORM/REMOTE-SERVICE-RESOURCE-SCHEMAS.md`
- Remote service privacy/retention: `docs/PLATFORM/REMOTE-SERVICE-PRIVACY-RETENTION-MATRIX.md`
- Remote service privacy evidence protocol: `docs/QUALITY/REMOTE-SERVICE-PRIVACY-RETENTION-EVIDENCE-PROTOCOL.md`
- Connection certification: `docs/ARCHITECTURE/CONNECTION-ADAPTER-CERTIFICATION-CONTRACT.md`
- Provider evidence protocols: corresponding files under `docs/QUALITY/`.

## Remaining evidence blockers

### Platform/runtime
Compatibility, UI runtime, Job adapter, Definition exact DDL, Vault envelope/interoperability, Free↔Pro runtime, CI/build, Query/Relations/Workflow/Forms/Notifications/Chat/REST/Email/Dashboard/Components/Settings/Menu/Status/Listings/Import remain executable-evidence gates.

### Membership
ADR-0062 fixes first provider source-truth profiles, but **0 billing profiles are MB-certified**. Enrollment/Entitlement physical schema, revoke-to-deny cache, protected-file delivery, Manual/Woo/SureCart MB0–MB5 fixtures, customer→WP identity resolution, refunds/switches/reconciliation, scheduler/webhook failure, seat concurrency and migration/privacy runtime evidence remain open.

### Email/notifications
ADR-0063 fixes initial provider source-truth profiles for `wp_mail`, generic SMTP, SES, SendGrid, Mailgun and Postmark. All six are EE3 static-paper maturity; **0 providers are ET-certified**. Renderer/client compatibility, Delivery/Attempt/Event physical schema, provider send adapters, webhook authenticity/replay/order, unknown-outcome reconciliation, bounce/complaint/suppression truth, JobService load and ET0–ET5 evidence remain open.

### Remote service/distribution
ADR-0060 fixes field-level minimization/consent/retention semantics and the future 30-fixture evidence protocol is documented, but **none has been executed**. OAuth endpoint/token lifecycle, exact schemas/OpenAPI, log redaction, diagnostics upload, RR0–RR6 cleanup, resource retention/deletion/export, clone isolation, entitlement canonicalizer/keyset rotation and production TUF client/key custody/conformance remain executable.

### Backup/operations/security
ADR-0061 fixes family/provider identity and static capability profiles for all 34 targets, but **0 providers are certified**. C0–C4 evidence, Remote Copy schema/finalization/retention, Backup crypto framing/KDF/recovery-kit, Protector, Watermark, Reset, Dashboard Widget and XML-RPC runtime certification remain open.

No executable evidence may run before explicit owner consent.
