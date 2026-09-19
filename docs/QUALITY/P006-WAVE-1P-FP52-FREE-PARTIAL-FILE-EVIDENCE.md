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

**Pending exact-head CI.**

Before terminal closeout this section must pin:

- exact implementation head;
- Governance run;
- FP-52 workflow run;
- candidate/minimum/reference/terminal artifact ids and digests;
- F0/F1/P0 ZIP and payload-tree identities;
- F0/P0 and F1/P0 pair ids;
- complete F1/P0 compatibility proof;
- all 8 exact state-manifest hashes;
- truncation source/truncated sizes and SHA-256 values;
- P0 unchanged identity;
- sentinel preservation;
- exact F0 recovery identity;
- final formal result.

## 10. Accounting boundary

Before execution:

**144 documented / 48 executed / 47 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**

If FP-52 terminally PASSes and exact-head evidence is accepted:

**144 documented / 49 executed / 48 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**

If FAIL or INCONCLUSIVE occurs, accounting records the actual terminal result.

## 11. Non-promotion boundary

FP-50 remains expectation-blocked and unexecuted.

No permanent P-001/CF certification, pair certification, runtime certification, generic WordPress recursive-copy certification, updater/TUF authority, rollback/migration certification, ADR-0010 promotion, production deploy/release or #947 authority follows.
