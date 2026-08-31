# ADR-0072 — Product License Remote Resource & Conflict-State Model

Status: **Accepted paper architecture / service evidence pending**  
Date: 2026-08-28

## Context

ADR-0070 defines product-license installation/network/site-allocation identity, clone/staging/migration/transfer and outage-vs-expiry semantics. A future WPE account service still needs stable resource boundaries, lifecycle states and conflict handling so retries, clones, migrations and Multisite site-allocation races do not become ad-hoc licensing behavior.

## Decision

WPE product licensing separates these remote resource classes:
- Account;
- Product Contract;
- Installation Activation;
- Network Activation where Multisite requires it;
- Site Allocation;
- independently signed Product Entitlement.

Ordinary authenticated service JSON never substitutes for signed entitlement authority.

Commercial/runtime state is multidimensional rather than one `active` boolean. Contract state, Activation state, Site Allocation state, environment class and machine-readable conflict code remain separate.

Allocation/site identity uses opaque WPE identities. Hostname/domain and WordPress numeric site ID are mutable metadata, not sole identity/authentication.

Future allocation/transfer/reconciliation mutations must be designed for idempotent retries and concurrency-safe expected-version semantics. A timeout/unknown remote outcome enters explicit ambiguous/reconciliation state rather than assuming success or failure.

Clone/migration conflicts never silently create a second production entitlement. Legitimate staging, migration and disaster-recovery environments are explicit policy classes.

Service outage is not expiry. Expiry/revocation are driven by verified contract/signed-entitlement facts. Product entitlement never becomes WordPress authorization or WPE Membership authorization.

## Accepted state principles

Client-visible activation/allocation semantics include explicit states for:
- unlinked/linked-unallocated;
- active;
- offline cached/service unavailable;
- revalidation required;
- clone review/conflict;
- staging/development approval;
- migration source/target;
- transfer pending;
- release pending/released;
- expired;
- revoked.

Machine conflict codes include allocation capacity, duplicate production binding, stale restored entitlement, binding mismatch, transfer conflict, network-binding change and unknown/ambiguous service state.

## Safety requirements

- no allocation decision from an editable local option alone;
- allocation ID is not a bearer credential;
- OAuth/service tokens stay Vault/P3;
- account auth and local target-site/network authority are both required for privileged mutations;
- local persistence failure after remote success must reconcile using stable operation/allocation identity;
- site-count/capacity is service-contract authority, not hidden client site crawling;
- clone conflict handling cannot disclose another customer's site/account details;
- expiry/revocation cannot delete site data or disable Membership protection.

## Consequences

Positive:
- reliable retry/reconciliation semantics;
- clearer Multisite allocation races;
- safe clone/staging/migration behavior;
- no hostname-only licensing;
- clean separation between commercial entitlement and site authorization.

Costs:
- remote service needs versioned resources and idempotency/concurrency controls;
- client requires explicit reconciliation/conflict UI;
- transfer/clone scenarios need service-side history and testing.

## Evidence still required

After explicit owner development/service consent:
- exact OpenAPI/resource schemas;
- idempotency behavior;
- last-seat concurrent allocation races;
- release/reallocate races;
- remote-success/local-failure recovery;
- timeout/unknown-outcome reconciliation;
- clone/staging/migration/DR fixtures;
- domain/host/network transfer;
- deleted/recreated site-ID behavior;
- offline grace/expiry/revocation;
- anti-rollback/binding mismatch;
- ownership transfer;
- privacy/no-hidden-site-inventory checks.

Executed service fixtures: **0**.

## Development gate

This ADR authorizes no remote service implementation, API call, entitlement signing, local client code, clone detector, database schema or UI. ADR-0014 explicit owner consent remains required.
