# WPEssential — Content Order & Sequence Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `ORD-001…ORD-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Order/sequence affects only explicitly configured contexts; it never hijacks unrelated queries.
- `menu_order` is a WordPress adapter, not universal canonical ordering truth.
- Independent sequences remain separate from hierarchy/reparenting unless an explicit operation says otherwise.
- UI drag/drop success is not proof of durable order until versioned persistence succeeds.
- Query/listing adapters consume an explicit sequence profile; they do not infer authorization.
- Translation, WooCommerce and provider adapters keep their own canonical object/business truth.
- Multisite sequence ownership is server-resolved and site-scoped unless a certified network profile says otherwise.
- Every mutation is version-aware, auditable, permission-gated and recoverable through its defined revision semantics.

## Exact fixtures

### Group 1 — definition/version/context
- `ORD-001` Create a sequence definition with stable key, owner scope, target object type, context and ordering strategy; normalized definition is deterministic.
- `ORD-002` Reject a definition whose target object type/provider is unknown; no partial definition persists.
- `ORD-003` Reject duplicate stable key inside the same site/namespace while allowing the same key in an isolated site namespace.
- `ORD-004` Update a definition with expected revision; revision increments and prior revision remains explainable.
- `ORD-005` Reject stale-revision update and return current revision/diff without overwriting newer configuration.
- `ORD-006` Disable a definition; existing stored sequence remains intact but stops affecting configured read adapters.
- `ORD-007` Archive a definition; historical revisions remain queryable and no new writes are accepted unless restored.
- `ORD-008` Export/import a definition without runtime secrets or unrelated site identifiers; semantic options round-trip.
- `ORD-009` Deny create/update/archive when capability/Policy fails even if the admin UI control is visible.
- `ORD-010` AI/MCP draft path produces a draft definition only and cannot publish/apply ordering without the same Policy gate.
- `ORD-011` Unknown future definition version fails typed or migrates through an explicit version adapter; silent reinterpretation is forbidden.

### Group 2 — native menu_order adapter
- `ORD-012` Apply configured `menu_order` ordering to the selected post type/context through supported WordPress APIs only.
- `ORD-013` Verify unrelated post types using `menu_order` are unchanged by the configured sequence.
- `ORD-014` Preserve existing non-WPE `menu_order` values when profile is read-only/observe mode.
- `ORD-015` Reject write when target object lacks supported `menu_order` semantics instead of inventing hidden storage.
- `ORD-016` Bulk reorder writes only selected object IDs and returns per-object success/failure.
- `ORD-017` Concurrent `menu_order` edit outside WPE is detected as version/conflict when expected baseline changed.
- `ORD-018` Deleting an ordered post removes/ignores its sequence membership without renumbering unrelated objects unexpectedly.
- `ORD-019` Restore/revision operation reapplies only the recorded selected-object order and does not restore post content.
- `ORD-020` REST/Ability reorder path enforces the same object capability checks as wp-admin reorder.
- `ORD-021` Multisite call cannot write another site's posts merely by supplying foreign post IDs.
- `ORD-022` Query evidence proves configured `menu_order` adapter is scoped; global main queries remain unchanged unless explicitly targeted.

### Group 3 — independent sequence storage
- `ORD-023` Create independent sequence entries without mutating `menu_order` when independent-storage mode is selected.
- `ORD-024` Sequence item identity includes definition + site/tenant + canonical object reference, preventing cross-provider ID collisions.
- `ORD-025` Insert item between two positions using bounded rank/order semantics without full-table rewrite when supported.
- `ORD-026` Duplicate object membership in a unique sequence is rejected/idempotently returned rather than duplicated.
- `ORD-027` Profile permitting repeated membership stores explicit occurrence identity so duplicates are intentional and addressable.
- `ORD-028` Removing an item does not delete or mutate the underlying WordPress/business object.
- `ORD-029` Replacing the whole sequence uses expected revision/fingerprint and fails on concurrent change.
- `ORD-030` Corrupt/missing sequence row is reported as degraded and can be repaired from authoritative sequence revision evidence only.
- `ORD-031` Sequence cache key includes site/definition/revision and cannot bleed order across sites.
- `ORD-032` Import remaps canonical object references explicitly; unresolved objects remain unresolved rather than guessed by numeric ID.
- `ORD-033` Large independent sequence preserves deterministic total order after repeated insert/move/remove operations.

### Group 4 — drag/drop + keyboard UX
- `ORD-034` Drag one item to a new position; preview and persisted result match after successful versioned save.
- `ORD-035` Drag multiple selected items while preserving their relative order and target insertion semantics.
- `ORD-036` Cancel drag before save leaves durable sequence unchanged.
- `ORD-037` Network/save failure after drag leaves UI in explicit unsaved/error state and does not claim success.
- `ORD-038` Keyboard move-up/down controls can perform every essential reorder action available by pointer.
- `ORD-039` Keyboard focus remains on the moved item and announces its new position through accessible status messaging.
- `ORD-040` Screen-reader labels expose item identity, current position and available move action without relying only on visual handles.
- `ORD-041` Reorder on paginated/virtualized list does not accidentally move hidden items because global position context is explicit.
- `ORD-042` Filtered admin view warns/uses configured filtered-reorder semantics instead of silently rewriting the full unseen sequence.
- `ORD-043` Operator without reorder permission can view order but drag/keyboard mutation endpoints are server-denied.
- `ORD-044` Concurrent browser tabs cause stale save conflict rather than last-writer silently overwriting newer sequence.

### Group 5 — hierarchy/sibling/reparent separation
- `ORD-045` Reorder siblings within one parent without changing each item's parent relationship.
- `ORD-046` Attempt to drag across parent groups is rejected when reparenting is disabled.
- `ORD-047` Explicit reparent mode presents parent change separately and requires applicable edit capability for both hierarchy and sequence effects.
- `ORD-048` Reparent validation rejects cycles such as making an ancestor a child of its descendant.
- `ORD-049` Child order remains scoped to its parent when sibling-order strategy is configured.
- `ORD-050` Moving a parent does not silently rewrite child order unless the selected hierarchy strategy requires it.
- `ORD-051` Deleting a parent follows WordPress/object lifecycle rules; sequence code does not invent child deletion semantics.
- `ORD-052` Orphaned child relation is surfaced as lifecycle inconsistency rather than silently attached to root by sequence engine.
- `ORD-053` Hierarchical taxonomy/post adapter preserves native parent IDs while applying display order.
- `ORD-054` Import with missing parent retains unresolved mapping and blocks destructive reparent guessing.
- `ORD-055` Permission to reorder siblings does not grant permission to edit parent relationships.

### Group 6 — taxonomy/term ordering
- `ORD-056` Create a term-order sequence for one taxonomy and verify other taxonomies are unaffected.
- `ORD-057` Term identity uses taxonomy/term reference correctly and does not confuse shared numeric IDs across contexts.
- `ORD-058` Hierarchical term sibling ordering preserves `parent` relationship.
- `ORD-059` Term deletion removes sequence membership idempotently without deleting unrelated taxonomy metadata.
- `ORD-060` Term merge/split lifecycle requires explicit mapping; sequence does not guess successor identity.
- `ORD-061` REST term ordering requires taxonomy-specific capabilities and same Policy as admin path.
- `ORD-062` Term query consumes sequence only when the explicit query/listing adapter requests it.
- `ORD-063` Unknown/external taxonomy provider cannot be written through native-term adapter without certified provider support.
- `ORD-064` Large taxonomy reorder persists deterministic ranks without N+1 unbounded writes beyond declared profile.
- `ORD-065` Multisite term order remains site-owned even when taxonomies have the same slug on multiple sites.
- `ORD-066` Import/export preserves taxonomy slug/provider reference plus sequence semantics without assuming matching term IDs.

### Group 7 — query/listing integration
- `ORD-067` Query Builder explicit sequence option joins/applies the selected sequence definition and returns deterministic order.
- `ORD-068` Query without sequence option retains its original ORDER BY semantics unchanged.
- `ORD-069` Explicit secondary sort resolves ties deterministically without contradicting primary sequence rank.
- `ORD-070` Missing sequence membership follows configured append/prepend/exclude/fallback policy and is explainable.
- `ORD-071` Pagination over ordered results has stable item boundaries across pages for an unchanged sequence revision.
- `ORD-072` Sequence revision change invalidates affected query/listing caches only.
- `ORD-073` Search/filtering may narrow results while preserving relative order of remaining sequence members.
- `ORD-074` Query adapter cannot expose objects that source Query/Policy would otherwise deny; sequence is not authorization.
- `ORD-075` Remote/Data Source listing uses typed external references and does not coerce provider IDs into local post IDs.
- `ORD-076` Explain output names sequence definition/revision/fallback that produced final ordering.
- `ORD-077` Unsupported query provider returns typed unsupported-order diagnostic instead of silently falling back to a different order.

### Group 8 — conflicts/coexistence
- `ORD-078` Detect another ordering plugin/filter affecting the same query context and report coexistence owner/conflict.
- `ORD-079` Observe-only compatibility mode reports effective external order without overwriting it.
- `ORD-080` Explicit WPE-owned mode applies only after conflict acknowledgement/profile permits takeover.
- `ORD-081` Native page `menu_order` and independent WPE sequence conflict is resolved by configured precedence, never hidden double-sorting.
- `ORD-082` Woo/product ordering adapter does not overwrite Woo catalog/business semantics outside certified context.
- `ORD-083` Builder/listing-specific ordering remains adapter-scoped and does not inject global hooks indiscriminately.
- `ORD-084` Database row imported from competitor is normalized into WPE definition rather than retaining executable competitor callbacks.
- `ORD-085` Disabling competitor after migration does not occur automatically; coexistence verification is required first.
- `ORD-086` Conflicting two WPE sequence definitions for one context are rejected or resolved by explicit precedence policy.
- `ORD-087` Conflict diagnostic contains no protected object data beyond operator authorization.
- `ORD-088` AI recommendation to resolve conflict remains draft and cannot disable another plugin or publish precedence automatically.

### Group 9 — concurrency/version conflicts
- `ORD-089` Every mutation includes expected sequence revision or equivalent conflict token.
- `ORD-090` Two simultaneous moves from same revision result in one accepted change and one typed conflict/rebase requirement.
- `ORD-091` Idempotent replay of the same mutation identity does not apply the move twice.
- `ORD-092` Same idempotency key with different reorder payload is rejected as conflict.
- `ORD-093` Partial batch write records exact succeeded/failed items and never reports whole batch success falsely.
- `ORD-094` Unknown DB/provider outcome is reconciled against durable sequence revision before retry where duplicate effects are possible.
- `ORD-095` Lock/transaction timeout leaves sequence in previous or explicitly recoverable state, never mixed unreported order.
- `ORD-096` Long-running normalization detects intervening revision change before commit.
- `ORD-097` Cache invalidation happens after durable commit and stale caches cannot become new source truth.
- `ORD-098` Audit records actor, old revision, new revision and operation identity without logging unrelated protected content.
- `ORD-099` High-contention benchmark fixture later measures conflict rate/latency but remains unexecuted until consent.

### Group 10 — revisions/rollback/import
- `ORD-100` Successful publish creates immutable sequence revision metadata sufficient to reconstruct ordering semantics.
- `ORD-101` Rollback creates a new revision based on a prior revision rather than deleting intervening history.
- `ORD-102` Rollback fails safely when referenced objects no longer exist, listing unresolved members.
- `ORD-103` Export contains definition, ordering references, version and provenance but no secrets.
- `ORD-104` Import dry run reports create/update/conflict/unresolved object mappings before persistence.
- `ORD-105` Import replace requires destructive preview and applicable capability; merge does not silently reorder unmentioned items.
- `ORD-106` Repeated import with same package identity is idempotent under selected conflict policy.
- `ORD-107` Imported cross-site references require explicit destination mapping; numeric IDs are never assumed portable.
- `ORD-108` Old schema import migrates through declared version transform and records provenance.
- `ORD-109` Corrupt package/hash/schema fails before any sequence mutation.
- `ORD-110` AI/MCP may explain revision diff/prepare rollback plan but applying rollback follows normal approval/Policy.

### Group 11 — translation/Woo/provider adapters
- `ORD-111` Translation adapter defines whether order is shared by translation group or independent per locale and enforces selected profile.
- `ORD-112` Missing translation does not create a phantom ordered object; fallback behavior is explicit.
- `ORD-113` Locale-specific sequence cannot expose a protected/unpublished translation through ordering alone.
- `ORD-114` Woo product order uses Woo-supported APIs/data stores and does not assume private table layout.
- `ORD-115` Variable product/variation ordering is distinguished from catalog product ordering.
- `ORD-116` Woo stock/order/payment facts are never changed by catalog ordering operation.
- `ORD-117` External provider adapter pins provider/version/capability metadata before writes.
- `ORD-118` Provider timeout after reorder is `unknown/reconcile_required`, not automatic failure/retry.
- `ORD-119` Provider partial acceptance records per-item result and retries only safe failed/unknown items after reconciliation.
- `ORD-120` Provider rate limit honors retry-after/backoff and does not block unrelated site/tenant queues unfairly.
- `ORD-121` Provider/translation adapter unavailable returns degraded/unsupported truth without silently falling back to local mutation.

### Group 12 — Multisite/site ordering
- `ORD-122` Same sequence key may exist independently on two sites without cache/storage collision.
- `ORD-123` Site admin can mutate only site-owned sequence definitions permitted by Policy.
- `ORD-124` Network template can instantiate a sequence definition on selected sites without sharing live sequence data by default.
- `ORD-125` Network-enforced sequence requires explicit network authority and cannot be changed by ordinary site admin.
- `ORD-126` Cross-site aggregate listing ordering requires explicit network contract and per-source Policy.
- `ORD-127` Site clone copies definition only per clone policy and remaps object identities instead of reusing source-site IDs blindly.
- `ORD-128` Site deletion cleans WPE-owned sequence rows/caches according lifecycle without touching other sites.
- `ORD-129` Site switch during one mutation is impossible; server-resolved site identity is pinned to operation.
- `ORD-130` Shared user identity across network does not grant cross-site sequence management authority.
- `ORD-131` Network import dry run reports per-site conflicts/unresolved references before apply.
- `ORD-132` Network scale fixture later validates thousands of site definitions with isolation; no paper result counts as execution.

### Group 13 — security/permissions/audit
- `ORD-133` Read, create, update, publish, reorder, import/export and rollback permissions are independently enforceable where configured.
- `ORD-134` Nonce/UI token alone is insufficient; capability/Policy is checked server-side for mutation.
- `ORD-135` Object IDs in request body do not grant authorization to reorder those objects.
- `ORD-136` Bulk reorder validates every target object authorization or follows explicit all-or-nothing/per-item policy.
- `ORD-137` CSRF attempt without valid request protection is rejected before mutation.
- `ORD-138` Malformed oversized reorder payload is bounded by item/size/depth limits.
- `ORD-139` Audit entry records normalized sequence operation but redacts protected titles/content when actor lacks access.
- `ORD-140` Audit log is evidence of operation, not canonical sequence storage.
- `ORD-141` Export endpoint cannot exfiltrate sequence members outside caller's authorized scope.
- `ORD-142` AI/MCP principal is attributed and subject to same capability/Policy/rate limits.
- `ORD-143` Security regression fixture later verifies direct REST/Ability/admin endpoint parity; UI hiding never counts as enforcement.

### Group 14 — lifecycle/delete/orphan repair
- `ORD-144` Object deletion removes/marks sequence membership idempotently according configured retention policy.
- `ORD-145` Definition deletion is blocked or previewed when active query/listing dependencies exist.
- `ORD-146` Archived definition is not applied to live query contexts unless explicitly restored.
- `ORD-147` Orphan scan distinguishes missing object from temporarily unavailable remote provider.
- `ORD-148` Orphan repair cannot relink by title/name guess when identity is ambiguous.
- `ORD-149` Purge of old revisions follows retention/legal-hold rules and does not delete current sequence.
- `ORD-150` Site/plugin deactivation leaves durable definitions intact unless explicit uninstall policy says otherwise.
- `ORD-151` Re-activation rebuilds derived caches from durable definition/revision, not from stale cache state.
- `ORD-152` Provider object resurrection/remap requires explicit identity evidence before restoring prior rank.
- `ORD-153` Repair operation itself is versioned/audited and conflict-aware.
- `ORD-154` Backup/restore of sequence data verifies definition/revision/member counts before declaring recovered.

### Group 15 — 10K/100K scale
- `ORD-155` 10K-item read fixture later measures ordered query latency/memory with declared DB/index profile.
- `ORD-156` 100K-item independent sequence fixture later measures rank lookup and pagination stability.
- `ORD-157` Large move operation avoids unbounded full-sequence rewrite where storage strategy claims sparse ranks.
- `ORD-158` Rank compaction/normalization is bounded, resumable or explicitly transactional for large sequences.
- `ORD-159` 10K drag/virtualized admin view remains keyboard-accessible and does not require rendering all rows.
- `ORD-160` Concurrent bulk reorder benchmark measures lock/conflict behavior under declared worker count.
- `ORD-161` Ordered query cache hit/miss behavior is measured with revision-aware invalidation.
- `ORD-162` Network-scale test later covers many sites without cross-site cache/key collisions.
- `ORD-163` Import of 100K references streams/chunks with bounded memory and per-chunk reconciliation.
- `ORD-164` Audit volume from bulk operations is bounded/aggregated without losing required per-operation provenance.
- `ORD-165` Performance claims remain `NOT EXECUTED` until actual hardware/software/query profile is recorded.

### Group 16 — golden editorial/catalog scenarios
- `ORD-166` Golden editorial pages scenario preserves explicit homepage/article ordering while unrelated archive sorting remains unchanged.
- `ORD-167` Golden hierarchical pages scenario reorders siblings without accidental reparenting.
- `ORD-168` Golden taxonomy scenario orders product categories/terms with hierarchy intact.
- `ORD-169` Golden independent curated list scenario includes only intended objects and deterministic missing-member fallback.
- `ORD-170` Golden Woo catalog scenario changes display sequence without mutating price/stock/order/payment truth.
- `ORD-171` Golden multilingual scenario proves selected shared-vs-locale order semantics and no protected translation leak.
- `ORD-172` Golden concurrent-editor scenario produces typed conflict rather than silent overwrite.
- `ORD-173` Golden rollback scenario creates a new revision restoring prior order with unresolved-object report if needed.
- `ORD-174` Golden import/migration scenario remaps identities explicitly and leaves unresolved references pending.
- `ORD-175` Golden Multisite scenario proves same keys/IDs cannot bleed ordering across sites.
- `ORD-176` Golden adversarial AI/MCP scenario cannot publish/reorder unauthorized objects or bypass Policy through a generated plan.

## Execution gate

This document specifies evidence only. **ORD executed remains 0/176.** No reorder mutation, WordPress runtime, Woo operation, provider call, test, benchmark or AI/MCP execution is authorized by this protocol.