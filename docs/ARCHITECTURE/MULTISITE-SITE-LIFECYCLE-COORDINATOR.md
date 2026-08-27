# WPEssential — Multisite Site Lifecycle Coordinator

Status: **Phase 0 paper architecture / no hooks, jobs or cleanup authorized**  
Date: 2026-08-28  
Related: ADR-0014, ADR-0059, ADR-0069, ADR-0071, ADR-0074, MS evidence protocol.

## 1. Purpose

WPE uses mixed Multisite physical topology:
- native WordPress site/network storage;
- PT-C shared control-plane tables;
- PT-D shared runtime tables;
- PT-E per-site custom runtime tables;
- PT-F external authoritative resources.

Therefore WordPress site creation, state changes, uninitialization, deletion, transfer and restore cannot be handled by isolated modules independently. WPE needs one **Site Lifecycle Coordinator** that creates a reviewed impact plan and dispatches bounded, idempotent domain handlers.

This is a logical coordination contract only. No hook or cleanup code is authorized.

## 2. WordPress lifecycle facts used

Current WordPress APIs distinguish:
- inserting a site record;
- initializing a site's database/defaults;
- updating site properties/status flags;
- uninitializing a site, including core table/upload cleanup behavior;
- deleting the site record.

WPE must not treat these as one event.

The coordinator must re-resolve actual site state instead of assuming hook name alone proves completion.

## 3. WPE lifecycle phases

Canonical WPE phases:

### L0 — Discovered
WordPress site identity exists or has been observed; WPE has not yet completed its scoped provisioning assessment.

### L1 — Core Initialized
WordPress reports the site as initialized and target context can be safely resolved.

### L2 — WPE Provisioning Pending
Network defaults/templates/module policy/site-scoped WPE resources need review/provisioning.

### L3 — WPE Ready
Required WPE site-scoped control-plane/runtime prerequisites for enabled modules are healthy.

### L4 — Restricted/Inactive
Site still exists but is archived/spam/deactivated/deleted-flagged or otherwise not eligible for normal site jobs according to policy.

### L5 — Teardown Planned
A destructive/uninitialize/delete action has an impact plan and retention decisions.

### L6 — WPE Runtime Detached
Jobs/caches/live grants/external active bindings that must not survive teardown have been stopped/revoked/detached according to policy.

### L7 — WPE Data Retained/Purged
Each WPE domain has applied explicit retention action; this is not necessarily full deletion.

### L8 — Site Missing / Historical
WordPress site no longer resolves, but required audit/commercial/security/recovery records may remain.

These phases are WPE coordination states, not replacements for WordPress site flags.

## 4. Coordinator inputs

Every lifecycle operation resolves:
- network ID;
- site ID;
- stable WPE site/scope identity where one exists;
- WordPress current site status;
- operation source/reason;
- actor/system authority;
- requested lifecycle operation;
- enabled/forced module policy;
- Product License allocation/environment state;
- active Jobs/Workflows/Imports/Backups/Resets;
- WPE-owned resources by topology class;
- external PT-F bindings;
- retention/security obligations;
- current Backup/recovery readiness for destructive actions.

## 5. Creation/provisioning flow

WPE must not create site-scoped resources simply because plugin code is network-active.

Conceptual flow:
1. observe/resolve newly created site;
2. confirm WordPress core initialization before site-dependent provisioning;
3. load network module/default/template policy;
4. create an idempotent WPE provisioning Plan;
5. provision only required site-scoped resources;
6. instantiate/link network templates according to explicit modes;
7. initialize site generation/cache/module state;
8. reconcile Product License allocation only if policy explicitly requires/allows it;
9. verify target-site health;
10. mark WPE site state Ready or Partial/Needs Attention.

Creation of a site never silently:
- consumes a paid allocation unless the signed plan/policy says automatic allocation is enabled;
- copies live Membership enrollments from another site;
- copies Vault plaintext;
- enables every Pro module;
- clones another site's WPE runtime data;
- creates cross-site Relations.

## 6. Provisioning idempotency

Provisioning can be triggered/retried more than once.

Each domain handler declares:
- operation key/version;
- target site;
- expected current state;
- idempotent create/verify behavior;
- rollback/recovery coverage;
- dependency ordering.

A duplicate create-site event must not duplicate Definitions, allocations, jobs or network-template instances.

## 7. Site status updates

WordPress site properties such as archived/spam/deleted/public can change without physical deletion.

WPE evaluates state transition impact rather than equating every flag with purge.

Examples:
- archived/spam site: pause nonessential public-facing jobs, block new privileged site actions where policy says, preserve data;
- restored/reactivated site: revalidate module/license/jobs/cache before resuming;
- URL/domain change: update metadata and Product License reconciliation rules, but retain stable WPE scope identity where continuity is verified;
- network reassignment/migration: treated as explicit transfer workflow, not ordinary flag update.

## 8. Pre-uninitialize/destructive impact plan

Before WPE participates in a destructive site teardown, create an impact document containing at least:
- site/network identity;
- operation kind;
- PT-A/PT-B native artifacts affected by WordPress;
- PT-C site-scoped Definitions/dependencies;
- PT-D rows by domain;
- PT-E custom/per-site tables;
- private/protected assets;
- active Jobs/Workflow/Import/Backup/Reset runs;
- Membership Enrollments/Entitlements;
- site-specific roles/configuration references;
- network-shared connection/Vault use grants;
- external PT-F product allocation/support/integration bindings;
- audit/retention classes;
- available verified Backup/recovery point where required;
- reversible vs irreversible steps;
- unresolved blockers.

High-risk network/site destructive UI must show this impact before execution.

## 9. Stop-live-work phase

Before destructive data cleanup:
- reject/stop new site mutations where appropriate;
- mark target lifecycle generation as draining/teardown;
- cancel or pause eligible queued site jobs;
- allow critical teardown/recovery jobs only through explicit class;
- invalidate authorization/query/render caches;
- prevent stale Membership grants from authorizing a now-inactive site;
- stop outgoing automation/webhook/email actions where site no longer has authority;
- record unknown external side-effects honestly.

Cancellation is cooperative and JobService-backed; queue row deletion is not business rollback.

## 10. PT-C cleanup semantics

Shared control-plane rows cannot be removed by dropping a site table.

Examples:
- site Definition identities/revisions/dependencies;
- compiled descriptors/control-plane state;
- module/site lifecycle metadata.

Default teardown is **tombstone/archive + retention policy**, not blind hard-delete.

Physical purge is separate and requires:
- reverse dependency analysis;
- network-template/shared-resource check;
- import/Backup/audit impact;
- retention expiry;
- explicit destructive authorization.

A Network-scoped Definition is never deleted because one dependent site is removed.

## 11. PT-D cleanup semantics

Shared runtime rows are selected by explicit site/scope coordinates.

Domains may include:
- Relations;
- Membership runtime;
- Workflow state;
- Notifications/Email state;
- Event Inbox;
- Audit;
- Job logical history;
- future Form/Chat profile if PT-D selected.

Each domain declares one lifecycle action:
- delete immediately;
- revoke/detach then retain for policy window;
- anonymize/pseudonymize;
- tombstone;
- preserve as network/commercial/security record;
- transfer/remap through explicit migration.

**No generic `DELETE FROM shared_table WHERE site_id = ?` across all domains.**

## 12. PT-E cleanup semantics

Per-site runtime/custom tables require inventory before drop.

Rules:
- table identity comes from WPE registry, never arbitrary prefix discovery alone;
- active migration/Backup/restore checked;
- retention/export requirement handled before drop;
- user-created Custom Table deletion policy may differ from WPE-owned operational table;
- one failed drop leaves explicit partial state/retry path;
- network upgrade registry records site/table schema state until teardown complete.

## 13. PT-F external resources

Site deletion does not automatically delete external commercial/support/provider records.

Potential actions:
- release Product License site allocation according to ADR-0070/0072;
- revoke site-use grant for a shared network connection without deleting shared credential;
- unregister webhooks where site-owned;
- mark external site binding inactive;
- preserve support/commercial/audit history according to remote retention;
- handle provider timeout/unknown outcome through reconciliation.

Local success cannot falsely claim remote deletion succeeded.

## 14. Membership lifecycle

WordPress users are network-shared, while WPE Membership is site-scoped by default.

On site teardown:
- no global WordPress user deletion by default;
- site Enrollment/Entitlement stops authorizing target site;
- billing source facts are not deleted merely because local site is removed;
- source subscription cancellation is **not** automatically implied by site deletion unless explicit reviewed action says so;
- role-sync removes only WPE-provenance site roles/grants;
- protected files/assets follow retention/export policy;
- member privacy/export records follow site policy.

## 15. Relations lifecycle

For R1/PT-D shared edges:
- edges owned by deleted site are selected by explicit scope;
- cross-site edges, if future profile exists, require two-sided policy instead of one-side bulk deletion;
- restrict/detach/archive policies applied by Relation Definition;
- endpoint deletion outside WPE can create orphan state requiring repair.

For R2/PT-E profile, per-site relation table cleanup follows PT-E rules.

## 16. Jobs/workflows/cron lifecycle

Every queued/logical job carries target site scope.

When site becomes restricted/missing:
- new site jobs rejected according to job class;
- queued jobs re-resolve site state before execution;
- nonessential jobs become skipped/cancelled with reason;
- teardown/recovery child jobs remain allowed only with coordinator authority;
- recurring schedules pause/remove according to lifecycle policy;
- network coordinator marks site child result without aborting unrelated sites;
- Job/Audit history retention remains independent from Action Scheduler cleanup.

## 17. Backup/restore lifecycle

Before destructive purge where policy requires recovery:
- require suitable verified restore point;
- record Backup ID/verification class in impact plan;
- distinguish site Backup from full network Backup;
- shared PT-C/PT-D rows exported by site scope;
- PT-E tables exported as site-owned artifacts;
- network/shared user tables handled explicitly, not copied as ordinary site table data.

A successful backup upload without restore verification may be insufficient for destructive Level-3 operation according to policy.

## 18. Site record deletion vs uninitialization

WPE tracks these separately:
- site may be uninitialized/cleaned while a lifecycle record still matters;
- site database row can be deleted after/beside cleanup paths;
- hooks may fail or third-party code may bypass expected sequence;
- coordinator therefore reconciles actual observed site/tables/resources, not just event order.

Missing WordPress site with remaining WPE rows becomes an explicit orphaned-site lifecycle state for diagnostics/recovery.

## 19. Transfer/migration lifecycle

Moving site between hosts within same identity differs from moving site between Multisite networks.

### Same logical site/host migration
Preserve stable WPE site allocation/scope identity where verified; reconcile domain/host metadata and restored caches/tokens.

### Network-to-network transfer
Requires explicit mapping Plan for:
- new network/site coordinates;
- PT-C Definitions/templates;
- PT-D runtime rows;
- PT-E tables;
- network-shared Vault/Connections references;
- Product License allocation;
- roles/capabilities;
- Membership/billing source mapping;
- Backups/private assets;
- external bindings.

Do not mutate `network_id/site_id` across all shared rows with one blind SQL update.

## 20. Clone lifecycle

A site clone is not ordinary creation.

Clone classifier uses ADR-0070/0072 semantics:
- staging clone;
- development clone;
- migration target;
- DR restore;
- unknown/possible production clone.

WPE site-scoped runtime copy defaults vary by domain. Membership enrollments, OAuth tokens, production webhook bindings and live Product License allocation are never silently activated on clone.

## 21. Coordinator journal

Long/destructive lifecycle operation needs durable logical journal separate from ephemeral queue backend.

Conceptual fields:
- lifecycle operation UUID;
- network/site scope;
- operation type/reason;
- actor/system authority;
- source site state snapshot/fingerprint;
- Plan version;
- current phase;
- domain step states;
- child Job IDs/correlation;
- external reconciliation references;
- reversible/irreversible markers;
- failure/recovery status;
- started/updated/completed timestamps.

Exact physical topology is future evidence, likely PT-C/PT-D depending volume/retention.

## 22. Step state model

Each domain step:
- `pending`;
- `running`;
- `succeeded`;
- `skipped_not_applicable`;
- `blocked`;
- `retryable_failure`;
- `permanent_failure`;
- `remote_outcome_unknown`;
- `compensated` where a real compensation exists.

Do not call a lifecycle run fully successful when required steps remain unknown/failed.

## 23. Ordering model

Paper dependency order for destructive teardown:
1. authorize + lock lifecycle operation;
2. impact Plan + recovery requirement;
3. drain mutations/jobs;
4. invalidate access/cache/live grants;
5. detach/reconcile external active bindings;
6. domain-specific runtime cleanup/retention;
7. control-plane tombstone/archive;
8. PT-E table cleanup if approved;
9. WordPress/native lifecycle step if WPE initiated it;
10. reconcile actual final site/resource state;
11. health/audit/report.

Third-party/core-triggered lifecycle may enter at a later point; coordinator detects missed prerequisites and records degraded/manual-recovery state rather than pretending full ordered execution occurred.

## 24. Site initialization safety

Provisioning occurs only after target site is sufficiently initialized for required WordPress APIs/storage.

A site creation event is not proof that every module dependency, external service or Pro entitlement is ready.

Provisioning failures must leave the WordPress site usable where possible and show WPE Partial/Needs Attention rather than corrupting core site creation.

## 25. Observability

Network Admin diagnostics need:
- WPE lifecycle state per site;
- provisioning version;
- PT-E schema drift where applicable;
- active/blocked lifecycle runs;
- orphan shared rows/resources;
- paused jobs;
- Product License allocation conflict;
- Backup/recovery readiness;
- last lifecycle error/correlation ID.

Do not expose site secrets/private member data in network diagnostics.

## 26. Privacy/retention

Site deletion is not universal right-to-erasure and not universal retention justification.

Each domain follows its data classification/retention policy. Personal data exporter/eraser obligations remain separate from operational site teardown.

Where historical records are retained, minimize/anonymize according to purpose and legal/product policy.

## 27. Future evidence — NOT AUTHORIZED

Fixtures after explicit owner consent:
- normal site creation/initialization;
- duplicate provisioning trigger;
- provisioning partial failure/retry;
- archived/spam/deleted flag changes;
- reactivate archived site;
- domain/path change;
- WordPress uninitialize path;
- WordPress site-record delete path;
- delete invoked by third-party/bypassed expected WPE preflight;
- active Job/Workflow during deletion;
- Membership active during deletion;
- Product License allocation release timeout;
- shared Vault/Connection delegation cleanup;
- PT-C row tombstone;
- PT-D domain retention variants;
- PT-E table drop partial failure;
- Site Backup scoped extraction;
- network-to-network transfer;
- staging clone / production clone / DR restore;
- 100/1k/10k-site provisioning/upgrade/cleanup fan-out;
- crash/restart at every journal phase;
- idempotent retry;
- wrong-site/scope IDOR/destructive attack fixtures.

Executed lifecycle fixtures: **0**.

## 28. Development gate

No WordPress hook, lifecycle handler, table, job, deletion routine, Product License call, migration, fixture network or test may be implemented/executed before explicit owner development consent under ADR-0014.
