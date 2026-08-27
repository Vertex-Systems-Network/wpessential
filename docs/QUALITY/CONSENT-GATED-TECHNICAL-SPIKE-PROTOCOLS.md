# WPEssential — Consent-Gated Technical Spike & Benchmark Protocols

Status: **Phase 0 planning only / DO NOT EXECUTE WITHOUT EXPLICIT OWNER DEVELOPMENT CONSENT**

Related: ADR-0014 Development Consent Gate, ADR-0002/0005/0006/0008/0009/0010/0011/0012, Implementation Readiness Matrix.

## Purpose

Define exactly what evidence future executable spikes must produce so an engineer/AI cannot convert “try this package” into uncontrolled development or declare an ADR accepted after a superficial demo.

This document authorizes **nothing**. It is only the future test protocol.

## Consent boundary

Before any protocol below starts, obtain explicit owner instruction that authorizes development/executable spikes.

The following do not count:
- `continue`;
- `proceed`;
- approve the plan;
- Phase 0 complete;
- accept ADR;
- research this;
- merge planning PR.

After consent, each spike still requires a bounded branch/checkpoint and must not silently become production implementation.

---

# Common spike rules

Every spike records:
- objective;
- hypotheses/alternatives;
- exact environment matrix;
- dependencies + versions/licenses;
- commands executed;
- fixtures/data sizes;
- metrics;
- failures/warnings;
- security observations;
- artifacts/logs;
- conclusion: pass/fail/inconclusive;
- recommended ADR change;
- cleanup/removal status.

A spike is not production code unless separately promoted after review.

## Evidence quality

Prefer:
1. automated repeatable test;
2. machine-readable measurements;
3. representative fixtures;
4. failure/adversarial cases;
5. reproducible commands.

One happy-path screenshot is never enough.

## Pass/fail discipline

A protocol may finish **Inconclusive**. Do not force a winner if evidence is weak.

---

# P-001 — Compatibility Floor Spike

Related: ADR-0002.

## Goal
Select supported WordPress/PHP/DB floor based on actual install/boot/representative behavior.

## Candidate matrix
At execution time refresh current supported versions from official sources, then include:
- proposed minimum WordPress;
- latest stable WordPress;
- one near-minimum/current branch if useful;
- proposed minimum PHP;
- current supported PHP branches relevant to WordPress;
- MySQL/MariaDB versions consistent with target WordPress hosting expectations.

## Fixture
Minimal WPE kernel candidate plus representative no-op registrations needed to prove:
- activation;
- admin route boot;
- REST/Abilities availability;
- database connectivity;
- Free/Pro compatibility guard;
- basic test bootstrap.

Do not implement product modules merely to test compatibility.

## Tests
- clean install/activate/deactivate;
- multisite network activation candidate where planned;
- WP_DEBUG on/off;
- REST/Abilities registration;
- PHP deprecation/error scan;
- DB charset/collation baseline;
- object cache absent/present candidate;
- WP-CLI bootstrap;
- uninstall/deactivate no fatal.

## Acceptance
ADR floor may become Accepted only if:
- all required minimum cells boot without fatal/deprecation classified as blocking;
- representative platform APIs behave consistently;
- dependency solver supports matrix;
- CI can reproduce matrix;
- unsupported cells fail clearly rather than corrupting state.

---

# P-002 — UI / Design System Spike

Related: ADR-0005.

## Goal
Prove WPE wrapper architecture with WordPress component/design-system primitives, DataViews/DataForm where appropriate, and Untitled-inspired visual language without runtime React conflicts.

## Representative surfaces
Build only spike fixtures:
- list/data table;
- editor form;
- modal/confirmation;
- tabs/navigation;
- toast/notice;
- loading/empty/error/degraded state;
- drag/reorder + keyboard alternative;
- destructive confirmation.

## Measures
- bundle size by route/chunk;
- duplicate React/WordPress package detection;
- accessibility automated scan + keyboard walkthrough;
- RTL rendering;
- localization extraction;
- dark/system appearance only if in accepted scope;
- mobile/narrow wp-admin behavior;
- CSS leakage to unrelated wp-admin screens;
- component replacement cost through wrappers.

## Acceptance
- no second React runtime;
- no global CSS leakage;
- WCAG-oriented keyboard/focus semantics pass agreed gates;
- wrappers can swap underlying primitive without module API rewrite;
- visual consistency meets product system;
- route-level assets load only when needed.

---

# P-003 — Job Service / Action Scheduler Coexistence Spike

Related: ADR-0006.

## Goal
Prove Job Service abstraction and preferred concrete adapter behavior under coexistence, retries, concurrency and failures.

## Cases
- another plugin ships older/newer Action Scheduler copy;
- queue enqueue/claim/run;
- duplicate/idempotency key;
- retry/backoff;
- cancel queued;
- cancel running where unsupported/declared;
- overlap lock;
- worker crash/recovery;
- timeout;
- WP-Cron disabled;
- WP-CLI runner;
- multisite scope;
- retention/pruning;
- 10k/100k queued jobs synthetic load.

## Measure
- enqueue latency;
- claim/run throughput;
- DB growth/index size;
- retry correctness;
- duplicate execution rate;
- stale claim recovery;
- admin observability cost.

## Acceptance
- WPE modules depend only on Job Service interface;
- coexistence does not fatal/load wrong version;
- at-least-once semantics + idempotency are demonstrably safe;
- failures are observable/recoverable;
- delayed WP-Cron does not create false exact-time claims.

---

# P-004 — Definition Repository Schema Benchmark

Related: ADR-0008.

## Alternatives
Compare at least:
A. identity/lifecycle + immutable revisions + dependency edges;
B. any justified alternative retained from schema-alternatives doc.

## Fixtures
- 1k definitions;
- 10k definitions;
- 100k stress only if meaningful;
- average/large payload sizes;
- draft/published revisions;
- dependency fan-out/fan-in;
- package import UUID remap;
- archival/tombstone cases.

## Operations
- get current definition;
- get published definition;
- list/filter by module/type/status;
- create revision;
- publish pointer switch;
- dependency impact query;
- compare/diff revisions;
- archive;
- package import;
- degraded unknown-future schema.

## Measure
- query count/latency;
- DB/index size;
- write amplification;
- revision growth;
- dependency traversal cost;
- cache effectiveness/invalidation;
- migration complexity.

## Acceptance
- common admin/runtime reads stay bounded/indexed;
- draft publish is atomic enough for intended semantics;
- unknown future schema can fail safe/read-only;
- dependency impact query does not require full payload scans;
- storage remains configuration-focused, not runtime EAV dumping ground.

---

# P-005 — Secrets Vault Crypto / Recovery Spike

Related: ADR-0009.

## Goal
Validate exact cryptographic envelope, key separation, rotation and recovery behavior.

## Threat cases
- DB dump stolen, config key not stolen;
- filesystem/config stolen, DB not stolen;
- full server compromise;
- salts/key changed;
- backup restored to staging/new domain;
- lost key;
- rotated key interrupted midway;
- multisite isolation;
- accidental secret logging/export.

## Candidate evidence
Compare only reviewed standard library primitives available in accepted PHP floor, such as Sodium AEAD/envelope patterns.

Do not invent crypto.

## Tests
- encrypt/decrypt known fixture;
- tamper ciphertext/tag/AAD;
- wrong key;
- key rotation;
- partial rotation resume;
- redact logs/support/export;
- secret access policy;
- backup restore with/without key material.

## Acceptance
- plaintext never stored as fallback;
- DB-only leak does not reveal secrets under external-key mode;
- integrity tampering detected;
- recovery/loss behavior explicit;
- rotation is resumable/audited;
- full-server compromise limitations documented honestly.

---

# P-006 — Free ↔ Pro Compatibility / Boot Matrix

Related: ADR-0010.

## Matrix
Test:
- compatible Free + Pro;
- newer Free / older Pro within supported window;
- older Free / newer Pro;
- unsupported mismatch both directions;
- Pro absent;
- Pro expired entitlement;
- Pro disabled mid-request cycle only through normal lifecycle;
- update interruption;
- DB schema ahead/behind code;
- rollback to previous package;
- restored backup with mismatched packages.

## Acceptance
- no fatal boot for supported/known mismatch states;
- premium modules remain unloaded/degraded safely on incompatible pair;
- Free CPT/Taxonomy remain usable;
- no destructive migration starts while counterpart incompatible;
- exact required versions visible;
- recovery/rollback path works.

---

# P-007 — CI / Quality Matrix Execution

Related: ADR-0011.

## Goal
Turn paper CI plan into reproducible gates.

## Required lanes candidate
- PR fast checks;
- integration compatibility matrix;
- E2E reference workflows;
- security/static analysis;
- nightly extended versions/performance;
- release artifact verification.

## Acceptance
- required PR checks deterministic enough for normal development;
- flaky tests classified/fixed, not blanket retried forever;
- package artifact tested, not only source tree;
- compatibility floor matrix represented;
- secrets absent from logs/artifacts;
- failed gate blocks release according to policy.

---

# P-008 — Build Toolchain Comparison

Related: ADR-0012.

## Compare
1. `@wordpress/build` candidate;
2. `@wordpress/scripts` fallback/comparison;
3. Vite only where a demonstrated unmet need exists.

Refresh versions/docs at execution time.

## Representative build
- React/TS admin route;
- CSS Modules/scoped CSS;
- dynamic route chunk;
- WordPress package imports;
- PHP asset registration metadata;
- translations;
- production sourcemap policy;
- tests/lint/typecheck integration.

## Measures
- build time;
- output size;
- duplicate externalized dependencies;
- cache/rebuild behavior;
- manifest/PHP registration complexity;
- RTL/CSS output;
- translation extraction;
- developer ergonomics/maintenance burden.

## Acceptance
Choose simplest toolchain that:
- externalizes WordPress/React correctly;
- supports code splitting/scoped assets;
- integrates translation/CSS requirements;
- produces deterministic release artifacts;
- does not require dual competing build systems without real need.

---

# P-009 — Query AST Compiler / Cost Guard Spike

Related: Query AST v1.

## Providers
At minimum representative:
- WP_Query/posts;
- users/terms if in v1;
- WPE Custom Table SQL provider.

## Cases
- nested AND/OR;
- meta/tax/date filters;
- typed params;
- relation join;
- aggregates;
- sort/pagination;
- invalid/unsupported operator;
- malicious identifier/value input;
- public cost budget exceeded.

## Acceptance
- no raw user SQL path;
- values parameterized;
- identifiers registry-validated/prepared;
- explain/estimated cost available enough for admin diagnostics;
- public query budgets block pathological plans;
- permission-aware cache does not leak protected results.

---

# P-010 — Relations Runtime Schema / Concurrency Spike

Related: Relations runtime alternatives.

## Fixtures
- one-to-one;
- one-to-many;
- many-to-many;
- ordered relation;
- pivot data;
- reverse lookup;
- polymorphic typed endpoints where accepted;
- 100k/1M edge scale candidate.

## Race cases
- two concurrent inserts violating one-to-one;
- detach/delete during attach;
- endpoint deletion/orphan cleanup;
- ordering collisions.

## Acceptance
- forward/reverse lookups indexed;
- cardinality enforced below UI layer;
- duplicate edge prevention deterministic;
- delete policy/reconciliation recoverable;
- generic edge model performs adequately or benchmark justifies per-relation alternative.

---

# P-011 — Workflow Runtime / Failure Semantics Spike

Related: Workflow Runtime candidate.

## Cases
- run pins published revision;
- retry idempotent step;
- non-idempotent external call unknown outcome;
- wait until time;
- wait for event duplicate/out-of-order;
- approval authorization;
- parallel branches;
- cancellation;
- compensation;
- worker crash between side effect and checkpoint.

## Acceptance
- in-flight run never mutates because definition edited;
- at-least-once signal handling is duplicate-safe;
- unknown external outcomes become reconciliation state, not fake failure/retry;
- side-effect state and logs are diagnosable;
- no generic promise of distributed rollback/exactly-once.

---

# P-012 — Membership Runtime / Revocation Benchmark

Related: Membership Runtime candidate and Accepted access semantics.

## Scale fixtures
- 100k users;
- 200k+ Enrollments;
- 1M Entitlements candidate;
- multiple memberships/user;
- team seats;
- 10k rules/resources.

## Critical cases
- active→revoked immediate access deny;
- Plan revision affecting 50k users;
- grace expiry without cron firing;
- seat revoke;
- cache service unavailable;
- object cache present;
- concurrent upgrade/exclusive Plan Group.

## Acceptance
- no provider API call on access hot path;
- timestamp expiry correct even when jobs late;
- revoked/force-denied access does not survive stale TTL;
- DB/cache query count stays bounded;
- rebuild/reconciliation path proven.

---

# P-013 — Backup/Restore Provider Certification Spike

Related: Backup provider matrix, restore semantics, ADR-0021.

For every marketed provider/protocol level:
- upload;
- resume/interruption;
- download;
- checksum/integrity;
- wrong credentials;
- quota/rate limit;
- remote deletion/retention;
- encrypted artifact recovery;
- actual restore fixture according to claimed V-level.

A successful HTTP/API response alone is not certification.

---

# Spike promotion rule

After a spike:
1. write results doc;
2. update ADR to Accepted/Rejected/continue Proposed;
3. delete/retain spike code according to whether it has production value;
4. no feature implementation starts automatically;
5. owner development consent scope still governs what happens next.

## Current state

**None of P-001 through P-013 has been executed.**

They remain future protocols only because explicit owner development/executable-spike consent has not been granted.