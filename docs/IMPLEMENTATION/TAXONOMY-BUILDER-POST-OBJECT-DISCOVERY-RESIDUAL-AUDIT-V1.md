# Taxonomy Builder — Post Object-Type Discovery Residual Audit V1

Surface: **2 / Taxonomy Builder**  
Parent implementation issue: **#474**  
Audit issue: **#584**  
Supervisor wave: **#583**  
Exact-main audit anchor: `6e7a79e680f08a58676e44191e1a59cf16dcee2f`  
Latest promoted UX slice: **Issue #577 / merged PR #578**

This is an exact-main accounting audit only. It does not add runtime behavior, authorize taxonomy-key migration execution, grant roles, promote generic package orchestration into Surface 2, or certify `RUNTIME_CERTIFIED` / `PRODUCT_PARITY_CERTIFIED`.

## Audit inputs

The audit reconciles:

- `docs/UI/TAXONOMY-BUILDER-UX-CONTRACT-V1.md`;
- `docs/IMPLEMENTATION/TAXONOMY-BUILDER-RUNTIME-GAP-MATRIX-V1.md`;
- the exact-main Taxonomy admin controller and `admin-ui/src/taxonomy*.ts` enhancement code;
- packaged Taxonomy browser/accessibility evidence;
- the promoted runtime/evidence slices already enumerated by the Runtime Gap Matrix;
- merged PR #578 object-type discovery behavior.

The Runtime Gap Matrix remains the canonical promoted-slice ledger. This document narrows its post-#578 residual interpretation; it does not rewrite that ledger.

## Post-#578 corrections

### Object-type search and grouping are no longer a residual

The reviewed UX contract requires a searchable object-type selector grouped into core WordPress post types, WPE CPT definitions, external runtime post types and preserved missing/external keys. Merged PR #578 now implements that discovery layer while preserving the existing canonical association payload and server-authoritative health rules.

The packaged browser evidence proves that:

- the core WordPress group is rendered;
- search filters visible discovery rows without changing the selected association state;
- preserved external keys remain visible and editable rather than being silently dropped;
- create/edit hydration retains canonical associations;
- the Taxonomy Builder remains axe-clean on the exact promoted behavior.

Therefore **search/grouping itself is CLOSED and must not be reopened as a duplicate #474 slice**.

## Residual classification

| Candidate | Classification after #578 | Exact-main evidence / boundary | Recommended disposition |
|---|---|---|---|
| Object-type search/grouping | **ALREADY PROMOTED / CLOSED** | PR #578 plus packaged browser/axe evidence implements the reviewed selector grouping/search and preserved-key behavior. | Do not reopen. |
| Object-type dependency impact | **GENUINE SURFACE 2 READ-MODEL / UX RESIDUAL** | The editor exposes association health and discovery state, but the reviewed diagnostics contract also calls for a dependency/usage summary. Key-migration preview has migration-specific association/dependent impact, which is not a general editor/list dependency summary. | Bounded read-only dependency-summary slice; no association mutation ownership change. |
| Definition-list revision | **ALREADY PROMOTED / CLOSED** | Exact-main list columns already include Revision and the client renders `definition.revision`. | Do not reopen. |
| Definition-list runtime health | **GENUINE SURFACE 2 UX RESIDUAL** | The reviewed Definition-list contract requires runtime health. Exact-main saved-taxonomy columns are Name, Key, Object types, Status, Revision and Actions; runtime health is absent from the list even though validation diagnostics already expose runtime registration state. | Add a server-authoritative read-only list projection; do not create a second health compiler. |
| Definition-list dependency count | **GENUINE SURFACE 2 UX RESIDUAL** | The reviewed Definition-list contract requires dependency count; exact-main list has no dependency-count column/read model. | Implement together with dependency-summary read model when ownership can be resolved safely. |
| Lifecycle history/diff | **OPTIONAL / NOT A V1 CONTRACT EXIT REQUIREMENT BY ITSELF** | The Runtime Gap Matrix calls history/diff a potential lifecycle presentation area, while the reviewed UX contract explicitly requires revision in the list but does not require a full revision-history/diff browser. | Do not block #474 on a new history system unless a later accepted contract explicitly promotes it. |
| Capability current-user lockout warning | **ALREADY PROMOTED / CLOSED** | Capability authoring exposes the four native capability names, WordPress-default state and validation guidance/current-user lockout diagnostics. | Do not reopen. |
| Capability role-impact preview | **GENUINE CROSS-SURFACE READ-MODEL UX RESIDUAL** | The reviewed UX contract explicitly calls for a read-only role-impact preview. Exact-main Taxonomy capability UI states that role grants remain owned by Roles & Capabilities and exposes effective/current-user diagnostics, but it does not provide the broader role-impact preview. | Surface 2 may consume a read-only Roles & Capabilities projection; it must not own grants, memberships or a parallel role rules engine. |
| Label reset/hydration | **ALREADY PROMOTED / CLOSED** | PRs #486/#489 are recorded as covering reviewed label authoring/reset/hydration. | Do not reopen. |
| Family-specific visibility/rewrite/capability/provider reset/default semantics | **SUBSTANTIALLY PROMOTED; VERIFY ONLY ACTUAL MISSING CONTRACT CONTROL** | Existing slices preserve Default/Explicit inheritance and reset semantics across promoted families. A generic reset feature must not be inferred merely from wording in the global UX principles. | No broad reset project. Any future reset work must name the exact missing reviewed control and prove it is not already covered. |
| Naming/help text | **NO STANDALONE RUNTIME GAP; BOUNDED UX CLEANUP ONLY IF SPECIFIC CONTRACT EVIDENCE EXISTS** | Name/singular/description editing and field descriptions are present. The current matrix already limits this area to demonstrable missing reviewed semantics. | Do not create an open-ended copy/help lane. |
| Dependency/usage summary in diagnostics | **GENUINE SURFACE 2 READ-MODEL / UX RESIDUAL** | Current diagnostics render effective args, explicit overrides, runtime registration, REST/rewrite preview, providers and association health. The reviewed contract additionally requires dependency/usage summary. | Prefer one canonical dependency read model shared with Definition-list dependency count. |
| Guarded taxonomy-key migration planning | **ALREADY PROMOTED / CLOSED** | PR #580 provides non-mutating target validation, impact/conflict and recovery planning. | Do not reopen planning. |
| Taxonomy-key migration execution | **BLOCKED BY DESIGN / NOT AN ACTIVE #474 SLICE** | Ordinary save/import paths keep the key immutable and the current governance explicitly withholds a rename executor. | Requires separate explicit safety authorization, migration/recovery evidence and its own issue before any mutation code. |
| Owner-specific Taxonomy Definition portability | **ALREADY PROMOTED / CLOSED** | PR #572 provides the canonical owner import seam with revision/CAS rules. | Do not reopen. |
| Generic package envelope, multi-owner preflight/conflict orchestration and import/export UI | **CROSS-SURFACE OWNERSHIP — NOT SURFACE 2** | The Runtime Gap Matrix assigns this to Configuration Packages / Import-Export. Surface 2 must only expose/invoke its canonical owner seam. | Track under Surface 26 / package owner, not #474. |
| Final exact-main runtime/product certification | **LATER SUPERVISOR AUDIT** | Green slice evidence is not itself full-surface certification. | Run only after accepted V1 residuals are closed/accounted. |

## Exact-main UI evidence relevant to the remaining read models

The saved-taxonomy table currently presents:

`Name | Key | Object types | Status | Revision | Actions`

That closes the reviewed revision-list requirement but leaves the contract's **runtime health** and **dependency count** columns unimplemented.

The validation diagnostics already provide a safe server-authoritative pattern for runtime state: client code accepts a diagnostics payload containing effective args, overrides, provider IDs, association health, runtime registration state and REST/rewrite previews. A future list-health slice should reuse/extend canonical server projections instead of recompiling WordPress/runtime truth in the browser.

Likewise, exact-main capability authoring explicitly preserves the Roles & Capabilities boundary: Taxonomy authors the four native capability names and can warn about the current user's effective checks, but role grants remain external. The missing broader role-impact preview must therefore be a read-only consumer of Roles & Capabilities truth, not a Taxonomy-owned role model.

## Dependency-safe next slices under #474

The following sequence is narrow enough to avoid reopening already-promoted work.

### Slice A — Definition List Runtime Health + Dependency Summary UX V1

Scope:

- one server-authoritative read model for dependency/usage summary;
- Definition-list runtime health;
- Definition-list dependency count;
- diagnostics dependency/usage presentation;
- object-type dependency-impact presentation only to the extent supplied by the same canonical read model;
- browser/accessibility evidence for list and diagnostics presentation.

Guardrails:

- no browser-side dependency compiler;
- no association mutation changes;
- no generic package orchestration;
- no migration execution;
- no cross-owner storage writes.

This is the highest-value next Surface 2 slice because it closes multiple explicit V1 information-architecture requirements with one read-only source of truth.

### Slice B — Read-Only Capability Role-Impact Preview V1

Scope:

- consume an existing or separately approved Roles & Capabilities read-only projection;
- show which roles are affected by the authored/effective four native Taxonomy capability names;
- preserve current-admin lockout warning and effective-default diagnostics;
- add browser/accessibility evidence.

Guardrails:

- no role creation;
- no grant/membership mutation;
- no duplicated role-resolution engine inside Taxonomy;
- fail closed or show unavailable state when a canonical cross-surface projection is unavailable.

If no canonical Roles & Capabilities read seam exists on exact main, this slice is **dependency-blocked** and must not invent one inside Surface 2. The integration requirement should instead be handed to the owning surface.

### Slice C — Contract-Required Reset Coverage Audit / Small Closure V1

Run only after A/B and only if exact UI evidence identifies a reviewed reset control that is still missing.

Scope must enumerate the specific control(s). A generic “reset everything” implementation is not authorized merely to satisfy wording in the global UX principles, especially where dormant explicit values must remain preserved by design.

### Final — Exact-Main Surface 2 Certification Audit

After all accepted V1 residuals are either promoted, explicitly cross-surface, or deliberately blocked-by-design, perform a fresh exact-main evidence audit. Only that later Supervisor decision may consider a runtime lifecycle promotion. Product parity remains separately gated.

## Items that must not be scheduled from this audit

Do not create #474 child work for:

- object-type search/grouping;
- Definition-list revision;
- label authoring/reset;
- rewrite/query-var authoring and deferred soft refresh;
- REST authoring/collision diagnostics;
- default-term lifecycle;
- bounded term-query semantics/performance;
- provider catalog/selectors;
- CPT UI compatibility import;
- owner-specific Definition portability;
- taxonomy-key migration planning;
- taxonomy-key migration **execution** without a new explicit safety authorization;
- generic Configuration Package / Import-Export orchestration;
- role grants or membership mutation.

## Exit decision

Issue #474 **must remain open** after this audit.

The post-#578 genuine V1 residual is no longer “object-type discovery.” The evidence-backed implementation runway is now primarily read-only information architecture:

1. canonical dependency/usage summary + list runtime health/dependency count; and
2. reviewed read-only capability role-impact preview, subject to a canonical Roles & Capabilities read seam.

Everything else above is either already promoted, optional/non-blocking, cross-surface, or explicitly blocked by safety governance. This narrower accounting should be used when opening the next bounded #474 child issue and when deciding whether a later exact-main certification audit is justified.
