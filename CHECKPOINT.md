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
- **ADR-0222 WPE-owned durable Job persistence, revision CAS, attempts, leases, heartbeat, checkpoints and stale-worker rejection**.
- minimal Platform admin shell with a server-rendered Runtime Observatory and progressive TypeScript enhancement;
- deterministic `@wordpress/scripts` admin artifact contract (`main.js`, `main.css`, `main.asset.php`);
- Composer and Node 24/npm quality toolchains with committed lockfiles;
- executable 10K/100K compiled-registration scale evidence.

## WP121 admin toolchain closure

Exact source head `8b822655800f1489ec5be611ae0ca8217d7d7bfb` is GREEN in:
- Architecture Guards push run **33306049048 / #293**;
- Architecture Guards PR run **33306050305 / #294**;
- Platform Compatibility Matrix run **33306050296 / #38**.

The run verifies package metadata, full and distributable npm advisory capture, JavaScript lint, Stylelint, strict TypeScript, deterministic admin production artifacts, the locked PHP quality stack, architecture/engineering guards, PHPUnit, smoke/integration suites and the 10-combination WordPress/PHP/MySQL/MariaDB matrix.

The green push run generated canonical `package-lock.json`; GitHub Actions projected it as commit `155390b2ba180020d7a181ae454094dc622cb7ee`. The current documentation projection intentionally triggers one locked-graph `npm ci` exact-head verification.

The npm distributable graph reports **0 vulnerabilities**. The full development toolchain audit still reports upstream transitive advisories from `@wordpress/scripts`; these are retained as diagnostics and are not misrepresented as distributable plugin dependencies.

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
- final public Action Scheduler packaging mechanism remains a release/build decision;
- Multisite-specific AJAX/queue worker matrices remain pending;
- queue fairness/resource admission/high-concurrency performance is not yet certified;
- automatic Action Scheduler dispatch → Ability → durable attempt lifecycle wiring remains pending;
- Job checkpoint privacy/retention policy remains pending;
- Audit read/retention/privacy/export/legal-hold workflows remain pending;
- browser E2E/accessibility baseline remains pending before critical interactive admin workflows;
- deterministic distributable plugin package/license/Free-vs-Pro validation remains pending;
- upstream development-toolchain npm advisories remain recorded and require periodic reassessment;
- business-module implementation has not started.

No live provider call, production deployment, destructive live-site/customer-data mutation, live production migration or irreversible external operation occurred.

## Current next action

Continue WP121:
1. certify the committed npm lock through hosted `npm ci` exact-head execution;
2. establish browser E2E/accessibility and distributable package validation baselines;
3. close or explicitly stage the Multisite-specific runtime matrix;
4. rerun the shared-foundation readiness gate;
5. start the first business-module tranche only after that gate passes.

Repository evidence overrides conversational memory.
