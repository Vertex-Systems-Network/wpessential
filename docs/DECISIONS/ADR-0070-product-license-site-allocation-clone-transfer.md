# ADR-0070 — Product License Site Allocation, Clone & Transfer Architecture

Status: **Accepted commercial/platform architecture / service evidence pending**  
Date: 2026-08-28

## Context

WPEssential Pro licensing must work across single-site WordPress, Multisite networks, site allocations, staging copies, migrations, domain changes, disaster restores and account transfers without becoming a hidden replacement for WordPress authorization or WPE Membership access control.

Binding a license only to hostname or blindly copying cached entitlement through cloned databases would create operational, commercial and security failures.

## Decision

WPE product licensing uses explicit opaque identities and signed entitlement facts:
- WPE account/license identity;
- local installation UUID;
- network activation identity where applicable;
- stable WPE site-allocation UUID;
- environment class;
- signed entitlement document.

Hostname/domain is metadata and binding signal, **not sole license identity**.

Product entitlement remains completely separate from:
- WordPress site/network authority;
- WPE Membership Entitlement;
- site user roles/capabilities;
- billing-provider status strings.

Multisite supports explicit commercial modes such as network-wide entitlement, selected site allocations or hybrid models according to the signed plan contract. Account connection alone never auto-allocates every site.

Site-count policy is explicit in entitlement/service contract and is not inferred by covertly counting every `wp_blogs` row.

Cloned environments enter explicit states such as review/staging/migration/revalidation rather than being treated as a legitimate second production activation automatically.

Staging/development/disaster environments are separate environment classes and may have plan-specific allocation rules.

Domain changes and host migrations preserve allocation identity when continuity is verified. Site transfer between Multisite networks remaps network scope, site allocation, shared connection/Vault references and entitlement allocation explicitly.

Service outage is not product expiry. Signed cached entitlement and offline-grace/freshness semantics remain authoritative according to ADR-0017/0042. Expiry/revocation never disables WPE Membership protection or deletes product data.

## Privacy

Activation/allocation transmits only fields needed for commercial entitlement. Account connection is not telemetry consent. Site-count licensing does not authorize hidden crawling/inventory of unrelated network sites.

## Security

- local activation/transfer requires WordPress authority + WPE Policy;
- remote ownership/account changes require WPE account authorization;
- signed entitlement verification is independent from ordinary API responses;
- OAuth/refresh tokens remain Vault/P3;
- allocation IDs are not authorization tokens;
- cloned/restored entitlement caches must reconcile and cannot bypass anti-rollback/freshness rules.

## Consequences

Positive:
- migrations/domain changes can preserve legitimate allocations;
- staging/DR can be modeled without unsafe license copying;
- Multisite site-count semantics become explicit and auditable;
- product licensing cannot accidentally expose member content;
- less need for invasive installation fingerprinting.

Costs:
- remote service must model installations/networks/site allocations/transfers;
- clone/migration states need reconciliation UX;
- concurrent allocation/release races require idempotency and evidence;
- plan catalog must explicitly declare allocation/count policy.

## Evidence still required

After explicit owner development/service consent:
- single-site/network activation;
- selected-site allocation;
- site-count exhaustion/release races;
- staging clone and duplicate production clone;
- domain/host migration;
- network-to-network site transfer;
- deleted/recreated site identity;
- backup restore with stale entitlement;
- service outage/offline grace;
- expiry vs revocation;
- account ownership transfer;
- privacy/no-hidden-inventory proof;
- token/secret redaction;
- Free↔Pro version skew.

No licensing/service fixture has been executed.

## Development gate

Acceptance of this architecture does not authorize licensing service, OAuth, entitlement, UI, clone-detection, site-allocation or migration implementation. ADR-0014 remains the hard consent gate.
