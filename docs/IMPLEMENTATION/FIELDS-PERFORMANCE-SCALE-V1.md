# Fields Runtime Performance and Scale V1

## Scope

This certification tranche covers the canonical Surface 3 runtime paths that materially scale with Field Group size:

- published Field value target resolution;
- native post-meta registration/binding for published Field Groups;
- native post-meta single-value and multi-row value IO.

The goal is deterministic operation-count and complexity evidence rather than CI wall-clock thresholds, which are hardware- and runner-dependent.

## Audit finding and correction

The previous published-group binder first preflighted every compiled registration and then called `WordPressPostMetaRegistrar::register()` for every registration. `register()` performed the same preflight again. Because each preflight queried both global and subtype registered-meta maps, ownership/support work scaled as two full per-registration passes before mutation.

A first optimization draft reduced this too aggressively to one request-local snapshot. That was rejected during semantic review because WordPress permits later registrations to replace an existing registry slot and registration callbacks/filters can change request-local registry state while a batch is executing. A stale batch snapshot must never authorize a later overwrite.

The certified V1 model therefore combines an optimized **full-plan snapshot preflight** with **live per-tuple revalidation immediately before mutation**:

1. structurally validate every registration;
2. reject duplicate `(post_type, meta_key)` tuples before snapshots or mutation;
3. snapshot global registered post-meta once for the full-plan phase;
4. snapshot each targeted subtype registered-meta map once for the full-plan phase;
5. cache full-plan `post_type_supports()` checks per unique `(post type, feature)` pair;
6. run ownership/support checks for the complete plan and fail before mutation if any current plan entry is invalid;
7. for every tuple, re-run the existing live support/ownership preflight immediately before its possible WordPress registration;
8. register only when the live preflight still authorizes the tuple.

This retains the previous fail-closed defense against a registration callback changing a later key while removing the old expensive full-plan per-registration snapshot pass.

`FieldGroupPostMetaBinder` submits one complete compiled plan to this registrar-owned batch boundary. It does not receive a raw registration bypass.

Standalone `WordPressPostMetaRegistrar::register()` remains available and delegates to the same one-entry batch contract, preserving the existing ownership/support boundary.

## Certified scale envelope

### Target resolution

`FieldValueTargetResolver::resolve()` addresses exactly one Definition UUID with `DefinitionRepositoryInterface::get()`.

It does not call `byType()` or scan unrelated Field Group definitions. Per resolution it performs:

- one selected Definition lookup;
- one post-type lookup;
- one post-status lookup;
- in-memory canonical normalization, location evaluation and top-level Field lookup within the selected group.

The selected payload work is linear in that Field Group's locations/fields, not in site post count or total repository Definition count.

Deterministic evidence uses a 512-Field published group and resolves the final Field while asserting the repository and post-target call counts above.

### Published-group binding

For `P` unique finite post-type targets and `F` value-storing Fields, let `N = P × F` be the compiled registration tuple count.

The old binder path performed two complete live preflight passes, each reading global and subtype registered-meta maps for every tuple: `4N` registered-meta map reads before/around registration.

The certified batch path performs:

- full-plan snapshot phase: `1 + P` registered-meta map reads;
- live safety revalidation phase: `2N` registered-meta map reads;
- total ownership-map reads: `2N + P + 1`;
- full-plan feature checks cached once per unique `(post type, feature)` pair;
- live feature revalidation per tuple for required features;
- one WordPress registration call for each tuple still authorized by the live preflight.

The deterministic scale fixture uses 64 stored Fields across 4 post types (`N = 256`) and proves:

- exactly 256 registration calls when all tuples are new;
- exactly `517 = 2(256) + 4 + 1` registered-meta map reads;
- required feature checks are one cached batch check plus one live recheck per relevant tuple rather than two uncached full-plan passes.

This is intentionally less aggressive than snapshot-only registration because ownership safety is not traded for a lower call count.

Any malformed registration, duplicate batch tuple, unsupported post-type feature, or ownership collision found in the full-plan phase occurs before the first registration mutation. A separate regression fixture proves that if an earlier registration callback introduces foreign ownership for a later tuple, the live revalidation rejects that tuple before `register_post_meta()` can overwrite it.

### Native value IO

Single-value access is independent of unrelated posts, unrelated Fields and total Field Group count. It uses the selected post/meta key through WordPress metadata APIs only.

The deterministic fresh scalar write fixture certifies:

- one post-type lookup;
- two metadata-existence checks (initial state plus post-write verification path);
- one native update;
- one verification read.

A subsequent read adds one post-type lookup, one metadata-existence check and one metadata read.

Multi-row replacement is intentionally linear in the desired row count because WordPress stores the certified multiple-row shape as individual metadata rows. For `R` desired rows, the successful replacement performs exactly `R` native add operations plus bounded existence/read verification; it does not perform a repository or target-resolution scan per row.

The deterministic fixture certifies 128 desired rows with exactly 128 adds and bounded verification calls.

Existing compensation and uncertain-state handling for failed multi-row replacement remains unchanged.

## Limits and non-goals

This certification does not add arbitrary product caps to Field count, target count or row count. Such limits would change product semantics without evidence of a correctness boundary.

The following remain outside this tranche:

- synthetic wall-clock pass/fail thresholds;
- cross-request caches or persistent compiled-plan caches;
- direct SQL or bypassing WordPress metadata APIs;
- provider-specific Field storage;
- Relations runtime/storage/cardinality behavior;
- Query, Columns, Listings or Status work;
- production deployment/release.

The full-plan snapshots and live checks operate only on request-local WordPress registration state. This optimization does not claim cross-request caching or distributed shared state.

## Evidence

Primary deterministic scale tests:

- `tests/Unit/Modules/Fields/FieldValueTargetResolverScaleTest.php`
- `tests/Unit/Modules/Fields/WordPressPostMetaRegistrarBatchScaleTest.php`
- `tests/Unit/Modules/Fields/PostMetaValueStoreScaleTest.php`

Existing Field Group binder, registrar ownership, retirement, value-store recovery, migration and real WordPress integration tests remain regression evidence and must remain green.

Exact-head CI is required before this tranche can be promoted or counted toward the Surface 3 Gate A performance/scale exit criterion in #66.
