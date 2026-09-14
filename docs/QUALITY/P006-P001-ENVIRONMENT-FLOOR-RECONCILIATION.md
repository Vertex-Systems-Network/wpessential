# WPEssential — P-006 / P-001 Environment-Floor Dependency Reconciliation

Status: **DEPENDENCY RECONCILIATION ONLY — NO P-001 OR P-006 FIXTURE EXECUTION**  
Issue: **#949**  
Parent: **#939**  
Queue: **v40**  
Exact assessment base: **`main @ 199fbcfe58b1517d2ce680a28579fc5a2b58585e`**  
Claim branch: **`agent/p006-p001-environment-floor-reconciliation-v1`**

## 1. Purpose and hard boundary

This document reconciles the environment-floor dependency that P-006 Lane A places in front of real WordPress/runtime compatibility fixtures. It answers one narrow question:

> Does current accepted repository evidence satisfy the P-001/CF executable compatibility-floor prerequisite for P-006 runtime fixtures?

This document does **not**:

- execute any P-001 or P-006 fixture;
- install, activate, deactivate or update WordPress plugins;
- mutate runtime/source/test/workflow/package behavior;
- mutate a database, provider, account, entitlement or Membership state;
- promote a WordPress/PHP/database profile to certified support;
- accept ADR-0010;
- promote Free/Pro pair or runtime certification;
- authorize deployment or release.

P-006 truth remains **144 documented / 8 executed / 8 passed / 0 failed / 0 certified Free/Pro pairs / 0 runtime certifications**. ADR-0010 remains **Proposed**.

## 2. Current declared environment metadata

At the exact assessment base, the repository declarations are internally consistent at the minimum WordPress/PHP level:

| Source | WordPress | PHP | Other relevant declaration | Evidence classification |
| --- | --- | --- | --- | --- |
| `wpessential.php` | `Requires at least: 6.9` | `Requires PHP: 8.2` | Free version `0.1.0-dev`; Platform API `0.1.0`; Platform schema `1` | Product/package declaration, not P-001 acceptance |
| `wpessential-pro.php` | `Requires at least: 6.9` | `Requires PHP: 8.2` | `Requires Plugins: wpessential`; bounded Free/API/schema compatibility metadata | Product/package declaration, not P-001 acceptance |
| `readme.txt` | `Requires at least: 6.9`; `Tested up to: 7.1` | `Requires PHP: 8.2` | Stable tag `0.1.0-dev` | Distribution metadata, not P-001 acceptance |
| `composer.json` | n/a | `>=8.2` | Composer dependency floor | Build/runtime dependency declaration, not P-001 acceptance |

No contradiction was identified between the represented PHP floor in plugin/readme metadata and Composer. The existence of coherent declared minima is necessary evidence, but it does not prove that the minimum environment has been formally accepted as the executable compatibility floor.

## 3. Current Platform Compatibility regression infrastructure

`.github/workflows/platform-compatibility.yml` currently exercises a real WordPress compatibility matrix using:

- WordPress `6.9` and `7.1`;
- PHP `8.2`, `8.3`, `8.4`, and `8.5`;
- MySQL `8.4` for the primary WordPress/PHP matrix;
- exact-head checkout and repository/runtime regression assertions.

This is meaningful executable regression infrastructure. It demonstrates that the repository has machinery capable of exercising the declared minimum and current/reference WordPress/PHP combinations.

A concrete accepted historical regression example is merged PR **#217**. Its exact candidate head `6ac0948c14f922ae95d673a02bbd44c0f91e1ab3` recorded:

- **Platform Compatibility Matrix #613 — SUCCESS**;
- a real WordPress reference workflow across WP `6.9`/`7.1` × PHP `8.2`/`8.3`/`8.4`/`8.5` with MySQL `8.4`;
- additional MariaDB `10.11` reference lanes for WP `6.9` and `7.1` on PHP `8.4`.

That evidence is accepted regression evidence for the tested source head. It is **not** a repository record accepting a P-001/CF floor, and it must not be relabeled as such.

## 4. Governing P-006 requirement

`docs/QUALITY/P006-FREE-PRO-COMPATIBILITY-EXECUTABLE-EVIDENCE-PROTOCOL.md` lists an environment prerequisite before P-006 execution: an **accepted executable compatibility floor or explicitly scoped temporary P-001 matrix evidence**.

The same protocol requires exact artifact/environment identity and preserves binary/package compatibility, Platform API compatibility, schema compatibility, entitlement, Membership authorization and updater trust as separate truth domains. A successful regression workflow therefore cannot silently upgrade the P-001 acceptance state.

For baseline boot fixtures, the protocol explicitly binds:

- **FP-13** to Free-only clean activation on the **accepted minimum environment**;
- **FP-14** to Free-only boot on the **current/reference environment**;
- later runtime/load-order/mismatch fixtures to disposable WordPress contexts whose environment semantics must be known rather than inferred.

## 5. Lane-A dependency statement is explicit

`docs/QUALITY/P006-PLANNING-LANE-A-ARTIFACT-BOOTSTRAP.md` explicitly classifies the existing Platform Compatibility workflow as reusable **non-P-006 regression infrastructure**, not by itself the accepted P-001/P-006 certification matrix.

Lane A also defines the following profile only as a **candidate, not certified** environment:

- minimum candidate: WordPress `6.9`, PHP `8.2`, MySQL `8.4`;
- reference/current candidate: WordPress `7.1`, PHP `8.5`, MySQL `8.4`.

It then states that execution must not begin for the environment-dependent runtime work until P-001 compatibility-floor evidence is accepted or a temporary P-001 scope is explicitly authorized. Its FP-13 and FP-14 plans specifically retain the accepted-P-001 dependency.

This is decisive repository evidence that the current matrix and declarations were intentionally **not** self-promoted into P-001 acceptance.

## 6. Search for formal P-001/CF acceptance

The reconciliation searched current repository code/documentation and GitHub issue history for `P-001`, `P001`, `P-001/CF`, `environment floor`, and equivalent accepted-floor language.

The following were found:

- P-006 protocol references to the P-001/CF prerequisite;
- Lane-A candidate environment and explicit non-certifying classification;
- current package/readme/Composer environment declarations;
- current Platform Compatibility workflow infrastructure;
- prior successful real-WordPress matrix evidence such as merged PR #217;
- this reconciliation issue #949.

The following acceptance evidence was **not identified**:

- a merged P-001/CF acceptance issue or decision record;
- an accepted ADR that declares the executable minimum/reference profile as P-001 truth;
- an owner approval that explicitly promotes WP `6.9` + PHP `8.2` + MySQL `8.4` from candidate regression profile to accepted P-001 minimum;
- a separately recorded temporary P-001 matrix exception for FP-13+ runtime execution;
- a current certification record binding exact P-001 environment semantics to P-006 runtime evidence.

Absence of that acceptance record is material because the governing P-006 documents explicitly require it rather than permitting inference from ordinary CI success.

## 7. Classification of current environment evidence

| Evidence | Current classification | Why |
| --- | --- | --- |
| WP `6.9` minimum header/readme | Declared minimum | Metadata declaration alone is not executable-floor acceptance |
| PHP `8.2` minimum header/readme/Composer | Declared minimum | Internally consistent, but not an owner/ADR P-001 decision |
| MySQL `8.4` in compatibility CI | Regression environment | CI service choice does not itself define the product compatibility floor |
| WP `7.1` + PHP `8.5` + MySQL `8.4` | Candidate reference/current profile | Lane A explicitly labels it candidate/not certified |
| Platform Compatibility Matrix | Reusable real-runtime regression infrastructure | Strong executable evidence, but Lane A explicitly says it is not by itself accepted P-001/P-006 certification matrix |
| Merged successful matrix runs | Accepted regression results for their exact heads | They prove those runs, not a P-001 policy/acceptance decision |
| P-006 Wave 1A/1B results | Bounded static/package/harness evidence | FP-01/02/03/05/07/08/10/11 remain within their accepted evidence boundaries and do not establish environment-floor truth |

## 8. Impact on P-006 execution

### 8.1 Blocked by unresolved P-001/CF

The current Supervisor must treat **FP-13+ real WordPress/runtime execution as blocked** wherever the fixture requires an accepted minimum/reference environment or relies on environment semantics that P-001 is supposed to establish.

At minimum this blocks the Lane-A baseline boot transition beginning with:

- FP-13 — Free-only clean activation on accepted minimum;
- FP-14 — Free-only current/reference boot;
- FP-15+ real WordPress activation/load-order/mismatch/request-context fixtures that build on those accepted environment profiles.

This document does not attempt to enumerate every later FP-45…FP-144 cross-gate dependency. Later update, migration, Multisite, security and recovery fixtures may add further prerequisites beyond P-001 and must retain their own lane-specific gates.

### 8.2 Evidence already accepted without P-001 promotion

The completed bounded P-006 evidence remains exactly what its merged issues/PRs proved:

- Wave 1A: FP-01/02/05/07/08 PASS;
- Wave 1B: FP-03/10/11 PASS.

Those results are static/package/local-harness evidence. They do not certify a WordPress environment, do not satisfy P-001, do not certify a Free/Pro pair, and do not authorize runtime fixture expansion.

### 8.3 Future non-runtime work

This reconciliation does **not** create a blanket exemption for arbitrary future harness fixtures. A future static/harness-only P-006 tranche may proceed without P-001 only when a fresh accepted issue/queue slot explicitly scopes that evidence, records why environment-floor semantics do not participate, and preserves the P-001/runtime boundary. No such new tranche is authorized by this document.

## 9. Missing acceptance evidence required to unblock runtime work

Before FP-13+ runtime work can rely on P-001, a dedicated accepted P-001/CF gate should record, at minimum:

1. **Acceptance authority and provenance**
   - owner/ADR authority;
   - exact issue/decision/merge reference;
   - acceptance date and source commit.
2. **Minimum profile**
   - exact WordPress minimum;
   - exact PHP minimum;
   - database engine/version semantics;
   - single-site/Multisite applicability.
3. **Reference/current profile**
   - exact WordPress/PHP/database versions or a deterministic rule for resolving them.
4. **Range semantics**
   - whether intermediate supported WordPress/PHP versions are covered by policy, tested cells, or both;
   - database variants that are supported versus merely regression-tested.
5. **Executable evidence**
   - exact-head workflow/run identities;
   - environment image/package versions;
   - pass/fail state for the accepted cells;
   - relevant raw artifacts/logs where retained.
6. **P-006 binding**
   - explicit statement that the accepted minimum/reference cells are valid for FP-13+ P-006 evidence;
   - no inference from package, entitlement or ADR-0010 state.
7. **Change/revalidation rule**
   - what changes invalidate or require re-acceptance of the floor, including WordPress/PHP/database support changes.

A separately authorized temporary P-001 matrix scope may satisfy the protocol instead, but it must be explicit about the same environment identity and evidence boundaries. Ordinary CI success is insufficient.

## 10. Next gate

The next legitimate dependency action is a dedicated P-001/CF acceptance/readiness gate that either:

- accepts an exact minimum/reference environment using existing plus fresh exact-head evidence; or
- defines a narrower temporary P-001 matrix scope specifically for the next authorized P-006 runtime tranche.

Until such a gate is accepted and merged, the Supervisor must not open FP-13+ runtime execution merely because the current matrix is green.

## 11. Final reconciliation outcome

Current declarations and regression infrastructure are strong and internally useful, but the repository deliberately distinguishes them from formal P-001/CF acceptance. No accepted P-001/CF decision or explicit temporary P-001 runtime matrix authorization was identified at the exact assessment base.

**P001_DEPENDENCY_NOT_SATISFIED**
