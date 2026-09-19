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

Pre-closeout exact implementation head:

`63f8c493bf82e8d6d4cbf129377c56db2cc143a4`

CI:

- Governance Gate run **35411082352** — PASS;
- P-006 Wave 1N FP-49 run **35411082371** — PASS;
- deterministic candidate graph — PASS;
- minimum runtime — PASS;
- reference runtime — PASS;
- FP-49 terminal aggregate — PASS.

Immutable artifacts:

| Evidence | Artifact | Digest |
| --- | ---: | --- |
| candidates | 10573987005 | `sha256:6402a9681ec85488589bf0a882de80d6af500c03ebc03985c9e966e4cefb5d1b` |
| minimum | 10573822241 | `sha256:d879095f1e5845426c593b0a11416cc75674bf202105cf8343c4e5d30a0c0b78` |
| reference | 10574337678 | `sha256:7deaac32593afd050a9ccdf0c8539a2cf2fc14ef1f32e7da3096703ba3defa7b` |
| terminal | 10574322713 | `sha256:f071153b5427cd43f758feecf8474d35ac789ea9178eb6ee600303007573906a` |

Exact candidate identities:

- F0 ZIP: `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80`;
- F1 ZIP: `f9401f81b4a65d6f610b05cd445d3d8bdec75b2996296e0deaa13a8a2a265185`;
- P0 ZIP: `0b12ada8265f3032641e09a80ba09f9950d3aef8b526b63c2b3665663aca4178`;
- F0 payload tree: `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9`;
- F1 payload tree: `3e8e56d3887b1f6ed29a66beb0a489d44e547d8b5104c6819e41b91bb6518460`;
- P0 payload tree: `08ef083162109a15899dba7b14d1e420f327323b20cd1434cf9e7cfa23af09a7`;
- baseline F0/P0 pair id: `c564b2fdc079a02dbba57bee0d90dd64fd683605c0fdbca89ab48323b394f2a0`;
- target F1/P0 pair id: `f5157c46d4a29af6df4929cafeaad831fc50a8d38e469c2df73340766bb3faef`;
- ordered 238-file non-entry list SHA-256: `a0f7cb06b7739aacc9e5bba4dcc0f7a7c0f617a31d6866f62a9ede738dc1a792`.

Both minimum and reference runtimes produced the same deterministic interruption-state manifests:

| Cell | Copied files | State manifest SHA-256 | Observed compatibility | Recovery tree |
| --- | ---: | --- | --- | --- |
| F49-01 | 1 | `7303446f9ec15974b9f1feb90ffc6d81765ae3d63498033394dc52996f04aac4` | `free_missing` | exact F0 |
| F49-25 | 59 | `b31ab1cebd5e71c2132cedb326921e8a4059c62c88b7526746e914754d9975f6` | `free_missing` | exact F0 |
| F49-50 | 119 | `74b157a1eb5b1f2c32bcb654617a0926238c5232652d19b18420ec97700a9918` | `free_missing` | exact F0 |
| F49-75 | 178 | `7e033af63a5025ff62573dca57d1e4920cf0cc2942188df5454f6fd9b9302620` | `free_missing` | exact F0 |
| F49-100 | 238 | `9669cb81ae5815d5212ad14d661b3e698ceb68f7d04e3d14ebcb92cb2a89529b` | `free_missing` | exact F0 |

At all ten formal runtime observations:

- configured Free entry was absent;
- Free bootstrap/kernel did not run;
- Pro entry remained present;
- compatibility was `free_missing`;
- premium boot was false;
- premium migrations were false;
- premium mutations were false;
- no fatal/error occurred;
- active-plugin records remained present;
- outbound WordPress HTTP attempts were zero.

Every cell restored the exact F0 payload tree `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9`, and every fresh recovery observation returned `compatible`.

Terminal formal result:

**FP-49 — PASS_WAVE_1N_FREE_INTERRUPTION**

This PASS is limited to the accepted direct-filesystem, entrypoint-last external publication-owner profile. It does not certify generic WordPress recursive-copy interruption.

## 9. Formal accounting boundary

Pre-execution accounting:

**144 documented / 46 executed / 45 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**

Terminal accepted accounting from this bounded fixture:

**144 documented / 47 executed / 46 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**

TEMP-012 is consumed at terminal closeout and is not reusable.

## 10. Non-promotion boundary

No FP-50, FP-51 or FP-52 result follows.

No permanent P-001/CF certification, Free/Pro pair certification, runtime certification, updater/TUF authority, rollback/migration certification, production deployment, release/GA authority, ADR-0010 promotion or #947 authority follows.
