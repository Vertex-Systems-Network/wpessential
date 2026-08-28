# ADR-0205 — Geospatial & Territory Executable Evidence Protocol

Date: **2026-08-29**  
Status: **Accepted — planning/evidence only**

## Context

ADR-0177 reserved F11 — Geospatial & Territory as a universal foundation and ADR-0180 fixed its technical evidence envelope as `GEO-001…GEO-176` across 16 groups. WP73 required that group-level envelope to be expanded into deterministic executable-evidence fixtures before any implementation or runtime-certification claim.

WPEssential must not confuse geocoded coordinates with verified address/identity truth, spatial matches with authorization or serviceability, territory assignment with entitlement/jurisdiction, provider confidence with certainty, or routing estimates with guaranteed travel/service outcomes. Precise-location data also requires explicit privacy, consent, retention and Multisite isolation evidence.

## Decision

Accept `docs/QUALITY/GEOSPATIAL-TERRITORY-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical detailed evidence protocol for F11. Its group ownership is aligned exactly with `docs/QUALITY/UNIVERSAL-FOUNDATIONS-TECHNICAL-EVIDENCE-MASTER-PLAN.md`.

The protocol fully enumerates:

- `GEO-001…011` location/address/coordinate/territory schema;
- `GEO-012…022` geocoder provider/mapping/confidence/provenance;
- `GEO-023…033` coordinate validation/normalization/precision;
- `GEO-034…044` radius/distance/bounding-box semantics;
- `GEO-045…055` polygon/zone containment/boundaries/holes;
- `GEO-056…066` territory hierarchy/overlap/priority/assignment;
- `GEO-067…077` spatial query backend/capability fallback;
- `GEO-078…088` caching/geocoder terms/freshness/revalidation;
- `GEO-089…099` privacy/precise location/retention/redaction;
- `GEO-100…110` external routing/matrix provider unknown outcome/limits;
- `GEO-111…121` import/export coordinate systems/invalid geometry;
- `GEO-122…132` Policy/site/tenant-protected locations;
- `GEO-133…143` Multisite/network territories/site lifecycle;
- `GEO-144…154` provider/version/data-source drift;
- `GEO-155…165` large spatial dataset/query performance;
- `GEO-166…176` delivery/service-area/real-estate/fleet golden regression.

Restore/clone/provider remapping remains a cross-foundation environment-safety boundary, but it does not repurpose the canonical `GEO-144…154` namespace.

## Non-negotiable boundaries accepted

- Geocoded coordinate is not verified physical identity/address truth by default.
- Provider confidence is not certainty.
- Spatial match/territory assignment is not authorization, entitlement or legal jurisdiction.
- Bounding-box match is not polygon containment; polygon containment is not guaranteed serviceability.
- Straight-line distance is not travel distance/time; route/matrix estimate is not a delivery or travel guarantee.
- Coordinate precision, CRS, axis order, algorithm/model and provenance are explicit.
- Precise location remains consent/Policy/retention/redaction governed.
- Unknown geocoder/routing/provider outcome is not automatically failed.
- Provider credentials remain Vault-owned; provider terms/cache/licensing constraints remain binding.
- Provider/version/data-source drift must surface explicit compatibility/provenance state rather than silent coercion.
- Multisite/site/tenant ownership is server-resolved and isolated.
- Restore/clone/staging cannot blindly reuse production provider tokens/cache/mappings/private-location authority.
- F10 synchronized location data keeps its source provenance; synchronization does not automatically transfer authority to F11.
- AI/MCP has no hidden privileged precise-location/provider/territory path.

## Evidence state

- GEO documented: **176/176**
- GEO executed: **0/176**
- F11 runtime certification: **0**
- Product implementation authorization: **0/56 / NOT GRANTED**

No geocoder/routing/provider call, spatial DB query, coordinate mutation, territory assignment, cache mutation, precise-location collection, benchmark, test, build or AI/MCP execution occurred while creating or accepting this ADR.

## Consequences

WP73 is complete as a planning/evidence package. The next safe planning package is **WP74 — WooCommerce Commerce Domain Adapter (`WCA-001…WCA-176`)**.

This ADR does not authorize implementation or runtime execution. A later explicitly authorized execution phase must produce reproducible evidence before any GEO fixture can be marked executed or F11 can be runtime-certified.
