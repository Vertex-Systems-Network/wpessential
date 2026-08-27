# WPEssential — Option-Level Module Specification Standard

Status: **Required Phase 0 contract**

The goal of this standard is to prevent vague feature planning. A module is not considered specified because a feature name exists. Every user-visible and developer-visible behavior must have an explicit contract before production implementation begins.

## 1. Required module header

Every module specification must define:

- module name and slug;
- Free/Pro/platform classification;
- purpose and non-goals;
- market baseline and differentiator;
- owned definitions/data;
- shared engines reused;
- third-party dependencies/adapters;
- minimum WordPress/PHP/runtime compatibility assumptions;
- module-specific capabilities;
- registered Abilities/events/hooks;
- asset bundles and enqueue conditions;
- applicable project/work lifecycle state;
- stable work/milestone IDs once execution planning begins.

## 2. Screen specification format

For every screen and sub-screen record:

### Identity
- screen name;
- admin route/menu location;
- required capability;
- whether accessible in multisite network admin;
- whether deep-linkable;
- list/create/edit/view/import/export/diagnostics intent.

### Header actions
Document every header button separately, including:
- label;
- icon if any;
- visible/hidden conditions;
- enabled/disabled conditions;
- confirmation requirements;
- result state;
- keyboard/focus behavior where relevant.

### Filters/search/view controls
Document:
- search scope;
- available filters;
- default values;
- multi-select behavior;
- URL persistence;
- pagination/page-size choices;
- sort keys/directions;
- saved views;
- reset behavior;
- no-results state.

### Table/list columns
For every column document:
- source;
- formatter;
- sortable/filterable/searchable status;
- responsive behavior;
- visibility customization;
- row-action relationships;
- expensive-data strategy.

### Row actions
Every row action must define:
- required capability;
- whether it mutates data;
- confirmation/reauth requirements;
- dependency checks;
- audit event;
- undo/rollback behavior where possible.

## 3. Field / option contract

Every input, field, toggle, picker or setting must specify:

- stable key/identifier;
- label;
- help text intent;
- input/control type;
- data type;
- storage representation;
- default value;
- required/optional;
- allowed values;
- min/max/length/pattern constraints;
- normalization rules;
- sanitization rules;
- validation errors;
- conditional visibility;
- conditional enabled/disabled behavior;
- permission to view;
- permission to edit;
- whether it is secret/sensitive/PII;
- whether it is exportable;
- whether it is revisioned;
- whether changing it has migration/runtime impact;
- whether changing it invalidates cache or rewrite rules;
- API/Ability exposure status.

A checkbox named only by its UI label is not a complete specification.

## 4. State model

Every module must define states for its definitions/records as appropriate, including:

- draft;
- active/published;
- disabled/paused;
- archived;
- error/degraded;
- deleted/trashed if supported;
- external/discovered read-only state where applicable.

Transitions must declare:
- source states;
- target state;
- actor permissions;
- guards;
- side effects;
- events emitted;
- retry/rollback semantics.

## 5. Cross-module contract

For every dependency or integration, specify:

- owning module;
- consuming module;
- stable reference type/UUID;
- required vs optional dependency;
- behavior when dependency is disabled;
- behavior when dependency is deleted;
- behavior when Pro entitlement expires;
- dependency graph representation;
- import/export order;
- circular-dependency prevention.

No module may duplicate another module's source-of-truth definition merely for convenience.

## 6. Authorization contract

Separate:

- menu visibility;
- screen access;
- object/definition access;
- field-level visibility;
- field-level mutation;
- execute/run capability;
- export capability;
- import capability;
- destructive capability;
- unsafe/developer capability.

Every REST endpoint, Ability, Ajax action and background action must re-check authorization server-side. UI hiding is not authorization.

## 7. Security contract

Each module must explicitly assess, where applicable:

- CSRF;
- stored/reflected/DOM XSS;
- SQL/command/code injection;
- SSRF;
- path traversal;
- unsafe deserialization;
- unrestricted file upload;
- privilege escalation;
- IDOR/object-level authorization;
- mass assignment;
- replay/idempotency;
- race conditions;
- rate limiting/abuse;
- secret/PII exposure;
- supply-chain/dependency risk;
- destructive data loss.

## 8. Negative requirements / MUST NOT contract

Every substantial module must define the important things it **must not** do, especially around authorization, data ownership, scope, retries, destructive behavior and privacy.

At minimum consider:
- cross-user/cross-site/cross-tenant access that must be impossible;
- fields/actions that must never mutate protected authority/identity state;
- data that must never be exposed in UI/API/export/log/cache;
- dependency failures that must not fail open;
- retries/duplicate requests that must not repeat committed side effects;
- scope selectors/IDs that must not become trusted authority;
- destructive actions that must not run without required preconditions/approval/recovery evidence;
- caches that must not preserve revoked privileged access;
- module disable/license expiry behavior that must not expose protected content.

Format important rules explicitly, for example:

`MUST NOT: A site administrator must not enumerate or mutate another site's protected settings.`

`MUST NOT: A generic Profile field must not mutate password, role/capability, session or Application Password internals.`

Each security/data-critical negative requirement must map to an acceptance/security test or evidence fixture where execution is applicable.

## 9. UX state contract

Every primary screen/action must have intentional:

- initial loading state;
- empty state;
- filtered-empty state;
- permission-denied state;
- validation-error state;
- recoverable request-error state;
- offline/network-failure state for remote operations;
- success confirmation;
- destructive confirmation;
- long-running progress state;
- background-processing state;
- partial-failure state;
- module-disabled state;
- Pro-not-installed state;
- Pro-expired/read-only state;
- incompatible-dependency state.

## 10. Performance contract

Specify budgets/limits where relevant:

- default page size;
- maximum page size;
- preview row limits;
- query timeout policy;
- batch size;
- upload/file limits;
- worker concurrency;
- polling intervals;
- cache strategy and invalidation;
- pagination strategy;
- lazy loading thresholds;
- N+1 avoidance expectations.

## 11. Observability contract

Define:

- audit event names;
- operational logs;
- warning/error codes;
- correlation/run IDs;
- health indicators;
- metrics worth recording;
- redaction rules;
- retention policy where sensitive data is involved.

## 12. Import/export/revisions

Every configuration definition must answer:

- stable ID strategy;
- schema version;
- dependency manifest;
- export representation;
- secret handling;
- conflict strategy;
- dry-run support;
- remapping behavior;
- revision granularity;
- restore/rollback behavior;
- forward/backward compatibility.

## 13. Change-impact / implementation boundary

Before implementation begins, identify as applicable:
- affected components;
- explicitly unaffected areas;
- expected files/modules/APIs;
- shared surfaces;
- dependencies/lockfiles;
- migrations/schema/configuration;
- compatibility impact;
- rollback/recovery class;
- critical-path class;
- parallelism class;
- FAST/FULL test expectations.

If implementation materially exceeds this expected boundary, stop and reassess rather than silently expanding scope.

## 14. Acceptance-test matrix

At minimum plan tests for:

- happy path;
- blank/default configuration;
- every validation boundary;
- permission denied;
- important negative/MUST-NOT rules;
- capability change mid-session;
- nonce/CSRF failure;
- malformed IDs/input;
- deleted dependency;
- disabled dependency;
- expired Pro entitlement;
- import of old/newer schema;
- concurrent update where relevant;
- duplicate/replay behavior where relevant;
- background job failure/retry where relevant;
- crash/reconciliation where relevant;
- high-volume dataset where relevant;
- malicious payloads;
- accessibility keyboard/focus behavior;
- responsive narrow viewport where UI is user-facing;
- cleanup/uninstall semantics.

## 15. Approval-readiness self-audit

Before a module/milestone moves to `AWAITING_DEVELOPMENT_APPROVAL`, review it as:
- Product Manager;
- End User;
- Administrator;
- Attacker;
- QA Engineer;
- Database Engineer;
- DevOps/Platform Engineer;
- Support Engineer;
- Future Maintainer.

Ask:
- what option is missing?
- what permission is ambiguous?
- what happens with no data or huge data?
- what happens twice or concurrently?
- what happens when a dependency fails?
- what happens if execution crashes midway?
- how is it recovered/audited/deleted?
- how does unauthorized access behave?
- can production support diagnose failure?

Update the spec before approval when a real gap is found.

## 16. Option inventory rule

`OPTION-INVENTORY.md` is the planning ledger. An option may be marked `resolved` only when the module specification or ADR defines its semantics, default, validation, permission and side effects.

Implementation work must not silently invent unresolved option behavior. If implementation reveals a missing option or state, update documentation first or in the same coherent change before relying on it.

## 17. Gap classification rule

A missing requirement discovered during audit/implementation is classified using `docs/PROJECT-STATE-AND-ADOPTION.md`:

- `CORRECTION`
- `COMPLETION`
- `HARDENING`
- `OPTIMIZATION`
- `NEW_PRODUCT_SCOPE`

`NEW_PRODUCT_SCOPE` requires explicit approval before implementation. Do not silently convert a useful idea into approved product behavior.