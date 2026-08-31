# ADR-0131 — P-009 Query Compiler / Cost / Cache / Security Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP14`

## Context

WPEssential already has strong paper Query architecture:

- the Query AST is typed/versioned and provider-neutral;
- raw SQL/arbitrary PHP callbacks are not normal Query primitives;
- runtime values are typed/untrusted while identifiers resolve through registered schemas;
- authorization/data-source policy runs before execution/disclosure;
- public/admin/API/Workflow budgets are distinct;
- QP1 WordPress-native, QP2 Custom Table, QP3 Relations-assisted and QP4 remote Data Source are separate compiler/provider profiles;
- persistent cache is only safe when scope/principal/source/relation/policy/pagination/locale dependencies are representable;
- normal relation traversal may not degrade into unbounded per-row N+1;
- site scope is default and network/cross-site aggregation requires explicit server-side authorization/capability/bounds.

The generic P-009 spike and benchmark profile did not freeze one complete adversarial certification boundary covering revisions, typed parameters, row/field/count authorization, provider semantic equivalence, cursor integrity, cost rejection, revocation-safe caching, remote adapter reauthorization, consumer composition and Multisite aggregation.

## Decision

Accept `docs/QUALITY/P009-QUERY-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the fixed P-009 executable evidence contract.

It defines **QRY-01…QRY-168** covering:

- Query Definition/revision/consumer identity and incompatible-future-schema handling;
- AST validation, typed parameters and hostile semantic/input cases;
- Policy, row/resource, projection/field and scope enforcement;
- QP1 WordPress posts/users/terms/comments/media/ecosystem adapter behavior;
- QP2 registered Custom Table compiler, prepared values, identifiers, null/collation/join/aggregate semantics;
- QP3 relation traversal, two-phase plans, cardinality, fanout and zero-tolerance normal-list N+1;
- QP4 remote Data Source semantics through Connections/Safe HTTP/Vault boundaries;
- context-specific cost budgets and pathological-plan rejection;
- deterministic order, offset/keyset/cursor integrity and concurrent-write behavior;
- count/aggregate inference-leakage boundaries;
- persistent cache identity, invalidation and authorization-generation revocation;
- Multisite/network aggregation isolation and scale;
- Explain/diagnostics/error redaction;
- consumer composition for Listings/Admin Columns/REST/Forms/Dashboard/Workflow;
- 10k/100k/1M workloads and independent final security review.

## Preserved invariants

1. Query Definition/Revision, invocation, compiled provider operation and result/cache entry are distinct truths.
2. Draft edits do not silently alter live published consumer semantics.
3. No raw SQL node/arbitrary callback/eval exists in normal Query AST.
4. User parameters never become unchecked identifiers/control syntax.
5. Unsupported provider semantics fail validation rather than silently degrade.
6. Authentication is never substituted for row/resource/field/scope authorization.
7. Admin-preview safety/performance does not imply public/API/Workflow safety.
8. Wrong-site/network/unauthorized result count must remain zero.
9. Count/aggregate metadata is protected when it can reveal hidden rows/cohorts.
10. Persistent shared caching is disabled when authorization/invalidation dependencies cannot be modeled safely.
11. A committed revoke/policy-generation change cannot keep serving stale protected allow/results.
12. Cursor/page state is untrusted and must be bound/revalidated against revision/provider/scope/order/parameters/authorization.
13. QP4 remote provider data is reauthorized locally and cannot bypass Safe HTTP/Connection/Vault policy.
14. Relation/list execution cannot use unbounded N+1 merely because it is simpler.
15. Performance cannot override scope, authorization, semantic correctness or cache isolation.

## Evidence state

- QRY fixtures documented: **168**
- QRY fixtures executed: **0/168**
- P-009 runtime certifications: **0**
- QP1 certified provider/profile count: **0**
- QP2 certified provider/profile count: **0**
- QP3 certified provider/profile count: **0**
- QP4 certified provider/profile count: **0**
- independent P-009 security review executed: **NO**
- final numeric cost thresholds: **OPEN / evidence-gated**
- final persistent cache backend/default: **OPEN / evidence-gated**
- final cursor encoding/profile: **OPEN / evidence-gated**

ADR-0086 remains the accepted compiler/benchmark profile boundary. This ADR fixes the evidence protocol only; it does not certify QP1–QP4, select one universal compiler or accept numeric performance thresholds.

## Stop-the-line examples

P-009 cannot certify if raw SQL/arbitrary eval becomes reachable; SQL/identifier/order/projection injection succeeds; unsupported semantics are ignored; wrong-site/network data is returned; row/field/count authorization is bypassed; privileged cache leaks to lower privilege; stale protected cache survives committed revoke; cursor tamper/replay leaks data; public/API hard budgets can be escaped; normal relation execution becomes unbounded N+1; remote provider results bypass local Policy; secrets appear in AST/logs/diagnostics/cache; or benchmark speed is used to waive a correctness/security failure.

## Development gate

This ADR authorizes no Query compiler, SQL generation, DB fixture, data mutation, query execution, cache operation, remote request, WordPress runtime, benchmark, provider certification or security test.

ADR-0014 explicit scoped owner consent remains required before every executable P-009 action.