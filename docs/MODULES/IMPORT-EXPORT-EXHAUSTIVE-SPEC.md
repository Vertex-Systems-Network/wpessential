# WPEssential — Import / Export Exhaustive Option Specification

Status: **Phase 0 exhaustive product specification / no implementation authorized**

## 1. Product boundary

Two separate systems live under this module:

1. **WPE Configuration Packages** — versioned WPE definitions/configuration, stable UUIDs, dependency graph, revisions and compatibility negotiation.
2. **Runtime Data Import/Export** — posts/users/terms/custom tables/relations/media and other supported data-source records.

They share run/log/job UX but do not share destructive semantics blindly.

---

# 2. Screens

- Overview
- Configuration Packages
- Data Imports
- Data Exports
- Mapping Templates
- Runs / History
- Sources / Connections shortcut
- Settings
- Diagnostics

---

# 3. Overview

Cards:
- recent imports
- recent exports
- failed/partial runs
- scheduled imports/exports
- unresolved mapping conflicts
- source adapters needing attention
- storage/temp-space health

Quick actions:
- Import WPE package
- Export WPE package
- New Data Import
- New Data Export
- View failed rows

---

# 4. Configuration package export

Uses `PORTABLE-CONFIGURATION-PACKAGE-FORMAT.md` as canonical contract.

## Scope selector
- All WPE definitions
- Selected suites
- Selected modules
- Selected definitions
- Include transitive dependencies — default on
- Include optional dependencies — explicit

## Options
- Package name
- Description
- Environment/source label
- Include revisions: current only / published + current / selected history
- Include inactive/archived definitions
- Include local UI preferences only if explicitly portable
- Include module settings
- Include non-secret Connection definitions
- Include credential placeholders — default yes when referenced
- Include actual secrets — **not available in normal export**

## Preflight
- dependency graph
- missing/broken definitions
- Pro-module definitions
- unsupported schema versions
- package estimated size
- secrets excluded summary

## Output
- versioned manifest
- stable UUIDs
- schema/version metadata
- checksums
- dependency edges
- definition payloads
- credential placeholders
- no local numeric DB IDs as portable identity

---

# 5. Configuration package import

Steps:
1. Select package
2. Verify structure/checksums/schema
3. Compatibility analysis
4. Dependency/conflict mapping
5. Semantic diff
6. Import strategy
7. Dry run
8. Execute
9. Verification/report

## Source
- local upload
- existing server/import library item
- trusted Connection/storage object through adapter
- URL import only through Connections/SSRF policy; not arbitrary `file_get_contents()`

## Conflict statuses
- exact existing UUID/revision
- update available
- local newer
- same UUID divergent history
- key/slug collision different UUID
- dependency missing
- module unavailable
- Pro definition on Free-only site
- unsupported newer schema

## Resolution per definition
- Skip
- Update existing
- Create as clone/new UUID
- Map to existing compatible definition
- Defer until module/dependency available
- Abort package

Destructive replace is never hidden default.

## Free-only site behavior
Pro definitions remain visible in import report and may be stored as deferred/read-only package records only if architecture permits; never silently discarded or falsely activated.

---

# 6. Data Imports list

Columns:
- Name
- Target data source/entity
- Source format/type
- Mode create/update/sync
- Schedule
- Last run
- Last result
- Rows processed/failed
- Source connection/file
- Updated
- Health
- Actions

Actions:
- Edit
- Run
- Dry run
- Preview source
- Duplicate
- View Runs
- Export definition
- Pause schedule
- Archive/Delete

---

# 7. Create Data Import — identity

Fields:
- Name required
- Key stable
- Description
- Status Draft/Enabled/Paused/Archived
- Tags optional
- Source type
- Target data source/entity

---

# 8. Source types

Core candidates:
- CSV
- JSON
- XML
- XLSX/Excel through reviewed parser
- Google Sheets through certified Connection/provider
- remote REST/API through Connection
- another WPE/WordPress data source through adapter
- certified source-plugin migration adapter

Not standard:
- arbitrary PHP script
- raw SQL file executed without controlled import semantics

---

# 9. File upload source

Fields:
- file
- max size according server/module policy
- encoding detection/selection
- compressed source ZIP/GZIP only through safe archive limits and supported format
- one-shot upload vs saved source file retention

Security:
- MIME/extension/size checks
- archive traversal/bomb limits
- import files stored private/non-executable
- cleanup retention

---

# 10. CSV parsing options

- Encoding: UTF-8 default / selectable approved encodings
- Delimiter: comma / semicolon / tab / pipe / custom one-character bounded
- Header row yes/no
- Header row number
- Quote/enclosure character
- Escape semantics from selected parser
- Trim BOM — default yes
- Empty lines skip — default yes
- Comment rows optional only if explicit syntax
- Row start/limit for testing

Preview shows parsed columns/first bounded rows before mapping.

---

# 11. JSON parsing options

- Root path / collection selector
- Object vs array source validation
- Nested path notation using controlled selector grammar
- Flatten preview optional
- Maximum depth
- Maximum items per source object
- Missing path behavior

No arbitrary JavaScript/JMESPath-like expression unless a reviewed bounded selector engine is adopted.

---

# 12. XML parsing options

- Record element/path selector
- Namespace mappings
- attribute/text extraction
- maximum depth/entity/security settings

XXE/external entity expansion disabled. Do not fetch external DTD/resources.

---

# 13. Spreadsheet options

- Sheet selector
- Header row
- start row
- formula handling: import evaluated cached value only candidate; never execute arbitrary spreadsheet macro
- date serial interpretation/timezone
- merged-cell warning

Parser/library choice remains technical evidence work.

---

# 14. Remote API source

Uses Connections Manager.

Options:
- Connection
- request profile
- pagination strategy: page/cursor/next-link provider adapter
- response collection path
- source item ID field
- rate/retry profile
- incremental cursor/watermark field

No secrets typed into Import definition.

---

# 15. Source preview/profiling

Preview bounded sample:
- source fields/paths
- inferred candidate types
- null/empty counts sample
- duplicate sample values for proposed match key
- date/number parsing issues
- source encoding warnings
- media URL fields
- nested collections

Inference is suggestion only; administrator confirms target mapping.

---

# 16. Target entity selection

Supported via Data Source Registry:
- posts/CPTs
- users
- terms
- comments/media where adapter supports
- custom tables
- Membership import through dedicated migration/runtime adapters
- WPE module/runtime data only through owning module importer

Do not direct-write another module's private tables from generic importer.

---

# 17. Import mode

- Create only
- Update only
- Create or update / upsert
- Synchronize selected dataset

`Synchronize` is high-impact because missing source rows can change/delete target records.

---

# 18. Match / unique identity

Target matching options:
- WordPress ID only for same-environment controlled imports; warning not portable
- slug
- email for users with privacy/security constraints
- stable external/source ID stored in mapped field
- custom unique field(s)
- composite key
- certified adapter identity

Fields:
- match one/all key parts
- case sensitivity according target semantics
- trim/normalization
- duplicate target handling

Preflight detects non-unique target key before run.

---

# 19. Duplicate source handling

If multiple source rows share same match key:
- fail duplicates — default safest
- first wins
- last wins
- merge only through explicit field aggregation strategy

Report duplicate row numbers/identities.

---

# 20. Field mapping row

Per mapping:
- source field/path
- target field/property/meta
- source type
- target type
- transform pipeline
- required target value
- empty/null policy
- create/update applicability
- fallback/default
- validation status

Mapping uses target Field/Data Source schema; no arbitrary mass assignment.

---

# 21. Empty/null semantics

Per field:
- source missing → Ignore target — default
- source missing → Clear target
- empty string → keep / set empty / set null where target supports
- explicit null → keep / clear / null
- zero/false never confused with empty

Preview shows examples because silent clearing is dangerous.

---

# 22. Transformation pipeline

Allowlisted typed transforms only, candidates:
- trim
- case conversion
- cast integer/number/boolean
- date parse/format/timezone
- split string → list
- join list → string
- replace exact text
- bounded regex replace after security/performance review
- prefix/suffix
- map source enum/value → target enum
- JSON decode bounded
- HTML sanitize/strip tags
- phone/email/URL normalization
- lookup another entity/query
- registered SDK transform

No inline PHP/eval.

Each transform preview shows before/after sample.

---

# 23. Taxonomy mapping

Options:
- source terms delimiter/list
- map by term ID only controlled same-site
- map by slug/name
- create missing terms yes/no
- parent hierarchy mapping
- selected taxonomy
- replace existing terms / append / leave unchanged

Creating missing taxonomy terms requires target capability and validates hierarchy cycles.

---

# 24. Relations mapping

Options:
- target Relation definition
- source related identity field
- lookup source/target entity
- one/many handling
- attach mode replace/append
- missing related entity: error / skip relation / defer relation second pass

Use relation engine, not raw serialized meta.

Many-to-many large imports need batch/deferred relation linking.

---

# 25. Media import

Source types:
- existing media ID controlled same-site
- local/imported file reference
- remote URL through approved HTTP fetch security

Options:
- download remote media yes/no
- allowed schemes/hosts through SSRF policy
- max bytes
- MIME allowlist
- image dimension policy
- filename strategy
- duplicate media match: checksum/source URL/external ID candidate
- set featured image
- gallery field mapping
- attachment title/alt/caption mapping

Remote URL does not bypass SSRF/private-network/file checks.

---

# 26. User import

Fields/options:
- email/username mapping
- existing user match
- display name
- user meta through allowlist
- initial role allowlist low-privilege
- send account/reset notification
- password handling: do not import plaintext passwords from arbitrary CSV by default
- password hashes only through certified same-system migration if compatible and explicitly supported

Administrator-equivalent roles require dedicated high-risk permission and migration evidence.

---

# 27. Status/date/author mapping

Posts:
- status mapping allowlist
- publish date/timezone
- modified date preservation optional controlled
- author lookup/map/fallback
- slug
- parent/hierarchy
- comment status/ping status where relevant

Invalid status or capability falls back/error according mapping policy, never silently publish private content.

---

# 28. Missing source records in Synchronize mode

Options:
- Do nothing — safest default
- Archive/trash target records missing from source
- Delete permanently — exceptional high-risk
- set status/field value
- detach relation

Scope requires explicit source ownership marker so WPE does not delete unrelated manually created target records.

Sync must only manage records previously associated with this Import/source identity unless administrator explicitly widens scope with high warning.

---

# 29. Ownership marker

Candidate hidden/runtime metadata records:
- Import definition UUID
- source item stable ID
- last source hash/version
- first/last imported timestamp

Used for deterministic re-import, sync missing records and conflict detection.

Never rely solely on title/email fuzzy matching for destructive sync.

---

# 30. Conflict policy on update

When target changed locally after previous import:
- Source wins
- Target/local wins
- Field-by-field policy
- Conflict/report and skip — safe candidate for managed sync where change tracking exists

Exact change detection uses stored source fingerprint/version + target revision where available.

---

# 31. Dry run

Required/strongly encouraged before first destructive import.

Shows counts:
- source rows
- create
- update
- unchanged
- skip
- conflicts
- validation errors
- missing relations
- media downloads
- deletes/archives due sync
- role/status sensitive changes

Dry run does not write target runtime data except temporary analysis records cleaned by retention policy.

---

# 32. Validation thresholds

Run options:
- abort before import if error count > N
- abort if error percentage > X
- continue valid rows
- stop on first error

Destructive sync candidate default: abort when preflight has unresolved mapping/identity errors.

---

# 33. Batch/chunking

Fields:
- batch size Auto default / bounded manual
- memory/time diagnostics
- concurrent workers candidate only after idempotency/locking proof

Checkpoint state records:
- source cursor/row
- processed count
- source checksum/version
- mapping revision

Resume only if source/mapping compatibility still matches; changed source file may require restart/new run.

---

# 34. Import run states

- queued
- analyzing
- dry_running
- waiting_confirmation
- importing
- linking_relations
- processing_media
- finalizing
- completed
- completed_with_errors
- failed
- cancelled
- rollback_available
- rolled_back

---

# 35. Per-row result

Store bounded diagnostic:
- source row/item ID
- target record ID/UUID safe reference
- action create/update/skip/delete
- status
- error codes
- changed field keys summary

Do not persist full PII source row forever by default.

Failed-row export may include source data only with proper capability/privacy controls.

---

# 36. Rollback

Generic import rollback is possible only for changes WPE records sufficiently.

Candidate strategies:
- delete records created by run if no later external changes or with warning
- restore pre-change field snapshot for updated records where captured
- restore trashed records
- undo relation changes from recorded delta

Limitations:
- external side effects/emails/provider calls may not be reversible
- media downloads/files may need separate cleanup policy
- later user edits after import make automatic rollback dangerous

UI must show rollback coverage percentage/limitations before import.

For high-impact sync, optional Backup restore point integration may be recommended/required.

---

# 37. Scheduled imports

Schedule uses Cron/Job Service.

Options:
- enabled
- recurrence
- source must be persistent Connection/path, not temporary browser upload
- overlap policy skip/queue
- dry-run only schedule optional
- failure threshold/pause
- notification on failure

Schedule references published Import revision; changing mapping does not mutate already-running run.

---

# 38. Data Exports list

Columns:
- Name
- Source/query
- Format
- Destination
- Schedule
- Last run/result
- row count/bytes
- Updated
- Actions

Actions:
- Edit
- Run
- Preview
- Download latest if authorized
- Duplicate
- Runs
- Pause schedule
- Export definition
- Archive/Delete

---

# 39. Export source

Options:
- Data Source entity type
- Query Builder definition
- current admin filtered list snapshot candidate
- selected records explicit

Query/source policy checked server-side.

---

# 40. Export fields

Per field:
- output column/key
- source property/token
- formatter
- nullable/default
- privacy classification
- relation flattening strategy

Sensitive fields require additional capability; P3 secrets unavailable.

User passwords/hashes/application passwords/tokens never generic export fields.

---

# 41. Export formats

Core candidates:
- CSV
- JSON
- XML
- XLSX through reviewed writer
- WPE package is separate config export mode

Format settings:
CSV:
- delimiter
- quote/escape
- header
- encoding UTF-8 default
- line ending
- BOM optional for spreadsheet compatibility

JSON:
- array / newline-delimited JSON candidate for large data
- pretty print off for large output

XML:
- root/record element safe names

XLSX:
- sheet name
- column formats

---

# 42. CSV spreadsheet injection protection

Any cell beginning with spreadsheet formula control characters (`=`, `+`, `-`, `@`, tab/CR depending policy) is escaped/sanitized according to export security policy unless trusted developer mode explicitly requests raw machine data.

Never sacrifice security silently for Excel convenience.

---

# 43. Export destination

- Browser/manual download
- server private file
- Backup/Storage destination adapter
- SFTP/WebDAV/S3/etc. through Connections/Backup storage adapter reuse
- email notification containing secure link, not giant attachment by default
- API/webhook destination only via registered Connection action

Destination credentials remain Vault-managed.

---

# 44. Export file options

- filename template tokens allowlisted
- compression: none / ZIP/GZIP where supported
- archive encryption only via reviewed Backup/file-encryption contract if reused, not ad-hoc password ZIP
- split files after rows/bytes
- retention
- overwrite strategy: never / version / explicit replace

Filename tokens sanitized against path traversal.

---

# 45. Incremental export

Optional after source supports stable cursor/change marker.

Options:
- modified-after watermark
- numeric/UUID cursor
- include deletes/tombstones only if source tracks them
- state checkpoint

Do not claim complete incremental sync when source cannot detect deletes/old modifications reliably.

---

# 46. Export preview

Bounded rows with:
- selected fields
- transformed output
- masking/redaction
- estimated total rows/size
- query warnings

Preview is not full export in browser memory.

---

# 47. Export run states

- queued
- querying
- writing
- compressing/encrypting
- uploading
- verifying destination
- completed
- completed_with_warnings
- failed
- cancelled
- pruned

For remote destination, completed means according to selected verification tier, not only HTTP success.

---

# 48. Mapping templates

Save reusable source→target mapping definition only when source/target schema identities compatible.

Fields:
- name/key
- source schema fingerprint
- target type/schema version
- mappings/transforms
- defaults
- match policy

Applying template to changed source shows invalid/missing mappings, not silently shifts columns by position.

---

# 49. Source migration adapters

Source-specific migration uses `SOURCE-MIGRATION-ADAPTER-REGISTRY.md`.

Adapter UI shows:
- source product/version detected
- certified version range
- domains supported: definitions/runtime/billing refs/etc.
- fidelity summary
- unsupported/lossy items
- migration order
- source plugin deactivation readiness

Adapter never writes target DB directly; it maps to neutral IR/import service.

---

# 50. Source deactivation verification

After migration candidate:
- compare source/target counts/identities
- access/render/query reference app checks later
- identify still-live dependencies on source plugin
- do not tell admin “safe to deactivate” unless required fidelity checks pass

Source remains untouched by default until administrator chooses cleanup separately.

---

# 51. Settings

- temp directory
- temp file retention
- max upload bytes bounded by server
- default batch size Auto
- maximum batch cap
- run history retention
- failed row retention
- source file retention
- default encoding
- remote download max bytes
- media download timeout/size
- spreadsheet formula protection on
- scheduled import failure pause threshold
- export file retention
- privacy logging level

---

# 52. Permissions

Separate:
- read definitions/runs
- create/update imports/exports
- run dry-run
- execute import
- execute destructive sync/delete
- rollback
- view/export failed PII rows
- export sensitive data
- import users/roles
- manage scheduled runs
- config package import/export

Destructive sync and administrator-equivalent user changes require dedicated high-risk capability/re-auth candidate.

---

# 53. Abilities

Candidate:
- import list/get/create/update/validate/preview/dry-run/run/cancel/rollback
- export list/get/create/update/preview/run/cancel
- run list/get
- package analyze/import/export
- mapping template list/get/create/update
- source adapter detect/analyze

AI default:
- analyze/preview/mapping suggestions/dry-run explanation
- no destructive import/sync/rollback/export-sensitive-data by default.

---

# 54. Events

- import run started/completed/partial/failed/cancelled/rolled_back
- export run started/completed/failed
- package analyzed/imported
- source migration conflict detected
- scheduled import paused after failures

Do not emit one generic workflow event per million rows by default.

---

# 55. Error/degraded states

- source changed since preview
- mapping schema stale
- encoding error
- malformed source
- source adapter version unsupported
- target dependency missing
- low disk/temp space
- media fetch blocked/failed
- relation target missing
- role/status permission denied
- remote rate limit
- run checkpoint corrupted
- rollback partial/unavailable
- Pro expiry → definitions read-only; active scheduled mutating imports pause safely

---

# 56. Performance

- streaming parsers/writers where possible
- no full large file in memory
- bounded preview
- chunk DB writes
- batch relation/media work
- indexes/match key diagnostics before upsert
- no N+1 entity lookup when batchable
- asynchronous scheduled/large runs
- backpressure for remote APIs
- temp disk monitoring

---

# 57. Assets/accessibility

Assets only Import/Export screens.
Mapping table supports keyboard navigation/reordering.
Progress includes text/count/status, not color only.
Large run live updates bounded polling/shared job UI, not high-frequency global AJAX.
Errors link to row/mapping with focus-safe behavior.

---

# 58. Future tests

After consent:
- CSV encodings/delimiters/quotes
- malformed JSON/XML + XXE rejection
- XLSX formulas/macros ignored safely
- source identity duplicates
- zero/false/null semantics
- user role escalation
- remote media SSRF
- relation second-pass
- sync missing-record ownership guard
- source changed after dry run
- resumable import checkpoint
- concurrent scheduled overlap
- rollback after later local edit
- CSV formula injection
- million-row streaming fixture
- source adapter exact/lossy reports
- Free-only Pro package behavior
- secrets excluded from package
- privacy/failed-row access

## Maturity

Import / Export is now **Exhaustive option spec** at Phase 0 product level. Parsers, physical run tables, rollback implementation and source adapters remain technical/consent-gated.