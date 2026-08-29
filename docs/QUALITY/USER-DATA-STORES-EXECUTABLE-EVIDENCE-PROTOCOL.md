# WPEssential — User Data Stores, Favorites & Collections Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `UDS-001…UDS-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- User Data Store membership/favorite state ≠ entitlement, authorization, Woo cart/order state or canonical source-object truth.
- Guest identity is temporary/profile-bound and cannot be assumed to be the same person after login without explicit merge semantics.
- Shared/team collections require explicit ownership/membership/Policy; knowing a collection ID never grants access.
- Store ordering/metadata is scoped to the store membership, not the source object.
- Privacy/export/erase and Multisite ownership are server-resolved and cannot be bypassed through client IDs.
- Cache/REST/AI/MCP paths use the same Policy and tenant/site boundaries.

## Exact fixtures

### Group 1 — store definitions/types
- `UDS-001` Create store definition with stable key, owner model, allowed object providers/types, membership uniqueness and metadata schema.
- `UDS-002` Reject unknown store type/provider/object reference schema before persistence.
- `UDS-003` Same store key is unique inside one site/tenant namespace but may exist independently on another isolated site.
- `UDS-004` Definition revision update requires expected revision and preserves prior schema/provenance.
- `UDS-005` Disable store stops new mutations while preserving stored memberships for later restore/export per lifecycle policy.
- `UDS-006` Archive store prevents ordinary use without silently deleting entries.
- `UDS-007` Definition can declare private, user-owned, shared/team or system-derived profile with explicit semantics.
- `UDS-008` Object reference uses typed provider + canonical ID and prevents local/remote numeric-ID collision.
- `UDS-009` Metadata schema rejects undeclared/private fields and bounded sizes/types are enforced.
- `UDS-010` AI/MCP may draft store definition but cannot publish broad/shared access or mutate entries outside Policy.
- `UDS-011` Unknown definition schema version fails typed or migrates through explicit version transform.

### Group 2 — add/remove/toggle idempotency
- `UDS-012` Add a permitted object to unique-membership store and return stable membership identity.
- `UDS-013` Repeating identical add operation returns existing membership/idempotent success rather than duplicate row.
- `UDS-014` Same idempotency key with different object/payload is rejected as conflict.
- `UDS-015` Remove existing membership once and make repeated remove idempotent/no-op according API contract.
- `UDS-016` Toggle uses current version/expected state so concurrent toggles cannot produce ambiguous double flip.
- `UDS-017` Add fails when source object reference is invalid/unresolvable under store provider rules.
- `UDS-018` Add cannot expose or store object the caller is forbidden to reference under source Policy.
- `UDS-019` Batch add returns per-item status and does not claim full success after partial failure.
- `UDS-020` Unknown remote-source outcome remains pending/reconcile-required where external membership side effect exists.
- `UDS-021` Mutation audit records actor/store/object/operation identity without copying protected source content.
- `UDS-022` REST/Ability/AI mutation paths enforce same membership ownership/Policy as UI action.

### Group 3 — ordering/metadata
- `UDS-023` Reorder collection members without mutating source objects or unrelated store order.
- `UDS-024` Membership metadata update changes only declared metadata fields and increments membership/store revision as designed.
- `UDS-025` Duplicate metadata key/type mismatch is rejected rather than silently coerced.
- `UDS-026` Position insertion/move is deterministic under stable rank/order strategy.
- `UDS-027` Concurrent reorder/update uses expected revision and rejects stale overwrite.
- `UDS-028` Source object title/price/content change does not overwrite user-authored collection note/tag metadata.
- `UDS-029` Deleted source object produces missing/unavailable membership state instead of silently reassigning to another object ID.
- `UDS-030` Hidden/private metadata is omitted from public/shared collection rendering unless authorized.
- `UDS-031` Import/export preserves membership order and typed metadata without guessing source IDs.
- `UDS-032` Cache key includes user/shared-owner, site, store and revision so order/metadata cannot bleed between collections.
- `UDS-033` Large reorder remains bounded and deterministic; benchmark evidence remains unexecuted until later authorization.

### Group 4 — limits/expiry/eviction
- `UDS-034` Per-store max-membership limit rejects or applies declared eviction policy before adding beyond capacity.
- `UDS-035` Per-user/store quota is isolated so one user's entries do not consume another user's private quota incorrectly.
- `UDS-036` Expiry timestamp uses explicit timezone/clock semantics and expired membership no longer appears as active.
- `UDS-037` Expiry cleanup is idempotent and does not delete source object.
- `UDS-038` LRU/FIFO/custom eviction strategy is explicit and records which membership was evicted.
- `UDS-039` Pinned/protected membership is exempt from eviction only when profile declares that behavior.
- `UDS-040` Quota/expiry settings update does not silently purge existing data without preview/lifecycle rule.
- `UDS-041` Legal hold/retention policy can block destructive cleanup for applicable stored metadata.
- `UDS-042` Over-limit import reports excess/conflict handling instead of silently dropping memberships.
- `UDS-043` Rate limit and storage quota are separate; rate-limit denial never mutates store.
- `UDS-044` AI/MCP cannot bypass configured membership limit/expiry/retention by generating direct entry calls.

### Group 5 — guest identity/storage
- `UDS-045` Guest store uses explicit opaque guest identity/token/profile and never trusts client-supplied user ID.
- `UDS-046` Guest token is unguessable, scoped, expiring/rotatable according profile and stored with safe cookie/storage attributes.
- `UDS-047` Guest favorites on one browser/session do not appear for unrelated guest identity.
- `UDS-048` Guest token tampering cannot select another guest's collection.
- `UDS-049` Guest storage disabled profile refuses persistence and may operate ephemeral-only as declared.
- `UDS-050` Consent/privacy profile can prohibit non-essential guest persistence before allowed signal.
- `UDS-051` Guest collection expiration/cleanup follows declared retention and does not affect registered-user stores.
- `UDS-052` Guest source-object access is reauthorized at render; favorite membership does not expose protected resource.
- `UDS-053` Full-page/cache layer cannot cache one guest's store state into another guest's response.
- `UDS-054` Guest export/access capability is limited to possession/identity profile and does not reveal other guest records.
- `UDS-055` AI/MCP has no ambient guest token access and cannot enumerate guest collections.

### Group 6 — guest→user merge
- `UDS-056` On authenticated login, merge is triggered only under explicit merge profile and server-resolved user identity.
- `UDS-057` Unique memberships present in both guest and user store deduplicate deterministically.
- `UDS-058` Conflicting membership metadata follows declared guest-vs-user authority/merge rule and records provenance.
- `UDS-059` Guest order merged into existing user order follows explicit append/prepend/interleave policy.
- `UDS-060` Merge is idempotent so login/retry does not duplicate entries.
- `UDS-061` Partial merge failure records per-entry state and does not delete guest data before successful durable transfer where policy requires.
- `UDS-062` Same guest token cannot be merged into two different users without explicit conflict/recovery policy.
- `UDS-063` Expired/revoked guest token cannot claim old guest collection after login.
- `UDS-064` Merge reauthorizes source objects and may omit entries user still cannot access rather than granting access.
- `UDS-065` Guest record cleanup after merge follows retention/privacy policy and is independently auditable.
- `UDS-066` AI/MCP cannot initiate cross-account guest merge or choose target user from payload alone.

### Group 7 — Query/Listing integration
- `UDS-067` Query Builder can explicitly source current user's selected store through typed store provider.
- `UDS-068` Query without store provider remains unaffected; User Data Store does not globally filter listings.
- `UDS-069` Store query preserves membership order when requested and applies declared secondary sort only for ties/fallback.
- `UDS-070` Missing/deleted source objects follow configured skip/include-placeholder behavior.
- `UDS-071` Query source reauthorizes every protected source object; store membership is not authorization.
- `UDS-072` Facets/counts do not reveal hidden memberships/source objects beyond caller Policy.
- `UDS-073` Shared/team collection listing checks collection access plus each source object access.
- `UDS-074` Remote-source store item uses typed provider ID and does not coerce into local post query.
- `UDS-075` Pagination remains stable for an unchanged store revision.
- `UDS-076` Store revision invalidates only affected listing/query caches.
- `UDS-077` Explain output identifies store/revision/owner/fallback without leaking unauthorized membership metadata.

### Group 8 — REST/Abilities/rate limits
- `UDS-078` REST list endpoint requires authenticated/guest/shared access appropriate to store profile.
- `UDS-079` REST add/remove/toggle enforces store ownership and source-object Policy server-side.
- `UDS-080` Ability calls expose same validation/authorization as REST and cannot accept hidden privileged flags.
- `UDS-081` Client-supplied owner/user/site ID is treated as reference, never authority.
- `UDS-082` Per-principal/store mutation rate limit prevents abuse without changing store state on denial.
- `UDS-083` Bulk endpoint enforces bounded item count/payload size and per-item validation.
- `UDS-084` Enumeration-resistant responses do not reveal existence of private collection when caller lacks access.
- `UDS-085` ETag/revision token supports conditional update and stale mutation conflict.
- `UDS-086` Idempotency key prevents duplicate add/create on retry.
- `UDS-087` Logs/metrics exclude sensitive item metadata/token contents by default.
- `UDS-088` AI/MCP principal is rate/Policy scoped and cannot bypass REST/Ability ownership checks.

### Group 9 — privacy/export/erase
- `UDS-089` Store definition classifies membership/metadata fields for privacy and retention.
- `UDS-090` User privacy export includes only that subject's authorized WPE-owned store data and portable source references.
- `UDS-091` Erase request deletes/anonymizes erasable private store records according retention/legal-hold policy.
- `UDS-092` Erasing store membership never deletes canonical source object.
- `UDS-093` Shared collection ownership/membership data is separated so one member's erasure does not silently destroy other users' shared collection.
- `UDS-094` Guest data retention/erase follows opaque guest identity capabilities and declared privacy profile.
- `UDS-095` Protected metadata is redacted from ordinary export when caller lacks required scope.
- `UDS-096` Downstream replicas/cache entries are invalidated on erase without claiming remote deletion until confirmed.
- `UDS-097` Legal hold blocks destructive erase only for authorized scope and is reported explicitly.
- `UDS-098` Privacy logs record operation metadata without preserving erased sensitive payload.
- `UDS-099` AI/MCP cannot export/erase another subject's store by supplying subject ID alone.

### Group 10 — shared/team collections
- `UDS-100` Create shared/team collection with explicit owner and membership/role Policy.
- `UDS-101` Invite/add member requires owner/delegated capability and explicit target identity resolution.
- `UDS-102` Viewer/editor/manager permissions are distinct and enforced on mutation endpoints.
- `UDS-103` Revoked member loses future collection access; cached shared views are invalidated accordingly.
- `UDS-104` Shared collection ID/token alone does not grant access.
- `UDS-105` Concurrent edits use collection revision and reject stale overwrite.
- `UDS-106` Ownership transfer is explicit, audited and cannot target unresolved/unauthorized identity.
- `UDS-107` Team removal follows declared ownership/orphan policy and does not delete source objects.
- `UDS-108` Shared note/metadata visibility follows collection/member Policy and does not leak private source fields.
- `UDS-109` Public-share mode is a separate explicit profile with revocable/expiring scoped token where supported.
- `UDS-110` AI/MCP cannot add itself/member, transfer ownership or publish private collection without same approval/Policy.

### Group 11 — Woo cart/order boundary
- `UDS-111` Wishlist/favorite product membership stores product reference only; it is not Woo cart line/order.
- `UDS-112` Add favorite does not reserve/decrement stock.
- `UDS-113` Favorite metadata may store user note/desired quantity but does not become price/stock/order truth.
- `UDS-114` “Add to cart from wishlist” delegates to Woo supported cart API and revalidates purchasability/current price/stock.
- `UDS-115` Woo add-to-cart failure leaves wishlist membership unchanged unless explicit separate action succeeds.
- `UDS-116` Cart/order changes do not silently remove favorites unless configured event profile explicitly does so.
- `UDS-117` Compare collection reads current product attributes through Woo/Data Source; cached favorite snapshot is not canonical product truth.
- `UDS-118` Deleted/unpublished product becomes unavailable membership and cannot be purchased through stale favorite reference.
- `UDS-119` Woo customer/site boundaries remain canonical; wishlist cannot access another customer's private data.
- `UDS-120` HPOS/private table assumptions are prohibited; Woo adapter uses supported APIs/Data Stores.
- `UDS-121` AI/MCP cannot turn favorites into order/payment/stock mutations without Woo adapter and explicit commerce authorization.

### Group 12 — imports/migration
- `UDS-122` Import package schema defines store, owner mapping, source references, metadata and order explicitly.
- `UDS-123` Dry run reports create/merge/skip/conflict/unresolved identities before persistence.
- `UDS-124` Legacy favorites/wishlist import never trusts numeric source/user IDs without destination mapping.
- `UDS-125` Duplicate imported membership follows store uniqueness/deduplication policy.
- `UDS-126` Imported protected metadata is accepted only when target schema/Policy permits it.
- `UDS-127` Merge preserves existing user-authored metadata according explicit conflict rule.
- `UDS-128` Replace operation requires destructive preview/authorization and does not delete source objects.
- `UDS-129` Repeated same-package import is idempotent.
- `UDS-130` Cross-site migration remaps user/site/source identities and cannot silently attach data to same-number IDs.
- `UDS-131` Corrupt/schema-mismatched package fails before mutation.
- `UDS-132` AI/MCP may draft mapping but cannot execute cross-user migration or destructive replace automatically.

### Group 13 — Multisite
- `UDS-133` Same user/network account has independent site-owned private stores unless explicit network profile says otherwise.
- `UDS-134` Same store key/object numeric ID across two sites cannot collide in storage/cache.
- `UDS-135` Site admin cannot read/mutate another site's user stores by supplying site ID.
- `UDS-136` Network template can create store definitions without copying live memberships by default.
- `UDS-137` Network-shared collection requires explicit network owner/Policy and per-source access.
- `UDS-138` Cross-site aggregate favorites view requires explicit network contract; ordinary membership does not imply access.
- `UDS-139` Site clone copies definitions per policy but quarantines/remaps live user/guest memberships by default.
- `UDS-140` Site deletion removes site-owned store data according retention without touching other sites.
- `UDS-141` Shared user identity deletion/export correctly enumerates per-site stores without cross-site leakage.
- `UDS-142` Network cache keys include site/store/owner identity.
- `UDS-143` AI/MCP site principal cannot enumerate network-wide stores unless explicitly network-authorized.

### Group 14 — concurrency/cache isolation
- `UDS-144` Membership mutation uses store/member revision or atomic uniqueness so concurrent duplicate adds do not duplicate rows.
- `UDS-145` Concurrent add/remove on same object results in deterministic final state under expected-state semantics.
- `UDS-146` Shared collection concurrent metadata edits reject stale revision rather than silent overwrite.
- `UDS-147` Cache is invalidated after durable commit; stale cache never becomes authoritative membership state.
- `UDS-148` User A cache key cannot return User B private membership set.
- `UDS-149` Guest cache key cannot bleed across guest tokens/sessions.
- `UDS-150` Full-page caching integrates variation/private fragment strategy so private store indicators are not shared publicly.
- `UDS-151` Unknown DB/write outcome is reconciled before retry where duplicate membership may result.
- `UDS-152` Batch partial failure exposes exact item states and safe retry identity.
- `UDS-153` Queue/event redelivery is idempotent and does not duplicate memberships/notifications.
- `UDS-154` Cache corruption triggers rebuild from durable store records, not reverse write into durable state.

### Group 15 — high-cardinality/store scale
- `UDS-155` 10K memberships in one collection fixture later measures paginated read/order performance with declared DB/index profile.
- `UDS-156` 100K user/store membership fixture later measures lookup/count/index behavior with bounded memory.
- `UDS-157` High-cardinality metadata filtering uses declared indexed/queryable fields and refuses unbounded arbitrary scans where policy forbids.
- `UDS-158` Bulk add/remove chunks operations and records per-chunk idempotency/reconciliation.
- `UDS-159` Guest cleanup at scale uses bounded batches and does not lock active-user stores globally.
- `UDS-160` Shared collection fan-out notifications use queue/backpressure and do not block durable membership commit.
- `UDS-161` Cache strategy remains user/store scoped without key explosion beyond declared limits.
- `UDS-162` Network-scale test later verifies many sites/users without cross-tenant collision.
- `UDS-163` Privacy export/erase at scale streams/chunks and reports incomplete state accurately.
- `UDS-164` Metrics/logs remain bounded and avoid raw membership metadata payloads.
- `UDS-165` Performance claims remain NOT EXECUTED until reproducible environment/results are recorded.

### Group 16 — favorites/wishlist/compare golden scenarios
- `UDS-166` Golden private favorites scenario isolates two users with same product reference.
- `UDS-167` Golden guest favorites scenario persists scoped guest data and merges idempotently after login.
- `UDS-168` Golden wishlist scenario preserves order/notes while Woo price/stock remain current Woo truth.
- `UDS-169` Golden compare scenario renders only authorized/current product attributes without treating stored membership as product snapshot truth.
- `UDS-170` Golden shared collection scenario enforces viewer/editor/owner roles and revocation.
- `UDS-171` Golden expiry/limit scenario evicts/expires according explicit policy without deleting source objects.
- `UDS-172` Golden privacy erase scenario removes erasable membership data while retaining authorized shared/legal-hold state accurately.
- `UDS-173` Golden import scenario maps identities explicitly and leaves unresolved objects/users pending.
- `UDS-174` Golden Multisite scenario proves same user/store/object IDs do not leak across sites.
- `UDS-175` Golden concurrency scenario resolves duplicate/toggle races with deterministic revision/idempotency semantics.
- `UDS-176` Golden adversarial AI/MCP scenario cannot enumerate private collections, merge accounts or convert favorites into Woo order/payment mutations.

## Execution gate

This document specifies evidence only. **UDS executed remains 0/176.** No favorite/store mutation, login merge, Woo cart action, privacy erase, test, benchmark or AI/MCP execution is authorized by this protocol.