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

Terminal accepted runtime source head:

`135b93b441e92da1b716d2347bec724d79ebc6ab`

Exact-head verification:

- Governance Gate run **35509406247 — PASS**;
- Wave 1R run **35509406280 — PASS**;
- latest-change gate — PASS;
- deterministic candidate build — PASS;
- minimum / WordPress 6.9 / PHP 8.2 / MySQL 8.4 — PASS;
- reference / WordPress 7.1 / PHP 8.5 / MySQL 8.4 — PASS;
- terminal aggregate — PASS;
- review threads before evidence finalization — zero;
- fresh main before evidence finalization remained `d5fb4ce2840b5f3b0dd5c232ee2ec558a1225da3`.

Immutable exact-run artifacts:

- candidate graph — id **10604414587**, digest `sha256:8c74d747770e7e92371bfee329613ea1887d88502733b5e0394184ddd1623007`;
- minimum runtime — id **10604809033**, digest `sha256:801955e29ce00fefd1584bc1d18a0d95795b1741b58497bf003bc7f465456d86`;
- reference runtime — id **10604639353**, digest `sha256:5c41cb7f880f71e4ba9803df36d01af91bbcb0ac793da89ceba2343cbf45639d`;
- terminal marker — id **10604629265**, digest `sha256:f108bdf0ffef306ffb932cb702a065f5bc5589631a63cdfd381d65784d7cee65`.

### FP-21 accepted observation

Exact pair:

- Free F1 ZIP: `f9401f81b4a65d6f610b05cd445d3d8bdec75b2996296e0deaa13a8a2a265185`;
- Free F1 tree: `3e8e56d3887b1f6ed29a66beb0a489d44e547d8b5104c6819e41b91bb6518460`;
- Pro P0 ZIP: `0b12ada8265f3032641e09a80ba09f9950d3aef8b526b63c2b3665663aca4178`;
- Pro P0 tree: `08ef083162109a15899dba7b14d1e420f327323b20cd1434cf9e7cfa23af09a7`;
- pair id: `f5157c46d4a29af6df4929cafeaad831fc50a8d38e469c2df73340766bb3faef`.

Both minimum and reference runtime cells proved:

- compatibility state `compatible`;
- dimension `pair`;
- reason `compatible_local_pair`;
- remediation `none`;
- premium boot allowed;
- premium migrations allowed;
- local/effective entitlement `pro_active`;
- premium reads and mutations allowed under that explicit local test state;
- required Free modules `custom-post-types` and `taxonomies`;
- full expected premium module set registered;
- zero compatibility persistence keys;
- zero outbound WordPress HTTP attempts;
- no fatal/error.

**FP-21 — PASS_WAVE_1R_OVERLAP_BOOT**

### FP-22 accepted observation

Exact pair:

- Free F0 ZIP: `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80`;
- Free F0 tree: `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9`;
- Pro P1 ZIP: `96acfb8527e62f3ee52f8ef61b801f770315a3a633502ead845c5c4f145b30ac`;
- Pro P1 tree: `d9a2d208871d684d50e4eabcdb7d4ffb0e2f51cff160ebb311e47d210c3d2a46`;
- pair id: `532f93f984bb5ecee1793a4c325c904e84945e35f1f5f682e20b59cbdbc58b26`.

Both minimum and reference runtime cells proved the same accepted compatible/premium boot path as FP-21.

The newer P1 marketing version did not advance the older F0 Platform contract:

- Platform API remained `0.1.0`;
- Platform schema remained `1`;
- Pro minimum/maximum Platform API remained `0.1.0`.

**FP-22 — PASS_WAVE_1R_OVERLAP_BOOT**

### Non-terminal setup attempts

Wave 1R run **35509193394** stopped in candidate authority normalization before any runtime cell executed because the historical pair record did not carry an `expected_state` field. The Wave 1R wrapper was corrected to declare the accepted `compatible` expectation explicitly.

Wave 1R run **35509258009** reached successful minimum FP-21/FP-22 individual observations, but its minimum aggregate failed on a harness-only null assertion that treated an explicitly recorded `fatal_or_error: null` as missing. That assertion was corrected without product-runtime changes. The run was not accepted as terminal Wave 1R evidence and does not add duplicate fixture accounting.

Run **35509406280** is the terminal accepted formal execution.

## 8. Accounting boundary

Before Wave 1R:

**144 documented / 50 executed / 49 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

Terminal Wave 1R result:

**FP-21 — PASS_WAVE_1R_OVERLAP_BOOT**

**FP-22 — PASS_WAVE_1R_OVERLAP_BOOT**

Accounting after terminal shared-truth acceptance becomes:

**144 documented / 52 executed / 51 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

FP-24 remains **N_A_CURRENT_ACCEPTED_CONTRACT / NOT_EXECUTED**.

FP-33 remains **INCONCLUSIVE / NOT_EXECUTED_IN_WAVE_1R**.

TEMP-016 is consumed only in the separate terminal shared-truth closeout after this runtime/evidence PR merges.

No permanent P-001/CF, pair/runtime certification, provider integration, updater/TUF, production deploy/release or ADR-0010 promotion follows.

## 9. Terminal conclusion

**Wave 1R — PASS_FP21_FP22_OVERLAP_BOOT / NON-CERTIFYING**

The bounded evidence establishes the declared overlap boot behavior for exact F1/P0 and F0/P1 on the accepted minimum/reference disposable runtimes. It does not convert either exact pair into a certified production pair and does not certify the wider update transport, provider, deployment, migration, rollback or release surface.
