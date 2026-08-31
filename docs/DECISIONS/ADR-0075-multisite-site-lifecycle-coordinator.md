# ADR-0075 — Multisite Site Lifecycle Coordinator

Status: **Accepted paper architecture / runtime evidence pending**  
Date: 2026-08-28

## Context

WPE Multisite uses mixed storage/topology classes (PT-A…PT-F), shared Jobs, Membership, Vault/Connections, Backup/Restore and remote Product License allocations. WordPress itself separates site insertion/initialization, site property updates, uninitialization and site-row deletion. Independent module hooks would create race conditions, duplicated cleanup and false completion claims.

## Decision

WPE adopts one **Site Lifecycle Coordinator** for site creation/provisioning, restricted-state changes, destructive teardown, clone/migration/transfer and final reconciliation.

The coordinator:
- uses explicit network/site scope;
- creates an impact Plan for destructive operations;
- drains live work before data cleanup where possible;
- dispatches idempotent domain handlers;
- understands PT-C shared control-plane, PT-D shared runtime, PT-E per-site tables and PT-F external authority separately;
- journals long/destructive lifecycle work independently of the queue backend;
- re-resolves actual WordPress/site/resource state instead of trusting event order alone;
- preserves domain-specific retention rather than issuing one generic shared-table delete;
- coordinates Product License allocation, Membership site authorization, Jobs, Vault/Connections, Relations, Backup/Restore and external bindings without conflating their authority.

WordPress site record deletion, site uninitialization and archive/spam/deleted property changes remain distinct lifecycle facts.

## Provisioning rule

Network activation does not auto-provision every WPE module/resource. New-site provisioning applies only explicit network policy/templates and is idempotent.

Site creation never silently:
- consumes paid allocation unless contract/policy permits it;
- copies Membership enrollments;
- copies Vault plaintext;
- enables every Pro module;
- creates cross-site Relations;
- activates production webhook/OAuth bindings from a clone.

## Teardown rule

Destructive flow conceptually orders:
1. authorize + acquire lifecycle operation ownership;
2. impact/recovery Plan;
3. drain mutations/jobs;
4. invalidate access/cache/live grants;
5. detach/reconcile external active bindings;
6. apply domain-specific PT-D retention/cleanup;
7. archive/tombstone PT-C resources;
8. clean PT-E tables only when approved;
9. execute/observe native WordPress lifecycle step;
10. reconcile actual final state;
11. audit/health/report.

If WordPress/third-party deletion bypasses WPE preflight, coordinator records degraded/orphan recovery state instead of claiming orderly teardown.

## Journal semantics

Lifecycle run/step state is durable and includes retryable/permanent failure and `remote_outcome_unknown`. Queue cancellation/deletion is not business rollback.

Exact journal tables/topology remain evidence-gated.

## Evidence still required

After explicit owner consent:
- site create/init/update/uninitialize/delete hooks;
- duplicate provisioning/idempotency;
- partial provisioning failure;
- archive/reactivation;
- active jobs/workflows during deletion;
- Membership and protected assets;
- PT-C tombstone, PT-D retention, PT-E partial drop;
- Product License release timeout/unknown outcome;
- Vault/shared connection delegation cleanup;
- Site Backup extraction/recovery;
- transfer/clone/DR restore;
- crash/restart at every lifecycle phase;
- 100/1k/10k-site fan-out;
- scope/IDOR destructive regression fixtures.

Executed lifecycle fixtures: **0**.

## Development gate

This ADR authorizes no WordPress hooks, lifecycle handlers, jobs, tables, cleanup, site deletion, service calls, migrations or tests. ADR-0014 explicit owner consent remains required.
