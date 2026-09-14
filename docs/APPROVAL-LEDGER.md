# WPEssential — Approval Ledger & Work Lifecycle

Status: **Active governance**  
Last reviewed: **2026-09-15**

| Approval ID | Scope | Work ID | Status | Included | Excluded / notes |
|---|---|---|---|---|---|
| GOV-OWNER-CONSENT-000 | PROJECT | WPEssential | **SUPERSEDED** | Historical pre-consent gate | Superseded by explicit owner grant `GOV-OWNER-CONSENT-001` on 2026-08-29. |
| GOV-OWNER-CONSENT-001 | PROJECT | WPEssential | **ACTIVE** | Implementation Baseline / Adoption Gate; machine-enforced architecture guards; Milestone 1 Platform Foundation; subsequent module source development across the accepted 56-surface architecture; development/test build, CI, automated tests, migrations and dependencies required by approved milestones | Does **not** authorize production deployment/release, destructive live-site/customer-data mutations, chargeable/irreversible external-provider side effects, live payment/communication/provider-authority actions, or destructive production reset/restore/migration/rescue. Those require separate privileged approval and recovery evidence. |
| GOV-OWNER-CONSENT-P006-001 | MILESTONE | P-006 | **ACTIVE** | Explicit ADR-0014-scoped authorization recorded 2026-09-15 for bounded P-006 FP execution in disposable/sandbox environments under Issue #939 and the fixed P-006 executable-evidence protocol; permits candidate package/build evidence, bounded tests/CI, disposable WordPress/runtime fixtures where separately dependency-ready, reversible sandbox DB state where required by an authorized fixture, and evidence recording | Does **not** authorize production deployment/release; live provider/billing/payment/license/allocation calls with authoritative, chargeable or irreversible external side effects; production credentials/data; irreversible/destructive database, schema, user, content, provider or production mutations; or promotion of adjacent P-001/P-005/P-007/P-008/OAuth/Product License/TUF/Membership/provider certifications. Protocol stop-the-line, technical, security and recovery gates remain mandatory. Owner wording is durably recorded in Issue #924 comment `5671331999`. |

## Current lifecycle

Project state: `ACTIVE_EXISTING_PROJECT`.
Execution mode: **`IMPLEMENTATION_GATED`**.
Lifecycle: **`IMPLEMENTING_PLATFORM_FOUNDATION`**.
Accepted product scope: **56/56 surfaces**.
Source implementation authorization: **56/56**, executed only through bounded milestone/work-package gates.
WP119 / ADR-0214 and WP120 / ADR-0215: **DONE / PASS**.
Current executable work: **WP121 — Milestone 1 Platform Foundation**.
P-006 scoped executable evidence authorization: **ACTIVE / bounded by `GOV-OWNER-CONSENT-P006-001` and Issue #939**.
Business-module handoff remains blocked until the WP121 shared-foundation readiness gate passes.

## Approval interpretation

The owner explicitly authorized this sequence:

`Implementation Baseline / Adoption Gate → Machine-enforced architecture guards → Milestone 1 Platform Foundation → module development`.

The project-level grant prevents repeated approval prompts for ordinary reversible source-development decisions that remain inside accepted architecture and milestone budgets. It does **not** waive technical, security, recovery, provider or release gates.

The P-006 milestone grant additionally authorizes bounded compatibility evidence execution only inside disposable/sandbox environments. It does not convert production/live/destructive privileges into ordinary development authority and does not promote any certification without the evidence required by the P-006 protocol.

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

ADR-0014 and `DEVELOPMENT-CONSENT.md` remain controlling. Owner may revoke or narrow `GOV-OWNER-CONSENT-001` or `GOV-OWNER-CONSENT-P006-001` at any time.