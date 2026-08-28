# WPEssential — Implementation Readiness Matrix

Status: **Phase 0 planning / NO DEVELOPMENT CONSENT**  
Last synchronized: **2026-08-29**

## Global rule

A surface may be `Exhaustive` and have accepted architecture/evidence design while remaining technically unverified and unauthorized. Implementation requires applicable runtime evidence, compatibility, security, privacy, recovery, performance, build/CI/provider gates and **explicit owner consent under ADR-0014**.

Current owner consent: **NOT GRANTED**.  
Current canonical scope after ADR-0197: **56 surfaces**.  
Authorized: **0/56**.  
Implemented/runtime verified: **none**.

Historical 31-, 43-, 48-, 50- and 55-surface snapshots remain historical truth only.

## Current planning truth

- Product-option maturity: **56/56 Exhaustive**.
- Logical Multisite mapping: **56/56**.
- Shared AI Prompt product mapping: **56/56**.
- Production implementation WIP: **0**.
- Current lifecycle: **SPECIFICATION**.
- Latest accepted planning/evidence decision: **ADR-0208**.
- WP112 final closure/readiness audit: **DONE / ADR-0207**.
- WP113 Market Expansion exact evidence: **DONE / ADR-0208**.
- Current work: **WP114 — First Competitive exact executable-evidence specification**.

P0 remains **not ready** for `AWAITING_DEVELOPMENT_APPROVAL` because WP114–WP116 exact planning gaps remain.

## Readiness classification

### PLANNING GAP

Known remaining exact fixture expansion after ADR-0208:

| Work | Namespaces | Exact definitions remaining |
|---|---|---:|
| **WP114 CURRENT** | MPR/RPR/ATM/MDP/STM | **880** |
| WP115 | ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC | 1,936 |
| WP116 | UAF/MIG/WLB/DUP/ALX/MBX/THM/RSX/RDX/CPTX | 1,760 |
| **Total** | **26 namespaces** | **4,576** |

The namespace IDs and 16-group ownership are already accepted. The remaining planning work is exact individual fixture enumeration/evidence/boundary detail.

### NO GAP / READY AS PLAN — exact evidence design

The following have exact numbered evidence specifications and are therefore planning-complete at the evidence-design layer, despite zero execution:

| Scope | Namespace | Exact documented | Executed |
|---|---:|---:|---:|
| F01 Solution Blueprint | SBP | 176/176 | 0/176 |
| F02 Analytics/Journey | ANL | 176/176 | 0/176 |
| F03 Search/Indexing | SRH | 176/176 | 0/176 |
| F04 Decision/Formula | DEC | 176/176 | 0/176 |
| F05 Ledger | LED | 176/176 | 0/176 |
| F06 Reservation | RSV | 176/176 | 0/176 |
| F07 Placement/Personalization | PLC | 176/176 | 0/176 |
| F08 Experiments/Rollout | EXP | 176/176 | 0/176 |
| F09 Documents/Records | DOC | 176/176 | 0/176 |
| F10 Sync/ETL | SYN | 176/176 | 0/176 |
| F11 Geo/Territory | GEO | 176/176 | 0/176 |
| F12 AI Gateway/Copilot | AIP | 176/176 | 0/176 |
| A01 WooCommerce Adapter | WCA | 176/176 | 0/176 |
| URL Redirection & Routing | RDR | 176/176 | 0/176 |
| Search/Replace & Transformation | SRT | 176/176 | 0/176 |
| Dummy/Synthetic Fixture Studio | DMY | 176/176 | 0/176 |
| Link Health/Crawl Intelligence | LNK | 176/176 | 0/176 |
| Database Maintenance/Cleanup | DBM | 176/176 | 0/176 |
| Product Discovery/Planning | PDO | 176/176 | 0/176 |
| Market Intelligence Radar | MIR | 176/176 | 0/176 |

`NO GAP / READY AS PLAN` does not imply executable/runtime readiness.

### RUNTIME EVIDENCE PENDING

All exact protocols above remain unexecuted. Established exact/shared protocols also remain pending across compatibility, Multisite/site lifecycle, UI/build/CI, Job/Cron/async, Definition/Fields/Relations/Query/Tables, Vault/Free↔Pro/OAuth/TUF, Workflow/Notification/Chat/Connections, Audit/Kernel/Policy/Abilities/SDK, Privacy/Error/Version/Lifecycle, Data Source/Assets/Conditions/Dynamic Value/Rate/Cache, REST/Import-Export/Roles/Users/Protector/XML-RPC/Reset/Settings/Dashboard/Media and other module-specific evidence.

A zero evidence counter on an exact protocol is a runtime blocker, not automatically a planning blocker.

### PROVIDER CERTIFICATION PENDING

Provider contracts/evidence remain unexecuted for applicable external authorities, including:
- email transport;
- membership billing;
- protected-file delivery;
- backup providers;
- connection/integration adapters;
- builder/provider adapters where certification applies;
- geocoder/routing;
- Woo payment/tax/shipping/external inventory providers.

No HTTP success, paper contract or static fixture constitutes provider certification.

### OWNER CONSENT PENDING

ADR-0014 blocks every production source/runtime/build/migration/test/provider/API/AI/MCP activity until explicit scoped owner consent is recorded.

## Per-surface readiness — 56 current surfaces

All 56 current surfaces are **Exhaustive / Authorized: No**.

Surfaces 1–43 retain exact/shared module/foundation evidence as accepted. Surfaces 44–48 now have exact RDR/SRT/DMY/LNK/DBM evidence under ADR-0208. S07/S08 planning services now have exact PDO/MIR evidence. Their execution remains zero.

Remaining surface/parity planning gaps:
- 15/28/30/49/50 parity: MPR/RPR/MDP/ATM/STM — WP114;
- 51–55 plus second-audit owner supplements: ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC — WP115;
- Surface 56 plus third-audit parity: THM/UAF/MIG/WLB/DUP/ALX/MBX/RSX/RDX/CPTX — WP116.

WooCommerce Commerce Domain Adapter is cross-domain, not a numbered product surface; WCA is exact planning evidence and remains 0 executed.

## Cross-owner stop-the-line conditions

Planning or later implementation must stop on any design that violates:
- UI hiding ≠ authorization;
- Search/index ≠ source truth;
- score/formula/rank ≠ Policy/mutation authority;
- ledger ≠ payment/order/reservation truth outside explicit profile;
- reservation ≠ payment/order/entitlement;
- placement ≠ entitlement;
- experiment assignment ≠ consent/exposure;
- generated document ≠ source/legal/payment truth;
- sync copy ≠ source truth absent explicit authority;
- geospatial match ≠ verified address/authorization/serviceability;
- Woo adapter ≠ second commerce engine;
- redirect match/simulator ≠ authorization; server export ≠ active server state;
- Search/Replace Dry Run ≠ mutation; mutation ≠ verified rollback;
- synthetic data ≠ source truth; generated cleanup requires durable ownership;
- inconclusive/restricted link check ≠ proven broken;
- cleanup candidate/orphan suspicion ≠ deletion authority;
- market score/planning output ≠ product acceptance/development approval;
- Backup ≠ Staging/Migration; clone ≠ same identity;
- Safe Script/Tag and Theme Workspace ≠ arbitrary PHP/eval/server execution;
- AI/MCP ≠ hidden privilege/provider/mutation path.

## Planned implementation critical path after P0 closure

Planning only; not authorization:

1. explicit owner consent + bounded first milestone;
2. compatibility/build/CI/kernel/Policy/Vault/module-lifecycle/data-definition/query/relation/storage foundations;
3. Multisite/privacy/error/version/cache/rate/jobs/recovery foundations;
4. independent module batches;
5. provider/adapters after owning contracts stabilize;
6. sync/geospatial/AI/Woo orchestration after canonical authorities exist;
7. full regression/migration/recovery/concurrency/performance/provider certification;
8. packaging/release/deployment after release gates.

## Current planning work

**P0-M00-WP114 — First Competitive exact executable-evidence specification (`MPR/RPR/ATM/MDP/STM`, 880 fixtures).**

After WP116, a new final closure/readiness audit must decide whether P0 may transition to `AWAITING_DEVELOPMENT_APPROVAL`.

## Development gate

No executable evidence or implementation may run until explicit scoped owner consent. Current implementation authorization: **0/56**.