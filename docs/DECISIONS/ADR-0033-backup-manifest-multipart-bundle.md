# ADR-0033 — Backup Manifest-First Multipart Bundle

Status: **Accepted logical architecture / physical formats pending**  
Date: 2026-08-27

## Decision

WPEssential Backup uses a provider-neutral **manifest-first multipart logical bundle** rather than assuming one monolithic ZIP as the canonical backup format.

The logical bundle contains:
- minimal non-sensitive outer header;
- authoritative manifest;
- independently verifiable DB/file/index/metadata parts;
- encryption profile references;
- finalization/commit state.

Encrypted backups keep sensitive manifest/path metadata protected. Partial/unfinalized bundles are never presented as completed restore points.

## Why

- large-site streaming and low-memory operation;
- resumable provider uploads;
- independent corruption detection;
- cross-provider portability;
- per-part encryption/authentication;
- disaster restore without original site/runtime journal.

## Consequences

- single ZIP may remain small/manual convenience only;
- provider adapters map the same logical bundle to filesystem/object/drive storage;
- finalization occurs only after required parts verify;
- exact DB payload/file-record container/chunk size/compression/hash/AEAD profile remain evidence-gated.

## Evidence still required

After explicit consent:
- ZIP/TAR/custom record comparisons;
- chunk-size benchmarks;
- many-small-files and huge-file fixtures;
- crash/resume/finalization tests;
- corrupt/missing-part restore;
- S3/SFTP/WebDAV/Drive mapping;
- encrypted cross-server disaster restore;
- DB artifact format comparison.

Supporting doc: `docs/ARCHITECTURE/BACKUP-MANIFEST-CHUNK-PROFILE-CANDIDATE.md`.