# Master Options & Possibilities Bank — Milestone 1

Status: implementation-planning foundation

## Scope

This milestone establishes the canonical Master Options & Possibilities Bank discovery layer and seeds the first two WordPress-native product surfaces.

Included:
- canonical Bank charter and discovery-vs-contract separation;
- lean machine-readable Bank schema;
- shardable per-surface Bank files for large modules;
- CPT seed covering registration arguments, current labels, capability-map keys, explicit autosave/support extras, internal-only exclusions, compatibility and WPE soft/exceed ideas;
- Taxonomy seed covering registration arguments, current labels, capability-map keys, provider-only callback boundaries, internal-only exclusions, compatibility and WPE soft/exceed ideas;
- machine progress registry for all 56 canonical surfaces;
- smoke validation for Bank shard invariants and progress truth;
- Composer fast/smoke integration.

## Current truthful Bank state

- target canonical surfaces: 56;
- seeded surfaces: 2;
- current CPT Bank records: 107;
- current Taxonomy Bank records: 71;
- total normalized Bank records: 178;
- NATIVE_AUDITED surfaces: 0/56;
- MARKET_AUDITED surfaces: 0/56;
- BANK_REVIEWED surfaces: 0/56.

`BANK_SURFACE_SEEDED` is intentionally weaker than `NATIVE_AUDITED`. Current WordPress source material has been used to deepen these seeds, but a final exhaustive native cross-check plus competitor-by-competitor evidence pass is still required before promotion.

## Next milestone

Continue Bank normalization in dependency order:

1. Fields / Field Groups / control registry;
2. Relations;
3. Query Builder;
4. Custom Tables;
5. Settings / Options Pages;
6. then the remaining canonical surfaces.

For each surface use the lifecycle:

`UNSEEDED → BANK_SURFACE_SEEDED → NATIVE_AUDITED → MARKET_AUDITED → BANK_REVIEWED → Atomic Option Contract → UX Contract → Implementation`.

No implementation-completeness or product-parity claim is implied by this milestone.
