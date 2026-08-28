# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-28**  
Branch: `planning/master-architecture`  
Canonical project state: **`PLANNED_EXISTING_PROJECT`**  
Execution mode: **`PLANNER_ONLY`**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit scoped owner consent is required before runtime/source/build/migration/test implementation, executable spikes/benchmarks, dependency/package setup, WordPress runtime execution, queues, provider/API calls, data mutations, packaging or deployment.

`continue`, `resume`, planning acceptance, ADR acceptance and technical readiness do **not** authorize production development.

Source of truth: `DEVELOPMENT-CONSENT.md`, `AGENTS.md`, `docs/APPROVAL-LEDGER.md`, ADR-0014.

## Product milestone

- **31/31 Exhaustive product-option maturity**
- **31/31 Multisite scope behavior mapped**
- **0/31 Authorized**
- **0 MS1+ runtime-certified surfaces**
- Implemented: **none**
- Runtime verified: **none**
- Production implementation WIP: **0**

## Accepted architecture/evidence milestone

Accepted evidence decisions/refinements now extend through **ADR-0174**.

### Fixed evidence sequence

| ADR | Work | Evidence truth |
|---|---|---|
| ADR-0117 | Forms | FM **0/92** |
| ADR-0118 | Workflow | WF **0/116** |
| ADR-0119 | Job/Cron | JS **0/106** |
| ADR-0120 | Notification | NT **0/142** |
| ADR-0121 | Message & Chat | CH **0/142** |
| ADR-0122 | Webhooks/Connections/Event Inbox | WC **0/156** |
| ADR-0123 | Compatibility | CF **0/112** |
| ADR-0124 | Vault | VT **0/128** |
| ADR-0125 | UI | UI **0/104** |
| ADR-0126 | Build | BT **0/112** |
| ADR-0127 | CI | CI **0/120** |
| ADR-0128 | Free↔Pro | FP **0/144** |
| ADR-0129 | Membership core | MBR **0/160** |
| ADR-0130 | Backup/Restore | BK **0/180** |
| ADR-0131 | Query | QRY **0/168** |
| ADR-0132 | Definition | DEF **0/144** |
| ADR-0133 | Relations | REL **0/160** |
| ADR-0134 | Field Storage | FST **0/176** |
| ADR-0135 | Custom Tables | CTB **0/184** |
| ADR-0136 | Admin Columns | AC **0/176** |
| ADR-0137 | Dynamic Listings | DL **0/176** |
| ADR-0138 | Free CPT + Taxonomy | CPTX **0/176** |
| ADR-0139 | Emails Builder render/composition | EBR **0/176** |
| ADR-0140 | Platform Account/Docs/Support/Diagnostics | PLT **0/176** |
| ADR-0141 | Multisite + Site Lifecycle | MSI **0/160**; LC **0/96** |
| ADR-0142 | Audit & Observability | AUD **0/176** |
| ADR-0143 | Kernel/Registry/Policy/Abilities/Events/SDK | KPA **0/176** |
| ADR-0144 | Local Privacy/Data Lifecycle | PDL **0/176** |
| ADR-0145 | Error Taxonomy/Failure UX | ERR **0/176** |
| ADR-0146 | Component Blueprint Core | CBP **0/176** |
| ADR-0147 | Contract Versioning/Deprecation | VER **0/176** |
| ADR-0148 | Module Lifecycle/Uninstall/Recovery | MLC **0/176** |
| ADR-0149 | Entity/Data Source Registry | DSR **0/176** |
| ADR-0150 | Asset Registry/Scoped Loader | ASR **0/176** |
| ADR-0151 | Conditional Logic Engine | CLG **0/176** |
| ADR-0152 | Dynamic Value/Token Resolver | DVR **0/176** |
| ADR-0153 | Shared Rate Limit/Abuse Control | RLT **0/176** |
| ADR-0154 | Shared Cache/Invalidation | CAC **0/176** |
| ADR-0155 | REST API Builder refinement | REST **0/176** |
| ADR-0156 | Import/Export refinement | IM **0/176** |
| ADR-0157 | Role & Capability refinement | RA **0/176** |
| ADR-0158 | User Profile refinement | UP **0/176** |
| ADR-0159 | Protector refinement | PR **0/176** |
| ADR-0160 | XML-RPC Manager refinement | XR **0/176** |
| ADR-0161 | Reset Manager refinement | RM **0/176** |
| ADR-0162 | Settings Page refinement | ST **0/176** |
| ADR-0163 | Frontend Dashboard refinement | FD **0/176** |
| ADR-0164 | Admin Menu refinement | AM **0/176** |
| ADR-0165 | Dashboard Widgets refinement | DW **0/176** |
| ADR-0166 | Status Manager refinement | SM **0/176** |
| ADR-0167 | Builder Widgets adapters refinement | BW **0/176**; BC0…BC4 certs 0 |
| ADR-0168 | Watermarker / Media refinement | WM **0/176** |
| ADR-0169 | Pro Updater TUF refinement | TU **0/176** |
| ADR-0170 | OAuth Account-Link refinement | OA **0/176** |
| ADR-0171 | Remote Service Privacy / Retention refinement | RS **0/176** |
| ADR-0172 | Email Transport / Provider Certification refinement | ET-F **0/176**; **6 EE3 / 0 ET-certified** |
| ADR-0173 | Membership Billing Provider Certification refinement | MB-F **0/176**; **4 BE3 / 0 MB-certified** |
| ADR-0174 | Membership Protected File Delivery Certification refinement | PC-F **0/176**; **0 PC1+**; PD1…PD4 runtime certs 0 |

## Critical preserved truth

- Compatibility floor remains unverified; ADR-0002 stays evidence-gated.
- Canonical build toolchain/runtime CI implementation remains unverified.
- Action Scheduler remains a preferred candidate only, not certified.
- Free owns the shared kernel/registry family; Pro registers into it.
- Every invocation channel remains Capability + target resource Policy bound.
- `condition=true`, DVR success, RLT allow, CAC hit, route/menu/widget visibility, CORS/preflight or idempotency never grants authorization.
- WordPress remains native identity/auth and Role/Capability authority where accepted.
- Generic User Profile/Settings/Dashboard/builder fields cannot bypass password/session/role/Membership/Vault/security operations.
- Protector is application-layer hardening, not a complete edge WAF/DDoS product.
- XML-RPC endpoint reachability, method registry/policy, native auth and outer gating remain distinct.
- Reset is staged destructive orchestration with verified recoverability; WordPress Recovery Mode is not data rollback.
- Menu/widget/dashboard presentation never substitutes for direct server-side authorization.
- Status current state, transition result/history and side effects remain separate; WP Post Status and generic state machine certifications remain separate.
- Builder-private documents are adapters; Component Blueprint remains canonical.
- Watermarker/Media original source bytes/checksum remain immutable under standard WPE processing.
- Account identity, Product entitlement and TUF update authenticity remain separate trust domains.
- TUF target execution requires a trusted metadata graph + target hash/length + archive/compatibility gates; Account/API/CDN cannot add Root trust.
- OAuth Account link never grants WordPress/Membership/Product entitlement authority by itself; PKCE S256/exact redirect/issuer binding remain first-profile requirements.
- Remote disconnect/local erase/remote deletion/provider deletion/backup expiry remain distinct privacy and retention truths.
- Free local-only activation/use does not contact WPE-controlled remote services solely because WPE is installed/active.
- Diagnostics upload remains separately previewed/approved and cannot be implied by Account connection.
- Email renderer success ≠ Transport Attempt ≠ provider accepted ≠ receiving-server delivered ≠ complaint/suppression ≠ engagement.
- Provider acceptance does not prove mailbox/inbox delivery; open/click never proves human read/view/intent.
- Static EE3 provider evidence never becomes ET0 runtime certification.
- Billing commercial source fact ≠ WPE Enrollment ≠ Membership Entitlement ≠ Product Entitlement ≠ WordPress Role.
- Static BE3 provider evidence never becomes MB0 runtime certification.
- Provider hooks/webhooks are freshness/source evidence, not direct Membership authority; ambiguous provider state remains unknown until reconciled.
- Woo HPOS compatibility is evidence-scoped; direct private order-storage assumptions are not the canonical billing-adapter contract.
- Protected-file storage possession ≠ authorization; page/button hiding ≠ origin-byte protection.
- Signed token/URL issuance ≠ durable Membership entitlement; already-issued bearer URL revocation semantics must be stated truthfully.
- Backup-provider certification ≠ protected-file delivery certification; direct-origin isolation is mandatory before Protected/Supported claims.
- JobService at-least-once execution never becomes exactly-once email, provider or Membership behavior.
- module disable ≠ delete ≠ Pro expiry ≠ uninstall ≠ privacy erase.
- live privacy erase ≠ backup erase ≠ remote deletion.
- cache state ≠ canonical business/Audit/Rate-Limit truth.
- Multisite current-blog context never becomes durable ownership/authorization.
- no static/paper evidence is promoted to runtime/provider certification.

## Current evidence/certification counters

Primary/shared:
- CF **0/112**; UI **0/104**; JS **0/106**; DEF **0/144**; VT **0/128**; FP **0/144**; CI **0/120**; BT **0/112**.
- QRY **0/168**; REL **0/160**; WF **0/116**; MBR **0/160**; BK **0/180**.
- FST **0/176**; CTB **0/184**; AC **0/176**; DL **0/176**; CPTX **0/176**; EBR **0/176**; PLT **0/176**.
- MSI **0/160**; LC **0/96**; AUD **0/176**; KPA **0/176**; PDL **0/176**; ERR **0/176**; CBP **0/176**.
- VER **0/176**; MLC **0/176**; DSR **0/176**; ASR **0/176**; CLG **0/176**; DVR **0/176**; RLT **0/176**; CAC **0/176**.
- REST **0/176**; IM **0/176**; RA **0/176**; UP **0/176**; PR **0/176**; XR **0/176**; RM **0/176**; ST **0/176**; FD **0/176**.
- AM **0/176**; DW **0/176**; SM **0/176**; BW **0/176**; WM **0/176**; TU **0/176**; OA **0/176**; RS **0/176**.

Provider/other evidence truth:
- FM **0/92**; NT **0/142**; CH **0/142**; WC **0/156**.
- Email transport ET-F **0/176**; provider profiles **6 EE3 / 0 ET-certified**; ET0…ET5 certified profiles **0 each**.
- Membership billing MB-F **0/176**; provider profiles **4 BE3 / 0 MB-certified**; MB0…MB5 certified profiles **0 each**.
- Membership protected files PC-F **0/176**; PC1+ runtime-certified profiles **0**; PD1…PD4 runtime-certified profiles **0**.
- Backup providers **34 targets / 0 C-certified / 0 C3 Supported; V3 0**.
- Connection adapters **0 I4/I5**.
- Multisite runtime surfaces at MS1+ **0**; Site Lifecycle runtime certs **0**.

## Current VCS / verification truth

- planning branch: `planning/master-architecture`.
- Draft PR #1 remains the planning PR; re-verify open/draft/mergeability after PR body synchronization.
- direct GitHub branch reads on 2026-08-28 reported `main` and `planning/master-architecture` unprotected.
- repository-wide rulesets remain **UNKNOWN** because earlier ruleset access returned 403/plan limitation.
- no package install/build/WordPress runtime/browser/CI/DB/DDL/migration/provider/commerce-object/HPOS/webhook/API/Job/Membership-transition/protected-file/file-move/server-config/signed-URL/download/archive/restore/query/cache/rate-limit/REST/import/identity/Protector/XML-RPC/Reset/Settings/Dashboard/Menu/Widget/Status/Builder/Media/TUF/OAuth/remote-service/email-transport runtime or benchmark execution occurred.

## Next planning-only priority

Current work package: **`P0-M00-WP58` — Backup provider certification reassessment — SPECIFICATION**.

Reason: protected-file delivery evidence is now fixed at PC-F **0/176**, while Backup provider support remains **34 targets / 0 C-certified / 0 C3 Supported; V3 0**. WP58 must audit C0–C4/V3/provider-family evidence against BK-01…BK-180, Vault, JobService, remote-copy lifecycle, privacy, ERR, VER, Multisite, Site Lifecycle and restore-first certification truth. Static provider evidence must never become runtime Backup support automatically.

All gates remain intact. Do not restart from zero. Explicit owner consent is required before executable work.

## Resume order

1. `DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/PROJECT-STATE-AND-ADOPTION.md`
5. `docs/APPROVAL-LEDGER.md`
6. `docs/ENGINEERING-EXECUTION-GOVERNANCE.md`
7. `docs/WORK-COORDINATION-LEDGER.md`
8. `docs/RELEASE-INCIDENT-RECOVERY-GOVERNANCE.md`
9. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
10. `docs/OPEN-DECISIONS-REGISTER.md`
11. `docs/DECISIONS/README.md`
12. relevant architecture/security/quality/module/provider docs.

Repository evidence overrides conversational memory.