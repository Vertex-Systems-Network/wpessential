# P-006 Wave 1S — FP-33 Activation Lifecycle Evidence

Issue: #1081

Authorization: `GOV-P001-CF-TEMP-017`

Classification: **FORMAL FP-33 RE-EXECUTION / DISPOSABLE RUNTIME / NON-CERTIFYING**

Implementation base: `7ff24ae84e8a4ed976a17dc4bf9f7e5c2651ccd5`

## 1. Fixture contract

> **FP-33** — duplicate activation hooks do not run destructive work before compatibility is known.

Wave 1H already counted FP-33 as executed but left its result INCONCLUSIVE after a broad static lifecycle-owner scan. Wave 1S re-executes that same fixture with real WordPress activation/deactivation/reactivation evidence; it does not add a new executed-fixture count.

## 2. Current source boundary

Current first-party entry files use request lifecycle callbacks rather than WordPress plugin activation hooks:

- Pro compatibility callback: `plugins_loaded` priority `-200`;
- Free boot callback: `plugins_loaded` priority `-100`;
- no accepted first-party `register_activation_hook()`, `register_deactivation_hook()` or `register_uninstall_hook()` is expected.

Current Free persistence boot always owns baseline non-destructive migration ids:

- `006.create-compiled-registration-atomic-store`;
- `007.create-definition-persistence`;
- `008.create-audit-ptd-store`.

Pro-dependent Custom Tables store migrations are:

- `220.custom_tables_migration_runs_v1`;
- `221.custom_tables_migration_execution_confirmations_v1`.

Those Pro-dependent migrations are registered only when the request-local compatibility result is compatible and premium migrations are allowed.

## 3. Exact scenarios

### Scenario A — newer Free / older Pro breaking pair

- F2 `0.2.0-test-breaking`;
- P0 `0.1.0-dev`;
- expected compatibility: `free_version_too_new`;
- accepted pair id: `72f260a4882bb04aaf05a8a4960aaaf7fa2bba0f83591aeb79f5ece4f303480c`.

### Scenario B — older Free / newer breaking Pro

- F1 `0.1.1-test-overlap`;
- P2 `0.2.0-test-breaking`;
- expected compatibility: `free_version_too_old`;
- accepted pair id: `a3bb184caaef86f4c303c2cf73ba5f7631d711cae4e280cbca035e95937f6501`.

Wave 1L tooling is reused only as deterministic candidate construction/setup/swap primitives. Historical TEMP-010 authority is not reused.

## 4. Runtime cells

| Cell | WordPress | PHP | MySQL |
| --- | --- | --- | --- |
| minimum | 6.9 | 8.2 | 8.4 |
| reference | 7.1 | 8.5 | 8.4 |

Each cell runs both incompatible scenarios and two activation cycles per scenario.

## 5. Immediate WPE state snapshot

Immediately before and after each WordPress `activate_plugin()` call, the Wave 1S harness records:

- sorted applied WPE migration ids;
- sorted WPE-owned table names;
- normalized SHA-256 of `SHOW CREATE TABLE` for every WPE-owned table;
- presence/absence of the two Pro-dependent Custom Tables stores;
- deterministic sentinel `fp33-preserve-v1`;
- SHA-256 of the normalized WPE snapshot.

The WordPress `active_plugins` option is intentionally outside this snapshot because activation/deactivation is expected to mutate that WordPress lifecycle state.

Immediate pre/post WPE snapshot hashes must be identical.

Fresh incompatible requests must also match the settled Free-only baseline WPE snapshot.

## 6. Required formal assertions

Every accepted cell/scenario/cycle must prove:

- exact candidate ZIP/tree/pair identity;
- static first-party activation/deactivation/uninstall hook scan is empty;
- Pro activation succeeds through WordPress's plugin activation API;
- activation itself does not mutate WPE migration/schema state;
- fresh request resolves the exact expected incompatibility;
- premium boot denied;
- premium migrations denied;
- premium mutations denied;
- premium module set empty;
- Free CPT + Taxonomy modules remain present;
- Pro migration ids 220/221 absent;
- Pro Custom Tables migration-run/confirmation tables absent;
- deactivation does not mutate WPE migration/schema state;
- repeated activation gives the same result;
- compatibility persistence authority absent;
- external object cache absent;
- sentinel preserved;
- outbound WordPress HTTP attempts zero;
- no fatal/error.

## 7. Timeout containment

Wave 1S uses:

- latest-change gate <= 3 minutes;
- deterministic candidate + static prerequisite <= 15 minutes;
- minimum/reference lifecycle jobs <= 18 minutes each;
- `fail-fast:false`;
- terminal aggregate <= 5 minutes;
- stale-run concurrency cancellation.

The heavy workflow does not include this evidence markdown, queue, CHECKPOINT or README in its pull-request paths. A latest-change gate additionally suppresses heavy jobs on documentation-only synchronize events.

Shared-truth closeout is a separate Governance-only PR after the runtime/evidence PR merges.

## 8. Formal result

Accepted runtime source head:

`dcc8e965408331b2d0be449b2859a64729ee8b3b`

Exact-head verification:

- Governance Gate run **35510888422 — PASS**;
- Wave 1S run **35510888416 — PASS**;
- latest-change gate — PASS;
- deterministic candidate build — PASS;
- static first-party activation/deactivation/uninstall hook prerequisite — PASS with zero hits;
- minimum / WordPress 6.9 / PHP 8.2 / MySQL 8.4 — PASS;
- reference / WordPress 7.1 / PHP 8.5 / MySQL 8.4 — PASS;
- terminal aggregate — PASS;
- review threads before evidence finalization — zero;
- fresh main before evidence finalization remained `7ff24ae84e8a4ed976a17dc4bf9f7e5c2651ccd5`.

Immutable exact-run artifacts:

- candidate graph + static scan — id **10605022170**, digest `sha256:34f6b8b3792fba11665edb0377ddce6891a80ba114f4792761244e77270635d4`;
- minimum runtime — id **10605280957**, digest `sha256:17da20cfd7c35d36c5ab1c641a5583ce2e4430301dc02a15773ce67f1a5f144f`;
- reference runtime — id **10606060168**, digest `sha256:d4290f7200e3fb41c1b17ab9b244359f30067ed698b6c3ec405f9acde1dba9e4`;
- terminal marker — id **10606030821**, digest `sha256:fa52290de2c182c779eddd2986e0d2b7b4586207b1bd6280a727d48472dbdbff`.

Both runtime cells produced the same settled Free-only WPE snapshot SHA-256:

`42e5d91120cbca74e9857efd778afecb18fe5978507d3052493819fb57eae551`

### Scenario A — F2/P0

Both runtime cells executed two complete activation/deactivation cycles for exact F2/P0.

Each cycle proved:

- WordPress `activate_plugin()` succeeded;
- immediate pre/post activation WPE snapshot hashes were identical;
- fresh request resolved `free_version_too_new`;
- premium boot, migrations and mutations remained denied;
- premium module set remained empty;
- required Free CPT + Taxonomy modules remained present;
- Pro migration ids 220/221 remained absent;
- Pro Custom Tables migration-run/confirmation stores remained absent;
- deactivation left WPE migration/schema state unchanged;
- sentinel remained `fp33-preserve-v1`;
- outbound WordPress HTTP attempts remained zero;
- no fatal/error occurred.

### Scenario B — F1/P2

Both runtime cells executed two complete activation/deactivation cycles for exact F1/P2.

Each cycle proved the same activation/deactivation WPE-state invariants, with fresh request compatibility equal to `free_version_too_old`.

Across all accepted observations, no destructive migration executed and no first-party WordPress activation/deactivation/uninstall hook was present.

Terminal result:

**FP-33 — PASS_WAVE_1S_ACTIVATION_LIFECYCLE**

The earlier Wave 1H INCONCLUSIVE result is resolved by this formal re-execution. FP-33 is **not** counted as a new executed fixture.

The workflow YAML required one tooling-only normalization before the accepted run: the PHP setup step was converted from YAML flow-map syntax to block syntax so the new workflow registered correctly. No Wave 1S runtime evidence was accepted before head `dcc8e965408331b2d0be449b2859a64729ee8b3b`.

## 9. Accounting boundary

Before Wave 1S:

**144 documented / 52 executed / 51 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

Terminal Wave 1S reclassification:

**144 documented / 52 executed / 52 PASS / 0 FAIL / 0 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

Executed remains **52**, not 53.

TEMP-017 is consumed only by the separate terminal shared-truth closeout after this runtime/evidence PR merges.

No permanent P-001/CF, pair/runtime/migration certification, provider integration, updater/TUF, production deploy/release or ADR-0010 promotion follows.

## 10. Terminal conclusion

**Wave 1S — PASS_FP33_ACTIVATION_LIFECYCLE / NON-CERTIFYING**

The bounded evidence resolves the prior static-harness ambiguity by demonstrating that repeated WordPress Pro activation/deactivation against both accepted incompatible breaking pairs does not mutate WPE migration/schema state before compatibility is known and does not admit Pro-dependent migrations afterward. This remains fixture evidence, not runtime, migration, pair or production certification.
