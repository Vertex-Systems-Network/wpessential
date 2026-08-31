# Relations Market Ecosystem Audit V1

Status: **MARKET_AUDITED candidate**  
Surface: **4 — Relations**  
Snapshot: **2026-09-01**

## Objective

Certify the current market/provider coverage of the Relations Options Bank without converting field-level relationship controls, provider storage quirks, or peer-owned UI semantics into duplicate WPE relation features.

Relations owns the persistent edge graph: relation identity, endpoints, cardinality/direction, storage, pivot metadata, connection lifecycle, relation queries/APIs, permissions and integrity. Fields owns relationship selector/control configuration and typed field schema. Admin Columns owns column presentation; Relations exposes only the relation integration capability.

## Primary provider benchmark

The audit dispositions eight capability families for each primary provider:

1. `definition_cardinality`
2. `endpoint_direction`
3. `storage_pivot`
4. `editor_permissions`
5. `query_traversal`
6. `api_integration`
7. `lifecycle_integrity`
8. `portability_ecosystem`

Primary providers:

- JetEngine Relations
- Meta Box Relationships
- Advanced Custom Fields (ACF)
- Pods
- Toolset Post Relationships
- ACPT

Specialist provider:

- JetFormBuilder — front-end relation connect/disconnect/edit path.

Official provider documentation is recorded directly in `config/product/options-bank-audits/relations-market-ecosystem.json`.

## Market gaps added

The existing 142-record Relations Bank already covered the great majority of mature-provider behavior. The audit found two capabilities that were not represented atomically enough.

### 1. Relation-specific admin filter

`relations.marketaudit.admin_filter`

JetEngine exposes “Filter by related items” in Admin Filters, and Meta Box exposes an admin relationship filter. WPE therefore needs a relation-owned integration control for filtering an admin object list by a related item.

This does not make Relations the owner of general Admin Columns/filter presentation. It owns only the semantic relation predicate/integration.

### 2. REST read/public-access policy

`relations.marketaudit.rest_read_policy`

JetEngine relation GET endpoints can be public when no Access Capability is supplied. Meta Box relationship read endpoints are public by default and provide filters to change read/public authorization.

WPE therefore separates relation-level REST **read/public exposure** from the existing write permission. This avoids conflating safe public reads with mutation authorization.

## Explicit non-gaps / ownership decisions

The audit did not add count-only records.

- ACF bidirectional relationships store references in fields on both objects. That is a provider compatibility/storage pattern, not WPE canonical edge storage.
- ACF top-level target, non-chaining and reverse-cardinality limitations are provider behavior constraints, not new WPE controls.
- Pods relationship REST response/read-write formatting remains Fields-owned because it is field-level REST behavior.
- Pods link-table guidance maps to the existing typed pivot model.
- Toolset intermediary relationship fields map to existing pivot/storage/migration capabilities.
- ACPT inverse relational meta fields are treated as provider field-sync/storage compatibility.
- JetFormBuilder maps to existing front-end relation form operations; it does not create a separate relation engine.
- Admin column presentation remains Surface 8; Relations retains only `relations.admin_columns` compatibility/integration.

## Coverage result

- 6 primary providers
- 1 specialist provider
- 8 capability families
- 34 provider-family mappings
- 14 explicitly non-applicable provider-family cells
- 120 Relations Bank references
- 4 extra provider dispositions
- 0 unresolved items
- 2 genuine new current-market records

Candidate Relations total: **144 records**.

## Lifecycle result

After executable contracts pass, Surface 4 advances from `NATIVE_AUDITED` to `MARKET_AUDITED`.

This audit does **not** claim `BANK_REVIEWED`, runtime implementation, shipped provider parity, or production readiness. The next gate is the formal Relations whole-surface Bank Review.
