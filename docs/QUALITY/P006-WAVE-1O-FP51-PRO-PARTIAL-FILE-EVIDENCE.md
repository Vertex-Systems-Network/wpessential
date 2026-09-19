# P-006 Wave 1O — FP-51 Pro Partial-File Evidence

Issue: #1061

Authorization: `GOV-P001-CF-TEMP-013`

Fixture: **FP-51 only**

Exact authorized base: `6fb08e7c4f4dbdd07db0ec22a1fb6327f224e96c`

Classification: **FORMAL FP-51 RUNTIME EVIDENCE / NON-CERTIFYING**

## 1. Fixture contract

Accepted Lane B intent:

> Remove/truncate one Pro file at a time from disposable candidate classes representing bootstrap/preflight/module/runtime categories; preserve data and Free operation.

The fixed finite coverage was accepted by Issue #1055 / PR #1057. This tranche does not widen, replace or simplify that matrix.

## 2. Runtime matrix

Required cells:

- minimum — WordPress 6.9 / PHP 8.2 / MySQL 8.4;
- reference — WordPress 7.1 / PHP 8.5 / MySQL 8.4.

Baseline / target graph:

- baseline F0 + P0 compatible;
- complete target candidate P1 with Free remaining F0;
- every live partial-file cell excludes the configured Pro entry;
- every cell returns to exact P0 before the next one.

Complete P1/F0 compatibility must be proven before the fault matrix.

## 3. Fixed 12-cell matrix

### Bootstrap — staged rejection only

| Cell | Path | Fault | Exposure |
| --- | --- | --- | --- |
| P51-B-MISSING | `wpessential-pro.php` | missing | staged candidate only |
| P51-B-TRUNCATED | `wpessential-pro.php` | readable deterministic half-prefix | staged candidate only |

Both bootstrap faults must be rejected before live mutation. The configured live P0 tree remains exact and executable.

### Preflight

| Cell | Path | Fault |
| --- | --- | --- |
| P51-P-MISSING | `frameworks/Modules/Compatibility/LocalCompatibilityPreflight.php` | missing |
| P51-P-TRUNCATED | same | readable deterministic half-prefix |

### Required top-level modules

| Cell | Position | Path | Fault |
| --- | --- | --- | --- |
| P51-M1-MISSING | first | `frameworks/Modules/Roles/RolesModule.php` | missing |
| P51-M1-TRUNCATED | first | same | readable deterministic half-prefix |
| P51-MM-MISSING | middle | `frameworks/Modules/BuilderWidgets/BuilderWidgetsModule.php` | missing |
| P51-MM-TRUNCATED | middle | same | readable deterministic half-prefix |
| P51-ML-MISSING | last | `frameworks/Modules/Chat/ChatModule.php` | missing |
| P51-ML-TRUNCATED | last | same | readable deterministic half-prefix |

### Runtime support

| Cell | Path | Fault |
| --- | --- | --- |
| P51-R-MISSING | `frameworks/Modules/Roles/WordPressRoleRuntimeEnvironment.php` | missing |
| P51-R-TRUNCATED | same | readable deterministic half-prefix |

The ten non-bootstrap cells are exposed only with `wpessential-pro/wpessential-pro.php` absent.

## 4. Live partial-state contract

For every non-bootstrap fault:

- complete exact P1 is staged first;
- exact old P0 generation is preserved;
- every P1 non-entry file except the single declared fault matches the exact candidate;
- configured Pro entry is absent and unreadable;
- fresh PHP/WordPress process is used;
- Free F0 remains independently usable;
- Free kernel boots;
- required Free modules remain present;
- premium modules remain absent;
- premium boot is denied;
- premium migrations are denied;
- premium mutations are denied;
- no PHP fatal/error occurs;
- active-plugin records remain inspectable;
- persisted sentinel remains unchanged;
- outbound WordPress HTTP attempts remain zero;
- exact P0 is restored entrypoint-last;
- recovered F0/P0 is compatible.

Readable truncated Pro non-entry PHP is never executed because the configured Pro entry is withheld.

## 5. Data-preservation sentinel

The disposable WordPress site stores:

`wpe_p006_fp51_data_sentinel = fp51-preserve-v1`

Every baseline, fault observation and recovery must prove the sentinel remains byte-for-byte unchanged.

This is a bounded fixture sentinel assertion only. It is not broad migration/rollback/schema certification.

## 6. Deterministic truncation rule

For every truncation cell:

1. read the exact complete source PHP file;
2. calculate source size and SHA-256;
3. write exactly `max(1, floor(source_bytes / 2))` bytes to the fault destination;
4. record truncated size and SHA-256;
5. keep the file readable;
6. never execute the truncated file.

The same rule applies to staged bootstrap truncation, but that fault remains staged-only and is rejected before live mutation.

## 7. Harness and workflow

Harness:

`tools/p006/p006-wave1o-fp51-pro-partial-files.php`

Workflow:

`.github/workflows/p006-wave1o-fp51-pro-partial-files.yml`

Timeout-safe shape:

- deterministic candidate build <=15 minutes;
- minimum runtime <=18 minutes;
- reference runtime <=18 minutes;
- `fail-fast:false`;
- stale-run cancellation;
- immutable candidate and per-runtime artifacts;
- fresh PHP process for every observation;
- terminal aggregate <=5 minutes.

## 8. Stop conditions

Stop rather than manufacture PASS if:

1. staged bootstrap corruption mutates live P0;
2. configured Pro entry exists in a live partial state;
3. the wrong fixed file is faulted;
4. more than one P1 non-entry file differs from the declared candidate state;
5. Free F0 becomes unusable;
6. premium module registration occurs;
7. premium boot/migration/mutation is admitted;
8. a PHP fatal/error occurs;
9. sentinel data changes;
10. exact state identity cannot be proven;
11. exact P0 recovery fails;
12. recovered F0/P0 is not compatible;
13. outbound WordPress HTTP occurs;
14. product runtime source must change.

## 9. Terminal evidence

Pre-closeout exact implementation head:

`6e7e40599635876cc7b54108c46bac59eced6903`

CI:

- Governance Gate run **35412194583** — PASS;
- P-006 Wave 1O FP-51 run **35412194610** — PASS;
- deterministic candidate graph — PASS;
- minimum runtime — PASS;
- reference runtime — PASS;
- FP-51 terminal aggregate — PASS.

Immutable artifacts:

| Evidence | Artifact | Digest |
| --- | ---: | --- |
| candidates | 10574708923 | `sha256:8f4d45e68d3c5830800e27b3201b3190e52621aa7690d93aec7c4bf42330a5c1` |
| minimum | 10574424230 | `sha256:4bdd51f1feebe4aa2c63c1ed80a9f19dc5e0ca003605968963fe63b321588c6d` |
| reference | 10574459295 | `sha256:b3a439d01a64795f9fa6629acaadca72a27ffedcfa65b76c02f9b1e32e196f9e` |
| terminal | 10573608656 | `sha256:86c68462ff614aa0153a18842c78d961022b8ff04cbeafa0482cc789a37ad2c9` |

Exact candidate identities:

- F0 ZIP: `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80`;
- P0 ZIP: `0b12ada8265f3032641e09a80ba09f9950d3aef8b526b63c2b3665663aca4178`;
- P1 ZIP: `96acfb8527e62f3ee52f8ef61b801f770315a3a633502ead845c5c4f145b30ac`;
- F0 payload tree: `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9`;
- P0 payload tree: `08ef083162109a15899dba7b14d1e420f327323b20cd1434cf9e7cfa23af09a7`;
- P1 payload tree: `d9a2d208871d684d50e4eabcdb7d4ffb0e2f51cff160ebb311e47d210c3d2a46`;
- baseline F0/P0 pair id: `c564b2fdc079a02dbba57bee0d90dd64fd683605c0fdbca89ab48323b394f2a0`;
- target F0/P1 pair id: `532f93f984bb5ecee1793a4c325c904e84945e35f1f5f682e20b59cbdbc58b26`.

Complete P1/F0 was proven `compatible` before the fault matrix, with exact P1 tree `d9a2d208871d684d50e4eabcdb7d4ffb0e2f51cff160ebb311e47d210c3d2a46`; exact P0 recovery returned `compatible`.

### Staged bootstrap rejection identities

| Cell | Staged state SHA-256 | Faulted staged tree | Live P0 |
| --- | --- | --- | --- |
| P51-B-MISSING | `131c287ce80951c2e7134f3c29b92011b223dc3ebca5ea22f3ceaed49b4246a1` | `b58af5c9b9ab6e21363236df544a39a113a0fd1be3b5272d840f883b6b11293b` | exact / unchanged |
| P51-B-TRUNCATED | `de098153fe5509f6b6c77383e08a7bc1449bc0c8d5e1a51ced40e7ba727a79d8` | `0098f9db2119636fea246a1038d8864d7434f1fefbf8035f8cf6267ff760d343` | exact / unchanged |

The P1 entry source was **10,960 bytes**, SHA-256 `cc004ef6dffaa41eff60da1445355bb7754553e8c10c4cc1f36a5d18555e979b`. Deterministic truncation wrote **5,480 bytes**, SHA-256 `52e299c8fb76562347f11cf69cba53903a91d4de367a61e196c409df533d466c`.

Both staged faults were refused before live mutation. The request continued against exact P0, therefore the live observation remained fully `compatible` with the normal P0 premium modules present. This is expected staged-rejection evidence, not a live partial-Pro observation.

### Live non-entry fault identities

Both runtime families produced the same deterministic state-manifest SHA-256 values:

| Cell | Fault path | Mode | Live state manifest SHA-256 |
| --- | --- | --- | --- |
| P51-P-MISSING | `frameworks/Modules/Compatibility/LocalCompatibilityPreflight.php` | missing | `1ffd6698195e577fd70ab8a353491a7791642a5d0d4cd5d87c25ae807f11093c` |
| P51-P-TRUNCATED | same | truncated | `f803a4cbcc3b5baf31aea0621ba05801ba77e2e30b116eecd9867a3d062145c6` |
| P51-M1-MISSING | `frameworks/Modules/Roles/RolesModule.php` | missing | `b14ee38c71945ef563980c2497e813f943880f4775164f28642231d4db2e3bea` |
| P51-M1-TRUNCATED | same | truncated | `75d82ad9c74d2ea7dde343d705d5fa83eaeebb0c4074d104a7ddace429d8a862` |
| P51-MM-MISSING | `frameworks/Modules/BuilderWidgets/BuilderWidgetsModule.php` | missing | `17c77fdd552978c35c04a312370de51ea89554ca4e3d1acfab60dbdffa583c4e` |
| P51-MM-TRUNCATED | same | truncated | `513b1ef5dc980ff9fce569886002752390cf7b80438269afff24a0d4fb291444` |
| P51-ML-MISSING | `frameworks/Modules/Chat/ChatModule.php` | missing | `31bda2dc8f25d2fe37e49d40d0c9fe1f98f242e859868efbb2a094f5be1ae4eb` |
| P51-ML-TRUNCATED | same | truncated | `299960ba9dcac658a8ac1b318e6c7c04a2d83944bb78b8cb0e2f488f3eab240c` |
| P51-R-MISSING | `frameworks/Modules/Roles/WordPressRoleRuntimeEnvironment.php` | missing | `8fcfa6a3458d5f56c2c6848c3847e1275dad1fd9bec4c0171bceee81969c24e4` |
| P51-R-TRUNCATED | same | truncated | `6c000aab2d227df092fb418c96af6182ae9512c92ec24ce6ba6a890d8a01cd1c` |

Truncation identities:

| Path | Source bytes | Source SHA-256 | Truncated bytes | Truncated SHA-256 |
| --- | ---: | --- | ---: | --- |
| `LocalCompatibilityPreflight.php` | 13,407 | `53333d4bc59947e8dcc22b5119f7f0edd5609150cc47a33a360e40aaf62470f2` | 6,703 | `04f3dbaf041c6618b1fd03904aa34296a40ea96fdf63ca45810317e2e3be26c3` |
| `RolesModule.php` | 4,538 | `3d2cc42cc6d59e5d0aa4098d4fee63cbf2b9cff2ff3355791672da6588d06619` | 2,269 | `a43a191ff61f84e0bceb3a98d02493657b19245df94fad91a0d141c1f7e7023d` |
| `BuilderWidgetsModule.php` | 3,516 | `32719e47868a8d9c593680e47027967b583576b5c81b24c06686103ba5370029` | 1,758 | `340c361affe81ef35146c09db965c8ede7b15ccf3a26412c516b5075bcc8f38e` |
| `ChatModule.php` | 3,327 | `9e3a57fb1951014ad40f92e2ec6ad4ca7bb6fe804d9cc6bbff1e1bb97be820a8` | 1,663 | `b38eda5010e9e29fe53d030aab89a96ee5ac3cc08b2c14e21262a31e52dbce75` |
| `WordPressRoleRuntimeEnvironment.php` | 3,330 | `a556a06f06ba17e17b541655a76992b60f6013dcffb6f7091f000cf8c6132c9f` | 1,665 | `a2ec26b8ea3b4ef4505c25606527ff49841f7b04d5793241dfcffdac56672520` |

Across all **20 live non-entry observations** (10 faults × 2 runtimes):

- configured Pro entry was absent;
- Free bootstrap was ready;
- Free kernel was booted;
- required Free modules remained available;
- premium module list was empty;
- premium boot was false;
- premium migrations were false;
- premium mutations were false;
- no fatal/error occurred;
- sentinel remained exactly `fp51-preserve-v1`;
- outbound WordPress HTTP attempts were zero;
- exact P0 recovery tree was `08ef083162109a15899dba7b14d1e420f327323b20cd1434cf9e7cfa23af09a7`;
- every recovery returned `compatible`.

The compatibility state during live entry-excluded Pro faults is intentionally absent/null because the configured Pro entry does not execute. This is not promoted to `pro_package_incomplete` and does not resolve FP-50's separate expectation blocker.

Terminal formal result:

**FP-51 — PASS_WAVE_1O_PRO_PARTIAL_FILES**

This PASS is limited to the accepted direct-filesystem, entrypoint-last external publication-owner profile.

## 10. Accounting boundary

Before execution:

**144 documented / 47 executed / 46 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**

Terminal accepted accounting from this bounded fixture:

**144 documented / 48 executed / 47 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**

TEMP-013 is consumed at terminal closeout and is not reusable.

## 11. Non-promotion boundary

No FP-50 or FP-52 result follows.

No permanent P-001/CF certification, pair certification, runtime certification, generic WordPress recursive-copy certification, updater/TUF authority, rollback/migration certification, ADR-0010 promotion, production deploy/release or #947 authority follows.
