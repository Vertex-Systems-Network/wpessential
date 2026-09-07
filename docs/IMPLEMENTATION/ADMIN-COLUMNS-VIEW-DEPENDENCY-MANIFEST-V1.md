# Admin Columns View Dependency Manifest V1

Status: **IMPLEMENTED FOUNDATION / GATE D STILL ACTIVE**  
Issue: #252  
Parent: #66

## Scope

This tranche adds a pure, side-effect-free dependency manifest projector for one canonical Admin Columns View.

The projector first delegates payload validation/normalization to `AdminColumnsViewDefinitionNormalizer`, then emits only stable typed dependency references needed by later **Used By**, import-review and dependency-inspection integrations.

## Manifest output

The V1 manifest contains:

- the stable View machine key;
- the target dependency (`type` + `key`);
- unique source dependencies (`owner` + typed `reference`) in deterministic first-use order;
- the ordered authored Column keys that reference each source dependency;
- assignment role, user and capability dependency sets when present.

Repeated use of one source is consolidated into one source dependency while retaining all referencing Column keys in authored order.

## Ownership boundary

The projector does not resolve a dependency into runtime/provider state. It does not read peer-private storage, call providers, execute Query, inspect Fields/Relations internals, persist anything or grant authorization.

Environment availability/degraded-state resolution remains owned by canonical adapters/owners and the separately certified effective-state projection boundary.

## Safety properties

- malformed View payloads fail through the canonical normalizer;
- no heuristic remapping or environment discovery occurs;
- no provider secrets/private state are exposed;
- no dependency reference becomes an authorization grant;
- the manifest is derived/read-only state and is not written back into the View definition.

## Bounds

The projector inherits the canonical View V1 bounds, including the maximum of 100 Columns and bounded typed references/assignment sets.

## Non-scope

- dependency graph persistence;
- cross-View reverse-index / Used By storage;
- provider/source discovery;
- import commit/migration workflow;
- UI/controller/AJAX/REST wiring;
- Query execution;
- source-owner mutation;
- Policy authorization;
- Gate D PASS, Gate E start, Status runtime, product parity or deployment.
