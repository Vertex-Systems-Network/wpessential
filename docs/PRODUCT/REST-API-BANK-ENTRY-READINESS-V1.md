# REST API — Bank Entry Readiness V1

Surface: **22 / REST API**  
Planning issue: **#588**  
Supervisor wave: **#583**  
Exact-main claim anchor: `b95e2ab190d452dc13882370ae613397f48f38c0`

Status: **BANK ENTRY READY FOR RESEARCH/SEEDING — NOT BANK_REVIEWED — NO OPTION CONTRACT PROMOTION**

This document is planning evidence only. It does not seed/review the Master Options Bank, create schema-valid Atomic Option Contracts, register REST routes, widen CORS/auth policy, expose data or authorize runtime implementation.

## Current machine truth

- Surface 22 `rest-api` is `UNSEEDED` with **0 normalized Master Options Bank records**.
- The atomic planning ledger marks Surface 22 `ATOMIC_INVENTORY_COMPLETE`; that is planning inventory, not `OPTION_CONTRACT_COMPLETE` or `UX_CONTRACT_COMPLETE`.
- The canonical ownership map assigns endpoint definitions to Surface 22 while execution remains bound to canonical Query/Data Source/Ability owners.
- Exact-main `frameworks/Modules` has no dedicated REST API Builder runtime module.

The next valid gate is Master Options Bank seeding + native/market review, not endpoint registration.

## Existing in-repo evidence

Primary sources:

- `docs/MODULES/REST-API-BUILDER-EXHAUSTIVE-SPEC.md`;
- `docs/MODULES/OPTION-INVENTORY.md`;
- `docs/PRODUCT/56-SURFACE-COMPETITOR-PARITY-MATRIX.md`;
- `docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md`.

The exhaustive spec already fixes the critical product boundary: REST API Builder creates typed WordPress REST endpoints over approved WPE/WordPress data/actions. It is not an unauthenticated SQL tunnel, arbitrary PHP callback executor, permission bypass or default OAuth server. Every endpoint requires explicit `permission_callback` semantics and shared Policy.

## Candidate Bank families

Normalize at least these families independently:

1. **Endpoint identity/lifecycle** — name/key/description/status/namespace/version/route/method.
2. **Binding** — Query read, Data Source get/list, CRUD Ability, Workflow trigger, registered Ability, certified SDK provider.
3. **Route/query/header inputs** — typed schema, validation, mapping, sensitivity and reserved-name handling.
4. **Request body** — bounded JSON/form/file schema, nested depth/item limits, strict unknown-property policy.
5. **Authentication mode** — same-site cookie+nonce, Application Passwords, explicit public, registered future adapter.
6. **Authorization/Policy** — public/authenticated/capability/resource/owner/Membership/registered Policy provider.
7. **Public safeguards** — method class, anti-abuse, rate, payload/result limits, field allowlist, enumeration warning.
8. **Response schema** — explicit output allowlist, typing, nullability, formatting, sensitivity/permission.
9. **Pagination/filter/sort** — bounded page/cursor, max size, safe Query mapping, allowlisted sort keys.
10. **Sparse fields/embed** — only from already-allowed schema, bounded relation depth/count, related-object Policy.
11. **Write/CRUD semantics** — create/update/delete behavior, optimistic version/preconditions, trash/permanent policy.
12. **Idempotency** — scoped key, retention, request-hash conflict, stored prior result/ref.
13. **Rate limiting** — profile, dimensions, burst/window, retry headers, trusted-proxy rules.
14. **CORS** — explicit exact origins/methods/headers/credentials/max age; no unsafe reflection.
15. **Response caching** — public/private context, TTL/key dimensions/invalidation/ETag candidates.
16. **HTTP status/error envelope** — stable safe errors, field errors, correlation IDs, retry hints.
17. **Versioning/deprecation** — breaking-change warning, replacement, migration window.
18. **Docs/OpenAPI-like export** — schema-driven examples with synthetic/redacted data.
19. **Test console** — bounded current-context/anonymous testing, no privilege impersonation by default.
20. **Request logs/retention** — metadata-first, redacted, no auth/cookies/raw secrets, sampling for high volume.
21. **Disable/archive/delete impact** — route behavior, dependency/log retention.
22. **Permissions/Abilities** — builder management separated from runtime endpoint Policy.
23. **Performance/reliability** — request/body/depth/page/result/time budgets, async long-running work, no N+1.
24. **Multisite/portability** — site/network route scope, dependency refs, conflict/deprecation mapping.

## Native WordPress audit required before Bank review

A future native audit must explicitly classify current WordPress REST behavior:

- route registration, namespace/version/path grammar and collision rules;
- `permission_callback` requirements and current-user context;
- cookie + REST nonce semantics;
- Application Passwords over HTTPS;
- REST request schema validation/sanitization callbacks;
- standard collection pagination/header conventions;
- `_fields`, `_embed`, OPTIONS/schema behavior where applicable;
- media upload limits/security;
- multisite/site/network context;
- WP error/status conventions;
- CORS/origin behavior and interaction with hosting/security plugins;
- response caching caveats and personalized-data isolation.

Native REST primitives must remain execution transport, not authorization by themselves.

## Market audit required before Bank review

Future market evidence should compare API-builder/automation products for:

- typed endpoint schema breadth;
- Query/action binding;
- auth/permission models;
- public endpoint controls;
- pagination/filter/sort;
- file uploads;
- idempotency/rate limiting;
- CORS/cache;
- logs/test console;
- docs/OpenAPI export;
- versioning/deprecation;
- import/export and dependency handling.

Features that enable raw SQL/PHP, weak bearer tokens, blanket public routes, arbitrary origin reflection, mass assignment or unbounded responses should be rejected or tightly bounded rather than copied.

## Canonical ownership decisions

| Concern | Canonical owner |
|---|---|
| Endpoint definition, public contract, request/response schema, HTTP behavior | **Surface 22 REST API** |
| Query execution/filter semantics | **Surface 6 Query** |
| Entity Data Source mutation | owning Data Source surface |
| Business action execution | registered **Ability** owner |
| Workflow orchestration | **Surface 17 Forms & Workflows** |
| Membership/role/resource authorization | canonical Membership/Roles/Policy owners |
| Connection/OAuth provider lifecycle | **Surface 23 Connections/Webhooks** or certified auth adapter |
| Shared rate/request hardening | **Surface 27 Protector/shared rate service**, while endpoint policy chooses profile |
| Generic package import/export | **Surface 26 Import/Export** |

## Rejected-unsafe / bounded candidates

Reject or tightly bound:

- raw SQL/PHP/filesystem execution;
- missing/implicit permission callbacks;
- arbitrary Authorization/Cookie/internal proxy header mapping;
- blind mass assignment;
- arbitrary raw object/meta responses;
- client-controlled SQL identifiers/orderby;
- unbounded `per_page=all`;
- recursive unrestricted embeds;
- `*` CORS with credentials or arbitrary Origin mirroring;
- shared caching of permission-dependent personalized responses;
- raw request/response body logging by default;
- auth credentials, cookies, app passwords or secrets in logs/examples;
- privilege impersonation in test console without dedicated re-auth/capability;
- public write enabled by a single casual toggle.

## Readiness decision

Surface 22 has sufficient in-repo semantics to begin disciplined Master Options Bank seeding/native/market review, but exact-main `UNSEEDED / 0` blocks option-contract, UX and runtime lifecycle promotion.

Next gate:

1. normalize candidate Bank records;
2. complete current WordPress native audit;
3. complete evidence-backed market audit;
4. resolve ownership, duplicates, unsafe/deferred entries;
5. promote Bank only with zero unresolved review items;
6. derive schema-valid Atomic Option Contracts;
7. re-review provisional UX before runtime authorization.
