# ADR-0144 — Privacy & Data Lifecycle Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP27`

## Decision

Accept `docs/QUALITY/PRIVACY-DATA-LIFECYCLE-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for WPEssential's **local WordPress privacy and data lifecycle**.

The protocol freezes **PDL-01…PDL-176**.

It operationalizes the existing P0–P4 privacy/data-retention contracts across modules without replacing remote-service privacy, Vault, Audit, provider or domain-specific evidence.

## Accepted truth boundary

The following remain separate:

`Data classification ≠ storage location ≠ owner ≠ access authorization ≠ retention policy ≠ export eligibility ≠ erasure eligibility ≠ anonymization ≠ external deletion ≠ backup expiry ≠ secure erasure proof`

Additional hard separations:
- WordPress user deletion is not universal WPE data deletion;
- site deletion is not privacy erasure;
- module disable is not data deletion;
- local erase is not confirmed remote-provider deletion;
- live-data erase does not retroactively rewrite retained backups;
- cache/search-index removal is not canonical source deletion;
- pseudonymized data remains personal where re-identification remains possible;
- technical retention configuration is not jurisdiction-specific legal advice.

## Fixed evidence coverage

- classification/purpose/ownership/schema inventory — PDL-01…PDL-16;
- collection/data minimization — PDL-17…PDL-32;
- storage/access/visibility/authorization — PDL-33…PDL-48;
- WordPress personal-data export/subject resolution — PDL-49…PDL-64;
- erase/anonymize/pseudonymize/unlink — PDL-65…PDL-80;
- retention/holds/cleanup jobs — PDL-81…PDL-96;
- derived data/caches/indexes/queues/logs/artifacts — PDL-97…PDL-112;
- import/export packages/backup/restore reconciliation — PDL-113…PDL-128;
- external processors/AI/telemetry/support/remote boundary — PDL-129…PDL-144;
- Multisite/site lifecycle/identity isolation — PDL-145…PDL-160;
- concurrency/failure/scale/composite safety — PDL-161…PDL-176.

## Certification classes

Certify independently:
- `PDL-C` classification/purpose/ownership;
- `PDL-M` minimization/storage boundaries;
- `PDL-A` authorization/export/subject resolution;
- `PDL-E` erase/anonymize/unlink;
- `PDL-R` retention/holds/cleanup;
- `PDL-D` derived-data lifecycle;
- `PDL-B` backup/import/export/restore;
- `PDL-X` external processor/AI/telemetry/support boundaries;
- `PDL-S` Multisite/site lifecycle;
- `PDL-O` concurrency/failure/observability/scale.

Passing one class never implies another.

## Accepted invariants

1. P0–P4 classification applies to persisted categories and, for mixed objects, individual fields.
2. Every persistent data category has explicit owner, purpose, retention direction and export/erase behavior.
3. P3 credentials/secrets use Vault/private token stores where applicable and are excluded from generic outputs/logs/events/audit/support bundles.
4. Modules operate on other domains through owner contracts; privacy orchestration does not direct-delete foreign private tables.
5. WordPress personal-data exporters/erasers are registered only for locally controlled data and use bounded/paginated processing.
6. Erase may mean delete, anonymize, retain-with-reason or unlink depending on owner/integrity policy; UI/reporting must remain truthful.
7. Search indexes, caches, projections, queues and temporary artifacts inherit source classification/lifecycle obligations.
8. Imported data receives the same classification/access/retention contract as native data.
9. Configuration exports exclude secrets by default and data exports remain authorization/redaction scoped.
10. Live erasure does not imply old Backup copies were rewritten or securely erased.
11. Restore that reintroduces previously erased data requires explicit privacy reconciliation/reporting; Restore itself remains visible in current chronology.
12. Local deletion is separate from remote provider/service deletion and unknown remote outcome remains explicit.
13. AI/telemetry/support transmission is explicit purpose/scope controlled; P3 cannot become generic model/context data.
14. Global WordPress user identity does not collapse per-site privacy ownership in Multisite.
15. Site deletion/module disable do not masquerade as universal erasure.
16. Cleanup/export/erase jobs are bounded, resumable, scope-pinned and truthful on partial failure.

## Relationship to RS and domain evidence

PDL owns local WordPress privacy lifecycle. `REMOTE-SERVICE-PRIVACY-RETENTION-EVIDENCE-PROTOCOL.md` remains authoritative for observed WPE-controlled remote-service transmission, retention and deletion behavior (`RS-001…RS-030`).

A PDL pass cannot promote RS certification, provider deletion status, Backup provider secure-erasure claims, Vault certification, Audit integrity certification or module-specific privacy/security certification.

## Current evidence state

- PDL documented: **176**.
- PDL executed: **0/176**.
- all `PDL-*` certification classes: **0**.
- exact jurisdiction-specific retention periods: **OUT OF SCOPE / NOT SELECTED**.
- category/default product retention durations: **OPEN where domain contracts do not already fix them**.
- WordPress exporter/eraser runtime registrations: **0 certified**.
- cross-module privacy coordinator: **NOT IMPLEMENTED**.
- derived-store invalidation registry: **NOT IMPLEMENTED**.
- retention cleanup runtime certifications: **0**.
- Multisite/privacy runtime certifications: **0**.
- remote WPE service privacy remains **RS 0/30**.

## Rejected shortcuts

- one global privacy classification for a mixed blob;
- collecting IP/user-agent by default without purpose;
- generic request/body/provider payload logging;
- secrets/passwords/card/private URLs in ordinary storage/export/log/event/audit;
- direct bulk deletion of another module's data;
- reporting retained/anonymized/remote-unknown data as completely erased;
- treating user/site/module deletion as universal privacy erasure;
- leaving source-derived caches/search indexes readable after erase/revoke;
- claiming live erase removed old backups;
- claiming provider/filesystem secure erasure without evidence;
- hidden telemetry/AI/support upload;
- one-request unbounded export/erase/retention fan-out.

## Development gate

No WordPress exporter/eraser registration, privacy coordinator, retention job, DB/file/cache/search mutation, Import/Export, Backup/Restore, remote provider/AI/support call, Multisite fixture or benchmark is authorized by this ADR.

ADR-0014 and the Approval Ledger still require explicit scoped owner consent before executable evidence or implementation.

Current execution count remains **0/176**.