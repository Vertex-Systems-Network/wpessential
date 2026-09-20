# P-006 B5 — Lane C Schema / Migration Readiness Review

Issue: #1085

Authorization: `GOV-P006-B5-LANE-C-SCHEMA-MIGRATION-READINESS-001`

Classification: **NON-RUNTIME READINESS / APPLICABILITY REVIEW / NON-CERTIFYING**

Exact-main review anchor: `078f43b592f8006b98e42303d3907d4a825a0b03`

## 1. Purpose and accounting boundary

This review re-evaluates the fixed P-006 schema/migration fixtures **FP-77…FP-94** against current WPEssential implementation after Wave 1S.

No fixture is executed here. Formal accounting remains:

**144 documented / 52 executed / 52 PASS / 0 FAIL / 0 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

No permanent P-001/CF, migration certification, Free/Pro pair certification, P-006 runtime certification, ADR-0010 promotion, deploy/release, provider or updater authority follows.

## 2. Current implementation truth

Current main has materially more Lane C machinery than the original planning snapshot:

- `MigrationRegistry` enforces stable unique migration IDs and unique positive sequence numbers, then returns migrations in numeric sequence order.
- `MigrationRunner` reads durable applied IDs, skips already-applied IDs, invokes `apply()`, and only then persists the applied marker. A destructive migration is rejected unless its migration object declares a non-empty recovery plan.
- `MigrationCoordinator` exposes only canonical registration and `runPending()`; it does not expose force-apply/reset/bypass APIs.
- `WpdbMigrationStateStore` persists canonical migration IDs in the WPE migration-state table and verifies marker persistence.
- settled Free-owned migrations include:
  - `006.create-compiled-registration-atomic-store` / sequence 60;
  - `007.create-definition-persistence` / sequence 70;
  - `008.create-audit-ptd-store` / sequence 80.
- current Pro-dependent Custom Tables persistence migrations are:
  - `220.custom_tables_migration_runs_v1` / sequence 220;
  - `221.custom_tables_migration_execution_confirmations_v1` / sequence 221.
- 220/221 are non-destructive `CREATE TABLE IF NOT EXISTS` migrations.
- Pro compatibility is evaluated before entitlement/module registration. An incompatible result returns before premium modules can register contributed migrations.
- Custom Tables now has table-level observed-schema introspection, migration generation stamps, recovery-evidence binding, precondition evaluation, persisted run state and stale-generation revalidation.
- Those Custom Tables primitives do **not** currently establish one global persisted Free/Pro schema-generation authority used by the top-level compatibility preflight.
- the generic migration runner has no cross-request lock/lease and no generic marker↔schema reconciliation engine.
- Wave 1S proved incompatible Pro activation does not register/apply migrations 220/221 or create their stores; that is prerequisite evidence, not retroactive FP-77…94 execution.

## 3. Exact protocol readiness matrix

| Fixture | Original protocol condition | Current decision | Current-main rationale / exact next primitive |
| --- | --- | --- | --- |
| **FP-77** | compatible code + matching schema boots without migration | **READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION** | Prepare a settled disposable DB with exact expected WPE migration markers/tables, boot an exact compatible pair, and prove `runPending()` produces no new migration IDs/schema changes. |
| **FP-78** | Free schema behind code executes only an authorized compatible Free migration path | **READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION** | Free 006/007/008 provide a bounded current migration set. Start from a disposable pre-006/007/008 DB state, boot Free, record exact ordered additions and unchanged sentinel. |
| **FP-79** | Pro schema behind code executes only after Free/Pro binary compatibility passes | **READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION** | Start with settled Free schema but absent 220/221 Pro stores; use an exact compatible pair plus explicit local test entitlement needed to admit the module, then prove 220/221 appear only after compatibility PASS. |
| **FP-80** | Pro schema migration does not use entitlement state as a substitute for binary/schema compatibility | **READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION** | Use an entitled test-local state with an incompatible pair and prove entitlement cannot admit 220/221; pair with the compatible control so entitlement and compatibility remain separate gates. |
| **FP-81** | incompatible Free/Pro pair starts no Pro destructive migration | **N_A_CURRENT_ACCEPTED_MIGRATION_SET** | Current accepted Pro migrations 220/221 are non-destructive. Do not invent a destructive Pro migration merely to manufacture execution. Re-open applicability if a real destructive Pro migration enters the accepted set. |
| **FP-82** | schema ahead of current Pro code yields read-only/degraded/recovery state rather than unsafe downgrade writes | **BLOCKED_MISSING_PERSISTED_PRO_SCHEMA_GENERATION_AUTHORITY** | Table-level Custom Tables introspection exists, but top-level Pro compatibility has no authoritative persisted Pro schema-generation/fingerprint bridge that can classify code-behind-schema globally. |
| **FP-83** | schema ahead of current Free code yields safe recovery behavior | **BLOCKED_MISSING_PERSISTED_FREE_SCHEMA_GENERATION_AUTHORITY** | Same gap for Free: declared code schema metadata is not an authoritative observation of a newer restored durable schema. |
| **FP-84** | interrupted migration resumes/reconciles according to its migration contract and does not rerun blindly | **BLOCKED_MISSING_GENERIC_INTERRUPTION_RECONCILIATION_CONTRACT** | Current fixed create-table migrations are idempotent, but the generic runner has no per-migration interruption state machine or resume/reconcile contract. Idempotency of selected DDL must not be promoted to generic recovery. |
| **FP-85** | migration metadata written but data step failed is detected | **BLOCKED_APPLIED_MARKER_IS_NOT_SCHEMA_VERIFIED** | The runner trusts an existing applied ID and skips that migration. A falsely persisted marker with missing/corrupt schema is not generically reconciled by the migration layer. |
| **FP-86** | data step committed but migration marker write failed is reconciled | **READY_AFTER_EXACT_HARNESS_PREREQUISITE** | Marker persistence happens after `apply()`. For the current fixed idempotent create-table migrations, an absent marker can be retried safely in principle; a bounded fault-injection state-store harness must prove exact convergence before formal execution. This does not certify arbitrary future migrations. |
| **FP-87** | concurrent requests do not execute the same migration unsafely | **BLOCKED_MISSING_CROSS_REQUEST_MIGRATION_LOCK_OR_PER_MIGRATION_CONCURRENCY_PROOF** | Marker uniqueness does not serialize two requests that both observe an unapplied ID before either marks it. Current generic runner exposes no lock/lease/advisory-lock primitive. |
| **FP-88** | Free and Pro migration ordering is explicit when both need changes | **READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION** | Canonical sequences make current ordering explicit: Free 60/70/80 precede Pro 220/221, and contributed migrations share the same registry/runner. Runtime evidence must still trace actual registration/application order rather than infer it from documentation. |
| **FP-89** | a Pro migration cannot require a newer Platform API than installed Free allows without preflight rejection | **READY_AFTER_EXACT_HARNESS_PREREQUISITE** | Compatibility preflight occurs before premium module registration. A test-only immutable candidate profile must isolate Platform API incompatibility without an earlier marketing-version mismatch masking the dimension, then prove no Pro migration registration/execution. |
| **FP-90** | restore of older DB under newer code follows supported upgrade path only after package compatibility passes | **READY_AFTER_EXACT_HARNESS_PREREQUISITE** | Current compatible package boot + bounded current migrations can represent this, but a disposable snapshot/restore fixture must pin an older DB state and prove package compatibility before any upgrade migration. |
| **FP-91** | restore of newer DB under older code blocks unsafe mutation | **BLOCKED_ON_SCHEMA_AHEAD_AUTHORITY_FP82_FP83** | Cannot safely prove restored-newer-schema rejection until authoritative observed schema-ahead state reaches the runtime compatibility/recovery decision. |
| **FP-92** | failed/rolled-back package update does not automatically roll back database state without a proven recovery plan | **BLOCKED_ON_SCHEMA_AHEAD_AND_ROLLBACK_RECOVERY_CONTRACT** | Package replacement evidence exists, but safe older-code behavior against post-migration newer DB state is not yet established. Do not test rollback by relying on an unsafe code/schema combination. |
| **FP-93** | irreversible migration requires its declared Backup/restore boundary before release certification | **BLOCKED_GENERIC_RUNNER_ONLY_REQUIRES_RECOVERY_PLAN_TEXT** | The generic runner checks for a non-empty recovery-plan declaration but does not verify backup/snapshot evidence. There is no current accepted destructive Pro migration subject; no synthetic irreversible migration may be added for P-006. |
| **FP-94** | migration logs/status contain no license tokens, Vault plaintext or unrelated user data | **READY_AFTER_EXACT_HARNESS_PREREQUISITE** | Current migration exceptions/status surfaces are bounded and current migration classes do not intentionally serialize row contents. A fixed diagnostics/redaction harness must capture exact failure/readiness/run-state outputs and assert forbidden secret/private-data classes are absent. |

## 4. Dependency-ready follow-on slices

### B5a — current-schema boot and ordering

Smallest direct formal-runtime candidate:

`FP-77, FP-78, FP-79, FP-80, FP-88`

Required shape:

- minimum and reference disposable WordPress/MySQL cells already used by P-006;
- exact immutable compatible pair identity;
- exact incompatible control for FP-80;
- explicit local test entitlement only where necessary to expose the compatible premium path;
- before/after WPE migration IDs, WPE-owned table list/schema hashes and sentinel;
- outbound HTTP deny/record;
- no destructive migration;
- no production/provider/updater activity.

A later authorization must predeclare whether each fixture uses a fresh DB, settled DB, or Free-settled/Pro-behind DB. Matrix cells are coverage and do not increase fixture count.

### B5b — harness-prerequisite candidates

These are not yet direct formal execution:

- **FP-86:** deterministic state-store failure after successful apply, constrained to the exact fixed idempotent migration set;
- **FP-89:** isolated Platform API mismatch candidate profile;
- **FP-90:** disposable DB snapshot/restore harness;
- **FP-94:** fixed diagnostics/redaction capture harness.

Each prerequisite must be reviewed separately before the formal fixture is counted.

## 5. Blocking architecture that must not be papered over

The following are real product/evidence gaps, not missing test assertions:

1. no authoritative persisted Free/Pro schema-generation bridge for schema-ahead code;
2. no generic migration marker↔physical-schema reconciliation;
3. no generic interrupted-migration resume protocol;
4. no cross-request migration lock/lease;
5. no verified backup/snapshot evidence gate in the generic destructive-migration runner.

Those blockers affect FP-82/83/84/85/87/91/92/93 and must not be bypassed by synthetic PASS fixtures.

## 6. Stop conditions for later execution

Stop and mark FAIL/INCONCLUSIVE rather than changing semantics if:

- exact package or database-state identity drifts;
- a formal fixture requires inventing a destructive migration;
- prior Wave evidence is being relabeled as current formal execution;
- Pro migrations register or execute before compatibility PASS;
- entitlement state overrides binary/API/schema incompatibility;
- a stale/applied marker is treated as proof of physical schema where the fixture requires observed truth;
- concurrency safety is claimed only from migration marker uniqueness;
- a rollback fixture would run older code against newer durable schema without an accepted safe schema-ahead decision;
- any provider/live/production/destructive authority would be required.

## 7. Terminal review conclusion

**B5 readiness review: COMPLETE / NON-RUNTIME / NON-CERTIFYING.**

Directly dependency-ready for a separately authorized bounded formal tranche:

**FP-77, FP-78, FP-79, FP-80, FP-88**.

Ready only after exact harness prerequisites:

**FP-86, FP-89, FP-90, FP-94**.

Current accepted migration set has no meaningful destructive-Pro subject for **FP-81**; it is recorded as **N_A_CURRENT_ACCEPTED_MIGRATION_SET**, not PASS and not executed.

Blocked on substantive runtime/recovery contracts:

**FP-82, FP-83, FP-84, FP-85, FP-87, FP-91, FP-92, FP-93**.

Formal P-006 accounting remains **144/52/52/0/0**, with zero certified pairs and zero runtime certifications.
