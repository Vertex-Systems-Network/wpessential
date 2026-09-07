# Admin Columns Effective / Degraded-State Projector V1

Status: **IMPLEMENTED FOUNDATION / GATE D STILL ACTIVE**  
Issue: #249  
Parent: #66

## Scope

This tranche adds a pure, side-effect-free projector for Admin Columns **effective/runtime** and bounded **diagnostic** state.

The projector consumes only evidence already supplied by canonical target/source owners or adapters. It performs no provider discovery, Query execution, persistence, source-owner mutation, controller/UI work or authorization decision.

## Effective states

V1 accepts these explicit states:

- `available`
- `unavailable`
- `provider_required`
- `expensive`
- `degraded`

Each target/source evidence node carries an explicit finite capability projection for sort/filter/edit together with batchability, provider compatibility and storage compatibility.

Contradictory evidence fails closed. Examples:

- `available` cannot carry an error reason or incompatible provider/storage evidence;
- `unavailable` cannot advertise usable capabilities or batchability;
- `provider_required` cannot claim that the provider is compatible/present;
- `expensive` requires a bounded reason plus compatible provider/storage evidence and at least one usable capability;
- `degraded` requires a bounded reason and cannot be paired with a fully healthy all-capabilities/batchability/compatibility projection.

## Diagnostic projection

V1 exposes only the following bounded diagnostic fields supplied by the caller:

- row count;
- page size;
- query count;
- remote-call count;
- cache state (`hit`, `miss`, `unknown`).

Source-level batchability and provider/storage compatibility remain part of each effective evidence node rather than being copied into authored View data.

## Security and privacy boundary

Unknown evidence keys fail closed. Keys that indicate secrets, credentials, authorization material, cookies, nonces, SQL, stack traces or private diagnostics are rejected recursively before projection.

The projector does not emit or infer an `authorized` state. Effective availability is **not** an authorization grant; any later operation still requires canonical Policy and owner authorization.

## State separation

The output is derived/read-only runtime evidence. It is not written into:

- revisioned shared View definitions;
- personal preference state;
- source-owner storage;
- provider configuration.

## V1 budgets

- source evidence nodes: <= 100;
- page size: 1..100;
- row count: 0..1,000,000;
- query / remote-call counters: 0..100,000;
- nested evidence scan depth: <= 16;
- source references: bounded typed references;
- reason codes: bounded machine keys.

## Non-scope

- provider/source discovery;
- Query execution;
- Policy authorization;
- diagnostics persistence/logging;
- controller/AJAX/REST/UI wiring;
- action execution or mutation;
- Gate D PASS, Gate E start, Status runtime, product parity or deployment.
