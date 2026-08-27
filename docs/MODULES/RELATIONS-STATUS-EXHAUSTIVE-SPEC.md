# WPEssential — Relations Builder & Status Manager Exhaustive Specification

Status: **Phase 0 planning only — no development consent**

Modules:
- **Relations Builder — Pro**
- **Status Manager — Pro**

This document deepens `CONTENT-MODEL-SPECS.md` and `OPTION-INVENTORY.md`.

## Research baseline

Reviewed current official/primary documentation for:

- WordPress `register_post_status()` semantics;
- Meta Box Relationships, including from/to sides, one-side cardinality limits, reciprocal mode, query filtering and admin integrations;
- Pods relationship/link-table patterns for relationships carrying extra contextual fields;
- PublishPress Statuses workflows, branches, visibility statuses and per-status permission patterns.

WPEssential deliberately separates:

1. **Relation Definition** — structural connection between data-source entities;
2. **Relation Link** — one persisted connection instance, optionally with pivot metadata;
3. **WordPress Post Status** — WordPress registration primitive;
4. **Domain State Machine** — generic application workflow state model for forms, tickets, custom-table records, membership/process records and extension entities.

---

# Part A — Relations Builder

# 1. Relation list screen

Columns:

1. Name
2. Key
3. From endpoint
4. To endpoint
5. Cardinality
6. Links count
7. Pivot fields count
8. Status
9. Used by
10. Health
11. Updated
12. Actions

Filters:

- Draft / Published / Disabled / Archived
- endpoint source/type
- one-to-one / one-to-many / many-to-many
- self relation
- has pivot metadata
- healthy / warning / degraded

Actions:

- Edit
- Manage links
- Preview/query
- Duplicate
- Used by
- Revisions
- Export
- Disable/Enable
- Archive/Restore
- Delete definition

---

# 2. Relation editor sections

1. General
2. From Endpoint
3. To Endpoint
4. Cardinality
5. Link Rules
6. Pivot Fields
7. Admin Editing
8. Query & Display
9. Delete / Integrity Policy
10. Permissions
11. REST & Abilities
12. Performance / Storage
13. Dependencies
14. Diagnostics
15. Revisions
16. Export

---

# 3. Identity

Options:

- name — required
- stable UUID — system-generated, immutable
- machine key — generated, unique, immutable after references exist except explicit migration
- description — optional
- status — Draft default

Relation machine key is internal WPEssential configuration identity; it is not a database table name by default.

---

# 4. Endpoint contract

Each endpoint independently declares:

- endpoint label
- reverse/directional label
- Data Source provider
- entity type/subtype
- record-query filter UUID optional
- selectable-status/visibility policy
- editor title
- empty message
- create-new target shortcut enabled false by default
- endpoint view policy
- endpoint link-management policy

Initial source families:

- posts/CPTs
- users
- terms/taxonomies
- media
- comments where sensible
- WPEssential Custom Tables
- WPEssential runtime entities
- registered external Data Source

Changing source/provider/entity subtype after links exist is migration-class and blocked by ordinary edit flow.

---

# 5. Direction semantics

Every relation stores canonical **From → To** orientation even though traversal may be bidirectional.

Options:

- from label singular/plural
- to label singular/plural
- reverse traversal enabled: true default
- reciprocal/self-relation mode: false default

## Reciprocal self relation

Only valid when endpoints have compatible source/entity schema.

Example: Person ↔ Person colleague relation.

Rules:

- canonical pair ordering prevents duplicate A→B and B→A representations when reciprocal semantics say they are the same link;
- UI may show one relation editor instead of mirrored controls;
- directional pivot data incompatible with true reciprocal semantics requires explicit non-reciprocal self relation instead.

---

# 6. Cardinality model

Persisted cardinality is explicit; no hidden default.

Supported:

- one-to-one
- one-to-many
- many-to-one — normalized representation of one-to-many orientation in UI
- many-to-many

Cardinality settings:

- max From links per To entity
- max To links per From entity
- min required links per side — validation/business policy, not always DB-enforceable

Derived presets:

### One-to-one

- max from per to = 1
- max to per from = 1

### One-to-many

- one parent can connect many children
- child-side parent max = 1

### Many-to-many

- both unbounded within platform safety/configured max

Database uniqueness/index strategy must enforce maximum=1 where physically possible rather than only UI validation.

---

# 7. Per-entity connection limits

Beyond cardinality preset:

- minimum links
- maximum links
- optional Query/Condition-based eligibility
- duplicate pair allowed: false v1

A user-configured max cannot exceed platform/provider safety limits without Advanced policy.

Concurrent attach operations must not bypass uniqueness/cardinality.

---

# 8. Link ordering

Modes:

- unordered — default
- ordered from side
- ordered to side
- independently ordered both sides

Ordering field is integer/rank metadata managed transactionally.

Options:

- manual drag/drop
- default append
- default prepend
- automatic sort by related entity field/query for display only

Display-time sort does not rewrite stored manual order unless user explicitly chooses materialize/reorder.

---

# 9. Pivot / relation metadata

A relation link may own typed metadata.

Uses a constrained Custom Fields schema bound to relation-link storage.

Examples:

- role/title in relationship
- start/end date
- quantity
- priority
- note
- status

Rules:

- pivot fields have stable UUIDs;
- simple scalar/indexable types preferred;
- nested repeaters/flexible layouts disabled by default because link rows are relational records;
- field schema changes use migration discipline;
- relation queries may filter/sort on declared pivot fields;
- pivot values are not duplicated into endpoint meta.

---

# 10. Link lifecycle

Link state candidates:

- active — standard
- archived — optional future/history use

Do not overload relation link with workflow status unless product use case requires it; a pivot Status field can reference a domain state machine when needed.

Operations:

- attach
- attach many
- update pivot metadata
- reorder
- detach
- detach many
- replace set

`replace set` is destructive to omitted links and requires diff preview for bulk/admin APIs.

---

# 11. Admin editing UI

Per endpoint:

- show editor panel: true/false
- panel placement/context according to target adapter
- title
- description
- selector mode:
  - autocomplete
  - select
  - table/list picker
  - relationship manager
- search query UUID
- result display fields
- sort
- page size
- show current connected items
- allow attach
- allow detach
- allow reorder
- allow edit pivot fields
- allow create new target

Large result sets must use server query/search; never preload all users/posts/terms.

---

# 12. Admin filter & admin-column quick integrations

Relations Builder may expose quick toggles:

- offer relationship filter on supported list screen
- offer connected-count column
- offer connected-item column

These toggles create/link definitions in **Admin Columns Builder** or registered list-filter service when Pro modules are available.

Relations Builder does not maintain a second private admin-column engine.

---

# 13. Query integration

Relations expose Query Builder primitives:

- traverse From→To
- traverse To→From
- filter by linked entity
- filter by pivot field
- relation exists/not exists
- relation count
- relation aggregate over pivot fields
- ordered related records

Query result authorization applies to endpoint entities independently.

---

# 14. Link storage direction

Preferred planning model: dedicated relation-link tables keyed by relation UUID/internal numeric relation identity, not serialized endpoint meta.

Physical candidate row concepts:

- link ID
- relation ID/UUID reference
- from entity canonical ID/reference
- to entity canonical ID/reference
- from order
- to order
- created/updated timestamps
- actor/source metadata where audit needs it

Pivot fields may use:

- typed columns when relation-specific schema warrants it;
- dedicated relation-meta table;
- explicit structured payload only when query requirements are low.

Exact physical schema remains an ADR/benchmark item.

---

# 15. Polymorphic/entity references

If a relation endpoint can target multiple entity subtypes, canonical reference must include source/type information; raw integer IDs alone are ambiguous across posts/users/terms/custom tables.

Default product direction:

- endpoint definition is subtype-constrained where possible;
- polymorphic endpoint is Advanced and requires provider-specific identity contract.

---

# 16. Delete policy

When **relation definition** is deleted:

Default: retain data during archive/soft-delete stage; actual relation-row purge is separate destructive cleanup action.

When an **endpoint entity** is permanently deleted:

Policy options:

1. Detach links — default
2. Restrict deletion while links exist
3. Registered custom policy

Not ordinary v1 option:

- cascade-delete connected entities

Cascade deleting endpoint entities is too dangerous for generic no-code UI.

---

# 17. Missing/deactivated endpoint provider

Relation becomes **Degraded**, not corrupted.

Behavior:

- definition remains readable/exportable;
- links retained;
- edits requiring missing provider disabled;
- diagnostics identify provider/plugin missing;
- no automatic link purge.

---

# 18. Permissions

Distinct capabilities/policies:

- list relation definitions
- read definition
- create/edit/publish definition
- delete definition
- view links
- attach
- detach
- edit pivot metadata
- bulk link management
- export
- run integrity repair

Object-level rule:

Ability to edit entity A does not automatically mean user may connect it to entity B.

Attach policy can require:

- permission on relation
- permission on source entity
- permission to view/select target
- optional target edit/link capability

---

# 19. REST & Abilities

Potential typed abilities:

- relation.list
- relation.get
- relation.links.list
- relation.attach
- relation.detach
- relation.pivot.update
- relation.explain_integrity

All use typed canonical entity references.

No endpoint accepts arbitrary table/key callbacks.

Bulk attach/detach supports idempotency keys where exposed externally.

---

# 20. Import/export

Definition export:

- relation UUID
- endpoint definitions
- cardinality
- ordering
- pivot schema refs
- policies
- dependencies

Runtime link data export is separate Data Import/Export scope.

Import must remap endpoint/provider/field UUIDs and cannot assume numeric IDs are portable across sites.

---

# 21. Relation revisions

Semantic diff includes:

- endpoint source/type changed
- cardinality changed
- limit changed
- reciprocal changed
- ordering changed
- pivot field added/removed/type changed
- delete policy changed
- permission changed

Cardinality tightening with existing violating links blocks Publish until conflict-resolution plan exists.

---

# 22. Integrity diagnostics

- orphan from entity
- orphan to entity
- duplicate pair
- one-to-one violation
- one-to-many violation
- min-cardinality business violation
- pivot schema mismatch
- unknown endpoint provider
- invalid polymorphic reference
- missing indexes
- stale cached relation count
- ordering duplicates/gaps where problematic

Repair actions must preview affected rows and be audited.

---

# 23. Relation acceptance tests

- each cardinality
- concurrent attach race
- duplicate pair prevention
- reciprocal self relation
- non-reciprocal self relation
- pivot save/query
- from/to independent ordering
- target permission denied
- endpoint provider disabled/restored
- source/target permanent deletion
- detach vs restrict
- cardinality migration with violating data
- import UUID remapping
- query traversal both directions
- relation cache invalidation
- bulk replace diff/idempotency

---

# Part B — Status Manager

# 24. Two distinct products inside one module

Status Manager UI has two top-level tabs:

1. **WordPress Post Statuses**
2. **Domain State Machines**

They share presentation/workflow helpers but never pretend `register_post_status()` is a generic application state-machine API.

---

# 25. WordPress post-status list

Columns:

- Label
- Key
- Core/External/WPE source
- Public/Internal/Protected/Private summary
- Search
- Queryable
- Admin lists
- Assigned post types/integrations
- Posts count
- Health
- Actions

Core/external statuses default read-only.

WPE-owned status actions:

- Edit
- Duplicate
- Disable/Enable
- Usage
- Export
- Delete definition

---

# 26. WordPress post-status identity

### key

- required
- sanitize key semantics
- practical storage maximum **20 characters** because `wp_posts.post_status` is length-constrained even though `register_post_status()` itself sanitizes without enforcing that DB limit
- reserved/core/external collisions blocked

Key change after content exists is migration-class.

### label

Required human label.

### label_count

Inputs:

- singular count label
- plural count label

Database-configured labels cannot be treated like source-code gettext literals; localization strategy is data/content translation, not compile-time gettext extraction.

---

# 27. Exact WordPress registration flags

Expose all public documented arguments except core-only `_builtin`.

## `public`

Nullable/inherit in definition editor until preset expands; effective WordPress default false unless another flag/default relationship applies.

## `internal`

Explicit boolean/inherit.

Important WordPress behavior: if public/internal/protected/private are all unspecified, WordPress makes status internal.

## `protected`

Explicit boolean.

## `private`

Explicit boolean.

## `publicly_queryable`

Inherit from public / true / false.

## `exclude_from_search`

Inherit from internal / true / false.

## `show_in_admin_all_list`

Inherit from inverse internal / true / false.

## `show_in_admin_status_list`

Inherit from inverse internal / true / false.

## `date_floating`

Advanced boolean; false by default.

UI explains this is a WordPress core status timing characteristic, not a generic workflow scheduling option.

## `_builtin`

Never editable; read-only diagnostic for core statuses.

---

# 28. Post-status presets

Preset expands to explicit/inherited flags before Publish.

Potential presets:

- Pre-publication/editorial
- Public published-like
- Private/visibility
- Internal/system
- Advanced custom

Do not claim every custom combination is fully supported by every WordPress editor/plugin.

Compatibility preview is mandatory.

---

# 29. Post-type availability

WordPress status registration itself is global, but WPEssential editor/workflow integration can scope availability.

Options:

- allowed post types
- excluded post types
- show in block editor integration
- show in classic editor integration
- allow bulk edit where safely supported
- allow quick edit where safely supported
- external editor compatibility status

The module must not imply core `register_post_status()` has a native `post_types` argument.

---

# 30. WordPress editor workflow sequence

WPEssential may define a **presentation/workflow sequence** over statuses for selected post types.

This is separate metadata from `register_post_status()`.

Options:

- workflow name
- post types
- ordered statuses
- default workflow
- alternate workflow
- branches/sub-statuses
- guidance mode:
  - next permitted status
  - highest permitted status
  - show all permitted transitions
- allow skipping steps
- require transition graph

Core statuses with essential WordPress semantics cannot be casually deleted/overridden.

---

# 31. Per-status assignment/edit/delete permissions

Status-specific content permissions can be modeled through shared Policy/Role integrations.

Operations may include:

- set/assign this status
- edit own content in this status
- edit others' content in this status
- delete own content in this status
- delete others' content in this status
- transition out
- transition into

Do not generate opaque capability explosion unless Role/Capability design explicitly maps it.

If capability keys are materialized, names and compatibility must be documented and reversible.

---

# 32. Transition rules for WordPress content

Each allowed transition edge:

- from status set
- to status
- actor policy
- applicable post types
- condition group
- required fields
- reason required
- confirmation level
- optional approval requirement
- side-effect workflow reference

Default behavior is no side effect.

Transition validation happens server-side when WPEssential owns the action path.

Third-party code can call WordPress APIs directly; diagnostics/docs must not falsely promise WPEssential can intercept every possible status write safely without compatibility impact.

---

# 33. Visibility/access distinction

A WordPress post status flag is not automatically a full resource access-control engine.

For advanced visibility:

- frontend access policy belongs to shared Policy/Membership/Protector integration;
- status can be an input condition to policy;
- custom status should not silently grant or remove permissions merely by color/order.

---

# 34. Status lifecycle / delete

Disabling WPE-owned status registration while posts currently use it is high-risk.

UI must show:

- count by post type
- last use
- content URLs/visibility impact
- workflow dependencies
- integrations

Default delete-definition flow blocked while posts use status unless a migration/remapping plan exists.

Migration options planned:

- map to another status
- retain unavailable status value temporarily with degraded warning
- background remap for large count

No automatic content deletion.

---

# 35. Domain State Machine list

Columns:

- Name
- Key
- Scope/entity
- States count
- Transitions count
- Initial state
- Status
- Used by
- Health
- Updated
- Actions

---

# 36. Domain state-machine identity

- name
- UUID
- machine key
- description
- scope/Data Source
- status Draft default

Changing scope after runtime records reference state machine is migration-class.

---

# 37. State definition

Each state:

- stable UUID
- key
- label
- description
- type/category optional
- initial: maximum one
- terminal: false default
- active/enabled
- color
- icon
- sort/workflow position
- public presentation label optional
- badge style/token

Color/icon are presentation only.

State key uniqueness is per machine.

---

# 38. Transition definition

Each transition:

- UUID
- name/label
- from states one/many
- to state
- enabled
- actor policy
- trigger mode manual/automatic/registered action
- condition groups
- required fields/data
- reason/comment required
- confirmation level
- approval rule
- before actions
- after actions
- failure policy
- audit verbosity

A transition is an operation, not merely setting a string field.

---

# 39. Transition side-effect ordering

Recommended semantic planning:

1. authorize
2. validate current state
3. validate conditions/required data
4. acquire concurrency guard/version check
5. execute pre-transition checks
6. persist state transition transactionally where storage supports
7. append state-history entry
8. enqueue post-transition side effects

External side effects such as email/webhooks should generally not be inside a DB transaction.

Failure after state commit is recorded as side-effect failure; state history remains truthful and Workflow retries according to idempotency policy.

If a business process requires side effect before state commitment, it must be a separately explicit transition strategy with compensation semantics.

---

# 40. Optimistic concurrency

Transition command carries current state/version expectation.

If another actor changed the record first:

- reject stale transition;
- show current state;
- do not silently overwrite.

Critical for approvals/tickets/membership workflows.

---

# 41. Guards / conditions

Condition inputs:

- current entity fields
- actor
- relations
- query results
- dates
- membership/entitlement
- registered resolver

No arbitrary PHP.

Guard result:

- allowed
- denied with safe reason/code
- unavailable dependency

---

# 42. Required fields by transition

A transition may require schema fields to be non-empty/valid.

This does not mutate field definition globally.

UI can focus/highlight missing fields before transition.

Server revalidates.

---

# 43. Approval patterns

Initial supported patterns to plan:

- single approver from role/query/relation
- any one of approver group
- all required approvers — later if storage/runtime model accepted
- manual admin approval

Approval is a Workflow/State-machine concern with audit records, not a magic status permission checkbox.

---

# 44. Automatic transitions

Triggers only through Event/Workflow/Job service:

- time elapsed
- field changed
- relation changed
- form submitted
- external webhook
- schedule
- membership/billing event

State Manager does not create a second hidden cron engine.

---

# 45. Transition history

Append-only records:

- history ID
- machine UUID
- entity reference
- from state
- to state
- transition UUID
- actor/source
- timestamp
- reason/comment
- correlation/workflow run ID
- metadata reference

Admin correction creates another corrective transition/history event where possible; no silent history rewrite.

Retention/privacy policy depends on domain.

---

# 46. Unknown/external state

Adapters may surface runtime state unknown to current WPE definition.

Behavior:

- show `Unknown external state` with raw safe key;
- preserve value;
- block invalid WPE transitions unless adapter maps it;
- never normalize unknown state to default automatically.

---

# 47. Domain state storage

State machine definition belongs in Definition Repository.

Runtime current state belongs to domain storage or explicit state assignment table according to Data Source adapter.

History belongs to append-only runtime/event table.

Do not store all application state as one giant WPE option/meta blob.

---

# 48. REST & Abilities for states

Potential operations:

- state_machine.list/get
- state.transitions.available
- state.transition.preview
- state.transition.execute
- state.history.list

Preview returns:

- allowed/denied
- missing requirements
- side effects summary
- resulting state

Execute rechecks all conditions; preview is not authorization token.

---

# 49. Import/export

Definitions export:

- state machine UUID
- states/transitions
- policies
- field/query/relation references

Runtime state/history export is Data Import/Export scope.

Import must remap dependencies and preserve stable state UUIDs when intended.

---

# 50. Status diagnostics

WordPress status:

- key > DB-safe length
- collision
- content uses disabled status
- editor integration unsupported
- contradictory public/internal/private/protected flags
- workflow references missing status
- role/policy denies every transition

Domain machines:

- no initial state
- multiple initial states
- unreachable state
- dead-end non-terminal state
- transition cycle — informational unless harmful
- no outgoing transition from active process state
- missing dependency
- required field removed
- workflow side effect missing
- unknown runtime states
- stale history/current mismatch

Graph diagnostics should identify reachability, not merely render a pretty diagram.

---

# 51. Acceptance tests

## WordPress status

- all documented registration flags
- inherited WordPress defaults
- 20-character DB-safe key boundary
- collision/core read-only
- content count before disable/delete
- post type availability integration
- block/classic editor compatibility
- status permission cases
- transition allowed/denied
- map existing content to replacement status
- search/query behavior by flags

## Domain state machine

- single initial state enforcement
- terminal state
- unreachable state diagnostic
- transition permission denied
- guard failure
- required field failure
- stale concurrent transition
- successful history append
- side-effect failure after committed transition
- retry idempotency
- unknown external state
- import dependency remap
- large history pagination

---

# 52. Differentiators

## Relations

1. Typed Data Source endpoints, not only posts/users/terms.
2. Database-enforced cardinality where feasible.
3. Pivot fields are first-class schema.
4. Relation query/filter/order capabilities shared with Query Builder.
5. Independent object-level authorization on source and target.
6. Integrity diagnostics and repair planning.
7. Relation management delegates columns/filters instead of duplicating UI engines.

## Status

1. Clean separation between WordPress status registration and generic state machines.
2. Explicit transition graph with conditions, approvals and side-effect semantics.
3. Optimistic concurrency prevents approval overwrites.
4. Append-only history.
5. Workflow/Job service reused for automation.
6. Status can feed Policy/Membership access without pretending status itself is universal authorization.

---

# 53. Open decisions before implementation

Relations:

- physical relation/pivot table schema and indexes;
- canonical entity-reference encoding across polymorphic providers;
- transactional ordering strategy;
- cardinality DB constraints across DB versions;
- orphan detection cost at large scale;
- relation cache/count materialization policy.

Statuses:

- exact editor integration strategy for custom WordPress statuses at supported WP versions;
- capability-materialization strategy vs Policy-only rules;
- state history physical schema/retention;
- approval runtime schema;
- transition transaction boundary adapters;
- migration of posts using a removed/custom status.

Global blockers:

- explicit user development consent;
- accepted compatibility/UI/build/Definition/CI/Job/Secrets/Free-Pro ADRs.

**Development authorization remains NO.**
