# ADR-0174 — Membership Protected File Delivery Certification Refinement

Status: **Accepted planning/evidence contract / executable evidence pending**  
Date: 2026-08-28

## Context

ADR-0090 already accepts protected-file delivery profiles PD1–PD4 and origin-bypass certification levels PC0–PC4. The remaining gap is not architecture invention; it is a bounded executable evidence contract that can prove origin isolation, authorization, transfer semantics, lifecycle/recovery and provider/deployment scope without promoting paper support to runtime certification.

Membership protected files remain **0 PC1+ runtime-certified profiles**.

## Decision

Accept the refined canonical profile in:

`docs/ARCHITECTURE/MEMBERSHIP-PROTECTED-FILE-DELIVERY-CERTIFICATION-PROFILE.md`

with fixed executable evidence IDs **PC-F001…PC-F176**.

Current evidence truth:
- PC-F documented: **176**;
- PC-F executed: **0/176**;
- PC1+ runtime-certified profiles: **0**;
- PD1/PD2/PD3/PD4 runtime-certified profiles: **0**.

## Preserved semantics

ADR-0090 is not superseded. The following remain unchanged:
- PD1 — private local storage + PHP streaming correctness baseline;
- PD2 — server-accelerated private local delivery after deployment-specific proof;
- PD3 — private object storage + short-lived signed delivery with bearer-link limitations;
- PD4 — provider-specific private CDN/tokenized stronger-revocation profile;
- PC0–PC4 maturity ladder.

## Authority boundaries

The accepted evidence contract preserves:
- storage possession ≠ authorization;
- attachment/page visibility ≠ origin-byte protection;
- Membership allow ≠ bypass of outer WordPress/Protector security;
- signed token/URL issuance ≠ durable Membership entitlement;
- Backup-provider certification ≠ protected-file delivery certification;
- provider/CDN reachability ≠ WPE authorization;
- static/provider documentation ≠ PC1+ runtime certification.

## Evidence coverage

PC-F001…PC-F176 covers:
- Protected Asset identity and canonical binding;
- direct-origin and derivative bypass resistance;
- Membership/Policy/outer authorization and stale-cache revoke behavior;
- local signed-token semantics;
- PD1 PHP streaming correctness/resource safety;
- PD2 server-accelerated internal delivery;
- PD3 object-storage signed URL behavior;
- PD4 CDN/tokenized stronger-revocation behavior;
- MIME/header/Range/resume/request-abuse handling;
- cache/CDN intermediary isolation;
- preview/derivative/Watermarker/Media boundaries;
- download limits/redemption/concurrency/rate-control semantics;
- public→private migration/cutover/rollback;
- Backup/Restore/clone/deletion/key lifecycle;
- privacy/Audit/Error/observability/recovery;
- Multisite/Site Lifecycle/provider-version/scale certification.

## Certification rule

A profile earns only the PC level whose applicable lower and current-level evidence has passed for the exact delivery/storage/provider/version/deployment identity.

A direct-origin isolation failure is stop-the-line for any `Protected`/`Supported` claim.

A higher-level happy path cannot skip lower PC maturity evidence.

## Signed delivery truth

PD3/PD4 claims must match the actual provider/CDN semantics. WPE does not claim instant revocation of an already-issued bearer URL or termination of in-flight bytes unless the certified delivery profile demonstrably provides that property.

## Migration/recovery truth

Metadata change alone does not prove public-origin removal. Public→private migration must include post-cutover direct-origin evidence, rollback/recovery class, derivative inventory and Backup/Restore/clone behavior.

## Runtime state

**NOT EXECUTED.** No file move/copy, server configuration, download endpoint, token/signature implementation, object/CDN/storage API call, Range implementation, provider credential operation, protected-object mutation, migration, Backup/Restore, test download or benchmark occurred.

## Development gate

ADR-0014 remains binding. This ADR is planning/evidence acceptance only and does not authorize source/runtime implementation or executable evidence.