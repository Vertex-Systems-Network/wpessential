# P-006 Wave 1P — FP-52 Free Partial-File Evidence

Issue: #1064

Authorization: `GOV-P001-CF-TEMP-014`

Fixture: **FP-52 only**

Exact authorized base: `b8681eebeede713957fbf314b730290db516d6e4`

Classification: **FORMAL FP-52 RUNTIME EVIDENCE / NON-CERTIFYING**

## 1. Fixture contract

Accepted Lane B intent:

> Remove/truncate representative Free Platform/bootstrap file during disposable replacement; Pro must never mutate/load premium against partial Free.

The fixed representative set was accepted by Issue #1055 / PR #1057.

## 2. Runtime matrix

Required cells:

- minimum — WordPress 6.9 / PHP 8.2 / MySQL 8.4;
- reference — WordPress 7.1 / PHP 8.5 / MySQL 8.4.

Baseline / target graph:

- baseline F0 + P0 compatible;
- complete staged target F1 while Pro remains P0;
- each partial Free destination with `wpessential/wpessential.php` absent;
- exact F0/P0 recovery before the next fault cell.

Complete F1/P0 compatibility must be proven before the formal fault matrix.

## 3. Fixed 8-cell matrix

| Cell | Category | Fault path | Fault |
| --- | --- | --- | --- |
| F52-A-MISSING | composer/bootstrap | `vendor/autoload.php` | missing |
| F52-A-TRUNCATED | composer/bootstrap | `vendor/autoload.php` | readable deterministic half-prefix |
| F52-B-MISSING | Free bootstrap class | `frameworks/Bootstrap/Plugin.php` | missing |
| F52-B-TRUNCATED | Free bootstrap class | `frameworks/Bootstrap/Plugin.php` | readable deterministic half-prefix |
| F52-E1-MISSING | Platform entitlement first | `frameworks/Platform/Entitlements/ProductEntitlementState.php` | missing |
| F52-E1-TRUNCATED | Platform entitlement first | same | readable deterministic half-prefix |
| F52-EL-MISSING | Platform entitlement last | `frameworks/Platform/Entitlements/EntitlementAwareModuleActivationPolicy.php` | missing |
| F52-EL-TRUNCATED | Platform entitlement last | same | readable deterministic half-prefix |

## 4. Partial-state contract

For every cell:

- staged F1 is exact before live mutation;
- exact old F0 generation is preserved;
- destination differs from complete F1 at exactly the one declared non-entry fault;
- configured Free entry is absent and unreadable;
- complete P0 remains unchanged and configured Pro entry remains present;
- fresh PHP/WordPress process is used;
- partial Free does not execute;
- premium module list remains empty;
- premium boot is denied;
- premium migrations are denied;
- premium mutations are denied;
- no PHP fatal/error occurs;
- persisted sentinel remains unchanged;
- outbound WordPress HTTP attempts remain zero;
- exact F0 is restored entrypoint-last;
- recovered F0/P0 is compatible.

Readable truncated Free non-entry PHP is never executed because the configured Free entry is withheld.

## 5. Data sentinel

The disposable WordPress site stores:

`wpe_p006_fp52_data_sentinel = fp52-preserve-v1`

Every baseline, partial observation and recovery must prove this value remains byte-for-byte unchanged.

This is bounded fixture evidence only; it is not broad migration/rollback/schema certification.

## 6. Deterministic truncation

For each truncation:

1. use the exact complete F1 source file;
2. record source byte count and SHA-256;
3. write exactly `max(1, floor(source_bytes / 2))` bytes to the destination;
4. record truncated byte count and SHA-256;
5. ensure the truncated destination remains readable;
6. never execute it.

## 7. Harness and workflow

Harness:

`tools/p006/p006-wave1p-fp52-free-partial-files.php`

Workflow:

`.github/workflows/p006-wave1p-fp52-free-partial-files.yml`

Timeout-safe CI:

- candidate build <=15 minutes;
- minimum runtime <=18 minutes;
- reference runtime <=18 minutes;
- `fail-fast:false`;
- stale-run cancellation;
- immutable candidate/per-runtime artifacts;
- fresh PHP process per partial observation;
- terminal aggregate <=5 minutes.

## 8. Stop conditions

Stop rather than force PASS if:

1. staged F1 is not exact;
2. configured Free entry exists in a partial state;
3. P0 changes;
4. the wrong fixed path is faulted;
5. more than one F1 non-entry file differs from the declared state;
6. partial Free executes;
7. premium modules register;
8. premium boot/migration/mutation is admitted;
9. a PHP fatal/error occurs;
10. sentinel changes;
11. exact state identity cannot be proven;
12. exact F0 recovery fails;
13. recovered F0/P0 is not compatible;
14. outbound WordPress HTTP occurs;
15. product runtime source must change.

## 9. Terminal evidence

Pre-closeout exact implementation head:

`6eee1496adc92a818165e2ed57b270f8a45ee053`

CI:

- Governance Gate run **35436900216** — PASS;
- P-006 Wave 1P FP-52 run **35436900226** — PASS;
- deterministic candidate graph — PASS;
- minimum runtime — 8/8 fixed cells PASS;
- reference runtime — 8/8 fixed cells PASS;
- FP-52 terminal aggregate — PASS.

Immutable artifacts:

| Evidence | Artifact | Digest |
| --- | ---: | --- |
| candidates | 10581564385 | `sha256:1d10d4c40c60c41d8d01be446073c2caaa540d80cfc2179b2b5b0d50419d6026` |
| minimum | 10582875950 | `sha256:5c1ed933fbddadcb3061e60789ec5a1c69b4171747793d9e1a50033c0323731e` |
| reference | 10582930369 | `sha256:e43f86807471e4d3f3de716fa770c8b89bbab93bd0d7b552e0eb580420b9c8e9` |
| terminal | 10581449897 | `sha256:8044e47daccdacb8304bfba1feadfa8cf4ed1dd475e8d747bc94777b7f5604f1` |

Exact candidate identities:

- F0 ZIP: `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80`;
- F1 ZIP: `f9401f81b4a65d6f610b05cd445d3d8bdec75b2996296e0deaa13a8a2a265185`;
- P0 ZIP: `0b12ada8265f3032641e09a80ba09f9950d3aef8b526b63c2b3665663aca4178`;
- F0 payload tree: `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9`;
- F1 payload tree: `3e8e56d3887b1f6ed29a66beb0a489d44e547d8b5104c6819e41b91bb6518460`;
- P0 payload tree: `08ef083162109a15899dba7b14d1e420f327323b20cd1434cf9e7cfa23af09a7`;
- baseline F0/P0 pair id: `c564b2fdc079a02dbba57bee0d90dd64fd683605c0fdbca89ab48323b394f2a0`;
- target F1/P0 pair id: `f5157c46d4a29af6df4929cafeaad831fc50a8d38e469c2df73340766bb3faef`.

Complete F1/P0 was proven `compatible` before the fault matrix. Exact recovery returned F0 tree `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9`, unchanged P0 tree `08ef083162109a15899dba7b14d1e420f327323b20cd1434cf9e7cfa23af09a7`, and `compatible`.

Both minimum and reference runtimes produced the same deterministic fault-state manifests:

| Cell | Fault path | Mode | State manifest SHA-256 |
| --- | --- | --- | --- |
| F52-A-MISSING | `vendor/autoload.php` | missing | `3509831e69ac01c353607cc24338212d25e1669b29c7e3b54ac06cdb9720c64f` |
| F52-A-TRUNCATED | same | truncated | `2889f7144750fd813d0b974642a77b6f575253e94119411bd71fdff5aa72dc45` |
| F52-B-MISSING | `frameworks/Bootstrap/Plugin.php` | missing | `67f0cda2e3cc5aa581955bfa157e0f131d351c8eda51fc53b4f63cea9350d81a` |
| F52-B-TRUNCATED | same | truncated | `0b84b482152efdd54a34e431019918f35d42df7b297197cfc7d1f4a482b66bce` |
| F52-E1-MISSING | `frameworks/Platform/Entitlements/ProductEntitlementState.php` | missing | `3885aa6cdf95b51496982f2bfb8d7ab7613f68f33e1b6f0cef0f501b527e30a6` |
| F52-E1-TRUNCATED | same | truncated | `3279e8cb6009a77a372800525a261534b65a37682f165c41c23316d3e0c24f84` |
| F52-EL-MISSING | `frameworks/Platform/Entitlements/EntitlementAwareModuleActivationPolicy.php` | missing | `66b151185f67a898852ef36659cca64291a17dc97ea7bed2b06a3d1b178d916e` |
| F52-EL-TRUNCATED | same | truncated | `8f1d65d2032c256ccf20795e0908ed8a89d0941d708fcc015c1ce66b70267524` |

Truncation identities:

| Path | Source bytes | Source SHA-256 | Truncated bytes | Truncated SHA-256 |
| --- | ---: | --- | ---: | --- |
| `vendor/autoload.php` | 748 | `79c30eda7334e2cc9fb1f98ab169e11f12c6fd6441de0cd3349e5bbe5f8c2c15` | 374 | `83fb4633003eaec658b156aad092933c5847af1ca7453830d13892d97abccea0` |
| `frameworks/Bootstrap/Plugin.php` | 17,804 | `72818b3d4fecf143a8acaea1643a99b103d52f4b6246d64b77bcb169516c291e` | 8,902 | `9361ad1a8c9b99841dad80277245d5120792e27088571437b3e2287f5490ee0b` |
| `frameworks/Platform/Entitlements/ProductEntitlementState.php` | 517 | `3818b76ee7232a29bdc96033a37253cab152b11c04b276feb5811eaa02bfa860` | 258 | `883e7fcfaed30063cecd5bf1a273587c10ad5b4719431d33b5b12901648dcf19` |
| `frameworks/Platform/Entitlements/EntitlementAwareModuleActivationPolicy.php` | 867 | `c33ba66093cc8df64f9ea0e7b640c21572a09ff9efbffd957e739be069ce01dd` | 433 | `1966af7859b3277824801e40c5d8261b96cf5864e7420b63aa81a45c0340acde` |

Across all **16 formal partial-state observations**:

- compatibility state was `free_missing`;
- configured Free entry was absent;
- complete Pro entry remained present;
- Free bootstrap was false;
- Free kernel was not booted;
- premium module list was empty;
- premium boot was false;
- premium migrations were false;
- premium mutations were false;
- no fatal/error occurred;
- P0 remained exactly `08ef083162109a15899dba7b14d1e420f327323b20cd1434cf9e7cfa23af09a7`;
- sentinel remained exactly `fp52-preserve-v1`;
- outbound WordPress HTTP attempts were zero;
- recovery returned exact F0/P0 and `compatible`.

Terminal formal result:

**FP-52 — PASS_WAVE_1P_FREE_PARTIAL_FILES**

This PASS is limited to the accepted direct-filesystem, entrypoint-last external publication-owner profile. It does not prove that corrupt PHP is safe to execute.

## 10. Accounting boundary

Before execution:

**144 documented / 48 executed / 47 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**

Terminal accepted accounting from this bounded fixture:

**144 documented / 49 executed / 48 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**

TEMP-014 is consumed at terminal closeout and is not reusable.

## 11. Non-promotion boundary

FP-50 remains expectation-blocked and unexecuted.

No permanent P-001/CF certification, pair certification, runtime certification, generic WordPress recursive-copy certification, updater/TUF authority, rollback/migration certification, ADR-0010 promotion, production deploy/release or #947 authority follows.
