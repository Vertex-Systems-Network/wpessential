# REST API — Provisional UX Contract V1

Surface: **22 / REST API**  
Planning issue: **#588**  
Supervisor wave: **#583**  
Exact-main claim anchor: `b95e2ab190d452dc13882370ae613397f48f38c0`

Lifecycle status: **PROVISIONAL PLANNING ONLY**. Surface 22 remains `UNSEEDED / 0` in the Master Options Bank. This document guides later review; it does not promote `UX_CONTRACT_COMPLETE` or authorize route registration.

## Product model

The UI keeps endpoint contract separate from execution ownership:

- **Endpoint Definition** owns method/path/schema/auth/policy/HTTP behavior;
- **Binding** references a canonical Query/Data Source/Ability/Workflow owner;
- **Runtime request** is authorized and validated server-side on every call;
- **Logs/docs/test console** are observability/developer tools, not alternate execution paths.

## Information architecture

1. Endpoints
2. Create/Edit Endpoint
3. Documentation / Schema Export
4. Test Console
5. Request Logs
6. Rate Limits
7. Auth Integrations shortcut
8. Settings
9. Diagnostics

## Progressive disclosure

### Essential

- endpoint name/key/status;
- namespace/version/route;
- HTTP method;
- operation class: Read / Write / Destructive;
- binding type/reference;
- authentication mode;
- explicit Policy mode;
- minimal request/response schema;
- Validate / Preview Docs / Save.

Public and destructive operations are visually high-impact. “Public” never means “no permission callback”.

### Advanced

- path/query/header parameter schemas;
- body schema and strict/known-property policy;
- pagination/filter/sort mapping;
- response field allowlist;
- sparse-field/embed policy;
- rate-limit profile;
- cache profile;
- CORS exact-origin configuration;
- idempotency for writes;
- version/deprecation metadata;
- dependency/usage summary.

### Expert

- registered auth/Policy/provider adapters;
- optimistic concurrency/precondition behavior;
- trusted-proxy/rate dimensions;
- cache key/invalidation dimensions;
- response/error mapping;
- logging/redaction profile;
- execution/result byte/time budgets;
- async Job handoff policy;
- portability/environment diagnostics.

No expert control accepts raw SQL, PHP, filesystem paths, arbitrary callback/class execution or secret values.

### System / Diagnostics

Read-only diagnostics show:

- endpoint revision and compiled route;
- route collision/deprecation state;
- bound Query/Ability/provider health;
- effective auth/Policy summary;
- current site/network scope;
- rate/cache/CORS effective state;
- schema validation status;
- slow/error/health summary;
- dependency compatibility;
- documentation schema revision.

## Endpoints list

Columns:

- Name / Key / Status;
- Method;
- Namespace/version;
- Route;
- Binding;
- Auth mode;
- Policy summary;
- Rate profile;
- Cache state;
- Last request;
- Error/health summary;
- Updated;
- Actions.

Filters include lifecycle, method, namespace/version, auth, public/authenticated, read/write/destructive, errors and owner/module.

## Route and method editor

Validate grammar, reserved/collision state and duplicate method+route combinations before save. Published route/version changes trigger breaking-change/deprecation guidance.

GET is explicitly blocked from state-changing business actions.

## Binding UX

Allowed binding selectors:

- Query Builder read;
- Data Source get/list;
- registered CRUD/business Ability;
- Workflow trigger/run;
- certified SDK provider.

The UI displays the bound input/output schema and owner. Endpoint Policy cannot weaken the bound Ability's own authorization.

## Request schema UX

Path/query/header/body controls are typed and declarative:

- type;
- required/default;
- enum/range/length/pattern;
- nested depth/items;
- sensitivity;
- mapping.

Authorization/Cookie/internal proxy headers are never general mappable inputs. Write-body mapping is field-allowlisted rather than mass assignment.

## Authentication and Policy

Authentication modes clearly distinguish:

- same-site cookie + REST nonce;
- Application Passwords;
- explicit public/anonymous;
- registered future adapter.

Policy editor always requires an explicit mode and shows effective order: authentication/context → site/network → capability → resource Policy → Membership/resource protections → validation/business guard.

Public write requires dedicated high-risk confirmation/capability and abuse controls.

## Response schema

Only explicitly mapped fields appear. Every field can carry type/nullability/formatter/sensitivity/conditional inclusion.

Do not offer “return full WP object/meta/user” as a default shortcut.

## Pagination/filter/sort/embed

- no unbounded page size;
- allowlisted filters/operators/sort keys;
- cursor only where stable source supports it;
- sparse fields only within already-allowed response schema;
- embeds have max depth/rows and related-object Policy checks.

The editor shows expected Query cost/dependency warnings when available.

## Rate / CORS / cache

Rate profile exposes bounded requests/window/burst/dimensions and Retry-After behavior.

CORS uses exact validated origins. Wildcard + credentials and automatic arbitrary Origin reflection are rejected in the UI.

Cache controls distinguish public vs private/user-specific keys and warn when Policy-dependent responses could leak across principals.

## Test console

Admin-only test console exposes:

- route/method;
- typed inputs;
- current authenticated context or explicit anonymous mode;
- request preview;
- status/headers/body/timing;
- safe Policy trace summary.

It does not provide arbitrary higher-privilege impersonation. Sensitive headers/tokens remain masked.

## Logs

Default log view stores/displays metadata only: request ID, endpoint/revision, safe principal summary, method, status, duration, bytes, rate outcome, error category.

Body/header payload logging is off by default, bounded/redacted and never stores Authorization, cookies, Application Passwords or secrets.

## Docs / OpenAPI-like export

Generated documentation comes from the same endpoint schema:

- method/path;
- auth/policy;
- parameters/body;
- response/error schema;
- examples;
- limits;
- deprecation.

Examples use synthetic/redacted values. Docs never become a second mutable contract.

## Accessibility

- keyboard-operable schema editor and list/test console;
- semantic fieldsets/tables/statuses;
- linked validation errors;
- focus after add/remove/save/test actions;
- no color-only read/write/destructive/risk state;
- responsive parameter/schema tables;
- accessible code/source views with labels;
- axe coverage for Essential/Advanced/Expert/System and failure states once implemented.

## Multisite

Future normalized contract must explicitly define:

- site-local vs network endpoint definitions;
- route collision scope;
- network/site principal context;
- Application Password/site membership behavior;
- logs/rate/cache isolation;
- endpoint portability across sites;
- network admin permissions.

No network access is implied by presentation or Super Admin status alone.

## Portability

Export/import includes schema and canonical dependency references, not secrets. Import preflight reports missing Query/Ability/Auth/Policy providers, route collisions and version conflicts. Generic package orchestration remains Surface 26.

## Lifecycle decision

This is provisional interaction guidance only. Because exact-main Master Options Bank status remains `UNSEEDED / 0`, it must be re-reviewed after Bank review and schema-valid Atomic Option Contracts. No endpoint/runtime/product-parity promotion follows from this document.
