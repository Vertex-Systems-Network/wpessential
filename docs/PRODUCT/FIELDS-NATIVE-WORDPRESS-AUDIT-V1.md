# Fields Native WordPress Audit V1

Snapshot: 2026-09-01

Surface: 3 — Fields / Field Groups / Control Registry

Target platform: WordPress 7.1-era public/supported contracts, including compatibility behavior retained by current core.

Decision: **`NATIVE_AUDITED` candidate — subject to exact-head CI certification**

This audit closes the native-platform blocker identified by `FIELDS-BANK-REVIEW-V1.md`. It is deliberately narrower than market parity: `MARKET_AUDITED` and `BANK_REVIEWED` remain open.

## Audit method

The native audit separates three things that should not be conflated:

1. **authored/product-relevant primitives** → normalized Bank records;
2. **legitimate executable extension points** → registered provider mappings rather than arbitrary callback text;
3. **runtime registry helpers, internal core sources, and adjacent platform APIs** → explicit dispositions without manufacturing fake Fields options.

The machine-readable source of truth is:

`config/product/options-bank-audits/fields-native-wordpress.json`

It contains **61 disposition items** with zero unresolved items.

## Primary WordPress sources

- `register_meta()` — https://developer.wordpress.org/reference/functions/register_meta/
- `is_protected_meta()` — https://developer.wordpress.org/reference/functions/is_protected_meta/
- `get_registered_meta_keys()` — https://developer.wordpress.org/reference/functions/get_registered_meta_keys/
- `unregister_meta_key()` — https://developer.wordpress.org/reference/functions/unregister_meta_key/
- `register_block_bindings_source()` — https://developer.wordpress.org/reference/functions/register_block_bindings_source/
- Block Bindings handbook — https://developer.wordpress.org/block-editor/reference-guides/block-api/block-bindings/
- `@wordpress/blocks` — https://developer.wordpress.org/block-editor/reference-guides/packages/packages-blocks/
- supported Block Bindings attributes — https://developer.wordpress.org/reference/functions/get_block_bindings_supported_attributes/

## Metadata API audit

Current `register_meta()` directly exposes or governs:

- object type and metadata key;
- object subtype;
- type;
- label;
- description;
- single/multiple storage semantics;
- default value;
- sanitization callback;
- authorization callback;
- REST exposure;
- custom REST schema and array item schema;
- REST prepare callback;
- revisions support;
- deprecated pre-4.6 callback forms retained for compatibility.

The function also has material native behavior around:

- subtype-specific registrations partly overriding object-type-wide registrations;
- registered default values being validated against the effective schema;
- revisions being restricted to post metadata;
- subtype metadata revisions requiring the post subtype to support revisions;
- authorization falling back to `is_protected_meta()` when no auth callback is supplied;
- registration arguments being filterable through `register_meta_args`.

### Native gap delta

The previous Bank already covered most of the direct metadata contract. This audit adds only the proven missing primitives:

- registered metadata API description;
- registered metadata authorization provider;
- protected-key policy;
- subtype override precedence;
- default/schema validation rule;
- post-object requirement for metadata revisions;
- revision-capable subtype requirement;
- registered metadata arguments provider.

Convenience and registry functions such as `register_post_meta()`, `register_term_meta()`, `registered_meta_key_exists()`, `get_registered_meta_keys()`, `get_registered_metadata()`, `unregister_meta_key()`, and `sanitize_meta()` are dispositioned as runtime/system APIs where appropriate rather than being turned into user controls.

## Block Bindings audit

Server registration is normalized across:

- namespaced source identity;
- source label;
- server value provider;
- source context requirements;
- native source-name validation;
- registration lifecycle timing;
- post-resolution `block_bindings_source_value` filtering.

Editor registration is normalized across:

- matching source identity;
- label and editor-side label precedence;
- context;
- value reads;
- value writes;
- edit authorization;
- native default read-only behavior;
- WordPress 6.9+ `getFieldsList` selector integration.

Server/editor registry lookup and unregister helpers are classified as system/runtime lifecycle APIs, not authored Fields options.

### Supported block attributes ownership

WordPress 6.9 introduced public supported-attribute extensibility and WordPress 7.1 added List Item support. These APIs determine which **block attributes** can participate in bindings; they are not field-definition options. The audit therefore assigns the helper and its global/per-block filters to canonical `platform` ownership rather than inflating Surface 3.

### Built-in sources

- `core/post-meta` is Fields-relevant and remains mapped to Fields records, including protected-key and REST-registration restrictions.
- `core/post-data`, `core/term-data`, and `core/pattern-overrides` are explicit core/platform dispositions, not Fields configuration.
- generic `register_rest_field()` is assigned to the canonical `rest-api` surface; Fields owns registered-metadata projection, not arbitrary REST object-field registration.

## 13-record native delta

The audit adds **13** genuine missing Bank records in `fields--native-audit-v1.json`.

Previous truth:

- Fields: 596
- total Bank: 774

Candidate truth after this audit:

- Fields: **609**
- total Bank: **787**
- seeded surfaces: 3
- native-audited surfaces: **1**
- market-audited surfaces: 0
- bank-reviewed surfaces: 0

## Machine gate

`tests/Smoke/options-bank-native-audit-contract.php` validates:

- the native-audit schema is parseable;
- audit ownership is canonical Surface 3 / Fields;
- primary-source snapshot metadata exists;
- disposition IDs are unique;
- evidence URLs are primary `developer.wordpress.org` references;
- Bank/provider mappings resolve to real Fields Bank records;
- out-of-surface/internal items name a different canonical owner;
- critical Metadata and Block Bindings audit entries cannot silently disappear;
- declared disposition counters equal actual items;
- `NATIVE_AUDITED` is forbidden when any item remains `UNRESOLVED`.

The standard Options Bank and progress contracts continue to enforce record/path uniqueness, semantic relationships, shard counts, 56-surface progress truth, and lifecycle counters.

## Non-claims

- `NATIVE_AUDITED` does not mean market parity.
- Runtime/introspection APIs are not UI controls merely because WordPress exposes them publicly.
- Private/core sources are not copied into WPE architecture.
- Provider mappings preserve legitimate extension use cases without arbitrary executable admin configuration.
- The lifecycle promotion is valid only after the exact source head passes all applicable CI gates.

## Next gate

After exact-head CI certification, Surface 3 may truthfully remain at `NATIVE_AUDITED` while the next branch builds the provider-by-provider market capability coverage matrix required for `MARKET_AUDITED`.
