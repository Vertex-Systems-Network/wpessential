# WPEssential — JetEngine Existing-Surface Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `JEX-001…JEX-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- JEX is parity/refinement evidence only; it must not create duplicate Fields, Relations, Query, Custom Tables, Listings, Conditions/DVR or Solution Blueprint engines.
- CCT-style presets remain typed Custom Table/application-entity compositions, not hidden custom storage runtimes.
- Visibility ≠ authorization; REST/query endpoint publication requires Policy/rate/row limits.
- Reference Data remote source goes through Sync/Connection; no ad-hoc unsafe fetch.
- AI-generated structure remains Draft/Blueprint Plan until validated/approved; planning output ≠ implementation consent.

## Exact fixtures

### Group 1 — CCT preset
- `JEX-001` Create CCT-style application entity preset by composing Custom Table + Fields + Admin Columns + CRUD metadata under one stable definition.
- `JEX-002` Reject preset whose field/table owner references are unknown or schema-incompatible.
- `JEX-003` Preset generation produces ordinary canonical owner definitions, not a private JEX data engine.
- `JEX-004` Stable entity key/version maps deterministically to table/field/admin definitions.
- `JEX-005` Changing preset revision creates explicit owner-definition diff and does not silently rebuild storage.
- `JEX-006` Disable/archive preset does not delete table/data automatically.
- `JEX-007` Public exposure remains off unless explicit route/listing/REST definition exists.
- `JEX-008` Import/export uses canonical owner packages/mappings and records preset provenance.
- `JEX-009` Permissions are delegated to owner capabilities/Policy rather than one broad JEX super-capability.
- `JEX-010` AI/MCP may draft CCT preset but cannot create tables/migrations/runtime without normal owner gates.
- `JEX-011` Unknown preset schema/version fails typed or migrates explicitly.

### Group 2 — Custom Table isolation
- `JEX-012` Table-per-type profile produces unique canonical table identity and declared site/network ownership.
- `JEX-013` Same logical entity key on two isolated sites cannot collide in physical table/cache ownership.
- `JEX-014` Custom Table schema uses owner-supported typed columns/indexes and rejects arbitrary SQL fragments.
- `JEX-015` Field writes use canonical Custom Table/Fields APIs, not direct JEX SQL shortcuts.
- `JEX-016` Tenant/site discriminator strategy is explicit when shared physical table profile is supported.
- `JEX-017` Cross-entity relation/reference validates canonical target owner and cannot trust numeric ID alone.
- `JEX-018` Schema change requires migration Plan/version and cannot silently reinterpret existing data.
- `JEX-019` Table drop/delete is separate destructive lifecycle action with dependency/data preview.
- `JEX-020` Backup/export identifies table owner/schema/data separately and no secret connection values are embedded.
- `JEX-021` Query/listing cache keys include site/table/schema revision.
- `JEX-022` AI/MCP cannot select raw table names/SQL to bypass canonical Custom Table owner.

### Group 3 — public route/listing
- `JEX-023` Optional public route is created only through explicit registered route/listing definition.
- `JEX-024` Route slug collision is detected before publish and never hijacks existing WordPress route silently.
- `JEX-025` Route source Query/Listing is typed and row/object access is reauthorized by Policy.
- `JEX-026` Route visibility condition does not replace direct resource authorization.
- `JEX-027` Pagination/filter/query params are allowlisted/typed/bounded.
- `JEX-028` Public route output escapes/sanitizes fields/components by context.
- `JEX-029` Private entity/field cannot leak through public route count/facet/empty-state side channel.
- `JEX-030` Route disabled/archive removes routing effect while preserving entity data.
- `JEX-031` Full-page/cache behavior varies by audience/Policy where protected data can appear.
- `JEX-032` Multisite route registration is site-scoped and cannot override another site's route.
- `JEX-033` AI/MCP cannot publish public entity route without same route/listing approval.

### Group 4 — import/export/REST/relation
- `JEX-034` Entity export uses canonical Custom Table/Fields/Relations schemas and portable typed references.
- `JEX-035` Import dry run reports schema/field/relation/identity conflicts before writes.
- `JEX-036` Numeric IDs from source package are not assumed portable; mappings are explicit.
- `JEX-037` REST publication requires explicit endpoint definition, capability/Policy, rate and row limits.
- `JEX-038` REST writes validate field schema and object-level Policy through canonical owners.
- `JEX-039` Relation import validates parent/child owner/types and unresolved references remain pending.
- `JEX-040` Partial import reports per-record/per-relation status and safe retry identity.
- `JEX-041` Same import package/operation replay is idempotent under selected conflict policy.
- `JEX-042` Export excludes Vault secrets/private fields outside caller authorization.
- `JEX-043` Multisite import maps site ownership and cannot bind source site IDs blindly.
- `JEX-044` AI/MCP may draft endpoint/import map but cannot publish/write/import outside owner Policy.

### Group 5 — CPT-vs-table guidance
- `JEX-045` Guidance compares CPT vs Custom Table using declared requirements such as WP content semantics, scale, querying, revisions and integrations.
- `JEX-046` Guidance remains recommendation, not automatic migration/storage mutation.
- `JEX-047` Existing CPT with needed WordPress editorial/taxonomy features is not pushed to table solely for row count.
- `JEX-048` High-scale application entity guidance cites query/index/storage constraints rather than generic “tables are faster” claim.
- `JEX-049` Need for public permalink/revisions/comments/taxonomies is considered explicitly.
- `JEX-050` Need for typed high-cardinality relational data/custom indexes is considered explicitly.
- `JEX-051` Migration cost, compatibility and rollback are included in recommendation.
- `JEX-052` Woo/order/product data is excluded from arbitrary storage migration; Woo adapter remains canonical.
- `JEX-053` Multisite/network ownership affects storage recommendation explicitly.
- `JEX-054` Performance conclusions remain hypotheses until measured on representative data.
- `JEX-055` AI/MCP cannot convert CPT↔table from recommendation without approved migration Plan.

### Group 6 — physical relation table
- `JEX-056` High-scale relation may select separate physical relation table only through Relations owner evidence/profile.
- `JEX-057` Relation table identity/schema is canonical Relations storage, not JEX-owned duplicate.
- `JEX-058` Parent/child typed references include provider/site identity and prevent numeric-ID collision.
- `JEX-059` Unique relation constraint enforces configured cardinality without hidden duplicates.
- `JEX-060` Relation ordering/meta columns follow explicit typed schema.
- `JEX-061` Relation insert/delete/update uses Relations API/Policy and never raw JEX SQL.
- `JEX-062` Relation table migration is versioned/recoverable and does not orphan edges silently.
- `JEX-063` Deleted parent/child lifecycle follows Relations owner policy, not automatic cascade assumption.
- `JEX-064` Query indexes support declared relation access patterns and are evidence-gated.
- `JEX-065` Multisite relation table cannot link sites unless explicit cross-site relation contract exists.
- `JEX-066` AI/MCP cannot create physical table or relation edges outside Relations/Custom Table gates.

### Group 7 — relation meta/cardinality
- `JEX-067` Relation meta fields use shared Fields schema with typed validation/sanitization.
- `JEX-068` Min/max parent/child constraints are checked before relation mutation and surfaced in forms/admin UX.
- `JEX-069` One-to-one uniqueness remains atomic under concurrent writes.
- `JEX-070` One-to-many/many-to-many cardinality is explicit and does not infer ownership/authorization.
- `JEX-071` Relation meta update increments relation revision and rejects stale overwrite where required.
- `JEX-072` Private relation meta is excluded from public listing/REST without Policy.
- `JEX-073` Create-related-item workflow composes Forms/CRUD and Relations as separate transitions.
- `JEX-074` Failure creating relation after new item creation yields explicit partial/compensation state rather than false atomic success.
- `JEX-075` Delete relation does not delete related items unless explicit separate governed action exists.
- `JEX-076` Import/export preserves typed relation meta and cardinality constraints.
- `JEX-077` AI-generated relation proposal cannot exceed constraints or bypass Policy.

### Group 8 — relation REST
- `JEX-078` Relation GET endpoint requires source/target resource read Policy and returns only authorized edges/meta.
- `JEX-079` Relation create endpoint validates both endpoint object identities and relation schema.
- `JEX-080` Relation update/delete requires same canonical capability/meta-cap/Policy as admin/Ability path.
- `JEX-081` Client-provided site/user/owner IDs do not grant relation authority.
- `JEX-082` Endpoint enforces row/page/depth limits and prevents unbounded graph traversal.
- `JEX-083` Bulk relation mutation reports per-edge status under declared atomic policy.
- `JEX-084` Idempotency prevents duplicate edge creation on retry.
- `JEX-085` ETag/relation revision prevents stale meta/cardinality overwrite.
- `JEX-086` Error response avoids leaking existence of protected related object.
- `JEX-087` Rate limits isolate principals/sites and prevent graph-enumeration abuse.
- `JEX-088` AI/MCP relation abilities have no hidden broader scope than REST/UI owner Policy.

### Group 9 — Query matrix
- `JEX-089` Query provider matrix enumerates supported posts/terms/users/custom tables/relations/UDS/REST/remote providers with capability metadata.
- `JEX-090` Unsupported provider/query feature returns typed unsupported result, not silent fallback to unrelated query.
- `JEX-091` Query definition pins provider/version/schema and stable key/revision.
- `JEX-092` Filters/sorts/pagination are typed by provider and reject arbitrary SQL/callback code.
- `JEX-093` Query result Policy is applied where source resources are protected.
- `JEX-094` Provider-specific field identifiers are mapped through Data Source schema, not raw request strings.
- `JEX-095` Query explain output identifies provider, filters, sort, cache and Policy effects.
- `JEX-096` Query cache key includes site/provider/definition/revision/audience dimensions.
- `JEX-097` Query definition import remaps providers/fields and leaves unresolved mappings explicit.
- `JEX-098` Provider drift/schema version mismatch blocks unsafe query execution.
- `JEX-099` AI/MCP can draft Query definition but cannot publish unsafe endpoint or bypass provider/Policy restrictions.

### Group 10 — Relations/UDS/REST merged query
- `JEX-100` Relations Query consumes canonical Relations owner and returns typed target refs without duplicating relation logic.
- `JEX-101` UDS Query consumes selected User Data Store and reauthorizes source objects.
- `JEX-102` REST source query uses registered Connection/REST provider and bounded endpoint schema.
- `JEX-103` Merged/sub-query definition declares union/intersection/join semantics explicitly.
- `JEX-104` Pagination limitations of merged providers are surfaced and no false stable-pagination guarantee is made.
- `JEX-105` Sort compatibility across providers uses declared comparable types/normalization or rejects unsupported mix.
- `JEX-106` Duplicate result identity across providers uses typed provider+ID and explicit dedupe policy.
- `JEX-107` Partial provider failure returns explicit degraded/partial result per query profile.
- `JEX-108` Remote timeout/unknown state is not converted to empty result silently.
- `JEX-109` Merged query cache cannot expose private UDS/relation/remote results to another user/site.
- `JEX-110` AI/MCP cannot turn merged query into hidden remote fetch/SSRF/arbitrary SQL path.

### Group 11 — endpoint permission/cache
- `JEX-111` Query endpoint publication requires explicit capability/Policy or explicit public-safe profile.
- `JEX-112` Public endpoint row/field allowlist prevents arbitrary underlying field exposure.
- `JEX-113` Rate/row/depth/time limits are enforced server-side.
- `JEX-114` Endpoint parameter schema rejects undeclared filters/sorts/expansions.
- `JEX-115` Cache varies by site/audience/query params/Policy revision where protected results exist.
- `JEX-116` Cache hit never skips required current authorization for protected response.
- `JEX-117` Definition/provider/schema revision invalidates affected cache.
- `JEX-118` Error/empty response does not leak hidden resource counts/identities.
- `JEX-119` Signed/private endpoint token is scoped/expiring and not equivalent to broad API authorization.
- `JEX-120` Endpoint diagnostics redact remote credentials/private query payloads.
- `JEX-121` AI/MCP cannot publish endpoint or weaken its rate/Policy/cache isolation automatically.

### Group 12 — Dynamic Table/listing/chart
- `JEX-122` Dynamic Table is a Listing renderer over typed Query data, not a new storage/query engine.
- `JEX-123` Columns bind to declared fields/tokens/components with context-safe escaping.
- `JEX-124` Sort/filter/pagination delegates to Query provider when supported and reports client-side fallback limitations explicitly.
- `JEX-125` Responsive table→card fallback preserves same authorized data and semantic labels.
- `JEX-126` Missing/null/denied/redacted values render distinct safe states.
- `JEX-127` Listing source can switch among canonical providers through typed definition without changing storage ownership.
- `JEX-128` Chart adapter consumes typed aggregate data only and does not execute arbitrary chart callbacks/SQL.
- `JEX-129` Chart labels/tooltips do not leak protected hidden fields.
- `JEX-130` Large result set requires pagination/aggregation and cannot render unbounded rows blindly.
- `JEX-131` Builder adapter output uses WPE component/query contract and does not bypass Policy in preview.
- `JEX-132` AI/MCP may draft table/chart/listing but cannot publish a public data surface without normal gates.

### Group 13 — Conditional Visibility/DVR
- `JEX-133` Conditional Visibility composes shared Conditional Logic and is explicitly presentation-only.
- `JEX-134` Hidden component/resource remains protected by canonical Policy on direct access.
- `JEX-135` Conditions may use current user/entity/field/relation/store facts only through registered typed facts.
- `JEX-136` Unknown/denied fact follows explicit false/unknown policy and never reveals protected value in diagnostics.
- `JEX-137` DVR token resolves through registered provider/context and uses typed fallback.
- `JEX-138` DVR token cannot call arbitrary PHP/callback/SQL/shell.
- `JEX-139` Aggregation function delegates to Query/F04 and preserves source/precision/provenance.
- `JEX-140` Context mismatch returns unavailable/typed error rather than reading ambient global state unsafely.
- `JEX-141` Dynamic output escapes by HTML/URL/JSON/attribute context.
- `JEX-142` Cache varies by relevant dynamic audience/facts and avoids private personalization bleed.
- `JEX-143` AI/MCP cannot use visibility/DVR expression to create authorization/provider/mutation bypass.

### Group 14 — Reference Data
- `JEX-144` Reference Data Set supports manual typed key/label/additional columns with stable key/revision.
- `JEX-145` Duplicate key/type mismatch is rejected according schema.
- `JEX-146` CSV/JSON import dry run validates column mapping/types/duplicates before write.
- `JEX-147` Locale-specific labels preserve stable value keys and explicit fallback.
- `JEX-148` Remote Reference Data source delegates to Sync/Connection and never arbitrary fetch from field definition.
- `JEX-149` Remote sync copy is not source truth unless authority contract says destination is authoritative.
- `JEX-150` Reference Data revision change invalidates dependent field/filter/formula/display caches.
- `JEX-151` Private dataset values are Policy-protected and not exposed merely because a field uses them as choices.
- `JEX-152` Import/export preserves typed columns/locale/version and excludes provider secrets.
- `JEX-153` Deleted key follows explicit dependent-value orphan/deprecation semantics, not silent remap by label.
- `JEX-154` AI/MCP can draft dataset/mapping but cannot fetch remote source or publish sensitive dataset outside Policy.

### Group 15 — AI Blueprint
- `JEX-155` Guided AI Blueprint may draft CPTs/taxonomies/fields/tables/relations/queries/listings/filters/forms/profile routes as canonical owner definitions.
- `JEX-156` Generated definition bundle records dependency graph and stable draft identities.
- `JEX-157` Validation rejects duplicate keys, unsupported providers/types and circular/invalid dependencies before approval.
- `JEX-158` Security/Policy validation prevents generated public route/endpoint from exposing protected fields by default.
- `JEX-159` Privacy validation classifies PII/sensitive fields and requires applicable handling before publish.
- `JEX-160` Performance validation flags unindexed/unbounded query/listing/table choices as planning risks.
- `JEX-161` AI output remains Draft; “generate site structure” never authorizes implementation/runtime migration.
- `JEX-162` User may approve definitions individually or as bounded Blueprint Plan with explicit scope.
- `JEX-163` Re-running prompt creates diff/revision rather than silently replacing accepted definitions.
- `JEX-164` AI cannot invent arbitrary PHP/SQL/callbacks to fill unsupported gaps.
- `JEX-165` Prompt injection in source/sample data cannot grant tool permissions or bypass approval.

### Group 16 — Multisite/scale/golden
- `JEX-166` Golden CCT preset scenario yields canonical table/fields/admin/listing definitions with no duplicate engine.
- `JEX-167` Golden relation scenario enforces typed parent/child/cardinality/meta and REST Policy.
- `JEX-168` Golden Query matrix scenario composes posts/custom tables/relations/UDS/REST providers with explicit limitations.
- `JEX-169` Golden Dynamic Table/chart scenario renders typed authorized Query/Aggregate data responsively.
- `JEX-170` Golden Reference Data scenario imports locale labels and remote sync through canonical Sync/Connection.
- `JEX-171` Golden visibility/DVR scenario proves presentation hiding never substitutes for authorization.
- `JEX-172` Golden AI Blueprint scenario drafts full application structure but performs no runtime implementation.
- `JEX-173` Golden Multisite scenario isolates site definitions/data/cache and requires explicit cross-site contract.
- `JEX-174` Golden scale scenario later measures high-cardinality tables/relations/queries/listings with declared indexes/hardware; currently NOT EXECUTED.
- `JEX-175` Golden import/migration scenario remaps canonical owner identities and leaves unresolved dependencies explicit.
- `JEX-176` Golden adversarial AI/MCP scenario cannot execute raw SQL/PHP, publish unsafe endpoint, expose private data or create duplicate canonical engines.

## Execution gate

This document specifies evidence only. **JEX executed remains 0/176.** No table/schema mutation, relation/query/listing execution, endpoint publication, remote sync, AI generation runtime, test or benchmark is authorized by this protocol.