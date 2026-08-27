# WPEssential — Source Migration Adapter Registry Contract

Status: **Phase 0 planning — no source adapter implementation authorized**

## Purpose
Make migrations maintainable across changing third-party plugin versions. A migration source is an explicit versioned adapter, not scattered `if plugin_exists()` logic.

## Adapter manifest

Every source adapter must declare:

- `adapter_id` — e.g. `acf`, `scf`, `metabox`, `jetengine`, `pmpro`, `memberpress`, `wc-memberships`;
- adapter version;
- source product name/vendor;
- source plugin slug/package identifiers;
- certified source version range(s);
- supported WordPress/PHP context where material;
- discovery methods;
- artifact formats accepted;
- live API capability requirements;
- domains supported (definitions, data, membership, billing refs, etc.);
- certification level;
- source entities recognized;
- fidelity limits;
- known unsupported add-ons/features;
- external dependencies;
- privacy/sensitive-data classes;
- import prerequisites;
- rollback support class;
- source-deactivation verification support;
- fixtures/test-suite version;
- documentation/research references.

## Detection confidence

Discovery returns one of:
- `confirmed` — plugin/version/artifact signature clearly identified;
- `probable` — strong clues but version/product ambiguity remains;
- `unknown` — cannot safely identify.

Only `confirmed` sources may use automatic versioned conversion. `probable` requires user confirmation and may remain dry-run-only.

## Version support policy

Support is explicit, for example:
- `>=6.5 <6.9` certified;
- `6.9.x` certified separately if schema changed;
- newer unknown version → scan/read-only, no automatic write migration unless backward-compatible evidence exists.

Never assume future source versions remain compatible.

## Artifact parser vs live adapter

A source can expose multiple readers:

### Artifact reader
Reads exported JSON/CSV/ZIP/XML. Preferred when official and sufficient.

### Live API reader
Uses documented plugin/public WordPress APIs to discover registered definitions and values.

### Storage reader
Version-gated read-only inspection of source DB/options only where public/export APIs cannot provide required migration data.

Storage reader is the last choice and has the strictest compatibility certification.

## Normalized reader contract

Reader produces source-neutral IR records containing:
- source entity type;
- source identity;
- parent/dependency identities;
- schema version;
- normalized semantic payload;
- original setting metadata required for diagnostics;
- privacy class;
- source location/reference;
- fidelity hints;
- warnings/errors.

Reader never writes target state.

## Mapper contract

Mapper consumes IR and target context, returning:
- target domain/module;
- proposed target identity;
- target payload;
- fidelity class;
- conflicts;
- unresolved dependencies;
- transformation notes;
- validation errors;
- required user decisions.

Mapper also never writes durable target state.

## Executor contract

Only generic Import/Export Engine executes approved mapped operations through owning module/Data Source APIs. Source adapters do not bypass target validation or write target DB tables directly.

This separation prevents source-specific code from becoming a privileged target-data backdoor.

## Adapter capabilities

Potential adapter feature flags:
- `discover`
- `definitions`
- `runtime_data`
- `relationships`
- `users`
- `memberships`
- `external_billing_refs`
- `media_refs`
- `presentation_artifacts`
- `incremental_reimport`
- `rollback_metadata`
- `source_deactivation_check`

Marketing support claims derive from these certified flags.

## Per-source planned registry

| Adapter | Initial target certification | Planned domains | Important boundary |
|---|---|---|---|
| `cptui` | Level 1 Definitions | CPT, Taxonomy | runtime posts/terms are separate WordPress data |
| `acf` | Level 1 then Level 2 | Fields, CPT, Taxonomy, Options Pages, supported values | PHP/local definitions may not exist as editable DB records |
| `scf` | Level 1 then Level 2 | Fields, CPT, Taxonomy, Options Pages, values | detect separately from ACF despite API ancestry |
| `metabox` | Level 1 then Level 2 | Fields, CPT/Taxonomy where certified, values | Builder export settings != values |
| `jetengine` | staged Level 1/2/3 | CPT, Taxonomy, Fields, Relations, Queries, CCT, Options, Listings | structural Skin != all runtime records |
| `wc-memberships` | Level 3 membership after Membership engine accepted | Plans, rules, memberships, benefits, Woo billing refs | membership CSV != recurring subscription migration |
| `pmpro` | Level 3 membership | levels/groups, memberships, user fields, subscription refs | status/history and multiple levels need semantic normalization |
| `memberpress` | Level 3 membership | memberships/groups/rules, transactions/access, subscription refs | member access vs subscription rebill state are distinct |

## Adapter package location

Future architecture preference:
- first-party certified adapters may ship in Pro or optional integration packages;
- rarely used/heavy adapters should be optional packages to avoid runtime/bundle/support cost;
- all use one public Source Adapter SDK contract;
- Free plugin should not load dormant Pro migration adapter code.

Exact packaging is later distribution design.

## Failure classes

Adapter-specific normalized errors include:
- `source_not_found`
- `source_version_unsupported`
- `artifact_invalid`
- `artifact_schema_unknown`
- `source_dependency_missing`
- `source_permission_denied`
- `source_data_inconsistent`
- `mapping_unsupported`
- `mapping_lossy_requires_approval`
- `target_conflict`
- `external_reference_unverified`

No raw SQL/table/path details leak to ordinary users; diagnostics can show safe developer context.

## Security

Adapters are trusted first-party/installed extension code, but source data remains untrusted.

Adapter cannot:
- request arbitrary PHP eval;
- bypass target capabilities/policies;
- decrypt/display WPE Secrets Vault values;
- execute source-provided callbacks/code;
- auto-call remote billing mutation APIs during ordinary migration;
- auto-uninstall source plugin;
- silently fetch arbitrary remote URLs.

## Certification evidence

Each certified source-version range has a fixture pack with:
- source artifact fixtures;
- expected IR snapshots;
- expected mapped target definitions/data;
- source→target ID maps;
- fidelity/warning expectations;
- malicious/malformed fixtures;
- re-import fixture;
- rollback fixture where supported;
- source-deactivation checks.

## Deprecating adapter support

If a source plugin version becomes unsupported:
- existing migrated target data is unaffected;
- adapter remains capable of reading its historical import manifest where needed;
- support removal has a documented release note;
- no deletion of source metadata needed for historical reconciliation;
- newer adapter versions may supersede mapping rules without rewriting completed historical imports automatically.

## Development gate

No adapter plugin package, parser, source DB inspection or fixture execution is authorized before explicit owner consent under ADR-0014.