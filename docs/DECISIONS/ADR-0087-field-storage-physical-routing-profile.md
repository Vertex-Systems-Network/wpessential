# ADR-0087 — Field Storage Physical Routing Profile

Status: **Accepted paper routing profile / executable evidence pending**  
Date: 2026-08-28

## Context

WPEssential Custom Fields span WordPress posts/users/terms/comments, WPE application entities, repeaters, relationships, computed values and secrets. One universal EAV/JSON/custom-table format would either weaken interoperability or create unnecessary scale/constraint limitations.

## Decision

Accept adapter routing by semantics and workload:

- **FS1 — Native WordPress object storage** is the default for ordinary values that naturally belong to WordPress objects and fit native workload semantics.
- **FS2 — WPE typed Custom Table column** is the escalation path for high volume, strong schema/index, Q3/Q4 queryability, uniqueness or application-entity requirements.
- **FS3 — First-class child rows** are used for structured/repeater data only when child rows need real identity/queryability.
- **FS4 — Relations Engine** owns many-to-many/reverse/pivot/cardinality semantics.
- **FS5 — Vault reference** is mandatory for persisted secrets.
- **FS6 — Derived/search projection** remains rebuildable derived data, never source-of-truth replacement.

No single physical format is accepted as the product-wide Field Storage model.

## Queryability rule

The selected adapter must truthfully declare Q0–Q4 queryability. A builder cannot promise indexed high-volume filtering/aggregation simply because a slow meta/blob query can be constructed.

## Uniqueness rule

Concurrent hard uniqueness requires a proven transactional/DB guarantee. Native metadata cannot be marketed as strong uniqueness without an accepted locking/index strategy.

## Migration rule

Changing storage/type is a reviewed Field Migration Plan, separate from publishing the Field Definition. Large migrations must be resumable and verify source→target fidelity before cutover.

Definition publish does not imply runtime value migration success.

## Multisite rule

Native WordPress values keep native site/global ownership. WPE Custom Table fields inherit the owning table's explicit PT-D/PT-E topology. Network definitions do not silently globalize site runtime values.

## Evidence still required

After explicit owner consent:
- FS1 vs FS2 representative query/write/storage evidence;
- structured blob vs child-row workloads;
- uniqueness/concurrency races;
- field type/storage migrations with crash/resume;
- wrong-site migration attacks;
- 10k/100k/1M rows and 100/1k/10k-site operational evidence;
- exact adapter/index/storage implementation profiles.

Executed Field Storage fixtures: **0**.

## Development gate

This ADR authorizes no metadata registration, custom table, migration, backfill, benchmark, projection or runtime adapter. ADR-0014 explicit owner consent remains required.