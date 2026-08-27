# WPEssential — User/Profile Identity-Change Security Model

Status: **Phase 0 security architecture / no implementation authorized**  
Related: Dashboard/Profile/Roles exhaustive spec, Custom Fields, Policy, ADR-0014.

## 1. Security boundary

User Profile Builder may present user data, but it is **not** a generic editor for every property/meta row attached to a WordPress user.

WPE separates five classes:

1. **Ordinary profile data** — name, bio, locale, approved custom fields.
2. **Sensitive identity data** — email, public/profile slug/identity attributes with account implications.
3. **Authentication credentials/actions** — password/reset flows, sessions, Application Passwords, SSO/2FA adapters.
4. **Authorization state** — roles, capabilities, Membership/Entitlements, security bypasses.
5. **Opaque WordPress/plugin internals** — activation/reset/session/provider/private operational metadata.

Classes 3–5 never become ordinary Custom Field bindings.

## 2. Protected core properties

Normal profile field binding may support approved properties such as:
- first name;
- last name;
- nickname;
- display name;
- URL;
- description;
- locale;
- selected presentation preferences.

Special handling required:
- `user_email` — dedicated identity-change workflow;
- `user_login` — immutable in normal WPE edit flows;
- `user_pass` — dedicated password action only;
- `user_activation_key` — never generic read/write field.

Internal IDs and account dates are normally read-only informational fields if exposed at all.

## 3. Protected user-meta registry

WPE maintains a non-editable-by-generic-profile registry of security/authorization metadata families.

Core categories include:
- role/capability storage (`*_capabilities`, `*_user_level` and site/network equivalents);
- session-token storage;
- Application Password storage (`_application_passwords` and current core equivalents);
- password reset/activation internals;
- WPE Vault references/credential records not explicitly exposed through dedicated components;
- WPE Membership entitlement/runtime authority fields;
- WPE account/product entitlement connection secrets;
- registered third-party security-provider protected keys.

The registry is semantic/capability based, not only a brittle hardcoded list. Extensions can register protected meta descriptors.

Unknown user meta is **not** automatically safe/public/editable.

## 4. Field access layers

Every user/profile field resolves four independent questions:

1. can actor view field definition/UI?
2. can actor read this value for target user?
3. can actor edit this value for target user?
4. can this value be rendered publicly/exported?

UI visibility never substitutes server authorization.

For editing another user, WordPress object-level `edit_user` capability semantics remain part of the outer security boundary.

## 5. Self-edit vs administrative edit

### Self profile
Default target = current authenticated user.

Allowed fields are explicitly listed by profile definition/policy.

### Administrative profile
Editing another user requires:
- WordPress/WPE object capability;
- field-level policy;
- extra capability for authorization/security-sensitive actions;
- audit for privileged changes.

Generic `edit_user` does not automatically grant ability to change WPE Membership, roles, secrets or security settings through profile fields.

## 6. Email change workflow

Email is identity/account-sensitive and is never treated as a normal text-meta update.

Candidate self-change flow:
1. authenticated current user requests new email;
2. nonce/CSRF verification;
3. require recent re-auth/current password according to accepted account-security policy;
4. validate/normalize new address;
5. verify uniqueness/current WordPress constraints;
6. create pending email-change intent;
7. send confirmation to new address through WordPress/core-compatible flow where supported;
8. optionally notify old address of requested/completed change;
9. apply change only after valid confirmation;
10. expire/revoke pending intent after bounded interval;
11. audit safe metadata without confirmation secret.

WordPress currently provides `send_confirmation_on_profile_email()` for confirmation-request behavior in relevant core profile flow. WPE should reuse compatible core primitives rather than downgrading security for generic profile editing.

Administrative email change may have a separate privileged policy but must be explicit and audited.

## 7. Password change workflow

Password is an action, never a readable field.

Self-change candidate:
- recent authentication/current password where appropriate;
- new password + confirmation;
- WordPress password APIs;
- strength guidance;
- optional/standard invalidation of other sessions according to chosen policy;
- notification of security-sensitive change;
- no logging of plaintext/derived password material.

Forgot password uses WordPress reset-key flow.

## 8. Session management

Session management is a dedicated account-security component.

Supported concepts may include:
- current session metadata;
- other sessions count/list where WordPress exposes safely;
- log out other sessions;
- log out all sessions.

Use WordPress session-token APIs; WPE does not create a parallel authentication-session store for ordinary WordPress login.

A session invalidation operation is privileged and audited but never logs raw cookies/tokens.

## 9. Application Passwords

Application Passwords are revocable integration credentials, not profile fields.

WPE may expose a dedicated component using current WordPress APIs to:
- list safe metadata;
- create;
- revoke one;
- revoke all.

Rules:
- generated secret shown only at creation according to core semantics;
- stored hash never displayed as secret;
- `_application_passwords` is protected from generic meta UI/export/token rendering;
- creating/revoking requires capability + re-auth policy as appropriate;
- operations audited without plaintext credential.

## 10. Role/capability changes

Role and capability mutation belongs exclusively to Role & Capability Manager/authorized workflows.

Profile editor may display a safe read-only role summary where useful, but generic Profile fields cannot write:
- roles;
- primitive caps;
- explicit user cap denies/allows;
- Super Admin state.

This prevents mass-assignment privilege escalation.

## 11. Membership/account access state

Membership Enrollment/Entitlements are separate domains.

Profile may render membership/account components, but editing an arbitrary custom field named `membership_level` cannot grant/modify Membership access.

Enrollment/Entitlement changes use Membership abilities/policies and audit/state-machine rules.

## 12. Public profiles

Public profile has an explicit allowlist.

Defaults:
- email hidden;
- telephone/address/private identifiers hidden;
- security/account metadata never exposed;
- public slug should not require exposing numeric user ID;
- custom fields classified Personal/Sensitive are not public unless explicitly overridden with warning;
- route and REST output use same server-side policy.

Public output is separate from whether a user can self-edit a field.

## 13. Identity/profile slug

Profile slug can use:
- stable generated slug;
- user nicename where acceptable;
- dedicated profile slug field.

Changing published profile slug:
- collision check;
- redirect mapping candidate;
- enumeration/privacy review;
- never changes `user_login` as a side effect.

## 14. Avatar/media

Local avatar upload, if supported:
- file/MIME/dimension policy;
- ownership/reference tracking;
- original media handling explicit;
- removing avatar does not delete unrelated attachment by default;
- public visibility explicit.

No arbitrary remote-image fetch during profile save without Connections/SSRF-safe adapter.

## 15. Account deletion / erasure

Delete-account is a dedicated destructive flow, not a profile checkbox.

Before availability:
- capability/policy;
- recent re-auth;
- content ownership/reassignment implications;
- Membership/billing consequences;
- privacy erasure vs legal/audit retention;
- multisite/network implications;
- explicit confirmation;
- recovery impossibility warning.

Ordinary v1 profile editing does not silently delete WordPress users.

## 16. Re-authentication model

Security-sensitive actions can require `recent_auth` rather than relying solely on a long-lived logged-in cookie.

Actions likely to require higher assurance:
- password change;
- email change;
- Application Password creation/revocation;
- role/capability mutation;
- destructive account deletion;
- security recovery settings.

Exact recent-auth mechanism is a separate executable/security decision; no weak custom password cache is accepted.

## 17. Audit

Audit security-sensitive changes with:
- actor;
- target user;
- action;
- timestamp;
- success/failure;
- correlation ID;
- changed field/category;
- safe before/after classification where permitted;
- origin/interface.

Never log:
- passwords;
- reset/activation tokens;
- session cookies/tokens;
- Application Password plaintext;
- OAuth secrets.

## 18. Import/export

Profile definition export may contain field configuration.

User data export:
- follows field privacy classification and explicit data-export flow;
- security/auth metadata excluded;
- Application Password/session/Vault secrets excluded;
- roles/capabilities exported only through dedicated authorization migration tooling, not ordinary profile CSV.

WordPress personal-data exporter/eraser integration remains required for WPE-owned personal data.

## 19. Failure/recovery

- pending email intent orphaned → old email remains authoritative;
- mail transport failure → do not apply new email merely because confirmation could not send;
- session invalidation failure → surface security warning;
- unavailable 2FA/SSO provider → preserve WordPress recovery path according to provider policy;
- denied protected-meta write → structured authorization failure, not partial silent save;
- profile definition missing → core WordPress account remains accessible; WPE must not lock users out of native recovery.

## 20. Future executable evidence — NOT AUTHORIZED

After explicit consent:
- current WordPress profile/email confirmation integration;
- object capability tests (`edit_user`, multisite/Super Admin);
- protected-meta registry fixtures;
- session invalidation behavior;
- Application Password create/revoke secrecy;
- email-change races/replay/expiry;
- password-change session behavior;
- public profile IDOR/privacy tests;
- multiple-role profile resolution;
- account deletion/reassignment fixtures.

## Paper recommendation

Accept the security principle:

**Profile data editing, identity changes, authentication credentials and authorization state are separate action classes. Generic Custom Fields can never become a backdoor to user credentials/roles/session/Application Password/Membership authority.**