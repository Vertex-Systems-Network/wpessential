# Reset — Market Audit V1

Status: `MARKET_AUDITED` (integrated in `config/product/options-bank-audits/reset-market-ecosystem.json`)

Surface: 25 — Reset
Snapshot: 2026-10-19
Issue: #1452

## 1. Scope and boundary

Reconciles all 148 Reset records. Surface 25 owns reset profiles, scope presets, preservation rules, dry-run impact preview, confirmation levels, execution phases, locks and recovery semantics. Backup (S24) owns restore-point truth and verification tiers; DB Maintenance (S48) physical cleanup; Cron/Job Service (S18) execution; Notifications (S19) delivery; Roles/Policy (S30) user/capability truth; Import/Export (S26) packages; Staging (S55) environments; each module owner its own delete contract.

## 2. Evidence

- **E1 WP Reset** — https://wordpress.org/plugins/wp-reset/, https://wpreset.com/ — full database reset to defaults keeping the current user, typed confirmation, partial tools (transients, uploads, plugins/themes, custom tables), reactivation options and in-plugin DB snapshots.
- **E2 Advanced WP Reset** — https://wordpress.org/plugins/advanced-wp-reset/ — database reset plus partial resets of comments, posts, themes, plugins, transients and uploads.
- **E3 WP Database Reset** — https://wordpress.org/plugins/wordpress-database-reset/ — select tables to reset, keep current user and reactivate current theme/plugins.
- **E4 WP-CLI db reset / site empty** — https://developer.wordpress.org/cli/commands/db/reset/, https://developer.wordpress.org/cli/commands/site/empty/ — drop/recreate all tables, or empty posts/comments/terms (optionally uploads) with a `--yes` confirmation bypass.
- **S1 Bulk Delete** — https://wordpress.org/plugins/bulk-delete/ — filtered deletion of posts/pages/users/meta by type, status, term, date and author.
- **S2 WP-Optimize** — https://wordpress.org/plugins/wp-optimize/ — revision/transient/spam cleanup (DB Maintenance class).
- **S3 WP STAGING** — https://wordpress.org/plugins/wp-staging/ — staging clone/reset environment workflows (Surface 55 boundary).
- **S4 NIST SP 800-34 Rev. 1 Contingency Planning** — https://csrc.nist.gov/pubs/sp/800/34/r1/upd1/final — recovery points, validated backups before change and recovery verification.
- **S5 OWASP Transaction Authorization Cheat Sheet** — https://cheatsheetseries.owasp.org/cheatsheets/Transaction_Authorization_Cheat_Sheet.html — re-authentication and what-you-see-is-what-you-sign confirmation for high-risk operations.

## 3. Record reconciliation

| Record | Disposition |
|---|---|
| `reset.profile.identity` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.profile.status` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.profile.list-columns` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.profile.list-filters` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.profile.row-actions` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.profile.no-bulk-run` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.preset.catalog` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.preset.explicit-expansion` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.preset.near-factory` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.preset.dev-staging` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.wpe.modules-scope` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.wpe.definitions-only` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.wpe.runtime-data` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.wpe.preserve-audit` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.wpe.preserve-account` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.wpe.preserve-support-cache` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.wpe.preserve-vault` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.wpe.module-activation` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.wpe.onboarding-state` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.wpe.license-cache` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.wpe.diagnostics-cache` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.wpe.generated-assets` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.content.post-types` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.content.filters` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.content.query-definition` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.content.relation-selection` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `reset.content.trash-vs-delete` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.content.revisions-autosaves` | MARKET_EVIDENCED_WITH_BOUNDS |
| `reset.content.linked-comments` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.content.post-meta` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.content.term-relationships` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.content.media-mode` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.content.media-files` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.content.children` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.content.orphan-relations` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.taxonomy.selection` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.taxonomy.mode` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.taxonomy.term-meta` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.taxonomy.default-terms` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.taxonomy.hierarchy` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.taxonomy.relation-impact` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.comments.selection` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.comments.delete-mode` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.comments.meta` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.settings.groups` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.settings.transients` | MARKET_EVIDENCED_WITH_BOUNDS |
| `reset.settings.rewrite-repair` | MARKET_EVIDENCED_WITH_BOUNDS |
| `reset.settings.integrations` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.settings.preserve-identity` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.settings.preserve-admin-defaults` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.settings.wp-defaults` | MARKET_EVIDENCED_WITH_BOUNDS |
| `reset.users.excluded-default` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.users.advanced-reset` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `reset.users.preserve-operator` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.users.recovery-admin` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.users.super-admin` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.users.reassign-content` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.users.impact` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `reset.plugins.preserve-packages` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.plugins.preserve-theme` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.plugins.preserve-active` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.plugins.deactivate-reactivate` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.plugins.cache-adapters` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.tables.module-runtime` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.tables.ctb` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.tables.typed-rows` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.tables.truncate-drop` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.preserve.mandatory` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.preserve.site-urls` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.preserve.salts-file` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.preserve.backup-catalog` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.preserve.selected-objects` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.preserve.uploads-paths` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.preserve.precedence` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.restore-point.mode` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.restore-point.min-tier` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.restore-point.max-age` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.restore-point.destination` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.restore-point.protection` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.restore-point.remove-protection` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.restore-point.engine-unhealthy` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.restore-point.unsafe-override` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.restore-point.proof-tier` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress reset plugins |
| `reset.preview.counts` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.preview.dry-run` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.preview.fingerprint` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress reset plugins |
| `reset.preview.drift` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress reset plugins |
| `reset.preview.dependencies` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.confirm.levels` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.confirm.level2` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.confirm.reauth` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.confirm.typed-phrase` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.confirm.reason` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.confirm.countdown` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.exec.phases` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress reset plugins |
| `reset.exec.job-service` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.exec.maintenance` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.locks.concurrency` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.locks.stale-recovery` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.failure.states` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.failure.no-rollback-claim` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.recovery.screen` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.recovery.support-bundle` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.post.safe-actions` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.post.redirect` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.post.notify-report` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.post.health-checks` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.history.columns` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.snapshot.visual` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `reset.snapshot.video` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `reset.perm.caps` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.perm.high-risk-separation` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.abilities.read` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.abilities.run-disabled` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.events.typed` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.audit.safe-summary` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.multisite.subsite` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `reset.multisite.network-wide` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `reset.multisite.unsupported-block` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.privacy.personal-data` | MARKET_EVIDENCED_WITH_BOUNDS |
| `reset.portability.profile-export` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.a11y.flow` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.perf.chunking` | MARKET_EVIDENCED / WPE_HARDENED |
| `reset.safety.one-click-reset` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.full-db-wipe` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.no-backup` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.silent-downgrade` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.delete-users-default` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.last-admin` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.delete-uploads-default` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.delete-packages` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.option-wildcards` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.table-wildcards` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.generic-module-wipe` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.outside-wpe-scope` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.core-files` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.vault-silent-reset` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.arbitrary-post-actions` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.ai-triggered` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.concurrent` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.false-rollback` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.network-default` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.bulk-run` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.stale-preview` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.broad-sql-comments` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.payload-events` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.direct-request` | KEEP `REJECTED_UNSAFE` |
| `reset.safety.countdown-security` | KEEP `REJECTED_UNSAFE` |

Coverage: **148 / 148**; 138 Bank references; 6 extra dispositions; unresolved **0**; record delta after market audit **0**.

## 4. Rejected market patterns

- One-click whole-database reset keeping only the current user (E1, E2, E3) → `reset.safety.full-db-wipe`, `reset.safety.one-click-reset`, `reset.safety.delete-users-default`.
- Drop and recreate all tables with a `--yes` bypass (E4) → `reset.safety.full-db-wipe`, `reset.safety.table-wildcards`.
- Deleting uploads, plugin/theme packages or wp-content files as reset tools (E1, E2) → `reset.safety.delete-uploads-default`, `reset.safety.delete-packages`, `reset.safety.core-files`.
- Resetting without a verified pre-reset restore point (E3) → `reset.safety.no-backup`, `reset.restore-point.mode`.
- In-plugin DB snapshots (E1) are restore points owned by Surface 24 Backup, not a Reset option.
- Staging clone/push (S3) is out of surface and owned by Surface 55 Staging.
- AI-triggered or network-wide-by-default reset is not offered by any audited product and stays rejected (`reset.safety.ai-triggered`, `reset.safety.network-default`).

No reset execution, deletion, restore-point creation, runtime or product parity is authorized.
