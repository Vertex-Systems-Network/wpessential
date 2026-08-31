# ADR-0156 — Import / Export Executable Evidence Refinement

Status: **Accepted evidence refinement / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP39`

## Context

ADR-0041, ADR-0095 and ADR-0116 established the reviewed Plan/Dry Run, source fingerprint, durable Run/Checkpoint/Identity Map/Journal, rollback classes and IR1/PT-D vs IR2/PT-E Import/Export architecture. The original canonical executable protocol contained IM-01…IM-56.

Subsequent shared contracts introduced explicit versioning, Data Source, Field Storage, Relations, Custom Tables, Query, Component Blueprint, Conditional Logic, Dynamic Value, Cache, Privacy, Module Lifecycle and Error boundaries. Package trust, dependency closure, cross-environment remap and modern parser/provider failure behavior also required explicit certification without duplicating those shared domains.

## Decision

Refine `docs/QUALITY/IMPORT-EXPORT-EXECUTABLE-EVIDENCE-PROTOCOL.md` in place to **IM-01…IM-176**.

The original IM-01…IM-56 semantics remain preserved. IM-57…IM-176 add explicit evidence for:
- package manifest/version/hash/signature/trust separation;
- package schema migration and unknown-future-version fail-safe behavior;
- Definition dependency closure and deterministic ordering;
- UUID/identity remapping across environments;
- DSR/FST/REL/CTB schema/capability/constraint and transaction boundaries;
- Query/CLG/DVR/Blueprint dependency portability;
- JSON/XML/CSV/spreadsheet/archive/parser security;
- remote source/media Safe HTTP and provider unknown-outcome reconciliation;
- cache invalidation, Audit, privacy and post-restore reconciliation;
- site/network/multi-site package scope and large-scale execution truth.

## Preserved boundaries

- reviewed Plan + source/package fingerprint remain mandatory before execution;
- package integrity/signature status does not itself authorize import;
- numeric database IDs are not portable identity authority;
- readable source/target capability does not imply writable capability;
- Custom Table desired schema never authorizes unreviewed DDL;
- generic importer cannot become an arbitrary SQL/PHP/filesystem/Vault-secret execution channel;
- Job delivery/lease state never proves target mutation outcome;
- rollback classes R0–R3 remain truthful and never overwrite newer unrelated local edits silently;
- shared VER/DSR/FST/REL/CTB/CAC/KPA/provider certifications do not promote Import/Export certification.

## Current execution truth

- IM fixtures documented: **176**.
- IM fixtures executed: **0/176**.
- Import/Export runtime certifications: **0**.
- IR1/PT-D and IR2/PT-E remain evidence-gated.

## Development gate

No package parse/extraction, signature verification runtime, target mutation, DDL, Job execution, provider/media fetch, cache operation, rollback/restore, cleanup or benchmark was executed by this refinement.

Execution and implementation remain prohibited until explicit scoped owner consent under ADR-0014 and the Approval Ledger.