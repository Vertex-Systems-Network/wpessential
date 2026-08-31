# ADR-0142 — Audit & Observability Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP25`

## Decision

Accept `docs/QUALITY/AUDIT-OBSERVABILITY-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for WPEssential Audit and its observability boundary.

The protocol freezes **AUD-01…AUD-176**.

It operationalizes ADR-0081 without changing the architectural truth that `AU1 / PT-D` is only the favored first future physical baseline and not a runtime-certified implementation.

## Accepted truth boundary

The following remain separate:

`Audit Event ≠ Domain History ≠ Operational Diagnostic ≠ Security Alert ≠ Request Trace ≠ Job Attempt ≠ Workflow Run ≠ Event Inbox record ≠ Provider log ≠ analytics event ≠ immutable external evidence`

Audit may correlate those domains but does not replace their canonical state/history.

A locally stored Audit row/hash does not prove cryptographic non-repudiation or tamper-proof behavior against a privileged DB/server attacker.

## Fixed evidence coverage

- event model/identity/scope/classification — AUD-01…AUD-16;
- write/commit/failure/idempotency — AUD-17…AUD-32;
- read authorization/filters/exports — AUD-33…AUD-48;
- privacy/redaction/minimization/secret exclusion — AUD-49…AUD-64;
- correlation/causation/observability boundaries — AUD-65…AUD-80;
- retention/purge/erasure/hold — AUD-81…AUD-96;
- integrity/provenance/migration/restore truth — AUD-97…AUD-112;
- Multisite/site-lifecycle isolation — AUD-113…AUD-128;
- query/index/performance/scale — AUD-129…AUD-144;
- diagnostics/incidents/operational observability — AUD-145…AUD-160;
- compatibility/failure injection/operational envelope — AUD-161…AUD-176.

## Certification classes

Certify independently:

- `AUD-M` model/identity/scope;
- `AUD-W` write/failure/idempotency;
- `AUD-A` read/export authorization;
- `AUD-P` privacy/redaction/minimization;
- `AUD-C` correlation/domain-boundary correctness;
- `AUD-R` retention/privacy lifecycle;
- `AUD-I` integrity/provenance/restore;
- `AUD-S` Multisite/site lifecycle;
- `AUD-Q` query/index/performance;
- `AUD-O` diagnostics/incident observability.

Passing one class never implies another.

## Accepted invariants

1. Audit records meaningful administrative/security explanation, not every byte or debug trace.
2. Domain histories remain authoritative in their owning domains.
3. Site/network ownership is explicit; current blog context is never durable ownership.
4. Secret-bearing values, reusable security tokens and private URLs must never persist in Audit.
5. Redaction is server-side and viewer/destination aware.
6. Mandatory-Audit high-risk mutations must follow an explicit fail policy; Audit failure never silently disables authorization.
7. Corrections are new linked events, not silent in-place history rewrites.
8. Restore/import preserves origin/provenance and cannot make old events appear newly occurred.
9. Restore itself remains visible in current chronology.
10. Local DB/hash/hash-chain evidence is not described as tamper-proof without an actually certified attacker model/profile.
11. Retention is classification/purpose based; no implicit forever retention.
12. Local Audit enablement does not imply remote telemetry consent.
13. Network aggregation, export and purge are bounded and scope-authorized.
14. Operational diagnostics remain shorter-lived/separate unless an event is intentionally promoted to Audit semantics.

## Current evidence state

- AUD documented: **176**.
- AUD executed: **0/176**.
- all `AUD-*` certification classes: **0**.
- AU1/PT-D: favored first future baseline only.
- exact DDL/index set: **OPEN**.
- exact retention durations: **OPEN**.
- exact mandatory/fail-closed Ability classes: **OPEN**.
- optional tamper-evidence profile: **NONE SELECTED / OPEN**.
- external immutable checkpoint profile: **NONE SELECTED / OPEN**.
- runtime/Multisite/scale certification: **0**.

## Rejected shortcuts

- generic “log everything forever”;
- raw request/header/provider payload dumping;
- storing passwords/tokens/Vault plaintext/private signed URLs;
- current-blog-derived durable ownership;
- CSS-only redaction;
- Audit as replacement for Workflow/Job/Event Inbox/Membership/Backup/Email histories;
- false success after rolled-back or unknown mutation;
- silent mandatory-Audit drop under storage/load failure;
- blind history overwrite on restore/import;
- local hash chain marketed as tamper-proof/non-repudiable;
- local Audit automatically transmitting telemetry/support data.

## Development gate

No Audit table/migration, logger, integrity chain, checkpoint, runtime fixture, privacy mutation, export, WordPress/Multisite execution, provider/service call or benchmark is authorized by this ADR.

ADR-0014 and the Approval Ledger still require explicit scoped owner consent before executable evidence or implementation.

Current execution count remains **0/176**.