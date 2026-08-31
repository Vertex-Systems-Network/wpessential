# WPEssential — Access, Admin Experience, Media Performance & Code Injection Market Audit — August 2026

Status: **Planning research / no development authorization**  
Date: 2026-08-29

## 1. Scope

Owner-requested audit of:
- `members`
- `wp-members`
- `user-role-editor`
- `admin-color-schemes`
- `admin-color-schemer`
- `image-prioritizer`
- `auto-sizes` / Enhanced Responsive Images
- `insert-headers-and-footers` / WPCode

The supplied WordPress.org SVN trunk URLs were treated as the requested source family. Direct directory rendering is not reliable in the research client, so conclusions were corroborated against current WordPress.org plugin pages, official/public repositories where available, current WordPress Core developer documentation, and current security/release evidence. Source-derived behavior is separated from WPE architecture decisions below.

## 2. Executive decision

| Area | WPE decision | Reason |
|---|---|---|
| Members / WP-Members | **Expand existing Membership + Role/Profile/Form/Policy surfaces** | WPE already has the broader entitlement/lifecycle architecture; competitor UX and compatibility gaps are compositional, not a new membership engine. |
| User Role Editor | **Expand existing Role & Capability Manager** | Existing module already owns native WordPress role/cap authority; add hierarchy, recovery, object/admin-surface delegation and stronger compatibility UX. |
| Admin Color Schemes / Schemer | **New Surface 49: Admin Theme, Branding & Experience Manager** | Visual admin theming is a reusable user-facing primitive not owned by current menu/settings/dashboard modules. |
| Image Prioritizer / Auto Sizes | **Expand existing Surface 28 Media Rules** | Priority/lazy/responsive/modern-format behavior belongs to one media delivery pipeline, not a second image plugin. |
| Insert Headers and Footers / WPCode | **New Surface 50: Safe Script, Tag & Code Injection Manager** | Strong user demand exists, but arbitrary PHP/eval is incompatible with WPE safety. A scoped browser-code/tag manager is reusable and distinct from Asset Registry. |

## 3. Membership / access findings

### Members market behavior
Current Members product behavior includes:
- native WordPress role/capability editing;
- multiple user roles;
- explicit capability deny;
- role cloning;
- role import/export with conflict preview;
- role/capability-based restricted content;
- role-aware shortcodes/widgets;
- whole-site privacy mode;
- Administrator Rescue via a short-lived email link;
- broad third-party integration around native capabilities.

### WP-Members market behavior
Current WP-Members behavior includes:
- restrict/hide posts, pages and CPTs;
- menu visibility for logged-in users;
- frontend login, registration and profile experiences;
- custom registration/profile fields;
- Woo registration/checkout field integration in supported contexts;
- custom memberships and content restriction;
- admin notifications for registrations;
- registration approval;
- teaser/excerpt behavior;
- editable dialogs and emails;
- shortcodes plus extensive action/filter/API extension points.

### WPE conclusion
WPE Membership already exceeds these plugins in core domain modeling: Plan, Enrollment, Entitlement, Access Rule, Benefits, Drip, upgrades, teams/seats, protected files, provider normalization and Policy explainability. Required parity additions are therefore UX/profile/policy presets and interoperability, not a replacement membership engine.

Must add:
- private-site / site-lockdown Policy profile with safe public exclusions;
- registration/onboarding wizard built from Forms + Profile + Membership + Workflow;
- registration approval and email-verification profiles;
- default restricted/public behavior per resource type with per-resource override;
- teaser/excerpt/fallback presets;
- login/register/profile Blocks, shortcodes/components and Dashboard routes;
- navigation visibility adapter that never substitutes for server authorization;
- dialogs/messages/email composition through shared renderer/email systems;
- member directory/login widget compositions through Listings/Profile;
- role-based legacy membership migration assistant;
- Members/WP-Members compatibility/migration inspection;
- developer integration through Events, Abilities and SDK instead of 120+ undocumented/private hook variants.

## 4. Role Manager findings

User Role Editor market bar includes:
- create/edit/copy/delete roles;
- edit capabilities with checkbox UX;
- default registration role;
- individual user capabilities;
- multiple roles;
- add/remove orphan custom capabilities;
- Multisite support and network synchronization;
- Pro restrictions for admin menus, frontend menus, widgets, meta boxes, posts/pages/CPT access, plugins, forms and content;
- import/export;
- “Other roles access” hierarchy controlling which roles an operator can see/assign/manage;
- backend page permission inspection.

WPE already has stronger primitives:
- allow/deny/absent distinction;
- primitive vs meta capability awareness;
- object-aware `map_meta_cap()` explanation;
- individual user overrides;
- role diff/snapshots/rollback;
- anti-lockout and administrator-equivalent risk analysis;
- site-vs-Super-Admin separation;
- Policy/Ability integration.

Required additions:
- **Assignable Role Policy / Role Hierarchy**: which target roles an operator may list, create users into, assign, remove, or edit;
- server-side enforcement across Users list, Add User, profile, bulk actions, REST/Abilities and Multisite;
- explicit built-in Administrator Recovery flow using one-time expiring email artifact + rate limits + audit;
- orphan capability inventory with provenance/usage before deletion;
- capability source/owner registry and external drift view;
- admin page/menu/meta-box/editor-feature restrictions delegated to owning Admin/UI surfaces but configured through linked role policies;
- object-level content access delegated to Policy engine, not flattened into fake capabilities;
- plugin-management permission profiles through typed WordPress capability/resource rules;
- form access delegated to Forms Policy;
- bulk users-without-role and multi-role operations;
- network role template/sync dry run and conflict report;
- support impersonation remains separate high-risk account action, not permission simulation.

## 5. Admin theming findings

`admin-color-schemes` demonstrates persistent demand for additional predefined schemes. `admin-color-schemer` demonstrates demand for building custom schemes inside wp-admin. Current WordPress still exposes `wp_admin_css_color()` and the core scheme registry.

WordPress 7.0 materially modernized the Dashboard and admin styles. WordPress 7.1 adds Design System theming with semantic `wp-theme` CSS custom properties and brings admin color schemes into more Site Editor shell surfaces. Therefore a competitive WPE solution must be version-adaptive instead of hard-coding legacy selectors.

Decision: new **Admin Theme, Branding & Experience Manager**.

It must exceed simple color pickers with:
- token-driven color/roundness/density/typography/interaction profiles;
- native color-scheme registration where appropriate;
- WordPress 7.1 `wp-theme` token integration with older fallback;
- per-user, role, site, network and environment assignment;
- default vs forced vs selectable policy;
- staging/production visual identity;
- login branding and admin-bar branding;
- accessible contrast validation;
- dark/light/high-contrast variants;
- live preview and compatibility diagnostics;
- import/export/versioning;
- no authorization semantics tied to visual hiding.

## 6. Image performance findings

Image Prioritizer uses real visitor URL metrics through Optimization Detective to make decisions impossible to make safely from server heuristics alone. Current behavior includes:
- LCP-aware responsive preload links / Link headers;
- `fetchpriority=high` for common LCP;
- removal of unjustified server-added high priority where field evidence contradicts it;
- `fetchpriority=low` for occluded initial-viewport images such as hidden carousel slides;
- avoid lazy-loading potential initial-viewport images;
- lazy-load confidently below-viewport images when adequate viewport data exists;
- synchronize `sizes=auto` with lazy-loading;
- image and picture support;
- background-image and video-poster optimization in supported paths.

Enhanced Responsive Images (`auto-sizes`) improves `sizes` accuracy using block-theme layout information. Its original `sizes=auto` feature has been merged into WordPress Core, which demonstrates a critical WPE rule: **detect Core capability and do not duplicate merged features**.

Adjacent WordPress Performance Team features strengthen the competitive bar:
- Modern Image Formats: AVIF/WebP derivative generation, fallbacks, Picture support and capability-aware format choice;
- Image Placeholders: dominant-color placeholders.

Decision: expand existing Media Rules/Watermarker into a coherent **Media Transformation, Performance & Delivery** capability set while preserving the original-source immutability invariant.

## 7. Code / script injection findings

WPCode has strong market demand (2M+ sites) around:
- header/body/footer insertion;
- JS, CSS, HTML, text and PHP snippets;
- conditional logic;
- automatic placements;
- code libraries/generation;
- import/export.

However, a 2026 public advisory reported a remote-code-execution path in affected WPCode versions involving snippet CPT capability exposure and PHP `eval()` execution. Regardless of exact future patch state, the architectural lesson is strong: browser-managed arbitrary PHP execution dramatically enlarges the privilege and remote-entry attack surface.

WPE decision:
- **do not reverse ADR-0004**;
- no generic PHP/eval runtime;
- add a dedicated safe browser-code/tag surface for HTML/CSS/JavaScript/external scripts/meta/link/JSON-LD with strict placement, Policy, CSP, consent, versioning and audit;
- server-side custom logic belongs in a reviewed extension/plugin using the SDK and typed Abilities.

## 8. Market-level differentiators WPE must preserve

1. Native WordPress authority is preserved; WPE does not invent parallel role/login authority.
2. Visibility never substitutes for authorization.
3. Membership billing facts do not directly authorize resources.
4. Admin theming is version/capability adaptive.
5. Image optimization uses evidence and Core capability detection rather than blindly setting `fetchpriority`/lazy rules.
6. Custom script injection cannot become a PHP RCE console.
7. Every mutation has revision/diff/audit/recovery semantics.
8. Multisite ownership is explicit.
9. AI creates structured drafts/plans and cannot silently publish high-risk access/code changes.
10. Competitor popularity is demand evidence, not a reason to copy code or architecture.

## 9. Development gate

Research only. No supplied plugin source was executed. No WordPress role, membership, admin style, image output, script, PHP snippet or database state was modified.