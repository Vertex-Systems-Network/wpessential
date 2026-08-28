# ADR-0175 — Backup Provider Certification Refinement

Status: **Accepted planning/evidence contract / executable evidence pending**  
Date: 2026-08-28

## Context

ADR-0053 already defines Backup provider certification levels C0–C4, ADR-0056 defines Remote Copy lifecycle truth, ADR-0061/0064 separate static provider-family evidence from runtime certification, ADR-0065 captures transport/security profiles, ADR-0084 fixes the first Remote Copy physical baseline, and ADR-0130 fixes core Backup/Restore evidence as BK-01…BK-180.

The remaining gap is a fixed provider-certification executable evidence namespace that does not collide with core Backup evidence and cannot promote provider documentation, connection success or upload success to Supported Backup status.

Current provider truth before execution:
- provider targets: **34**;
- C-certified profiles: **0**;
- C3 Supported Backup Destination profiles: **0**;
- C4 Disaster Restore Certified profiles: **0**;
- V3 restore-tested provider profiles: **0**.

## Decision

Accept the refined canonical provider evidence protocol:

`docs/QUALITY/BACKUP-PROVIDER-CERTIFICATION-EVIDENCE-PROTOCOL.md`

with fixed executable fixture IDs **BPC-F001…BPC-F176**.

Current execution state:
- BPC-F documented: **176**;
- BPC-F executed: **0/176**.

## Preserved certification ladder

This ADR does not supersede C0–C4:
- C0 — Detected / Connectable; not Backup Supported;
- C1 — Upload Certified; not normal Supported Backup Destination;
- C2 — Resumable & Integrity Certified, or explicitly Non-resumable where the family lacks a trustworthy resume contract;
- C3 — Restore Certified; minimum normal `Supported Backup Destination` gate;
- C4 — Disaster Restore Certified; requires V3 fresh-environment recovery evidence.

Core Backup/Restore evidence `BK-01…BK-180` remains a separate domain from provider certification `BPC-F001…BPC-F176`.

## Authority/truth boundaries

The evidence contract preserves:
- provider catalog/logo ≠ compatibility;
- static SE0–SE3 ≠ C0;
- OAuth/credential connection ≠ Backup support;
- successful upload ≠ restorable Backup;
- provider object visible ≠ WPE integrity verified;
- `remote_committed` ≠ `remote_verified`;
- provider ETag/checksum ≠ WPE manifest digest unless exact profile semantics prove it;
- V2 Remote Verified ≠ V3 Restore Tested;
- JobService at-least-once ≠ exactly-once provider writes;
- delete request accepted ≠ confirmed erasure;
- Backup-provider certification ≠ protected-file delivery certification.

## Evidence coverage

BPC-F001…BPC-F176 covers:
- exact family/provider/API/client/adapter/environment certification identity;
- C0 authentication, Vault, endpoint and namespace safety;
- C1 upload/read-back/delete/error behavior;
- multipart/resumable transfer and provider limits;
- JobService retries, crashes, idempotency and unknown outcomes;
- finalization/commit ambiguity and reconciliation;
- WPE/provider integrity, manifest-last and encryption failure cases;
- remote retrieval/range/archive/version behavior;
- C3 complete remote restore with local-source exclusion;
- C4/V3 fresh-environment disaster restore and independent key/credential recovery;
- retention/prune/delete/versioning/object-lock truth;
- auth rotation, TLS/SSH/custom endpoint/secret handling;
- rate/quota/backpressure/resource/cost observations;
- Multisite/Site Lifecycle/clone/environment ownership;
- privacy/Audit/Error/observability/version drift/recertification;
- family/provider deviations and the 34-target registry.

## Restore-first support rule

A provider can be marketed as a normal Supported Backup Destination only after the exact profile reaches C3. A C1 upload or C2 integrity result is not sufficient.

C4 requires repeatable V3 disaster recovery from a fresh supported environment with original runtime state unavailable except documented independent recovery material.

## Remote Copy truth

A provider copy is not V2 merely because bytes were written or finalized. It must reach `remote_verified` through WPE manifest/inventory/integrity evidence.

Unknown commit/delete/provider outcomes reconcile against provider state; they are never guessed into success.

## Runtime state

**NOT EXECUTED.** No provider account/credential validation, API/network request, SDK/client install, upload, multipart session, download, delete, encryption/recovery operation, Backup/Restore, provider mutation or benchmark occurred.

## Development gate

ADR-0014 remains binding. This ADR accepts planning/evidence semantics only and does not authorize source/runtime implementation or executable certification.