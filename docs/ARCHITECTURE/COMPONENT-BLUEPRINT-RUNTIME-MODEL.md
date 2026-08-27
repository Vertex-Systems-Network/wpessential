# WPEssential — Shared Component Blueprint Runtime Model

Status: **Phase 0 paper architecture / no implementation authorized**  
Used by: Builder Widgets, Frontend Dashboard, Dynamic Listings/Templates, Gutenberg/shortcodes/builder adapters, future AI composition.

## 1. Purpose

WPEssential needs one portable component contract without making Elementor, Gutenberg, Bricks, WPBakery or any other builder the canonical data model.

Architecture:

**Component Blueprint Definition → Published Compiled Blueprint → Component Instance Settings/Bindings → Authorized Render Context → Shared Renderer → Target Adapter/Markup/Assets**

## 2. Definition ownership

Definition Repository owns Component Blueprint configuration:
- UUID/key/name/category;
- component type: leaf/container/collection;
- control schema;
- slots/child constraints;
- render structure primitives;
- dynamic binding permissions;
- named style targets;
- responsive rules;
- conditions;
- accessibility contract;
- declared assets;
- adapter extension metadata;
- compatibility/schema version.

Rendered instances, builder documents and runtime query data are not Definition Repository revisions of the Blueprint itself.

## 3. Published compiled Blueprint

Publish validates and compiles to immutable descriptor containing:
- Blueprint/revision UUID;
- descriptor version;
- normalized control schemas/defaults;
- slot/nesting constraints;
- render primitive tree;
- binding descriptors;
- style target/property allowlists;
- asset graph;
- accessibility requirements;
- adapter capability requirements;
- dependency fingerprint.

Unknown/invalid controls or recursive structures cannot silently publish.

## 4. Component instance

An instance contains only values/references needed for one placement:
- Blueprint UUID/revision policy;
- instance UUID where host supports;
- static control values;
- dynamic binding descriptors;
- responsive values;
- child instances/slot references;
- adapter-specific extension data in namespaced area;
- instance visibility condition;
- optional style-token overrides.

Instance data never contains arbitrary executable PHP/JS.

## 5. Control schema

Canonical control properties:
- stable UUID/key;
- logical type;
- UI preference;
- value schema/default;
- validation/normalization;
- responsive support;
- dynamic binding allowed;
- visibility/dependency conditions;
- privacy/security classification;
- style mapping optional;
- import/export behavior.

Server validation is authoritative even when a builder provides its own editor control.

## 6. Dynamic bindings

Binding types may reference:
- current entity field;
- WPE Custom Field;
- site setting;
- user/profile safe field;
- relation;
- Query result/aggregate;
- Membership/Entitlement safe value;
- route/event context;
- registered resolver;
- builder-native dynamic source as adapter-specific extension.

Each binding declares:
- output type;
- source reference;
- context requirements;
- policy requirement;
- formatter;
- null/fallback behavior;
- cache sensitivity.

Builder-native binding data is not assumed portable across builders.

## 7. Render primitives

Canonical portable primitive family:
- container/section;
- text/heading;
- media/image/icon;
- link/button;
- list;
- key/value/data table;
- conditional;
- bounded loop/repeater;
- child slot;
- partial/component reference;
- semantic wrapper;
- registered SDK primitive.

No arbitrary raw PHP renderer or global arbitrary HTML/script primitive.

Sanitized rich text/HTML is a typed content value, not an execution channel.

## 8. Slots/nesting

Container Blueprint defines named slots with:
- allowed child component keys/categories;
- min/max children;
- ordering;
- default children;
- recursive-depth limits.

Cycle/depth validation occurs before publish/render.

A builder unable to represent native nested children can expose the component as one server-rendered WPE unit instead of pretending full fidelity.

## 9. Shared renderer

Default renderer is WPE server-side.

Pipeline:
1. load compiled Blueprint;
2. validate instance settings;
3. build authorized render context;
4. resolve bindings in batch where possible;
5. evaluate instance/primitive conditions;
6. resolve child components with depth guard;
7. create semantic render tree;
8. apply safe style-token/property mapping;
9. render escaped/sanitized markup;
10. enqueue declared scoped assets;
11. return render metadata/diagnostics.

Builder adapters may delegate to this renderer.

## 10. Builder-native rendering

Allowed only when certified adapter proves equivalent security/behavior.

Adapter declares fidelity:
- exact;
- adapted;
- degraded;
- unsupported.

Builder-native implementation must still use WPE validation/data Policy for WPE dynamic bindings.

## 11. Style system

Blueprint declares named style targets, not arbitrary global selectors.

Style mapping includes:
- target token;
- allowlisted CSS/property concept;
- typed value/control;
- responsive values;
- design token mapping;
- renderer transform.

Prefer WPE/design-system tokens.

Arbitrary scoped CSS Developer Mode remains separate future security/ADR work.

## 12. Assets

Blueprint declares assets by registered handle/module, not remote arbitrary CDN URL.

Asset metadata:
- style/script/module handle;
- editor/frontend/both;
- dependencies;
- condition;
- version/fingerprint.

Rules:
- load only when component present;
- no duplicate library copies where WordPress/builder supplies compatible dependency;
- builder adapters declare required editor assets separately;
- Pro-expired deployed safe component retains assets required for existing render according to ADR-0007.

## 13. Component contexts

Renderer context can expose typed safe values:
- site;
- current user safe subset;
- route;
- current entity;
- query item/index;
- listing/query metadata;
- Membership safe entitlement labels/flags;
- component parent/slot context.

Context does not expose secrets/raw global objects.

## 14. Collection component

Collection/query Blueprint declares:
- Query reference/inline bounded Query descriptor;
- item Blueprint/template;
- empty/loading/error state;
- limit/pagination contract;
- context mapping;
- optional aggregate/header/footer.

Query execution remains Query Builder/Data Source responsibility.

## 15. Caching

Cache eligibility derived from descriptor + bindings/context.

Cache key can include:
- Blueprint revision;
- instance fingerprint;
- locale;
- Query/data generation;
- entity revision;
- principal/access generation when output is user/access-specific;
- relevant setting/policy generation.

Public/static output may be shared. Personalized/protected output never enters a public shared cache without proof it is identical and safe.

## 16. Accessibility contract

Blueprint declares semantic requirements:
- root semantic element/role;
- interactive control names;
- keyboard behavior;
- focus states;
- live/state announcements;
- image alt/decorative semantics;
- reduced-motion handling;
- heading rules where applicable.

Adapter cannot claim high certification if essential accessibility semantics are lost.

## 17. Error/degraded behavior

- missing Blueprint → scoped placeholder/diagnostic for authorized admin, safe frontend fallback;
- missing binding source → declared fallback/error state;
- denied binding → no protected value leak;
- unsupported builder adapter → server-rendered WPE fallback if compatible, otherwise explicit unsupported;
- missing asset → health warning;
- recursive component → validation block;
- Pro editing unavailable → deployed safe render preserved under ADR-0007.

## 18. Portability

Portable package exports:
- Blueprint definition/revision;
- control/render/style schemas;
- dependencies;
- WPE bindings;
- adapter extension namespaces.

Importer reports adapter-specific nonportable data rather than silently dropping it.

## 19. AI-native composition

AI may create/edit Component Blueprint drafts only through typed Abilities/schema.

Default AI permissions:
- inspect/explain;
- generate draft component structure/settings;
- preview/diff.

AI does not gain arbitrary JS/PHP/style injection or publish privilege by default.

## 20. Future executable evidence — NOT AUTHORIZED

After explicit consent:
- descriptor schema and renderer prototype;
- nested/recursive/deep component fixtures;
- dynamic binding authorization/N+1 tests;
- asset isolation;
- cache isolation;
- Gutenberg/Elementor/Bricks/WPBakery adapter mapping;
- Visual Composer current API certification;
- responsive/style rendering;
- accessibility tests;
- SSR/client enhancement and performance benchmarks.

## Paper recommendation

Accept one canonical **Component Blueprint** shared by Builder Widgets, Dashboard and Listings, with third-party builders as adapters rather than canonical storage/runtime authorities.