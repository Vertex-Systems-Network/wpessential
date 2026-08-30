# WPEssential Engineering Conventions, Request Security, Compiled Registration & Observability

Status: **CANONICAL / IMPLEMENTATION CONTRACT**  
Applies to: **all production source and future module development**  
Owner instruction: **2026-08-29**

## 1. Purpose

This contract fixes WPEssential-wide implementation conventions before business-module expansion. It is not optional style guidance. New code that violates these rules is an architecture regression and must fail the engineering guard or be explicitly superseded by a later owner-approved ADR.

## 2. Canonical PHP source and naming

- Root PHP namespace: `WPEssential`.
- Canonical PSR-4 source root: `frameworks/`.
- Composer maps `WPEssential\\` to `frameworks/`.
- The former `src/` runtime root is retired and must not coexist as a parallel production class tree.
- Global functions use `wpessential_*`.
- Plugin global constants use `WPE_*`.
- Canonical bootstrap constants currently include:
  - `WPE_VERSION`;
  - `WPE_AJAX_ACTION`;
  - `WPE_NONCE_ACTION`;
  - `WPE_DEBUG`.

A duplicate parallel namespace, source tree, kernel, or loader is prohibited.

## 3. Public custom hook contract

The owner-prescribed public hook spellings are intentional API contracts:

- filters: `wpesential/apply_*`;
- actions: `wpessential/hook_*`.

The asymmetric `wpesential` spelling in the filter prefix is **intentional** and must not be silently corrected by refactoring.

`HookNames` is the canonical constructor for WPE-owned custom hooks. New custom hook suffixes use stable lowercase letters, numbers and underscores. WordPress/core/plugin hooks that WPE merely consumes are not renamed.

## 4. Single AJAX gateway

WPEssential exposes one canonical WordPress AJAX action through `WPE_AJAX_ACTION` (`wpessential_dispatch`).

Every WPE AJAX request enters the same gateway and declares an allowlisted logical `type`. The dispatcher then resolves a registered typed route. It must never derive an arbitrary PHP class, method, filename, callable, SQL fragment, or executable target from request input.

Required order of concerns:

1. validate request `type`;
2. resolve allowlisted route;
3. enforce authentication unless guest access was explicitly declared;
4. enforce declared capability/Policy boundary;
5. verify operation- and route-scoped nonce when required;
6. validate payload shape;
7. invoke the typed handler;
8. emit a structured response.

Unknown or missing request types fail closed. Handler exceptions are not returned to clients as raw stack traces or secrets.

The gateway registers the WordPress `wp_ajax_*`/`wp_ajax_nopriv_*` hooks centrally; module code must not create scattered competing WPE AJAX entrypoints.

## 5. Central nonce service

`NonceManager` is the common nonce boundary. Canonical operation contexts are:

- `apply`;
- `create`;
- `update`;
- `reset`;
- `delete`.

Nonce action identity includes the global base action, operation and bounded scope. A nonce created for one operation/scope cannot be treated as valid for another.

Nonce verification is CSRF protection only. It does **not** replace authentication, capability checks, canonical Policy, resource ownership, site/network authority, or business invariants.

## 6. Compile-on-write registration architecture

Dynamic WordPress registration surfaces include at least:

- post types;
- taxonomies;
- metaboxes;
- settings pages.

The runtime request path must not load 10K/100K historical definition rows and repeatedly normalize/compile them before registering WordPress structures.

Canonical model:

`definition mutation -> validate/normalize -> compile next generation -> atomically publish active generation -> runtime reads active compiled manifest only`

The compiled manifest carries a monotonically increasing generation and canonical checksum. Disabled definitions disappear during compilation rather than being filtered from a giant historical set on every request.

### Production persistence requirement

`InMemoryCompiledRegistrationStore` is a **reference/test adapter only**. It is not a production persistence certification and must not be represented as a 100K-ready backend.

Before dynamic registration surfaces rely on this subsystem in production, WPE must implement and evidence a persistent store with:

- atomic generation publication;
- site/network isolation;
- last-known-good generation retention;
- corruption/checksum detection;
- write-time invalidation;
- rollback/recovery semantics;
- no partially published generation;
- bounded bootstrap/read cost independent of historical definition count;
- executable 10K/100K definition performance fixtures and query/memory budgets.

No paper benchmark is runtime certification.

## 7. Debug / Runtime Observatory foundation

WPE debug tooling is modeled as a bounded, redacted flow trace rather than arbitrary `var_dump`, raw SQL dumps, secret-bearing logs, or production stack leakage.

A trace can represent:

- correlation/request identity;
- participating classes/components as nodes;
- class/call/data movement as edges;
- ordered checkpoints;
- operation metadata;
- exact failure boundary: last successful checkpoint -> failed component/operation;
- future timing/query/memory measurements;
- future graph/chart/architecture visualization.

`FlowTrace` is the data model; a later Platform admin screen can render the graph without inventing a second tracing engine.

### Redaction and production safety

Trace metadata sanitization must redact at minimum passwords, secrets, tokens, authorization headers, cookies, API/private keys, card data, nonces and signed URLs. Objects/resources are represented safely rather than serialized blindly.

`WPE_DEBUG` is disabled by default. The normal runtime uses a null recorder. Debug capture must remain bounded by trace/event budgets and must not become a second business-truth store.

Audit history and debug traces have different semantics: Audit is accountable event evidence; debug traces are temporary diagnostic evidence. Neither grants authorization or becomes source business truth.

## 8. Machine enforcement

`tools/architecture/validate-engineering-contracts.php` enforces key invariants including:

- `frameworks/` canonical PSR-4 mapping;
- no legacy parallel `src/` runtime root;
- global function prefix;
- required WPE bootstrap constants;
- exact hook prefixes;
- WPE namespace on production class files;
- single AJAX registration boundary;
- required nonce/compiled-registration/observability sources;
- smoke suites no longer resolving `/src/`.

The engineering-contract smoke suite additionally exercises nonce operation isolation, AJAX fail-closed routing, compiled generations, disabled-definition removal, trace failure-boundary reporting and secret redaction.

## 9. Stop-the-line conditions

Stop the affected tranche if any of the following occurs:

- a second WPE AJAX front door is introduced without an accepted architecture change;
- request input can select arbitrary executable PHP targets;
- nonce is treated as authorization;
- a protected mutation bypasses capability/Policy;
- dynamic WordPress registrations require a full historical-definition scan on ordinary requests;
- a partial compiled generation can become active;
- debug traces leak credentials, nonce values or protected data;
- debug mode is unbounded/on by default in production;
- `src/` or another parallel production class tree reappears;
- the exact owner-prescribed custom hook prefixes drift.

## 10. Current implementation boundary

Implemented foundation:

- canonical `frameworks/` source root;
- global WPE bootstrap constants;
- hook-name contract;
- global helper contract;
- central nonce operation service;
- single typed AJAX gateway/dispatcher/registry;
- compile-on-write registration domain model + in-memory reference adapter;
- runtime compiled-manifest reader;
- redacted bounded flow-trace model/recorders;
- machine engineering guard + smoke coverage.

Still required before claiming complete production readiness for these areas:

- persistent atomic compiled-registration backend;
- actual CPT/taxonomy/metabox/settings modules wired to that persistent backend;
- 10K/100K executable performance evidence;
- Platform Runtime Observatory admin UI/graph, access Policy, retention/export controls and production-safe diagnostics profile;
- broader AJAX integration tests in a real WordPress runtime.

These remaining items stay inside milestone-gated implementation and must not be silently upgraded from planned/foundation status to certified runtime status.
