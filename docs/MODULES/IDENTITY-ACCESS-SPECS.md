# WPEssential — Identity, Membership & Access Detailed Specifications

Status: **Phase 0 — specified with security/privacy blockers**

Applies `COMMON-OPTION-CONTRACTS.md`. Resolves User Profile Builder, Membership System integration boundaries and Role & Capability Manager.

---

# 14. User Profile Builder — Pro

## Scope
Profile Builder controls presentation/editability of user attributes and member/account surfaces. It does not replace WordPress authentication, password hashing, application passwords, sessions or core user security flows.

## Template definition
- name/key/status;
- mode: account/private, directory/member, public, admin augmentation;
- target audience conditions;
- subject-user conditions;
- priority;
- route/display configuration.

Default status Draft. Public profile templates require explicit Publish + visibility policy.

## Template resolution
Matching order:
1. explicit specific-user override if product permits;
2. membership/query/role segment rules by priority;
3. general default.

Conflicts surfaced in diagnostics; deterministic priority required.

## Sections/tabs
Each:
- title/key;
- icon optional;
- order;
- visibility condition;
- edit/view mode;
- empty section behavior: hide by default on public profile, show guidance in account edit.

## Field sources
- safe core user property;
- user meta through registered Field Schema;
- computed token;
- relation/listing output;
- membership summary component;
- read-only operational field registered by extension.

Protected internal meta is not selectable by raw key unless adapter explicitly exposes it.

## Core identity fields
Allowed UI may include:
- display name;
- first name;
- last name;
- nickname;
- biography/description;
- website URL;
- locale where WordPress supports user locale;
- avatar display/upload adapter.

Username/login is read-only after account creation by default because WordPress does not support casual rename as a normal core operation.

## Email change
Never direct meta update. Uses WordPress-compatible secure change flow and confirmation behavior. UI can initiate, show pending state and errors.

## Password
- current password field only when flow requires;
- new password + confirmation or generated password;
- WordPress password functions;
- no password value stored in WPE definitions/logs;
- reset link uses core lost-password flow.

## Avatar
Modes:
- WordPress/Gravatar default;
- local media/avatar adapter if implemented;
- upload MIME/size/dimension validation;
- fallback initials/icon.

Deleting local avatar does not delete unrelated Media Library attachment unless user explicitly chooses ownership-aware delete.

## Field privacy
Every display field chooses one visibility:
- only subject user + authorized admins;
- admins only;
- logged-in users;
- selected role/membership/entitlement audience;
- public.

Default for custom profile field: private to user/admin until explicitly exposed.

Edit permission is separate from visibility.

## Public profile route
Options:
- enabled false by default;
- base slug;
- identifier strategy: sanitized user slug/public profile slug, never expose sensitive numeric assumption as security;
- index/noindex default noindex until explicitly public;
- canonical integration;
- 404/403 behavior for hidden user;
- exclude selected users/roles.

## Directory integration
Profile Builder defines profile item; Dynamic Listing/Query powers directory. Directory must not expose users/fields the profile privacy policy denies.

## Account actions
- edit profile;
- change email;
- change password;
- logout;
- privacy export/erase request links where enabled;
- memberships/benefits links when Membership enabled;
- sessions/security link if a supported WordPress/API surface exists.

## Admin user profile augmentation
Fields may appear on core user profile screen only when exact group matches. Assets scoped to `profile.php`/`user-edit.php` and only when field group exists.

## Tests
- user edits own allowed fields;
- cannot edit another user via REST tampering;
- hidden public field not leaked in Query/REST;
- email/password secure flows;
- role/membership template conflict;
- public route enumeration/privacy.

---

# 15. Membership System — Pro

The full option-level specification is `MEMBERSHIP-SYSTEM.md` and is normative.

## Integration rules summarized
- Membership Plan ≠ Enrollment ≠ billing Subscription ≠ WordPress Role ≠ Entitlement.
- Membership runtime creates/revokes normalized entitlements.
- Policy consumers ask entitlement/policy layer rather than payment plugin.
- card credentials never stored/processed by WPEssential.
- external billing event mapping is signed/idempotent/reconcilable.
- disabling/expiring WPEssential Pro must not expose previously protected content/files.
- role sync is optional side effect only.

## Required cross-module dependencies
Hard platform dependencies:
- Policy/Entitlement contract;
- Definition Repository;
- Jobs for bulk/reconcile/drip tasks;
- Audit.

Soft module dependencies:
- Profile/Dashboard for member portal;
- Forms/Workflow for custom enrollment automation;
- Notifications/Email for lifecycle communication;
- Query for segmentation;
- Webhooks/Connections for external sources;
- Role Manager for role-sync UX.

Membership remains usable in a reduced core mode when soft modules are disabled.

---

# 30. Role & Capability Manager — Pro

## Principle
This module manages WordPress roles/capabilities. It does not turn arbitrary UI visibility into authorization and does not replace object-level Policies.

## Role list
Columns:
- role display name;
- slug;
- user count;
- capability count;
- source classification core/WPE/custom/discovered;
- admin-equivalent risk indicator;
- modified snapshot status.

Filters:
- source;
- contains capability;
- admin-equivalent;
- has users/empty.

## Role identity
- display name editable for WPE/custom roles;
- slug immutable after creation by default;
- clone source optional;
- core Administrator/Editor/etc. not deletable.

## Create role
Required:
- display name;
- slug;
- clone from or blank capability set.

Slug uses safe role-key rules; collisions blocked.

## Capability inventory
Capabilities grouped by detected source:
- WordPress core;
- post types;
- taxonomies;
- WPEssential modules;
- WooCommerce/known adapter;
- third-party/discovered/uncategorized.

Search by capability key/label/source.

Unknown capabilities are preserved and editable only as raw capability keys with warning; do not discard because source plugin disabled.

## Grant/revoke
Every toggle shows:
- capability key;
- description when known;
- source;
- risk level;
- inherited? WordPress roles are flat grants, so UI must not imply inheritance that does not exist.

Saving role mutations:
- computes diff;
- highlights privilege escalation;
- checks current actor recovery access;
- Level 2 confirmation for dangerous capability bundle changes.

## Administrator-equivalent detector
Heuristic/rule registry flags capabilities such as plugin/theme/user management and WPE dangerous operations. It is a warning/risk classifier, not a promise that any exact set mathematically equals Administrator.

## Delete role
Allowed only custom/non-protected roles.

Before delete:
- count users;
- choose replacement role(s) where needed;
- preview capability loss;
- protect current actor;
- snapshot roles;
- Level 2 confirmation.

No user is deleted when role is deleted.

## Multiple roles
WordPress user objects can hold multiple roles. UI supports add/remove role, while clarifying that many plugins assume one primary-looking role. WPE does not invent a fake authoritative “primary role” unless a presentation label is explicitly required.

## Bulk user assignment
- Query/filter users;
- add role;
- remove role;
- replace selected roles advanced;
- affected count preview;
- background batch;
- anti-lockout rules.

## Compare roles
Select 2–4 roles:
- common capabilities;
- only-in-A/B;
- risk differences;
- export diff.

## Presets
WPEssential may provide named role/capability presets for module access, but applying a preset shows exact diff and never overwrites unrelated third-party capabilities unless mode explicitly says replace.

Default preset action = additive.

## CPT/Taxonomy capability helper
Consumes capability requirements from CPT/Taxonomy definitions and can propose grants. Source definition remains CPT/Taxonomy module.

## Membership role sync
Role Manager exposes role choices/risk; Membership owns mapping and lifecycle. Directly assigning a role does not create membership unless a separately explicit reverse-sync workflow is configured; default no reverse sync.

## Test-as-role / simulation
Potentially useful but security-sensitive. V1 preferred implementation is policy/capability explanation/preview rather than changing administrator session identity. Full impersonation requires separate ADR/audit/re-auth and must never expose credentials/session hijack behavior.

## Backup/restore
Before high-risk role change, snapshot:
- roles and capabilities;
- affected user-role assignments where operation changes them.

Restore creates audited mutation; cannot silently demote Super Admin in multisite.

## Multisite
- Super Admin semantics displayed separately;
- site roles vs network capabilities distinguished;
- operations requiring network admin unavailable on site-only context.

## Anti-lockout hard rules
- actor cannot remove own last known path to required WPE recovery/admin capability through ordinary flow;
- current session capability revalidated server-side;
- recovery mechanism documented;
- dangerous role mutation Level 2 or 3 depending impact.

## Import/export
Role definitions export capability keys/source metadata, not users by default. User-role assignment export is separate data export with privacy controls.

## Tests
- custom create/clone/delete;
- unknown capability preserved;
- current admin lockout blocked;
- multisite Super Admin behavior;
- bulk role concurrency;
- membership side-effect does not become source of truth;
- API privilege escalation payload rejected.

---

# Identity & Access specification status

User Profile, Membership and Role/Capability behavior are **Specified at Phase 0 behavioral level**. Open blockers include privacy defaults, Membership runtime schema/cache, protected-file delivery, and platform authorization/capability ADR details.
