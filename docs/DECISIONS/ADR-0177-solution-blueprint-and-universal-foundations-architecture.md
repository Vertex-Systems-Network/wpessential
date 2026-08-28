# ADR-0177 — Solution Blueprint Layer & Universal Foundation Architecture

Status: **Accepted planning architecture / expanded product scope / executable evidence pending**  
Date: 2026-08-28

## Context

The owner supplied a 32-page WooCommerce AI-Native Commerce OS planning document and requested a deep audit against WPEssential, plus pre-planning for 100+ / 100K+ WordPress systems across arbitrary domains and programmer workflows.

The source document contains 71 numbered commerce systems and explicitly favors shared event/rule/workflow/permission/audit/design/analytics foundations rather than disconnected mini-plugins.

WPEssential already follows the same architectural direction: reusable schemas, relations, queries, renderers, policies, abilities, workflows, jobs, integrations and operational services.

Creating a dedicated WPE module/runtime for every CRM, helpdesk, booking system, directory, commerce feature, LMS, HR system or industry application would violate the existing architecture and create duplicated engines.

## Decision 1 — System is not a module by default

Accept a first-class **Solution Blueprint Layer**.

A business/application system is normally a composition of canonical WPE definitions and shared services.

Create a new user-facing module only when a missing capability:
1. cannot be safely represented by existing primitives;
2. is reusable across multiple unrelated domains;
3. needs one canonical runtime/ownership model;
4. would otherwise be reimplemented inconsistently in many Blueprints.

Provider/domain-specific behavior belongs in an adapter unless it is genuinely universal.

## Decision 2 — Three-layer model

Accept:

### Layer A — Platform primitives
The original WPE module/shared-service architecture remains the foundation.

### Layer B — Universal reusable foundations
Accept these 12 expanded product foundations into Phase 0 product scope:

1. **Solution Blueprint & Application Composer**
2. **Analytics, Event Tracking & Journey Intelligence**
3. **Search & Indexing Engine**
4. **Decision, Formula, Scoring & Ranking Studio**
5. **Ledger, Balance & Movement Engine**
6. **Resource Scheduling, Availability & Reservation Engine**
7. **Experience Placement & Personalization Manager**
8. **Experimentation & Feature Rollout Manager**
9. **Documents, Records & Template Generation**
10. **Data Sync, ETL & Integration Pipelines**
11. **Geospatial, Location & Territory Engine**
12. **AI Gateway, Knowledge & Copilot Studio**

Their screen/option behavior is specified in `docs/SOLUTIONS/UNIVERSAL-FOUNDATIONS-EXHAUSTIVE-SPEC.md`.

### Layer C — Solution Blueprints
Complete user systems compose Layers A/B plus adapters and external authorities.

## Decision 3 — Shared-service enhancements

Accept these as platform-service planning scope rather than sellable modules:

- Simulation & Historical Replay Service;
- Transaction / Saga Coordination Contract;
- Protected Asset Service generalized beyond Membership;
- Context Resolver;
- Money / Decimal / Unit Type Library;
- Approval Policy Profile.

They must extend existing shared contracts rather than establish competing runtimes.

## Decision 4 — WooCommerce is a domain adapter

Accept the **WooCommerce Commerce Domain Adapter** as the first formal domain adapter pack.

Its contract is specified in:

`docs/SOLUTIONS/WOOCOMMERCE-COMMERCE-DOMAIN-ADAPTER-EXHAUSTIVE-SPEC.md`

The adapter exposes supported Woo products, customers, cart, checkout, HPOS orders, stock, shipping/payment eligibility, My Account, placements, events and typed mutation abilities through WPE registries.

It does not replace WooCommerce and does not infer payment settlement, tax/duties, carrier or external subscription truth.

## Decision 5 — 71-system Commerce OS audit

Accept the capability map in:

`docs/SOLUTIONS/COMMERCE-OS-71-SYSTEM-AUDIT.md`

The source document has **71** numbered systems.

Primary audit classification:
- `DIRECT`: **3**;
- `COMPOSABLE_WITH_ADAPTER`: **20**;
- `NEEDS_FOUNDATION`: **46**;
- `EXTERNAL_AUTHORITY_REQUIRED` as primary blocker: **2**.

The 46 foundation-dependent systems do **not** become 46 new modules; they converge on the reusable foundation set above.

## Decision 6 — 100K+ model

Accept the validated compositional system model in:

`docs/SOLUTIONS/100K-SYSTEM-SPACE.md`

Primary grammar:

`20 domains × 40 application patterns × 7 actor models × 8 workflow profiles × 6 experience profiles = 268,800 raw primary combinations`

This is an addressable composition space, **not** a claim of 268,800 implemented or supported products.

Unsafe/meaningless combinations must fail Blueprint validation.

Initial human-curated reference catalog:
- **160 systems** across 20 domains in `UNIVERSAL-SYSTEM-CATALOG.md`.

Reusable detailed flow/option patterns:
- **40** in `REFERENCE-FLOW-AND-OPTION-PATTERNS.md`.

## Decision 7 — Blueprint specification bar

Every curated/generated Blueprint follows:

`docs/SOLUTIONS/SYSTEM-BLUEPRINT-SPECIFICATION-STANDARD.md`

It must document actors, objects, screens/options, roles/Policy, state machines, happy/alternate/failure/recovery flows, automation, communication, reports, integrations, privacy/security, concurrency, lifecycle, AI permissions, module composition and missing foundations/adapters.

AI-generated Blueprints compile into the same deterministic schema and receive no privileged bypass.

## Decision 8 — Expanded product maturity truth

The old statement **31/31 Exhaustive** remains historically correct for the original product scope.

After ADR-0177:
- original surfaces: **31**;
- newly accepted universal foundation surfaces: **12**;
- combined planned module/platform surfaces: **43**;
- product-option behavior documented for new foundations: **12/12**;
- logical Multisite scope behavior mapped for new foundations: **12/12**;
- combined logical scope mapping: **43/43**;
- implementation authorization: **0/43**;
- implementation/runtime verification: **0**.

Technical readiness remains evidence-gated. “Exhaustive” means product behavior planned, not implemented or certified.

## Decision 9 — No 100K codebases

WPE must not generate or maintain one isolated plugin codebase per Solution Blueprint as the normal architecture.

Blueprints install/configure canonical WPE definitions against shared runtimes.

Generated custom source is not required for ordinary Blueprint functionality and remains outside the default no-code product model.

## Decision 10 — External authority boundary

Blueprint validation must mark operations that require external authoritative providers, including where applicable:
- payment authorization/capture/settlement;
- tax/duties determinations;
- carrier labels/live rates/tracking facts;
- legally binding e-signatures;
- identity/KYC verification;
- external subscription billing;
- banking/accounting settlement.

WPE can orchestrate, normalize, explain and reconcile provider facts; it must not fabricate them.

## Preserved safety rules

- deterministic engines execute money/inventory/authorization/high-impact state;
- AI defaults to insight/draft and uses typed Abilities;
- visibility is never authorization;
- Event Bus ≠ behavioral analytics store;
- Audit Log ≠ analytics warehouse;
- Query Builder ≠ search index;
- Cron ≠ reservation lock;
- Custom Table ≠ ledger semantics;
- successful external request ≠ known business outcome;
- Blueprint installation ≠ implementation authorization;
- module disable/expiry never silently destroys business data.

## Development gate

ADR-0177 is a planning/product-architecture decision only.

It authorizes no source code, plugin generation, DB schema, analytics collection, search index, ledger posting, reservation lock, PDF generation, sync execution, geocoder/model/provider call, WooCommerce mutation, compatibility test or executable benchmark.

ADR-0014 explicit scoped owner development consent remains required.