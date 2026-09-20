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

**PENDING EXACT-HEAD RUNTIME EXECUTION.**

## 9. Accounting boundary

Before Wave 1S:

**144 documented / 52 executed / 51 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

FP-33 is already included in the executed count.

If Wave 1S resolves FP-33 to PASS:

**144 documented / 52 executed / 52 PASS / 0 FAIL / 0 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

Executed remains **52**, not 53.

TEMP-017 is consumed only by the separate terminal shared-truth closeout.

No permanent P-001/CF, pair/runtime/migration certification, provider integration, updater/TUF, production deploy/release or ADR-0010 promotion follows.
