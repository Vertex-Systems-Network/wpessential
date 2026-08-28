# WPEssential — Watermarker / Media Rules Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0046, ADR-0107, DSR, DVR, ASR, CAC, JobService, PDL, ERR, VER, MLC, Backup, media/offload adapters, Multisite, ADR-0014.

## 1. Purpose

Define evidence required before WPEssential can claim a watermark/media derivative profile is supported on a given WordPress host/editor/offload environment.

The original-source checksum invariant is non-negotiable:

**Standard WPE watermark/media-rule processing never mutates original upload/source bytes. Derivatives are separately identified, versioned and disposable/regenerable outputs.**

## 2. Non-negotiable invariants

1. Original source bytes/checksum remain unchanged.
2. File extension never proves decoder/encoder capability.
3. Derivative currentness requires complete verified local/remote commit.
4. Source fingerprint + Rule revision + renderer profile are part of derivative identity.
5. Private/protected source access does not become public because a derivative exists.
6. Cache/CDN state is never authoritative derivative truth.
7. Provider/offload credentials remain server-side/Vault-owned.
8. Job retry/duplicate delivery cannot create uncontrolled duplicates or stale-current results.
9. Unsupported/corrupt/malicious media fails safely without arbitrary file/network/code access.
10. Paper/static evidence never becomes runtime format/provider certification.

## 3. Fixed fixture matrix

### A. Original fixtures — preserved
- **WM-01** JPEG basic text watermark; original unchanged.
- **WM-02** PNG alpha source.
- **WM-03** PNG overlay alpha.
- **WM-04** WebP capability-gated read/write.
- **WM-05** AVIF capability-gated read/write.
- **WM-06** GIF static behavior explicit.
- **WM-07** Animated GIF preserve/refuse/first-frame policy explicit.
- **WM-08** HEIC/HEIF capability-gated.
- **WM-09** Unsupported MIME safe skip/error.
- **WM-10** EXIF orientation semantics.
- **WM-11** Metadata/EXIF privacy policy separate from watermark.
- **WM-12** Nine placement presets.
- **WM-13** Percentage/custom offsets.
- **WM-14** Rotation bounds.
- **WM-15** Tiled watermark instance cap.
- **WM-16** Relative scaling.
- **WM-17** Minimum-dimension rule.
- **WM-18** Multiple matching rule precedence/composition.
- **WM-19** Unicode/RTL text rendering truth.
- **WM-20** Missing font safe failure/fallback.
- **WM-21** Font license/package profile.
- **WM-22** Malicious SVG watermark blocked/sanitized.
- **WM-23** Huge-image memory pressure preflight.
- **WM-24** Corrupt image safe error.
- **WM-25** Output-quality bounds.
- **WM-26** Explicit/certified format conversion only.
- **WM-27** Selected registered WP image sizes only.
- **WM-28** Custom WPE rendition separate identity/storage.
- **WM-29** Preview never mutates source.
- **WM-30** Batch 10k bounded Jobs/chunks.
- **WM-31** Batch pause/resume reconciliation.
- **WM-32** Duplicate Job delivery dedupe.
- **WM-33** Concurrent upload + batch regeneration race.
- **WM-34** Source changed while Job queued.
- **WM-35** Rule republished while Job running.
- **WM-36** Remove watermark deletes/regenerates derivative only.
- **WM-37** Local source/local derivative profile.
- **WM-38** Offloaded source fetch via certified adapter.
- **WM-39** Offload upload failure not marked current.
- **WM-40** CDN stale derivative generation semantics.
- **WM-41** Private/protected source derivative access class.
- **WM-42** Public teaser only by explicit authorized policy.
- **WM-43** Attachment deleted during Job reconciliation.
- **WM-44** Migration/domain change uses stable attachment identity, not URL.
- **WM-45** Multisite same numeric attachment ID isolation.
- **WM-46** Image-editor implementation switch versions output identity.
- **WM-47** Partial save cannot become current final derivative.
- **WM-48** Pro expiry preserves safe generated outputs/originals.

### B. Rule Definition/versioning/dependency lifecycle
- **WM-49** Draft Rule never affects published runtime.
- **WM-50** Render pins Rule revision for one derivative operation.
- **WM-51** Concurrent Rule publish conflict explicit.
- **WM-52** Rule UUID survives label rename.
- **WM-53** Unknown future Rule schema degrades safely.
- **WM-54** Rule migrator chain preserves semantic settings.
- **WM-55** Missing watermark asset blocks/degrades without touching source.
- **WM-56** Watermark asset replacement changes fingerprint/generation.
- **WM-57** Missing font dependency does not silently use uncontrolled host font unless profile says so.
- **WM-58** Module disable stops new processing and preserves source/derivatives per policy.
- **WM-59** Module re-enable revalidates source/rule/renderer generations.
- **WM-60** Plugin deactivation removes active processing hooks without deleting originals.
- **WM-61** Pro expiry does not turn protected derivatives public.
- **WM-62** Free↔Pro version skew degrades editor/runtime safely.
- **WM-63** Import Rule remaps referenced asset UUID explicitly.
- **WM-64** Clone/transfer regenerates site-scoped derivative/cache identities.

### C. Source identity, file safety and parser security
- **WM-65** MIME sniff/type verification independent of filename.
- **WM-66** Double-extension/polyglot input does not bypass parser policy.
- **WM-67** Truncated header/body mismatch fails safely.
- **WM-68** Image dimension bomb preflight bounded before full decode where possible.
- **WM-69** Decompression/animation frame explosion bounded.
- **WM-70** ICC/profile/metadata corruption cannot trigger uncontrolled failure.
- **WM-71** Embedded thumbnail does not become authoritative source unexpectedly.
- **WM-72** Symlink/path traversal cannot choose arbitrary local source.
- **WM-73** Local path/reference supplied by user cannot read arbitrary filesystem.
- **WM-74** SVG source external entities/resources blocked/refused.
- **WM-75** SVG watermark external image/font references blocked unless explicit safe profile.
- **WM-76** Remote source URL cannot access localhost/private network through media adapter.
- **WM-77** Redirect chain remains within Safe HTTP policy.
- **WM-78** Remote response byte/time limits enforced.
- **WM-79** Content-Type mismatch on remote source fails/degrades.
- **WM-80** Original source checksum revalidated before commit of derivative currentness.

### D. Geometry, typography and composition
- **WM-81** Portrait/square/landscape anchor math stable across odd dimensions.
- **WM-82** Crop/resize order relative to watermark is explicit.
- **WM-83** High-DPI/retina rendition identity includes actual pixel geometry.
- **WM-84** Padding/margin percentage rounding deterministic.
- **WM-85** Negative/overflow placement clamps or rejects explicitly.
- **WM-86** Rotation bounding box does not write outside canvas unexpectedly.
- **WM-87** Text wrapping/max-width semantics deterministic.
- **WM-88** Very long text bounded against memory/CPU runaway.
- **WM-89** Unicode normalization does not silently change configured identity/text unexpectedly.
- **WM-90** RTL/BiDi order verified for certified renderer/font.
- **WM-91** Emoji/color-font support truthfully certified/unsupported.
- **WM-92** Missing glyph diagnostics explicit.
- **WM-93** Image watermark color/alpha conversion profile explicit.
- **WM-94** Multiple-rule composition order deterministic and revision-pinned.
- **WM-95** Rule conflict/overlap diagnostics do not silently stack unknown effects.
- **WM-96** Conditional-rule `true` does not bypass source access/privacy Policy.

### E. DSR/DVR/media ownership and authorization
- **WM-97** Attachment source resolves through declared DSR/media adapter.
- **WM-98** Readable media does not imply writable/delete permission.
- **WM-99** DVR source values used in text watermark are authorized and escaped.
- **WM-100** Secret/protected dynamic values cannot become watermark text.
- **WM-101** User-profile protected fields blocked from generic dynamic text.
- **WM-102** Membership-protected media reauthorizes current access class.
- **WM-103** Derivative generation Ability requires explicit capability/Policy.
- **WM-104** Batch target selection reauthorizes every attachment/site scope.
- **WM-105** Forged attachment/site IDs cannot cross scope.
- **WM-106** Public preview endpoint cannot expose private source bytes.
- **WM-107** Download/render path rechecks current access for private derivative.
- **WM-108** Delete derivative action does not delete original/source attachment.

### F. Jobs, concurrency, commit and reconciliation
- **WM-109** Job payload pins source fingerprint/Rule/renderer profile.
- **WM-110** Worker crash before decode safely retries.
- **WM-111** Crash after temp write before final move cleans/reconciles safely.
- **WM-112** Crash after final local file before metadata commit adopts/verifies rather than duplicates.
- **WM-113** Crash after metadata before offload upload reconciles local/remote states.
- **WM-114** Crash after remote object upload before commit marker verifies/adopts safely.
- **WM-115** Duplicate Job after complete current derivative is idempotent.
- **WM-116** Lease expiry while old worker may continue does not permit stale-current overwrite.
- **WM-117** Newer source wins against older in-flight output.
- **WM-118** Newer Rule revision wins against older in-flight output.
- **WM-119** Two workers same derivative identity use safe claim/fencing semantics.
- **WM-120** Batch cancellation stops future work without deleting valid committed derivatives.
- **WM-121** Pause/resume revalidates source/rule/currentness.
- **WM-122** Failed items remain explicit; batch success is not all-items fiction.
- **WM-123** Job enqueue failure leaves discoverable/reconcilable processing intent where required.
- **WM-124** Backpressure/fairness prevents one media batch starving unrelated jobs unboundedly.

### G. Offload, remote copy, CDN and cache
- **WM-125** Local-only profile separates local currentness from remote availability.
- **WM-126** Offload adapter capability/version certification is explicit.
- **WM-127** Upload checksum/etag semantics are interpreted per adapter, not assumed universal hash.
- **WM-128** Remote object marked current only after verified commit semantics.
- **WM-129** Provider unknown outcome enters reconciliation-required state.
- **WM-130** Credential rotation/revocation handled by Connection/Vault owner.
- **WM-131** Remote delete unknown outcome not reported as confirmed deletion.
- **WM-132** CDN URL is projection, not canonical derivative identity.
- **WM-133** CAC generation invalidates stale derivative metadata/output references.
- **WM-134** CDN purge failure is distinct from derivative generation success.
- **WM-135** Cache key partitions site/access class/rule/source/renderer generation.
- **WM-136** Private derivative cannot be served from public shared cache by key collision.
- **WM-137** Signed/private object delivery expiry does not change derivative ownership.
- **WM-138** Restore of DB without remote object reconciliation detects missing/stale derivative.
- **WM-139** Restore of remote objects without matching DB state does not auto-adopt arbitrary files.
- **WM-140** Offload adapter absence degrades without corrupting source metadata.

### H. Privacy, lifecycle and media integration
- **WM-141** Metadata strip/preserve policy follows privacy classification.
- **WM-142** Faces/location/EXIF handling is explicit; watermark does not imply privacy sanitization.
- **WM-143** Support logs contain fingerprints/status, not private media bytes/secrets.
- **WM-144** Privacy exporter includes only eligible WPE rule/runtime metadata.
- **WM-145** Privacy eraser does not delete shared/public source media blindly.
- **WM-146** Attachment trash vs permanent delete semantics are distinct.
- **WM-147** Source replacement plugin behavior invalidates derivative by source fingerprint.
- **WM-148** WordPress regenerate-thumbnails integration does not duplicate WPE derivatives uncontrollably.
- **WM-149** Core image-edit rotation/crop creates new source/current fingerprint semantics explicitly.
- **WM-150** Backup includes/excludes derivative data according to declared recoverability strategy.
- **WM-151** Uninstall cleanup deletes WPE-owned derivatives only according to explicit policy.
- **WM-152** Module disable/expiry leaves originals and required deployed derivatives safe.
- **WM-153** Site archive/deactivate stops automatic new processing as declared.
- **WM-154** Site deletion removes site-owned derivative state without touching other sites.
- **WM-155** Site clone copies source/rules but regenerates execution/currentness identities.
- **WM-156** Domain/CDN change remaps projections without treating URL as identity.

### I. Multisite, scale, quality and regression
- **WM-157** Same attachment ID on 100 sites has zero derivative/cache collision.
- **WM-158** Network rule can target sites only under explicit network authority.
- **WM-159** Site admin cannot mutate network-owned rule/assets.
- **WM-160** Network batch enumerates authorized sites with bounded fan-out.
- **WM-161** Current-blog switching never becomes durable target authority.
- **WM-162** 100/1k/10k-site derivative metadata profile remains scope-safe.
- **WM-163** 10k/100k/1M attachment batch throughput measured with Job fairness.
- **WM-164** Small/medium/huge image memory/time envelopes measured per renderer.
- **WM-165** JPEG/PNG/WebP/AVIF certified quality comparisons record objective/tolerant metrics.
- **WM-166** Renderer/library upgrade regression records visual differences and versioned identity.
- **WM-167** Compression/quality optimization never mutates source or bypasses explicit output profile.
- **WM-168** Cache cold-start/mass regeneration avoids stale-current correctness compromise.
- **WM-169** Concurrent source edits + rule publish + batch workers preserve latest-current semantics.
- **WM-170** Malicious corpus (polyglot/SVG/bomb/path/SSRF) yields zero arbitrary file/network/code access.
- **WM-171** Private-media corpus yields zero public derivative/cache/CDN disclosure.
- **WM-172** Corrupt derivative metadata/file yields diagnostics/regeneration, not source mutation.
- **WM-173** Failure injection at each commit boundary preserves truthful currentness/recovery state.
- **WM-174** Cross-editor GD↔Imagick profile does not claim bit-identical output unless proven.
- **WM-175** Full upgrade/restore/offload/version-drift regression retains original source checksum invariant.
- **WM-176** Certification report pins exact WordPress/PHP/editor/library/MIME/offload/Rule/renderer/topology profile; no generic media support beyond tested evidence.

## 4. Independent certification classes

Future evidence records separately:
- `WM-F` format/editor capability;
- `WM-G` geometry/text/composition;
- `WM-S` source/file/parser security;
- `WM-J` Job/concurrency/commit correctness;
- `WM-O` offload/CDN/cache;
- `WM-A` access/privacy/lifecycle;
- `WM-M` Multisite;
- `WM-P` performance/quality/regression.

Passing one class never promotes another.

## 5. Stop-the-line gates

Certification fails if:
- original source checksum changes;
- unsupported/malicious input causes arbitrary file/network/code access or silent corruption;
- derivative is marked current before verified complete local/remote commit;
- stale source/rule worker overwrites newer current generation;
- private media derivative becomes public unintentionally;
- offload/Vault credentials leak;
- duplicate/retry creates uncontrolled derivative duplicates;
- format support is claimed from extension rather than tested editor capability;
- cross-site derivative/cache collision occurs.

## 6. Required future evidence report

Include exact runtime/editor/library/MIME/offload/topology profile, WM-01…WM-176 pass/fail/NA, source/derivative checksums, parser/adversarial results, visual/quality measurements, Job/concurrency failures, offload/cache/CDN results, privacy/private-media evidence, Multisite/lifecycle and unsupported formats.

## 7. Current state

**WM fixtures documented: 176.**  
**WM fixtures executed: 0/176.**  
Format/editor/offload/runtime certifications: **0**.

No image decode/render/save, watermark, derivative, Job, offload request, cache/CDN mutation, media deletion, Multisite action or benchmark has run.

## 8. Development gate

Execution requires explicit owner consent under ADR-0014 and the Approval Ledger. `continue` remains planning-only.
