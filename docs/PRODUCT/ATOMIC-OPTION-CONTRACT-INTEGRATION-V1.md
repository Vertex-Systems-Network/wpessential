# Atomic Option Contract Integration V1

Status: shared implementation-contract infrastructure candidate  
Tracker: #92  
Current-main base: `14315a5454d836d7ee2b3a6558044224f7fd0dd4`

## Purpose

The reviewed Options Bank is authoritative product research/inventory, but a Bank record is not automatically an authored setting or runtime schema property. This shared contract defines the machine boundary that converts `BANK_REVIEWED` source truth into implementation-ready Atomic Options without duplicating runtime, diagnostics, peer-owned or provider-owned semantics.

The lifecycle is:

`CAPABILITY_INVENTORY_COMPLETE → ATOMIC_INVENTORY_COMPLETE → OPTION_CONTRACT_COMPLETE → UX_CONTRACT_COMPLETE → PRODUCT_PLANNED → IMPLEMENTING → RUNTIME_CERTIFIED → PRODUCT_PARITY_CERTIFIED`.

`ATOMIC_INVENTORY_COMPLETE` means detailed inventory exists but schema-valid per-option contracts are still required before implementation. `OPTION_CONTRACT_COMPLETE` means the reviewed source has been projected with `missing = 0` and `unclassified = 0`; it does not authorize runtime by itself.

## Shared artifacts

- `config/product/option-contract.schema.json` — schema vocabulary.
- `config/product/atomic-option-contract-progress.json` — canonical 56-surface lifecycle truth.
- `config/product/option-contracts/<surface>.json` — surface-local contract instances when present.
- `tests/Smoke/option-contracts-contract.php` — generic fail-closed validator.

This infrastructure tranche does not promote any surface status and does not contain a Surface 8/Columns instance from historical draft PR #52.

## Source projection

A BANK_REVIEWED surface promoted to `OPTION_CONTRACT_COMPLETE` must include a `source_projection` covering the canonical reviewed Bank record count.

Each source record is classified using a `source_kind` and `disposition`. Supported dispositions distinguish:

- authored Atomic Options;
- user preferences;
- integrations;
- effective/diagnostic state;
- native/runtime evidence;
- out-of-surface ownership references;
- compatibility/provider mappings;
- deferred behavior;
- rejected unsafe behavior;
- WPE-exceed behavior.

A projection may map one source record to zero, one or multiple Atomic Option IDs. Zero mapping is valid only when the disposition/reason explicitly explains why the source record is not an authored Atomic Option. An out-of-surface record must name its canonical owner.

This prevents count-driven fake settings and duplicate semantic engines.

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

1. verifies the schema lifecycle includes current repository statuses and source-projection definitions;
2. verifies canonical 56-surface identity from the surface registry;
3. derives lifecycle counters from `atomic-option-contract-progress.json` and requires `truth` counters to match;
4. accepts zero contract instances without promoting any surface;
5. validates every discovered surface instance identity/status and rejects progress that outruns the instance;
6. verifies Atomic Option identity uniqueness and required server-authoritative/storage/runtime/security/portability/testing evidence boundaries;
7. recomputes coverage counters and rejects `OPTION_CONTRACT_COMPLETE` instances with `missing > 0` or `unclassified > 0`;
8. requires BANK_REVIEWED complete instances to carry source projection matching canonical Options Bank record count;
9. validates source projection uniqueness, mapped Atomic Option references and explicit out-of-surface ownership.

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

**Affected:** product contract schema, generic contract validation, Composer smoke registration, future surface contract derivation.  
**Unaffected:** runtime PHP modules, database schemas, WordPress registrations, current Options Bank content/counts, current surface lifecycle statuses.  
**Risk:** an invalid future contract could falsely advance implementation truth; validator therefore fails closed on identity, lifecycle, coverage and source projection inconsistency.  
**Migration:** none; schema extension is additive for existing vocabulary.  
**Rollback/Recovery:** revert this shared infrastructure commit/PR; no product/runtime data is mutated.  
**Verification:** exact-head architecture, PHP quality, Platform Compatibility and distributable gates as path-applicable, plus aggregate smoke execution.

## Relations Gate B dependency

After this infrastructure is promoted, Surface 4 must derive and certify `config/product/option-contracts/relations.json` from its 144-record BANK_REVIEWED source before Relation runtime implementation begins. Runtime source must not infer undocumented semantics directly from Bank labels.