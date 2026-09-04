# Admin Columns Authoring Scaffold V1

Status: **NON-RUNTIME IMPLEMENTATION CANDIDATE / GATE D NOT PASS**  
Parent: #66  
Issue: #192

## Purpose

Provide the first accessible Surface 8 admin authoring scaffold derived from the reviewed Admin Columns UX contract without exposing save, mutation, provider execution or a private query engine.

The scaffold is intentionally separate from the PHP Definition foundation (#191). Canonical route/bootstrap/build/enqueue integration is a later serialized tranche after both sides expose compatible contracts.

## Bootstrap boundary

`parseAdminColumnsBootstrap()` accepts only a server-owned future contract with:

- `surface = columns`;
- `contractVersion = 1`;
- one or more typed target adapters;
- one or more typed source-owner references;
- explicit effective capability booleans for sort/filter/edit/export;
- allowlisted source owners and display formats.

Malformed, empty or unsupported bootstrap data fails closed. No peer-private storage keys or executable callbacks are accepted.

## UX boundary

`mountAdminColumnsScaffold()` renders:

- module navigation for Column Sets, Segments, Adapters and Diagnostics;
- an authored shared Column Set draft only;
- target selection with read-only capability state;
- ordered Columns with source-owner reference, format and presentation visibility;
- keyboard-accessible Move up / Move down controls;
- explicit Query ownership messaging for sort/filter/search;
- explicit source-owner + Policy messaging for mutation;
- export safety and diagnostics/effective-state boundaries;
- disabled save with an explanation of the missing server integration.

No network request, AJAX/REST call, Definition mutation or provider invocation exists in this file.

## State separation

The scaffold explicitly preserves the reviewed UX classes:

- authored shared draft state is locally editable;
- Personal preferences are a separate future user-scoped contract;
- effective capabilities are read-only bootstrap/runtime facts;
- diagnostics are read-only runtime evidence;
- deferred/prohibited behavior is not converted into ordinary controls.

Presentation visibility is explicitly described as non-authorizing.

## Accessibility

- native form controls and labels;
- fieldsets/legends for grouped concepts;
- keyboard-operable reorder controls rather than drag-only behavior;
- `role=status` + `aria-live` for local state changes;
- explanatory text for disabled save/degraded capabilities;
- 44px minimum mobile action height at the WordPress admin breakpoint;
- no unsafe HTML injection; user/provider labels are assigned through `textContent`.

## Candidate validation

The TypeScript source is normalized with the repository-owned JavaScript formatter/linter before promotion. Formatting changes do not widen the runtime boundary, add shared build wiring or authorize save/execution behavior.

Reorder actions also guard both indexed draft entries before assignment. This preserves the same local move behavior while satisfying the repository's strict indexed-access TypeScript contract; invalid/stale indices fail closed without mutating the authored draft.

## Explicit non-goals

- canonical WordPress route or enqueue;
- package/build entry registration;
- persistence or revision conflict UI;
- Query execution;
- inline/bulk/source-owner mutation;
- export execution;
- Personal preference storage;
- effective diagnostics provider;
- product parity or Gate D PASS.

Those integrations require later serialized or owner-specific tranches and their own exact-head evidence.
