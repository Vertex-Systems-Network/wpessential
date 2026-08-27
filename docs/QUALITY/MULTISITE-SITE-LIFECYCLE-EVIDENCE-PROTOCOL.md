# WPEssential — Multisite Site Lifecycle Evidence Protocol

Status: **Future executable evidence protocol / NOT AUTHORIZED**  
Date: 2026-08-28  
Related: ADR-0014, ADR-0059, ADR-0069, ADR-0071, ADR-0075.

## Goal

Verify that WPE site provisioning, status changes, uninitialization/deletion, clone/migration/transfer and PT-C/PT-D/PT-E/PT-F cleanup/reconciliation are idempotent, scope-safe, recoverable and truthful.

**Executed fixtures: 0.**

## Certification extension

This protocol supplements MS0–MS4 Multisite certification.

A surface cannot claim lifecycle-safe Multisite support merely because network activation succeeds.

Suggested lifecycle evidence classes:
- **SL0 Static mapped** — domain lifecycle policy documented.
- **SL1 Provisioning certified** — create/init/retry works for target site.
- **SL2 Restrict/reactivate certified** — archive/spam/deleted-status transitions safe.
- **SL3 Teardown certified** — uninitialize/delete/cleanup/recovery semantics verified.
- **SL4 Transfer/Clone/Disaster certified** — migration/clone/network transfer + large-network/crash recovery verified.

Public support labels, if any, must name actual certified scope.

## Test environments

At minimum after authorization:
- single-site baseline where lifecycle abstraction still applies;
- Multisite subdirectory network;
- Multisite subdomain network where lab DNS permits;
- object cache off/on if supported;
- P-001 DB profiles;
- site-admin vs Network Admin vs Super Admin actors;
- Free only, Free+Pro matched, version-skew fixtures;
- Action Scheduler selected candidate only after P-003 permits it.

## LC-001 Normal site creation

Create site through supported WordPress flow.

Verify:
- core site initializes;
- WPE observes correct network/site IDs;
- provisioning Plan targets only new site;
- required network defaults/templates applied once;
- no other site modified;
- no automatic paid allocation unless explicit policy;
- WPE state reaches Ready or truthful Partial.

## LC-002 Duplicate provisioning event/retry

Trigger/replay provisioning logical operation multiple times.

Pass:
- no duplicate Definition/template instance;
- no duplicate recurring schedule;
- no duplicate Product License allocation;
- no duplicate external webhook binding;
- stable operation/journal result.

## LC-003 Provisioning partial failure

Inject failure at each domain handler boundary.

Verify:
- WordPress site remains usable where possible;
- completed idempotent steps are not duplicated on retry;
- blocked step visible;
- no false Ready state.

## LC-004 Network default/template conflict

New site already has colliding CPT/taxonomy/field/route key.

Verify conflict policy: skip/report/review/override only as documented; no blind overwrite.

## LC-005 Archived site

Set archived state through supported WordPress path.

Verify:
- nonessential public jobs/actions pause according to policy;
- data retained;
- Membership no longer grants site access where site itself unavailable;
- no billing subscription cancellation inferred;
- Network Admin diagnostics remain available.

## LC-006 Spam/restricted site

Same class with security-sensitive restrictions and audit.

## LC-007 Reactivation

Restore archived/restricted site.

Verify revalidation before resuming:
- module policy;
- Product License/environment;
- Jobs/schedules;
- caches;
- Membership generation;
- provider bindings.

## LC-008 Domain/path change

Verify stable WPE site identity; cache/routes/license metadata reconcile; hostname alone does not create/release allocation.

## LC-009 Site uninitialization

Exercise supported WordPress uninitialization path.

Verify WPE distinguishes core table/upload cleanup from commercial/audit history and site-row deletion.

## LC-010 Site record deletion

Exercise supported site delete path.

Verify WPE site historical state, orphan detection, scoped shared rows and external reconciliation.

## LC-011 Delete bypasses WPE preflight

Simulate third-party/core action where WPE did not run ideal impact Plan first.

Pass:
- no claim of orderly/fully verified teardown;
- orphan/degraded lifecycle state created;
- recovery/reconciliation available;
- no wrong-site cleanup.

## LC-012 Active Job during deletion

Long-running target-site Job overlaps teardown.

Verify lifecycle generation/state causes safe stop/skip/recheck; queue cancellation alone not treated as rollback.

## LC-013 Active Workflow wait

Delayed/waiting Workflow resumes after site became restricted/deleted.

It must re-resolve target scope/state before side effects.

## LC-014 Email/Webhook queued during teardown

Queued outbound side effect must honor lifecycle state at execution time and not send on behalf of a site that lost authority unless explicitly critical/allowed.

## LC-015 Membership active during teardown

Verify:
- local site entitlement stops authorizing;
- global WP user remains unless separate user operation;
- WPE role-provenance cleanup only target site;
- external billing not auto-cancelled by implication;
- protected assets remain protected under retention.

## LC-016 Shared network Vault/Connection

Site uses shared network credential/profile.

On site removal:
- site use-right revoked;
- raw shared secret remains network-owned;
- other sites keep valid access;
- audit shows site-specific revocation.

## LC-017 Product License allocation release

Normal release on site deletion according to contract policy.

Verify service response + local commit and capacity only after authoritative result.

## LC-018 Product License release timeout

Unknown remote outcome.

Pass:
- local state `release_pending`/ambiguous;
- same operation ID used on retry/reconcile;
- no second seat consumed/released blindly.

## LC-019 PT-C Definition cleanup

Verify site Definitions archived/tombstoned/retained per policy; network templates untouched; dependency impact visible.

## LC-020 PT-D Relations cleanup

R1 profile after P-010 selection only.

Verify site-scope selection; cross-site edge policy if such profile exists; unrelated site edges untouched.

## LC-021 Other PT-D domain cleanup

Membership/Workflow/Notifications/Email/Event Inbox/Audit each use registered lifecycle policy—not generic bulk delete.

## LC-022 PT-E table cleanup

Inject one table drop/cleanup failure.

Pass:
- partial state/journal retained;
- no next-site table affected;
- retry only failed/required steps;
- retention/export policy observed.

## LC-023 Site Backup before destructive operation

Verify required recovery class and scoped export of PT-C/PT-D/PT-E data.

If policy requires restore-tested recovery, upload-only backup cannot satisfy gate.

## LC-024 Crash after drain

Terminate process after jobs/access drained but before cleanup. Resume from journal safely.

## LC-025 Crash mid PT-D cleanup

Retry idempotently without deleting another site's rows.

## LC-026 Crash after remote success/local failure

External release/unregister succeeded; local commit fails. Reconcile remote authority on resume.

## LC-027 Clone to staging

Copy DB/files.

Verify:
- clone classification;
- no automatic second production allocation;
- production OAuth/webhook tokens not blindly live;
- Membership/site data copy policy explicit;
- jobs/email/webhooks disabled/revalidated according environment policy.

## LC-028 Unknown production clone

Conflict/review state; safe deployed output rules; no silent production rights duplication.

## LC-029 Temporary migration overlap

Source and target coexist within policy window; allocation/side-effect ownership explicit; completion retires source correctly.

## LC-030 Network-to-network site transfer

Verify explicit mapping of PT-C/PT-D/PT-E, roles, Vault/Connections references, Product License allocation, Membership, Backup and domain metadata. No blind `network_id/site_id` mass replacement.

## LC-031 Disaster restore

Restore old backup with stale site/allocation/job/cache state.

Verify revalidation of:
- Product License signed entitlement/allocation;
- remote provider bindings;
- recurring Jobs;
- cache generations;
- Membership access;
- lifecycle journal.

## LC-032 Deleted/recreated numeric site ID

Ensure old WPE identity/history is not incorrectly attached solely because WordPress numeric ID matches/reappears.

## LC-033 Wrong-site destructive IDOR

Authenticated Site A admin modifies request target to Site B.

Must fail before Plan/cleanup; no timing/count leakage beyond safe policy.

## LC-034 Super Admin high-risk action

Super Admin still passes WPE high-risk capability/Policy/confirmation/audit/recovery requirements.

## LC-035 100-site lifecycle fan-out

Network policy rollout/provision diagnostics with bounded JobService child work.

## LC-036 1k/10k synthetic site scale

Measure coordinator enumeration, queue admission, state registry/cache footprint and maintenance cost. No unbounded interactive loop.

## LC-037 Plugin deactivation/network deactivation

Verify deactivation does not equal site data deletion; active lifecycle runs become safe paused/recoverable state.

## LC-038 Uninstall

Only explicit uninstall cleanup policy applies; required audit/recovery/export impacts reviewed; network/site scope accurate.

## LC-039 Free↔Pro version skew

Lifecycle operation under compatible/incompatible Free/Pro combinations follows degraded-safe boot rules rather than fatal/destructive behavior.

## LC-040 Privacy erasure overlap

Personal-data erasure request overlaps site teardown. Ensure retention/legal/product policy is domain-specific; site deletion is not treated as universal privacy erasure.

## Required evidence per fixture

Capture:
- environment/version profile;
- site/network IDs and stable WPE identities;
- actor/capabilities;
- starting state;
- lifecycle Plan fingerprint;
- journal/step transitions;
- Job correlation IDs;
- affected domain row/resource counts without sensitive payloads;
- external operation/reconciliation state;
- final WordPress site state;
- final WPE lifecycle state;
- security assertions;
- recovery result;
- timing/scale metrics where relevant.

## Failure rule

Any of these is critical failure:
- wrong-site data deletion/mutation;
- stale Membership allow after required revoke;
- shared secret exposure/deletion affecting unrelated site;
- duplicate production allocation from retry/clone;
- external billing cancellation by implicit site deletion;
- orphan privileged Job continues side effects after deletion;
- lifecycle run claims success with required unknown/failed step;
- irrecoverable destructive operation when recovery policy required a verified restore point.

## Authorization gate

**Executed fixtures: 0/40.**

No Multisite lab, hook, lifecycle handler, site creation/deletion test, queue run, Product License call, data cleanup or benchmark may execute before explicit owner consent under ADR-0014.
