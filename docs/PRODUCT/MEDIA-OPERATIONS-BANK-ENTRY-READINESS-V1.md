# Media Operations — Bank Entry Readiness V1

Surface: **28 / Media Operations**  
Planning issue: **#594**  
Supervisor wave: **#583**  
Exact-main claim anchor: `3f3aedaf6a2a3d568aa36bd8a570346bb65d1698`

Status: **BANK ENTRY READY FOR RESEARCH/SEEDING — NOT BANK_REVIEWED — NO OPTION CONTRACT PROMOTION**

This document is planning evidence only. It does not seed/review the Master Options Bank, create schema-valid Atomic Option Contracts, mutate attachments/files, generate derivatives, apply watermarks, call CDN/provider APIs, rewrite references, purge caches or authorize runtime implementation.

## Current machine truth

- Surface 28 `media` is `UNSEEDED` with **0 normalized Master Options Bank records**.
- The atomic planning ledger marks Surface 28 `ATOMIC_INVENTORY_COMPLETE`; this is planning inventory only, not `OPTION_CONTRACT_COMPLETE` or `UX_CONTRACT_COMPLETE`.
- Canonical ownership assigns watermark/derivative and broader media-operation policy to Surface 28. Generic reference mutation belongs Surface 45 Transform; font assets belong Surface 53 Fonts; remote provider/connection security belongs Surface 23 Connections/Webhooks.
- Exact-main `frameworks/Modules` has no dedicated Media Operations runtime module.

The next valid product gate is Bank seeding + native/market review, not file/attachment mutation.

## Existing in-repo evidence

Primary sources include:

- `docs/MODULES/WATERMARKER-MEDIA-RULES-EXHAUSTIVE-SPEC.md`;
- the canonical option ownership index;
- the 56-surface competitor/parity planning matrix;
- the global atomic-inventory ledger;
- WordPress/media behavior already referenced by the exhaustive spec.

The Watermarker spec establishes the central safety invariant: **standard processing never modifies the original uploaded source file in place**. WPE-owned derivatives or explicitly selected regeneratable sub-sizes are the normal mutation boundary. Permanent baking into the original is outside standard scope and requires separate design/authorization.

## Candidate Bank families

Normalize at least these families independently:

1. **Rule identity/lifecycle** — name/key/status/priority/tags/matching/output strategy/revision.
2. **Target conditions/exclusions** — MIME/content, dimensions, size, animation, alpha, attachment/context, upload origin, related resource and protected-original exclusions.
3. **Text watermark** — safe text/tokens, font reference, size, style, color, opacity, placement-safe rendering.
4. **Image watermark** — approved local media source, scale/opacity/blend/rotation/alpha, sanitized SVG-to-raster only when certified.
5. **Placement** — anchors, offsets, safe margins, clamp, optional tiling with deterministic preview.
6. **Size/derivative variants** — per registered image size/variant enablement and overrides.
7. **Output strategy** — dedicated WPE derivative, selected regeneratable sub-size, registered custom derivative; never original by default.
8. **Format/quality capability** — active editor read/write capability probe, format/quality/lossless/alpha/metadata/color-profile behavior.
9. **Generation identity/integrity** — source fingerprint, Rule revision, editor capability, generated files, stale-generation tracking.
10. **Preview/test** — temporary safe render, before/after, output metadata and warnings without production mutation.
11. **Batch/regeneration** — dry-run impact, bounded chunk/concurrency, stale/missing/failed filters, preserve-old-until-new-verified.
12. **Responsive/delivery policy** — responsive rendition selection, lazy-loading policy, LCP/eager/fetchpriority exceptions, `srcset`/`sizes`, placeholder policy and explicit context limits.
13. **CDN/offload** — read/write/delete/URL/cache-version/purge capabilities of certified adapters; no false processed state when capabilities are missing.
14. **Attachment replacement/supersede/restore** — source/version identity, previous-source preservation, derived-output regeneration and recovery semantics.
15. **Reference graph** — read-only impact/usage graph inside Surface 28; generic cross-content reference mutation delegated to Surface 45 Transform.
16. **Cache regeneration/invalidation** — derivative/browser/CDN versioning or explicit invalidation through owning adapters.
17. **EXIF/orientation/privacy** — orientation handling, GPS stripping candidate, metadata policy, original unchanged.
18. **Animation/special formats** — explicit unsupported/degraded semantics unless a preserving renderer exists.
19. **Protected/private media** — resource access policy remains external; watermark/CDN does not itself make a public file private.
20. **Permissions/Abilities** — read/edit/publish/preview/process/regenerate/cancel/cleanup/settings boundaries; no standard original-source mutation ability.
21. **Multisite/portability/recovery** — site-specific upload roots/variants/adapters, secret-free configuration portability and reversible derivative cleanup.
22. **Performance/reliability** — memory/megapixel guards, background jobs, capability cache, no frontend full-library scans.

## Native WordPress audit required before Bank review

A future native audit must classify current WordPress media behavior precisely:

- attachment metadata and original-file identity;
- registered intermediate image sizes and regeneration semantics;
- `WP_Image_Editor` capability probing and active GD/Imagick behavior;
- MIME detection vs actual decode/encode capability;
- EXIF/orientation behavior;
- `srcset` / `sizes` generation and attachment image attributes;
- native lazy-loading and fetch-priority/LCP heuristics for images;
- upload directory/site/network paths;
- attachment URL/file APIs and offload-plugin interactions;
- multisite upload ownership;
- media delete/replace hooks and reference limitations;
- image format/version support across the supported WordPress/PHP matrix.

Native metadata or a recognized MIME type must never be treated as proof that the active editor can read/write a format safely.

## Market audit required before Bank review

Compare current specialist products/adapters for:

- watermark rule targeting and regeneration;
- derivative/non-destructive output models;
- image conversion/compression/quality;
- responsive/lazy/LCP/fetch-priority management;
- placeholders and CDN/offload;
- attachment replacement/versioning/restore;
- reference update/impact analysis;
- batch queues/retries;
- EXIF/privacy controls;
- cache purge/versioning;
- multisite and remote-media compatibility.

Market features are benchmarks, not automatic acceptance criteria. Unsafe “overwrite original”, public-file-as-private, unbounded library rewrites, hidden reference mutation and optimistic offload success must be rejected or explicitly isolated behind separate high-risk contracts.

## Canonical ownership decisions

| Concern | Canonical owner |
|---|---|
| Watermark rules, derivatives, media delivery attributes, attachment version/replace policy | **Surface 28 Media Operations** |
| WordPress original attachment/source identity | WordPress media owner, consumed by Surface 28 |
| Generic search/replace/reference rewrite execution | **Surface 45 Transform** |
| Font catalog/licensing/file management | **Surface 53 Fonts** |
| Remote connection/CDN credentials and Safe HTTP | **Surface 23 Connections/Webhooks** |
| Protected resource authorization | canonical resource/Policy owner; Surface 28 does not create privacy by watermarking |
| Generic package/data portability orchestration | **Surface 26 Import/Export** |

## Rejected-unsafe / bounded candidates

Reject or tightly bound:

- modifying the original uploaded source file in place in standard mode;
- cleanup that can delete the original;
- assuming MIME support equals editor read/write support;
- unsanitized SVG or arbitrary renderer code;
- arbitrary shortcode/PHP in dynamic watermark text;
- unbounded image megapixels/memory/concurrency;
- synchronous full Media Library processing in a request;
- marking offloaded media processed when required adapter write/delete/URL capability is absent;
- treating a public media URL as protected/private merely because it is watermarked;
- first-frame-only animation processing labelled as preserving animation;
- cross-content reference rewrites hidden inside attachment replacement;
- cache/CDN purge calls without owning adapter capability/evidence;
- irreversible permanent-original watermarking under ordinary permissions.

## Readiness decision

Surface 28 has enough in-repo semantic material to begin disciplined Master Options Bank seeding/native/market review, but exact-main `UNSEEDED / 0` blocks option-contract, UX and runtime promotion.

Next gate: normalize Bank records → complete native audit → complete market/offload audit → resolve ownership/unsafe/deferred items → Bank review with zero unresolved items → derive schema-valid Atomic Option Contracts → re-review UX → separately authorize bounded runtime slices.
