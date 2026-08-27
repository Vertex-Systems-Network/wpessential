# WPEssential — Product License Remote Resource & Conflict-State Model

Status: **Phase 0 planning only / no service implementation authorized**  
Date: 2026-08-28  
Related: ADR-0014, ADR-0017, ADR-0034, ADR-0042, ADR-0054, ADR-0060, ADR-0069, ADR-0070.

## 1. Purpose

ADR-0070 defines product-license identity, allocation, clone, migration and transfer semantics. This document narrows the future remote-service resource model and the client-visible state machine without creating any API, database, entitlement signer or service call.

The model must prevent these common licensing failures:
- hostname treated as identity;
- copied database silently becoming another paid production activation;
- service outage treated as license expiry;
- one stale local option granting Pro indefinitely;
- site deletion automatically destroying commercial/audit history;
- a Multisite account connection silently allocating all sites;
- transfer/clone conflicts resolved by destructive guessing;
- product license state accidentally controlling Membership authorization.

## 2. Authoritative remote resources

The future WPE service separates at least these resource classes.

### Account
Commercial/support identity. It does not grant local WordPress authority.

Candidate fields:
- opaque account ID;
- account status;
- organization/team membership summary where needed;
- locale/region preferences where needed;
- no unnecessary site inventory.

### Product Contract
Represents purchased/trial commercial terms.

Candidate fields:
- contract/license ID;
- product/tier ID;
- lifecycle state;
- start/end dates where applicable;
- allocation policy ID/version;
- production-site/network allowance;
- staging/development/DR allowance policy;
- support/update rights;
- feature-entitlement policy reference.

Billing/card data is not replicated into the WordPress client.

### Installation Activation
One logical WPE installation identity.

Candidate fields:
- remote activation ID;
- local installation UUID binding;
- environment class;
- activation state;
- created/last-reconciled timestamps;
- optional canonical URL metadata;
- current ownership/account binding;
- transfer/clone flags;
- no content/plugin/theme inventory requirement.

### Network Activation
Exists only when a Multisite network needs explicit commercial network scope.

Candidate fields:
- remote network activation ID;
- installation activation ID;
- local network coordinate as metadata;
- plan allocation mode;
- network policy version;
- activation state.

### Site Allocation
Consumes or represents a site entitlement according to Product Contract.

Candidate fields:
- opaque allocation ID;
- installation/network activation ID;
- locally mapped site UUID/site ID metadata;
- environment class;
- allocation state;
- current URL metadata;
- production-counting flag derived from contract policy;
- lineage/source allocation for clone/migration where applicable;
- transfer state;
- created/updated/reconciled timestamps.

### Signed Product Entitlement
Cryptographically authoritative client-consumable feature state under ADR-0017/0042.

Candidate claims:
- issuer/keyset/profile version;
- contract/product/tier;
- installation/network/site binding;
- allocation IDs/coverage mode;
- allowed feature/suite rights;
- environment class/policy;
- issued-at/not-before/expiry/freshness;
- revocation/keyset metadata references;
- entitlement schema version.

Ordinary Account/API JSON never substitutes for the signed entitlement.

## 3. Local references and cache

WordPress stores only minimum local references/cache:
- installation UUID;
- remote activation/network/allocation IDs;
- current environment declaration;
- last verified signed entitlement;
- anti-rollback/freshness metadata;
- reconciliation state;
- conflict code and safe user-facing summary;
- last successful/failed reconciliation timestamps;
- no account password;
- no card/payment data;
- no unnecessary remote profile data.

OAuth refresh/access credentials remain Vault/P3 and are not part of entitlement data.

## 4. State families

Do not collapse all commercial state into one `active` boolean.

### Contract state
Candidate values:
- `trialing`;
- `active`;
- `grace`;
- `expired`;
- `suspended`;
- `revoked`;
- `terminated`.

Exact billing-provider facts remain server-side and are normalized into product-contract semantics.

### Activation state
Candidate values:
- `unlinked`;
- `linked_unallocated`;
- `active`;
- `offline_cached`;
- `revalidation_required`;
- `transfer_pending`;
- `clone_review`;
- `service_unavailable`;
- `expired`;
- `revoked`;
- `disconnected`.

### Site allocation state
Candidate values:
- `unallocated`;
- `reserved`;
- `active`;
- `staging_approved`;
- `development_approved`;
- `migration_source`;
- `migration_target`;
- `dr_temporary`;
- `release_pending`;
- `released`;
- `conflict`;
- `site_missing`;
- `transfer_pending`;
- `revoked`.

Environment class and allocation state remain separate dimensions.

## 5. Conflict codes

Machine-readable conflict code is separate from lifecycle state.

Initial candidates:
- `allocation_limit_exceeded`;
- `allocation_already_bound_elsewhere`;
- `possible_production_clone`;
- `duplicate_installation_identity`;
- `stale_restored_entitlement`;
- `site_id_reuse_detected`;
- `domain_changed_revalidation_required`;
- `old_host_still_active`;
- `transfer_source_not_released`;
- `transfer_target_not_authorized`;
- `network_binding_changed`;
- `account_ownership_changed`;
- `contract_no_longer_covers_environment`;
- `signed_entitlement_binding_mismatch`;
- `entitlement_rollback_detected`;
- `remote_allocation_missing`;
- `local_allocation_missing`;
- `service_state_ambiguous`.

A conflict never silently grants a second production allocation.

## 6. Core transitions

### New activation
`unlinked → linked_unallocated → reserved/active → signed entitlement verified`

Preconditions:
- local WordPress authority + WPE Policy;
- successful WPE account authorization;
- explicit target scope;
- contract capacity/policy permits allocation.

Failure leaves prior safe state intact.

### Release
`active → release_pending → released`

Unknown remote outcome stays `release_pending` until reconciliation; never immediately reuses the same seat based only on local optimism.

### Production clone detected
`active(copy) → clone_review/conflict`

Public safe deployed runtime follows ADR-0007 while privileged editing/mutation may require revalidation. The original production allocation is not automatically revoked solely because a copied DB appeared.

### Approved staging clone
`clone_review → staging_approved`

Requires service/plan authorization and separate scoped entitlement. Production OAuth/token material is not blindly reused.

### Migration
Source:
`active → migration_source → released/retired`

Target:
`unallocated/clone_review → migration_target → active`

Overlap window is policy/time-bound and visible. Unknown transfer result remains reconcilable, not guessed.

### Site transfer between networks
`active(old network) → transfer_pending → active(new network)`

Network-bound resources/refs must be separately remapped. Allocation transition alone does not copy network Vault secrets/connections.

### Service outage
`active → offline_cached/service_unavailable`

This transition does **not** mean `expired`.

### Expiry
Only verified time/contract/entitlement facts can move effective product state to expiry semantics.

### Revocation
Requires verified signed/authoritative source fact. Revocation blocks high-risk Pro management but does not bypass Membership protection or delete site data.

## 7. Idempotent mutation model

Future service mutations should support idempotency for operations where retries are expected:
- activate installation/network;
- allocate site;
- release site;
- classify environment;
- approve staging;
- begin transfer;
- complete/cancel transfer;
- reconcile clone;
- reconnect/rebind after recovery.

Conceptual request identity includes:
- operation UUID/idempotency key;
- actor/account context;
- target installation/network/site allocation;
- expected current resource version where concurrency-sensitive.

A retry returns the same logical result instead of consuming another seat.

## 8. Optimistic concurrency

Remote mutable resources expose an opaque version/generation or equivalent precondition token.

Examples requiring conflict-safe mutation:
- two admins allocate the last available production seat;
- two release/reallocate operations race;
- transfer and deletion happen concurrently;
- staging classification changes while contract policy changes;
- account ownership transfer overlaps local revalidation.

Last-write-wins is not acceptable for allocation capacity or ownership transfer.

## 9. Allocation capacity semantics

Server is authoritative for commercial capacity.

Client may display cached `used/available` counts for UX but must treat them as advisory until mutation response/signed entitlement confirms result.

Capacity calculation uses explicit Product Contract policy, not hidden crawling of every WordPress site.

## 10. Clone lineage

Optional clone/migration lineage records can include:
- source installation/allocation ID;
- source environment class;
- target environment class;
- explicit operation type (`staging_clone`, `migration`, `dr_restore`, `unknown_clone`);
- operation correlation ID;
- approval/expiry window.

Lineage is commercial/reconciliation metadata, not user-tracking telemetry.

## 11. Deleted/recreated site identity

WordPress numeric site/blog IDs may be reused or remapped during migration/restore.

Therefore allocation identity is not keyed solely by `blog_id`.

On site disappearance:
- local/network state records `site_missing`;
- capacity is not blindly released until policy/reconciliation permits;
- recreated site with same numeric ID is evaluated as a new/reattached identity according to continuity evidence.

## 12. Domain/host changes

Domain is mutable metadata.

Low-risk continuity may update metadata without consuming a new allocation. High-risk divergence moves to revalidation/clone review.

No decision uses domain equality alone as authentication.

## 13. Offline and freshness model

Effective state combines:
- last verified signed entitlement;
- signed expiry/freshness claims;
- anti-rollback local state;
- current time validity checks;
- service availability;
- revocation evidence if available.

UI distinguishes at minimum:
- Verified Active;
- Active from signed offline cache;
- Revalidation Required;
- Service Unavailable;
- Expired;
- Revoked;
- Allocation Conflict.

## 14. Local failure policy

When local persistence fails after remote mutation:
- keep correlation/idempotency key if safely available;
- reconciliation retrieves authoritative remote state;
- do not repeat a capacity-consuming mutation with a fresh key unless reviewed;
- audit `remote_success_local_commit_unknown`.

When remote result is unknown after request timeout:
- mark `service_state_ambiguous`;
- reconcile by operation/allocation identity;
- do not assume failure and allocate again.

## 15. Remote API boundary

Conceptual operations are resource-oriented, not arbitrary commands.

Potential categories:
- read contract/entitlement summary;
- list explicitly allocated sites for current account/network scope;
- create/reconcile installation activation;
- create/release site allocation;
- set approved environment class;
- begin/complete/cancel transfer;
- reconcile clone/conflict;
- fetch signed entitlement/keyset metadata references.

Exact paths, HTTP verbs, schemas and rate limits remain future service/OpenAPI work.

Errors follow ADR-0054 RFC 9457 Problem Details direction and expose stable application error codes without internal stack/secrets.

## 16. Security invariants

1. Local WordPress admin authority alone cannot seize a remote WPE account.
2. Account login alone does not bypass target-site/network authorization.
3. Allocation ID is not a bearer credential.
4. Editable local options cannot manufacture Pro entitlement.
5. Signed entitlement verification is separate from authenticated API response.
6. Conflict recovery never discloses another customer's account/site details.
7. Clone detection does not require invasive content fingerprinting.
8. Tokens and secrets remain Vault/P3.
9. Product entitlement cannot become Membership authorization.
10. Expiry/revocation cannot delete user/site data automatically.

## 17. Privacy/retention

Remote allocation records contain only commercial/reconciliation metadata required for the service contract.

Potential retained commercial/security facts:
- allocation/transfer history;
- contract lifecycle;
- fraud/security revocation record;
- audit-safe site/domain metadata where contract requires it.

Client-side disconnect is not equivalent to server-side deletion. Export/delete/retention behavior must match ADR-0060 and applicable service policy.

## 18. Observability

Audit-safe events:
- activation requested/succeeded/failed;
- allocation reserved/activated/released;
- conflict detected/resolved;
- staging approved/expired;
- migration/transfer begun/completed/cancelled;
- entitlement verified/stale/expired/revoked;
- service outage/recovery;
- account ownership transfer.

Never log OAuth tokens, private signing material, recovery secrets or full signed bearer-like artifacts where unnecessary.

## 19. Future evidence — NOT AUTHORIZED

After explicit owner development/service consent:
- OpenAPI/schema conformance;
- idempotency retry tests;
- last-seat concurrent allocation race;
- release/reallocate race;
- remote-success/local-failure recovery;
- timeout/unknown-outcome reconciliation;
- production clone conflict;
- staging approval/expiry;
- same-site domain migration;
- old/new host overlap;
- network transfer;
- deleted/recreated blog ID;
- stale Backup-restored entitlement;
- service outage vs expiry;
- revocation propagation;
- ownership transfer;
- signed-entitlement binding mismatch;
- anti-rollback;
- no hidden site inventory/telemetry.

**Executed fixtures: 0.**

## 20. Development gate

This document does not authorize remote service code, OpenAPI implementation, licensing tables, client API calls, entitlement signing, clone detection code or allocation UI. ADR-0014 explicit consent remains mandatory.
