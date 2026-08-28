# WPEssential — Ledger, Balance & Movement Executable Evidence Protocol

Status: **Accepted evidence design candidate / execution pending / no development authorization**  
Date: **2026-08-29**

Namespace: **LED-001…LED-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## 1. Purpose

This protocol fixes the executable evidence required before F05 — Ledger, Balance & Movement Engine can be called runtime-ready.

F05 owns durable, append-oriented movement history and derived balances only for explicitly configured ledger profiles. A ledger movement is not a payment-provider settlement, bank fact, inventory reservation, order, entitlement, accounting/legal assertion or authorization merely because the ledger records a related quantity. External facts remain authoritative at their source and require typed reconciliation.

Ledger history is corrected through explicit reversal, compensation or superseding movement semantics. Silent mutation/deletion of posted history is prohibited except for separately governed retention/privacy operations that preserve required financial/audit invariants.

No fixture below has executed. No table, posting engine, lock, transaction, provider call, balance rebuild, import, benchmark, AI/MCP call or runtime mutation is authorized by this protocol.

## 2. Non-negotiable truth boundaries

- `Movement ≠ Payment settlement / bank confirmation / inventory reservation / order / entitlement / Policy`.
- `Derived balance ≠ externally verified balance unless reconciliation says so`.
- `Hold ≠ final posting`; hold state and posted movement state are separate.
- `Reversal/compensation ≠ history deletion`.
- `Idempotency key ≠ authorization`.
- `Audit Log ≠ ledger truth`; the ledger stores canonical movement facts for its profile while Audit records operational history around them.
- `Snapshot/checkpoint ≠ canonical movement history`; rebuild from authoritative movements must remain possible for profiles that claim reconstructability.
- `Unknown external outcome ≠ failed`; reconciliation is required before replay.
- Binary floating point is not canonical money arithmetic.
- Currency/unit conversion requires explicit compatible semantics and provenance; a ledger must not invent exchange rates.
- Cross-site/tenant ownership is server-resolved and durable; request-supplied site IDs do not grant access.
- AI/MCP may draft/explain/reconcile plans only through Policy and approval gates; it cannot create privileged hidden postings.

## 3. Certification classes

- `LED-SCH` — ledger/account/movement schemas and lifecycle.
- `LED-IDM` — append identity, idempotency and source references.
- `LED-BAL` — debit/credit/quantity semantics and balance derivation.
- `LED-HLD` — holds/reservations/release/expiry.
- `LED-REV` — reversals, compensation, refund and void semantics.
- `LED-CON` — concurrency, locking and isolation.
- `LED-REC` — crash, partial failure, unknown outcome and reconciliation.
- `LED-RBL` — rebuild, snapshot and checkpoint integrity.
- `LED-NUM` — currency/unit/decimal/rounding correctness.
- `LED-GOV` — Policy, approvals, re-auth and manual adjustments.
- `LED-MIG` — import, migration, replay and duplicate-source safety.
- `LED-OBS` — Audit, privacy, retention and explainability.
- `LED-MSI` — Multisite/tenant/site lifecycle isolation.
- `LED-BCP` — backup, restore, clone and environment continuity.
- `LED-PERF` — scale, throughput and query performance.
- `LED-DET` — end-to-end golden and deterministic regression.

Passing one class never implies another. Runtime readiness requires every class applicable to the enabled ledger profile and consumer domain.

# Group 1 — Ledger/account/movement type schemas — LED-001…LED-011

- **LED-001** Valid Ledger Definition publishes with stable key, immutable revision identity and explicit ledger profile/type.
- **LED-002** Account schema declares stable account identity, owner/scope, asset/currency/unit semantics, status and allowed movement classes.
- **LED-003** Movement schema requires immutable movement identity, ledger/account references, amount/quantity type, direction/class, effective time and posting time.
- **LED-004** Unknown ledger/account/movement schema version is rejected as incompatible rather than silently coerced.
- **LED-005** Required metadata fields are type/range/enum validated before a posting plan can be accepted.
- **LED-006** Closed/frozen/archived account state blocks disallowed new postings while preserving historical reads and reconciliation.
- **LED-007** Account identity cannot be reassigned to a different owner/site/tenant through mutable display metadata.
- **LED-008** Ledger definition export/import preserves stable IDs, schema versions and references without posting history unless explicitly requested.
- **LED-009** Deleting/archiving a Ledger Definition performs dependency review and never silently deletes posted movement history.
- **LED-010** Movement effective time and recorded/posting time remain distinct fields when backdated/future-effective profiles are supported.
- **LED-011** Unsupported combination of ledger profile, asset type or movement class blocks publish/posting with actionable typed diagnostics.

# Group 2 — Append-only identity/idempotency/source references — LED-012…LED-022

- **LED-012** Successful posting creates one immutable movement identity and does not mutate an earlier posted movement in place.
- **LED-013** Repeating the same valid idempotency key with identical normalized payload returns the original posting result without duplication.
- **LED-014** Reusing an idempotency key with materially different payload is rejected as conflict and surfaces the original fingerprint safely.
- **LED-015** Idempotency scope includes the required ledger/site/tenant/operation dimensions so unrelated postings cannot collide.
- **LED-016** Source reference supports typed origin such as order/payment/import/workflow while remaining distinct from ledger movement identity.
- **LED-017** Duplicate external source event with a different transport/request ID is detected through configured source identity rules where available.
- **LED-018** Missing optional source reference is allowed only for movement classes whose profile explicitly permits manual/system-origin posting.
- **LED-019** Posting retry after client timeout first resolves idempotency/source state before attempting any new movement.
- **LED-020** Append sequence/order metadata is deterministic enough for replay and does not rely solely on wall-clock timestamps for uniqueness.
- **LED-021** Imported legacy movement identities are namespaced/provenanced so they cannot collide with native runtime identities.
- **LED-022** Ledger history API never exposes a client capability to overwrite immutable movement amount, account, source or posting identity.

# Group 3 — Debit/credit or quantity semantics/balance derivation — LED-023…LED-033

- **LED-023** Signed-quantity profile defines canonical direction semantics and rejects ambiguous double-negation between sign and movement type.
- **LED-024** Debit/credit profile enforces balanced-entry invariants when the selected ledger class requires paired/balanced postings.
- **LED-025** Single-account quantity profile derives balance strictly from accepted movement classes according to its documented equation.
- **LED-026** Opening balance is represented by an explicit opening/import movement or certified genesis state, not hidden mutable account metadata.
- **LED-027** Current balance query includes exactly the movement states configured as balance-affecting and excludes pending/void/failed states.
- **LED-028** Available balance distinguishes posted balance from active holds/reservations according to the selected profile.
- **LED-029** Negative balance/overdraft behavior follows explicit account policy and is enforced atomically with posting where required.
- **LED-030** Balance-at-time query uses effective/posting chronology defined by the profile and produces deterministic historical results.
- **LED-031** Multiple movements sharing the same timestamp resolve through stable sequence/identity ordering without balance flapping.
- **LED-032** Account transfer posts the required linked movements atomically or records no completed transfer when atomic semantics are claimed.
- **LED-033** Cached/materialized balance is treated as derived state and must reconcile to canonical movement history within declared guarantees.

# Group 4 — Holds/reservations/release/expiration — LED-034…LED-044

- **LED-034** Hold creation records immutable hold identity, account, quantity, reason/source, creation time and expiry policy.
- **LED-035** Active hold reduces available balance without pretending a final posted movement occurred.
- **LED-036** Duplicate hold request with the same idempotency identity returns the existing hold rather than reserving twice.
- **LED-037** Hold confirmation converts/links to the configured final posting exactly once and cannot be confirmed repeatedly.
- **LED-038** Hold release restores availability exactly once and preserves the released hold record/history.
- **LED-039** Hold expiry uses canonical time and an idempotent expiry transition so delayed jobs cannot release twice.
- **LED-040** Confirm-vs-expire race resolves atomically to one valid terminal state with no double spend or phantom availability.
- **LED-041** Partial hold capture is allowed only when the profile explicitly supports it and records remaining/released quantity deterministically.
- **LED-042** Hold extension requires authorized policy, bounded new expiry and conflict-safe versioning.
- **LED-043** Insufficient available balance prevents a new constrained hold under concurrency, not only during UI pre-check.
- **LED-044** Hold history remains queryable and explainable after confirmation/release/expiry without becoming duplicate posted balance impact.

# Group 5 — Compensation/reversal/refund/void truth — LED-045…LED-055

- **LED-045** Posted movement correction creates an explicit reversal/compensating movement linked to the original rather than editing original value.
- **LED-046** Full reversal negates the configured balance impact exactly once and records reason, actor/source and original reference.
- **LED-047** Partial reversal/refund is bounded by remaining reversible quantity and cannot cumulatively exceed the original posting unless profile explicitly permits over-adjustment.
- **LED-048** Repeated refund/reversal request is idempotent and cannot duplicate compensation.
- **LED-049** Void semantics apply only to eligible non-final/pending movement states and do not silently erase a completed posting.
- **LED-050** Reversal of a reversal follows an explicit new movement/restore policy and never mutates the prior reversal history.
- **LED-051** External provider refund status and internal ledger compensation are separately represented and reconciled; one does not falsely prove the other.
- **LED-052** Chargeback/dispute/adjustment movement classes retain typed provenance and do not masquerade as ordinary refund when semantics differ.
- **LED-053** Compensation posted after period/account closure follows explicit exceptional approval and historical effective-date policy.
- **LED-054** Original plus compensating movement chain yields the expected net balance and remains explainable at every step.
- **LED-055** Deleting a business source object does not delete its ledger reversal/refund history; references follow retention/redaction policy.

# Group 6 — Concurrent postings/locking/isolation — LED-056…LED-066

- **LED-056** Two concurrent unconstrained append postings both persist exactly once without lost movement records.
- **LED-057** Concurrent constrained debits against the same available balance cannot both succeed when together they exceed the limit.
- **LED-058** Lock/version scope is limited to required ledger/account resources and does not serialize unrelated accounts unnecessarily.
- **LED-059** Deadlock/serialization conflict follows bounded retry and preserves idempotency identity across retries.
- **LED-060** Transaction rollback after validation failure leaves no posted movement, balance delta or orphan linked entry.
- **LED-061** Multi-account atomic transfer uses a stable lock/order strategy that avoids lock inversion under repeated concurrency fixtures.
- **LED-062** Read-your-write consistency for a successful posting matches the declared transaction/storage profile.
- **LED-063** Snapshot/read isolation for balance history is explicit so reporting cannot combine an impossible partial transfer state when atomicity is claimed.
- **LED-064** Long-running reporting/rebuild reads do not block posting beyond declared performance/isolation budgets where backend supports snapshots.
- **LED-065** Concurrency across different sites/tenants cannot share lock keys or account identities accidentally.
- **LED-066** Forced process termination at each transactional boundary cannot yield a state labelled successful unless every required durable posting committed.

# Group 7 — Partial failure/crash/unknown external outcome — LED-067…LED-077

- **LED-067** Crash before transaction commit produces no successful posting and safe retry uses the same idempotency identity.
- **LED-068** Crash after commit but before response returns is resolved by idempotency lookup, preventing duplicate replay.
- **LED-069** Multi-step integration failure records local posting and external-action states separately rather than collapsing them into one ambiguous success flag.
- **LED-070** Provider timeout after mutation request is classified as unknown outcome until authoritative reconciliation resolves it.
- **LED-071** Unknown external outcome blocks blind compensating/retry action that could duplicate money/quantity movement.
- **LED-072** Partial bulk posting reports per-item state and never marks the whole batch complete when some items failed/unknown.
- **LED-073** Queue redelivery reuses source/idempotency identity and cannot create duplicate ledger movements.
- **LED-074** Dead-lettered posting request retains safe diagnostic metadata and a controlled replay path without exposing secrets.
- **LED-075** Reconciliation can transition external linkage from unknown to confirmed/failed without rewriting immutable ledger history improperly.
- **LED-076** System outage distinguishes unavailable balance/posting service from a confirmed zero balance or declined posting.
- **LED-077** Recovery operator can inspect exact posting/source/reconciliation timeline before choosing retry, compensate or no-op.

# Group 8 — Rebuild/reconciliation/snapshot/checkpoint — LED-078…LED-088

- **LED-078** Full balance rebuild from canonical movements reproduces current derived balance for an unchanged ledger.
- **LED-079** Historical balance rebuild reproduces balances at certified checkpoints/effective dates.
- **LED-080** Materialized balance mismatch is detected and surfaced before automatic overwrite/reconciliation policy is applied.
- **LED-081** Rebuild can run in verify-only mode with no mutation to canonical movement history.
- **LED-082** Snapshot/checkpoint records ledger revision/range/high-water mark and checksum/fingerprint sufficient to detect incompatible reuse.
- **LED-083** Incremental rebuild from a valid checkpoint produces the same result as a full rebuild over the same movement set.
- **LED-084** Corrupt/missing checkpoint is rejected and triggers earlier checkpoint/full rebuild rather than trusting partial state.
- **LED-085** Reconciliation compares internal ledger facts with typed external statements/events without treating external source as writable ledger history automatically.
- **LED-086** Reconciliation mismatch categories distinguish missing internal, missing external, amount mismatch, duplicate, timing and unknown states.
- **LED-087** Reconciliation correction plan requires explicit governed posting/compensation rather than direct balance overwrite.
- **LED-088** Rebuild/reconciliation report includes counts, ranges, checksums and unresolved exceptions without leaking protected account details to unauthorized actors.

# Group 9 — Currency/unit/precision/rounding — LED-089…LED-099

- **LED-089** Money ledger stores canonical decimal amount with explicit currency code and configured scale/precision.
- **LED-090** Binary floating-point input is normalized/rejected before canonical posting so common 0.1+0.2 drift cannot enter money truth.
- **LED-091** Currency mismatch between account and movement is rejected unless the ledger profile explicitly supports multi-currency subaccounts/conversion postings.
- **LED-092** Currency conversion posts source/target amounts with explicit rate, source, effective time and provenance; no rate is invented.
- **LED-093** Rounding mode is explicit and applied at the configured posting/conversion boundary with deterministic positive/negative halfway cases.
- **LED-094** Intermediate calculations retain sufficient precision and never silently round before the configured canonical boundary.
- **LED-095** Unit-based ledger rejects incompatible dimensions and records unit identity alongside quantity.
- **LED-096** Unit conversion uses registered compatible conversion semantics and preserves source/conversion provenance when the profile allows conversion.
- **LED-097** Overflow, magnitude or scale beyond configured numeric bounds fails posting atomically rather than wrapping/truncating.
- **LED-098** Zero amount/quantity movement is rejected or allowed only for an explicitly meaningful movement class; it never hides a missing amount.
- **LED-099** Cross-runtime golden vectors produce identical canonical decimal balances, conversions and rounding outcomes.

# Group 10 — Policy/approval/manual adjustment/re-auth — LED-100…LED-110

- **LED-100** View-account, view-history, create-posting, create-hold, reverse, adjust and reconcile permissions are separate capabilities/Policies.
- **LED-101** Posting authorization is evaluated server-side against actor, ledger, account, movement class and resource scope.
- **LED-102** High-risk/manual adjustment can require re-auth and maker-checker approval before posting.
- **LED-103** Approval binds to the exact normalized posting plan/fingerprint; any material edit invalidates prior approval.
- **LED-104** Manual adjustment requires explicit reason/source and cannot be represented as a hidden balance field edit.
- **LED-105** System/automation actor postings retain verifiable principal/service identity and cannot use an anonymous privileged path.
- **LED-106** F04 score/formula or Workflow branch may propose a typed amount/action but cannot bypass ledger posting Policy.
- **LED-107** Account freeze/hold imposed by governance blocks configured movement classes independently of user interface visibility.
- **LED-108** Break-glass posting path, if supported, is narrowly scoped, re-authenticated, audited and cannot disable immutable-history guarantees.
- **LED-109** AI/MCP-generated adjustment/reversal remains a Draft/plan until the same capability/approval requirements as human action are satisfied.
- **LED-110** Denied posting/approval does not leak protected account balance/source details beyond authorized explanation policy.

# Group 11 — Import/migration/duplicate source event/replay — LED-111…LED-121

- **LED-111** Import validates ledger/account/movement schema, currency/unit, identity and source references before any production posting phase.
- **LED-112** Import dry-run reports creates, duplicates, conflicts, invalid rows and projected balances without mutation.
- **LED-113** Re-import of the same source dataset with stable source identities is idempotent and does not duplicate movements.
- **LED-114** Duplicate source identity with conflicting amount/account/state is quarantined as conflict rather than arbitrarily choosing one.
- **LED-115** Legacy opening balances without full history are represented with explicit migration/genesis provenance and cannot be claimed as reconstructed history.
- **LED-116** Migration preserves original effective time, source identity and external reference where valid while assigning safe canonical WPE identities.
- **LED-117** Replay of event stream processes each accepted source event once under the configured idempotency/source rules.
- **LED-118** Out-of-order replay follows explicit effective-time/sequence semantics and converges to the same deterministic derived balance.
- **LED-119** Import cancellation/partial failure records checkpoint and per-row state; it never reports complete migration when unresolved items remain.
- **LED-120** Import/export excludes secrets and applies Policy/redaction to protected account/user metadata.
- **LED-121** Migrated/replayed history passes full rebuild/reconciliation gates before being marked verified/current.

# Group 12 — Audit vs ledger truth/privacy/retention — LED-122…LED-132

- **LED-122** Ledger movement record remains canonical movement truth while Audit records who/what attempted, approved, posted, reversed or reconciled it.
- **LED-123** Audit failure cannot silently roll back an already-committed ledger posting unless the selected atomic architecture explicitly guarantees combined durability.
- **LED-124** Ledger history response redacts protected actor/source metadata according to Policy without changing numeric movement truth.
- **LED-125** Secrets, access tokens, full provider payloads and sensitive request bodies are never stored in movement metadata.
- **LED-126** Privacy export returns only data the requester is authorized/entitled to receive and preserves required third-party confidentiality.
- **LED-127** Erasure/anonymization policy separates removable personal metadata from movement facts that must be retained for contractual/legal/integrity reasons.
- **LED-128** Retention policy cannot delete a movement that is still required to reconstruct an active ledger balance unless the profile has a certified compaction invariant.
- **LED-129** Compaction/archive, if supported, retains verifiable opening/checkpoint proof and does not falsely claim raw-history availability.
- **LED-130** Explanation endpoint can show balance derivation/movement chain without revealing hidden notes, actors or unrelated account data.
- **LED-131** Audit/export timestamps and ledger effective/posting timestamps remain semantically distinct.
- **LED-132** Tamper/integrity check detects unexpected mutation/removal of immutable movement fields within the supported storage threat model.

# Group 13 — Multisite/tenant/site lifecycle — LED-133…LED-143

- **LED-133** Site-scoped ledger/account/movement identity includes durable site ownership independent of current WordPress blog context.
- **LED-134** Network-shared ledger is explicitly declared; ordinary site admin cannot promote a site ledger to network scope by request parameter.
- **LED-135** `switch_to_blog()`/context switch cannot cause stale account/Policy/cache ownership to post into another site's ledger.
- **LED-136** Same account external key on two sites remains distinct through composite ownership identity.
- **LED-137** Network aggregate reporting resolves the authorized site set explicitly and does not imply cross-site posting authority.
- **LED-138** Cross-site transfer is prohibited by default and requires a specifically defined network ledger/bridge profile with independent evidence.
- **LED-139** New-site provisioning can instantiate ledger definitions/templates without copying another site's live balances or movements.
- **LED-140** Site clone creates new ownership identities and cannot duplicate canonical monetary history as if it were the same production ledger unless an explicit environment-clone policy marks it non-production.
- **LED-141** Site archive/delete lifecycle freezes/quarantines ledger access according to retention policy without affecting sibling sites.
- **LED-142** Network admin support access still passes declared ledger Policy/re-auth gates and is auditable.
- **LED-143** Noisy/high-volume tenant receives bounded posting/rebuild resources so it cannot exhaust network-wide ledger service capacity uncontrolled.

# Group 14 — Backup/restore/clone continuity — LED-144…LED-154

- **LED-144** Backup plan includes ledger canonical movements, definitions, accounts, idempotency/source identities and required checkpoints consistently.
- **LED-145** Restore to the same environment reproduces ledger movement history and derived balances at the backup point after verification.
- **LED-146** Restore does not resurrect a superseded idempotency namespace in a way that allows duplicate replay of already-settled external actions.
- **LED-147** Point-in-time restore records recovery boundary and requires reconciliation for external events that may have occurred after backup time.
- **LED-148** External payment/provider state is never rolled back merely because the WordPress ledger database was restored.
- **LED-149** Environment clone/staging copy is marked non-production or given new environment identity so it cannot emit production postings/provider actions accidentally.
- **LED-150** Production promotion/migration preserves stable ledger identities only under an explicit migration plan and prevents two writable authorities for the same ledger.
- **LED-151** Restore verification performs rebuild/checksum/sample reconciliation before declaring ledger healthy.
- **LED-152** Corrupt/incomplete backup containing only derived balances but missing required canonical movements cannot satisfy a full-history restore claim.
- **LED-153** Clone/reset/delete tooling recognizes ledger criticality and requires stronger warning/approval/recovery policy before destructive action.
- **LED-154** Disaster-recovery runbook distinguishes restored internal ledger state, external reconciliation state and final readiness status.

# Group 15 — Million-entry accounts/throughput/query benchmarks — LED-155…LED-165

- **LED-155** 10K-movement account posting/query baseline records environment, schema, indexes, latency percentiles and resource usage.
- **LED-156** 100K-movement account balance-at-time and history pagination remain within declared profile budgets or surface unsupported/degraded status.
- **LED-157** 1M-movement account current-balance lookup uses certified materialization/index strategy and reconciles to canonical history.
- **LED-158** Concurrent posting benchmark measures correctness first; throughput result is invalid if duplicate/lost/overdraft invariants fail.
- **LED-159** Hot-account contention benchmark records lock waits/retries/deadlocks and preserves constrained-balance correctness.
- **LED-160** Many-account workload measures aggregate throughput without allowing one hot account/tenant to starve unrelated accounts indefinitely.
- **LED-161** Large historical range pagination uses bounded cursor/window semantics and cannot trigger unbounded memory response.
- **LED-162** Full rebuild benchmark at 100K/1M movements records duration, peak memory, checkpoint behavior and reconciliation result.
- **LED-163** Reconciliation of large external statement/event set uses bounded batching/checkpoints and reports unresolved exceptions accurately.
- **LED-164** Backup/restore benchmark for large ledger records consistency point and post-restore verification, not just file-copy duration.
- **LED-165** Performance certification is profile/backend-specific and cannot be generalized to every WordPress database/hosting topology without evidence.

# Group 16 — End-to-end wallet/loyalty/inventory/commission golden profiles — LED-166…LED-176

- **LED-166** Wallet golden flow: opening credit → hold → capture → refund produces expected posted/available balances and immutable linked history.
- **LED-167** Wallet concurrency golden flow prevents double spend under two simultaneous constrained debits and preserves one deterministic accepted outcome set.
- **LED-168** Loyalty-points golden flow handles earn → pending/hold → confirm → expire/reverse with unit-safe integer/decimal semantics defined by profile.
- **LED-169** Inventory-quantity golden flow demonstrates movement history while preserving boundary that F06 reservation/commerce inventory authority remains separate unless explicitly adapted.
- **LED-170** Commission golden flow records accrual → approval → payable/reversal without pretending ledger posting itself proves external payout.
- **LED-171** Provider payment golden flow records internal pending/posted/reversal linkage and resolves timeout/unknown provider outcome through reconciliation without duplicate posting.
- **LED-172** Import/replay golden flow yields the same movement identities/net balances on repeated deterministic replay and flags a conflicting duplicate source event.
- **LED-173** Backup/restore golden flow restores to a checkpoint, rebuilds balances and identifies post-backup external facts requiring reconciliation.
- **LED-174** Multisite golden flow proves same external account key/site-local ledgers remain isolated across posting, cache, history and reporting.
- **LED-175** AI/adversarial golden suite proves generated posting/reversal plans cannot bypass Policy, approval, idempotency, numeric, site or immutable-history gates.
- **LED-176** Cross-runtime full regression replays the canonical golden corpus and produces identical movement identities/fingerprints, terminal states, decimal balances and reconciliation classifications within declared deterministic fields.

## 4. Minimum runtime evidence record per fixture

When separately authorized for execution, every LED fixture record must retain:

- fixture ID and protocol revision;
- ledger/profile/schema revision;
- WordPress/PHP/database/storage adapter versions;
- site/network/tenant/environment identity;
- input movements/accounts/source/idempotency facts with secret-safe redaction;
- Policy/actor/approval context where applicable;
- concurrency/crash/fault-injection parameters where applicable;
- expected invariant/result;
- actual movement IDs/states/balance/checksum/reconciliation outputs;
- transaction/lock/retry/checkpoint evidence where applicable;
- pass/fail/blocked/unsupported classification;
- retained logs/artifacts/checksums sufficient for reproduction;
- reviewer and timestamp.

Static prose, screenshots or UI presence alone cannot pass a fixture whose claim requires runtime posting, atomicity, isolation, recovery or performance evidence.

## 5. Acceptance truth

This document completes the fixture-level **planning** of `LED-001…LED-176` only.

Current truth:

- LED documented: **176/176**;
- LED executed: **0/176**;
- F05 runtime certification: **0**;
- implementation authorization: **NOT GRANTED**.

No production/runtime ledger work is authorized by acceptance of this protocol.