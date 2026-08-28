# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-29**  
Branch: `planning/master-architecture`  
Canonical project state: **`PLANNED_EXISTING_PROJECT`**  
Execution mode: **`PLANNER_ONLY`**  
Current planning lifecycle: **`SPECIFICATION`**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit scoped owner consent is required before runtime/source/build/migration/test implementation, executable spikes/benchmarks, package/dependency setup, WordPress runtime execution, DB/file mutation, queues, provider/API/AI/MCP calls, scheduled workflow installation, packaging or deployment.

`continue`, `resume`, planning acceptance and ADR acceptance do **not** authorize production development.

Source of truth: `DEVELOPMENT-CONSENT.md`, `AGENTS.md`, `docs/APPROVAL-LEDGER.md`, ADR-0014.

## Current product milestone

Scope history:
- original: **31** surfaces;
- ADR-0177: **43**;
- ADR-0188: **48**;
- ADR-0194: **50**;
- ADR-0195: **55**;
- **ADR-0197 current: 56/56 Exhaustive**.

Current logical product mappings:
- Multisite: **56/56**;
- AI Prompt: **56/56**;
- implementation authorization: **0/56**;
- implemented/runtime verified: **none**;
- production implementation WIP: **0**.

Historical denominators remain valid planning snapshots.

## Accepted architecture/evidence milestone

Accepted planning/evidence decisions extend through **ADR-0205**.

### Universal foundations

- ADR-0177 — Solution Blueprint + 12 universal foundations + Woo adapter.
- ADR-0178/0179 — shared AI Prompt / Requirement Compiler / MCP architecture; AIP 0/176.
- ADR-0180 — universal evidence master plan.
- ADR-0181 — F01 SBP documented 176 / executed 0/176.
- ADR-0182 — F02 ANL documented 176 / executed 0/176.
- ADR-0196 — F03 Search & Indexing; SRH documented 176 / executed 0/176.
- ADR-0198 — F04 Decision/Formula/Scoring/Ranking; DEC documented 176 / executed 0/176.
- ADR-0199 — F05 Ledger/Balance/Movement; LED documented 176 / executed 0/176.
- ADR-0200 — F06 Resource Scheduling/Reservation; RSV documented 176 / executed 0/176.
- ADR-0201 — F07 Placement/Personalization; PLC documented 176 / executed 0/176.
- ADR-0202 — F08 Experimentation/Rollout; EXP documented 176 / executed 0/176.
- ADR-0203 — F09 Documents/Records/Templates; DOC documented 176 / executed 0/176.
- ADR-0204 — F10 Data Sync/ETL; SYN documented 176 / executed 0/176.
- **ADR-0205 — F11 Geospatial & Territory; GEO documented 176 / executed 0/176.**

Market and competitive expansions through ADR-0197 remain accepted planning-only state. Current denominator remains **56**. Supplemental namespaces and all previously reserved evidence remain unexecuted unless a later ADR explicitly states otherwise.

## Important architecture boundaries

- User ≠ Role/Capability ≠ Membership Plan ≠ Enrollment ≠ Entitlement ≠ Access Policy.
- Search/index result ≠ source truth or authorization.
- Formula/score/decision/rank ≠ authorization or business mutation authority.
- Ledger hold ≠ resource reservation; availability ≠ reservation; reservation ≠ payment/order/entitlement/external-calendar truth.
- Placement/personalization decides presentation eligibility, not authorization; audience match ≠ entitlement; selected component ≠ exposure.
- Experiment assignment ≠ authorization/consent/exposure; exposure ≠ conversion; statistical signal ≠ automatic causal proof.
- Generated document/artifact ≠ source business/legal/payment/authorization truth; hash/checksum ≠ legal signature; application timestamp ≠ trusted timestamp.
- Synchronized copy ≠ source truth unless explicit entity/field authority assigns it; transport success ≠ business acceptance; unknown remote sync outcome ≠ failed.
- Bidirectional sync requires explicit field/entity authority; delete/archive/tombstone/privacy erase/revoke remain distinct.
- **Geocoded coordinate ≠ verified physical identity/address truth by default.**
- **Provider confidence ≠ certainty.**
- **Spatial match/territory assignment ≠ authorization, entitlement or legal jurisdiction.**
- **Bounding-box match ≠ polygon containment; polygon containment ≠ guaranteed serviceability.**
- **Straight-line distance ≠ travel distance/time; routing/matrix estimate ≠ delivery/travel guarantee.**
- **CRS, axis order, coordinate precision, distance/containment model and provenance are explicit.**
- **Precise location remains consent/Policy/retention/redaction governed.**
- **Unknown geocoder/routing/provider outcome ≠ failed; provider terms/cache/licensing constraints remain binding.**
- **Multisite geospatial ownership is server-resolved and isolated; request scope IDs grant no authority.**
- **Restore/clone/staging cannot blindly reuse production provider tokens, caches, mappings or precise-location authority.**
- Backup/restore/clone cannot roll back external provider facts; external mappings require reconciliation.
- White-label/menu/plugin hiding ≠ authorization; login branding ≠ authentication authority; audit/AI attribution ≠ identity/privilege.
- DB snapshot ≠ full backup; migration replacement ≠ database merge; clone/duplicate ≠ same entity identity.
- Surface 56 Theme Workspace must not expose arbitrary PHP live execution.
- Safe Script/Tag remains browser-side only; PHP/server logic remains Extension SDK/VCS territory.
- AI/MCP may draft/explain/validate only within Policy; high-risk mutation remains separately approved.

## Evidence truth

All evidence remains **documented, not executed**.

Current detailed universal counters:
- SBP 176 documented / 0 executed;
- ANL 176 documented / 0 executed;
- SRH 176 documented / 0 executed;
- DEC 176 documented / 0 executed;
- LED 176 documented / 0 executed;
- RSV 176 documented / 0 executed;
- PLC 176 documented / 0 executed;
- EXP 176 documented / 0 executed;
- DOC 176 documented / 0 executed;
- SYN 176 documented / 0 executed;
- **GEO 176 documented / 0 executed**;
- AIP/WCA remain unexecuted unless a later ADR explicitly states otherwise.

Third-audit supplemental UAF/MIG/WLB/DUP/ALX/MBX/THM/RSX/RDX/CPTX remain 0/176. Earlier evidence namespaces retain their accepted historical counts and remain unexecuted unless explicitly recorded otherwise.

No paper/static evidence has been promoted to runtime certification.

## Work coordination / resume point

Completed interrupts:
- WP75…WP82 market expansion — DONE;
- WP83…WP89 first competitive audit — DONE;
- WP90…WP99 second competitive audit — DONE;
- WP100…WP111 third competitive audit/governance — DONE.

Universal detailed evidence sequence:
- WP63 F01 — DONE;
- WP64 F02 — DONE;
- WP65 F03 Search — DONE / ADR-0196;
- WP66 F04 Decision/Formula/Scoring — DONE / ADR-0198;
- WP67 F05 Ledger/Balance/Movement — DONE / ADR-0199;
- WP68 F06 Resource Scheduling/Reservation — DONE / ADR-0200;
- WP69 F07 Placement/Personalization — DONE / ADR-0201;
- WP70 F08 Experimentation/Rollout — DONE / ADR-0202;
- WP71 F09 Documents/Records/Templates — DONE / ADR-0203;
- WP72 F10 Data Sync/ETL — DONE / ADR-0204;
- **WP73 F11 Geospatial/Territory — DONE / ADR-0205; GEO documented 176 / executed 0/176**;
- **WP74 WooCommerce Commerce Domain Adapter — SPECIFICATION / CURRENT; WCA 0/176 envelope**.

## Current VCS / execution truth

Planning branch: `planning/master-architecture`; Draft PR #1 is the planning PR and must reflect ADR-0205/56-surface/WP74-current state.

No F11 geocoder/routing/provider request, spatial backend query, coordinate mutation, territory assignment, precise-location collection, cache mutation, geometry import/repair, Multisite geospatial operation, restore/provider reconciliation, benchmark, F10 connector runtime, document render, experiment/placement/scheduling/ledger/formula/search runtime, plugin/theme mutation, provider/AI/MCP call, build or test occurred.

## Next safe planning action

Continue **WP74 — WooCommerce Commerce Domain Adapter detailed executable-evidence specification (`WCA-001…WCA-176`)**.

Development remains **NOT GRANTED / 0/56**.

Repository evidence overrides conversational memory.
