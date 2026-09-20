# P-006 Wave 1Q — FP-50 Pro Interruption Evidence

Issue: #1071

Authorization: `GOV-P001-CF-TEMP-015`

Execution classification: **FORMAL FP-50 EVIDENCE / DISPOSABLE RUNTIME / NON-CERTIFYING**

Exact implementation base: `bdeeb8c474628ae4e9aa9fd73f87df14188a6056`

## 1. Fixture contract

FP-50:

> Fault-inject Pro replacement; Free remains usable, premium disabled with safe package-incomplete result.

Issue #1067 / PR #1070 resolved **safe package-incomplete result** for the accepted direct-filesystem entrypoint-last publisher as the harness-observed execution-excluded publication state. A product-local `pro_package_incomplete` reason is not required while the configured Pro entry is absent.

This wave executes that interpretation only.

## 2. Runtime matrix

Required cells:

| Cell | WordPress | PHP | MySQL |
| --- | --- | --- | --- |
| minimum | 6.9 | 8.2 | 8.4 |
| reference | 7.1 | 8.5 | 8.4 |

Both cells use disposable WordPress and the accepted direct-filesystem entrypoint-last publication-owner profile.

## 3. Candidate graph

The exact-head workflow derives a deterministic F0/P0/P1 graph twice from the canonical package build and refuses drift.

Required candidate facts:

- exact F0 baseline Free;
- exact P0 baseline Pro;
- exact P1 target Pro;
- complete staged P1 verified before live mutation;
- P1 contains exactly **286 deterministic sorted non-entry files** plus configured entry `wpessential-pro.php`;
- generic recursive-copy publication is not used as the formal safety claim.

Exact ZIP/tree/pair identities are populated from the terminal workflow artifacts after the first exact-head execution.

## 4. Fixed fault cells

| FP-50 cell | P1 non-entry files materialized | Required external publication result |
| --- | ---: | --- |
| P50-01 | 1 / 286 | `EXECUTION_EXCLUDED_LIVE_PARTIAL` |
| P50-25 | 71 / 286 | `EXECUTION_EXCLUDED_LIVE_PARTIAL` |
| P50-50 | 143 / 286 | `EXECUTION_EXCLUDED_LIVE_PARTIAL` |
| P50-75 | 214 / 286 | `EXECUTION_EXCLUDED_LIVE_PARTIAL` |
| P50-100 | 286 / 286, configured Pro entry absent | `NON_ENTRY_COMPLETE_ENTRY_ABSENT` |

At every cut point the configured Pro entry must be absent and unreadable.

## 5. Required observations

Every fixed cut point must prove:

- exact F0 remains present;
- Free bootstrap remains ready;
- Free kernel remains booted;
- required Free modules remain available;
- configured Pro entry remains absent;
- no premium module registers;
- premium boot is denied;
- premium migrations are denied;
- premium mutations are denied;
- no fatal/error occurs;
- no product-local `pro_package_incomplete` result is forced;
- sentinel `wpe_p006_fp50_data_sentinel=fp50-preserve-v1` remains unchanged;
- outbound WordPress HTTP attempts remain zero;
- exact P0 recovery succeeds entrypoint-last;
- recovered F0/P0 returns `compatible`.

## 6. Timeout-safe execution boundary

The dedicated workflow is intentionally bounded:

- candidate build timeout: 15 minutes;
- runtime timeout: 18 minutes per matrix cell;
- runtime matrix: two cells, `fail-fast:false`;
- terminal aggregate timeout: 5 minutes;
- stale runs cancelled by per-PR concurrency;
- one immutable candidate artifact shared by both runtime cells;
- fresh PHP process per observed fault state;
- queue/CHECKPOINT/README excluded from FP-50 workflow pull-request paths.

The already-terminal FP-49, FP-51 and FP-52 workflows are not part of this execution.

## 7. Formal result

**PENDING EXACT-HEAD WORKFLOW EXECUTION.**

The terminal section must record the actual result. PASS may be recorded only if both runtime cells and all five fixed cells per runtime are green and exact recovery/sentinel/network assertions pass.

## 8. Accounting boundary

Before Wave 1Q:

**144 documented / 49 executed / 48 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

If FP-50 terminally PASSes:

**144 documented / 50 executed / 49 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

No permanent P-001/CF, pair/runtime certification, generic WordPress recursive-copy certification, updater/TUF, rollback/migration, provider, multisite, production deploy/release, ADR-0010 acceptance or #947 authority follows from this wave.
