# WPEssential — Foundational Module & Shared-Service Gap Plan

Status: **Phase 0 planning / proposed scope expansion / no development authorization**  
Date: 2026-08-28

## 1. Decision rule

A new module is justified only when all are true:

1. current primitives cannot express the required semantics safely or performantly;
2. the capability appears in multiple unrelated domains;
3. duplicating it inside Solution Blueprints would create inconsistent runtimes;
4. it has a clear ownership boundary;
5. it can reuse the existing Definition/Policy/Ability/Event/Workflow/Job/Vault/Audit architecture.

Commerce-specific behavior should normally become a **domain adapter pack**, not a universal module.

---

# A. Proposed reusable user-facing modules

## F01 — Solution Blueprint & Application Composer

### Why required
Current Import/Export can package definitions, but WPEssential does not yet have a first-class whole-application composition object with dependency resolution, blueprint upgrades, existing-site mapping and solution-level lifecycle.

### Cross-domain uses
CRM, LMS, HR, ERP, helpdesk, directories, booking apps, portals, project systems, WooCommerce solution packs, agency starter systems.

### Core objects
- Solution Blueprint
- Blueprint Version
- Blueprint Dependency
- Blueprint Variable
- Installation Plan
- Installed Solution
- Definition Binding
- Upgrade Plan

### Screens/options
**Blueprints**
- list/search/filter by domain/status/source;
- create/import/duplicate/archive;
- manifest version;
- compatibility range;
- required/optional modules;
- required adapters;
- required capabilities;
- dependency constraints;
- conflict rules;
- edition/license requirements;
- install variables;
- sample-data policy;
- documentation links.

**Composition editor**
- objects/definitions included;
- dependency graph;
- install order;
- mapping rules;
- environment-bound values;
- optional components;
- visibility of generated module definitions;
- ownership/reference mode;
- upgrade strategy.

**Install wizard**
- requirements check;
- existing definition collision detection;
- create/reuse/map/skip strategy;
- dry run;
- permission impact;
- route/menu collision;
- data/schema impact;
- final plan fingerprint;
- install as draft vs activate;
- rollback class.

**Installed Solutions**
- health;
- current version;
- customized definitions;
- drift view;
- upgrade available;
- export fork;
- detach from blueprint;
- disable solution without deleting module data;
- uninstall plan.

### Flow
`Blueprint → Resolve Dependencies → Map Existing Site → Dry Run → Install Definitions → Validate → Activate → Observe → Upgrade/Drift Reconcile`

### MUST NOT
- own private copies of fields/queries/workflows;
- overwrite existing definitions silently;
- treat template installation as development authorization;
- hide destructive migrations inside a version bump.

---

## F02 — Analytics, Event Tracking & Journey Intelligence

### Why required
The Event Bus is operational integration infrastructure; Audit is security/decision history. Neither is a durable behavioral analytics warehouse, visitor/session model, funnel engine or attribution system.

### Cross-domain uses
commerce analytics, LMS learning journeys, SaaS/product analytics, membership engagement, CRM touchpoints, form funnels, content analytics, support SLA analytics, application telemetry.

### Core objects
- Event Definition
- Event Occurrence
- Visitor / Anonymous Principal
- Session
- Journey
- Identity Link
- Metric Definition
- Metric Snapshot
- Funnel
- Cohort
- Attribution Model / Touch

### Screens/options
**Event catalog**
- event key/version/category;
- schema;
- source allowlist;
- PII classification;
- retention;
- sampling;
- dedupe identity;
- consent requirement;
- server/client collection availability.

**Tracking**
- enabled surfaces;
- anonymous/session cookie policy;
- first-party storage;
- consent categories;
- UTM/referrer capture;
- device/browser granularity;
- bot filtering;
- IP handling/minimization;
- cross-device account linking after login;
- do-not-track/global privacy signal policy where applicable.

**Metrics**
- count/sum/average/rate/distinct;
- numerator/denominator;
- dimensions;
- filters;
- time grain;
- freshness;
- late events;
- correction/backfill.

**Funnels/cohorts**
- ordered/unordered steps;
- conversion window;
- exclusion events;
- first/any occurrence;
- segment comparison;
- retention cohorts;
- path exploration bounds.

### Flow
`Collect → Validate Schema/Consent → Normalize → Dedupe → Persist → Identity Link → Aggregate → Query/Funnel → Explain/Alert`

### MUST NOT
- use Audit as behavioral analytics;
- collect unrestricted raw PII by default;
- claim causation from correlation;
- allow client events to authorize server-side actions.

---

## F03 — Search & Indexing Engine

### Why required
Query Builder is a structured data query engine. It does not provide typo tolerance, tokenization, stemming, synonyms, relevance ranking, faceting, zero-result analytics or large-content indexes.

### Cross-domain uses
site search, ecommerce, directories, knowledge bases, documentation, CRM, jobs, real estate, LMS, support, media libraries.

### Core objects
- Search Index
- Search Document Schema
- Index Source
- Analyzer Profile
- Synonym Set
- Ranking Profile
- Search Rule
- Search Log

### Options
- source query/data source;
- indexed fields + weights;
- exact/text/keyword/facet/numeric/date/geo/vector field modes;
- language/analyzer;
- stop words;
- stemming;
- synonyms;
- typo tolerance;
- prefix/autocomplete;
- filters/facets;
- boosts/bury/pins;
- redirects;
- security filter projection;
- incremental/full indexing;
- index schedule;
- backend adapter;
- local DB baseline vs external engine adapter;
- result highlighting;
- zero-result handling;
- search analytics integration.

### MUST NOT
- bypass source Policy when returning indexed results;
- assume remote index is source of truth;
- expose protected fields because they were indexed earlier.

---

## F04 — Decision, Formula, Scoring & Ranking Studio

### Why required
Conditional Logic answers boolean conditions and DVR resolves values. Many universal systems require reusable typed formulas, weighted scores, thresholds, rankings and deterministic decision tables that should not be reimplemented in each form/workflow/commerce feature.

### Cross-domain uses
lead scoring, risk scoring, eligibility, commissions, grades, pricing, shipping rules, SLAs, recommendations, fit scoring, prioritization, procurement, budgets.

### Core objects
- Formula Definition
- Scorecard
- Decision Table
- Ranking Profile
- Threshold Policy
- Evaluation Trace

### Options
- typed variables/data sources;
- decimal/integer/money/percentage/date/duration/unit types;
- arithmetic/comparison/boolean functions;
- bounded string/date functions;
- safe aggregate functions;
- lookup tables;
- piecewise/tiered formulas;
- weights;
- min/max caps;
- rounding mode;
- currency/unit policy;
- missing/null strategy;
- versioning/effective dates;
- simulation datasets;
- conflict/priority rules;
- explain trace;
- public/private evaluation policy.

### Flow
`Inputs → Type/Policy Validation → Deterministic Evaluate → Trace → Threshold/Rank → Consumer Action`

### MUST NOT
- use arbitrary PHP/JS/eval;
- use floating-point shortcuts for monetary correctness where decimal semantics are required;
- let a score itself authorize an action without Policy.

---

## F05 — Ledger, Balance & Movement Engine

### Why required
Custom Tables can store transactions, but wallet credit, loyalty points, inventory movement, quota consumption and financial-like balances need a shared immutable posting model with idempotency, compensating entries and reconciliation.

### Cross-domain uses
wallet/store credit, loyalty, inventory, prepaid quotas, credits, commissions, leave balances, time banks, resource quotas, usage allowances.

### Core objects
- Ledger
- Account
- Entry / Posting
- Transaction
- Balance Snapshot
- Hold / Reservation
- Reconciliation Run

### Options
- single/double-entry profile according domain;
- unit/currency;
- precision;
- account types;
- allowed posting types;
- negative balance policy;
- holds/reservations;
- expiry lots;
- idempotency key;
- source reference;
- reversal/compensation rules;
- settlement/finalization states;
- balance snapshot strategy;
- reconciliation;
- retention/export;
- approval threshold;
- immutable fields.

### Flow
`Request Posting → Policy → Validate Accounts/Unit → Reserve/Commit → Append Entries → Recompute/Verify Balance → Emit Event → Reconcile`

### MUST NOT
- overwrite balances as primary truth;
- delete historical financial/movement rows as ordinary cleanup;
- treat cached balance as canonical.

---

## F06 — Resource Scheduling, Availability & Reservation Engine

### Why required
Cron schedules work; it does not own customer/bookable-resource availability, conflict locking, capacity, slots or reservations.

### Cross-domain uses
appointments, rentals, rooms, equipment, staff shifts, delivery slots, classes, events, facilities, pickup scheduling, interviews.

### Core objects
- Resource
- Resource Group
- Availability Rule
- Calendar
- Slot
- Reservation
- Capacity Pool
- Blackout
- Hold

### Options
- timezone;
- weekly availability;
- date exceptions;
- recurring rules;
- duration;
- buffers;
- lead time;
- booking horizon;
- capacity;
- shared capacity pools;
- min/max participants;
- resource combinations;
- location;
- holds and expiry;
- waitlist;
- overbooking policy;
- approval mode;
- cancellation/reschedule windows;
- conflict strategy;
- external calendar adapters;
- reminder workflows.

### Flow
`Resolve Context → Query Availability → Create Atomic Hold → Collect Required Data/Payment → Confirm → Notify → Reschedule/Cancel → Release Capacity`

### MUST NOT
- use Cron timestamps as reservation locks;
- confirm two overlapping reservations against the same exclusive capacity;
- rely only on browser state for capacity.

---

## F07 — Experience Placement & Personalization Manager

### Why required
Listings and Builder Widgets render content, but WPE lacks a first-class system for registering placements/slots and conditionally injecting components into frontend/admin/application contexts with priority, frequency and experiment bindings.

### Cross-domain uses
banners, popups, notices, recommendations, account widgets, contextual help, personalization, upsells, onboarding, role-specific content.

### Core objects
- Placement Slot
- Experience
- Placement Rule
- Experience Variant
- Frequency State

### Options
- surface: frontend/admin/dashboard/email-capable reference;
- hook/block/template slot adapter;
- before/after/replace where adapter permits;
- component/listing/block/template binding;
- query/context binding;
- priority;
- schedule;
- audience/role/policy conditions;
- page/entity conditions;
- device/market/locale conditions when available;
- frequency cap;
- dismiss state;
- fallback;
- experiment binding;
- asset dependencies;
- SSR/client mode;
- accessibility constraints.

### MUST NOT
- use visibility as authorization;
- globally enqueue assets for every placement;
- mutate proprietary builder documents directly when supported adapter APIs exist.

---

## F08 — Experimentation & Feature Rollout Manager

### Why required
Feature flags exist as infrastructure, but user-facing A/B/multivariate experimentation needs assignment, exposure, metrics and rollout semantics.

### Cross-domain uses
marketing, checkout, forms, dashboards, onboarding, content, recommendations, UI, pricing-display experiments, developer feature rollout.

### Core objects
- Experiment
- Variant
- Assignment
- Exposure
- Metric Link
- Rollout

### Options
- hypothesis;
- target audience;
- allocation;
- deterministic assignment key;
- sticky assignment;
- control/variants;
- start/end;
- primary/guardrail metrics;
- minimum sample/duration;
- exclusions;
- mutual exclusion groups;
- staged rollout percentages;
- kill switch;
- privacy;
- bot/test exclusion;
- result confidence presentation;
- manual decision record.

### MUST NOT
- claim statistical certainty from insufficient data;
- randomize security/authorization rules;
- expose one user to multiple conflicting variants due cache/session errors.

---

## F09 — Documents, Records & Template Generation

### Why required
Forms can upload files and Emails render email; many systems require governed PDFs/documents such as invoices, certificates, reports, letters, contracts, labels and application packs.

### Cross-domain uses
CRM proposals, HR letters, school certificates, clinic reports, invoices, quotes, procurement POs, tickets, labels, membership certificates.

### Core objects
- Document Template
- Document Definition
- Generated Document
- Document Version
- Signature/Approval Reference

### Options
- document type;
- page size/orientation/margins;
- header/footer;
- component schema;
- dynamic data bindings;
- tables/repeaters;
- conditional sections;
- numbering;
- locale/timezone;
- fonts through approved embedding/licensing policy;
- output PDF/HTML/other certified adapters;
- filename;
- storage;
- access policy;
- watermark;
- retention;
- regenerate vs immutable snapshot;
- approval/signature integration;
- email/download/portal delivery.

### MUST NOT
- treat editable template output as legally signed merely because a name image is present;
- expose private generated files through public URLs without applicable protected-asset controls.

---

## F10 — Data Sync, ETL & Integration Pipelines

### Why required
Import/Export handles runs and Webhooks handle events, but repeated incremental/bidirectional synchronization needs cursors, checkpoints, mapping, ownership and conflict resolution.

### Cross-domain uses
CRM sync, ERP sync, product feeds, HR directories, finance exports, inventory sync, learning records, external databases, SaaS integrations.

### Core objects
- Sync Definition
- Source Connector
- Destination Connector
- Mapping
- Cursor/Checkpoint
- Sync Run
- Conflict
- Dead Letter

### Options
- direction one-way/two-way;
- source/destination;
- selection query;
- key mapping;
- transformations through safe expression engine;
- incremental cursor;
- watermark timestamp/version;
- schedule/event trigger;
- create/update/delete policy;
- conflict winner/manual merge;
- field ownership;
- dedupe;
- batching;
- rate limits;
- retry/reconciliation;
- dry run;
- resume;
- error rows;
- privacy filters;
- secrets via Vault.

### MUST NOT
- infer two-way conflict rules silently;
- treat last-write-wins as universally safe;
- store provider secrets in mapping definitions.

---

## F11 — Geospatial, Location & Territory Engine

### Why required
A map field stores coordinates; many systems need radius search, zones, service areas, territories, route/location matching and geospatial policy.

### Cross-domain uses
real estate, stores, delivery, field service, events, healthcare locations, jobs, directories, logistics, sales territories.

### Core objects
- Location
- Address
- Geometry/Area
- Territory
- Service Zone
- Geocode Record

### Options
- address schema by country;
- coordinate precision;
- geocoder adapter;
- reverse geocode;
- map provider;
- radius/distance queries;
- bounding box;
- polygon/service zone;
- point-in-polygon;
- territory hierarchy;
- timezone resolution;
- privacy precision reduction;
- caching;
- provider quota;
- import/export formats;
- distance units.

### MUST NOT
- expose sensitive precise locations when policy allows only coarse geography;
- assume geocoder result is legally authoritative address evidence.

---

## F12 — AI Gateway, Knowledge & Copilot Studio

### Why required
WPEssential is AI-native by architecture, but universal systems need a formal user-facing/provider-neutral control plane for model providers, prompts, knowledge grounding, budgets, permissions, evaluations and action drafts.

### Cross-domain uses
all Solution Blueprints and developer workflows.

### Core objects
- AI Provider Connection
- Model Profile
- Prompt/Instruction Template
- AI Task Definition
- Knowledge Source
- Retrieval Profile
- AI Run
- Evaluation Set
- Usage Budget

### Options
- provider/model;
- purpose/capabilities;
- Vault credentials;
- allowed data classes;
- role/policy;
- read-only/draft/execute classification;
- token/cost limits;
- timeout;
- temperature/structured output where provider supports it;
- tool/Ability allowlist;
- retrieval sources;
- chunk/index profile through Search Engine;
- citations/evidence requirement;
- conversation retention;
- PII minimization;
- prompt versioning;
- eval datasets;
- fallback provider policy;
- human approval threshold;
- rate/budget limits;
- audit.

### Flow
`User Intent → Policy/Context → Retrieve Approved Evidence → Model Structured Draft → Schema Validate → Simulate/Diff → Human/Policy Approval → Deterministic Ability → Audit Outcome`

### MUST NOT
- give models raw arbitrary PHP/SQL execution;
- expose Vault secrets as prompt context;
- allow AI output to bypass the owning module's Policy/Ability validation.

---

# B. Shared-service enhancements — not sellable modules

## S01 — Simulation & Historical Replay Service

Needed by rules, workflows, commerce decisions, migrations and diagnostics.

Capabilities:
- immutable input context snapshot;
- draft vs published comparison;
- no-write evaluation mode;
- historical event replay where data exists;
- expected side-effect preview;
- diff trace;
- deterministic seed/time controls where applicable;
- safe synthetic fixture support.

## S02 — Transaction / Saga Coordination Contract

Workflow already supports compensating actions conceptually. Cross-domain high-impact operations require a formal contract for:
- atomic local transactions where possible;
- multi-resource saga steps;
- idempotency;
- unknown external outcome;
- compensation;
- reconciliation;
- durable checkpoint;
- recovery state.

This service does not pretend distributed transactions exist across third-party providers.

## S03 — Protected Asset Service Generalization

Membership protected-file architecture should be generalized so HR, legal, education, support and private portals can protect files using Policy, not only Membership.

Membership becomes one Policy/Entitlement source; Protected Asset remains a reusable storage/delivery service.

## S04 — Context Resolver

Central typed runtime context for:
- principal;
- site/network;
- request;
- locale;
- timezone;
- device class;
- visitor/session;
- organization;
- market;
- location/territory;
- selected resource.

Consumers use explicit context keys rather than inventing module-specific globals.

## S05 — Money / Decimal / Unit Type Library

Shared correctness primitives for formulas, ledger, commerce adapters and reporting:
- decimal arithmetic;
- currency + minor-unit metadata;
- rounding modes;
- percentage;
- duration;
- measurement units;
- conversion adapters;
- formatting separated from canonical value.

## S06 — Approval Policy Profile

Workflow manual approvals exist; add reusable approval-policy definitions:
- threshold;
- approver query/role/relation;
- sequential/parallel;
- quorum;
- timeout;
- delegation;
- escalation;
- re-auth requirement;
- immutable decision evidence.

---

# C. Domain adapter packs

## A01 — WooCommerce Commerce Domain Adapter Pack — required for the uploaded Commerce OS plan

This is **not** a generic WPEssential module. It is a certified adapter package exposing WooCommerce through WPEssential registries.

### Required surfaces
- Product / Variation Data Sources;
- Customer / Order Data Sources;
- HPOS-safe order access;
- cart/session context;
- cart line operations;
- checkout fields/context;
- coupon/discount integration points;
- shipping method eligibility/rates extension points;
- payment gateway availability extension points;
- order create/edit/refund abilities only where safe;
- stock read/write/reservation integration;
- order/item/product events;
- Cart/Checkout Blocks integration;
- classic template compatibility where supported;
- My Account endpoints/placements;
- product/archive/cart/checkout/thank-you placement slots;
- webhook/provider mapping;
- capability/Policy boundaries;
- transactional/idempotency semantics;
- diagnostics/explain hooks.

### Rules
- no direct dependency on legacy order-post storage;
- no private Woo DB assumptions outside public/supported APIs;
- exact mutation abilities are risk-classified;
- payment settlement remains payment-provider authority;
- tax/legal calculations remain Woo/authoritative provider contracts;
- adapter certification is version/profile scoped.

## A02 — Generic Commerce Provider Adapter Family

Future Shopify/headless/ERP commerce sources can implement the same WPE domain contracts where semantics genuinely map. Do not force incompatible platforms into Woo-specific assumptions.

## A03 — Calendar Provider Adapters

Google/Microsoft/CalDAV/etc. remain adapters to Resource Scheduling; provider calendar does not become WPE reservation truth unless the selected profile explicitly delegates authority.

## A04 — E-signature Provider Adapters

Document module may integrate DocuSign/Adobe Sign/etc. through Connection adapters. WPE must not invent legal-signature guarantees.

## A05 — Tax / Duties / Carrier / Payment authoritative adapters

WPE may configure, route and explain provider outcomes but should not fabricate regulated or provider-owned facts.

---

# D. What does NOT need a new foundational module

These common systems can be Solution Blueprints over existing primitives:
- CRM contacts/leads/opportunities;
- helpdesk/ticketing;
- project/task trackers;
- job boards;
- directories;
- real-estate listing/lead portals;
- application/admissions systems;
- supplier records;
- quote/deal workflows;
- approval workflows;
- customer/vendor portals;
- knowledge bases once Search is available for advanced retrieval;
- asset registers;
- compliance registers;
- case management;
- surveys/intake;
- review/Q&A systems;
- employee/student/member profiles;
- onboarding/offboarding;
- basic LMS course/enrollment tracking;
- document request workflows;
- issue/exception queues.

They use CPT/Tables/Fields/Relations/Status/Query/Listings/Forms/Workflow/Dashboard/Roles/Notification/API instead of receiving dedicated runtimes.

---

# E. Scope impact

The previous **31/31 Exhaustive** statement remains true for the original 31 surfaces, but it no longer describes the entire expanded product scope if F01–F12 are accepted as product modules.

Until their exhaustive specs and ADR treatment are synchronized, report truth as:

- original surfaces: **31/31 Exhaustive**;
- proposed universal foundation modules: **12 planned candidates**;
- shared-service enhancements: **6 planned**;
- first domain adapter pack: **WooCommerce Commerce Adapter planned**;
- development authorization: **0**.

No new candidate becomes production scope merely by appearing in this planning document; Product Master Plan/Module Catalog/Option Maturity must be explicitly synchronized after the gap audit is accepted.