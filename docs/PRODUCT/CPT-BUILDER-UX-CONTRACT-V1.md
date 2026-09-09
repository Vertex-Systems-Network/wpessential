# CPT Builder — UX Contract V1

Surface: **1 / CPT Builder**  
Issue: **#473**  
Machine contract: `config/product/option-contracts/cpt.json`  
Reviewed source: **107/107 CPT Options Bank records**

This document closes the reviewed interaction and information-architecture contract for the CPT Builder planning lane. It does not claim runtime certification or product-parity certification.

## Product principles

1. **Safe defaults first.** A valid post type can be created from the Essential tier without exposing low-level WordPress internals.
2. **Progressive disclosure.** Advanced and Expert controls are visible only when the author needs them; hiding a tier never changes stored values.
3. **One canonical owner.** CPT Definition remains the sole writer for post-type registration semantics. Taxonomy, Fields, Roles, Import/Export and other surfaces are referenced rather than shadow-owned.
4. **No executable text input.** REST controllers, meta-box registration and structured support extensions use registered provider IDs/schemas only. Raw PHP, callbacks, arbitrary class names and executable snippets are never accepted as authored values.
5. **Server authority.** Client UI may guide, preview and search, but canonical validation, capability checks, revision/CAS and compilation remain server-authoritative.
6. **Explain WordPress inheritance.** Native defaults, explicit overrides, inactive/dormant values and computed diagnostics must be distinguishable in the UI.

## Information architecture

### Essential

The default editing experience contains the minimum high-frequency controls:

- post type key, plural name, singular name and description;
- adaptive label generation toggle with explicit-override indicator;
- Public, Hierarchical and REST exposure intent;
- common editor supports;
- archive intent;
- validation summary;
- lifecycle state and revision-safe Save/Publish actions.

Essential must be sufficient for a typical non-developer CPT without requiring knowledge of `register_post_type()` internals.

### Advanced

Advanced contains native configuration that materially changes WordPress behavior:

- complete reviewed label override editor;
- inherited/default/explicit visibility matrix;
- admin menu placement/icon/menu-parent controls;
- complete editor-support selection;
- block template + template lock;
- taxonomy associations with dependency health;
- archive/rewrite/query-var configuration and URL preview;
- capability type, explicit capability map and effective capability diagnostics;
- REST base/namespace/exposure plus allowlisted controller provider selection;
- `can_export` and `delete_with_user` lifecycle semantics;
- definition import/export and CPT UI compatibility import;
- effective `register_post_type()` arguments and override diff.

### Expert

Expert is intentionally narrow and safety-labelled:

- structured support-feature argument schemas from registered providers;
- meta-box registration provider selection;
- autosave/revision REST controller provider selection where supported;
- `late_route_registration` and endpoint-mask controls;
- guarded post-type-key migration wizard **planning surface only until separately authorized**;
- detailed dependency, portability and compatibility diagnostics.

Expert never exposes raw callbacks or arbitrary executable class names.

## Field-state model

Every authored option exposed by the full UX uses one of these visible states:

- **Default / WordPress default** — no explicit CPT override is persisted.
- **Explicit** — the Definition contains an authored value.
- **Inherited** — value follows a parent native setting, such as `public` or `show_ui`.
- **Dormant** — a stored child value is currently ineffective because its enabling parent is disabled; it is preserved but clearly inactive.
- **Computed** — read-only diagnostic derived from the compiled registration or runtime environment.
- **Blocked** — invalid, unsafe, collision-prone or governance-forbidden input; saving/publishing is prevented when required.

The UI must never silently coerce a Blocked state into a different authored value.

## Reset contract

- **Reset field** removes the explicit authored value and returns to its documented default/inheritance behavior.
- **Reset section** resets only options in the selected group and requires a preview of affected values.
- **Reset all advanced options** preserves identity and required naming while returning optional configuration to defaults.
- Resets participate in ordinary Definition revision/CAS and validation; there is no direct bypass write.

## Validation and diagnostics

The editor must surface server-produced issues using stable severity classes:

- blocked;
- compatibility warning;
- performance warning;
- information.

Required diagnostic families for the implementation lane:

- post-type key ownership/runtime collision;
- taxonomy association health;
- URL/rewrite/query-var preview and collision state;
- effective capability map;
- REST route/provider state;
- effective compiled `register_post_type()` arguments and authored override diff;
- environment-sensitive portability/import conflicts.

Diagnostics are read-only and cannot become a second configuration store.

## Editing workflows

### Create

1. Enter Essential identity/naming.
2. Select common behavior/supports.
3. Run server validation.
4. Save Draft or Publish through the canonical Ability/Policy path.
5. Show resulting revision and effective registration summary.

### Edit

1. Load the latest canonical Definition + revision.
2. Existing runtime key remains immutable in ordinary edit.
3. Changed values show explicit/default/inherited state before save.
4. Save requires revision/CAS and canonical validation.
5. Stale revision conflicts must not overwrite newer work.

### Lifecycle

Draft, Published, Disabled and Archived are Definition lifecycle states. The UI must describe runtime emission implications and must not equate lifecycle changes with deletion.

### Post-type key migration

Ordinary Save cannot rename an existing runtime key. The future Expert migration wizard must be a separate workflow with dry-run, dependency impact, collision report and recovery/rollback evidence. This contract does **not** authorize destructive content/meta/term rewrites.

## Accessibility requirements

The implementation lane must provide:

- native form labels and fieldset/legend grouping;
- keyboard-operable tier navigation and expandable sections;
- no color-only status meaning;
- programmatic descriptions for Default/Inherited/Dormant/Blocked states;
- `aria-live` validation/status updates that do not steal focus;
- focus placement on the first blocking validation error after submit;
- accessible tables/lists for saved definitions and diagnostics;
- screen-reader-readable collision/provider/migration warnings.

## Search and discoverability

A **Find setting** control indexes user-facing option labels, WordPress argument names and common synonyms. Selecting a result switches to the required tier, opens the owning section and focuses the control. Internal `_builtin` and `_edit_link` items are searchable only as read-only safety explanations, never authorable settings.

## Security / capability contract

- Current baseline administration is capability-gated and routed through shared Policy/Ability infrastructure.
- Provider-backed fields accept registered provider identifiers only.
- Internal WordPress `_builtin` and `_edit_link` arguments remain prohibited.
- No raw PHP/callback/class execution authoring surface may be added.
- Roles & Capabilities remains the grant owner; CPT only authors its native capability mapping.

## UX closure acceptance

This V1 UX contract is considered reviewed/planning-complete when the repository simultaneously contains:

- schema-valid `config/product/option-contracts/cpt.json`;
- deterministic projection of all **107** reviewed CPT Bank records;
- `coverage_summary.missing = 0` and `unclassified = 0`;
- this Essential / Advanced / Expert interaction contract;
- `docs/IMPLEMENTATION/CPT-BUILDER-RUNTIME-GAP-MATRIX-V1.md` mapping every normalized atomic option to existing runtime evidence or a concrete implementation gap;
- machine lifecycle promotion to `UX_CONTRACT_COMPLETE` only after contract validation passes.

`UX_CONTRACT_COMPLETE` is a planning milestone. It is not `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment or release approval.
