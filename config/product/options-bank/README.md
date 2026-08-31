# WPEssential Master Options Bank Data

This directory contains normalized discovery records for the Master Options & Possibilities Bank.

## File naming

- `<surface>.json` — primary seed for a canonical surface.
- `<surface>--<shard>.json` — additional records for large surfaces such as labels, capabilities, field families, providers, compatibility adapters, or future possibilities.

All shards for a surface share the same canonical `surface.id` and `surface.key`.

## Semantic relationships

Discovery records are preserved as evidence even when later review proves that two records describe the same authored control or that one record is the resolved/effective form of another.

Cross-record meaning is therefore kept outside the discovery shards in `../options-bank-semantic-relations.json`:

- `ALIAS` — the source record is retained as discovery evidence, but the target record is the canonical authored Option Contract. Consumers must not create a second independent control for the alias.
- `EFFECTIVE_DERIVATION` — the source record describes a resolved/effective pipeline state derived from the target authored configuration. Consumers may expose diagnostics/preview for the source, but must not treat it as an independent authored option unless a later contract explicitly says otherwise.

The semantic registry must reference existing records on the same surface, may map a source only once, and aliases must point directly to canonical records rather than to another alias.

## Important

These files are discovery inventory, not implementation contracts. A Bank record may ultimately be shipped, deferred, provider-backed, expert-only, or explicitly rejected. Exact defaults, storage semantics, UI dependencies, migration rules and test obligations become mandatory in the downstream Atomic Option Contract.

Run the repository smoke suite to validate duplicate IDs/option paths, parent references, surface mapping, coverage counts, semantic relationships and progress truth.
