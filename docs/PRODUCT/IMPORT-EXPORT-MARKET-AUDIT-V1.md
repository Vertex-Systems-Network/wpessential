# Import/Export — Market Audit V1

Status: `MARKET_AUDITED` (integrated in `config/product/options-bank-audits/import-export-market-ecosystem.json`)

Surface: 26 — Import/Export
Snapshot: 2026-10-19
Issue: #1454

## 1. Scope and boundary

Reconciles all 188 Import/Export records. Surface 26 owns one-time configuration package and runtime data movement, mapping, dry-run, run and rollback-coverage semantics only; each owner surface keeps Definition/data validation and mutation, Sync (S41) recurring synchronization, Transform (S45) the reusable typed transform engine, Connections (S23) remote HTTP/credentials, Backup (S24) restore points and storage encryption, Cron/Job (S18) scheduling, Notifications (S19) delivery, Roles/Policy (S30) role truth and Staging/Migration (S55) environment migration. Packaging ownership matches the Surface 22, 23, 24 and 25 Bank rows, which route package movement to Surface 26.

## 2. Evidence

- **E1 WordPress Importer (WXR) + Tools Export** — https://wordpress.org/plugins/wordpress-importer/, https://wordpress.org/documentation/article/tools-export-screen/ — WXR content export from Tools > Export and import of posts, pages, comments, custom fields, terms and authors with author mapping and optional attachment download; no dry run, mapping or ownership marker.
- **E2 WP All Import** — https://wordpress.org/plugins/wp-all-import/, https://www.wpallimport.com/ — CSV/XML/JSON/Excel import with drag-and-drop field mapping, taxonomies, images, users, update/create matching, delete-missing records, scheduling and custom PHP functions.
- **E3 WP All Export** — https://wordpress.org/plugins/wp-all-export/ — Export of posts/users/custom data to CSV/XML/Excel with field selection, filtering, scheduled exports and custom PHP functions.
- **E4 WP Ultimate CSV Importer** — https://wordpress.org/plugins/wp-ultimate-csv-importer/ — CSV/XML import and export of posts, pages, CPTs, users, taxonomies and media with field mapping, update existing records, scheduled import and rollback.
- **S1 Customizer Export/Import** — https://wordpress.org/plugins/customizer-export-import/ — Customizer settings export/import as a single file (Specialist: settings packaging; serialized-payload risk).
- **S2 Widget Importer & Exporter** — https://wordpress.org/plugins/widget-importer-exporter/ — Widget export/import as a JSON .wie file with sidebar mapping report (Specialist: definition portability).
- **S3 Import and export users and customers** — https://wordpress.org/plugins/import-users-from-csv-with-meta/ — User/customer CSV import/export with roles, meta and notification options (Specialist: user import safety).
- **S4 WooCommerce Product CSV Importer and Exporter** — https://woocommerce.com/document/product-csv-importer-exporter/ — Product CSV importer/exporter with column mapping, update-existing by ID/SKU and batched processing.
- **S5 WP Migrate** — https://wordpress.org/plugins/wp-migrate-db/ — Database export/push/pull with find-and-replace (boundary: Surface 55/45).
- **S6 OWASP CSV Injection** — https://owasp.org/www-community/attacks/CSV_Injection — Formula-injection risk in CSV exports and escaping guidance.
- **S7 OWASP XML External Entity Prevention Cheat Sheet** — https://cheatsheetseries.owasp.org/cheatsheets/XML_External_Entity_Prevention_Cheat_Sheet.html — Disabling external entities/DTDs in XML parsers.
- **S8 OWASP Deserialization Cheat Sheet** — https://cheatsheetseries.owasp.org/cheatsheets/Deserialization_Cheat_Sheet.html — Never deserialize untrusted data into objects; prefer data-only formats such as JSON.
- **S9 OWASP File Upload Cheat Sheet** — https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html — Extension/MIME/size validation, storage outside webroot and archive handling for uploads.
- **S10 RFC 4180 CSV Format** — https://www.rfc-editor.org/rfc/rfc4180 — Common CSV format and quoting semantics.

## 3. Record reconciliation

| Record | Disposition |
|---|---|
| `import-export.overview.cards` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.overview.quick-actions` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.export.scope` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.export.dependency-closure` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.export.metadata` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.export.revisions` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.export.inactive` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.export.module-settings` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.export.ui-preferences` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.export.connection-definitions` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.export.credential-placeholders` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.export.preflight` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.manifest.format` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.manifest.stable-identity` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.manifest.checksums` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.manifest.dependency-edges` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.type.catalog` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.container.safety` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.assets.portable` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.personal-data-exclusion` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.wizard` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.source` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.verify` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.compatibility` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.conflict-classes` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.resolution` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.semantic-diff` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.conflict-center` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.bulk-decisions` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.modes` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.free-pro` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.credential-required` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.revisions` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.package.import.history` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.data-import.list.columns` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.data-import.list.actions` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.data-import.identity` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.data-import.source-type` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.data-import.target` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.source.upload.limits` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.source.upload.archive` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.source.upload.storage` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.source.upload.encoding` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.parse.csv.delimiter` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.parse.csv.header` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.parse.csv.quote-escape` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.parse.csv.bom-empty` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.parse.csv.row-window` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.parse.json.root` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.parse.json.limits` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.parse.json.flatten` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.parse.xml.record` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.parse.xml.xxe` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.parse.sheet.options` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.parse.sheet.formulas` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.source.remote.connection` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.source.remote.pagination` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.source.remote.rate` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.preview.profile` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.preview.inference-confirm` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.mode.selection` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.match.key` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.match.normalization` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.match.preflight-unique` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.match.duplicate-source` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.mapping.row` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.mapping.null-semantics` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.transform.allowlist` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.transform.preview` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.transform.regex` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.mapping.taxonomy` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.mapping.relations` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.mapping.relations-deferred-pass` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.media.remote-fetch` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.media.options` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.user.mapping` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.user.role-allowlist` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.user.notification` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.user.password-policy` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.post.status-date-author` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.sync.missing-records` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.sync.ownership-scope` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.sync.ownership-marker` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.update.conflict-policy` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.dryrun.required` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.dryrun.counts` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.dryrun.drift` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.threshold.abort` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.batch.size` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.batch.checkpoint` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.run.states` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.run.row-result` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.run.failed-row-export` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.rollback.strategies` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.rollback.coverage` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.rollback.restore-point` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.schedule.import` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.schedule.revision-pin` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.list` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.source` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.fields` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.sensitive-gate` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.secret-exclusion` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.format.csv` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.format.json` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.format.xml-xlsx` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.csv-injection` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.destination` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.file.naming` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.file.options` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.file.encryption` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.incremental` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.preview` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.export.run-states` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.schedule.export` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.template.mapping` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.template.drift` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.adapter.detection` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.adapter.fidelity` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.adapter.ir-boundary` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.adapter.deactivation-verify` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.settings.temp` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.settings.upload-cap` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.settings.batch-defaults` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.settings.retention` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.settings.encoding` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.settings.remote-limits` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.settings.formula-protection` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.settings.failure-pause` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.settings.privacy-logging` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.perm.separation` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.perm.high-risk-reauth` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.ability.catalog` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.ability.ai-default` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.events.catalog` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.errors.degraded` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.perf.streaming` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.a11y.mapping-table` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.multisite.boundary` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.audit.trail` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.security.untrusted-input` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.security.no-unserialize` | MARKET_EVIDENCED / WPE_HARDENED |
| `import-export.native.post-crud` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `import-export.native.post-meta` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `import-export.native.terms` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `import-export.native.users` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `import-export.native.media-sideload` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `import-export.native.upload-checks` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `import-export.native.archive-unzip` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `import-export.native.slug-uuid` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `import-export.native.wxr` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `import-export.native.privacy-exporters` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `import-export.native.cron` | MARKET_EVIDENCED / NATIVE_SUBSTRATE |
| `import-export.native.kses` | MARKET_EVIDENCED_WITH_BOUNDS |
| `import-export.native.json-encode` | MARKET_EVIDENCED_WITH_BOUNDS |
| `import-export.native.wp-cli` | MARKET_EVIDENCED_WITH_BOUNDS |
| `import-export.exceed.signed-packages` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress import/export plugins |
| `import-export.exceed.blueprint-registry` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress import/export plugins |
| `import-export.exceed.semantic-risk-diff` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress import/export plugins |
| `import-export.exceed.rollback-coverage` | KEEP WPE_EXCEED — standards/specialist-evidenced, not common in WordPress import/export plugins |
| `import-export.deferred.encrypted-secrets` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `import-export.deferred.concurrent-workers` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `import-export.deferred.password-hash-migration` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `import-export.deferred.admin-list-snapshot` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `import-export.deferred.blueprint-sample-data` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `import-export.deferred.pro-deferred-objects` | KEEP DEFERRED — market demand or future need exists; separate design/certification required |
| `import-export.unsafe.php-eval` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.raw-sql` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.unserialize` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.private-table-write` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.xxe` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.archive-traversal` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.arbitrary-fetch` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.plaintext-passwords` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.role-elevation` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.silent-delete-sync` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.fuzzy-destructive` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.overwrite-all` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.secret-export` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.csv-injection` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.macro-exec` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.unbounded-batch` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.generic-rollback-claim` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.ai-destructive` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.free-pro-faking` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.local-id-identity` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.runtime-in-package` | REJECTED_UNSAFE — market pattern rejected |
| `import-export.unsafe.unredacted-pii` | REJECTED_UNSAFE — market pattern rejected |

Coverage: **188 / 188**; 124 Bank references; 6 extra dispositions; unresolved **0**; record delta after market audit **0**.

## 4. Rejected market patterns

- Executing inline custom PHP functions during import or export (E2, E3) → `import-export.unsafe.php-eval`, `import-export.transform.allowlist`.
- `unserialize()` of imported settings payloads (S1, S8) → `import-export.unsafe.unserialize`, `import-export.security.no-unserialize`.
- Deleting records missing from the source without an ownership marker or dry run (E2) → `import-export.unsafe.silent-delete-sync`, `import-export.sync.ownership-marker`.
- Overwrite-all imports of whole settings/widget files without per-item preview (S1, S2) → `import-export.unsafe.overwrite-all`, `import-export.package.import.resolution`.
- Exporting option tables or credentials in plaintext (S5) → `import-export.unsafe.secret-export`, `import-export.package.export.credential-placeholders`.
- Unescaped spreadsheet formulas in CSV exports (S6) → `import-export.unsafe.csv-injection`, `import-export.export.csv-injection`.
- XML external entity expansion and archive traversal (S7, S9) → `import-export.unsafe.xxe`, `import-export.unsafe.archive-traversal`.
- Plaintext password import and unrestricted administrator role assignment (S3) → `import-export.unsafe.plaintext-passwords`, `import-export.unsafe.role-elevation`.
- Full database push/pull with serialized find-and-replace (S5) is out of surface and owned by Surface 55 Staging/Migration with Surface 45 Transform.

No import, export, package build, runtime or product parity is authorized.
