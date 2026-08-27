# WPEssential — Free CPT & Taxonomy Exhaustive Registration Specification

Status: **Phase 0 — planning only / no development consent**  
Scope: **Custom Post Types Builder (Free)** and **Taxonomy Builder (Free)**  
Purpose: make the first future implementation modules exhaustive against WordPress registration semantics before any production code exists.

This document supplements `CONTENT-MODEL-SPECS.md`, `OPTION-INVENTORY.md`, `COMMON-OPTION-CONTRACTS.md`, and the accepted development-consent gate. Where this document is more specific for CPT/Taxonomy controls, it is the more specific planning source of truth.

## Research baseline

Verified against current WordPress Developer Resources for:

- `register_post_type()`
- `get_post_type_labels()`
- `add_post_type_support()` / `post_type_supports()`
- `register_taxonomy()`
- `get_taxonomy_labels()`
- REST exposure of post types/taxonomies

Important verified constraints:

- post type keys: maximum 20 characters; lowercase alphanumeric, dash, underscore after WordPress-compatible sanitization;
- taxonomy keys: maximum 32 characters; lowercase alphanumeric, dash, underscore after WordPress-compatible sanitization;
- both registrations must not occur before `init`;
- CPT taxonomy associations should be represented through the CPT `taxonomies` argument as well as actual taxonomy registration so WordPress query hooks see the relationship consistently;
- `show_in_rest` is required for expected block-editor interoperability;
- CPT editor support implies autosave support unless explicitly removed later by supported runtime logic;
- WordPress provides newer registration arguments such as CPT REST namespace/controller variants, late REST route registration, block templates/template locking, and taxonomy `default_term`, `meta_box_sanitize_cb`, `args`, and `update_count_callback`;
- WordPress internal `_builtin` / `_edit_link` flags are not normal user configuration fields.

---

# Part A — Cross-module UX contract

## A1. CPT list screen

Columns:

1. Name
2. Key
3. Status
4. Public
5. REST
6. Archive
7. Attached taxonomies count
8. Content count
9. Source/Owner
10. Health
11. Updated
12. Actions

Filters:

- All / Active / Draft / Disabled / Archived
- Public / Private/internal
- REST enabled / disabled
- Has content / empty
- WPEssential-owned / discovered external
- Healthy / warning / degraded

Row actions:

- Edit
- Preview registration
- Duplicate
- Export
- Dependency/Used-by
- Disable/Enable
- Archive/Restore
- Delete definition

External/discovered registrations are read-only unless a future supported override contract explicitly says otherwise.

## A2. CPT editor navigation

Sections, in order:

1. General
2. Labels
3. Visibility & Admin
4. Editor Features
5. Menu
6. URLs & Querying
7. REST API
8. Capabilities
9. Taxonomies
10. Block Editor Template
11. Advanced
12. Dependencies
13. Diagnostics
14. Revisions
15. Export / Generated PHP Preview

The UI may use tabs/side navigation, but the data model remains section-independent.

## A3. Taxonomy list screen

Columns:

1. Name
2. Key
3. Status
4. Hierarchical
5. Public
6. REST
7. Object types count
8. Terms count
9. Source/Owner
10. Health
11. Updated
12. Actions

Filters mirror CPT semantics where relevant.

## A4. Taxonomy editor navigation

1. General
2. Labels
3. Visibility & Admin
4. Object Types
5. Editing UI
6. URLs & Querying
7. REST API
8. Capabilities
9. Default Term
10. Counting & Ordering
11. Advanced
12. Dependencies
13. Diagnostics
14. Revisions
15. Export / Generated PHP Preview

---

# Part B — Custom Post Types Builder exhaustive contract

## B1. Identity

### `name` / user-facing plural label

- UI: text
- required: yes
- default: none
- normalization: trim surrounding whitespace; preserve intended human-language casing
- changing this label is non-destructive

### `singular_name`

- UI: text
- required: yes
- suggestion may be generated from plural/name, but suggestion is never treated as authoritative linguistic conversion

### `post_type_key`

- UI: machine-key field
- required: yes before Publish
- maximum: 20 characters
- permitted normalized form: lowercase alphanumeric, dash, underscore according to WordPress-compatible `sanitize_key` behavior
- immutable after the definition has been published and referenced, unless entering explicit migration flow
- block core/reserved/conflicting names
- collision scan includes current registered post types, taxonomies, important public/private query vars, WPEssential definition keys, and known route/rewrite conflicts

### `description`

- UI: textarea
- optional
- plain text product metadata; not executable markup

## B2. Label mode

`automatic_labels` default: **on**.

When on, WPEssential derives safe defaults from singular/plural labels and WordPress hierarchy semantics. Every individual label still supports an override toggle.

CPT label inventory to expose/track for the supported WordPress floor:

- `name`
- `singular_name`
- `add_new`
- `add_new_item`
- `edit_item`
- `new_item`
- `view_item`
- `view_items`
- `search_items`
- `not_found`
- `not_found_in_trash`
- `parent_item_colon` — hierarchy-sensitive
- `all_items`
- `archives`
- `attributes`
- `insert_into_item`
- `uploaded_to_this_item`
- `featured_image`
- `set_featured_image`
- `remove_featured_image`
- `use_featured_image`
- `menu_name`
- `name_admin_bar`
- `filter_items_list`
- `filter_by_date`
- `items_list_navigation`
- `items_list`
- `item_published`
- `item_published_privately`
- `item_reverted_to_draft`
- `item_trashed`
- `item_scheduled`
- `item_updated`
- `item_link`
- `item_link_description`
- `template_name` where supported by compatibility floor

Rules:

- empty override means “inherit/generated”, not literal empty string;
- hierarchy-only labels display only when relevant, but remain safely preserved if hierarchy is toggled off/on;
- labels are stored as site configuration values, not dynamically wrapped in runtime translation functions as if they were plugin-shipped source strings;
- future multilingual integration belongs to an adapter/translation strategy, not fake gettext extraction of database content.

## B3. Visibility & admin registration arguments

### `public`

UI: select `Public / Private-internal preset / Advanced explicit`.

For raw value, boolean.

New normal content-type preset default: **true**.

`public` is a convenience parent and does not erase independent controls.

### `hierarchical`

Default false.

Turning on:

- enables parent semantics;
- warns about performance/admin usability for very large hierarchical datasets;
- enables hierarchy-relevant labels;
- makes page-attributes Parent behavior meaningful.

### `exclude_from_search`

UI values: `Inherit from public / Yes / No`.

For new public preset: inherited effective false.

### `publicly_queryable`

UI: Inherit / Yes / No.

### `show_ui`

UI: Inherit / Yes / No.

If false, dependent admin fields communicate that they have no effect.

### `show_in_menu`

UI modes:

- Inherit `show_ui`
- Top-level menu
- Hidden
- Under existing approved top-level menu

String parent paths are selected from discovered valid admin parents; ordinary users do not type arbitrary PHP admin paths without validation.

### `show_in_nav_menus`

UI: Inherit / Yes / No.

### `show_in_admin_bar`

UI: Inherit from `show_in_menu` / Yes / No.

## B4. REST arguments

### `show_in_rest`

WPEssential recommended default for new public content types: **true**.

UI warning when disabling while editor/block-related features expect REST.

### `rest_base`

- blank = post type key/default
- validated route segment
- route collision preview before Publish

### `rest_namespace`

- blank = WordPress default `wp/v2`
- advanced field
- validate namespace/version path format
- warn that changing it is an API-breaking change

### `rest_controller_class`

No arbitrary class-name execution textbox in normal UI.

Modes:

- WordPress default
- registered controller adapter

### `autosave_rest_controller_class`

Advanced only.

Modes:

- WordPress default
- disabled where supported/meaningful
- registered approved adapter

### `revisions_rest_controller_class`

Same adapter pattern as autosave controller.

### `late_route_registration`

Advanced boolean.

Default inherits WordPress behavior; WPEssential should not expose it in Basic mode because it exists to solve controller route-ordering cases.

Changing REST namespace/base/controller-related configuration is classified as integration-impacting.

## B5. Editor `supports`

Supported core controls at current compatibility baseline:

- title
- editor
- author
- thumbnail
- excerpt
- trackbacks
- custom-fields
- comments
- revisions
- page-attributes
- post-formats
- autosave

Rules:

- normal new CPT preset starts with title + editor;
- because WordPress editor support implies autosave support for backward compatibility, the UI must display effective autosave state rather than pretending it is independent;
- if a future supported implementation deliberately removes autosave after editor support is added, that becomes an explicit advanced semantic with compatibility tests;
- `thumbnail` shows theme-support diagnostic because post type support alone is insufficient for every frontend theme context;
- `page-attributes` explains that Parent selection requires hierarchical=true; menu order can still have distinct semantics;
- each support may eventually carry registered feature arguments because WordPress supports feature+args registration; arbitrary unregistered feature arguments are extension-SDK territory.

### Custom supports

Extensions may register an additional support descriptor:

- key
- human name
- description
- optional schema for arguments
- compatibility callback

Unknown discovered supports appear read-only rather than being discarded.

## B6. `register_meta_box_cb`

Advanced extension-only selector.

No user-entered PHP callback.

Default WordPress behavior: null.

Registered callback adapters may be exposed by SDK integrations and are compatibility-sensitive.

## B7. Menu configuration

### `menu_position`

- integer or blank/null
- default blank
- UI includes common WordPress position hints, but does not promise collision-free exact placement because other plugins can use same positions

### `menu_icon`

Modes:

- WordPress/default posts icon
- Dashicon
- approved sanitized SVG/data URI generated by WPEssential icon system
- validated URL only if future policy permits
- `none` only in advanced mode because it expects another styling mechanism

Lucide does not automatically become a valid WordPress menu icon merely because WPEssential uses Lucide in its own React UI; conversion/sanitization must be explicit if offered.

## B8. Capabilities

### `capability_type`

Modes:

1. `post/posts` default model
2. `page/pages` model
3. generated singular/plural capability base
4. advanced explicit base

Support both string and singular/plural pair semantics.

### `capabilities`

Advanced explicit map.

The future UI must enumerate the capability keys WordPress derives for post types at the selected compatibility floor rather than use an unstructured JSON textarea.

### `map_meta_cap`

Default follows selected capability strategy.

Do not globally set true without understanding the generated primitive/meta capabilities.

Before Publish of capability changes:

- show capability diff;
- show currently affected roles when Role Manager is available;
- warn about editor/list access loss;
- prevent obvious current-admin lockout through shared anti-lockout policy.

## B9. Taxonomy associations: `taxonomies`

Multi-select all compatible registered taxonomies.

Rules:

- selecting a WPEssential taxonomy stores UUID/reference plus runtime key;
- external taxonomy stores stable runtime key and source metadata;
- missing external taxonomy later => degraded diagnostic, not fatal plugin error;
- relationship must be represented consistently in CPT registration and taxonomy/object-type registration strategy;
- detaching does not remove terms/relationships from database automatically;
- creating a new taxonomy is a cross-module action that opens Taxonomy Builder and returns the created definition reference.

## B10. Archive and rewrite

### `has_archive`

UI modes:

- Disabled — default
- Enabled using CPT key
- Enabled custom archive slug

Archive change is permalink-impacting.

### `rewrite`

Modes:

- Disabled
- WordPress default
- Custom

Custom subfields:

- `slug`
- `with_front`
- `feeds`
- `pages`
- `ep_mask`

Defaults follow WordPress semantics unless a WPEssential preset explicitly states otherwise.

`feeds` effective default follows archive semantics.

`ep_mask` is Advanced; use a controlled enumeration/bitmask UI based on supported endpoint constants, never arbitrary executable input.

Rewrite flush contract:

- never flush on every request;
- definition change records a rewrite-dirty marker;
- future runtime performs one controlled flush at safe lifecycle point;
- UI can show “Permalink rules need refresh” diagnostic if necessary.

## B11. `query_var`

Modes:

- WordPress default / CPT key
- Disabled
- Custom string

Custom query var must pass collision checks against reserved/public/private query variables.

## B12. `can_export`

Default true.

This controls WordPress export behavior for content type; it is separate from WPEssential definition export, which remains available according to product rules.

## B13. `delete_with_user`

UI modes:

- WordPress default/inherit
- Keep content
- Trash/delete according to WordPress user-delete semantics

Default **Inherit**, not silently false, because core behavior changes depending on author support.

UI must explain effective behavior under current supports.

Changing this setting is data-lifecycle-sensitive.

## B14. Block template

### `template`

Only shown when editor/block-editor context is compatible.

Future UI uses a structured block-template builder/selector, not arbitrary PHP arrays.

Each block entry:

- registered block name
- approved attributes schema
- optional nested inner blocks if supported by chosen model

Unknown/unavailable blocks cause degraded warning; existing definition data is preserved.

### `template_lock`

Options:

- unlocked / false
- all
- insert
- contentOnly

UI explains exact behavioral difference.

Template changes affect newly initialized editor content; they must not imply automatic destructive rewrite of existing post content.

## B15. Internal-only WordPress args

Do **not** expose as normal controls:

- `_builtin`
- `_edit_link`

If discovered on an external/core post type, show read-only diagnostics.

## B16. Generated PHP preview

Read-only developer aid.

Requirements:

- deterministic representation of current published definition;
- no “Run PHP” action;
- comments identify inherited/defaulted vs explicit values where useful;
- preview must not be treated as canonical storage;
- labels stored in database cannot be pretended to be static gettext literals.

## B17. CPT migration-impact matrix

### Safe/minor

- description
- most labels
- menu icon
- menu position

### Behavior/integration-impacting

- public/publicly_queryable/show_ui
- REST exposure/base/namespace
- supports
- capabilities
- taxonomy attachment
- archive/rewrite/query var
- block template lock

### Migration-class/high risk

- post type key
- capability namespace when users/roles depend on old capabilities
- changes that alter public permalink identity at scale

Key migration future planning must consider:

- existing `wp_posts.post_type` rows;
- taxonomy relationships;
- post meta remains attached by post ID;
- revisions/autosaves;
- nav menu links;
- rewrite redirects;
- REST consumers;
- third-party references storing post type key;
- WPEssential dependency graph.

No key-migration implementation is authorized yet.

---

# Part C — Taxonomy Builder exhaustive contract

## C1. Identity

### plural/general name
Required.

### singular name
Required.

### `taxonomy_key`

- maximum 32 characters
- lowercase alphanumeric/dash/underscore after WordPress-compatible sanitization
- immutable after publication/references except explicit migration flow
- block reserved terms, core taxonomy conflicts, CPT conflicts where harmful, and important WordPress query-var collisions

### `description`
Optional plain text.

## C2. Taxonomy labels

Automatic labels default on with per-label override.

Track/expose all supported keys:

- `name`
- `singular_name`
- `search_items`
- `popular_items` — non-hierarchical relevance
- `all_items`
- `parent_item` — hierarchical relevance
- `parent_item_colon` — hierarchical relevance
- `name_field_description`
- `slug_field_description`
- `parent_field_description`
- `desc_field_description`
- `edit_item`
- `view_item`
- `update_item`
- `add_new_item`
- `new_item_name`
- `template_name`
- `separate_items_with_commas` — non-hierarchical
- `add_or_remove_items` — non-hierarchical
- `choose_from_most_used` — non-hierarchical
- `not_found`
- `no_terms`
- `filter_by_item` — hierarchical
- `items_list_navigation`
- `items_list`
- `most_used`
- `back_to_items`
- `item_link`
- `item_link_description`
- `menu_name`
- `name_admin_bar` if supported by current object-label handling

Hierarchy-specific values are preserved when temporarily hidden.

## C3. Core behavior

### `public`
Recommended new public taxonomy preset: true.

### `publicly_queryable`
Inherit / yes / no.

### `hierarchical`
Default false.

Changing after terms exist is behavior-impacting because UI, parent relationships and rewrite expectations change; existing parent data must not be silently destroyed.

### `show_ui`
Inherit / yes / no.

### `show_in_menu`
Inherit from show_ui / yes / no.

### `show_in_nav_menus`
Inherit from public / yes / no.

### `show_tagcloud`
Inherit from show_ui / yes / no.

### `show_in_quick_edit`
Inherit from show_ui / yes / no.

### `show_admin_column`
Default false.

Explain that this is WordPress core simple taxonomy column behavior; Admin Columns Builder provides richer controls in Pro.

## C4. Object types

Multi-select object types.

Draft may have none.

Publishing without an object type requires warning/explicit confirmation because it is usually not useful.

Important ownership rule:

`register_taxonomy()` can overwrite the object-type value of an existing taxonomy registration. Therefore WPEssential does not casually “edit” third-party taxonomy object types as if it owns them.

For WPEssential-owned types, object-type changes preserve term/relationship data unless an explicit cleanup operation is selected separately.

## C5. REST

### `show_in_rest`
Recommended default true for new public taxonomy because block editor term panels depend on REST exposure.

### `rest_base`
Blank/default or validated custom route segment.

### `rest_namespace`
Blank/default `wp/v2` or validated custom namespace in Advanced.

### `rest_controller_class`
Default WordPress controller or registered adapter only; no arbitrary callback/class execution field.

REST route changes are API-breaking/integration-impacting and require dependency preview.

## C6. Term editing UI callbacks

### `meta_box_cb`
Modes:

- WordPress automatic based on hierarchical setting
- Disabled
- registered approved callback adapter

No arbitrary PHP callback.

Block editor compatibility must be shown because classic meta-box callback semantics do not necessarily map to Gutenberg UI behavior.

### `meta_box_sanitize_cb`
Advanced extension adapter.

Default: WordPress-derived behavior.

No arbitrary callable input.

## C7. Capabilities

Expose explicit capability map:

- `manage_terms` — WordPress default `manage_categories`
- `edit_terms` — WordPress default `manage_categories`
- `delete_terms` — WordPress default `manage_categories`
- `assign_terms` — WordPress default `edit_posts`

Modes:

1. WordPress defaults
2. generated WPEssential taxonomy capability preset
3. explicit advanced map

Before changing capabilities, show role impact and use shared anti-lockout checks.

## C8. Rewrite

Modes:

- Disabled
- WordPress default
- Custom

Custom fields:

- `slug`
- `with_front`
- `hierarchical`
- `ep_mask`

Important distinction: taxonomy data hierarchy (`hierarchical`) and URL rewrite hierarchy (`rewrite.hierarchical`) are separate values. UI must not silently bind them, though it may offer “match taxonomy hierarchy” as a convenience mode.

Same controlled rewrite-flush contract as CPT.

## C9. `query_var`

Modes:

- WordPress default/taxonomy key
- disabled
- custom string

Collision/reserved query-var checks required.

## C10. `update_count_callback`

Advanced integration option.

Default WordPress behavior depends on attached object types.

User choices:

- WordPress automatic
- registered approved count adapter

No arbitrary function-name execution field.

Changing count callback can alter term-count semantics, so preview/diagnostic must explain that term counts may require recount.

## C11. `default_term`

Default disabled.

When enabled:

- name: required
- slug: optional, normalized
- description: optional

Future activation/publish behavior must be idempotent.

If matching term exists, link/update default-term option rather than duplicate.

Default-term deletion later should trigger a diagnostic.

## C12. `sort`

Default false.

When true, taxonomy remembers object-term order as supplied to WordPress term assignment APIs.

UI warns that not every consumer/theme/query automatically presents terms in that order.

## C13. `args`

WordPress supports taxonomy-level arguments automatically passed into `wp_get_object_terms()`.

WPEssential treatment:

- Advanced only;
- typed allowlisted options once the exact supported/desired subset is approved;
- no arbitrary serialized PHP array textbox in normal product UI;
- extension SDK may register additional validated term-query argument schemas.

This field was missing from the earlier high-level taxonomy spec and is now explicitly tracked.

## C14. Internal-only taxonomy argument

Do not expose `_builtin` as a user setting.

Show read-only for discovered core taxonomies.

## C15. Taxonomy migration-impact matrix

### Safe/minor

- description
- most labels
- show tag cloud
- simple admin-column visibility

### Behavior/integration-impacting

- public/publicly queryable
- hierarchy
- object types
- REST routes
- meta-box behavior
- capabilities
- rewrite/query var
- count callback
- default term
- sort
- automatic term-query args

### Migration-class/high risk

- taxonomy key
- rewrite slug where public URLs are externally indexed/linked
- capability namespace used by roles/integrations

Future key migration must consider:

- `term_taxonomy.taxonomy` identity;
- term relationships;
- term meta stays tied to term IDs;
- options such as default term;
- nav/menu references;
- URLs/redirects;
- REST consumers;
- WPEssential dependencies;
- third-party stored taxonomy keys.

No migration implementation is authorized yet.

---

# Part D — Presets

Presets are UI accelerators only. They expand into explicit values before Publish so no hidden magic persists.

## D1. CPT presets

### Public Content

Suggested effective intent:

- public yes
- publicly queryable inherit/yes
- show UI yes
- show in menu yes
- nav menu yes
- search included
- REST yes
- title + editor
- archive user chooses (do not silently create)

### Admin-only Records

- public false
- publicly queryable false
- show UI true
- show in menu true
- exclude search true
- REST off by default unless app/API use requires it
- rewrite/archive off

### Headless/API Content

- public behavior is explicitly reviewed rather than blindly true
- show UI optional
- REST true
- route namespace visible
- frontend rewrite/archive choices explicit

### Hierarchical Content

Same as public preset plus hierarchical=true and page-attributes suggested.

## D2. Taxonomy presets

### Category-like

- hierarchical true
- public true
- REST true
- admin UI true
- rewrite enabled

### Tag-like

- hierarchical false
- public true
- REST true
- admin UI true

### Internal Classification

- public false
- publicly queryable false
- show UI true or false by explicit choice
- rewrite false
- REST off unless internal app/API explicitly requires it

---

# Part E — Existing registrations / takeover policy

## E1. Discovery

WPEssential inventories every registered CPT/taxonomy and stores no duplicate ownership merely by viewing it.

Show:

- source heuristic/provider if knowable
- current args/effective values
- runtime owner unknown when WordPress cannot reliably identify owner
- whether key is built-in
- attached relationships
- content/term counts

## E2. Import external registration into WPEssential

Not equivalent to editing a third-party definition in place.

Possible future action: **Create WPEssential definition from current effective registration**.

Before activation of that imported definition:

- detect whether original owner is still registering same key;
- refuse dual ownership by default;
- offer compatibility guidance rather than race hook priority;
- never promise WPEssential can safely override every third-party registration.

## E3. Core objects

Core post types/taxonomies remain read-only except for separately designed, narrowly scoped supported presentation integrations.

---

# Part F — Validation and dependency preview

Before Publish/Update, future UI must produce a validation report with at least:

- key validity
- reserved/conflict detection
- duplicate WPE definition
- runtime registration collision
- query-var collision
- REST route collision
- rewrite/permalink collision
- missing taxonomy/object type
- unsupported controller/callback adapter
- incompatible support/hierarchy combination
- capability lockout risk
- missing block from template
- dependency graph impact
- content/term count at risk

Warnings are categorized:

- Info
- Compatibility warning
- Breaking/integration-impacting
- Blocked/invalid

---

# Part G — Revisions and publish model

CPT/Taxonomy definitions follow shared Definition Repository semantics:

- Draft revision editable without changing runtime registration;
- Publish promotes validated revision to published pointer;
- runtime reads published revision;
- rollback creates/promotes a known historical revision rather than mutating history;
- dependency edges are revision-aware where needed;
- disable is definition lifecycle state, not deletion of WordPress content/terms.

---

# Part H — Import/export

Definition export contains:

- schema/package version
- stable UUID
- module/type
- explicit options
- label overrides
- dependency references
- source compatibility metadata
- no secrets

Import preview must show:

- key collisions
- dependency mapping
- unsupported options for current WordPress floor
- changed defaults between package versions
- whether imported definition would conflict with existing runtime registration

Import never automatically deletes existing content/terms.

---

# Part I — Acceptance-test planning

## I1. CPT tests

At minimum plan automated cases for:

- 1-char and 20-char valid keys
- >20 invalid key
- uppercase/space normalization behavior
- reserved/core/conflicting key
- every visibility inheritance combination
- show_ui false with show_in_menu configuration
- top-level/submenu/hidden menu modes
- REST enabled/disabled/base/namespace collision
- editor + autosave effective behavior
- supports false/no features
- hierarchy + page attributes
- capability preset and explicit map
- archive bool/custom slug
- rewrite false/default/custom every subfield
- query_var default/false/custom
- can_export false
- delete_with_user inherit/keep/delete behavior contract
- block template and each lock mode
- taxonomy association lifecycle
- disabling definition with existing content
- deleting definition retains content
- external CPT remains untouched
- rollback from changed labels/rewrite config

## I2. Taxonomy tests

At minimum:

- 1-char and 32-char valid keys
- >32 invalid key
- reserved/query-var collision
- hierarchical/non-hierarchical label visibility
- each visibility inheritance control
- show quick edit/tag cloud/admin column
- object type added/removed/missing
- REST enabled/disabled/base/namespace
- classic meta box default/disabled/adapter contract
- all four capability defaults/custom values
- rewrite false/default/custom + hierarchical URL distinction
- query_var modes
- automatic/custom count callback adapter contract
- default term existing/new/idempotent behavior
- sort true/false
- typed taxonomy term-query args
- disable/delete definition retains terms/relationships
- external taxonomy remains untouched

## I3. Cross-module tests

- CPT creates linked taxonomy via cross-module quick action
- taxonomy attaches to CPT and relation is represented consistently
- dependency graph blocks destructive definition deletion when hard dependencies exist
- import/export preserves UUID references and maps runtime keys
- Role Manager unavailable still leaves capability warnings understandable
- Pro unavailable does not impair Free CPT/Taxonomy management
- no optional Pro assets load on Free module screens

---

# Part J — Open decisions before implementation

This document deliberately does **not** authorize development.

Still blocked by:

1. explicit user development consent;
2. accepted compatibility floor;
3. accepted Definition Repository physical schema;
4. accepted UI/build system;
5. accepted CI matrix;
6. Free↔Pro compatibility protocol;
7. finalized capability-map inventory for chosen WordPress minimum;
8. finalized reserved-name/query-var source and update strategy;
9. implementation-time proof of exact rewrite collision detection limits;
10. evidence for any external-registration override/import-to-ownership workflow.

# Completion status

**Verified:** current WordPress registration arguments and label inventories were re-audited and mapped into planning.  
**Not Verified / not executed:** PHP registration, WordPress activation, migrations, build, runtime REST routes, rewrite behavior, UI, tests.  
**Development authorization:** **NO** until the user gives explicit future consent.
