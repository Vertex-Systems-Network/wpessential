# WPEssential — Media Performance, Responsive Delivery & Field Optimization Exact Executable-Evidence Protocol

Status: **Planning-only exact protocol / NOT EXECUTED**  
Date: 2026-08-29  
Work package: **WP114**  
Namespace: **MDP-001…MDP-176**

## Contract

This protocol expands the fixed MDP groups into exact individual fixtures for Surface 28’s performance/delivery expansion. It supplements canonical watermark/derivative evidence (`WM`) and does not replace WordPress Core, browser, CDN/private-media or field-metric authorities.

Truth boundaries: priority/LCP inference ≠ measured CWV improvement; field sample ≠ permanent truth; preload/lazy hint ≠ guaranteed browser behavior; private media must not leak through optimization; Core/Performance-Team ownership must be detected/composed; original source remains immutable in standard mode.

Execution status: **176/176 documented; 0/176 executed; runtime certification 0.**

## Group 1 — Core capability/version detection — MDP-001…011
- **MDP-001** — Detect current WordPress version/profile before enabling any media optimization override.
- **MDP-002** — Detect native `sizes="auto"` support and expose it only when the current Core/browser profile permits.
- **MDP-003** — Detect Core loading-optimization APIs/filters before WPE claims ownership of lazy/eager decisions.
- **MDP-004** — Detect Core `fetchpriority` heuristics and avoid duplicate high-priority output when Core already owns it.
- **MDP-005** — Detect responsive image/srcset generation availability and report unsupported/degraded state explicitly.
- **MDP-006** — Detect Picture/source output support profile without assuming every theme/renderer preserves it.
- **MDP-007** — Detect AVIF/WebP editor capability per format/operation rather than by extension alone.
- **MDP-008** — Detect transparency-preserving support before generating modern derivatives from transparent sources.
- **MDP-009** — Detect active WordPress Performance Team plugin components and classify Core/plugin-owned vs WPE-owned behavior.
- **MDP-010** — Capability cache invalidates on WordPress/plugin/image-editor version/profile change.
- **MDP-011** — Unknown/unsupported capability path fails to Core/default and does not guess from old plugin behavior.

## Group 2 — Field-metric collection/privacy/sampling — MDP-012…022
- **MDP-012** — RUM metric schema contains only approved optimization facts and excludes form/user-entered content.
- **MDP-013** — Route/template fingerprint is stable enough for aggregation but does not embed arbitrary sensitive query strings.
- **MDP-014** — Viewport group is coarse/bounded and not used as device identity/fingerprinting authority.
- **MDP-015** — Media structural locator stores bounded non-content fingerprint and does not capture full DOM/page text.
- **MDP-016** — LCP candidate/result record is observational browser evidence with timestamp/profile/version provenance.
- **MDP-017** — Rendered dimensions/DPR are bucketed/minimized according to privacy profile.
- **MDP-018** — Attachment mapping stores canonical media identity where resolvable; raw signed/private URL is not retained unnecessarily.
- **MDP-019** — Sampling rate is explicit/versioned and report displays sample count rather than treating sparse data as certainty.
- **MDP-020** — Consent/privacy gate blocks collection when required; absence of consent does not fabricate “not LCP” evidence.
- **MDP-021** — Retention expiry/downsampling is idempotent and removes old raw metrics according to owner policy.
- **MDP-022** — Logs/exports redact identifiers/URLs classified protected and cannot expose private media access tokens.

## Group 3 — Viewport evidence confidence — MDP-023…033
- **MDP-023** — Narrow/medium/wide groups are configurable evidence buckets, not device-name truth.
- **MDP-024** — Confidence calculation records sample count, freshness, consistency and template/profile revision inputs.
- **MDP-025** — Missing evidence falls back to Core/server heuristic rather than guessing visible/below-fold state.
- **MDP-026** — Conflicting samples across same route/template produce uncertain state, not arbitrary majority override without threshold policy.
- **MDP-027** — Template revision invalidates stale viewport evidence or lowers confidence according to explicit rule.
- **MDP-028** — Dynamic/personalized layout evidence is segmented only when privacy/cache profile supports; otherwise optimization remains conservative.
- **MDP-029** — Authenticated/private route metrics cannot influence public output if privacy/cache segmentation is unsafe.
- **MDP-030** — One extreme viewport sample cannot classify all viewport groups.
- **MDP-031** — Evidence-age threshold marks old samples stale and stops field-driven override when expired.
- **MDP-032** — Manual override contradicting strong field evidence emits warning but remains governed explicit configuration.
- **MDP-033** — Diagnostic distinguishes observed, inferred, Core-owned, manual and unknown viewport facts.

## Group 4 — LCP image prioritization/preload — MDP-034…044
- **MDP-034** — Certified LCP candidate has `loading=lazy` removed when WPE owns decision.
- **MDP-035** — `fetchpriority=high` is emitted only for justified candidate and duplicate high priority is suppressed.
- **MDP-036** — Multiple plausible LCP candidates follow bounded priority policy; WPE does not mark every hero/high image as high.
- **MDP-037** — Responsive preload uses matching `imagesrcset`/`imagesizes` only when browser/Core profile supports semantics.
- **MDP-038** — Breakpoint/media-conditioned preload matches the selected candidate/source tree and does not preload all variants.
- **MDP-039** — HTTP Link header preload, if enabled, is emitted only under certified server/cache profile.
- **MDP-040** — Crossorigin/referrerpolicy attributes are preserved consistently between preload and delivered image where required.
- **MDP-041** — CDN/offload mapping resolves actual delivered URL before preload; local origin is not preloaded when adapter owns transformed URL.
- **MDP-042** — Protected/private media is never preloaded into unauthorized/public page response or shared cache.
- **MDP-043** — LCP candidate whose source becomes missing/unavailable degrades without stale preload pointing to dead/private URL.
- **MDP-044** — Diagnostics report reason/evidence/owner for each priority/preload decision without claiming measured CWV gain.

## Group 5 — Unjustified priority removal — MDP-045…055
- **MDP-045** — WPE may remove extra Core/server high priority only under certified compatibility profile plus sufficient contrary evidence.
- **MDP-046** — Priority removal does not occur from a single stale/low-confidence field sample.
- **MDP-047** — If ownership is third-party/plugin-owned, WPE reports conflict rather than rewriting its priority attribute blindly.
- **MDP-048** — Manual force-high setting is preserved unless policy explicitly allows field data to override manual intent.
- **MDP-049** — Template revision clears previously learned “not LCP” decision before applying removal.
- **MDP-050** — Removing fetchpriority does not automatically add lazy loading; loading and priority are separate decisions.
- **MDP-051** — Priority reduction for one viewport group does not affect another group without evidence.
- **MDP-052** — Responsive source change re-evaluates candidate identity rather than carrying priority by stale URL alone.
- **MDP-053** — Cached HTML invalidates when priority decision generation changes.
- **MDP-054** — Diagnostics expose both original Core/third-party decision and WPE override reason.
- **MDP-055** — Regression fixture defines stop-the-line when measured authorized evidence later shows LCP degradation beyond budget.

## Group 6 — Occluded initial-viewport handling — MDP-056…066
- **MDP-056** — Hidden carousel/menu media is classified occluded only from certified structural/field evidence, not class-name heuristic alone.
- **MDP-057** — Occluded initial-viewport image may receive low priority only under explicit profile and evidence.
- **MDP-058** — Occluded interactive media is not automatically lazy-loaded when immediate interaction can reveal it.
- **MDP-059** — First visible carousel slide remains independently prioritized from hidden siblings.
- **MDP-060** — CSS/display/state change invalidates occlusion evidence when template/component revision changes.
- **MDP-061** — Client-side component-owned marker can prevent WPE rewrite where third-party component manages preload/loading.
- **MDP-062** — Multiple hidden responsive variants do not all receive preload/priority hints.
- **MDP-063** — Occluded private media does not leak through diagnostic/public preload metadata.
- **MDP-064** — Unknown initial visibility preserves Core/default behavior.
- **MDP-065** — Diagnostics record `occluded`, `visible`, `unknown` separately from lazy/eager/priority actions.
- **MDP-066** — Accessibility/user preference that reveals hidden content early is considered in certified component profile before aggressive deferral.

## Group 7 — Lazy/eager behavior — MDP-067…077
- **MDP-067** — Known LCP image is never lazy under WPE-owned certified decision.
- **MDP-068** — Known initially visible image has lazy removed while preserving other image attributes.
- **MDP-069** — Confident below-fold image is lazy candidate, not guaranteed browser-delay claim.
- **MDP-070** — Unknown visibility preserves Core/default loading attribute.
- **MDP-071** — Manual eager/lazy override is validated and warns when contradicting strong evidence.
- **MDP-072** — Third-party ownership marker prevents double rewrite of loading behavior.
- **MDP-073** — Loading decision is recomputed when template/media/source generation changes.
- **MDP-074** — Lazy behavior does not remove width/height/aspect-ratio needed for CLS stability.
- **MDP-075** — Protected image lazy/eager output remains behind same authorization/private delivery contract.
- **MDP-076** — Cached markup segments decisions correctly where logged-in/public rendering differs or else uses non-personalized safe policy.
- **MDP-077** — Diagnostics explain final loading attribute source: Core, WPE heuristic, field, manual, third-party or unknown.

## Group 8 — `sizes=auto` / responsive sizes — MDP-078…088
- **MDP-078** — Existing valid `sizes` is preserved when WPE has no stronger certified layout rule.
- **MDP-079** — `auto` is emitted only for supported browser/Core profile and compatible lazy-loaded image semantics.
- **MDP-080** — Removing lazy loading from an image revalidates/removes contradictory `auto` usage according to certified profile.
- **MDP-081** — Block-theme layout-aware sizes uses stable layout metadata and falls back to Core sizes when unavailable.
- **MDP-082** — Classic-theme profile does not invent exact layout width without evidence; Core/default sizes remain authoritative.
- **MDP-083** — Invalid sizes syntax is reported and WPE does not silently generate malformed replacement.
- **MDP-084** — Srcset candidate widths and sizes rule are checked for impossible/undersized selection diagnostics.
- **MDP-085** — Dynamic container/layout changes invalidate generated layout-aware sizes generation.
- **MDP-086** — Art-directed Picture sources maintain per-source sizes semantics rather than applying one incompatible rule blindly.
- **MDP-087** — Private/CDN URL mapping does not alter width descriptors or expose protected source URL.
- **MDP-088** — Diagnostics can compare sample viewport source selection without claiming browser outcome identical across all clients.

## Group 9 — Picture/source/art direction — MDP-089…099
- **MDP-089** — IMG-only renderer path preserves valid responsive attributes and fallback source.
- **MDP-090** — Picture tree validates Source ordering/media/type/srcset syntax before WPE optimization.
- **MDP-091** — MIME-type source does not claim support unless actual derivative exists and browser/fallback path is valid.
- **MDP-092** — Art-directed media-query sources are treated as distinct candidates for priority/preload decisions.
- **MDP-093** — Responsive preload for Picture uses certified matching source/fallback semantics or is disabled with diagnostic.
- **MDP-094** — Missing modern source leaves original-format IMG fallback intact.
- **MDP-095** — Source tree generated by third-party component is not rewritten without certified adapter ownership.
- **MDP-096** — Duplicate/overlapping Source media conditions are diagnosed and not “optimized” by arbitrary reorder.
- **MDP-097** — Private/signed source URLs preserve authorization and are not copied into public cache/diagnostic output.
- **MDP-098** — Picture source regeneration updates source fingerprint and invalidates stale cache/preload metadata.
- **MDP-099** — Golden art-direction fixture defines expected semantics across narrow/medium/wide groups without claiming pixel-identical browser behavior.

## Group 10 — Background-image/video-poster behavior — MDP-100…110
- **MDP-100** — Inline-style background image is detected only through safe parser and URL validation.
- **MDP-101** — Block/theme background image uses registered adapter metadata, not arbitrary CSS scraping.
- **MDP-102** — Arbitrary stylesheet background remains unsupported without CSS/source-map-aware certified adapter.
- **MDP-103** — Background LCP candidate may be reported/preloaded only when source URL/context ownership is known.
- **MDP-104** — Background preload cannot expose private/signed media into unauthorized response.
- **MDP-105** — Multiple CSS background layers are not blindly rewritten; unsupported complexity is diagnosed.
- **MDP-106** — Video LCP poster detection treats poster image separately from video binary/transcoding.
- **MDP-107** — Poster derivative selection uses rendered max dimensions/profile and retains fallback original poster.
- **MDP-108** — Video/embed lazy strategy is certified separately; image optimization does not imply video transcoding support.
- **MDP-109** — Hidden/occluded video poster priority follows same evidence-confidence rules as image media.
- **MDP-110** — Template/source change invalidates learned background/poster candidate evidence.

## Group 11 — AVIF/WebP/fallback generation — MDP-111…121
- **MDP-111** — Format policy Auto/AVIF/WebP/source selects only editor-supported output for the exact source profile.
- **MDP-112** — Original source remains immutable in standard mode; modern outputs are derivatives.
- **MDP-113** — Transparency source generates only format/profile that preserves required alpha semantics.
- **MDP-114** — Modern derivative larger than source-format equivalent is discarded unless explicit policy overrides with diagnostics.
- **MDP-115** — Format-specific quality setting is bounded/versioned and stored in derivative provenance.
- **MDP-116** — Failed derivative generation leaves valid fallback image path and truthful failed/degraded state.
- **MDP-117** — Existing library regeneration is planned through JobService with idempotent derivative identity/checkpoint.
- **MDP-118** — Source replacement changes fingerprint and invalidates stale modern derivatives/metadata.
- **MDP-119** — Picture/fallback output references only generated existing derivatives and never invents AVIF/WebP URL.
- **MDP-120** — CDN/offload derivative mapping preserves format/fallback semantics and private delivery contract.
- **MDP-121** — Diagnostics explain skipped/discarded format with capability/size/transparency/failure reason.

## Group 12 — Placeholder/dominant-color/CLS dimensions — MDP-122…132
- **MDP-122** — Dominant-color placeholder derives from authorized local media/source and stores algorithm/version/source fingerprint.
- **MDP-123** — Placeholder metadata contains color/algorithm only and does not expose protected original URL.
- **MDP-124** — Lightweight gradient/color placeholder does not embed arbitrary remote assets.
- **MDP-125** — Future blurhash/LQIP path remains disabled until separate renderer/privacy/performance evidence exists.
- **MDP-126** — Width/height attributes reflect reliable derivative metadata and prevent avoidable layout shift where applicable.
- **MDP-127** — Aspect-ratio output remains consistent with actual derivative crop/resize profile.
- **MDP-128** — Source replacement/regeneration invalidates stale dimensions/placeholder metadata.
- **MDP-129** — Block/editor renderer output preserves dimension attributes or reports adapter limitation.
- **MDP-130** — Placeholder failure falls back to no placeholder without hiding/breaking image layout.
- **MDP-131** — Private media placeholder is itself classified so shared/public cache cannot leak sensitive visual content beyond approved policy.
- **MDP-132** — Diagnostics separate measured CLS outcome from structural dimension correctness; dimensions alone are not proof of CWV success.

## Group 13 — CDN/offload/cache/private media — MDP-133…143
- **MDP-133** — CDN adapter resolves canonical delivered source/derivative URL from attachment identity without accepting arbitrary request URL.
- **MDP-134** — Responsive derivative URL mapping preserves srcset width descriptors and source revision.
- **MDP-135** — Modern-format CDN mapping is capability/version aware and does not assume every edge can serve every format.
- **MDP-136** — Cache purge/versioning uses adapter contract and unknown purge outcome is not claimed successful.
- **MDP-137** — Preload points to final mapped asset when adapter provides it; local duplicate preload is suppressed.
- **MDP-138** — Signed/private media URL is generated per authorization/delivery profile and never persisted into public cache longer than allowed.
- **MDP-139** — Field metrics/logs redact signed token/query portions of private URLs.
- **MDP-140** — Shared CDN credential remains Vault/adapter owned and never interpolates into frontend markup.
- **MDP-141** — Public/private cache keys separate access contexts so authorized derivative response cannot leak to guest.
- **MDP-142** — CDN unavailable/error falls back only according to configured safe fallback; no private-origin bypass without authorization.
- **MDP-143** — Site/network CDN sharing does not expose another site’s private media metadata or cache keys.

## Group 14 — Coexistence with Performance Team/Core features — MDP-144…154
- **MDP-144** — Detect Image Prioritizer-equivalent ownership and avoid duplicate priority rewrite.
- **MDP-145** — Detect Optimization Detective/field metric ownership and do not falsify source/provenance of third-party observations.
- **MDP-146** — Detect Enhanced Responsive Images/Core `sizes=auto` ownership and suppress duplicate rule generation.
- **MDP-147** — Detect Modern Image Formats ownership and avoid creating competing derivative sets unless explicit adapter mode selected.
- **MDP-148** — Detect Image Placeholders ownership and avoid duplicate placeholder metadata/output.
- **MDP-149** — `Core/plugin-owned`, `WPE-owned`, `compatibility adapter` mode is explicit per capability family.
- **MDP-150** — Conflicting filters/attributes are diagnosed with ownership confidence and WPE does not repeatedly override at higher priority by default.
- **MDP-151** — Third-party option/data presence is observational and WPE does not modify it unless adapter contract explicitly allows.
- **MDP-152** — Deactivating third-party owner triggers capability re-detection before WPE takes ownership.
- **MDP-153** — Activating a new Core/plugin owner causes WPE override to back off according to coexistence policy.
- **MDP-154** — Upgrade regression verifies no duplicate preload/fetchpriority/srcset/modern-format/placeholder output across supported coexistence profiles.

## Group 15 — Regeneration/lifecycle/Multisite — MDP-155…165
- **MDP-155** — Regeneration Plan selects derivative families/media/site scope and previews counts/bytes without changing originals.
- **MDP-156** — Regeneration uses stable job/item identity so retry does not duplicate derivative artifacts.
- **MDP-157** — Partial regeneration records per-media outcome and supports resume/reconcile.
- **MDP-158** — Module disable preserves original/native media output and stops WPE-owned runtime rewrites safely.
- **MDP-159** — Uninstall removes WPE-owned derivatives/metrics only according to retention choice and does not delete originals/native sizes accidentally.
- **MDP-160** — Field metrics are site-scoped by default in Multisite; cross-site aggregation requires explicit privacy profile.
- **MDP-161** — Network delivery policy template instantiates site bindings and does not share private attachment identity across sites.
- **MDP-162** — Shared CDN adapter may share credential through network Policy but keeps site/media authority separate.
- **MDP-163** — Site deletion cleans/archives WPE metrics/derivatives according to lifecycle without deleting shared network/provider assets blindly.
- **MDP-164** — Clone/staging copy quarantines production field metrics/private CDN tokens and re-resolves environment/provider mappings.
- **MDP-165** — Cache/job/metric keys include site/environment namespace to prevent cross-site collision.

## Group 16 — Performance/regression/large media library — MDP-166…176
- **MDP-166** — 10k-media library profile defines derivative/metadata query and list-diagnostic budgets.
- **MDP-167** — 100k/1M-media profile defines resumable regeneration, storage, queue/backpressure and index expectations without preclaiming throughput.
- **MDP-168** — High-traffic route profile defines markup decision/cache budget with no per-request N+1 field-metric query.
- **MDP-169** — RUM burst profile defines sampling/rate/storage backpressure so telemetry cannot overload frontend/database.
- **MDP-170** — Performance evidence later must compare LCP/CLS/request bytes against baseline; static configuration alone cannot pass CWV gate.
- **MDP-171** — Regression fixture covers wrong lazy on LCP, over-preload, duplicate high priority, stale sizes and missing dimensions as stop-the-line candidates.
- **MDP-172** — Private-media regression covers preload, placeholder, metrics, CDN, srcset and cache leakage paths.
- **MDP-173** — Browser/WP/image-editor version matrix records exact hardware/software/profile when executable evidence is eventually run.
- **MDP-174** — Failure of field metrics/CDN/image editor degrades to Core/native safe behavior without false optimization claim.
- **MDP-175** — AI adversarial fixture asks to mark every image high priority, expose metrics/private URLs or destructively convert originals; expected result is bounded draft/refusal.
- **MDP-176** — Golden flow covers Core detection → field evidence → LCP/loading/sizes/Picture/format/placeholder/private CDN decision → regeneration/diagnostics, with observed hints separated from measured performance truth.

## Completion truth

`MDP-001…MDP-176` are **176/176 documented and 0/176 executed**. No Core Web Vitals, browser, WordPress Performance Team, image-editor, CDN or private-delivery certification is implied.