# WPEssential — Market Expansion AI Prompt Mapping — Surfaces 44–48

Status: **Phase 0 exhaustive product planning / no development authorization**
Date: 2026-08-29
Parent standard: `MODULE-AI-PROMPT-OPTION-STANDARD.md`
Accepted architecture: ADR-0178 + ADR-0188

## Purpose

Extend the shared WPE Prompt & Requirement Compiler contract to the five market-expansion surfaces accepted by ADR-0183…ADR-0188. These modules use the same F12 AI Gateway, Requirement IR, Plan IR, Abilities, Policy and optional MCP bridge as every other WPE surface.

No module may add a private AI provider/key/chat execution stack.

## Coverage matrix

| # | Surface | Primary AI prompt uses | Never silently allow |
|---:|---|---|---|
| 44 | URL Redirection & Routing | create redirect rules from requirements; audit 404s/chains/loops; permalink migration plan; explain match trace | auto-publish unsafe redirect; open redirect; unbounded regex; authorization bypass; unsupported server export |
| 45 | Search, Replace & Data Transformation | generate Search/Transform/Scope Drafts; URL migration plan; explain Dry Run/diff; identify protected fields | destructive Run without reviewed Plan; secret/password/token mutation; arbitrary SQL; concurrent overwrite |
| 46 | Dummy Data & Fixture Studio | generate Dataset/scenario definitions; locale/volume/edge-case profiles; explain cleanup ownership | generate real secrets/PII/payment/provider side effects; create insecure admin; delete real data |
| 47 | Link Health & Crawl Intelligence | design scan; summarize/prioritize issues; suggest redirect/content/media Fix Plans; explain crawl result | infer replacement target as business truth; unsafe/private-network crawl; mutate content from scan result; leak protected URL existence |
| 48 | Database Maintenance & Cleanup | audit storage health; rank safe cleanup candidates; create Dry Run/retention/maintenance Plan; explain autoload/table health | blind third-party deletion; arbitrary DELETE/TRUNCATE; bypass retention/backup/reauth; auto-run destructive cleanup |

## 44 — Redirect AI modes

Allowed:
- Create;
- Modify Draft;
- Explain;
- Audit;
- Troubleshoot;
- Optimize;
- Migrate/Map;
- Generate acceptance-test plan;
- Request Capability.

Context providers:
- redirect/group Definitions;
- permalink inventory;
- aggregate 404/redirect stats under Policy;
- Link Health issues;
- domain/site routing inventory;
- relevant Search/Replace migration Plans;
- schema/config, not unrelated request PII.

Structured outputs:
- RedirectDefinitionDraft[];
- RedirectMigrationPlanDraft;
- ChainCollisionReport;
- RoutingSimulationInput;
- GapReport.

High-risk apply:
- publish/enable bulk redirects;
- external-domain targets;
- network route set;
- server config export/apply;
- response security headers.

AI safety:
- destination evidence shown;
- open-redirect validator mandatory;
- loop analyzer mandatory before publish;
- regex complexity validator mandatory;
- 404 “best target” is suggestion, never authoritative mapping.

## 45 — Search/Replace AI modes

Allowed:
- Create Search Draft;
- Create Transform Draft;
- Map URL/domain migration;
- Explain scope;
- Generate Dry Run;
- Audit diff;
- Suggest exclusions;
- Troubleshoot malformed serialization/JSON;
- Request Capability.

Context:
- validated Data Source/table schema;
- selected field metadata;
- redacted samples only with permission;
- Backup availability metadata;
- module ownership/dependencies;
- no Vault/plain credentials.

Structured output:
- SearchDefinitionDraft;
- TransformDefinitionDraft;
- ScopeDraft;
- DryRunRequest;
- ChangePlanDraft;
- RiskReport;
- RollbackRequirement.

MUST NOT:
- execute production mutation directly from prose;
- generate raw SQL as ordinary action;
- include secret values in model context;
- claim rollback if Run class is not reversible;
- silently select all network/global tables.

## 46 — Dummy Data AI modes

Allowed:
- Generate Dataset Draft;
- Generate Scenario;
- Generate Field Provider mappings;
- Generate relation/status distributions;
- Build edge/adversarial QA pack;
- Estimate volume;
- Explain cleanup;
- Request missing generator/provider.

Structured outputs:
- DatasetDefinitionDraft;
- GeneratorDefinitionDraft[];
- ScenarioGraphDraft;
- VolumeProfileDraft;
- CleanupPlanDraft;
- AdapterGapReport.

Context:
- entity/field/relation schemas;
- Solution Blueprint requirements;
- supported adapter fixture Abilities;
- locale packs;
- no real production personal-data sample needed by default.

MUST NOT:
- use scraped people;
- create real provider credentials/payment events;
- auto-run large generation;
- create known/default passwords;
- cleanup anything not owned by Generation Run.

## 47 — Link Health AI modes

Allowed:
- Create Scan Draft;
- Explain status/inconclusive result;
- Cluster issues;
- Prioritize remediation;
- Generate Fix Plan Draft;
- suggest likely replacement target with confidence/evidence;
- summarize redirect chains/orphan graph;
- Request Capability.

Structured outputs:
- ScanDefinitionDraft;
- IssueClusterReport;
- FixPlanDraft;
- TargetSuggestion[] with evidence/confidence;
- CrawlBudgetDraft.

MUST NOT:
- fetch arbitrary URL outside Safe HTTP policy;
- expose protected source/result to unauthorized actor;
- turn AI confidence into destination authority;
- modify source content, routing or media without owning Ability + reviewed Plan.

## 48 — DB Maintenance AI modes

Allowed:
- Analyze Storage Health;
- Explain autoload/table growth;
- Rank cleanup candidates;
- Create retention/cleanup Plan Draft;
- Generate Dry Run;
- explain orphan confidence;
- compare before/after health;
- Request cleanup provider.

Structured outputs:
- MaintenancePlanDraft;
- CandidateRiskReport;
- RetentionPolicyDraft;
- BackupRequirement;
- ProviderGapReport;
- VerificationPlan.

MUST NOT:
- issue raw DELETE/TRUNCATE/ALTER;
- infer third-party ownership from table/name pattern alone;
- bypass legal/privacy retention;
- auto-run C3/C4 destructive profile;
- treat estimated reclaimed bytes as verified outcome.

## Shared “ABC system add karo” integration

When a prompt requests a complete system and these surfaces are needed, S07 Planning Orchestrator resolves them like any other module:
- redirects/migrations → surface 44;
- bulk content/data transformations → 45;
- demo/QA/load data → 46;
- link/crawl health → 47;
- storage maintenance → 48.

If one of these modules lacks a requested option, the standard Capability Gap Request flow offers **Request New Option/System** rather than inventing runtime behavior.

## Current coverage truth

- Base AI Prompt standard surfaces 1–43: mapped.
- Market-expansion surfaces 44–48: mapped here.
- Combined current module-wide AI Prompt product coverage: **48/48**.
- AI Prompt executable evidence AIP: **0/176**.
- Runtime AIC/MCP certifications: **0**.

## Development gate

No AI provider call, Prompt Session, MCP invocation, scan, DB transform, redirect publish, data generation or cleanup is authorized by this mapping.
