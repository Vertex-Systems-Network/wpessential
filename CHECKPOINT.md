# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-30**
Implementation branch: `implementation/baseline-adoption-gate`  
Planning authority: `planning/master-architecture` through ADR-0213  
Implementation decisions: through **ADR-0222**  
Project classification: **`GREENFIELD_IMPLEMENTATION_WITH_EXISTING_ACCEPTED_PLAN`**  
Execution mode: **`IMPLEMENTATION_GATED`**  
Lifecycle: **`IMPLEMENTING_PLATFORM_FOUNDATION`**  
Development approval: **GOV-OWNER-CONSENT-001 ACTIVE / source scope 56/56**

## Approval boundary

Authorized sequence:

`Implementation Baseline / Adoption Gate → Machine-enforced architecture guards → Milestone 1 Platform Foundation → module development`.

Source implementation, development/test tooling, CI and milestone-scoped schemas/tests are authorized. Production deployment/release, destructive live-site/customer-data operations, chargeable/irreversible provider side effects and separately privileged merge/release operations remain excluded unless explicitly authorized.

## Product/planning truth

Accepted scope remains **56/56 Exhaustive**, Multisite **56/56**, AI Prompt **56/56**, with no known planning or semantic-owner gap after WP118 / ADR-0213.

## Implementation gates

- WP119 / ADR-0214 — **DONE / PASS** — greenfield Implementation Baseline / Adoption Gate.
- WP120 / ADR-0215 — **DONE / PASS** — machine-enforced architecture guards.
- WP121 — **CURRENT / IMPLEMENTING through ADR-0222**.

## WP121 accepted foundation

Implemented/accepted:
- Bootstrap / Kernel / Service Registry / Module lifecycle;
- Definition / ExecutionContext / Policy / Ability / Event core;
- Audit foundation;
- Vault / Assets / Integrations;
- WordPress Capability + Abilities API bridge;
- ADR-0216 engineering contract;
- ADR-0217 atomic compiled-registration persistence/recovery;
- ADR-0218 Definition + Audit MySQL persistence + migration ledger;
- ADR-0219 WordPress.org-facing metadata/contribution/release preparedness + machine-enforced `ABSPATH` guards;
- ADR-0220 real WordPress AJAX/nonce/Policy integration through canonical Ability/Policy;
- **ADR-0221 Action Scheduler public-API backend + tested 3.9.3/4.1.0 coexistence profile**;
- **ADR-0222 WPE-owned durable Job persistence, revision CAS, attempts, leases, heartbeat, checkpoints and stale-worker rejection**;
- minimal Platform admin shell with a server-rendered Runtime Observatory and progressive TypeScript enhancement;
- deterministic `@wordpress/scripts` admin artifact contract (`main.js`, `main.css`, `main.asset.php`);
- Composer and Node 24/npm quality toolchains with committed lockfiles;
- executable 10K/100K compiled-registration scale evidence;
- deterministic runtime-only WordPress plugin ZIP construction and license/package validation baseline;
- real-browser packaged Runtime Observatory E2E/accessibility baseline with a committed locked, read-only Playwright/Playground/Axe toolchain.

## WP121 admin toolchain closure

Exact locked source head `b5b409c58490b179eb1a1f424b952d4a8eceeda1` is GREEN in:
- Architecture Guards push run **33306251922 / #296**;
- Architecture Guards PR run **33306254242 / #297**;
- Platform Compatibility Matrix run **33306254204 / #40**.

The run verifies package metadata, full and distributable npm advisory capture, JavaScript lint, Stylelint, strict TypeScript, deterministic admin production artifacts, the locked PHP quality stack, architecture/engineering guards, PHPUnit, smoke/integration suites and the 10-combination WordPress/PHP/MySQL/MariaDB matrix.

The earlier green bootstrap generated canonical `package-lock.json`; GitHub Actions projected it as commit `155390b2ba180020d7a181ae454094dc622cb7ee`. The exact locked head above then installed that graph with hosted `npm ci` and passed the complete gate.

The npm distributable graph reports **0 vulnerabilities**. The full development toolchain audit still reports upstream transitive advisories from `@wordpress/scripts`; these are retained as diagnostics and are not misrepresented as distributable plugin dependencies.

## WP121 deterministic distributable package closure

Exact package source head `019f496e10e04455cd939c75383fc41661dd26f7` is GREEN in:
- Distributable Package run **33307203441 / #3**;
- Architecture Guards run **33307203442 / #303**;
- Platform Compatibility Matrix run **33307203444 / #46**, with all 10 matrix jobs successful.

The deterministic package gate now:
- stages only plugin runtime code, `readme.txt`, GPL license, generated production Composer autoloader and compiled admin assets;
- excludes repository governance, CI, source-only admin UI, tests, tools, architecture config and Node development dependencies from the installable ZIP;
- validates plugin/readme/Composer GPL metadata before package construction;
- fails closed if runtime Composer packages appear until an explicit distribution-license review is added;
- normalizes ZIP entry ordering, timestamps, Unix file permissions and compression settings;
- performs two independent staging/package executions and requires byte-for-byte identical output;
- validates ZIP integrity, single `wpessential/` install root, required runtime files and forbidden development paths;
- uploads the candidate only as CI evidence; it does not publish or release it.

Verified plugin ZIP evidence:
- SHA-256 **`a61257866088f5bde5a421cef27f9cf8302062eb74eac7a2ee17171415cbe929`**;
- **156 files**;
- **137,667 bytes**;
- install root **`wpessential/`**;
- fixed package mtime **`2000-01-01T00:00:00+00:00`**;
- **0 runtime Composer packages** at this checkpoint;
- CI evidence artifact ID **9730849701**.

This certifies deterministic development-line packaging for the package-gate source head. It is not a WordPress.org stable release and does not certify a future Free/Pro split that does not yet exist in this implementation tranche.

## WP121 real-browser E2E/accessibility closure

WP121.2 is **DONE / PASS**.

Canonical locked/read-only browser source head `9e1039a697db44b6102377eafdf667afdfc79817` is GREEN in:
- Browser E2E Accessibility run **33308824454 / #11**;
- Architecture Guards run **33308824404 / #317**;
- Distributable Package run **33308824442 / #17**;
- Platform Compatibility Matrix run **33308824465 / #60**.

The browser gate now:
- requires committed `tests/e2e/package-lock.json` and installs the E2E graph only with hosted `npm ci`;
- runs with `contents: read`; the temporary bootstrap self-commit/write path has been removed;
- builds the exact deterministic distributable and mounts that unpacked plugin into WordPress Playground;
- uses Playwright **1.62.1**, `@wp-playground/cli` **3.1.51** and `@axe-core/playwright` **4.13.0**;
- navigates the administrator Runtime Observatory in Chromium;
- asserts semantic headings/table/runtime diagnostics, progressive enhancement `data-wpessential-enhanced="ready"` and zero page errors;
- scans only the WPE-owned `#wpessential-admin-root` subtree with Axe so WordPress-core admin chrome is not falsely certified;
- uses a named plugin-owned `<section aria-labelledby="wpessential-admin-title">` inside WordPress admin's existing main landmark, with no Axe-rule suppression.

Hosted browser evidence:
- **2/2 Playwright tests passed**, 0 unexpected, 0 flaky;
- Axe **0 violations / 15 passes** in the scoped WPE-owned Runtime Observatory root;
- locked E2E dependency install reported **0 vulnerabilities**;
- Chromium for Testing **151.0.7922.34** was installed by the pinned Playwright graph;
- evidence artifact ID **9731346638**;
- artifact digest **`sha256:65ec1d2e7ea41e3e4a6f0165a94d6e5a2aa1dcc09b1558c291b6fac2a247b748`**;
- artifact retention **14 days**.

Exact packaged plugin under the canonical browser run:
- SHA-256 **`e59e394dd964cad44d81dbfaeddee38d084381bea64d176de89d308fb78fdb66`**;
- **156 files**;
- **137,688 bytes**;
- install root **`wpessential/`**;
- fixed package mtime **`2000-01-01T00:00:00+00:00`**;
- **0 runtime Composer packages**.

Bootstrap provenance is retained rather than hidden: source head `e6968acfda59ec926a5c85de61fad62c7f236390` passed Browser E2E #8; push Browser E2E #7 (`33308640794`) then committed the generated E2E lock as `898f51722c89ddfdcab606212b00ee6ccf8ddaba`. The final canonical head above removed that temporary write path and proved the strict locked/read-only workflow.

This certifies the current packaged Runtime Observatory baseline only. It does not certify WordPress-core admin accessibility globally, future interactive WPE admin workflows, Multisite behavior, a stable release, or future Free/Pro packaging.

## Engineering/public contract

Mandatory conventions remain:
- namespace `WPEssential` and PSR-4 root `frameworks/`;
- globals `wpessential_*`, constants `WPE_*`;
- exact filters `wpesential/apply_*` and actions `wpessential/hook_*`;
- one typed allowlisted AJAX front door;
- centralized nonce operations;
- compile-on-write registrations;
- bounded/redacted observability;
- fail-closed `ABSPATH` guard on production PHP;
- machine enforcement in hosted CI.

The asymmetric `wpesential/apply_*` spelling is intentional public API.

## ADR-0221 — Action Scheduler coexistence/backend — ACCEPTED

WPE uses public Action Scheduler APIs through an isolated backend adapter. Canonical ownership is:
- hook `wpessential/hook_job_dispatch`;
- group `wpessential-jobs`;
- backend args contain only WPE `job_id`;
- WPE JobService remains business state/idempotency/history authority;
- WPE query/cancel is scoped to exact hook + group + Job UUID;
- third-party Action Scheduler actions are not WPE-owned.

An early source-audit mismatch was corrected before acceptance: upstream `as_has_scheduled_action()` is boolean; action IDs are obtained with `as_get_scheduled_actions(..., 'ids')`.

Hosted evidence run **33267115851 / #178 SUCCESS** proves WordPress 7.1 + MySQL 8.4 with simultaneous Action Scheduler 3.9.3 and 4.1.0 registration, latest-version selection, store initialization, WPE scheduling/uniqueness/query/cancel behavior and third-party action isolation.

This certifies the tested profile, not every future Action Scheduler version, Multisite behavior or final WPE distribution packaging mechanism.

## ADR-0222 — Durable Job persistence/leases/checkpoints — ACCEPTED

WPE-owned durable Job state is now separate from Action Scheduler operational rows.

Migration `009.create-job-persistence` creates network-prefixed jobs and attempts tables. Accepted semantics include:
- explicit network/site scope;
- SHA-256 stable-key idempotency digest instead of raw persistent idempotency key;
- reloadable Job payload/state across service instances;
- revision compare-and-swap mutations;
- retry state persistence;
- serialized lease acquisition for a Job;
- monotonic attempt numbering;
- raw lease token held by worker while DB stores only its SHA-256 hash;
- heartbeat-based lease extension;
- strictly increasing checkpoints;
- terminal attempt completion only under valid unexpired lease;
- expired/stale worker heartbeat/completion rejection;
- bounded reclaim to `abandoned` plus fresh replacement attempt.

Hosted evidence source head `8601d6f17325681c63cdbc97e6b64e1a3892db1e`, run **33267525349 / #209 SUCCESS**.

Run #209 is GREEN across Composer, architecture/engineering guards, PHP syntax, 9/9 smoke, compiled-registration MySQL, Definition/Audit MySQL, real WordPress AJAX/nonce/Policy, Action Scheduler coexistence/backend and **real WordPress durable JobService persistence/lease integration**.

## Important non-certifications

Do not overclaim:
- no WordPress.org submission/stable release;
- no live production DB migration/rollback;
- final public Action Scheduler bundling/coexistence behavior inside a future stable release remains a release decision even though the base plugin ZIP gate is now deterministic;
- Multisite-specific AJAX/queue worker matrices remain pending;
- queue fairness/resource admission/high-concurrency performance is not yet certified;
- automatic Action Scheduler dispatch → Ability → durable attempt lifecycle wiring remains pending;
- Job checkpoint privacy/retention policy remains pending;
- Audit read/retention/privacy/export/legal-hold workflows remain pending;
- current browser evidence certifies the Runtime Observatory baseline only; future critical interactive WPE admin workflows require their own browser/accessibility evidence;
- future Free/Pro package separation remains uncertified until such a distribution tranche exists;
- upstream development-toolchain npm advisories remain recorded and require periodic reassessment;
- business-module implementation has not started.

No live provider call, production deployment, destructive live-site/customer-data mutation, live production migration or irreversible external operation occurred.

## Current next action

Continue WP121:
1. close or explicitly stage the Multisite-specific AJAX/queue-worker runtime matrix;
2. rerun the shared-foundation readiness gate;
3. start the first business-module tranche only after that gate passes.

Repository evidence overrides conversational memory.
