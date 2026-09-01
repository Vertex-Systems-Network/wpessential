# Atomic Option Contract Integration V1

Status: shared implementation-contract infrastructure  
Trackers: #92, #96  
Current-main base for projection-sharding extension: `d8a04adb72b6d31fa037e46aec8e6121c46e1ab4`

## Purpose

The reviewed Options Bank is authoritative product research/inventory, but a Bank record is not automatically an authored setting or runtime schema property. This shared contract defines the machine boundary that converts `BANK_REVIEWED` source truth into implementation-ready Atomic Options without duplicating runtime, diagnostics, peer-owned or provider-owned semantics.

The lifecycle is:

`CAPABILITY_INVENTORY_COMPLETE → ATOMIC_INVENTORY_COMPLETE → OPTION_CONTRACT_COMPLETE → UX_CONTRACT_COMPLETE → PRODUCT_PLANNED → IMPLEMENTING → RUNTIME_CERTIFIED → PRODUCT_PARITY_CERTIFIED`.

`ATOMIC_INVENTORY_COMPLETE` means detailed inventory exists but schema-valid per-option contracts are still required before implementation. `OPTION_CONTRACT_COMPLETE` means the reviewed source has been projected with `missing = 0` and `unclassified = 0`; it does not authorize runtime by itself.

## Shared artifacts

- `config/product/option-contract.schema.json` — schema vocabulary.
- `config/product/atomic-option-contract-progress.json` — canonical 56-surface lifecycle truth.
- `config/product/option-contracts/<surface>.json` — surface-local contract instances when present.
- `config/product/option-contract-projections/<surface>/*.json` — optional surface-local source-projection shards for large reviewed Banks.
- `tests/Smoke/option-contracts-contract.php` — generic fail-closed validator.

The shared infrastructure itself never promotes a surface status.

## Source projection

A `BANK_REVIEWED` surface promoted to `OPTION_CONTRACT_COMPLETE` must include a `source_projection` covering the canonical reviewed Bank record count.

Each source record is classified using a `source_kind` and a matching `disposition`. The mapping is deterministic:

- `authored_option` → `AUTHORED_ATOMIC`;
- `user_preference` → `USER_PREFERENCE_ATOMIC`;
- `integration` → `INTEGRATION_ATOMIC`;
- `native_runtime` → `RUNTIME_IMPLEMENTATION_EVIDENCE`;
- `effective_state` or `diagnostic` → `EFFECTIVE_OR_DIAGNOSTIC`;
- `out_of_surface` → `OUT_OF_SURFACE_REFERENCE`;
- `compatibility_provider` → `COMPATIBILITY_PROVIDER_MAPPING`;
- `deferred` → `DEFERRED`;
- `rejected_unsafe` → `REJECTED_UNSAFE`;
- `wpe_exceed` → `WPE_EXCEED`.

A projection may map one source record to zero, one or multiple Atomic Option IDs. Zero mapping is valid when the disposition explains why the source record is runtime/effective/peer-owned instead of a local authored option. An out-of-surface record must name another canonical surface owner and must not map back to a local Atomic Option.

This prevents count-driven fake settings and duplicate semantic engines.

## Inline versus sharded projections

`source_projection` supports exactly one representation:

1. inline `entries`; or
2. `entry_files` containing repository-relative shard paths.

Using both or neither fails closed.

Sharding is intended for large reviewed surfaces such as Relations (144 records) and Fields (618 records). It changes file organization only; it does not weaken or change source-coverage semantics.

Valid shard paths are constrained to:

`config/product/option-contract-projections/<surface-key>/<shard>.json`

Paths containing traversal segments, cross-surface directories, duplicate paths, missing files or non-JSON names are rejected.

Each projection shard is a JSON object with:

- `schema_version: 1`;
- the exact `surface_id` and `surface_key` of the parent contract;
- `source_file` naming one canonical Bank shard for that surface under `config/product/options-bank/`;
- `entries`, using the same `sourceProjectionEntry` shape as inline projection entries.

The generic validator loads referenced shards deterministically, merges their entries, rejects duplicate source IDs across shard boundaries, and then applies the exact same kind/disposition/owner/Atomic-ID validation as the inline format.

`source_record_count` is checked against the merged entry count and, for `BANK_REVIEWED` surfaces, against `options-bank-progress.json`.

## Atomic Option minimum contract

An authored Atomic Option carries machine-readable semantics for:

- identity and feature grouping;
- parity disposition and requiredness;
- value type / allowed values / default behavior;
- dependencies;
- UI tier/control projection;
- server-authoritative validation and collision checks;
- storage owner/mode;
- runtime effect/mutation class/performance class;
- security class/capability;
- portability/migration policy;
- multisite scope where relevant;
- required verification evidence and MUST-NOT rules;
- official competitor/native evidence.

The contract is implementation input, not a replacement for owner-specific runtime validation.

## Generic validation rules

The shared smoke validator:

1. verifies the schema lifecycle and source-projection definitions;
2. verifies canonical 56-surface identity from the surface registry;
3. derives lifecycle counters from `atomic-option-contract-progress.json` and requires `truth` counters to match;
4. accepts zero contract instances without promoting any surface;
5. validates every discovered surface instance identity/status and rejects progress that outruns the instance;
6. verifies Atomic Option identity uniqueness and required server-authoritative/storage/runtime/security/portability/testing evidence boundaries;
7. recomputes coverage counters and rejects `OPTION_CONTRACT_COMPLETE` instances with `missing > 0` or `unclassified > 0`;
8. requires `BANK_REVIEWED` complete instances to carry source projection matching canonical Options Bank record count;
9. accepts exactly one projection representation: inline entries or validated surface-owned shards;
10. rejects duplicate projection source IDs globally across all shards;
11. enforces deterministic source kind/disposition pairing;
12. validates mapped Atomic Option references and canonical out-of-surface ownership.

No new Composer/runtime dependency is introduced.

## Ownership / no-bypass boundary

Canonical surface ownership remains authoritative. A surface contract must reference peer-owned semantics rather than cloning them. In particular:

- Relations owns persistent relation/cardinality/pivot semantics;
- Fields owns Field schema/control semantics;
- Query owns structured query/data-source semantics;
- Columns owns list-table presentation definitions;
- Roles/shared Policy owns authorization semantics.

`OPTION_CONTRACT_COMPLETE` must not be achieved by inventing duplicate authored controls for derived/native/internal/peer-owned records.

## Change impact

**Affected:** product contract schema, generic contract validation, future surface projection organization.  
**Unaffected:** runtime PHP modules, database schemas, WordPress registrations, current Options Bank content/counts and surface lifecycle statuses.  
**Risk:** malformed shard paths or duplicate mappings could falsely advance implementation truth; validation fails closed before lifecycle promotion.  
**Migration:** none; inline projections remain valid and no existing contract is forced to shard.  
**Rollback/Recovery:** revert the sharding extension; no product/runtime data is mutated.  
**Verification:** exact-head architecture, PHP quality, Platform Compatibility, Browser E2E and distributable gates as path-applicable, plus downstream real sharded Surface contract execution.

## Relations Gate B dependency

Surface 4 must derive and certify `config/product/option-contracts/relations.json` from its 144-record `BANK_REVIEWED` source before Relations runtime implementation begins. Its projection may be sharded by canonical Bank source file so every source record remains reviewable without creating a monolithic contract file.
