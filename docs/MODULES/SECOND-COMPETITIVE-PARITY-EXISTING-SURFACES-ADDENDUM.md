# WPEssential — Second Competitive Parity Addendum for Existing Surfaces

Status: **Phase 0 exhaustive planning addendum / no development authorization**  
Date: **2026-08-29**

## 1. Purpose

This addendum records requested competitor-derived improvements that belong to existing WPEssential owners rather than new duplicate modules.

Research basis: `../RESEARCH/SECOND-COMPETITIVE-AUDIT-BACKUP-MEDIA-ORDER-SECURITY-FONTS-PROFILE-CROCOBLOCK-2026-08.md`.

## 2. Backup Manager expansion

Add:
- certified incremental/differential backup profile;
- chain/base dependency graph and orphan-base diagnostics;
- changed-file/hash incremental candidates;
- DB incremental/change-capture only where backend semantics are proven;
- pre-update/pre-migration automatic recovery point integrations;
- one plan → multiple destination fan-out with required/optional mirrors;
- provider direct-restore capability matrix;
- standalone recovery application/package candidate profile;
- WP-CLI surface using canonical Abilities;
- MCP/AI backup status/run/cancel/log actions under Policy and explicit high-risk approval;
- migration handoff to Surface 55;
- backup health score based on destination verification + restore confidence + age + encryption/recovery readiness, never only latest job status.

Backup Manager remains backup/recovery truth. Surface 55 owns staging topology/push/pull.

## 3. Surface 28 — Media Asset Replacement Lifecycle

Add a first-class replacement flow:

Modes:
- replace binary but preserve attachment identity/current URL where compatible;
- replace and rename/repath with reference-update Plan;
- create new attachment and supersede old attachment;
- restore previous retained asset revision where available.

Preflight:
- source/target MIME compatibility;
- dimensions/duration/metadata;
- referenced-in graph;
- builders/serialized values;
- offload/CDN ownership;
- derivatives;
- protected/private delivery;
- cache state;
- storage free space.

Execution:
- preserve canonical original according media policy;
- write/checksum new artifact;
- regenerate certified derivatives;
- update attachment metadata;
- reference changes delegated to Search/Replace with dry run;
- purge/invalidate cache/CDN/offload via adapters;
- verify representative references;
- record old/new fingerprint and actor.

Optional external image editing such as background removal is a provider action with provenance/cost/privacy approval and never silently changes the canonical original.

## 4. Surface 50 — Header/Footer Code parity

Add:
- explicit `latest N posts` context preset;
- category/tag/taxonomy object presets;
- selected CPT instance presets;
- coarse desktop/mobile display preset;
- manual shortcode/block placement preset;
- created-by / last-edited-by / timestamps in list;
- Header Footer Code Manager migration importer;
- before/after-content compatibility matrix for classic/block themes and page builders;
- snippet occurrence/placement diagnostics.

Existing consent/CSP/SRI/environment/dependency/revision capabilities remain authoritative and stronger than baseline market tools.

## 5. Link Health parity

Add:
- engine profile: Local / Remote Cloud / Hybrid;
- cloud provider data-transfer disclosure and opt-in;
- inline quick actions: Edit Target / Unlink / Ignore / Snooze / Recheck;
- quick action always compiles the owning typed Fix Plan before mutation;
- occurrence source types explicitly cover comments, custom fields, menus, block attrs, media, redirects;
- notification modes: immediate critical, daily digest, weekly digest, scan-complete;
- saved issue views and bulk triage;
- agency/network summary counts without raw cross-site leakage.

## 6. User Profile + Membership + Forms + Role parity from Profile Builder

### User Profile Builder
Add:
- multiple profile/view/edit compositions per role/segment;
- multi-step profile-edit composition;
- field columns/layout schema;
- repeater/group fields through shared Fields;
- profile change approval per field/group;
- public profile permalink profiles;
- member/user directory presets with search/sort/facets;
- avatar/media ownership;
- user import/export mapping;
- custom account navigation menu composition;
- hide/restrict wp-admin presentation/action through Admin Menu/Policy owners.

### Registration / Membership
Add/confirm:
- multiple registration flows by intended role/Plan;
- email confirmation;
- admin approval;
- role assignment side effect only after validated flow;
- content/block/Woo restriction adapters;
- file restriction through protected-file owner;
- password policy UI backed by WordPress auth;
- redirect matrix for register/login/logout/reset/profile;
- per-flow CAPTCHA/spam adapter;
- profile registration fields with field-level privacy.

### Account security
Add account-security composition points for:
- TOTP/2FA provider adapter;
- recovery codes where provider supports;
- WebAuthn/passkey future adapter;
- social/OAuth login via Account Link owner;
- re-auth requirements for sensitive profile changes.

Membership does not implement a second password/session stack.

### Woo adapter
Support typed mappings for:
- billing/shipping profile fields;
- My Account registration/edit profile surfaces;
- checkout field synchronization where Woo APIs allow;
- role/Plan-based store access via Policy, not visual hiding.

## 7. JetEngine parity refinements to existing platform

### Custom Tables / CCT parity
- CCT-style application entity preset over Custom Tables + Fields + Admin Columns + CRUD;
- optional single public route only through explicit route/listing definition;
- table-per-type isolation profile;
- import/export + REST + relation mappings;
- scale guidance explaining when CPT vs custom table is appropriate.

### Relations
- optional separate physical relation table per high-scale relation where evidence justifies;
- relation meta fields;
- minimum parent/child constraints;
- create-related-item UX through forms/admin adapters;
- REST get/update relation Abilities with capability/Policy enforcement.

### Query Builder
- first-class query provider matrix;
- Relations Query;
- User Data Store Query from Surface 54;
- REST source query;
- merged/sub-query profile with explicit pagination/cache limitations;
- query endpoint publication with permission/rate/row-limit controls;
- query ID/provider binding for filtering integrations;
- cache on/off + invalidation/explainability.

### Dynamic Listings / Tables
- Dynamic Table profile;
- columns from fields/tokens/components;
- sort/filter/pagination;
- responsive table/card fallback;
- listing source may be posts/terms/users/CCT/relations/remote/Store Query;
- optional chart visualization adapter only over typed Query/Aggregate data.

### Conditional Logic / Dynamic Visibility
- shared conditions exposed consistently to listings/components/fields/forms/profile routes;
- current user/entity/field/relation/store state facts;
- never use visibility as authorization.

### DVR / Dynamic Tags / Macros
- macro generator UX over typed DVR tokens;
- context + fallback values;
- typed aggregation functions delegate to F04/Query rather than arbitrary callbacks;
- supported-context matrix in UI.

### Reference Data / Glossaries
Add a reusable **Reference Data Set** definition type:
- manual key/label pairs;
- CSV/JSON import;
- remote source via Sync/Connection only;
- value/label/additional typed columns;
- locale support;
- version/revision;
- use as field choices, filter values, formula lookups and display labels;
- no private ad-hoc copies per field.

### AI Website Structure parity
Solution Blueprint + AI Requirement Compiler should expose a guided mode capable of drafting:
- CPTs;
- taxonomies;
- fields;
- tables/CCT presets;
- relations;
- queries;
- listings;
- filters;
- forms;
- account/profile routes.

Generated definitions remain Draft, validated against dependency/security/privacy/performance rules, then approved individually or as a Blueprint Plan.

## 8. Evidence reservations for existing-surface refinements

Supplemental planning namespaces reserved:
- `BKX-001…176` — advanced backup/incremental/CLI/MCP parity;
- `MRL-001…176` — Media Replacement Lifecycle;
- `PBX-001…176` — Profile/Registration parity;
- `JEX-001…176` — JetEngine existing-surface parity refinements;
- `LHX-001…176` — Link Health parity refinements;
- `HFC-001…176` — Header/Footer Code migration/placement parity.

All executed: **0/176** for every namespace.

No existing runtime certification is upgraded by this planning addendum.