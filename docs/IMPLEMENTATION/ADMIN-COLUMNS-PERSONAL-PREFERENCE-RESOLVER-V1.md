# Admin Columns Personal Preference Resolver V1

Status: **IMPLEMENTED FOUNDATION / GATE D STILL ACTIVE**  
Issue: #242  
Parent: #66

## Scope

This tranche introduces a pure, side-effect-free resolver for the user-scoped Admin Columns state defined by the UX contract as **Personal preference**.

The resolver accepts one canonical View identity/revision, its finite enabled column projection and one bounded preference object. It returns normalized personal state for:

- chosen View identity;
- temporary sort;
- temporary filters;
- personal hidden columns;
- personal density;
- bounded personal saved-filter state.

## Safety boundaries

The resolver performs **no** persistence, authorization, Query execution, source-owner reads/writes, UI rendering or shared View mutation.

Personal visibility or a chosen View is presentation/user state only. It cannot grant Policy access and cannot make a disabled/missing canonical column addressable.

Unknown keys, foreign View identity, duplicate sort/visibility references, unsupported operators/directions/density, malformed UTF-8, non-finite floats and over-budget list/string values fail closed.

## Separation from shared authored state

The canonical View projection is input evidence only. The resolver never rewrites a revisioned View payload. Persistence to WordPress user-meta or another user-scoped owner remains a later separately gated lane.

## V1 budgets

- canonical columns: <= 100;
- temporary filters: <= 20;
- temporary sorts: <= 5;
- list-valued filter operand: <= 20 scalar/null values;
- string filter operand: <= 1024 bytes.

## Non-scope

- user-meta/database persistence;
- assignment/authorization decisions;
- Query compilation/execution;
- controller/AJAX/REST/UI wiring;
- shared Segment definition persistence;
- Gate D PASS, Gate E start, Status runtime, product parity or deployment.
