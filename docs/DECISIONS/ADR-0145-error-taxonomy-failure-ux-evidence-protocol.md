# ADR-0145 — Error Taxonomy & Failure UX Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP28`

## Decision

Accept `docs/QUALITY/ERROR-TAXONOMY-FAILURE-UX-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for WPEssential error semantics and failure UX across UI, REST, Abilities, Jobs, Workflow, CLI/AI adapters and integrations.

The protocol freezes **ERR-01…ERR-176**.

## Accepted truth boundary

The following remain separate:

`Error category ≠ severity ≠ retryability ≠ HTTP status ≠ UI presentation ≠ provider code ≠ Job terminal state ≠ Workflow state ≠ Audit event ≠ incident severity`

Additional hard separations:
- translated/human error text is not machine/business authority;
- timeout is not confirmed failure when a side effect may already have happened;
- partial completion is not success;
- retry is not rollback;
- raw exception/provider response is not public error envelope;
- correlation ID is not authorization;
- UI presentation does not replace server authorization.

## Fixed evidence coverage

- taxonomy/stable codes/normalized envelopes — ERR-01…ERR-16;
- validation/field failure semantics — ERR-17…ERR-32;
- authorization/policy/privacy-safe denial — ERR-33…ERR-48;
- conflict/concurrency/preconditions — ERR-49…ERR-64;
- retry/rate/timeout/network/unknown outcome — ERR-65…ERR-80;
- partial/batch/workflow/import/provider failure truth — ERR-81…ERR-96;
- cross-channel normalization/parity — ERR-97…ERR-112;
- UI/accessibility/localization/recovery UX — ERR-113…ERR-128;
- security/redaction/privacy/Multisite isolation — ERR-129…ERR-144;
- observability/Audit/incident/support boundaries — ERR-145…ERR-160;
- compatibility/performance/failure injection/composite truth — ERR-161…ERR-176.

## Certification classes

Certify independently:
- `ERR-T` taxonomy/code/envelope;
- `ERR-V` validation/field UX;
- `ERR-A` authorization/privacy-safe denial;
- `ERR-C` conflict/concurrency;
- `ERR-R` retry/rate/timeout/unknown outcome;
- `ERR-P` partial/batch/workflow/provider truth;
- `ERR-X` cross-channel normalization/parity;
- `ERR-U` UI/accessibility/localization/recovery UX;
- `ERR-S` security/redaction/Multisite;
- `ERR-O` observability/incidents/performance/compatibility.

Passing one class never implies another.

## Accepted invariants

1. Stable machine codes remain independent from translated human messages.
2. Category, severity, retryability and transport/UI representation are separate typed concerns.
3. Authorization/privacy checks may intentionally suppress validation/existence details.
4. Conflicts expose safe current/precondition information and never silently overwrite newer state by auto-retry.
5. Automatic write retries require idempotency/duplicate protection/preconditions appropriate to the operation.
6. Timeout/unknown provider outcome remains unknown/reconciliation-needed until authoritative evidence resolves it.
7. Batch/Workflow/Import/Backup/Restore partial outcomes report succeeded/failed/skipped/unknown truthfully.
8. UI, REST, Ability, CLI, Workflow, Job and AI adapters preserve one semantic error contract.
9. Error/recovery actions remain independently capability/Policy/re-auth/impact authorized.
10. Production surfaces redact secrets/private content/raw stack/provider bodies by default.
11. Correlation IDs are safe references, not access tokens.
12. Critical data-integrity/security failures are durable/actionable and not reduced to transient toast-only UX.
13. Accessibility/RTL/localization cannot change error meaning or machine code.
14. Error-rate aggregation/sampling never drops mandatory security Audit evidence.
15. Recovery is not marked complete until actual post-recovery verification succeeds.

## Current evidence state

- ERR documented: **176**.
- ERR executed: **0/176**.
- all `ERR-*` certification classes: **0**.
- concrete error registry/mapper: **NOT IMPLEMENTED**.
- final machine-code catalog: **OPEN / module-implementation dependent**.
- exact REST/Problem Details mapping: **OPEN / evidence-gated**.
- UI error component implementation: **NOT IMPLEMENTED**.
- exact retry budgets/backoff profiles: **OPEN by operation/provider**.
- runtime/accessibility/Multisite/performance certification: **0**.

## Rejected shortcuts

- parsing translated text as business logic;
- generic 500 for every failure;
- raw stack/SQL/token/provider payload exposure;
- auto-retrying non-idempotent destructive operations;
- treating timeout as confirmed failure;
- reporting partial work as complete success;
- silently overwriting stale/conflicting state;
- UI hiding as authorization;
- recovery buttons bypassing Policy/re-auth;
- provider/user-controlled error injection into HTML/log/CLI/redirect;
- recursive logging/error amplification;
- critical integrity/security failure shown only as disposable transient toast;
- reporting recovery complete before verification.

## Development gate

No error registry/mapper/UI component, REST/Ability/CLI/AI adapter, Job retry, Workflow/provider failure injection, browser/accessibility test, Multisite fixture or benchmark is authorized by this ADR.

ADR-0014 and the Approval Ledger still require explicit scoped owner consent before executable evidence or implementation.

Current execution count remains **0/176**.