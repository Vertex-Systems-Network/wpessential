# WPEssential — Database Maintenance / Cleanup Executable Evidence Protocol

Status: **Exact planning evidence / NOT EXECUTED / no development authorization**  
Date: 2026-08-29  
Work package: **WP113**  
Namespace: **DBM-001…DBM-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## Purpose

Freeze exact future evidence for Surface 48 Database Maintenance, Cleanup & Storage Health, preserving the 16 canonical groups from the market-expansion master plan and `MODULES/DATABASE-MAINTENANCE-CLEANUP-EXHAUSTIVE-SPEC.md`.

## Truth boundaries

- Candidate/orphan suspicion ≠ deletion authority.
- Ownership must be known through WordPress semantics, WPE owner/provider registry or certified adapter before destructive cleanup.
- Dry Run/estimated reclaim ≠ actual deletion/reclaimed bytes.
- Cache/transient cleanup ≠ business-data cleanup.
- Backup success ≠ restore verification unless the required restore class is proven.
- Audit/privacy/legal-hold retention belongs to owning services and cannot be overridden by generic maintenance.
- Third-party/Woo/Action Scheduler data is cleaned only through certified owner adapter/profile.
- AI may explain/rank/propose only; destructive execution remains Policy/re-auth/approval governed.

---

## Group 1 — owner/provider registry — DBM-001…011

- **DBM-001** — Registered cleanup provider exposes stable key/version, storage owner, candidate query, identity keys, eligibility, action and post-check contract.
- **DBM-002** — Duplicate provider key/version in same owner scope is rejected; a later provider cannot silently replace another owner’s cleanup semantics.
- **DBM-003** — Provider candidate query returns typed identities only from declared storage/site scope and cannot expand to arbitrary table names from request input.
- **DBM-004** — Provider minimum-age/retention rule is applied using server-authoritative time and cannot be bypassed by a user-supplied cutoff beyond allowed policy.
- **DBM-005** — Provider marks delete/archive/compact action and rollback class explicitly; generic UI cannot infer reversibility from action label.
- **DBM-006** — Provider dependency list blocks cleanup when dependent owner state would be orphaned and records exact blocker.
- **DBM-007** — Provider disabled/uninstalled after Dry Run invalidates affected Plan and does not fall back to raw SQL deletion.
- **DBM-008** — Provider version change invalidates stale Plan where candidate/action semantics changed.
- **DBM-009** — Unknown table/row owner is classified `UNKNOWN` and excluded from automatic destructive Plan even if naming resembles known plugin data.
- **DBM-010** — Provider privacy/Multisite metadata is propagated into candidate report and cannot be stripped by aggregate cleanup view.
- **DBM-011** — Import/export of cleanup rules references provider keys/versions only and never embeds arbitrary callbacks, SQL or Vault credentials.

## Group 2 — revisions, auto-drafts, trash and comments — DBM-012…022

- **DBM-012** — Post revision candidate older than configured retention is identified through WordPress ownership and linked parent post identity.
- **DBM-013** — `keep newest N` preserves the newest required revisions per object deterministically under equal timestamps/IDs.
- **DBM-014** — `keep newer than duration` uses server/site time semantics and preserves revisions inside cutoff exactly.
- **DBM-015** — Named/milestone/pinned revisions are excluded even when old enough for ordinary retention deletion.
- **DBM-016** — Auto-draft candidate requires correct WordPress status/age and cannot delete active editor draft or unrelated custom status.
- **DBM-017** — Trashed post/page/CPT cleanup respects post-type exclusions and owner/legal retention before deletion.
- **DBM-018** — Spam comment cleanup follows comment status/age profile and preserves comments under configured moderation/legal retention.
- **DBM-019** — Unapproved comments are not automatically equivalent to spam/trash and require explicit retention profile.
- **DBM-020** — Deleting parent content uses WordPress owner semantics for revisions/meta/comments; maintenance module does not manually cascade unknown tables.
- **DBM-021** — Concurrent restore/untrash/edit before delete is rechecked at execution time and invalidates candidate rather than deleting newer live content.
- **DBM-022** — Actual deleted counts are journaled separately from Dry Run candidates/skips/errors and are verified after batch.

## Group 3 — transients and cache-like cleanup — DBM-023…033

- **DBM-023** — Expired site transient candidate follows verified WordPress expiration semantics and can be cleaned under certified low-risk profile.
- **DBM-024** — Expired network transient is distinguished from site transient and requires correct network/global scope.
- **DBM-025** — Non-expired transient remains excluded unless explicit cache-reset profile is selected and authorized.
- **DBM-026** — Object-cache presence is detected/labeled; deleting DB transient rows is not claimed to flush external object cache automatically.
- **DBM-027** — Cache flush is a separate owner action from DB-row cleanup and reports success/failure independently.
- **DBM-028** — Option name matching `_transient_*` but not valid WordPress transient pair/owner semantics is not blindly deleted.
- **DBM-029** — WPE Cache generations are cleaned through Cache owner and cannot be deleted by generic options-table heuristic.
- **DBM-030** — Cache cleanup failure cannot promote a DB cleanup Run to fully verified if required cache postcondition remains unresolved.
- **DBM-031** — High-volume transient cleanup uses bounded batches/checkpoints and does not hold an unbounded transaction/lock.
- **DBM-032** — Concurrent transient recreation after candidate scan is rechecked so live refreshed data is not removed using stale expiry state.
- **DBM-033** — Cache/transient cleanup report never counts business-table bytes or derived estimates as reclaimed without storage evidence.

## Group 4 — metadata/relation orphan certainty — DBM-034…044

- **DBM-034** — Postmeta whose parent post is definitively missing is classified `CERTAIN_BY_WORDPRESS_IDENTITY` only after correct site/table identity check.
- **DBM-035** — Commentmeta/termmeta orphan detection resolves parent existence through owning WordPress identity and does not infer from display keys.
- **DBM-036** — Usermeta cleanup distinguishes global user existence from site membership removal; site deletion is not proof global user is orphaned.
- **DBM-037** — Relation endpoint missing under certified Relation owner can be `CERTAIN_BY_FK/OWNER API`; generic raw pivot guessing remains prohibited.
- **DBM-038** — Probable orphan from heuristic plugin prefix is classified `PROBABLE` and excluded from automatic deletion.
- **DBM-039** — Unknown orphan confidence remains `UNKNOWN`; UI cannot turn unknown into “safe cleanup” through checked-by-default option.
- **DBM-040** — Attachment derivative/source orphan is delegated to Media owner; DB maintenance does not delete files based on path/meta guess.
- **DBM-041** — Job/event child missing parent is cleaned only through Job/Audit/Event owner retention contract and preserves required incident evidence.
- **DBM-042** — Search index/tombstone stale entries are derived owner data and are rebuilt/retained/cleaned through Search owner rather than source business deletion.
- **DBM-043** — Concurrent parent recreation/change before orphan delete is rechecked and prevents stale candidate deletion where identity is now valid.
- **DBM-044** — Orphan cleanup verification proves target identity is gone and no certified dependency remains; estimated candidate count alone is insufficient.

## Group 5 — WPE Jobs, Workflow, Notification, Analytics and Audit retention providers — DBM-045…055

- **DBM-045** — Completed Job/Attempt history becomes candidate only after JobService retention minimum and active/retry dependencies are satisfied.
- **DBM-046** — Active/queued/running/retryable Job cannot be removed by generic age rule.
- **DBM-047** — Workflow history cleanup uses Workflow retention provider and preserves histories required by active executions/approvals.
- **DBM-048** — Notification/email/chat delivery-history cleanup follows communication owner retention/privacy and does not delete source messages/records implicitly.
- **DBM-049** — Analytics raw-event retention/downsampling delegates to Analytics owner; summarized metrics are not assumed equivalent backup of raw data.
- **DBM-050** — Audit log cleanup can occur only through Audit retention/integrity contract; generic maintenance cannot truncate audit tables.
- **DBM-051** — Legal hold on audit/workflow/communication record blocks destructive retention for exact authorized scope and is reported as retained.
- **DBM-052** — Search index generations/tombstones are cleaned only when Search owner marks generation superseded and safe to remove.
- **DBM-053** — Import/export journals remain while active/recovery/rollback dependencies exist; age alone is insufficient.
- **DBM-054** — Old generated Fixture Run data cleanup composes DMY ownership and never deletes real records merely referenced by a fixture run.
- **DBM-055** — Provider-specific cleanup partial failure leaves per-provider counts/state and cannot report the aggregate Plan as fully successful.

## Group 6 — module uninstall and retained-data providers — DBM-056…066

- **DBM-056** — Disabled module data remains retained by default unless its lifecycle/uninstall policy explicitly exposes cleanup candidates.
- **DBM-057** — Uninstalled module with known retained-data provider can expose candidates by stable owner signature/version without guessing unknown rows.
- **DBM-058** — Unknown third-party plugin table after uninstall remains untouched until a certified adapter/owner mapping exists.
- **DBM-059** — Definition/revision orphan cleanup uses Definition Repository dependency graph and cannot remove referenced archived revisions silently.
- **DBM-060** — Module uninstall provider distinguishes user-selected “retain data” vs “remove data” intent and preserves prior owner consent record where required.
- **DBM-061** — Reinstall/reactivation before cleanup re-evaluates retained-data candidates and can cancel stale delete eligibility.
- **DBM-062** — Shared table rows owned by multiple modules/providers are never removed solely because one module is uninstalled.
- **DBM-063** — Provider schema unavailable after uninstall can yield inspect-only/quarantine state rather than arbitrary raw deletion.
- **DBM-064** — Retained secret/Vault references are cleaned through Vault/lifecycle owner and are never exposed in maintenance previews.
- **DBM-065** — Cleanup of module-generated files/artifacts routes through owning asset/file provider; DB row deletion cannot claim file removal.
- **DBM-066** — Post-cleanup reinstall/migration verifies no required retained owner state was removed beyond approved lifecycle contract.

## Group 7 — autoload health and owner-aware changes — DBM-067…077

- **DBM-067** — Report total autoload bytes from current DB/profile and label engine/version semantics used for measurement.
- **DBM-068** — Largest autoloaded options are ranked by measured storage size; owner/plugin guess is advisory unless registry ownership exists.
- **DBM-069** — Duplicate/obsolete option candidate is not auto-deleted without owner signature/dependency evidence.
- **DBM-070** — Changing autoload flag for WPE-owned setting uses Settings owner contract and validates expected runtime access pattern.
- **DBM-071** — Third-party/core-critical option autoload flag cannot be toggled by generic heuristic “large option” action.
- **DBM-072** — High update-frequency autoload option is reported as performance signal only; write frequency does not authorize deletion/flag change.
- **DBM-073** — Suggested migration of large WPE setting to alternate storage requires accepted owner migration Plan and rollback/recovery profile.
- **DBM-074** — Autoload size estimate vs actual page/runtime impact remains separate; bytes alone are not proof of performance benefit.
- **DBM-075** — Multisite site option vs network/sitewide option ownership is distinguished and reported in correct scope.
- **DBM-076** — Concurrent option update before remediation rechecks revision/value and avoids overwriting newer configuration.
- **DBM-077** — Post-change verification measures resulting autoload state and owner behavior; attempted flag update is not sufficient evidence.

## Group 8 — table size, index, schema and fragmentation health — DBM-078…088

- **DBM-078** — Table report records engine, charset/collation, estimated/actual row count source, data/index size and owner where known.
- **DBM-079** — Missing expected WPE index is detected against owning schema contract, not inferred solely from slow-query symptom.
- **DBM-080** — Unexpected schema drift is reported with expected/current signature and blocks destructive maintenance that depends on old schema.
- **DBM-081** — High-growth table trend is an observability signal, not automatic cleanup authority.
- **DBM-082** — Temporary/staging table is candidate only when owner/job/migration provider proves it is abandoned and outside recovery window.
- **DBM-083** — Fragmentation/free-space estimate is DB-engine-specific and labeled advisory; unsupported engine cannot receive generic optimize claim.
- **DBM-084** — Optimize/repair action is exposed only for supported certified DB-engine/profile and requires its own risk/lock/backup semantics.
- **DBM-085** — Integrity/check operation is read-only where possible and result failure does not trigger automatic destructive repair.
- **DBM-086** — Table-size/reclaimed-byte reporting distinguishes logical deleted rows from physical file/engine space actually released.
- **DBM-087** — Index/schema report for third-party table remains inspect/advisory unless owner adapter authorizes change.
- **DBM-088** — Post-maintenance table verification records schema/row/size/engine facts and cannot infer success from SQL command return alone.

## Group 9 — Dry Run, Plan, fingerprint and estimates — DBM-089…099

- **DBM-089** — Dry Run writes no candidate/source data and records Plan revision/fingerprint, provider versions, site scope and cutoff.
- **DBM-090** — Candidate count is separated by provider/class/site with exact vs estimated flags.
- **DBM-091** — Estimated bytes recoverable is labeled estimate and records calculation method/engine; UI cannot present as guaranteed disk savings.
- **DBM-092** — Oldest/newest candidate timestamps use authoritative storage values/timezone and distinguish null/unknown dates.
- **DBM-093** — Dependency/false-orphan uncertainty is visible per candidate class and can elevate Plan from automatic to review-only.
- **DBM-094** — Backup requirement/rollback class derives from highest-risk included action/provider and cannot be manually downgraded without policy authority.
- **DBM-095** — Estimated duration/lock/resource cost is labeled projection and cannot become performance certification.
- **DBM-096** — Plan fingerprint changes when candidate classes/cutoff/site/provider/version/schema materially change.
- **DBM-097** — Stale Dry Run expires/rechecks candidates before execution because concurrent writes/retention transitions can change eligibility.
- **DBM-098** — Preview/redacted sample does not expose deleted payloads/secrets beyond actor’s Policy/purpose.
- **DBM-099** — Re-running unchanged Dry Run yields deterministic Plan semantics while candidate counts may change only when source time/data changed and that delta is explicit.

## Group 10 — backup, rollback and re-authentication — DBM-100…110

- **DBM-100** — C0 inspect-only Plan executes no mutation and needs no destructive reauth/rollback.
- **DBM-101** — C1 certified cache/expired cleanup still records candidates/actions/post-check and cannot be called reversible unless provider says so.
- **DBM-102** — C2 archive/journal profile captures recoverable identity/state within privacy/size limits before deletion.
- **DBM-103** — C3 destructive history/business cleanup requires configured verified backup class/freshness before first mutation.
- **DBM-104** — C4 irreversible/provider-specific cleanup is blocked unless explicit policy/provider contract exists; generic module cannot invent compensation.
- **DBM-105** — Stale/failed/unverified backup cannot satisfy a Plan requiring verified restore-ready protection.
- **DBM-106** — High-risk execution requires re-auth/confirmation token bound to actor, Plan fingerprint, scope and expiry.
- **DBM-107** — Plan changes after reauth invalidate authorization where governance requires fingerprint binding.
- **DBM-108** — Rollback/recovery revalidates current owner state and cannot blindly restore deleted rows that now conflict with newer data.
- **DBM-109** — Backup restore can restore local data but not external provider/cache facts; post-restore owner reconciliation remains required.
- **DBM-110** — Failed rollback/recovery produces partial truthful state and preserves original deletion journal; it does not erase evidence of the cleanup Run.

## Group 11 — jobs, batches, concurrency and precondition recheck — DBM-111…121

- **DBM-111** — Large cleanup runs as durable Job tied to exact Plan/provider revisions; queued ≠ running ≠ completed.
- **DBM-112** — Batch checkpoint advances only after delete/archive/compact action and required post-check commit successfully.
- **DBM-113** — Candidate eligibility is rechecked immediately before destructive action to detect concurrent edit/revival/recreation.
- **DBM-114** — Concurrent writes that make candidate live cause skip/conflict, never stale deletion.
- **DBM-115** — Two overlapping cleanup Runs use resource keys/version fencing so same identity cannot be destroyed twice unpredictably.
- **DBM-116** — Non-overlapping provider/site batches can run concurrently under configured DB/resource budgets.
- **DBM-117** — Crash before commit replays safely; crash after commit-before-checkpoint reconciles actual owner/storage state first.
- **DBM-118** — DB deadlock/lock timeout retries only when action idempotency/preconditions are proven and does not advance checkpoint on failure.
- **DBM-119** — Pause/resume preserves cursor and revalidates provider/schema/site lifecycle before continuing.
- **DBM-120** — Cancel stops future batches at safe boundary and does not imply already deleted items were restored.
- **DBM-121** — Load/maintenance-window budget can pause work under site load without classifying pending candidates as cleaned.

## Group 12 — run journal and post-verification — DBM-122…132

- **DBM-122** — Run journal pins Plan fingerprint, actor, site/network scope, provider versions, backup reference and start/end state.
- **DBM-123** — Journal separates candidate/deleted/archived/skipped/conflict/error counts per provider/class/site.
- **DBM-124** — Per-batch checkpoint and error provenance allows exact resume/recovery without storing unnecessary full deleted payload.
- **DBM-125** — Reclaimed-byte value is recorded only when physically/engine-observably measured; otherwise remains estimate/unknown.
- **DBM-126** — Provider post-check confirms identity removed/archived and required dependencies remain valid before item is `verified`.
- **DBM-127** — Global Plan cannot be `verified complete` while one required provider/site remains failed/unknown.
- **DBM-128** — Sensitive deleted values are absent from generic logs; protected rollback journal, if required, has separate encrypted/access-controlled profile.
- **DBM-129** — Audit event records action/class/count/provenance without promoting audit log into the deleted business-data backup.
- **DBM-130** — Verification after cache/transient cleanup distinguishes DB state from external object-cache state.
- **DBM-131** — Report/export uses Policy/redaction and CSV formula safety and cannot leak table rows/secrets through diagnostics.
- **DBM-132** — Retention of Run journals follows privacy/operational policy and failed journal cleanup is reported truthfully.

## Group 13 — third-party, Woo and Action Scheduler adapter boundaries — DBM-133…143

- **DBM-133** — Unknown third-party table is inspect-only/unknown-owner and cannot be auto-cleaned by naming heuristic.
- **DBM-134** — Certified third-party cleanup adapter declares exact table/row owner/version and only its bounded candidate/action schema is exposed.
- **DBM-135** — Woo session/transient cleanup routes through supported Woo/WCA APIs/profile, not direct assumptions about private storage.
- **DBM-136** — Woo product/order/customer/payment/refund business rows are never generic cleanup candidates merely because old/inactive.
- **DBM-137** — Woo lookup/cache rebuild/cleanup remains derived owner action and cannot become source product/order truth.
- **DBM-138** — Action Scheduler completed/failed action/log cleanup uses certified Action Scheduler/JobService adapter retention semantics.
- **DBM-139** — Pending/running/retryable Action Scheduler job is never removed by simple age cutoff.
- **DBM-140** — Provider adapter version/storage change invalidates stale cleanup Plan and blocks raw fallback.
- **DBM-141** — Third-party provider unavailable yields unresolved/degraded candidate class, not permission to delete underlying storage directly.
- **DBM-142** — External service/provider artifacts are listed separately; deleting local references does not claim remote deletion/cancellation.
- **DBM-143** — Cross-plugin coexistence conflict is surfaced where two providers claim same storage; WPE does not choose owner silently.

## Group 14 — REST, Abilities, MCP, CLI and AI — DBM-144…154

- **DBM-144** — REST storage-health/list-provider endpoints return only actor-authorized metadata and redact protected details.
- **DBM-145** — REST create Plan/Dry Run is read-only and cannot delete rows through crafted “preview” option.
- **DBM-146** — Execute Ability requires actor capability, exact Plan fingerprint, scope, risk-class reauth/approval and provider availability.
- **DBM-147** — No raw DELETE/TRUNCATE/SQL Ability exists in standard REST/MCP/AI surface.
- **DBM-148** — Pause/resume/cancel Ability validates Run ownership/site scope and cannot retarget another tenant’s job ID.
- **DBM-149** — MCP discovery is opt-in and hides destructive abilities from principals lacking equivalent WordPress permissions.
- **DBM-150** — MCP/AI request naming arbitrary table/SQL is rejected by provider/schema registry instead of being passed through.
- **DBM-151** — AI Prompt explains/ranks candidates and drafts Cleanup Plan; hallucinated owner/provider/candidate classes fail deterministic validation.
- **DBM-152** — Prompt injection in option/table/comment/log content remains untrusted and cannot broaden tool scope or approve destructive Run.
- **DBM-153** — CLI dry-run/report can run with explicit scope, but destructive CLI requires fingerprint/reauth/approval semantics appropriate to environment.
- **DBM-154** — Audit records actor/channel/Run while AI/MCP agent metadata never substitutes for authentication/authorization.

## Group 15 — Multisite, global tables and site lifecycle — DBM-155…165

- **DBM-155** — Site cleanup resolves site tables/server ownership and cannot touch another site through forged prefix/site ID.
- **DBM-156** — Network Plan enumerates every target site/provider/class and applies site-level Policy plus network/global authority where needed.
- **DBM-157** — Global users/usermeta/network options are classified separately and cannot be cleaned by site-only authority.
- **DBM-158** — Site deletion lifecycle owns post-delete cleanup; maintenance provider cannot race lifecycle and remove shared/global data prematurely.
- **DBM-159** — Same provider/table suffix across sites remains isolated by site namespace in candidate/job/checkpoint keys.
- **DBM-160** — Network noisy-site/fairness budgets prevent one large site from monopolizing cleanup DB/job window.
- **DBM-161** — Site deactivation/deletion mid-Run fences pending site batches and records unresolved state.
- **DBM-162** — Site clone does not copy active cleanup Run/checkpoint as same identity and quarantines provider/environment-sensitive mappings.
- **DBM-163** — Network report can aggregate counts while redacting site details the actor cannot inspect.
- **DBM-164** — 100/1k/10k-site performance profiles include global-table coordination/fairness and remain unexecuted until evidence run.
- **DBM-165** — Restore/site recreation reconciles lifecycle/provider ownership before resuming an old cleanup Plan; stale site IDs/prefixes cannot be reused blindly.

## Group 16 — large DB, resource budgets, failure, recovery and security — DBM-166…176

- **DBM-166** — Large DB benchmark profile records DB engine/version, storage, table sizes, candidate distribution, hardware and concurrency before throughput/lock claim.
- **DBM-167** — Million-row candidate scan uses indexed owner query/batching and cannot load all identities/payloads into memory.
- **DBM-168** — Resource budgets cap transaction duration, batch size, CPU/IO and journal growth; exceeding budget pauses/fails truthfully.
- **DBM-169** — Storage-full/journal-write failure prevents checkpoint advancement and preserves already committed batch evidence.
- **DBM-170** — DB connection/server outage produces partial/recoverable state and does not mark pending candidates deleted.
- **DBM-171** — SQL injection payload in public/REST filters cannot alter table/column/action because identifiers are registry-validated and values parameterized.
- **DBM-172** — Malicious third-party table name/schema/payload cannot create arbitrary DELETE/TRUNCATE or log/CSV injection path.
- **DBM-173** — Backup/restore after cleanup re-establishes local truth but invalidates stale candidates/run when data/schema fingerprint changed.
- **DBM-174** — Security regression covers unknown-owner deletion, legal-hold bypass, cross-site/global-table leakage, arbitrary SQL and destructive AI/MCP bypass.
- **DBM-175** — Provider/module disabled/outage leaves its class unresolved and cannot convert failure into raw-storage cleanup or aggregate “success.”
- **DBM-176** — Golden end-to-end maintenance fixture covers provider inventory → Dry Run → risk/backup/re-auth → batched cleanup → post-check/journal → partial failure/recovery with ownership and truth boundaries preserved.

## Stop-the-line conditions

Certification stops on deletion of unknown-owner/real protected data, arbitrary SQL, legal-hold/retention bypass, cross-site/global-table leakage, false reclaimed-byte/rollback claim, third-party raw cleanup bypass or AI/MCP destructive approval bypass.

## Execution gate

All 176 fixtures are documented only. No DB cleanup, optimize/repair, WordPress mutation, test, benchmark, provider/API/AI/MCP call or build has executed. ADR-0014 remains mandatory.