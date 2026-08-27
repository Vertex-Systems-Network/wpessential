# WPEssential — User Profile Runtime Authority & Security Evidence Profile

Status: **Phase 0 paper security/runtime profile / no user mutation, auth action or session execution authorized**  
Date: 2026-08-28  
Related: User/Profile Identity-Change Security Model, Field Storage ADR-0087, Role ADR-0032, Multisite ADR-0069, ADR-0014.

## Purpose

Fix the runtime authority boundaries for User Profile Builder so profile UX cannot become a generic privilege, credential or protected-meta editor.

## Authority profiles

### UP1 — Native WordPress identity/auth authority — first baseline

WordPress remains source of truth for:
- core user row/identity properties;
- password hash/reset mechanisms;
- login/session token semantics;
- Application Password storage and lifecycle;
- core user registration/deletion APIs;
- site role assignment capability semantics;
- Super Admin/network authority.

WPE wraps certified core APIs/flows; it does not mirror these facts into a parallel WPE identity database.

### UP2 — Ordinary WPE profile field storage through Field Storage routing

Non-core custom profile values use ADR-0087:
- native user meta when natural and workload fits;
- site-specific profile fields only with explicit site scope/storage mapping;
- typed Custom Table escalation only where scale/constraint/query needs justify it;
- Vault reference for secrets/credentials owned by integration domains.

A custom field named like `role`, `password`, `membership_level`, `session` or similar never acquires authority by naming convention.

### UP3 — dedicated security-action intent/runtime state — exceptional only

If a WPE-managed identity workflow requires durable pending state not safely provided by current WordPress primitives, store a minimal scoped action intent separate from profile values.

Candidate uses:
- pending email-change intent/reference;
- recent-auth challenge/result reference;
- account deletion/reassignment plan;
- security action correlation/replay guard.

Rules:
- no plaintext password/reset token/session cookie/Application Password secret;
- token material stored hashed/one-way or delegated to core/security provider where appropriate;
- bounded expiry/retention;
- immutable action purpose + target binding;
- not a generic workflow table for ordinary profile edits.

Exact persistence is evidence-gated; reuse core state when it already supplies the needed semantics.

## Protected binding registry

Generic Profile/Field binding cannot read/write protected classes:
- `user_pass`;
- `user_activation_key` and reset internals;
- role/capability meta families;
- session token storage;
- Application Password internals;
- WPE Membership entitlement/runtime authority;
- Vault/connection/product-license secrets;
- registered third-party security-provider internals.

The registry is semantic and extensible; unknown user meta is not automatically considered safe.

## Self vs administrative mutation

### Self edit
Requires:
- authenticated target = current user unless explicit delegated flow;
- Profile Definition field allowlist;
- field read/write Policy;
- typed validation/sanitization;
- security action adapter for sensitive identity/auth changes.

### Editing another user
Adds:
- WordPress object-level user authority (`edit_user`/mapped semantics as applicable);
- target-scope Policy;
- field/action-specific privilege;
- audit for privileged changes.

Generic `edit_user` does not grant role, Membership, Vault or credential mutation through Profile fields.

## Email-change profile

### UE1 — certified WordPress-compatible confirmation flow — first baseline

Where supported by accepted WordPress core behavior, WPE reuses compatible email-change confirmation primitives rather than directly updating `user_email` from a generic field save.

Required semantics:
- normalized/unique candidate address;
- current authenticated principal;
- recent-auth policy when required;
- one bounded pending intent;
- confirmation secret not logged or returned after issue;
- old email remains authoritative until completion;
- replay/expiry/cancel behavior;
- safe notification policy;
- audit without token/plaintext secret.

### UE2 — WPE dedicated intent adapter — fallback only if core flow cannot meet required surface

Must preserve UE1 security properties and undergo separate evidence.

## Password/session/Application Password actions

These are typed actions, not fields.

### Password
- uses WordPress password/reset APIs;
- no readable current password field;
- recent-auth/current-password policy where applicable;
- session invalidation behavior explicit;
- no plaintext/hash logging.

### Sessions
- uses WordPress session-token APIs;
- current/other session metadata only as safely exposed;
- logout-one/others/all actions separately authorized;
- no raw cookie/token output.

### Application Passwords
- uses WordPress APIs;
- generated secret shown only at creation according to core semantics;
- stored hash never exposed as secret;
- revoke-one/revoke-all typed actions;
- generic meta/export excludes internals.

## Recent-auth boundary

Sensitive actions can require a short-lived `recent_auth` assertion.

The assertion must be:
- purpose/binding aware;
- short-lived;
- server verified;
- non-reusable beyond declared policy;
- never implemented as cached plaintext password.

Exact mechanism may use core reauthentication or a certified provider adapter; no custom weak credential cache is accepted.

## Public profile projection

Public profile is a separate allowlisted projection.

Default-deny classes:
- email;
- phone/address/private identifiers;
- authentication/security metadata;
- roles/capabilities/entitlements unless an explicit safe public presentation use case exists;
- protected custom fields.

Public route/REST/listing must reapply server-side Profile/Field Policy; hiding a field in UI is insufficient.

Stable public slug does not require exposing numeric user ID and never mutates `user_login` as a side effect.

## Multisite identity model

WordPress user account is network/global according to core Multisite semantics, while many profile/application values can be site-specific.

Rules:
- site admin authority over a user's site profile does not imply network identity authority;
- site-specific custom profile values carry explicit site scope;
- global email/password/session/Application Password actions use proper global/network authority semantics;
- Super Admin remains WordPress core authority;
- deleting/removing a user from one site is distinct from deleting the network user;
- one site's profile export/delete cannot leak another site's scoped custom values.

## Account removal/deletion

Separate actions:
- remove user from site;
- reassign site content where applicable;
- delete network/single-site account where core semantics and authority allow;
- privacy erase/anonymize WPE-owned personal fields;
- cancel/reconcile Membership/billing/provider effects through owning domains.

A Profile checkbox cannot invoke these implicitly.

High-risk deletion requires impact Plan, current target fingerprint, recent auth/confirmation and recovery/irreversibility disclosure.

## Concurrency/replay cases

Future action adapters must handle:
- two email-change requests racing;
- old confirmation after newer intent;
- email uniqueness changes before confirmation;
- password change vs session invalidation;
- Application Password create/revoke race;
- user removed/deleted while Profile form stale;
- concurrent admin + self edit;
- site removal vs network identity action.

Stale sensitive action intent cannot overwrite newer authoritative identity state silently.

## Failure behavior

Fail safe:
- protected-meta registry unavailable → deny generic sensitive binding;
- email delivery fails → keep old email authoritative;
- recent-auth unavailable → block high-risk action, ordinary safe profile edit may continue separately;
- session invalidation fails → report degraded security result;
- WPE Profile Definition corrupt/missing → native WordPress account/recovery remains available;
- Vault/integration failure does not lock native WordPress login.

## Audit/privacy

Audit security actions using safe actor/target/action/result/correlation metadata.

Never audit plaintext:
- password;
- reset/activation token;
- session cookie/token;
- Application Password secret;
- OAuth/Vault secret.

WPE-owned ordinary personal profile values integrate with WordPress privacy exporter/eraser according to field classification and site/network ownership.

## Future executable evidence — NOT AUTHORIZED

### Authority/binding
- protected meta read/write attempts;
- mass-assignment field names;
- self vs `edit_user` admin behavior;
- third-party protected-meta registry;
- ordinary field FS1/FS2 site/global routing.

### Identity/auth
- UE1 current WordPress email confirmation behavior;
- race/replay/expiry/newer-intent invalidation;
- password/session invalidation;
- Application Password create/show-once/revoke secrecy;
- recent-auth expiry/purpose binding.

### Public/privacy
- public profile IDOR/field leakage;
- REST/listing projection;
- exporter/eraser;
- profile slug collision/redirect.

### Multisite
- site admin vs network identity authority;
- site removal vs global delete;
- Super Admin;
- site-scoped custom profile isolation at 100/1k/10k-site metadata scale where relevant.

Unauthorized credential/role/protected-meta mutation or public sensitive-field exposure required: **0**.

Executed User Profile security fixtures: **0**.

## Paper recommendation

Use **UP1 native WordPress identity/auth authority** as the non-negotiable baseline, **UP2 Field Storage routing** for ordinary custom profile data, and UP3 minimal dedicated security-action state only when core primitives cannot supply the required durable semantics.

WPE Profile Builder is a controlled presentation/action layer, not a parallel identity provider or generic user-meta editor.