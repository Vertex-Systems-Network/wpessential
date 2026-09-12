# Roles & Capabilities Runtime Gap Matrix V1

Status: **ACCEPTED PLANNING GAP AUDIT — RUNTIME UNPROMOTED**  
Surface: **30 — Roles & Capabilities (`roles`)**  
Issue: **#615**  
Exact-main audit anchor: `3b20b3bd2a99e44b0cc1c28d6bb5f969a61b2bb9`

## Purpose

This audit compares the reviewed Surface 30 Bank, Atomic Option Contract, UX contract and canonical read-seam contract with the runtime that exists on the exact-main anchor. It identifies the smallest safe next implementation lane without authorizing mutation work.

## Exact-main baseline

At the audit anchor:

- no canonical `frameworks/Modules/Roles` / Roles & Capabilities runtime module is promoted;
- Surface 30 previously existed only as planning inventory (`ATOMIC_INVENTORY_COMPLETE` before Issue #615);
- no canonical peer-facing role/capability read service exists;
- existing platform principal/auth abstractions do not provide a role catalog, three-state role capability matrix, user-override explanation or Super Admin-aware impact query;
- Taxonomy #474 therefore cannot safely implement role-impact preview without duplicating Surface 30 ownership.

Issue #615 adds planning/contracts only and does not change those runtime facts.

## Gap matrix

| Capability | Planning contract | Exact-main runtime | Gap status | Next action |
|---|---|---|---|---|
| Scoped role catalog | Reviewed | No canonical Surface 30 service | **MISSING** | Implement in a later read-only runtime issue |
| Primitive capability registry | Reviewed | No Surface 30 canonical reader | **MISSING** | Implement with safe provenance/unknown states |
| Explicit Allow / Deny / Absent role entries | Reviewed | No canonical Surface 30 read model | **MISSING** | Read-only preservation is prerequisite for peer consumers |
| Capability-impact query | Reviewed WPE-exceed seam | Absent | **MISSING / TAXONOMY BLOCKER** | Implement read-only scoped impact seam first |
| Effective capability explain | Reviewed | No canonical explanation service | **MISSING** | Later bounded diagnostic runtime; requires user/object context |
| Permission simulator | Reviewed | No Surface 30 simulator | **MISSING** | Later read-only diagnostic; no impersonation |
| Site/network/Super Admin context | Reviewed | Native WordPress APIs exist but no Surface 30 abstraction | **PARTIAL NATIVE / MISSING CANONICAL SEAM** | Normalize in read-only seam |
| Target-role policy | Reviewed | No Surface 30 canonical target-role service | **MISSING** | Later read-only policy integration before any mutations |
| Role create/clone/rename | Reviewed | Native WP primitives exist, no WPE guarded runtime | **MUTATION GATED** | Do not implement in read-seam lane |
| Capability grant/deny/remove | Reviewed | Native WP primitives exist, no WPE guarded runtime | **MUTATION GATED** | Separate future high-impact authorization |
| User role assignment / user overrides | Reviewed | Native WP primitives exist, no WPE guarded runtime | **MUTATION GATED** | Separate future high-impact authorization |
| Delete/remap/role-key migration | Reviewed as guarded/rejected-unsafe when silent | No safe WPE destructive workflow | **DESTRUCTIVE GATED** | Separate explicit destructive safety authorization only |
| Admin recovery/rescue execution | Reviewed | No authorized Surface 30 recovery runtime | **PRIVILEGED GATED** | Separate explicit recovery authorization |
| Import/export apply | Reviewed | No Surface 30 orchestration | **MUTATION GATED** | Diff/preview may precede apply; apply separately authorized |
| Revisions/forward repair | Reviewed | No Surface 30 revision runtime | **MISSING / NON-BLOCKING FOR TAXONOMY** | Later Surface 30 implementation |
| Peer Taxonomy role-impact preview | Dependency contract exists | No canonical Surface 30 runtime seam | **BLOCKED** | Remains blocked until read seam is implemented and accepted |

## Smallest safe next runtime slice

The next separately authorized runtime issue should implement **read-only Surface 30 truth only**:

1. explicit-site role catalog;
2. primitive capability entries preserving `true`, `false`, and absent;
3. capability registry/provenance with unknown state;
4. scoped `capabilityImpact()` query;
5. site/network/Super Admin context and degraded-state handling;
6. canonical Ability/Policy read authorization;
7. focused peer-consumer contract evidence for Taxonomy.

That slice must not include:

- role or capability mutation;
- user-role assignment mutation;
- user capability mutation;
- role deletion or role-key migration;
- Super Admin grant/revoke;
- impersonation/user switching;
- rescue/recovery execution;
- production deployment/release.

## Why direct Taxonomy inspection remains forbidden

WordPress native role APIs exist today, but consuming them directly inside Taxonomy would create a second owner for role truth and force Taxonomy to interpret:

- explicit false versus missing capability entries;
- multiple roles;
- individual user overrides;
- site context;
- meta-capability mapping;
- Super Admin semantics;
- actor-sensitive editable-role policy.

Those semantics belong to Surface 30. The accepted dependency boundary is therefore a canonical Surface 30 read service, not a Taxonomy-specific `wp_roles` adapter.

## Runtime evidence required for the next read-only issue

A later implementation PR must demonstrate:

- exact-head unit tests for three-state capability entry preservation;
- WordPress runtime tests across role catalog and site context;
- multisite/Super Admin behavior where the repository's compatibility matrix supports it;
- Policy denial/allow evidence;
- degraded/unavailable result tests;
- no mutation side effects;
- no direct peer role-store scan in Taxonomy;
- integration test proving Taxonomy can consume capability impact through the Surface 30 boundary;
- applicable security, compatibility and performance evidence;
- clean review threads and exact-head CI.

## Issue #615 exit truth

Issue #615 may promote Surface 30 only through **`UX_CONTRACT_COMPLETE`** if the Bank, machine contract, UX contract, read-seam contract and this gap audit validate cleanly.

It must not claim:

- Surface 30 runtime implementation;
- Taxonomy #474 is unblocked;
- `RUNTIME_CERTIFIED`;
- `PRODUCT_PARITY_CERTIFIED`.

The dependency transition after #615 is: **planning prerequisite complete → separate read-only Surface 30 runtime seam authorization required**.
