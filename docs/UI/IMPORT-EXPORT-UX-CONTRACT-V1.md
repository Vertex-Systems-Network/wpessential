# Import/Export — UX Contract V1

Surface: **26 / Import-Export**  
Planning issue: **#592**  
Supervisor wave: **#583**  
Exact-main claim anchor: `4350862f3f3397abdf64cc897abdb615faac4af5`

Lifecycle status: **UX_CONTRACT_COMPLETE (planning)**. Bank review #1454 accepted 188/188 records with zero unresolved; Atomic Option Contract: `config/product/option-contracts/import-export.json` (188/188 projected; 156 parity, 4 exceeds, 6 deferred, 22 rejected unsafe; zero missing/unclassified). Contract and UX completion are planning states only; they do not authorize import writes, export generation, upload parsing, remote fetch or any data/package mutation.

## Product model

Keep two workflows separate:

- **Configuration Package** — portable WPE definitions/settings/dependencies.
- **Data Import/Export** — runtime records through canonical Data Source/owner APIs.

They may share run/history UI but destructive conflict/update/delete semantics remain distinct.

## Information architecture

1. Overview
2. Configuration Packages
3. Data Imports
4. Data Exports
5. Mapping Templates
6. Runs / History
7. Sources / Connections shortcut
8. Diagnostics

## Progressive disclosure

### Essential

For imports:

- name/status;
- source type;
- target Data Source/entity;
- create/update/upsert mode;
- identity/match key;
- basic mappings;
- Preview / Dry Run / Validate.

For configuration packages:

- source/package;
- package verification summary;
- dependencies/conflicts;
- per-definition strategy;
- Dry Run.

No live Execute is authorized by this provisional contract.

### Advanced

- parser/source settings;
- null/empty behavior;
- taxonomy/relation/media mappings;
- typed transforms;
- duplicate policy;
- conflict policy;
- synchronize missing-source policy;
- batch/checkpoint/resume;
- validation/error thresholds;
- rollback coverage;
- Backup restore-point recommendation/requirement;
- dependency/usage summary.

### Expert

- certified source/migration adapters;
- owner-specific runtime importers;
- composite identities;
- high-risk sync delete/archive controls;
- protected role/status mappings;
- advanced export destinations;
- schedule/Job profile;
- environment/package compatibility diagnostics.

No arbitrary PHP/eval/SQL, unmanaged remote fetch, secret input, raw private-table mutation or unbounded batch controls.

### System / Diagnostics

Read-only diagnostics show:

- source fingerprint/version;
- parser/adapter health;
- target schema revision;
- mapping revision;
- dependency availability;
- conflict/duplicate counts;
- batch/checkpoint state;
- temp/storage health;
- Connection/Job health;
- rollback coverage/limitations;
- current site/network context.

## Configuration package import UX

Steps:

1. Select package
2. Verify manifest/checksum/schema
3. Compatibility analysis
4. Dependency/conflict mapping
5. Semantic diff
6. Per-definition strategy
7. Dry Run
8. Execute only in separately authorized runtime
9. Verification/report

Conflict states distinguish exact existing revision, update available, local newer, divergent history, key collision different UUID, missing dependency/module, unsupported schema and licensing/deferred states.

Destructive replace is never a hidden default.

## Data source preview

Before mapping, show bounded sample fields/paths, inferred types, null/empty issues, duplicate match-key candidates, date/number parsing problems, encoding warnings and nested/media candidates.

Inference is advisory. Administrator confirms target semantics.

## Identity and sync safety

Match controls emphasize stable source/external IDs, canonical unique fields or composite keys. WordPress numeric ID is marked same-environment only. Fuzzy/title matching is not a destructive identity mechanism.

Synchronize mode is high-impact. Missing-source delete/archive/status actions require an explicit ownership marker linking target records to this Import/source. “Do nothing” is the safest default.

## Mapping UX

Each row shows source, target, types, transform pipeline, required state, null/empty behavior, create/update applicability and validation status.

Transforms are typed/allowlisted and preview before/after samples. No inline code.

## Dry Run

Dry Run shows counts for create/update/unchanged/skip/conflict/error/missing relations/media downloads and destructive sync effects. It writes no target runtime data apart from bounded temporary analysis records.

Unresolved identity/mapping errors block destructive sync by default.

## Batch/resume

Run settings use Auto/bounded batch size. Checkpoint stores source cursor, source checksum/version and mapping revision. Resume is allowed only if those remain compatible; changed source/mapping forces restart/new run.

## Rollback UX

Before a high-impact import, show rollback coverage percentage and non-reversible effects. Generic rollback claims are forbidden where emails/provider actions/files or later user edits cannot be safely reversed.

Backup restore point integration can be recommended/required for destructive synchronization.

## Export UX

Source can be Data Source, Query or explicit selected records. Export fields are allowlisted with privacy classification.

Passwords/hashes/app passwords/tokens/secrets are not generic export choices.

CSV export applies spreadsheet formula-injection neutralization by default.

## Accessibility

- keyboard mapping/source/dry-run flows;
- accessible large mapping tables with row labels;
- focus after parse/validation/conflict actions;
- no color-only conflict/destructive states;
- clear count summaries and error links;
- responsive run/history tables;
- progress announcements without noise;
- axe coverage for import preview, conflicts, destructive sync warning and failed-row states once implemented.

## Multisite

Future contract must explicitly define site/network target scope, network users, site-local IDs, package dependencies, media/URL mapping, capabilities and export privacy. A subsite import cannot silently mutate network-global data.

## Portability

Configuration package manifests use stable UUIDs/schema versions/checksums/dependency edges and exclude secrets. Data Import/Export definitions carry source/target/mapping refs, not runtime secrets. Imported environments always rebind Connection/Vault credentials locally.

## Lifecycle decision

## Ownership and safety boundaries

Surface 26 owns import/export profiles, format parsing, field mapping, dry-run/preview, conflict and identity resolution, and outcome semantics. Surface 24 owns backup artifacts and restore; Surface 18 durable jobs/chunking; Surface 19 notifications; Surface 23 remote transport and credentials; Surface 55 staging/migration. Owner-module data is written only through owner-registered import adapters.

Uploads are size/type-validated and parsed without XXE, unsafe deserialization or formula injection; exports neutralize CSV formula cells and exclude secrets. Imports require dry-run preview and explicit confirmation; overwrite/delete/synchronize modes require a restore point by default.

This UX review is paired with the 188-record Atomic Option Contract and reviewed Bank. Runtime import/export remains separately gated.
