# User Profile — Native & Market Evidence Matrix V1

Surface: **14 / User Profile**  
Issue: **#497**  
Research anchor: **`main @ fad3b2f37bd44a01d77438e71877d7990b4bfe2c`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WordPress `edit_user_profile` hook | https://developer.wordpress.org/reference/hooks/edit_user_profile/ | extension points for additional profile fields/data on Edit User |
| WordPress user metadata API | https://developer.wordpress.org/reference/functions/get_user_meta/ | canonical user-meta retrieval semantics for custom profile values |
| Profile Builder | https://wordpress.org/plugins/profile-builder/ | frontend registration/edit-profile forms, custom fields, role-aware forms, approval, redirects and user directories |

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

This matrix does **not** promote `BANK_REVIEWED`. Own-profile hooks, registration APIs, privacy/export/erasure semantics, authentication ownership, directory competitors, multisite and zero-unresolved review remain open. No runtime implementation is authorized.