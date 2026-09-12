# User Profile — Native WordPress Audit V1

Surface: **14 / User Profile**  
Issue: **#690**  
Parent: **#686**  
Exact-main audit anchor: `3839142cc8a225f443fcf9f6e0c26eb9641742e7`  
Seed: `config/product/options-bank/profiles.json` — **16 records / BANK_SURFACE_SEEDED**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE WITH ONE PRIVACY-LIFECYCLE FAMILY REQUIRED AND CORE-USER OWNERSHIP ENRICHMENT.** No user/role mutation or lifecycle promotion occurs here.

WordPress natively owns core user identity/account fields, user meta, profile edit/update hooks, registration/login/reset flows, roles/capabilities, avatar lookup, user queries and personal-data exporter/eraser integration points. Surface 14 may compose these safely but must not create shadow users, private role grants or browser-only privacy controls.

## Current native sources

- `edit_user_profile` — https://developer.wordpress.org/reference/hooks/edit_user_profile/
- `personal_options_update` — https://developer.wordpress.org/reference/hooks/personal_options_update/
- `edit_user_profile_update` — https://developer.wordpress.org/reference/hooks/edit_user_profile_update/
- `profile_update` — https://developer.wordpress.org/reference/hooks/profile_update/
- `update_user_meta()` — https://developer.wordpress.org/reference/functions/update_user_meta/
- `wp_update_user()` — https://developer.wordpress.org/reference/functions/wp_update_user/
- `register_new_user()` — https://developer.wordpress.org/reference/functions/register_new_user/
- `get_avatar()` — https://developer.wordpress.org/reference/functions/get_avatar/
- `WP_User_Query` — https://developer.wordpress.org/reference/classes/wp_user_query/
- `wp_privacy_personal_data_exporters` / `wp_privacy_personal_data_erasers` — WordPress privacy extension points
- accepted planning evidence: `docs/PRODUCT/USER-PROFILE-NATIVE-MARKET-EVIDENCE-MATRIX-V1.md`

## Seed-record disposition

| Seed record | Native disposition | Integration action |
|---|---|---|
| `profiles.schema.fields` | **NATIVE-CONFIRMED MIXED OWNER** — core `WP_User` fields and user meta are distinct | add notes/provenance separating immutable/security-sensitive core fields from custom meta/Fields composition |
| `profiles.schema.media` | **NATIVE-PARTIAL** — avatar lookup is native; cover/private media is not a native profile primitive | keep media references delegated; record avatar source only |
| `profiles.schema.content` | **NATIVE-PARTIAL** — description/contact methods/user meta provide substrate; social schema is WPE/market | preserve typed custom-meta boundary |
| `profiles.schema.privacy` | **WPE POLICY + NATIVE PRIVACY INTEGRATION** | server-filter output; connect later to exporter/eraser family |
| `profiles.schema.editability` | **NATIVE CAPABILITY SUBSTRATE** | distinguish own-profile vs edit-other-user authorization; no UI-only enforcement |
| `profiles.public.slug` | **NATIVE-ADJACENT** — user nicename/author routes exist, but dedicated profile routing/collision is WPE | preserve routing-owner boundary |
| `profiles.public.visibility` | **WPE POLICY** | no native promotion beyond capability/auth inputs |
| `profiles.public.template` | **WPE PRESENTATION / ROUTING REFERENCE** | no native profile-template engine claim |
| `profiles.registration.policy` | **NATIVE-CONFIRMED SUBSTRATE** | registration/account creation is native; custom policy/fields remain composed |
| `profiles.registration.role` | **OWNERSHIP-CONFLICT / NATIVE ROLE SUBSTRATE** | role assignment must remain Surface 30/Policy-owned; Profile stores refs only |
| `profiles.registration.verification` | **MARKET/WPE-ONLY EXCEPT NATIVE MAIL/FLOW SUBSTRATES** | do not claim core admin-approval product semantics |
| `profiles.access.login-reset` | **NATIVE-CONFIRMED** | integrate login/logout/password reset as native account surfaces, not private auth |
| `profiles.directory.source` | **NATIVE-CONFIRMED QUERY SUBSTRATE / QUERY OWNER** | use `WP_User_Query` via canonical Query/reference boundary |
| `profiles.directory.controls` | **NATIVE-PARTIAL + WPE PRESENTATION** | filters/search/sort/paging can map to user query args; presentation stays WPE |
| `profiles.directory.privacy` | **WPE POLICY** | authorization-filtered output required; role inclusion does not grant access |
| `profiles.portability.definition` | **WPE/IMPORT-EXPORT** | schema Definition portable; live users/passwords/secrets are separate data/privacy concerns |

## Missing native family to add during integration

### `profiles.privacy.export-erasure`

Model the surface's participation in WordPress personal-data export/erasure without implying that shared-conversation/resource data can be blindly deleted. The record should cover:

- registered exporter/eraser integration identity;
- field privacy class and canonical-owner mapping;
- erase vs anonymize vs retain-with-legal-reason outcome;
- multisite/site ownership context;
- explicit exclusion of credentials/secrets from ordinary exports.

Recommended classification: `NATIVE_HARD`, `HARD`, `CURRENT_NATIVE`.

## Required Supervisor Bank integration

1. Populate reviewed profile/user/meta/query/privacy native sources.
2. Enrich `schema.fields` to separate core user fields from user-meta/custom fields.
3. Add `profiles.privacy.export-erasure`.
4. Add native provenance to editability, registration, login-reset and directory-query records.
5. Preserve role assignment, media ownership, routing, templates and privacy policy as canonical cross-owner/WPE semantics.

## Native completeness / unresolved items

After the privacy family and ownership enrichment, no known WordPress user/profile primitive remains missing for this V1 Bank. Password/email-change execution, user creation, role mutation and actual privacy erasure remain runtime/security gates and are not authorized by this audit.

## Gate boundary

Worker conclusion: **native evidence complete; Bank integration pending**.  
Not promoted here: `NATIVE_AUDITED`, market review, `BANK_REVIEWED`, Atomic Option Contract, UX, runtime or product parity.