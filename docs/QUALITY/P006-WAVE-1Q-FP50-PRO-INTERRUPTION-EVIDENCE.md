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

Exact execution identity from successful run **35507343274** at head `c682c728b48f64c9c783101712c776d3da1f322f`:

| Identity | SHA-256 |
| --- | --- |
| F0 ZIP | `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80` |
| F0 payload tree | `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9` |
| P0 ZIP | `0b12ada8265f3032641e09a80ba09f9950d3aef8b526b63c2b3665663aca4178` |
| P0 payload tree | `08ef083162109a15899dba7b14d1e420f327323b20cd1434cf9e7cfa23af09a7` |
| P1 ZIP | `96acfb8527e62f3ee52f8ef61b801f770315a3a633502ead845c5c4f145b30ac` |
| P1 payload tree | `d9a2d208871d684d50e4eabcdb7d4ffb0e2f51cff160ebb311e47d210c3d2a46` |
| F0/P0 pair id | `c564b2fdc079a02dbba57bee0d90dd64fd683605c0fdbca89ab48323b394f2a0` |
| F0/P1 pair id | `532f93f984bb5ecee1793a4c325c904e84945e35f1f5f682e20b59cbdbc58b26` |
| P1 sorted non-entry list | `bcdde6de56c6c24305a167288f27e1487b3666c48639634ef807404014ec23a8` |

P1 has exactly **287 total files / 286 non-entry files**.

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

**PASS — bounded FP-50 evidence / non-certifying.**

Successful exact-head execution:

- PR: **#1073**;
- head: `c682c728b48f64c9c783101712c776d3da1f322f`;
- Governance Gate run **35507343320 — PASS**;
- Wave 1Q run **35507343274 — PASS**;
- candidate graph job — PASS;
- minimum / WordPress 6.9 / PHP 8.2 / MySQL 8.4 — PASS;
- reference / WordPress 7.1 / PHP 8.5 / MySQL 8.4 — PASS;
- terminal aggregate — PASS.

The first workflow attempt, run **35507243222**, stopped in candidate derivation before any runtime cell started because the reused graph builder was missing its canonical ZIP environment inputs. That wiring failure executed no FP-50 runtime cell and is not counted as fixture execution. The workflow was corrected by passing the same canonical Free/Pro ZIP inputs used by the proven predecessor workflows; the successful exact-head run above is the formal result.

Immutable artifacts from run 35507343274:

| Artifact | ID | Digest |
| --- | ---: | --- |
| candidate graph | 10604072092 | `sha256:a3abb5245b707bf145c137f1c6c22a7b01e6f4ef4beeb8b53e77600bb13161c4` |
| minimum runtime | 10604356914 | `sha256:0d03e81b60a95d0d93dd161c1f46ec0a1efcc69728f7da18c53a9f2f64f765c0` |
| reference runtime | 10604356913 | `sha256:c5c2f5d9458495a01d2c4ee68da5e00b6bde41567fb8fd0e37cd4a0a05a4e2b1` |
| terminal marker | 10604771248 | `sha256:909201f74683d2dc751c6913812b794fe340b7c7b2f6e7e38377e1a003408224` |

Both runtimes produced the same deterministic cut-state identities:

| Cell | Count | External publication state | Prefix SHA-256 | State manifest SHA-256 |
| --- | ---: | --- | --- | --- |
| P50-01 | 1 | `EXECUTION_EXCLUDED_LIVE_PARTIAL` | `bead537895379e59c408be85aebb08c2f3fc269a132c49ba961aa82a102daf5c` | `d5e7449709265d2cb8b91a20537c2ba631a20e9d9677ed1aa1a5b19fc4993368` |
| P50-25 | 71 | `EXECUTION_EXCLUDED_LIVE_PARTIAL` | `97d27cea3da264c5b6dbb60d849de7281e0e1b9b6bc4158ae56eb3bce95d856e` | `2316ba734e10b11af465852b20487ea050bd8ab0f36e1b6ab56ea4df8e2f9db7` |
| P50-50 | 143 | `EXECUTION_EXCLUDED_LIVE_PARTIAL` | `08a1facbf4fc140513821f70640e25819dda4c823f03b1106e1c908f5dddc44f` | `529775f533a26fef188e6cc8b7511992b1a9a27ea28c94a60e1f53f5efa145da` |
| P50-75 | 214 | `EXECUTION_EXCLUDED_LIVE_PARTIAL` | `0b20a3b4b290103d534c7e91ea05283a339a33f9f5667ddb16c276cc9e746247` | `d30c6af462412cc2c49d3343eed04a6639d4116327a771ccf1360c8fd8466929` |
| P50-100 | 286 | `NON_ENTRY_COMPLETE_ENTRY_ABSENT` | `bcdde6de56c6c24305a167288f27e1487b3666c48639634ef807404014ec23a8` | `131c287ce80951c2e7134f3c29b92011b223dc3ebca5ea22f3ceaed49b4246a1` |

At all ten observations across the two runtimes:

- configured Pro entry was absent/unreadable;
- observed product-local compatibility state was null, as permitted by the accepted execution-exclusion decision;
- Free bootstrap was ready and Free kernel remained booted;
- required Free modules remained exactly `custom-post-types` and `taxonomies`;
- premium module set remained empty;
- premium boot, migrations and mutations remained denied;
- no fatal/error was observed;
- sentinel remained exactly `fp50-preserve-v1`;
- outbound WordPress HTTP attempt list remained empty.

Every cut recovered exact F0/P0:

- recovered F0 tree `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9`;
- recovered P0 tree `08ef083162109a15899dba7b14d1e420f327323b20cd1434cf9e7cfa23af09a7`;
- recovery compatibility state `compatible`;
- sentinel remained `fp50-preserve-v1`.

## 8. Accounting boundary

Before Wave 1Q:

**144 documented / 49 executed / 48 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

Terminally accepted FP-50 result:

**144 documented / 50 executed / 49 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

`GOV-P001-CF-TEMP-015` is consumed by this one tranche and is non-reusable after terminal closeout.

No permanent P-001/CF, pair/runtime certification, generic WordPress recursive-copy certification, updater/TUF, rollback/migration, provider, multisite, production deploy/release, ADR-0010 acceptance or #947 authority follows from this wave.
