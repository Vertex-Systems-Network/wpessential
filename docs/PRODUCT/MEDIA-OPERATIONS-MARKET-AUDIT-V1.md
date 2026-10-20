# WPEssential — Media Operations Market Audit V1

Status: MARKET_AUDITED — planning evidence only
Surface: 28 — Media Operations
Source issue: #1458
Snapshot: 2026-10-20

## Boundary

Surface 28 owns media rules, watermark/derivative policy, media delivery attributes and attachment-version policy. WordPress owns uploaded source identity and its image-editor/media APIs. Surface 45 owns generic reference mutation; Surface 53 owns font assets/licensing; Surface 23 owns remote connection credentials and Safe HTTP; Surface 26 owns generic package/data portability; Policy/Protected Asset owners retain authorization for private media.

## Current product evidence

| Kind | Product/source | Evidence and relevant feature area |
|---|---|---|
| Primary | [Image Watermark](https://wordpress.org/plugins/image-watermark/) | Text/image overlays on new uploads and bulk processing; backup/restore messaging. |
| Primary | [Easy Watermark](https://wordpress.org/plugins/easy-watermark/) | Automatic upload watermarking and manual/bulk existing-media workflows. |
| Primary | [EWWW Image Optimizer](https://wordpress.org/plugins/ewww-image-optimizer/) | Compression, WebP/AVIF, lazy loading, responsive and CDN offerings; features can depend on paid tier/server. |
| Primary | [ShortPixel Image Optimizer](https://wordpress.org/plugins/shortpixel-image-optimiser/) | Bulk/automatic optimization, WebP/AVIF conversion, resizing and optional original backups. |
| Primary | [Smush](https://wordpress.org/plugins/wp-smushit/) | Compression, bulk optimization, lazy loading and modern-format delivery. |
| Primary | [WP Offload Media Lite](https://wordpress.org/plugins/amazon-s3-and-cloudfront/) | Copies/offloads media to object storage and can integrate CDN delivery. |
| Specialist | [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/) | Regeneration of registered thumbnails; supports selected/bulk regeneration workflows. |
| Specialist | [Modern Image Formats](https://wordpress.org/plugins/webp-uploads/) | WordPress Performance Team format generation/selection with environment-dependent support. |
| Specialist | [Image Prioritizer](https://wordpress.org/plugins/image-prioritizer/) | Performance-Team work around evidence-informed image priority, preload and loading. |
| Specialist | [Optimization Detective](https://wordpress.org/plugins/optimization-detective/) | Field evidence for optimization decisions and route/template observations. |
| Specialist | [Image Placeholders](https://wordpress.org/plugins/image-placeholders/) | Placeholder generation/delivery as a distinct performance capability. |
| Specialist | [ShortPixel Adaptive Images](https://wordpress.org/plugins/shortpixel-adaptive-images/) | Responsive image optimization, format adaptation and CDN delivery. |
| Specialist | [Converter for Media](https://wordpress.org/plugins/webp-converter-for-media/) | WebP/AVIF conversion and delivery, subject to server support. |
| Specialist | [Enable Media Replace](https://wordpress.org/plugins/enable-media-replace/) | Attachment replacement workflows and consumer-impact concerns. |
| Specialist | [Phoenix Media Rename](https://wordpress.org/plugins/phoenix-media-rename/) | Rename/URL and reference-impact behavior, highlighting cross-content ownership. |

These are product-page/documentation observations, not source-code review, benchmark results, or proof that a feature works in every hosting environment. Market functionality is evidence of demand and workflow families, not an instruction to copy implementation or defaults.

## Reconciliation

| Family | Market evidence | WPE decision |
|---|---|---|
| Rule-based watermarking | Image Watermark and Easy Watermark expose text/image overlays, upload automation and bulk flows. | Adopt the feature family with draft/revision, preview, deterministic rules and derivative-only output. Never treat competitor backup behavior as permission to overwrite originals by default. |
| Optimization and conversion | EWWW, ShortPixel and Smush show bulk/automatic compression and modern formats; paid/service/server capabilities vary. | Include capability-aware settings and truthful degraded states. No API, paid-service assumption or format promise without runtime certification. |
| Responsive/loading behavior | Core performance plugins and ShortPixel Adaptive Images expose srcset/sizes, lazy-loading, priority or CDN options. | Compose with Core and plugin ownership; field-measured LCP/priority and broad preload remain future-only. |
| Regeneration | Regenerate Thumbnails and optimization plugins show bulk regeneration workflows. | Use explicit dry-run, bounded asynchronous chunks, stale/missing filters and preserve-valid-output semantics. |
| Offload/CDN | WP Offload Media and adaptive-image products offer object storage/CDN routing. | Defer provider execution to a certified adapter with read/write/delete/URL/version/purge proof; no false success. |
| Replacement and recovery | Replacement plugins expose file replacement and backup/restore workflows. | Keep source lineage and recovery as separate high-impact gates; preserve attachment ID/URL/reference impact visibility. |

## Record dispositions

All 232 canonical Bank records have MARKET_EVIDENCED dispositions in config/product/options-bank-audits/media-market-ecosystem.json; the file pins all source URLs, record IDs, and extra safety dispositions. Record counts by family:

| Family | Count | Bank classification |
|---|---:|---|
| Overview | 5 | 5 WPE_HARD |
| Rule Management | 8 | 8 WPE_HARD |
| Targeting | 16 | 7 SOFT_NATIVE, 9 WPE_HARD |
| Exclusions | 8 | 8 WPE_HARD |
| Text Watermark | 21 | 1 DEFERRED, 20 WPE_HARD |
| Image Watermark | 12 | 2 DEFERRED, 10 WPE_HARD |
| Placement | 10 | 10 WPE_HARD |
| Variants and Output | 14 | 14 WPE_HARD |
| Editor and Format Capability | 17 | 5 NATIVE_HARD, 1 SOFT_NATIVE, 11 WPE_HARD |
| Derivative Identity | 11 | 1 NATIVE_HARD, 10 WPE_HARD |
| Preview and Test | 9 | 9 WPE_HARD |
| Queue and Batch | 20 | 20 WPE_HARD |
| Responsive and Loading Delivery | 13 | 3 SOFT_NATIVE, 2 WPE_EXCEED, 8 WPE_HARD |
| Performance and Diagnostics | 12 | 12 WPE_HARD |
| Storage Offload and CDN | 9 | 9 DEFERRED |
| Replacement and Versioning | 8 | 7 DEFERRED, 1 WPE_HARD |
| Reference and Cache Ownership | 7 | 5 DEFERRED, 2 WPE_HARD |
| Metadata Animation and Privacy | 9 | 9 WPE_HARD |
| Security and Ownership | 13 | 13 WPE_HARD |
| Rejected Unsafe Patterns | 10 | 10 REJECTED_UNSAFE |

## Rejected, deferred and future-only findings

- Rejected unsafe: in-place original overwrite/delete, arbitrary PHP/shortcode execution, unsanitized SVG rasterization, MIME-only support claims, first-frame flattening presented as animation preservation, synchronous unbounded bulk work, hidden reference rewrite and unsupported provider-success claims.
- Deferred: certified storage/offload capabilities, attachment replacement/recovery, reference graph completeness and purge/remote mutation. Their planning presence does not grant execution authority.
- Future-only WPE exceed: LCP/preload decisions based on field evidence require confidence, freshness, actual delivered URL and Core/plugin coexistence. Server heuristics alone are not evidence.
- Private media: a watermark or public CDN URL is not access control; protected delivery must remain with the canonical protected-resource owner.

## Gate result

PASS for market planning audit: 232/232 records dispositioned, 0 unresolved and no post-market record delta. This does not promote runtime, parity, provider execution, attachment mutation, deployment or release.
