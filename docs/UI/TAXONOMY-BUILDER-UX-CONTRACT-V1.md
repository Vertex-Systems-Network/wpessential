# Taxonomy Builder — UX Contract V1

Surface: **2 / Taxonomy Builder**  
Issue: **#468**  
Lifecycle target of this lane: **UX_CONTRACT_COMPLETE**  
Full-final target: **PARITY_OR_EXCEED** after later runtime/parity certification.

## UX principles

Taxonomy Builder presents one canonical Taxonomy definition owner. WordPress registration, Admin UI, REST, CLI, AI and future import adapters must all project through that owner rather than create parallel stores.

The editor uses progressive disclosure:

- **Essential** — safe creation and the controls most users need;
- **Advanced** — complete native behavior, labels, rewrite, capabilities, REST and diagnostics;
- **Expert** — controlled providers, bounded runtime defaults, migration and high-impact operations.

Changing UI tier never changes authorization. Hidden controls retain their stored explicit values and remain visible in effective-state diagnostics.

## Essential mode

### Identity

- Taxonomy key.
- Plural name.
- Singular name.
- Description.
- Lifecycle status.
- Key validation and collision state.

The key is immutable after ordinary creation. Existing definitions do not silently rename their runtime taxonomy key.

### Object-type associations

A searchable selector groups:

- core WordPress post types;
- WPE CPT definitions;
- external runtime post types;
- preserved missing/external keys.

Each association shows a state badge: `healthy`, `missing`, `external`, or `disabled`.

Missing/external keys are never silently deleted merely because the current request cannot resolve them.

### Core behavior

Essential exposes:

- Public;
- Hierarchical;
- Show Admin UI;
- Show in REST API;
- Show Admin Column.

Controls with WordPress inheritance display **Default / Explicit** state. Reset returns the field to WordPress inheritance rather than writing a guessed boolean.

### Labels

Labels are collapsed by default. `Customize labels` opens the complete reviewed label inventory.

Each label shows one of:

- Generated;
- WordPress default;
- Explicit override.

Actions:

- reset one label;
- reset all label overrides;
- preview tag-like/category-like generated labels when hierarchical mode changes.

## Advanced mode

### Visibility & Admin

Expose the complete policy:

- public;
- publicly queryable;
- hierarchical;
- show UI;
- show in menu;
- show in nav menus;
- show tagcloud;
- show in quick edit;
- show admin column.

Parent/child inheritance is visible. Disabling a parent does not erase dormant explicit child values.

### Rewrite & query vars

Expose:

- rewrite enabled/disabled/structured;
- slug;
- with_front;
- hierarchical rewrite;
- endpoint mask through safe bounded values;
- query_var disabled/default/custom.

Show live previews for:

- base term URL;
- hierarchical parent-term URL;
- query variable;
- effective rewrite object.

Validation reports reserved/collision risks. Saving an ordinary definition must not flush rewrite rules on every request.

### Permissions

Edit the complete native capability map:

- manage_terms;
- edit_terms;
- delete_terms;
- assign_terms.

Show:

- WordPress default/effective value;
- role-impact preview as read-only cross-surface information;
- current-admin lockout warning;
- reset to WordPress defaults.

Role grants remain owned by Roles & Capabilities.

### REST

Expose:

- show_in_rest;
- rest_base;
- rest_namespace;
- controller mode: WordPress default or registered provider.

Show route preview and collision diagnostics. No arbitrary class name/callback text is accepted as executable configuration.

### Default term

When enabled:

- name;
- slug;
- description;
- existing-term collision/selection diagnostic.

Creation uses WordPress semantics and is validated server-side.

### Diagnostics

Read-only diagnostics include:

- effective `register_taxonomy()` args;
- overrides-only diff;
- association health;
- runtime registration state;
- dependency/usage summary;
- REST and rewrite previews;
- validation issues grouped by field and severity.

## Expert mode

### Runtime defaults

Expose bounded:

- `sort`;
- structured `args` for object-term queries.

Only allowlisted argument keys are accepted. Expensive or unsafe query settings produce blocking/warning diagnostics as defined by the runtime contract.

Independent persistent manual term ordering remains owned by Surface 51 Content Order.

### Controlled providers

Provider selectors support:

- meta_box_cb;
- meta_box_sanitize_cb;
- update_count_callback;
- REST controller provider.

Each provider shows:

- provider ID/name;
- health;
- compatibility/version state;
- required capability;
- unavailable/missing status.

Raw PHP/callback text is prohibited.

### Guarded key migration

Key migration is a separate high-impact workflow, never an ordinary text-field save.

Before execution it must provide:

- proposed new key validation;
- term URL impact;
- object-type association impact;
- REST route/query-var impact;
- dependent-definition impact;
- dry run;
- conflict report;
- rollback/recovery plan.

The current lane defines this UX only; it does not authorize destructive migration execution.

### Portability / compatibility

Definition portability supports:

- export;
- create-only import;
- update-existing import with revision/CAS checks;
- environment-sensitive conflict report;
- CPT UI compatibility adapter preview.

Imported data is normalized into the same canonical Taxonomy definition schema; no provider-owned shadow store is created.

## Global editor behavior

- Sticky **Validate** and **Save** commands.
- Unsaved-change guard.
- Find Setting search over friendly labels and native argument names.
- Field-level server validation messages linked to controls.
- Keyboard-reachable tier/section navigation.
- Proper labels, fieldsets, descriptions, error summaries and ARIA live regions.
- Reset field / section / all overrides where safe.
- No UI-only authorization assumptions.
- No arbitrary executable input.

## Definition list

The list shows:

- name;
- key;
- object types;
- lifecycle status;
- revision;
- runtime health;
- dependency count;
- actions allowed by Policy.

Bulk actions must use the canonical Taxonomy Ability/Policy path and remain separately authorized.

## Full-final evidence required later

This UX contract becomes implemented evidence only after browser/accessibility tests prove the actual UI behavior. `UX_CONTRACT_COMPLETE` in this lane means the interaction/information architecture is reviewed and implementation-ready; it does not mean the current admin screen already implements every item above.
