# WPEssential — Multisite Site Lifecycle Executable Evidence Protocol

Status: **Phase 0 fixed evidence contract / execution NOT AUTHORIZED**  
Date: 2026-08-28  
Refinement work package: `P0-M00-WP24`  
Related: ADR-0014, ADR-0059, ADR-0069, ADR-0071, ADR-0075, Multisite Scope/Isolation protocol, Product License allocation semantics, Backup/Restore, Membership, Vault, Workflow/JobService.

## 1. Goal

Verify that WPEssential site provisioning, status transitions, uninitialization/deletion, clone/migration/transfer, storage cleanup/reconciliation and disaster recovery remain idempotent, scope-safe, recoverable and truthful.

The canonical lifecycle fixture set is now **LC-01…LC-96**. The original LC-001…LC-040 semantic coverage is preserved and expanded inside these fixed ranges; those legacy IDs are superseded as future execution identifiers.

No fixture has been executed.

## 2. Lifecycle certification levels retained

- **SL0 — Static mapped:** domain lifecycle policy documented; no runtime claim.
- **SL1 — Provisioning certified:** create/init/retry and network-default application proven for target profile.
- **SL2 — Restrict/reactivate certified:** archive/spam/restricted/reactivate and dependent runtime behavior proven.
- **SL3 — Teardown certified:** uninitialize/delete/cleanup/recovery and remote reconciliation proven.
- **SL4 — Transfer/Clone/Disaster certified:** clone/migration/network transfer/disaster/large-network/crash recovery proven.

A surface cannot claim lifecycle-safe Multisite support merely because network activation succeeds. SL classes supplement, and do not replace, MS0–MS4 scope/isolation certification.

## 3. Result model

Each fixture reports `PASS`, `FAIL`, `BLOCKED`, `NOT_EXECUTED` or `INCONCLUSIVE` under pinned WordPress/PHP/DB/Free-Pro/topology/storage/provider profiles. Missing prerequisite evidence remains BLOCKED.

## 4. Required future environments

After explicit authorization only:

- single-site control;
- Multisite subdirectory network;
- Multisite subdomain network where lab DNS permits;
- object cache off/on where supported;
- accepted P-001 DB profiles;
- Site Admin, Network Admin and Super Admin actor profiles;
- Free-only, matched Free+Pro and version-skew profiles;
- selected JobService backend only after P-003 evidence permits it;
- disposable/recoverable test network for destructive fixtures.

---

# 5. Fixed matrix — LC-01…LC-96

## A. Provisioning, initialization and idempotency — LC-01…LC-16

- **LC-01** Normal WordPress site creation produces exact network/site identity and begins only the target site's WPE lifecycle plan.
- **LC-02** Required network defaults/templates apply once according to declared new-site policy.
- **LC-03** New-site creation never auto-consumes Product License allocation unless explicit accepted policy requires it.
- **LC-04** Duplicate/replayed site-created event does not duplicate Definitions/templates/schedules/allocation/webhook bindings.
- **LC-05** Partial failure at each registered lifecycle-domain handler records Partial/Blocked state, not false Ready.
- **LC-06** Retry resumes only incomplete/idempotent steps and preserves completed step evidence.
- **LC-07** New site with CPT/taxonomy/field/route key conflict applies documented skip/report/review/block policy without blind overwrite.
- **LC-08** Network linked/pinned template revision selected during provisioning matches target site's configured propagation policy.
- **LC-09** Site created while Pro unavailable/incompatible still initializes safe Free state without fatal/destructive Pro dependency.
- **LC-10** Site created while remote Product License service is unavailable remains locally usable and allocation state is truthful pending/unknown.
- **LC-11** Site created while JobService is unavailable does not claim fully Ready when required async provisioning is pending.
- **LC-12** Site creation from Network Admin and programmatic/core path converge on same lifecycle identity/idempotency semantics.
- **LC-13** Import/restore-created site uses an explicit initialization/restore path rather than accidentally replaying ordinary blank-site defaults twice.
- **LC-14** Provisioning journal/operation identity is stable across request retry/process restart.
- **LC-15** Provisioning cannot target another network/site by forged lifecycle request coordinates.
- **LC-16** Provisioning at 100+ sites is admitted as bounded JobService/coordinator work, not unbounded synchronous loop.

## B. Archive, spam, restrict, domain changes and reactivation — LC-17…LC-32

- **LC-17** Archived-site transition records exact lifecycle state and pauses/blocks nonessential public operations according to policy.
- **LC-18** Spam/restricted-site transition applies stronger security-sensitive restrictions without deleting owned data.
- **LC-19** Site marked deleted/unavailable by WordPress cannot continue ordinary public WPE side effects merely because local module state says enabled.
- **LC-20** Membership cannot make a WordPress-unavailable site reachable; global user remains separate from site access.
- **LC-21** Site restriction never implies external billing subscription cancellation.
- **LC-22** Site restriction never deletes Product License Account/contract/allocation records automatically.
- **LC-23** Network Admin diagnostics/recovery view remains available where WordPress permits despite site public restriction.
- **LC-24** Reactivation revalidates module dependencies/compatibility before resuming module operations.
- **LC-25** Reactivation revalidates signed Product Entitlement/allocation/environment state before Pro management resumes.
- **LC-26** Reactivation revalidates Jobs/schedules and does not blindly replay all missed occurrences.
- **LC-27** Reactivation invalidates/rebuilds scope/access/content caches according to owning generation contracts.
- **LC-28** Reactivation revalidates Membership authorization generation before protected output is served.
- **LC-29** Reactivation revalidates provider/webhook/shared-connection use-right before external side effect.
- **LC-30** Domain/path change preserves stable WPE site identity when continuity is verified and invalidates routes/cache metadata correctly.
- **LC-31** Domain/path change alone does not consume/release Product License allocation or rotate commercial identity blindly.
- **LC-32** Wrong/ambiguous domain/identity change enters review/revalidation rather than silently claiming successful migration.

## C. Uninitialization, deletion, storage cleanup and recovery gates — LC-33…LC-48

- **LC-33** Supported site uninitialization distinguishes core table/upload cleanup, site-row deletion and WPE domain retention.
- **LC-34** Normal site deletion runs reviewed target inventory/lifecycle plan where integration point allows it.
- **LC-35** Third-party/core deletion bypassing WPE preflight creates degraded/orphan reconciliation state, not false orderly teardown.
- **LC-36** Site deletion cannot delete another site's PT-C/PT-D/PT-E/PT-F resources through missing/incorrect scope predicate.
- **LC-37** PT-C Definition cleanup/archive/tombstone follows retention policy; network templates remain untouched.
- **LC-38** PT-D Relations cleanup selects only edges owned/affected by exact site according to certified relation profile.
- **LC-39** Membership/Workflow/Notification/Email/Event Inbox/Audit PT-D domains use registered lifecycle policies, not generic bulk delete.
- **LC-40** PT-E site-owned table cleanup drops/archives only exact site's physical tables/profile.
- **LC-41** Mixed shared-row table cleanup requires trusted scope predicate/index and cannot use current-blog assumption alone.
- **LC-42** PT-F/external/provider resource cleanup follows owning adapter/remote-authority semantics rather than local delete optimism.
- **LC-43** One cleanup-step failure preserves durable journal/Partial state and allows targeted retry.
- **LC-44** Required pre-destructive Backup/restore-point gate checks actual certified recovery class, not `backup uploaded` label alone.
- **LC-45** If restore-tested recovery is required, unverified or upload-only backup cannot satisfy destructive gate.
- **LC-46** Site deletion preserves shared/global WP users unless a separate explicit global-user operation is authorized.
- **LC-47** Site deletion preserves protected assets/history according to retention policy and does not make retained private content public.
- **LC-48** Teardown completion requires every required local/remote step to be success/accepted terminal or explicitly waived; unknown is not success.

## D. Runtime overlap, Jobs, Workflows, Membership and remote reconciliation — LC-49…LC-64

- **LC-49** Long-running target-site Job overlapping restriction/deletion re-checks lifecycle generation before each irreversible side effect.
- **LC-50** Queue cancellation alone is not represented as rollback of an already-started side effect.
- **LC-51** Waiting/delayed Workflow resumes after target restriction/deletion and re-authorizes site state before continuation.
- **LC-52** Queued email during teardown does not send for a site that lost authority unless explicitly allowed critical category says so.
- **LC-53** Queued webhook/provider action during teardown revalidates site/provider use-right before dispatch.
- **LC-54** Active Membership during teardown stops authorizing site resources while global user identity remains intact.
- **LC-55** Membership role-provenance cleanup affects only exact target site's synchronized roles.
- **LC-56** Site removal never infers payment/subscription cancellation from Membership/site lifecycle alone.
- **LC-57** Shared network Vault/Connection removal revokes only target site's use-right; raw shared secret remains network-owned.
- **LC-58** Other sites using same network credential continue functioning after target-site revocation.
- **LC-59** Product License allocation normal release commits local released state only after authoritative remote result.
- **LC-60** Product License release timeout/unknown outcome retains `release_pending/unknown` and reuses same idempotency identity.
- **LC-61** External remote success followed by local commit failure is reconciled from remote authority instead of repeating blind destructive mutation.
- **LC-62** Local success followed by remote failure preserves explicit pending/reconcile state and does not free commercial capacity optimistically.
- **LC-63** Permission/actor authority revoked during lifecycle operation is re-evaluated at sensitive continuation checkpoints.
- **LC-64** Audit correlates parent lifecycle run, child domain steps, Jobs and remote operations without logging secrets/private payload.

## E. Crash recovery, cloning, migration, transfer and disaster restore — LC-65…LC-80

- **LC-65** Crash after drain/pause but before cleanup resumes from journal without repeating completed operations.
- **LC-66** Crash mid PT-D cleanup retries idempotently and cannot delete sibling-site rows.
- **LC-67** Crash mid PT-E cleanup leaves truthful partial physical state and resumes exact table/profile step.
- **LC-68** Crash after remote success/local failure reconciles provider/allocation state before retry.
- **LC-69** Staging clone from production DB is classified and does not silently obtain a second production allocation.
- **LC-70** Staging clone does not blindly keep production OAuth refresh credential/webhook/provider side effects live.
- **LC-71** Staging clone has explicit Membership/data-copy policy and does not infer production user access semantics blindly.
- **LC-72** Unknown production clone enters conflict/review/revalidation while safe deployed output follows accepted degraded policy.
- **LC-73** Temporary migration overlap designates side-effect/commercial authority for source vs target during window.
- **LC-74** Migration completion retires source ownership/allocation/provider side effects according to explicit state machine.
- **LC-75** Network-to-network site transfer remaps WPE site/network identities explicitly; no blind numeric ID substitution.
- **LC-76** Network-to-network transfer remaps PT-C/PT-D/PT-E resources and dependencies according to owning storage/import contracts.
- **LC-77** Transfer never copies old Network Vault/shared credentials as target network ownership without explicit rebind.
- **LC-78** Transfer reconciles Product License allocation/environment and does not double-count/lose seat under timeout race.
- **LC-79** Disaster restore from old backup revalidates entitlement/allocation/provider/Job/cache/Membership/lifecycle journal before resuming side effects.
- **LC-80** Deleted/recreated numeric site ID after restore cannot inherit old history/authority solely because number matches.

## F. Authorization, scale, deactivation/uninstall, privacy and certification truth — LC-81…LC-96

- **LC-81** Site A admin forged destructive lifecycle target Site B is denied before impact plan/cleanup/timing-sensitive resource enumeration.
- **LC-82** Super Admin still passes WPE high-risk Policy/confirmation/audit/recovery invariants.
- **LC-83** 100-site lifecycle fan-out measures bounded enumeration/queue admission/child concurrency and partial-failure behavior.
- **LC-84** 1,000-site synthetic lifecycle profile measures state registry/cache/job/audit footprint.
- **LC-85** 10,000-site profile, if executed, yields environment-specific measured limits and never blanket scale claim.
- **LC-86** One failing site in network rollout/teardown does not corrupt or indefinitely block unrelated sites.
- **LC-87** Noisy-site repeated lifecycle retries honor JobService fairness/backpressure.
- **LC-88** Plugin deactivation pauses/leaves active lifecycle runs in safe recoverable state and never equals data deletion.
- **LC-89** Network deactivation and per-site deactivation remain semantically distinct.
- **LC-90** Uninstall uses explicit site/network/storage-class inventory and retention/export/recovery policy before cleanup.
- **LC-91** Uninstall cannot delete remote Account/support/commercial history by local implication.
- **LC-92** Free↔Pro incompatible version pair degrades safely rather than executing destructive lifecycle steps with mismatched contracts.
- **LC-93** Privacy erasure overlapping site teardown follows domain-specific retention and does not treat site deletion as universal erasure.
- **LC-94** Restored/clone environment privacy state does not silently resume telemetry/diagnostics/support-upload consent.
- **LC-95** Diagnostics distinguish Ready/Partial/Blocked/Orphaned/Reconciliation Required/Unknown lifecycle state and surface failed step.
- **LC-96** Final certification report pins topology/storage/provider versions, SL0–SL4 result and unsupported lifecycle operations; no runtime claim from static mapping.

---

## 6. Required evidence per run

Capture:
- fixture ID;
- WPE build/commit and Free/Pro pair;
- WordPress/PHP/DB/topology/object-cache profile;
- stable WPE network/site identity plus local coordinates;
- actor/capability/Policy state;
- prerequisite module/provider certification;
- starting WordPress + WPE lifecycle state;
- lifecycle Plan fingerprint and journal/step transitions;
- Job/Workflow/remote correlation IDs;
- affected domain resource counts without private payloads;
- external authoritative state before/after where relevant;
- final WordPress and WPE lifecycle state;
- recovery/reconciliation outcome;
- security assertions;
- timing/scale metrics where applicable;
- `PASS/FAIL/BLOCKED/NOT_EXECUTED/INCONCLUSIVE`.

## 7. Stop-the-line failures

Critical failures include:
- wrong-site data deletion/mutation;
- stale Membership allow after required revoke;
- shared secret exposure/deletion affecting unrelated site;
- duplicate production allocation/provider side effect from retry/clone;
- external billing cancellation by implicit site deletion;
- orphan privileged Job continuing side effects after site lost authority;
- lifecycle run claiming success with required unknown/failed step;
- irrecoverable destructive operation where verified recovery was required;
- restored/clone stale authority resuming without required revalidation.

## 8. Current evidence state

- LC fixtures documented: **96**;
- executed: **0/96**;
- SL0–SL4 runtime certification: **0**;
- Site Lifecycle implementation: none;
- Multisite MSI scope/isolation evidence remains independent and required;
- development authorization remains **NOT GRANTED**.

## 9. Development gate

No Multisite lab, lifecycle handler, site creation/archive/delete/uninitialize, queue/workflow execution, Product License/provider call, data cleanup, Backup/Restore, clone/transfer or benchmark may execute before explicit owner consent under ADR-0014 is granted and recorded.