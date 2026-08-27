# WPEssential — Open Decisions Register

Status: **Phase 0 / planning-only / no development consent**  
Last reviewed: 2026-08-27

This register is the queue of unresolved decisions. It prevents an AI/engineer from silently converting a recommendation into an architectural fact.

## Decision states

- **Researching** — evidence still being collected.
- **Ready for non-executable decision** — enough static/official evidence may exist to accept without code.
- **Executable evidence required** — a bounded spike/benchmark is needed; owner consent is required before writing/running code under ADR-0014.
- **Accepted** — recorded by ADR or specification.
- **Deferred** — intentionally not blocking the current phase.

---

# A. Phase 0 platform blockers

## D-001 — Minimum WordPress/PHP compatibility floor

**Related:** ADR-0002  
**Current recommendation:** WordPress 6.9+ strongly preferred because Abilities API becomes a native platform primitive; PHP floor remains a market/security tradeoff.

Need to resolve:
- exact minimum WordPress;
- exact minimum PHP;
- support window policy;
- latest/current WordPress testing policy;
- MySQL/MariaDB floor inherited from supported WordPress versions;
- whether multisite is supported by all core surfaces from first release or module-by-module.

Static research can determine:
- official WordPress/PHP requirements and compatibility statements;
- PHP security/support lifecycle;
- dependency declared requirements;
- Abilities availability.

Executable evidence desired later:
- installation/activation matrix on candidate versions;
- representative unit/integration/E2E suite;
- dependency install/build matrix.

**Executable spike required before final acceptance:** Yes.  
**Owner consent for spike:** Not granted.  
**Exit condition:** supported matrix passes and ADR-0002 becomes Accepted.

## D-002 — Admin UI/design-system contract

**Related:** ADR-0005

Need to resolve:
- exact wrapper boundary around Untitled UI / WordPress components;
- whether DataViews becomes the default list framework where stable;
- design token ownership;
- accessibility primitives;
- forms/dialog/toast/table patterns;
- dark mode scope, if any;
- RTL/localization requirements;
- component replacement strategy if upstream UI package changes.

Static research can determine licensing/API stability.  
Executable evidence desired: representative list/editor/dialog/form accessibility and bundle spike.

**Executable spike required:** Yes.  
**Consent:** Not granted.

## D-003 — Background Job Service implementation

**Related:** ADR-0006

Need to resolve:
- Action Scheduler version/coexistence policy;
- prefix/loader ownership;
- claims/locks/concurrency;
- retry/backoff API;
- idempotency key behavior;
- cancellation;
- retention/pruning;
- system cron/WP-CLI runner;
- multisite scope;
- observability interface.

Static research can define interfaces/risks.  
Executable evidence needed for coexistence, concurrency, failed-run recovery and load behavior.

**Executable spike required:** Yes.  
**Consent:** Not granted.

## D-004 — Definition Repository physical schema

**Related:** ADR-0008

Concept already accepted:
- definitions are versioned configuration objects;
- runtime WordPress/application data is not forced into a universal EAV store.

Still need:
- table names;
- ID/UUID strategy;
- type/module columns;
- status/version model;
- normalized vs JSON payload split;
- dependency-edge storage;
- revisions/diff storage;
- indexes;
- multisite scope;
- transaction rules;
- deletion/tombstone behavior;
- package import ID remapping;
- 10k/100k definition performance budget.

Static modeling can narrow candidates.  
Executable benchmark required before final schema acceptance.

**Executable spike required:** Yes.  
**Consent:** Not granted.

## D-005 — Secrets Vault key and recovery model

**Related:** ADR-0009

Need to resolve:
- secret-record storage;
- encryption algorithm/library strategy;
- key source and separation;
- behavior when WordPress salts change;
- multisite isolation;
- export/backup inclusion policy;
- key rotation;
- lost-key recovery semantics;
- staging/domain migration behavior;
- read/write capability boundaries;
- redaction/logging.

Static threat model must come first.  
A small cryptographic/storage prototype is likely required before acceptance.

**Executable spike required:** Likely yes.  
**Consent:** Not granted.

## D-006 — Free ↔ Pro compatibility protocol

**Related:** ADR-0010

Need to resolve:
- Platform API version format;
- minimum/maximum compatible versions;
- boot order;
- mismatched-version degraded state;
- shared dependency ownership;
- database migration ownership;
- Pro downgrade behavior;
- update order and rollback;
- update-channel handoff.

Static contract can be designed first.  
Executable mismatch matrix required before acceptance.

**Executable spike required:** Yes.  
**Consent:** Not granted.

## D-007 — CI/test matrix

**Related:** ADR-0011

Need to resolve:
- PR required checks;
- main/nightly matrix;
- WordPress/PHP/DB versions;
- unit vs integration vs E2E split;
- Playground use;
- plugin/theme compatibility fixtures;
- artifact packaging checks;
- dependency audit tools;
- static-analysis levels;
- provider integration test isolation;
- performance regression cadence.

Final acceptance depends on D-001 and D-008.

**Executable spike required:** CI execution ultimately yes.  
**Consent:** Not granted.

## D-008 — Frontend/admin build toolchain

**Related:** ADR-0012

Need to resolve:
- Vite vs `@wordpress/scripts` or hybrid;
- WordPress React/package externalization;
- code splitting/module manifests;
- PHP asset-manifest lookup;
- CSS Modules/scoping strategy;
- production sourcemaps;
- translation extraction;
- dependency deduplication;
- bundle budgets;
- test runner integration.

Static research can shortlist.  
Executable build/bundle spike required to avoid duplicate React/WordPress packages.

**Executable spike required:** Yes.  
**Consent:** Not granted.

---

# B. Membership System decisions

## M-001 — Entitlement runtime schema

**State:** Researching  
Need:
- grant subject (`user`, team/seat, future service principal);
- entitlement key/resource dimensions;
- source/enrollment linkage;
- valid-from/valid-until;
- priority/override semantics;
- metadata/versioning;
- indexes for hot access checks;
- historical audit vs current-state split.

**Executable benchmark required before acceptance:** Yes.

## M-002 — Access rule precedence

Need deterministic behavior for:
- explicit allow vs explicit deny;
- plan-level vs resource-level rule;
- multiple memberships;
- manual complimentary grant;
- admin bypass;
- expired/grace/pending states;
- ancestor/archive/taxonomy restrictions;
- partial-content rule vs whole-resource rule.

Candidate principle:
1. security hard deny;
2. explicit resource deny;
3. explicit resource grant;
4. inherited/plan grant;
5. default policy.

This is **not accepted yet**. Produce a truth-table before ADR acceptance.

**Executable spike required:** No for semantic decision; tests will be required after development consent.

## M-003 — Membership cache/invalidation

Need:
- cache unit (user/plan/resource/policy version);
- object-cache compatibility;
- request-local memoization;
- invalidation on enrollment/plan/rule/role/team changes;
- no stale authorization after revocation;
- cache stampede handling.

Security rule: cached access may optimize a policy decision but must not create a weaker authorization model.

**Executable load/concurrency evidence required:** Yes.

## M-004 — Enrollment state machine

Need accepted transition graph for:
- draft/pending;
- trialing;
- active;
- grace;
- paused;
- canceled;
- expired;
- revoked;
- payment-failed/source-disputed states where applicable.

Must define:
- allowed transitions;
- effective timestamps;
- idempotency;
- late/out-of-order billing events;
- manual override precedence;
- reactivation;
- audit invariants.

Semantic graph can be accepted without executable code after review.

## M-005 — Protected file delivery

Need deployment-safe designs for:
- Apache;
- Nginx;
- generic PHP streaming fallback;
- CDN/offload providers;
- signed expiring URLs;
- Range requests;
- cache-control;
- original uploads vs protected copies;
- filename/content-disposition;
- large files;
- object storage.

Must not claim protection if files remain directly guessable at a public origin URL.

**Executable environment evidence:** Required before provider support claims.

## M-006 — Billing adapter v1

Need select first supported source adapters based on market/value/support cost.

Candidates:
- WooCommerce + WooCommerce Subscriptions;
- SureCart;
- Stripe-direct only if WPEssential intentionally owns more billing responsibility;
- manual/admin grants remain provider-free baseline.

Rule: Membership remains source of access state; provider IDs/events map into enrollment/billing references.

Need:
- webhook signature verification;
- reconciliation job;
- event idempotency;
- refunds/disputes/cancellations;
- retry/out-of-order events;
- plan/product mapping;
- currency/proration ownership.

**Executable integration evidence:** Required before adapter is marketed supported.

## M-007 — Upgrade/downgrade semantics

Need explicit choices for:
- immediate vs renewal-effective;
- carry-over trial;
- entitlement overlap;
- grace during transition;
- external provider proration vs WPEssential access effective time;
- group exclusivity;
- scheduled downgrade cancellation.

Billing math should remain provider responsibility where possible; WPEssential controls access effective timestamps.

## M-008 — Teams/seats

Need:
- owner/admin/member roles inside a membership team;
- seat count and reserved invitation semantics;
- concurrent invite/accept races;
- transfer seat;
- member removal;
- enrollment expiry cascading;
- per-seat entitlement materialization vs derived checks;
- audit/privacy.

**Concurrency test evidence:** Required before release.

## M-009 — Role sync conflicts

Need deterministic behavior when:
- WPEssential adds a role and another plugin removes it;
- enrollment expires while role was manually assigned later;
- user has multiple membership-mapped roles;
- administrator/system roles are involved;
- rollback/reconciliation runs.

Default direction remains: role sync **off**; memberships are not represented solely by roles.

## M-010 — Privacy/retention/export/erasure

Need classify:
- enrollment history;
- billing provider references;
- webhook logs;
- IP/device/security logs;
- invitation records;
- team membership;
- protected-download logs;
- audit events.

Need define:
- retention defaults;
- WordPress privacy exporter/eraser integration;
- legally/operationally required retained audit data;
- anonymization vs deletion.

---

# C. Module-specific decision queue by suite

## Content Model

- field schema versioning and migration language;
- relation storage/cardinality transactions;
- post-status compatibility and domain-state separation;
- third-party CPT/taxonomy override ownership.

## Data & Query

- Query AST v1;
- compiler/provider extension contract;
- safe public query cost budget;
- custom-table migration planner;
- list-table adapter API;
- renderer/listing schema v1.

## Admin & Identity

- admin menu profile conflict precedence;
- frontend route/authorization schema;
- protected user-meta denylist;
- Component Blueprint v1;
- role anti-lockout/recovery.

## Automation & Communication

- Workflow graph/schema v1;
- form entry storage and retention;
- job idempotency/cancellation;
- notification persistence/deduplication;
- email rendering schema;
- chat storage/index/attachment model.

## Integration & Data Movement

- REST endpoint definition schema;
- OAuth connection adapter contract;
- SSRF/DNS/redirect protections;
- import mapping/transformation language;
- package compatibility/version negotiation.

## Operations & Protection

- backup manifest/archive format;
- restore verification contract;
- reset dependency/restore-point contract;
- password protection session model;
- trusted proxy/IP policy;
- watermark derivative naming and regeneration;
- XML-RPC compatibility presets.

---

# D. Decision-processing rule

For each decision:

1. inspect current project docs/ADRs;
2. research current primary sources if external behavior matters;
3. write alternatives and tradeoffs;
4. identify data/security/performance/compatibility impact;
5. determine whether static evidence is enough;
6. if executable evidence is required, prepare a spike proposal but **do not execute it without owner consent**;
7. record the final decision in an ADR/specification;
8. update the readiness matrix and checkpoint.

# Current next planning actions

Without development consent, the safest useful work is:

1. produce static compatibility/toolchain research updates for D-001/D-002/D-008;
2. design candidate Definition Repository schemas on paper for D-004;
3. produce Job Service interface/state model on paper for D-003;
4. complete the Secrets Vault threat model for D-005;
5. define Free↔Pro compatibility state machine for D-006;
6. create Membership access-precedence truth table and enrollment transition table for M-002/M-004;
7. prepare, but do not execute, the future benchmark/spike protocols requiring consent.
