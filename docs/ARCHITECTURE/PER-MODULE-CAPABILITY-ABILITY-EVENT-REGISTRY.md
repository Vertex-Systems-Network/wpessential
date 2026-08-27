# WPEssential — Per-Module Capability, Ability & Event Registry

Status: **Phase 0 planning — no runtime implementation authorized**  
This document turns the generic contracts in `SECURITY/CAPABILITY-POLICY-MATRIX.md` and `ARCHITECTURE/EVENT-AND-ABILITY-CATALOG.md` into a module-by-module registry.

## Contract rules

1. Capabilities are server-side authorization classes, not menu/UI flags.
2. Abilities are typed reusable operations. Registration does not automatically expose an Ability to REST, workflow, CLI or AI.
3. Events are immutable domain facts in past tense. They are not commands.
4. Sensitive/high-volume audit events may remain audit-only rather than general workflow triggers.
5. Every mutation Ability also requires resource Policy evaluation where a resource is involved.
6. Stable Ability/Event IDs are public contracts after stable release and require deprecation/version policy.
7. This registry defines intended vocabulary; input/output schemas remain module-spec artifacts before implementation.

---

## 1. Custom Post Types Builder (`cpt`) — Free

### Capabilities
`wpe_cpt_read`, `wpe_cpt_create`, `wpe_cpt_update`, `wpe_cpt_delete`, `wpe_cpt_publish`, `wpe_cpt_import`, `wpe_cpt_export`.

### Abilities
`wpessential/cpt.list`, `.get`, `.create`, `.update`, `.duplicate`, `.validate`, `.preview-impact`, `.publish`, `.archive`, `.export`, `.import`.

### Events emitted
`wpessential.cpt.definition.created|updated|published|archived|deleted`, `wpessential.cpt.rewrite.changed`.

### Events consumed
Definition dependency changes; taxonomy attach/detach notifications for diagnostics only.

---

## 2. Taxonomy Builder (`taxonomy`) — Free

Capabilities: `wpe_taxonomy_read|create|update|delete|publish|import|export`.

Abilities: `wpessential/taxonomy.list|get|create|update|duplicate|validate|preview-impact|publish|archive|export|import`.

Events: `wpessential.taxonomy.definition.created|updated|published|archived|deleted`, `wpessential.taxonomy.assignment_contract.changed`.

---

## 3. Custom Fields Builder (`fields`) — Pro

Capabilities: `wpe_fields_read|create|update|delete|publish|import|export`; elevated `wpe_fields_manage_secret_fields`.

Abilities: `wpessential/fields.group.list|get|create|update|duplicate|validate|publish|archive|export|import`, `wpessential/fields.schema.get|validate`, `wpessential/fields.value.validate`.

Events: `wpessential.fields.group.created|updated|published|archived|deleted`, `wpessential.fields.schema.changed`.

Field-value CRUD is performed through owning Data Source adapters, not a generic unrestricted Fields Ability.

---

## 4. Relations Builder (`relations`) — Pro

Capabilities: `wpe_relations_read|create|update|delete|publish|import|export`, `wpe_relations_manage_links`.

Abilities: `wpessential/relations.list|get|create|update|validate|publish|archive`, `wpessential/relations.link.list|attach|detach|reorder`, `wpessential/relations.integrity.check|repair-preview`.

Events: `wpessential.relations.definition.created|updated|published`, `wpessential.relations.link.attached|detached|reordered`, `wpessential.relations.integrity.issue_detected`.

Cascade repair/delete operations are high-risk and require separate dry-run/recovery semantics.

---

## 5. Status Manager (`status`) — Pro

Capabilities: `wpe_status_read|create|update|delete|publish`, `wpe_status_transition`.

Abilities: `wpessential/status.list|get|create|update|validate|publish`, `wpessential/status.transition.preview|run`, `wpessential/status.history.list`.

Events: `wpessential.status.definition.created|updated`, `wpessential.status.entity.transitioned`, `wpessential.status.transition.rejected` (audit/diagnostic class).

---

## 6. Custom Query Builder (`query`) — Pro

Capabilities: `wpe_query_read|create|update|delete|publish|import|export`, `wpe_query_execute`.

Abilities: `wpessential/query.list|get|create|update|duplicate|validate|preview|explain|execute|publish|archive|export|import`.

Events: `wpessential.query.definition.created|updated|published|archived`, `wpessential.query.execution.budget_exceeded` (diagnostic).

Public/runtime execution applies source policy, row/time budgets and parameter schemas.

---

## 7. Custom Tables Builder (`tables`) — Pro

Capabilities: `wpe_tables_read|create|update|delete|import|export`, `wpe_tables_rows_manage`, `wpe_tables_schema_change`; reserved future `wpe_tables_unsafe_sql`.

Abilities: `wpessential/tables.schema.list|get|create|change-preview|change`, `wpessential/tables.row.list|get|create|update|delete`, `wpessential/tables.query.preview|explain`, `wpessential/tables.export|import`.

Events: `wpessential.tables.schema.created|changed|migration_failed`, `wpessential.tables.row.created|updated|deleted` where event emission is enabled and payload-safe.

---

## 8. Admin Columns Builder (`columns`) — Pro

Capabilities: `wpe_columns_read|create|update|delete|publish|import|export`, `wpe_columns_inline_edit`, `wpe_columns_bulk_edit`.

Abilities: `wpessential/columns.view.list|get|create|update|duplicate|validate|publish|archive|export|import`, `wpessential/columns.cell.preview`, `.cell.update`, `.bulk-update-preview`, `.bulk-update`.

Events: `wpessential.columns.view.created|updated|published`, `wpessential.columns.bulk_edit.completed|failed`.

---

## 9. Dynamic Listings / Template Builder (`listings`) — Pro

Capabilities: `wpe_listings_read|create|update|delete|publish|import|export`.

Abilities: `wpessential/listings.list|get|create|update|duplicate|validate|preview|publish|archive|export|import`, `wpessential/listings.render`.

Events: `wpessential.listings.definition.created|updated|published|archived`, `wpessential.listings.render.budget_exceeded` (diagnostic).

---

## 10. Dashboard Widgets Manager (`dashboard-widgets`) — Pro

Capabilities: `wpe_dashboard_widgets_read|create|update|delete|publish`, `wpe_dashboard_widgets_manage_core_visibility`.

Abilities: `wpessential/dashboard-widgets.inventory`, `.list`, `.get`, `.create`, `.update`, `.duplicate`, `.preview`, `.publish`, `.archive`, `.dismiss`.

Events: `wpessential.dashboard_widget.created|updated|published|dismissed`.

---

## 11. Custom Admin Menu Builder (`admin-menu`) — Pro

Capabilities: `wpe_admin_menu_read|create|update|delete|publish`, elevated `wpe_admin_menu_recovery`.

Abilities: `wpessential/admin-menu.inventory`, `.profile.list|get|create|update|validate|preview|publish|restore-default`.

Events: `wpessential.admin_menu.profile.created|updated|published|restored`.

Menu visibility never emits/changes authorization grants.

---

## 12. Settings Page Builder (`settings`) — Pro

Capabilities: `wpe_settings_builder_read|create|update|delete|publish|import|export`; runtime page values use page-specific capabilities. Secret values require `wpe_secrets_manage` where applicable.

Abilities: `wpessential/settings.page.list|get|create|update|validate|preview|publish|archive|export|import`, `wpessential/settings.values.get|update` subject to page policy and secret redaction.

Events: `wpessential.settings.page.created|updated|published`, `wpessential.settings.values.updated` with safe changed-key summaries only.

---

## 13. Dashboard Builder (`dashboard`) — Pro

Capabilities: `wpe_dashboard_read|create|update|delete|publish|import|export`; runtime routes use policy/role/entitlement checks.

Abilities: `wpessential/dashboard.list|get|create|update|validate|preview|publish|archive`, `wpessential/dashboard.route.explain-access`.

Events: `wpessential.dashboard.definition.created|updated|published`, `wpessential.dashboard.route.access_denied` as sampled audit/diagnostic event.

---

## 14. User Profile Builder (`profiles`) — Pro

Capabilities: `wpe_profiles_read|create|update|delete|publish`, `wpe_profiles_manage_private_fields`.

Abilities: `wpessential/profiles.template.list|get|create|update|validate|preview|publish`, `wpessential/profiles.user.get|update` subject to self/admin/resource policy.

Events: `wpessential.profile.template.created|updated|published`, `wpessential.profile.user.updated` with privacy-safe changed-field metadata.

---

## 15. Membership System (`membership`) — Pro

Capabilities:
- `wpe_membership_read`
- `wpe_membership_plans_manage`
- `wpe_membership_rules_manage`
- `wpe_membership_members_manage`
- `wpe_membership_enrollments_grant`
- `wpe_membership_enrollments_revoke`
- `wpe_membership_teams_manage`
- `wpe_membership_reconcile`
- elevated `wpe_membership_force_access`, `wpe_membership_manage_billing_links`, `wpe_membership_bypass_access`.

Abilities:
`wpessential/membership.plan.list|get|create|update|validate|publish|archive`,
`wpessential/membership.rule.list|get|create|update|validate`,
`wpessential/membership.enrollment.list|get|grant|pause|resume|revoke|explain`,
`wpessential/membership.entitlement.list|explain|recompute`,
`wpessential/membership.access.check|explain`,
`wpessential/membership.team.list|get|create|update`,
`wpessential/membership.seat.assign|release`,
`wpessential/membership.invitation.issue|revoke|accept`,
`wpessential/membership.reconciliation.preview|run`.

Events:
`wpessential.membership.plan.created|updated|published|archived`,
`wpessential.membership.enrollment.created|trialing|activated|grace_started|paused|resumed|expired|revoked`,
`wpessential.membership.cancellation.scheduled|cancelled`,
`wpessential.membership.entitlement.granted|revoked|recomputed`,
`wpessential.membership.team.seat_assigned|seat_released`,
`wpessential.membership.invitation.issued|accepted|expired|revoked`,
`wpessential.membership.billing_source.linked|unlinked`,
`wpessential.membership.reconciliation.drift_detected|resolved`.

Access denial and overrides default to audit/diagnostic streams rather than general high-volume event workflows.

---

## 16. Builder Widgets Builder (`builder-widgets`) — Pro

Capabilities: `wpe_builder_widgets_read|create|update|delete|publish|import|export`, builder-specific adapter management capability where required.

Abilities: `wpessential/builder-widgets.list|get|create|update|validate|preview|publish|archive|export|import`, `wpessential/builder-widgets.adapter.compatibility`.

Events: `wpessential.builder_widget.created|updated|published`, `wpessential.builder_adapter.compatibility_failed`.

---

## 17. Forms & Workflow Builder (`forms`, `workflows`) — Pro

Capabilities:
`wpe_forms_read|create|update|delete|publish|import|export`, `wpe_forms_entries_read|update|delete|export`,
`wpe_workflows_read|create|update|delete|publish|run|retry|cancel`.

Abilities:
`wpessential/forms.list|get|create|update|validate|preview|publish`,
`wpessential/forms.entry.list|get|submit|update|delete|export`,
`wpessential/workflows.list|get|create|update|validate|preview|publish|run|retry|cancel`,
`wpessential/workflows.run.get|list`.

Events:
`wpessential.form.entry.submitted|validated|rejected|updated|deleted`,
`wpessential.workflow.run.started|completed|failed|cancelled`,
`wpessential.workflow.step.completed|failed|retried`.

---

## 18. Cron Job Builder (`cron`) — Pro

Capabilities: `wpe_cron_read`, `wpe_cron_manage`, `wpe_cron_run`, `wpe_cron_manage_third_party`.

Abilities: `wpessential/cron.events.list|get`, `wpessential/cron.schedule.create|update|pause|resume|delete`, `wpessential/cron.run`, `wpessential/cron.runner.health`.

Events: `wpessential.cron.schedule.created|updated|paused|resumed|deleted`, `wpessential.cron.run.started|completed|failed|overlap_blocked`.

Third-party WP-Cron mutation requires explicit ownership warning/capability.

---

## 19. Notification System (`notifications`) — Pro

Capabilities: `wpe_notifications_read|create|update|delete|publish`, `wpe_notifications_delivery_read`.

Abilities: `wpessential/notifications.rule.list|get|create|update|validate|publish`, `wpessential/notifications.send`, `wpessential/notifications.delivery.list|get`, `wpessential/notifications.preference.get|update`, `wpessential/notifications.mark-read`.

Events: `wpessential.notification.created|read`, `wpessential.notification.delivery.attempted|accepted|delivered|failed` where provider semantics can support each state truthfully.

---

## 20. Emails Builder (`emails`) — Pro

Capabilities: `wpe_emails_read|create|update|delete|publish|import|export`, `wpe_emails_test_send`, `wpe_emails_logs_read`.

Abilities: `wpessential/emails.template.list|get|create|update|validate|preview|publish|export|import`, `wpessential/emails.test-send`, `wpessential/emails.delivery.list|get`.

Events: `wpessential.email.send.requested|accepted|failed`; `delivered` only where an external provider supplies reliable delivery evidence.

---

## 21. Message & Chat System (`chat`) — Pro

Capabilities: `wpe_chat_use`, `wpe_chat_moderate`, `wpe_chat_manage_retention`, `wpe_chat_export`.

Abilities: `wpessential/chat.conversation.list|get|create|leave`, `wpessential/chat.message.list|send|edit|delete|mark-read|report`, `wpessential/chat.participant.add|remove|block`, `wpessential/chat.moderation.resolve`.

Events: `wpessential.chat.conversation.created`, `wpessential.chat.message.sent|edited|deleted|read|reported`, `wpessential.chat.participant.added|removed|blocked`.

Every Ability requires object-level participant/resource policy.

---

## 22. REST API Builder (`rest-api`) — Pro

Capabilities: `wpe_rest_api_read|create|update|delete|publish`, `wpe_rest_api_logs_read`, elevated `wpe_rest_api_security_manage`.

Abilities: `wpessential/rest-api.endpoint.list|get|create|update|validate|preview|publish|disable`, `wpessential/rest-api.endpoint.openapi-preview`, `wpessential/rest-api.logs.list|get`.

Events: `wpessential.rest_endpoint.created|updated|published|disabled`, `wpessential.rest_endpoint.rate_limited|policy_denied` as diagnostic events.

Generated endpoint execution invokes its bound Query/Data Source/Ability contract rather than a generic bypass Ability.

---

## 23. Webhooks & Connections Manager (`connections`) — Pro

Capabilities: `wpe_connections_read|create|update|delete`, `wpe_connections_test`, `wpe_connections_logs_read`, high-risk `wpe_connections_manage_credentials`.

Abilities: `wpessential/connections.list|get|create|update|delete|test`, `wpessential/connections.rotate-secret`, `wpessential/webhooks.inbound.create|disable`, `wpessential/webhooks.delivery.list|get|retry`.

Events: `wpessential.connection.created|updated|credential_rotated|test_failed`, `wpessential.webhook.received|verified|rejected`, `wpessential.webhook.delivery.attempted|completed|failed`.

---

## 24. Backup Manager (`backup`) — Pro

Capabilities: `wpe_backup_read`, `wpe_backup_manage_destinations`, `wpe_backup_run`, `wpe_backup_cancel`, `wpe_backup_verify`, high-risk `wpe_backup_restore`, `wpe_backup_delete`.

Abilities: `wpessential/backup.destination.list|get|create|update|test`, `wpessential/backup.preview|run|cancel|get|list|verify|delete`, `wpessential/backup.restore-preview|restore|restore-status`.

Events: `wpessential.backup.started|completed|verified|failed|cancelled|pruned`, `wpessential.restore.started|completed|failed`.

---

## 25. Reset Manager (`reset`) — Pro

Capabilities: `wpe_reset_read`, high-risk `wpe_reset_execute`, `wpe_reset_override_restore_point`.

Abilities: `wpessential/reset.profile.list|get|create|update`, `wpessential/reset.impact-preview`, `wpessential/reset.execute`, `wpessential/reset.status`.

Events: `wpessential.reset.requested|started|completed|failed`; restore-point linkage included in audit metadata.

---

## 26. Import / Export (`import-export`) — Pro

Capabilities: `wpe_import_export_read`, `wpe_export_configuration`, `wpe_import_configuration`, `wpe_export_data`, `wpe_import_data`, `wpe_import_execute`, `wpe_import_rollback`.

Abilities: `wpessential/export.preview|run|get`, `wpessential/import.inspect|map|dry-run|run|resume|cancel|rollback-preview|rollback`, `wpessential/import.report.get`.

Events: `wpessential.export.started|completed|failed`, `wpessential.import.started|row_failed|completed|failed|cancelled|rolled_back`.

Row-failed events may be aggregated to avoid event storms.

---

## 27. Protector (`protector`) — Pro

Capabilities: `wpe_protector_read|update|publish`, high-risk `wpe_protector_recovery_manage`, `wpe_protector_logs_read`.

Abilities: `wpessential/protector.rules.get|update|validate|preview|publish`, `wpessential/protector.access-explain`, `wpessential/protector.recovery.status|rotate`, `wpessential/protector.logs.list`.

Events: `wpessential.protector.rules.updated|published`, `wpessential.protector.access.denied`, `wpessential.protector.lockout.triggered`, `wpessential.protector.recovery.used`.

High-volume access-denied events require aggregation/sampling/retention controls.

---

## 28. Watermarker / Media Rules (`watermark`) — Pro

Capabilities: `wpe_watermark_read|create|update|delete|publish`, `wpe_watermark_regenerate`.

Abilities: `wpessential/watermark.rule.list|get|create|update|validate|preview|publish`, `wpessential/watermark.regenerate-preview|run|cancel|status`, `wpessential/watermark.derivatives.remove|rebuild`.

Events: `wpessential.watermark.rule.created|updated|published`, `wpessential.watermark.batch.started|completed|failed`.

---

## 29. XML-RPC Manager (`xmlrpc`) — Pro

Capabilities: `wpe_xmlrpc_read`, `wpe_xmlrpc_update`, `wpe_xmlrpc_logs_read`.

Abilities: `wpessential/xmlrpc.status`, `wpessential/xmlrpc.methods.inventory`, `wpessential/xmlrpc.rules.get|update|validate|publish`, `wpessential/xmlrpc.logs.list`.

Events: `wpessential.xmlrpc.rules.updated`, `wpessential.xmlrpc.request.denied` as diagnostic/audit event.

---

## 30. Role & Capability Manager (`roles`) — Pro

Capabilities: `wpe_roles_read|create|update|delete|import|export`, high-risk `wpe_roles_manage_admin_equivalent`, `wpe_roles_recovery`.

Abilities: `wpessential/roles.list|get|create|clone|update|delete|compare|export|import`, `wpessential/roles.capability-impact-preview`, `wpessential/roles.recovery.status|restore`.

Events: `wpessential.role.created|updated|deleted`, `wpessential.role.capabilities.changed`, `wpessential.role.admin_equivalent.changed`, `wpessential.role.recovery.used`.

---

## 31. Platform Surfaces (`platform`) — not sellable module

Capabilities:
`wpe_view_home`, `wpe_manage_modules`, `wpe_view_diagnostics`, `wpe_manage_account_connection`, `wpe_manage_support`, `wpe_support_include_sensitive_diagnostics`.

Abilities:
`wpessential/platform.status`, `wpessential/platform.modules.list|get|enable|disable`, `wpessential/platform.diagnostics.run|export-preview`, `wpessential/platform.account.status|connect|disconnect`, `wpessential/platform.support.ticket.list|get|create|reply|close|reopen`, `wpessential/platform.changelog.list|get`, `wpessential/platform.docs.search`.

Events:
`wpessential.platform.module.enabled|disabled|degraded|recovered`, `wpessential.platform.account.connected|disconnected`, `wpessential.platform.support.ticket.created|replied|closed|reopened`, `wpessential.platform.health.issue_detected|resolved`.

---

# Cross-module trigger allowlist

The Workflow Builder may consume published domain events only after the owning module marks them workflow-safe. Security/audit events such as access denials, login failures or protected-download denials are **not automatically workflow-safe**, because exposing them as unrestricted automation triggers can create loops, privacy leaks or abuse amplification.

# AI exposure defaults

Across all modules:
- `list/get/status/validate/preview/explain` may be considered for opt-in AI allowlists;
- creation/update remains explicit opt-in;
- `delete/restore/reset/force-access/manage-credentials/role-admin-equivalent/unsafe` are AI-disabled by default;
- AI never receives a capability solely because a human who configured the AI had it at configuration time; execution uses the current explicit principal/service policy.

# Remaining before implementation

Each Ability still needs a concrete JSON input/output schema, error codes, async/sync classification, idempotency contract, privacy class and test cases in the owning module's implementation specification. This document does not authorize source work.