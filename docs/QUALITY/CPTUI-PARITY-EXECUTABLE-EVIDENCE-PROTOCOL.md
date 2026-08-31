# WPEssential — CPTUI Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `CPTX-001…CPTX-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- CPTUI parity is a migration/UX layer over WPE canonical CPT/Taxonomy definitions; competitor registration formats never become runtime truth.
- WordPress registration arguments, rewrite rules, capabilities and REST exposure remain versioned declarative definitions and require compatibility evidence.
- Import/discovery does not authorize overwriting third-party registered object types or changing existing content semantics silently.
- Listing/filter/Admin Columns/block/shortcode features compose canonical owners and never create duplicate query/presentation engines.
- Capability labels/maps do not replace WordPress meta-cap/Policy authority.
- Multisite definitions/templates are scope-aware; same post-type/taxonomy slug across sites does not imply shared ownership.
- Rewrite flush/performance is operational behavior and cannot be triggered repeatedly or claimed safe from static planning alone.
- AI/MCP may draft/import/explain definitions only; no live registration/rewrite flush/network push without explicit development/runtime approval.

## Exact fixtures

### Group 1 — CPTUI discovery/import
- `CPTX-001` Discover supported CPTUI post-type/taxonomy definitions and record provider/version/source provenance without executing provider callbacks.
- `CPTX-002` Duplicate source slug from multiple providers is preserved as conflict with provider namespace, not silently merged.
- `CPTX-003` Inactive legacy CPTUI definitions remain discoverable as inactive/legacy and not assumed currently registered.
- `CPTX-004` Unknown source field/argument is preserved as unsupported/unresolved in import report.
- `CPTX-005` Discovery performs no registration/rewrite/data mutation.
- `CPTX-006` Import preview maps source definition to canonical WPE CPT/Taxonomy schema before apply.
- `CPTX-007` Raw PHP callback/code references are rejected as executable import behavior and retained only as unsupported provenance where useful.
- `CPTX-008` Protected/private config values are redacted from discovery/export reports.
- `CPTX-009` Site scope prevents discovery/import from reading another site’s definitions by guessed ID.
- `CPTX-010` Provider is not disabled/deleted automatically after successful mapping; coexistence verification is required first.
- `CPTX-011` AI/MCP may summarize/import-map discovered definitions but cannot register/apply them under planner-only mode.

### Group 2 — CPT labels/arguments
- `CPTX-012` Map post-type slug, singular/plural labels and full label set into canonical WPE definition deterministically.
- `CPTX-013` Reject invalid/reserved/too-long post-type slug according to supported WordPress profile.
- `CPTX-014` Public/show_ui/show_in_menu/show_in_nav_menus/exclude_from_search flags remain distinct and do not imply authorization.
- `CPTX-015` Hierarchical/supports/menu_position/menu_icon settings preserve typed semantics.
- `CPTX-016` `has_archive` boolean/string semantics are preserved explicitly.
- `CPTX-017` Query/rewrite/publicly_queryable combinations that create inconsistent routing receive diagnostics rather than silent normalization.
- `CPTX-018` `supports` list rejects unknown items or records provider extension requirement rather than passing arbitrary values blindly.
- `CPTX-019` Capability type/map_meta_cap settings are normalized but actual permission decisions remain WordPress/Policy authority.
- `CPTX-020` REST visibility/controller fields validate compatibility and cannot name arbitrary executable callback classes from untrusted import.
- `CPTX-021` Export/import round-trips supported post-type arguments without losing explicit false/null distinctions.
- `CPTX-022` Definition revision is pinned so later argument changes remain diffable and reversible at planning layer.

### Group 3 — taxonomy labels/arguments
- `CPTX-023` Map taxonomy slug, labels and assigned object types into canonical Taxonomy definition.
- `CPTX-024` Reject invalid/reserved/too-long taxonomy slug according to supported WordPress profile.
- `CPTX-025` Hierarchical/public/show_ui/show_admin_column/show_in_nav_menus/query_var flags remain typed independently.
- `CPTX-026` Rewrite slug/hierarchical/front settings are preserved and validated.
- `CPTX-027` REST visibility/controller configuration uses supported declarative adapter only.
- `CPTX-028` Taxonomy capability map is normalized without treating label/role as authorization truth.
- `CPTX-029` Object-type assignment to unknown/unavailable CPT remains unresolved rather than silently dropped.
- `CPTX-030` Shared taxonomy numeric IDs are never assumed portable across sites/environments.
- `CPTX-031` Default term configuration is typed and does not create runtime term during planning/import preview.
- `CPTX-032` Unsupported provider taxonomy callback remains explicit blocker/unresolved.
- `CPTX-033` Taxonomy import/export preserves source provenance and schema revision.

### Group 4 — rewrite/query/capability changes
- `CPTX-034` Changing CPT rewrite slug produces explicit route-impact plan and identifies potential conflicts before runtime apply.
- `CPTX-035` Taxonomy rewrite change similarly reports affected routes/permalinks.
- `CPTX-036` Rewrite flush is modeled as bounded post-change operation and never scheduled on every request.
- `CPTX-037` Query-var change distinguishes public query behavior from admin/query builder behavior.
- `CPTX-038` Publicly-queryable false prevents frontend route assumptions even if UI menu remains visible.
- `CPTX-039` Capability type change reports affected capability names/meta-cap mapping before apply.
- `CPTX-040` Existing role assignments are not silently rewritten merely because capability type changes.
- `CPTX-041` Reserved route collision with page/CPT/taxonomy/provider is detected and requires resolution.
- `CPTX-042` Rewrite simulation is labeled simulation and not proof final server/runtime routing will succeed.
- `CPTX-043` Unsupported server/permalink profile remains runtime evidence pending.
- `CPTX-044` AI/MCP cannot flush rewrites/change capabilities live from a draft definition.

### Group 5 — ownership/provenance/diff
- `CPTX-045` Definition records owner source as WPE/imported/third-party/core with stable provenance.
- `CPTX-046` Third-party registered CPT discovered at runtime/static source is not automatically claimed as WPE-owned.
- `CPTX-047` Import creates a WPE-managed copy only after explicit takeover/map semantics.
- `CPTX-048` Diff shows source-provider vs target WPE arguments/labels without conflating effective runtime registration with stored config.
- `CPTX-049` Provider update/drift is detectable via source fingerprint/version when available.
- `CPTX-050` WPE definition change does not mutate original CPTUI/plugin config automatically.
- `CPTX-051` Deleting WPE definition does not delete third-party source definition/content data by default.
- `CPTX-052` Orphaned content after provider removal is identified but not deleted automatically.
- `CPTX-053` Ownership handoff includes coexistence/duplicate-registration diagnostics.
- `CPTX-054` Historical revisions preserve prior args/labels/source provenance.
- `CPTX-055` Audit/explain output identifies which definition/provider is intended owner without claiming runtime precedence until verified.

### Group 6 — Dynamic Listing display presets
- `CPTX-056` Create Dynamic Listing preset for selected CPT using canonical Query/Listing owner, not a private CPTUI renderer.
- `CPTX-057` Preset default fields/title/excerpt/image use typed Data Source tokens.
- `CPTX-058` Preset respects source Query/Policy and cannot expose protected posts merely because CPT is public.
- `CPTX-059` Missing field/media displays configured fallback and not stale cached value.
- `CPTX-060` Pagination/sort/filter options delegate to Query/Listing owner.
- `CPTX-061` Builder adapter preset remains optional and does not require a specific builder for canonical listing definition.
- `CPTX-062` Preset import/export references CPT stable key/slug and remaps explicitly across environments.
- `CPTX-063` Deleted/renamed CPT places preset in unresolved state rather than pointing to similarly named type automatically.
- `CPTX-064` Listing cache keys include CPT/query/revision/policy-safe context.
- `CPTX-065` UI preset availability is presentation convenience and not content authorization.
- `CPTX-066` AI/MCP can draft listing preset but cannot publish frontend templates without normal approval.

### Group 7 — taxonomy filters
- `CPTX-067` Create taxonomy filter for a CPT listing through canonical Query/Filter owner.
- `CPTX-068` Filter only exposes terms authorized/allowed by source taxonomy/query profile.
- `CPTX-069` Multi-select AND/OR semantics are explicit and deterministic.
- `CPTX-070` Empty/no-match term state is handled without changing underlying query authorization.
- `CPTX-071` Hierarchical filter preserves parent/child relation and does not infer tree from labels alone.
- `CPTX-072` Term ID/slug reference is scoped to taxonomy/site and cannot collide across taxonomies/sites.
- `CPTX-073` Filter URL/query parameter is allowlisted/sanitized and cannot become arbitrary query injection.
- `CPTX-074` Large taxonomy uses bounded search/pagination and avoids loading all terms blindly.
- `CPTX-075` Translation adapter maps term/language semantics explicitly where supported.
- `CPTX-076` Cache invalidates on taxonomy/term changes and remains site scoped.
- `CPTX-077` Removing filter preset does not alter taxonomy terms or CPT registration.

### Group 8 — Admin Columns integration
- `CPTX-078` Create default Admin Columns preset for selected CPT using canonical Admin Columns owner.
- `CPTX-079` Column definitions reference registered fields/taxonomies/data sources rather than raw arbitrary meta keys when typed registry exists.
- `CPTX-080` Protected values are server-authorized/redacted in columns.
- `CPTX-081` Sortable/filterable flags enabled only when canonical query/storage adapter supports them.
- `CPTX-082` Inline/bulk edit requires separate write capability and field-owner validation.
- `CPTX-083` Taxonomy column uses target taxonomy capability/relationship data correctly.
- `CPTX-084` Featured image/media column respects private media delivery.
- `CPTX-085` Column preset does not alter CPT registration arguments.
- `CPTX-086` Import collision with existing Admin Columns definition requires explicit mapping/replace/skip.
- `CPTX-087` CPT rename/delete puts column preset into unresolved/repair state instead of silently reassigning.
- `CPTX-088` AI/MCP may draft columns but cannot expose hidden data or enable privileged inline edits without Policy.

### Group 9 — block/shortcode adapters
- `CPTX-089` Generate/define typed block/query/listing adapter for selected CPT without embedding arbitrary PHP callbacks.
- `CPTX-090` Block attributes use canonical CPT/taxonomy/query references and validate allowed values.
- `CPTX-091` Server-rendered block permission checks source data at render time where protected data may appear.
- `CPTX-092` Shortcode compatibility adapter is bounded to registered shortcode handler definition and sanitized attributes.
- `CPTX-093` Imported source shortcode PHP cannot be evaluated from CPTUI parity configuration.
- `CPTX-094` Block editor inserter visibility does not grant ability to view/edit protected CPT content.
- `CPTX-095` Missing CPT produces graceful block/shortcode diagnostic/fallback, not fatal error.
- `CPTX-096` Block schema/version change follows deprecation/migration rules explicitly.
- `CPTX-097` Full-page/block cache remains permission-safe and invalidates on relevant CPT definition/content changes.
- `CPTX-098` Builder-specific block/shortcode adapter delegates to certified integration and avoids duplicate rendering engines.
- `CPTX-099` AI/MCP generated block/shortcode config remains draft and no server code execution path is created.

### Group 10 — developer/JSON compiler
- `CPTX-100` Export canonical CPT/taxonomy definition as versioned declarative JSON with explicit schema.
- `CPTX-101` JSON parser rejects malformed data and unknown required schema version before apply.
- `CPTX-102` JSON cannot contain executable PHP/JS/callback bodies as runtime behavior.
- `CPTX-103` Optional code-generation output is source scaffold for reviewed VCS workflow, not database-evaluated PHP.
- `CPTX-104` Generated source escapes labels/slugs/arguments safely and uses supported WordPress APIs.
- `CPTX-105` Deterministic generation yields stable output for identical normalized definition/compiler version.
- `CPTX-106` Unsupported argument is preserved/reported and not silently omitted in strict mode.
- `CPTX-107` Import/export excludes secrets and environment-specific private IDs unless portable mapping exists.
- `CPTX-108` Generated source includes provenance/version headers without claiming runtime compatibility before tests.
- `CPTX-109` Extension SDK route is used for custom logic beyond declarative schema; no eval escape hatch.
- `CPTX-110` AI/MCP may generate JSON/source proposal but cannot install/activate it pre-consent.

### Group 11 — REST/Ability exposure
- `CPTX-111` REST visibility for CPT/taxonomy is explicit and uses WordPress-supported registration args/profile.
- `CPTX-112` REST controller/base/namespace values are validated and cannot instantiate arbitrary untrusted class from imported config.
- `CPTX-113` REST read/write permissions remain source CPT/taxonomy capabilities/Policy, not show_in_rest boolean alone.
- `CPTX-114` Ability list/get/create/update/delete definition actions enforce dedicated management capability.
- `CPTX-115` Ability cannot register arbitrary raw PHP callback or write directly to foreign site definition by supplied site ID.
- `CPTX-116` REST schema for custom fields delegates to Field owner and respects field-level privacy.
- `CPTX-117` Rate limits apply to expensive enumeration/migration endpoints as configured.
- `CPTX-118` Deprecated REST/Ability version fails/migrates explicitly.
- `CPTX-119` Error responses do not leak protected definition/source paths/secrets.
- `CPTX-120` AI/MCP uses same Ability/Policy route and cannot bypass approval to publish registration.
- `CPTX-121` REST exposure success does not imply frontend public access; route/object permission remains authoritative.

### Group 12 — Multisite network template/push
- `CPTX-122` Site-owned CPT/taxonomy definition is isolated to its site by default.
- `CPTX-123` Network template can instantiate definition on selected sites with explicit target set.
- `CPTX-124` Same slug on two sites can have different definitions without cross-site overwrite unless network enforced profile says otherwise.
- `CPTX-125` Network push uses per-site diff/conflict result and never assumes all sites are identical.
- `CPTX-126` Site admin cannot modify network-enforced definition fields outside delegated override set.
- `CPTX-127` Network admin template visibility does not grant protected content access on target sites.
- `CPTX-128` Site clone copies definitions according to clone policy but remaps site-specific routes/provider references.
- `CPTX-129` Site deletion removes site-owned definition metadata without deleting network template.
- `CPTX-130` Network push failure on one site does not falsely mark all sites successful.
- `CPTX-131` Rewrite flush/run is per target site/runtime and remains unexecuted until authorized.
- `CPTX-132` AI/MCP site context cannot network-push definitions by setting a network flag.

### Group 13 — import/export/versioning
- `CPTX-133` Export CPT/taxonomy definitions with schema/version/provenance and related optional presets separately.
- `CPTX-134` Import validates slug/reserved words/version/compatibility before applying definition.
- `CPTX-135` Collision offers create/rename/map/merge/replace/skip with semantic diff.
- `CPTX-136` Destructive replace does not delete content unless separately selected/authorized.
- `CPTX-137` Imported definition remains Draft where policy requires review before registration.
- `CPTX-138` Source provider unknown argument remains unresolved rather than silently dropped.
- `CPTX-139` Version migration records old→new schema transformation and preserves original package evidence.
- `CPTX-140` Import is idempotent by package/definition identity and retries do not create duplicate definitions.
- `CPTX-141` Cross-site import remaps related taxonomy/listing/column refs explicitly.
- `CPTX-142` Export/import never carries raw executable callbacks/secrets.
- `CPTX-143` Post-import verification compares normalized definition and dependency readiness before provider retirement.

### Group 14 — third-party registration coexistence
- `CPTX-144` Detect runtime/known third-party registration of same CPT slug and report owner/conflict before WPE takeover.
- `CPTX-145` Observe-only mode documents effective registration without attempting duplicate register call.
- `CPTX-146` Explicit takeover profile requires source/provider disable plan/coexistence evidence and never auto-disables third party.
- `CPTX-147` Duplicate taxonomy registration conflict is handled similarly with source provenance.
- `CPTX-148` Third-party adds extra supports/taxonomies/capabilities and diff shows effective mismatch without overwriting source config.
- `CPTX-149` Registration priority/order ambiguity is reported as runtime evidence pending rather than static certainty.
- `CPTX-150` Provider removal leaving content under unregistered type is diagnosed; content is not deleted.
- `CPTX-151` WPE disable restores no third-party settings because it never owned them.
- `CPTX-152` Import from plugin source does not preserve executable callbacks in WPE definition.
- `CPTX-153` Coexistence with Woo/core reserved types is blocked unless certified adapter explicitly owns extension behavior.
- `CPTX-154` AI/MCP cannot claim ownership/takeover merely from matching slug.

### Group 15 — scale/rewrite performance
- `CPTX-155` Large registry of hundreds of CPT/taxonomy definitions compiles/loads within declared memory/time budget.
- `CPTX-156` Definition lookup/cache is keyed by site/revision and avoids reparsing full catalog unnecessarily.
- `CPTX-157` Rewrite flush is triggered only on relevant definition changes and coalesced/bounded.
- `CPTX-158` Bulk network push uses Job Service/backpressure and per-site result state.
- `CPTX-159` Large taxonomy/object-type assignment lists are validated in bounded batches.
- `CPTX-160` Admin listing/search of definitions is paginated and avoids N+1 provider/dependency lookups.
- `CPTX-161` Cache invalidation on definition revision does not bleed across sites.
- `CPTX-162` Concurrent edits detect stale revision and avoid lost update.
- `CPTX-163` Rewrite collision analysis records route-set size/environment and does not claim production routing correctness statically.
- `CPTX-164` Performance evidence records WordPress/PHP/site count/definition count/rewrite profile.
- `CPTX-165` Static estimates do not certify runtime registration/rewrite performance.

### Group 16 — end-to-end registration/display regression
- `CPTX-166` Golden: import a simple CPTUI CPT+taxonomy definition into WPE and verify normalized labels/args/relationship without source mutation.
- `CPTX-167` Golden: invalid/reserved slug blocks import/publish with clear diagnostics and no partial definition.
- `CPTX-168` Golden: rewrite/capability change produces impact/diff plan and does not live-flush/register during planning.
- `CPTX-169` Golden: Dynamic Listing/taxonomy filter/Admin Columns presets compose canonical owners and respect protected content Policy.
- `CPTX-170` Golden: REST/Ability exposure remains governed by CPT/taxonomy/field permissions despite show_in_rest enabled.
- `CPTX-171` Golden: Multisite network template pushes to selected sites with per-site conflict state and isolated same-slug site definitions.
- `CPTX-172` Golden: third-party same-slug registration remains coexistence conflict until explicit takeover; no duplicate ownership claim.
- `CPTX-173` Golden: unsupported CPTUI/provider callback stays unresolved and strict migration does not claim full fidelity.
- `CPTX-174` Golden: export/import/version migration preserves declarative semantics and excludes executable callbacks/secrets.
- `CPTX-175` Golden: large definition registry/rewrite analysis remains bounded and no repeated global flush behavior is introduced.
- `CPTX-176` Golden: AI/MCP adversarial request to register reserved type, inject callback/PHP, bypass capabilities or network-push without authority is denied/draft-only.

## Runtime truth

This protocol is documentation-only. `CPTX-001…CPTX-176` are **176/176 documented, 0/176 executed**. No CPT/taxonomy discovery runtime, definition registration, rewrite flush, content/query mutation, REST/Ability execution, network push, test or AI/MCP call occurred. Development authorization remains **NOT GRANTED / 0/56**.