# WPEssential — Capability & Policy Matrix

Status: Phase 0 planning. No runtime implementation authorized.

## Principle
UI visibility is never authorization. Every read/create/update/delete/run/export/import/restore/protect operation is checked server-side.

## Capability tiers
WPEssential should register granular capabilities grouped by operation rather than one global `manage_wpessential` switch.

### Platform administration
- `wpe_view_home`
- `wpe_manage_modules`
- `wpe_view_diagnostics`
- `wpe_manage_account_connection`
- `wpe_manage_support`
- `wpe_export_configuration`
- `wpe_import_configuration`

### Common builder pattern
Every definition-driven module should expose where relevant:
- `wpe_<module>_read`
- `wpe_<module>_create`
- `wpe_<module>_update`
- `wpe_<module>_delete`
- `wpe_<module>_publish`
- `wpe_<module>_export`
- `wpe_<module>_import`

### Execution-sensitive pattern
For modules that execute or alter runtime state:
- `wpe_<module>_run`
- `wpe_<module>_retry`
- `wpe_<module>_cancel`
- `wpe_<module>_restore`
- `wpe_<module>_unsafe`

`unsafe` is reserved for exceptional high-risk features and must not be granted by ordinary presets.

## High-risk dedicated capabilities
- `wpe_backup_restore`
- `wpe_reset_execute`
- `wpe_tables_schema_change`
- `wpe_tables_unsafe_sql` (future developer mode only if ever approved)
- `wpe_roles_manage_admin_equivalent`
- `wpe_membership_force_access`
- `wpe_membership_manage_billing_links`
- `wpe_membership_bypass_access`
- `wpe_protector_recovery_manage`
- `wpe_secrets_manage`
- `wpe_connections_manage_credentials`
- `wpe_support_include_sensitive_diagnostics`

## Role presets
WPEssential may offer presets, but capabilities remain canonical.

Suggested presets:
- **WPEssential Viewer** — read-only operational visibility.
- **Content Architect** — CPT/Taxonomy/Fields/Relations/Query/Listings without operations/security powers.
- **Experience Builder** — dashboards/profile/menu/widgets/settings/templates.
- **Automation Manager** — forms/workflows/cron/notifications/email/connections, no secrets reveal or destructive operations.
- **Membership Manager** — plans/members/enrollments/normal access operations, no forced bypass or infrastructure secrets.
- **Operations Manager** — backup/import/watermark/diagnostics with restore/reset powers separately controlled.
- **WPEssential Administrator** — broad product management but high-risk capabilities can still require explicit grant/re-auth.

Presets must never rely on role names at runtime.

## Resource-level Policy Engine
WordPress capabilities answer “may this principal perform this class of operation?” Policy answers “may this principal act on this specific resource/context?”

Examples:
- user can edit Form A but not Form B;
- manager can see dashboard route for assigned department only;
- member can download Asset X only while entitlement active;
- editor can update a custom-table row only if relation.owner_user_id == current user;
- API caller can run Ability only against resources matched by policy.

## Evaluation order
1. authentication/context validity where required;
2. WordPress/network/site boundary;
3. required WordPress/WPE capability;
4. module availability/health;
5. entitlement/product-management state where relevant;
6. resource Policy evaluation;
7. validation/business guard;
8. operation execution;
9. audit.

A later layer cannot override a failure in an earlier security boundary unless an explicitly designed audited bypass exists.

## Multisite
- Super Admin semantics are explicit, never inferred as an ordinary role.
- Site-level capabilities do not automatically grant network-level operations.
- Network-wide module/configuration changes require dedicated network capabilities.
- Membership/site content access remains site-scoped by default unless a future network-membership mode is explicitly designed.

## Re-authentication
High-impact operations should support recent-auth/re-auth requirements:
- full reset;
- backup restore;
- secrets/key operations;
- administrator-equivalent role/capability changes;
- unsafe developer SQL if ever approved;
- Membership force-allow/force-deny on protected resources when configured as sensitive;
- permanent deletion of critical module data.

## Ownership/creator is not automatic authorization
Creating a definition does not guarantee perpetual edit/delete rights. Authorization comes from current capabilities/policies, not stored creator ID alone.

## REST / Ability / CLI parity
The same policy contract applies to:
- React/admin API;
- REST endpoints;
- WordPress Abilities;
- WP-CLI;
- workflow execution;
- AI/MCP caller.

No alternate channel may bypass authorization because it is “internal.”

## Audit requirements
Audit at least:
- principal/user/application identity;
- capability/policy decision category;
- resource/module/action;
- outcome;
- correlation/request ID;
- before/after summary for sensitive mutations;
- bypass/re-auth usage.

Do not log secrets or unnecessary protected content.

## Acceptance tests inherited by every module
- unauthenticated denial where required;
- missing-capability denial;
- wrong-resource denial;
- correct-capability + policy allow;
- UI-hidden but direct-request denial;
- REST/Ability parity;
- multisite boundary;
- stale role/capability changes effective promptly;
- privilege escalation regression;
- audit record created for sensitive operations.