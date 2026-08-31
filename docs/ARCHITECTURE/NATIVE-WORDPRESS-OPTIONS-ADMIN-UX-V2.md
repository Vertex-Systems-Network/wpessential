# WPEssential — Native WordPress Options & Admin UX V2

Status: **Architecture contract / implementation required**  
Date: 2026-08-31  
Applies to: WP122 Custom Post Types, Taxonomies, Field/Meta surfaces and every future WPEssential module that wraps a WordPress registration primitive.  
Reference products reviewed: WordPress core admin, SCF/ACF-style builders, CPT UI-style registration tools. These are UX references, not implementation or visual cloning targets.

## 1. Decision

WPEssential MUST NOT treat a small starter form as the product contract for a WordPress registration surface.

Every module that owns a WordPress registration primitive MUST expose the complete **safe declarative option surface** that WPEssential supports, while keeping the default editing experience small and understandable through progressive disclosure.

The product model is:

`WordPress native primitive -> WPE Option Contract -> canonical Definition -> validation/effective-value resolution -> owner Ability -> compiled/runtime registration -> admin UX generated from the same contract metadata`

The Option Contract is the source of truth for supported configuration. UI code MUST NOT become a second independent list of options.

## 2. Why the current bounded WP122 UI is insufficient

The existing CPT/Taxonomy implementation established identity, persistence, validation, registration ownership, status lifecycle and associations. It intentionally did not model the full native registration argument space.

That bounded foundation is valid, but it is not the finished product UX. A mature builder must support:

- minimal required/common settings for normal users;
- all supported native declarative settings for advanced users;
- WordPress defaults and inherited values without forcing users to re-enter them;
- dependencies between settings;
- expert/developer settings without exposing arbitrary executable PHP;
- clear consequences for URL, capability, REST, editor and visibility changes;
- validation before mutation;
- safe migration semantics for identity/runtime-key changes;
- portable serialization through WPE definitions/packages.

A flat form containing every control is explicitly rejected.

## 3. Option Contract

Each owned primitive gets a machine-readable Option Contract. Each option entry MUST declare at least:

| Property | Meaning |
| --- | --- |
| `key` | Canonical WPE/native option key |
| `source_api` | WordPress primitive or WPE extension source |
| `value_type` | bool/string/int/enum/list/map/structured value |
| `required` | Required for creation vs optional |
| `wp_default` | Authoritative WordPress default or inheritance rule |
| `wpe_default` | WPE explicit default when intentionally different |
| `default_mode` | inherited / explicit / computed |
| `tier` | Essential / Advanced / Expert |
| `group` | UX section/domain |
| `control` | switch/text/select/token-list/icon/etc. |
| `visible_when` | Declarative dependency rules |
| `enabled_when` | Declarative dependency rules |
| `sanitize` | Sanitization contract |
| `validate` | Validation constraints and collisions |
| `immutable_after_create` | Whether direct mutation is allowed |
| `migration_effect` | rewrite/cache/registration/data implications |
| `portable` | export/import behavior |
| `security_class` | normal / privileged / extension-only / prohibited |
| `multisite_scope` | site/network applicability |
| `help` | concise product explanation/source reference |
| `since` | WordPress/WPE version source marker when relevant |

Contract metadata MUST be independently testable. UI visibility is not security: server-side validation and Policy remain authoritative.

## 4. Coverage policy

### 4.1 Exhaustive means safe declarative coverage

For a WordPress primitive, WPEssential targets every public, supported **declarative** argument that can be represented and validated safely.

Excluded from ordinary free-text UI:

- arbitrary PHP callbacks;
- arbitrary executable code;
- arbitrary class execution with no allowlist/contract;
- internal-use-only WordPress arguments;
- undocumented behavior that cannot be compatibility-tested;
- options that violate an existing WPE ownership/security boundary.

An excluded native argument MUST appear in the coverage registry with an explicit reason. Silent omission is not acceptable.

### 4.2 Executable extension points

Native arguments such as callback/controller hooks are classified individually:

1. **Known WordPress controller class** — selectable through an allowlisted option when safe.
2. **Registered WPE extension provider** — selectable by provider ID after capability/schema validation.
3. **Arbitrary PHP callback/class string** — not accepted from normal admin input.

This keeps advanced capability without turning a settings screen into code execution.

## 5. Progressive disclosure model

### Essential

Default mode. Contains only identity and the most common behavior needed to create a valid object.

Goals:
- user can create a useful object quickly;
- inherited WordPress defaults remain inherited;
- no wall of toggles;
- descriptions explain consequences rather than restating labels.

### Advanced

Explicitly enabled per definition or entered through an Advanced configuration affordance.

Contains complete safe native controls grouped by domain. Advanced controls show effective values and whether they are inherited from WordPress, derived from another option, or explicitly overridden.

### Expert

Contains compatibility-sensitive or developer-oriented declarative controls. Expert mode requires deliberate entry and stronger warnings where changes affect capabilities, REST controllers, query variables, endpoints or migration behavior.

Expert does not mean arbitrary PHP.

## 6. Inheritance and explicit-value UX

WPEssential MUST distinguish these states:

- **WordPress default** — option not persisted when omission preserves native semantics;
- **Inherited** — effective value comes from another option, e.g. `show_ui <- public`;
- **Explicit override** — user intentionally set a value;
- **Computed by WPE** — WPE resolves a safe runtime value;
- **Unavailable** — dependency makes the option inapplicable.

The UI SHOULD provide:
- `Default`/`Inherited` badges;
- effective-value explanation;
- reset-to-WordPress-default at field and section level;
- diff of explicit overrides before save;
- no serialization noise from values that simply mirror defaults unless needed for portability stability.

## 7. Shared Admin UX V2 shell

The admin shell MUST feel like a WordPress product, not a raw diagnostics form.

### 7.1 Collection/list screen

Use mature list-management patterns:

- page title + primary Add New action;
- search;
- status filters;
- bulk actions where lifecycle semantics are safe;
- configurable columns and Screen Options-compatible preferences;
- compact/comfortable density preference;
- clear row actions;
- relationship counts/chips where useful;
- pagination for large definition sets;
- no bespoke horizontal card grid for data that is fundamentally tabular.

### 7.2 Editor frame

Desktop layout:

- contextual title/identity at top;
- sticky command bar containing Save/Validate and state feedback;
- main settings workspace;
- optional right diagnostics/summary rail on sufficiently wide screens;
- rail collapses below content on smaller screens;
- sections/tabs remain keyboard reachable and URL/state stable where practical.

### 7.3 Settings navigation

A long definition MUST be split by domain. Recommended CPT tabs:

1. General
2. Labels
3. Visibility
4. Admin UI
5. Content & Editor
6. Relationships
7. URLs & Rewrite
8. Permissions
9. REST API
10. Developer / Compatibility

Recommended Taxonomy tabs:

1. General
2. Labels
3. Visibility
4. Admin UI
5. Relationships / Object Types
6. URLs & Rewrite
7. Permissions
8. REST API
9. Developer / Compatibility

Tabs do not bypass Essential/Advanced/Expert tiers. In Essential mode only relevant sections/options appear.

## 8. Conditional controls

Dependencies MUST be declarative in the Option Contract and enforced both client- and server-side.

Examples:

- REST route/base/controller options appear only when `show_in_rest` is enabled;
- rewrite sub-options appear only when rewrite is enabled;
- archive slug behavior appears only when archives are enabled/applicable;
- menu position/icon/parent appear only when admin UI/menu placement is enabled;
- custom capability controls appear only when custom capabilities are enabled;
- template lock appears only when a block template exists;
- taxonomy term UI controls respect `show_ui` and associated object-type behavior;
- inherited values update immediately when their parent option changes unless explicitly overridden.

Hidden controls MUST NOT accidentally retain stale explicit overrides without warning. If a parent setting makes children inapplicable, the user must be told whether child values are retained dormant or reset.

## 9. CPT native option coverage target

The CPT contract MUST account for public `register_post_type()` configuration, including current WordPress native arguments and nested structures.

### Identity & labels

- post type key;
- label;
- complete supported `labels` set;
- description.

### Visibility/query behavior

- public;
- hierarchical;
- exclude_from_search;
- publicly_queryable;
- show_ui;
- show_in_menu (boolean or controlled parent target);
- show_in_nav_menus;
- show_in_admin_bar.

### Admin presentation

- menu_position;
- menu_icon with safe Dashicon/media/validated SVG strategy;
- inherited/explicit visibility dependencies.

### Content/editor

- supports including native support features and structured arguments where WordPress supports them;
- template;
- template_lock.

### Relationships

- taxonomies with two-sided association validation and ownership-safe updates.

### URLs/query

- has_archive (boolean/custom slug);
- rewrite;
- rewrite.slug;
- rewrite.with_front;
- rewrite.feeds;
- rewrite.pages;
- rewrite.ep_mask via safe enum/constant mapping;
- query_var.

### Permissions

- capability_type singular/plural;
- map_meta_cap;
- capabilities map with validated capability names and preview of effective capabilities.

### REST API

- show_in_rest;
- rest_base;
- rest_namespace;
- rest_controller_class through allowlisted/provider mechanism;
- autosave_rest_controller_class through allowlisted/provider mechanism;
- revisions_rest_controller_class through allowlisted/provider mechanism;
- late_route_registration.

### Lifecycle/export

- can_export;
- delete_with_user.

### Controlled extension-only

- register_meta_box_cb through registered extension providers, never arbitrary admin-entered executable callback.

### Explicit exclusions

- `_builtin` and `_edit_link` are WordPress internal-use-only and MUST NOT be exposed as normal WPE settings.

The contract MUST be updated when supported WordPress changes its public argument surface.

## 10. Taxonomy native option coverage target

The Taxonomy contract MUST account for current public `register_taxonomy()` configuration.

### Identity/association

- taxonomy key;
- object type associations;
- label;
- complete supported labels;
- description.

### Behavior/visibility

- public;
- publicly_queryable;
- hierarchical;
- show_ui;
- show_in_menu;
- show_in_nav_menus;
- show_tagcloud;
- show_in_quick_edit;
- show_admin_column.

### URLs/query

- rewrite and supported nested rewrite options;
- query_var.

### Permissions

- capabilities map with validated capability names/effective preview.

### REST API

- show_in_rest;
- rest_base;
- rest_namespace;
- rest_controller_class through allowlisted/provider mechanism.

### Term/default/query behavior

- default_term structured value;
- sort;
- args when representable as a bounded declarative map.

### Controlled extension-only

- meta_box_cb;
- meta_box_sanitize_cb;
- update_count_callback.

These require registered extension providers rather than arbitrary executable strings.

## 11. Labels UX

Complete labels are powerful but visually noisy. WPE SHOULD:

- generate sensible labels from plural/singular essential inputs;
- display generated labels as inherited defaults;
- expose complete label customization in the Labels section;
- allow individual label override/reset;
- offer `Customize labels` rather than persisting every generated string;
- preview selected high-impact labels (menu, Add New, Edit, archive, search, item-updated messaging where applicable).

## 12. URL and rewrite safety

URL settings can break public links. The editor MUST show:

- current effective base/archive/query behavior;
- whether rewrite is inherited/default/explicit;
- collision diagnostics against reserved/core/WPE runtime keys;
- a before/after URL example;
- warning when a change requires rewrite-rule refresh;
- no uncontrolled flush on every request;
- migration/recovery path for immutable or high-impact identity changes.

## 13. Permissions UX

Capabilities are not a flat checkbox list.

WPE SHOULD provide:

- Standard WordPress capability mode as default;
- Custom capability mode as explicit advanced action;
- singular/plural capability base inputs;
- effective capability map preview;
- validation against malformed capability names;
- warnings before locking the current administrator out;
- Policy enforcement independent of UI.

## 14. REST UX

When REST is disabled, REST detail controls stay collapsed/inapplicable.

When enabled, UI SHOULD expose:

- route preview: namespace + base;
- controller/provider selection where supported;
- block-editor compatibility note;
- autosave/revision controller behavior for CPTs;
- collision/namespace validation;
- effective REST endpoint preview after preflight.

## 15. Setting search and navigation

Advanced/Expert modes SHOULD include `Find setting...`.

Search results must:

- search label, option key and help text;
- reveal the containing section without destroying its tier/dependency context;
- show why a matching option is unavailable if its parent condition is disabled;
- support keyboard navigation.

This is preferred over showing hundreds of controls simultaneously.

## 16. Presets without lock-in

WPE MAY provide presets such as:

- Public content;
- Private/internal records;
- Headless / REST-first;
- Hierarchical content;
- Taxonomy category-like;
- Taxonomy tag-like.

A preset is a one-time explicit-values proposal, not a hidden runtime mode. After applying, the user can inspect every changed option and revert selected values to WordPress defaults.

## 17. Effective registration preview

Advanced/Expert users SHOULD have a read-only **Effective WordPress Args** view.

The preview displays the normalized declarative structure that WPE intends to pass to its registration projector after defaults/inheritance and policy resolution.

It MUST:

- omit secrets;
- never be executable code input;
- identify WPE-computed/provider-backed values;
- make omitted/default values understandable;
- help support/debug portability issues.

## 18. Validation and diagnostics

Validation happens before save/publish/import mutation.

Diagnostics classes include:

- invalid key/length/character constraints;
- reserved identifiers;
- collision with existing external registrations;
- invalid dependencies;
- URL/rewrite collisions;
- REST route conflicts;
- invalid capability structures;
- impossible UI inheritance combinations;
- unsupported provider/callback reference;
- stale revision/CAS conflict;
- association ownership conflict;
- portability/schema incompatibility.

Errors identify the setting path and link/focus the relevant control.

## 19. Unsaved changes and command behavior

The editor MUST provide:

- dirty-state indicator;
- Save/Validate command states;
- navigation warning for unsaved changes;
- no false success before server persistence;
- deterministic post-save state refresh;
- optimistic UI only where reconciliation is proven;
- accessibility announcements for save/validation results.

## 20. Responsive and accessibility contract

The admin experience MUST remain usable at WordPress-supported admin viewport sizes.

Requirements:

- no horizontal page overflow for ordinary forms;
- labels remain associated with controls;
- tablist/accordion semantics follow ARIA patterns;
- focus is moved/announced appropriately after validation errors;
- keyboard-only configuration is possible;
- visible focus states;
- color is never the only status signal;
- Axe has zero WPE-owned violations in certified surfaces;
- compact desktop density does not reduce touch targets below usable sizes.

## 21. Design system policy

WPEssential SHOULD use a small shared admin design system instead of copying markup/styles per module.

Shared primitives include:

- PageFrame;
- ModuleNav;
- CommandBar;
- DefinitionList/Table;
- SettingsSection;
- SettingsTabs;
- DisclosureTierControl;
- Switch/Select/Text/Token controls;
- InheritedValueBadge;
- HelpText;
- DiagnosticsPanel;
- RelationshipPicker;
- SettingSearch;
- EffectiveArgsPreview;
- EmptyState;
- ConfirmDangerousChange.

WordPress visual conventions and accessibility behavior are preferred. SCF/ACF/CPT UI may inform product ergonomics, but WPE must not create a visual or code clone.

## 22. Definition schema evolution

Expanding option coverage MUST NOT require replacing canonical UUID identity.

Rules:

- old definitions with missing keys continue to resolve through WordPress/WPE defaults;
- schema migrations are explicit and versioned;
- default-only newly introduced options should not generate noisy mutations;
- import/export preserves explicit overrides and schema provenance;
- update paths remain revision/CAS protected;
- changing runtime key/identity remains migration-class behavior, not an ordinary text edit.

## 23. WordPress API drift guard

Each owned WordPress primitive SHOULD maintain a checked-in coverage registry containing:

- supported argument keys;
- intentionally excluded/internal keys;
- extension-only executable keys;
- WordPress version/source snapshot.

CI MUST fail when the project updates its supported WordPress source snapshot and discovers an unclassified public argument.

This turns “did we forget an option?” into a machine-visible engineering question.

## 24. Module-wide adoption rule

This architecture applies beyond CPT and Taxonomy.

For every future WPE module wrapping native WordPress concepts (meta/fields, statuses, roles/capabilities, admin columns, REST-facing definitions, menus, media behavior, etc.):

1. inventory the authoritative WordPress/native API;
2. classify every public option;
3. publish the Option Contract;
4. separate essential/default/advanced/expert controls;
5. encode dependencies and validation;
6. generate or bind UI to the contract;
7. preserve explicit/default semantics in persistence;
8. certify accessibility, portability and runtime output.

A module is not considered feature-complete merely because its required inputs work.

## 25. Testing and certification

At minimum the implementation requires:

### Contract/unit

- every contract entry has type/default/tier/group/validation metadata;
- no duplicate option keys;
- all source keys classified;
- inheritance/effective-value tests;
- conditional visibility state-machine tests;
- serialization round-trip tests;
- schema migration/backward-compatibility tests.

### Runtime

- emitted registration args match effective contract values;
- omitted options preserve WordPress defaults;
- relationships remain two-sided/ownership-safe;
- collision and reserved-name guards remain intact;
- custom capability and REST structures are validated;
- no arbitrary executable callback enters from user input.

### Browser UX

- Essential flow can create a valid definition without opening Advanced;
- enabling Advanced reveals complete grouped safe options;
- dependency controls reveal/hide correctly;
- reset-to-default updates effective values;
- setting search navigates to options;
- validation focuses relevant fields;
- dirty-navigation guard works;
- packaged plugin behaves the same as source build;
- Axe zero WPE-owned violations;
- keyboard path certified.

### Compatibility

Existing Architecture Guards, PHP quality, distributable package, CPT Runtime, Taxonomy Runtime, browser/Axe and supported WordPress/PHP/database compatibility gates remain mandatory.

## 26. Implementation sequence

1. **WP122-O1 — Option Contract foundation**
   - contract schema;
   - CPT/Taxonomy source coverage registries;
   - effective-value/inheritance resolver;
   - API drift classification test.

2. **WP122-O2 — Shared Admin UX V2 shell**
   - page/list/editor frame;
   - command bar;
   - tiers;
   - settings navigation/search;
   - inherited/default indicators;
   - diagnostics integration.

3. **WP122-O3 — CPT full safe-native coverage**
   - schema/projector expansion;
   - grouped UX;
   - labels/rewrite/capabilities/REST/editor settings;
   - migration/backward compatibility.

4. **WP122-O4 — Taxonomy full safe-native coverage**
   - schema/projector expansion;
   - grouped UX;
   - labels/UI/rewrite/capabilities/REST/default-term behavior.

5. **WP122-O5 — Relationships/presets/effective preview polish**
   - richer relationship picker;
   - presets;
   - effective args preview;
   - cross-module consistency pass.

6. **WP122-O6 — Reusable adoption framework**
   - document/guard how future modules register option contracts;
   - migrate additional module UIs incrementally rather than creating one-off forms.

Each slice is separately reviewable/certifiable and MUST preserve the previous runtime path until its replacement is green.

## 27. Non-goals for this phase

- visual cloning of SCF/ACF/CPT UI;
- arbitrary PHP/eval/callback execution from admin text fields;
- bypassing owner Ability/Policy architecture;
- rewriting unrelated modules in one large PR;
- destructive live-site migrations;
- pretending hidden advanced controls are implemented when runtime/projector support does not exist.

## 28. Acceptance statement

For CPT and Taxonomy, WPEssential is considered product-complete only when a beginner can create a safe useful definition from Essentials **and** an advanced user can reach every classified safe native WordPress option without leaving WPEssential, with defaults/inheritance, consequences, validation and portability made explicit.
