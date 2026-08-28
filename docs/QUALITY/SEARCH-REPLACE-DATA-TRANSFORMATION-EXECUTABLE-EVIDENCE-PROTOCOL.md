# WPEssential — Search, Replace & Data Transformation Executable Evidence Protocol

Status: **Exact planning evidence / NOT EXECUTED / no development authorization**  
Date: 2026-08-29  
Work package: **WP113**  
Namespace: **SRT-001…SRT-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## Purpose

Freeze the exact evidence fixtures for Surface 45 Search, Replace & Data Transformation. This protocol preserves the 16 groups fixed by the market-expansion master plan and the behavior contract in `MODULES/SEARCH-REPLACE-DATA-TRANSFORMATION-EXHAUSTIVE-SPEC.md`.

## Truth boundaries

- Dry Run ≠ mutation.
- Successful mutation ≠ successful verification or rollback.
- Generic Search/Replace does not own passwords, secrets, external provider facts, commerce truth or another module’s protected storage.
- Table/column identifiers must come from validated schema/registry; parameterized values do not make arbitrary identifiers safe.
- Serialized PHP objects must never be instantiated by generic transformation.
- Concurrent newer data must not be silently overwritten.
- Rollback is claimed only for the exact certified reversal class actually available.
- AI/MCP/CLI cannot bypass Policy, re-authentication or reviewed-plan requirements.

---

## Group 1 — literal, case and URL-aware search — SRT-001…011

- **SRT-001** — Exact literal search finds byte/logical matches in a declared text column and leaves null/nonmatching rows untouched.
- **SRT-002** — Case-sensitive search distinguishes `Foo` from `foo`; case-insensitive mode follows the declared charset/collation/case-fold profile rather than PHP locale accident.
- **SRT-003** — Whole-value mode matches only values equal to the search term while substring mode records exact occurrence count and offsets where supported.
- **SRT-004** — Null, empty string and missing field remain distinct search states and cannot be collapsed by generic normalization.
- **SRT-005** — URL-aware host replacement changes only the parsed host component and does not replace matching text inside path/query/content accidentally.
- **SRT-006** — Scheme migration `http→https` preserves host/path/query according to URL transform profile and rejects malformed URL values separately.
- **SRT-007** — Domain-aware search distinguishes `example.com` from `notexample.com` and handles subdomain inclusion only when explicitly enabled.
- **SRT-008** — Path-aware search normalizes configured slash/encoding semantics without interpreting URL path as filesystem traversal.
- **SRT-009** — Unicode normalization profile demonstrates composed/decomposed equivalent search behavior while preserving original value when no mutation runs.
- **SRT-010** — Maximum match count/length budget stops pathological values with typed skipped/error state instead of unbounded scanning.
- **SRT-011** — Binary/blob and encrypted/Vault-backed columns are excluded by default; generic search cannot infer permission from readable storage metadata.

## Group 2 — regex and bounded pattern safety — SRT-012…022

- **SRT-012** — Valid bounded regex search returns expected captures without mutation and records regex revision/options in the Plan fingerprint.
- **SRT-013** — Invalid regex syntax fails validation before a Dry Run job can start.
- **SRT-014** — Catastrophic/backtracking-prone pattern exceeds complexity/runtime budget and is rejected/quarantined rather than consuming unbounded CPU.
- **SRT-015** — Regex capture replacement validates referenced groups; missing group references fail before mutation.
- **SRT-016** — Regex replacement escaping distinguishes literal `$`, backslash and capture syntax deterministically across supported runtime profile.
- **SRT-017** — Multiline/dotall/unicode flags are explicit Plan properties; defaults cannot vary silently between environments.
- **SRT-018** — Zero-length regex matches use bounded iteration rules and cannot create infinite replacement loops.
- **SRT-019** — Regex output expansion ratio over configured maximum is rejected before writing oversized values.
- **SRT-020** — Regex against very large field respects per-value time/size budget and records skipped/error status rather than partial field mutation.
- **SRT-021** — User-supplied regex is data, not executable PHP/JS/SQL; no callback/eval replacement primitive exists in standard UI/API.
- **SRT-022** — Changing regex/options after Dry Run changes Plan fingerprint and invalidates stale approval/execution request.

## Group 3 — table, schema and column scope validation — SRT-023…033

- **SRT-023** — Selected native WordPress table is resolved through current site/schema registry and cannot be selected by an unvalidated request string.
- **SRT-024** — Selected custom table must be registered/introspected with stable owner/schema metadata before generic inspection is permitted.
- **SRT-025** — Unknown table defaults to excluded; advanced inspected profile requires explicit schema review and never becomes arbitrary raw SQL access.
- **SRT-026** — Column identifiers are validated against current schema; a crafted identifier containing SQL syntax is rejected before query construction.
- **SRT-027** — Read-only/protected column is reported in scope preview and cannot be mutated by generic transform.
- **SRT-028** — Primary/unique key mutation is blocked by default and requires a specialized migration profile with collision/referential evidence.
- **SRT-029** — Password/hash/token/secret columns are prohibited from generic replace even for a high-privilege actor unless an owning adapter exposes a safe typed Ability.
- **SRT-030** — Scope filter/query is compiled through registered Query/Data Source primitives and cannot inject raw WHERE/ORDER SQL.
- **SRT-031** — Schema fingerprint includes table/column/type/collation facts material to the Plan and changes when incompatible schema drift occurs.
- **SRT-032** — Dropped/renamed table between Dry Run and Run invalidates Plan before mutation; it cannot silently retarget a similarly named table.
- **SRT-033** — Estimated row/byte counts are labeled estimates and are not reused as actual mutation counts after execution.

## Group 4 — PHP serialized data and no object instantiation — SRT-034…044

- **SRT-034** — Valid serialized scalar string is parsed and replacement recalculates serialized string length correctly in preview.
- **SRT-035** — Serialized nested array traversal replaces allowed string values while preserving scalar types, indexes and associative keys according to profile.
- **SRT-036** — `O:` serialized object payload is parsed/handled without class instantiation; magic methods/autoload cannot execute.
- **SRT-037** — Incomplete/unknown class serialization cannot cause object creation and is rejected or preserved under typed unsupported state.
- **SRT-038** — Malformed serialized value is reported and excluded from structured mutation; no silent partial rewrite occurs.
- **SRT-039** — Double/nested serialization is detected and transformed only to configured depth with bounded recursion.
- **SRT-040** — Serialized keys vs values obey explicit transform policy; key changes that could alter semantics require separate review/collision checks.
- **SRT-041** — Serialized references/recursive structures unsupported by safe parser produce explicit unsupported status rather than unsafe unserialize fallback.
- **SRT-042** — Multibyte UTF-8 replacement recalculates byte lengths, not character counts, preserving valid PHP serialization.
- **SRT-043** — Output is re-parsed/validated before write; structurally invalid serialization is never committed.
- **SRT-044** — Serialized values containing strings that resemble PHP code remain inert data; generic transform provides no execution path.

## Group 5 — JSON, block, HTML and shortcode structured transforms — SRT-045…055

- **SRT-045** — Valid JSON object path update changes only the selected typed value and preserves unrelated keys/types.
- **SRT-046** — JSON array path/index handling rejects out-of-range/ambiguous target instead of creating accidental structure.
- **SRT-047** — Invalid JSON under structured mode is skipped/error; literal fallback requires an explicit distinct profile/warning.
- **SRT-048** — JSON number/bool/null remain typed and are not coerced to strings by generic replacement.
- **SRT-049** — Gutenberg block parser-aware transform updates a certified attribute while preserving block comment grammar and validity.
- **SRT-050** — Block structural diff detects parser breakage and prevents commit when output cannot be parsed under certified profile.
- **SRT-051** — HTML attribute-aware transform changes only selected URL/text attribute and preserves escaped context; script/event-handler insertion is rejected by destination policy.
- **SRT-052** — HTML text replacement does not blindly rewrite markup when parser-aware profile is required for correctness.
- **SRT-053** — Shortcode attribute transform changes only certified attribute syntax and preserves unknown shortcodes/content unchanged.
- **SRT-054** — Nested shortcode/block/HTML content exceeding parser depth/size budget is skipped with bounded diagnostic, not recursively expanded forever.
- **SRT-055** — Registered structured transformer is versioned and deterministic; plugin disable/version change invalidates affected Plan instead of falling back to literal mutation silently.

## Group 6 — typed transforms, URL mapping and output validation — SRT-056…066

- **SRT-056** — Integer typed mapping preserves integer storage semantics and rejects nonnumeric replacement outside declared coercion policy.
- **SRT-057** — Decimal/money transform uses canonical precision/scale rules and never applies binary floating-point rounding silently.
- **SRT-058** — Boolean map distinguishes true/false from string representations under the declared storage adapter.
- **SRT-059** — Date/time transform records source timezone/profile and preserves intended instant/local semantics across DST boundaries.
- **SRT-060** — URL host/base-path map preserves percent encoding/query fragments per profile and rejects malformed destination base URL.
- **SRT-061** — Prefix/suffix transformation enforces maximum output length and field validator before write.
- **SRT-062** — Field rename/mapping routes through owning Field/Definition API and cannot directly rename protected storage keys behind the owner.
- **SRT-063** — Collision policy `reject/skip/map/review` is explicit for transforms that would create duplicate unique values.
- **SRT-064** — Idempotent transform applied to already-transformed value produces no second semantic change; non-idempotent profiles are marked accordingly.
- **SRT-065** — Output failing owner field validator is row-scoped failure and cannot be silently truncated/coerced to pass.
- **SRT-066** — Deterministic registered SDK transformer cannot access arbitrary network/filesystem/secrets through the generic transform contract.

## Group 7 — Dry Run fingerprint, counts and sample truth — SRT-067…077

- **SRT-067** — Dry Run writes no business/source data and records Plan fingerprint, schema fingerprint, transform revision and actor/scope metadata only.
- **SRT-068** — Matched row count and matched field/occurrence count are reported separately; one row with many matches is not misrepresented.
- **SRT-069** — Exact count vs sampled/estimated count is labeled truthfully based on selected volume/profile.
- **SRT-070** — Sample before/after values are privacy-redacted while still preserving enough typed evidence to review the transformation.
- **SRT-071** — Protected/denied rows are counted separately from no-match rows; authorization denial cannot look like absence.
- **SRT-072** — Parse/validation errors are itemized separately from skipped/excluded records and are included in approval impact.
- **SRT-073** — Unique/collision warnings are derived from current target constraints and invalidate Plan if material source/schema changes occur.
- **SRT-074** — Dry Run lists affected sites/tables/modules and cannot hide a global/network table behind a site-only summary.
- **SRT-075** — Backup/rollback class is computed from selected owners/actions and cannot claim R1/R2 reversibility without corresponding owner evidence.
- **SRT-076** — Stale-plan expiry/version policy prevents an old Dry Run fingerprint from authorizing materially changed scope/data indefinitely.
- **SRT-077** — Repeated Dry Run of unchanged canonical inputs yields deterministic Plan identity/count semantics within declared snapshot consistency profile.

## Group 8 — exact diff, privacy, redaction and CSV safety — SRT-078…088

- **SRT-078** — Field-level diff distinguishes unchanged, changed, added, removed, null and redacted values accurately.
- **SRT-079** — Structured JSON/block diff shows semantic path changes rather than misleading whole-blob replacement where parser support exists.
- **SRT-080** — Very large value diff is bounded/truncated with checksum/length evidence and cannot dump unlimited sensitive content into UI/logs.
- **SRT-081** — PII classification masks values according to actor/purpose while retaining counts and resource identity only as allowed.
- **SRT-082** — Vault secret/password/token values never appear in preview/diff/export even when matching search text exists in protected storage.
- **SRT-083** — Row denied by Policy cannot leak before/after through aggregate samples, error text or downloadable diff.
- **SRT-084** — CSV preview/export neutralizes spreadsheet formulas in every untrusted text cell and records encoding/CSV dialect.
- **SRT-085** — Exported redacted preview is scoped to approved sites/tables and cannot include excluded records through hidden/raw columns.
- **SRT-086** — Search/filter on diff view does not reveal protected values through hit highlighting/count side channels beyond allowed metadata.
- **SRT-087** — Downloaded preview artifact has retention/access classification and is not placed in a public predictable media path.
- **SRT-088** — Diff generated from a stale source revision is marked stale and cannot be presented as current approval evidence.

## Group 9 — backup, rollback classes and journals — SRT-089…099

- **SRT-089** — R0 preview-only plan requires no mutation rollback and cannot display an “Undo” action.
- **SRT-090** — R1 exact-journal plan records sufficient before-value identities within privacy/size budget and proves reversal preconditions before claiming undo.
- **SRT-091** — R2 module-specific compensation invokes only the owning module’s typed compensation contract and records resulting new revision/state.
- **SRT-092** — R3 critical migration verifies required backup class/freshness/reference before starting destructive mutation.
- **SRT-093** — R4 external/irreversible side-effect request is rejected by generic Search/Replace rather than routed to arbitrary provider mutation.
- **SRT-094** — Backup requirement cannot be satisfied by an unverified/stale/failed backup artifact when policy requires verified restore-ready evidence.
- **SRT-095** — Mutation journal stores plan/run/batch/item identity and before/after provenance without exposing unnecessary full sensitive payloads.
- **SRT-096** — Rollback Dry Run revalidates current values; a row changed after original Run is conflict/skip, not blindly overwritten with old data.
- **SRT-097** — Partial rollback is allowed only when referential/owner consistency contract says subset reversal is safe; otherwise whole-plan recovery is required.
- **SRT-098** — Rollback failure produces truthful partial recovery state and preserves evidence for forward-fix; it never marks the original Run “not happened.”
- **SRT-099** — Backup restore can rewind local DB/files but cannot claim external caches/providers/search engines were rolled back; post-restore reconciliation is required.

## Group 10 — JobService, checkpoints, pause/resume and crash — SRT-100…110

- **SRT-100** — Large Run is represented by durable Job identity tied to Plan fingerprint and target scope; queued ≠ started ≠ completed.
- **SRT-101** — Batch cursor/checkpoint records last durably verified item/range and advances only after committed batch semantics.
- **SRT-102** — Pause stops at a safe boundary and preserves current checkpoint; resume revalidates Plan/schema/source preconditions.
- **SRT-103** — Cancel request distinguishes requested/canceling/canceled and does not claim already committed mutations were undone.
- **SRT-104** — Crash before batch commit replays safely without duplicate mutation under idempotency/precondition contract.
- **SRT-105** — Crash after commit but before checkpoint update reconciles actual row state/journal before deciding replay.
- **SRT-106** — Retryable DB/transient error uses bounded backoff; validation/schema/permission errors do not retry forever.
- **SRT-107** — Lease expiry/redelivery cannot permit two workers to mutate the same batch concurrently without conflict fencing.
- **SRT-108** — Progress shows examined/matched/changed/skipped/failed/verified counts separately and cannot derive success from queue completion alone.
- **SRT-109** — Temp artifacts/journals are run-scoped and cleanup does not remove evidence needed for active resume/rollback.
- **SRT-110** — Job timeout/resource budget yields paused/failed/partial truthful state, never hidden timeout-driven “success.”

## Group 11 — concurrency, schema drift and unique collisions — SRT-111…121

- **SRT-111** — Row changed after Dry Run but before mutation is detected through revision/hash/value precondition and handled by configured conflict policy.
- **SRT-112** — Concurrent Run targeting overlapping row/field is serialized/fenced or conflict-detected; no silent lost update.
- **SRT-113** — Two non-overlapping Runs can proceed without a global lock when resource-key model proves separation.
- **SRT-114** — Schema type/collation change between Dry Run and Run invalidates affected Plan before write.
- **SRT-115** — Plugin/module disabled between Plan and Run blocks owner-required mutation rather than bypassing owner API with direct DB update.
- **SRT-116** — Unique key collision on transformed value is caught before/at write and recorded per row with no silent suffix/truncation.
- **SRT-117** — Foreign/relation dependency conflict routes through owning relation/table contract and cannot be “fixed” by generic nulling.
- **SRT-118** — Site deleted/transferred mid-run fences remaining site batches and records unresolved scope.
- **SRT-119** — Table renamed/replaced with same name but different schema fingerprint cannot inherit old cursor blindly.
- **SRT-120** — Re-read/re-evaluate conflict policy uses the same immutable Transform revision and records changed source provenance.
- **SRT-121** — Critical-scope policy can abort whole Plan on first conflict; already committed batches remain explicitly partial and are not hidden.

## Group 12 — URL migration, home/siteurl, GUID, permalink and cache flow — SRT-122…132

- **SRT-122** — URL migration inventory distinguishes home/siteurl, content URLs, media URLs, serialized/JSON/block occurrences and external references.
- **SRT-123** — `guid` is excluded by default and enabling a specialized GUID action requires explicit semantics/review rather than broad host replacement.
- **SRT-124** — `home`/`siteurl` changes use the controlled migration step/owner API and cannot be transformed accidentally by general table scope.
- **SRT-125** — Old/new scheme, host and base-path mapping validates canonical origins and prevents open/invalid destination URLs.
- **SRT-126** — Multisite domain mapping produces explicit per-site old→new map; shared/global tables are not transformed using current-blog assumption.
- **SRT-127** — Permalink flush/cache invalidation occurs as a distinct post-mutation action and failure is reported separately from data mutation success.
- **SRT-128** — Redirect handoff creates a Draft/Plan for URL Routing, not an implicit active redirect, and preserves migration provenance.
- **SRT-129** — Media URL changes defer to Media/asset ownership where identifiers/storage need owner-aware migration rather than text replacement.
- **SRT-130** — Link Health post-scan result is verification evidence, not proof every external URL/provider is reachable or correct.
- **SRT-131** — Partial URL migration can be resumed/reconciled using Run journal and cannot reuse stale count/fingerprint after environment/domain change.
- **SRT-132** — Restore/rollback after URL migration distinguishes local database reversal from external DNS/CDN/search-index state, which requires separate reconciliation.

## Group 13 — DSR, Field, Relation, Custom Table and adapter-owned writes — SRT-133…143

- **SRT-133** — Data Source Registry exposes searchable/mutable fields with owner/capability metadata; generic engine cannot infer write authority from column visibility.
- **SRT-134** — Custom Field mutation calls Field Storage normalization/validation hooks and records field schema revision.
- **SRT-135** — Relation endpoint/reference update uses Relation API and cannot directly rewrite pivot/foreign metadata behind that owner.
- **SRT-136** — Custom Table update uses registered table/repository contract and respects typed constraints/indexes/row Policy.
- **SRT-137** — Definition-owned key/reference change checks dependency graph and cannot orphan dependent definitions silently.
- **SRT-138** — Woo product/order/customer fields are writable only through certified WCA abilities; direct HPOS/private-table assumptions are prohibited.
- **SRT-139** — Membership/ledger/reservation/document immutable business history is excluded unless owning domain exposes an explicit safe migration/compensation contract.
- **SRT-140** — Search index/projection/cache data is treated as derived and repaired/rebuilt through owner rather than promoted to source truth by replacement.
- **SRT-141** — Third-party registered adapter can declare inspect-only fields; generic engine honors read-only contract even if DB user can technically write.
- **SRT-142** — Owner callback failure leaves row unmodified or typed partial according to owner transaction contract; engine does not fall back to raw SQL.
- **SRT-143** — Owner adapter version change after Dry Run invalidates affected mutation Plan until compatibility/migration evidence is refreshed.

## Group 14 — REST, Abilities, MCP, CLI and AI approval — SRT-144…154

- **SRT-144** — REST can create Draft Search/Transform/Scope and run read-only Dry Run only for actor-visible sources.
- **SRT-145** — REST start Run requires capability, target Policy, exact Plan fingerprint and high-risk reauth/approval where configured.
- **SRT-146** — Ability `inspect/search` remains read-only and cannot be parameter-smuggled into mutation.
- **SRT-147** — Ability `execute` exposes typed operation schema only; no raw SQL/PHP/JS callback or arbitrary table identifier parameter exists.
- **SRT-148** — MCP discovery is opt-in and hides mutation abilities the current principal cannot invoke.
- **SRT-149** — MCP prompt/tool input containing prompt injection from DB content remains untrusted data and cannot grant extra tools/approval.
- **SRT-150** — CLI dry-run supports explicit site/scope/profile and outputs fingerprint; it does not infer destructive production Run from a saved profile alone.
- **SRT-151** — CLI execution requires exact fingerprint/approval semantics and returns partial/conflict state accurately for automation callers.
- **SRT-152** — AI Prompt produces typed Draft Plan only; hallucinated tables/columns/options fail schema/registry validation.
- **SRT-153** — AI cannot approve its own destructive Run, alter approval fingerprint or broaden protected-field scope through prose.
- **SRT-154** — Audit identifies actor/channel/plan/run while AI/MCP attribution remains supplemental, never authentication authority.

## Group 15 — Multisite, global tables and site lifecycle — SRT-155…165

- **SRT-155** — Site-scoped Plan resolves table prefixes/DSR owners from server-trusted site identity and cannot switch sites via request parameter.
- **SRT-156** — Network Plan enumerates exact target sites and per-site counts; `current_blog` context cannot become durable ownership.
- **SRT-157** — Global users/usermeta are classified separately from site tables and require explicit network/global authority before mutation.
- **SRT-158** — Same custom table name on different sites remains isolated by owner/site namespace and cannot share cursor/fingerprint accidentally.
- **SRT-159** — Network actor still needs per-resource Policy for protected site data; network visibility alone does not reveal redacted row values.
- **SRT-160** — Site deletion mid-Plan fences unresolved batches and delegates cleanup/lifecycle rather than writing into a removed site schema.
- **SRT-161** — Site clone/migration generates new environment identity and quarantines live provider/secrets; Search/Replace does not blindly copy active external identifiers.
- **SRT-162** — Site-domain migration map is explicit per site and detects collision/ambiguous destination domains.
- **SRT-163** — Network rollback reports per-site success/failure; one successful site cannot promote the entire network Run to fully rolled back.
- **SRT-164** — Import/export of saved profiles excludes secrets and requires explicit destination site/table mapping; missing owners are unresolved, not guessed.
- **SRT-165** — Large-network scheduling/fairness prevents one site from monopolizing Job/DB budget while preserving deterministic per-site checkpoints.

## Group 16 — multibyte charset, performance, recovery and adversarial security — SRT-166…176

- **SRT-166** — utf8mb4 emoji/combining/RTL values survive literal/structured replacement without truncation or invalid byte sequences.
- **SRT-167** — Charset/collation mismatch is detected and cannot cause unsafe query escaping or false case-insensitive match claims.
- **SRT-168** — 1k/100k/1M/10M-row benchmark profiles record hardware/software/schema/batch/field-size context before throughput claims.
- **SRT-169** — Large serialized/JSON field benchmark includes parser memory/time caps so one pathological row cannot exhaust worker memory.
- **SRT-170** — DB deadlock/lock timeout leaves batch checkpoint consistent and retries only when idempotency/preconditions prove safe.
- **SRT-171** — SQL injection payload in search/replacement/value is bound as data and cannot escape parameterization; identifier injection is rejected by schema validation.
- **SRT-172** — Serialized-object gadget payload cannot trigger autoload/magic methods or arbitrary code during scan/preview/mutation.
- **SRT-173** — CSV formula, HTML/script-looking strings and log-control characters remain inert/escaped in reports/audit.
- **SRT-174** — Backup restore/crash recovery re-establishes local source truth then invalidates stale Plan/checkpoint where schema/data fingerprint changed.
- **SRT-175** — Regression suite covers Policy bypass, protected-field leak, concurrent overwrite, unique collision, schema drift, partial Run and rollback-truth failures.
- **SRT-176** — Golden end-to-end domain migration fixture covers inventory → Dry Run → redacted diff → backup gate → batched apply → cache/permalink/link verification → rollback/reconcile without false execution/provider claims.

## Stop-the-line conditions

Certification stops on arbitrary SQL/code execution, serialized object instantiation, secret/password mutation by generic replace, unvalidated identifiers, silent concurrent overwrite, false rollback claim, cross-site/global-table leakage, privacy leak or AI/MCP approval bypass.

## Execution gate

All 176 fixtures are documented only. No DB mutation, WordPress Run, test, benchmark, provider/API/AI/MCP call or build has executed. ADR-0014 owner consent remains mandatory.