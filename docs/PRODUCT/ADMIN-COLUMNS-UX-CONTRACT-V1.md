# WPEssential — Admin Columns UX Contract V1

Status: **UX_CONTRACT_CANDIDATE**  
Surface: **8 — `columns` / Admin Columns**  
Planning owner: **Issue #48**  
Shared Atomic infrastructure: **Issue #49 / PR #52**  
Atomic contract input: **`config/product/option-contracts/columns.json` — 41 Atomic Options / 214 source records**  
Runtime implementation: **NOT AUTHORIZED BY THIS CONTRACT**

## 1. Purpose

This document is the reviewed UX projection for Surface 8 Admin Columns.

It derives the user experience from:

`BANK_REVIEWED / 214 → Atomic Option contract / 41 → canonical ownership → Admin Columns UX`.

The UX must not recreate the Master Options Bank as 214 visible controls. Native hooks, diagnostics, runtime evidence, provider mappings, deferred behavior and peer-owned semantics are surfaced only when they are useful to the user and must remain distinct from authored settings.

This contract defines information architecture, interaction/state behavior, safety, accessibility, performance, portability and multisite expectations. It does **not** certify runtime implementation or product parity.

## 2. Canonical route and navigation

Admin Columns belongs under the canonical **Data & Intelligence** administration area.

Primary route label: **Admin Columns**.

Primary navigation within the module:

1. **Column Sets** — shared View/Column Set definitions.
2. **Segments** — saved shared/private filters, search and sort state.
3. **Adapters** — target/source/provider compatibility and degraded-state information.
4. **Diagnostics** — effective runtime evidence only; never normal authored settings.

A direct deep link may open a View editor, Segment editor or diagnostics detail while preserving module navigation and a clear path back to the collection screen.

## 3. UX state classes

Every visible value or state belongs to exactly one class.

### 3.1 Authored definition

User-configurable, revisioned Surface 8 definition state, including:
- View identity/name/enabled/default/scope;
- target adapter selection;
- shared layout;
- Column definitions;
- source/format selection;
- sorting/filtering/search configuration;
- shared Segments;
- editing/action policies;
- conditional formatting/visibility;
- export/performance/portability policies.

### 3.2 Personal preference

User-scoped state that MUST NOT mutate the shared View definition:
- chosen View;
- temporary sort;
- temporary filters;
- personal column visibility;
- personal density;
- personal saved filter state.

Personal preference controls are visually identified as **Personal** when confusion with shared definition state is plausible.

### 3.3 Effective/runtime state

Read-only state derived at runtime, for example:
- whether a target/source supports sorting/filtering/editing;
- provider availability;
- effective capability outcome;
- primary-column resolution;
- lazy/eager delivery outcome.

Effective state is never presented as an editable setting.

### 3.4 Diagnostic state

Read-only support/performance evidence, including:
- DB query count;
- remote call count;
- render duration;
- cache-hit state;
- batchability;
- row/page-size evidence;
- missing source;
- unsupported sort/filter/edit;
- invalid primary column;
- duplicate key;
- inaccessible field;
- stale adapter;
- Woo storage compatibility;
- remote source failure;
- unsupported bulk operation;
- sensitive export warning.

### 3.5 Deferred / prohibited state

Deferred or unsafe Bank semantics are not normal controls.

- Expert shortcode source remains **Deferred**.
- Arbitrary executable PHP/JavaScript source remains **Prohibited**.
- The UI MUST NOT offer an arbitrary code editor as a fallback.

## 4. Column Sets collection

The default Admin Columns screen is a collection of Column Sets / Views.

Each row/card exposes:
- View name;
- target type/key;
- enabled/disabled state;
- shared/personal scope where applicable;
- default state;
- assignment summary;
- last revision/update metadata when available;
- degraded/provider warning when applicable;
- actions allowed by Policy.

Collection actions:
- create View;
- open/edit;
- clone;
- enable/disable;
- set default where permitted;
- export definition;
- inspect dependencies / Used By;
- archive/delete only when the owning lifecycle supports it and impact is known.

The collection MUST provide explicit empty, loading, error and permission-denied states.

## 5. View / Column Set editor

The editor uses a stable multi-region layout rather than one unstructured settings page.

Recommended sections:

1. **General**
2. **Columns**
3. **Query & Segments**
4. **Editing & Actions**
5. **Export & Performance**
6. **Access & Visibility**
7. **Portability & Dependencies**
8. **Diagnostics**

The editor must distinguish shared authored state from personal/runtime state. Personal preferences are not edited inside the shared definition editor unless the UI explicitly labels a personal preview/reset action.

### 5.1 General

Contains:
- name;
- enabled state;
- target adapter/type/key;
- default View state;
- shared scope;
- role/user/capability assignment references;
- sticky header/horizontal scroll/density defaults.

Changing the target must trigger a capability re-evaluation before target-specific options are shown as usable.

### 5.2 Columns

The Columns section is an ordered builder/list.

Each Column summary shows:
- label/key;
- source type;
- display format;
- sort/filter/edit capability badges;
- width/responsive state;
- primary/row-action state;
- degraded or expensive-source warning when applicable.

Supported interactions:
- add Column;
- reorder Columns;
- enable/disable;
- edit Column;
- duplicate when identity semantics are regenerated safely;
- remove from the View;
- identify the primary Column.

Reordering is presentation order only and MUST NOT be confused with persistent content ordering owned by Surface 51.

## 6. Column editor

Column editing is progressive: Essential controls first, Advanced/Expert controls available without duplicating the same semantic.

### 6.1 Identity and layout

Expose as applicable:
- label;
- key/stable identity presentation;
- width/min/max width;
- alignment;
- responsive priority;
- sticky state;
- active state;
- primary-column eligibility;
- row-action behavior.

Stable internal UUIDs may be shown in diagnostics/developer context but are not ordinary editable text fields.

### 6.2 Source

Source chooser groups sources by canonical owner:
- WordPress/native property;
- Fields;
- Taxonomy;
- Relations;
- Media;
- Status;
- Query aggregate;
- registered computed/provider source;
- registered server-rendered block;
- compatibility/provider adapter.

The UI stores typed references only. It MUST NOT expose peer-private storage or clone peer engines.

When a source owner/provider is unavailable:
- preserve the saved reference;
- mark it **Unavailable/Degraded**;
- explain which owner/provider is missing;
- disable dependent operations that cannot run safely;
- provide a remediation path when one exists;
- do not silently substitute another source.

### 6.3 Formatting

Formatter choice is data/source-aware.

The UI may expose relevant format controls for:
- text/safe rich output;
- numbers/currency/percentage;
- dates/date-times/relative time;
- boolean/status;
- image/media/link/email;
- chips/relation items/count;
- JSON/progress/rating;
- registered formatter providers.

Only compatible formatter controls are enabled. Safe HTML never becomes arbitrary executable markup/code.

## 7. Sorting, filtering and search

Sorting/filtering/search controls describe a request to the canonical backend query/target adapter. Surface 8 MUST NOT become a private query engine.

### 7.1 Capability states

Every capability may be:
- **Available**;
- **Unavailable** with reason;
- **Provider required**;
- **Expensive** with warning;
- **Degraded** because a dependency is missing.

A disabled control without explanation is insufficient when the capability was requested or previously configured.

### 7.2 Sorting

Expose when supported:
- sortable toggle;
- mode/type;
- default direction;
- initial active sort;
- null/empty placement.

Sorting that cannot execute server-side before pagination MUST NOT be presented as valid full-list sorting.

### 7.3 Filtering

Expose only data-aware operators valid for the selected source, such as:
- text;
- numeric/range;
- date/time;
- taxonomy/entity;
- boolean;
- relation through Relations/Query integration.

Filter value sources may be static or registered/dynamic where the canonical adapter permits it.

### 7.4 Saved Segments

A Segment can combine:
- filters;
- search;
- sorting;
- View reference;
- private/role/global visibility.

Personal saved state stays user-scoped. Shared Segments require appropriate Policy checks.

Shareable Segment URLs, when implemented, must re-evaluate authorization and source visibility on every request; a URL is never authority.

## 8. Editing and actions

Surface 8 orchestrates the presentation/action request. The source owner remains authoritative for validation, sanitization, authorization and storage mutation.

### 8.1 Inline edit

The UI must:
- use an editor derived from the source/Field schema;
- show validation errors adjacent to the edited cell;
- preserve the previous canonical value on rejected writes;
- re-check capability/Policy server-side;
- support confirmation for configured risky values/actions;
- expose conflict/revision feedback when runtime support exists.

### 8.2 Bulk edit

Bulk editing may support source-compatible operations such as:
- set/replace;
- clear/null;
- numeric adjustment;
- list/taxonomy add/remove;
- date shift.

The UI must distinguish:
- current page selection;
- all rows matching the current canonical backend query.

“All matching” must show the selection/query scope clearly before a mutation is confirmed.

Large operations should expose background-job state when the runtime implementation later supports it.

### 8.3 Quick Edit / Quick Add

Quick Edit integrates with supported WordPress/source-owner controls.

Quick Add may expose a minimal create form derived from the source/Field schema. It does not create a Surface 8-private schema or persistence path.

### 8.4 Destructive actions

Bulk delete or comparable destructive operations require:
- explicit permission;
- explicit impact/scope preview;
- confirmation proportional to risk;
- no inference from UI visibility alone;
- recovery/restore path where the canonical owner supports one.

This UX contract does not authorize executing destructive operations.

## 9. Conditional formatting and visibility

Conditional formatting uses structured non-executable rules.

Accessibility requirement:
- color MUST NOT be the only carrier of meaning;
- state changes need text/icon/semantic alternatives where necessary;
- contrast must remain usable in supported themes/states.

Visibility is presentation behavior only.

**MUST NOT:** hiding a Column, row action, View or Segment be treated as revoking access to the underlying resource or operation.

## 10. Export UX

Export UX defines:
- scope: current page / selected rows / all matching when supported;
- selected Columns;
- respect current filters;
- respect current sort;
- canonical raw versus displayed value policy;
- CSV delimiter/encoding/headings;
- date/time timezone;
- background threshold/status when supported.

Before export:
- Policy/capability is checked server-side;
- sensitive/inaccessible fields are excluded or explicitly blocked according to owner policy;
- formula-injection mitigation is applied by the runtime export contract;
- UI explains redactions/omissions rather than silently leaking or fabricating values.

## 11. Performance UX

Performance configuration and diagnostics are separate concepts.

Authored performance policy may expose:
- cache behavior;
- eager/batched/lazy delivery mode;
- lazy error behavior;
- disabling sort/filter when backend query support does not exist.

Read-only diagnostics may show:
- query count;
- remote calls;
- render duration;
- cache hit/miss;
- batchability;
- per-page row count;
- unsupported expensive operations.

WPE safety expectations include no-N+1 evidence, query budget and batch hydration contracts. These remain runtime certification requirements and are not considered proven by this document.

## 12. Adapters and degraded states

Adapters include native targets and supported compatibility providers such as ACF/SCF, Meta Box, JetEngine and WooCommerce where installed and supported.

Adapter status UI should distinguish:
- available/healthy;
- provider not installed;
- provider disabled;
- unsupported version/capability;
- stale mapping;
- storage-mode incompatibility;
- permission unavailable.

WooCommerce order data MUST use supported WooCommerce/storage adapters and must not assume legacy `shop_order` postmeta truth when HPOS is authoritative.

DataViews compatibility, if exposed later, is a presentation/view-state integration and never a client-side replacement for canonical server query semantics.

## 13. Portability and dependencies

View-definition export/import is definition portability, not raw source-data ownership.

Import review must identify references that require mapping, including:
- Field UUID;
- Query UUID;
- Relation UUID;
- target adapter/provider.

Missing references produce a degraded state with explicit remediation. They are not silently dropped or remapped heuristically.

A **Used By / Dependencies** view should expose typed dependencies without opening peer-private implementation state.

## 14. Multisite and scope

Site/network/user scope is derived from trusted runtime context.

Rules:
- site-scoped definitions cannot silently mutate another site;
- personal preference state remains user-scoped within the applicable site/context;
- network-level behavior requires an explicit network-owned contract and capability;
- request parameters are not trusted authority for site/network identity;
- imported definitions must resolve references within the target scope deliberately.

## 15. Accessibility contract

All implemented Admin Columns UI must provide, as applicable:
- semantic headings/landmarks;
- keyboard-operable builders, menus and dialogs;
- visible focus;
- accessible reorder alternative to pointer-only drag/drop;
- screen-reader labels/status for capability/degraded state;
- accessible validation/error association;
- non-color-only conditional formatting;
- focus restoration after dialogs/actions;
- loading/empty/error/success announcements when dynamic behavior requires them;
- reduced-motion-safe behavior where animation exists.

Runtime browser/Axe evidence is required later; this document alone is not accessibility certification.

## 16. Loading, empty, error and recovery states

Every major screen/editor must define:
- initial loading;
- empty collection;
- no results after search/filter;
- recoverable validation error;
- permission denied;
- missing provider/source;
- stale/deleted reference;
- network/remote provider error where applicable;
- failed save with retained user input;
- conflict/revision mismatch;
- background operation pending/success/failure where later implemented.

Errors must be actionable and production-safe. No stack traces, SQL, secrets or reusable credentials are exposed.

## 17. Security UX invariants

The UX MUST NOT:
- treat hidden controls/routes as authorization;
- accept arbitrary executable PHP/JavaScript configuration;
- expose secrets in visible config, export, logs or diagnostics;
- bypass source-owner validation or Policy from inline/bulk/quick actions;
- let a client-provided site/network scope become authority;
- present a provider transport success as proof of a committed source-owner mutation;
- expose an unsupported sort/filter/edit capability as if it were operational.

## 18. Performance and scale acceptance targets

Future runtime implementation must include evidence for realistic large list tables and expensive sources.

At minimum, runtime acceptance should evaluate:
- query count/N+1 behavior;
- pagination correctness;
- sort/filter execution before pagination;
- batched hydration;
- cache invalidation;
- remote/provider failure cost;
- bulk/export threshold behavior;
- payload/render cost;
- diagnostics accuracy.

No concrete runtime performance threshold is invented by this UX contract where repository evidence has not yet certified one.

## 19. UX lifecycle exit criteria

Surface 8 may be promoted to `UX_CONTRACT_COMPLETE` only when all are true:

1. Atomic Option contract remains schema-valid and `OPTION_CONTRACT_COMPLETE` or later.
2. 214/214 Bank-source projection remains complete with zero duplicates/unmapped records.
3. `missing = 0` and `unclassified = 0` remain true.
4. Authored, personal, effective/runtime, diagnostic and deferred/prohibited states are distinguished.
5. Canonical Query/Fields/Relations/Tables/Roles/Policy ownership is preserved.
6. Visibility-not-authorization and arbitrary-code rejection are explicit.
7. Degraded/provider-disabled states are defined.
8. Accessibility, security, performance, portability and multisite behavior are explicit.
9. Surface-local UX validator/evidence passes on the exact candidate head.
10. Shared progress promotion is applied only through Issue #49 integrator after exact-head evidence.

## 20. Non-certifications

This contract does **not** claim:
- Admin Columns runtime implementation;
- real WordPress list-table hook integration;
- inline/bulk mutation implementation;
- export implementation;
- Query/Relations/Fields runtime adapter completion;
- browser UI completion;
- accessibility runtime certification;
- performance runtime certification;
- production deployment/release;
- product parity certification.

Those require later dependency-aware implementation and executable evidence.
