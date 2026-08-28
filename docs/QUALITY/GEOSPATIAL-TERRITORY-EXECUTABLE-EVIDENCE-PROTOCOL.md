# WPEssential — Geospatial & Territory Executable Evidence Protocol

Status: **Accepted evidence design candidate / execution pending / no development authorization**  
Date: **2026-08-29**

Namespace: **GEO-001…GEO-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## 1. Purpose

This protocol fixes the executable evidence required before F11 — Geospatial & Territory can be called runtime-ready.

F11 owns typed location/address/coordinate/territory definitions, geocoding provenance, deterministic spatial evaluation, territory assignment logic, privacy-safe precise-location handling and bounded routing/matrix integration for explicitly configured geospatial profiles. It does not become identity, address-verification, authorization, serviceability, legal-jurisdiction, logistics or provider truth merely because a coordinate, polygon, route or territory matched.

No fixture below has executed. No geocoder/routing/provider call, spatial DB query, coordinate mutation, territory assignment, cache mutation, precise-location collection, AI/MCP call, benchmark, build, test or runtime mutation is authorized by this protocol.

## 2. Non-negotiable truth boundaries

- `Geocoded coordinate ≠ verified physical identity/address truth` unless an explicit verification profile and authority establish that fact.
- `Provider confidence ≠ certainty`.
- `Spatial match ≠ authorization`.
- `Territory assignment ≠ entitlement, ownership or legal jurisdiction` unless a separate canonical domain contract establishes it.
- `Bounding-box match ≠ polygon containment`.
- `Polygon containment ≠ guaranteed serviceability`.
- `Straight-line distance ≠ travel distance/time`.
- `Routing/matrix estimate ≠ guaranteed travel time, delivery promise or serviceability`.
- Unknown geocoder/routing/provider outcome is not automatically failed.
- Coordinate precision, source, CRS and axis order are explicit; silent coordinate-system assumptions are prohibited.
- Precise location remains consent/Policy/retention/redaction governed.
- Provider credentials remain Vault-owned; provider cache/licensing/terms constraints remain binding.
- Site/tenant/territory ownership is server-resolved; request-provided scope IDs grant no authority.
- Restore/clone/staging may not blindly reuse production provider tokens, caches or private-location authority; this remains a cross-foundation environment-safety rule and does not repurpose the canonical GEO fixture groups.
- F10 synchronized location data retains source provenance; synchronization does not automatically make F11 authoritative.
- F04 scores/ranks may consume typed spatial facts but do not become Policy or territory authority.
- AI/MCP may draft territories or explain spatial results only through normal Policy/approval gates.

## 3. Canonical evidence groups

The 16 groups below preserve the exact group ownership fixed by `UNIVERSAL-FOUNDATIONS-TECHNICAL-EVIDENCE-MASTER-PLAN.md`.

### Group 1 — GEO-001…GEO-011 — location/address/coordinate/territory schema
- **GEO-001** — Minimal location schema validates stable ID, label, type, owner scope and provenance.
- **GEO-002** — Address components remain typed; optional raw source is preserved separately.
- **GEO-003** — Coordinate schema requires latitude, longitude, CRS/profile, precision and source metadata.
- **GEO-004** — Reject out-of-range coordinates, NaN, infinity and malformed numbers.
- **GEO-005** — Territory schema validates stable ID, revision, geometry reference, hierarchy, priority and assignment policy.
- **GEO-006** — Edits preserve identity/revision provenance rather than silently reusing entity meaning.
- **GEO-007** — Unsupported schema/version rejects or quarantines instead of silent coercion.
- **GEO-008** — Provenance distinguishes user-entered, imported, synchronized, geocoded and provider-derived values.
- **GEO-009** — Unknown address parts remain unknown rather than fabricated verified values.
- **GEO-010** — Site/network/tenant ownership is server-resolved and request payload cannot override it.
- **GEO-011** — Schema export/import round-trip preserves types, revision, provenance and ownership without increasing authority.

### Group 2 — GEO-012…GEO-022 — geocoder provider/mapping/confidence/provenance
- **GEO-012** — Provider adapter capability/version is discovered before geocoding.
- **GEO-013** — Forward-geocode result records provider, profile, response identity/version and retrieval time where available.
- **GEO-014** — Reverse geocoding preserves provenance and does not silently overwrite canonical source address.
- **GEO-015** — Confidence score retains provider-specific semantics and is not universalized into certainty.
- **GEO-016** — Multiple provider candidates remain candidates until deterministic selection policy applies.
- **GEO-017** — Ambiguous address returns ambiguity/candidate evidence instead of a fabricated definitive location.
- **GEO-018** — Country/region/language bias is explicit input with provenance.
- **GEO-019** — Provider fields map only through typed adapter contracts.
- **GEO-020** — Timeout/rate-limit/5xx/connection loss produces unknown/retryable state, not false `not found`.
- **GEO-021** — Credentials and secret provider parameters are redacted from evidence/logs.
- **GEO-022** — Golden ambiguous-geocode fixture preserves candidates, confidence caveat and provenance.

### Group 3 — GEO-023…GEO-033 — coordinate validation/normalization/precision
- **GEO-023** — Decimal-degree normalization preserves sign/bounds/precision and avoids locale corruption.
- **GEO-024** — Longitude wrap behavior is explicit; no silent wrapping unless profile declares it.
- **GEO-025** — Axis order is explicit and swapped-axis inputs are detected.
- **GEO-026** — Rounding cannot imply more accuracy than the source provides.
- **GEO-027** — Poles, antimeridian, zero coordinates and exact limits are deterministic.
- **GEO-028** — Negative zero/floating canonicalization yields stable equality/cache keys.
- **GEO-029** — Source precision/accuracy metadata survives supported transformations.
- **GEO-030** — CRS identifier/version is preserved; unsupported implicit CRS assumptions are rejected.
- **GEO-031** — CRS conversion records source/destination CRS and transformation version/provenance.
- **GEO-032** — Unsupported conversion rejects/quarantines rather than guessing.
- **GEO-033** — Golden coordinate vectors reproduce normalized outputs within declared tolerance.

### Group 4 — GEO-034…GEO-044 — radius/distance/bounding box semantics
- **GEO-034** — Distance profile declares model/algorithm, units and input CRS.
- **GEO-035** — Same-point distance is zero within declared tolerance.
- **GEO-036** — Meter/kilometer/mile conversions are deterministic and presentation rounding is separate.
- **GEO-037** — Antimeridian distance/bounding boxes avoid false world-spanning boxes.
- **GEO-038** — Polar-region behavior follows declared supported model.
- **GEO-039** — Radius edge semantics (`<`/`<=`) are explicit.
- **GEO-040** — Bounding-box result remains distinct from radius/polygon containment.
- **GEO-041** — Negative/NaN/infinite/empty radius is rejected.
- **GEO-042** — Distance result retains algorithm/profile version for reproducibility.
- **GEO-043** — Coordinate/CRS/algorithm revision invalidates dependent distance cache.
- **GEO-044** — Golden radius/bounding-box suite covers edge, antimeridian and high-latitude cases.

### Group 5 — GEO-045…GEO-055 — polygon/zone containment/boundaries/holes
- **GEO-045** — Polygon rings validate closure/normalization under the declared profile.
- **GEO-046** — Self-intersecting/invalid polygons reject, explicitly repair, or quarantine with provenance.
- **GEO-047** — Point-in-polygon distinguishes inside/outside/boundary deterministically.
- **GEO-048** — Holes exclude interior points and hole-boundary semantics are explicit.
- **GEO-049** — MultiPolygon/disconnected zones preserve separate components.
- **GEO-050** — Antimeridian-crossing polygons follow the certified geometry model.
- **GEO-051** — Ring orientation cannot silently invert containment semantics.
- **GEO-052** — Zero-area/degenerate geometry is rejected.
- **GEO-053** — Geometry revision invalidates derived containment/membership cache.
- **GEO-054** — Bounding-box prefilter cannot replace exact containment where exactness is required.
- **GEO-055** — Golden polygon suite covers boundary, hole, multipolygon, invalid and antimeridian cases.

### Group 6 — GEO-056…GEO-066 — territory hierarchy/overlap/priority/assignment
- **GEO-056** — Territory hierarchy rejects cycles and excessive depth.
- **GEO-057** — Overlap requires explicit policy and is never resolved by DB row order.
- **GEO-058** — Priority/tie-break is deterministic.
- **GEO-059** — Assignment may return none/one/many only according to declared profile and keeps reason/provenance.
- **GEO-060** — Manual override requires explicit authority/audit and configured expiry/review semantics.
- **GEO-061** — Child territory match does not inherit unrelated parent authorization.
- **GEO-062** — Effective-date rules use explicit timezone semantics.
- **GEO-063** — Disabled/expired territory stops new assignment while historical provenance remains governed.
- **GEO-064** — F04 rank may order eligible candidates only; it cannot authorize denied candidates.
- **GEO-065** — Geometry/priority revision uses explicit recompute/migration semantics rather than historical rewrite.
- **GEO-066** — Golden nested/overlap/tie/manual-override outcomes are deterministic.

### Group 7 — GEO-067…GEO-077 — spatial query backend/capability fallback
- **GEO-067** — Backend declares supported predicates, CRS, indexes, precision/tolerance and version.
- **GEO-068** — Unsupported predicate produces explicit unsupported state, not silent approximation.
- **GEO-069** — Fallback backend/profile is declared and cannot silently weaken semantics.
- **GEO-070** — Spatial query result is reauthorized before protected location/entity data is exposed.
- **GEO-071** — Spatial query construction is parameterized/bounded; raw geometry cannot become arbitrary SQL/query execution.
- **GEO-072** — Geometry complexity/query budgets resist pathological inputs.
- **GEO-073** — Backend outage returns degraded/unknown state, not empty result interpreted as no match.
- **GEO-074** — Backend/index generation migration cannot mix incompatible result generations.
- **GEO-075** — Backend precision differences remain within certified tolerance or surface incompatibility.
- **GEO-076** — Pagination/cursor ordering is stable for many matches.
- **GEO-077** — Golden backend parity compares certified predicates across supported engines.

### Group 8 — GEO-078…GEO-088 — caching/geocoder terms/freshness/revalidation
- **GEO-078** — Cache key includes normalized input, provider/profile, locale/bias, ownership and material Policy dimensions.
- **GEO-079** — Provider cache/retention terms are enforced as contract constraints.
- **GEO-080** — Cached provider result stores retrieval time, freshness and provenance.
- **GEO-081** — Stale cache is distinguishable from fresh provider result.
- **GEO-082** — Negative caching has bounded TTL and provider outage cannot become durable `not found`.
- **GEO-083** — Address/coordinate/territory revision invalidates dependent caches as configured.
- **GEO-084** — Provider terms/profile changes can invalidate prohibited cached fields.
- **GEO-085** — Site/tenant cache isolation prevents protected-location leakage.
- **GEO-086** — Stampede/single-flight behavior remains uncertified until executed.
- **GEO-087** — Failed revalidation preserves stale-vs-unknown distinction.
- **GEO-088** — Golden cache lifecycle covers hit/stale/negative/invalidation/terms/tenant cases.

### Group 9 — GEO-089…GEO-099 — privacy/precise location/retention/redaction
- **GEO-089** — Precise/coarse location classification is explicit by purpose/profile.
- **GEO-090** — Consent-required precise-location collection blocks before valid consent.
- **GEO-091** — Revocation stops future use/collection and invalidates required derived caches.
- **GEO-092** — Coarsening/redaction deterministically reduces precision without claiming exactness.
- **GEO-093** — Logs/audit/errors redact precise coordinates/address fields unless explicitly authorized.
- **GEO-094** — Retention expiry deletes/anonymizes eligible precise-location data according to policy.
- **GEO-095** — Subject export includes authorized provenance without cross-user/site leakage.
- **GEO-096** — Erasure distinguishes authoritative source, synchronized copies, derived caches and legal-hold exceptions.
- **GEO-097** — Small-cohort territory reporting does not expose precise-location membership through unrestricted counts.
- **GEO-098** — AI/MCP receives only Policy-projected/coarsened location context.
- **GEO-099** — Golden privacy suite covers missing/revoked consent, redaction, retention, export and cross-tenant attacks.

### Group 10 — GEO-100…GEO-110 — external routing/matrix provider unknown outcome/limits
- **GEO-100** — Routing/matrix adapter declares modes, units, limits, traffic/time semantics and version.
- **GEO-101** — Route estimate records provider/profile/time semantics/retrieval time.
- **GEO-102** — Straight-line fallback is never labelled routed distance/time.
- **GEO-103** — Timeout/connection loss returns unknown/retryable, not false no-route.
- **GEO-104** — No-route, provider-denied, quota-exceeded and invalid-input remain distinct.
- **GEO-105** — Matrix batching preserves origin-destination identity and ordering.
- **GEO-106** — Retry-After/quota/backoff behavior is honored.
- **GEO-107** — Route result does not grant delivery/serviceability/authorization.
- **GEO-108** — Sensitive origins/destinations are redacted from provider logs as required.
- **GEO-109** — Route cache includes material time/profile/provider dimensions.
- **GEO-110** — Golden routing suite covers normal/no-route/quota/timeout/matrix/stale-cache outcomes.

### Group 11 — GEO-111…GEO-121 — import/export coordinate systems/invalid geometry
- **GEO-111** — Import profile records CRS assumptions and axis order.
- **GEO-112** — Supported GeoJSON/WKT/WKB/CSV inputs use typed bounded parsers.
- **GEO-113** — Unsupported CRS rejects/quarantines instead of silently assuming WGS84.
- **GEO-114** — Import transformation records provenance and precision/tolerance loss.
- **GEO-115** — Invalid polygons/rings/vertices follow explicit reject/repair policy.
- **GEO-116** — Repair, when supported, preserves original hash plus repair profile/version.
- **GEO-117** — Import size/geometry complexity/resource limits are bounded.
- **GEO-118** — Export preserves ownership and filters protected geometry through Policy.
- **GEO-119** — Redacted/coarsened export does not leak original precision in metadata.
- **GEO-120** — Round-trip export/import remains within declared tolerance and preserves provenance.
- **GEO-121** — Golden import suite covers CRS mismatch, swapped axes, invalid/repair, oversized and privacy-safe export cases.

### Group 12 — GEO-122…GEO-132 — Policy/site/tenant-protected locations
- **GEO-122** — Geospatial result is Policy-filtered before location/entity details are returned.
- **GEO-123** — Existence of a protected location is redacted where enumeration is forbidden.
- **GEO-124** — Territory membership does not grant resource access automatically.
- **GEO-125** — Request-provided site/tenant/owner IDs cannot re-scope queries.
- **GEO-126** — Field-level address/coordinate redaction composes with spatial matches.
- **GEO-127** — Territory counts obey protected-count/small-cohort policy.
- **GEO-128** — Saved filters do not retain unauthorized protected IDs after permission change.
- **GEO-129** — Protected spatial cache invalidates/bypasses after Policy/ownership changes.
- **GEO-130** — Admin/debug views remain capability-gated and redacted.
- **GEO-131** — AI/MCP geospatial paths cannot bypass canonical Policy.
- **GEO-132** — Golden adversarial suite covers cross-tenant IDs, hidden-location enumeration, stale cache and AI/MCP bypass attempts.

### Group 13 — GEO-133…GEO-143 — Multisite/network territories/site lifecycle
- **GEO-133** — Site-local territory keys are namespace-isolated.
- **GEO-134** — Network template inheritance uses explicit copy/link/override semantics.
- **GEO-135** — Network territory visibility does not imply protected site-location access.
- **GEO-136** — Site override preserves source template/revision provenance.
- **GEO-137** — Network aggregate query resolves authorized site set server-side.
- **GEO-138** — Shared provider adapter does not share tenant-specific cache/result data.
- **GEO-139** — New site template instantiation creates fresh site-scoped identities without historical assignments.
- **GEO-140** — Site archive stops new geospatial/provider assignment work while preserving governed history.
- **GEO-141** — Site delete/unlink respects retention/export and cannot delete shared network definitions accidentally.
- **GEO-142** — Site ownership change does not automatically transfer private-location access.
- **GEO-143** — Golden Multisite suite covers key isolation, template override, shared provider and site lifecycle.

### Group 14 — GEO-144…GEO-154 — provider/version/data-source drift
- **GEO-144** — Geocoder adapter/provider version change is detected and compatibility state recorded before use.
- **GEO-145** — Provider response-schema additions/removals/type changes are surfaced instead of silently coerced.
- **GEO-146** — Provider confidence/quality semantics drift is detected; old and new scores are not treated as directly equivalent without mapping evidence.
- **GEO-147** — Geocoding dataset/source revision drift retains source/version/retrieval provenance and invalidates affected derived cache as declared.
- **GEO-148** — Routing provider algorithm/mode/traffic-model version drift is recorded with result provenance.
- **GEO-149** — Provider terms/licensing/cache-policy changes trigger compatibility/revalidation state rather than silent continued use.
- **GEO-150** — Source-of-address/coordinate authority change is explicit; provider-derived values cannot silently replace newly authoritative source data.
- **GEO-151** — Synchronized/imported source revision drift preserves source-system identity and detects incompatible coordinate/address schema changes.
- **GEO-152** — Spatial backend/library version drift that changes containment/distance tolerance requires recertification before equivalence is claimed.
- **GEO-153** — Source-vs-geocoder disagreement is surfaced as conflict/provenance state and does not silently rewrite authoritative data.
- **GEO-154** — Golden drift suite covers provider version, response schema, confidence semantics, source revision, terms/cache policy and backend-version changes.

### Group 15 — GEO-155…GEO-165 — large spatial dataset/query performance

These are **reserved executable benchmarks**. No performance claim is certified until executed on declared hardware/backend/dataset.

- **GEO-155** — Benchmark 100K indexed point locations with representative radius selectivity.
- **GEO-156** — Benchmark 1M point locations with bounding-box/radius p50/p95/p99 latency.
- **GEO-157** — Benchmark 100K polygons/territories for point-in-polygon assignment.
- **GEO-158** — Benchmark high-overlap territory resolution with deterministic priority.
- **GEO-159** — Benchmark large MultiPolygon/hole complexity under resource budgets.
- **GEO-160** — Benchmark cache hit/miss/stale-revalidation query paths under provider terms.
- **GEO-161** — Benchmark many simultaneous spatial queries with bounded DB/backend concurrency.
- **GEO-162** — Benchmark large candidate-set nearest-location/territory queries without unbounded N+1 behavior.
- **GEO-163** — Benchmark Multisite aggregate spatial query across declared site counts with Policy filtering.
- **GEO-164** — Benchmark million-row point import/index build plus invalid-row accounting.
- **GEO-165** — Record dataset fingerprints, backend versions, latency/memory budgets and no-paper-performance certification gate.

### Group 16 — GEO-166…GEO-176 — delivery/service-area/real-estate/fleet golden regression
- **GEO-166** — Golden delivery-zone flow: address candidate → coordinate provenance → polygon/service-area match → separate domain serviceability Policy.
- **GEO-167** — Golden service-area boundary/hole flow proves boundary semantics and no false service guarantee.
- **GEO-168** — Golden real-estate search flow: imported/geocoded property location → radius/polygon filtering → protected listing Policy.
- **GEO-169** — Golden real-estate territory flow: overlapping sales regions → deterministic assignment without granting listing/customer access.
- **GEO-170** — Golden fleet nearest-asset flow: precise-location consent/Policy → spatial shortlist → explicit distance-model semantics.
- **GEO-171** — Golden fleet routing flow: candidate route/matrix estimate → provider caveats → no guaranteed ETA/service promise.
- **GEO-172** — Golden antimeridian/polar flow validates normalization, distance, boxes and supported polygon behavior.
- **GEO-173** — Golden provider-outage flow preserves unknown/degraded state and avoids false no-match/no-route claims.
- **GEO-174** — Golden Multisite service-area flow combines network template, site override, shared adapter and isolated cache/Policy.
- **GEO-175** — Golden data-source drift flow changes provider/source revision and proves provenance/revalidation without silent authority transfer.
- **GEO-176** — Golden AI/MCP adversarial flow blocks raw precise-location disclosure, cross-tenant territory access, provider bypass and unauthorized mutation.

## 4. Execution evidence requirements

A future authorized execution of any GEO fixture must record fixture/protocol revision, code/build commit, spatial backend/provider versions, CRS/axis/model/tolerance, dataset hash, Policy/consent profile, cache/provider-terms profile, redacted inputs/expected/actual outputs, pass/fail reason and benchmark metrics where applicable.

Screenshots, prose, static schema review or generated test plans do not count as executed evidence.

## 5. Runtime certification gate

F11 runtime certification remains **0** until explicitly authorized execution produces accepted evidence.

Current truth:
- documented: **176/176**;
- executed: **0/176**;
- runtime certification: **0**;
- implementation authorization: **NOT GRANTED**.
