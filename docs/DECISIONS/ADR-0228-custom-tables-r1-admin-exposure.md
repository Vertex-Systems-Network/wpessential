# ADR-0228 — Custom Tables R1 Migration Admin Exposure

Status: **ACCEPTED**
Date: **2026-10-19**
Approval: `GOV-OWNER-CONSENT-002` (full autonomy; decision recorded instead of asked)
Builds on: ADR-0227 (internal-only migration abilities); `docs/MODULES/CUSTOM-TABLES-ADMIN-COLUMNS-EXHAUSTIVE-SPEC.md` §17, §25, §26

## Context

ADR-0227 registered the seven R1 migration abilities on the internal channel only. Administrators still have no way to use them. The repository's established admin pattern is:

- An admin page renders a JSON bootstrap with per-route nonces.
- TypeScript calls `admin-ajax.php?action=wpessential_dispatch`.
- `AjaxDispatcher` checks login, nonce (`wpessential_request:<operation>:<route>`) and payload.
- `AbilityAjaxHandler` executes the ability on the `Ui` channel after registry authorization.

Every existing mutating AJAX ability is also exposed to the WordPress Abilities API REST route through `WordPressAbilityBridge::expose()`. That route has no nonce requirement and accepts any authenticated client, including application passwords.

The product spec constrains this surface:

- §26: "Table does not automatically expose public CRUD"; `migration.apply` is "privileged/destructive"; "User-facing REST API Builder chooses endpoint exposure/policy; Custom Tables only supplies Data Source capabilities."
- §25: "Schema migration permission is significantly stronger than ordinary row edit."
- §17: every physical migration receives a schema diff, affected rows estimate, data compatibility scan, expected lock/online capability note, dependency impact, backup/restore-point requirement by risk, and rollback strategy.

Neither existing mutating abilities nor the registry record audit events, and no Custom Tables admin screen exists.

## Decision

1. **No REST or Abilities API exposure.** Migration abilities are never passed to `WordPressAbilityBridge::expose()` and never declare `ExecutionChannel::Rest`, `Cli` or `Ai`. Per §26, REST exposure belongs to the REST API Builder, not to Custom Tables.
2. **Admin-AJAX only.** The abilities add `ExecutionChannel::Ui`. Each gets an `AjaxRoute` of type `custom-tables.migration.<action>`:
   - mutating actions use `NonceOperation::Update`;
   - routes keep the default `requiresNonce: true` and `allowGuests: false`;
   - nonces are minted only by the Custom Tables admin page.
3. **Capability.** All seven stay on `manage_options`, the strongest single-site administrator capability, which satisfies §25 relative to row editing.
   - A dedicated migration capability arrives with the Custom Tables roles/permissions surface; it would need role registration this ADR does not add.
   - R1 additive plans need no re-authentication.
   - R2+ execution must add re-authentication before it is exposed.
4. **Audit.** Every migration ability records an `AuditRecord`. This is done in the handler, because the registry does not audit. Each record carries:
   - owner surface 7;
   - action `custom-tables.migration.<action>`;
   - outcome success, denied or failure;
   - resource type `custom_table_migration_run` and the run id, or the definition id for prepare;
   - metadata holding the resulting state, state revision and reasons.

   Successes, service-level rejections and failures are audited by the handler. The registry refuses an unauthorized caller before any handler runs. On the admin-AJAX path, a wrapper records those refusals as `denied` with the registry's reason and rethrows them unchanged. Registry refusals for internal callers, which are plugin code rather than people, are not audited.

   An audit write failure never changes a migration outcome. Each response reports `audit_recorded` instead.
5. **Preview.** `prepare` returns the §17 evidence available for R1 additive plans:
   - schema diff as the reviewed SQL statements;
   - affected rows estimate: 0 for a new table, the current row count for an existing one;
   - expected lock/online note from the provider capability profile;
   - backup requirement: a verified, plan-bound database backup at most 24 hours old (ADR-0226);
   - rollback strategy: drop the created table or added column/index while the run is `failed_recoverable`.

   Data compatibility scan and dependency impact are reported as *not applicable to additive changes* rather than omitted.
6. **Milestones.**
   - 0228-A: audit recording, `Ui` channel, AJAX routes and the §17 preview payload.
   - 0228-B: the Custom Tables admin page and TypeScript client, which render nonces, the preview and the lifecycle actions.

   Routes are inert until 0228-B, because no page mints their nonces before then.

## Consequences

- Administrators get a nonce-protected, audited, browser-session-only path. No application-password or REST client can run schema migrations.
- Every lifecycle step leaves an audit trail.
- A dedicated migration capability and R2+ re-authentication are explicit follow-ups.
