# Reset — Native WordPress Audit V1

Surface: **25 / Reset**
Issue: **#1452**
Planning parent: **#591 / merged PR #604**
Seed: `config/product/options-bank/reset.json` — **148 records**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE; WORDPRESS CORE PROVIDES POST, ATTACHMENT, COMMENT, TERM, USER, OPTION, TRANSIENT, REWRITE, PLUGIN/THEME ACTIVATION, MULTISITE, NONCE/CAPABILITY, MAINTENANCE-MODE AND SITE HEALTH PRIMITIVES BUT NO RESET PROFILE, SCOPE PREVIEW, DRY-RUN, RESTORE-POINT GATE, CONFIRMATION LEVEL, EXECUTION JOURNAL OR RECOVERY STATE.** Native delete APIs are deletion substrate only and never substitute for Reset impact, recovery or ownership semantics. No deletion, option reset, user change, table change, restore point or restore occurs here.

## Current native sources

- wp_delete_post() — https://developer.wordpress.org/reference/functions/wp_delete_post/
- wp_trash_post() — https://developer.wordpress.org/reference/functions/wp_trash_post/
- wp_delete_attachment() — https://developer.wordpress.org/reference/functions/wp_delete_attachment/
- wp_delete_comment() — https://developer.wordpress.org/reference/functions/wp_delete_comment/
- wp_delete_term() — https://developer.wordpress.org/reference/functions/wp_delete_term/
- wp_set_object_terms() — https://developer.wordpress.org/reference/functions/wp_set_object_terms/
- wp_delete_user() — https://developer.wordpress.org/reference/functions/wp_delete_user/
- wpmu_delete_user() — https://developer.wordpress.org/reference/functions/wpmu_delete_user/
- is_super_admin() — https://developer.wordpress.org/reference/functions/is_super_admin/
- register_setting() — https://developer.wordpress.org/reference/functions/register_setting/
- delete_option() — https://developer.wordpress.org/reference/functions/delete_option/
- populate_options() — https://developer.wordpress.org/reference/functions/populate_options/
- delete_transient() — https://developer.wordpress.org/reference/functions/delete_transient/
- wp_cache_flush() — https://developer.wordpress.org/reference/functions/wp_cache_flush/
- flush_rewrite_rules() — https://developer.wordpress.org/reference/functions/flush_rewrite_rules/
- deactivate_plugins() — https://developer.wordpress.org/reference/functions/deactivate_plugins/
- activate_plugin() — https://developer.wordpress.org/reference/functions/activate_plugin/
- switch_theme() — https://developer.wordpress.org/reference/functions/switch_theme/
- wpdb — https://developer.wordpress.org/reference/classes/wpdb/
- is_multisite() — https://developer.wordpress.org/reference/functions/is_multisite/
- get_sites() — https://developer.wordpress.org/reference/functions/get_sites/
- wp_delete_site() — https://developer.wordpress.org/reference/functions/wp_delete_site/
- current_user_can() — https://developer.wordpress.org/reference/functions/current_user_can/
- wp_verify_nonce() — https://developer.wordpress.org/reference/functions/wp_verify_nonce/
- wp_schedule_event() — https://developer.wordpress.org/reference/functions/wp_schedule_event/
- wp_maintenance() — https://developer.wordpress.org/reference/functions/wp_maintenance/
- WP_Site_Health — https://developer.wordpress.org/reference/classes/wp_site_health/
- Privacy (Plugin Handbook) — https://developer.wordpress.org/plugins/privacy/
- WP-CLI wp db reset — https://developer.wordpress.org/cli/commands/db/reset/
- WP-CLI wp site empty — https://developer.wordpress.org/cli/commands/site/empty/
- In-repo: `docs/MODULES/RESET-MANAGER-EXHAUSTIVE-SPEC.md`, `docs/PRODUCT/RESET-BANK-ENTRY-READINESS-V1.md`, `docs/MODULES/BACKUP-MANAGER-EXHAUSTIVE-SPEC.md` (restore-point provider), `config/product/options-bank/backup.json` (Surface 24 BANK_REVIEWED)

Native classification of the readiness checklist: post/comment/term/user trash/delete APIs and hooks (`wp_trash_post()`, `wp_delete_post()`, `wp_delete_comment()`, `wp_delete_term()`, `wp_delete_user()` fire native hooks and cascade meta/relationships; selection, preview and preservation are WPE), options/transients/rewrite/cache (`register_setting()` registry, `delete_option()`, `delete_transient()`, `wp_cache_flush()`, `flush_rewrite_rules()`; no native notion of a resettable option group, so wildcard option deletion is rejected and defaults come only from deterministic `populate_options()` keys), multisite boundaries (`is_multisite()`, `get_sites()`, `wpmu_delete_user()`, `wp_delete_site()`; network-wide reset is deferred and Super Admins are protected via `is_super_admin()`), attachment/file ownership (`wp_delete_attachment()` removes originals and generated sizes; uploads are kept by default), plugin/theme activation without package removal (`deactivate_plugins()`, `activate_plugin()`, `switch_theme()`; package deletion is rejected), current admin/Super Admin protection (no native last-admin guard; WPE mandatory preservation), DB table ownership/prefix (`wpdb` prefix and site/network tables are substrate; only registered owners may truncate/drop), privacy export/erase (WordPress privacy tooling is the reference; reset is not an erasure workflow), nonce/session/re-auth primitives (`wp_verify_nonce()` and `current_user_can()` are necessary but not sufficient; recent re-auth and typed phrase are WPE). WP-CLI `db reset` and `site empty` are developer precedents of unscoped wipes, not product options.

## Seed-record disposition

| Seed record | Native disposition | Notes |
|---|---|---|
| `reset.profile.identity` | **WPE PRODUCT/POLICY** | Name required, generated stable key, description, Draft/Active status, normalized tags and internal note; the key never encodes scope and the note never reaches logs/events unless sanitized. |
| `reset.profile.status` | **WPE PRODUCT/POLICY** | Only Active profiles can be previewed for execution; Archived profiles keep history linkage; deleting a definition never deletes run history or restore-point references. |
| `reset.profile.list-columns` | **WPE PRODUCT/POLICY** | Name, key, status, scope summary, preservation summary, backup requirement, last executed, last result, created/updated and actions; summaries are computed, never free text. |
| `reset.profile.list-filters` | **WPE PRODUCT/POLICY** | Status, scope type, backup required, last result and date filters; search by name/key/tags only. |
| `reset.profile.row-actions` | **WPE PRODUCT/POLICY** | Run always routes through impact preview, restore-point policy and confirmation; Duplicate copies definition only (never restore-point bindings or run state); delete of a definition is archive-first. |
| `reset.profile.no-bulk-run` | **WPE PRODUCT/POLICY** | No bulk Run action exists; each execution is a single previewed, confirmed and audited run. |
| `reset.preset.catalog` | **WPE PRODUCT/POLICY** | Preset only populates explicit scope options and never hides what will be deleted; WPE configuration only is the default suggestion; Near-factory is Level 3 and never the default. |
| `reset.preset.explicit-expansion` | **WPE PRODUCT/POLICY** | Changing a preset rewrites visible explicit selections; the saved profile stores the expanded scope, not only the preset key, so preset evolution never silently changes a saved profile. |
| `reset.preset.near-factory` | **WPE PRODUCT/POLICY** | Composed only of registered owner scopes (content, taxonomy, comments, registered settings, WPE data) with mandatory preservation; never a raw DB drop/reinstall; Level 3 confirmation and verified pre-reset restore point mandatory. |
| `reset.preset.dev-staging` | **WPE PRODUCT/POLICY** | Allowed only where environment policy marks the site non-production when such identity exists; environment identity/cloning stays Surface 55 Staging. |
| `reset.wpe.modules-scope` | **WPE PRODUCT/POLICY** | Explicit module selection; each module contributes only scopes its owner registers. |
| `reset.wpe.definitions-only` | **WPE PRODUCT/POLICY** | Resets saved definitions/config while leaving runtime data untouched by default. |
| `reset.wpe.runtime-data` | **WPE PRODUCT/POLICY** | Runtime delete requires the module-specific delete contract; no generic table wipe; modules without a contract are shown as not resettable. |
| `reset.wpe.preserve-audit` | **WPE PRODUCT/POLICY** | Default on; audit history of the reset itself is always written regardless of selection. |
| `reset.wpe.preserve-account` | **WPE PRODUCT/POLICY** | Default on; account/Vault connections are never silently reset by presets. |
| `reset.wpe.preserve-support-cache` | **WPE PRODUCT/POLICY** | Default on. |
| `reset.wpe.preserve-vault` | **WPE PRODUCT/POLICY** | Default on unless explicitly selected; selecting Vault reset is Level 3 with an explicit per-reference list; secret values never appear in preview, events or logs. |
| `reset.wpe.module-activation` | **WPE PRODUCT/POLICY** | Preserve default; reset re-applies registry defaults through the module lifecycle, never by deleting module files. |
| `reset.wpe.onboarding-state` | **WPE PRODUCT/POLICY** | Level 1 when alone; re-shows onboarding without touching data. |
| `reset.wpe.license-cache` | **WPE PRODUCT/POLICY** | Clears local cache only; the account entitlement source of truth is never altered. |
| `reset.wpe.diagnostics-cache` | **WPE PRODUCT/POLICY** | Level 1 cache/local UI-state reset. |
| `reset.wpe.generated-assets` | **WPE PRODUCT/POLICY** | Regenerable outputs only; rebuild is a post-reset action. |
| `reset.content.post-types` | **NATIVE SUBSTRATE + WPE POLICY** | Registered post types/statuses (get_post_types/get_post_stati substrate) select scope; WP-CLI `site empty` is a precedent for whole-site emptying, which WPE never offers unscoped. |
| `reset.content.filters` | **WPE PRODUCT/POLICY** | Typed filters combined with AND semantics; preview shows matched counts and samples. |
| `reset.content.query-definition` | **WPE PRODUCT/POLICY** | Selection via a saved Query Builder definition owned by Surface 4; the definition is snapshotted into the preview fingerprint. |
| `reset.content.relation-selection` | **DEFERRED / CROSS-OWNER** | Only when relation owners support it; deferred until Relations delete contracts exist. |
| `reset.content.trash-vs-delete` | **NATIVE SUBSTRATE + WPE POLICY** | wp_trash_post()/wp_delete_post() substrate with native hooks; trash default where WP entity semantics support it; permanent delete is Level 2+. |
| `reset.content.revisions-autosaves` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Native revision/autosave records follow parent selection; standalone revision cleanup belongs to DB Maintenance (Surface 48). |
| `reset.content.linked-comments` | **NATIVE SUBSTRATE + WPE POLICY** | Native post deletion cascades comments; preview counts them explicitly. |
| `reset.content.post-meta` | **NATIVE SUBSTRATE + WPE POLICY** | Removed through native delete APIs with the post; no raw postmeta SQL. |
| `reset.content.term-relationships` | **NATIVE SUBSTRATE + WPE POLICY** | Native wp_delete_post() clears relationships; terms themselves stay unless Taxonomy scope selects them. |
| `reset.content.media-mode` | **WPE PRODUCT/POLICY** | Keep attachments is default; delete exclusively-attached media is advanced; delete regardless of usage only with dependency impact preview. |
| `reset.content.media-files` | **NATIVE SUBSTRATE + WPE POLICY** | wp_delete_attachment() media ownership rules govern originals and generated sizes; files outside the uploads ownership model are never touched. |
| `reset.content.children` | **WPE PRODUCT/POLICY** | Explicit choice per hierarchy; default keeps children and reparents per native behaviour. |
| `reset.content.orphan-relations` | **WPE PRODUCT/POLICY** | Only through owning modules' cleanup contracts. |
| `reset.taxonomy.selection` | **NATIVE SUBSTRATE + WPE POLICY** | Registered taxonomies/terms via wp_delete_term() substrate. |
| `reset.taxonomy.mode` | **WPE PRODUCT/POLICY** | Relationships-only uses wp_set_object_terms() semantics; term delete is explicit. |
| `reset.taxonomy.term-meta` | **NATIVE SUBSTRATE + WPE POLICY** | Deleted with the term by native API. |
| `reset.taxonomy.default-terms` | **NATIVE SUBSTRATE + WPE POLICY** | Default category cannot be deleted natively; posts reassigned to fallback term where core requires. |
| `reset.taxonomy.hierarchy` | **WPE PRODUCT/POLICY** | Explicit choice; native default reparents children. |
| `reset.taxonomy.relation-impact` | **WPE PRODUCT/POLICY** | Preview lists WPE relations, listings and queries depending on selected terms. |
| `reset.comments.selection` | **NATIVE SUBSTRATE + WPE POLICY** | wp_delete_comment() substrate; selection never via broad SQL. |
| `reset.comments.delete-mode` | **NATIVE SUBSTRATE + WPE POLICY** | Native comment trash/spam/delete semantics; permanent delete Level 2+. |
| `reset.comments.meta` | **NATIVE SUBSTRATE + WPE POLICY** | Removed with the comment by native API. |
| `reset.settings.groups` | **WPE PRODUCT/POLICY** | Normal mode exposes only registered/known groups (register_setting substrate, WPE module settings, owner adapters); arbitrary option names are never listed for deletion. |
| `reset.settings.transients` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | delete_transient()/wp_cache_flush() substrate; safe and regenerable. |
| `reset.settings.rewrite-repair` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | flush_rewrite_rules() once as a repair action only. |
| `reset.settings.integrations` | **WPE PRODUCT/POLICY** | Only owners exposing a reset adapter appear. |
| `reset.settings.preserve-identity` | **WPE PRODUCT/POLICY** | Default on: siteurl/home, blogname and URLs preserved. |
| `reset.settings.preserve-admin-defaults` | **WPE PRODUCT/POLICY** | Default on unless a full baseline intentionally changes them, shown in preview. |
| `reset.settings.wp-defaults` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | populate_options()-equivalent defaults only for deterministic keys; non-deterministic options listed as not resettable. |
| `reset.users.excluded-default` | **WPE PRODUCT/POLICY** | No standard preset deletes users; market tools that keep only the current user are a different (rejected) model. |
| `reset.users.advanced-reset` | **DEFERRED / CROSS-OWNER** | Spec: 'if accepted later'; deferred until owner acceptance with Surface 30 Roles/Policy. |
| `reset.users.preserve-operator` | **WPE PRODUCT/POLICY** | Mandatory; cannot be disabled. |
| `reset.users.recovery-admin` | **WPE PRODUCT/POLICY** | Mandatory; failure state blocks the run. |
| `reset.users.super-admin` | **NATIVE SUBSTRATE + WPE POLICY** | is_super_admin() substrate; Super Admins never in subsite scope. |
| `reset.users.reassign-content` | **NATIVE SUBSTRATE + WPE POLICY** | wp_delete_user() reassign parameter substrate; wpmu_delete_user() for network. |
| `reset.users.impact` | **DEFERRED / CROSS-OWNER** | Part of the deferred advanced user reset. |
| `reset.plugins.preserve-packages` | **WPE PRODUCT/POLICY** | Default on and not disableable in ordinary profiles; package removal is not reset. |
| `reset.plugins.preserve-theme` | **WPE PRODUCT/POLICY** | Default on; switch_theme() only if explicitly configured. |
| `reset.plugins.preserve-active` | **WPE PRODUCT/POLICY** | Default on. |
| `reset.plugins.deactivate-reactivate` | **NATIVE SUBSTRATE + WPE POLICY** | deactivate_plugins()/activate_plugin() substrate; reactivation failures reported, never fatal. |
| `reset.plugins.cache-adapters` | **WPE PRODUCT/POLICY** | Certified adapters only. |
| `reset.tables.module-runtime` | **WPE PRODUCT/POLICY** | Only through registered owners and their delete contracts. |
| `reset.tables.ctb` | **WPE PRODUCT/POLICY** | Explicit selection; schema change requires wpe_reset_schema. |
| `reset.tables.typed-rows` | **WPE PRODUCT/POLICY** | Typed filters only, no free-form WHERE. |
| `reset.tables.truncate-drop` | **WPE PRODUCT/POLICY** | Only if the owning contract explicitly allows and impact is known. |
| `reset.preserve.mandatory` | **WPE PRODUCT/POLICY** | Current admin/recovery admin, salts/secrets file, backup catalog/restore point and the reset audit cannot be disabled in normal mode. |
| `reset.preserve.site-urls` | **WPE PRODUCT/POLICY** | Default on. |
| `reset.preserve.salts-file` | **WPE PRODUCT/POLICY** | Never modified by reset. |
| `reset.preserve.backup-catalog` | **WPE PRODUCT/POLICY** | Mandatory; Surface 24 owns restore-point truth. |
| `reset.preserve.selected-objects` | **WPE PRODUCT/POLICY** | Explicit preservation lists. |
| `reset.preserve.uploads-paths` | **WPE PRODUCT/POLICY** | Default on; uploads are only touched through media ownership rules. |
| `reset.preserve.precedence` | **WPE PRODUCT/POLICY** | Explicit preservation beats broad preset; mandatory safety preservation cannot be disabled without separately accepted unsafe mode. |
| `reset.restore-point.mode` | **WPE PRODUCT/POLICY** | Required is default; verified pre-reset restore point produced by Surface 24 Backup (trigger Pre-Reset); never silently downgraded. |
| `reset.restore-point.min-tier` | **WPE PRODUCT/POLICY** | V1/V2 according to environment policy; tier semantics owned by Surface 24. |
| `reset.restore-point.max-age` | **WPE PRODUCT/POLICY** | Reuse an existing verified point only within configured age. |
| `reset.restore-point.destination` | **WPE PRODUCT/POLICY** | Destination reference resolved by Surface 24. |
| `reset.restore-point.protection` | **WPE PRODUCT/POLICY** | Protected from retention until reset is closed; remains protected after a failed reset. |
| `reset.restore-point.remove-protection` | **WPE PRODUCT/POLICY** | Toggle; only after post-reset verification passes. |
| `reset.restore-point.engine-unhealthy` | **WPE PRODUCT/POLICY** | Reset blocked, not silently downgraded. |
| `reset.restore-point.unsafe-override` | **WPE PRODUCT/POLICY** | Only when dedicated capability wpe_reset_unsafe_override and policy permit; requires reason, typed phrase and recent re-auth; recorded in audit; never available to AI or presets. |
| `reset.restore-point.proof-tier` | **WPE-EXCEED (no native equivalent)** | Run records restore-point id, tier and verification timestamp, re-validated immediately before the destructive phase. |
| `reset.preview.counts` | **WPE PRODUCT/POLICY** | Posts/revisions/comments, terms, media/files, users, options, WPE definitions/runtime rows, tables, jobs, entitlements, plugin/theme changes, retained items and restore-point state. |
| `reset.preview.dry-run` | **WPE PRODUCT/POLICY** | Preview is a read-only dry run with no side effects; required before every run. |
| `reset.preview.fingerprint` | **WPE-EXCEED (no native equivalent)** | Records relevant source counts/versions. |
| `reset.preview.drift` | **WPE-EXCEED (no native equivalent)** | Safety-critical scope re-evaluated before execution; critical drift requires new preview or blocks; non-critical within declared tolerance. |
| `reset.preview.dependencies` | **WPE PRODUCT/POLICY** | Active jobs, workflows, memberships and relations affected. |
| `reset.confirm.levels` | **WPE PRODUCT/POLICY** | Level derived from scope, never chosen lower by the user. |
| `reset.confirm.level2` | **WPE PRODUCT/POLICY** | Explicit summary, checkbox acknowledgement and confirmation button with verified recovery point. |
| `reset.confirm.reauth` | **WPE PRODUCT/POLICY** | Level 3; nonce (wp_verify_nonce) alone is not re-auth. |
| `reset.confirm.typed-phrase` | **WPE PRODUCT/POLICY** | Phrase includes site/profile identifiers, not a generic word. |
| `reset.confirm.reason` | **WPE PRODUCT/POLICY** | Required at Level 3. |
| `reset.confirm.countdown` | **WPE PRODUCT/POLICY** | Optional; never relied on as security. |
| `reset.exec.phases` | **WPE-EXCEED (no native equivalent)** | queued/preflight/validating restore point/locking/maintenance/content/taxonomy+comments/module/settings/users/plugins-themes/cache-rewrite/post-verify/completed/completed_with_warnings/failed_recoverable/failed_recovery_required; idempotency/recovery-aware. |
| `reset.exec.job-service` | **NATIVE SUBSTRATE + WPE POLICY** | Job Service (Surface 18) over WP-Cron substrate; no long in-request destructive loop. |
| `reset.exec.maintenance` | **NATIVE SUBSTRATE + WPE POLICY** | wp_maintenance() substrate entered only when required. |
| `reset.locks.concurrency` | **WPE PRODUCT/POLICY** | Backup creation is a prerequisite phase, not concurrent. |
| `reset.locks.stale-recovery` | **WPE PRODUCT/POLICY** | Explicit diagnostics and safe ownership/expiry rules. |
| `reset.failure.states` | **WPE PRODUCT/POLICY** | No verified restore point, stale point, disk, DB permissions, last recovery admin, owner unavailable, protected data, lock conflict, midway failure, boot/health failure, reactivation failure, multisite unsupported, backup unavailable. |
| `reset.failure.no-rollback-claim` | **WPE PRODUCT/POLICY** | Failure never implies rollback completed unless restore verification proves it. |
| `reset.recovery.screen` | **WPE PRODUCT/POLICY** | Failure summary, safe diagnostic ID, restore-point health, boot health, recommended actions and support bundle. |
| `reset.recovery.support-bundle` | **WPE PRODUCT/POLICY** | Redacted; no deleted payloads or secrets. |
| `reset.post.safe-actions` | **WPE PRODUCT/POLICY** | Flush rewrites once, clear WPE caches, rebuild definition caches, rebuild entitlements, regenerate derived media if required, reactivate validated modules/plugins. |
| `reset.post.redirect` | **WPE PRODUCT/POLICY** | Destination determined by result. |
| `reset.post.notify-report` | **WPE PRODUCT/POLICY** | Delivery via Surface 19 Notifications; safe summary only. |
| `reset.post.health-checks` | **NATIVE SUBSTRATE + WPE POLICY** | WP_Site_Health substrate plus WPE boot checks. |
| `reset.history.columns` | **WPE PRODUCT/POLICY** | Run ID, profile/manual, operator, requested vs actual scope, restore-point ref, times, phase, result, recovery state. |
| `reset.snapshot.visual` | **DEFERRED / CROSS-OWNER** | Optional planning feature only; auxiliary evidence, not recovery artifact. |
| `reset.snapshot.video` | **DEFERRED / CROSS-OWNER** | Not promised until a separate client/browser architecture exists. |
| `reset.perm.caps` | **WPE PRODUCT/POLICY** | read/profile CRUD/preview/run/history/recover/execute/unsafe_override/users/schema separated (current_user_can substrate). |
| `reset.perm.high-risk-separation` | **WPE PRODUCT/POLICY** | unsafe_override, users and schema are never in ordinary presets. |
| `reset.abilities.read` | **WPE PRODUCT/POLICY** | AI default exposure read/preview/explain only. |
| `reset.abilities.run-disabled` | **WPE PRODUCT/POLICY** | Mutating abilities off by default. |
| `reset.events.typed` | **WPE PRODUCT/POLICY** | previewed/requested/started/phase_changed/completed/failed/recovery_required/recovered; counts/IDs only. |
| `reset.audit.safe-summary` | **WPE PRODUCT/POLICY** | Audit contains safe summary only; never deleted private payloads. |
| `reset.multisite.subsite` | **NATIVE SUBSTRATE + WPE POLICY** | is_multisite()/get_sites() substrate; never touches network users/options; network-active packages preserved. |
| `reset.multisite.network-wide` | **DEFERRED / CROSS-OWNER** | Requires separate explicit support/certification; off by default. |
| `reset.multisite.unsupported-block` | **WPE PRODUCT/POLICY** | No best-effort destructive action. |
| `reset.privacy.personal-data` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | WordPress privacy tooling is the native reference; reset is not a privacy erasure workflow. |
| `reset.portability.profile-export` | **WPE PRODUCT/POLICY** | Definitions only without restore-point bindings; package movement owned by Surface 26. |
| `reset.a11y.flow` | **WPE PRODUCT/POLICY** | Keyboard wizard, focus-managed confirmation, non-colour progress, no fake completion. |
| `reset.perf.chunking` | **WPE PRODUCT/POLICY** | Bounded batches resumable through Job Service. |
| `reset.safety.one-click-reset` | **REJECTED-UNSAFE CONFIRMED** | Rejected: no normal one-click reset; every run is previewed, confirmed and audited. |
| `reset.safety.full-db-wipe` | **REJECTED-UNSAFE CONFIRMED** | Rejected: raw DROP DATABASE/all-table drop and reinstall are prohibited standard behavior. |
| `reset.safety.no-backup` | **REJECTED-UNSAFE CONFIRMED** | Rejected: required verified restore point by default; only recorded unsafe override. |
| `reset.safety.silent-downgrade` | **REJECTED-UNSAFE CONFIRMED** | Rejected: unhealthy backup engine blocks. |
| `reset.safety.delete-users-default` | **REJECTED-UNSAFE CONFIRMED** | Rejected: users excluded from standard presets. |
| `reset.safety.last-admin` | **REJECTED-UNSAFE CONFIRMED** | Rejected: mandatory preservation. |
| `reset.safety.delete-uploads-default` | **REJECTED-UNSAFE CONFIRMED** | Rejected: keep attachments default. |
| `reset.safety.delete-packages` | **REJECTED-UNSAFE CONFIRMED** | Rejected: not package-manager shortcut. |
| `reset.safety.option-wildcards` | **REJECTED-UNSAFE CONFIRMED** | Rejected in normal UI. |
| `reset.safety.table-wildcards` | **REJECTED-UNSAFE CONFIRMED** | Rejected: registered owners only. |
| `reset.safety.generic-module-wipe` | **REJECTED-UNSAFE CONFIRMED** | Rejected: module delete contracts only. |
| `reset.safety.outside-wpe-scope` | **REJECTED-UNSAFE CONFIRMED** | Rejected: defaults stay within WPE-owned scope; core content/settings require explicit selection. |
| `reset.safety.core-files` | **REJECTED-UNSAFE CONFIRMED** | Rejected: wp-content clearing and core file deletion are not reset behaviour. |
| `reset.safety.vault-silent-reset` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `reset.safety.arbitrary-post-actions` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `reset.safety.ai-triggered` | **REJECTED-UNSAFE CONFIRMED** | Rejected: AI read/preview/explain only. |
| `reset.safety.concurrent` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `reset.safety.false-rollback` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `reset.safety.network-default` | **REJECTED-UNSAFE CONFIRMED** | Rejected: network scope needs separate certification. |
| `reset.safety.bulk-run` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `reset.safety.stale-preview` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `reset.safety.broad-sql-comments` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `reset.safety.payload-events` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `reset.safety.direct-request` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |
| `reset.safety.countdown-security` | **REJECTED-UNSAFE CONFIRMED** | Rejected. |

## Native completeness / unresolved items

No additional Reset V1 Bank family is required. Native unresolved items: **0**. Reset profiles, scope presets, dry-run impact preview and drift fingerprint, the verified pre-reset restore-point gate (owned by Surface 24 Backup), confirmation levels with re-auth and typed phrase, execution phases, destructive locks and recovery state have no native WordPress equivalent and remain WPE/market-owned.

## Gate boundary

Not promoted here: content/user/option/table deletion, restore-point creation, reset execution, runtime, Atomic Option Contracts, UX contract closure or product parity.
