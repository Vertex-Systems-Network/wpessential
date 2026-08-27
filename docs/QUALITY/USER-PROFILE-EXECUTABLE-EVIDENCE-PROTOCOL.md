# WPEssential — User Profile Executable Security Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0030, ADR-0096, `docs/SECURITY/USER-PROFILE-RUNTIME-AUTHORITY-EVIDENCE-PROFILE.md`, Field Storage, Role/Capability, Multisite, ADR-0014.

## 1. Purpose

Define executable evidence required before User Profile Builder can claim secure self/admin profile editing, identity changes, password/session/Application Password actions, public profile projection, privacy and Multisite behavior.

The authority invariant is fixed:

**WordPress remains native identity/auth authority; generic Profile fields cannot mutate credentials, roles/capabilities, sessions, Membership entitlement, Vault or other protected security internals.**

## 2. Runtime profile

Certification records:
- WordPress/PHP/database versions;
- single-site/Multisite topology;
- UP1/UP2/UP3 profile use;
- Field Storage mapping/version;
- Profile Definition revision;
- recent-auth/email delivery/session/Application Password environment;
- third-party protected-meta providers registered;
- public/REST/listing projection profile;
- privacy exporter/eraser integration profile.

## 3. Fixture matrix

### UP-01 — Self ordinary field edit
Authenticated user can edit only allowlisted writable ordinary profile fields for self.

### UP-02 — Self target spoof
Changing submitted user ID cannot edit another user.

### UP-03 — Admin edit another user
Authorized administrator can edit declared ordinary fields subject to object-level `edit_user`/Policy semantics.

### UP-04 — Unauthorized admin target
Insufficient actor cannot edit another user even if Profile UI/action URL is known.

### UP-05 — Protected `user_pass`
Generic field binding/read/write to password hash is blocked.

### UP-06 — Protected activation/reset internals
Generic binding cannot expose/mutate `user_activation_key` or reset internals.

### UP-07 — Protected roles/caps meta
Generic Profile/Field binding cannot read/write role/capability meta families.

### UP-08 — Protected session internals
Session token storage/cookies are not exposed as Profile fields.

### UP-09 — Protected Application Password internals
Stored hashes/metadata outside approved projection cannot be mass-read/written through generic Profile fields.

### UP-10 — WPE Membership/Vault/product-license protected binding
Naming/forging a field cannot mutate entitlement or secrets owned by other domains.

### UP-11 — Unknown sensitive third-party meta
Registered protected-meta provider blocks generic access; unknown meta is not automatically declared safe.

### UP-12 — Mass-assignment payload
Unknown/hidden/protected submitted keys cannot mutate user internals.

### UP-13 — Ordinary FS1 user-meta field
Typed custom field stored/read correctly through approved Field Storage route.

### UP-14 — Site-scoped custom profile field
Same network user can hold site-specific WPE profile value without cross-site collision.

### UP-15 — Typed Custom Table escalation
UP2 custom-table field preserves target user/scope/Policy semantics and cannot become parallel identity authority.

### UP-16 — Label/key migration
Profile field presentation/key migration preserves ordinary value identity through explicit mapping.

### UP-17 — Public projection default deny
Private email/phone/address/security/internal fields are absent from public profile by default.

### UP-18 — Public allowlisted field
Explicit public field renders only after server-side Profile/Field Policy.

### UP-19 — Public user IDOR
Changing public slug/identifier cannot reveal non-public fields of another user.

### UP-20 — REST profile projection
REST returns only authorized allowlisted fields and does not serialize protected internals.

### UP-21 — Dynamic Listing projection
Listing/search of users reauthorizes field projection and does not leak protected values via sort/filter/facet/count.

### UP-22 — Stable public slug collision
Collision/rename behavior is deterministic without requiring mutation of `user_login`.

### UP-23 — Email change candidate validation
New email is normalized/unique and invalid/duplicate candidate fails before authoritative identity change.

### UP-24 — Email remains old until confirmation
Pending change does not replace authoritative email before accepted confirmation flow completes.

### UP-25 — Email confirmation replay
Used/expired/cancelled confirmation cannot be replayed.

### UP-26 — Newer email intent supersedes old
Old confirmation cannot overwrite a newer pending/current identity state.

### UP-27 — Email delivery failure
Old email remains authoritative and failure is reported/recoverable without partial identity change.

### UP-28 — Recent-auth required sensitive action
High-risk action rejects expired/missing recent-auth assertion according to declared policy.

### UP-29 — Recent-auth purpose binding
Assertion for one action/purpose cannot be replayed for unrelated credential/admin action.

### UP-30 — Password change
Uses certified WordPress password API/semantics; plaintext/current hash is never logged or returned.

### UP-31 — Password/session invalidation
Declared session invalidation result is verified; degraded failure is reported rather than assumed.

### UP-32 — Logout one session
Authorized action invalidates selected session without exposing raw token.

### UP-33 — Logout others/all
Scope/current-session semantics match declared action and cannot target another user without admin authority.

### UP-34 — Application Password create
Secret is shown only according to certified core creation semantics; stored secret/hash is not later retrievable as plaintext.

### UP-35 — Application Password revoke one/all
Typed authorized actions revoke intended credentials without generic meta mutation.

### UP-36 — Concurrent email intents
Two racing requests produce deterministic winning/pending state and no stale confirmation overwrite.

### UP-37 — Concurrent admin + self ordinary edit
Field/document version/conflict semantics prevent unintended loss where both edit overlapping values.

### UP-38 — User deleted/removed while stale form open
Stale form cannot recreate/mutate deleted/removed authority silently.

### UP-39 — Remove user from site
Site removal is distinct from network/global user deletion and requires proper site authority.

### UP-40 — Network user deletion
Global/network delete requires appropriate authority, impact/reassignment semantics and cannot be invoked by ordinary Profile checkbox.

### UP-41 — Super Admin boundary
Site admin cannot mutate Super Admin/network authority through Profile Builder.

### UP-42 — Site export isolation
One site profile export excludes another site’s scoped custom fields unless explicit network-authorized export says otherwise.

### UP-43 — Privacy exporter
WPE-owned personal fields are exported according to classification/scope without protected credential material.

### UP-44 — Privacy eraser
Eraser deletes/anonymizes only owned eligible fields and preserves required authority/audit/legal records according to policy.

### UP-45 — Audit redaction
Security action audit contains safe actor/target/action/result metadata, never password/reset/session/Application Password/OAuth/Vault secret.

### UP-46 — Definition missing/corrupt
Native WordPress login/account/recovery remains available; WPE Profile failure cannot lock out core identity recovery.

### UP-47 — Pro expiry
Safe deployed public/profile runtime follows ADR-0007 while restricted editing does not corrupt identity or custom values.

### UP-48 — Scale/security regression
Reference user/profile dataset and Multisite scopes meet bounded query/cache behavior with zero protected-field leakage or cross-site collisions.

## 4. Pass gates

Certification fails if:
- generic Profile field can mutate password/roles/caps/session/Application Password/entitlement/Vault secrets;
- self target spoof edits another user;
- public/REST/listing projection exposes protected field;
- stale email confirmation overwrites newer identity;
- plaintext credential/token appears in logs/history/export;
- site admin gains network/Super Admin authority;
- one site reads another site’s scoped custom profile data;
- WPE Profile failure disables native WordPress recovery/login.

## 5. Required future evidence report

Include:
- runtime/authority/storage profile;
- UP-01…UP-48 pass/fail;
- protected binding/mass-assignment tests;
- email/recent-auth replay/race evidence;
- password/session/Application Password evidence;
- public/REST/listing leakage scans;
- privacy exporter/eraser results;
- Multisite authority/isolation results.

## 6. Current state

**UP fixtures executed: 0/48.**

No user mutation, email change, password/session/Application Password action, privacy operation or runtime test has been executed.

## 7. Development gate

Execution requires explicit owner consent under ADR-0014.