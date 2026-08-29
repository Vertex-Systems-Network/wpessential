# WPEssential — Second Competitive Expansion Evidence Master Plan

Status: **WP115 current evidence specification / group ownership fixed / NOT EXECUTED / no development authorization**  
Date: **2026-08-29**

## 1. Purpose

Fix executable-evidence namespace/group ownership for the accepted Second Competitive expansion before exact fixture enumeration. No fixture has executed.

WP115 target: **11 namespaces × 176 = 1,936 exact fixtures**.

The five new-surface group envelopes were already explicit. During WP115 preflight, the six existing-surface supplement namespaces were found to have reserved IDs but no explicit 16-group ranges in the master plan. Their group ownership is now normalized from the accepted `SECOND-COMPETITIVE-PARITY-EXISTING-SURFACES-ADDENDUM.md` before exact fixture writing; IDs are not renumbered or repurposed.

## 2. Namespaces

| Scope | Namespace | Envelope | Exact | Executed |
|---|---|---:|---:|---:|
| Content Order & Sequence Manager | ORD | 001…176 | pending WP115 | 0 |
| Security Integrity/Malware/Vulnerability | SEC | 001…176 | pending WP115 | 0 |
| Font Library/Typography/Delivery | FNT | 001…176 | pending WP115 | 0 |
| User Data Stores/Favorites/Collections | UDS | 001…176 | pending WP115 | 0 |
| Staging/Clone/Migration | STG | 001…176 | pending WP115 | 0 |
| Backup advanced/incremental/CLI/MCP parity | BKX | 001…176 | pending WP115 | 0 |
| Media Replacement Lifecycle | MRL | 001…176 | pending WP115 | 0 |
| Profile/Registration parity | PBX | 001…176 | pending WP115 | 0 |
| JetEngine existing-surface parity | JEX | 001…176 | pending WP115 | 0 |
| Link Health parity | LHX | 001…176 | pending WP115 | 0 |
| Header/Footer Code migration/placement parity | HFC | 001…176 | pending WP115 | 0 |

## 3. ORD groups — 16 × 11
1. `ORD-001…011` definition/version/context
2. `012…022` native `menu_order` adapter
3. `023…033` independent sequence storage
4. `034…044` drag/drop + keyboard UX
5. `045…055` hierarchy/sibling/reparent separation
6. `056…066` taxonomy/term ordering
7. `067…077` Query/Listing integration
8. `078…088` conflicts/coexistence
9. `089…099` concurrency/version conflicts
10. `100…110` revisions/rollback/import
11. `111…121` translation/Woo/provider adapters
12. `122…132` Multisite/site ordering
13. `133…143` security/permissions/audit
14. `144…154` lifecycle/delete/orphan repair
15. `155…165` 10K/100K scale
16. `166…176` golden editorial/catalog scenarios

## 4. SEC groups — 16 × 11
1. `SEC-001…011` baseline/provenance
2. `012…022` Core checksums
3. `023…033` plugin/theme integrity
4. `034…044` custom files/change classification
5. `045…055` signature/heuristic scanning
6. `056…066` vulnerability-feed mapping
7. `067…077` remote malware/blocklist providers
8. `078…088` finding confidence/severity/suppression
9. `089…099` quarantine/repair/recovery
10. `100…110` post-hack workflows
11. `111…121` hardening-owner integration
12. `122…132` privacy/provider data transfer
13. `133…143` Multisite/network ownership
14. `144…154` scanner degradation/failure truth
15. `155…165` scale/resource budgets
16. `166…176` incident golden/regression scenarios

## 5. FNT groups — 16 × 11
1. `FNT-001…011` font metadata/format validation
2. `012…022` family/variant/variable axes
3. `023…033` upload/Asset Registry
4. `034…044` Google/provider adapters
5. `045…055` Adobe/authorized provider adapters
6. `056…066` licensing/provenance/redistribution
7. `067…077` typography profiles/assignment precedence
8. `078…088` theme.json/builder integration
9. `089…099` local hosting/external-font detection
10. `100…110` font-display/preload/subset/performance
11. `111…121` privacy/CSP/mixed-content
12. `122…132` revisions/import/export
13. `133…143` Multisite shared libraries
14. `144…154` fallbacks/locale/script coverage
15. `155…165` page-load/font budget profiles
16. `166…176` visual/performance golden regressions

## 6. UDS groups — 16 × 11
1. `UDS-001…011` store definitions/types
2. `012…022` add/remove/toggle idempotency
3. `023…033` ordering/metadata
4. `034…044` limits/expiry/eviction
5. `045…055` guest identity/storage
6. `056…066` guest→user merge
7. `067…077` Query/Listing integration
8. `078…088` REST/Abilities/rate limits
9. `089…099` privacy/export/erase
10. `100…110` shared/team collections
11. `111…121` Woo cart/order boundary
12. `122…132` imports/migration
13. `133…143` Multisite
14. `144…154` concurrency/cache isolation
15. `155…165` high-cardinality/store scale
16. `166…176` favorites/wishlist/compare golden scenarios

## 7. STG groups — 16 × 11
1. `STG-001…011` environment identity/topology
2. `012…022` staging creation
3. `023…033` environment side-effect safety
4. `034…044` DB/files copy scope
5. `045…055` serialization-safe URL/path transforms
6. `056…066` remote transfer/checkpoints
7. `067…077` clone identity/secrets
8. `078…088` live→staging pull
9. `089…099` staging→live push
10. `100…110` drift/conflict detection
11. `111…121` recovery points/rollback
12. `122…132` migration/cutover verification
13. `133…143` Multisite conversions
14. `144…154` privacy redaction
15. `155…165` large-site/network performance
16. `166…176` golden migration/staging regressions

## 8. BKX groups — 16 × 11
1. `BKX-001…011` advanced backup profile identity/revision/capabilities
2. `012…022` full/incremental/differential semantics
3. `023…033` chain/base dependency graph/orphan-base diagnostics
4. `034…044` changed-file/hash incremental candidates
5. `045…055` DB incremental/change-capture capability truth
6. `056…066` pre-update/pre-migration automatic recovery points
7. `067…077` multi-destination fan-out/required-vs-optional mirrors
8. `078…088` provider capability/direct-restore matrix
9. `089…099` standalone recovery application/package profile
10. `100…110` WP-CLI through canonical Abilities
11. `111…121` MCP/AI status/run/cancel/log governance
12. `122…132` health score/provenance/restore-confidence truth
13. `133…143` restore/reconcile/unknown-provider-outcome handling
14. `144…154` Multisite/environment/site-lifecycle boundaries
15. `155…165` chain/storage/throughput/resource budgets
16. `166…176` backup/recovery/migration-handoff golden regressions

## 9. MRL groups — 16 × 11
1. `MRL-001…011` replacement Plan identity/revision/mode
2. `012…022` replace binary while preserving attachment identity/current URL
3. `023…033` rename/repath + reference-update Plan
4. `034…044` create-new/supersede-old attachment semantics
5. `045…055` retained prior asset revision/restore semantics
6. `056…066` MIME/dimensions/duration/metadata/storage preflight
7. `067…077` reference graph/builders/serialized-value discovery
8. `078…088` offload/CDN/private/protected delivery ownership
9. `089…099` source immutability/write/checksum/artifact provenance
10. `100…110` derivative regeneration/metadata consistency
11. `111…121` cache/CDN/offload invalidation and unknown outcomes
12. `122…132` Search/Replace delegation/dry-run/reference verification
13. `133…143` external image-editing provider provenance/cost/privacy
14. `144…154` Multisite/lifecycle/privacy/retention
15. `155…165` jobs/concurrency/large-library performance
16. `166…176` replacement/rename/reference golden regressions

## 10. PBX groups — 16 × 11
1. `PBX-001…011` profile composition identity/revision/segment
2. `012…022` multiple profile/view/edit compositions by role/segment
3. `023…033` multi-step profile-edit state/resume/validation
4. `034…044` layout columns/repeater/group fields through shared Fields
5. `045…055` per-field/group change approval/workflow
6. `056…066` public profile permalink/privacy/concealment
7. `067…077` member directory/search/sort/facets/listing
8. `078…088` avatar/media ownership/private delivery
9. `089…099` user import/export/mapping/replay
10. `100…110` account navigation + wp-admin presentation/Policy delegation
11. `111…121` registration/Membership flow parity and field privacy
12. `122…132` account security/reauth/2FA/passkey/OAuth adapter boundaries
13. `133…143` Woo billing/shipping/My Account/checkout field mappings
14. `144…154` Policy/REST/Abilities/AI approval/security
15. `155…165` Multisite/network/global-user boundaries
16. `166…176` profile/registration/directory/security golden regressions

## 11. JEX groups — 16 × 11
1. `JEX-001…011` CCT-style application-entity preset identity
2. `012…022` Custom Table/schema/table-per-type isolation
3. `023…033` optional public route/listing definition
4. `034…044` import/export/REST/relation mappings
5. `045…055` CPT-vs-custom-table guidance/provenance
6. `056…066` high-scale physical relation-table option
7. `067…077` relation meta/cardinality/create-related UX
8. `078…088` relation REST get/update Ability/Policy
9. `089…099` Query provider matrix/bindings
10. `100…110` Relations/UDS/REST/merged/sub-query semantics
11. `111…121` query endpoint permissions/rate/row-limit/cache/invalidation
12. `122…132` Dynamic Table/listing/sort/filter/pagination/chart adapter
13. `133…143` Conditional Logic/Dynamic Visibility/DVR macro integration
14. `144…154` Reference Data Set/import/locale/version/source
15. `155…165` AI Website Structure/Solution Blueprint draft validation
16. `166…176` Multisite/scale/coexistence/golden JetEngine-parity regressions

## 12. LHX groups — 16 × 11
1. `LHX-001…011` Local/Remote Cloud/Hybrid engine-profile identity
2. `012…022` cloud-provider opt-in/data-transfer disclosure/privacy
3. `023…033` occurrence source types and source ownership
4. `034…044` local/remote/hybrid result classification/provenance
5. `045…055` Edit Target/Unlink typed Fix Plans
6. `056…066` Ignore/Snooze/Recheck lifecycle/idempotency
7. `067…077` immediate/daily/weekly/scan-complete notifications
8. `078…088` saved issue views/bulk triage
9. `089…099` agency/network aggregate summaries without raw leakage
10. `100…110` provider authentication/rate/Safe HTTP/SSRF boundaries
11. `111…121` scan jobs/checkpoints/cache/backpressure
12. `122…132` Redirect/SearchReplace/Media Fix-Plan composition
13. `133…143` privacy/query-redaction/protected-source handling
14. `144…154` Multisite/domain/site lifecycle
15. `155…165` provider failure/large scan scale/resource budgets
16. `166…176` local/cloud/hybrid/fix/notification golden regressions

## 13. HFC groups — 16 × 11
1. `HFC-001…011` source detection/import definition/provenance
2. `012…022` browser-snippet type/placement mapping
3. `023…033` `latest N posts` context preset
4. `034…044` category/tag/taxonomy object presets
5. `045…055` selected CPT-instance presets
6. `056…066` coarse desktop/mobile display preset
7. `067…077` manual shortcode/block placement preset
8. `078…088` created-by/last-edited/timestamps/list metadata
9. `089…099` classic-theme before/after-content compatibility
10. `100…110` block-theme placement compatibility
11. `111…121` page-builder placement compatibility
12. `122…132` occurrence/placement diagnostics
13. `133…143` consent/CSP/SRI/environment/dependency preservation
14. `144…154` PHP/server-code rejection + Extension Plan boundary
15. `155…165` Multisite/import/coexistence/idempotency
16. `166…176` security/cache/performance/migration golden regressions

## 14. Stop-the-line boundaries

WP115 exact evidence must stop on designs that allow:
- reorder/query hijack outside configured context;
- malware quarantine/repair without provenance/recovery;
- font redistribution without allowed provenance or private/provider leakage;
- cross-user/store/site UDS leakage or Woo cart/order truth confusion;
- migration reported complete before target verification or staging causing production side effects;
- backup chain missing required base or false restore confidence;
- media replacement breaking references/private delivery or mutating original contrary to profile;
- profile registration/account security bypass;
- JEX visibility/query convenience becoming authorization or duplicate canonical engine;
- cloud link scanning transferring protected data without privacy/provider policy;
- Header/Footer migration importing PHP/server code or weakening STM consent/CSP/security boundaries.

## 15. Current truth

All WP115 namespaces are **0 executed**. No benchmark, WordPress runtime test, scan, staging clone, migration, reorder, font download, favorites mutation, backup run, media replacement, provider/API/AI/MCP call or code injection has occurred.

Exact fixture enumeration is the current WP115 planning task. Formal completion/acceptance will require a later WP115 ADR; this group-normalization step itself does not grant runtime certification or development consent.