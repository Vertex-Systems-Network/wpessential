# ADR-0097 — Role & Capability Runtime Mutation Profile

Status: **Accepted paper security/runtime profile / executable evidence pending**  
Date: 2026-08-28

## Context

Role & Capability Manager changes WordPress authorization itself. A syntactically valid mutation can still cause self-lockout, remove every recovery principal, corrupt third-party role state or incorrectly grant network/Super Admin authority.

## Decision

Accept:
- **RA1 — native WordPress role/capability authority** as the source of truth;
- **RA2 — third-party/custom role compatibility profile** preserving externally registered roles/caps;
- WPE-owned immutable/bounded Change Plan, impact fingerprint, effective-capability simulation, recovery-principal analysis, optional pre-change snapshot and reconciliation metadata around native mutations;
- RR1 alternate authorized admin, RR2 WordPress/WP-CLI authority and RR3 non-authenticating WPE recovery mode as recovery layers.

WPE does not create a parallel persistent authorization engine or anonymous recovery authentication path.

## Recovery-principal invariant

Ordinary WPE UI must not knowingly commit a change that leaves zero viable administrative recovery principals for the affected site/network scope.

Role names alone do not prove recovery; effective primitive/mapped capabilities, multiple roles, user overrides and Multisite Super Admin semantics are evaluated.

## Stale-plan invariant

High/critical mutation is pinned to a current role/user authority fingerprint. If authority changed after review, execution blocks and requires a refreshed impact Plan.

## Partial-failure invariant

If native WordPress authority changes but WPE metadata/audit persistence fails, WPE re-reads WordPress as authority and reconciles. It does not blindly replay destructive role/user mutations because its own result row is missing.

## Multisite invariant

Super Admin/network authority is separate from site roles. Site administrators cannot manufacture or remove Super Admin through site Role Manager, and switching blog context never creates network authority.

## Recovery-mode invariant

WPE recovery mode can disable WPE overlays/restrictions for an already WordPress-authorized principal, but cannot bypass login, mint admin authority, expose a public recovery endpoint or disable core capability checks globally.

## Evidence still required

After explicit owner consent:
- core/custom/third-party role mutation;
- mapped/meta capability behavior;
- multiple role/user overrides;
- self-lockout/last-recovery cases;
- stale Change Plan and partial commit reconciliation;
- Super Admin/network/site cases;
- WP-CLI/recovery-mode/snapshot reverse-diff recovery;
- capability-dependent cache revocation across Profile/REST/Dashboard/Listings.

Zero-recovery-principal ordinary UI commits required: **0**.  
Unauthorized Super Admin/network grants required: **0**.

Executed Role/Capability security fixtures: **0**.

## Development gate

This ADR authorizes no role/capability/user mutation, recovery-mode runtime, WP-CLI repair execution, snapshot persistence, cache invalidation implementation or test. ADR-0014 explicit owner consent remains required.