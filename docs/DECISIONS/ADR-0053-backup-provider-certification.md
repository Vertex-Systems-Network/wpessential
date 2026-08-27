# ADR-0053 — Backup Provider Certification by Protocol Family

Status: **Accepted architecture / executable certification pending**  
Date: 2026-08-27

## Context

WPEssential targets a large catalog of remote Backup destinations. Treating each provider as a separate ad-hoc upload implementation would create duplicated transfer logic and inconsistent restore/security behavior. Treating every provider that advertises a common protocol as fully equivalent would also be unsafe because limits, checksum semantics, resumability, lifecycle APIs and extensions differ.

A provider logo or successful connection/upload is not sufficient evidence that WPE can reliably restore from that provider.

## Decision

WPEssential Backup provider support is defined by:

1. a reusable **Protocol Family Adapter**; and
2. a provider-specific **Capability Profile** with explicit deviations/limits.

Initial families include local/mounted storage, S3-compatible object storage, Google Cloud Storage native, Azure Blob, Google Drive, Microsoft Graph Drives, Dropbox, SFTP, WebDAV and provider-native adapters where no family fits.

Provider capabilities are explicitly declared for authentication, namespace/identity, upload/resume/finalization, integrity, lifecycle/retention, security and operational behavior.

## Certification levels

- **C0 — Detected / Connectable**
- **C1 — Upload Certified**
- **C2 — Resumable & Integrity Certified**, or `Integrity Certified / Non-resumable` when the family does not offer a trustworthy resumable mechanism
- **C3 — Restore Certified**
- **C4 — Disaster Restore Certified**

A provider may be marketed as a normal **Supported Backup Destination only at C3 or higher** unless a future superseding ADR defines a narrower product label.

C4 corresponds to a repeatable V3-style fresh/disaster restore scenario.

## Integrity rule

WPE’s manifest/hash model remains authoritative. Provider-native ETags/checksums are additional evidence and must be interpreted according to the certified provider profile.

Examples:
- multipart Amazon S3 ETag is not assumed to be a whole-object MD5;
- preauthenticated upload-session URLs are secrets;
- generic WebDAV is not advertised as resumable without a certified extension/profile;
- SFTP resume/rename behavior is certified against the actual client/server profile rather than inferred from a fictional final SFTP RFC.

## Remote commit rule

Remote completion is a state machine, not `HTTP 2xx == backup complete`.

A destination must reach a provider-specific commit/finalization point and pass WPE remote integrity checks before becoming `remote_verified` / V2.

Examples of commit points include multipart completion, upload-session final object creation, block-list commit, Dropbox finish, or certified temporary-resource rename/move.

## Restore rule

C3 requires an actual remote restore fixture, including missing/corrupt object detection and long-running authentication/retry behavior. Upload-only certification can never imply restore certification.

## Marketing truth

The existing destination catalog remains a **target roadmap**, not a public supported-count claim, until corresponding profiles reach the agreed certification level.

WPE UI/documentation should display certification state explicitly rather than infer it from a logo.

## Consequences

Positive:
- common transfer logic can be reused safely;
- provider quirks remain explicit;
- restore reliability drives support claims;
- provider regressions can downgrade one profile without invalidating the entire family;
- 25+/34 destination ambition does not require 34 unrelated engines.

Costs:
- every marketed provider requires certification fixtures;
- some providers will remain lower-level/experimental despite having a working upload API;
- certification must be refreshed when provider APIs materially change.

## Evidence still required

After explicit owner development consent:
- adapter implementations;
- authentication/refresh tests;
- interruption/crash/resume tests;
- integrity/read-back tests;
- provider checksum interpretation;
- commit/finalization unknown-outcome tests;
- retention/delete/versioning tests;
- full restore tests;
- C4 fresh-environment disaster fixtures;
- supported-version/profile registry.

No provider certification has been executed yet.
