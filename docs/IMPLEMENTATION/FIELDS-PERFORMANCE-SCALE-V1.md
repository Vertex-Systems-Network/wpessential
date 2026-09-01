# Fields Runtime Performance and Scale V1

## Scope

This certification tranche covers the canonical Surface 3 runtime paths that materially scale with Field Group size:

- published Field value target resolution;
- native post-meta registration/binding for published Field Groups;
- native post-meta single-value and multi-row value IO.

The goal is deterministic operation-count and complexity evidence rather than CI wall-clock thresholds, which are hardware- and runner-dependent.

## Audit finding and correction

The previous published-group binder first preflighted every compiled registration and then called `WordPressPostMetaRegistrar::register()` for every registration. `register()` performed the same preflight again. Because each preflight queried both global and subtype registered-meta maps, ownership/support work scaled unnecessarily with the full `post types × stored fields` registration count twice.

V1 moves the complete two-phase binding plan into `WordPressPostMetaRegistrar::registerBatch()`:

1. structurally validate every registration;
2. reject duplicate `(post_type, meta_key)` tuples before snapshots or mutation;
3. snapshot global registered post-meta once;
4. snapshot each targeted subtype registered-meta map once;
5. cache `post_type_supports()` per unique `(post type, feature)` pair;
6. run ownership checks for the complete plan;
7. only after the whole plan passes, execute the required WordPress registrations.

`FieldGroupPostMetaBinder` now submits one complete compiled plan to this registrar-owned batch boundary. It does not receive a raw registration bypass.

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

For `P` unique finite post-type targets and `F` value-storing Fields, the binder compiles `P × F` registration tuples.

The batch ownership/support boundary is certified to perform:

- exactly one global registered-meta snapshot;
- at most one subtype registered-meta snapshot for each unique targeted post type;
- at most one required support check for each unique `(post type, feature)` pair;
- one WordPress registration call for each tuple that is not already an idempotent WPE-owned registration.

The scale fixture uses 64 stored Fields across 4 post types (256 registration tuples) and proves registered-meta snapshots are `1 + P`, not proportional to 256 registrations.

Any malformed registration, duplicate batch tuple, unsupported post-type feature, or ownership collision discovered during preflight occurs before the first registration mutation.

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

The registration snapshots are request-local WordPress registry state used for one synchronous two-phase binding operation. This optimization does not make a claim about cross-request caching or distributed shared state.

## Evidence

Primary deterministic scale tests:

- `tests/Unit/Modules/Fields/FieldValueTargetResolverScaleTest.php`
- `tests/Unit/Modules/Fields/WordPressPostMetaRegistrarBatchScaleTest.php`
- `tests/Unit/Modules/Fields/PostMetaValueStoreScaleTest.php`

Existing Field Group binder, registrar ownership, retirement, value-store recovery, migration and real WordPress integration tests remain regression evidence and must remain green.

Exact-head CI is required before this tranche can be promoted or counted toward the Surface 3 Gate A performance/scale exit criterion in #66.
