# WPEssential — Autonomous Product Discovery & Pre-Development Planning Orchestrator

Status: **Phase 0 planning / no development authorization**
Date: 2026-08-29

## 1. Purpose

Create a reusable planning system so an owner can write only a minimal command such as:

> `ABC system add karna hai`

and WPE planning automation performs the same pre-development work that is currently being done manually: repository audit, internet/market research, source review, capability mapping, duplicate detection, module-vs-shared-service-vs-adapter classification, exhaustive options/flows, AI/MCP coverage, Multisite, security/privacy, evidence protocol design, ADR/governance synchronization and a clear implementation blocker report.

This is **not** a production code generator. It creates reviewed planning artifacts and cannot cross ADR-0014 implementation consent.

## 2. Architectural placement

Non-sellable shared platform service: **S07 Product Discovery & Planning Orchestrator**.

It composes:
- F01 Solution Blueprint & Application Composer;
- F12 AI Gateway / Prompt Runtime;
- Requirement Compiler;
- Capability Gap Request System;
- WordPress Abilities / optional MCP;
- repository/VCS connector;
- Safe HTTP / research adapters;
- market intelligence S08;
- Audit/Observability;
- Document/Definition versioning.

It must not create a second AI provider stack or separate authorization system.

## 3. Supported intents

- `ADD_SYSTEM`
- `ADD_MODULE`
- `ADD_OPTION`
- `ADD_FOUNDATION`
- `ADD_ADAPTER`
- `AUDIT_COMPETITOR`
- `AUDIT_SOURCE_URL`
- `EXPAND_EXISTING_MODULE`
- `MARKET_RESEARCH`
- `REASSESS_SCOPE`
- `GENERATE_REFERENCE_BLUEPRINT`
- `UPDATE_EXISTING_PLAN`

User can provide only a name, or name + URLs/docs/examples/constraints.

## 4. Canonical pipeline

`Owner request`
→ intent normalization
→ repository state/canonical-scope read
→ source material ingestion
→ public market/research discovery
→ competitor/source evidence extraction
→ demand/problem statement
→ current WPE capability map
→ duplicate/overlap analysis
→ architecture classification
→ missing primitive/gap map
→ exhaustive product specification
→ roles/objects/states/screens/options
→ happy/alternate/failure/recovery flows
→ data/storage/scale assumptions
→ integrations/adapters/external authorities
→ AI Prompt/MCP/Ability mapping
→ security/privacy/negative requirements
→ Multisite/lifecycle mapping
→ migration/import/export/uninstall behavior
→ executable evidence namespace/protocol plan
→ ADR proposal
→ source-of-truth synchronization plan
→ owner review/acceptance
→ planning artifacts become canonical
→ stop at `AWAITING_DEVELOPMENT_APPROVAL` unless explicit development consent exists.

## 5. Repository audit phase

Must inspect current canonical sources before proposing anything:
- DEVELOPMENT-CONSENT;
- AGENTS;
- CHECKPOINT;
- Approval Ledger;
- Work Coordination Ledger;
- Implementation Readiness;
- Open Decisions;
- ADR index;
- module option specs;
- shared-service architecture;
- Solution catalog/patterns;
- AI Prompt/MCP contracts;
- existing evidence protocols;
- Draft planning PR state.

Outputs:
- current scope denominator;
- existing related modules;
- reusable shared services;
- current ADR/evidence IDs;
- current planning work package;
- conflicts/duplicates;
- explicit facts that must be preserved.

Repository evidence overrides conversational memory.

## 6. Source ingestion

Accepted source classes:
- user-provided URLs;
- WordPress.org plugin pages;
- plugin SVN/Trac/GitHub repositories;
- official vendor docs;
- public API docs/OpenAPI;
- changelogs/releases;
- support forums/issues;
- WordPress core/Developer docs;
- standards/RFCs;
- user-provided files/documents.

Each source record stores:
- URL/title;
- source type;
- publisher/authority;
- retrieved date;
- version/revision if known;
- relevant claims/features;
- confidence;
- freshness;
- license/provenance note;
- citations/evidence references.

The system audits behavior/architecture; it must not copy incompatible proprietary implementation/code.

## 7. Research strategy

For each request:
1. exact source/name search;
2. official WordPress.org/vendor source;
3. current version/changelog;
4. code/repository architecture where public;
5. market alternatives;
6. user reviews/support issues for pain signals, clearly treated as anecdotal;
7. security/reliability history where relevant;
8. standards/core APIs;
9. current demand indicators;
10. WPE differentiation opportunities.

Research report distinguishes:
- source fact;
- web/market fact;
- inference;
- proposed WPE decision.

## 8. Capability classification

Each discovered feature maps to one of:
- `ALREADY_SUPPORTED_DIRECTLY`;
- `SUPPORTED_BY_COMPOSITION`;
- `SUPPORTED_BY_EXISTING_OPTION_EXPANSION`;
- `NEEDS_NEW_MODULE`;
- `NEEDS_SHARED_SERVICE`;
- `NEEDS_DOMAIN_ADAPTER`;
- `NEEDS_PROVIDER_CERTIFICATION`;
- `NEEDS_EXTERNAL_AUTHORITY`;
- `SECURITY_MODEL_REJECTS`;
- `NOT_PRODUCT_FIT`;
- `UNKNOWN_REQUIRES_EVIDENCE`.

No feature is silently omitted.

## 9. Reuse test before new module

A new module is proposed only when the capability:
1. cannot be represented safely by existing primitives;
2. is useful across multiple workflows/users;
3. has one coherent data/runtime ownership model;
4. would otherwise be duplicated;
5. deserves independent permissions/lifecycle/UI;
6. has sustainable maintenance/security value.

Otherwise classify as:
- option extension;
- shared service;
- adapter;
- Solution Blueprint;
- developer tool;
- rejected unsafe primitive.

## 10. Competitive audit template

For each comparator:
- product/plugin name;
- current version/date;
- active-install/demand signals if public;
- major features;
- admin screens/workflow;
- data/storage model if observable;
- API/CLI/REST;
- Multisite;
- permissions;
- import/export;
- performance approach;
- privacy/logging;
- AI/automation;
- known limitations/issue themes;
- Free/Pro boundary;
- useful ideas;
- ideas WPE intentionally rejects;
- differentiation opportunities.

Do not treat popularity as proof of correct architecture.

## 11. Exhaustive planning output

A new module/system plan must document:
- identity/navigation;
- list screens;
- editor tabs;
- every known option/default;
- validation/sanitization;
- role/capability/Policy;
- objects/data ownership;
- states/transitions;
- happy path;
- alternate paths;
- failure/recovery;
- bulk actions;
- search/filter/sort;
- import/export;
- revisions/versioning;
- cache/jobs;
- REST/Abilities/MCP/CLI;
- AI Prompt examples;
- privacy/retention;
- Multisite;
- lifecycle/disable/expiry/uninstall;
- performance/scale;
- negative requirements;
- evidence namespace.

## 12. Planning IR

Planning automation emits structured `PlanningIR`:
- request identity;
- intent;
- source manifest;
- problem statement;
- personas/use cases;
- capability map;
- architecture class;
- dependencies;
- option tree;
- object schemas;
- flow/state models;
- permissions;
- external authorities;
- risks;
- Multisite matrix;
- evidence plan;
- ADR proposals;
- canonical files to update;
- implementation blockers;
- confidence/open questions.

AI prose is generated from validated PlanningIR, not the other way around.

## 13. Automatic planning write policy

Default:
- generate Draft research and planning artifacts;
- create/update a planning branch/PR or local Draft space;
- do not auto-merge canonical architecture;
- do not modify source/runtime code;
- do not change implementation approval.

Canonical acceptance requires configured owner/governance approval.

High-confidence routine option documentation can be auto-drafted, but new sellable module/foundation/authority/security decisions require review.

## 14. “ABC system add karo” UX

Prompt screen returns:
1. Understood requirement summary.
2. Existing capability reuse percentage.
3. Missing capability list.
4. Research plan/sources.
5. Proposed architecture classification.
6. Recommended new modules/services/adapters only where needed.
7. Risks/external authorities.
8. Planning work packages.
9. “Run planning audit” action.
10. Final “Planning complete — development not authorized” state.

If ambiguity is resolvable from repository/research, do not ask unnecessary questions. Ask owner only where business semantics materially affect the product.

## 15. Market-intelligence handoff

S07 consumes candidates from S08 Market Intelligence Radar.

Candidate flow:
`market signal → capability extraction → dedupe → demand/reuse/risk score → S07 audit → Draft plan/watchlist/reject`.

S08 cannot directly authorize a module.

## 16. AI Prompt / MCP

Prompt templates:
- Add a system;
- Audit this plugin/source;
- Compare market competitors;
- Expand this module;
- Reconcile new WordPress core capability;
- Audit a support pain point;
- Re-plan from new evidence.

MCP/Abilities can expose read/search/planning-draft operations. Source/runtime write/merge/deploy abilities remain separate and consent-gated.

## 17. Provenance and anti-hallucination

Every feature claim in competitive audit needs source reference or explicit inference label.

Rules:
- no invented active-install counts;
- no invented source code behavior;
- no “latest” claim without fresh source;
- user review ≠ fact;
- static docs ≠ runtime certification;
- inaccessible source must be reported as inaccessible;
- conflicting evidence remains unresolved until reconciled.

## 18. Scope-growth controls

To prevent endless module inflation:
- reuse score;
- duplicate score;
- strategic fit;
- cross-domain reuse;
- user demand;
- maintenance burden;
- security risk;
- external authority;
- implementation complexity;
- monetization/Free-Pro fit;
- capability adjacency.

Thresholds are planning policy, not automatic truth.

## 19. Evidence namespace

Future protocol: `PDO-001…PDO-176`, executed 0 until implementation consent.

Coverage: intent classification, repo audit, research provenance, source freshness, capability mapping, duplicate detection, architecture classification, exhaustive spec generation, gap requests, ADR drafts, canonical sync plans, owner approvals, AI/MCP security, VCS behavior, privacy, concurrency, market handoff and regression.

## 20. MUST NOT

- start development because owner said only “add system”;
- auto-merge a new canonical module without governance policy;
- copy proprietary source code;
- invent market facts;
- expose repo/provider secrets to research models;
- create arbitrary runtime code from competitor source;
- bypass module reuse checks;
- silently ignore unsupported requirements;
- convert popularity into architecture authority.
