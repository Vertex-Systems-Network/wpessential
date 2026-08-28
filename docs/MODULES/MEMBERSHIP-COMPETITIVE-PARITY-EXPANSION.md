# WPEssential — Membership Competitive Parity Expansion

Status: **Phase 0 exhaustive planning addendum / no development authorization**  
Parent surface: **15 — Membership System**

## 1. Purpose

Extend the existing WPE Membership System with the strongest practical UX/interoperability patterns from Members and WP-Members without weakening WPE's existing Plan → Enrollment → Entitlement → Policy architecture.

This addendum is authoritative together with `MEMBERSHIP-SYSTEM.md`.

## 2. What WPE already does better

WPE already plans:
- Plans, Plan Groups and multiple simultaneous memberships;
- normalized Enrollment states;
- Benefits/Entitlements separate from WordPress Roles;
- provider-independent billing/source adapters;
- Access Rules with allow/deny semantics;
- drip/expiration/trial/grace;
- upgrades/downgrades;
- teams/seats;
- quotas;
- protected files;
- Workflow/Notification/Email integration;
- policy explainability;
- Multisite scope;
- audit/reconciliation.

Competitor parity therefore must be added as reusable presets/adapters, not a second membership engine.

## 3. Membership Site Lockdown / Private Site profile

Add an optional **Site Access Profile**.

Modes:
- public site — default;
- protected content only;
- authenticated-site;
- selected Membership required;
- maintenance/invite-only Membership profile.

Public exclusions must be explicit:
- login route;
- registration route where enabled;
- lost-password/reset flow;
- email-verification/activation route;
- privacy/legal pages;
- health/verification endpoints required by WordPress/provider integrations;
- explicitly public REST routes;
- explicitly public feeds if selected;
- webhook receiver routes only through their own authentication;
- static assets.

Never globally redirect every request without loop/system-route preflight.

Denied behavior:
- 401/login;
- 403/access page;
- safe internal redirect;
- membership/pricing CTA;
- 404-like concealment only if intentionally configured.

## 4. Registration & Onboarding Studio

This is a composition profile over Forms + User Profile + Membership + Workflow.

Screens:
- Registration Flows list;
- Flow editor;
- Approval queue;
- Activation/verification monitor;
- Registration diagnostics.

Flow steps may include:
1. collect identity fields;
2. collect approved profile/custom fields;
3. account existence/duplicate check;
4. spam/rate/abuse checks;
5. terms/privacy acknowledgement;
6. optional email verification;
7. optional admin approval;
8. optional Plan selection;
9. optional external billing handoff;
10. create/activate WordPress account through native APIs;
11. create Enrollment only after qualifying conditions;
12. role-sync side effect where configured;
13. welcome email/notification;
14. safe post-registration redirect/dashboard.

### Flow options
- allow guest self-registration;
- invite-only;
- admin-created only;
- email domain allow/deny;
- username strategy;
- email-as-login presentation where WordPress flow supports;
- default Plan;
- selectable Plans;
- approval required;
- email verification required;
- auto-login after safe verified registration;
- redirect after register/login/logout;
- terms checkbox/reference;
- rate limit profile;
- CAPTCHA/spam adapter;
- duplicate email behavior;
- existing user enrollment behavior;
- delete/retain rejected pending registration data;
- notification templates.

Passwords/reset artifacts never enter Form Entry analytics/logs.

## 5. Registration approval

Approval states:
- submitted;
- verification_pending;
- approval_pending;
- approved;
- rejected;
- expired;
- cancelled;
- error.

Approval options:
- approver capability/Policy;
- reason required for reject;
- SLA/reminder;
- auto-expiry;
- approve creates/enables the next allowed account/enrollment step;
- reject does not silently delete evidence unless retention policy says so.

No email click alone grants Membership unless the planned flow explicitly qualifies it.

## 6. Login / Register / Profile presentation adapters

Provide shared components for:
- Login Form;
- Registration Form;
- Lost Password;
- Reset Password;
- Account/Profile;
- Logout link/action;
- Membership status card;
- Upgrade CTA.

Expose through:
- Gutenberg blocks;
- WPE components;
- shortcodes for compatibility;
- Frontend Dashboard routes;
- supported builder adapters.

Theme integration must inherit available design tokens rather than hard-code forms.

## 7. Content restriction defaults

Add a **Restriction Defaults** layer per resource class.

For posts/pages/CPTs:
- public by default;
- restricted by default;
- hidden/concealed by default;
- inherit site access profile.

Per-resource override:
- inherit;
- public;
- restricted;
- concealed.

Bulk editor:
- set override;
- clear override;
- assign Access Rule/Plan requirement;
- preview affected resources.

Existing direct Access Rules always remain the actual Policy definitions.

## 8. Teaser / excerpt behavior

Fallback modes:
- no teaser;
- manual excerpt;
- content-before-More marker;
- generated bounded excerpt;
- custom renderer/component;
- replacement message only.

Options:
- length;
- preserve safe formatting;
- CTA;
- show title/featured image/meta;
- exclude protected dynamic fields.

Teaser generation must not evaluate protected blocks/tokens and then truncate them.

## 9. Navigation visibility

Adapters for:
- WordPress Navigation/Menu;
- admin-defined frontend menu sources;
- Dashboard navigation.

Conditions:
- logged-in/logged-out;
- Plan/Group/Entitlement;
- supplemental Role/Capability;
- custom Policy.

Critical rule: menu visibility is presentation only. Direct URL/resource Policy remains authoritative.

## 10. Dialogs, messages and email presets

Provide editable definitions for:
- login required;
- registration success/failure;
- verification sent/expired;
- approval pending/approved/rejected;
- access denied;
- upgrade required;
- membership expired/grace;
- password reset guidance;
- account disabled/suspended where supported.

Use shared Email/Renderer/DVR systems. Message strings do not become business logic.

## 11. Member directory and login widgets

Do not create private membership widget engines.

Presets compose:
- User Query;
- Profile presentation;
- Listing;
- Membership/Entitlement filters;
- Login component.

Directory options:
- plans/groups;
- public-profile permission;
- search/facets;
- sort;
- pagination;
- avatar;
- visible fields;
- empty/privacy states.

## 12. Legacy migration / competitor interoperability

Add migration assistants for detected legacy membership/access setups.

Initial audit targets:
- Members role/capability restrictions;
- WP-Members content restriction/meta/config;
- role-based membership conventions;
- generic WordPress role-only protected sites.

Workflow:
`detect → inventory → map → preview → unresolved items → dry run → import → verify → optional legacy disable later`.

Potential mappings:
- role → Plan candidate;
- restricted post/CPT → Access Rule;
- default blocked/unblocked → Restriction Default;
- custom registration fields → User Profile/Fields;
- registration form → Form definition;
- email/dialog copy → Email/Message definition.

Never silently convert role into canonical Membership truth without explicit mapping.

## 13. Developer extension parity

Instead of competitor-specific large hook surfaces, support:
- typed Events;
- Abilities;
- Policy adapters;
- Data Source/Field adapters;
- Workflow actions/triggers;
- Component/Block adapters;
- documented SDK extension points.

Compatibility hooks can exist where WordPress conventions require them, but canonical behavior stays typed/versioned.

## 14. Admin UX additions

Membership menu adds/clarifies:
- Registration & Onboarding;
- Approval Queue;
- Site Access / Private Site;
- Restriction Defaults;
- Migration Assistant;
- Login/Register/Profile Components;
- Messages & Dialogs;
- Compatibility diagnostics.

Overview alerts add:
- registration backlog;
- verification failure rate;
- orphan role-sync mappings;
- resource default conflicts;
- lockdown redirect loop risk;
- legacy plugin coexistence conflict.

## 15. AI Prompt additions

Prompt examples:
- “Create an invite-only members site with admin approval.”
- “Restrict all Courses except the public intro lessons.”
- “Migrate Members role restrictions into WPE Plans.”
- “Create login, registration, profile and upgrade screens.”
- “Audit why this user can still access this page.”

AI may draft definitions and migration maps; it may not approve registrations, elevate roles, grant Entitlements, publish site-wide lockdown, or execute migration without applicable approval.

## 16. Multisite

- registration flow is site-scoped by default;
- network template may instantiate flows;
- WordPress user identity is network-global where Core behaves that way, but Membership remains site-scoped unless a network Membership profile is explicitly certified;
- Site Access Profile cannot accidentally lock Network Admin;
- Super Admin recovery remains separate from site membership;
- site clone does not copy live enrollments/verification artifacts by default.

## 17. MUST NOT

- do not make Role the canonical Membership object;
- do not let billing status directly grant access;
- do not hide content client-side and call it restricted;
- do not store/reset passwords inside generic fields/forms;
- do not make private-site redirect rules apply to auth/webhook/system routes without preflight;
- do not convert legacy roles to Plans silently;
- do not treat navigation visibility as authorization.

## 18. Evidence

Supplemental competitive-parity evidence namespace reserved: **MPR-001…MPR-176**, executed **0/176**.

Existing Membership evidence remains separately authoritative:
- MBR core runtime;
- MB-F billing provider certification;
- PC-F protected file delivery.

No existing runtime certification is upgraded by this addendum.