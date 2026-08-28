# ADR-0149 — Entity / Data Source Registry Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP32`

## Context

WPEssential architecture defines one shared Entity / Data Source engine beneath Fields, Relations, Query, REST, Workflows, Listings, Component Blueprints, Import/Export and future AI/Abilities. Existing protocols verify those consumer domains and their provider-specific paths, but no dedicated evidence contract existed for the Data Source Registry itself.

The shared registry must describe source identity, entity identity, schema, scope, read/list/query/create/update/delete capabilities, batch behavior, concurrency/transaction semantics, authorization/Policy, cache generations, lifecycle and provider limitations without allowing consumers to infer unsupported write/query/security behavior from simple source discovery.

ADR-0014 remains authoritative: accepting this protocol does not authorize entity/provider/runtime execution.

## Decision

Adopt `docs/QUALITY/ENTITY-DATA-SOURCE-REGISTRY-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for the Entity / Data Source Registry.

The protocol freezes **DSR-01…DSR-176**.

Current execution truth: **0/176 executed**.

No Data Source runtime or provider certification exists yet.

## Canonical boundary

Keep these truths distinct:

`entity type ≠ Data Source adapter ≠ registered source instance ≠ schema descriptor ≠ capability descriptor ≠ source scope ≠ principal authorization ≠ resource Policy ≠ queryability ≠ mutability ≠ transaction guarantee ≠ runtime entity record ≠ cached projection ≠ provider certification`

A source being readable/discoverable never implies create/update/delete/query/sort/filter/transaction/public/AI capability.

## Independent certification classes

- `DSR-R` — registry identity/ownership/discovery;
- `DSR-S` — schema/type/field capability truth;
- `DSR-A` — authorization/Policy/scope isolation;
- `DSR-Q` — read/list/query/pagination/count semantics;
- `DSR-W` — create/update/delete/bulk mutation;
- `DSR-C` — concurrency/transactions/idempotency;
- `DSR-P` — first-party WordPress providers;
- `DSR-T` — Custom Table/module-owned providers;
- `DSR-X` — remote/ecosystem/extension providers;
- `DSR-I` — identity/Relations/import-export/lifecycle;
- `DSR-O` — cache/events/privacy/observability/performance/Multisite.

Passing one class never certifies another.

## Core invariants

1. Every source/adapter has stable namespaced identity, owner, version and explicit site/network/global scope.
2. Schema visibility and source registration never grant authorization.
3. Read, get-one, list/query, create, update, delete and bulk capabilities remain independent.
4. Field read/write/search/sort/filter/projection capabilities are explicit and typed; protected/system/security fields remain separately governed.
5. Capability + current target Policy applies to every record/field operation, regardless of UI, REST, Workflow, CLI or AI channel.
6. Caller-supplied/current-blog context cannot expand target source/site/network authority.
7. Unsupported query/provider semantics fail validation rather than silently approximating a different result.
8. Remote-provider responses are revalidated/reauthorized locally and do not carry authority merely because remote access succeeded.
9. Generic Data Source interfaces cannot become arbitrary table/column/meta/option/URL/PHP/SQL access channels.
10. Transaction/concurrency claims are exact to the provider/operation boundary; one provider transaction is not a distributed transaction across sources.
11. P3 secrets do not appear in generic source schema/read/query/cache/event/audit/export channels.
12. Source/entity/cache identity contains sufficient source/type/scope dimensions to prevent numeric-ID or cross-site collisions.
13. Module disable/version/schema changes invalidate or degrade dependent source descriptors and compiled consumers safely.
14. Lifecycle cleanup preserves data ownership; one source/module cannot directly delete another owner's private data through the generic contract.
15. Source registration is not provider certification. Provider/version support remains separately evidence-gated.
16. Query/Field/Custom Table/Relations/Privacy/Version/Lifecycle domain evidence remains independent and is referenced, not duplicated.

## Evidence scope

DSR-01…DSR-176 covers:
- registry identity/ownership/discovery;
- schema/field descriptors/capability truth;
- authorization/Policy/scope;
- get/list/query/projection/pagination/count;
- create/update/delete/bulk mutation;
- concurrency/version tokens/transactions/idempotency;
- Posts/CPTs, terms, users, comments, media and settings/options providers;
- Custom Tables and module-owned entities;
- remote/ecosystem/extension providers;
- stable identity, Relations, import/export and lifecycle;
- caches/events/privacy/Audit/performance and Multisite isolation.

## Anti-duplication boundary

This ADR does not replace:
- QRY Query AST/compiler/provider evidence;
- FST Field Storage/value evidence;
- CTB Custom Table physical DDL/migration evidence;
- REL relation/cardinality/edge evidence;
- KPA registry/Policy/Ability/Event execution evidence;
- PDL privacy lifecycle evidence;
- VER version/migration/deprecation evidence;
- MLC module lifecycle/uninstall evidence;
- WC Safe HTTP/Connection/Event Inbox evidence;
- provider-specific certification.

DSR success never auto-promotes any of these domains.

## Current truth

- DSR fixtures documented: **176**.
- DSR fixtures executed: **0/176**.
- Data Source runtime/provider certifications: **0**.
- No Data Source registration, WordPress entity operation, database/custom-table query, remote HTTP/provider call, entity mutation, cache benchmark, import/export, Multisite operation or runtime fixture has been executed by accepting this ADR.

## Consequences

WPEssential now has a single evidence gate for the shared entity/data access contract rather than allowing individual modules or AI/UI consumers to infer capabilities from storage/provider details. Future adapters must advertise only semantics they can prove and must remain subject to the same Policy/scope/security boundaries.

Exact adapter implementations, supported provider/version combinations, transaction classes, performance thresholds and runtime certifications remain evidence-gated.

## Development-consent gate

**Accepted evidence only. No production code, Data Source/provider registration, entity read/write/delete, Query/database/custom-table operation, HTTP/provider call, cache/event mutation, benchmark or Multisite fixture is authorized until explicit owner consent under ADR-0014 and `/DEVELOPMENT-CONSENT.md`.**
