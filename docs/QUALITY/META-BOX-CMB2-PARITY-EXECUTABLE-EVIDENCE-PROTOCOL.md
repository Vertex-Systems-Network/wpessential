# WPEssential — CMB2 / Meta Box / wpmetabox Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `MBX-001…MBX-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Competitor field definitions/formats are migration inputs, not WPE canonical storage or execution semantics.
- Field mapping uses WPE Field Registry/types/storage owners; no arbitrary PHP callbacks/eval are imported as runtime behavior.
- Custom-table/relations/listings/REST integrations delegate to their canonical owners rather than creating a private Meta Box-like engine.
- Discovery/import does not prove semantic equivalence; unsupported behaviors remain unresolved and block strict migration success.
- Frontend submission/profile behavior uses Forms/User Profile/Policy/auth owners; field parity does not create a second form/auth stack.
- Builder dynamic-data adapters expose authorized typed values only; visibility/display is not authorization.
- Multisite import/export preserves site ownership and never assumes matching object IDs across sites.
- AI/MCP may draft mappings but cannot silently publish schema/migrate data or introduce arbitrary code paths.

## Exact fixtures

### Group 1 — CMB2/Meta Box discovery
- `MBX-001` Discover supported CMB2/Meta Box field groups and record provider/version/source location without executing provider callbacks.
- `MBX-002` Detect duplicate provider group IDs and preserve source namespace to avoid collisions.
- `MBX-003` Unknown provider field definition remains discoverable as unsupported/unresolved rather than silently ignored.
- `MBX-004` Discovery reads configuration through safe/static/provider APIs where available and never evals arbitrary source code.
- `MBX-005` Inactive provider definitions found in stored data are labeled legacy/inactive and not assumed executable.
- `MBX-006` Discovery scope respects site/network ownership and cannot enumerate another site’s private definitions by guessed ID.
- `MBX-007` Provider group context/title/priority/order metadata is captured as migration evidence, not canonical WPE UI semantics.
- `MBX-008` Raw PHP callback references are recorded only as unsupported provenance identifiers, never executable import payload.
- `MBX-009` Protected/secret field configuration values are redacted from discovery report.
- `MBX-010` Discovery dry-run performs no schema/data mutation or provider deactivation.
- `MBX-011` AI/MCP can summarize discovered definitions only within principal’s authorized scope.

### Group 2 — field-type mapping
- `MBX-012` Map provider text field to WPE typed text with label/default/validation semantics preserved where equivalent.
- `MBX-013` Map number field with min/max/step/precision explicitly and reject lossy coercion under strict profile.
- `MBX-014` Map select/radio/checkbox choices preserving typed values/labels and detecting duplicate keys.
- `MBX-015` Map date/time/datetime with timezone/storage semantics explicit rather than copying display format as storage truth.
- `MBX-016` Map URL/email/color/media fields through corresponding WPE validators and Asset references.
- `MBX-017` Map wysiwyg/rich-text while preserving sanitization/rendering owner and not executing shortcodes during migration.
- `MBX-018` Provider-specific unsupported type becomes unresolved custom adapter requirement rather than generic text silently.
- `MBX-019` Field default distinction between absent/null/empty/false/zero is preserved.
- `MBX-020` Repeatable scalar semantics are mapped to explicit cardinality/list storage rather than overloaded serialized blob when WPE supports typed list.
- `MBX-021` Unique/key field constraints are revalidated under destination storage owner.
- `MBX-022` Mapping report records exact source type→target type and any semantic loss/blocker.

### Group 3 — posts/terms/users/comments/settings contexts
- `MBX-023` Post field group maps to selected post types only and does not appear on unrelated content types.
- `MBX-024` Term field group maps taxonomy scope correctly and preserves term-level storage semantics.
- `MBX-025` User field group maps through User Profile/Field owner and honors identity/security restrictions.
- `MBX-026` Comment field group maps only when WPE supports declared comment context; otherwise remains unresolved.
- `MBX-027` Settings/options field group maps through Settings Page storage scope and autoload/network rules explicitly.
- `MBX-028` Attachment/media context maps through Media/Asset ownership rather than generic post assumption where semantics differ.
- `MBX-029` Provider group targeting multiple contexts splits/normalizes into compatible WPE definitions when one target model cannot represent all contexts safely.
- `MBX-030` Context-specific capability/Policy is preserved and not replaced by UI visibility condition.
- `MBX-031` Source object numeric IDs are not embedded as portable cross-site identity in migrated definitions.
- `MBX-032` Context migration preview lists affected object counts without exposing protected values.
- `MBX-033` Unsupported context blocks strict full-fidelity migration and is reported explicitly.

### Group 4 — groups/repeaters/clones
- `MBX-034` Map repeatable group into WPE group/repeater schema preserving row order and stable child field keys.
- `MBX-035` Nested repeater depth respects configured schema/runtime limit and rejects pathological unbounded nesting.
- `MBX-036` Cloneable fields map repeated values without conflating cloned schema with cloned data identity.
- `MBX-037` Empty repeater vs missing repeater vs one empty row remain distinguishable where source semantics require it.
- `MBX-038` Group row IDs are generated/stable according to WPE owner and not copied blindly from provider transient indices.
- `MBX-039` Conditional child fields preserve row-local context without leaking values between rows.
- `MBX-040` Repeater media/entity references use typed IDs and remap explicitly across import/site migration.
- `MBX-041` Large repeater values use bounded serialization/query strategy and do not create uncontrolled autoload payload.
- `MBX-042` Unsupported provider group callback/template remains unresolved instead of imported as arbitrary PHP.
- `MBX-043` Partial row conversion reports row/field errors and does not claim complete group migration.
- `MBX-044` Export/import round-trips mapped group schema and sample data semantics without flattening hierarchy silently.

### Group 5 — conditional/include-exclude
- `MBX-045` Map simple field-value condition into shared Conditional Logic Engine typed rule.
- `MBX-046` Map post-type/taxonomy/template include-exclude conditions to explicit context facts.
- `MBX-047` Role/user visibility condition remains presentation condition and does not become authorization.
- `MBX-048` Unsupported arbitrary PHP callback condition is rejected/unresolved, never evaluated from imported definition.
- `MBX-049` Nested AND/OR condition precedence is preserved deterministically.
- `MBX-050` Unknown fact/provider in condition yields typed unknown/degraded behavior according to policy, not hidden true.
- `MBX-051` Server-side validation still enforces field rules even when client-side condition hides the field.
- `MBX-052` Conditional value source that viewer cannot read cannot be leaked through UI condition explanation.
- `MBX-053` Imported include/exclude target IDs are remapped explicitly and unresolved IDs do not match accidentally.
- `MBX-054` Condition loop/dependency cycle is detected before publish.
- `MBX-055` AI/MCP can translate condition expressions but cannot introduce raw code/eval operators.

### Group 6 — custom table storage
- `MBX-056` Map provider custom-table field group only after target Custom Table schema/ownership is defined and compatible.
- `MBX-057` Column types/null/default/index semantics are compared before data migration.
- `MBX-058` Unknown provider table is not queried/written by arbitrary name without registered ownership/schema.
- `MBX-059` Parameterized typed migration prevents SQL injection through table/column/value config.
- `MBX-060` Primary/unique key mapping is explicit and does not reuse source auto-increment identity blindly across environments.
- `MBX-061` Serialized provider cell can be decoded only through bounded known codec; object instantiation/eval is forbidden.
- `MBX-062` Target schema migration/diff is previewed before DDL and remains planning-only until authorized runtime.
- `MBX-063` Row-level failure is item-scoped and target partial-state semantics are explicit.
- `MBX-064` Cross-site table prefix/topology is resolved server-side and cannot be selected by request-controlled raw table name.
- `MBX-065` Custom-table storage does not bypass Field/Policy/privacy classification.
- `MBX-066` Verification compares row counts/key samples/checksums under declared profile and not transport status alone.

### Group 7 — relations
- `MBX-067` Map Meta Box relation definition into canonical Relation owner with explicit parent/child types/cardinality.
- `MBX-068` Relation table/edge IDs are remapped into WPE relation identity rather than reused as canonical truth.
- `MBX-069` Relation meta/pivot fields map through typed relation-meta schema.
- `MBX-070` Minimum/maximum cardinality constraints are preserved and validated.
- `MBX-071` Self-relation directionality is explicit and not inferred from label order.
- `MBX-072` Missing related object yields unresolved edge rather than silent null/incorrect ID.
- `MBX-073` Cross-site relation migration requires explicit source→target object mapping.
- `MBX-074` Relation visibility/query does not grant access to protected related entities.
- `MBX-075` Provider relation callback/business rule not representable declaratively remains blocker/unresolved.
- `MBX-076` Bulk edge migration is idempotent and does not duplicate relations on retry.
- `MBX-077` Post-migration verification checks cardinality/count/sample endpoints through canonical Relation API.

### Group 8 — frontend submission/profile
- `MBX-078` Map supported frontend form fields into Forms/User Profile composition rather than private field-runtime submission.
- `MBX-079` Authentication/account creation remains WordPress/User Profile owner and not inferred from field group existence.
- `MBX-080` Nonce/CSRF validation uses canonical form/auth mechanisms and provider legacy token is not copied as reusable runtime secret.
- `MBX-081` Field-level edit/read Policy is preserved for frontend profile routes.
- `MBX-082` File/media upload delegates to Asset/Media owner with type/size/ownership checks.
- `MBX-083` Password/reset/security fields are excluded from generic field migration unless User Profile auth owner defines explicit secure mapping.
- `MBX-084` Validation errors preserve field-level mapping and do not expose hidden/protected submitted values.
- `MBX-085` Anonymous submission is allowed only when target Form/Policy explicitly permits it.
- `MBX-086` Approval workflow for sensitive profile changes composes Workflow/User Profile owner rather than direct meta write.
- `MBX-087` Redirect/success message remains presentation result and not proof of durable source object mutation unless commit succeeded.
- `MBX-088` AI/MCP cannot submit/edit protected profile data merely because it can draft mapped forms.

### Group 9 — block bindings/blocks
- `MBX-089` Map supported field-to-block binding into WordPress/WPE dynamic value adapter with typed field reference.
- `MBX-090` Block binding read reauthorizes protected field access at render time.
- `MBX-091` Missing field renders declared fallback/empty state and not stale cached protected value.
- `MBX-092` Imported block metadata cannot contain executable PHP/callback payload.
- `MBX-093` Custom provider block unsupported by destination remains unresolved/legacy content, not falsely “migrated”.
- `MBX-094` Block attributes containing source object IDs are remapped explicitly across site migration.
- `MBX-095` Reusable/global block/template references remain owned by their canonical block/theme owners.
- `MBX-096` Editor preview and frontend render use same typed field contract while allowing context-specific authorization.
- `MBX-097` Cache key includes source entity/field revision and permission-safe context where needed.
- `MBX-098` Removing provider after migration occurs only after block-binding compatibility verification.
- `MBX-099` Block migration report identifies lossy/unresolved bindings per content item.

### Group 10 — REST/Abilities
- `MBX-100` Expose mapped fields through REST only when field/endpoint schema explicitly allows read/write.
- `MBX-101` REST schema reflects target WPE types/cardinality/nullability and not stale provider metadata.
- `MBX-102` Protected field read/write requires same Policy as admin/frontend paths.
- `MBX-103` Ability field get/set uses typed canonical field reference and cannot accept raw table/meta keys to bypass registry.
- `MBX-104` Bulk REST update reports per-field/object validation errors and no false all-success.
- `MBX-105` Unknown source/provider field ID is rejected after migration rather than dynamically resolving arbitrary legacy callbacks.
- `MBX-106` API output redacts secrets/private values and does not leak through error detail.
- `MBX-107` Rate limits apply to expensive relation/repeater/custom-table reads.
- `MBX-108` REST/Ability versioning/deprecation preserves migration compatibility explicitly.
- `MBX-109` AI/MCP uses same Ability/Policy route and cannot directly query storage tables/options.
- `MBX-110` Endpoint publication does not automatically make field data public; auth/permission callbacks remain mandatory.

### Group 11 — builder dynamic-data adapters
- `MBX-111` Map field tokens into certified builder dynamic-data registry using canonical field references.
- `MBX-112` Builder adapter cannot expose protected field value to public render when Policy denies it.
- `MBX-113` Provider-specific formatter unsupported by WPE remains unresolved instead of importing arbitrary callback.
- `MBX-114` Repeater/group display uses typed collection traversal with depth/row limits.
- `MBX-115` Media field maps to Asset reference/URL only according to media privacy/delivery policy.
- `MBX-116` Relation field dynamic output uses Relation/Query owner and not ad-hoc SQL.
- `MBX-117` Builder cache invalidates when mapped field/schema/value revision changes as required.
- `MBX-118` Missing builder plugin leaves mapping dormant/degraded without fatal site render.
- `MBX-119` Same field can feed multiple builders through adapters without duplicating storage truth.
- `MBX-120` Builder adapter version/capability is part of compatibility evidence.
- `MBX-121` AI/MCP may draft token mapping but cannot publish builder templates/field exposure without normal approval.

### Group 12 — admin-columns integration
- `MBX-122` Map selected field to Admin Columns owner with typed formatter/sort/filter capabilities where supported.
- `MBX-123` Column display reads through Field/Data Source and enforces protected-value Policy.
- `MBX-124` Sort/filter uses canonical query/storage adapter rather than arbitrary provider SQL.
- `MBX-125` Unsupported field type is display-only/unavailable with diagnostic instead of false sortability.
- `MBX-126` Repeater/group field column uses bounded summary and not full unescaped payload.
- `MBX-127` Media field column uses safe thumbnail/asset ref and no private URL leakage.
- `MBX-128` Inline/bulk edit is enabled only when Admin Columns + Field owner supports validated write.
- `MBX-129` Column cache keys include site/schema/value permission context appropriately.
- `MBX-130` Provider removal does not delete canonical migrated field/column definitions.
- `MBX-131` Import collision with existing WPE column offers map/skip/replace with diff.
- `MBX-132` Column visibility remains presentation and not resource authorization.

### Group 13 — ACF/Pods/Toolset/CMB2/Meta Box migration
- `MBX-133` Migration inventory identifies provider/version/field groups/types/storage before mapping.
- `MBX-134` Each source provider maps through its adapter into WPE canonical schema; provider formats never become canonical runtime storage by default.
- `MBX-135` Duplicate semantic fields across providers are detected and require merge/map/keep-separate decision.
- `MBX-136` Source key/name collisions preserve provider namespace and do not overwrite silently.
- `MBX-137` Unsupported callback/custom field type is explicit blocker/unresolved extension requirement.
- `MBX-138` Dry-run reports definitions, data counts, relations, templates/builders and lossy mappings without writes.
- `MBX-139` Data migration is idempotent using source provider/object/key identity.
- `MBX-140` Interrupted migration resumes/reconciles without duplicate field rows/relations.
- `MBX-141` Legacy provider is not disabled/deleted automatically after import; verification/coexistence gate is required.
- `MBX-142` Rollback semantics distinguish WPE-created definitions/data from source provider originals.
- `MBX-143` Migration success requires verification of mapped schema/data/representative renders, not import command success alone.

### Group 14 — Multisite/import/export
- `MBX-144` Site-owned field definitions/data remain isolated by site scope.
- `MBX-145` Network template can instantiate field schemas on selected sites without sharing site data automatically.
- `MBX-146` Same provider field key on two sites maps to separate WPE definitions unless network template explicitly links them.
- `MBX-147` Cross-site import remaps post/term/user/media references explicitly.
- `MBX-148` Network admin access to templates does not automatically grant protected site field-data visibility.
- `MBX-149` Site clone/import does not copy OAuth/provider secrets from field config.
- `MBX-150` Export package carries schema/conditions/mappings and permitted data separately, with privacy classification.
- `MBX-151` Import destination capability/schema check occurs before any writes.
- `MBX-152` Site deletion follows field/data retention owner and does not delete network-shared templates.
- `MBX-153` Network-wide migration requires explicit target-site set and per-site result state.
- `MBX-154` AI/MCP cross-site mapping requires network/site authority and cannot infer access from same user account.

### Group 15 — schema/performance
- `MBX-155` 100K-object field query uses declared indexes/storage route and bounded pagination.
- `MBX-156` Large repeater/group dataset avoids unbounded unserialize/load-all patterns.
- `MBX-157` Custom-table migration batches rows and respects DB lock/time budgets.
- `MBX-158` Relation migration uses bounded edge batches and avoids N+1 object lookups beyond declared profile.
- `MBX-159` Builder/admin-column reads use batched Data Source paths where possible.
- `MBX-160` Schema cache invalidates on definition revision without cross-site bleed.
- `MBX-161` Concurrent schema/data migration detects version conflict and cannot write under stale definition silently.
- `MBX-162` Import failure/retry storm is bounded by Job Service backpressure/rate policy.
- `MBX-163` Performance evidence records provider version/source size/DB schema/environment for reproducibility.
- `MBX-164` Unsupported pathological nested schema fails bounded instead of exhausting memory/CPU.
- `MBX-165` Static estimates are not runtime performance certification.

### Group 16 — end-to-end migration/interoperability regression
- `MBX-166` Golden: migrate post text/select/media fields from Meta Box into WPE and verify representative values/rendering.
- `MBX-167` Golden: migrate term/user/settings contexts with correct scope/storage and no cross-context leakage.
- `MBX-168` Golden: nested repeater/group preserves rows/child keys/order and reports unsupported custom callback.
- `MBX-169` Golden: custom-table fields+relations migrate with explicit key remap and cardinality verification.
- `MBX-170` Golden: frontend profile/form composition uses native auth/Policy and does not create second password/session stack.
- `MBX-171` Golden: builder dynamic-data + Admin Columns adapters reference canonical fields and preserve authorization.
- `MBX-172` Golden: ACF/Pods/CMB2/Meta Box mixed inventory surfaces duplicate/conflicting semantics for explicit resolution.
- `MBX-173` Golden: migration interrupted/retried remains idempotent and source provider data stays unchanged.
- `MBX-174` Golden: Multisite same field keys remain isolated and cross-site import remaps references explicitly.
- `MBX-175` Golden: unsupported source semantics block strict “complete” status and remain auditable unresolved items.
- `MBX-176` Golden: AI/MCP adversarial request to import arbitrary PHP callback/raw SQL or expose protected fields is rejected/draft-only.

## Runtime truth

This protocol is documentation-only. `MBX-001…MBX-176` are **176/176 documented, 0/176 executed**. No provider discovery execution, schema/data migration, table/field/relation write, frontend submission, builder/REST runtime or AI/MCP call occurred. Development authorization remains **NOT GRANTED / 0/56**.