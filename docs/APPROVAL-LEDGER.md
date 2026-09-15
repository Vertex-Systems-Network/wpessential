# WPEssential — Approval Ledger & Work Lifecycle

Status: **Active governance**  
Last reviewed: **2026-09-15**

| Approval ID | Scope | Work ID | Status | Included | Excluded / notes |
|---|---|---|---|---|---|
| GOV-OWNER-CONSENT-000 | PROJECT | WPEssential | **SUPERSEDED** | Historical pre-consent gate | Superseded by explicit owner grant `GOV-OWNER-CONSENT-001` on 2026-08-29. |
| GOV-OWNER-CONSENT-001 | PROJECT | WPEssential | **ACTIVE** | Implementation Baseline / Adoption Gate; machine-enforced architecture guards; Milestone 1 Platform Foundation; subsequent module source development across the accepted 56-surface architecture; development/test build, CI, automated tests, migrations and dependencies required by approved milestones | Does **not** authorize production deployment/release, destructive live-site/customer-data mutations, chargeable/irreversible external-provider side effects, live payment/communication/provider-authority actions, or destructive production reset/restore/migration/rescue. Those require separate privileged approval and recovery evidence. |
| GOV-OWNER-CONSENT-P006-001 | MILESTONE | P-006 | **ACTIVE** | Explicit ADR-0014-scoped authorization recorded 2026-09-15 for bounded P-006 FP execution in disposable/sandbox environments under Issue #939 and the fixed P-006 executable-evidence protocol; permits candidate package/build evidence, bounded tests/CI, disposable WordPress/runtime fixtures where separately dependency-ready, reversible sandbox DB state where required by an authorized fixture, and evidence recording | Does **not** authorize production deployment/release; live provider/billing/payment/license/allocation calls with authoritative, chargeable or irreversible external side effects; production credentials/data; irreversible/destructive database, schema, user, content, provider or production mutations; or promotion of adjacent P-001/P-005/P-007/P-008/OAuth/Product License/TUF/Membership/provider certifications. Protocol stop-the-line, technical, security and recovery gates remain mandatory. Owner wording is durably recorded in Issue #924 comment `5671331999`. |
| GOV-OWNER-CONSENT-P001-TEMP-P006-001 | TEMPORARY_MATRIX | P-001/CF → P-006 | **CONSUMED** | Temporary executable environment matrix used exactly for P-006 Wave 1C FP-13/14: minimum WordPress `6.9` + PHP `8.2` + MySQL `8.4`; reference WordPress `7.1` + PHP `8.5` + MySQL `8.4`; disposable/sandbox exact-head evidence only | Consumed by merged PR #965 / main `08cfd952de671167e5b23784b8a983e821b7468e` after terminal FP-13/14 PASS evidence. It does **not** establish broader permanent P-001/CF product-support certification and is **not reusable authority for FP-15+** without explicit renewal/new authorization. Production deploy/release, production credentials/data, live authoritative or chargeable provider/billing/payment/license/allocation side effects, irreversible/destructive operations, Free/Pro pair certification, runtime certification, ADR-0010 acceptance and adjacent evidence-domain promotion remain separately gated. Owner wording and least-privilege interpretation are recorded in Issue #957; its decision gate is closed completed. |
| GOV-OWNER-CONSENT-P001-TEMP-P006-002 | TEMPORARY_MATRIX | P-001/CF → P-006 Wave 1D | **CONSUMED** | One-tranche executable environment matrix used exactly for P-006 Wave 1D FP-15/16 on WordPress `6.9` + PHP `8.2` + MySQL `8.4` and WordPress `7.1` + PHP `8.5` + MySQL `8.4`; disposable/sandbox exact-head compatible Free+Pro activation/load-order evidence only | Consumed by merged PR #975 / main `3e41bfb509235c7f2c6604099ed8acea42828eac` after terminal FP-15/16 PASS evidence. The grant is **not reusable authority for FP-17+** and does not establish permanent P-001/CF product-support certification, Free/Pro pair certification, P-006 runtime certification or ADR-0010 acceptance. Live provider/billing/license/payment/allocation calls, production credentials/data, irreversible/destructive operations and deploy/release remain separately gated. Owner wording is durably recorded in Issue #969 comment `5678050416`; governance record work was Issue #970 / PR #971. |
| GOV-OWNER-CONSENT-P001-TEMP-P006-003 | TEMPORARY_MATRIX | P-001/CF → P-006 Wave 1E | **CONSUMED** | One-tranche executable environment matrix used exactly for P-006 Wave 1E FP-17/18 on WordPress `6.9` + PHP `8.2` + MySQL `8.4` and WordPress `7.1` + PHP `8.5` + MySQL `8.4`; disposable/sandbox exact-head inactive-Pro/dependency-state evidence only | Consumed by merged PR #986 / main `6bdceed17a481e8cd6915d11f86a2f00f1432f63` after terminal FP-17/18 PASS evidence. The grant is **not reusable authority for FP-19+** and does not establish permanent P-001/CF product-support certification, Free/Pro pair certification, P-006 runtime certification or ADR-0010 acceptance. Live entitlement/provider/billing/payment/license/allocation calls, production credentials/data, irreversible/destructive operations and deploy/release remain separately gated. Owner wording is durably recorded in Issue #980; governance record work was Issue #981 / PR #982. |

## Current lifecycle

Project state: `ACTIVE_EXISTING_PROJECT`.
Execution mode: **`IMPLEMENTATION_GATED`**.
Lifecycle: **`IMPLEMENTING_PLATFORM_FOUNDATION`**.
Accepted product scope: **56/56 surfaces**.
Source implementation authorization: **56/56**, executed only through bounded milestone/work-package gates.
WP119 / ADR-0214 and WP120 / ADR-0215: **DONE / PASS**.
Current executable work: **WP121 — Milestone 1 Platform Foundation**.
P-006 scoped executable evidence authorization: **ACTIVE / bounded by `GOV-OWNER-CONSENT-P006-001` and Issue #939**.
Wave 1C temporary P-001/CF matrix grant: **CONSUMED / `GOV-OWNER-CONSENT-P001-TEMP-P006-001`**.
Wave 1D temporary P-001/CF matrix grant: **CONSUMED / `GOV-OWNER-CONSENT-P001-TEMP-P006-002`**.
Wave 1E temporary P-001/CF matrix grant: **CONSUMED / `GOV-OWNER-CONSENT-P001-TEMP-P006-003`**.
P-006 evidence truth after Wave 1E: **144 documented / 14 executed / 14 PASS / 0 FAIL / 0 certified Free/Pro pairs / 0 runtime certifications**.
ADR-0010 remains **Proposed**.
Business-module handoff remains blocked until the WP121 shared-foundation readiness gate passes.

## Approval interpretation

The owner explicitly authorized this sequence:

`Implementation Baseline / Adoption Gate → Machine-enforced architecture guards → Milestone 1 Platform Foundation → module development`.

The project-level grant prevents repeated approval prompts for ordinary reversible source-development decisions that remain inside accepted architecture and milestone budgets. It does **not** waive technical, security, recovery, provider or release gates.

The P-006 milestone grant additionally authorizes bounded compatibility evidence execution only inside disposable/sandbox environments. It does not convert production/live/destructive privileges into ordinary development authority and does not promote any certification without the evidence required by the P-006 protocol.

The first temporary P-001/CF matrix grant was intentionally narrower than a permanent support-floor decision. It unblocked exact-head disposable runtime evidence only for Wave 1C FP-13/14 on the two recorded environment cells. That one-tranche authority is consumed and remains non-reusable.

The second temporary P-001/CF matrix grant was a distinct one-tranche authorization for Wave 1D FP-15/16 only on the same two disposable environment cells. It was consumed by merged PR #975 after raw evidence reconciliation. FP-15 proved Free→Pro activation and compatibility-before-premium contribution in both cells. FP-16 proved real WordPress dependency prevention of Pro-first activation via `plugin_missing_dependencies`, followed by successful Free activation and Pro retry in both cells. This evidence does not establish permanent P-001/CF support, pair/runtime certification, ADR-0010 acceptance or authority for FP-17+.

The third temporary P-001/CF matrix grant was a distinct one-tranche authorization for Wave 1E FP-17/18 only on the same two disposable environment cells. It was consumed by merged PR #986 after raw evidence reconciliation. FP-17 proved that an exact installed but inactive Pro package contributes no Pro files, compatibility publication, premium module/runtime state, migration admission or unexpected runtime HTTP while Free CPT/Taxonomy remain available. FP-18 proved that WordPress blocks Pro activation while Free is inactive with `plugin_missing_dependencies` in both authorized cells; all fresh frontend/admin/REST/cron/CLI-shaped contexts remain non-fatal with both plugins inactive, zero Pro file/runtime/compatibility contribution and zero unexpected runtime HTTP. This evidence does not establish permanent P-001/CF support, pair/runtime certification, ADR-0010 acceptance or authority for FP-19+.

## Planning closure retained

WP113–WP116 closed the 5,808 exact-definition gap identified by ADR-0207. WP117 / ADR-0212 final closure passed. WP118 / ADR-0213 completed the deep structural audit of modules, options, UI, systems, relationships, duplicate semantics, Abilities/events, data ownership and no-bypass flows; its findings were remediated and passed.

Known planning/integration semantic-owner gap: **none known** at the accepted scope.

## Implementation entry invariant

Before ordinary feature code:
1. WP119 must establish the exact VCS/repository/code/runtime/tool/dependency/build/test baseline;
2. baseline failures and UNKNOWN capabilities must be recorded truthfully;
3. a safe implementation branch/checkpoint must exist;
4. ADR-0213 ownership rules must become machine-enforced manifests/validators;
5. the bounded Milestone 1 Platform Foundation work package and FAST/FULL gates must be recorded;
6. project state may then transition to `ACTIVE_EXISTING_PROJECT` / `IMPLEMENTING` only if the gate passes.

ADR-0014 and `DEVELOPMENT-CONSENT.md` remain controlling. Owner may revoke or narrow `GOV-OWNER-CONSENT-001` or `GOV-OWNER-CONSENT-P006-001` at any time, and may explicitly renew or replace a consumed temporary matrix only through a new bounded authorization.