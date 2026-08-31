# ADR-0096 — User Profile Runtime Authority Profile

Status: **Accepted paper security/runtime profile / executable evidence pending**  
Date: 2026-08-28

## Context

User Profile Builder can expose ordinary user data and account-security actions, but without an explicit authority profile it could accidentally become a generic editor for credentials, protected meta, roles or network-global identity.

## Decision

Accept:
- **UP1 — native WordPress identity/auth authority** as the mandatory source of truth for core identity, password/reset, sessions, Application Passwords and core account lifecycle;
- **UP2 — ADR-0087 Field Storage routing** for ordinary WPE custom profile values;
- **UP3 — minimal dedicated security-action intent state** only when accepted WordPress primitives cannot provide required durable pending/replay semantics;
- **UE1 — WordPress-compatible confirmed email-change flow** as the first identity-change baseline, with UE2 WPE intent adapter only as an evidence-backed fallback.

WPE does not create a parallel user identity/auth database.

## Protected binding invariant

Generic Profile/Custom Field bindings cannot read or mutate password/reset/session/Application Password internals, role/capability authority, Membership entitlement authority, Vault/product secrets or registered security-provider protected metadata.

Unknown user meta is not automatically safe.

## Authorization invariant

Self edit, editing another user, global/network identity mutation and site-scoped profile editing remain distinct authorization contexts. `edit_user` alone does not grant role/Membership/Vault/credential mutation through Profile fields.

## Multisite invariant

Global WordPress identity and site-scoped WPE profile values remain separate. Removing a user from one site does not silently become network-user deletion, and site authority cannot manufacture Super Admin/network identity authority.

## Recovery invariant

A broken/missing WPE Profile definition or integration cannot remove native WordPress account/recovery access. Sensitive action failure fails safe without applying partial identity changes.

## Evidence still required

After explicit owner consent:
- protected-meta/mass-assignment tests;
- WordPress email-confirmation race/replay/expiry behavior;
- password/session/Application Password actions;
- recent-auth mechanism;
- public profile IDOR/privacy/exporter/eraser;
- Multisite site-vs-network identity cases.

Unauthorized credential/role/protected-meta mutation or public sensitive exposure required: **0**.

Executed User Profile security fixtures: **0**.

## Development gate

This ADR authorizes no user update, email/password/session/Application Password action, recent-auth flow, REST route, profile storage adapter or test. ADR-0014 explicit owner consent remains required.