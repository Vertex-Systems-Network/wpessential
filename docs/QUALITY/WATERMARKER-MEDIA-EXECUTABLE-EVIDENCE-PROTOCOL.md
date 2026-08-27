# WPEssential — Watermarker / Media Rules Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0046, Watermarker exhaustive spec, WordPress image-editor APIs, JobService, Media/offload adapters, ADR-0014.

## 1. Purpose

Define the evidence required before WPEssential can claim a watermark/media derivative profile is supported on a given WordPress host/editor/offload environment.

The original-source checksum invariant is non-negotiable: standard WPE watermark processing must not modify the original upload/source bytes.

## 2. Runtime capability profile

Future WPE media profile records:
- WordPress version;
- selected `WP_Image_Editor` implementation;
- underlying GD/Imagick/library version where observable;
- readable MIME types;
- writable/output MIME types;
- alpha/transparency behavior;
- animation policy;
- EXIF/orientation behavior;
- memory/size limits;
- offload adapter/profile;
- WPE derivative renderer version.

Support is certified by actual profile, not file extension marketing.

## 3. Derivative identity

Canonical derivative identity includes:
- source attachment/source fingerprint;
- Rule Definition revision;
- output size/geometry;
- renderer/editor profile;
- output MIME/quality;
- relevant font/watermark asset fingerprint;
- privacy/access class.

Same identity can be regenerated/reconciled; original source remains immutable.

## 4. Fixture matrix

### WM-01 — JPEG basic text watermark
Derivative correct; original SHA-256 unchanged.

### WM-02 — PNG alpha source
Transparency preserved according to profile; original unchanged.

### WM-03 — PNG watermark overlay alpha
Opacity/placement correct.

### WM-04 — WebP source/read/write
Only passes if runtime editor actually supports required operations/output.

### WM-05 — AVIF source/read/write
Same capability-gated rule.

### WM-06 — GIF static
Behavior explicit.

### WM-07 — Animated GIF
Product either preserves animation under certified adapter or clearly refuses/uses defined first-frame policy; must not silently destroy animation while claiming supported.

### WM-08 — HEIC/HEIF input if environment supports
Capability-gated; no generic claim.

### WM-09 — Unsupported MIME
Safe skip/error, no corrupt derivative.

### WM-10 — EXIF orientation
Visual placement after orientation normalization matches accepted semantics; source untouched.

### WM-11 — Metadata/EXIF privacy policy
Preserve/remove behavior explicit and independent from watermark feature defaults.

### WM-12 — 9 placement presets
Coordinates correct across portrait/landscape/square dimensions.

### WM-13 — Percentage/custom offsets
Bounds/clamping/negative policies deterministic.

### WM-14 — Rotation
Text/image watermark rotation within accepted bounds.

### WM-15 — Tiled watermark
Instance-count safety cap prevents runaway memory/CPU.

### WM-16 — Relative scaling
Very small/large source dimensions handled predictably.

### WM-17 — Minimum dimension rule
No watermark below configured threshold.

### WM-18 — Multiple matching rules
Highest-priority/selected composition semantics deterministic; no accidental stacking.

### WM-19 — Text Unicode/RTL
Font shaping/render support truthfully reflected; unsupported glyphs diagnosed rather than fake success.

### WM-20 — Font missing
Safe failure/fallback policy; no system-font assumption marketed as portable.

### WM-21 — Font license/package profile
Bundled font only if licensing permits; no hidden system font dependency.

### WM-22 — Malicious SVG watermark
Sanitization/rasterization profile prevents script/external-resource/file access; unsupported SVG rejected safely.

### WM-23 — Huge image memory pressure
Preflight/memory budget blocks or safely Jobs/chunks; no fatal that corrupts original.

### WM-24 — Corrupt image
Safe error.

### WM-25 — Output quality 1/100/default
Bounds and inherited/default semantics correct.

### WM-26 — Output-format conversion
Only explicit/certified conversion; no silent JPEG→WebP/AVIF just because core/environment can.

### WM-27 — Registered WP image sizes
Only selected sizes receive WPE derivative; not every sub-size by default.

### WM-28 — Custom WPE rendition
Separate identity/storage metadata; core attachment original/current source not replaced.

### WM-29 — Preview
Uses temp/derived output; preview never mutates attachment/source.

### WM-30 — Batch 10k attachments
Bounded Job/chunk behavior, progress/failed items.

### WM-31 — Batch pause/resume
Committed derivative cursor reconciles; no duplicate/corrupt output.

### WM-32 — Duplicate Job delivery
Same derivative identity prevents uncontrolled duplicates.

### WM-33 — Concurrent upload processing + batch regeneration
Lock/version/fingerprint rules resolve races.

### WM-34 — Source changed while derivative Job queued
Old source fingerprint result cannot become current derivative for new source.

### WM-35 — Rule republished while Job running
Run pins intended Rule revision; new rule generates distinct derivative generation.

### WM-36 — Remove watermark
Only WPE derivative deleted/regenerated; original never reverse-edited.

### WM-37 — Local source + local derivative
Normal filesystem profile.

### WM-38 — Offloaded source fetch
Uses certified media/offload adapter, bounded download, credentials server-side.

### WM-39 — Offload derivative upload failure
Original/local state safe; derivative not marked current until remote commit verified according to adapter.

### WM-40 — CDN stale derivative
Generation/fingerprint path/cache invalidation semantics prevent old watermark being mistaken for new current derivative.

### WM-41 — Private/protected source
Derivative inherits/declares correct access class; no public CDN leak.

### WM-42 — Public teaser from protected source
Only explicit sanitized/authorized derivative policy can make it public.

### WM-43 — Attachment deleted during Job
Safe reconcile/cleanup; no orphan current metadata pointing to nonexistent source.

### WM-44 — Site migration/domain change
Attachment identity not canonical URL; derivative references remap safely.

### WM-45 — Multisite same attachment numeric ID
Site scope prevents cross-site source/derivative collision.

### WM-46 — Image editor implementation switches
Existing derivative remains versioned; regeneration with new renderer profile creates/reconciles distinct generation where visual fidelity can change.

### WM-47 — Editor save error/partial file
Atomic-enough temp→final strategy prevents corrupt final derivative from being marked current.

### WM-48 — Pro expiry
Existing safe generated derivatives remain; editing/regeneration policy follows ADR-0007 without touching originals.

## 5. Visual/quality evidence

For certified combinations capture:
- pixel dimensions;
- file size;
- MIME;
- source checksum before/after;
- derivative checksum;
- placement coordinates;
- alpha/transparency result;
- orientation;
- quality profile;
- renderer/library identity;
- objective failure/warnings.

Visual golden-image comparison may be used with tolerant pixel metrics where different image libraries encode differently; bit-identical output is not assumed unless profile promises it.

## 6. Performance evidence

Measure:
- decode/render/encode time;
- peak memory;
- output ratio;
- Job throughput;
- huge-image preflight behavior;
- concurrent worker contention;
- offload fetch/upload bytes/time.

No quality/performance optimization may violate original-source immutability.

## 7. Pass gates

Fail support profile if:
- original checksum changes;
- unsupported format silently corrupts/converts;
- derivative marked current before complete verified write/upload;
- rule/source race publishes stale derivative as current;
- private media derivative becomes public unintentionally;
- malicious SVG/external resource executes/fetches unsafely;
- job retry creates uncontrolled duplicates;
- offload credentials leak client-side/logs;
- format support is claimed based only on extension rather than editor capability/evidence.

## 8. Required future evidence report

Include:
- WordPress/PHP;
- image editor/library versions;
- MIME capability matrix;
- offload adapter;
- WM-01…WM-48 pass/fail;
- original checksum results;
- visual/golden comparisons;
- memory/performance;
- concurrency/Job results;
- private/public media cases;
- unresolved unsupported formats.

## 9. Current state

**WM fixtures executed: 0/48.**

No image decode/render/save, watermark, derivative, Job, offload fetch/upload or media mutation has run.

## 10. Development gate

Execution requires explicit owner consent under ADR-0014.