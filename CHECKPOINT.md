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

Accepted evidence decisions/refinements now extend through **ADR-0163**.

Recent fixed evidence sequence:

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
| ADR-0129 | Membership | MBR **0/160** |
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

## Critical preserved truth

- Compatibility floor remains unverified; ADR-0002 stays evidence-gated.
- Canonical build toolchain/runtime CI implementation remains unverified.
- Action Scheduler remains preferred candidate only, not certified.
- Free owns shared kernel/registry family; Pro registers into it.
- Every invocation channel remains Capability + target resource Policy bound.
- `condition=true`, DVR success, RLT allow, CAC hit, route visibility, menu visibility, CORS/preflight or idempotency never grants authorization.
- WordPress remains native identity/auth and Role/Capability authority where accepted.
- generic User Profile/Settings/Dashboard fields/components cannot bypass dedicated password/session/role/Membership/Vault/security operations.
- Protector is application-layer hardening, not a complete edge WAF/DDoS product.
- `xmlrpc_enabled=false` is not equivalent to endpoint disabled; endpoint, registry, method policy and native auth remain separate.
- Reset is staged destructive orchestration with verified recoverability; WordPress Recovery Mode is not data rollback.
- Settings Definition ≠ runtime value document ≠ secret plaintext ≠ external setting authority.
- route/menu visibility never substitutes for direct server-side authorization.
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
- VER **0/176**; MLC **0/176**; DSR **0/176**; ASR **0/176**; CLG **0/176**; DVR **0/176**.
- RLT **0/176**; CAC **0/176**; REST **0/176**; IM **0/176**; RA **0/176**; UP **0/176**.
- PR **0/176**; XR **0/176**; RM **0/176**; ST **0/176**; FD **0/176**.

Other existing evidence:
- FM **0/92**; NT **0/142**; CH **0/142**; WC **0/156**; OA **0/32**; TU **0/44**.
- DW **0/36**; AM **0/40**; WM **0/48**; BW **0/50**; SM **0/48**.
- Membership billing **4 BE3 / 0 MB-certified**; protected files **0 PC1+**.
- Email transport/provider **6 EE3 / 0 ET-certified**; Connection adapters **0 I4/I5**.
- Backup providers **34 targets / 0 C-certified / 0 C3 Supported; V3 0**.
- Multisite runtime surfaces at MS1+ **0**; Site Lifecycle runtime certs **0**; Remote privacy RS **0/30**.

## Current VCS / verification truth

- planning branch: `planning/master-architecture`.
- Draft PR #1 remains the planning PR; current mergeability must be re-verified after this sync.
- direct GitHub reads on 2026-08-28 previously showed `main` and `planning/master-architecture` unprotected.
- repository-wide rulesets remain **UNKNOWN** because ruleset endpoint access returned 403/plan limitation.
- no package install/build/WordPress runtime/browser/CI/DB/DDL/migration/backfill/provider/archive/restore/query/cache/rate-limit/REST/import/role/profile/Protector/XML-RPC/Reset/Settings/Dashboard runtime/benchmark execution occurred.

## Next planning-only priority

Current work package: **`P0-M00-WP47` — Admin Menu canonical evidence refinement — SPECIFICATION**.

Reason: Admin Menu remains a recovery-sensitive wp-admin presentation surface at **AM 0/40**. The existing protocol predates KPA/RA/CAC/ASR/ERR/VER/MLC and current Multisite lifecycle contracts. WP47 will preserve the core invariant that menu hiding/reordering never changes screen authorization, while refining runtime discovery, stable target identity, conflict ordering, safe mode/recovery, site/network isolation and performance evidence.

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