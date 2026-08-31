# WPEssential — AI Prompt & Requirement Compiler Architecture

Status: **Phase 0 planning / no development authorization**  
Date: 2026-08-29

## 1. Purpose

Every WPEssential module and every Solution Blueprint surface must expose a consistent natural-language entry point so a permitted user can describe what they want in ordinary language and have WPEssential convert that requirement into canonical, typed, reviewable WPE definitions.

The AI layer is an interface to existing deterministic WPE engines. It is not a second runtime, an authorization bypass, or an arbitrary-code generator.

Primary outcome:

`User requirement → structured Requirement IR → capability resolution → proposed definitions/change plan → validation/simulation → approval → typed Ability execution → verification/audit`

If the requirement cannot be represented by currently available modules/foundations/adapters, WPE must say so explicitly and offer a **Request New Option / Request New System** workflow rather than fabricating capability.

## 2. WordPress-native substrate

WPE should prefer the current WordPress AI building blocks rather than inventing a parallel provider/runtime stack:

- **WordPress Abilities API** — available in WordPress 6.9+, typed/discoverable actions with input/output schemas and `permission_callback`.
- **WordPress AI Client** — native in WordPress 7.0+, provider-agnostic server-side AI API through `wp_ai_client_prompt()`.
- **Connectors API / Settings → Connectors** — provider credential ownership; WPE features should not create separate copies of OpenAI/Anthropic/Google/etc. API keys.
- **WordPress MCP Adapter** — optional external-agent bridge converting selected Abilities into MCP tools/resources/prompts.
- **WordPress 7.1 Abilities improvements** — portable client schema preparation, ability filtering, execution lifecycle filters, and unified `meta.public` exposure semantics.

WPE must capability-detect these facilities. AI unavailability must degrade to ordinary deterministic WPE builders; the site must not become unusable because no model/provider is connected.

## 3. Relationship with F12 AI Gateway

ADR-0177 accepted **F12 — AI Gateway, Knowledge & Copilot Studio**.

This document does not create a competing AI module. F12 remains the owner of:
- AI provider/model policy;
- AI tasks;
- prompt/instruction templates;
- knowledge sources;
- retrieval profiles;
- copilots;
- evaluations;
- usage/budgets;
- AI diagnostics and provider/model health.

The **Prompt & Requirement Compiler** is a shared service and UX contract consumed by all original modules and all universal foundations.

## 4. Core objects

### 4.1 Prompt Template

Fields:
- stable UUID/key;
- label;
- module/surface owner;
- task type;
- version/revision;
- system/developer instructions owned by WPE;
- user-input schema;
- expected structured output schema;
- allowed context providers;
- allowed Abilities/tools;
- write class: read-only / draft-only / apply-capable / destructive-never-direct;
- model capability requirements;
- budget profile;
- safety policy profile;
- locale;
- active/paused/archived;
- source/provenance;
- evaluation profile.

### 4.2 Prompt Session

Fields:
- session ID;
- actor/user ID;
- site/network scope;
- module/surface;
- selected definition/resource;
- original user request;
- normalized requirements;
- context manifest;
- model/provider metadata when available;
- generated Requirement IR versions;
- proposed Plan IR versions;
- validation results;
- approvals;
- executed Ability correlation IDs;
- final result status;
- error/degraded state;
- retention/privacy class.

A Prompt Session is not the canonical business definition. Canonical WPE definitions remain in their owning modules.

### 4.3 Requirement IR

Minimum schema:
- intent;
- target system/module;
- desired outcome;
- actors;
- roles/permissions;
- data objects;
- fields/types/validation;
- relations;
- states/transitions;
- screens/placements;
- forms/actions;
- queries/views;
- workflows/triggers/conditions/actions;
- notifications/messages;
- integrations/adapters;
- reports/analytics;
- privacy/retention;
- scale/performance assumptions;
- Multisite scope;
- external authorities;
- constraints/MUST NOT;
- explicit user-provided facts;
- inferred assumptions;
- unresolved questions.

Every inferred field must be marked as inferred rather than silently presented as user fact.

### 4.4 Capability Resolution Result

For every requirement:
- `SUPPORTED_DIRECTLY`;
- `SUPPORTED_BY_COMPOSITION`;
- `SUPPORTED_WITH_ADAPTER`;
- `SUPPORTED_WITH_EXTERNAL_AUTHORITY`;
- `PARTIALLY_SUPPORTED`;
- `UNSUPPORTED_OPTION`;
- `UNSUPPORTED_MODULE_CAPABILITY`;
- `UNSUPPORTED_FOUNDATION`;
- `UNSUPPORTED_ADAPTER`;
- `UNSUPPORTED_SYSTEM_PATTERN`;
- `BLOCKED_BY_SECURITY_POLICY`;
- `BLOCKED_BY_COMPATIBILITY`;
- `UNKNOWN_REQUIRES_RESEARCH`.

The resolver records which module/foundation/adapter/Ability satisfies each requirement.

### 4.5 Plan IR

A typed, deterministic proposed-change graph containing:
- create/update/bind/archive operations;
- owning module for each operation;
- definition stable IDs;
- dependencies;
- install variables;
- role/capability/Policy changes;
- data/schema/migration impact;
- routes/placements/assets;
- jobs/index/build work;
- adapters/external authorities;
- risk class;
- rollback/recovery class;
- parallel-safe/serialized operations;
- approval requirements;
- dry-run fingerprint.

The AI may draft Plan IR. Deterministic WPE validators decide whether it is valid.

## 5. User interaction flow

### 5.1 Entry points

Every applicable module exposes:
- **Ask AI** button in module header;
- Prompt tab/panel in create/edit screens;
- contextual action on selected rows/definitions;
- optional global **Build with AI** entry under `WPEssential → AI` and `WPEssential → Solutions`.

### 5.2 Prompt modes

- Create;
- Modify;
- Explain;
- Troubleshoot;
- Optimize;
- Migrate/Map;
- Generate sample/test data plan;
- Generate workflow;
- Generate query/filter;
- Generate UI/listing/dashboard plan;
- Audit existing definition;
- Compare variants;
- Build complete Solution Blueprint;
- Request unsupported capability.

Not every module must enable every mode. Availability comes from its manifest/Ability registry.

### 5.3 Scope selector

Before sending a prompt, user can select:
- current definition only;
- selected related definitions;
- current module;
- installed Solution;
- site architecture summary;
- selected sample records after permission check;
- no live data / schema-only.

Default to the smallest useful context.

### 5.4 Draft-first result

Result UI shows:
- interpreted requirements;
- assumptions;
- capability coverage;
- unsupported/gap items;
- definitions to create/change;
- exact before/after diff where modifying;
- permissions/security impact;
- data/migration impact;
- dependency impact;
- external service requirements;
- estimated AI/runtime work where measurable;
- validation errors/warnings;
- simulation/preview action;
- Save as Draft;
- Apply approved safe changes;
- Request missing capability.

## 6. Prompt compilation pipeline

1. **Authenticate actor**.
2. **Resolve site/network/module context**.
3. **Check AI feature permission**.
4. **Resolve connected AI capability** through WordPress AI Client/Connectors.
5. **Assemble minimal context manifest**.
6. **Redact/prohibit secrets and disallowed sensitive fields**.
7. **Run requirement-extraction prompt** with structured JSON output schema.
8. **Validate Requirement IR deterministically**.
9. **Resolve requirements against Module Registry, Data Source Registry, Definition Registry, Ability Registry, adapters and Solution patterns**.
10. **Classify gaps**.
11. If complete enough, generate Plan IR.
12. Deterministically validate dependencies, types, policies, scope, lifecycle, compatibility and risk.
13. Show preview/diff/simulation.
14. Obtain required approval.
15. Execute typed Abilities only; never run generated PHP/SQL/JS.
16. Verify resulting definitions/health.
17. Audit prompt → IR → approval → execution correlations.
18. Offer follow-up explanation or request flow for unresolved gaps.

## 7. Structured output rule

AI free-form prose can explain, but any machine-consumed proposal must use a versioned JSON Schema.

Examples:
- `wpe.requirement-ir/v1`;
- `wpe.plan-ir/v1`;
- `wpe.gap-report/v1`;
- `wpe.change-summary/v1`.

Unknown schema versions fail closed for execution.

Provider-specific JSON-schema limitations must be handled by the WordPress AI Client/provider adapter; WPE validates canonical output again server-side.

## 8. Context providers

Registered context providers may include:
- module manifest;
- definition schemas;
- current definition revision;
- dependency graph;
- role/capability/Policy summary;
- Data Source schema only;
- selected records after row Policy;
- Query result samples with limits;
- site health/compatibility summary;
- installed Solution manifest;
- WPE docs/knowledge;
- user-provided attachments/text through accepted file rules;
- analytics summaries where F02 exists;
- Audit excerpts only when separately authorized.

Each provider declares:
- data classification;
- required capability/Policy;
- maximum size;
- whether values or schema only are allowed;
- whether data may leave the site through an external AI connector;
- redaction rules;
- caching/retention rules.

## 9. AI permission model

Separate capabilities/policies for:
- use AI read-only explanation;
- use AI with site data;
- use AI with PII;
- draft definitions;
- view generated diffs;
- apply non-destructive changes;
- request approval;
- approve high-impact AI-generated plans;
- configure prompts/templates;
- configure knowledge/retrieval;
- view AI usage/costs;
- export AI session logs;
- expose WPE abilities through MCP;
- manage MCP servers/profiles.

`AI can generate it` never means `AI may execute it`.

## 10. Approval/risk classes

Suggested planning classes:
- `AI-R0` read-only explain/summarize;
- `AI-R1` draft-only definition proposal;
- `AI-R2` reversible non-destructive definition write;
- `AI-R3` operational mutation with bounded reversible effect;
- `AI-R4` financial/access/security/schema/destructive/high-impact.

R4 is never silently auto-approved. Existing module-specific Policy/approval rules remain authoritative.

## 11. Model/provider selection

Default:
- provider/model agnostic;
- WPE states required capabilities (text, structured output, vision, embeddings, tool use where certified), not a hard-coded vendor;
- user/provider configuration stays in WordPress Connectors;
- optional model preferences are hints, not authority;
- record actual provider/model metadata where WordPress returns it;
- support per-task budget/quality/latency profiles;
- graceful failure if no suitable model exists.

No WPE module should ship its own duplicate OpenAI/Anthropic/Google key setting when WordPress Connectors can own the credential.

## 12. Prompt libraries

Prompt templates can exist at:
- WPE core/shared;
- module-owned;
- Solution Blueprint-owned;
- site-owned custom;
- network template scope where accepted;
- third-party SDK extension.

Templates support:
- versioning;
- localization;
- variables;
- required context;
- output schema;
- evaluation fixtures;
- deprecation;
- clone/fork;
- export/import without secrets.

Built-in WPE templates are not directly editable; user forks them.

## 13. Multi-turn behavior

Multi-turn session memory is explicit and bounded.

Options:
- current session only;
- retain N days;
- local transcript off/on;
- store structured IR without full prose transcript;
- delete session;
- export session;
- share session with another authorized user;
- resume from saved Requirement IR.

The model's chat history is not canonical project memory. Canonical definitions, ADRs, Blueprints and WPE records remain source of truth.

## 14. Failure and degraded states

- no AI provider connected;
- no suitable model;
- provider quota/rate limit;
- connector invalid/expired;
- timeout;
- malformed structured output;
- schema validation failed;
- context too large;
- policy denied context;
- missing module/foundation;
- compatibility blocker;
- partial plan invalid;
- stale definition changed after prompt started;
- approval revoked;
- Ability execution partial failure;
- provider unknown outcome.

Failure must preserve the user's original requirement and allow manual builder continuation.

## 15. Concurrency/staleness

A generated change plan records source definition revision IDs/fingerprints.

Before apply:
- compare current revision to source revision;
- if changed, require regenerate/rebase/three-way review;
- do not overwrite a newer human edit.

## 16. Privacy

Before external AI transmission:
- show data classes the task may send;
- minimize context;
- exclude Vault secrets, passwords, session tokens, reset tokens and protected security internals;
- apply row/resource Policy before retrieval;
- honor local AI/remote-service consent policy;
- record provider/model/task purpose where allowed;
- allow schema-only mode;
- support local/self-hosted provider through WordPress Connector when available/certified.

Prompt injection in content is treated as untrusted data, not system instruction.

## 17. Cost/rate controls

Per task/profile:
- max prompt/context size;
- max output size;
- max model calls;
- max tool/Ability calls;
- timeout;
- retry policy;
- daily/monthly budget warning/limit where usage metadata permits;
- user/role/site/network rate limits;
- background job policy for long work;
- cancellation.

## 18. Observability

Record separately:
- Prompt Session state;
- AI provider/model call metadata;
- token/usage/cost metadata when available;
- Requirement IR validation;
- capability resolution;
- Plan IR validation;
- approval events;
- Ability executions;
- final definition revisions;
- failures/retries.

Do not store full sensitive prompts in generic logs.

## 19. AI-generated full-system flow

For requests such as:

> “Mere liye property CRM banao jisme leads, agents, properties, follow-ups, pipeline, WhatsApp reminders aur reports hon.”

WPE should:
1. map to curated/derived Solution patterns;
2. identify actors and objects;
3. propose fields/relations/statuses;
4. resolve existing WPE modules/foundations;
5. detect the messaging adapter requirement;
6. produce Blueprint/Plan IR;
7. show unsupported/external-authority items;
8. validate permissions/privacy;
9. install only as Draft after explicit approval and normal Solution Composer safety flow.

If a requested feature does not exist, do not drop it silently.

## 20. MUST NOT

- generate or eval arbitrary PHP, SQL or JavaScript as the ordinary execution path;
- bypass Capability + resource Policy;
- send secrets to a model;
- expose entire databases to AI because a user asked a broad question;
- assume model output is valid because JSON parsed;
- mutate production from free-form text without typed plan/diff/approval;
- label a partially supported requirement as fully built;
- invent external provider facts;
- let MCP exposure automatically expose every Ability;
- treat AI/MCP availability as required for ordinary deterministic WPE module operation.

## 21. Development gate

This is planning only. No AI Client call, provider connection, MCP server, Ability registration, prompt execution, schema migration or runtime test is authorized by this document.