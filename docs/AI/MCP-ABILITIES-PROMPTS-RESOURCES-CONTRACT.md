# WPEssential — MCP / Abilities / Prompts / Resources Contract

Status: **Phase 0 planning / no MCP runtime authorization**  
Date: 2026-08-29

## 1. Decision

WPEssential will **not** implement a competing Model Context Protocol transport/runtime by default.

Preferred architecture:
- register WPE operations in the native WordPress **Abilities API**;
- use the official WordPress **MCP Adapter** as an optional bridge;
- expose only explicitly approved WPE Abilities/resources/prompts;
- keep ordinary WPE UI/automation functional when MCP is absent.

MCP is an external-agent integration surface, not the canonical business runtime and not an authorization system.

## 2. Current WordPress research basis

Planning is aligned with official WordPress materials current through August 2026:

- WordPress 6.9+ includes the Abilities API.
- WordPress 7.0+ includes the provider-agnostic AI Client and Connectors integration.
- WordPress 7.1 adds a unified `meta.public` ability exposure flag, schema preparation for client compatibility, ability filtering and execution lifecycle filters.
- The official WordPress MCP Adapter maps Abilities into MCP tools, resources and prompts, supports HTTP and STDIO transports, layered discovery, permission controls and custom servers.
- MCP Adapter exposure is opt-in; transport authorization and per-Ability permission checks are separate layers.

Reference sources:
- https://developer.wordpress.org/apis/abilities-api/
- https://make.wordpress.org/core/2026/03/24/introducing-the-ai-client-in-wordpress-7-0/
- https://make.wordpress.org/core/2026/08/04/a-unified-public-exposure-flag-for-abilities-in-wordpress-7-1/
- https://developer.wordpress.org/news/2026/02/from-abilities-to-ai-agents-introducing-the-wordpress-mcp-adapter/
- https://github.com/WordPress/mcp-adapter

Exact MCP Adapter/version compatibility remains evidence-gated and must not be inferred from paper documentation alone.

## 3. WPE Ability naming

Suggested namespaces:
- `wpe-core/*`;
- `wpe-definitions/*`;
- `wpe-data/*`;
- `wpe-solutions/*`;
- `wpe-ai/*`;
- module-specific `wpe-<module>/*` only where ownership is clearer.

Names are stable contracts, not UI labels.

Ability categories must be registered before abilities according to WordPress lifecycle requirements.

## 4. Ability classes

Each WPE Ability declares:
- name/label/description/category;
- JSON input schema;
- JSON output schema;
- permission callback;
- resource Policy callback/owner where applicable;
- read/write/destructive annotation;
- idempotency expectations;
- dry-run support;
- risk class;
- audit metadata;
- execution callback;
- external exposure policy;
- MCP type eligibility.

Suggested classes:
- `READ`;
- `DRAFT`;
- `VALIDATE`;
- `SIMULATE`;
- `APPLY_REVERSIBLE`;
- `OPERATIONAL_MUTATION`;
- `HIGH_IMPACT`;
- `DESTRUCTIVE`.

## 5. Recommended WPE AI/solution Abilities

### Discovery/read
- `wpe-core/read-module-catalog`;
- `wpe-core/read-capability-map`;
- `wpe-definitions/read-definition-schema`;
- `wpe-definitions/read-definition`;
- `wpe-definitions/read-dependency-graph`;
- `wpe-solutions/read-blueprint-schema`;
- `wpe-solutions/read-installed-solution`;
- `wpe-ai/read-prompt-template`;
- `wpe-ai/read-ai-health`.

### Requirement/plan
- `wpe-ai/compile-requirements`;
- `wpe-ai/resolve-capabilities`;
- `wpe-solutions/generate-plan`;
- `wpe-solutions/validate-plan`;
- `wpe-solutions/simulate-plan`;
- `wpe-solutions/save-plan-draft`.

### Apply
- narrow module-owned create/update/publish abilities;
- `wpe-solutions/apply-approved-plan` only as orchestrator over registered typed Abilities;
- never an arbitrary command executor.

### Gap/request
- `wpe-ai/create-capability-gap-draft`;
- `wpe-ai/submit-capability-request` only after preview/consent;
- `wpe-ai/read-capability-request-status`.

## 6. MCP mapping

### Tools
Use for callable operations:
- compile requirement;
- validate Plan IR;
- create draft definition;
- simulate;
- apply approved bounded change;
- create support/capability request.

### Resources
Use for passive context where supported:
- module catalog;
- Solution Blueprint schema;
- public/sanitized docs;
- installed-solution manifest;
- schema-only Data Source descriptions;
- sanitized system-health snapshot;
- Prompt template catalog.

Resource URIs must be stable, scope-aware and permission-checked.

### Prompts
Expose selected WPE workflow templates, for example:
- `Build a WPE Solution from Requirements`;
- `Modify a WPE Definition Safely`;
- `Explain This WPE Configuration`;
- `Audit a WPE Module Definition`;
- `Troubleshoot a WPE Configuration`;
- `Find Missing WPE Capabilities`;
- `Draft a Capability Request`.

MCP prompts guide the external model; they do not grant tool authority.

## 7. Custom WPE MCP server profiles

Prefer explicit custom server profiles over exposing the entire site Ability universe through one unrestricted surface.

### `wpe-builder`
Purpose: Solution/module design.

Allow:
- read schema/catalog;
- compile requirements;
- draft definitions;
- validate/simulate;
- capability-gap draft.

Default: no high-impact apply.

### `wpe-operator`
Purpose: authorized operational automation.

Allow only explicitly certified operational Abilities and policies.

Requires stronger capability/Policy profile, rate limits, audit and re-auth for high-impact classes.

### `wpe-developer`
Purpose: developer inspection/testing on permitted environments.

Allow:
- schemas;
- diagnostics;
- compatibility information;
- test/simulation resources;
- no arbitrary PHP/SQL shell.

### `wpe-support-readonly`
Purpose: scoped diagnostics/support context.

Read-only and minimized; diagnostics upload remains separately consented.

## 8. Exposure semantics

WordPress 7.1 `meta.public=true` may influence multiple external channels, including REST defaults.

Therefore WPE must choose exposure deliberately:
- internal only: no public flag/channel exposure;
- REST-only: explicit REST exposure when needed;
- MCP-only: explicit MCP public override where supported;
- generally external: `public=true` only when intentionally appropriate;
- explicit per-channel opt-out must be honored.

Do not mass-set `public=true` on every WPE Ability.

Exposure is discoverability, **not authorization**.

## 9. Two-layer MCP authorization

At minimum:
1. **Transport/server permission** — who can access the MCP server.
2. **Ability permission + target resource Policy** — whether that authenticated principal can execute the specific operation on the target.

Additional WPE checks may include:
- module entitlement;
- site/network scope;
- AI/MCP feature permission;
- re-auth requirement;
- approval token/plan fingerprint;
- rate/cost budget;
- environment restrictions.

A server-wide allow does not imply any individual Ability allow.

## 10. Authentication

Initial profiles should rely on WordPress-supported authenticated identities.

Examples subject to future certification:
- same-site authenticated WordPress requests;
- Application Passwords for external HTTP MCP clients;
- WP-CLI/STDIO with explicitly selected WordPress user in controlled developer environments.

Do not invent anonymous bearer access to privileged WPE tools.

Custom transport authentication is a future adapter concern only when normal WordPress/MCP Adapter mechanisms cannot satisfy a documented requirement.

## 11. MCP session and cache safety

HTTP MCP responses are user/session specific.

Requirements:
- session identifiers scoped to authenticated transport;
- reject missing/invalid session where protocol requires;
- terminate sessions cleanly;
- no shared full-page/object cache of per-user MCP responses;
- explicit private/no-store strategy where required by certified adapter/version/environment;
- never cache tool listings/results in a way that crosses user/site/tenant permission context.

Known upstream transport/cache risks must be included in compatibility evidence before claiming production support.

## 12. Tool discovery safety

External agents receive only abilities that are:
- explicitly eligible;
- version-compatible;
- module enabled;
- actor-visible;
- scope-appropriate;
- not revoked/paused;
- allowed by selected MCP server profile.

Layered discovery metadata must not expose sensitive hidden names/configuration beyond permission.

## 13. Prompt injection / untrusted content

Content read from posts, forms, imports, webhooks, chat, documents or remote APIs is **data**, not trusted instruction.

WPE must:
- distinguish system/developer instructions from retrieved user/content data;
- label provenance;
- avoid placing untrusted content in privileged instruction channels;
- restrict tool/Ability allowlist per task;
- validate all tool inputs independently;
- require Policy at execution time;
- prevent retrieved text from modifying MCP exposure or approvals;
- redact secrets and credentials.

## 14. Tool chaining

An AI agent can plan multiple calls, but WPE maintains bounded orchestration:
- max tool calls;
- max nested orchestration depth;
- cycle detection;
- idempotency keys where appropriate;
- time/cost limits;
- high-impact approval barrier;
- partial-failure record;
- no self-expanding permission/tool allowlist.

## 15. Plan fingerprint / approval binding

For AI-generated plans that become executable:
- canonical Plan IR serialized/fingerprinted;
- approval binds actor + scope + exact plan/revision + expiry;
- changes after approval invalidate or require re-approval according to risk;
- external MCP client cannot replace the approved payload while reusing approval.

## 16. MCP observability

Record:
- server/profile;
- transport class;
- authenticated actor;
- session/correlation ID;
- discovered/called Ability;
- target scope;
- permission result;
- input hash/minimized diagnostic metadata;
- output/error class;
- duration;
- approval correlation;
- provider/model correlation only where AI task is involved.

Do not log secrets/full sensitive payloads indiscriminately.

## 17. Multisite

MCP server/profile declares scope:
- site;
- network administration;
- explicit bounded multi-site coordinator.

A site-scoped MCP tool cannot gain cross-site authority by accepting arbitrary `site_id` input.

Network actions require Network/Super Admin-aware Policy and child-site audit/fan-out semantics.

## 18. Module lifecycle

If a module is disabled/unavailable/read-only:
- its mutable MCP tools disappear or fail closed;
- safe read-only schema/resource may remain only if explicitly supported;
- historical audit remains;
- Pro expiry must not remove security/access enforcement;
- stale MCP clients get a typed unavailable/degraded error rather than invoking missing business logic.

## 19. Versioning

Every externally exposed Ability/prompt/resource is a versioned contract.

Breaking change handling:
- new Ability/version or accepted schema migration;
- deprecation metadata;
- client-readable replacement info;
- no silent meaning change under same stable identifier;
- exact MCP Adapter/WordPress compatibility matrix remains evidence-gated.

## 20. MUST NOT

- ship a second homegrown MCP transport merely because MCP exists;
- expose every Ability by default;
- equate MCP server access with business authorization;
- allow an agent to choose its own permissions;
- expose Vault secrets as resources/tools;
- create generic `run-php`, `run-sql`, shell or arbitrary hook tools;
- let untrusted retrieved content alter tool allowlists/approval;
- make MCP required for ordinary WPE functionality;
- claim adapter/server production compatibility before executable evidence.

## 21. Development gate

No MCP Adapter package install, MCP server registration, Application Password creation, HTTP/STDIO session, Ability exposure or tool invocation occurred or is authorized by this document.