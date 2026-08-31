# Fields Market Ecosystem Audit V1

Snapshot: 2026-09-01

Surface: 3 — Fields / Field Groups / Control Registry

Decision: **`MARKET_AUDITED` candidate — subject to exact-head CI certification**

This audit closes the provider-by-provider market-coverage blocker identified by `FIELDS-BANK-REVIEW-V1.md`. Native WordPress coverage remains certified by `FIELDS-NATIVE-WORDPRESS-AUDIT-V1.md`. Final `BANK_REVIEWED` is intentionally not claimed here.

## Machine source of truth

`config/product/options-bank-audits/fields-market-ecosystem.json`

The matrix covers:

- **12 primary providers** across six canonical capability families;
- **15 specialist / long-tail ecosystems** with explicit direct or compatibility Bank mappings;
- **64 primary-provider family mappings**;
- **8 explicitly non-material primary-provider family cells**;
- **191 Bank-record references**;
- **7 extra implementation / ownership / safety dispositions**;
- **0 unresolved market dispositions**.

The six capability families are:

1. field definition;
2. composition and behavior;
3. data model and storage;
4. API and integration;
5. governance and editor UX;
6. portability and extensibility.

For primary providers, every family must be either mapped to canonical Fields Bank records or explicitly marked non-material for that provider's audited role. Specialist ecosystems must cite evidence and map to at least one real Fields Bank record. The smoke contract rejects silent omissions.

## Primary provider roster

- Advanced Custom Fields (ACF)
- Secure Custom Fields
- Meta Box
- Pods
- CMB2
- Carbon Fields
- Fieldmanager
- JetEngine
- Redux Framework
- ACF Extended
- Toolset Types
- ACPT

## Specialist / long-tail roster

- Smart Custom Fields
- WCK Custom Fields Creator
- atshift Fields / Custom Field Suite compatibility
- Modern Fields
- Native Custom Fields
- Codeideal Open Fields
- Massoftind Field Builder
- Cptify
- OneMeta
- OZY Custom Fields
- Field Forge
- YAML Custom Fields
- Meta Field Block
- Piklist
- CMB2 extension ecosystem

## Current 2026 recheck

The existing 609-record Fields Bank already covered most mature market semantics. Rechecking current provider documentation exposed only **nine genuine missing records**.

### ACF 6.8

Primary sources:

- https://www.advancedcustomfields.com/resources/schema-org-property-mapping/
- https://www.advancedcustomfields.com/resources/schema-org-output-formats/
- https://www.advancedcustomfields.com/resources/abilities-api/
- https://www.advancedcustomfields.com/changelog/

ACF 6.8 adds field-level Schema.org property mapping and output formatting plus per-item Abilities API access and AI descriptions. These are materially distinct from generic REST exposure, editor instructions, or the existing field-level WPE AI possibilities.

Added records:

- `fields.marketaudit.schema_org_property`
- `fields.marketaudit.schema_org_output_format`
- `fields.marketaudit.group_ai_access`
- `fields.marketaudit.group_ai_description`

The global ACF feature flags are assigned to canonical Settings ownership rather than duplicated as Fields controls. Automatic JSON-LD assembly and ACF's later Block Bindings datastore synchronization are implementation patterns once canonical mapping/binding settings exist, so they do not inflate the Bank.

### Pods REST API

Primary source:

- https://docs.pods.io/advanced-topics/rest-api/

Pods exposes per-field REST read and write controls independently, and relationship fields add response type plus traversal depth. Those semantics were not explicitly represented in the previous Bank.

Added records:

- `fields.marketaudit.rest_readable`
- `fields.marketaudit.rest_writable`
- `fields.marketaudit.relation_rest_response_type`
- `fields.marketaudit.relation_rest_depth`

The documented global-over-field REST precedence is treated as runtime policy resolution rather than another authored field option.

### ACPT custom data tables

Primary source:

- https://acpt.io/features/

ACPT currently advertises custom database tables with indexes and foreign keys. Existing Fields records already covered custom tables and indexing; explicit foreign-key integrity was missing.

Added record:

- `fields.marketaudit.custom_table_foreign_keys`

CCT endpoint lifecycle remains owned by the REST API / data-object surfaces rather than being duplicated into Fields.

## Other provider coverage

The market matrix also keeps explicit normalization coverage for Meta Box custom-table/group/clone patterns, Pods table-backed and relationship data, CMB2 reusable forms and extension ecosystem, Carbon Fields complex/association controls, Fieldmanager repeatability and validation/sanitization providers, JetEngine CCT/relations/query/glossary patterns, Redux design controls and option override semantics, ACF Extended governance/sync/block features, Toolset repeating groups/relationships, and the long-tail WordPress.org/GitHub ecosystems recorded in `FIELDS-ECOSYSTEM-BENCHMARK-REGISTER.md`.

The audit normalizes capabilities, not vendor implementations. Public/GPL sources may establish behavior and option semantics; proprietary, nulled, anonymous, or unverifiable mirrors do not establish implementation provenance.

## Count delta

Previous certified truth:

- Fields: 609
- total Bank: 787
- native-audited surfaces: 1
- market-audited surfaces: 0

Candidate truth after this audit:

- Fields: **618**
- total Bank: **796**
- seeded surfaces: 3
- native-audited surfaces: **1**
- market-audited surfaces: **1**
- bank-reviewed surfaces: 0

## Machine gate

`tests/Smoke/options-bank-market-audit-contract.php` validates:

- the market-audit schema remains parseable;
- canonical Surface 3 ownership;
- the exact six capability families;
- the exact 12-provider primary roster;
- the exact 15-provider specialist roster;
- evidence URL presence;
- every primary family is mapped or explicitly non-material;
- every Bank reference resolves to a real Fields record;
- every specialist provider has direct/compatibility Bank coverage;
- all nine current 2026 gap records stay attached to the required ACF, Pods, or ACPT provider/family cell;
- out-of-surface ownership resolves to another canonical surface;
- declared coverage counters equal actual matrix coverage;
- `MARKET_AUDITED` is forbidden with unresolved dispositions;
- the lifecycle audit and `options-bank-progress.json` cannot disagree.

The existing Bank, progress, semantic-relation, and native-audit contracts remain in the same smoke suite.

## Safety decisions

- No arbitrary executable PHP/JS callback configuration is adopted.
- CMB2-style callback extension freedom is represented through registered provider contracts; raw executable configuration is rejected.
- Unverified Git mirrors or redistributed premium packages are not accepted as audit evidence.
- Implementation patterns do not become duplicate options merely because a competitor exposes internal machinery.
- Cross-surface behavior is assigned to its canonical owner instead of silently expanding Fields.

## Non-claims

`MARKET_AUDITED` does not mean the Fields runtime is implemented or shipped. It certifies that the current native and market possibility inventory has explicit evidence/disposition coverage. WPE-future/exceed semantics still require the closing whole-surface review before implementation contracts can be generated.

## Next gate

After exact-head CI certification, the next pass is **Fields Bank Review V2**. That review must confirm:

1. native audit is still valid;
2. market audit is still valid;
3. semantic aliases/effective derivations remain closed;
4. WPE-future/exceed records are intentional and non-duplicative;
5. no unresolved or unexplained gap remains.

Only then may Surface 3 advance to `BANK_REVIEWED`.
