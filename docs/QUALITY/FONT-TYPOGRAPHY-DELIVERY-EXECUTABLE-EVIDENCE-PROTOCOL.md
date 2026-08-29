# WPEssential — Font Library, Typography & Delivery Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `FNT-001…FNT-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Font metadata/provenance ≠ license permission; local hosting ≠ automatic legal compliance.
- Provider availability ≠ authorization to download, redistribute or embed a font.
- Typography assignment changes presentation only; it never grants access/authorization.
- Preload/subset/font-display recommendations are performance hypotheses until measured.
- Protected/private font assets cannot leak through public URLs, caches, exports or previews.
- External providers, CSS and asset URLs remain versioned/bounded and subject to Privacy/CSP/Asset policy.

## Exact fixtures

### Group 1 — font metadata/format validation
- `FNT-001` Register font asset with stable family/source key, format, checksum, source provenance and owner scope; metadata normalizes deterministically.
- `FNT-002` Reject unknown/unsupported font container instead of trusting filename extension.
- `FNT-003` Detect format by content signature and flag extension/content mismatch.
- `FNT-004` Reject corrupt/truncated font before activation and preserve original evidence.
- `FNT-005` Parse names/weight/style tables safely with bounded parser limits.
- `FNT-006` Duplicate exact binary checksum reuses/deduplicates asset according declared library policy.
- `FNT-007` Same family/style label with different binary remains distinct revision/source identity.
- `FNT-008` Metadata edit never mutates original font binary.
- `FNT-009` Asset read/export is denied when caller lacks font-library scope even if URL/key is known.
- `FNT-010` AI/MCP may classify metadata/draft registration but cannot activate/download/provider-sync outside Policy.
- `FNT-011` Unknown metadata schema/font parser version fails typed or migrates explicitly; silent reinterpretation is forbidden.

### Group 2 — family/variant/variable axes
- `FNT-012` Build family from normal/bold/italic variants and resolve requested weight/style deterministically.
- `FNT-013` Reject duplicate family variant collision unless explicit source-priority rule resolves it.
- `FNT-014` Numeric weight maps to exact/static variant or supported variable axis without arbitrary nearest coercion beyond policy.
- `FNT-015` Variable font axis metadata records min/default/max and validates configured values.
- `FNT-016` Reject axis value outside supported range before generated CSS/output.
- `FNT-017` Optical-size/slant/width axes remain distinct typed axes and do not collapse into generic weight.
- `FNT-018` Missing requested variant follows declared fallback/synthetic-style policy and reports degradation.
- `FNT-019` Synthetic bold/italic can be disabled per profile and does not silently occur when prohibited.
- `FNT-020` Family revision preserves prior variant mapping for historical definitions.
- `FNT-021` Locale/script coverage may select fallback family without changing canonical requested typography profile.
- `FNT-022` Import preserves variable-axis semantics and rejects ambiguous provider-specific axis names without mapping.

### Group 3 — upload/Asset Registry
- `FNT-023` Upload passes capability, MIME/content, size and parser validation before Asset Registry persistence.
- `FNT-024` Oversized font upload is rejected before memory-intensive parsing.
- `FNT-025` Upload filename/path is normalized and cannot traverse storage root.
- `FNT-026` Stored font has immutable binary fingerprint plus mutable descriptive metadata/revision.
- `FNT-027` Replacing font binary creates new asset/revision rather than silently changing bytes behind immutable fingerprint.
- `FNT-028` Deleting font asset is blocked/previewed while active typography profiles depend on it.
- `FNT-029` Private library mode stores non-public asset and uses access-mediated delivery where applicable.
- `FNT-030` Public font asset response uses safe MIME/cache headers and no executable interpretation.
- `FNT-031` Asset Registry clone/import remaps storage identity and never copies source-site protected URLs blindly.
- `FNT-032` Malware/security scanner integration may flag font binary but does not auto-delete it.
- `FNT-033` Upload audit records actor/source/checksum without logging unrelated user content.

### Group 4 — Google/provider adapters
- `FNT-034` Google/provider catalog sync pins provider/API/catalog version and fetched timestamp.
- `FNT-035` Provider family lookup distinguishes exact family/variant IDs from display labels.
- `FNT-036` Provider catalog outage returns stale/degraded data with age instead of fabricating availability.
- `FNT-037` Download plan records exact provider URL/resource/version/license metadata before any later authorized fetch.
- `FNT-038` Provider HTTP success with unexpected content type/hash fails validation.
- `FNT-039` Redirect chain for provider font fetch revalidates host/scheme and blocks SSRF/private-network targets.
- `FNT-040` Provider rate limit honors Retry-After/backoff and is scoped fairly across sites.
- `FNT-041` Provider API credential remains Vault-backed and absent from exports/logs/frontend CSS.
- `FNT-042` Provider family removal/deprecation marks local mapping stale but does not delete local asset automatically.
- `FNT-043` Provider update to font binary creates new provenance/revision rather than rewriting historical asset identity.
- `FNT-044` AI/MCP provider recommendation remains draft and cannot initiate external fetch without approved provider action.

### Group 5 — Adobe/authorized provider adapters
- `FNT-045` Adobe/authorized-provider adapter validates account/project entitlement metadata separately from font metadata.
- `FNT-046` Provider project/embed identifier is typed and cannot be supplied as arbitrary executable HTML/JS.
- `FNT-047` Expired/revoked provider authorization is distinct from missing font asset.
- `FNT-048` Provider-hosted webfont CSS remains provider-owned and is not copied/redistributed unless policy/license explicitly permits.
- `FNT-049` Local-download option appears only when provider capability/license metadata explicitly allows it.
- `FNT-050` Unknown provider licensing terms block redistribution claim and require review.
- `FNT-051` Provider CSS/asset URLs are allowlisted/HTTPS and subject to CSP/privacy diagnostics.
- `FNT-052` Provider timeout remains unknown/degraded and does not silently switch to unlicensed local copy.
- `FNT-053` Multi-project/account IDs cannot leak across tenant/site boundaries.
- `FNT-054` Revoked provider token is removed/rotated via Vault owner, not stored in font definition.
- `FNT-055` Importing provider-backed definition uses placeholders/mapping and never exports credential/token.

### Group 6 — licensing/provenance/redistribution
- `FNT-056` Font record stores license name/source/reference and provenance evidence as metadata, not legal verdict.
- `FNT-057` Missing license information is shown as unknown/review-required, not presumed free.
- `FNT-058` License metadata claiming desktop-only does not automatically authorize web embedding/local serving.
- `FNT-059` Redistribution/export setting is blocked when explicit policy marks license non-redistributable.
- `FNT-060` User attestation of license ownership is recorded as attestation, not external legal verification.
- `FNT-061` Public download endpoint is disabled for protected/non-redistributable fonts.
- `FNT-062` Font package export can omit binary and include mapping placeholder when redistribution is not allowed.
- `FNT-063` License/provenance revision preserves historical source metadata when font binary revision changes.
- `FNT-064` Provider license URL/content change does not silently rewrite prior attestation/evidence.
- `FNT-065` UI wording avoids “licensed/legal/compliant” claims unless separately certified by policy/process.
- `FNT-066` AI/MCP cannot infer legal permission from metadata or auto-enable redistribution.

### Group 7 — typography profiles/assignment precedence
- `FNT-067` Create typography profile with body/heading/UI/monospace roles and stable revision.
- `FNT-068` Assignment to site/theme/component uses explicit target and does not become authorization rule.
- `FNT-069` Precedence between network/site/theme/component/user override is deterministic and explainable.
- `FNT-070` User accessibility preference can override permitted typography aspects without being erased by branding assignment.
- `FNT-071` Missing font asset triggers declared fallback stack and diagnostics rather than invisible text.
- `FNT-072` Profile update invalidates only affected generated CSS/cache artifacts.
- `FNT-073` Archived profile remains historical but no longer resolves for new assignments.
- `FNT-074` Assignment import remaps font asset references explicitly; unresolved assets remain unresolved.
- `FNT-075` Role/user-based typography remains presentation only and cannot hide protected data as access control.
- `FNT-076` Multisite network template instantiation does not share live site-local assignment state by default.
- `FNT-077` AI may draft typography profile/assignment but publishing broad/network assignment uses normal approval/Policy.

### Group 8 — theme.json/builder integration
- `FNT-078` theme.json adapter maps WPE font family/slug through supported WordPress schema for detected version.
- `FNT-079` Existing theme-owned font definition conflict is detected rather than silently overwritten.
- `FNT-080` Block editor/frontend generated family slugs remain consistent for one profile/revision.
- `FNT-081` Builder adapter uses documented provider API and does not inject private CSS when native registration is available.
- `FNT-082` Unsupported builder/version returns typed degraded state and leaves builder configuration untouched.
- `FNT-083` Builder/global typography import maps to WPE assets/profile only after explicit preview.
- `FNT-084` Removing WPE profile does not delete theme/builder-owned font definitions automatically.
- `FNT-085` Site Editor preview and frontend output resolve same intended font asset/fallback semantics.
- `FNT-086` Editor iframe/isolation differences are explicitly handled by certified asset-enqueue path.
- `FNT-087` Theme switch recomputes integration ownership and avoids stale theme-specific CSS leakage.
- `FNT-088` AI-generated theme/builder typography does not publish or overwrite owner definitions without applicable approval.

### Group 9 — local hosting/external font detection
- `FNT-089` Detector inventories external font CSS/resource origins without executing arbitrary remote code.
- `FNT-090` Google Fonts CSS detection distinguishes stylesheet request from actual font binaries and family variants.
- `FNT-091` Unknown external font origin is reported with privacy/CSP implications, not automatically migrated.
- `FNT-092` Local-host migration dry run lists exact family/variant/resource mappings and unresolved items.
- `FNT-093` Migration refuses to download font when license/provenance/provider permission is unknown or policy blocks it.
- `FNT-094` Existing local asset with same checksum can be reused without duplicate download.
- `FNT-095` External URL query/version changes are normalized cautiously and never used to bypass origin allowlist.
- `FNT-096` Detection of CSS @font-face with data/blob/local() sources is classified accurately and not treated as remote binary automatically.
- `FNT-097` Private/internal font URL is not crawled through server-side fetch unless explicit safe provider/source profile exists.
- `FNT-098` External-font disable action compiles affected references first and does not break frontend silently.
- `FNT-099` Detection result is evidence at scan time, not permanent truth after theme/plugin changes.

### Group 10 — font-display/preload/subset/performance
- `FNT-100` font-display option maps only to supported values and generated CSS reflects configured profile.
- `FNT-101` Preload is emitted only for font actually needed early under declared route/profile and never for every variant blindly.
- `FNT-102` Preload URL matches delivered CDN/local asset and crossorigin semantics.
- `FNT-103` Private/protected font is not exposed by public preload or cache artifact.
- `FNT-104` Subset generation pins source checksum, glyph/unicode range, tool/version and output checksum.
- `FNT-105` Missing required glyph in subset triggers fallback/full-font policy and cannot silently render tofu.
- `FNT-106` Locale/script subset selection preserves explicit script coverage and fallback chain.
- `FNT-107` Variable-font vs multiple-static-font recommendation remains measurable performance hypothesis, not guaranteed improvement.
- `FNT-108` Duplicate preload/font-face emission from theme/plugin/WPE is detected and deduplicated by ownership profile.
- `FNT-109` Font cache invalidates when binary/subset/profile revision changes.
- `FNT-110` Performance claim requires later measured transfer/render metrics; paper byte estimates remain NOT EXECUTED.

### Group 11 — privacy/CSP/mixed-content
- `FNT-111` External font origin appears in privacy/CSP inventory before publish.
- `FNT-112` HTTP font/CSS URL on HTTPS site is blocked/warned as mixed content according policy.
- `FNT-113` CSP font-src/style-src conflict blocks publish or reports explicit incompatibility; module never weakens CSP silently.
- `FNT-114` Referrer/cookie/query behavior of external provider is recorded where known; no legal-compliance claim is inferred.
- `FNT-115` Protected user/site identity is not embedded in public font URL/cache key.
- `FNT-116` Font analytics/telemetry is off unless separately configured and privacy-governed.
- `FNT-117` Provider credential/token never appears in CSS, browser request URL or HTML.
- `FNT-118` Data residency/region policy is checked for provider-hosted font service where applicable.
- `FNT-119` Privacy export/erase identifies WPE-owned font metadata only; public font binary is not treated as personal data by default.
- `FNT-120` Logs redact signed URLs/tokens and avoid storing full provider responses unnecessarily.
- `FNT-121` AI/MCP receives only authorized font/provenance metadata and cannot exfiltrate private asset URLs.

### Group 12 — revisions/import/export
- `FNT-122` Every family/profile publish records immutable revision and affected asset fingerprints.
- `FNT-123` Rollback creates a new revision referencing prior state and does not rewrite history.
- `FNT-124` Rollback with missing binary reports unresolved asset instead of pretending success.
- `FNT-125` Export separates definitions, metadata, optional binaries and provider placeholders according redistribution policy.
- `FNT-126` Export excludes Vault credentials/provider tokens.
- `FNT-127` Import dry run reports family/variant/slug/asset/license conflicts before write.
- `FNT-128` Replace vs merge semantics are explicit and preserve unmentioned variants under merge.
- `FNT-129` Same package reimport is idempotent under selected conflict strategy.
- `FNT-130` Cross-site import remaps storage URLs/IDs and never trusts source attachment IDs.
- `FNT-131` Corrupt/hash-mismatched font package fails before activation.
- `FNT-132` AI/MCP may prepare import/rollback plan but cannot apply protected binary/provider changes without Policy.

### Group 13 — Multisite shared libraries
- `FNT-133` Same family key may exist independently on two sites without cache/asset collision.
- `FNT-134` Network library can expose approved font template/assets to sites without granting sites network-management authority.
- `FNT-135` Network-enforced font definition is immutable to ordinary site admin unless delegated.
- `FNT-136` Network-shared binary uses explicit ownership/storage/redistribution policy and site references remain isolated.
- `FNT-137` Site-local license/provenance metadata cannot overwrite network library evidence.
- `FNT-138` Network aggregate usage report avoids exposing private site content/user data.
- `FNT-139` Site clone remaps local font attachments and environment-specific provider references.
- `FNT-140` Site deletion removes site-owned assignments/metadata without deleting shared binary still referenced by other sites.
- `FNT-141` Shared user account does not imply permission to manage fonts on every site.
- `FNT-142` Network import reports per-site conflicts before applying definitions.
- `FNT-143` AI/MCP site-scoped principal cannot modify network library by passing network scope ID.

### Group 14 — fallbacks/locale/script coverage
- `FNT-144` Latin-only primary font with Cyrillic content selects declared Cyrillic fallback and reports coverage gap.
- `FNT-145` Arabic/RTL script fallback preserves shaping-capable family and does not assume Latin metrics.
- `FNT-146` CJK coverage profile avoids downloading massive font blindly without explicit script/locale policy.
- `FNT-147` Emoji/symbol fallback remains system/profile-specific and is not forced through unsupported text font.
- `FNT-148` Missing glyph detection uses declared coverage metadata/tool evidence and is versioned.
- `FNT-149` CSS fallback stack ordering is deterministic and safely escaped.
- `FNT-150` Locale switch invalidates only locale-dependent subset/assignment cache.
- `FNT-151` Variable font with incomplete script coverage still requires fallback; variable capability does not imply coverage.
- `FNT-152` Webfont load failure leaves readable fallback and no invisible permanent text.
- `FNT-153` Font metric mismatch/CLS risk is reported as measurable risk rather than guaranteed layout defect.
- `FNT-154` Import of fallback profile preserves family references or marks unresolved mappings explicitly.

### Group 15 — page-load/font budget profiles
- `FNT-155` Budget profile defines max font families/variants/preloads/bytes per route class without silently blocking required accessibility fallback.
- `FNT-156` Exceeding budget produces diagnostic/approval warning, not hidden asset deletion.
- `FNT-157` 10/50/100-font library admin listing later measures pagination/query performance with declared environment.
- `FNT-158` Large variable-font metadata parsing later measures CPU/memory under bounded parser profile.
- `FNT-159` Subset job queue uses backpressure and dedupe for identical source+glyph profile.
- `FNT-160` CDN/local delivery cache behavior later measures transfer/cache-hit metrics by exact asset revision.
- `FNT-161` Route-specific preload selection later measures whether preloads reduce or worsen rendering/network contention.
- `FNT-162` Multisite shared-library scale later proves cache/storage key isolation across many sites.
- `FNT-163` Import/export of large library streams/chunks and remains bounded in memory.
- `FNT-164` Metrics/logs remain bounded and do not store full page content.
- `FNT-165` All performance budget results remain NOT EXECUTED until actual browser/server environment is recorded.

### Group 16 — visual/performance golden regressions
- `FNT-166` Golden family/variant scenario resolves regular/bold/italic and deterministic fallback.
- `FNT-167` Golden variable-font scenario validates axis ranges and generated CSS semantics.
- `FNT-168` Golden theme.json/block editor scenario matches intended frontend family/slug without duplicate injection.
- `FNT-169` Golden provider/local-host scenario preserves provenance and blocks unapproved redistribution.
- `FNT-170` Golden privacy/CSP scenario prevents mixed-content/unapproved external origin/secret leakage.
- `FNT-171` Golden multilingual scenario preserves Arabic/Cyrillic/CJK fallback coverage and readable output.
- `FNT-172` Golden preload/subset scenario emits only justified assets and never exposes protected font URL.
- `FNT-173` Golden rollback/import scenario preserves revision history and unresolved mappings accurately.
- `FNT-174` Golden Multisite scenario proves site/network library assignment isolation.
- `FNT-175` Golden missing/corrupt font scenario falls back safely and reports degradation instead of blank text.
- `FNT-176` Golden adversarial AI/MCP scenario cannot claim license compliance, fetch providers or publish network typography outside Policy.

## Execution gate

This document specifies evidence only. **FNT executed remains 0/176.** No font upload/download, provider call, CSS emission test, subset generation, benchmark or runtime mutation is authorized by this protocol.