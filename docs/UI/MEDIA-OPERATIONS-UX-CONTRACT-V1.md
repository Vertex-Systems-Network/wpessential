# Media Operations — Provisional UX Contract V1

Surface: **28 / Media Operations**  
Planning issue: **#594**  
Supervisor wave: **#583**  
Exact-main claim anchor: `3f3aedaf6a2a3d568aa36bd8a570346bb65d1698`

Lifecycle status: **PROVISIONAL PLANNING ONLY**. Surface 28 remains `UNSEEDED / 0` in the Master Options Bank. This document does not promote `UX_CONTRACT_COMPLETE` or authorize attachment/file/derivative/CDN/reference mutation.

## Product rule

Media Operations is non-destructive by default. Original uploaded source files remain unchanged. Standard processing creates WPE-owned derivatives or explicitly regeneratable WordPress sub-sizes. Permanent modification of the original is outside normal scope.

## Information architecture

1. Media Rules
2. Create/Edit Rule
3. Media Processing Queue
4. Processed Media
5. Delivery & Performance Policy
6. Attachment Replacement / Versions
7. Reference Impact
8. Diagnostics / Capabilities

## Progressive disclosure

### Essential

A media Rule exposes:

- name/status/priority;
- target media type/size/context;
- exclusions;
- watermark mode: none/text/image where applicable;
- simple placement/scale/opacity;
- output strategy with **WPE derivative** as preferred default;
- target format/quality as capability-aware choices;
- Preview / Validate / Save.

The UI always states that the original source will remain unchanged.

### Advanced

Adds:

- dimension/file-size/context conditions;
- size-specific variants;
- text typography/background/stroke/shadow controls supported by the active renderer;
- image-watermark source/rotation/blend/alpha controls;
- output format/metadata/orientation/privacy choices;
- responsive rendition policy;
- lazy/eager loading policy;
- LCP/fetchpriority exceptions by validated rendering context;
- placeholder policy;
- batch/regeneration filters and dry-run impact;
- preserve previous generation until replacement verifies;
- dependency/usage summary.

### Expert

Adds bounded operational controls:

- certified offload/CDN adapter and capability status;
- custom registered derivative strategies;
- explicit selected WordPress sub-size replacement;
- cache version/purge integration through owning adapter;
- advanced EXIF/color-profile/format capability detail;
- attachment replacement/supersede/restore planning;
- read-only reference graph;
- provider/storage compatibility and recovery diagnostics.

No Expert control exposes arbitrary PHP/shortcodes, unrestricted filesystem paths, secret/provider credentials or permanent-original mutation.

### System / Diagnostics

Read-only diagnostics show:

- active WordPress image editor and read/write format capability;
- source attachment/original fingerprint;
- current Rule/revision generation;
- derivative inventory and stale/missing state;
- memory/megapixel risk;
- offload/CDN read/write/delete/URL/purge capability;
- queue/runner health;
- reference graph availability;
- current replacement/source-version lineage;
- privacy/EXIF state;
- site/network storage scope.

## Rule targeting and preview

Targeting uses actual media/content facts and explicit exclusions. If related context is unknown at upload time, UI shows unresolved/deferred state rather than guessing.

Preview can use existing or temporary test media and shows resolved dimensions, placement, output format/quality/path plus warnings. Preview never mutates production derivatives.

## Non-destructive output UX

Output strategies are clearly ranked:

1. **WPE dedicated derivative** — preferred;
2. **replace selected generated sub-sizes** — advanced, regeneratable;
3. **registered custom derivative**.

The original file is never offered as an ordinary output target. Cleanup actions enumerate exactly which WPE-owned/generated files will be removed.

## Responsive / delivery policy

A dedicated policy surface can eventually control media attributes that WPE actually owns or can safely influence:

- responsive variant/rendition preference;
- `srcset` / `sizes` integration;
- lazy loading default;
- explicit eager/LCP exception for validated hero/above-fold contexts;
- `fetchpriority` policy;
- placeholder strategy;
- format negotiation/CDN URL resolution.

The UI must not promise universal LCP improvement or override WordPress/browser heuristics blindly. A rendering context must be known before applying high-priority/eager exceptions.

## Batch/regeneration UX

Batch processing starts with **Dry Run** and reports matched attachments, current/stale generations, unsupported formats, context-waiting items, expected output count and estimated resource risk where evidence exists.

Controls use Auto/bounded chunk/concurrency. Processing runs asynchronously. No “process entire library now in this request” path exists.

Previous verified derivative remains available until replacement succeeds when the strategy permits it.

## Attachment replacement / supersede / restore

Replacing an attachment source is a high-impact version operation, not a casual file overwrite.

Before future execution show:

- current source/version identity;
- proposed replacement metadata;
- compatibility/MIME/dimension changes;
- derivatives that become stale;
- attachment/reference usage summary;
- whether URLs/IDs remain stable;
- reference rewrites that would be required;
- recovery/source-version retention policy.

Generic cross-content reference mutation is delegated to Surface 45 Transform. Surface 28 may request/preview the operation but must not silently rewrite arbitrary content/meta/options.

Restore chooses a known prior source/version and then regenerates owned derivatives through the normal verified pipeline. It does not assume external references can be rolled back automatically.

## Reference graph UX

Reference Impact is primarily read-only:

- posts/content using attachment;
- featured-image/media-field/listing/resource references where canonical adapters can report them;
- derivative/CDN delivery dependencies;
- unknown/unindexed references clearly labelled.

“0 references” must not be presented as proof of no usage when coverage is incomplete.

## Offload / CDN UX

Adapter capability is granular: source read, derivative write, delivery URL, derivative delete, cache purge/versioning.

If a required capability is absent, the Rule/operation is degraded or blocked. Local temporary output must not be left as a false permanent success.

Connection credentials remain managed by Surface 23/Vault and are never displayed in Media Rules.

## Format / animation / EXIF UX

Only formats verified writable by the active editor are selectable as output. Unsupported input/output appears as a truthful degraded state.

Animated formats default to unsupported unless an animation-preserving renderer exists. First-frame processing must be explicitly labelled as destructive-to-animation semantics, never “preserved”.

Original EXIF remains untouched. Derivative metadata/GPS removal/preservation is explicit and capability-tested.

## Protected/private media

The interface states clearly that watermarking, CDN delivery or obscure derivative URLs are not authorization. Protected/private assets require the owning resource/storage/Policy delivery mechanism.

## Accessibility

- keyboard-operable Rule, preview, queue, replacement and recovery flows;
- meaningful before/after preview descriptions and metadata, not image-only state;
- focus after validation/test/batch/replacement actions;
- no color-only queue/health/stale/degraded states;
- accessible tables and batch progress;
- clear alternative text/decorative-state handling for watermark source previews;
- axe coverage for normal, unsupported-format, offload-degraded, stale-generation and replacement-impact states once implemented.

## Multisite

Future normalized contract must explicitly define site upload roots, attachment IDs/source ownership, registered image sizes, network/site settings, offload credentials/adapters, batch scope and restore/version behavior. Cross-site attachment mutation is never inferred from network privileges alone.

## Portability and recovery

Rule exports include structured configuration and canonical asset/font/provider references, never secrets or binary originals by default. Imports report missing watermark source/font/provider/size dependencies.

Recovery preserves original source identity and generation lineage. Derived-output cleanup/rollback is limited to WPE-owned/regeneratable artifacts. Generic reference mutation rollback remains Surface 45/owning-surface responsibility.

## Lifecycle decision

This is provisional interaction guidance only. Exact-main Master Options Bank remains `UNSEEDED / 0`; Bank review and schema-valid Atomic Option Contracts must precede UX lifecycle/runtime promotion.
