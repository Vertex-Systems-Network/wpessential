# WPEssential — P-007 CI / Quality Matrix Executable Evidence Protocol

Status: **Phase 0 planning only / EXECUTION NOT AUTHORIZED**  
Work package: `P0-M00-WP10`  
Related: ADR-0011, ADR-0014, P-001/CF, P-002/UI, P-008/BT, `docs/QUALITY-GATES.md`, `docs/QUALITY/CI-TEST-MATRIX-PLAN.md`, release/recovery governance.

## 1. Purpose

Freeze the evidence required before WPEssential can claim a reliable CI/quality gate system.

The protocol is CI-provider-neutral. GitHub Actions may later be an adapter because the repository is hosted on GitHub, but the evidence semantics must survive another provider.

This document authorizes no workflow/config creation, runner, package install, container, build or test execution.

## 2. Current state

- `.github/` currently contains the PR template only; no workflow implementation is verified.
- branch protection/rulesets: **UNKNOWN** unless later directly verified.
- CI runtime/provider certification: **none**.
- P-007 fixtures defined: **CI-01…CI-120**.
- fixtures executed: **0/120**.

## 3. Core invariants

CI must eventually prove, not merely display:
- FAST and FULL gates are distinct and correctly triggered;
- minimum/current compatibility profiles are first-class;
- the actual built artifact is tested;
- untrusted code cannot access trusted secrets/tokens;
- baseline failures and flaky tests are disclosed, not hidden by reruns;
- provider outages/infrastructure failures are not mislabeled as product passes/fails;
- security/negative requirements become durable regression evidence where practical;
- required checks are selected by risk/reliability, not maximum job count;
- release evidence identifies the exact source/config/lock/artifact/environment;
- informational upstream/trunk lanes cannot silently block normal releases unless policy explicitly promotes them;
- no green badge can override an unexecuted mandatory module-specific evidence gate.

---

# 4. Fixed fixtures

## Group A — Repository/provider capability baseline — CI-01…CI-08

### CI-01 — CI configuration inventory
Record actual workflow/config files and provider adapters on the target revision; absent remains absent, not “planned”.

### CI-02 — branch/ruleset capability detection
Read branch protection/ruleset/required-check state when authorized and accessible; inaccessible becomes `UNKNOWN`, never guessed.

### CI-03 — runner/environment inventory
Record hosted/self-hosted/container/Playground availability actually used by each lane.

### CI-04 — canonical command inventory
Each applicable quality category maps to one documented command from accepted build/test tooling.

### CI-05 — no hidden developer-only gate
A clean CI environment can run every required automated quality category without undocumented local state.

### CI-06 — provider-neutral evidence mapping
Required semantic gates are named independently of provider-specific job IDs; adapter mapping is documented.

### CI-07 — workflow/config revision provenance
Run record identifies exact CI configuration revision used for the source revision.

### CI-08 — unsupported capability behavior
If CI/branch-protection feature is unavailable, status reports `UNAVAILABLE/UNKNOWN` and release policy defines manual/alternate evidence; no fake green state.

## Group B — Untrusted PR and token/secret isolation — CI-09…CI-16

### CI-09 — fork/untrusted PR no secrets
Attacker-controlled PR fixture cannot read provider, release, signing, Vault recovery or privileged repository secrets.

### CI-10 — repository token minimum permissions
PR token permissions are least-privilege and cannot perform unneeded write/admin/release operations.

### CI-11 — privileged event confusion test
Chosen PR event model cannot cause unreviewed attacker code to execute automatically with trusted token merely through event type.

### CI-12 — environment approval boundary
Secret-bearing/provider/release jobs require trusted ref/context and configured approval/environment boundary where applicable.

### CI-13 — logs redact secrets
Synthetic secret injected into approved secret-bearing fixture is not printed by normal commands/error paths/artifacts.

### CI-14 — artifact secret scan
Generated CI artifacts are scanned for synthetic secrets/tokens/private keys before trusted promotion/release use.

### CI-15 — cache poisoning boundary
Untrusted branch cache cannot become blindly trusted release dependency/output without key/provenance validation.

### CI-16 — third-party CI action/plugin trust
External CI actions/plugins have recorded publisher/version/permissions; mutable/unreviewed privileged action use is rejected by future policy.

## Group C — PR FAST Gate — CI-17…CI-24

### CI-17 — repository hygiene
Forbidden-secret/generated-artifact/metadata consistency checks run on meaningful PRs.

### CI-18 — PHP fast static lane
Syntax/coding standards/static analysis run deterministically for affected PHP code.

### CI-19 — JS/TS/style fast lane
Formatting/lint/typecheck/style checks run for affected frontend code.

### CI-20 — targeted unit tests
Affected deterministic unit tests are selected/run without pretending untouched suites were executed.

### CI-21 — targeted integration/permission tests
Changed WordPress/API/policy boundaries run focused integration/permission checks.

### CI-22 — affected production build
Changes affecting frontend/build assets execute the production build, not dev-only transpilation.

### CI-23 — targeted negative/security checks
Relevant MUST-NOT/security regressions run based on change impact.

### CI-24 — FAST report truth
PR report lists exactly executed/skipped/not-applicable checks and does not label FAST as FULL.

## Group D — Minimum/current PR compatibility — CI-25…CI-32

### CI-25 — minimum contract smoke
Minimum accepted/candidate WP+PHP environment installs/boots affected artifact safely.

### CI-26 — current contract smoke
Current stable/reference WP + preferred/current PHP environment installs/boots affected artifact.

### CI-27 — accidental floor increase
Dependency/API change that silently requires newer WP/PHP fails minimum lane.

### CI-28 — minimum UI/build composition
P-002/P-008 minimum-version fixture remains free of later-only package/runtime dependency.

### CI-29 — current enhanced path
Current environment may enable later stable capability without changing core semantic behavior.

### CI-30 — database representative smoke
Changes affecting DB behavior run selected MySQL/MariaDB profile(s) according to P-001 risk mapping.

### CI-31 — Multisite impact trigger
Scope-aware/core platform change includes Multisite lane when claimed behavior can be affected.

### CI-32 — compatibility lane cost policy
Risk-based PR subset is explicit; omitted full combinations are scheduled/main/release evidence, not forgotten.

## Group E — Main/FULL integration lane — CI-33…CI-40

### CI-33 — broad unit suite
Main/milestone FULL lane runs complete applicable unit suite.

### CI-34 — broad WordPress integration
Hooks/options/meta/REST/abilities/migrations and other applicable integration suites run beyond PR focus.

### CI-35 — security regression suite
Persistent auth/IDOR/CSRF/XSS/SQLi/SSRF/upload/replay/secret/multisite tests run as applicable.

### CI-36 — migration/upgrade suite
Supported historical schema/data fixtures run for changes requiring migration evidence.

### CI-37 — critical E2E
Reference user workflows run against installed artifact where implemented.

### CI-38 — UI/accessibility release subset
Required P-002 accessibility/runtime/asset regression subset runs on actual build.

### CI-39 — Free/Pro combinations
When Pro exists, declared compatible/incompatible combinations are tested according to P-006 policy.

### CI-40 — FULL report truth
FULL report distinguishes passed, failed, quarantined, not-applicable and unexecuted mandated suites.

## Group F — Nightly/upstream/risk-based matrix — CI-41…CI-48

### CI-41 — WordPress trunk/nightly signal
Trunk/nightly run is clearly informational/early-warning until promoted by policy.

### CI-42 — newest supported PHP signal
Forward compatibility lane detects future/runtime deprecations without silently changing support floor.

### CI-43 — extended DB combinations
Risk-based MySQL/MariaDB combinations cover DB-sensitive surfaces without full Cartesian explosion.

### CI-44 — persistent object cache variation
Cache-sensitive services run representative persistent-cache lane when implementation exists.

### CI-45 — scheduler/low-traffic variation
Job/Cron-sensitive services run delayed/low-traffic runner profile according to JS evidence.

### CI-46 — large-data/performance variation
Large fixtures execute only on stable measurable harness; regressions are recorded against evidence baseline.

### CI-47 — builder/integration versions
Supported third-party builder/Woo/plugin adapter versions run version-scoped lanes after adapters exist.

### CI-48 — upstream failure classification
Known upstream/trunk break is not rewritten as WPE pass; it remains visible with classification and issue/evidence.

## Group G — Actual build artifact provenance — CI-49…CI-56

### CI-49 — source commit binding
Artifact records exact source commit/ref.

### CI-50 — lock/tool binding
Artifact provenance records dependency lock and relevant build-tool/runtime versions.

### CI-51 — workflow/config binding
Artifact records CI configuration revision and lane that produced it.

### CI-52 — artifact hash
ZIP/package hash is calculated and attached to evidence.

### CI-53 — install exact artifact
Runtime smoke/integration installs the same artifact hash intended for promotion/release.

### CI-54 — no source-tree substitution
A passing source-tree test cannot satisfy an artifact install/package gate for a different ZIP.

### CI-55 — artifact contents gate
P-008 package rules verify no missing assets/dev secrets/Pro leakage/forbidden files.

### CI-56 — artifact promotion provenance
Any handoff from build to release job preserves hash/provenance and does not rebuild silently under different dependencies unless new identity is recorded.

## Group H — P-008 build and UI integration in CI — CI-57…CI-64

### CI-57 — externalization gate
CI detects duplicate React/ReactDOM/JSX or invalid WordPress package externalization.

### CI-58 — route asset isolation gate
Representative unrelated wp-admin route does not receive WPE module bundle/style.

### CI-59 — LTR/RTL build gate
Required LTR/RTL artifacts exist and register correctly.

### CI-60 — localization gate
Translation extraction/registration/artifact completeness checks run.

### CI-61 — generated metadata consistency
Asset/PHP registration metadata resolves all packaged files.

### CI-62 — bundle budget/regression
Once measured baseline exists, route/module/shared bundle regressions are surfaced with justified threshold policy.

### CI-63 — experimental dependency scan
Unapproved experimental foundational packages/routes/widgets are detectable.

### CI-64 — UI behavioral accessibility subset
Critical keyboard/focus/permission-denied workflow remains automated/manual-gated according to release boundary.

## Group I — Baseline failure truth — CI-65…CI-72

### CI-65 — known pre-existing failure reproduction
A failure shown to exist on baseline revision is labeled `BASELINE FAILURE` with signature/revision/date.

### CI-66 — unknown attribution
Failure without baseline proof is `UNKNOWN/INVESTIGATING`, not automatically blamed on baseline or new change.

### CI-67 — baseline remains visible
Known non-blocking baseline failure remains surfaced in PR/milestone/release report until resolved/accepted.

### CI-68 — baseline-blocking policy
Security/data-loss/critical compatibility baseline can still block release even if not introduced by current PR.

### CI-69 — unrelated cleanup prohibition
CI failure does not authorize scope-creep code cleanup; separate work item is created unless current task must fix it.

### CI-70 — baseline signature change
Changed failure signature invalidates stale baseline classification and returns to investigation.

### CI-71 — baseline resolved
When fixed, label/issue/evidence is closed and future recurrence becomes regression.

### CI-72 — baseline report machine/human readability
Evidence is queryable enough to distinguish regressions from known failures across runs.

## Group J — Flaky test and rerun discipline — CI-73…CI-80

### CI-73 — intermittent failure detection
Repeated controlled evidence demonstrates true flakiness before quarantine label is assigned.

### CI-74 — no rerun-until-green hiding
Prior failed attempt remains visible in final task/release report when a rerun passes.

### CI-75 — quarantine metadata
Quarantined test records issue, owner, reason, expiry/review date, blocking status and replacement verification.

### CI-76 — quarantine expiry enforcement
Expired quarantine becomes visible failure/action item rather than permanent silent skip.

### CI-77 — security-test quarantine restriction
Critical security/data-isolation test cannot be casually quarantined without explicit risk acceptance/replacement evidence.

### CI-78 — infrastructure retry classification
Retry for known transient CI infrastructure failure is distinguished from retrying product test until green.

### CI-79 — provider retry classification
Provider sandbox outage/rate limit is reported as provider/infrastructure result, not converted into product pass.

### CI-80 — flaky-rate metric
Suite/test instability is measurable so chronic flaky areas are not normalized as healthy.

## Group K — Security and negative requirements — CI-81…CI-88

### CI-81 — change-to-negative-test mapping
Touched high-risk contract maps to applicable MUST-NOT regression tests.

### CI-82 — auth/permission corpus
Unauthenticated/insufficient-capability/IDOR cases exist for exposed privileged API paths when implemented.

### CI-83 — injection/content corpus
Relevant XSS/SQLi/path/upload/SSRF inputs run for affected modules.

### CI-84 — replay/idempotency corpus
Duplicate/replay/race tests run for applicable Jobs/Workflow/Webhooks/mutations.

### CI-85 — secret leakage corpus
Logs/artifacts/API outputs are scanned for synthetic secrets where Vault/provider credentials are involved.

### CI-86 — multisite wrong-scope corpus
Scope-aware services include deterministic wrong-site/network isolation tests.

### CI-87 — destructive/recovery gate
Reset/Backup/migration/role/security changes require recovery/anti-lockout evidence appropriate to risk.

### CI-88 — security failure cannot be allowed-failure silently
Critical security gate requires explicit risk/governance decision; it is not downgraded merely to keep CI green.

## Group L — Migration and historical data — CI-89…CI-96

### CI-89 — fresh schema
Fresh install schema initializes correctly on declared DB profile.

### CI-90 — previous release upgrade
Representative previous supported schema/data upgrades successfully.

### CI-91 — oldest supported upgrade
Oldest supported upgrade path either passes or is explicitly unsupported/migration-assisted.

### CI-92 — interrupted migration
Crash/interruption fixture resumes/reconciles according to accepted migration contract.

### CI-93 — idempotent rerun
Safe rerun/recovery does not duplicate/corrupt migrated data.

### CI-94 — incompatible downgrade behavior
Downgrade/read-only/restore policy behaves truthfully rather than corrupting newer schema.

### CI-95 — large data migration lane
Risky backfill/index migration has scheduled/release scale evidence.

### CI-96 — actual artifact migration
Migration tests run from packaged code/artifact version pair, not only mocked migration functions.

## Group M — Provider/certification lanes — CI-97…CI-104

### CI-97 — dedicated sandbox credentials
No production credentials are used for automated provider certification fixtures.

### CI-98 — trusted-only provider lane
Provider secrets never enter untrusted PR execution context.

### CI-99 — provider cleanup
Fixtures clean up created sandbox resources or report retained resource IDs safely.

### CI-100 — provider result truth
Authentication success does not imply read/write/event/restore certification beyond provider-specific protocol.

### CI-101 — provider outage
External outage produces explicit unavailable/inconclusive result and preserves last certification truth.

### CI-102 — rate-limit/retry behavior
Provider lane respects certified Retry-After/idempotency/unknown-outcome contract where applicable.

### CI-103 — provider version/profile binding
Result records provider API/plugin/adapter/environment version profile.

### CI-104 — certification promotion gate
Only provider-specific required evidence can update I/ET/C/MB certification status; generic CI green cannot.

## Group N — Performance/resource evidence — CI-105…CI-112

### CI-105 — stable harness prerequisite
No hard performance threshold is enforced until harness variance/baseline is measured.

### CI-106 — p50/p95/p99 capture
Where meaningful, scheduled evidence records latency distribution rather than one average.

### CI-107 — DB query/plan metrics
DB-sensitive workloads record query count/plan/rows examined as applicable.

### CI-108 — memory/payload/bundle metrics
Relevant runtime memory, payload and asset size are retained by workload.

### CI-109 — queue throughput/lag metrics
Job-heavy workloads record throughput/lag/retry/deadlock indicators.

### CI-110 — regression threshold provenance
Threshold changes require evidence/decision, not silent increase to make build green.

### CI-111 — noisy-neighbor/multisite performance
Shared-table/services include representative large-network/noisy-site evidence where required.

### CI-112 — performance failure classification
Performance regression can block release for critical path even when functional tests pass.

## Group O — Reports, required checks and release gate — CI-113…CI-120

### CI-113 — machine-readable result summary
Run emits structured summary of lane/check/status/environment/artifact IDs.

### CI-114 — human-readable final report
Task/release report states Verified, Not Verified, Baseline Failures, Flaky/Quarantined, Known Issues and next safe action.

### CI-115 — required vs informational mapping
Each check/lane is explicitly required, conditional or informational by branch/release boundary.

### CI-116 — branch-protection mapping
When provider capability is available, required semantic gates map to verified provider check names/rules without claiming unavailable state.

### CI-117 — cancellation/superseded run truth
Cancelled/superseded run cannot be mistaken for a successful complete required gate.

### CI-118 — release candidate gate
Exact release artifact cannot advance while any mandatory release gate is failed/unexecuted unless explicit governance-approved exception exists.

### CI-119 — release provenance retention
Final release evidence preserves source/config/lock/environment/artifact hash/test summary sufficiently for later incident diagnosis.

### CI-120 — P-007 production-readiness decision
ADR-0011 remains Proposed until mandatory fixtures pass, selected required checks are reliable/costed, secret isolation is proven, artifact provenance works and branch/release gating can be verified on the chosen provider.

---

## 5. Stop-the-line conditions

P-007 fails if CI permits:
- untrusted PR code to access trusted/provider/release secrets;
- privileged mutable third-party CI code without accepted trust control;
- rerun-until-green hiding a product failure;
- a source-tree pass to certify an untested different release ZIP;
- a competing React/minimum-version/build stop-line issue to be ignored because unrelated jobs are green;
- critical security/data-loss/multisite-isolation failures to be silently allowed-failure;
- cancelled/unexecuted mandatory checks to appear successful;
- release artifact promotion without source/config/lock/hash provenance;
- false claims about branch protection/rulesets when access is unavailable.

## 6. Future evidence report

Authorized P-007 execution must record:
- CI-01…CI-120 result table;
- chosen provider/runner/environment adapters and versions;
- FAST/FULL lane map;
- P-001/P-002/P-008 consumed commands/artifacts;
- secret/token permission model;
- baseline/flaky/quarantine register;
- compatibility matrix and actual artifact IDs/hashes;
- security/migration/provider/performance results as applicable;
- run duration/cost/reliability observations;
- required/informational/branch/release gate mapping;
- final recommendation: accept ADR-0011, revise it, or remain inconclusive.

## 7. Development gate

No `.github/workflows`, other CI config, runner registration, secret/environment configuration, package install, container/Playground environment, build, test, provider call or branch-protection mutation is authorized by this protocol. Explicit owner consent under ADR-0014 remains required.
