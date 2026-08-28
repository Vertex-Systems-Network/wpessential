# WPEssential — Role & Capability Manager Competitive Parity Expansion

Status: **Phase 0 exhaustive planning addendum / no development authorization**  
Parent surface: **30 — Role & Capability Manager**

## 1. Purpose

Extend the existing Role & Capability Manager to meet and exceed User Role Editor / Members-style role administration while preserving WordPress as the actual role/capability authority.

This addendum is authoritative together with `DASHBOARD-PROFILE-ROLES-EXHAUSTIVE-SPEC.md`.

## 2. Existing WPE strengths preserved

Already planned:
- create/clone/edit/delete custom roles;
- allow / explicit deny / absent distinctions;
- multiple roles;
- individual user capabilities;
- primitive vs meta capability awareness;
- object-aware effective-capability explain;
- capability provenance inventory;
- role comparison/diff;
- default role handling;
- role snapshots/rollback;
- anti-lockout;
- administrator-equivalent risk detection;
- site vs Super Admin separation;
- import/export;
- Multisite-aware operations.

## 3. Assignable Role Policy / role hierarchy

Add a separate **Role Administration Policy** object.

For each operator role/user/Policy, configure target roles they may:
- list/see users of;
- create users into;
- assign;
- remove;
- bulk assign/remove;
- edit profile of;
- edit individual capabilities of;
- edit role definition of;
- delete;
- clone;
- import/replace.

Presets:
- unrestricted within current site;
- below-my-tier;
- selected target roles;
- selected roles excluded;
- custom Policy.

Administrator and Super Admin semantics are never represented by simple numeric hierarchy alone.

## 4. Enforcement surfaces

Role Administration Policy must be enforced server-side across:
- Users list;
- Users role tabs/counts where safe;
- Add User/New User;
- Edit User;
- bulk Change Role;
- individual capability editor;
- REST endpoints;
- Abilities;
- Workflow user/role actions;
- Import/Export;
- Multisite site/network admin flows.

Hiding a dropdown item without enforcing the mutation endpoint is a defect.

## 5. Administrator Recovery / Rescue

Add a dedicated, separately privileged recovery flow inspired by the market need proven by Members.

### Eligibility
Candidate default:
- built-in Administrator recovery principal on single site;
- Super Admin / explicitly configured recovery principal on Multisite;
- custom cloned admin-like roles are not automatically recovery principals.

### Flow
1. recovery route requested;
2. generic response prevents account enumeration;
3. identity eligibility checked;
4. rate/IP/account limits;
5. one-time cryptographically random artifact generated;
6. short expiration, candidate 15 minutes;
7. email sent through configured transport;
8. token consumed once;
9. current role/cap state impact preview internally determined;
10. restore a documented minimal safe recovery capability set or built-in Administrator role according to selected recovery profile;
11. revoke token;
12. audit event + notification;
13. prompt operator to review role drift.

### Safety
- no reusable rescue link;
- no raw token logs;
- no rescue for arbitrary email addresses;
- no AI/MCP exposure by default;
- network recovery never mutates Super Admin status through an ordinary site path;
- rate-limit and abuse monitoring required.

## 6. Capability provenance & orphan cleanup

Capability Registry adds:
- discovered source/provider where known;
- first/last observed;
- roles/users using it;
- CPT/tax/WPE/plugin reference;
- primitive/meta/unknown classification;
- risk level;
- active provider present/missing;
- orphan candidate state.

Orphan capability action:
- inspect;
- mark ignored;
- remove from selected roles/users with diff;
- never auto-delete unknown capability just because provider is inactive.

## 7. Admin-surface restriction integrations

Role Manager provides linked policy configuration while owning modules enforce it.

Targets:
- admin menu items → Admin Menu Manager;
- toolbar items → Admin Theme/Admin Experience adapters;
- dashboard widgets → Dashboard Widgets;
- meta boxes / editor panels → Admin/UI feature policy adapter;
- profile fields → User Profile;
- frontend navigation → navigation visibility adapter;
- forms → Forms Policy;
- protected posts/pages/CPT resources → Policy engine;
- plugin management operations → WordPress capability/resource policy;
- WPE module screens/Abilities → KPA/Policy.

Do not duplicate each surface's enforcement code in Role Manager.

## 8. Object-level content access

Market tools may expose lists of posts/pages/authors/taxonomies. WPE models this as resource Policy.

Role Manager may provide a guided builder:
- subject roles/users;
- resource post types;
- explicit records;
- authors;
- taxonomy terms;
- ownership relation;
- action: read/edit/delete/publish/etc.;
- allow/deny;
- precedence preview.

Compiled result is a Policy definition, not thousands of synthetic capabilities.

## 9. Plugin-operation policy

Provide a guided high-risk profile over native/plugin management capabilities:
- view installed plugins;
- activate/deactivate selected plugin families where safely representable;
- install/update/delete remain separately privileged;
- network-active plugins are Network Admin scope;
- dependency plugin relationships considered;
- no UI-only hiding as enforcement.

Exact safe granularity depends on WordPress APIs and is evidence-gated.

## 10. User operations

Users screen additions:
- users with no role;
- multiple-role users;
- individual-capability override present;
- administrator-equivalent effective access;
- stale/orphan role assignment;
- role drift since snapshot;
- network membership/site assignment filters.

Bulk actions:
- add role;
- remove role;
- replace roles with destructive preview;
- clear individual override;
- apply role template;
- export selected effective-access report.

## 11. Role templates and network sync

Multisite profiles:
- Site-only role;
- Network template copied to selected sites;
- linked/enforced network role definition only if explicitly supported;
- compare drift across sites;
- dry-run synchronization;
- conflict classification;
- skip/migrate/manual-review strategy;
- new-site bootstrap policy;
- no implicit Super Admin mutation.

## 12. Permission viewer / explainability

Add views:
- **Can this user do X?**
- **Who can do X?**
- **Why can this user access this screen/resource?**
- **Which capability/Policy grants or denies it?**
- **Which role change would remove/grant access?**

Inputs may include:
- user;
- role;
- capability;
- object/resource;
- site;
- module screen/Ability.

Outputs show evidence chain, not only final boolean.

## 13. Import / Export / Migration

Import choices:
- create;
- merge;
- replace;
- rename role;
- skip.

Preview:
- users affected;
- individual overrides;
- target-role policy dependencies;
- admin-equivalent risk;
- default-role impact;
- network target sites;
- unknown capabilities.

Compatibility importers may recognize Members/User Role Editor role exports where licensing/source format permits, but must normalize through WPE preview rather than blindly overwrite WordPress roles.

## 14. Support impersonation boundary

Actual user switching is a separate protected Account/Support action.

Role Manager can:
- simulate permissions;
- link to approved support impersonation tool;
- show audit trail.

Role Manager must not silently turn “Test as Role” into a live session takeover.

## 15. AI Prompt additions

Examples:
- “Create a Content Manager role that cannot manage plugins or administrators.”
- “Let Store Managers assign only Customer and Subscriber roles.”
- “Explain why this editor can edit this page.”
- “Compare Administrator and Agency Admin and show dangerous differences.”
- “Find orphan capabilities left by removed plugins.”
- “Plan a safe network-wide role sync.”

AI may draft role diffs/Policies. High-risk role mutation, rescue, network sync, user impersonation or privilege escalation requires deterministic validation and explicit approval.

## 16. MUST NOT

- do not replace `current_user_can()` / `map_meta_cap()` with a parallel permission database;
- do not infer power solely from role names;
- do not model Super Admin as an ordinary role checkbox;
- do not let a menu/widget restriction become authorization;
- do not delete unknown capabilities automatically;
- do not let a user assign a target role they are forbidden to administer merely because they have generic `edit_users`;
- do not run rescue without enumeration-safe response, expiration and rate limits;
- do not let AI grant itself capabilities.

## 17. Evidence

Supplemental parity namespace: **RPR-001…RPR-176**, executed **0/176**.

Existing `RA-01…RA-176` remains the canonical Role runtime evidence protocol. RPR focuses on newly added hierarchy/recovery/integration/compatibility behaviors and does not upgrade RA certification.