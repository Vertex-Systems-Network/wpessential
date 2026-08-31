# WPEssential — Status Manager: Post Status & Generic State-Machine Runtime

Status: **Phase 0 paper architecture / no implementation authorized**  
Related: Relations/Status exhaustive spec, Workflow, Policy, Custom Fields, Data Sources, ADR-0014.

## 1. Two status domains

WPE Status Manager has two intentionally separate modes:

### A. WordPress Post Status Adapter
Integrates with WordPress `post_status` and registered post statuses.

### B. Generic Domain State Machine
Defines application states/transitions for WPE/custom Data Source entities.

Membership Enrollment, Workflow runs and other modules with specialized lifecycle machines remain owned by those modules. Status Manager may inspect/integrate, but does not replace their canonical state model.

## 2. Why separate

WordPress post status has core-specific storage/UI/query semantics. A generic business state such as `invoice_reviewing` or `ticket_waiting_customer` may belong to a custom table/entity and require transition guards/history impossible to express safely as a global WordPress post status.

Do not force every domain state into `wp_posts.post_status`.

---

# Part A — WordPress Post Status

## 3. Definition

WPE-owned Post Status definition stores:
- UUID;
- machine key;
- label + plural count label;
- visibility flags compatible with WordPress;
- public/internal/protected/private/queryability semantics;
- admin status-list/all-list flags;
- date-floating behavior where relevant;
- allowed/visible post-type mappings in WPE UI adapters;
- color/icon/presentation metadata;
- transition/permission policy optional;
- status ownership/source;
- status definition revision.

## 4. WordPress storage constraint

Actual post status value lives in `wp_posts.post_status` and must respect database/core constraints.

WPE validates the accepted status-key length/format against supported WordPress/DB behavior rather than relying only on `register_post_status()` accepting a sanitized key.

## 5. Global registry vs post-type availability

WordPress registered post statuses are global. Therefore WPE models **registration** separately from **where this status is offered/allowed**.

Post-type availability layer can say:
- available for selected WPE-owned CPTs;
- visible in WPE edit/status selector;
- available in quick/bulk edit adapter if certified;
- available through Forms/Workflow/REST transition ability;
- hidden for unrelated post types.

This layer cannot pretend WordPress itself natively binds a registered status to one CPT.

## 6. Core/third-party status ownership

Status registry classifies:
- WordPress core;
- WPE-owned;
- third-party/unknown.

Default mutation rules:
- WPE-owned: editable with migration/dependency controls;
- core: inspect/presentation/integration only unless a separately accepted advanced operation exists;
- third-party: inspect + optional WPE availability/presentation overlays; destructive unregister/delete not ordinary behavior.

Unknown status is preserved, not treated as invalid merely because WPE did not create it.

## 7. Rename/key change

Label rename is presentation change.

Machine-key change is data migration:
1. count affected posts;
2. inspect Query/Listings/Workflow/Form dependencies;
3. register target status;
4. migrate post rows in bounded/recoverable operation;
5. verify counts;
6. update WPE references;
7. retain alias/history where needed;
8. unregister/archive old WPE status only after verification.

No direct key rename while posts still contain old value.

## 8. Delete/archive

A WPE status with existing posts cannot be simply removed.

Options:
- Archive definition: stop offering for new transitions, preserve registration/runtime compatibility for existing content.
- Migrate then remove: choose target status, preview affected posts/dependencies, migrate/verify, then remove WPE registration.

Core/third-party statuses are not destructively deleted through ordinary WPE UI.

## 9. Transition rules for posts

Optional WPE transition policy can define:
- from status(es);
- to status;
- capability/Policy;
- conditions;
- required fields;
- confirmation severity;
- Workflow/event hooks.

Direct WordPress/plugin writes outside WPE may bypass this transition policy unless WPE implements certified enforcement hooks. UI must label enforcement coverage honestly.

Do not claim global unbypassable workflow enforcement before executable compatibility proof.

## 10. Post transition execution

For WPE-controlled action:
1. authorize post/action;
2. load current status/version/context;
3. validate transition/guards;
4. perform WordPress update using core API;
5. verify resulting status;
6. write WPE transition audit/history if enabled;
7. emit typed event after authoritative state change;
8. enqueue non-critical Workflow/Notification side effects.

Avoid double-sending side effects from both WPE and core transition hooks through idempotency/correlation design.

---

# Part B — Generic Domain State Machine

## 11. State Machine Definition

Definition Repository owns:
- machine UUID/key;
- target Data Source/entity type;
- states;
- initial-state policy;
- transitions;
- guards/conditions;
- permissions;
- terminal-state markers;
- UI/presentation metadata;
- events/actions references;
- history/retention policy;
- version.

## 12. State definition

Each state:
- UUID;
- stable key;
- label/description;
- color/icon/badge presentation;
- category optional (`open`, `closed`, etc. as display grouping only);
- terminal flag;
- selectable/manual-entry flag;
- SLA/timer metadata optional;
- permissions/visibility if needed.

Display category never substitutes actual state key.

## 13. Transition definition

Each transition:
- UUID/key;
- from state(s);
- to state;
- label/action verb;
- actor capability/Policy;
- Condition Engine guard;
- required field/value validation;
- confirmation level;
- idempotency/retry classification;
- event name;
- Workflow hook/reference;
- optional reason/comment requirement.

No arbitrary PHP guard/action.

## 14. Current state storage

Target Data Source adapter declares where current state is stored:
- dedicated typed column — preferred for custom tables/high-query domains;
- registered post/user/term meta only for appropriate lightweight domains;
- external provider state adapter where certified.

The generic state machine definition does not force one universal storage table for every entity.

## 15. Transition history

Structured runtime history candidate stores:
- entity type/ref;
- state-machine UUID/revision;
- from/to state;
- transition UUID;
- actor/source;
- requested/effective/processed timestamps;
- reason code/note safe classification;
- correlation/idempotency key;
- result;
- source event/reference where applicable.

History is separate from generic Audit Log because domain analytics/reconstruction can require structured transition records. Generic Audit may link to it.

## 16. Concurrency

Transitions use optimistic version/transactional guard where Data Source supports:
- read current state + version;
- validate expected state;
- update only if version still matches;
- append history atomically where possible;
- conflict returns structured stale-state error.

Two simultaneous transition requests cannot both assume the same old state and silently overwrite each other.

## 17. Initial state

Options:
- explicit fixed initial state;
- condition-derived initial state through bounded policy;
- imported/external existing state mapping.

Creation fails or enters explicit `uninitialized/degraded` handling if required state cannot resolve; do not silently choose first UI state.

## 18. Terminal states

Terminal means ordinary transition graph has no outgoing transitions unless explicit reopen/reactivate transition is defined.

Terminal does not mean row deletion.

Reopening is a named audited transition, not direct value edit.

## 19. State field editing

When entity participates in a managed state machine, current-state field is not an ordinary free select in generic Custom Fields editor by default.

UI should expose **allowed transition actions** so guards/history/permissions execute.

Privileged force-state repair, if offered, is a separate high-risk audited ability with reason and recovery implications.

## 20. Workflow integration

State transition emits typed event after authoritative state commit.

Workflows can:
- react to transition;
- request another allowed transition;
- send notifications;
- create/update related data.

Workflow failure does not silently roll back already committed state unless a specifically transactional local operation was designed for that boundary.

## 21. Timed transitions / SLA

Automatic timed transitions use Job Service.

Examples:
- expire after date;
- move `waiting` → `stale`;
- SLA warning event.

Authorization correctness must still respect timestamps if exact job dispatch is late where domain requires.

No PHP request sleep/timer.

## 22. UI integration registry

A status/state integration can expose in:
- admin edit panel;
- list column/filter;
- quick/bulk action;
- frontend Dashboard action;
- Form action;
- REST endpoint/Ability;
- Dynamic Listing badge;
- Workflow trigger/action.

Each adapter declares support; “status exists” does not imply every WordPress screen automatically supports it.

## 23. Query integration

Query Builder can filter/sort by current state through Data Source's typed field/provider.

Transition history querying uses separate provider/aggregate and remains paginated/bounded.

## 24. Import/migration

Import maps:
- source state keys;
- target state UUID/key;
- terminal/active semantics;
- transition history where source genuinely provides it.

Unknown source state is `unsupported/conflict`, never silently mapped by label similarity.

Changing state-machine keys/revisions must preserve existing entity state through migration mapping.

## 25. Failure/degraded states

- target Data Source missing → machine disabled/degraded, data untouched;
- state definition removed while entities use it → publish blocked or migration required;
- transition target missing → invalid definition;
- Workflow/action missing → transition may remain valid if side effect optional; health warning;
- history write fails around state commit → exact transactional/reconciliation strategy required before implementation;
- Pro expiry → existing state rendering/transitions required for safe deployed runtime evaluated under ADR-0007; editing definitions restricted.

## 26. Security

- route/UI visibility is not transition authority;
- transition uses actor + target resource Policy;
- no arbitrary state mutation through generic REST meta write when managed machine requires transition semantics;
- force repair high privilege;
- reason/history output respects privacy;
- no status label used as capability.

## 27. Future executable evidence — NOT AUTHORIZED

After explicit consent:
- WordPress post-status registration/editor/quick-edit/list-query matrix;
- key length/storage fixtures;
- custom status migration with existing posts;
- core/third-party coexistence;
- generic state custom-table/meta adapters;
- concurrent transition race;
- history transaction/reconciliation;
- Workflow idempotency;
- REST/Form direct-write bypass prevention;
- large history indexes;
- multisite.

## Paper recommendation

Accept **WordPress Post Status Adapter** and **Generic Domain State Machine** as separate Status Manager engines sharing presentation, transition Policy, events and diagnostics—not one universal status storage primitive.