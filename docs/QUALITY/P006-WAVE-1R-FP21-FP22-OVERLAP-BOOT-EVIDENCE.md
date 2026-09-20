# P-006 Wave 1R — FP-21 / FP-22 Overlap Boot Evidence

Issue: #1077

Authorization: `GOV-P001-CF-TEMP-016`

Classification: **FORMAL FP-21 + FP-22 EVIDENCE / DISPOSABLE RUNTIME / NON-CERTIFYING**

Implementation base: `d5fb4ce2840b5f3b0dd5c232ee2ec558a1225da3`

## 1. Fixture contracts

FP-21:

> Free active with older Pro inside the supported overlap window boots the documented compatibility path.

FP-22:

> older Free with newer Pro inside a declared overlap window boots only supported functionality.

FP-24 is N/A under the current accepted pair-wide compatibility contract and is not executed here. FP-33 remains formally INCONCLUSIVE and is not executed here.

## 2. Runtime cells

| Cell | WordPress | PHP | MySQL |
| --- | --- | --- | --- |
| minimum | 6.9 | 8.2 | 8.4 |
| reference | 7.1 | 8.5 | 8.4 |

Each cell uses one disposable WordPress installation and executes FP-21 then FP-22 in fresh PHP processes.

## 3. Candidate graph and authority

The runtime workflow reuses `tools/p006/p006-wave1k-build-overlap-artifacts.php` only as a deterministic graph-construction primitive.

Historical `GOV-P001-CF-TEMP-009` authority is not reused.

The workflow derives the graph twice from current exact canonical packages and wraps the resulting F0/F1/P0/P1 identities in a Wave 1R identity document carrying:

`GOV-P001-CF-TEMP-016`.

Required formal pairs:

- FP-21 — F1 / P0;
- FP-22 — F0 / P1.

Exact current-head ZIP/tree/pair identities are populated from the successful immutable candidate artifact.

## 4. Test-local entitlement isolation

Before WordPress loads in every formal observation the harness defines:

`WPE_PRO_LOCAL_ENTITLEMENT_STATE=pro_active`.

This is local test configuration only. No provider/license/billing/remote entitlement service participates.

The purpose is to ensure binary-compatible overlap pairs can enter the documented premium boot path rather than being masked by entitlement denial.

## 5. Required observations

Each FP-21 and FP-22 observation must prove:

- exact expected ZIP/tree/pair identity;
- exact expected marketing/API/schema metadata;
- both plugins active;
- request-local compatibility state `compatible`;
- compatibility dimension `pair`;
- reason `compatible_local_pair`;
- remediation `none`;
- premium boot allowed;
- premium migrations allowed;
- effective local entitlement `pro_active`;
- premium reads allowed;
- premium mutations allowed;
- Free kernel booted;
- required Free modules exactly CPT + Taxonomy;
- expected current premium module set registered;
- no compatibility persistence keys;
- no external object cache authority;
- zero outbound WordPress HTTP attempts;
- no fatal/error.

FP-22 additionally proves the newer Pro marketing version does not imply a newer Platform API/schema: the F0/P1 pair remains on its declared `0.1.0` / schema `1` contract.

## 6. Timeout containment

Wave 1R is bounded by:

- latest-change gate <= 3 minutes;
- candidate build <= 15 minutes;
- two runtime jobs <= 18 minutes each;
- `fail-fast:false`;
- terminal aggregate <= 5 minutes;
- one immutable candidate graph shared by both runtime jobs;
- stale-run cancellation through per-PR concurrency.

The runtime workflow does not include this evidence markdown, queue, CHECKPOINT or README in its pull-request path list.

Because GitHub pull-request path matching evaluates the whole PR diff, Wave 1R also includes a latest-synchronize commit gate. A documentation-only synchronize event may start the lightweight workflow shell, but candidate/runtime jobs are skipped unless the latest commit changes a runtime-relevant path.

Shared-truth closeout is a separate PR after the runtime/evidence PR merges.

## 7. Formal result

**PENDING EXACT-HEAD RUNTIME EXECUTION.**

PASS may be recorded only after:

- candidate graph PASS;
- minimum runtime PASS for both fixtures;
- reference runtime PASS for both fixtures;
- terminal aggregate PASS;
- immutable artifacts pinned;
- exact-head Governance PASS;
- zero review threads;
- fresh-main / behind-by-zero pre-merge check.

## 8. Accounting boundary

Before Wave 1R:

**144 documented / 50 executed / 49 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

If both fixtures terminally PASS:

**144 documented / 52 executed / 51 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

TEMP-016 is consumed only in the separate terminal shared-truth closeout after the runtime/evidence PR merges.

No permanent P-001/CF, pair/runtime certification, provider integration, updater/TUF, production deploy/release or ADR-0010 promotion follows.
