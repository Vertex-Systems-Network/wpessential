# WPEssential — Use Any Font / Advanced Font Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `UAF-001…UAF-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Font upload/local hosting does not prove license or redistribution authority.
- Conversion output is a derived artifact; it never replaces the canonical source font silently.
- Browser/font-format support is capability-detected; a generated file does not prove the client can render it.
- Variable-font axes, static faces and CSS mappings are typed metadata, not inferred from filenames alone.
- Selector assignment is presentation only and never changes authorization or content ownership.
- Builder/theme registration uses adapters; WPE does not create a parallel typography truth engine.
- Multisite font ownership is site/network-policy scoped; matching family names never imply shared authorization.
- Performance claims require measured runtime evidence; preload or local hosting alone is not proof of faster rendering.

## Exact fixtures

### Group 1 — upload/MIME/provenance
- `UAF-001` Upload an allowed font file with declared source/provenance metadata; Asset Registry stores stable source identity, checksum, MIME and owner scope.
- `UAF-002` Reject an executable or non-font payload renamed with a font extension; no asset record is published.
- `UAF-003` Reject a font whose detected MIME/signature conflicts with the selected format unless an explicit supported parser resolves it.
- `UAF-004` Preserve original filename only as display metadata while storage identity uses bounded generated references.
- `UAF-005` Record source type as uploaded/local/provider/imported and keep that provenance through derived variants.
- `UAF-006` Duplicate byte-identical upload in the same scope deduplicates or links intentionally without inventing a second source truth.
- `UAF-007` Same checksum uploaded in another isolated site remains a separate authorization/ownership reference even if storage deduplicates physically.
- `UAF-008` Missing provenance/license fields produce explicit unknown status rather than automatic “safe to use” status.
- `UAF-009` Unauthorized operator cannot upload/register a font even when Media Library upload capability alone is present if UAF Policy denies it.
- `UAF-010` Import from another site/package preserves source/provenance metadata without carrying foreign private storage paths.
- `UAF-011` AI/MCP may draft metadata or suggest classification but cannot upload/register/publish the asset without the same capability/Policy checks.

### Group 2 — TTF/OTF/WOFF/WOFF2 validation
- `UAF-012` Validate a structurally correct TTF and record detected tables/format without executing embedded data.
- `UAF-013` Validate a structurally correct OTF/CFF font and distinguish it from TrueType outlines.
- `UAF-014` Validate WOFF container metadata and decompressed size against configured limits.
- `UAF-015` Validate WOFF2 through the approved parser and reject unsupported/corrupt transforms deterministically.
- `UAF-016` Reject truncated font tables and surface a typed parse failure without registering a usable face.
- `UAF-017` Reject or quarantine font with impossible table offsets/lengths that exceed the actual binary.
- `UAF-018` Bound decompression/decoded-size work so malformed compressed fonts cannot exhaust memory/CPU.
- `UAF-019` Preserve Unicode/name table strings through safe decoding and replace invalid sequences with diagnostics, not silent metadata corruption.
- `UAF-020` Detect duplicate family/style records inside a collection/container and require explicit face selection where ambiguous.
- `UAF-021` Unsupported collection/format variant remains `unsupported` rather than being mislabeled as a known face.
- `UAF-022` Validation output includes parser/version provenance so later parser changes can trigger revalidation without altering the original bytes.

### Group 3 — conversion adapter/output identity
- `UAF-023` Convert an authorized source face to a supported web format through a registered conversion adapter and record source checksum + adapter/version + output checksum.
- `UAF-024` Conversion never overwrites the canonical uploaded source; derived output receives its own artifact identity.
- `UAF-025` Same source + same deterministic conversion profile returns the existing matching artifact or an identical fingerprint, avoiding unnecessary duplicates.
- `UAF-026` Different quality/subset/format profile produces a distinct derived identity and never reuses an incompatible cached artifact.
- `UAF-027` Conversion failure leaves no partially published font URL; temporary artifacts are cleaned/quarantined.
- `UAF-028` Adapter timeout after remote conversion request is classified unknown when provider side effects may exist and reconciled before duplicate billing/retry.
- `UAF-029` Local conversion adapter cannot execute arbitrary user-supplied shell arguments or binaries.
- `UAF-030` Remote conversion adapter sends only approved font bytes/metadata under explicit privacy/provider policy and never Vault secrets in payload logs.
- `UAF-031` Unsupported source license/profile can block conversion even when technically possible; technical capability does not override policy.
- `UAF-032` Conversion metadata records whether hinting/names/metadata were preserved, stripped or transformed rather than claiming byte-equivalence.
- `UAF-033` Deleting a derived conversion does not delete the source font unless a separately authorized lifecycle operation says so.

### Group 4 — variable axes/static faces
- `UAF-034` Detect a variable font’s registered axes and record axis tags, min/default/max values from parsed metadata.
- `UAF-035` Reject configured axis value outside the declared range rather than silently clamping unless profile explicitly allows bounded clamp.
- `UAF-036` Preserve custom/nonstandard axis tags as typed metadata without pretending browser/UI support exists.
- `UAF-037` Generate/select a static instance from variable source only through an explicit instance profile with source-axis provenance.
- `UAF-038` Static instance output gets a derived identity and does not replace the variable source.
- `UAF-039` Named instances map to their actual coordinates; display name alone is not treated as canonical coordinates.
- `UAF-040` A static font misnamed “Variable” is not treated as variable when required variation tables are absent.
- `UAF-041` Multiple variable axes combine deterministically and generated CSS uses only validated coordinates.
- `UAF-042` Import/export round-trips variable-axis metadata and instance profiles without dropping unknown supported axis tags.
- `UAF-043` Builder adapter lacking variable-axis support falls back to a declared static/default profile rather than emitting unsupported controls.
- `UAF-044` Diagnostics distinguish source-variable capability, generated-static availability and frontend/browser support as separate facts.

### Group 5 — weight/style/stretch/oblique mapping
- `UAF-045` Map a regular face to CSS weight/style using parsed font metadata rather than filename heuristics alone.
- `UAF-046` Map bold/semibold/numeric weights without collapsing distinct faces that share a family.
- `UAF-047` Map italic and oblique distinctly when metadata supports the distinction.
- `UAF-048` Map font-stretch only to supported percentage/category semantics and preserve source range when variable.
- `UAF-049` Duplicate family+weight+style+stretch mapping in one active scope triggers conflict resolution instead of nondeterministic face selection.
- `UAF-050` Missing weight metadata uses an explicit fallback/unknown classification and warns before assignment.
- `UAF-051` Synthetic bold/italic browser behavior is not advertised as an actual uploaded face.
- `UAF-052` CSS `@font-face` descriptors are generated from typed face metadata and do not interpolate raw unescaped user strings.
- `UAF-053` Family aliases remain presentation aliases and cannot silently change canonical asset identity.
- `UAF-054` Locale-specific family names resolve to the same canonical face while preserving safe display labels.
- `UAF-055` Theme/builder registration exposes only valid mapped faces and never duplicate conflicting descriptors without diagnostics.

### Group 6 — subsetting/unicode ranges
- `UAF-056` Create a Latin subset from an authorized source using an explicit codepoint/unicode-range profile and record subset provenance.
- `UAF-057` Subset output preserves required glyphs for declared range and fails evidence if requested glyphs are missing.
- `UAF-058` Requested codepoint outside source coverage is reported missing rather than silently substituted.
- `UAF-059` Unicode-range CSS matches the actual generated subset coverage and is not copied from an unrelated preset.
- `UAF-060` Multiple subsets for the same face use non-overlapping or intentionally overlapping ranges with deterministic priority.
- `UAF-061` Combining marks/ligatures required by selected script are included according to shaping profile rather than naive character-only slicing.
- `UAF-062` RTL/Arabic subset preserves required joining/shaping glyph behavior under the selected shaping engine profile.
- `UAF-063` CJK/high-cardinality subset request is bounded by output/resource policy and can fall back to full font/alternative profile explicitly.
- `UAF-064` Subsetting tool/version is recorded so changed output can be traced without rewriting historical artifact identity.
- `UAF-065` Privacy-sensitive “subset from observed page text” mode cannot collect arbitrary user/private content without explicit privacy approval.
- `UAF-066` Removing a subset invalidates only references/caches using that subset and does not delete unrelated faces/source.

### Group 7 — theme.json/Global Styles registration
- `UAF-067` Register an approved font family into supported `theme.json`/Global Styles adapter using stable asset references.
- `UAF-068` Existing theme-owned slug collision is detected and resolved by explicit namespace/override policy rather than silent replacement.
- `UAF-069` Theme without supported Global Styles font registration uses declared compatibility fallback and reports the limitation.
- `UAF-070` Site Editor preview resolves the same active face revision that frontend rendering is configured to use.
- `UAF-071` Removing a WPE registration does not delete theme-native font definitions or modify parent theme files.
- `UAF-072` Network/site scope prevents one site’s font slug registration from mutating another site’s Global Styles state.
- `UAF-073` Generated theme.json fragment contains only declarative supported keys; no arbitrary PHP/JS/server code path is introduced.
- `UAF-074` Font revision change invalidates dependent generated registration/cache fingerprints while preserving prior revision evidence.
- `UAF-075` Unsupported WordPress version/profile reports adapter unsupported instead of claiming registration succeeded.
- `UAF-076` Export/import maps font asset references explicitly and does not assume destination attachment IDs match source.
- `UAF-077` Global Styles registration does not grant permission to publish/activate a theme or alter unrelated typography settings.

### Group 8 — builder registry adapters
- `UAF-078` Register an approved family/face with a certified builder adapter and verify only the target builder registry changes.
- `UAF-079` Builder adapter unavailable/inactive produces a typed no-op/degraded state rather than fatal error.
- `UAF-080` Two builders can reference the same canonical font asset without duplicating source ownership.
- `UAF-081` Builder-specific slug/key collisions are resolved by adapter mapping and never by overwriting unrelated provider entries silently.
- `UAF-082` Builder cache regeneration is requested only through supported adapter APIs and scoped to affected typography assets.
- `UAF-083` Builder cannot expose a face whose source asset is denied/private for that site/user rendering context.
- `UAF-084` Removing builder integration leaves the canonical WPE font and other adapters intact.
- `UAF-085` Imported builder mapping with unknown destination builder remains unresolved/draft rather than guessed.
- `UAF-086` Adapter version/capability change triggers compatibility re-evaluation before publishing unsupported variable/subset features.
- `UAF-087` AI/MCP can propose builder registration but cannot publish broad builder typography changes without normal Policy/approval.
- `UAF-088` Diagnostics identify whether final frontend CSS came from Core/theme/builder/WPE adapter without falsely claiming ownership of third-party output.

### Group 9 — selector assignment/scoping
- `UAF-089` Assign a font profile to an explicit selector scope and emit bounded CSS only for the configured frontend context.
- `UAF-090` Reject malformed selector syntax and prevent CSS breakout/injection through selector fields.
- `UAF-091` Global body assignment is separately identified as broad-scope and can require elevated capability/approval.
- `UAF-092` Assignment to admin/login/editor scopes is blocked unless the owning module/adapter explicitly supports that scope.
- `UAF-093` Multiple assignments with overlapping selectors use documented precedence/order and remain explainable.
- `UAF-094` Role/user conditional selector presentation does not become authorization and avoids protected-data leakage through cache variants.
- `UAF-095` Route/template-scoped assignment includes the relevant context fingerprint in cache/invalidation keys.
- `UAF-096` Deleting an assignment removes only its generated style reference and does not delete the font asset.
- `UAF-097` Assignment referencing archived/missing face enters degraded state with fallback rather than emitting broken URL silently.
- `UAF-098` CSS family names and URLs are escaped/quoted safely and cannot inject additional declarations.
- `UAF-099` Full-page cache/CDN coexistence diagnostics warn when personalized/site-scoped typography could be served to the wrong context.

### Group 10 — preload/font-display/fallback
- `UAF-100` Preload an actually-used critical face only when the resolved route/profile justifies it; unrelated faces are not preloaded.
- `UAF-101` Preload URL matches the final delivered CDN/local artifact and includes correct `as=font`/type/crossorigin semantics.
- `UAF-102` Duplicate preload from theme/plugin/WPE is detected and not emitted twice.
- `UAF-103` `font-display` value is restricted to supported values and inherited/defaulted explicitly.
- `UAF-104` Fallback stack preserves configured system/local alternatives and never becomes empty when custom font fails.
- `UAF-105` Private/protected font asset is never exposed by public preload unless public delivery is explicitly authorized.
- `UAF-106` Preload configuration change invalidates affected document/cache output and not unrelated pages.
- `UAF-107` Face not used above the fold is not labeled performance-critical merely because it is first in registry.
- `UAF-108` Cross-origin CDN font includes compatible CORS profile; mismatch is surfaced as delivery error rather than typography success.
- `UAF-109` Browser unsupported format uses ordered fallback sources where available and does not claim universal compatibility.
- `UAF-110` Diagnostics distinguish configuration intent from measured font load/render behavior; preload alone is not performance proof.

### Group 11 — cache/CDN/fingerprint lifecycle
- `UAF-111` Generated font/CSS artifact fingerprint changes when source bytes or relevant generation profile changes.
- `UAF-112` Metadata-only label change does not churn binary artifact fingerprint when output semantics are unchanged.
- `UAF-113` CDN URL mapping resolves the exact active artifact revision and does not point to stale superseded bytes.
- `UAF-114` Cache purge/invalidation targets affected font/CSS keys and avoids global purge when unnecessary.
- `UAF-115` CDN purge unknown outcome is tracked as unknown/reconcile-required rather than confirmed success.
- `UAF-116` Removing CDN integration safely falls back to authorized local delivery if configured; otherwise reports unavailable.
- `UAF-117` Source replacement generates new derived artifacts before switching active references, preventing broken intermediate state.
- `UAF-118` Old artifact retention follows lifecycle/rollback policy and is not deleted while referenced by retained revisions.
- `UAF-119` Cache key includes site/network ownership and active font revision to prevent cross-site stale bleed.
- `UAF-120` Signed/private font URLs are not cached as public shared URLs unless provider policy explicitly supports that delivery model.
- `UAF-121` Restore/clone revalidates CDN/environment bindings and cannot reuse production-only delivery identifiers blindly.

### Group 12 — license/redistribution policy
- `UAF-122` Record declared license identifier/source/evidence separately from technical file metadata.
- `UAF-123` Missing license evidence remains `unknown`; local hosting does not auto-mark commercial/redistribution rights as allowed.
- `UAF-124` License profile that forbids redistribution blocks export/package embedding while allowing locally permitted use if policy says so.
- `UAF-125` License profile that forbids conversion blocks derived format generation regardless of converter availability.
- `UAF-126` Provider terms/version reference is stored as provenance evidence without making WPE legal authority.
- `UAF-127` Operator acknowledgement is auditable but does not magically convert an unknown/prohibited license into legally valid rights.
- `UAF-128` Export omits restricted binaries and emits unresolved asset/license requirements rather than silently copying them.
- `UAF-129` Multisite shared-library publication requires license profile compatible with the intended network distribution scope.
- `UAF-130` Third-party imported font retains original provenance/unknowns and is not relabeled as WPE-owned.
- `UAF-131` AI/MCP must not assert legal compliance; it may summarize recorded provenance and flag missing evidence.
- `UAF-132` License-policy change re-evaluates affected publishing/export paths without mutating historical source bytes.

### Group 13 — Multisite/site ownership
- `UAF-133` Site-owned font is visible/manageable only inside its site scope unless explicit network sharing Policy grants access.
- `UAF-134` Network library font can be offered to selected sites without exposing unrelated network/private metadata.
- `UAF-135` Site admin cannot edit/delete a network-enforced/shared source asset they do not own.
- `UAF-136` Same family/slug on two sites resolves to each site’s own canonical font reference.
- `UAF-137` Network template may instantiate assignments per site but does not silently share user-specific settings.
- `UAF-138` Site deletion follows configured font asset/reference lifecycle and does not delete network-shared bytes still in use.
- `UAF-139` Site clone creates new environment/site ownership references and reviews CDN/provider bindings before activation.
- `UAF-140` Cross-site import remaps assets by explicit package identity/checksum/provenance, never raw attachment ID.
- `UAF-141` Network-wide font publish requires network capability/Policy and cannot be triggered by ordinary site-admin route.
- `UAF-142` Shared physical storage dedupe does not allow one site to infer another site’s private font inventory.
- `UAF-143` Network usage reporting aggregates bounded metadata and does not expose protected filenames/license documents across sites without authority.

### Group 14 — import/export/migration
- `UAF-144` Export a font definition with source/face/assignment/provenance metadata and portable asset references; no private absolute paths/secrets.
- `UAF-145` Import creates a draft mapping when source binary is absent instead of claiming complete font availability.
- `UAF-146` Import checksum match can link to an authorized existing asset only after destination ownership/Policy validation.
- `UAF-147` Family/slug collision offers create/rename/map/skip semantics with diff rather than silent overwrite.
- `UAF-148` Import of unsupported format/axis metadata preserves it as unresolved diagnostics or rejects explicitly; no silent dropping.
- `UAF-149` Migration from competitor font plugin inventories source paths/assignments first and does not disable legacy output automatically.
- `UAF-150` Migrated selector assignments are normalized/validated before any publish action.
- `UAF-151` Migration never copies remote-provider credentials or private API keys into package data.
- `UAF-152` Dry-run reports affected families/faces/selectors/builders/sites without writing font registrations.
- `UAF-153` Interrupted import is resumable/idempotent by package+item identity and does not duplicate assets.
- `UAF-154` Post-import verification confirms active references resolve to destination assets before legacy mappings may be retired.

### Group 15 — performance/CLS/render timing
- `UAF-155` Measure representative page with no custom font as baseline before attributing any later change to UAF.
- `UAF-156` Measure critical font request count/bytes and distinguish transfer cache hits from cold-load behavior.
- `UAF-157` Verify font swap/fallback does not introduce unacceptable layout shift under the declared typography profile.
- `UAF-158` Compare full font vs subset using actual bytes/glyph coverage and runtime timing, not theoretical file size only.
- `UAF-159` Multiple weights/styles are tested for duplicate downloads caused by conflicting descriptors/preloads.
- `UAF-160` Large font library admin listing remains paginated/bounded and avoids loading every binary/font table into memory.
- `UAF-161` Cache/CDN warm/cold profiles are recorded separately so performance results are reproducible.
- `UAF-162` RTL/CJK/complex-script sample verifies fallback and shaping without visual disappearance during load.
- `UAF-163` Failed font request exercises fallback stack and records rendering usability rather than only HTTP error.
- `UAF-164` Performance evidence records WordPress/theme/builder/browser/font revision environment so result is not generalized beyond fixture.
- `UAF-165` No claim of “faster” or “CLS fixed” is accepted from static planning; only executed measurements can certify it.

### Group 16 — golden typography regression
- `UAF-166` Golden: local regular/bold/italic family renders correct faces across paragraph, heading and button assignments.
- `UAF-167` Golden: variable font weight/width axes produce expected typed CSS and visual samples without static-face conflict.
- `UAF-168` Golden: Latin subset + fallback handles an out-of-subset character through declared fallback without missing-glyph concealment.
- `UAF-169` Golden: Arabic/RTL sample preserves joining/shaping and direction with the selected font/fallback profile.
- `UAF-170` Golden: theme.json registration and builder adapter reference the same canonical font revision without duplicate downloads.
- `UAF-171` Golden: CDN delivery + preload uses final artifact URL and survives cache purge/version switch correctly.
- `UAF-172` Golden: restricted-license font can render under allowed local policy but is excluded from prohibited export/package path.
- `UAF-173` Golden: Multisite network-shared family and site-private family with same display name remain isolated/authorized correctly.
- `UAF-174` Golden: competitor import maps faces/selectors in dry-run, preserves unresolved items and does not disable legacy provider automatically.
- `UAF-175` Golden: corrupt/unsupported font never reaches published CSS and diagnostics retain parse/provenance evidence.
- `UAF-176` Golden: AI/MCP adversarial request to upload/publish an unlicensed font or bypass site Policy remains draft/denied and creates no runtime mutation.

## Runtime truth

This protocol is documentation-only. `UAF-001…UAF-176` are **176/176 documented, 0/176 executed**. No font upload, parsing, conversion, provider transfer, CSS publish, performance measurement, WordPress mutation or AI/MCP runtime call occurred. Development authorization remains **NOT GRANTED / 0/56**.