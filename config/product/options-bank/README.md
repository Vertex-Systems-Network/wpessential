# WPEssential Master Options Bank Data

This directory contains normalized discovery records for the Master Options & Possibilities Bank.

## File naming

- `<surface>.json` — primary seed for a canonical surface.
- `<surface>--<shard>.json` — additional records for large surfaces such as labels, capabilities, field families, providers, compatibility adapters, or future possibilities.

All shards for a surface share the same canonical `surface.id` and `surface.key`.

## Important

These files are discovery inventory, not implementation contracts. A Bank record may ultimately be shipped, deferred, provider-backed, expert-only, or explicitly rejected. Exact defaults, storage semantics, UI dependencies, migration rules and test obligations become mandatory in the downstream Atomic Option Contract.

Run the repository smoke suite to validate duplicate IDs/option paths, parent references, surface mapping, coverage counts and progress truth.
