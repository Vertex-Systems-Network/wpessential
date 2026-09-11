# Media Operations — Runtime Gap Matrix V1

Surface: **28 / Media Operations**  
Planning issue: **#594**  
Supervisor wave: **#583**  
Exact-main claim anchor: `3f3aedaf6a2a3d568aa36bd8a570346bb65d1698`

Status: **PLANNING GAP MATRIX / RUNTIME NOT AUTHORIZED**

Surface 28 remains `UNSEEDED / 0` in the Master Options Bank and exact main has no dedicated Media Operations runtime module. This matrix records prerequisites and owner boundaries only.

## Gap matrix

| Area | Exact-main state | Future requirement | Boundary / gate |
|---|---|---|---|
| Master Options Bank | **BLOCKED — UNSEEDED / 0** | Native/market/offload reviewed normalized records. | Product gate before option contracts/runtime. |
| Atomic Option Contracts | **PLANNING INVENTORY ONLY** | Schema-valid contracts after Bank review. | No lifecycle promotion from inventory alone. |
| Media Rule definitions | **ABSENT** | canonical identity/revision/lifecycle/priority/conditions/output policy. | Surface 28. |
| Image editor capability catalog | **ABSENT FOR SURFACE** | exact read/write format, animation, alpha, metadata and operation capability probes. | Must derive from active WordPress editor/environment. |
| Non-destructive derivative engine | **ABSENT** | generate WPE-owned/regeneratable outputs while preserving original source. | Original in-place mutation forbidden in standard scope. |
| Generation registry | **ABSENT** | source fingerprint, Rule revision, editor profile, derivative list and stale state. | Required for safe regeneration/cleanup. |
| Watermark renderer | **ABSENT** | bounded text/image watermark operations using safe fonts/assets/tokens. | No arbitrary HTML/PHP/shortcodes. |
| Preview/test render | **ABSENT** | temporary non-production render with resolved dimensions/output warnings. | Must not mutate attachment derivatives. |
| Batch/regeneration queue | **ABSENT** | dry-run, bounded chunks/concurrency, retries/cancel/stale generation handling. | Background Job execution only. |
| Responsive media policy | **ABSENT** | bounded `srcset`/`sizes`/rendition integration. | Compose with WordPress native media behavior. |
| Lazy/LCP/fetchpriority policy | **ABSENT** | context-aware lazy/eager/high-priority decisions. | No blanket heuristic override or fake performance guarantees. |
| Placeholder policy | **ABSENT** | bounded generated/local placeholder semantics. | No unsafe external fetch/runtime penalty. |
| Format conversion/quality | **ABSENT** | capability-tested output format/quality/alpha/metadata/color policy. | MIME detection alone insufficient. |
| Offload/CDN adapter | **DEPENDENCY/ABSENT** | granular source-read/derivative-write/delete/URL/cache capability. | Connection/credential security Surface 23. |
| Cache version/purge | **DEPENDENCY** | explicit versioning/purge only through certified owner adapters. | No generic provider calls. |
| Attachment replacement/versioning | **ABSENT / HIGH IMPACT** | preflight, source lineage, stale derivatives, recovery and stable-ID/URL semantics. | Separate bounded mutation slice. |
| Reference graph | **ABSENT** | read-only usage/coverage model with unknown-state reporting. | Generic rewrite execution Surface 45. |
| Reference rewrite | **CROSS-SURFACE / NOT OWNED** | preview/request owner-specific Transform plan. | Surface 28 must not silently rewrite arbitrary content/meta/options. |
| Restore/supersede | **ABSENT / HIGH IMPACT** | known prior source version + regenerate owned outputs + recovery proof. | Does not imply global reference rollback. |
| EXIF/orientation/privacy | **ABSENT** | capability-tested derivative metadata/GPS/orientation policy. | Original remains unchanged. |
| Animated/special formats | **ABSENT/DEFERRED** | truthful preserving renderer or explicit unsupported state. | First-frame conversion cannot be labelled preserved animation. |
| Protected/private media | **CROSS-SURFACE** | resource authorization on delivery/storage. | Watermark/CDN is not access control. |
| Permissions/Abilities | **ABSENT** | read/edit/publish/preview/process/regenerate/cancel/cleanup/settings separation. | No standard ability to alter original source. |
| Multisite | **UNSPECIFIED** | site/network upload roots/IDs/sizes/adapters/batch/restore scope. | Contract before runtime. |
| Portability | **ABSENT** | rule/dependency refs, secret-free exports and missing-provider/font/source preflight. | Generic package orchestration Surface 26. |
| Accessibility | **NO SURFACE UI** | Rule/preview/queue/replacement/recovery accessibility. | Browser/axe evidence later. |
| Compatibility | **NO SURFACE RUNTIME** | WP/PHP/GD/Imagick/format/offload/plugin matrix. | Exact-head evidence later. |
| Reliability/performance | **NO SURFACE RUNTIME** | memory/megapixel guards, async batches, generation idempotency, no frontend scans. | Deterministic tests later. |

## File/reference integrity hard gates

Future implementation must reject or fail closed on:

- standard-mode mutation of the original uploaded source file;
- cleanup/delete paths that can remove the original or unrelated owner files;
- output format selection without active editor write capability;
- unsanitized SVG or arbitrary executable watermark/token content;
- unbounded megapixel/memory/concurrency processing;
- synchronous full-library regeneration in a request;
- replacing an existing verified derivative before a new generation is verified where safe swap semantics are available;
- offload/CDN success claims when required read/write/delete/URL capability is missing;
- generic reference rewrite hidden inside attachment replacement;
- treating incomplete reference-graph coverage as zero usage;
- protected/private media delivery based only on watermarking or obscure URLs;
- first-frame animation conversion labelled as preserving animation;
- provider/cache purge calls without certified adapter ownership;
- permanent-original watermarking under ordinary Media Operations permissions.

## Required Policy / Ability separation

Separate abilities should cover:

- Rule list/get/create/update/validate/publish/archive;
- preview/test render;
- dry-run batch impact;
- process/regenerate/cancel;
- cleanup of **WPE-owned/regeneratable** derivatives;
- attachment replacement/version preflight;
- attachment replace/supersede/restore as separate high-impact mutations;
- diagnostics/settings.

No ordinary ability grants arbitrary filesystem mutation or original-source overwrite. Protected-resource delivery remains authorized by the owning resource/Policy surface.

## Accessibility evidence required later

- keyboard Rule/preview/queue/replacement/version flows;
- focus after validation/preview/batch/cancel/restore actions;
- meaningful text/metadata for before/after previews;
- non-color-only stale/unsupported/degraded/failed states;
- accessible progress and error summaries;
- responsive queue/processed-media/reference tables;
- axe coverage for unsupported format, offload degradation, stale generation and replacement-impact states.

## Multisite evidence required later

- site-specific upload roots and attachment IDs;
- registered image sizes per site/theme/context;
- network vs site settings;
- cross-site offload/provider credential isolation;
- batch scope and queue ownership;
- source-version/restore semantics;
- no cross-site attachment mutation inferred from network capability alone.

## Compatibility evidence required later

The exact-head compatibility matrix must distinguish:

- WordPress/PHP versions;
- active GD/Imagick/editor implementation;
- per-format decode **and** encode support;
- alpha/color-profile/EXIF/orientation behavior;
- animation support;
- offload/CDN plugin adapter presence/version/capabilities;
- registered custom image-size lifecycle;
- protected/private storage adapters;
- theme/plugin behavior that influences native responsive media attributes.

## Reliability/performance evidence required later

1. source/original checksum remains unchanged through standard processing;
2. generation identity makes repeated processing idempotent/current-generation aware;
3. stale Rule generations are detected deterministically;
4. previous verified output remains available until replacement verifies where possible;
5. failed/cancelled batches leave explainable recoverable generation state;
6. WPE cleanup cannot delete original/unowned files;
7. huge-image megapixel/memory guards block before unsafe decode/transform;
8. background batch processing is bounded and does not scan the full library on frontend requests;
9. format capability probes match actual read/write behavior;
10. offload adapter partial capability produces degraded/blocked state rather than false success;
11. protected media never bypasses canonical authorization;
12. replacement preflight exposes reference-graph coverage and unknowns;
13. generic Transform rewrites are previewed/executed by Surface 45, not hidden in Media Operations;
14. cache/CDN version/purge failure remains observable and retryable without corrupting attachment source state.

## Recovery / attachment replacement evidence required later

Before source replacement/supersede/restore execution is authorized, prove:

- current source/version identity and checksum;
- proposed replacement validation;
- retained prior source/recovery policy;
- derivative stale/regeneration impact;
- reference graph coverage/unknown state;
- stable attachment ID/URL consequences;
- separate Transform plan for any required cross-content rewrites;
- ability to restore a known prior source and regenerate owned derivatives;
- no claim that external/unindexed references were automatically rolled back unless the owning mutation engine proves it.

## Future implementation order

Only after Bank review + schema-valid option contracts:

1. Rule definitions + capability/read-only diagnostics;
2. image-editor capability catalog + non-destructive preview;
3. WPE derivative generation registry/engine;
4. watermark/format/metadata policies + bounded Job processing;
5. responsive/lazy/LCP/fetchpriority/placeholder delivery policy;
6. offload/CDN capability adapters + cache versioning;
7. read-only reference graph/usage diagnostics;
8. separately authorized attachment replace/supersede/restore with Transform-owned rewrites;
9. multisite/portability/degraded-state closure;
10. exact-head integrity/security/accessibility/compatibility/performance certification audit.

## Exit decision

Exact main has a mature Watermarker/Media Rules specification and clear non-destructive ownership boundaries, but the Master Options Bank is still `UNSEEDED / 0` and no Media Operations runtime exists. `runtime_allowed=false` remains correct.

The next valid action is Bank seeding/native/market review, then schema-valid option contracts and UX re-review — not media/file/reference mutation from this planning lane.
