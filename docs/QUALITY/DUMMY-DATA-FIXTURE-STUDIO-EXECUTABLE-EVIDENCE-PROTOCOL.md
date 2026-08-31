# WPEssential — Dummy Data / Fixture Studio Executable Evidence Protocol

Status: **Exact planning evidence / NOT EXECUTED / no development authorization**  
Date: 2026-08-29  
Work package: **WP113**  
Namespace: **DMY-001…DMY-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## Purpose

Freeze exact future evidence for Surface 46 Dummy Data, Synthetic Dataset & Fixture Studio. The 16 groups remain those fixed by the market-expansion master plan and `MODULES/DUMMY-DATA-FIXTURE-GENERATOR-EXHAUSTIVE-SPEC.md`.

## Truth boundaries

- Synthetic/generated data ≠ production/source truth.
- A realistic-looking person/company/address ≠ a real person/company/address.
- Generation must use owning APIs/validators and must not create a private bypass path into module storage.
- Generated commerce/membership/provider records do not imply real payment, message, shipment, entitlement or external-system facts.
- Cleanup authority comes from durable generated-object identity/provenance, never from data “looking synthetic.”
- Determinism claims require pinned seed + compatible generator/schema/provider versions; nondeterministic external providers invalidate strict reproduction.
- AI may draft Dataset/Scenario definitions only; execution/cleanup remains Policy/approval governed.

---

## Group 1 — posts, pages and CPTs — DMY-001…011

- **DMY-001** — Generate one Draft post through the owning WordPress API with Dataset/Run identity, deterministic title/body and no publication side effect beyond declared status.
- **DMY-002** — Generate published pages only when Dataset profile explicitly allows publish status; Draft-only profile must never leak content publicly.
- **DMY-003** — Generate a registered public CPT using its canonical schema/supports and reject unknown/unregistered post type instead of inserting raw rows.
- **DMY-004** — Generate private/non-public CPT only under actor/site Policy and keep generated data classification separate from visibility authorization.
- **DMY-005** — Parent/child hierarchical pages are created in dependency order with deterministic parent references and no orphan on successful Run.
- **DMY-006** — Slug uniqueness collision follows deterministic retry/suffix policy and records the final identity; it cannot overwrite a real post.
- **DMY-007** — Scheduled/future post date uses explicit site timezone and preserves DST semantics; local/client clock is not authority.
- **DMY-008** — Existing author reference is resolved under site/user Policy; missing author uses declared fallback or blocks item, never guesses an arbitrary admin.
- **DMY-009** — Block-content fixture remains valid block markup after creation and stores script-looking text as inert data.
- **DMY-010** — Bulk generation stops at configured per-entity count/byte quota and reports planned vs actual counts separately.
- **DMY-011** — Cleanup preview for generated posts matches durable Run ownership IDs only and does not select manually created content with similar titles/meta.

## Group 2 — terms and taxonomies — DMY-012…022

- **DMY-012** — Generate term in a registered taxonomy through owning WordPress API and bind exact site/taxonomy identity.
- **DMY-013** — Reject unknown taxonomy or taxonomy disabled after Dataset draft rather than creating orphan term-taxonomy rows.
- **DMY-014** — Hierarchical parent/child term generation respects parent creation order and reports unresolved parent rather than silently attaching to root.
- **DMY-015** — Term slug collision follows deterministic policy without renaming existing real term.
- **DMY-016** — Same term name in two taxonomies remains two typed identities and cannot be merged by display label.
- **DMY-017** — Weighted term assignment to generated posts is deterministic under seed and respects declared cardinality bounds.
- **DMY-018** — Empty-term generation in negative-test mode remains explicitly invalid/adversarial and cannot appear in ordinary demo dataset.
- **DMY-019** — Term description with HTML-like text passes owning sanitization profile and cannot become arbitrary script execution.
- **DMY-020** — Term meta generation delegates to Field/Meta owner and honors protected/read-only field policies.
- **DMY-021** — Cleanup removes generated terms only when dependency/usage policy permits; real content references can block deletion or require detach review.
- **DMY-022** — Multilingual/localized term variants preserve locale/provenance and are not assumed equivalent identities without translation adapter contract.

## Group 3 — users and comments — DMY-023…033

- **DMY-023** — Generate synthetic user with reserved/example email domain and no real-person source data.
- **DMY-024** — Generated username/email collision uses deterministic retry and never updates an existing real account accidentally.
- **DMY-025** — Authentication test user credential exists only in approved test/Vault profile; no known default password is embedded in Dataset/export/log.
- **DMY-026** — Generated user role assignment passes Role/Capability Policy and cannot grant Administrator merely because Dataset requested it without authorized profile.
- **DMY-027** — Global WordPress user identity vs site membership/role are created as separate facts in Multisite.
- **DMY-028** — Synthetic comments bind to generated/existing permitted content and preserve approved/unapproved/spam states as declared fixtures.
- **DMY-029** — Comment author name/email/URL use synthetic profiles and never scrape/import actual people.
- **DMY-030** — Threaded comments create parent references deterministically and detect cycles/invalid parent chains.
- **DMY-031** — Comment type/custom comment schema is validated; unknown type becomes unsupported rather than raw DB insert.
- **DMY-032** — User/comment cleanup respects ownership and external references; deleting a generated user cannot silently reassign/delete unrelated real content.
- **DMY-033** — PII/export/erase semantics label synthetic profiles accurately while still treating stored generated user data according to configured privacy class.

## Group 4 — fields, meta and value providers — DMY-034…044

- **DMY-034** — Required custom field receives value from compatible provider and is validated by owning field schema before save.
- **DMY-035** — Null probability and empty-string probability remain distinct and obey field nullability/default semantics.
- **DMY-036** — Integer/decimal provider respects min/max/precision/scale and deterministic distribution under seed.
- **DMY-037** — Date/time/timezone provider generates valid boundary values including leap day and DST fixture without inventing invalid instant unless negative mode selected.
- **DMY-038** — Enum/weighted-enum provider emits only allowed values and deterministic distribution metadata.
- **DMY-039** — Unique provider coordinates within target owner scope and records deterministic collision retries.
- **DMY-040** — Regex-generated string uses bounded grammar/length and cannot execute arbitrary regex callbacks/code.
- **DMY-041** — JSON/serialized providers emit structurally valid inert values and never instantiate classes or embed real secrets.
- **DMY-042** — Email/phone/IP providers use reserved/non-routable/documentation-safe profiles and clearly mark jurisdiction/profile assumptions.
- **DMY-043** — Cross-field dependency/correlation is evaluated in declared order and cycle is detected rather than recursing indefinitely.
- **DMY-044** — Negative invalid probability is allowed only in explicit isolated adversarial Dataset and never bypasses production owner validation unless the certified corruption-test harness says so.

## Group 5 — relations, graph and cardinality — DMY-045…055

- **DMY-045** — One-to-one relation generation creates exactly one permitted edge per endpoint and rejects cardinality conflict.
- **DMY-046** — One-to-many relation generates bounded child count with deterministic parent distribution.
- **DMY-047** — Many-to-many relation creates deterministic edge set with no duplicate pivot identity.
- **DMY-048** — Pivot fields are generated/validated through Relation owner and bind to exact edge identity.
- **DMY-049** — Reciprocal relation profile produces the owner-defined reciprocal edge once and avoids infinite mirror generation.
- **DMY-050** — Graph minimum/maximum degree constraints are satisfied or Run reports unsatisfiable constraints before creating invalid graph.
- **DMY-051** — Connected/disconnected cluster profile yields declared component count deterministically under seed.
- **DMY-052** — Cycle generation is permitted only when relation schema allows cycles; otherwise cycle attempt is rejected.
- **DMY-053** — Orphan endpoint fixture is generated only in explicit negative/corruption profile and is visibly marked as intentionally invalid.
- **DMY-054** — Relation generation never bypasses owning API with direct pivot/meta insertion just to satisfy volume target.
- **DMY-055** — Cleanup orders edges before/dependent on entities as required and verifies no generated residual relation references remain.

## Group 6 — status, lifecycle and time distributions — DMY-056…066

- **DMY-056** — Fixed state generation uses an allowed owning-domain state and records state definition revision.
- **DMY-057** — Weighted state distribution yields deterministic counts/order tolerance under seed without modifying state machine definitions.
- **DMY-058** — Transition-history fixture follows only permitted transitions and creates timestamps in monotonic valid order.
- **DMY-059** — Invalid transition sequence is isolated to explicit negative-test mode and never presented as normal domain truth.
- **DMY-060** — Time-in-state distribution respects current/future/expired boundaries and selected timezone.
- **DMY-061** — Overdue/stale fixtures are labeled synthetic and do not trigger real escalation/provider notifications.
- **DMY-062** — Scheduled/future domain record generation does not schedule real external jobs unless a local mock profile explicitly owns them.
- **DMY-063** — Cancelled/expired/revoked fixture remains domain-specific and does not imply payment/refund/entitlement fact outside owner.
- **DMY-064** — Historical status/event generation routes through owner’s fixture/import Ability rather than generic audit-log insertion.
- **DMY-065** — Impossible state is blocked outside corruption profile even if raw storage could represent it.
- **DMY-066** — Cleanup respects historical dependencies/retention and cannot silently delete generated records that owner marks immutable/non-destructive.

## Group 7 — media and attachments — DMY-067…077

- **DMY-067** — Local generated placeholder image has bounded dimensions/format/size and exact Run ownership metadata.
- **DMY-068** — Attachment creation uses WordPress media pipeline and records derivative generation result separately from source attachment creation.
- **DMY-069** — Featured-image assignment obeys post/media ownership and probability/seed deterministically.
- **DMY-070** — Alt/caption/description synthetic text is sanitized and accessibility-oriented profile is preserved.
- **DMY-071** — SVG generation/import is allowed only under Media security profile; active script/external reference payload is rejected.
- **DMY-072** — Remote placeholder provider is opt-in, Safe-HTTP bounded and stores terms/attribution/provenance; provider response is not deterministic by default.
- **DMY-073** — Remote provider failure falls back to local asset only when Dataset explicitly permits fallback; otherwise item is failed/skipped truthfully.
- **DMY-074** — Broken/missing media fixtures are available only in negative-test profile and cannot contaminate normal demo Dataset silently.
- **DMY-075** — Media byte/file quota stops generation before unbounded disk use and records actual bytes separately from estimate.
- **DMY-076** — Cleanup deletes only files/attachments owned by the Generation Run and rechecks that file path/attachment identity has not been reassigned.
- **DMY-077** — Backup/restore/clone of generated media preserves or remaps fixture ownership explicitly; identical filename is never proof of same generated identity.

## Group 8 — localization, Unicode, RTL and synthetic PII safety — DMY-078…088

- **DMY-078** — Locale-specific names/addresses are generated from synthetic locale packs with version provenance and no scraped personal dataset.
- **DMY-079** — RTL Arabic/Hebrew content preserves directionality/Unicode without being normalized into corrupted visual order.
- **DMY-080** — Combining marks, emoji, surrogate/Unicode edge values stay valid under field length/DB charset constraints.
- **DMY-081** — Duplicate display names can represent distinct synthetic identities with stable UUIDs; name equality never merges users/entities.
- **DMY-082** — Very long/short international names exercise limits while owner validation decides acceptance.
- **DMY-083** — Reserved/example email domains are enforced for default synthetic email profile so no real recipient can be contacted inadvertently.
- **DMY-084** — Synthetic phone profile uses designated non-routable/test values where available and is marked when no jurisdiction-safe guarantee exists.
- **DMY-085** — Address/geo fixture is labeled fictional/generated and cannot be presented as verified postal/geocoded truth without F11 evidence.
- **DMY-086** — Synthetic business/company data avoids trademarks/real-company identity unless explicit public-demo reference policy allows named fixtures.
- **DMY-087** — HTML/script-looking PII-like strings remain data and are escaped/sanitized in admin reports, logs and exports.
- **DMY-088** — Privacy export/cleanup report distinguishes synthetic provenance from real user data while still applying configured storage/access controls.

## Group 9 — deterministic seeds, reproduction and provider versions — DMY-089…099

- **DMY-089** — Same Dataset revision + seed + schema + provider versions reproduces logically equivalent entity/value ordering under certified deterministic profile.
- **DMY-090** — Different seed produces intentionally different generated values while preserving Dataset constraints and entity counts.
- **DMY-091** — Generator revision change changes reproduction fingerprint and cannot claim equivalence with older Run automatically.
- **DMY-092** — Locale-pack version is pinned in Run provenance; locale update invalidates strict byte/value reproduction claim.
- **DMY-093** — Target schema fingerprint mismatch before replay/regeneration triggers compatibility review rather than forcing old values into new schema.
- **DMY-094** — External random/image/provider input marks Run nondeterministic and records provider/version/reference; exact reproduction is not claimed.
- **DMY-095** — Deterministic UUID/sequence provider is scoped by Dataset/Run/owner namespace to avoid collisions with another Run/site.
- **DMY-096** — Pause/resume preserves PRNG/sequence state so resumed Run does not reorder/duplicate remaining deterministic entities.
- **DMY-097** — Retry of a failed item reuses its stable generation identity/seed position rather than creating a second logical entity.
- **DMY-098** — Export/import of Dataset preserves seed/provider/version declarations but excludes Vault/test credentials.
- **DMY-099** — Reproduction report states exact, logical-equivalent or nondeterministic class explicitly; visual similarity alone is not evidence.

## Group 10 — scenarios, Solution Blueprints and domain adapters — DMY-100…110

- **DMY-100** — Small-blog scenario resolves required post/term/user/media generators and validates dependency graph before Run.
- **DMY-101** — CRM 10k-lead scenario composes canonical fields/relations/statuses instead of creating a private CRM schema.
- **DMY-102** — Membership scenario generates plans/enrollments through Membership fixture APIs but makes no billing/provider/entitlement claim beyond synthetic owner facts.
- **DMY-103** — Woo store scenario uses certified WCA fixture abilities for products/variations/customers/orders and never direct HPOS/private-table writes.
- **DMY-104** — Woo synthetic order cannot capture/refund real payment, send provider webhook or create real shipment; all external effects are mock/stub/off.
- **DMY-105** — Reservation concurrency scenario uses F06 fixture contract and cannot become live capacity reservation outside isolated dataset.
- **DMY-106** — Search benchmark scenario records corpus/generator provenance and keeps search result quality evidence separate from generation success.
- **DMY-107** — Broken-link/redirect stress scenario generates local known-bad URLs without making uncontrolled external network requests during generation.
- **DMY-108** — Solution Blueprint scenario reuses current accepted definitions/modules/adapters and rejects missing dependency instead of inventing unsupported private primitive.
- **DMY-109** — Domain adapter disabled between Dataset validation and Run pauses/blocks dependent generators rather than falling back to raw storage.
- **DMY-110** — Scenario import from another site remaps owner/site IDs explicitly and quarantines environment/provider-sensitive references.

## Group 11 — volume profiles, jobs, checkpoints and backpressure — DMY-111…121

- **DMY-111** — XS/S/M/L/XL built-in profile resolves bounded per-entity target counts and exposes projected rows/files/bytes/jobs before execution.
- **DMY-112** — Custom count above configured safety limit requires explicit privileged profile or is rejected before queuing.
- **DMY-113** — Large Run uses JobService durable identity/checkpoint and queued status is not reported as generated/completed.
- **DMY-114** — Batch checkpoint advances only after generated entities and ownership journal are durably committed.
- **DMY-115** — Crash after entity commit but before checkpoint reconciles ownership identity before replay to avoid duplicate generation.
- **DMY-116** — Pause/resume preserves deterministic seed/cursor/dependency state and revalidates schema before continuing.
- **DMY-117** — Cancellation stops future batches at safe boundary and does not claim already generated entities were cleaned up.
- **DMY-118** — Queue backpressure caps pending work/memory/disk growth during 100k/1M generation profiles.
- **DMY-119** — Per-site/principal/global quotas prevent one Dataset/site from monopolizing shared job/storage budget.
- **DMY-120** — Progress reports planned/generated/failed/skipped/verified entities and bytes separately; partial completion is not rounded to success.
- **DMY-121** — Performance claims require recorded hardware/software/schema/profile and actual executed benchmarks; paper volume definitions remain planning only.

## Group 12 — generated-data identity, cleanup and regeneration — DMY-122…132

- **DMY-122** — Every generated entity/file has durable Run ownership reference without relying solely on user-visible meta/value patterns.
- **DMY-123** — Cleanup preview `by run` selects only identities owned by that Run and produces dependency/order impact before deletion.
- **DMY-124** — Cleanup `by dataset family` includes only accepted related Run identities and cannot match unrelated records by name/tag alone.
- **DMY-125** — Age-based cleanup applies only to generated ownership records older than cutoff and respects owner retention/legal hold.
- **DMY-126** — Detach/retain selected generated record removes or updates fixture ownership explicitly so later cleanup cannot delete retained real-use record accidentally.
- **DMY-127** — Cleanup uses owning module APIs and cannot direct-delete protected rows just because generator originally created them.
- **DMY-128** — Cleanup failure records residual identities and partial state; retry targets unresolved owned items only.
- **DMY-129** — Regeneration flow performs cleanup preview/approved cleanup then new Run with selected seed/version; it is not a Reset Manager full-site wipe.
- **DMY-130** — Generated record modified by real user after generation is detected by owner policy and can require detach/review rather than blind cleanup.
- **DMY-131** — External/mock/provider artifacts are listed separately and local cleanup cannot claim remote deletion without provider evidence.
- **DMY-132** — Post-cleanup verification reports residual generated identities, broken references and owner-specific failures rather than “clean” from attempted count alone.

## Group 13 — negative and adversarial datasets — DMY-133…143

- **DMY-133** — Null/empty/max-length boundary pack is explicitly marked negative and applies only to selected schemas/fields.
- **DMY-134** — Unicode/emoji/RTL/combining-character pack exercises validation/storage/rendering without introducing invalid DB encoding silently.
- **DMY-135** — Script/HTML/SQL-looking strings remain inert data and cannot execute through generator, logs, previews or exports.
- **DMY-136** — Invalid URL/date/timezone values are injected only where corruption-test harness permits bypass and never through ordinary owner API silently.
- **DMY-137** — Deep nested JSON/serialized payload respects depth/size caps and cannot create parser/recursive exhaustion.
- **DMY-138** — Duplicate-candidate dataset intentionally creates collisions under controlled schema and records expected reject/conflict outcomes.
- **DMY-139** — Relation cardinality/cycle/orphan violations are isolated to explicit negative owner profile; ordinary relation API remains authoritative.
- **DMY-140** — Permission-denied actor fixtures validate access behavior without actually broadening actor capabilities.
- **DMY-141** — Cross-site reference attack fixture proves site A generated identifiers cannot bind site B resources through client-supplied IDs.
- **DMY-142** — Import/version mismatch fixture remains a test artifact and cannot auto-migrate canonical definitions without owning migration policy.
- **DMY-143** — Known-dangerous values resembling API keys/card numbers/passwords use synthetic documented formats and are never valid real credentials.

## Group 14 — REST, Abilities, MCP, CLI and AI — DMY-144…154

- **DMY-144** — REST lists generator capabilities/schema for actor-visible modules only and excludes protected provider/Vault configuration.
- **DMY-145** — REST creates Dataset Draft and estimate without generating data.
- **DMY-146** — Start Run Ability requires capability, target site/tenant Policy, Dataset revision/fingerprint and production guard approval.
- **DMY-147** — Pause/resume/cancel Ability operates on actor-authorized Run only and cannot retarget another tenant’s Run ID.
- **DMY-148** — Cleanup preview Ability is read-only; cleanup mutation requires separate capability/approval and exact ownership scope.
- **DMY-149** — MCP exposes fixture tools only when opt-in and current principal has equivalent WordPress Ability permission.
- **DMY-150** — MCP/AI request for real credentials/payment/email/SMS provider execution is rejected; fixture mode cannot smuggle live side effects.
- **DMY-151** — AI Prompt compiles typed Dataset/Scenario Draft and hallucinated generator/provider options fail deterministic validation.
- **DMY-152** — Prompt injection embedded in generated content/scenario source remains untrusted and cannot alter tool allowlist/approval.
- **DMY-153** — CLI noninteractive Run requires explicit environment/site/Dataset revision and guard flags; saved profile alone is not production consent.
- **DMY-154** — Audit attribution records actor/channel/Run while AI agent metadata is not treated as authentication/authorization authority.

## Group 15 — Multisite, global users and site lifecycle — DMY-155…165

- **DMY-155** — Site-owned Dataset can create only resources inside server-resolved target site unless explicit network authority is granted.
- **DMY-156** — Network template instantiation produces separate per-site Runs/ownership IDs and does not share generated record identity across sites.
- **DMY-157** — Global user generation distinguishes global user object from site role/membership and applies both permission scopes explicitly.
- **DMY-158** — Same seed on two sites can reproduce equivalent values while stable generated identities remain site/run namespaced.
- **DMY-159** — Cross-site relation is rejected unless owning Relation schema explicitly supports/network-authorizes it.
- **DMY-160** — Site deletion/deactivation fences active generation/cleanup jobs before lifecycle removal and leaves truthful unresolved state.
- **DMY-161** — Site clone chooses preserve-seed vs regenerate-identities profile explicitly; production credentials/provider endpoints remain quarantined.
- **DMY-162** — Network cleanup enumerates sites/Runs and applies per-site Policy; network actor metadata visibility does not grant all record mutation automatically.
- **DMY-163** — Global table/user cleanup cannot be driven by site-only Dataset ownership without network/global authority.
- **DMY-164** — Large-network 10/100/1k/10k-site benchmark profiles include fairness/backpressure and are unexecuted until explicit evidence run.
- **DMY-165** — Site transfer/domain change preserves generated ownership references by stable site/environment mapping rather than current URL string matching.

## Group 16 — production guards, no real provider effects, performance and recovery — DMY-166…176

- **DMY-166** — Production environment defaults to generation blocked or privileged warning profile exactly as configured; environment label alone is not sufficient if identity is ambiguous.
- **DMY-167** — Real email/SMS/payment/refund/shipping/external webhook calls remain disabled/mocked even when generated record enters state that normally triggers them.
- **DMY-168** — Provider adapter must explicitly declare fixture/sandbox mode; absence of certified sandbox blocks external call rather than silently using live account.
- **DMY-169** — 10k/100k/1M entity performance evidence records CPU/memory/DB/files/jobs and no paper estimate is called certified throughput.
- **DMY-170** — Disk/database quota exhaustion produces partial truthful Run and preserves ownership journal for cleanup/recovery.
- **DMY-171** — Unique collision/retry storm is bounded and cannot spin indefinitely or generate untracked entities.
- **DMY-172** — Module/schema disable/drift mid-Run pauses dependent generator and does not bypass owner validation to hit target counts.
- **DMY-173** — Backup/restore re-establishes local generated identities but invalidates active job/checkpoint when environment/schema state no longer matches.
- **DMY-174** — Security regression covers known-password creation, real-contact leakage, cross-site references, cleanup of real data and provider side-effect escape.
- **DMY-175** — AI/MCP/provider outage leaves manual Dataset definitions intact and cannot turn incomplete generation into verified success.
- **DMY-176** — Golden end-to-end QA Dataset covers definitions → deterministic seed → dependencies → generation → relation/media/status verification → cleanup/regeneration with zero real provider effects and explicit partial/recovery truth.

## Stop-the-line conditions

Certification stops on deletion of non-generated data, real provider/payment/message side effects, real secrets/known insecure admin credentials, bypass of owning APIs, cross-site leakage, scraped real-person data, false determinism claims or AI/MCP generation/cleanup approval bypass.

## Execution gate

All 176 fixtures are documented only. No data generation, WordPress mutation, media/provider call, cleanup, test, benchmark, AI/MCP session or build has executed. ADR-0014 consent remains mandatory.