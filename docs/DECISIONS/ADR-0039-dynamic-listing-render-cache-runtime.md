# ADR-0039 — Dynamic Listing Render & Cache Runtime

Status: **Accepted architecture / compiler-cache evidence pending**  
Date: 2026-08-27

## Decision

Dynamic Listings use:

**Listing View Definition → Compiled Listing Descriptor → Authorized Query Execution → Visible Result Set → Item Component Blueprint → Server Render → Optional Progressive Enhancement**.

Authorization/filtering must participate before final pagination/count semantics where protected data is involved. Cache safety is derived from Query/Blueprint/data/policy dependencies rather than a manual blanket cache toggle.

## Why

- preserves correct pagination/counts for protected data;
- shares Component Blueprint instead of duplicating item templates;
- keeps SSR/SEO/accessibility baseline;
- prevents public cache leakage of personalized content;
- avoids arbitrary public query arguments.

## Consequences

- exposed filters/sorts map only to typed Query parameters;
- nested listings have depth/N+1 limits;
- builder embeds reference central Listing definitions;
- Load More/infinite scroll enhance the same server contract;
- private/personal caches include access/principal generation or are disabled.

## Evidence still required

After explicit consent: Query compiler integration, private pagination correctness, cache isolation/invalidation, enhanced navigation/accessibility, nested N+1, builder adapters and large datasets.

Supporting doc: `docs/ARCHITECTURE/DYNAMIC-LISTING-RENDER-CACHE-RUNTIME.md`.