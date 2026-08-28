# WPEssential — Geospatial & Territory Executable Evidence Protocol

Status: **Accepted evidence design candidate / execution pending / no development authorization**  
Date: **2026-08-29**

Namespace: **GEO-001…GEO-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## 1. Purpose

This protocol fixes the executable evidence required before F11 — Geospatial & Territory can be called runtime-ready.

F11 owns typed location/address/coordinate/territory definitions, geocoding provenance, deterministic spatial evaluation, territory assignment logic, privacy-safe precise-location handling and bounded routing/matrix integration for explicitly configured geospatial profiles. It does not become identity, address verification, authorization, serviceability, legal-jurisdiction, logistics or provider truth merely because a coordinate, polygon, route or territory matched.

No fixture below has executed. No geocoder/routing/provider call, spatial DB query, coordinate mutation, territory assignment, cache mutation, precise-location collection, AI/MCP call, benchmark, build, test or runtime mutation is authorized by this protocol.

## 2. Non-negotiable truth boundaries

- `Geocoded coordinate ≠ verified physical identity/address truth` unless the explicit verification profile and authority establish that fact.
- `Provider confidence ≠ certainty`; score semantics remain provider/profile specific.
- `Spatial match ≠ authorization`; canonical resource/action Policy remains authoritative.
- `Territory assignment ≠ entitlement, ownership or legal jurisdiction` unless an explicit canonical domain contract says so.
- `Bounding-box match ≠ polygon containment`.
- `Polygon containment ≠ guaranteed serviceability`.
- `Straight-line distance ≠ travel distance/time`.
- `Routing/matrix estimate ≠ guaranteed travel time, delivery promise or serviceability`.
- Unknown geocoder/routing/provider outcome is not automatically failed; reconcile/retry according to provider-safe semantics.
- Coordinate precision, source and CRS/axis order are explicit; silent coordinate-system assumptions are prohibited.
- Precise location is privacy-sensitive and remains consent/Policy/retention/redaction governed.
- Provider credentials remain Vault-owned; provider/cache/licensing/terms constraints are part of the evidence contract.
- Request-provided site/tenant/territory IDs do not grant cross-site access; ownership is server-resolved.
- Restore/clone/staging cannot blindly reuse production provider tokens, cache keys, webhook/callback identifiers or private location datasets.
- F10 synchronized location data retains its source provenance; synchronization does not make F11 the authoritative address owner automatically.
- F04 scores/ranks may consume typed spatial facts, but score/rank does not become Policy or territory authority.
- AI/MCP may draft territories, explain spatial results or propose mappings only through normal Policy/approval gates; no hidden privileged geocoder, routing or precise-location path exists.

## 3. Certification classes

- `GEO-SCH` — location/address/coordinate/territory schema.
- `GEO-GEO` — geocoder provider/mapping/confidence/provenance.
- `GEO-CRD` — coordinate validation/normalization/precision.
- `GEO-DST` — radius/distance/bounding-box semantics.
- `GEO-POL` — polygon/zone containment/boundaries/holes.
- `GEO-TER` — territory hierarchy/overlap/priority/assignment.
- `GEO-BKD` — spatial backend/capability/fallback.
- `GEO-CCH` — caching/provider terms/freshness/revalidation.
- `GEO-PRI` — privacy/precise location/retention/redaction.
- `GEO-RTE` — routing/matrix provider/unknown outcome/limits.
- `GEO-IMP` — import/export CRS/axis/invalid geometry.
- `GEO-POLICY` — Policy/site/tenant protected locations.
- `GEO-MUL` — Multisite/network territories/site lifecycle.
- `GEO-ENV` — restore/clone/migration/provider mapping safety.
- `GEO-PERF` — large spatial dataset/query/routing performance.
- `GEO-E2E` — golden geocoding/radius/polygon/territory/routing regressions.

---

## 4. Executable evidence fixtures

### Group 1 — GEO-001…GEO-011 — location/address/coordinate/territory schema

- **GEO-001** — Validate a minimal location definition with stable ID, label, type, ownership scope and provenance; reject missing required identity.
- **GEO-002** — Validate address components as typed fields rather than one unstructured authority string; preserve raw source separately where configured.
- **GEO-003** — Validate coordinate object schema with latitude, longitude, CRS/profile, precision and source metadata.
- **GEO-004** — Reject latitude outside `[-90,90]`, longitude outside the declared CRS/profile range, NaN, infinity and malformed numeric strings.
- **GEO-005** — Validate territory schema with stable ID, revision, geometry reference, hierarchy parent, priority and assignment policy.
- **GEO-006** — Prove location/address/coordinate/territory IDs are immutable enough for references; edits create revision/provenance rather than silent identity reuse.
- **GEO-007** — Validate unknown schema/version behavior: reject or quarantine unsupported definitions instead of silently coercing.
- **GEO-008** — Prove source/provenance fields distinguish user-entered, imported, synchronized, geocoded and provider-derived data.
- **GEO-009** — Validate nullable/unknown address components without fabricating empty strings as verified facts.
- **GEO-010** — Validate network/site/tenant ownership fields are server-resolved and cannot be overridden by request payload.
- **GEO-011** — Golden schema round-trip: export/import preserves typed values, revision, provenance and ownership without increasing authority.

### Group 2 — GEO-012…GEO-022 — geocoder provider/mapping/confidence/provenance

- **GEO-012** — Validate geocoder provider adapter capability discovery, version and supported operations before use.
- **GEO-013** — Prove address-to-coordinate result records provider, request profile, response ID/version where available and retrieval time.
- **GEO-014** — Prove coordinate-to-address reverse geocoding retains provider provenance and does not overwrite canonical user/source address silently.
- **GEO-015** — Validate provider confidence/quality score is stored with provider-specific semantics, not normalized into false universal certainty.
- **GEO-016** — Validate multiple candidate results remain candidates until deterministic selection policy chooses one; first provider row is not implicit authority.
- **GEO-017** — Prove ambiguous address handling surfaces ambiguity and candidate metadata rather than inventing one definitive location.
- **GEO-018** — Validate country/region/language bias is explicit input with provenance and does not masquerade as verification.
- **GEO-019** — Prove provider response fields are mapped through typed adapter contracts; unknown fields do not become trusted application fields automatically.
- **GEO-020** — Validate geocoder timeout/rate-limit/5xx/connection loss records `unknown`/retryable state without fabricating `not found`.
- **GEO-021** — Prove provider credentials and secret request parameters are redacted from logs, audit payloads and exported evidence.
- **GEO-022** — Golden ambiguous-geocode regression preserves candidate order policy, confidence caveat, provenance and no false verification claim.

### Group 3 — GEO-023…GEO-033 — coordinate validation/normalization/precision

- **GEO-023** — Validate decimal-degree normalization preserves sign, bounds and configured precision without locale comma corruption.
- **GEO-024** — Validate longitude wrap/normalization policy explicitly; values are not silently wrapped unless profile declares it.
- **GEO-025** — Validate latitude/longitude axis order is explicit for imported/provider formats and swapped-axis detection surfaces error/warning.
- **GEO-026** — Prove rounding precision is declared and cannot increase claimed measurement accuracy beyond source provenance.
- **GEO-027** — Validate coordinates near poles, antimeridian, zero longitude/latitude and exact boundary values deterministically.
- **GEO-028** — Validate negative zero and floating representation canonicalization produce stable equality/cache keys.
- **GEO-029** — Prove source precision/accuracy radius metadata survives transformations where applicable.
- **GEO-030** — Validate CRS identifier/version is preserved; implicit EPSG:4326 assumption is forbidden for profiles that accept other systems.
- **GEO-031** — Prove coordinate transformation records source CRS, destination CRS, library/profile version and transformation provenance.
- **GEO-032** — Validate invalid/unsupported CRS conversion fails closed/quarantines geometry rather than producing guessed coordinates.
- **GEO-033** — Golden coordinate-vector suite reproduces exact normalized outputs across supported runtimes/profiles within declared tolerance.

### Group 4 — GEO-034…GEO-044 — radius/distance/bounding box semantics

- **GEO-034** — Validate distance profile declares algorithm/model (e.g. spherical/geodesic/projected), units and input CRS.
- **GEO-035** — Prove same-point distance is zero within declared numeric tolerance without negative/NaN artifacts.
- **GEO-036** — Validate unit conversions for meters/kilometers/miles remain deterministic and explicitly rounded only at presentation boundaries.
- **GEO-037** — Validate antimeridian-crossing distance and bounding-box logic without false world-spanning boxes.
- **GEO-038** — Validate polar-region distance behavior under declared model and surface unsupported assumptions.
- **GEO-039** — Prove radius inclusion boundary semantics (`<` vs `<=`) are explicit and stable.
- **GEO-040** — Validate bounding-box containment separately from radius/polygon containment; box match cannot be reused as stronger fact.
- **GEO-041** — Validate empty/negative/NaN/infinite radius input is rejected.
- **GEO-042** — Prove distance result includes algorithm/model/profile version where needed for reproducibility.
- **GEO-043** — Validate cached distance facts invalidate when either coordinate, CRS/profile or algorithm revision changes.
- **GEO-044** — Golden radius/bounding-box suite covers exact-edge, antimeridian and high-latitude cases deterministically.

### Group 5 — GEO-045…GEO-055 — polygon/zone containment/boundaries/holes

- **GEO-045** — Validate polygon geometry is closed/normalized according to profile and rejects malformed rings.
- **GEO-046** — Validate self-intersecting/invalid polygon handling: reject, repair only through explicit certified profile, or quarantine with provenance.
- **GEO-047** — Prove point-in-polygon semantics distinguish inside, outside and boundary according to declared rule.
- **GEO-048** — Validate polygon holes exclude interior points deterministically and hole-boundary semantics are explicit.
- **GEO-049** — Validate MultiPolygon/disconnected zone containment without flattening distinct islands into one ring.
- **GEO-050** — Validate antimeridian-crossing polygon handling under supported geometry model.
- **GEO-051** — Prove ring orientation differences do not silently invert intended containment unless profile explicitly uses orientation semantics.
- **GEO-052** — Validate degenerate zero-area polygon/ring rejection.
- **GEO-053** — Prove polygon revision change invalidates derived membership/cache results.
- **GEO-054** — Validate bounding-box prefilter optimization cannot substitute final polygon containment when exact profile requires it.
- **GEO-055** — Golden polygon suite covers boundary, hole, multipolygon, invalid geometry and antimeridian fixtures.

### Group 6 — GEO-056…GEO-066 — territory hierarchy/overlap/priority/assignment

- **GEO-056** — Validate territory parent/child hierarchy rejects cycles and excessive depth.
- **GEO-057** — Prove territory overlap is allowed only under explicit overlap policy; overlap is not silently resolved by database row order.
- **GEO-058** — Validate deterministic priority/tie-break contract for overlapping eligible territories.
- **GEO-059** — Prove territory assignment can return none/one/many according to profile and preserves reason/provenance.
- **GEO-060** — Validate manual territory assignment override requires explicit authority, audit provenance and expiration/review semantics where configured.
- **GEO-061** — Prove child territory match does not automatically inherit unrelated parent authorization or data access.
- **GEO-062** — Validate effective-date/schedule rules on territory definitions using explicit timezone semantics.
- **GEO-063** — Validate disabled/expired territory is excluded from new assignments while historical provenance remains readable under Policy.
- **GEO-064** — Prove F04 score/rank may order already eligible territory candidates but cannot authorize a denied territory.
- **GEO-065** — Validate assignment recalculation after geometry/priority revision uses explicit migration/recompute semantics rather than silent historical rewrite.
- **GEO-066** — Golden overlap/hierarchy suite proves deterministic nested, overlapping, tied and manual-override outcomes.

### Group 7 — GEO-067…GEO-077 — spatial query backend/capability fallback

- **GEO-067** — Validate spatial backend adapter declares supported predicates, CRS, indexes, precision/tolerance and version.
- **GEO-068** — Prove unsupported spatial predicate fails with explicit capability state rather than silently approximating.
- **GEO-069** — Validate fallback backend/profile is declared and cannot silently weaken accuracy/security semantics.
- **GEO-070** — Prove database spatial query result is rechecked through Policy before protected location/entity data is exposed.
- **GEO-071** — Validate parameterized/bounded spatial query construction; raw user geometry cannot become arbitrary SQL/query-language execution.
- **GEO-072** — Validate query budget/geometry complexity limits against pathological polygons/radius requests.
- **GEO-073** — Prove backend timeout/outage returns degraded/unknown health rather than empty result interpreted as no matches.
- **GEO-074** — Validate backend migration/index rebuild generation switch does not mix incompatible spatial result generations.
- **GEO-075** — Prove backend precision differences stay within certified tolerance or surface incompatibility.
- **GEO-076** — Validate query result pagination/cursor stability for deterministic ordering when many matches exist.
- **GEO-077** — Golden backend parity suite compares certified predicates across supported engines/profile versions.

### Group 8 — GEO-078…GEO-088 — caching/geocoder terms/freshness/revalidation

- **GEO-078** — Validate cache key includes normalized input, provider/profile version, locale/bias, ownership scope and material Policy dimensions.
- **GEO-079** — Prove geocoder provider cache/retention terms are represented as configuration constraints, not ignored optimization hints.
- **GEO-080** — Validate cached provider result records retrieval time, freshness/expiry and provenance.
- **GEO-081** — Prove stale cache state is distinguishable from fresh provider result and does not masquerade as current verification.
- **GEO-082** — Validate negative/no-result caching has bounded TTL and does not convert transient provider outage into durable `not found`.
- **GEO-083** — Prove address/coordinate/territory revision invalidates dependent cached geocodes/spatial assignments where required.
- **GEO-084** — Validate provider terms change/profile revision can invalidate prohibited cached fields without exposing retained data.
- **GEO-085** — Prove cross-site/tenant cache keys cannot leak protected precise-location/geocoder results.
- **GEO-086** — Validate cache stampede/single-flight behavior is evidence-only until executed; no paper performance claims.
- **GEO-087** — Validate revalidation unknown outcome preserves old-stale vs new-unknown distinction without silently overwriting usable provenance.
- **GEO-088** — Golden cache lifecycle suite covers hit, stale, negative, invalidation, provider-term change and tenant isolation.

### Group 9 — GEO-089…GEO-099 — privacy/precise location/retention/redaction

- **GEO-089** — Classify precise vs coarse location fields under explicit privacy profile and data purpose.
- **GEO-090** — Validate consent-required precise-location collection is blocked before valid consent where configured.
- **GEO-091** — Prove revocation stops future collection/use according to profile and triggers required derived-cache invalidation.
- **GEO-092** — Validate coordinate redaction/coarsening reduces precision deterministically without claiming exact location.
- **GEO-093** — Prove logs/audit/error payloads redact raw precise coordinates/address fields unless explicitly authorized.
- **GEO-094** — Validate retention expiration deletes/anonymizes eligible precise-location data while preserving legally required non-sensitive audit metadata only as configured.
- **GEO-095** — Validate export/subject-access includes authorized location provenance without leaking other users/sites/tenants.
- **GEO-096** — Validate erase request distinguishes authoritative source, synchronized copies, derived caches and legal-hold exceptions.
- **GEO-097** — Prove small-cohort/territory reporting does not expose protected precise-location membership through unrestricted counts.
- **GEO-098** — Validate AI/MCP receives only Policy-projected/coarsened location context appropriate to its task.
- **GEO-099** — Golden privacy suite covers consent missing, revoked, redacted, retention-expired, export and cross-tenant adversarial cases.

### Group 10 — GEO-100…GEO-110 — external routing/matrix provider unknown outcome/limits

- **GEO-100** — Validate routing/matrix provider adapter declares modes, limits, units, traffic/time semantics and version.
- **GEO-101** — Prove route/matrix estimate records provider, request profile, departure/arrival time semantics and retrieval time.
- **GEO-102** — Validate straight-line fallback is never labelled route distance/time unless explicitly named as approximation.
- **GEO-103** — Prove provider timeout/connection loss returns unknown/retryable state, not infinite distance or no-route by default.
- **GEO-104** — Validate no-route/provider-denied/quota-exceeded/invalid-input outcomes remain distinct.
- **GEO-105** — Validate matrix size/batch limits and chunking preserve origin-destination identity without transposition.
- **GEO-106** — Prove provider Retry-After/quota/backoff is honored by the profile; retry storms are prohibited.
- **GEO-107** — Validate route results do not grant delivery/serviceability/authorization; domain policy re-evaluates separately.
- **GEO-108** — Validate sensitive origin/destination coordinates are redacted from provider logs where policy requires.
- **GEO-109** — Prove cached route estimates include time/profile/provider dimensions and invalidate when material inputs change.
- **GEO-110** — Golden routing suite covers normal, no-route, quota, timeout, matrix chunking and stale-cache outcomes.

### Group 11 — GEO-111…GEO-121 — import/export coordinate systems/invalid geometry

- **GEO-111** — Validate GeoJSON/import profile requires/records supported CRS assumptions and axis-order rules.
- **GEO-112** — Validate WKT/WKB/CSV/other supported geometry inputs through typed parsers; malformed input cannot become arbitrary DB/query execution.
- **GEO-113** — Prove unsupported CRS/import profile is rejected/quarantined rather than silently interpreted as WGS84.
- **GEO-114** — Validate coordinate transformation provenance on import and precision/tolerance loss reporting.
- **GEO-115** — Validate invalid polygons, self-intersections, unclosed rings and duplicate vertices according to explicit repair/reject policy.
- **GEO-116** — Prove geometry repair, when supported, stores original hash/provenance plus repair profile/version rather than hiding mutation.
- **GEO-117** — Validate large geometry import complexity/count/size limits and bounded parsing resources.
- **GEO-118** — Validate export preserves site/tenant ownership and does not include protected geometry without Policy authorization.
- **GEO-119** — Validate export of coarsened/redacted coordinates does not accidentally include original precision in metadata.
- **GEO-120** — Prove round-trip export/import remains within declared tolerance and retains revision/provenance.
- **GEO-121** — Golden import suite covers CRS mismatch, swapped axes, invalid polygons, repaired geometry, oversized input and privacy-safe export.

### Group 12 — GEO-122…GEO-132 — Policy/site/tenant-protected locations

- **GEO-122** — Prove geospatial lookup result is filtered by canonical resource Policy before entity/location details are returned.
- **GEO-123** — Validate knowledge that a protected location exists is itself redacted where Policy forbids enumeration.
- **GEO-124** — Prove territory membership does not grant underlying resource access automatically.
- **GEO-125** — Validate request-provided site/tenant/location owner IDs cannot re-scope a query across tenants.
- **GEO-126** — Validate protected field-level address/coordinate redaction composes with spatial match results.
- **GEO-127** — Prove aggregate territory counts honor protected-count/small-cohort policy.
- **GEO-128** — Validate saved geospatial queries/filters do not preserve unauthorized protected location IDs after permission change.
- **GEO-129** — Prove cached protected spatial result invalidates/bypasses after role/Policy/site ownership change.
- **GEO-130** — Validate admin/debug views require explicit capability and still redact secrets/precise protected data where appropriate.
- **GEO-131** — Validate AI/MCP geospatial tools cannot bypass normal Policy by requesting raw coordinates/territories directly.
- **GEO-132** — Golden adversarial Policy suite covers IDOR-style site/tenant IDs, hidden-location enumeration, stale cache and AI/MCP attempts.

### Group 13 — GEO-133…GEO-143 — Multisite/network territories/site lifecycle

- **GEO-133** — Validate site-local territory identity namespaces prevent same key on different sites from colliding.
- **GEO-134** — Validate network-level territory template inheritance uses explicit copy/link/override semantics.
- **GEO-135** — Prove network territory visibility does not imply access to every site's protected location data.
- **GEO-136** — Validate site override preserves template/revision provenance and cannot silently mutate network source.
- **GEO-137** — Validate network aggregate spatial query resolves authorized site set server-side.
- **GEO-138** — Prove shared geocoder/routing connection can be reused operationally without sharing tenant-specific cache/result data.
- **GEO-139** — Validate site creation from template creates correct new site-scoped territory identities and no historical assignments.
- **GEO-140** — Validate site archive disables new provider/assignment work while historical records remain governed.
- **GEO-141** — Validate site delete/unlink handles territory/location data according to retention/export policy and does not delete shared network definitions accidentally.
- **GEO-142** — Validate moving/reassigning site ownership does not carry unauthorized private-location access to new actors automatically.
- **GEO-143** — Golden Multisite suite covers same-key isolation, network template override, shared provider connection, site archive/delete and cross-site adversarial query.

### Group 14 — GEO-144…GEO-154 — restore/clone/migration/provider mapping safety

- **GEO-144** — Validate backup/restore preserves local location/geometry/territory revision/provenance but does not claim to restore external provider state.
- **GEO-145** — Validate cloned environment rewrites/quarantines environment identity before any provider write/callback behavior can activate.
- **GEO-146** — Prove production provider tokens/connection write authority are disabled/quarantined in clone by default unless explicitly remapped/approved.
- **GEO-147** — Validate provider cache/license restrictions during backup/export/restore; prohibited cached payloads are not blindly copied.
- **GEO-148** — Validate restored stale geocoder/routing cache is marked with original retrieval/freshness state and revalidated as required.
- **GEO-149** — Validate migration preserves CRS/profile/geometry version metadata and detects unsupported destination spatial capabilities.
- **GEO-150** — Prove restored territory assignments do not become new authorization or serviceability truth without current Policy/rule reevaluation.
- **GEO-151** — Validate external provider identifiers/callbacks from another environment cannot receive writes until explicit environment reconciliation.
- **GEO-152** — Validate duplicate/clone of a territory creates new identity by default and does not copy historical assignment/audit identity as same entity.
- **GEO-153** — Validate environment rollback cannot roll back real-world provider/geocoder/routing facts and records reconciliation requirement.
- **GEO-154** — Golden environment suite covers backup/restore, clone, provider remap, stale cache, unsupported backend and rollback reconciliation.

### Group 15 — GEO-155…GEO-165 — large spatial dataset/query/routing performance

All fixtures in this group are **reserved executable benchmarks**. No performance claim is certified until executed on declared hardware, DB/backend, dataset and provider profile.

- **GEO-155** — Benchmark 100K point locations with indexed radius queries across declared selectivity profiles.
- **GEO-156** — Benchmark 1M point locations with representative bounding-box/radius workloads and p50/p95/p99 latency.
- **GEO-157** — Benchmark 100K polygons/territories for point-in-polygon assignment under declared geometry complexity distribution.
- **GEO-158** — Benchmark high-overlap territory evaluation with deterministic priority resolution.
- **GEO-159** — Benchmark large MultiPolygon/holes geometry complexity and enforce resource budgets.
- **GEO-160** — Benchmark cache hit/miss/stale-revalidation profiles without violating provider terms.
- **GEO-161** — Benchmark concurrent geocoder requests under adapter quota/rate-limit/backoff controls using approved test provider/simulator only after consent.
- **GEO-162** — Benchmark routing matrix batching/chunking/backpressure at declared origin/destination sizes using approved test profile only after consent.
- **GEO-163** — Benchmark Multisite aggregate spatial query across declared site counts with Policy filtering.
- **GEO-164** — Benchmark import of million-row point dataset and large geometry set with bounded memory/time and invalid-row accounting.
- **GEO-165** — Capture regression budgets, dataset fingerprints, backend/provider versions and no-paper-performance certification gate.

### Group 16 — GEO-166…GEO-176 — golden geocoding/radius/polygon/territory/routing regressions

- **GEO-166** — Golden public-store-locator flow: typed address → geocode candidate → Policy-safe result → radius match, without claiming address verification.
- **GEO-167** — Golden delivery-zone flow: point → exact polygon/hole containment → domain serviceability Policy; polygon match alone does not promise delivery.
- **GEO-168** — Golden sales-territory flow: overlapping nested polygons → deterministic priority → assignment provenance; assignment does not grant customer-record access.
- **GEO-169** — Golden nearest-location flow: straight-line candidate shortlist → optional route estimate → explicit approximation/provider caveats.
- **GEO-170** — Golden precise-location privacy flow: consent granted → collection → coarsened display → consent revoked → cache/derived-data invalidation.
- **GEO-171** — Golden antimeridian/polar flow validates coordinate normalization, distance, bounding boxes and supported polygon behavior.
- **GEO-172** — Golden import flow: mixed CRS/swapped axes/invalid polygon → quarantine/repair provenance → certified normalized dataset.
- **GEO-173** — Golden provider outage flow: geocoder/routing timeout → unknown/degraded state → safe stale/fallback behavior without false no-match/no-route claims.
- **GEO-174** — Golden Multisite flow: network territory template + site override + shared provider adapter + isolated cache/Policy results.
- **GEO-175** — Golden backup/clone flow: restore local geometry/provenance → quarantine provider mapping/cache → revalidate current Policy before activation.
- **GEO-176** — Golden AI/MCP adversarial regression: malicious prompt/request attempts raw precise-location disclosure, cross-tenant territory query, provider bypass or unauthorized mutation; all remain Policy/consent/approval bounded.

---

## 5. Execution evidence requirements

A future executable run of any GEO fixture must record at minimum:

- fixture ID and protocol revision;
- code/build commit and schema/migration version;
- spatial backend/database/index version;
- geocoder/routing provider adapter and profile version when applicable;
- CRS, axis order, distance/containment algorithm and numeric tolerance;
- dataset/geometry fixture hash and site/tenant ownership profile;
- Policy/consent/privacy profile;
- cache/provider-terms profile;
- input, expected output and actual output with protected values redacted;
- deterministic pass/fail result and failure reason;
- latency/memory/provider quota metrics for performance fixtures;
- evidence artifact references sufficient for reproduction.

No fixture may be marked executed from screenshots, prose, static schema inspection or generated test plans alone.

## 6. Runtime certification gate

F11 runtime certification remains **0** until explicitly authorized execution produces evidence for the required fixture set and a later ADR accepts the results.

Current truth:

- documented: **176/176**;
- executed: **0/176**;
- runtime certification: **0**;
- implementation authorization: **NOT GRANTED**.
