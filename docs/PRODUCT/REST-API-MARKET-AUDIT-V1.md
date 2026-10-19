# REST API — Market Audit V1

Status: `MARKET_AUDITED` (integrated in `config/product/options-bank-audits/rest-api-market-ecosystem.json`)

Surface: 22 — REST API  
Snapshot: 2026-10-19  
Issue: #1446

## 1. Scope and boundary

Reconciles all 46 REST API records. Surface 22 owns endpoint definitions, request/response schema and HTTP behavior. Query (S6), Data Source owners, Ability owners and Workflows (S17) own execution; Roles/Policy and Membership own authorization; Connections (S23) owns auth adapters/OAuth lifecycle; the shared rate service (S27) owns rate storage; Import/Export (S26) owns package movement.

## 2. Evidence

- **E1 JetEngine (Crocoblock)** — https://crocoblock.com/plugins/jetengine/, https://crocoblock.com/knowledge-base/jetengine/ — REST API endpoints for custom content/queries, REST API query type and listings consuming REST items.
- **E2 Custom API for WP (miniOrange)** — https://wordpress.org/plugins/custom-api-for-wp/ — no-code custom REST endpoints/routes, external API integration, API key/bearer/OAuth auth options and endpoints that perform SQL operations against the database.
- **S1 WPGraphQL** — https://www.wpgraphql.com/docs/security — field-level authorization, query depth/cost limiting and private data handling for an alternative API transport.
- **S2 WP REST Cache** — https://wordpress.org/plugins/wp-rest-cache/ — REST response caching with flush on admin edits.
- **S3 Standards** — RFC 9110, RFC 9457, RFC 6585, IETF Idempotency-Key draft, OpenAPI, WHATWG Fetch CORS.

## 3. Record reconciliation

| Record | Disposition |
|---|---|
| `rest-api.endpoint.identity` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.endpoint.route` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `rest-api.endpoint.methods` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `rest-api.endpoint.lifecycle` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.binding.source` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.binding.ability-mapping` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.input.route-params` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `rest-api.input.query-params` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `rest-api.input.headers` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.input.body-schema` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `rest-api.input.file-upload` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.auth.cookie-nonce` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `rest-api.auth.application-passwords` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `rest-api.auth.adapter` | KEEP DEFERRED — market API-key/OAuth/JWT exists; certified adapter only |
| `rest-api.authz.policy` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `rest-api.public.safeguards` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.output.response-schema` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.collection.pagination` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `rest-api.collection.filter-sort` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.output.sparse-fields` | MARKET_EVIDENCED_WITH_BOUNDS |
| `rest-api.output.embed` | MARKET_EVIDENCED_WITH_BOUNDS |
| `rest-api.crud.semantics` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.write.idempotency` | KEEP WPE_EXCEED — standards-evidenced, not common in WP endpoint builders |
| `rest-api.rate.limit` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.cors.policy` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.cache.response` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.response.status-errors` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.versioning.deprecation` | KEEP WPE_EXCEED — standards-evidenced, not common in WP endpoint builders |
| `rest-api.docs.export` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.test.console` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.logs.request` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.settings.defaults-caps` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.permissions.builder` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.events.diagnostics` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.performance.budgets` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.scope.multisite-portability` | MARKET_EVIDENCED / WPE_HARDENED |
| `rest-api.safety.raw-execution` | KEEP `REJECTED_UNSAFE` |
| `rest-api.safety.missing-permission-callback` | KEEP `REJECTED_UNSAFE` |
| `rest-api.safety.static-bearer-default` | KEEP `REJECTED_UNSAFE` |
| `rest-api.safety.sensitive-header-mapping` | KEEP `REJECTED_UNSAFE` |
| `rest-api.safety.unbounded-output` | KEEP `REJECTED_UNSAFE` |
| `rest-api.safety.cors-wildcard` | KEEP `REJECTED_UNSAFE` |
| `rest-api.safety.shared-personalized-cache` | KEEP `REJECTED_UNSAFE` |
| `rest-api.safety.public-write-toggle` | KEEP `REJECTED_UNSAFE` |
| `rest-api.safety.sensitive-logging` | KEEP `REJECTED_UNSAFE` |
| `rest-api.safety.test-impersonation` | KEEP `REJECTED_UNSAFE` |

Coverage: **46 / 46**; 77 Bank references; 3 extra dispositions; unresolved **0**; record delta after market audit **0**.

## 4. Rejected market patterns

- No-code SQL endpoint execution (E2) → `rest-api.safety.raw-execution`.
- Static API key/bearer token as default auth (E2) → `rest-api.safety.static-bearer-default`; certified adapters stay `DEFERRED` (`rest-api.auth.adapter`).
- GraphQL transport (S1) is an implementation pattern for budgets/field authorization, not a Surface 22 record.

No endpoint registration, route exposure, CORS/auth widening, runtime or product parity is authorized.
