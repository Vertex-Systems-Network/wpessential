# WPEssential — Watermarker / Media Rules Exhaustive Option Specification

Status: **Phase 0 — Exhaustive Option Spec / planning only / no implementation authorized**  
Edition: **Pro**

## 1. Non-destructive invariant
Standard WPEssential watermark processing **never modifies the original uploaded source file in place**. It creates/replaces only WPE-owned derived renditions or explicitly selected generated sub-sizes.

A destructive “bake permanently into original” mode is outside standard scope and would require a separate ADR/consent because it conflicts with recovery/reversibility goals.

WordPress image processing is accessed through supported image-editor abstractions/capability checks. Format support is environment-dependent; detecting a MIME type does not prove the active image library can read/write every format.

---

# 2. Screens

## 2.1 Rules list
Columns:
- name;
- status;
- priority;
- watermark type;
- target formats/sizes;
- condition summary;
- output mode;
- last processed;
- affected media count estimate/index state;
- health;
- actions.

Filters:
- enabled/disabled;
- text/image watermark;
- output size;
- format;
- upload context;
- errors;
- priority conflict.

Actions:
- Edit;
- Duplicate;
- Preview;
- Test on Media;
- Enable/Disable;
- Regenerate matched derivatives;
- Roll back WPE derived outputs;
- Export;
- Archive/Delete definition.

## 2.2 Media Processing Queue
Columns:
- attachment;
- rule;
- requested outputs;
- state;
- attempts;
- created/started/completed;
- error;
- actions Retry/Cancel/View.

## 2.3 Processed Media
Shows WPE watermark state per attachment without replacing normal Media Library ownership.

## 2.4 Settings / Capabilities
- detected WordPress image editor;
- supported input/output MIME capabilities;
- memory/size health;
- background runner;
- derivative storage path/URL policy;
- default quality;
- cleanup/orphan status.

---

# 3. Rule identity

Fields:
- name required;
- key stable;
- enabled;
- description;
- priority integer/order;
- tags;
- matching strategy;
- output strategy;
- created/updated metadata.

Multiple matching rules strategy:
- highest-priority first rule only — default;
- explicitly compose multiple rules in priority order;
- stop-after-match per rule;
- conflict preview.

Composition is never accidental.

---

# 4. Target conditions

## File/media facts
- MIME type;
- extension advisory only, MIME/content wins;
- minimum width;
- minimum height;
- maximum width/height optional;
- aspect-ratio range;
- file size range;
- animation status where detectable;
- alpha/transparency requirement;
- orientation metadata condition optional.

## WordPress media facts
- attachment ID include/exclude;
- upload date;
- uploader user;
- uploader role/capability;
- attachment parent;
- attachment taxonomy/meta;
- Media Library vs sideload/import context;
- generated image sub-size name.

## Content/application context
- related post type;
- related taxonomy/term;
- relation context;
- membership/download asset classification;
- custom table/media reference through registered adapter.

If a relation/context is unknown at upload time:
- do not guess;
- defer processing until context exists if rule configured for deferred evaluation;
- display pending/context-unresolved state.

## Exclusions
- explicit attachments;
- selected folders/storage origins;
- logos/site icons;
- images below threshold;
- already WPE-derived output;
- protected original sources;
- externally offloaded items where adapter cannot safely generate/store derivative.

---

# 5. Watermark source types

## 5.1 Text watermark
Fields:
- text required;
- dynamic tokens optional from safe allowlist only;
- font source;
- font family;
- font file asset reference if bundled/uploaded under license policy;
- font size: absolute px / relative % of image dimension;
- minimum/maximum computed size;
- weight/style where supported;
- text color;
- opacity 0–100;
- letter spacing if rendering adapter supports;
- line height for multiline;
- max lines;
- alignment;
- rotation -180…180;
- stroke width/color optional capability-dependent;
- shadow offset/blur/color optional capability-dependent;
- background box off default;
- background color/opacity;
- padding;
- corner radius only if renderer reliably supports it.

Tokens must be resolved to plain text and escaped for renderer; no HTML.

## 5.2 Image watermark
Fields:
- WPE/local Media attachment source;
- source version/attachment ID;
- allowed raster source PNG/WebP/JPEG according capability;
- SVG source only if separately sanitized and rasterized through accepted safe renderer;
- scale mode relative to target / fixed px;
- width/height max;
- preserve aspect ratio default on;
- opacity;
- blend mode: Normal default; advanced blend modes only when image library has consistent semantics;
- rotation;
- tint optional future/capability-bound;
- source alpha handling;
- high-DPI source recommendation.

Deleting/replacing watermark source marks dependent rules unhealthy but does not delete original target media.

---

# 6. Placement

Preset anchors:
- top-left;
- top-center;
- top-right;
- center-left;
- center;
- center-right;
- bottom-left;
- bottom-center;
- bottom-right.

Controls:
- horizontal offset px/%;
- vertical offset px/%;
- safe edge margin;
- relative positioning to target image;
- clamp inside canvas default on;
- allow partial overflow off default;
- tile/repeat off default;
- tile spacing;
- diagonal tile pattern;
- random placement not standard default because reproducibility/support suffers.

Preview shows actual resolved coordinates for sample sizes.

---

# 7. Responsive/size-specific behavior

A rule may define variants by target derivative size:
- original-derived large;
- thumbnail;
- medium;
- large;
- registered custom image sizes;
- WPE custom derivative names.

Per-size overrides:
- enable/disable watermark;
- source variant;
- scale;
- opacity;
- placement;
- margin;
- quality.

Default candidate: do not watermark tiny thumbnails unless explicitly selected, because readability/performance value is low.

---

# 8. Output strategy

Modes:
1. **WPE dedicated derivative** — preferred default; original and native WP sub-sizes untouched.
2. **Replace selected generated sub-sizes** — advanced; regeneratable, never original.
3. **Create named custom image size/variant** through registered media strategy.

Controls:
- derivative suffix/key;
- storage folder strategy;
- metadata reference;
- URL resolution via WPE renderer/token;
- fallback to original when derivative generation fails: configurable, default yes for ordinary public image but no if security policy requires watermark presence;
- retain previous generated derivative during regeneration until replacement verified;
- cleanup old generations;
- regenerate on rule publish toggle off by default for huge libraries; impact preview required.

WPE must never silently replace every existing URL globally without dependency-aware rendering integration.

---

# 9. Output format

Options depend on actual WordPress/server image-editor capability probe:
- Same as source — default candidate;
- JPEG;
- PNG;
- WebP;
- AVIF where write support certified;
- other formats only through accepted adapter.

Controls:
- quality 1–100 bounded;
- lossless where supported;
- preserve alpha;
- strip metadata candidate default for derivative privacy/size, but color-profile/orientation behavior must be verified;
- preserve color profile where supported;
- progressive/interlace where library supports;
- filename extension consistent with MIME.

HEIC/HEIF may be detectable/ingestable in WordPress depending environment, but output support must be capability-tested rather than promised by name.

---

# 10. Quality defaults

Global default:
- inherit WordPress/media adapter recommended quality where possible;
- per-rule override;
- per-format override;
- no “100 quality always” default.

Preview estimates output byte size only if actual render occurred; do not fake estimates from dimensions alone.

---

# 11. Preview/Test screen

Inputs:
- choose existing media;
- upload temporary test asset through safe temp flow;
- select representative target sizes;
- rule/variant;
- before/after compare;
- resolved dimensions;
- resolved source/opacity/placement;
- output MIME/quality;
- expected file path/name;
- warnings.

Test render does not modify production attachment derivatives unless user explicitly runs regeneration.

---

# 12. Batch processing

Selectors:
- all attachments matching rule;
- date range;
- post/content relation;
- Query;
- explicit selected attachments;
- missing derivative only;
- outdated rule generation only;
- failed only.

Controls:
- dry-run count/impact;
- chunk size Automatic default;
- concurrency bounded;
- skip if already current generation;
- overwrite WPE derivative;
- preserve previous until new verified default;
- stop on error threshold optional;
- retry profile;
- pause/cancel future items;
- notify completion/failure.

No web request loops over an entire media library synchronously.

---

# 13. Processing states

- pending;
- context_waiting;
- queued;
- processing;
- completed;
- completed_with_warning;
- skipped_not_matched;
- skipped_unsupported;
- failed;
- cancelled;
- stale_needs_regeneration.

Per attachment/rule generation version stored so rule changes can identify stale derivatives.

---

# 14. Original media integrity

Record for processed attachment:
- original attachment ID;
- source file path/reference;
- source fingerprint/checksum metadata where practical;
- WPE derivative list;
- rule/revision used;
- generated timestamp;
- image editor/capability summary;
- errors.

Watermark cleanup only deletes WPE-owned derivatives unless user explicitly selects certified WP sub-size regeneration route.

---

# 15. Media offload/CDN

Adapter capability levels:
- detect offloaded source;
- read source securely;
- write derivative;
- resolve delivery URL;
- delete derivative;
- cache purge/versioning.

If adapter lacks required write/delete capability:
- rule shows degraded/unsupported;
- no false “processed” state;
- local temp copy does not become permanent orphan.

Signed/private protected assets require Membership/Protector storage policy; Watermarker alone does not make files private.

---

# 16. Animated images

Default planning:
- animated GIF/WebP/AVIF not processed unless an accepted animation-preserving renderer exists;
- converting only first frame must be explicit and never labeled preservation;
- UI displays animation warning.

---

# 17. EXIF/orientation/privacy

Controls:
- honor orientation before render;
- metadata strip/preserve policy;
- GPS metadata removal candidate privacy-safe default for WPE derivatives;
- copyright metadata preservation optional according site policy;
- original metadata unchanged.

Exact behavior depends on active editor/library and must be tested.

---

# 18. Dynamic text tokens

Allowlisted examples:
- site name;
- attachment title;
- copyright year;
- uploader display label only if privacy policy permits;
- related post title;
- selected custom field values through typed token engine.

Restrictions:
- no secrets;
- no arbitrary shortcode/PHP;
- missing token fallback behavior: empty / static fallback / skip processing;
- max resolved text length.

---

# 19. Permissions

Candidate:
- `wpe_watermark_read`
- `wpe_watermark_rule_create`
- `wpe_watermark_rule_update`
- `wpe_watermark_rule_delete`
- `wpe_watermark_publish`
- `wpe_watermark_preview`
- `wpe_watermark_regenerate`
- `wpe_watermark_cancel`
- `wpe_watermark_delete_derivatives`
- `wpe_watermark_manage_settings`

No ability to alter original source in standard capability set.

---

# 20. Abilities

- `wpessential/watermark.rule_list/get/create/update/delete/validate/publish`
- `wpessential/watermark.preview`
- `wpessential/watermark.process`
- `wpessential/watermark.regenerate`
- `wpessential/watermark.status`
- `wpessential/watermark.cleanup`

AI default exposure:
- read/preview/validate;
- batch regenerate/cleanup disabled by default.

---

# 21. Events

- watermark.rule.published/disabled;
- watermark.processing.queued/completed/failed;
- watermark.derivative.generated/deleted;
- watermark.source_missing;
- watermark.capability_degraded;
- watermark.batch.completed/failed.

Event payload contains attachment/rule IDs and safe metadata, not binary image data.

---

# 22. Failure/empty states

- no rules;
- no image editor;
- input format unsupported;
- output format unsupported;
- memory/size limit risk;
- watermark source missing;
- font unavailable;
- SVG sanitizer/renderer unavailable;
- context unresolved;
- output unwritable;
- offload adapter read/write failure;
- derivative stale;
- animation unsupported;
- queue runner unhealthy.

Fallback behavior must be explicit per render context.

---

# 23. Performance

- background batch processing;
- capability probe cached/versioned;
- avoid regenerating current generation;
- source image decoded once per batch item where practical;
- bounded memory/image megapixel guard;
- no full Media Library scan per frontend request;
- derivative existence metadata/index;
- CDN/browser cache versioned filenames or explicit invalidation.

---

# 24. Required tests after development consent

- original checksum remains unchanged;
- placement on portrait/landscape/square/tiny images;
- alpha PNG;
- JPEG/WebP/AVIF only where environment supports write;
- unsupported HEIC/animation behavior truthful;
- EXIF orientation;
- metadata/GPS policy;
- text Unicode/RTL fonts;
- missing font/source;
- rule-priority composition;
- stale regeneration;
- interrupted batch;
- offload failure;
- protected/private media interaction;
- derivative cleanup never deletes original;
- URL/render fallback;
- memory guard on huge image;
- permissions/direct-request;
- assets only on relevant WPE screens.

## Maturity
**Exhaustive Option Spec.** Exact image-editor operations, format capability matrix, derivative naming/storage and offload adapters remain implementation/certification work requiring explicit owner development consent.