# P-006 B3 FP-49…52 Applicability / Readiness Review

Issue: #1055

Authorization: `GOV-P006-B3-FP49-52-APPLICABILITY-REVIEW-001`

Exact review base: `d642a6db7b4bd2dc2173bf843ab8a623bef93aa8`

Classification: **NON-RUNTIME REVIEW ONLY / NO FORMAL FP EXECUTION / NON-CERTIFYING**

## 1. Purpose

This review re-evaluates FP-49, FP-50, FP-51 and FP-52 after the entrypoint-last external publication-owner prerequisite was validated by Issue #1052 / PR #1054.

It does not execute a fixture. It does not change WPEssential runtime behavior. It does not reinterpret generic WordPress recursive-copy replacement as safe.

Formal P-006 accounting remains:

**144 documented / 46 executed / 45 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

Permanent P-001/CF remains NOT_CERTIFIED. ADR-0010 remains Proposed.

## 2. Authoritative original fixture wording

The accepted Lane B source is `docs/QUALITY/P006-PLANNING-LANE-B-UPDATE-API-RANGES.md`.

| Fixture | Exact accepted future-fixture intent | Existing stop / dependency |
| --- | --- | --- |
| FP-49 | Fault-inject Free replacement at defined file-copy cut points; every resulting state must be detectable/non-runnable without Pro migration. | Needs filesystem fault injector and disposable install. |
| FP-50 | Fault-inject Pro replacement; Free remains usable, premium disabled with safe package-incomplete result. | STOP if partial Pro breaks Free. |
| FP-51 | Remove/truncate one Pro file at a time from disposable candidate classes representing bootstrap/preflight/module/runtime categories; preserve data and Free operation. | Coverage set must be fixed before execution; do not sample only one easy file. |
| FP-52 | Remove/truncate representative Free Platform/bootstrap file during disposable replacement; Pro must never mutate/load premium against partial Free. | STOP on premium boot/migration or fatal public/admin path that should degrade. |

This review does not replace those statements with easier claims.

## 3. Before / after blocker chain

### #1040 — fault-injection readiness review

Issue #1040 classified full arbitrary interruption/truncation as blocked because a readable partial PHP file could reach `require_once` or autoload before application-level compatibility code could publish a fail-closed result.

The key distinction was:

- missing/unreadable files can often be handled fail-closed;
- readable truncated/corrupt PHP can parser-fail before local compatibility logic runs.

### #1049 — integrity/publication architecture review

Issue #1049 established that plugin-internal integrity alone cannot protect a corrupt plugin entry file because PHP parses that entry before plugin-owned guards run.

It required an external publication/execution boundary first. Generic WordPress `move_dir()` / recursive-copy replacement was explicitly not accepted as universally atomic.

### #1052 / PR #1054 — publication-owner prerequisite

The prerequisite then validated one narrow external publication profile:

- disposable local WordPress;
- real plugin directories;
- WordPress Filesystem method `direct`;
- exact complete staged candidate validation;
- external publisher owns live materialization and recovery;
- configured target plugin entry remains absent while non-entry payload is incomplete;
- complete temporary entry is verified inside the final directory;
- configured entry is published last by same-directory rename;
- exact final tree is verified;
- corrupt staged candidate refuses before live mutation;
- unsupported publication profile refuses before live mutation.

The prerequisite passed on WordPress 6.9 / PHP 8.2 / MySQL 8.4 and WordPress 7.1 / PHP 8.5 / MySQL 8.4 for both Free and Pro targets.

This closes the original **entrypoint execution-exclusion prerequisite** for that explicit profile only.

It does not establish generic WordPress recursive-copy safety, remote filesystem behavior, or arbitrary in-place modification of an already executable plugin generation.

## 4. Supported publication state machine

The accepted publication owner makes these states meaningful.

1. **OLD_COMPLETE_EXECUTABLE** — exact old generation is live.
2. **STAGED_CANDIDATE_VALIDATED** — complete candidate tree is verified before live mutation.
3. **EXECUTION_EXCLUDED_LIVE_PARTIAL** — target live directory may contain a deterministic partial/corrupt destination copy of non-entry files, but the configured plugin entry does not exist.
4. **NON_ENTRY_COMPLETE_ENTRY_ABSENT** — every non-entry candidate file is exact, configured entry still absent.
5. **TEMP_ENTRY_VERIFIED** — complete candidate entry exists only at a non-configured temporary path inside the final live directory.
6. **NEW_COMPLETE_VERIFIED** — same-directory rename publishes the configured entry; complete final tree matches the candidate.
7. **RECOVERED_OLD_COMPLETE** — failure restores the exact prior generation through the same entrypoint-last discipline.

A supported request may observe states 1, 3, 4, 6 or 7.

A supported request must never observe:

- a readable partial/corrupt configured Free entry;
- a readable partial/corrupt configured Pro entry;
- a configured target entry published while the target non-entry tree is incomplete;
- a destination from an unsupported filesystem profile.

Those are **structurally excluded by the accepted publication contract**, not proven safe runtime states.

## 5. Current load-order facts

### Free

`wpessential.php` checks `vendor/autoload.php` with `is_readable()`, executes `require_once`, and only then defines `WPE_FREE_BOOTSTRAP_READY=true`.

Therefore a readable corrupt autoloader remains unsafe **if the Free entry is executable**.

Under the accepted publication contract, however, a destination autoloader may be missing/truncated during state 3 while `wpessential.php` itself remains absent. Normal WordPress plugin loading cannot execute that partial Free tree.

### Pro

`wpessential-pro.php` schedules its compatibility callback at `plugins_loaded` priority -200.

If the Pro entry is executable:

- missing `LocalCompatibilityPreflight.php` publishes `pro_package_incomplete`;
- missing required top-level Pro module files makes `$packageComplete=false` and local preflight publishes `pro_package_incomplete`;
- required Free runtime-class absence can publish `free_bootstrap_incomplete`.

The current required Pro module sequence is:

1. `frameworks/Modules/Roles/RolesModule.php`
2. `frameworks/Modules/AdminMenu/AdminMenuModule.php`
3. `frameworks/Modules/Settings/SettingsModule.php`
4. `frameworks/Modules/Dashboard/DashboardModule.php`
5. `frameworks/Modules/Profiles/ProfilesModule.php`
6. `frameworks/Modules/Membership/MembershipModule.php`
7. `frameworks/Modules/BuilderWidgets/BuilderWidgetsModule.php`
8. `frameworks/Modules/FormsWorkflows/FormsWorkflowsModule.php`
9. `frameworks/Modules/Cron/CronModule.php`
10. `frameworks/Modules/Notifications/NotificationsModule.php`
11. `frameworks/Modules/Emails/EmailsModule.php`
12. `frameworks/Modules/Chat/ChatModule.php`

Under the accepted Pro publication contract, none of those files is executable through the Pro plugin while incomplete because `wpessential-pro.php` remains absent until the non-entry tree is exact.

This distinction matters most for FP-50.

## 6. Per-fixture terminal review decision

| Fixture | Classification | Reason |
| --- | --- | --- |
| FP-49 | **READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION** | The original assertion is transport-oriented: defined Free file-copy cut points must be detectable/non-runnable without Pro migration. The accepted publication owner now defines those cut points and guarantees execution exclusion by withholding the Free entry. No wording change is required to execute the assertion against the accepted transport. |
| FP-50 | **BLOCKED_EXPECTATION_CLARIFICATION_REQUIRED** | The accepted Pro interruption state keeps the Pro entry absent. Free remains usable and premium is absent/inert, but no Pro code runs and no request-local `pro_package_incomplete` compatibility result is published. The original wording explicitly asks for a “safe package-incomplete result.” Treating external `EXECUTION_EXCLUDED` as that result would be a fixture interpretation change and cannot be assumed here. |
| FP-51 | **READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION** | A fixed representative bootstrap/preflight/module/runtime file set can now be pinned. Missing/truncated non-entry destination files are safely observable only while the Pro entry is absent; a missing/truncated main Pro entry is staged/rejected and never exposed as the configured live entry. This preserves the original remove/truncate and Free-operation assertions without claiming the corrupt PHP is safely executable. |
| FP-52 | **READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION** | Representative Free bootstrap/Platform files can be missing/truncated in the incomplete destination while the Free entry is absent. Pro then sees Free absent/incomplete and must not admit premium behavior. Main-entry corruption remains structurally excluded. This preserves the original partial-Free assertion under the accepted publisher. |

No fixture receives a PASS/FAIL result from this review.

## 7. FP-49 proposed finite formal cells

Current #1052 evidence records **238 Free non-entry files** in deterministic order.

A later formal FP-49 tranche should start from exact F0/P0, replace Free toward F1, and observe these five deterministic live publication cut points with `wpessential/wpessential.php` absent:

| Cell | Current-candidate copied non-entry count | Live state | Required observation |
| --- | ---: | --- | --- |
| F49-01 | 1 / 238 | EXECUTION_EXCLUDED_LIVE_PARTIAL | fresh request non-fatal; Free not runnable; Pro cannot admit premium migration/mutation |
| F49-25 | 59 / 238 | EXECUTION_EXCLUDED_LIVE_PARTIAL | same |
| F49-50 | 119 / 238 | EXECUTION_EXCLUDED_LIVE_PARTIAL | same |
| F49-75 | 178 / 238 | EXECUTION_EXCLUDED_LIVE_PARTIAL | same |
| F49-100 | 238 / 238 | NON_ENTRY_COMPLETE_ENTRY_ABSENT | same; entry still absent before final publication |

Every cell must:

- pin the exact state-manifest hash and copied-file list;
- assert the configured Free entry is absent;
- start a fresh PHP process;
- record the exact compatibility state rather than forcing a reason string;
- prove premium boot/migrations/mutations are not admitted;
- record zero outbound WordPress HTTP attempts;
- restore exact F0 through entrypoint-last recovery;
- prove recovered F0/P0 compatible state.

After those fault cells, successful final F1 publication may be recorded as recovery/terminal context but must not hide a failed interruption cell.

A readable partial Free main entry is not an FP-49 cell under the accepted publication contract because that state is unsupported and structurally excluded.

## 8. FP-50 expectation conflict and candidate cells

Current #1052 evidence records **286 Pro non-entry files**.

The supported publication cut points would naturally be:

- P50-01: 1 / 286;
- P50-25: 71 / 286;
- P50-50: 143 / 286;
- P50-75: 214 / 286;
- P50-100: 286 / 286 with configured Pro entry still absent.

At every such state, a fresh request can prove:

- Free remains independently usable;
- configured Pro entry is absent;
- no premium module is registered;
- premium boot/migrations/mutations are not admitted;
- no fatal occurs;
- exact P0 recovery succeeds.

However, the #1052 evidence also established that the Pro compatibility state is **not published by Pro** while the Pro entry is absent. The observed compatibility state can therefore be null/absent, not `pro_package_incomplete`.

Two interpretations are possible, and this review is not authorized to choose between them:

### Interpretation A — transport-level package-incomplete result

The externally recorded `EXECUTION_EXCLUDED_LIVE_PARTIAL` state is accepted as the “safe package-incomplete result.”

If this interpretation is explicitly accepted, FP-50 becomes ready for a separate formal execution using P50-01/25/50/75/100.

### Interpretation B — product-local compatibility result required

The phrase “safe package-incomplete result” requires `WPE_PRO_COMPATIBILITY_STATE=pro_package_incomplete` or the canonical request-local equivalent.

That requires an executable Pro entry while some Pro non-entry file is absent/incomplete. Such a publication state is deliberately excluded by the accepted entrypoint-last transport.

If Interpretation B is required, FP-50 is not applicable to the supported publication contract and must remain blocked unless a separately accepted non-publication package-corruption fixture is defined.

Formal FP-50 execution must not proceed until this interpretation is resolved in governance/fixture truth.

## 9. FP-51 fixed representative coverage set

The original fixture requires bootstrap/preflight/module/runtime categories and forbids sampling only one easy file.

This review pins the following finite current-source set.

### Bootstrap category

`wpessential-pro.php`

Required faults:

- staged candidate missing;
- staged candidate readable but deterministically truncated.

These are **STAGED_CANDIDATE_REJECTION** cells only. A partial configured Pro entry must never be placed at the live configured path.

### Preflight category

`frameworks/Modules/Compatibility/LocalCompatibilityPreflight.php`

Required destination-copy faults, with configured Pro entry absent:

- missing;
- readable deterministic truncation.

### Required top-level module category

Cover first / middle / last positions in the actual `$moduleClasses` order:

- first: `frameworks/Modules/Roles/RolesModule.php`;
- middle: `frameworks/Modules/BuilderWidgets/BuilderWidgetsModule.php`;
- last: `frameworks/Modules/Chat/ChatModule.php`.

For each path:

- missing destination file;
- readable deterministic truncation of destination file.

### Runtime-support category

`frameworks/Modules/Roles/WordPressRoleRuntimeEnvironment.php`

`RolesModule::register()` constructs this class when Roles is registered, so it is a concrete Pro-owned runtime-support class distinct from the top-level module file.

Required destination-copy faults:

- missing;
- readable deterministic truncation.

### FP-51 total

The fixed set is **12 cells**:

- 2 bootstrap staged-rejection cells;
- 2 preflight destination cells;
- 6 first/middle/last module destination cells;
- 2 runtime-support destination cells.

For every destination-copy cell, the configured Pro entry must remain absent. Free must boot normally, premium admission must remain false/absent, no fatal may occur, persisted baseline data must remain unchanged, and exact P0 recovery must succeed.

The formal result must distinguish:

- staged corruption rejected before mutation;
- live incomplete non-entry destination while execution is excluded.

It must not claim that a readable truncated Pro PHP file is safe to execute.

## 10. FP-52 fixed representative coverage set

The current Pro runtime explicitly requires Free bootstrap plus a Free Platform entitlement profile.

The fixed representative current-source set is:

### Composer/bootstrap

`vendor/autoload.php`

Faults:

- missing destination file;
- readable deterministic truncation.

### Free bootstrap class

`frameworks/Bootstrap/Plugin.php`

Faults:

- missing destination file;
- readable deterministic truncation.

### Free Platform profile — first explicit entitlement class

`frameworks/Platform/Entitlements/ProductEntitlementState.php`

Faults:

- missing destination file;
- readable deterministic truncation.

### Free Platform profile — last explicit entitlement policy class

`frameworks/Platform/Entitlements/EntitlementAwareModuleActivationPolicy.php`

Faults:

- missing destination file;
- readable deterministic truncation.

The fixed FP-52 set is **8 cells**.

For every cell:

- staged F1 source remains complete/verified;
- the fault belongs to destination materialization;
- configured `wpessential/wpessential.php` remains absent;
- Pro package remains complete/active;
- fresh request must be non-fatal;
- Pro must not register premium modules or admit premium boot/migrations/mutations;
- exact file-state manifest is recorded;
- exact F0 recovery succeeds;
- recovered F0/P0 returns compatible.

A readable partial/corrupt configured Free main entry is structurally excluded by the publication contract and is not a live FP-52 cell.

## 11. Staged vs live exposure matrix

| Fault type | Staged candidate | Live incomplete destination | Executable configured entry |
| --- | --- | --- | --- |
| missing/truncated target main entry | reject / record | configured entry remains absent | forbidden |
| missing/truncated non-entry file caused during destination copy | source stage stays exact | permitted only in execution-excluded state | forbidden until repaired/complete |
| corrupt staged non-entry file | reject before mutation | not published | no |
| complete verified target | accepted | complete non-entry tree then temp entry | final configured entry only after verification |

This matrix is the essential reason #1052 unblocks some formal interruption evidence without proving corrupt PHP execution safety.

## 12. Main-entry treatment

Main entries require explicit treatment because they are parsed before plugin-owned guards.

### Free

`wpessential/wpessential.php`

- missing during live publication: expected execution-exclusion state;
- readable partial/corrupt at configured live path: unsupported / forbidden;
- corrupt staged main entry: reject before live mutation.

### Pro

`wpessential-pro/wpessential-pro.php`

- missing during live publication: expected execution-exclusion state;
- readable partial/corrupt at configured live path: unsupported / forbidden;
- corrupt staged main entry: reject before live mutation.

No formal FP-49…52 tranche may manufacture a live partial configured entry merely to satisfy historical truncation language.

## 13. Remaining blockers

### FP-49

No architecture blocker remains for the accepted publication profile.

Still required before execution:

- separate formal runtime authorization;
- exact candidate identities generated at that future source head;
- a fresh temporary runtime grant if required by P-001/CF governance;
- per-cell immutable evidence and exact-head timeout-safe CI.

### FP-50

**Expectation clarification is mandatory.**

The governance source must decide whether an externally recorded execution-excluded incomplete publication is the accepted “safe package-incomplete result,” or whether the fixture requires Pro to publish `pro_package_incomplete`.

No runtime authorization should be created before that decision.

### FP-51

No architecture blocker remains for the fixed 12-cell set under the accepted publisher.

Still required:

- separate formal runtime authorization;
- future exact-head candidate/state manifests;
- explicit proof that persisted baseline data is unchanged;
- no claim that corrupt PHP was executable.

### FP-52

No architecture blocker remains for the fixed 8-cell set under the accepted publisher.

Still required:

- separate formal runtime authorization;
- future exact-head candidate/state manifests;
- Free/Pro partial-state and recovery evidence;
- no live partial configured Free entry.

## 14. Recommended serialization

Do not combine all four fixtures into one authorization.

Recommended order:

1. **FP-49 formal execution authorization** — Free interruption cut points only.
2. **FP-51 + FP-52 formal execution authorization** only if governance prefers one shared fault-materializer tranche; otherwise serialize separately. They may share deterministic state-manifest tooling but must retain independent fixture results.
3. **FP-50 expectation clarification** as a non-runtime decision before any FP-50 runtime authorization.
4. After any runtime tranche, reconcile formal fixture accounting only from terminal exact-head evidence.

A future runtime tranche should retain the existing timeout-safe shape:

- candidate/state build <=15 minutes;
- independent runtime cells <=12–18 minutes;
- `fail-fast:false`;
- stale-run cancellation;
- fresh PHP process for each observation;
- per-cell immutable artifacts.

## 15. Stop conditions

Stop rather than force PASS if:

1. a formal harness exposes a readable partial configured plugin entry;
2. a non-entry destination fault is evaluated while the target entry is executable;
3. FP-50 silently treats a null/absent Pro compatibility result as `pro_package_incomplete`;
4. any partial state registers premium modules or admits premium boot/migrations/mutations;
5. a partial Pro state breaks the complete Free request;
6. a state manifest cannot prove the exact missing/truncated set;
7. recovery incrementally repairs an unverified partial tree instead of restoring an exact complete generation;
8. generic WordPress `move_dir()`/recursive-copy semantics are represented as equivalent to the #1052 publisher;
9. DB/schema mutation, provider/network access, production resources or product-source changes are needed to manufacture success;
10. a fixture result is promoted beyond its explicit publication profile.

## 16. Terminal readiness summary

This review produces the following dependency classification, not formal fixture results:

- **FP-49 — READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION**
- **FP-50 — BLOCKED_EXPECTATION_CLARIFICATION_REQUIRED**
- **FP-51 — READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION**
- **FP-52 — READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION**

The publication-owner prerequisite therefore removes the earlier parser-risk blocker for FP-49/51/52 **by excluding execution of incomplete target entries and incomplete non-entry trees**, not by making corrupt PHP safe to parse.

FP-50 remains different because its original expected result names a product-local package-incomplete outcome that the accepted Pro interruption path does not produce while the Pro entry is excluded.

No P-006 counter changes, certification promotion, updater/TUF authority, rollback/migration authority, production/deploy authority, ADR-0010 promotion or #947 authority follows from this review.
