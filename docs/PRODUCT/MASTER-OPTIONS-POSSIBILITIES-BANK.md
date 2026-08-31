# WPEssential — Master Options & Possibilities Bank

Status: **canonical discovery/product-knowledge layer**  
Version: **v1**  
Snapshot baseline: **2026-08-31**

## 1. Purpose

WPEssential must not discover product options reactively while a module is already being implemented. The Master Options & Possibilities Bank is the platform-wide knowledge layer that records what is technically possible, what WordPress natively exposes, what credible competitors expose, what can be delivered through providers/adapters, and what WPE can improve beyond current market behavior.

The Bank is intentionally broader than the implementation roadmap.

> **Capture broadly. Classify rigorously. Implement selectively. Certify explicitly.**

The Bank is not a promise that every recorded possibility ships in the same release. It is a durable inventory so an option is not forgotten merely because it is deferred, expert-only, provider-backed or intentionally rejected as unsafe.

## 2. Bank vs Option Contract

These are different lifecycle artifacts.

### Master Options & Possibilities Bank
Answers:
- What is possible?
- Where does the possibility come from?
- Which products expose it?
- Is it native, hard, soft, provider-backed, compatibility-only, experimental or unsafe?
- What are its dependencies, risks and likely UX/runtime implications?
- Should WPE consider parity, exceed, defer or reject?

Discovery records may remain `UNREVIEWED`, `DEFERRED` or `REJECTED_UNSAFE` indefinitely while still remaining valuable knowledge.

### Atomic Option Contract
Answers:
- Exactly what will WPE support?
- Which value types/defaults/inheritance rules are authoritative?
- What UI tier/control exposes it?
- What is stored and where?
- Which Ability/Policy/runtime path owns it?
- What are the required tests and compatibility guarantees?

Only reviewed Bank entries selected for implementation become Atomic Option Contract records.

## 3. Mandatory sources

Every surface is researched from four independent directions.

### A. Native platform possibilities
Primary sources include:
- current WordPress core public APIs and classes;
- Gutenberg/block APIs where relevant;
- WordPress REST API;
- metadata/options/users/terms/media APIs;
- multisite APIs;
- Cron/Action Scheduler boundaries where adopted;
- supported PHP/database/browser primitives.

Native public options are never silently omitted. Each is classified as supported, provider/extension-only, deferred, internal/not applicable or rejected with reason.

### B. Market possibilities
Use current official documentation from credible market leaders. Platform-wide benchmarks include:
- JetEngine/Crocoblock;
- ACF PRO;
- Secure Custom Fields;
- Meta Box AIO;
- CPT UI/CPTUI Extended;
- Redux Framework;
- Pods/Toolset where relevant.

Use specialist leaders for specialist surfaces: forms, search/facets, membership, booking, admin columns, import/export, backup, security, staging, redirects, snippets, analytics, roles and other categories.

A competitor screenshot is useful for UX observation but not sufficient evidence for a capability claim by itself.

### C. Compatibility possibilities
Record migration/import/interoperability possibilities separately from runtime product features. Examples:
- import ACF/SCF field groups;
- import CPT UI post types/taxonomies;
- map Meta Box/JetEngine field definitions;
- import Redux settings schemas;
- preserve external registration keys.

### D. WPE exceed possibilities
The Bank must include capabilities enabled by WPE architecture even when they are uncommon in competitors, for example:
- effective native-args previews;
- definition revisions and diff;
- dependency graph and impact analysis;
- preflight/collision diagnostics;
- typed Query AST;
- typed relation pivot schema;
- migration preview/rollback strategy;
- provider registry instead of arbitrary executable text;
- Policy/Ability parity across UI, REST and AI;
- portability classification;
- performance-cost diagnostics;
- AI-readable schemas without exposing private values.

## 4. Hard / soft model

The Bank classifies implementation nature explicitly.

### `NATIVE_HARD`
A first-class native WordPress/platform setting or behavior that materially changes runtime semantics.

Examples: `register_post_type.public`, taxonomy `rewrite`, REST namespace/base, native capabilities, field storage type.

### `WPE_HARD`
A first-class WPE runtime/data capability that WPE must own rather than merely presenting a convenience around another option.

Examples: relation pivot storage, Query AST, migration engine, definition revisions.

### `SOFT_NATIVE`
UX/preset/diagnostic behavior built around native settings without creating an independent source of runtime truth.

Examples: auto-generated labels, URL preview, inherited-default badge, public-content preset.

### `PROVIDER_SOFT`
A capability whose implementation depends on a provider/adapter but whose configuration belongs in WPE.

Examples: Mapbox geocoder, Stripe gateway, SMTP/API email provider, OpenAI model provider, remote storage.

### `COMPATIBILITY`
Migration/interoperability/translation behavior for third-party ecosystems.

### `EXPERT`
Advanced developer-facing capability that is valid but should not appear as default product complexity.

### `EXPERIMENTAL`
Technically viable or emerging behavior that remains non-core until evidence/UX/runtime contracts mature.

### `DEFERRED`
A useful known possibility intentionally not scheduled yet.

### `REJECTED_UNSAFE`
A market behavior WPE intentionally does not replicate by the same mechanism because it violates security/data/architecture requirements. The legitimate use case should be preserved through a safer mechanism when practical.

### `WPE_EXCEED`
A WPE-specific differentiator that materially exceeds ordinary competitor behavior.

`hard_soft` is a separate dimension (`HARD`, `SOFT`, `HYBRID`) because a market classification and an implementation nature are not the same thing.

## 5. Adoption decision model

Every Bank record eventually receives one of:
- `UNREVIEWED`;
- `MUST_HAVE` — required for credible product baseline/native completeness;
- `PARITY` — required to meet a credible competitor floor;
- `COMPETITIVE` — valuable competitive capability but not mandatory baseline;
- `WPE_EXCEED` — planned differentiator;
- `PROVIDER` — valid only with a registered provider/adapter;
- `EXPERT_ONLY`;
- `LATER`;
- `REJECT`.

Priorities:
- `P0_NATIVE` — native correctness/completeness;
- `P0_PARITY` — market-blocking parity;
- `P1_CORE` — core product depth;
- `P1_EXCEED` — high-value WPE differentiator;
- `P2_COMPETITIVE`;
- `P3_LATER`;
- `NOT_SCHEDULED`.

Bank capture is not blocked by priority.

## 6. Required Bank metadata

Each normalized record should capture, where applicable:
- stable record ID;
- surface ID/key and owning module;
- feature group and option path;
- label/description;
- parent/child relationship;
- classification and hard/soft nature;
- requiredness;
- source type and authoritative API key;
- competitor products/capabilities/official evidence;
- market prevalence (`COMMON`, `SPECIALIST`, `NICHE`, `EMERGING`, `UNKNOWN`);
- adoption decision/priority;
- value model and allowed values;
- defaults/inheritance;
- dependencies/conflicts;
- suggested UX tier/control/progressive-disclosure rule;
- validation/sanitization/collision checks;
- storage/data-owner implications;
- runtime side effects;
- migration/destructive impact;
- security/capability class;
- performance/scaling class;
- multisite scope;
- import/export/environment sensitivity;
- required tests/evidence;
- compatibility adapters;
- WPE exceed opportunity;
- research status/freshness.

A field may be `UNKNOWN` during discovery. Unknown is preferable to fabricated certainty.

## 7. Hierarchical option trees

A capability family is never treated as one checkbox when meaningful child behavior exists.

Example: `Repeater` must separately inventory at least:
- subfield schema;
- minimum/maximum rows;
- default rows;
- row layout;
- row label/title source;
- collapsed field;
- sortable/reorder;
- clone/duplicate row;
- add/remove controls;
- nested repeater/group behavior;
- maximum nesting/depth guard;
- conditional logic;
- validation;
- storage strategy;
- REST representation;
- revisions;
- import/export;
- query/index behavior;
- large-dataset pagination/virtualization/performance warnings.

Likewise `REST`, `Membership`, `Booking`, `Search`, `Backup`, `Query`, `Typography`, `Relations`, `Import` and other broad labels must become option trees.

## 8. Possibility horizon

The Bank records three horizons.

### `CURRENT_NATIVE`
Possible through current supported WordPress/platform APIs.

### `CURRENT_MARKET`
Currently implemented by a credible competitor/provider.

### `WPE_FUTURE`
Architecturally possible and strategically useful even when uncommon in the market.

Future ideas are not product commitments until adopted by contract.

## 9. Research and freshness

Every market/native claim should include official evidence where practical and a snapshot date.

Research states:
- `UNVERIFIED`;
- `PRIMARY_SOURCE_VERIFIED`;
- `MULTI_SOURCE_VERIFIED`;
- `STALE_REVIEW_REQUIRED`.

Before a record is promoted to `MUST_HAVE`, `PARITY` or `WPE_EXCEED` for implementation, its relevant external research must be refreshed if stale.

## 10. Workflow

Canonical product flow becomes:

```text
Native API research
  + competitor research
  + existing atomic inventory
  + WPE architecture possibilities
        ↓
MASTER OPTIONS & POSSIBILITIES BANK
        ↓ classification/review
Adoption decision + priority
        ↓
Atomic Option Contract
        ↓
UX Contract / progressive disclosure
        ↓
Architecture/runtime design
        ↓
Milestone implementation
        ↓
Runtime + parity evidence
        ↓
PRODUCT_PARITY_CERTIFIED
```

Implementation MUST NOT become the discovery mechanism for missing product options.

## 11. Completion states

Bank states are independent from runtime/product certification.

- `BANK_SCHEMA_READY` — canonical Bank schema exists.
- `BANK_SURFACE_SEEDED` — existing atomic inventory has been normalized into Bank records for that surface.
- `BANK_MARKET_RESEARCHED` — native/market evidence has been reviewed for the surface.
- `BANK_CLASSIFIED` — records have adoption decision/priority/classification.
- `BANK_REVIEWED` — missing/unclassified/duplicate review complete for the current snapshot.
- `OPTION_CONTRACT_COMPLETE` — selected records have schema-valid implementation contracts.
- `PRODUCT_PARITY_CERTIFIED` — implemented behavior has runtime + parity evidence.

A surface can have a reviewed Bank while intentionally leaving many possibilities deferred/rejected.

## 12. Anti-bloat rule

WPE is not improved by exposing every possible option in the default UI.

The Bank is deliberately exhaustive; the product UI is deliberately selective.

The UX contract uses:
- Essential;
- Advanced;
- Expert;
- provider-specific;
- diagnostics/preview;
- conditional reveal;
- presets;
- search.

Exhaustive capability must coexist with low cognitive load.

## 13. Anti-copy rule

WPE may benchmark competitor capability and UX patterns, but must not copy proprietary source code, assets or distinctive visual identity. Product parity means solving the user problem at equivalent or better depth.

## 14. Current seed

The six existing atomic inventory documents covering all 56 canonical surfaces are the initial seed corpus. They are not automatically considered a fully researched/classified Bank.

The next stage normalizes those inventories into machine-readable Bank records, enriches them with native/market evidence, then promotes selected records into Atomic Option Contracts.
