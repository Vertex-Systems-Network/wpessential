# P-006 B3 FP-50 Package-Incomplete Expectation Decision

Issue: #1067

Authorization: `GOV-P006-B3-FP50-EXPECTATION-DECISION-001`

Exact decision base: `fac942ae585a22e9a8a89d270c8f6a665a61e21b`

Classification: **NON-RUNTIME GOVERNANCE / FIXTURE-EXPECTATION DECISION / NO FP-50 EXECUTION / NON-CERTIFYING**

## 1. Purpose

This decision resolves the remaining interpretation ambiguity for P-006 fixture FP-50 after the accepted entrypoint-last external publication-owner prerequisite and the terminal bounded FP-49, FP-51 and FP-52 evidence.

It does **not** execute FP-50, assign FP-50 PASS/FAIL, create or reuse a P-001/CF runtime grant, change product runtime source, or change P-006 accounting.

Formal accounting remains:

**144 documented / 49 executed / 48 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

Permanent P-001/CF remains NOT_CERTIFIED. ADR-0010 remains Proposed.

## 2. Exact original FP-50 contract

The accepted Lane B fixture wording is:

> **FP-50** — fault-inject Pro replacement; Free remains usable, premium disabled with safe package-incomplete result.

The existing stop condition is:

> **STOP if partial Pro breaks Free.**

This decision interprets the phrase **safe package-incomplete result**. It does not replace the fixture's two product-safety assertions:

1. Free remains usable during interrupted Pro publication.
2. Premium runtime remains disabled.

## 3. Established publication boundary

The accepted publication-owner profile is deliberately narrow:

- disposable local WordPress;
- real plugin directories;
- WordPress Filesystem method `direct`;
- exact staged-candidate validation before live mutation;
- external publisher owns live materialization and recovery;
- configured target plugin entry remains absent while non-entry payload is incomplete;
- complete temporary entry is verified inside the final directory;
- configured entry is published last by same-directory rename;
- exact final tree is verified;
- corrupt staged candidates refuse before live mutation;
- unsupported publication profiles refuse before live mutation.

The supported partial-live state is:

`EXECUTION_EXCLUDED_LIVE_PARTIAL`

In that state the Pro destination may contain a deterministic partial non-entry tree, but the configured `wpessential-pro.php` entry does not exist. Therefore normal WordPress plugin loading cannot execute the incomplete Pro generation.

This boundary intentionally excludes:

- readable partial/corrupt configured Pro entry publication;
- configured Pro entry publication while its non-entry tree is incomplete;
- generic WordPress `move_dir()` / recursive-copy interruption claims;
- remote/FTP filesystem behavior;
- arbitrary in-place mutation of an already executable Pro generation.

## 4. Distinguishing two different result domains

### 4.1 Publication / harness result

The external publisher and evidence harness can truthfully classify an interrupted Pro destination as `EXECUTION_EXCLUDED_LIVE_PARTIAL`.

That result can be established without executing Pro code. It directly states why the incomplete package is safe to observe: its configured executable entry is withheld.

### 4.2 Product-local compatibility result

When `wpessential-pro.php` is executable, current Pro code can publish local fail-closed compatibility states such as `pro_package_incomplete` for missing required files.

During the accepted interrupted-publication state, however, `wpessential-pro.php` is intentionally absent. Pro code therefore does not execute and cannot publish `WPE_PRO_COMPATIBILITY_STATE=pro_package_incomplete` or an equivalent request-local state.

Absence of that product-local state is expected under the accepted execution-exclusion contract; it is not evidence that premium execution was admitted.

## 5. Decision

**Interpretation A is accepted.**

For FP-50 under the accepted entrypoint-last publication contract, a harness-observed `EXECUTION_EXCLUDED_LIVE_PARTIAL` state is the required **safe package-incomplete result**, provided the formal fixture proves all of the following at each authorized cut point:

- Free remains usable;
- configured Pro entry remains absent;
- the intended Pro destination state is exactly identified;
- no premium module is registered;
- premium boot is denied;
- premium migrations are denied;
- premium mutations are denied;
- no fatal/error attributable to the interrupted Pro package occurs on the observed request;
- no outbound WordPress HTTP attempt is introduced by the compatibility/publication evidence path;
- exact P0 recovery succeeds;
- recovered F0/P0 returns to the expected compatible state.

This interpretation preserves the original FP-50 safety intent: the interrupted Pro package has a deterministic, safe, package-incomplete outcome while Free continues independently and premium execution is impossible.

It does **not** require a product-local `pro_package_incomplete` reason string in a state where Pro is deliberately non-executable.

## 6. Why Interpretation B is rejected for this publication contract

Interpretation B would require the interrupted state itself to execute Pro code and publish `pro_package_incomplete` or an equivalent request-local compatibility result.

Under the accepted publisher, satisfying that requirement would require publishing an executable Pro entry while some Pro non-entry payload remains incomplete. That is the precise state the entrypoint-last contract structurally excludes because readable missing/truncated/corrupt PHP can otherwise be reached before application-level fail-closed logic can reliably protect every case.

Requiring that state would therefore weaken or bypass the accepted execution-safety boundary merely to obtain a product-local label.

This decision does not claim that a product-local incomplete state is never useful. A separately accepted non-publication corruption fixture may exercise safe missing-file subcases where a complete executable entry is intentionally retained. Such a fixture would be a different governed assertion and does not redefine FP-50 here.

## 7. FP-50 dependency-readiness result

FP-50 becomes:

**READY_FOR_SEPARATE_FORMAL_EXECUTION_AUTHORIZATION**

It remains **NOT EXECUTED**.

A later formal runtime tranche requires separate explicit authorization and a new temporary P-001/CF matrix grant if the accepted runtime matrix is to be reused. This decision itself supplies neither.

The already proposed deterministic cut points remain the finite candidate set for that later tranche:

| Cell | Pro non-entry files materialized | Required live classification |
| --- | ---: | --- |
| P50-01 | 1 / 286 | `EXECUTION_EXCLUDED_LIVE_PARTIAL` |
| P50-25 | 71 / 286 | `EXECUTION_EXCLUDED_LIVE_PARTIAL` |
| P50-50 | 143 / 286 | `EXECUTION_EXCLUDED_LIVE_PARTIAL` |
| P50-75 | 214 / 286 | `EXECUTION_EXCLUDED_LIVE_PARTIAL` |
| P50-100 | 286 / 286, configured Pro entry still absent | `NON_ENTRY_COMPLETE_ENTRY_ABSENT` / execution still excluded |

The later formal fixture must pin the exact candidate graph and file ordering from its own exact execution head rather than assuming historical hashes remain current.

## 8. Stop conditions for any later formal execution

A later FP-50 tranche must STOP rather than manufacture PASS if any observed cut point:

1. exposes the configured Pro entry before the exact non-entry candidate tree is complete;
2. allows an incomplete Pro PHP file to execute;
3. breaks independent Free operation;
4. registers premium modules;
5. admits premium boot, migrations or mutations;
6. produces a PHP fatal/error on the observation path that should degrade safely;
7. cannot prove the exact live partial-state identity;
8. cannot restore exact P0;
9. cannot prove recovered F0/P0 compatible state;
10. substitutes generic WordPress recursive-copy semantics for the accepted publication owner;
11. requires product-runtime changes merely to obtain a PASS;
12. crosses into updater/TUF, schema rollback/migration, provider, production or release scope without separate authorization.

## 9. Timeout / CI boundary

This decision tranche is intentionally non-runtime and lightweight.

The prerequisite CI hardening in PR #1069 removes `config/coordination/agent-work-queue.json`, `CHECKPOINT.md` and `README.md` from the pull-request path filters of the already-terminal FP-49, FP-51 and FP-52 runtime workflows. Their workflow/tool/evidence/build triggers, manual dispatch, timeouts and concurrency cancellation remain intact.

Therefore this decision closeout must not re-run those terminal runtime matrices merely because shared truth changes.

No WordPress runtime, disposable database setup, package replacement or artifact matrix belongs to this decision.

## 10. Non-promotion boundary

This decision does not establish or promote:

- FP-50 PASS or FAIL;
- a new executed-fixture count;
- permanent P-001/CF;
- a certified Free/Pro pair;
- a P-006 runtime certification;
- generic WordPress recursive-copy interruption safety;
- updater/TUF/signature/remote-delivery trust;
- rollback/downgrade/migration certification;
- provider/license/billing behavior;
- multisite behavior;
- production deployment/release;
- ADR-0010 acceptance;
- Issue #947 authority.

## 11. Terminal decision

The phrase **safe package-incomplete result** in FP-50 is resolved, for the accepted entrypoint-last publication-owner profile, as the externally evidenced execution-excluded incomplete publication state rather than a mandatory product-local compatibility reason emitted by non-executing Pro code.

**Decision: Interpretation A accepted. FP-50 is expectation-resolved and dependency-ready for a separately authorized formal execution tranche, but remains unexecuted and non-certifying.**
