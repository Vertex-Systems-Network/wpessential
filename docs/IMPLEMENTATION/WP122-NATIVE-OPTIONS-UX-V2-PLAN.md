# WPEssential — WP122 Native Options & Admin UX V2 Execution Plan

Status: **Bounded implementation plan**  
Date: 2026-08-31  
Architecture: `docs/ARCHITECTURE/NATIVE-WORDPRESS-OPTIONS-ADMIN-UX-V2.md`

## 1. Objective

Upgrade the existing WP122 CPT and Taxonomy foundation from bounded CRUD/validation screens into a mature WordPress configuration product without discarding the certified Definition/Ability/Policy/runtime architecture.

This plan is deliberately incremental. Existing working runtime paths remain until each replacement slice passes exact-head CI.

## 2. Current-state finding

The existing implementation is not a complete native-option builder. It currently proves the important architectural base:

- canonical Definition identity and revision handling;
- CPT and Taxonomy owner modules;
- runtime registration;
- collision/preflight validation;
- authenticated owner Abilities/AJAX;
- admin assets and packaged browser/Axe coverage;
- CPT-to-Taxonomy association ownership.

The missing product layer is:

- exhaustive safe declarative native option coverage;
- default/inherited/explicit value semantics;
- schema/projector support for those options;
- mature progressive-disclosure editor UX;
- shared admin-shell primitives;
- API-drift coverage guards.

## 3. Workstream A — machine-readable native option contracts

### A1. Contract model

Introduce a framework-level immutable option metadata model, not CPT-specific arrays embedded in TypeScript.

Candidate namespace:

`WPEssential\Framework\Definitions\Options`

Candidate primitives:

- `OptionContract`;
- `OptionDescriptor`;
- `OptionGroup`;
- `OptionTier`;
- `DefaultRule`;
- `DependencyRule`;
- `SecurityClass`;
- `PortabilityClass`;
- `NativeApiCoverageRegistry`.

The final names may follow existing repository naming conventions after code audit.

### A2. Registry ownership

Surface 1 owns CPT contract/coverage.  
Surface 2 owns Taxonomy contract/coverage.  
Shared framework validates metadata but does not own feature semantics.

### A3. Source snapshot

Check in a native-API coverage registry for the supported WordPress line. Each key is one of:

- supported;
- extension-only;
- internal/prohibited;
- intentionally deferred with explicit issue/reason.

A missing classification fails tests.

## 4. Workstream B — definition schema and effective-value resolver

### B1. Persist explicit intent, not redundant defaults

Canonical payloads should store explicit overrides plus stable required identity fields. Effective values are resolved from:

`required identity + explicit overrides + WordPress defaults/inheritance + WPE policy/provider values`.

Do not blindly persist every current WordPress default because that freezes accidental defaults and makes future compatibility harder.

### B2. Backward compatibility

Existing CPT/Taxonomy definitions remain valid. Missing new keys resolve naturally to defaults.

No UUID replacement. No silent runtime-key mutation.

### B3. Effective resolver

Add server-side resolver used by:

- validation/preflight;
- runtime projector/registrar;
- admin bootstrap/effective preview;
- export/import normalization;
- tests.

Client-side dependency UI may mirror the contract but is never authoritative.

## 5. Workstream C — CPT projector expansion

Map classified safe native options into the CPT runtime registration args.

Slices:

1. labels/description;
2. visibility/admin UI;
3. supports/editor/template;
4. relationships;
5. archive/rewrite/query;
6. capabilities;
7. REST;
8. lifecycle/export;
9. provider-backed extension-only controls.

Every slice needs runtime assertions showing the emitted `register_post_type()` argument structure and effective WordPress object behavior.

## 6. Workstream D — Taxonomy projector expansion

Map classified safe native options into Taxonomy runtime registration args.

Slices:

1. labels/description;
2. visibility/admin UI;
3. associations;
4. rewrite/query;
5. capabilities;
6. REST;
7. default term/sort/bounded args;
8. provider-backed extension-only controls.

Two-sided CPT/Taxonomy association truth must remain consistent with `WP122-ASSOCIATION-OWNERSHIP.md`.

## 7. Workstream E — Shared Admin UX V2

### E1. Shared primitives

Create reusable admin UI primitives before duplicating complex forms across CPT and Taxonomy.

Target concepts:

- `PageFrame`;
- `ModuleNav`;
- `DefinitionTable`;
- `EditorCommandBar`;
- `SettingsNavigation`;
- `SettingsSection`;
- `DisclosureTier`;
- `SettingControl` family;
- `DefaultStateBadge`;
- `RelationshipPicker`;
- `DiagnosticsRail`;
- `SettingSearch`;
- `EffectiveArgsPreview`.

Do not introduce a heavyweight design-system dependency merely for this work.

### E2. Native visual language

Use WordPress admin conventions for typography, form semantics, notices, buttons and focus behavior. WPE may improve information architecture/density, but should not mimic SCF branding/layout pixel-for-pixel.

### E3. Editor modes

Default: **Essentials**.  
Opt-in: **Advanced**.  
Deliberate developer mode: **Expert**.

Mode is a visibility/navigation aid; it does not create separate runtime schemas.

## 8. CPT editor information architecture

### Essentials

- Plural label;
- Singular label;
- Post Type Key;
- short description (optional);
- Public;
- Hierarchical;
- primary Taxonomy associations;
- common Supports;
- Active/status controls.

### Advanced groups

#### General
Identity, description, status and inheritance overview.

#### Labels
Complete label overrides generated from plural/singular defaults.

#### Visibility
Public, publicly queryable, search inclusion, nav menus, admin bar.

#### Admin UI
show_ui, show_in_menu/parent, menu position, icon.

#### Content & Editor
supports, block template, template lock.

#### Relationships
Taxonomy associations with ownership/collision diagnostics.

#### URLs & Rewrite
archive, rewrite mode/slug/front/feed/pages/endpoint mask, query variable, examples.

#### Permissions
capability type, meta-cap mapping, custom capability map/effective preview.

#### REST API
show_in_rest, namespace/base, provider-backed controllers, route preview.

#### Developer / Compatibility
late route registration, provider-backed callbacks, portability/schema details, effective native args.

## 9. Taxonomy editor information architecture

### Essentials

- Plural label;
- Singular label;
- Taxonomy Key;
- Object Types;
- Public;
- Hierarchical;
- show admin column/common UI controls;
- Active/status.

### Advanced

General, Labels, Visibility, Admin UI, Relationships/Object Types, URLs & Rewrite, Permissions, REST API, Developer/Compatibility.

## 10. Interaction details required before completion

- sticky Save + Validate command bar;
- dirty state;
- browser navigation guard;
- inline errors plus summary diagnostics;
- error summary links/focuses the field;
- section-level reset to WordPress defaults;
- per-setting reset;
- inherited/default badges;
- setting search;
- collapsible diagnostics rail;
- visible current vs pending runtime effect where high impact;
- relationship chips with remove/search/add keyboard flows;
- icon picker using safe source modes;
- URL examples;
- REST route examples;
- effective capabilities preview;
- effective WordPress args preview;
- high-impact change confirmation only when materially needed.

## 11. List screen upgrade

CPT and Taxonomy collection screens should converge on a shared list experience:

- status tabs;
- search;
- pagination;
- bulk lifecycle actions where safe;
- configurable columns;
- Screen Options-compatible preference storage where feasible;
- compact/comfortable density;
- row actions visible on focus/hover without becoming inaccessible;
- relationship counts;
- validation state/diagnostic indicator;
- no oversized blank layout.

## 12. Data loss prevention

Conditional UI introduces stale hidden-value risk.

Policy:

- hiding a child control does not silently delete its explicit value;
- if a parent makes an override dormant, show that dormant overrides exist;
- provide reset dormant values action;
- runtime effective resolver ignores inapplicable values unless WordPress semantics require otherwise;
- changing a parent from inherited to explicit and back is round-trip tested.

## 13. Security requirements

- all mutations remain nonce + Policy + owner Ability protected;
- TypeScript visibility cannot authorize anything;
- callback/class-like controls accept provider IDs or allowlisted values, not arbitrary executable strings;
- capability changes are validated and warn against self-lockout conditions;
- import/export must preserve only portable safe declarative state;
- no secrets in effective args preview;
- no eval or dynamic PHP code generation.

## 14. Performance requirements

- option contracts bootstrap as bounded metadata;
- setting search is client-local after bootstrap;
- no per-control server request;
- list screens avoid N+1 relationship/diagnostic queries;
- validation can be incremental client-side but authoritative preflight remains bounded server-side;
- admin assets remain deterministic and distributable.

## 15. Test matrix

### Unit/contract

- source-key classification complete;
- defaults/inheritance;
- dependencies;
- dormant explicit values;
- label generation/override;
- rewrite structures;
- capabilities;
- REST structures;
- serialization.

### Integration/runtime

- real WordPress CPT registration object reflects intended options;
- real WordPress Taxonomy object reflects intended options;
- relations remain registered both ways;
- reserved/collision behavior;
- schema backward compatibility;
- CAS/revision behavior;
- package import/export preservation once Surface 26 is certified.

### Browser

- Essentials create/edit;
- Advanced toggle;
- Expert entry;
- all primary tabs;
- conditional reveal/hide;
- inherited reset;
- setting search;
- dirty guard;
- validation focus;
- relationship picker;
- effective args preview;
- responsive layout;
- Axe zero violations;
- keyboard-only critical flow.

## 16. Milestone/branch strategy

Use short-lived branches based on latest certified `main`.

Suggested sequence:

- `implementation/wp122-option-contract-foundation`
- `implementation/wp122-admin-ux-v2-shell`
- `implementation/wp122-cpt-native-options-v2`
- `implementation/wp122-taxonomy-native-options-v2`
- `implementation/wp122-options-ux-v2-polish`

Do not stack long-lived feature branches unnecessarily. Merge each certified slice to main before taking the next baseline when dependencies allow.

## 17. Merge gate

No slice merges until its exact source head passes all workflows applicable to the changed surface. At minimum preserve:

- Architecture Guards;
- PHP Quality Toolchain;
- Distributable Package;
- CPT Runtime;
- Taxonomy Runtime;
- Browser E2E Accessibility;
- Platform Compatibility Matrix.

A UI-only green test cannot compensate for a runtime regression.

## 18. Definition of done

WP122 Native Options & Admin UX V2 is done when:

1. all current public safe declarative CPT/Taxonomy native registration arguments are classified;
2. every supported option is represented by the machine-readable contract;
3. arbitrary executable options are explicitly extension-only/prohibited;
4. existing definitions remain compatible;
5. runtime registration consumes effective resolved values;
6. default UI remains concise;
7. Advanced/Expert users can reach every supported classified option;
8. defaults/inheritance are visible/resettable;
9. list/editor UX is shared, responsive and accessible;
10. API drift is machine-detectable;
11. packaged browser tests prove real create/edit flows;
12. full compatibility CI is green.
