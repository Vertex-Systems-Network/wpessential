# ADR-0229 — Custom Tables Dedicated Migration Capability

Status: **ACCEPTED**
Date: **2026-10-19**
Approval: `GOV-OWNER-CONSENT-002` (full autonomy; decision recorded instead of asked)
Builds on: ADR-0227, ADR-0228; `docs/MODULES/CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md` §25

## Context

Spec §25: "apply migration" is a separate permission, significantly stronger than ordinary row edit. ADR-0228 kept every R1 migration path on `manage_options` and listed a dedicated capability as a follow-up. Sites could not grant `manage_options` without also granting schema migration.

## Decision

1. **One canonical capability.** `CustomTablesCapabilities::APPLY_MIGRATIONS` = `wpessential_apply_table_migrations`. It replaces `manage_options` in:
   - `CustomTablesModule::CAPABILITY` (the seven ability descriptors);
   - `CustomTablesAdminController` (menu and render check);
   - the `$capability` defaults of `R1MigrationRunLifecycleService`, `WpdbMigrationExecutionConfirmationIssuer` and `MigrationExecutionAuthorizationPolicyAdapter`.

   All checks still go through `PolicyEngine` → `CapabilityCheckerInterface` → `WordPressCapabilityChecker` → `current_user_can()`. PolicyEngine semantics are unchanged.
2. **Runtime default grant.** The module registers a `user_has_cap` filter. When the new capability is checked and the user holds `manage_options`, it is granted. Nothing is written to roles or user meta; removing the filter removes the grant. On multisite the rule is the same (`manage_options` on the current site). Super admins pass through WordPress core as they already do for `manage_options`.
3. **Revocation.** `wpessential_custom_tables_grant_migration_capability` (bool `$grant`, int `$user_id`) can deny the default grant while keeping `manage_options`. Only a strict `true` grants (fail-closed).
4. **Explicit values win.** If the user's caps already contain the capability, true or false, the filter leaves it unchanged. An explicit deny is never overridden.
5. **No new surface.** No REST, AI or CLI channel, no role registration.

## Consequences

- Administrators keep access by default. Sites can split migration rights from `manage_options` with a filter or an explicit per-user/role capability.
- Custom checkers in tests and integration scripts must check the new capability.
- Role-management UI for the capability remains a follow-up of the Custom Tables roles/permissions surface.
