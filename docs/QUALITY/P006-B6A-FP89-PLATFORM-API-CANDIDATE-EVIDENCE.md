# P-006 B6a — FP-89 Isolated Platform API Candidate Prerequisite Evidence

Issue: #1093

Runtime/evidence PR: #1094

Authorization: `GOV-P006-B6A-FP89-PLATFORM-API-CANDIDATE-001`

Classification: **TEST-ONLY CANDIDATE/HARNESS PREREQUISITE / NON-RUNTIME / NON-CERTIFYING**

Accepted prerequisite source head: `bf7f5dc9dd73d13143dcc19d5c5743f1f5034ef8`

## 1. Purpose

B6a implements only the deterministic candidate prerequisite required before any later formal FP-89 execution.

It does **not** boot WordPress, connect to MySQL, run migrations, mutate product runtime source, create a P-001/CF runtime grant, execute FP-89, or promote a Free/Pro pair/runtime/migration certification.

Formal P-006 accounting remains:

**144 documented / 57 executed / 57 PASS / 0 FAIL / 0 INCONCLUSIVE / 0 certified Free/Pro pairs / 0 runtime certifications**.

## 2. Exact-head verification

Accepted workflow:

- workflow: **P-006 B6a FP-89 Platform API Candidate**;
- run id: **35539119381**;
- source head: `bf7f5dc9dd73d13143dcc19d5c5743f1f5034ef8`;
- latest-change gate: **PASS**;
- deterministic candidate job: **PASS**.

Exact-head Governance Gate:

- run id: **35539119359**;
- result: **PASS**.

The accepted implementation diff before evidence finalization contains only:

- `.github/workflows/p006-b6a-fp89-platform-api-candidate.yml`;
- `tools/p006/p006-b6a-fp89-platform-api-candidate.php`;
- `config/coordination/agent-work-queue.json`.

No product runtime/source file is modified.

## 3. Immutable candidate identity

| Node | ZIP SHA-256 | Payload-tree SHA-256 | Entries | Main-entry SHA-256 |
| --- | --- | --- | ---: | --- |
| F0 | `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80` | `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9` | 239 | `b2a32e91d7f7bbc3cbe22e37094274e5d1ccdbc0d31c79e385f02e399e679e61` |
| P0 | `bd74077f22c7268765519ac2d4c64dd785ba59c9eee68f5a5f1b4d8fd8de0467` | `fbaa10957eeb482bd248ef2833b021202068ce7f420fe2d78f35b0e522b0bfd0` | 287 | `aabf93d673c4dae5cf511d540079a2db1aef869069e047c3ac5c22c0bfe5b368` |
| P-API | `5a2634a0d3c72ff7ede89545837c3fe2773aefad90a3fed4b060485c55645af9` | `8985a20a68c7aafeee2d758845b26bddfe34ba83b5141ea335fad174acd52a69` | 287 | `c1e0183accef5e65d120dd5e22b73dfc9134ec8c5c4897156ae1356407ea1069` |

Pair identities:

- canonical F0/P0: `28e96f5207d3dc195bbf1b1a883ea7dcea5a314580661de5400e4e299dbd5211`;
- isolated F0/P-API: `9447c78174b7702879f318339c60942ebc20be9a477df78f9dbd2b734705d841`.

The workflow proves F0 is byte-identical to the canonical Free distributable and P0 is byte-identical to the canonical Pro distributable.

## 4. Variant boundary

Only this ZIP entry differs between P0 and P-API:

`wpessential-pro/wpessential-pro.php`

Only these constants change:

- `WPE_PRO_MIN_PLATFORM_API_VERSION`: `0.1.0` → `0.2.0`;
- `WPE_PRO_MAX_PLATFORM_API_VERSION`: `0.1.0` → `0.2.0`.

Unchanged dimensions include:

- Pro marketing version: `0.1.0-dev`;
- min/max Free marketing version: `0.1.0-dev`;
- min/max Platform schema generation: `1`;
- Pro schema generation: `1`;
- all non-main-entry Pro payload files.

This prevents an earlier marketing-version or schema mismatch from masking the Platform API condition.

## 5. Real compatibility preflight result

The candidate builder invokes the repository's actual:

`WPEssential\Modules\Compatibility\LocalCompatibilityPreflight::evaluate()`

Canonical F0/P0 result:

- state: `compatible`;
- dimension: `pair`;
- premium boot allowed: true;
- premium migrations allowed: true.

Isolated F0/P-API result:

- state: `platform_api_too_old`;
- dimension: `platform_api`;
- reason: `platform_api_below_supported_minimum`;
- remediation: `update_free`;
- Free version: `0.1.0-dev`;
- Pro version: `0.1.0-dev`;
- Free Platform API: `0.1.0`;
- premium boot allowed: false;
- premium migrations allowed: false.

No duplicate/custom compatibility evaluator is used for the terminal decision.

## 6. Determinism and immutable artifact

The canonical package pair is built twice byte-identically.

The B6a candidate builder is then executed twice from the same exact source and canonical ZIP inputs. These files compare byte-identically across both derivations:

- `f0.zip`;
- `p0.zip`;
- `p-api.zip`;
- `candidate-identity.json`.

Accepted immutable artifact:

- artifact id: **10613911938**;
- artifact name: `p006-b6a-fp89-candidates-35539119381`;
- archive digest: `sha256:dab04416de5ff4a9e1c2562b0aef0e8d147b953a67d5ab5bdaff07848838c44a`.

## 7. Security and scope invariants

The prerequisite workflow uses:

- exact source-head checkout;
- pinned GitHub Action commit SHAs;
- `permissions: contents: read`;
- no repository write credential persistence;
- path-bounded change gating;
- canonical packaging validation before candidate derivation;
- ZIP entry traversal checks;
- deterministic entry ordering, timestamp, mode and compression normalization;
- exact changed-entry and changed-constant assertions.

It does not use WordPress/MySQL services, external providers, license/billing services, updater/TUF/signing, production/live data or deployment credentials.

## 8. Terminal prerequisite decision

**B6a prerequisite: PASS / MERGE-READY SUBJECT TO FINAL EVIDENCE-ONLY SYNCHRONIZE CHECK.**

The repository now has the exact deterministic candidate identity required for a later separately authorized FP-89 formal runtime fixture.

FP-89 remains **NOT FORMALLY EXECUTED**.

No P-006 counter, pair certification, runtime certification, migration certification, permanent P-001/CF status, ADR-0010 state, deployment or release authority changes.
