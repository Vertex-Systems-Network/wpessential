# WPEssential — Media Replacement Lifecycle Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `MRL-001…MRL-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Replacement changes a media asset lifecycle; it never grants access to protected media or unrelated content.
- Preserve-attachment, rename/repath, supersede and retained-revision restore are distinct modes with different identity/reference effects.
- Original-source immutability remains governed by Media/Asset policy; destructive overwrite is never assumed.
- Reference updates delegate to canonical Search/Replace with dry run; replacement does not invent blind database rewriting.
- CDN/offload/private delivery/provider facts remain owned by their adapters; unknown external outcome is reconciled before replay.
- External editing provider output is a derived/provider artifact with provenance, not silent canonical-original replacement.

## Exact fixtures

### Group 1 — replacement Plan
- `MRL-001` Create replacement Plan with source attachment, mode, new asset, reference strategy, derivative/cache/provider actions and expected revision.
- `MRL-002` Reject plan whose source attachment/media owner cannot be resolved under current site/Policy.
- `MRL-003` Reject unsupported replacement mode rather than silently using preserve-identity semantics.
- `MRL-004` Plan records source/new MIME, dimensions/duration, checksums and owner scope before mutation.
- `MRL-005` Dry run lists attachment/reference/derivative/provider effects without changing media.
- `MRL-006` Stale source attachment revision invalidates plan before apply.
- `MRL-007` Plan can be cancelled/archived without altering source asset.
- `MRL-008` Repeated apply request with same operation identity is idempotent or safely conflicts.
- `MRL-009` Capability/Policy is checked at plan creation and again at apply boundary.
- `MRL-010` AI/MCP may draft/analyze Plan but cannot apply replacement or provider edit outside normal approval.
- `MRL-011` Unknown plan schema/version fails typed or migrates explicitly.

### Group 2 — preserve attachment identity
- `MRL-012` Replace binary while preserving attachment post ID only when mode explicitly selects identity preservation.
- `MRL-013` Preserve-ID operation retains attachment ownership/Policy metadata unless explicit field update says otherwise.
- `MRL-014` New binary checksum is recorded and old checksum remains in revision/provenance history.
- `MRL-015` MIME-incompatible replacement is blocked or requires explicit compatibility path before preserve-ID apply.
- `MRL-016` Attachment URL is preserved only when storage/provider path contract permits exact URL retention.
- `MRL-017` Metadata regeneration reflects new binary dimensions/duration and cannot keep stale dimensions silently.
- `MRL-018` Existing captions/alt/title are preserved unless Plan explicitly changes them.
- `MRL-019` Protected/private attachment remains protected after binary replacement; public URL is not introduced.
- `MRL-020` Failed binary write leaves attachment pointing to previous valid artifact or explicit incomplete state, never broken success.
- `MRL-021` Cache/derivatives are not marked current until new binary is durably committed and verified.
- `MRL-022` Preserve-ID does not imply external CDN/offload object was successfully replaced until adapter confirms.

### Group 3 — rename/repath
- `MRL-023` Rename/repath Plan records old/new storage locator and exact reference-update scope before mutation.
- `MRL-024` New filename/path is sanitized, collision-checked and cannot traverse storage roots.
- `MRL-025` Existing target path collision blocks apply or follows explicit version/new-name strategy.
- `MRL-026` Repath across storage provider is treated as transfer/copy+verify, not simple local rename assumption.
- `MRL-027` Old path remains until new artifact is verified when rollback/recovery policy requires it.
- `MRL-028` Search/Replace dry run identifies references to old URL/path before reference mutation.
- `MRL-029` Serialized/structured references delegate to canonical typed transformation, never raw blind regex.
- `MRL-030` External absolute URLs not owned by this attachment are not rewritten merely because string matches filename.
- `MRL-031` Rename preserves attachment identity only if selected mode says so; otherwise supersede semantics apply.
- `MRL-032` Redirect/compatibility alias for old public URL is explicit and does not become authorization.
- `MRL-033` Rollback can restore old path/reference mapping only with retained verified artifact/provenance.

### Group 4 — supersede
- `MRL-034` Supersede creates a new attachment identity and records typed predecessor/successor relation.
- `MRL-035` Old attachment remains queryable/retained according lifecycle and is not silently deleted.
- `MRL-036` References update only according explicit Plan; new attachment existence alone does not rewrite content.
- `MRL-037` Old/new ownership/Policy is re-evaluated; successor does not inherit broader access accidentally.
- `MRL-038` Supersede can leave selected references on old attachment while others move, as declared by Plan.
- `MRL-039` Duplicate supersede retry returns existing successor operation rather than creating multiple replacements.
- `MRL-040` New attachment metadata/provenance clearly identifies source/supersede operation.
- `MRL-041` Deleting old attachment after supersede requires separate dependency/reference/retention check.
- `MRL-042` External provider IDs are not copied as same object identity unless adapter explicitly maps successor.
- `MRL-043` Revision/history UI distinguishes “binary replaced” from “new attachment supersedes old”.
- `MRL-044` AI/MCP cannot hide supersede relation or auto-delete predecessor to simplify result.

### Group 5 — retained revision restore
- `MRL-045` Restore lists only retained verified prior media revisions/artifacts available under Policy.
- `MRL-046` Selected retained artifact checksum is verified before restore.
- `MRL-047` Restore creates new current revision/provenance rather than erasing replacement history.
- `MRL-048` Missing retained artifact yields unavailable/degraded state, not false restore success.
- `MRL-049` Restore of binary re-runs applicable metadata/derivative/provider/cache reconciliation.
- `MRL-050` Restore does not revert unrelated attachment metadata changed after old binary unless Plan explicitly includes it.
- `MRL-051` Restore of old public URL/path follows current storage/reference policy and cannot assume original path is free.
- `MRL-052` Protected/private delivery state is reauthorized under current Policy, not historical access state.
- `MRL-053` Provider/offload restoration remains external fact and may be partial/unknown until reconciled.
- `MRL-054` Concurrent newer replacement causes stale restore conflict.
- `MRL-055` AI/MCP may recommend a retained revision but applying restore requires same high-risk Policy.

### Group 6 — preflight
- `MRL-056` Preflight validates source/new MIME compatibility for selected mode.
- `MRL-057` Preflight records dimensions/aspect ratio/duration/codec metadata and flags material compatibility differences.
- `MRL-058` Preflight inventories derivative sizes/formats currently associated with attachment.
- `MRL-059` Preflight inventories known references through registered reference providers.
- `MRL-060` Preflight identifies offload/CDN/private-delivery ownership and required adapter actions.
- `MRL-061` Preflight verifies sufficient local/temp/provider capacity for new binary/derivative workflow where measurable.
- `MRL-062` Preflight checks attachment/source write permission and storage writability without granting authorization from writability alone.
- `MRL-063` Preflight identifies builder/serialized references needing certified Search/Replace adapter.
- `MRL-064` Preflight detects source changed since Plan draft and invalidates stale assumptions.
- `MRL-065` Missing/unavailable provider/reference adapter is reported as blocker/degradation according Plan requirements.
- `MRL-066` Preflight itself performs no replacement/provider edit/reference mutation.

### Group 7 — reference graph
- `MRL-067` Reference graph distinguishes post content, block attrs, custom fields, menus, options, builder data and external references by owner/provider.
- `MRL-068` Graph edge stores source resource/field/context and detected media reference type, not only raw string.
- `MRL-069` Unauthorized protected source content is not exposed merely because it references target media.
- `MRL-070` Duplicate occurrences in one resource remain individually addressable when Fix Plan requires occurrence-level changes.
- `MRL-071` Stale graph edge is revalidated before mutation.
- `MRL-072` Dynamic/generated URL that cannot be safely rewritten remains unresolved rather than guessed.
- `MRL-073` Reference to old attachment ID is distinct from URL/path reference.
- `MRL-074` External-site/backlink reference is informational and not mutated by local replacement.
- `MRL-075` Graph cache invalidates on relevant source content/attachment revision changes.
- `MRL-076` Large graph is paginated/bounded and does not copy entire protected content into scan records.
- `MRL-077` AI/MCP explanation uses authorized graph metadata and cannot use graph as cross-resource data-exfiltration channel.

### Group 8 — offload/CDN/private ownership
- `MRL-078` Adapter resolves whether canonical binary is local, remote-offloaded, mirrored or private/protected before write.
- `MRL-079` Private media replacement never generates public source/CDN URL by default.
- `MRL-080` Offload upload result includes provider object identity/checksum/version where supported.
- `MRL-081` Provider timeout after upload is unknown/reconcile-required before retry.
- `MRL-082` CDN purge success is separate from origin object replacement success.
- `MRL-083` Signed/private delivery tokens are invalidated/rotated according owning delivery policy after binary/path change.
- `MRL-084` Local deletion after offload happens only after provider durability verification and policy permits.
- `MRL-085` Provider credentials remain Vault-backed and absent from media revision/export/logs.
- `MRL-086` Cross-region/provider transfer follows residency/privacy policy.
- `MRL-087` Unsupported provider replacement mode falls back only to explicit safe copy/new-object strategy.
- `MRL-088` AI/MCP cannot promote private media to public or change offload provider through replacement shortcut.

### Group 9 — source immutability/checksum
- `MRL-089` Original-source immutability profile preserves original artifact and creates derived/current replacement artifact as specified.
- `MRL-090` Destructive original overwrite is blocked unless an explicitly supported exceptional policy exists and is separately approved.
- `MRL-091` Every retained/current artifact records checksum algorithm/version and exact byte target.
- `MRL-092` Checksum mismatch after write prevents successful commit.
- `MRL-093` Metadata-only change does not alter binary checksum/history.
- `MRL-094` Image metadata stripping/normalization that changes bytes produces new checksum/provenance.
- `MRL-095` External editor output is never labelled original unless provenance policy explicitly imports it as new canonical source with review.
- `MRL-096` Binary corruption detected later marks affected revision degraded without rewriting historical checksum.
- `MRL-097` Backup/recovery copy verifies retained artifacts by checksum before claiming restorable.
- `MRL-098` Hash equality supports byte identity only, not ownership/license/authenticity claims.
- `MRL-099` AI/MCP cannot bypass original-preservation policy by requesting “replace in place”.

### Group 10 — derivative regeneration
- `MRL-100` New current image triggers declared certified derivative size/format regeneration plan.
- `MRL-101` Derivative generation uses new source checksum/revision and never stale old binary.
- `MRL-102` Missing optional derivative yields degraded derivative state while mandatory derivative failure affects completion per profile.
- `MRL-103` AVIF/WebP/format generation occurs only when editor capability/profile supports it.
- `MRL-104` Derivative dimensions/metadata match new source/aspect/crop rules.
- `MRL-105` Watermark transformation remains separate derivative rule and is not silently baked into canonical source.
- `MRL-106` Regeneration job is idempotent for source revision + derivative profile.
- `MRL-107` Old derivatives are retained/deleted according lifecycle only after new derivatives verified.
- `MRL-108` Private derivative storage/delivery preserves source privacy classification.
- `MRL-109` Builder/theme-specific derivative references are updated only through certified adapter/Reference Plan.
- `MRL-110` AI/MCP can plan regeneration but cannot execute broad media jobs without same permission/approval.

### Group 11 — cache/CDN invalidation
- `MRL-111` Successful durable media commit emits scoped cache invalidation for attachment/source revision.
- `MRL-112` Object/page/CDN cache invalidation targets known dependent keys/routes, not blanket purge by default.
- `MRL-113` Purge provider timeout is unknown/degraded and does not roll back verified origin replacement automatically.
- `MRL-114` Versioned URL strategy may avoid purge only when actual delivered reference changes accordingly.
- `MRL-115` Browser cache caveat is reported; server cannot guarantee immediate removal of already downloaded bytes.
- `MRL-116` Private signed URL cache/tokens follow revocation/expiry contract and no impossible instant-revocation claim is made.
- `MRL-117` Cache invalidation happens after durable commit, never before source is ready where race could serve missing asset.
- `MRL-118` Stale cache diagnostic identifies old checksum/URL evidence where observable.
- `MRL-119` Cache key includes site/provider/revision and cannot bleed between Multisite attachments.
- `MRL-120` Repeated purge request is idempotent/bounded according provider contract.
- `MRL-121` AI/MCP cannot issue network-wide CDN purge from a site-scoped media replacement plan.

### Group 12 — Search/Replace delegation
- `MRL-122` Reference mutation is compiled as typed Search/Replace Plan referencing old/new media IDs/URLs/paths.
- `MRL-123` Search/Replace dry run count/resources are attached to replacement Plan before apply.
- `MRL-124` Serialized/JSON/builder data is changed only through certified parser/owner adapter.
- `MRL-125` Unrelated text containing old filename/domain substring is not changed without exact rule match.
- `MRL-126` Attachment-ID references and URL references use separate typed transforms.
- `MRL-127` Partial reference update reports exact failed/unresolved occurrences and affects completion state.
- `MRL-128` Search/Replace rollback/recovery semantics remain owned by SRT; MRL links provenance instead of duplicating mutation history.
- `MRL-129` Concurrent source-content edit causes stale reference conflict rather than overwrite.
- `MRL-130` Protected content reference mutation requires owning resource Policy.
- `MRL-131` External/reference provider unsupported context remains unresolved/manual.
- `MRL-132` AI/MCP cannot bypass SRT dry-run/Policy by submitting direct DB replacement through MRL.

### Group 13 — external editing provider
- `MRL-133` External edit action records provider, operation type, input asset revision, data transfer and expected output class.
- `MRL-134` Background-removal/image-edit provider receives only authorized media and privacy-approved metadata.
- `MRL-135` Provider credentials/cost account remain Vault/provider-owned and absent from frontend/export.
- `MRL-136` Provider timeout after submission is unknown/reconcile-required and duplicate-cost replay is prevented where possible.
- `MRL-137` Provider output is validated for MIME/dimensions/size/checksum before use.
- `MRL-138` Provider-generated output carries provenance and is not silently made canonical original.
- `MRL-139` User must choose replacement/supersede/derived-artifact handling for returned edit.
- `MRL-140` Provider content-policy/error response is recorded as provider fact without local success claim.
- `MRL-141` Data residency/retention/provider deletion capabilities are surfaced according privacy profile.
- `MRL-142` Cost estimate/quote is not final provider charge; actual billing remains provider truth.
- `MRL-143` AI may propose edit/provider choice but cannot send private media externally without same Policy/approval.

### Group 14 — Multisite/privacy
- `MRL-144` Attachment ownership is site-resolved; same numeric attachment ID on another site cannot be targeted.
- `MRL-145` Network library/shared asset replacement requires explicit network ownership and dependent-site impact plan.
- `MRL-146` Site admin cannot replace network/shared media outside delegated Policy.
- `MRL-147` Reference graph/network summary does not expose other sites' protected content.
- `MRL-148` Site clone remaps attachment/storage/provider identity and does not copy live signed/private URLs blindly.
- `MRL-149` Site deletion retention handles WPE-owned retained revisions without deleting shared provider object still referenced elsewhere.
- `MRL-150` Privacy export includes media lifecycle metadata only where subject-related and authorized, not arbitrary binary content.
- `MRL-151` Erasure/retention/legal hold may constrain retained media revisions and is resolved through Privacy/Policy.
- `MRL-152` EXIF/embedded metadata privacy changes are explicit transformation/revision, not hidden side effect.
- `MRL-153` Provider transfer of private media follows site/network residency/privacy policy.
- `MRL-154` AI/MCP site principal cannot enumerate/replace another site's attachment via raw ID/provider locator.

### Group 15 — jobs/concurrency
- `MRL-155` Replacement job pins source revision, Plan revision and operation identity before work starts.
- `MRL-156` Duplicate queue delivery is idempotent and cannot apply binary/reference replacement twice.
- `MRL-157` Concurrent replacement Plans for same attachment conflict/serialize according expected revision.
- `MRL-158` Cancel request distinguishes cancellation requested from safely stopped/committed state.
- `MRL-159` Crash before durable commit leaves source current and temp artifacts quarantined/cleanable.
- `MRL-160` Crash after binary commit but before provider/reference completion resumes/reconciles same operation instead of recreating identity.
- `MRL-161` Partial multi-step job reports binary/metadata/derivative/reference/provider/cache states individually.
- `MRL-162` Temp files are scoped to operation and cannot be served publicly as replacement.
- `MRL-163` Retry/backoff honors provider rate limits and avoids duplicate external edits/uploads.
- `MRL-164` Large media job budgets CPU/memory/disk and prevents one site starving network workers.
- `MRL-165` Performance/job claims remain NOT EXECUTED until actual runtime evidence exists.

### Group 16 — golden replacement
- `MRL-166` Golden preserve-ID image replacement keeps attachment ID/caption/Policy, updates binary metadata/checksum and regenerates derivatives.
- `MRL-167` Golden rename/repath scenario dry-runs and updates only certified references, preserving rollback artifact.
- `MRL-168` Golden supersede scenario creates new attachment relation and leaves old object retained according policy.
- `MRL-169` Golden retained-revision restore creates a new current revision without erasing history.
- `MRL-170` Golden private/offloaded media scenario never exposes public URL and reconciles provider timeout safely.
- `MRL-171` Golden builder/serialized reference scenario delegates to SRT and reports unresolved unsupported contexts.
- `MRL-172` Golden external background-removal scenario records provider provenance/cost/privacy and does not silently replace original.
- `MRL-173` Golden concurrent replacement scenario rejects stale plan rather than overwriting newer binary.
- `MRL-174` Golden Multisite scenario proves site/network attachment isolation and shared-asset impact handling.
- `MRL-175` Golden crash/partial job scenario resumes same operation with explicit step states and no false success.
- `MRL-176` Golden adversarial AI/MCP scenario cannot expose private media, overwrite canonical original, bypass SRT or perform provider edit without Policy.

## Execution gate

This document specifies evidence only. **MRL executed remains 0/176.** No media replacement, provider edit, reference mutation, derivative regeneration, cache purge, test, benchmark or runtime action is authorized by this protocol.