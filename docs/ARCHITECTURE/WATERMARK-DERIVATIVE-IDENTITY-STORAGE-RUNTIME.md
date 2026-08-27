# WPEssential — Watermark Derivative Identity & Storage Runtime

Status: **Phase 0 architecture / no implementation authorized**  
Related: Watermarker Exhaustive Spec, ADR-0014.

## Core principle

WPEssential watermarking is a **non-destructive derivative pipeline**. The WordPress attachment/original upload remains unchanged; WPE produces separately addressable derivatives and can regenerate/delete those derivatives independently.

## Source identity

A watermark job identifies its source using:
- attachment ID + stable WPE source identity where available;
- selected source mode (`current attached full`, `original uploaded image`, registered offload source);
- source content fingerprint/hash;
- source dimensions/MIME;
- current attachment metadata generation.

WordPress may keep an original uploaded image separately from a scaled “full” image. WPE therefore does not assume `get_attached_file()` always points to the originally uploaded pixels.

Source selection is explicit per Rule/profile. Regardless of selected source, WPE does not overwrite it.

## Derivative identity

A derivative key is deterministic from immutable inputs such as:

`source_fingerprint + watermark_rule_revision + output_profile + engine_profile_version`

Output profile includes:
- requested WordPress image size/context;
- dimensions/crop mode;
- output MIME/quality profile;
- watermark position/scale/opacity/rotation;
- overlay asset fingerprint;
- color/text/font-safe renderer profile when text watermarking is used.

Changing a Rule draft does not mutate existing derivatives until a new revision is published/regeneration is requested.

## Derivative Registry

Runtime registry candidate fields:
- derivative UUID;
- attachment/source ID;
- source fingerprint;
- rule UUID + published revision;
- output profile key;
- engine profile version;
- storage adapter;
- object/path key;
- MIME;
- width/height;
- byte size;
- checksum;
- state pending/generating/ready/stale/failed/deleting;
- created/updated/last-used;
- error code safe metadata.

Exact table/index shape requires later evidence.

## Storage namespaces

### Local public media

WPE derivative files live in a dedicated WPE namespace under the uploads/filesystem adapter, not beside the original under an ambiguous overwrite name.

Candidate logical layout:

`wpessential/watermarks/<source-bucket>/<derivative-id>.<ext>`

Physical path details are adapter-controlled and not embedded as canonical identity.

### Private/protected source

If source is a Protected Asset, derivative also remains private and uses the Protected Asset/private-origin adapter. WPE never turns a private original into a public uploads URL just because a watermark was applied.

### Offloaded media

Object storage/CDN uses a certified media adapter. Local path may be temporary; canonical derivative identity remains registry/object key.

## Generation pipeline

1. resolve attachment/source under authorization;
2. verify source still matches planned fingerprint;
3. check image-editor/format capability;
4. decode source with bounded memory/dimensions;
5. normalize actual pixel orientation according to image-editor behavior;
6. resolve overlay/text inputs;
7. calculate watermark geometry in output pixel space;
8. apply transformation;
9. encode target format/quality;
10. write temporary derivative;
11. checksum/inspect dimensions/MIME;
12. atomically finalize adapter object where supported;
13. mark registry Ready;
14. only then expose derivative URL/reference.

Failed generation never replaces an already valid source/original.

## WordPress image-editor capability

Use WordPress image-editor abstraction where it can faithfully support the operation/output.

Support is capability-driven, not filename-extension marketing.

A format can be:
- readable + writable certified;
- readable only;
- unsupported;
- supported but animation-lossy.

Do not claim all hosts support all formats merely because WordPress recognizes the MIME.

## Animated images

Default policy:
- preserve animation only through an adapter proven to composite every frame correctly;
- if certified animation preservation is unavailable, mark animated watermarking Unsupported/Requires flattening confirmation;
- do not silently flatten an animated source and call it equivalent.

## SVG

SVG is not an ordinary raster-image watermark target in v1.

Rasterizing or modifying SVG requires separate sanitization/rendering security review. A malicious/untrusted SVG cannot be fed into arbitrary XML/script-capable transformation as if it were PNG.

## Watermark source asset

Overlay image:
- attachment/protected asset/reference;
- fingerprinted;
- immutable per published Rule revision;
- alpha/transparency preserved when output supports it;
- safe MIME/dimensions limits.

Text watermark:
- text content/tokens;
- approved font family/runtime source;
- font file licensing/availability handled separately;
- no arbitrary remote font fetch during render;
- text escaping/layout bounds.

## Position geometry

Canonical positioning stores normalized intent, not precomputed source pixels:
- anchor 9-point/custom normalized x/y;
- margin/inset;
- scale relative to output width/height/short edge;
- max/min watermark pixel bounds;
- opacity;
- rotation;
- tile/repeat settings where enabled.

This allows the same Rule to render consistently across multiple image sizes.

## Integration with WordPress sizes

WPE does not rewrite `_wp_attached_file` or original attachment metadata to point at watermarked derivative.

Consumer integration can request:
- original/core rendition;
- WPE watermarked rendition for a specific context/size.

Listing/Email/Builder adapters select derivative intentionally.

A future “replace frontend image output automatically” mode must still resolve to WPE derivative references through documented hooks and preserve wp-admin/original-media access.

## Staleness/invalidation

Derivative becomes Stale when:
- source bytes/fingerprint change;
- published Rule revision changes;
- overlay asset changes;
- output profile/engine version changes materially;
- offload object missing/corrupt.

Stale does not mean immediately delete. Regeneration can create replacement first, switch references, then cleanup old derivative asynchronously.

## Cleanup

Cleanup policy:
- orphan derivative grace period;
- retain previous generated revision optional;
- delete WPE-owned derivatives only;
- never delete original media because derivative is deleted;
- offload deletion verified/retried through adapter;
- storage failures remain visible.

## Privacy/metadata

By default WPE output should minimize unnecessary metadata leakage. EXIF/GPS preservation/removal must be explicit and supported by renderer; do not claim original metadata survives transformations unless verified.

## Cache/CDN

Derivative URL/object can be immutable/content-versioned by derivative ID/fingerprint. This is preferred over mutating bytes behind the same long-cache URL.

CDN purge is needed only when integration chooses a mutable URL strategy, which should be avoided where possible.

## Performance

- generation belongs in Job Service for batches/large media;
- per-request frontend generation is avoided;
- memory preflight based on dimensions/format/host budget;
- duplicate requests coalesce on derivative identity;
- concurrency lock prevents two workers generating the same derivative simultaneously;
- batch job progress is durable.

## Future executable evidence — NOT AUTHORIZED

- GD/Imagick/editor capability matrix;
- original vs scaled WordPress source behavior;
- JPEG/PNG/WebP/AVIF/etc. read/write output certification;
- EXIF/orientation/transparency;
- animation behavior;
- corrupt/huge image memory guards;
- offload/CDN adapters;
- concurrent generation/dedupe;
- stale regeneration/cleanup;
- private source remains private;
- multisite paths/storage.

No derivative file, registry table or image-processing code has been created.