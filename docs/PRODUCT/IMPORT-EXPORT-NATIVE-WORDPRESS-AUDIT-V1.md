# Import/Export — Native WordPress Audit V1

Surface: **26 / Import/Export**
Issue: **#1454**
Planning parent: **#592 / merged PR #605**
Seed: `config/product/options-bank/import-export.json` — **188 records**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE; WORDPRESS CORE PROVIDES POST, META, TERM, USER, UPLOAD, ARCHIVE, MEDIA SIDELOAD, SAFE HTTP, SLUG/UUID, JSON, KSES, WXR EXPORT, PRIVACY EXPORTER, NONCE/CAPABILITY, CRON AND MULTISITE PRIMITIVES BUT NO PORTABLE CONFIGURATION PACKAGE, DEPENDENCY MANIFEST, CONFLICT MAPPING, SEMANTIC DIFF, DRY RUN, OWNERSHIP MARKER, ROLLBACK COVERAGE OR PRIVACY-CLASSIFIED EXPORT.** Native write APIs are substrate only and never substitute for Import/Export validation, preview or ownership semantics. No import, export, package build, file write or data mutation occurs here.

## Current native sources

- export_wp() — https://developer.wordpress.org/reference/functions/export_wp/
- wp_insert_post() — https://developer.wordpress.org/reference/functions/wp_insert_post/
- wp_update_post() — https://developer.wordpress.org/reference/functions/wp_update_post/
- update_post_meta() — https://developer.wordpress.org/reference/functions/update_post_meta/
- register_meta() — https://developer.wordpress.org/reference/functions/register_meta/
- wp_insert_term() — https://developer.wordpress.org/reference/functions/wp_insert_term/
- wp_set_object_terms() — https://developer.wordpress.org/reference/functions/wp_set_object_terms/
- wp_insert_user() — https://developer.wordpress.org/reference/functions/wp_insert_user/
- is_email() — https://developer.wordpress.org/reference/functions/is_email/
- sanitize_user() — https://developer.wordpress.org/reference/functions/sanitize_user/
- wp_hash_password() — https://developer.wordpress.org/reference/functions/wp_hash_password/
- wp_handle_upload() — https://developer.wordpress.org/reference/functions/wp_handle_upload/
- wp_check_filetype_and_ext() — https://developer.wordpress.org/reference/functions/wp_check_filetype_and_ext/
- wp_upload_dir() — https://developer.wordpress.org/reference/functions/wp_upload_dir/
- unzip_file() — https://developer.wordpress.org/reference/functions/unzip_file/
- sanitize_file_name() — https://developer.wordpress.org/reference/functions/sanitize_file_name/
- download_url() — https://developer.wordpress.org/reference/functions/download_url/
- media_handle_sideload() — https://developer.wordpress.org/reference/functions/media_handle_sideload/
- wp_safe_remote_get() — https://developer.wordpress.org/reference/functions/wp_safe_remote_get/
- wp_http_validate_url() — https://developer.wordpress.org/reference/functions/wp_http_validate_url/
- set_post_thumbnail() — https://developer.wordpress.org/reference/functions/set_post_thumbnail/
- wp_unique_post_slug() — https://developer.wordpress.org/reference/functions/wp_unique_post_slug/
- wp_generate_uuid4() — https://developer.wordpress.org/reference/functions/wp_generate_uuid4/
- wp_json_encode() — https://developer.wordpress.org/reference/functions/wp_json_encode/
- maybe_unserialize() — https://developer.wordpress.org/reference/functions/maybe_unserialize/
- is_serialized() — https://developer.wordpress.org/reference/functions/is_serialized/
- wp_kses_post() — https://developer.wordpress.org/reference/functions/wp_kses_post/
- get_post_types() — https://developer.wordpress.org/reference/functions/get_post_types/
- current_user_can() — https://developer.wordpress.org/reference/functions/current_user_can/
- wp_verify_nonce() — https://developer.wordpress.org/reference/functions/wp_verify_nonce/
- check_admin_referer() — https://developer.wordpress.org/reference/functions/check_admin_referer/
- wp_schedule_event() — https://developer.wordpress.org/reference/functions/wp_schedule_event/
- is_multisite() — https://developer.wordpress.org/reference/functions/is_multisite/
- switch_to_blog() — https://developer.wordpress.org/reference/functions/switch_to_blog/
- wp_privacy_personal_data_exporters — https://developer.wordpress.org/reference/hooks/wp_privacy_personal_data_exporters/
- Privacy (Plugin Handbook) — https://developer.wordpress.org/plugins/privacy/
- Tools > Export Screen — https://wordpress.org/documentation/article/tools-export-screen/
- Tools > Import Screen — https://wordpress.org/documentation/article/tools-import-screen/
- WP-CLI wp export — https://developer.wordpress.org/cli/commands/export/
- WP-CLI wp import — https://developer.wordpress.org/cli/commands/import/
- In-repo: `docs/MODULES/IMPORT-EXPORT-EXHAUSTIVE-SPEC.md`, `docs/ARCHITECTURE/PORTABLE-CONFIGURATION-PACKAGE-FORMAT.md`, `docs/ARCHITECTURE/SOURCE-MIGRATION-ADAPTER-REGISTRY.md`, `docs/PRODUCT/IMPORT-EXPORT-BANK-ENTRY-READINESS-V1.md`, `config/product/options-bank/dashboard-widgets--preferences-multisite-portability.json` (Surface 10 preset portability snapshot/preflight/mapping), `config/product/options-bank/connections.json`, `config/product/options-bank/backup.json` and `config/product/options-bank/reset.json` (Surfaces 23–25 BANK_REVIEWED), `config/product/options-bank/rest-api.json` (Surface 22 assigns packages to Surface 26)

Native classification of the readiness checklist: posts/users/terms/comments/media CRUD (`wp_insert_post()`, `wp_update_post()`, `wp_insert_term()`, `wp_set_object_terms()`, `wp_insert_user()`, `update_post_meta()`/`register_meta()` fire native hooks and sanitization; mapping, match identity and dry run are WPE), capability/Policy and object ownership (`current_user_can()`, `wp_verify_nonce()`, `check_admin_referer()` are necessary but not sufficient; separate import/export/destructive-sync/sensitive-export capabilities are WPE), WordPress IDs vs portable identity (numeric IDs are same-environment only; `wp_generate_uuid4()` and `wp_unique_post_slug()` support stable UUID/slug identity), media sideload/fetch (`download_url()`, `media_handle_sideload()`, `wp_safe_remote_get()` and `wp_http_validate_url()` reject private/local hosts; WPE adds host allowlists, byte caps and MIME allowlists), uploads and archives (`wp_handle_upload()`, `wp_check_filetype_and_ext()`, `wp_upload_dir()`, `unzip_file()`, `sanitize_file_name()`; WPE adds private non-executable storage, archive traversal/bomb limits and retention), WXR native import/export (`export_wp()`, the WordPress Importer and WP-CLI `export`/`import` are the content parity baseline without mapping, dry run or ownership markers), serialization (`maybe_unserialize()`/`is_serialized()` exist natively but imported payloads are never deserialized as PHP objects; packages are JSON-only via `wp_json_encode()`), multisite boundaries (`is_multisite()`, `switch_to_blog()`; per-site scope by default), privacy export/erasure (`wp_privacy_personal_data_exporters` and the Privacy handbook remain the GDPR path; Import/Export redacts PII and is not an erasure workflow), cron/job limits (`wp_schedule_event()` substrate; execution owned by Surface 18).

## Seed-record disposition

| Seed record | Native disposition | Notes |
|---|---|---|
| `import-export.overview.cards` | **WPE PRODUCT/POLICY** | Recent imports/exports, failed/partial runs, scheduled runs, unresolved mapping conflicts, adapters needing attention and storage/temp-space health are computed summaries; counts only, no source row content or PII.. |
| `import-export.overview.quick-actions` | **WPE PRODUCT/POLICY** | Import/Export WPE package, New Data Import/Export and View failed rows each route to their own wizard with capability checks; no quick action executes a write directly.. |
| `import-export.package.export.scope` | **WPE PRODUCT/POLICY** | Selection by suite, module or individual definition through stable UUIDs; Export All builds a Configuration Package only and never implicitly bundles runtime data.. |
| `import-export.package.export.dependency-closure` | **WPE PRODUCT/POLICY** | Include transitive hard dependencies by default; optional dependencies explicit; external references recorded in the manifest; excluding a hard dependency requires an impact preview and the importer never creates dangling hard dependencies.. |
| `import-export.package.export.metadata` | **WPE PRODUCT/POLICY** | Human title, description and environment/source label are optional manifest metadata; source site-identifying metadata is minimized by default.. |
| `import-export.package.export.revisions` | **WPE PRODUCT/POLICY** | Current only is default; history export is explicit and bounded.. |
| `import-export.package.export.inactive` | **WPE PRODUCT/POLICY** | Off by default; included definitions keep their exported status and never activate on import implicitly.. |
| `import-export.package.export.module-settings` | **WPE PRODUCT/POLICY** | Only settings their owner marks portable; environment-specific settings (URLs, paths, caches) are excluded or placeholdered.. |
| `import-export.package.export.ui-preferences` | **WPE PRODUCT/POLICY** | Only preferences the owner marks portable are offered; per-user state is never exported by default.. |
| `import-export.package.export.connection-definitions` | **WPE PRODUCT/POLICY** | Exports provider/type, non-secret settings and required scopes consistent with the Surface 23 Connections Bank (connections.portability.import-export); credentials never leave the Vault.. |
| `import-export.package.export.credential-placeholders` | **WPE PRODUCT/POLICY** | Typed secret slot/reference placeholders replace every credential; import marks the connection credential_required until an admin binds a local Vault secret.. |
| `import-export.package.export.preflight` | **WPE PRODUCT/POLICY** | Dependency graph, missing/broken definitions, Pro-module definitions, unsupported schema versions, estimated size and a secrets-excluded summary are shown before the package is written.. |
| `import-export.package.manifest.format` | **WPE PRODUCT/POLICY** | manifest.json carries format, format_version, package UUID, package type, created-at UTC, source product and Platform API versions, module requirements and secret policy; runtime-data inclusion is false for definition packages.. |
| `import-export.package.manifest.stable-identity` | **WPE PRODUCT/POLICY** | Definitions reference each other by stable UUID/key; local DB primary keys are never portable identity.. |
| `import-export.package.manifest.checksums` | **WPE PRODUCT/POLICY** | Checksums detect accidental or tampered content but do not establish author trust.. |
| `import-export.package.manifest.dependency-edges` | **WPE PRODUCT/POLICY** | Circular dependencies are a validation error unless the domain explicitly supports a safe mutually-referential import sequence.. |
| `import-export.package.type.catalog` | **WPE PRODUCT/POLICY** | Definition, Blueprint and Full WPE Configuration packages exclude runtime data and secrets by default; Runtime Data Export is a separate engine/artifact.. |
| `import-export.package.container.safety` | **WPE PRODUCT/POLICY** | Candidate ZIP container with UTF-8 JSON manifests/definitions and optional safe static assets only; no executable PHP/JS; archive traversal and bomb protections on import.. |
| `import-export.package.assets.portable` | **WPE PRODUCT/POLICY** | Only assets necessary for the definition and safe to redistribute; paid theme/plugin/vendor assets without redistribution rights are never packaged.. |
| `import-export.package.personal-data-exclusion` | **WPE PRODUCT/POLICY** | Users, entries, chat, enrollments, runs, audit logs, notification history, backup archives, custom-table rows and field values are excluded; blueprint sample data must be marked sample_data and contain no production personal data.. |
| `import-export.package.import.wizard` | **WPE PRODUCT/POLICY** | No step writes before approval; each step is resumable from the report, and the dry run reuses the Surface 10 Dashboard Widgets preset portability pattern of snapshot, preflight and mapping before apply.. |
| `import-export.package.import.source` | **WPE PRODUCT/POLICY** | URL import only through Surface 23 Connections Safe HTTP/SSRF policy, never arbitrary file_get_contents(); uploads go to private non-executable storage.. |
| `import-export.package.import.verify` | **WPE PRODUCT/POLICY** | Malformed manifests, checksum mismatch and schema violations stop the run before analysis; all package content is treated as untrusted input.. |
| `import-export.package.import.compatibility` | **WPE PRODUCT/POLICY** | Supported older schemas migrate through accepted migrators; unknown future schemas are inspect/report only with no lossy downgrade.. |
| `import-export.package.import.conflict-classes` | **WPE PRODUCT/POLICY** | exact existing UUID/revision, update available, local newer, same UUID divergent history, key/slug collision with different UUID, dependency missing, module unavailable, Pro on Free-only site and unsupported newer schema are explicit classes.. |
| `import-export.package.import.resolution` | **WPE PRODUCT/POLICY** | Destructive replace is never a hidden default; key collision never assumes identity; replace only when the target is WPE-owned and impact permits.. |
| `import-export.package.import.semantic-diff` | **WPE PRODUCT/POLICY** | Diff on normalized schema fields, not raw JSON lines; security/visibility/capability changes are highlighted separately.. |
| `import-export.package.import.conflict-center` | **WPE PRODUCT/POLICY** | Groups Safe/Create/Update/Rename/Map/Missing dependency/Credential required/Lossy/Unsupported; each row shows source, target candidate, reason, diff summary, dependency impact, action and severity.. |
| `import-export.package.import.bulk-decisions` | **WPE PRODUCT/POLICY** | Rename-all-collisions may be offered; overwrite-all is never a default safe action.. |
| `import-export.package.import.modes` | **WPE PRODUCT/POLICY** | Create-only never mutates matched targets; clone rewrites internal references to new UUIDs; sync/update applies only to same-source/same-UUID definitions with revision safeguards.. |
| `import-export.package.import.free-pro` | **WPE PRODUCT/POLICY** | Pro definitions stay visible in the report and may be stored as deferred/read-only package records only if architecture permits; never silently discarded or falsely activated.. |
| `import-export.package.import.credential-required` | **WPE PRODUCT/POLICY** | Imported connections/plans stay disabled or degraded until an admin binds a new Vault reference, matching Surface 23 and Surface 24 Bank portability decisions.. |
| `import-export.package.import.revisions` | **WPE PRODUCT/POLICY** | Updating a definition preserves its current revision, writes a new revision and records the package/batch ID for rollback.. |
| `import-export.package.import.history` | **WPE PRODUCT/POLICY** | Package UUID, source version, actor, decisions, counts and result are retained as audit-safe history without secret or personal payloads.. |
| `import-export.data-import.list.columns` | **WPE PRODUCT/POLICY** | Name, target, source format, mode, schedule, last run/result, rows processed/failed, source, updated, health and actions.. |
| `import-export.data-import.list.actions` | **WPE PRODUCT/POLICY** | Run always routes through preflight and the confirmation policy of its mode; Duplicate copies definition only; delete is archive-first.. |
| `import-export.data-import.identity` | **WPE PRODUCT/POLICY** | Name required, stable key, Draft/Enabled/Paused/Archived status; only Enabled definitions run on schedule.. |
| `import-export.data-import.source-type` | **WPE PRODUCT/POLICY** | Google Sheets and remote REST only through certified Connections providers; source-plugin migration only through certified adapters.. |
| `import-export.data-import.target` | **WPE PRODUCT/POLICY** | Posts/CPTs, users, terms, comments/media where supported, custom tables and owner-module importers; never direct writes to another module's private tables.. |
| `import-export.source.upload.limits` | **WPE PRODUCT/POLICY** | Max size bounded by server and module policy; MIME + extension + content sniffing via wp_check_filetype_and_ext() substrate.. |
| `import-export.source.upload.archive` | **WPE PRODUCT/POLICY** | Entry count, ratio, total bytes and path traversal limits; nested archives rejected.. |
| `import-export.source.upload.storage` | **WPE PRODUCT/POLICY** | Import files stored outside public executable paths with one-shot vs saved retention and cleanup schedule.. |
| `import-export.source.upload.encoding` | **WPE PRODUCT/POLICY** | UTF-8 default with approved encodings; invalid byte sequences reported per row.. |
| `import-export.parse.csv.delimiter` | **WPE PRODUCT/POLICY** | Custom delimiter bounded to one character; RFC 4180 quoting semantics.. |
| `import-export.parse.csv.header` | **WPE PRODUCT/POLICY** | Preview shows parsed columns before mapping.. |
| `import-export.parse.csv.quote-escape` | **WPE PRODUCT/POLICY** | Escape semantics follow the selected parser; never ad-hoc splitting.. |
| `import-export.parse.csv.bom-empty` | **WPE PRODUCT/POLICY** | Comment rows only with explicit syntax.. |
| `import-export.parse.csv.row-window` | **WPE PRODUCT/POLICY** | Bounded test window; never alters the saved full-run range silently.. |
| `import-export.parse.json.root` | **WPE PRODUCT/POLICY** | Controlled selector grammar; missing path behavior explicit.. |
| `import-export.parse.json.limits` | **WPE PRODUCT/POLICY** | Depth and item caps enforced while streaming.. |
| `import-export.parse.json.flatten` | **WPE PRODUCT/POLICY** | Preview only; mapping still targets explicit paths.. |
| `import-export.parse.xml.record` | **WPE PRODUCT/POLICY** | Namespace mappings declared explicitly.. |
| `import-export.parse.xml.xxe` | **WPE PRODUCT/POLICY** | External entity expansion disabled and external DTD/resources never fetched, per the OWASP XXE Prevention Cheat Sheet.. |
| `import-export.parse.sheet.options` | **WPE PRODUCT/POLICY** | Parser/library choice remains technical evidence work.. |
| `import-export.parse.sheet.formulas` | **WPE PRODUCT/POLICY** | Evaluated cached value only; spreadsheet macros never run.. |
| `import-export.source.remote.connection` | **WPE PRODUCT/POLICY** | Remote API source uses a Surface 23 Connection; secrets are never typed into the import definition.. |
| `import-export.source.remote.pagination` | **WPE PRODUCT/POLICY** | Provider adapter strategy; bounded page count per run.. |
| `import-export.source.remote.rate` | **WPE PRODUCT/POLICY** | Backpressure owned by Connections; recurring cursor sync remains Surface 41.. |
| `import-export.preview.profile` | **WPE PRODUCT/POLICY** | Fields/paths, candidate types, null/empty counts, duplicate key samples, parse issues, encoding warnings, media URL fields and nested collections from a bounded sample.. |
| `import-export.preview.inference-confirm` | **WPE PRODUCT/POLICY** | Administrator confirms every target mapping.. |
| `import-export.mode.selection` | **WPE PRODUCT/POLICY** | Synchronize is high-impact because missing source rows can change or delete target records; Create Only is the default.. |
| `import-export.match.key` | **WPE PRODUCT/POLICY** | WordPress ID only for same-environment controlled imports with a non-portable warning; email matching under privacy constraints.. |
| `import-export.match.normalization` | **WPE PRODUCT/POLICY** | Follows target semantics.. |
| `import-export.match.preflight-unique` | **WPE PRODUCT/POLICY** | Run blocked when the target key is not unique.. |
| `import-export.match.duplicate-source` | **WPE PRODUCT/POLICY** | Fail duplicates is the safest default; row numbers reported.. |
| `import-export.mapping.row` | **WPE PRODUCT/POLICY** | Mapping uses target Field/Data Source schema; no arbitrary mass assignment.. |
| `import-export.mapping.null-semantics` | **WPE PRODUCT/POLICY** | Missing source ignores target by default; zero/false never confused with empty; preview shows examples because silent clearing is dangerous.. |
| `import-export.transform.allowlist` | **WPE PRODUCT/POLICY** | trim, case, casts, date/timezone, split/join, exact replace, prefix/suffix, enum map, bounded JSON decode, HTML sanitize, phone/email/URL normalize, lookup and registered SDK transforms; reusable engine delegated to Surface 45 where shared.. |
| `import-export.transform.preview` | **WPE PRODUCT/POLICY** | Every transform shows a bounded sample.. |
| `import-export.transform.regex` | **WPE PRODUCT/POLICY** | Only after security/performance review with time and length bounds.. |
| `import-export.mapping.taxonomy` | **WPE PRODUCT/POLICY** | Term ID mapping only same-site; creating terms requires capability and validates hierarchy cycles.. |
| `import-export.mapping.relations` | **WPE PRODUCT/POLICY** | Relation definition, related identity, one/many, replace/append and missing-target error/skip/defer second pass; never raw serialized meta.. |
| `import-export.mapping.relations-deferred-pass` | **WPE PRODUCT/POLICY** | Large many-to-many imports link in a bounded second pass.. |
| `import-export.media.remote-fetch` | **WPE PRODUCT/POLICY** | Allowed schemes/hosts, private-network block, max bytes, timeout and MIME allowlist; remote URL never bypasses Safe HTTP/file checks.. |
| `import-export.media.options` | **WPE PRODUCT/POLICY** | Duplicate match by checksum, source URL or external ID.. |
| `import-export.user.mapping` | **WPE PRODUCT/POLICY** | User meta only through an allowlist.. |
| `import-export.user.role-allowlist` | **WPE PRODUCT/POLICY** | Administrator-equivalent roles require a dedicated high-risk permission and migration evidence; role truth stays Surface 30.. |
| `import-export.user.notification` | **WPE PRODUCT/POLICY** | Explicit opt-in; delivery owned by Notifications (Surface 19).. |
| `import-export.user.password-policy` | **WPE PRODUCT/POLICY** | Users get reset links; hashes only through certified same-system migration where compatible and explicitly supported.. |
| `import-export.post.status-date-author` | **WPE PRODUCT/POLICY** | Invalid status or capability falls back or errors per policy; never silently publishes private content.. |
| `import-export.sync.missing-records` | **WPE PRODUCT/POLICY** | Do nothing is the safest default; permanent delete is exceptional high-risk with dedicated capability.. |
| `import-export.sync.ownership-scope` | **WPE PRODUCT/POLICY** | Widening scope requires explicit administrator action with high warning.. |
| `import-export.sync.ownership-marker` | **WPE PRODUCT/POLICY** | Used for deterministic re-import, missing-record sync and conflict detection.. |
| `import-export.update.conflict-policy` | **WPE PRODUCT/POLICY** | Exact detection via stored source fingerprint plus target revision where available; report-and-skip is the managed-sync safe candidate.. |
| `import-export.dryrun.required` | **WPE PRODUCT/POLICY** | Required before destructive runs, strongly encouraged otherwise; writes no target runtime data except temporary analysis records cleaned by retention.. |
| `import-export.dryrun.counts` | **WPE PRODUCT/POLICY** | Role/status sensitive changes are listed explicitly.. |
| `import-export.dryrun.drift` | **WPE PRODUCT/POLICY** | Source checksum and mapping revision fingerprints invalidate a stale dry run.. |
| `import-export.threshold.abort` | **WPE PRODUCT/POLICY** | Destructive sync defaults to abort when preflight has unresolved mapping or identity errors.. |
| `import-export.batch.size` | **WPE PRODUCT/POLICY** | Memory/time diagnostics shown; manual size bounded by settings cap.. |
| `import-export.batch.checkpoint` | **WPE PRODUCT/POLICY** | Resume only when source/mapping compatibility still matches; changed source requires a new run.. |
| `import-export.run.states` | **WPE PRODUCT/POLICY** | queued, analyzing, dry_running, waiting_confirmation, importing, linking_relations, processing_media, finalizing, completed, completed_with_errors, failed, cancelled, rollback_available, rolled_back.. |
| `import-export.run.row-result` | **WPE PRODUCT/POLICY** | Source item ID, safe target reference, action, status, error codes and changed-field keys only; full PII source rows not persisted by default.. |
| `import-export.run.failed-row-export` | **WPE PRODUCT/POLICY** | Source data in failed-row exports requires dedicated capability and privacy policy.. |
| `import-export.rollback.strategies` | **WPE PRODUCT/POLICY** | Only for changes WPE recorded sufficiently; later local edits make automatic rollback dangerous and are warned.. |
| `import-export.rollback.coverage` | **WPE PRODUCT/POLICY** | External side effects, emails, provider calls and downloaded media may not be reversible.. |
| `import-export.rollback.restore-point` | **WPE PRODUCT/POLICY** | Recommended or required for high-impact sync; restore-point truth is Surface 24 Backup.. |
| `import-export.schedule.import` | **WPE PRODUCT/POLICY** | Persistent Connection/path source only, overlap skip/queue, dry-run-only schedules, failure threshold pause and failure notification; executes on Surface 18.. |
| `import-export.schedule.revision-pin` | **WPE PRODUCT/POLICY** | Mapping changes never mutate an already-running run.. |
| `import-export.export.list` | **WPE PRODUCT/POLICY** | Name, source/query, format, destination, schedule, last run/result, rows/bytes, updated; Download latest only if authorized.. |
| `import-export.export.source` | **WPE PRODUCT/POLICY** | Query/source policy checked server-side; object-level read permission enforced per record.. |
| `import-export.export.fields` | **WPE PRODUCT/POLICY** | Each field carries a privacy classification; PII is redacted or masked per policy unless the actor holds export-sensitive-data capability.. |
| `import-export.export.sensitive-gate` | **WPE PRODUCT/POLICY** | Sensitive fields require additional capability; P3 secrets are unavailable.. |
| `import-export.export.secret-exclusion` | **WPE PRODUCT/POLICY** | Never offered as generic export fields in any mode.. |
| `import-export.export.format.csv` | **WPE PRODUCT/POLICY** | BOM optional for spreadsheet compatibility.. |
| `import-export.export.format.json` | **WPE PRODUCT/POLICY** | NDJSON candidate for large data.. |
| `import-export.export.format.xml-xlsx` | **WPE PRODUCT/POLICY** | XLSX through a reviewed writer only.. |
| `import-export.export.csv-injection` | **WPE PRODUCT/POLICY** | Cells starting with =, +, -, @, tab or CR are escaped per OWASP CSV Injection guidance; raw output only via explicit trusted developer mode.. |
| `import-export.export.destination` | **WPE PRODUCT/POLICY** | Remote destinations reuse Backup storage or Connections adapters; credentials remain Vault-managed; email sends a secure link, not a large attachment.. |
| `import-export.export.file.naming` | **WPE PRODUCT/POLICY** | Tokens sanitized via sanitize_file_name() substrate; no path segments.. |
| `import-export.export.file.options` | **WPE PRODUCT/POLICY** | Overwrite never is default.. |
| `import-export.export.file.encryption` | **WPE PRODUCT/POLICY** | Reuses the Surface 24 encryption contract; no ad-hoc password ZIP.. |
| `import-export.export.incremental` | **WPE PRODUCT/POLICY** | Only when the source supports a stable cursor; never claims complete incremental sync when deletes are undetectable.. |
| `import-export.export.preview` | **WPE PRODUCT/POLICY** | Shows transformed output, masking/redaction, estimated rows/size and query warnings; never the full export in browser memory.. |
| `import-export.export.run-states` | **WPE PRODUCT/POLICY** | queued, querying, writing, compressing/encrypting, uploading, verifying destination, completed, completed_with_warnings, failed, cancelled, pruned; completed means the selected verification tier, not only HTTP success.. |
| `import-export.schedule.export` | **WPE PRODUCT/POLICY** | Overlap and failure policy as imports; executes on Surface 18.. |
| `import-export.template.mapping` | **WPE PRODUCT/POLICY** | Saved only when source and target schema identities are compatible.. |
| `import-export.template.drift` | **WPE PRODUCT/POLICY** | Never silently shifts columns by position.. |
| `import-export.adapter.detection` | **WPE PRODUCT/POLICY** | Detection confidence shown; uncertified versions are inspect-only.. |
| `import-export.adapter.fidelity` | **WPE PRODUCT/POLICY** | Lossy and unsupported items are listed before mapping.. |
| `import-export.adapter.ir-boundary` | **WPE PRODUCT/POLICY** | Adapters never write the target DB directly and never execute source-provided callbacks/code.. |
| `import-export.adapter.deactivation-verify` | **WPE PRODUCT/POLICY** | Counts/identities compared and live dependencies listed; never claims safe to deactivate unless fidelity checks pass; source untouched by default.. |
| `import-export.settings.temp` | **WPE PRODUCT/POLICY** | Private non-executable temp location with low-space monitoring.. |
| `import-export.settings.upload-cap` | **WPE PRODUCT/POLICY** | Never exceeds server limits.. |
| `import-export.settings.batch-defaults` | **WPE PRODUCT/POLICY** | Caps bound manual batch sizes.. |
| `import-export.settings.retention` | **WPE PRODUCT/POLICY** | Bounded retention; failed-row data with PII purged on schedule.. |
| `import-export.settings.encoding` | **WPE PRODUCT/POLICY** | UTF-8 default.. |
| `import-export.settings.remote-limits` | **WPE PRODUCT/POLICY** | Bounded defaults.. |
| `import-export.settings.formula-protection` | **WPE PRODUCT/POLICY** | Default on; disabling requires trusted developer mode.. |
| `import-export.settings.failure-pause` | **WPE PRODUCT/POLICY** | Pauses after N consecutive failures and notifies.. |
| `import-export.settings.privacy-logging` | **WPE PRODUCT/POLICY** | Minimal by default; logs never contain secret values or full PII rows.. |
| `import-export.perm.separation` | **WPE PRODUCT/POLICY** | Every write request requires capability plus nonce (current_user_can()/check_admin_referer() substrate).. |
| `import-export.perm.high-risk-reauth` | **WPE PRODUCT/POLICY** | Dedicated capability and recent re-authentication.. |
| `import-export.ability.catalog` | **WPE PRODUCT/POLICY** | list/get/create/update/validate/preview/dry-run/run/cancel/rollback with Policy checks.. |
| `import-export.ability.ai-default` | **WPE PRODUCT/POLICY** | No destructive import, sync, rollback or sensitive export by AI by default.. |
| `import-export.events.catalog` | **WPE PRODUCT/POLICY** | Aggregated run events; no per-row workflow event by default.. |
| `import-export.errors.degraded` | **WPE PRODUCT/POLICY** | source changed, schema stale, encoding, malformed, adapter unsupported, dependency missing, low disk, media blocked, relation missing, permission denied, rate limit, checkpoint corrupted, rollback partial; Pro expiry makes definitions read-only and pauses mutating schedules safely.. |
| `import-export.perf.streaming` | **WPE PRODUCT/POLICY** | No full large file in memory, no N+1 lookups, async large runs, temp disk monitoring.. |
| `import-export.a11y.mapping-table` | **WPE PRODUCT/POLICY** | Assets only on Import/Export screens; bounded polling via shared job UI.. |
| `import-export.multisite.boundary` | **WPE PRODUCT/POLICY** | switch_to_blog()/is_multisite() substrate; no cross-site import by default.. |
| `import-export.audit.trail` | **WPE PRODUCT/POLICY** | Actor, definition revision, source fingerprint, counts and result; no secret values or PII payloads.. |
| `import-export.security.untrusted-input` | **WPE PRODUCT/POLICY** | Schema validation, size limits and full validation whether or not a package is signed.. |
| `import-export.security.no-unserialize` | **WPE PRODUCT/POLICY** | Package and data payloads are parsed as JSON/CSV/XML only; serialized PHP strings are treated as opaque text and never passed to unserialize()/maybe_unserialize().. |
| `import-export.native.post-crud` | **NATIVE SUBSTRATE + WPE POLICY** | Native hooks, sanitization and capability semantics; no raw SQL writes.. |
| `import-export.native.post-meta` | **NATIVE SUBSTRATE + WPE POLICY** | Registered meta with sanitize callbacks; arbitrary meta keys only through allowlist.. |
| `import-export.native.terms` | **NATIVE SUBSTRATE + WPE POLICY** | Native term creation and relationship replace/append.. |
| `import-export.native.users` | **NATIVE SUBSTRATE + WPE POLICY** | Native user creation; passwords set by wp_hash_password() only from reset flows.. |
| `import-export.native.media-sideload` | **NATIVE SUBSTRATE + WPE POLICY** | Safe HTTP rejects private/local hosts via wp_http_validate_url(); set_post_thumbnail() for featured images.. |
| `import-export.native.upload-checks` | **NATIVE SUBSTRATE + WPE POLICY** | Real MIME/extension validation; private directory under wp_upload_dir() with deny rules.. |
| `import-export.native.archive-unzip` | **NATIVE SUBSTRATE + WPE POLICY** | WP_Filesystem extraction wrapped with entry/ratio/path limits.. |
| `import-export.native.slug-uuid` | **NATIVE SUBSTRATE + WPE POLICY** | Collision-safe slugs and stable UUID generation for portable identity.. |
| `import-export.native.wxr` | **NATIVE SUBSTRATE + WPE POLICY** | Native content export/import is the parity baseline for posts/terms/comments/authors; WPE adds mapping, dry run and ownership markers.. |
| `import-export.native.privacy-exporters` | **NATIVE SUBSTRATE + WPE POLICY** | Privacy export/erasure tooling remains the GDPR path; Import/Export is not an erasure workflow.. |
| `import-export.native.cron` | **NATIVE SUBSTRATE + WPE POLICY** | Execution owned by Surface 18 Job Service.. |
| `import-export.native.kses` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Used by the HTML sanitize transform.. |
| `import-export.native.json-encode` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Safe JSON encoding for packages/exports.. |
| `import-export.native.wp-cli` | **NATIVE OPTIONAL SUBSTRATE, WPE-BOUNDED** | Developer precedent; WPE CLI parity is a later Abilities surface, not a bypass of preflight.. |
| `import-export.exceed.signed-packages` | **WPE EXCEED / NO NATIVE EQUIVALENT** | Optional future signing establishes publisher trust; unsigned packages are still allowed but fully validated; requires separate key/distribution policy.. |
| `import-export.exceed.blueprint-registry` | **WPE EXCEED / NO NATIVE EQUIVALENT** | Signed publisher metadata, changelog and minimum requirements.. |
| `import-export.exceed.semantic-risk-diff` | **WPE EXCEED / NO NATIVE EQUIVALENT** | High-impact visibility/capability diffs highlighted beyond market line diffs.. |
| `import-export.exceed.rollback-coverage` | **WPE EXCEED / NO NATIVE EQUIVALENT** | Market importers rarely quantify rollback coverage before execution.. |
| `import-export.deferred.encrypted-secrets` | **DEFERRED / CROSS-OWNER** | Requires separate ADR/threat model; secrets excluded until then.. |
| `import-export.deferred.concurrent-workers` | **DEFERRED / CROSS-OWNER** | Only after idempotency/locking proof.. |
| `import-export.deferred.password-hash-migration` | **DEFERRED / CROSS-OWNER** | Only through a certified same-system adapter with compatibility evidence.. |
| `import-export.deferred.admin-list-snapshot` | **DEFERRED / CROSS-OWNER** | Spec marks candidate; deferred until list-table owners expose a stable query snapshot.. |
| `import-export.deferred.blueprint-sample-data` | **DEFERRED / CROSS-OWNER** | Separately marked sample_data; persistence design pending.. |
| `import-export.deferred.pro-deferred-objects` | **DEFERRED / CROSS-OWNER** | Exact persistence of deferred objects is later design work.. |
| `import-export.unsafe.php-eval` | **REJECTED-UNSAFE CONFIRMED** | Rejected: imported code is never executed.. |
| `import-export.unsafe.raw-sql` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.unserialize` | **REJECTED-UNSAFE CONFIRMED** | Rejected per OWASP Deserialization Cheat Sheet; PHP object injection risk.. |
| `import-export.unsafe.private-table-write` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.xxe` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.archive-traversal` | **REJECTED-UNSAFE CONFIRMED** | Rejected; no arbitrary file write outside the private import area.. |
| `import-export.unsafe.arbitrary-fetch` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.plaintext-passwords` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.role-elevation` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.silent-delete-sync` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.fuzzy-destructive` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.overwrite-all` | **REJECTED-UNSAFE CONFIRMED** | Rejected; destructive replace is never a hidden default.. |
| `import-export.unsafe.secret-export` | **REJECTED-UNSAFE CONFIRMED** | Rejected; Vault secrets, license keys, tokens and payment credentials never exported.. |
| `import-export.unsafe.csv-injection` | **REJECTED-UNSAFE CONFIRMED** | Rejected as a default.. |
| `import-export.unsafe.macro-exec` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.unbounded-batch` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.generic-rollback-claim` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.ai-destructive` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.free-pro-faking` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.local-id-identity` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.runtime-in-package` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |
| `import-export.unsafe.unredacted-pii` | **REJECTED-UNSAFE CONFIRMED** | Rejected.. |

## Native completeness / unresolved items

No additional Import/Export V1 Bank family is required. Native unresolved items: **0**. Configuration packages, dependency manifests, conflict classes and semantic diff, dry-run counts, ownership markers, rollback coverage, privacy-classified export fields and CSV formula-injection protection have no native WordPress equivalent and remain WPE/market-owned.

## Gate boundary

Not promoted here: import/export/package execution, file writes, data mutation, runtime, Atomic Option Contracts, UX contract closure or product parity.
