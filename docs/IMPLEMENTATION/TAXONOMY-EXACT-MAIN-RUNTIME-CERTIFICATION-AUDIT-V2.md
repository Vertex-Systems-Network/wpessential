# Taxonomy Builder — Exact-Main Runtime Certification Audit V2

Surface: **2 / Taxonomy Builder**  
Parent runtime program: **Issue #474**  
Audit issue: **#632**  
Audit authorization: **merged PR #633**  
Exact-main audit anchor: `327351fb1531f59aee893322fdfb1f3d9d00609e`  
Product-state anchor immediately before coordination-only PR #633: `2b36cb3f56ea853766249eb960d94d6b379dc24b`  
Prior audit: **Issue #626 / merged PR #628**  
Final admin UX closure: **Issue #629 / merged PR #631**

## Decision

**RUNTIME_CERTIFIED for the accepted Surface 2 / Taxonomy Builder V1 owner-runtime scope.**

The fresh exact-main audit finds no remaining accepted V1 runtime/UX exit blocker. The three concrete blockers proven by the V1 audit are now implemented and directly exercised by packaged browser/accessibility evidence:

1. canonical read-only role-impact preview;
2. deterministic unsaved-change guard lifecycle;
3. sticky Validate / Save command behavior.

All other accepted V1 rows were already evidence-accounted as promoted, present, cross-surface, optional/non-blocking, or separately safety-gated by the V1 audit. The exact-main diff from the V1 audit anchor to this V2 anchor contains no Taxonomy backend/runtime PHP change, so those previously accepted backend/runtime rows were not displaced by the final UI closure.

This certification is deliberately bounded:

- it does **not** promote `PRODUCT_PARITY_CERTIFIED`;
- it does **not** authorize taxonomy-key migration execution;
- it does **not** absorb generic package orchestration into Surface 2;
- it does **not** claim full Surface 30 Roles & Capabilities certification;
- it does **not** authorize deployment/release.

The worker audit branch does not mutate shared project truth or machine lifecycle state. Parent Issue #474 is eligible for Supervisor closeout only after the certification decision is merged and shared truth is reconciled.

## Exact-main evidence boundary

The prior V1 audit anchor was `81d26a8677ec027736bca2f1a6380faa1d4e7631`. Comparing that anchor with current exact main `327351fb1531f59aee893322fdfb1f3d9d00609e` shows only:

- `admin-ui/src/admin.scss`;
- `admin-ui/src/taxonomy-editor-safety.ts`;
- `admin-ui/src/taxonomy-role-impact-preview.ts`;
- `admin-ui/src/taxonomy.ts`;
- `tests/e2e/specs/taxonomy-final-admin-ux.spec.mjs`;
- the V1/V2-adjacent implementation evidence documents; and
- Supervisor-owned coordination queue updates.

There are no changed `frameworks/Modules/Taxonomies/**` backend/runtime PHP files in that delta. Therefore this audit treats the V1 audit's already-promoted backend/runtime accounting as the accepted baseline and re-opens only rows for which new exact-main evidence proves a regression. No such regression is found.

## Closure of the three V1 blockers

### 1. Visual role-impact preview — CLOSED

Current admin code now:

- types `read_model.role_impact` and `diagnostics.role_impact` as canonical Taxonomy client data;
- validates/parses the role-impact payload rather than dropping it;
- renders a read-only diagnostics section sourced from the existing Surface 30-backed Taxonomy read model;
- presents operation/capability entries plus explicit allow, explicit deny and absent role-entry states;
- surfaces canonical caveats and explicitly states that role-entry diagnostics are not final user authorization;
- adds no direct `wp_roles()`, `get_role()` or raw role-option access and creates no role-grant engine.

`tests/e2e/specs/taxonomy-final-admin-ux.spec.mjs` validates a healthy role-impact state, the `manage_terms` / `manage_categories` entry, Allow / explicit deny / absent summaries, the no-final-user-authorization caveat, and zero Axe violations while the preview is visible.

**Certification effect: blocker closed.**

### 2. Unsaved-change guard — CLOSED

`admin-ui/src/taxonomy-editor-safety.ts` establishes a canonical baseline from the existing collected editor request and marks dirty state only when the meaningful editor snapshot differs. It arms `beforeunload` only while dirty.

`admin-ui/src/taxonomy.ts` explicitly accepts a clean baseline after:

- initial editor boot;
- successful save/reset-to-new;
- Cancel/reset-to-new;
- canonical Edit hydration; and
- status-driven editor reset.

The packaged browser spec proves:

- clean boot state;
- meaningful edits become dirty;
- dirty `beforeunload` is prevented;
- successful save restores clean state;
- clean `beforeunload` is not prevented;
- canonical edit hydration starts clean;
- a meaningful edit becomes dirty; and
- Cancel restores clean state.

Because the snapshot comes from `collectEditor()`, UI-only search/tier controls that do not alter the canonical editor request do not become false payload changes.

**Certification effect: blocker closed.**

### 3. Sticky Validate / Save commands — CLOSED

The existing `.submit` command container is decorated with a Taxonomy-specific stable hook and styled with `position: sticky; bottom: 0`. No new mutation action or alternate save path is introduced.

The packaged browser spec proves that the sticky command bar is visible, contains Validate and Save taxonomy, and computes to `position: sticky`. The same state is covered by the zero-violation Axe assertion.

**Certification effect: blocker closed.**

## Exact-head regression evidence for the final closure

PR #631 exact head `7c49f294557b4a4e73ac7fc374e61558e61ffdc4` completed all applicable workflows successfully:

- **Architecture Guards** — run `34693898045`: success;
- **Distributable Package** — run `34693898054`: success;
- **Browser E2E Accessibility** — run `34693898066`: success.

The first Architecture attempt on an earlier head failed only Prettier formatting and was corrected on the same branch. Certification evidence uses the fresh final head above, not the failed earlier head.

PR #631 had zero review threads at merge and merged without main drift. Its product changes are the bounded admin UI/test/doc files listed above; Taxonomy backend runtime ownership was not changed.

## V1 residual reconciliation matrix

| Reviewed / historical area | V2 exact-main classification | Certification effect |
|---|---|---|
| Canonical Definition identity, naming and description | **PRESENT / PROMOTED** — unchanged from V1 audit baseline. | Pass. |
| Lifecycle status + revision/CAS | **PRESENT / PROMOTED** — canonical draft/published/disabled/archived mutation and revision-aware save/status/import remain accepted. | Pass. |
| Lifecycle history/diff browser | **OPTIONAL / NON-BLOCKING FOR V1** per accepted post-discovery/V1 audit accounting. | Not a blocker. |
| Object-type search/grouping and preserved external/missing keys | **PROMOTED / CLOSED**. | Pass. |
| Association health | **PROMOTED / CLOSED**. | Pass. |
| Dependency/usage summary | **PROMOTED / CLOSED** through Issue #609 / PR #610. | Pass. |
| Definition-list runtime health + dependency count | **PROMOTED / CLOSED** with packaged list evidence. | Pass. |
| Adaptive/reviewed labels and family reset/hydration | **PROMOTED / CLOSED**. | Pass. |
| Visibility/inheritance and dormant explicit child state | **PROMOTED / CLOSED**. | Pass. |
| Rewrite/query-var authoring, collision preview and deferred soft refresh | **PROMOTED / CLOSED**. | Pass. |
| Native capability-map authoring + current-admin warning | **PROMOTED / CLOSED**. | Pass. |
| Canonical Surface 30 role-impact backend/read model | **PROMOTED / CLOSED BACKEND** through #618/#620 and #621/#623. | Pass. |
| Visual role-impact preview | **PROMOTED / CLOSED IN #629/#631** with packaged browser/Axe evidence. | **Former blocker closed.** |
| REST authoring/controller safety | **PROMOTED / CLOSED** with allowlisted provider ownership. | Pass. |
| Default-term authoring + native lifecycle | **PROMOTED / CLOSED**. | Pass. |
| Bounded term-query semantics + performance | **PROMOTED / CLOSED**. | Pass. |
| Controlled providers | **PROMOTED / CLOSED** with provider-ID allowlisting/redaction/stale-ID handling. | Pass. |
| CPT UI compatibility import | **PROMOTED / CLOSED** through canonical create/update-CAS path. | Pass. |
| Owner-specific Definition portability | **PROMOTED / CLOSED OWNER SEAM**. Generic package orchestration remains outside Surface 2. | Pass. |
| Guarded taxonomy-key migration planning | **PROMOTED / CLOSED PLANNING**. | Pass. |
| Taxonomy-key migration execution | **SEPARATELY GATED / NOT AUTHORIZED BY V1 CONTRACT LANE**. Ordinary save/import remains immutable. | Not a V1 certification blocker; no execution authorization. |
| Generic package orchestration | **CROSS-SURFACE / OUTSIDE SURFACE 2**. | Not a Surface 2 blocker. |
| Validation + field/severity diagnostics | **PROMOTED / PRESENT**. | Pass. |
| Find Setting / tier navigation / reviewed accessible editor structure | **PROMOTED / PRESENT** from accepted implementation evidence; final closure adds no regression. | Pass. |
| Accessibility of promoted Taxonomy paths | **PROMOTED FOR TESTED V1 PATHS**; final role-impact/sticky state has zero Axe violations. | Pass. |
| Unsaved-change guard | **PROMOTED / CLOSED IN #629/#631**. | **Former blocker closed.** |
| Sticky Validate / Save commands | **PROMOTED / CLOSED IN #629/#631**. | **Former blocker closed.** |
| Broad reset field/section/all wording | **NO OPEN-ENDED GAP AUTHORIZED**; accepted family-specific reset/default behavior remains the V1 interpretation unless a specific reviewed missing control is proven. | Not independently blocking. |
| No UI-only authorization assumptions / no arbitrary executable input | **PRESERVED**; final closure consumes read-only canonical role impact and adds no executable config or authorization path. | Pass. |

## Why the V2 decision differs from V1

The V1 audit did not fail because of an unknown backend gap. It identified exactly three observable reviewed admin-interaction gaps and explicitly directed a narrow closure slice followed by a fresh audit.

Issue #629 / PR #631 implements exactly that slice. The current V2 audit verifies implementation code, packaged browser assertions, accessibility evidence, and exact-head CI. No additional product/runtime code changed after the accepted V1 baseline except the bounded closure itself.

Therefore retaining `NOT_CERTIFIED` would require a new exact-main blocker. This audit finds none within the accepted Surface 2 V1 owner-runtime boundary.

## Non-blocking / separately gated work after certification

The following remain real boundaries but do not invalidate this Surface 2 V1 runtime certification:

- **Taxonomy-key migration execution:** still unavailable and must remain separately safety-authorized with destructive migration/recovery evidence before any future executor exists.
- **Product parity certification:** not established here. `PRODUCT_PARITY_CERTIFIED` remains unpromoted.
- **Generic package orchestration:** remains a Configuration Packages / Import-Export / Platform responsibility; Surface 2 only exposes its owner-specific canonical Definition seam.
- **Full Roles & Capabilities runtime/product certification:** Surface 30's bounded canonical read seam is sufficient for this consumer, but Surface 30 itself is not certified by this audit.
- **Future history/diff or deeper UX:** may be added later, but accepted evidence does not make it a V1 exit blocker.
- **Deployment/release:** not authorized by this audit.

## Parent #474 closeout recommendation

After this audit PR merges, Supervisor should:

1. reconcile the exact new main SHA;
2. record the V2 certification result in `config/coordination/agent-work-queue.json` and remove the #632 audit slot;
3. update README/current shared truth once with the exact evidence-backed Surface 2 status;
4. update any repository machine lifecycle status only if its schema supports the evidence-backed `RUNTIME_CERTIFIED` state without inventing product parity;
5. comment on Issue #474 with the exact audit closure and remaining separately gated boundaries; and
6. close #474 if no independently accepted Surface 2 V1 residual remains after that reconciliation.

Do **not** open taxonomy-key migration execution merely to close #474. That execution is a separate safety program, not an implied V1 certification prerequisite.

## Lifecycle decision

- Surface 2 accepted V1 owner runtime: **RUNTIME_CERTIFIED**.
- Surface 2 `PRODUCT_PARITY_CERTIFIED`: **NOT PROMOTED**.
- Taxonomy-key migration execution: **NOT AUTHORIZED**.
- Generic package orchestration: **OUTSIDE SURFACE 2**.
- Deployment/release: **NOT AUTHORIZED**.
- Parent Issue #474: **eligible for Supervisor closeout after this audit decision merges; not closed from the worker branch.**
