# Dynamic Listings Reference Application V1

## Scope

This evidence slice proves the bounded Gate E common path against real WordPress data using the promoted production shared runtime and module/service lifecycle. It does not certify full Dynamic Listings product parity, asynchronous interaction, deployment, release readiness, relation-backed aggregation, or Status Manager.

The exact reference path is:

`Neutral RenderingServiceRegistrar -> shared ComponentBlueprintRegistry / DynamicValueRouter / BlueprintRendererDispatcher -> admitted QueryModule -> admitted ListingsModule -> Published Listing Definition -> ListingDefinitionCompiler -> explicit render binding plan -> ListingPublicStateCodec -> ListingScopeGuard -> module.listings.server-renderer -> ListingQueryReader -> canonical QueryReadConsumer -> Query Policy -> WordPressPostsQueryExecutor -> shared DynamicValueRouter -> trusted owner resolver -> shared BlueprintRendererDispatcher -> trusted component Renderer -> server-first ListingRenderResult -> ListingNoJsNavigation`

## Real WordPress fixture

`tests/Integration/Modules/Listings/wordpress-listings-reference-application.php` installs an isolated WordPress fixture, creates an administrator, inserts two published posts and one draft post, and stores real post-meta values used by the Dynamic Value contract.

The Query portion is not stubbed. The fixture uses the repository Kernel with an explicit test activation policy that admits only the required Pro `query` and `listings` modules. Query is registered before Listings through the declared module dependency, and Listings obtains `QueryModule::SERVICE_READ_CONSUMER` through the shared Service Registry. Query validation, policy authorization, provider planning and `WP_Query` execution therefore remain owned by Query.

The rendering side now uses the production neutral `RenderingServiceRegistrar`. The reference obtains the exact shared `AssetRegistry`, `ComponentBlueprintRegistry`, `DynamicValueRouter` and `BlueprintRendererDispatcher` from the canonical Service Registry. The fixture registers only its trusted server-owned post-meta resolver and card Renderer delegate into those production dispatchers; it does not replace the shared registries or construct a private Listings fallback. `ListingsModule` validates the shared Blueprint dependency graph and publishes the composed `module.listings.server-renderer` service consumed by the test.

## Positive evidence

The reference requires all of the following:

- the explicit activation policy admits Query and Listings through the shared Kernel/module lifecycle;
- Query and Listings reach the shared `Booted` module state and Listings declares Query as its dependency;
- the production neutral rendering registrar supplies one shared Asset Registry, Component Blueprint Registry, Dynamic Value Router and Blueprint Renderer Dispatcher;
- only a Published canonical Listing definition compiles;
- the compiler produces the explicit query-field and dynamic-value binding plan against the shared Blueprint registry;
- public filter state is normalized and serialized deterministically;
- execution scope is derived from the server `ExecutionContext`;
- the Listing reads real WordPress rows only through the canonical Query consumer resolved by `ListingsModule`;
- the `publish` filter excludes the draft fixture row;
- Dynamic Value resolution reaches the trusted post-meta delegate only through the production shared Dynamic Value Router using the compiled row resource id;
- rendering reaches the trusted card delegate only through the production Blueprint Renderer Dispatcher and exact Blueprint revision;
- the shared Renderer receives bounded typed bindings and escapes public output;
- server-first HTML and asset handles are deterministic;
- no-JS navigation preserves the canonical namespaced public state.

## Fail-closed evidence

The same reference executable verifies:

- a public `site_id` selector is rejected before Query Policy, Dynamic Value or Renderer execution;
- Query Policy denial returns no Listing HTML or protected row count;
- an unresolved Dynamic Value fails the whole Listing before Renderer invocation;
- a shared Renderer failure returns no partial item/page HTML;
- a Listings registration attempt without the production shared rendering runtime is rejected and publishes neither a Query Reader nor a server Renderer fallback.

No SQL, provider exception text, hidden rows, credentials, source records outside the authorized result, cross-site selector, authored callback, private provider, private Dynamic Value route or private Renderer fallback is surfaced by the Listing result.

## Exact-head CI

`.github/workflows/listings-reference-application.yml` is the dedicated execution gate for this evidence. The pre-existing Platform Compatibility workflow discovers and syntax-checks `tests/Integration/**` files but does not execute arbitrary new integration scripts, so the dedicated runner keeps this evidence executable rather than lint-only.

The dedicated gate verifies the exact pull-request source head and runs the reference on:

- WordPress 6.9 and 7.1;
- PHP 8.2, 8.3, 8.4 and 8.5 using MySQL 8.4;
- MariaDB 10.11 on WordPress 6.9 and 7.1 with PHP 8.4.

Promotion requires this dedicated gate plus all other applicable exact-head repository gates to be green and review threads to be clean.

## Certification boundary

Passing this reference is bounded Gate E evidence only. It must not be represented as `RUNTIME_CERTIFIED`, `PRODUCT_PARITY_CERTIFIED`, deployment certification, release certification, or authorization to begin Status Manager until a final exact-main Gate E closure audit confirms the remaining exit conditions and the Supervisor synchronizes repository shared truth.
