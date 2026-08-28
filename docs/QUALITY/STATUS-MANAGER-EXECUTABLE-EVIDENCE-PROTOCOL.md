# WPEssential — Status Manager Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0038, ADR-0110, DSR, CLG, DVR, CAC, VER, MLC, ERR, PDL, Workflow, JobService, Query, REST, Forms, Import, Multisite, ADR-0014.

## 1. Purpose

Define evidence required before WPEssential can claim Status Manager production readiness for either:

1. **WordPress Post Status Adapter**, or
2. **Generic Domain State Machine**.

These remain separate certification domains. Passing one never certifies the other.

## 2. Non-negotiable invariants

- WordPress-native post status semantics are not silently redefined by the generic engine.
- UI/menu/conditional visibility never authorizes a transition.
- Current state, transition intent, transition result, history, side effects and cache projections are separate truths.
- A state-machine `true` guard never grants missing Capability/Policy.
- Direct writes must not be marketed as impossible unless the certified adapter/runtime actually enforces them.
- Duplicate Job/request delivery cannot duplicate transition side effects beyond declared idempotency semantics.
- Cross-site current state/history must never leak.
- Migration, rename, archive and delete of states are explicit lifecycle operations.
- No paper/static evidence in this protocol is runtime certification.

## 3. Certification profile

Future evidence records WordPress/PHP/database versions, topology, post-status providers, Definition/Policy/Query/Workflow/Job versions, DSR/current-state storage class, history storage profile, concurrency primitive, cache generation, import/migration profile and exercised REST/Form/Dashboard adapters.

## 4. Fixed fixture matrix

### A. Original WordPress Post Status fixtures — preserved

- **SM-01** WPE custom status registration.
- **SM-02** Core status preservation.
- **SM-03** Third-party status preservation.
- **SM-04** Status key constraints.
- **SM-05** Post-type availability overlay.
- **SM-06** Admin edit integration.
- **SM-07** Quick edit integration.
- **SM-08** Bulk edit reauthorization/partial truth.
- **SM-09** List filter/query correctness.
- **SM-10** REST/Ability transition authority.
- **SM-11** Form transition uses same authority.
- **SM-12** Dashboard transition visibility ≠ authorization.
- **SM-13** Label rename preserves machine key.
- **SM-14** Machine-key migration preview.
- **SM-15** Key migration execution verification.
- **SM-16** Archive status with existing posts.
- **SM-17** Remove only after migration/reference safety.
- **SM-18** Direct third-party write coverage truth.
- **SM-19** Core transition hook side-effect dedupe.
- **SM-20** Scheduled/future post compatibility.

### B. Original generic state-machine fixtures — preserved

- **SM-21** Definition publish validation.
- **SM-22** Fixed initial state exactly once.
- **SM-23** Conditional initial state deterministic.
- **SM-24** Imported initial-state mapping explicit.
- **SM-25** Allowed transition commits target/history.
- **SM-26** Denied actor cannot transition.
- **SM-27** Failed guard/required field blocks before commit.
- **SM-28** Terminal state semantics.
- **SM-29** Explicit reopen transition.
- **SM-30** Optimistic concurrency race.
- **SM-31** History atomicity/reconciliation.
- **SM-32** Duplicate idempotency key.
- **SM-33** Force-state repair is high-risk/audited.
- **SM-34** Custom-table current-state storage.
- **SM-35** Meta current-state storage evidence limits.
- **SM-36** External/provider state reconciliation.
- **SM-37** Query Builder integration.
- **SM-38** Transition history bounded/authorized.
- **SM-39** Workflow reaction is post-commit truth.
- **SM-40** Workflow-requested transition uses same engine.
- **SM-41** Timed transition pins revision/current state.
- **SM-42** Duplicate/late Job cannot stale-transition.
- **SM-43** Machine revision publish with active entities.
- **SM-44** Missing target Data Source degrades safely.
- **SM-45** Multisite same machine key isolation.
- **SM-46** Import with history does not fabricate history.
- **SM-47** Pro expiry preserves safe deployed runtime.
- **SM-48** Large history/concurrency performance.

### C. Definition, versioning and dependency lifecycle

- **SM-49** Draft state-machine revision never changes published runtime.
- **SM-50** Published transition execution pins machine revision.
- **SM-51** Concurrent publish conflict is explicit.
- **SM-52** State UUID/internal identity survives label rename.
- **SM-53** Transition UUID/internal identity survives presentation rename.
- **SM-54** Unknown future schema version fails/degrades safely.
- **SM-55** Ordered migrator chain is explicit and verified.
- **SM-56** Removed state with active entities blocks publish or requires migration plan.
- **SM-57** Removed transition does not invalidate already-recorded history.
- **SM-58** Cardinal transition semantics changed after publish require impact analysis.
- **SM-59** Dependency deletion is blocked/diagnosed when machine is referenced by Forms/Workflow/Dashboard.
- **SM-60** Module disable preserves current state/history while stopping disabled execution as declared.
- **SM-61** Module re-enable revalidates machine/source/cache generations.
- **SM-62** Plugin deactivation cannot corrupt native WP status/content.
- **SM-63** Free↔Pro version skew degrades rather than fataling state reads.
- **SM-64** Clone/transfer remaps site scope without copying stale execution locks/jobs.

### D. WordPress-native adapter depth

- **SM-65** Native post save path maps only certified WPE statuses.
- **SM-66** Gutenberg editor integration respects status visibility/capabilities.
- **SM-67** Classic editor integration remains truthful for supported versions.
- **SM-68** REST core post update cannot bypass claimed transition enforcement.
- **SM-69** XML-RPC/core external write limitations are documented/tested where relevant.
- **SM-70** `wp_update_post` direct plugin write coverage is measured honestly.
- **SM-71** Revision/autosave does not create false transition history.
- **SM-72** Trash/untrash semantics remain distinct from custom status state unless explicitly integrated.
- **SM-73** Private/publish/draft visibility semantics preserve core query/access behavior.
- **SM-74** Scheduled publish from custom pre-publish state has explicit supported/unsupported semantics.
- **SM-75** Post type unregister/deactivate does not orphan status data silently.
- **SM-76** Third-party plugin changes status registration after WPE load are detected/diagnosed.
- **SM-77** Same status slug collision with third party is blocked/resolved deterministically.
- **SM-78** Core/third-party status label changes do not imply ownership.
- **SM-79** Bulk transition handles mixed post types/status eligibility per target.
- **SM-80** List counts do not leak inaccessible posts/statuses.

### E. Generic engine guards, Policy and typed context

- **SM-81** Transition requires current target resource Policy.
- **SM-82** Capability granted but object Policy denied still blocks.
- **SM-83** Condition guard `true` does not grant missing Policy.
- **SM-84** Condition guard reads only authorized/declared values.
- **SM-85** DVR value resolution cannot expose secret/protected data to guard diagnostics.
- **SM-86** Required-field guard uses canonical typed value/null semantics.
- **SM-87** Time/date guard records timezone/profile explicitly.
- **SM-88** Actor attribute guard reauthenticates current principal context.
- **SM-89** Membership guard observes revoke-safe generation.
- **SM-90** Relation guard reauthorizes relation endpoints/fields.
- **SM-91** Remote/provider guard unavailable state follows explicit fail policy.
- **SM-92** Guard evaluation error is distinguishable from guard=false.
- **SM-93** Guard diagnostics redact sensitive values.
- **SM-94** Preview/eligibility endpoint uses same semantics without granting execution.
- **SM-95** Transition reason/comment fields are typed/sanitized and separately authorized.
- **SM-96** High-risk transition can require recent-auth/confirmation without applying to all low-risk transitions.

### F. Concurrency, atomicity and idempotency

- **SM-97** Compare-and-set/current-version mismatch rejects stale transition.
- **SM-98** Two different valid outgoing transitions from same state yield one authoritative winner.
- **SM-99** Same transition concurrent duplicates dedupe per idempotency contract.
- **SM-100** History insert failure after state commit enters reconciliation-required truth.
- **SM-101** State write failure after history prewrite cannot leave false completed history.
- **SM-102** Audit failure after authoritative transition does not blindly repeat transition.
- **SM-103** Cache invalidation failure cannot preserve revoked/old state beyond declared correctness window.
- **SM-104** Event publish failure after commit is recoverable/reconcilable.
- **SM-105** Workflow enqueue failure after commit does not roll state back falsely.
- **SM-106** Worker crash before transition write safely retries.
- **SM-107** Worker crash after state write before history reconciles without duplicate transition.
- **SM-108** Worker crash after history before side-effect enqueue reconciles.
- **SM-109** Lease expiry does not prove transition side effect absence.
- **SM-110** Manual retry after unknown outcome re-reads authoritative state first.
- **SM-111** Force repair cannot bypass audit/reason/current authority.
- **SM-112** Bulk transition uses per-target concurrency/conflict truth, not all-or-nothing fiction.

### G. Query, cache, projections and history

- **SM-113** Query current-state filter uses authoritative typed storage.
- **SM-114** Query sort by state follows declared order/key semantics.
- **SM-115** State counts obey row/object authorization.
- **SM-116** History query is separately authorized from current state.
- **SM-117** History actor/reason fields follow privacy classification.
- **SM-118** Current-state cache key includes entity/scope/machine generation.
- **SM-119** Machine publish invalidates compiled state/transition cache.
- **SM-120** State commit invalidates dependent Listing/Dashboard/Admin Column caches.
- **SM-121** Role/Membership revoke cannot be hidden by cached transition eligibility.
- **SM-122** Cache backend outage never becomes authorization fail-open.
- **SM-123** Stale history cache is distinguishable from authoritative history.
- **SM-124** Derived “time in state” projection uses authoritative transition timeline.
- **SM-125** Aggregated state metrics do not leak inaccessible entities.
- **SM-126** Export of current state/history respects field/row Policy.
- **SM-127** Privacy erase does not rewrite legal/audit history blindly.
- **SM-128** Retention cleanup preserves required current-state lineage/recovery references.

### H. Workflow, Job, Notification and external effects

- **SM-129** Post-commit event emitted once per logical transition.
- **SM-130** Workflow listener duplicate delivery does not duplicate owned effects.
- **SM-131** Workflow transition action uses expected-current-state precondition.
- **SM-132** Workflow branch result cannot bypass transition Policy.
- **SM-133** Timed transition schedule pins machine/transition revision.
- **SM-134** Edited/cancelled timer does not leave stale Job silently active.
- **SM-135** DST/calendar timed transition follows explicit Job/calendar semantics.
- **SM-136** Notification failure does not revert authoritative transition falsely.
- **SM-137** Email/provider unknown outcome remains provider truth, not transition rollback.
- **SM-138** Webhook/Event Inbox request maps to transition only after trusted scope/signature/Policy.
- **SM-139** External provider state update is reconciled, not blind overwrite.
- **SM-140** Child/related transition cascade requires explicit bounded design; no arbitrary recursive loop.
- **SM-141** Circular transition-trigger workflow is detected/bounded.
- **SM-142** Backpressure delays side effects without changing committed transition truth.

### I. Import, migration, restore and lifecycle

- **SM-143** Import state mapping uses stable identity/explicit mapping, not labels.
- **SM-144** Import transition history preserves provenance and does not fabricate missing events.
- **SM-145** Import stale machine revision blocks/replans.
- **SM-146** State-key migration is resumable/checkpointed for large datasets.
- **SM-147** Migration duplicate/retry is idempotent/reconciled.
- **SM-148** Backup restore of active timed transitions requires revalidation before resume.
- **SM-149** Restored copied locks/jobs do not auto-own current execution.
- **SM-150** Module uninstall cleanup follows explicit ownership/retention policy.
- **SM-151** Pro expiry cannot strand entity in unreadable state.
- **SM-152** Site archive/deactivation stops new timed/site-owned actions as declared.
- **SM-153** Site deletion removes site-owned state/history only under lifecycle policy.
- **SM-154** Site clone copies configuration/state only per explicit clone semantics and regenerates execution identities.
- **SM-155** Site transfer/domain change does not alter authorization ownership.
- **SM-156** Network-owned machine remains distinct from site-owned machines.

### J. Multisite, privacy, diagnostics and scale

- **SM-157** Same machine/state keys on two sites remain isolated.
- **SM-158** Site admin cannot mutate network-owned machine/state.
- **SM-159** Network aggregate state query enumerates only authorized sites.
- **SM-160** Current-blog switch never becomes durable target authorization.
- **SM-161** Global user/entity with site-specific state retains explicit scope.
- **SM-162** Audit correlation links transition/history/job/workflow without storing secrets.
- **SM-163** Error taxonomy distinguishes denied/conflict/guard-false/guard-error/degraded/provider-unknown.
- **SM-164** Support bundle redacts protected transition payload/reason values.
- **SM-165** Privacy exporter returns only eligible WPE-owned history/state data.
- **SM-166** Privacy eraser follows legal/retention class instead of deleting all history.
- **SM-167** 100k/1M entities current-state filtering remains bounded.
- **SM-168** 10M+ history-row profile measures indexes/partition/retention candidates without weakening truth.
- **SM-169** 100 concurrent transitions on same entity preserve single authoritative sequence.
- **SM-170** 1k concurrent transitions across entities avoid global lock bottleneck beyond accepted profile.
- **SM-171** 100/1k/10k-site same-key isolation test has zero wrong-site rows.
- **SM-172** Large migration/history retention job respects JobService fairness/backpressure.
- **SM-173** Adversarial IDOR/mass-assignment/raw-state-write corpus yields zero unauthorized transition.
- **SM-174** Corrupt machine/state/history rows fail/degrade with repair diagnostics, not silent coercion.
- **SM-175** Full cross-channel parity test covers admin, REST, Ability, Form, Dashboard, Workflow and Job paths.
- **SM-176** Certification report pins exact engine/storage/provider/version profile; no generic Status Manager certification beyond tested profile.

## 5. Independent certification classes

Future evidence records separately:
- `SM-WP` WordPress Post Status adapter;
- `SM-G` generic state-machine semantics;
- `SM-C` concurrency/idempotency/history;
- `SM-I` integration/cross-channel enforcement;
- `SM-M` migration/import/restore/lifecycle;
- `SM-S` Multisite/security/privacy;
- `SM-P` performance/scale.

Passing one class never promotes another.

## 6. Stop-the-line / pass gates

Certification fails if:
- core/third-party status is destructively lost;
- WPE claims enforced transition authority while a certified direct path trivially bypasses it;
- stale concurrent transitions both succeed incorrectly;
- required history becomes silently inconsistent with current state;
- duplicate Job/request repeats logical transition/side effects;
- unknown import state is guessed by label;
- UI/condition/cache visibility substitutes for authorization;
- cross-site state/history leaks;
- state migration leaves orphaned/ambiguous entities;
- terminal/reopen/force-repair semantics can bypass current Policy/audit.

## 7. Required future evidence report

Include exact runtime/storage/topology profile, SM-01…SM-176 pass/fail/NA, WordPress adapter coverage, state/history DDL/index profile, concurrency/crash/idempotency evidence, Workflow/Job/provider results, cache/query/privacy results, migration/restore/Multisite evidence, performance and explicit enforcement limitations.

## 8. Current state

**SM fixtures documented: 176.**  
**SM fixtures executed: 0/176.**  
WordPress Post Status adapter certifications: **0**.  
Generic State Machine certifications: **0**.

No status registration, post/entity mutation, transition, history row, DB migration, Workflow/Job/provider action, cache mutation, Multisite action or benchmark has executed.

## 9. Development gate

Execution requires explicit owner consent under ADR-0014 and the Approval Ledger. `continue` remains planning-only.
