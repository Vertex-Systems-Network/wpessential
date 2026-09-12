# User Profile — Market Audit V1

Status: `RESEARCH_COMPLETE / SUPERVISOR_INTEGRATION_PENDING`

Surface: 14 — User Profiles  
Snapshot: 2026-09-12  
Parent: #713  
Worker issue: #717  
Base: `54e4e70095f74a6ebff15da83cc2bbf3d447a49c`  
Worker branch: `agent/user-profile-market-audit-v1`

## 1. Scope and boundary

This evidence-only audit reconciles all 17 current `NATIVE_AUDITED` User Profiles records. Profile owns presentation/composition of user profile data and frontend account/profile flows, while Roles/Policy, Media, Query/Listings and privacy lifecycle retain their canonical ownership.

## 2. Current market evidence

### E1 — Ultimate Member
Official evidence:
- https://docs.ultimatemember.com/article/92-core-pages
- https://docs.ultimatemember.com/article/1595-getting-started-with-ultimate-member
- https://docs.ultimatemember.com/article/119-user-roles

Verified capabilities: frontend profile/account/register/login/password-reset/member-directory pages, configurable profile fields/tabs, user roles and registration statuses including auto approval, email activation and admin review.

### E2 — UsersWP
Official evidence:
- https://userswp.io/docs/userswp-pages/
- https://userswp.io/docs/register/

Verified capabilities: frontend profile/register/login/account/password/user-list pages, registration field configuration and account/profile workflows.

### E3 — Ultimate Member directory/profile ecosystem
Official documentation categories cover user directories, custom profile fields, profile tabs, privacy/account behavior and extensions such as verified users/private messaging. These prove a broad market expectation for configurable public/member profile presentation without transferring canonical role/media/privacy ownership to this surface.

## 3. Findings

- Custom profile fields, avatar/profile presentation, biography/social/contact data and frontend editing are baseline market capabilities.
- Public profile routes/templates and member directories with search/filter/sort/pagination are established patterns.
- Registration forms, role references, email verification and admin approval are mainstream, but role grant truth remains Roles/Policy-owned.
- Privacy-aware field visibility and directory output must be server-authoritative; market visibility controls are not sufficient evidence for an authorization model.
- Profile definition portability must exclude passwords, live credentials/secrets and unrelated live user data.

## 4. Record reconciliation

| Bank record | Disposition | Evidence / boundary |
|---|---|---|
| `profiles.schema.fields` | MARKET_EVIDENCED_WITH_FIELDS_OWNER | E1/E2 expose custom registration/profile fields; reusable control schema stays Fields-owned. |
| `profiles.schema.media` | MARKET_EVIDENCED_WITH_MEDIA_OWNER | Avatar/profile imagery is parity; private/cover media remains Media/File-owned. |
| `profiles.schema.content` | MARKET_EVIDENCED | Biography/contact/social/profile content is standard. |
| `profiles.schema.privacy` | MARKET_EVIDENCED / WPE_HARDENED | Member/profile visibility controls exist; WPE requires server-side field privacy classes. |
| `profiles.schema.editability` | MARKET_EVIDENCED_WITH_POLICY | Frontend self-edit/admin workflows exist; own-profile vs edit-other remains explicit Policy. |
| `profiles.public.slug` | MARKET_EVIDENCED / WPE_HARDENED | Public profile URLs are standard; WPE retains collision/canonical-route policy. |
| `profiles.public.visibility` | MARKET_EVIDENCED_WITH_POLICY | Public/member/private patterns exist; authorization remains Policy-owned. |
| `profiles.public.template` | MARKET_EVIDENCED | Profile templates/tabs/sections are established. |
| `profiles.registration.policy` | MARKET_EVIDENCED | E1/E2 expose configurable registration forms and account policy. |
| `profiles.registration.role` | MARKET_EVIDENCED_WITH_ROLES_OWNER | E1 user-role registration behavior exists; actual role grants remain Surface 30/Policy. |
| `profiles.registration.verification` | MARKET_EVIDENCED | E1 supports email activation/admin review/approval states. |
| `profiles.access.login-reset` | KEEP native + market parity | E1/E2 provide frontend login/logout/reset surfaces on native auth truth. |
| `profiles.directory.source` | MARKET_EVIDENCED_WITH_QUERY_OWNER | Member/user directories are parity; source query remains Query/Listings-owned. |
| `profiles.directory.controls` | MARKET_EVIDENCED | Directory search/filter/sort/pagination is mainstream. |
| `profiles.directory.privacy` | MARKET_EVIDENCED / WPE_HARDENED | Role/privacy-aware directory output is required; server authorization stays canonical. |
| `profiles.portability.definition` | KEEP_WPE_HARD | Portable profile definitions are useful, but credentials/passwords/live user records remain excluded. |
| `profiles.privacy.export-erasure` | KEEP native | Native exporter/eraser integration remains canonical privacy lifecycle truth. |

Coverage: **17 / 17 records**.  
Unresolved research dispositions: **0**.  
Worker Bank/progress mutations: **0**.

## 5. WPE-exceed / safety

- Registration role selection is always an allowlisted Policy/Surface-30 reference; never arbitrary privilege escalation.
- Profile privacy is enforced on reads and exports, not only by hiding fields in UI.
- Media remains referenced, with authorization enforced by its owner for protected assets.
- Import/export moves definitions/dependency mappings only; password hashes, credentials, secrets and uncontrolled live user data are excluded.

## 6. Supervisor integration requirements

A later Supervisor integration may add E1–E3 market provenance, classify the evidenced profile/registration/directory families, retain native auth/privacy lifecycle provenance and canonical Roles/Media/Query/Fields owner boundaries, and promote `MARKET_AUDITED` only after exact-head Bank reconciliation and CI.

No `BANK_REVIEWED`, user/role mutation, credential handling, runtime/product certification, deployment or release is authorized.