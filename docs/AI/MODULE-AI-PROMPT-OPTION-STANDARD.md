# WPEssential — Module AI Prompt Option Standard

Status: **Phase 0 exhaustive product planning / no development authorization**  
Date: 2026-08-29

## 1. Scope

Every WPEssential user-facing module/platform surface and each universal foundation must expose AI assistance through the same shared Prompt & Requirement Compiler rather than implementing a private chatbot.

This standard defines the minimum product options each surface must declare.

## 2. Common module Prompt panel

Every applicable list/editor/detail screen may expose **Ask AI** with these shared controls.

### Context
- current module;
- current definition/resource;
- selected rows/resources;
- related definitions/dependencies;
- schema only / include authorized sample values;
- current site / network scope where supported;
- installed Solution context;
- optional user-provided text/file through safe upload rules.

### Mode
Module manifest chooses from:
- Create;
- Modify;
- Explain;
- Troubleshoot;
- Audit;
- Optimize;
- Migrate/Map;
- Generate Query;
- Generate Workflow;
- Generate UI/Listing;
- Generate Test/Sample Plan;
- Build Solution;
- Request Capability.

### Output
- interpreted requirements;
- assumptions;
- exact supported capabilities;
- unsupported/gap items;
- proposed definitions;
- diff;
- validation;
- permissions/security impact;
- data/migration impact;
- external dependencies;
- simulation/preview;
- Save Draft;
- Apply where policy permits;
- Request New Option/System.

## 3. Per-module AI policy options

Each module manifest/spec declares:
- AI enabled: inherited / enabled / disabled;
- allowed prompt modes;
- allowed context providers;
- maximum context class: schema-only / public / internal / PII-approved;
- allowed task classes;
- allowed draft operations;
- allowed apply operations;
- high-risk operations requiring approval;
- abilities/tool allowlist;
- model capability requirements;
- budget profile;
- timeout/retry profile;
- Prompt Session retention;
- audit detail level;
- whether MCP exposure is eligible;
- whether module prompts are exposed as MCP prompts;
- fallback/manual route when AI unavailable.

## 4. Standard built-in prompt actions

Where meaningful, modules should provide:
- **Create from requirement**;
- **Modify safely**;
- **Explain this configuration**;
- **Why is this not working?**;
- **Find conflicts**;
- **Suggest improvements**;
- **Generate from example**;
- **Map/import requirement**;
- **Show dependencies/impact**;
- **Generate acceptance-test plan**;
- **Find unsupported requirements**.

These actions produce structured output; they are not free-form permission bypasses.

## 5. Surface-by-surface minimum AI contract

| Surface | Primary AI prompt uses | Never silently allow |
|---|---|---|
| 1 CPT Builder | create labels/options from business object; audit registration; migration impact explanation | unregister/rename external/core type without ownership/migration plan |
| 2 Taxonomy Builder | generate taxonomy model; attach targets; rewrite/label suggestions | destructive slug/object binding changes without impact review |
| 3 Custom Fields | generate field groups, validation, locations, defaults; map existing schema | expose secrets/security meta or invent unsafe storage |
| 4 Relations | infer relation graph/cardinality/pivot fields; explain graph | approved cascade/delete bypass |
| 5 Status Manager | generate states/transitions/guards; explain impossible transition | direct state write bypassing transition policy |
| 6 Query Builder | natural-language query → typed AST; explain cost/results | raw SQL generation/execution as ordinary flow |
| 7 Custom Tables | draft schema/indexes/migrations; explain impact | execute arbitrary DDL/DML or destructive migration without recovery gate |
| 8 Admin Columns | propose views/columns/filters/sort/batching | convert visibility into authorization |
| 9 Dynamic Listings | generate listing from Query/data source; responsive/empty states | render protected fields without Policy |
| 10 Dashboard Widgets | create KPI/list/info widget from approved sources | arbitrary remote/iframe source without trust policy |
| 11 Admin Menu | propose role-specific navigation | treat hidden menu as access control |
| 12 Settings Page | create settings schema/tabs/validation | expose Vault value or protected core setting unsafely |
| 13 Frontend Dashboard | generate portal/routes/widgets by actor | direct-route Policy bypass |
| 14 User Profile | create role/profile views/fields | password/session/Application Password operations through generic fields |
| 15 Membership | draft Plans/Policies/Enrollment workflows | billing event → direct access grant; protected-resource fail-open |
| 16 Builder Widgets | generate Component Blueprint/control schema | arbitrary PHP/JS or proprietary builder internals |
| 17 Forms & Workflow | generate forms/workflows/actions/approvals | arbitrary code; user/role/security writes without explicit policy |
| 18 Cron/Jobs | create schedule around registered actions | promise exact timing on WP-Cron alone; eval code |
| 19 Notifications | create event/recipient/template rules | legal consent/unsubscribe bypass |
| 20 Emails | generate email-safe layouts/copy/tokens | claim inbox delivery; use arbitrary frontend HTML as email-safe |
| 21 Message & Chat | generate conversation policy/routing/moderation | object-ID-only access; expose private attachments |
| 22 REST Builder | generate endpoint schema/policy/binding | public write endpoint without authorization |
| 23 Webhooks & Connections | generate mappings/retry/signature profiles | reveal credentials; bypass SSRF/replay controls |
| 24 Backup | draft backup/retention/restore plans | claim Supported provider/restore without certification |
| 25 Reset | draft bounded reset plan/impact | execute destructive reset without verified recovery/reauth |
| 26 Import/Export | infer mappings/transforms/match rules | arbitrary inline PHP; overwrite unknown ownership silently |
| 27 Protector | generate hardening/access rules | describe obscurity as authentication/WAF replacement |
| 28 Watermarker/Media | generate rule profiles/rendition plan | mutate canonical original under standard mode |
| 29 XML-RPC | explain exposure; draft method allow/deny profile | claim full endpoint disabled from insufficient hooks |
| 30 Role & Capability | create role proposal/diff | lock out admin; AI grant itself broader authority |
| 31 Platform/Account/Docs/Support | explain health/account; draft support request | upload diagnostics or connect remote account without consent |
| F01 Solution Composer | requirement → complete Blueprint/Plan IR | install unsupported components or overwrite site collisions |
| F02 Analytics/Journeys | generate metrics/funnels/cohorts/alerts | treat client events as authority or correlation as causation |
| F03 Search | generate index/ranking/synonyms/search rules | stale protected index exposure |
| F04 Decision/Formula | generate typed formulas/scorecards/decision tables | score == authorization; arbitrary executable expressions |
| F05 Ledger | propose account/movement types/reconciliation | overwrite balances or create unbalanced movement where balanced profile required |
| F06 Reservation | generate resources/availability/capacity policies | non-atomic booking confirmation/double-booking semantics |
| F07 Placement/Personalization | generate placements/audiences/frequency | dark patterns, consent bypass, protected-data leakage |
| F08 Experiments/Rollouts | create experiment/variants/metrics | non-deterministic assignment where stable allocation required |
| F09 Documents/Records | create templates/record workflows | call generated file legally signed/official without authority |
| F10 Sync/ETL | generate mapping/sync/conflict strategy | unknown remote outcome as success; unsafe bidirectional overwrite |
| F11 Geo/Territory | create zones/territories/routing rules | geocoder result as legal/postal authority without provider contract |
| F12 AI Gateway | create prompt/task/retrieval/copilot/evaluation profiles | provider credential duplication; bypass policy/tool allowlist |

## 6. AI Prompt button states

- Ready;
- Provider not connected;
- AI unavailable on this WordPress version;
- Permission denied;
- Module AI disabled;
- Context contains restricted data;
- Required model capability unavailable;
- Budget/rate limit reached;
- Degraded connector;
- Draft generated;
- Validation failed;
- Gaps found;
- Awaiting approval;
- Applying;
- Partial failure;
- Completed/verified.

## 7. Prompt History

Per module/definition:
- recent Prompt Sessions;
- actor;
- request summary;
- mode;
- provider/model metadata where available;
- IR version;
- outcome;
- changes applied/not applied;
- approval state;
- usage/cost metadata where available;
- delete/export according to privacy policy.

Full prompt text may be excluded from retention while structured Requirement/Plan IR is retained.

## 8. Saved Prompts

Users with permission can:
- save a prompt template;
- fork a built-in prompt;
- assign module/mode;
- add structured variables;
- set default context scope;
- set output schema profile;
- share site/network where permitted;
- export/import without secrets;
- archive/deprecate.

## 9. Generated-change rules

AI-generated changes obey the same module editor semantics as human changes:
- draft/revision;
- validation;
- dependencies;
- Used-by impact;
- Policy;
- migration/recovery;
- preview/test;
- audit;
- import/export;
- module lifecycle.

No private AI-only definition format is allowed.

## 10. Development gate

No module AI UI, Prompt Session storage, AI call, MCP exposure or Ability execution is authorized by this planning document.