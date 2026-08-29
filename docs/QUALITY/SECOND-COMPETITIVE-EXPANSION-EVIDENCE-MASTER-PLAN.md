# WPEssential — Second Competitive Expansion Evidence Master Plan

Status: **WP115 current evidence specification / all 11 group envelopes fixed / NOT EXECUTED / no development authorization**  
Date: **2026-08-29**

## Purpose and current truth

WP115 target is **11 namespaces × 176 = 1,936 exact fixtures**. No fixture has executed.

The five new-surface namespaces already had explicit 16×11 group ranges. During WP115 preflight, the six existing-surface supplements had reserved namespace IDs but their explicit 16-group ownership was absent from this master plan. Those ranges are now fixed from the accepted `SECOND-COMPETITIVE-PARITY-EXISTING-SURFACES-ADDENDUM.md` before any exact fixture enumeration. This prevents later ID guessing/repurposing.

Formal WP115 exact-protocol acceptance is still pending a later WP115 ADR.

## Namespace envelope

| Namespace | Scope | Range | Exact | Executed |
|---|---|---|---|---:|
| ORD | Content Order & Sequence | 001…176 | pending | 0 |
| SEC | Security Integrity/Malware/Vulnerability | 001…176 | pending | 0 |
| FNT | Font Library/Typography/Delivery | 001…176 | pending | 0 |
| UDS | User Data Stores/Favorites/Collections | 001…176 | pending | 0 |
| STG | Staging/Clone/Migration | 001…176 | pending | 0 |
| BKX | Backup advanced/incremental/CLI/MCP parity | 001…176 | pending | 0 |
| MRL | Media Replacement Lifecycle | 001…176 | pending | 0 |
| PBX | Profile/Registration parity | 001…176 | pending | 0 |
| JEX | JetEngine existing-surface parity | 001…176 | pending | 0 |
| LHX | Link Health parity | 001…176 | pending | 0 |
| HFC | Header/Footer Code migration/placement parity | 001…176 | pending | 0 |

## ORD — 16 × 11
1. 001–011 definition/version/context
2. 012–022 native `menu_order` adapter
3. 023–033 independent sequence storage
4. 034–044 drag/drop + keyboard UX
5. 045–055 hierarchy/sibling/reparent separation
6. 056–066 taxonomy/term ordering
7. 067–077 Query/Listing integration
8. 078–088 conflicts/coexistence
9. 089–099 concurrency/version conflicts
10. 100–110 revisions/rollback/import
11. 111–121 translation/Woo/provider adapters
12. 122–132 Multisite/site ordering
13. 133–143 security/permissions/audit
14. 144–154 lifecycle/delete/orphan repair
15. 155–165 10K/100K scale
16. 166–176 golden editorial/catalog scenarios

## SEC — 16 × 11
1. 001–011 baseline/provenance
2. 012–022 Core checksums
3. 023–033 plugin/theme integrity
4. 034–044 custom files/change classification
5. 045–055 signature/heuristic scanning
6. 056–066 vulnerability-feed mapping
7. 067–077 remote malware/blocklist providers
8. 078–088 finding confidence/severity/suppression
9. 089–099 quarantine/repair/recovery
10. 100–110 post-hack workflows
11. 111–121 hardening-owner integration
12. 122–132 privacy/provider data transfer
13. 133–143 Multisite/network ownership
14. 144–154 scanner degradation/failure truth
15. 155–165 scale/resource budgets
16. 166–176 incident golden/regression scenarios

## FNT — 16 × 11
1. 001–011 font metadata/format validation
2. 012–022 family/variant/variable axes
3. 023–033 upload/Asset Registry
4. 034–044 Google/provider adapters
5. 045–055 Adobe/authorized provider adapters
6. 056–066 licensing/provenance/redistribution
7. 067–077 typography profiles/assignment precedence
8. 078–088 theme.json/builder integration
9. 089–099 local hosting/external-font detection
10. 100–110 font-display/preload/subset/performance
11. 111–121 privacy/CSP/mixed-content
12. 122–132 revisions/import/export
13. 133–143 Multisite shared libraries
14. 144–154 fallbacks/locale/script coverage
15. 155–165 page-load/font budget profiles
16. 166–176 visual/performance golden regressions

## UDS — 16 × 11
1. 001–011 store definitions/types
2. 012–022 add/remove/toggle idempotency
3. 023–033 ordering/metadata
4. 034–044 limits/expiry/eviction
5. 045–055 guest identity/storage
6. 056–066 guest→user merge
7. 067–077 Query/Listing integration
8. 078–088 REST/Abilities/rate limits
9. 089–099 privacy/export/erase
10. 100–110 shared/team collections
11. 111–121 Woo cart/order boundary
12. 122–132 imports/migration
13. 133–143 Multisite
14. 144–154 concurrency/cache isolation
15. 155–165 high-cardinality/store scale
16. 166–176 favorites/wishlist/compare golden scenarios

## STG — 16 × 11
1. 001–011 environment identity/topology
2. 012–022 staging creation
3. 023–033 environment side-effect safety
4. 034–044 DB/files copy scope
5. 045–055 serialization-safe URL/path transforms
6. 056–066 remote transfer/checkpoints
7. 067–077 clone identity/secrets
8. 078–088 live→staging pull
9. 089–099 staging→live push
10. 100–110 drift/conflict detection
11. 111–121 recovery points/rollback
12. 122–132 migration/cutover verification
13. 133–143 Multisite conversions
14. 144–154 privacy redaction
15. 155–165 large-site/network performance
16. 166–176 golden migration/staging regressions

## BKX — 16 × 11
1. 001–011 profile identity/revision/capabilities
2. 012–022 full/incremental/differential semantics
3. 023–033 chain/base graph/orphan diagnostics
4. 034–044 changed-file/hash candidates
5. 045–055 DB incremental/change-capture truth
6. 056–066 pre-update/pre-migration recovery points
7. 067–077 multi-destination fan-out/required mirrors
8. 078–088 provider/direct-restore capability matrix
9. 089–099 standalone recovery package profile
10. 100–110 WP-CLI canonical Abilities
11. 111–121 MCP/AI backup governance
12. 122–132 health score/provenance/restore confidence
13. 133–143 restore/reconcile/unknown-provider outcome
14. 144–154 Multisite/environment/lifecycle
15. 155–165 chain/storage/resource budgets
16. 166–176 recovery/migration-handoff golden regressions

## MRL — 16 × 11
1. 001–011 replacement Plan identity/mode
2. 012–022 preserve attachment identity/current URL
3. 023–033 rename/repath + reference-update Plan
4. 034–044 new attachment + supersede old
5. 045–055 retained revision/restore semantics
6. 056–066 MIME/dimensions/metadata/storage preflight
7. 067–077 reference graph/builders/serialized values
8. 078–088 offload/CDN/private delivery ownership
9. 089–099 source immutability/write/checksum provenance
10. 100–110 derivative regeneration/metadata consistency
11. 111–121 cache/CDN/offload invalidation/unknown outcome
12. 122–132 Search/Replace delegation/reference verification
13. 133–143 external editing provider provenance/cost/privacy
14. 144–154 Multisite/lifecycle/privacy
15. 155–165 jobs/concurrency/large-library scale
16. 166–176 replacement/rename/reference golden regressions

## PBX — 16 × 11
1. 001–011 profile composition identity/segment
2. 012–022 multiple view/edit compositions
3. 023–033 multi-step edit state/resume/validation
4. 034–044 columns/repeater/group fields
5. 045–055 field/group change approval
6. 056–066 public permalink/privacy/concealment
7. 067–077 directory/search/sort/facets
8. 078–088 avatar/media ownership
9. 089–099 import/export/mapping/replay
10. 100–110 account navigation + wp-admin/Policy delegation
11. 111–121 registration/Membership parity/field privacy
12. 122–132 reauth/2FA/passkey/OAuth adapter boundaries
13. 133–143 Woo profile/My Account/checkout mappings
14. 144–154 Policy/REST/Abilities/AI security
15. 155–165 Multisite/network/global-user boundaries
16. 166–176 profile/registration/directory golden regressions

## JEX — 16 × 11
1. 001–011 CCT-style entity preset identity
2. 012–022 Custom Table/schema isolation
3. 023–033 optional public route/listing
4. 034–044 import/export/REST/relation mappings
5. 045–055 CPT-vs-custom-table guidance
6. 056–066 physical relation-table option
7. 067–077 relation meta/cardinality/create-related UX
8. 078–088 relation REST Ability/Policy
9. 089–099 Query provider matrix
10. 100–110 Relations/UDS/REST/merged-query semantics
11. 111–121 endpoint permissions/rate/row-limit/cache
12. 122–132 Dynamic Table/listing/chart adapters
13. 133–143 Conditional Visibility/DVR macros
14. 144–154 Reference Data Sets/import/locale/version
15. 155–165 AI Website Structure/Blueprint drafts
16. 166–176 Multisite/scale/coexistence golden regressions

## LHX — 16 × 11
1. 001–011 Local/Remote/Hybrid engine profile
2. 012–022 cloud opt-in/data-transfer/privacy
3. 023–033 occurrence sources/ownership
4. 034–044 local/remote/hybrid classification/provenance
5. 045–055 Edit Target/Unlink typed Fix Plans
6. 056–066 Ignore/Snooze/Recheck lifecycle
7. 067–077 notifications/digests
8. 078–088 saved views/bulk triage
9. 089–099 agency/network aggregate summaries
10. 100–110 provider auth/rate/Safe HTTP/SSRF
11. 111–121 scan jobs/checkpoints/cache/backpressure
12. 122–132 Redirect/SearchReplace/Media Fix composition
13. 133–143 privacy/redaction/protected sources
14. 144–154 Multisite/domain/lifecycle
15. 155–165 provider failure/large-scan budgets
16. 166–176 local/cloud/hybrid golden regressions

## HFC — 16 × 11
1. 001–011 source detection/import provenance
2. 012–022 browser-snippet type/placement mapping
3. 023–033 latest-N-posts preset
4. 034–044 taxonomy object presets
5. 045–055 selected CPT-instance presets
6. 056–066 coarse desktop/mobile preset
7. 067–077 manual shortcode/block placement
8. 078–088 creator/editor/timestamp metadata
9. 089–099 classic-theme placement compatibility
10. 100–110 block-theme placement compatibility
11. 111–121 page-builder placement compatibility
12. 122–132 occurrence/placement diagnostics
13. 133–143 consent/CSP/SRI/environment preservation
14. 144–154 PHP/server-code rejection + Extension Plan
15. 155–165 Multisite/import/coexistence/idempotency
16. 166–176 security/cache/performance/migration golden regressions

## Stop-the-line boundaries

Exact WP115 planning must preserve:
- ordering changes only within configured sequence/query context;
- security findings retain provenance/confidence; quarantine/repair has recovery evidence;
- font delivery/local hosting does not imply license/redistribution authority;
- UDS store/favorites/wishlist state never becomes Woo cart/order truth;
- staging/clone identity is distinct and must not cause production side effects;
- Backup remains backup/recovery owner; Staging owns staging topology/push/pull;
- backup incremental chain must have valid base/dependency semantics;
- media replacement preserves source/private-delivery/reference truth;
- profile/account security composes WordPress/auth providers rather than replacing session/password stack;
- JEX refinements compose canonical Fields/Relations/Query/Tables/DVR/Blueprint owners;
- cloud Link Health requires provider/privacy/Safe HTTP boundaries;
- HFC migration must reject PHP/server snippets and preserve STM consent/CSP/security boundaries.

## Execution gate

All namespaces are **0 executed**. No reorder, scan/quarantine, font download, UDS mutation, staging clone, migration, backup run, media replacement, cloud scan, snippet import/runtime, provider/API/AI/MCP call, benchmark, test or build has occurred.

Formal WP115 completion requires exact `001…176` protocol enumeration for all eleven namespaces plus an accepting ADR. Production development remains blocked by ADR-0014.