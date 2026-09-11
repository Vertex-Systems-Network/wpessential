# User Profile — Native & Market Evidence Matrix V1

Surface: **14 / User Profile**  
Issue: **#497**  
Research anchor: **`main @ 42303a97938f1a80f9c1c9088fa70d40ba359279`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WordPress `edit_user_profile` hook | https://developer.wordpress.org/reference/hooks/edit_user_profile/ | extension points for additional profile fields/data on Edit User |
| WordPress `update_user_meta()` | https://developer.wordpress.org/reference/functions/update_user_meta/ | canonical user-scoped metadata mutation with serializable values and explicit user identity |
| WordPress `profile_update` hook | https://developer.wordpress.org/reference/hooks/profile_update/ | post-update lifecycle event with old user data and updated user payload |
| WordPress roles & capabilities | https://developer.wordpress.org/apis/security/user-roles-and-capabilities/ | server-side edit/view authorization and least-privilege boundary |
| Profile Builder | https://wordpress.org/plugins/profile-builder/ | frontend registration/edit-profile forms, custom fields, role-aware forms, approval, redirects and user directories |
| Ultimate Member profile tabs | https://docs.ultimatemember.com/article/69-how-do-i-add-my-extra-tabs-to-user-profiles | extensible public profile navigation/content tabs plus configurable visibility/privacy |

## Native decisions added in this pass

- WordPress user core fields and user meta are separate data families. Custom profile schema may map to user meta, but account credentials/core identity require explicit native ownership and cannot be treated as arbitrary custom fields.
- `profile_update` supplies a lifecycle observation point after an existing user changes; it does not authorize shadow persistence or duplicate user records.
- User-meta mutation requires an explicit target user ID and server authorization. “Own profile” and “manage another user” policies must remain distinct.
- Role/capability assignment is owned by Roles & Capabilities; Surface 14 may present effective role/profile context but must not create an alternate grant engine.
- Public profile tabs/directories are presentation/discovery surfaces. Private fields must remain server-filtered, not merely hidden in the browser.

## Seed disposition evidence

- `profiles.schema.fields` — native user data + user meta and mature profile plugins support a composed field schema; field widgets should consume canonical Fields controls.
- `profiles.field.privacy` — privacy/editability needs explicit server-side policy; frontend hiding alone is insufficient.
- `profiles.public.slug` — valid public-profile candidate, but collision/routing ownership must be coordinated with canonical routing.
- `profiles.public.visibility` — presentation and discovery policy may live here while actual authorization remains Policy-owned.
- `profiles.registration.policy` — market evidence supports verification/approval workflows; account creation and role grants need canonical security boundaries.
- `profiles.authentication.surface` — login/logout/reset presentation is integration/compatibility, not a private authentication engine.
- `profiles.directory.query` — directories are valid market behavior but query semantics must consume canonical Query and presentation may consume Listings.
- `profiles.portability.definition` — profile schema portability is a valid candidate; live user values/secrets require separate data/privacy rules.

## Remaining gates

This matrix does **not** promote `BANK_REVIEWED`. Registration/account-creation APIs, password/email change semantics, avatar/media ownership, privacy export/erasure, public-profile routing, directory competitors, multisite and zero-unresolved review remain open. No runtime implementation is authorized.
