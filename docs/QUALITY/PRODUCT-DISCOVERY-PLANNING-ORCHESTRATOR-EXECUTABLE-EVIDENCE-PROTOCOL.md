# WPEssential — Product Discovery & Planning Orchestrator Executable Evidence Protocol

Status: **Exact planning evidence / NOT EXECUTED / no development authorization**  
Date: 2026-08-29  
Work package: **WP113**  
Namespace: **PDO-001…PDO-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## Purpose

Freeze exact future evidence for S07 Product Discovery & Pre-development Planning Orchestrator. The protocol preserves the 16 canonical groups from the market-expansion evidence master plan and `AI/AUTONOMOUS-PRODUCT-DISCOVERY-PLANNING-ORCHESTRATOR.md`.

## Truth boundaries

- Planning output ≠ development approval.
- Competitor popularity ≠ architecture authority.
- Source fact, market fact, user statement, inference and WPE decision are distinct provenance classes.
- Repository current-state evidence overrides conversational memory when they conflict.
- Planning automation may draft canonical changes but cannot auto-merge production/source changes or grant consent.
- Unsupported requirements must remain explicit gaps/rejections/unknowns; they cannot be silently omitted.
- AI-generated PlanningIR/prose is non-authoritative until deterministic validation and governance acceptance.
- Connected repository/provider/MCP access is bounded by explicit authorization and cannot expose secrets/private data beyond task scope.

---

## Group 1 — intent recognition — PDO-001…011

- **PDO-001** — Minimal owner request “ABC system add karna hai” is classified as `ADD_SYSTEM` without treating the phrase as development consent.
- **PDO-002** — “Is plugin ko audit karo” with a comparator/source is classified `AUDIT_COMPETITOR`/`AUDIT_SOURCE_URL` rather than new-module approval.
- **PDO-003** — “Existing Membership me ye option add karo” resolves `EXPAND_EXISTING_MODULE` and preserves current module ownership before proposing new surface.
- **PDO-004** — “New shared capability chahiye jo multiple modules use karen” can resolve `ADD_FOUNDATION`/shared service after reuse test; classifier records confidence/inference.
- **PDO-005** — Request for a vendor/API integration is classified `ADD_ADAPTER` when source truth remains external and no new domain engine is needed.
- **PDO-006** — “Market research karo” remains `MARKET_RESEARCH`; it cannot mutate product scope automatically.
- **PDO-007** — “Current plan ko latest WordPress change ke against check karo” resolves `REASSESS_SCOPE`/`UPDATE_EXISTING_PLAN` and begins from canonical state.
- **PDO-008** — Ambiguous request containing both module and system language produces explicit candidate intents/confidence and preserves unresolved business semantics rather than guessing silently.
- **PDO-009** — Intentionally unsafe request for arbitrary PHP/eval capability is recognized but can resolve `SECURITY_MODEL_REJECTS` later; intent recognition does not sanitize away requested behavior from audit trail.
- **PDO-010** — User-provided constraints/URLs/files are attached to request identity without being treated as verified external facts automatically.
- **PDO-011** — Replayed identical request uses stable request/idempotency identity where configured and does not create duplicate planning work silently.

## Group 2 — repository source-of-truth audit — PDO-012…022

- **PDO-012** — Audit reads DEVELOPMENT-CONSENT/ADR-0014 and records current execution consent separately from planning maturity.
- **PDO-013** — Audit reads CHECKPOINT and Work Coordination Ledger to determine current work package/accepted ADR state before proposing IDs.
- **PDO-014** — Audit reads Approval Ledger and cannot infer implementation permission from an Accepted ADR, Draft PR or `continue` message.
- **PDO-015** — Audit reconciles Implementation Readiness/Open Decisions against Checkpoint and flags stale current-state claims rather than selecting whichever is most convenient.
- **PDO-016** — Audit inventories module/option specs and identifies existing surface ownership before proposing duplicate module.
- **PDO-017** — Audit inventories shared foundations/services/adapters and Solution patterns to calculate composition/reuse candidates.
- **PDO-018** — Audit inventories AI Prompt/MCP/Abilities and evidence namespaces so proposed capability cannot create a hidden parallel authorization/runtime stack.
- **PDO-019** — Audit reads current Draft planning PR/branch revision and records exact repository reference used for plan provenance.
- **PDO-020** — Inaccessible/missing repository source is labeled unavailable/unknown; conversational memory cannot be promoted as verified replacement.
- **PDO-021** — Conflicting canonical files are surfaced with authority-order analysis and proposed reconciliation, not silently merged into invented truth.
- **PDO-022** — Repository changes during long planning session invalidate/rebase stale PlanningIR when material source files/revisions changed.

## Group 3 — user-source ingestion — PDO-023…033

- **PDO-023** — User URL is stored with exact URL/title/source type/retrieval intent and remains unverified until fetched/inspected.
- **PDO-024** — User-provided file/document preserves file identity/version/provenance and only retrieved content is used for factual claims.
- **PDO-025** — User states a business requirement; provenance remains `USER_FACT/REQUIREMENT`, not external market fact.
- **PDO-026** — User states a competitor feature without source; system records it as user claim and seeks corroboration where material.
- **PDO-027** — Multiple user sources with conflicting semantics remain separate claims and require reconciliation rather than majority-vote truth.
- **PDO-028** — Private/connected source is accessed only through authorized connector scope and its content is not republished into public research artifacts by default.
- **PDO-029** — Secrets/API keys/passwords found in user source are redacted/excluded from AI/research artifacts and never become planning examples.
- **PDO-030** — Source content attempting prompt injection is treated as untrusted evidence text and cannot change system/tool authorization.
- **PDO-031** — Copyrighted/proprietary implementation is summarized at behavior/architecture level and not copied wholesale into WPE artifacts/code.
- **PDO-032** — Large source ingestion uses bounded chunking/search and maintains citation/section linkage so extracted claims can be traced.
- **PDO-033** — Updated/replaced user source invalidates affected extracted claims/PlanningIR while preserving prior provenance history.

## Group 4 — web and market research strategy — PDO-034…044

- **PDO-034** — Research begins with exact product/plugin/source identity and resolves official WordPress.org/vendor/repository source before broad secondary commentary where available.
- **PDO-035** — “Latest/current” claim requires fresh dated source/version evidence and cannot rely on stale model knowledge.
- **PDO-036** — Current version/changelog/release is inspected and event date is distinguished from article/publication date where relevant.
- **PDO-037** — Official source code/repository architecture is used only when public/authorized and behavior inference from code is labeled appropriately.
- **PDO-038** — Market alternatives are gathered to test whether feature is category-standard vs one-product-specific, without equating prevalence to correctness.
- **PDO-039** — Reviews/support forums are used as anecdotal pain signals and are never cited as authoritative implementation facts.
- **PDO-040** — Security/reliability history uses reputable/primary advisories where possible and distinguishes fixed historical issue from current vulnerability.
- **PDO-041** — WordPress core/developer docs/dev notes are checked for native capability that should be reused instead of duplicated.
- **PDO-042** — Standards/RFC/provider docs are consulted for externally governed protocol semantics and WPE does not invent conflicting local behavior.
- **PDO-043** — Research breadth/time/source budget is bounded and insufficient evidence produces `UNKNOWN_REQUIRES_EVIDENCE`, not fabricated certainty.
- **PDO-044** — Network/source failure is recorded explicitly and planning output identifies which claims remain unverified because research could not complete.

## Group 5 — provenance, freshness and citations — PDO-045…055

- **PDO-045** — Every external feature claim links to source reference plus source type/publisher/retrieval date/version when known.
- **PDO-046** — Source fact is labeled separately from WPE inference/proposed decision in research output.
- **PDO-047** — User review/support anecdote is labeled anecdotal and cannot become “users universally need X.”
- **PDO-048** — Active-install/download/review counts are used only if public source actually exposes them and retrieval date is recorded; missing data stays unknown.
- **PDO-049** — Freshness policy marks stale source and triggers refresh before a “current/latest” decision.
- **PDO-050** — Conflicting official sources remain unresolved with both citations until hierarchy/version/context explains difference.
- **PDO-051** — Source URL moved/removed later does not erase retained citation metadata/history; claim status can become unavailable/stale.
- **PDO-052** — Secondary source claim cannot be promoted to canonical plan when primary verification is required but unavailable, unless explicitly accepted as inference.
- **PDO-053** — Code/repository observation records exact revision/tag where material so later changes do not rewrite historical audit meaning.
- **PDO-054** — Citations in generated prose map to PlanningIR claim IDs so text generation cannot detach claim from its evidence.
- **PDO-055** — Citation/provenance export never includes connector tokens, private credentials or source content beyond configured sharing rights.

## Group 6 — competitor capability extraction — PDO-056…066

- **PDO-056** — Competitor feature is decomposed into user-visible behavior, underlying ownership/data/runtime assumptions and integration points rather than copied by label.
- **PDO-057** — Admin screens/workflow observations distinguish documented behavior from inferred implementation internals.
- **PDO-058** — Public API/CLI/REST capability is recorded with version/profile and no unsupported API is invented from UI presence.
- **PDO-059** — Multisite behavior is marked supported/unsupported/unknown only from evidence, not assumed because plugin runs on WordPress.
- **PDO-060** — Permission/security model is extracted explicitly; UI hiding is not mistaken for authorization.
- **PDO-061** — Import/export/migration features are separated into format, ownership, destructive/merge semantics and recovery truth.
- **PDO-062** — Performance claim from marketing text is labeled vendor claim until independent/runtime evidence exists.
- **PDO-063** — Privacy/logging/provider behavior records externally transferred data/retention where documented and marks unknowns instead of guessing.
- **PDO-064** — Free/Pro/commercial packaging is recorded as market fact separately from WPE architecture/product decision.
- **PDO-065** — Known limitation/issue theme is linked to source and does not become a universal defect or copied workaround automatically.
- **PDO-066** — Capability extraction never copies proprietary source implementation/code; only lawful behavior/API/architecture observations feed PlanningIR.

## Group 7 — WPE capability and dedupe map — PDO-067…077

- **PDO-067** — Exact existing option maps `ALREADY_SUPPORTED_DIRECTLY` with owning surface/spec/evidence references.
- **PDO-068** — Requirement achievable through two or more existing primitives maps `SUPPORTED_BY_COMPOSITION` with explicit dependency/order contracts.
- **PDO-069** — Missing small behavior inside coherent existing owner maps `SUPPORTED_BY_EXISTING_OPTION_EXPANSION`, not new module.
- **PDO-070** — Truly distinct reusable ownership/lifecycle/UI capability can map `NEEDS_NEW_MODULE` only after reuse test passes.
- **PDO-071** — Cross-cutting reusable primitive maps `NEEDS_SHARED_SERVICE` and cannot be hidden inside one module just for convenience.
- **PDO-072** — External domain/source-truth integration maps `NEEDS_DOMAIN_ADAPTER` rather than duplicating source system as WPE engine.
- **PDO-073** — Adapter behavior requiring concrete vendor profile maps `NEEDS_PROVIDER_CERTIFICATION`; interface design alone is not support claim.
- **PDO-074** — Legal/identity/payment/geo/external fact requiring third-party authority maps `NEEDS_EXTERNAL_AUTHORITY` and cannot be fabricated locally.
- **PDO-075** — Arbitrary code/unsafe primitive maps `SECURITY_MODEL_REJECTS` with safer typed alternative where possible.
- **PDO-076** — Capability with insufficient evidence maps `UNKNOWN_REQUIRES_EVIDENCE`; it cannot be silently dropped from coverage percentage.
- **PDO-077** — Dedupe score/reuse map cannot merge two capabilities that merely share labels but have distinct source-truth/lifecycle/permission ownership.

## Group 8 — module, service, adapter and Blueprint classification — PDO-078…088

- **PDO-078** — New module proposal demonstrates independent reusable user value, coherent ownership, lifecycle, permissions and UI before denominator expansion.
- **PDO-079** — Shared service proposal demonstrates multiple consumer surfaces and no user-facing domain truth ownership of its own.
- **PDO-080** — Domain adapter proposal keeps external system as authority and documents typed translation/reconciliation boundaries.
- **PDO-081** — Provider profile proposal remains under existing adapter/service and does not inflate module count for each vendor.
- **PDO-082** — Solution Blueprint proposal composes existing primitives and is not treated as a private plugin/runtime engine.
- **PDO-083** — Developer tool classification is used when capability is administrative/engineering support rather than product business domain.
- **PDO-084** — Option extension preserves existing owner and migration semantics; it cannot quietly fork a second storage/permission model.
- **PDO-085** — Rejected/not-product-fit capability remains documented with rationale so future audits do not repeatedly rediscover it as missing.
- **PDO-086** — Classification change from prior accepted plan requires explicit diff/ADR proposal rather than rewriting historical decision silently.
- **PDO-087** — Monetization/Free-Pro fit is evaluated after architecture classification and cannot override safety/ownership just to create sellable module.
- **PDO-088** — Architecture classification output includes confidence/open questions and does not present heuristic score as final authority.

## Group 9 — exhaustive option and flow generation — PDO-089…099

- **PDO-089** — Generated module/system plan includes identity/navigation/list screens/editor sections and every known option/default from accepted evidence.
- **PDO-090** — Validation/sanitization/normalization rules are generated per option type and no free-form executable callback is invented.
- **PDO-091** — Roles/capabilities/Policy and protected-resource access are documented separately from UI visibility.
- **PDO-092** — Data objects/source truth/ownership/storage and lifecycle states/transitions are explicit before implementation-ready claim.
- **PDO-093** — Happy path, alternate paths, denied states, partial failure, retries and recovery are all generated where meaningful.
- **PDO-094** — List search/filter/sort/bulk actions and empty/loading/error/degraded/read-only states are included rather than omitted as “UI details.”
- **PDO-095** — Import/export/revisions/versioning/migration/uninstall/disable behavior is documented before development.
- **PDO-096** — Jobs/cache/concurrency/idempotency/provider-unknown outcomes are generated for async/external capabilities rather than deferred to implementation.
- **PDO-097** — REST/Abilities/MCP/CLI and AI Prompt behavior is mapped only where applicable and cannot create hidden higher privilege path.
- **PDO-098** — Performance/scale budgets and destructive safeguards/rollback classes are planned with evidence namespaces, not optimistic prose.
- **PDO-099** — Unsupported/unresolved options remain explicit gaps/open questions and prevent false “exhaustive” completion where material.

## Group 10 — security, privacy, Multisite and lifecycle generation — PDO-100…110

- **PDO-100** — Threat-boundary pass identifies untrusted inputs, authorization checks, secret handling, SSRF/code/SQL/file risks and data ownership for new capability.
- **PDO-101** — Privacy pass classifies PII/secrets/logs/exports/retention/external transfer and avoids storing full sensitive payloads by default.
- **PDO-102** — Multisite pass defines site/network/global ownership and rejects current-blog/request-supplied site ID as durable authority.
- **PDO-103** — Site lifecycle covers clone/transfer/delete/archive/restore and provider/environment identifier quarantine where applicable.
- **PDO-104** — Module lifecycle covers enable/disable/expiry/uninstall and confirms disable does not silently delete data unless explicit policy.
- **PDO-105** — Backup/restore/recovery planning distinguishes local artifacts from external provider facts that cannot be rolled back locally.
- **PDO-106** — Concurrency/idempotency planning distinguishes queued/sent/accepted/unknown/reconciled states for external operations.
- **PDO-107** — AI/MCP security pass ensures prompt injection/tool misuse cannot broaden tools, bypass Policy or approve high-risk mutation.
- **PDO-108** — Compatibility pass identifies WordPress/PHP/provider/browser/DB/API version assumptions and evidence needed before support claim.
- **PDO-109** — Accessibility/UI pass covers keyboard/focus/semantic/status/error behavior where user-facing surfaces are added.
- **PDO-110** — Missing security/privacy/Multisite/lifecycle section is a planning-blocker, not a detail to leave for developers.

## Group 11 — evidence, ADR and work-package proposal — PDO-111…121

- **PDO-111** — New/expanded capability receives stable evidence namespace proposal with explicit owner and no collision with existing IDs.
- **PDO-112** — Evidence plan defines concrete groups/fixture intent and distinguishes documentation from executed/runtime certification.
- **PDO-113** — Security/compatibility/provider/runtime evidence dependencies are linked instead of duplicating existing canonical protocols.
- **PDO-114** — Proposed ADR states decision, context, alternatives, consequences, supersession/non-duplication and development-consent boundary.
- **PDO-115** — Historical ADRs are never silently edited to change accepted semantics; reversal uses superseding ADR.
- **PDO-116** — Work package receives stable WP ID/sequence without repurposing completed work IDs.
- **PDO-117** — Work package scope lists exact files/namespaces/counters/stop conditions and separates planning tasks from runtime tasks.
- **PDO-118** — Evidence counter can be `documented N/N, executed 0/N`; zero execution alone cannot be mislabeled planning gap.
- **PDO-119** — Provider certification requirements remain separate from generic adapter evidence and owner consent.
- **PDO-120** — Proposed milestone/closure criteria cannot automatically advance lifecycle merely because generated docs exist.
- **PDO-121** — Owner-requested new product scope is clearly marked as proposal until governance acceptance; planning automation cannot self-approve denominator expansion.

## Group 12 — canonical synchronization plan generation — PDO-122…132

- **PDO-122** — Sync plan identifies which canonical current-state files must change and which historical snapshots should remain untouched.
- **PDO-123** — CHECKPOINT/current work ledger/readiness/approval/open decisions/ADR index updates are generated consistently from one accepted state delta.
- **PDO-124** — Draft PR body is updated to reflect exact scope/counters/current WP without marking PR ready/merged automatically.
- **PDO-125** — Linear/project planner mirror is updated only as secondary mirror; GitHub canonical state remains authority when mismatch exists.
- **PDO-126** — File update requires current blob/revision precondition and detects concurrent edit instead of overwriting newer planning work.
- **PDO-127** — New file path/name is checked for collision/duplicate canonical source before creation.
- **PDO-128** — Current-state supersession/addendum is preferred over rewriting a long historical master file when historical snapshot must remain auditable.
- **PDO-129** — Generated counts/denominators are validated arithmetically and stale denominator references in current summaries are flagged.
- **PDO-130** — Cross-file ownership links are validated so module catalog/spec/evidence/ADR point to one canonical owner rather than conflicting duplicate engines.
- **PDO-131** — Failed partial synchronization leaves explicit unsynced files/planner items and cannot report planning package fully synchronized.
- **PDO-132** — Re-running same accepted sync plan is idempotent or conflict-detected and cannot append duplicate ADR/work entries endlessly.

## Group 13 — owner review and acceptance controls — PDO-133…143

- **PDO-133** — Draft planning package remains clearly `DRAFT/PROPOSED` until configured owner/governance acceptance occurs.
- **PDO-134** — Owner can accept a planning decision without granting production development consent; the two approvals remain distinct records.
- **PDO-135** — Owner can reject one proposed new module while accepting research/option expansions; partial decision scope is preserved.
- **PDO-136** — Material unresolved business semantics are presented for owner decision instead of guessed by automation.
- **PDO-137** — Routine low-risk documentation completion may be auto-drafted but new sellable surface/security authority/external-truth change remains review-required.
- **PDO-138** — Acceptance binds exact plan/ADR/revision/fingerprint; materially changed proposal requires new review.
- **PDO-139** — Owner approval revocation/supersession is recorded durably and future automation cannot rely on old approval.
- **PDO-140** — “continue/resume” advances planning only under current PLANNER_ONLY state and cannot be interpreted as implementation approval.
- **PDO-141** — Planning completion can move to `AWAITING_DEVELOPMENT_APPROVAL` only after closure criteria pass; transition never starts implementation automatically.
- **PDO-142** — Development approval, once explicitly requested/granted, is scoped separately by module/milestone/risk under Approval Ledger.
- **PDO-143** — Audit trail records owner/reviewer decision provenance without AI-agent metadata being treated as owner identity.

## Group 14 — AI, MCP and repository-connector authorization — PDO-144…154

- **PDO-144** — AI context contains only task-required authorized repository/source data and excludes Vault/secrets/private unrelated content.
- **PDO-145** — AI structured output validates against PlanningIR schema; malformed/unknown fields cannot drive canonical writes.
- **PDO-146** — Model hallucinated file/ADR/module/source is rejected by repository/catalog lookup before sync proposal.
- **PDO-147** — Prompt injection from README/research/web/user source cannot alter system instructions, connector scope, approval or tool allowlist.
- **PDO-148** — MCP discovery exposes read/planning-draft abilities only as configured and honors current WordPress principal permissions.
- **PDO-149** — MCP/AI cannot invoke production source write/build/deploy/provider operations through S07 merely because repository connector supports writes.
- **PDO-150** — Repository write action is limited to planning-approved paths/branch/PR profile and cannot modify runtime/source directories under PLANNER_ONLY.
- **PDO-151** — Private repository/connector content is not sent to external web research source or public artifact without explicit authorized data-flow policy.
- **PDO-152** — Tool failure/permission denial remains explicit and cannot be replaced by fabricated successful repository/provider state.
- **PDO-153** — AI provider/model change is recorded in planning-session provenance where generated analysis depends materially on it; deterministic validators remain authoritative.
- **PDO-154** — AI/MCP channel attribution is audit context only; owner consent and repository authorization still come from canonical governance/principal checks.

## Group 15 — VCS, concurrency, replay and idempotency — PDO-155…165

- **PDO-155** — Planning session pins base branch/revision; material head movement requires re-read/rebase before writing canonical state.
- **PDO-156** — Concurrent planners proposing same ADR/WP/namespace detect ID/path collision and reconcile instead of creating duplicates.
- **PDO-157** — Concurrent edits to same governance file use blob/revision precondition and never overwrite newer content silently.
- **PDO-158** — Same planning request/research candidate replay reuses stable identity/dedupe where configured and does not open duplicate Draft PR/issues endlessly.
- **PDO-159** — Partial write failure records successfully created files/commits and remaining sync work so retry is idempotent.
- **PDO-160** — Draft PR remains draft throughout pre-development planning unless owner separately requests review-state transition.
- **PDO-161** — Planner never merges its own planning PR automatically under default policy.
- **PDO-162** — Branch protection/ruleset state that connector cannot verify remains `UNKNOWN`; automation cannot weaken protections or claim none exist.
- **PDO-163** — VCS history preserves historical accepted decisions; force-push/destructive history rewrite is not a default planning operation.
- **PDO-164** — Cross-system GitHub↔Linear synchronization records canonical GitHub reference and planner mismatch as mirror drift, not dual authority.
- **PDO-165** — Recovered session resumes from Checkpoint/current repository evidence rather than previous conversation memory if they differ.

## Group 16 — hallucination, copyright, secrets, failure and regression — PDO-166…176

- **PDO-166** — Invented competitor version/install count/API behavior is caught by provenance/freshness validation and cannot enter factual audit as source fact.
- **PDO-167** — Inaccessible source remains `UNAVAILABLE/UNKNOWN`; model summary memory cannot be cited as if fetched.
- **PDO-168** — Copyrighted proprietary code/document is summarized within allowed behavior/architecture scope and not reproduced wholesale in planning artifact.
- **PDO-169** — Secret/token/private credential encountered in repository/user source is redacted from prompts/logs/citations/PR/Linear artifacts.
- **PDO-170** — Network/research/provider outage yields partial research report with unresolved claims and no fabricated completion.
- **PDO-171** — Repository connector write failure leaves canonical state unchanged or partially changed with exact recovery list; it cannot announce sync success prematurely.
- **PDO-172** — Arithmetic/counter regression validates namespace count, scope denominator and remaining-work totals before updating governance.
- **PDO-173** — Architecture regression test detects duplicate owner/source-of-truth proposal against accepted module/foundation/adapter contracts.
- **PDO-174** — Security regression tests arbitrary-code proposal, authorization/UI confusion, AI self-approval, cross-site leakage and private-source exfiltration planning failures.
- **PDO-175** — Historical current-state drift audit confirms stale snapshots are either clearly historical/superseded or reconciled before readiness claim.
- **PDO-176** — Golden end-to-end “add system” fixture covers intent → repo audit → sources/research → capability/dedupe → classification → exhaustive plan → evidence/ADR/WP → owner review → canonical Draft sync and stops before development consent.

## Stop-the-line conditions

Certification stops on development starting from planning request, fabricated market/repository facts, source/provenance loss, copied proprietary code, secret leakage, duplicate architecture owner, canonical auto-merge, AI/MCP authorization bypass or false readiness/approval claim.

## Execution gate

All 176 fixtures are documented only. No web research automation, repository write automation execution, AI/MCP/provider call, scheduled job, runtime test, build or deployment has executed under this protocol. ADR-0014 remains mandatory.