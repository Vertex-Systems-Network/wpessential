# WPEssential — Media Performance, Responsive Delivery & Field Optimization Expansion

Status: **Phase 0 exhaustive planning addendum / no development authorization**  
Parent surface: **28 — Watermarker / Media Rules**

## 1. Purpose

Expand Surface 28 from derivative/watermark rules into a coherent media transformation + delivery optimization layer without creating a second image runtime.

The existing original-source immutability invariant remains unchanged.

## 2. Architecture split

Surface 28 becomes conceptually three coordinated capability families:
1. **Transformation** — watermark, crop/resize/format/placeholder derivatives;
2. **Responsive Delivery** — `srcset`, `sizes`, Picture/source/fallback semantics;
3. **Performance Intelligence** — loading priority, field metrics, LCP candidate optimization, lazy/eager decisions.

Each family can be enabled independently and must detect native Core behavior before applying an override.

## 3. Core capability detection

Per WordPress version/runtime, detect:
- native `sizes="auto"` support;
- current image loading optimization APIs/filters;
- fetchpriority heuristics already applied by Core;
- responsive image generation availability;
- Picture support profile;
- AVIF/WebP editor support;
- transparency support;
- current media image-editor capabilities;
- WordPress Performance Team plugin coexistence where present.

Rule: if Core already provides equivalent behavior reliably, WPE reports **Core-owned / no duplicate override**.

## 4. Performance Intelligence modes

Modes:
- Core heuristics only;
- WPE server heuristics;
- field-data assisted;
- strict manual rule;
- disabled.

Recommended production mode after certification: field-data assisted with Core-aware fallback.

## 5. Field metrics / URL Metrics profile

Optional privacy-aware RUM records may capture only optimization facts needed for media decisions:
- route/template fingerprint;
- viewport group;
- media element structural fingerprint/XPath-like stable locator candidate;
- intersection/initial-viewport state;
- LCP candidate/result;
- rendered dimensions;
- device pixel ratio bucket where needed;
- image URL/attachment mapping where resolvable;
- timestamp/version/profile.

Do not collect:
- form values;
- user text;
- account identity unless explicitly required and privacy approved;
- full DOM/page content;
- arbitrary query strings containing personal data.

Retention, sampling and consent profile are explicit.

## 6. Viewport evidence

Default breakpoint groups are evidence profiles, not device names hard-coded as truth.

Candidate groups:
- narrow/mobile;
- medium/tablet;
- wide/desktop;
- optional custom ranges.

Before changing lazy/eager behavior based on field data, require adequate evidence for the relevant boundary groups. Missing data means fall back to Core/server heuristics, not guess.

## 7. LCP prioritization

Where field evidence identifies an image LCP candidate:
- remove `loading=lazy`;
- apply `fetchpriority=high` only when justified;
- generate responsive preload with `imagesrcset`/`imagesizes` where supported;
- optionally emit HTTP `Link` response header when safe/certified;
- include media condition for breakpoint-specific preload;
- preserve crossorigin/referrerpolicy when required.

If server/Core heuristics marked an image high-priority but field evidence proves it is not a justified LCP candidate, WPE may remove the extra priority only under a certified compatibility profile.

## 8. Occluded initial-viewport media

For images positioned in the initial viewport but not initially visible (e.g. hidden carousel slide / menu):
- candidate `fetchpriority=low`;
- do not automatically lazy-load when interaction may reveal instantly;
- report decision reason in diagnostics.

This must be evidence-driven, not inferred only from CSS class names.

## 9. Lazy/eager loading

Rules:
- known LCP → never lazy;
- known initially visible → lazy removed;
- confidently below/outside initial viewport → lazy candidate;
- unknown visibility → preserve Core/default;
- hidden initial-viewport interactive media → low priority, generally not lazy;
- third-party scripts/components may mark ownership to prevent unsafe rewrite.

Manual overrides require warning when contradicting collected field evidence.

## 10. `sizes` / responsive image intelligence

Capabilities:
- preserve valid existing `sizes` when appropriate;
- use `sizes=auto` only where native browser/Core profile supports and image is lazy-loaded;
- remove invalid/contradictory `auto` when image is no longer lazy under certified rules;
- block-theme layout-aware sizes calculation where reliable layout info exists;
- classic-theme fallback to Core-provided sizes;
- per-template diagnostics for over/under-sized source selection;
- detect missing/invalid width/height/srcset/sizes.

Do not reimplement a Core-merged feature merely because an older plugin once provided it.

## 11. Picture / art direction

Support diagnostics and output for:
- `IMG`;
- `PICTURE`;
- `SOURCE` with MIME/source-set;
- responsive preload compatibility.

Art-directed multiple media-query source trees require explicit support level; do not claim full optimization until fixture-certified.

## 12. Background images

Profiles:
- inline style background image detectable;
- block/theme background image adapter;
- arbitrary stylesheet background images only through a future CSS/source-map-aware adapter.

Can:
- report LCP background candidate;
- preload supported URL under certified context.

Must not blindly parse/rewrite arbitrary CSS files.

## 13. Video/poster optimization

Advanced media delivery may:
- detect LCP video poster;
- prioritize poster;
- choose a poster derivative close to rendered max dimensions;
- lazy-load video/embed according certified rules;
- keep poster transformation distinct from video binary transcoding.

Video transcoding is not implied by this expansion.

## 14. Modern image formats

Add derivative format policy inspired by current WordPress Performance Team practice:
- source JPEG/PNG;
- output Auto / AVIF / WebP / source format according editor capability;
- optional fallback original-format sub-sizes;
- do not generate modern derivative when it is larger unless policy overrides;
- preserve original source;
- regenerate existing library through explicit Job;
- transparency capability check;
- Picture/fallback output where certified;
- format-specific quality profile;
- diagnostics explaining discarded output.

## 15. Image placeholders

Placeholder profiles:
- none;
- dominant color;
- generated lightweight color/gradient;
- future blurhash/LQIP only after renderer/privacy/performance evidence.

Stored metadata:
- dominant color/token;
- algorithm/version;
- source fingerprint;
- generated date.

Placeholder must not cause layout shift or expose original protected media URL.

## 16. CLS / dimensions

Diagnostics enforce:
- width/height attributes where reliable dimensions known;
- aspect-ratio compatibility;
- derivative dimensions consistent with metadata;
- no stale dimensions after source replacement/regeneration;
- block/editor renderer compatibility.

## 17. Delivery Policy editor

New rule tabs:
1. Source/Scope
2. Transformation
3. Responsive Images
4. Loading Priority
5. Lazy/Eager
6. Modern Formats
7. Placeholder
8. Video Poster
9. CDN/Offload
10. Privacy/Field Metrics
11. Cache
12. Preview
13. Diagnostics

## 18. Priority rule conditions

Conditions may include:
- route/template;
- attachment/media;
- block/component type;
- viewport evidence profile;
- LCP evidence confidence;
- post type;
- page type;
- logged-in/public rendering context where caching permits;
- media source/offload adapter;
- explicit include/exclude.

No user PII in cache keys unless explicitly classified/required.

## 19. CDN / offload

Adapter capabilities:
- source URL resolution;
- responsive derivative URL;
- modern-format derivative URL;
- cache purge/versioning;
- preload URL mapping;
- signed/private delivery compatibility.

WPE must not preload local URLs when the actual delivered asset is a CDN variant if the adapter can resolve it.

## 20. Diagnostics dashboard

Per route/template show:
- candidate LCP image(s);
- evidence confidence;
- current loading/fetchpriority;
- WPE decision;
- Core decision/coexistence;
- preload state;
- responsive source selected by sample viewport;
- `sizes` health;
- modern format availability;
- estimated avoidable bytes based only on actual known derivatives/metrics;
- field-data age/sample count;
- conflicting plugin/filter.

## 21. Regeneration

Batch jobs can regenerate:
- modern formats;
- placeholders;
- WPE derivatives;
- selected native sizes through supported WP regeneration adapter;
- stale rule generations.

Never modify original source in standard mode.

## 22. Coexistence with WordPress Performance Team plugins

If Image Prioritizer / Optimization Detective / Enhanced Responsive Images / Modern Image Formats / Image Placeholders are active:
- detect;
- avoid duplicate processing;
- select `Core/plugin-owned`, `WPE-owned`, or explicit compatibility adapter mode;
- surface conflicting filters/output;
- do not falsify ownership of metrics/options created by another plugin.

## 23. Permissions / Abilities

Additional candidate capabilities:
- `wpe_media_performance_read`
- `wpe_media_performance_manage`
- `wpe_media_field_metrics_manage`
- `wpe_media_delivery_publish`
- `wpe_media_regenerate_formats`

Abilities:
- delivery policy list/get/create/update/validate/publish;
- analyze route/media;
- field-metric summary;
- regenerate plan;
- compatibility inspect.

AI/MCP default: analysis/draft only; publish/regenerate off by default.

## 24. Multisite

- field metrics site-scoped by default;
- network templates may define delivery policy;
- shared CDN adapter can be delegated without cross-site private metadata exposure;
- media attachment ownership remains site-aware;
- network aggregate performance reporting requires explicit privacy profile;
- site deletion cleans WPE-owned metrics/derivatives under lifecycle rules.

## 25. AI Prompt

Examples:
- “Why is this hero image hurting LCP?”
- “Create a safe AVIF/WebP policy with fallbacks.”
- “Find images that are lazy-loaded above the fold.”
- “Optimize carousel image priority without loading hidden slides too early.”
- “Regenerate only stale modern derivatives.”

AI may draft/analyze; it cannot collect field metrics, publish a broad delivery rewrite, or regenerate a library without permission.

## 26. MUST NOT

- do not duplicate Core-merged behavior blindly;
- do not mark every first image `fetchpriority=high`;
- do not preload every likely hero;
- do not lazy-load LCP/known visible images;
- do not collect page/user content as optimization telemetry;
- do not rewrite arbitrary CSS background assets without a certified adapter;
- do not convert originals destructively in standard mode;
- do not claim AVIF/WebP support without actual editor/transparency capability;
- do not treat correlation/field sample as permanent truth after template changes.

## 27. Evidence

Supplemental evidence namespace: **MDP-001…MDP-176**, executed **0/176**.

Existing `WM-01…WM-176` remains canonical derivative/watermark evidence. MDP covers priority, responsive, format, placeholder, field-metric and delivery behavior.