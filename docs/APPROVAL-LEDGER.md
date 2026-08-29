# WPEssential — Approval Ledger & Work Lifecycle

Status: **Active governance**  
Last reviewed: **2026-08-29**

| Approval ID | Scope | Work ID | Status | Included | Excluded / notes |
|---|---|---|---|---|---|
| GOV-OWNER-CONSENT-000 | PROJECT | WPEssential | **SUPERSEDED** | Historical pre-consent gate | Superseded by explicit owner grant `GOV-OWNER-CONSENT-001` on 2026-08-29. |
| GOV-OWNER-CONSENT-001 | PROJECT | WPEssential | **ACTIVE** | Implementation Baseline / Adoption Gate; machine-enforced architecture guards; Milestone 1 Platform Foundation; subsequent module source development across the accepted 56-surface architecture; development/test build, CI, automated tests, migrations and dependencies required by approved milestones | Does **not** authorize production deployment/release, destructive live-site/customer-data mutations, chargeable/irreversible external-provider side effects, live payment/communication/provider-authority actions, or destructive production reset/restore/migration/rescue. Those require separate privileged approval and recovery evidence. |

## Current lifecycle

Project state: `PLANNED_EXISTING_PROJECT` pending baseline verification.  
Execution mode: **`IMPLEMENTATION_GATED`**.  
Lifecycle: **`IMPLEMENTATION_BASELINE_ADOPTION_GATE`**.  
Accepted product scope: **56/56 surfaces**.  
Source implementation authorization: **56/56**, but work is executed only through bounded milestone/work-package gates.  
Current executable work: **WP119 — Implementation Baseline / Adoption Gate**.  
Ordinary feature/module implementation remains blocked until WP119 passes and machine-enforced ownership guards are established.

## Approval interpretation

The owner explicitly authorized this sequence:

`Implementation Baseline / Adoption Gate → Machine-enforced architecture guards → Milestone 1 Platform Foundation → module development`.

The project-level grant prevents repeated approval prompts for ordinary reversible source-development decisions that remain inside accepted architecture and milestone budgets. It does **not** waive technical, security, recovery, provider or release gates.

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

ADR-0014 and `DEVELOPMENT-CONSENT.md` remain controlling. Owner may revoke or narrow `GOV-OWNER-CONSENT-001` at any time.