# WPEssential — Content Duplication & Clone Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `DUP-001…DUP-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Clone/duplicate creates a new entity identity unless the operation is explicitly a revision/snapshot action owned elsewhere.
- Duplicating content never duplicates authorization, secrets, live provider bindings, sessions, payment/order identity or external webhook authority blindly.
- Source and clone remain independently versioned after creation; later source edits do not silently mutate the clone.
- Field/meta/media/relation copying follows typed ownership and privacy rules; unknown protected metadata is not copied by default.
- Clone of a post/order-like object does not make WPE owner of external business truth; Woo/order/provider semantics remain adapter-owned.
- Bulk/idempotent operations require deterministic source→clone operation identity; retries cannot create uncontrolled duplicates.
- Multisite clone is explicit migration/copy with new site-scoped identity; matching numeric IDs are not authority.
- UI “Duplicate” availability is not authorization; every read/write target is server-side capability/Policy checked.

## Exact fixtures

### Group 1 — clone plan schema
- `DUP-001` Create clone plan with stable ID, source object/provider, target type, copy rules, exclusions, target status, identity policy and revision.
- `DUP-002` Reject unknown source provider/object type before any clone state is created.
- `DUP-003` Reject plan that requests same canonical identity reuse for a new clone unless an owning revision API explicitly supports it.
- `DUP-004` Plan update uses expected revision and retains prior diff.
- `DUP-005` Stale plan update fails without overwriting newer clone semantics.
- `DUP-006` Draft plan can be previewed without creating target content.
- `DUP-007` Export/import of plan excludes source private values/secrets and stores portable typed rules only.
- `DUP-008` Capability/Policy denial prevents plan/run even if duplicate UI action is visible.
- `DUP-009` Plan clearly distinguishes copy, reuse/link, omit, transform and unresolved behavior per data family.
- `DUP-010` AI/MCP may draft clone plan but cannot execute bulk clone or privilege-sensitive copy without same Policy/approval.
- `DUP-011` Unknown future plan version fails typed or migrates explicitly rather than silently changing copy semantics.

### Group 2 — content/title/slug/status
- `DUP-012` Clone post/page body into a new object ID while preserving source unchanged.
- `DUP-013` Title strategy copy/prefix/suffix/custom template produces deterministic target title.
- `DUP-014` Slug strategy generates unique target slug according to WordPress rules and never silently overwrites existing target route.
- `DUP-015` Draft-by-default profile prevents source published status from automatically publishing clone unless explicitly configured/authorized.
- `DUP-016` Status mapping rejects unsupported target status and records unresolved choice.
- `DUP-017` Password-protected/private source does not become public merely through clone default.
- `DUP-018` Excerpt/content formatting/blocks round-trip without executing source shortcodes/scripts during cloning.
- `DUP-019` Source post lock/autosave state is not copied as target runtime lock/session state.
- `DUP-020` Clone source fingerprint/revision is recorded for provenance but target receives independent revision stream.
- `DUP-021` Target insert failure leaves no half-claimed successful clone and cleans/quarantines staged dependent writes.
- `DUP-022` Direct clone Ability/REST path enforces same object read/create capabilities as wp-admin action.

### Group 3 — author/date/permission rules
- `DUP-023` Author strategy preserve/source-current/selected-author is explicit and validated against target permissions.
- `DUP-024` Operator cannot assign clone to an author they are forbidden to create/edit content for.
- `DUP-025` Original publish date is preserved only when selected; default clone date can use current creation time without altering source.
- `DUP-026` Modified date/revision metadata is regenerated for clone rather than falsely copied as if target was edited historically.
- `DUP-027` Future/scheduled source does not automatically create a scheduled target unless profile says so and capability permits.
- `DUP-028` Private author/user identifiers are not exposed in logs/export beyond authorized metadata.
- `DUP-029` User lacking source read permission cannot clone source merely because they have generic create permission.
- `DUP-030` User with source read but no target create permission receives denial before dependent media/meta writes.
- `DUP-031` Administrator-equivalent capability is evaluated through WordPress/meta-cap/Policy, not role label string.
- `DUP-032` Cross-site author mapping is explicit; source numeric user ID is not blindly reused as site membership/author authority.
- `DUP-033` AI/MCP cannot select a more privileged target author to escalate access.

### Group 4 — taxonomy mapping
- `DUP-034` Copy selected taxonomy term assignments to target when taxonomy is supported for target type.
- `DUP-035` Excluded taxonomy remains absent without mutating source terms.
- `DUP-036` Target type lacking source taxonomy reports unresolved/skip according to plan rather than silently storing invalid term relation.
- `DUP-037` Hierarchical term assignments preserve canonical term references and do not create duplicate terms by label alone.
- `DUP-038` Cross-site clone maps terms explicitly by package/reference strategy, not numeric term ID.
- `DUP-039` Missing destination term can create/map/skip only under configured policy and capability.
- `DUP-040` Protected/private taxonomy metadata is not copied by generic taxonomy assignment.
- `DUP-041` Term capability checks apply when clone plan is allowed to create missing terms.
- `DUP-042` Taxonomy mapping conflict is item-scoped and target post creation state remains explicit partial/unresolved.
- `DUP-043` Translation/multilingual term adapters preserve language relationships through certified adapter only.
- `DUP-044` Deleting target clone never deletes shared taxonomy terms merely because they were copied assignments.

### Group 5 — field/meta/protected-meta policy
- `DUP-045` Copy allowlisted public/custom fields through Field Registry typed adapters.
- `DUP-046` Protected/underscore/unknown meta defaults to omit/review unless owning registry explicitly permits copy.
- `DUP-047` Serialized/structured field value is copied through its typed storage codec and remains valid.
- `DUP-048` Secret/token/password/session/provider credential field is never cloned into target generic metadata.
- `DUP-049` Field configured “reset on clone” receives its declared default/null behavior.
- `DUP-050` Unique/business identifier field is regenerated/omitted according to field owner rather than copied blindly.
- `DUP-051` Field reference to another entity is handled as copy/reuse/remap/unresolved according to relation/reference policy.
- `DUP-052` Target schema lacking source field reports incompatibility and does not silently drop it while claiming full clone fidelity.
- `DUP-053` Field-level Policy can deny copy even when source post itself is readable.
- `DUP-054` Meta copy failure yields per-field diagnostic and transactional/partial semantics are explicit.
- `DUP-055` Audit/explain output lists copied, reset, omitted, transformed and unresolved fields without leaking protected values.

### Group 6 — media copy/reuse policy
- `DUP-056` Featured image can be reused by reference without duplicating binary when plan selects reuse.
- `DUP-057` Media-copy mode creates new attachment identity/binary only under explicit plan and storage capability.
- `DUP-058` Private/protected source media does not become public through clone.
- `DUP-059` Embedded media references are mapped consistently with selected reuse/copy strategy.
- `DUP-060` Missing source binary produces unresolved media reference rather than false successful copy.
- `DUP-061` Offloaded/CDN media uses owning adapter and does not fabricate local file path.
- `DUP-062` Media copy checks checksum and avoids duplicate binary when dedupe profile permits while keeping new attachment identity semantics explicit.
- `DUP-063` Alt/caption/title metadata follows field privacy/copy rules and source ownership.
- `DUP-064` Attachment parent relationship is assigned intentionally and never changes the source attachment parent when reuse mode is used.
- `DUP-065` Media-copy failure can roll back newly created derivative target attachments when transactional profile requires it.
- `DUP-066` Deleting clone does not delete reused shared media still referenced elsewhere.

### Group 7 — relations/pivot/custom-table references
- `DUP-067` Copy relation edges according to explicit relation rule: reuse related target, duplicate related entity, omit or unresolved.
- `DUP-068` Relation pivot/meta fields are copied only through Relation owner schema.
- `DUP-069` Unique/cardinality constraints are revalidated for new clone rather than assumed valid from source.
- `DUP-070` Cyclic relation graph clone uses visited/source→target map and cannot recurse indefinitely.
- `DUP-071` Custom-table foreign references use typed canonical IDs and not raw blind numeric copy across environments.
- `DUP-072` Related entity the operator cannot read is not exposed through clone preview/log and is omitted/denied according to Policy.
- `DUP-073` “Duplicate related objects” broad operation requires separate high-risk scope and impact preview.
- `DUP-074` Relation write failure reports partial/unresolved state and does not claim full graph clone success.
- `DUP-075` Existing target relation conflict follows owner’s duplicate/update/skip semantics.
- `DUP-076` Cross-site relation copy is blocked unless an explicit migration/cross-site mapping profile exists.
- `DUP-077` Deleting clone removes only target relation edges and never source entity/edge data not owned by target.

### Group 8 — parent/order/hierarchical content
- `DUP-078` Hierarchical post clone can keep same parent when parent is valid in target scope.
- `DUP-079` Clone-parent-together mode maps child target to new cloned parent identity.
- `DUP-080` Missing/unauthorized parent becomes unresolved or root only according to explicit plan; no silent reparent guessing.
- `DUP-081` Parent cycle cannot be introduced through clone mapping.
- `DUP-082` `menu_order` copy occurs only when configured and does not hijack unrelated order contexts.
- `DUP-083` Independent Sequence owner can copy/add target membership through explicit adapter, separate from parent relation.
- `DUP-084` Bulk clone of parent+children preserves deterministic hierarchy source→target mapping.
- `DUP-085` Child source copied without parent retains source relation only when target scope supports it and plan says so.
- `DUP-086` Changing target hierarchy after clone does not mutate source hierarchy.
- `DUP-087` Target slug uniqueness is evaluated inside new parent path/context correctly.
- `DUP-088` Hierarchical clone rollback removes only newly created target objects/memberships covered by run.

### Group 9 — cross-post-type mapping
- `DUP-089` Clone source post type into a different compatible target type using explicit field/taxonomy/status mapping.
- `DUP-090` Unsupported source block/field for target type is reported unresolved rather than silently discarded.
- `DUP-091` Target type required fields/defaults are validated before finalizing clone.
- `DUP-092` Capability checks use target type meta-cap rules, not source type capability assumptions.
- `DUP-093` Rewrite/route slug conflicts for target type are detected before publish.
- `DUP-094` Taxonomy mapping includes only taxonomies supported/mapped for target type.
- `DUP-095` Target-specific unique identifier is regenerated through owning module.
- `DUP-096` Builder/template metadata copied cross-type only through certified adapter compatibility.
- `DUP-097` Woo product/order-like cross-type clone is not allowed through generic DUP unless domain adapter defines safe semantics.
- `DUP-098` Cross-type preview lists lossy/unmapped semantics and can block execution under strict fidelity profile.
- `DUP-099` Source remains unchanged if target validation fails.

### Group 10 — comments/revisions/autosaves exclusions
- `DUP-100` Default clone excludes revisions/autosaves unless explicit archival profile says otherwise.
- `DUP-101` Comment copy is opt-in and creates new comment identities attached to target.
- `DUP-102` Comment author/email/IP/privacy fields follow retention/privacy rules and are not blindly copied.
- `DUP-103` Moderation/status of comments maps through declared policy.
- `DUP-104` Pingbacks/trackbacks are excluded by default to prevent external side-effect duplication.
- `DUP-105` Source revisions are never attached to target under source revision IDs.
- `DUP-106` Target receives a normal initial revision according to WordPress behavior rather than fabricated historical edit timeline.
- `DUP-107` Autosave/session locks/nonces are never cloned.
- `DUP-108` Comment-copy failure is independently reported and cannot make target post appear source-identical falsely.
- `DUP-109` Export/preview redacts protected comment data based on operator Policy.
- `DUP-110` Deleting target comments after rollback does not affect source comments.

### Group 11 — bulk/multi-clone/idempotency
- `DUP-111` Bulk clone creates one deterministic operation item per source and selected target profile.
- `DUP-112` Retry of same idempotency key/source/profile returns existing committed target instead of creating duplicate.
- `DUP-113` Same idempotency key with different source/profile conflicts explicitly.
- `DUP-114` Partial bulk success records per-item target IDs/status and retries only failed/unknown items according to policy.
- `DUP-115` Crash after target creation but before response reconciles operation log before recreate.
- `DUP-116` Cancellation stops new clone items and keeps already committed targets explicitly committed unless rollback is requested/verified.
- `DUP-117` Bulk impact limit prevents accidental unbounded clone of huge query selection without explicit confirmation/policy.
- `DUP-118` Source deleted mid-run causes that item to fail/unresolve without reusing stale object data beyond pinned snapshot policy.
- `DUP-119` Concurrency on same source/profile respects uniqueness/idempotency and cannot produce uncontrolled duplicate targets.
- `DUP-120` Queue redelivery is safe because operation identity survives process restart.
- `DUP-121` Progress counts distinguish planned/created/failed/unknown/rolled-back items and do not infer target health.

### Group 12 — builder/multilingual adapters
- `DUP-122` Certified builder content/meta is copied through adapter without executing arbitrary builder callbacks.
- `DUP-123` Missing/incompatible builder plugin leaves builder-specific data unresolved rather than claiming visual fidelity.
- `DUP-124` Builder global template/library references use reuse/remap policy rather than blind numeric IDs.
- `DUP-125` Multilingual clone can create same-language copy or new-language translation candidate only through certified language adapter.
- `DUP-126` Translation group/link is assigned explicitly and does not overwrite existing language relationships.
- `DUP-127` Locale-specific slug/title strategy respects destination language profile.
- `DUP-128` Builder asset/media references remain consistent with media copy/reuse policy.
- `DUP-129` Dynamic tags/queries remain references to canonical definitions and are not duplicated into private engines.
- `DUP-130` Builder/multilingual adapter version is recorded in clone provenance.
- `DUP-131` Adapter unavailable at restore/replay causes degraded verification, not silent generic-meta fallback if unsafe.
- `DUP-132` AI/MCP can propose builder/translation clone mapping but cannot publish unsupported lossy clone silently.

### Group 13 — Audit/Workflow/Ability integration
- `DUP-133` Clone emits typed event containing actor, source ref, target ref, plan revision and outcome without protected field values.
- `DUP-134` Audit event attribution does not grant identity/authorization and is supplemental evidence only.
- `DUP-135` Workflow trigger on clone runs only after declared durable commit state.
- `DUP-136` Workflow redelivery cannot create second target when clone operation identity is reused.
- `DUP-137` Ability `plan/preview/run/status` enforces same Policy as admin action.
- `DUP-138` Ability payload cannot supply foreign site ID to bypass source/target scope.
- `DUP-139` Notification on clone failure uses bounded metadata and no secrets/private copied values.
- `DUP-140` Audit log cannot be used as canonical clone source for re-creation when source/target records disagree.
- `DUP-141` Workflow side effect failure after clone does not retroactively claim clone failed unless transaction profile explicitly couples them.
- `DUP-142` AI/MCP invocation is attributed separately but executes only through same deterministic Ability/Policy gate.
- `DUP-143` High-risk bulk/cross-site clone can require approval/re-auth beyond ordinary single-object clone.

### Group 14 — Multisite/site ownership
- `DUP-144` Same-site clone stays inside current site object namespace.
- `DUP-145` Cross-site clone requires explicit target site and server-resolved source/target authority.
- `DUP-146` Numeric post/term/user IDs are remapped and never treated as globally portable identities.
- `DUP-147` Network-global user identity is separated from per-site author membership/roles.
- `DUP-148` Site-private media/fields cannot leak to another site without explicit cross-site copy authority.
- `DUP-149` Network template may define clone plan but site execution still checks local object permissions.
- `DUP-150` Site clone/deletion lifecycle does not leave orphaned cross-site DUP operation mappings as active authority.
- `DUP-151` Cross-site provider references/secrets remain unresolved/quarantined unless destination owns compatible connection.
- `DUP-152` Same stable clone-plan key on two sites remains isolated.
- `DUP-153` Network Admin visibility of clone audit does not automatically grant ability to read all protected source content.
- `DUP-154` AI/MCP cross-site clone cannot infer target site authority from user account existing on both sites.

### Group 15 — scale/performance
- `DUP-155` Clone post with 10K typed meta/field values uses bounded iteration and memory.
- `DUP-156` Bulk 10K-object clone records queue/backpressure behavior and does not schedule unbounded simultaneous writes.
- `DUP-157` Large relation graph uses bounded traversal and cycle detection.
- `DUP-158` Large media-copy profile respects storage/bandwidth quotas and concurrency limits.
- `DUP-159` Shared media reuse avoids needless binary duplication under reuse profile.
- `DUP-160` Bulk clone caches source schema/mapping safely without leaking protected values across items/sites.
- `DUP-161` Database query count avoids obvious N+1 patterns for known field/relation adapters within declared budget.
- `DUP-162` Concurrent workers cannot clone same source/item twice under one operation identity.
- `DUP-163` Failure/retry storm remains bounded and respects Job Service fairness.
- `DUP-164` Performance evidence records environment/dataset/adapters for reproducibility.
- `DUP-165` Paper timing estimates do not certify performance; fixture requires executed measurement later.

### Group 16 — end-to-end clone integrity regression
- `DUP-166` Golden: duplicate a page to draft preserving allowed fields/terms and featured-image reuse with new content identity.
- `DUP-167` Golden: clone hierarchical parent+children preserving new target hierarchy and independent order.
- `DUP-168` Golden: cross-post-type clone reports unmapped fields and blocks strict-fidelity run until resolved.
- `DUP-169` Golden: protected meta/secret/provider token is omitted/reset while ordinary typed fields copy correctly.
- `DUP-170` Golden: builder+multilingual clone preserves supported adapter semantics and flags unsupported provider data.
- `DUP-171` Golden: retry after response loss reconciles existing target and does not create duplicate clone.
- `DUP-172` Golden: bulk partial failure reports exact per-item targets and safe retry behavior.
- `DUP-173` Golden: cross-site clone remaps users/terms/media and never reuses foreign numeric IDs as authority.
- `DUP-174` Golden: source deletion after successful clone leaves target independent and source provenance auditable.
- `DUP-175` Golden: target deletion/rollback never deletes source/shared assets outside operation ownership.
- `DUP-176` Golden: AI/MCP adversarial request to clone protected content/secrets into another site or publish with elevated author is denied/draft-only.

## Runtime truth

This protocol is documentation-only. `DUP-001…DUP-176` are **176/176 documented, 0/176 executed**. No content, media, relation, user, site or provider state was cloned or mutated. Development authorization remains **NOT GRANTED / 0/56**.