# Dynamic Listings Reference Application V1

## Scope

This evidence slice proves the bounded Gate E common path against real WordPress data. It does not certify full Dynamic Listings product parity, asynchronous interaction, deployment, release readiness, relation-backed aggregation, or Status Manager.

The exact reference path is:

`Published Listing Definition -> ListingDefinitionCompiler -> explicit render binding plan -> ListingPublicStateCodec -> ListingScopeGuard -> ListingQueryReader -> canonical QueryReadConsumer -> Query Policy -> WordPressPostsQueryExecutor -> DynamicValueResolverInterface -> RendererInterface -> server-first ListingRenderResult -> ListingNoJsNavigation`

## Real WordPress fixture

`tests/Integration/Modules/Listings/wordpress-listings-reference-application.php` installs an isolated WordPress fixture, creates an administrator, inserts two published posts and one draft post, and stores real post-meta values used by the Dynamic Value contract.

The Query portion is not stubbed. The test registers the canonical Query module with the shared Data Source Registry and Policy Engine, then consumes `QueryModule::SERVICE_READ_CONSUMER`. Query validation, policy authorization, provider planning and `WP_Query` execution therefore remain owned by Query.

The Component Blueprint registry, Dynamic Value resolver and Renderer are test-only implementations of their already-promoted shared interfaces because the platform currently exposes those contracts/descriptors but no canonical concrete runtime implementations. They do not create new production ownership.

## Positive evidence

The reference requires all of the following:

- only a Published canonical Listing definition compiles;
- the compiler produces the explicit query-field and dynamic-value binding plan;
- public filter state is normalized and serialized deterministically;
- execution scope is derived from the server `ExecutionContext`;
- the Listing reads real WordPress rows only through the canonical Query consumer;
- the `publish` filter excludes the draft fixture row;
- Dynamic Value resolution reads the real WordPress post-meta value using the compiled row resource id;
- the shared Renderer receives bounded typed bindings and escapes public output;
- server-first HTML and asset handles are deterministic;
- no-JS navigation preserves the canonical namespaced public state.

## Fail-closed evidence

The same reference executable verifies:

- a public `site_id` selector is rejected before Query execution;
- Query Policy denial returns no Listing HTML or protected row count;
- an unresolved Dynamic Value fails the whole Listing before Renderer invocation;
- a shared Renderer failure returns no partial item/page HTML.

No SQL, provider exception text, hidden rows, credentials, source records outside the authorized result, or cross-site selector is surfaced by the Listing result.

## Exact-head CI

`.github/workflows/listings-reference-application.yml` is the dedicated execution gate for this evidence. The pre-existing Platform Compatibility workflow discovered and syntax-checked new `tests/Integration/**` files but did not execute arbitrary new integration scripts, so a dedicated runner is required to make the evidence real rather than lint-only.

The dedicated gate verifies the exact pull-request source head and runs the reference on:

- WordPress 6.9 and 7.1;
- PHP 8.2, 8.3, 8.4 and 8.5 using MySQL 8.4;
- MariaDB 10.11 on WordPress 6.9 and 7.1 with PHP 8.4.

Promotion requires this dedicated gate plus all other applicable exact-head repository gates to be green and review threads to be clean.

## Certification boundary

Passing this reference is bounded Gate E evidence only. It must not be represented as `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment certification, release certification, or authorization to begin Status Manager unless the remaining Gate E closure audit separately declares its prerequisites complete.
