# WPEssential — Media Operations Native WordPress Audit V1

Status: NATIVE_AUDITED — Bank planning evidence only
Surface: 28 — Media Operations
Source issue: #1458
Exact-main audit anchor: b4f2cb5fc707bee0358bdc83323424638cd4ef4f
Snapshot: 2026-10-20

## Scope and decision

The 232 records in config/product/options-bank/media.json are classified against the current WordPress Core media substrate and the WPE exhaustive media specification. All 232 records have a disposition; native unresolved items: 0. This audit verifies API roles and ownership boundaries, not live image-editor capability, actual format support on a host, or runtime behavior.

WordPress owns attachment/source identity, image-editor selection/capability APIs, registered image sizes, attachment metadata, srcset, and loading-optimization attributes. Those are substrate APIs. They do not implement WPE rules, watermark rendering, derivative identity, queues, previews, recovery, privacy policy or provider/offload truth.

## Native source review

| Native area | Verified behavior and WPE boundary | Official reference |
|---|---|---|
| Attachment originals and metadata | WordPress owns attachment IDs, metadata, attached-file and original-image APIs. WPE consumes these as source identity and never repoints the original to a watermark output. | [1](https://developer.wordpress.org/reference/functions/wp_get_attachment_metadata/)<br>[2](https://developer.wordpress.org/reference/functions/wp_get_original_image_path/)<br>[3](https://developer.wordpress.org/reference/functions/wp_get_original_image_url/) |
| Image editor selection and capabilities | wp_get_image_editor() selects a compatible editor; wp_image_editor_supports() and WP_Image_Editor::supports_mime_type() provide capability probes. A MIME/extension label is not proof that every installed editor can decode, transform and encode the requested output. | [1](https://developer.wordpress.org/reference/functions/wp_get_image_editor/)<br>[2](https://developer.wordpress.org/reference/functions/wp_image_editor_supports/)<br>[3](https://developer.wordpress.org/reference/classes/wp_image_editor/supports_mime_type/) |
| Sizes and regeneration substrate | Core registers and generates intermediate image sizes and attachment metadata. WPE can target registered sizes, but the normal output remains a separate WPE derivative; selected generated sub-size replacement stays an explicit future mutation gate. | [1](https://developer.wordpress.org/reference/functions/add_image_size/)<br>[2](https://developer.wordpress.org/reference/functions/wp_create_image_subsizes/)<br>[3](https://developer.wordpress.org/reference/functions/wp_generate_attachment_metadata/) |
| Uploads and attachment delivery | Core provides upload directory, attachment URL and intermediate-size APIs. Storage/offload plugins may change physical location and URL behavior; WPE therefore delegates remote storage read/write/delete and URL resolution to certified adapters. | [1](https://developer.wordpress.org/reference/functions/wp_get_upload_dir/)<br>[2](https://developer.wordpress.org/reference/functions/wp_get_attachment_url/)<br>[3](https://developer.wordpress.org/reference/functions/image_get_intermediate_size/) |
| Responsive image generation | Core computes attachment srcset candidates and their sizes. Surface 28 composes with these APIs and does not claim arbitrary art direction or rewrite references. | [1](https://developer.wordpress.org/reference/functions/wp_get_attachment_image_srcset/)<br>[2](https://developer.wordpress.org/reference/functions/wp_calculate_image_srcset/) |
| Loading optimization | Core exposes loading optimization and lazy-loading policy, including context filters. WPE preserves Core/Performance-plugin ownership, avoids loading=lazy and fetchpriority=high together, and makes any override context/evidence-bound. | [1](https://developer.wordpress.org/reference/functions/wp_get_loading_optimization_attributes/)<br>[2](https://developer.wordpress.org/reference/functions/wp_lazy_loading_enabled/) |
| Output format mapping | Core can select an output MIME through an editor output-format filter, but actual read/write support still depends on the active editor/runtime. WPE's format choices remain capability-driven. | [1](https://developer.wordpress.org/reference/functions/wp_get_image_editor_output_format/)<br>[2](https://developer.wordpress.org/reference/classes/wp_image_editor/) |

## Record-level reconciliation

Every record ID below maps to the classification in the canonical Bank. NATIVE_HARD / SOFT_NATIVE means WordPress provides a native substrate that WPE must reuse; it does not mean the WPE feature is implemented. All other rows are WPE policy/product, future-gated work, or a rejected unsafe pattern.

| Family | Records | Native disposition |
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

## Safety and ownership findings

- Original uploads and original-image paths are source identity and remain unchanged in the standard pipeline. Output defaults to a separately identified WPE derivative.
- Recognized MIME types, filename extensions, WordPress upload acceptance, and editor input support do not prove that the selected active editor can perform the required output operation.
- WordPress sizes and srcset are native; WPE must not silently claim ownership of Core/plugin outputs or mutate arbitrary attachment metadata.
- Loading=lazy, fetchpriority=high, LCP selection and preloads are context-sensitive. WPE defers to Core and compatible performance plugins, and does not add broad priority/preload heuristics.
- Offload/CDN, signed/private delivery and purge semantics are not native guarantees. They require capability-certified owner adapters; failure must remain visible.
- This Bank audit does not change attachment files, metadata, URLs, references, caches, runtime code or provider state.

## Gate result

PASS for native planning audit: 232/232 records classified; 0 unresolved. Bank classification tally: 24 DEFERRED, 6 NATIVE_HARD, 10 REJECTED_UNSAFE, 11 SOFT_NATIVE, 2 WPE_EXCEED, 179 WPE_HARD.

Not promoted: editor-format certification, runtime, derivative generation, attachment replacement, CDN/offload execution, reference rewrite, product parity, deployment or release.
