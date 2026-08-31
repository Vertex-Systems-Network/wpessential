# ADR-0137 — Dynamic Listings Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP20`

## Decision

Accept `docs/QUALITY/DYNAMIC-LISTINGS-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for Dynamic Listings / Template Builder.

The protocol freezes **DL-01…DL-176** and preserves ADR-0099's **DL1 — authorization-aware Query + batched hydration + Component Blueprint SSR** as the first operational baseline to test.

## Accepted truth boundary

The following remain separate:

`Listing Definition ≠ Published Listing Revision ≠ Compiled Listing Descriptor ≠ Query capability ≠ candidate result set ≠ authorized visible result set ≠ count/cursor metadata ≠ rendered HTML ≠ cache artifact ≠ client transition state ≠ certified runtime behavior`

Visual output alone proves none of secure pagination, truthful counts, cache safety, no-JS parity, action authorization or builder compatibility.

## Authorization strategies

The evidence protocol recognizes:

- `DL-A1` — authorization pushed into source Query, preferred;
- `DL-A2` — bounded candidate query + server authorization/refill with truthful metadata, evidence-gated;
- `DL-A3` — secure pageable context unsupported and therefore blocked/degraded.

No strategy is runtime-certified by this ADR.

## Capability certifications

Certify independently:

- `DL-R` SSR/render;
- `DL-A` authorization;
- `DL-P` pagination/count/cursor;
- `DL-F` filters/search/facets;
- `DL-H` batched hydration/nesting;
- `DL-C` cache safety/invalidation;
- `DL-I` progressive interaction/history/no-JS parity;
- `DL-B` block/shortcode/builder embeds;
- `DL-S` SEO/accessibility;
- `DL-M` Multisite/scope safety;
- `DL-O` performance/observability.

## Fixed evidence coverage

- definition/publish/dependency truth — DL-01…DL-16;
- SSR/bindings/output security — DL-17…DL-40;
- Query parameters/filters/search/URL state — DL-41…DL-64;
- authorization/visible-result/count truth — DL-65…DL-88;
- pagination/cursors/mutation-between-pages — DL-89…DL-112;
- batched hydration/nesting/budgets — DL-113…DL-132;
- cache classes/keys/invalidation — DL-133…DL-152;
- progressive enhancement/embeds/SEO/accessibility — DL-153…DL-168;
- Multisite/lifecycle/scale — DL-169…DL-176.

## Accepted invariants

1. Draft Listing/Query/Template definitions are not public runtime inputs.
2. Published callers use typed parameters only; no raw SQL/PHP/JS or arbitrary request Query shapes.
3. Authorization and visible-result semantics precede truthful final page/count/cursor exposure.
4. DL-A2 post-filter/refill is bounded; inability to preserve truth becomes unsupported, not silent approximation.
5. Hydration is grouped/batched and nested listings have depth/result/query budgets.
6. Public shared cache is only for genuinely public deterministic output; protected cache includes access/scope generations.
7. stale-while-revalidate is disallowed where revoked access must fail closed.
8. client transitions use the same Listing/Query/Policy contract as SSR.
9. action visibility is not authorization; invocation rechecks Policy/Ability contracts.
10. builder adapters reference the canonical Listing definition rather than owning competing schemas where the integration permits.
11. SEO/indexability and enhanced-navigation claims require usable SSR/direct-link/no-JS semantics appropriate to the advertised mode.
12. Multisite site/network scope is server-resolved and participates in result/cache/link identity.

## Current evidence state

- DL documented: **176**.
- DL executed: **0/176**.
- `DL-A1/DL-A2/DL-A3` strategy certifications: **0**.
- `DL-R/DL-A/DL-P/DL-F/DL-H/DL-C/DL-I/DL-B/DL-S/DL-M/DL-O` certifications: **0**.
- WordPress Interactivity API certification: **OPEN**.
- builder adapter runtime certifications: **0**.
- exact cache store/TTL/invalidation thresholds: **OPEN**.
- exact nesting/refill/performance budgets: **OPEN**.

## Rejected shortcuts

- fetch protected rows then hide them while exposing source totals;
- client-only filtering/conditions used as security;
- unbounded authorization refill;
- per-item Query/Relation/remote N+1 default;
- personalized output under public cache key;
- stale protected cache after access revocation;
- arbitrary public field/sort/projection/AST input;
- builder-specific duplicate canonical Listing schemas;
- silent provider fallback when pagination/filter/sort semantics are unsupported.

## Development gate

No renderer, Query execution, block/shortcode, REST/Interactivity transition endpoint, cache, builder adapter, browser test or benchmark is authorized by this ADR.

ADR-0014 and the Approval Ledger still require explicit scoped owner consent before executable evidence or implementation.

Current execution count remains **0/176**.