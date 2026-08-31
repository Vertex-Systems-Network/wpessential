# WPEssential — Product License, Site Allocation, Clone & Transfer Semantics

Status: **Phase 0 planning only / no service or licensing implementation authorized**  
Date: 2026-08-28  
Related: ADR-0001, ADR-0007, ADR-0017, ADR-0034, ADR-0042, ADR-0054, ADR-0060, ADR-0069.

## 1. Purpose

WPEssential product licensing must control commercial feature entitlement without becoming a hidden authorization system for site users, Membership content, WordPress roles or site ownership.

This document defines how one WPE account/license can represent:
- one WordPress single site;
- one Multisite network;
- site allocations inside a network;
- staging/development clones;
- site/domain migration;
- disaster restore;
- ownership/account transfer;
- expired/offline entitlement.

It is a commercial/platform contract, not a payment-processing implementation.

## 2. Hard separation

These remain distinct:

- **WPE Product Entitlement** — whether WPE Pro modules/features may be managed/edited/used according to commercial contract;
- **WordPress Site/Network Authority** — who may administer target site/network;
- **WPE Membership Entitlement** — visitor/member access to site content/features;
- **Billing Subscription/Purchase** — source commercial facts from WPE's own account service/payment system.

Product license expiry or WPE service outage must never make protected Membership content public.

## 3. Canonical license identities

Logical identities:
- WPE account ID;
- license/subscription ID;
- product/tier ID;
- installation identity;
- network identity where Multisite;
- allocated site identity;
- environment class;
- signed entitlement document ID/version.

Do not use hostname alone as stable license identity.

## 4. Installation identity

A WPE installation receives a locally generated opaque installation UUID.

Properties:
- high entropy;
- not derived from domain, DB credentials, admin email or filesystem path;
- stored locally as product platform state;
- included in signed entitlement binding where contract requires;
- regenerated only through explicit transfer/recovery rules, not casually on plugin reactivation.

Installation UUID is not analytics identity and does not opt in telemetry.

## 5. Multisite network identity

For Multisite, product service can bind one installation/network identity separately from individual site allocations.

Network identity includes:
- installation UUID;
- WordPress network ID locally;
- remote opaque network activation ID where needed;
- commercial plan/site allowance;
- allocation policy.

WordPress numeric network/site IDs are local coordinates, not globally stable remote identities.

## 6. Site allocation identity

A licensed site allocation uses a stable WPE site-allocation UUID mapped locally to:
- network ID;
- WordPress site/blog ID;
- current home/site URL summary;
- environment class;
- allocation state.

Domain/path changes update metadata, not allocation identity, when continuity is verified.

## 7. Environment classes

Initial commercial environment classes:
- `production`;
- `staging`;
- `development`;
- `temporary_migration`;
- `disaster_recovery`.

Exact plan allowances are service/catalog configuration, not hardcoded product semantics.

Environment class affects allocation/counting only if plan contract says so.

## 8. Site-count rules

WPE must not silently count every row in `wp_blogs` as a paid seat without the commercial contract saying so.

Possible plan policies:
- per installation;
- per network;
- per active production site allocation;
- production sites count, approved staging mirrors do not;
- fixed site allowance;
- agency/unlimited-style allowance.

The signed product entitlement explicitly states the allocation policy/limit understood by client.

## 9. Activation flow

Site/network activation is explicit:
1. local authorized admin opens WPE account connection;
2. OAuth/account link under ADR-0034;
3. client requests activation/allocation for explicit target scope;
4. service returns signed entitlement/allocation facts;
5. client verifies signature/binding/freshness;
6. local product state updates;
7. audit event records target scope and result.

Account connection alone does not allocate every site automatically.

## 10. Network activation modes

Potential plan modes:

### Network-wide entitlement
One commercial entitlement covers all eligible sites in network according to plan.

### Site allocation entitlement
Network is connected, but Pro management is allocated only to selected sites.

### Hybrid
Network platform/admin features covered network-wide while selected site-level Pro modules consume allocations.

UI must make mode explicit.

## 11. Site activation inside Multisite

A Network Admin can allocate eligible sites according to plan and WPE Policy.

Site Admin cannot self-allocate a paid seat unless plan/policy explicitly permits it.

Allocation actions show:
- site label/domain;
- current environment class;
- current allocation state;
- remaining allowance when service contract provides it;
- impact if activation would consume/change paid allocation.

## 12. Clone detection principles

A cloned database/site can copy:
- installation UUID;
- allocation UUID;
- entitlement cache;
- OAuth credentials;
- Vault material.

Therefore clone handling is security/commercial-critical.

WPE must not assume copied identifiers represent legitimate second installation.

Signals can include:
- changed canonical URL/domain/path;
- changed environment declaration;
- restored backup marker;
- explicit staging-clone action;
- service-side concurrent activation conflict;
- manually initiated transfer.

No invasive fingerprinting/telemetry is required by default.

## 13. Clone states

Candidate local states:
- `normal`;
- `possible_clone_review`;
- `approved_staging_clone`;
- `migration_in_progress`;
- `transfer_required`;
- `entitlement_revalidation_required`.

A possible clone does not immediately break public site output.

## 14. Staging clone contract

If plan allows staging:
- production allocation remains primary;
- staging gets separate local site/install identity or approved mirror identity;
- staging does not reuse production OAuth refresh credentials blindly;
- service issues separate scoped activation/entitlement facts;
- staging cannot mutate production allocation or support/private account state without authorization;
- environment label visible in UI.

## 15. Development/local environments

Development allowance can use:
- recognized local environment classification;
- explicit user declaration;
- service-granted development activation.

Local/dev does not automatically mean free/unlimited Pro forever unless product plan explicitly provides it.

## 16. Domain change

Legitimate domain change should not force unnecessary seat loss.

Flow:
1. detect current domain differs from recorded metadata;
2. preserve allocation UUID locally;
3. ask/revalidate when risk threshold requires;
4. service updates allocation metadata after authorization;
5. signed entitlement reflects current binding if binding profile includes domain metadata.

Domain is metadata/binding signal, not sole license identity.

## 17. Site migration between hosts

Expected migration:
- same site/allocation moves to new host;
- old host can be deactivated or temporarily coexist during migration window;
- transfer flow avoids double-counting where contract permits;
- both endpoints cannot indefinitely share one allocation if plan does not allow it.

Unknown-outcome transfer states are explicit.

## 18. Site transfer between Multisite networks

Moving one site from Network A to Network B changes local scope coordinates.

WPE must remap:
- WPE site allocation UUID;
- new network/install identity;
- site-owned definitions/runtime;
- shared network connection refs;
- network Vault refs;
- module policies;
- product entitlement allocation.

Old network-shared credentials are not automatically copied to new network.

## 19. Ownership/account transfer

Changing WPE account owner is separate from WordPress admin/user changes.

Transfer requires explicit remote account authorization.

Local WordPress administrator cannot silently take ownership of remote WPE commercial account simply because they control wp-admin.

After transfer:
- old account credentials revoked;
- new account entitlement issued;
- local service connection re-bound;
- support/commercial history visibility follows service policy;
- audit records transfer.

## 20. Site deletion

Deleting a WordPress site does not automatically delete remote commercial records.

Local behavior:
- site allocation becomes `site_missing/deallocation_pending` until explicit/reconciled release;
- local site-owned entitlement cache inaccessible with deleted site;
- network admin can release allocation where contract permits.

Remote retention follows commercial/security policy.

## 21. Network deletion / reinstall

Destructive reinstall/network replacement can invalidate local identities.

Recovery path uses:
- WPE account authentication;
- remote activation/allocation records;
- signed entitlement;
- documented transfer/recovery action.

Do not require old DB availability as sole recovery mechanism.

## 22. Disaster restore

Restoring a backup may restore stale license state.

Rules:
- signed entitlement anti-rollback/freshness checks remain active;
- restored OAuth tokens may be invalid/revoked and require reconnection;
- installation/site allocation identity may be recoverable but must reconcile against service;
- a DR environment can use explicit temporary/disaster class if plan allows;
- public deployed output follows ADR-0007 safe-runtime preservation rules during revalidation.

## 23. Expiry behavior

On legitimate product entitlement expiry:
- Free CPT/Taxonomy remain available;
- Pro creation/editing/unsafe mutation can lock according to ADR-0007;
- existing safe deployed output remains rendered where architecture permits;
- data/definitions preserved;
- background operations requiring active Pro entitlement can pause safely;
- public Membership/content protection remains enforced independently.

No destructive data deletion on expiry.

## 24. Service outage vs entitlement expiry

Service unreachable is not same as license expired.

Client uses signed cached entitlement with:
- issued-at;
- expiry/freshness window;
- offline grace semantics;
- anti-rollback state.

UI distinguishes:
- Active;
- Active — offline cached;
- Revalidation needed;
- Expired;
- Revoked;
- Allocation conflict;
- Service unavailable.

## 25. Revocation

Security/fraud/manual revocation is stronger than ordinary expiry and must be signed/verified source fact.

Even on revocation:
- no public Membership bypass;
- no secret/data deletion;
- high-risk Pro operations blocked;
- audit/support path remains available where safe.

## 26. Entitlement cache

Local cache stores only necessary signed claims and derived state.

It does not need:
- full billing history;
- payment card data;
- unrelated profile PII;
- support messages;
- analytics profile.

Cache is site/network scoped and privacy-minimized under ADR-0060.

## 27. Site allocation API semantics

Conceptual remote resources:
- Account;
- License/Subscription;
- Installation Activation;
- Network Activation;
- Site Allocation;
- Signed Entitlement.

Mutations are idempotent where possible:
- allocate site;
- release allocation;
- mark/approve staging;
- begin transfer;
- complete transfer;
- cancel transfer;
- reconcile clone.

Exact HTTP/OpenAPI schemas remain evidence/service implementation work.

## 28. Conflict handling

Examples:
- same allocation active on two production sites unexpectedly;
- cloned DB appears on new domain;
- old host still online after migration;
- network site ID reused after delete/recreate;
- local cached allocation not found remotely.

State becomes review/revalidation required rather than destructive automatic guessing.

## 29. Privacy

Activation transmits only fields needed for commercial entitlement/allocation.

No hidden full site inventory/content/plugin/theme list is required by this architecture.

When site-count plans need allocations, service receives explicit allocated-site records, not covert crawl of all network sites unless future plan/service contract clearly discloses and justifies it.

Account connection still does not opt into telemetry.

## 30. Security

- activation/transfer requires local WordPress authority + WPE Policy;
- remote account actions require valid WPE account auth;
- signed entitlement verified independently from ordinary API response;
- no license decision solely from editable local option;
- clone conflicts cannot expose account/support secrets;
- OAuth/refresh tokens stay Vault/P3;
- allocation IDs are opaque and non-secret but not authorization tokens;
- API errors are safe/redacted.

## 31. Audit

Record:
- account connection/disconnection;
- activation/deactivation;
- site allocate/release;
- staging approval;
- clone conflict detection/reconciliation;
- domain update;
- transfer begin/complete/cancel;
- entitlement expiry/revocation/recovery;
- actor + target site/network + correlation.

Do not log bearer/refresh tokens or signed upload URLs.

## 32. Future evidence — NOT AUTHORIZED

After explicit development/service consent, test:
- single-site activation;
- Multisite network activation;
- selected site allocation;
- site-admin unauthorized allocation;
- staging clone from production DB;
- duplicate production clone conflict;
- domain rename;
- host migration overlap window;
- transfer between networks;
- deleted/recreated site ID;
- backup restore with stale entitlement;
- service outage/offline grace;
- expired vs revoked behavior;
- site-count exhaustion;
- release/reallocate race;
- concurrent allocation requests;
- Free↔Pro version skew;
- token/secret redaction;
- no hidden telemetry/site inventory;
- account ownership transfer;
- disaster recovery.

No service call or entitlement fixture has been executed.

## 33. Development gate

This document authorizes no licensing API, UI, service endpoint, OAuth, entitlement code, clone detector or allocation implementation. Explicit owner development consent remains required under ADR-0014.
