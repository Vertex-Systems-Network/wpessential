# P-006 B3 Entrypoint-Last Publication Owner Harness

Issue: #1052

Authorization: `GOV-P006-B3-PUBLICATION-OWNER-HARNESS-001`

Classification: **RUNTIME HARNESS PREREQUISITE ONLY / NOT FP-49…52 EXECUTION**

## Purpose

Validate the narrow external publication contract selected after Issue #1049 without changing WPEssential product runtime source.

The harness proves that a disposable local publisher can keep a target plugin's configured WordPress entry file absent while non-entry payload files are incomplete, then publish that entry file last only after the complete non-entry tree is verified.

This is prerequisite evidence. It does not increment P-006 fixture counters.

## Supported publication profile

The accepted harness profile is deliberately narrow:

- disposable local WordPress only;
- real plugin directories, not symlinks;
- WordPress Filesystem method `direct`;
- final plugin-entry publication requires a same-directory local rename;
- the external harness owns stage validation, old-generation preservation, copy ordering, final-tree validation and recovery;
- generic `Plugin_Upgrader`, `WP_Upgrader::install_package()` and `move_dir()` are not represented as equivalent publication owners.

Unsupported publication profiles are refused before live mutation.

## Why generic WordPress replacement is not the contract

WordPress `move_dir()` attempts a filesystem move and may fall back to `copy_dir()` when the move fails. `WP_Upgrader::install_package()` may also use recursive copy depending on destination/working conditions.

Therefore this harness does not infer universal atomic publication from a successful WordPress plugin overwrite in one filesystem environment.

## Entrypoint-last invariant

For Free target publication the withheld entry is:

`wpessential/wpessential.php`

For Pro target publication the withheld entry is:

`wpessential-pro/wpessential-pro.php`

The publisher:

1. verifies the complete staged candidate tree;
2. preserves the exact old complete live generation;
3. removes the target live generation;
4. creates a fresh live directory;
5. copies non-entry files in deterministic order;
6. executes fresh WordPress probes at deterministic mid-copy cut points while the target entry remains absent;
7. verifies the exact non-entry payload;
8. writes the candidate entry to a temporary file inside the final live directory;
9. verifies that temporary entry;
10. publishes it last by same-directory rename;
11. verifies the exact complete final payload tree;
12. performs a fresh compatible boot observation.

If entry publication fails, the target entry remains absent while the old complete generation is restored through the same entrypoint-last discipline.

## Runtime matrix

- minimum: WordPress 6.9 / PHP 8.2 / MySQL 8.4;
- reference: WordPress 7.1 / PHP 8.5 / MySQL 8.4;
- Free target: F0 → F1 with P0 unchanged;
- Pro target: P0 → P1 with F0 unchanged.

The deterministic F0/F1/P0/P1 graph is derived using the already accepted Wave 1M test-only builder only as a graph generator. Its historical FP-58 authorization is explicitly not reused.

## Required scenarios per target/cell

### Successful publication

- baseline F0/P0 is compatible;
- target entry is absent during non-entry materialization;
- three deterministic mid-copy fresh-process probes are non-fatal;
- partial state never allows premium boot, migrations or premium mutations;
- final entry rename succeeds only after non-entry verification;
- final tree exactly matches F1 or P1;
- final fresh boot is compatible;
- required Free modules and the accepted premium module set are present;
- outbound WordPress HTTP attempts remain zero.

### Interruption before entry publication

- only a deterministic partial subset of non-entry candidate files exists;
- target entry remains absent;
- fresh-process probe remains non-fatal and fail-closed;
- exact old generation is restored;
- recovery boot returns the baseline compatible state.

### Final-entry rename failure

- complete new non-entry tree exists;
- verified temporary entry exists;
- injected final rename failure leaves the configured target entry absent;
- fresh-process probe remains non-fatal/fail-closed;
- exact old generation is restored and boots compatibly.

### Corrupt staged tree

- deterministic staged corruption is introduced before live mutation;
- candidate tree digest no longer matches;
- publication is refused;
- old live tree remains exact and compatible.

### Unsupported profile

- a simulated non-direct publication profile is refused before live mutation;
- old live tree remains exact and compatible.

## Evidence model

Each cell records:

- exact source SHA;
- observed WordPress/PHP/MySQL versions;
- filesystem profile;
- old/new ZIP and payload-tree identities;
- deterministic ordered payload files;
- cut-point counts;
- state-manifest hashes;
- entry exists/readable state at each cut point;
- fresh-process compatibility/module state;
- premium boot/migration/mutation admission;
- active-plugin option records;
- network attempts;
- recovery tree identity;
- final publication classification.

## Timeout-safe CI

- candidate graph build: <=15 minutes;
- each of four runtime target/cell jobs: <=18 minutes;
- matrix `fail-fast:false`;
- stale-run cancellation;
- per-cell immutable evidence artifacts;
- terminal prerequisite aggregate: <=5 minutes.

## Non-promotion boundary

This harness does not execute or certify:

- FP-49, FP-50, FP-51 or FP-52;
- generic interrupted WordPress `move_dir()`/recursive-copy replacement safety;
- automatic updater/TUF/signature trust;
- rollback or migration recovery;
- provider/license/billing behavior;
- Multisite;
- production deployment/release;
- a certified Free/Pro pair;
- runtime certification;
- ADR-0010 acceptance.

Formal accounting remains:

**144 documented / 46 executed / 45 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

## Future effect if prerequisite passes

A passing prerequisite establishes one concrete publication profile that can be considered in a later, separately authorized FP-49…52 applicability/execution review.

It does not itself decide that every byte-truncation state is non-applicable to all WPEssential installation/update paths, and it does not convert missing-entry probes into formal FP fixture PASS results.


## Terminal prerequisite evidence

Pre-closeout exact source head: **`6ee016d8c13f1c0eaeecd1971c3ebf4fd6ca0f5c`**.

CI:

- Governance Gate run **35405126545** — PASS;
- P-006 B3 Publication Owner Harness run **35405126544** — PASS;
- deterministic candidate graph — PASS;
- minimum Free target — PASS;
- minimum Pro target — PASS;
- reference Free target — PASS;
- reference Pro target — PASS;
- terminal aggregate — PASS.

Observed deterministic payload ordering:

- Free target non-entry file count: **238**;
- Pro target non-entry file count: **286**.

Observed partial-state behavior:

- Free target: configured entry absent at every mid-copy cut point; compatibility state `free_missing`; premium boot, premium migrations and premium mutations denied; no PHP fatal/error.
- Pro target: configured Pro entry absent at every mid-copy cut point; Free remains independently bootable with required Free modules; Pro premium runtime remains absent/inert; premium boot, migrations and mutations denied; no PHP fatal/error.

Final exact payload identities:

- F1 payload tree: **`3e8e56d3887b1f6ed29a66beb0a489d44e547d8b5104c6819e41b91bb6518460`**;
- P1 payload tree: **`d9a2d208871d684d50e4eabcdb7d4ffb0e2f51cff160ebb311e47d210c3d2a46`**.

Recovery identities:

- F0 payload tree: **`0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9`**;
- P0 payload tree: **`08ef083162109a15899dba7b14d1e420f327323b20cd1434cf9e7cfa23af09a7`**.

All five required scenario classes pass on both runtime families/targets: successful entrypoint-last publication; interruption-before-entry recovery; injected final-entry rename failure recovery; corrupt staged-tree refusal before mutation; unsupported-profile refusal before mutation. Outbound WordPress HTTP attempts remain **0**.

Immutable pre-closeout artifacts:

| Evidence | Artifact | Digest |
| --- | ---: | --- |
| candidates | 10572222787 | `sha256:14841f3b5062a9bdb163d48ffa28a9374cc28601f70dec17d625e1ac6fbfc2d5` |
| minimum Free | 10572305453 | `sha256:e3026b0e7ca6b13e09f9988402771772f6277c79870527cd39a52abbeff766eb` |
| minimum Pro | 10571733040 | `sha256:238ea0c81424aa4246b7d0e83b64c0bc4b64f8b042b75acf3af80338689e7b11` |
| reference Free | 10571832978 | `sha256:e59a0461e50f697668b68c5da89898c1f7ce0c7dd990c7bc244f2322693b3527` |
| reference Pro | 10572262741 | `sha256:396aaf55f1155fcf3b36bc7e8429d6b696b6724266b816b8d9d5c075e2d1f9e6` |
| terminal | 10572192814 | `sha256:112ea45ede2ee0d1a83b54f8e5fb66bf16a46be3461387a32e0787b4e157ff86` |

### Terminal classification

**PASS_PREREQUISITE_ONLY** for the explicit direct/same-filesystem entrypoint-last publication profile.

This result does **not** execute FP-49, FP-50, FP-51 or FP-52; does not certify generic WordPress `move_dir()`/recursive-copy interruption safety; does not certify an updater/TUF/signature system, rollback or migration recovery, a Free/Pro pair, a runtime, ADR-0010, deploy or release; and changes no P-006 fixture counters.

Formal accounting remains **144 documented / 46 executed / 45 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

The next possible P-006 step is a **separately authorized FP-49…52 applicability/readiness review**. It must map the original fixture wording to this validated publication profile and determine dependency readiness before any formal execution is authorized.
