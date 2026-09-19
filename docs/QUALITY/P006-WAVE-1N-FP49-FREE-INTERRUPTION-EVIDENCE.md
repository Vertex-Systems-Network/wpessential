# P-006 Wave 1N — FP-49 Free Interruption Evidence

Issue: #1058

Authorization: `GOV-P001-CF-TEMP-012`

Fixture: **FP-49 only**

Exact authorized base: `8f0416e017a371212b61cee0be0a04ccfa21a7c6`

Classification: **FORMAL FP-49 RUNTIME EVIDENCE / NON-CERTIFYING**

## 1. Fixture contract

Accepted Lane B intent:

> Fault-inject Free replacement at defined file-copy cut points; every resulting state must be detectable/non-runnable without Pro migration.

This tranche uses the accepted #1052 entrypoint-last external publication-owner profile only. It does not claim generic WordPress `move_dir()` or recursive-copy interruption safety.

## 2. Runtime matrix

Required cells:

- minimum — WordPress 6.9 / PHP 8.2 / MySQL 8.4;
- reference — WordPress 7.1 / PHP 8.5 / MySQL 8.4.

Baseline and target graph:

- baseline F0 + P0;
- target Free F0 → F1;
- Pro remains P0;
- every interruption cell restores exact F0 before the next observation.

## 3. Deterministic interruption coverage

The accepted #1055 applicability review pinned a 238-file F1 non-entry payload and these five formal cut points:

| Cell | Copied F1 non-entry files | Configured Free entry |
| --- | ---: | --- |
| F49-01 | 1 / 238 | absent |
| F49-25 | 59 / 238 | absent |
| F49-50 | 119 / 238 | absent |
| F49-75 | 178 / 238 | absent |
| F49-100 | 238 / 238 | absent |

If the exact current candidate no longer contains 238 non-entry files, the workflow must stop before formal fixture execution.

## 4. Required assertion per cut point

Each cut point must prove:

- exact staged F1 candidate identity is pinned;
- exact deterministic copied-file prefix is pinned;
- exact state-manifest SHA-256 is recorded;
- `wpessential/wpessential.php` is absent and unreadable;
- a fresh PHP/WordPress process returns without fatal/error;
- incomplete Free generation is non-runnable;
- observed compatibility state is recorded without forcing a reason string;
- premium boot is denied;
- premium migrations are denied;
- premium mutations are denied;
- active-plugin records remain inspectable;
- outbound WordPress HTTP attempts are zero;
- exact F0 tree is restored entrypoint-last;
- recovered F0/P0 fresh process returns compatible.

## 5. Main-entry boundary

A readable partial/corrupt configured Free entry is not a supported FP-49 cell.

The accepted publication owner proves interruption safety by **withholding the configured entry until the non-entry tree is exact**. A corrupt staged entry is rejected before live publication.

This fixture must not be described as proving corrupt PHP is safe to parse.

## 6. Harness and workflow

Formal harness:

`tools/p006/p006-wave1n-fp49-free-interruption.php`

Workflow:

`.github/workflows/p006-wave1n-fp49-free-interruption.yml`

The harness reuses only bounded deterministic helpers from the already accepted publication-owner mechanism. Formal authority is `GOV-P001-CF-TEMP-012`; historical runtime authority is not reused.

Timeout-safe CI shape:

- candidate build <=15 minutes;
- minimum runtime <=18 minutes;
- reference runtime <=18 minutes;
- `fail-fast:false`;
- stale-run cancellation;
- per-runtime immutable artifacts;
- terminal aggregate <=5 minutes.

## 7. Stop conditions

The fixture must stop rather than manufacture PASS if:

1. configured Free entry exists at any interruption cut;
2. deterministic copied prefix drifts;
3. candidate non-entry count is not exactly 238;
4. a partial state produces a fatal/error;
5. premium boot, migration or mutation becomes allowed;
6. exact state identity cannot be proven;
7. exact F0 recovery fails;
8. recovered F0/P0 is not compatible;
9. outbound WordPress HTTP occurs;
10. product runtime source changes are needed.

## 8. Terminal evidence

**Pending exact-head CI.**

Before terminal closeout this section must be updated with:

- final exact source head;
- Governance run;
- FP-49 workflow run;
- candidate artifact id/digest;
- minimum runtime artifact id/digest;
- reference runtime artifact id/digest;
- terminal artifact id/digest;
- exact F0/F1/P0 ZIP and payload-tree hashes;
- ordered non-entry list SHA-256;
- five per-runtime state-manifest hashes;
- exact recovery tree hash;
- final FP-49 result.

## 9. Formal accounting boundary

Pre-execution accounting:

**144 documented / 46 executed / 45 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**

If FP-49 terminally PASSes and exact-head evidence is accepted:

**144 documented / 47 executed / 46 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**

If the fixture fails or becomes inconclusive, accounting must record the actual terminal result instead.

## 10. Non-promotion boundary

No FP-50, FP-51 or FP-52 result follows.

No permanent P-001/CF certification, Free/Pro pair certification, runtime certification, updater/TUF authority, rollback/migration certification, production deployment, release/GA authority, ADR-0010 promotion or #947 authority follows.
