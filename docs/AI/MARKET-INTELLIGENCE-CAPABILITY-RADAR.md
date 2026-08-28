# WPEssential — Market Intelligence & Capability Radar

Status: **Phase 0 planning / no development authorization**
Date: 2026-08-29

## 1. Purpose

Continuously research the WordPress ecosystem and adjacent developer/product markets for new or fast-growing capabilities, options, plugin patterns, core APIs, standards, user pain points and security changes that may matter to WPEssential.

The Radar does not auto-copy competitors or auto-authorize code. It produces evidence-backed candidates and can invoke S07 Product Discovery & Planning Orchestrator to create Draft planning packages.

## 2. Architectural placement

Non-sellable shared platform service: **S08 Market Intelligence & Capability Radar**.

Consumers:
- S07 Planning Orchestrator;
- F01 Solution Blueprint;
- F12 AI Gateway;
- Product roadmap;
- compatibility/research governance.

## 3. Research sources

Primary sources:
- WordPress.org Plugin Information API (`query_plugins`, `plugin_information`, popular/new/beta/recommended searches);
- WordPress.org plugin pages;
- Plugin SVN/Trac development/changelog when relevant;
- official public GitHub repository/release/issues when linked or authoritative;
- WordPress Core Developer Blog/Make/Core/dev notes;
- Developer.WordPress.org APIs/handbooks;
- public RFC/standards/vendor docs;
- security advisories from reputable primary/recognized sources;
- public support forums/reviews as user-pain signals only.

Secondary sources:
- comparison/review articles for discovery only;
- developer community discussions;
- release newsletters.

Secondary source claims require primary-source verification before canonical planning.

## 4. Daily scan modes

- new plugins;
- popular plugins;
- recently updated plugins;
- fast-moving categories/tags;
- known comparator watchlist;
- existing WPE gap watchlist;
- WordPress core capability changes;
- security/compatibility changes;
- provider/API version changes;
- support issue themes;
- AI/MCP/Abilities ecosystem changes.

## 5. Candidate extraction

Each candidate records:
- name/slug;
- source URLs;
- category/tags;
- current version;
- last update;
- active-install/review indicators when public;
- problem solved;
- top features;
- new features since previous snapshot;
- public API/CLI/integration surface;
- Multisite indications;
- security/privacy implications;
- Free/Pro/business model;
- repository/source availability;
- observed user pain themes;
- WPE capability overlap;
- candidate architecture class.

## 6. Change detection

Store compact metadata snapshots and hashes. Detect:
- new plugin/candidate;
- major/minor release;
- meaningful changelog feature;
- API/compatibility floor change;
- rapid active-install/review movement where data permits;
- new category/tag trend;
- new WordPress core API;
- security fix/advisory;
- provider deprecation;
- competitor capability that closes a known WPE gap.

Do not treat minor text edits as product signals.

## 7. Scoring

Candidate planning score 0–100 composed from configurable weights:
- user/demand signal;
- cross-domain reuse;
- gap severity;
- strategic fit;
- composition leverage across existing modules;
- developer usefulness;
- competitive differentiation;
- WordPress-native alignment;
- security/privacy burden (negative);
- maintenance burden (negative);
- external-authority dependence (negative/neutral by type);
- implementation complexity (negative);
- evidence quality/freshness;
- monetization/Free-Pro fit.

Default actions:
- 80–100: full S07 Draft audit automatically;
- 60–79: candidate report + lightweight capability map;
- 40–59: watchlist;
- below 40: archive/no action;
- any severe security/core compatibility issue: priority review regardless of score.

Scores are triage, not acceptance authority.

## 8. Dedupe / capability map

Before recommending a module, compare candidate against:
- all current module options;
- shared services;
- adapters;
- Solution patterns;
- Open Decisions;
- planned but unimplemented foundations;
- rejected unsafe primitives.

Output:
- existing exact match;
- option enhancement;
- shared-service enhancement;
- adapter/provider profile;
- new module candidate;
- Blueprint/template candidate;
- monitor only;
- reject.

## 9. Automatic planning draft

For high-confidence candidates, S08 may call S07 to create Draft artifacts containing:
- research manifest;
- competitor comparison;
- WPE overlap/gap analysis;
- architecture classification;
- option/flow proposal;
- evidence namespace proposal;
- risks;
- owner decision required.

Default location:
`docs/RESEARCH/CANDIDATES/YYYY-MM/<slug>/`

Draft candidate must not be counted in canonical module denominator until accepted by ADR/governance.

## 10. Daily report

Report sections:
- new high-priority candidates;
- changed watched plugins;
- WordPress core/platform changes;
- security/compatibility alerts;
- top user pain themes;
- candidates promoted/demoted;
- duplicate/rejected findings;
- planning drafts created;
- stale sources needing refresh;
- scan failures.

## 11. GitHub integration

Default automation output should be non-destructive:
- upload Markdown/JSON artifact;
- create/update one daily/weekly market-radar issue;
- optionally open a **Draft planning PR** containing only `docs/RESEARCH/CANDIDATES/**` when explicitly enabled;
- never merge automatically;
- never modify runtime/source directories;
- never grant development consent.

## 12. Source/network safety

- rate-limit and cache requests;
- honor robots/terms where relevant;
- Safe HTTP only;
- no credential scraping;
- no private repository scanning without explicit connected authorization;
- redact secrets from issue/PR artifacts;
- record source retrieval failures truthfully.

## 13. AI usage

AI may:
- summarize changelogs;
- cluster feature themes;
- extract capability candidates;
- compare with WPE capability catalog;
- draft S07 research plan;
- explain why a candidate is/not useful.

AI may not:
- invent active install counts;
- browse through arbitrary unsafe fetch bypass;
- accept a module itself;
- write production code;
- merge PRs;
- treat reviews as facts;
- copy source implementation.

## 14. GitHub daily schedule

Planned cadence: once per day, approximately every 24 hours.

Recommended schedule uses a non-round minute to reduce shared scheduler contention. GitHub scheduled workflows run on the default branch and may be delayed; “daily” is therefore the truthful contract, not exact-to-the-second 24-hour execution.

The executable workflow is intentionally **not enabled before development consent**. Exact proposed YAML is documented in `docs/OPERATIONS/MARKET-INTELLIGENCE-DAILY-GITHUB-JOB.md`.

## 15. Evidence namespace

Future protocol: `MIR-001…MIR-176`, executed 0 until development consent.

Coverage includes WordPress.org API paging, source freshness, dedupe, changelog parsing, GitHub/SVN source resolution, scoring, false positives, candidate routing, S07 handoff, VCS permissions, secret safety, rate limits, failed sources, issue/PR generation, daily scheduling, replay/idempotency and audit.

## 16. MUST NOT

- automatically add every new plugin feature as a WPE module;
- auto-merge canonical scope;
- write runtime code;
- scrape private/proprietary sources without permission;
- publish private credentials/content;
- fabricate popularity/trend claims;
- let one noisy marketplace source dominate decisions;
- confuse daily scan completion with full technical validation.
