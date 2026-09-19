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

**Pending exact-head CI.**

Before terminal closeout this section must pin:

- exact implementation head;
- Governance run;
- FP-51 workflow run;
- candidate artifact id/digest;
- minimum artifact id/digest;
- reference artifact id/digest;
- terminal artifact id/digest;
- exact F0/P0/P1 ZIP and payload-tree identities;
- baseline and target pair identities;
- complete P1 compatibility proof;
- all 12 fault-state manifests / staged-rejection identities;
- truncation source/truncated hashes and sizes;
- sentinel preservation;
- exact P0 recovery identity;
- final formal result.

## 10. Accounting boundary

Before execution:

**144 documented / 47 executed / 46 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**

If FP-51 terminally PASSes and exact-head evidence is accepted:

**144 documented / 48 executed / 47 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**

If FAIL or INCONCLUSIVE occurs, accounting must record the actual terminal result.

## 11. Non-promotion boundary

No FP-50 or FP-52 result follows.

No permanent P-001/CF certification, pair certification, runtime certification, generic WordPress recursive-copy certification, updater/TUF authority, rollback/migration certification, ADR-0010 promotion, production deploy/release or #947 authority follows.
