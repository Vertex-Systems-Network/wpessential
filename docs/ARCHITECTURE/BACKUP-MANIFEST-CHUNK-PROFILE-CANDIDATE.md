# WPEssential — Backup Manifest & Chunk Profile Candidate

Status: **Phase 0 paper architecture / no implementation authorized**  
Related: Backup exhaustive spec, Restore Semantics, Provider Matrix, Backup Encryption ADR-0021.

## 1. Goal

Define a provider-neutral logical backup bundle that can:
- stream on low-memory hosts;
- resume transfers;
- detect missing/corrupt parts;
- encrypt independently from provider storage;
- restore without original server/database;
- live on filesystem, S3-compatible, WebDAV/SFTP/drive providers;
- avoid treating a partial upload as a completed backup.

This document does not select final compression/archive libraries.

## 2. Logical bundle over physical archive

WPE backup is a **logical bundle**, not necessarily one ZIP file.

Candidate bundle objects:

- `bundle-header` — minimal non-sensitive bootstrap metadata;
- `manifest` — authoritative complete backup description, encrypted when backup encryption is enabled;
- DB artifacts/parts;
- file-data artifacts/parts;
- optional file-index parts for large inventories;
- optional metadata/diagnostic artifacts;
- completion/commit marker or finalized manifest state.

A provider can store these as separate objects/files or inside a convenience archive after generation.

Single ZIP may remain an export/download convenience for small backups but is not the canonical internal assumption.

## 3. Backup identity

Every Backup Set has:
- random backup UUID;
- source installation UUID/network/site scope;
- created-at UTC;
- manifest schema version;
- WPE product/platform/schema versions;
- generation/revision number if backup is resumed/rebuilt;
- scope fingerprint;
- encryption profile ID if enabled.

Object names derive from opaque backup identity, not sensitive site titles/user content.

## 4. Outer bundle header

Purpose: identify and bootstrap bundle before decryption.

Candidate non-sensitive fields:
- format magic/name;
- bundle schema/version;
- backup UUID;
- created-at;
- encrypted yes/no;
- encryption profile/version + key-wrapper descriptors needed for recovery;
- manifest object/part reference;
- stored-bytes size summary;
- finalization state/commit reference;
- optional required feature flags.

Do not place sensitive file paths, user data, SQL contents, provider credentials or secrets in plaintext outer header.

## 5. Authoritative inner manifest

Manifest contains the complete restore contract:
- source WP/PHP/DB metadata safe subset;
- source URL/home/path/table prefix;
- single/multisite topology;
- requested vs captured scope;
- exclusions;
- plugin/theme/version inventory;
- WPE module/schema versions;
- table inventory + logical DB part mapping;
- file inventory/index mapping;
- artifact/part list;
- restore ordering/dependencies;
- compression/encryption profiles;
- checksums/hashes;
- capture warnings/skips/volatility;
- destination verification states;
- retention/restore-point flags;
- compatibility notes needed by restore.

If encryption is enabled, the authoritative manifest is encrypted/authenticated with backup key architecture.

## 6. Part descriptor

Every artifact part has stable descriptor fields candidate:
- part UUID/ordinal;
- logical artifact type (`database`, `file-data`, `file-index`, `metadata`, etc.);
- logical stream/group ID;
- sequence number + total if known;
- stored object name/reference;
- uncompressed logical bytes optional;
- stored bytes;
- compression profile;
- encryption profile;
- ciphertext/stored-bytes checksum;
- plaintext/logical checksum inside protected manifest when useful;
- record/file counts where relevant;
- previous/next or ordering metadata;
- required/optional flag.

Missing Required part means backup cannot be fully restore-ready.

## 7. Hash/checksum model

Transport/storage integrity and encryption authenticity are distinct.

Candidate:
- a broadly available cryptographic hash such as SHA-256 for stored-object integrity/inter-provider comparison;
- AEAD authentication for encrypted content integrity/authenticity;
- optional logical/plaintext hashes inside protected manifest for restore verification.

Exact accepted hash/profile is executable compatibility evidence work, but weak/non-cryptographic checksums are not the sole integrity mechanism.

Provider ETag is only used as checksum when provider semantics prove it represents the required content hash; multipart/S3-style ETags are not universally MD5.

## 8. Chunk sizing

Chunk size is profile/configuration derived from:
- PHP memory limit;
- max execution time;
- provider multipart constraints;
- network reliability;
- object count overhead;
- file size distribution;
- encryption/compression streaming behavior.

No universal fixed size is accepted yet.

Future benchmark selects safe defaults and adapter overrides.

## 9. File-data packing

Avoid one remote object per tiny WordPress file at scale.

Candidate packing strategy:
- deterministic sequence of file records into bounded stream parts;
- each record contains safe normalized relative path metadata in protected manifest/index;
- file bytes streamed;
- large files may occupy dedicated or multiple parts;
- symlink policy explicit;
- path traversal/outside-root targets never silently archived.

Exact TAR-like/local record container remains open.

## 10. File index scaling

Small backup can hold file inventory in manifest.

Large backup may split inventory into index parts referenced by manifest.

Index supports:
- relative path;
- logical size;
- modified/stat metadata where needed;
- file content hash according to verification policy;
- data-part/offset or record reference;
- capture status;
- permissions metadata only where restore semantics support;
- symlink metadata where allowed.

Index itself is protected/encrypted with backup when paths/content metadata are sensitive.

## 11. Database artifacts

Database backup is a typed logical artifact family.

Manifest records:
- table name/mapped logical identity;
- engine/charset/collation;
- schema metadata needed for restore;
- capture consistency level;
- row/count estimate where practical;
- DB part sequence;
- skipped/unsupported views/triggers/routines if any.

Exact physical DB payload format remains open among:
- controlled SQL-like logical stream;
- WPE typed row/schema stream;
- hybrid schema + bulk row records.

Raw database SQL from untrusted external source is never blindly executed merely because it sits in an archive.

## 12. Encryption per part

Architecture from ADR-0021:
- one per-backup DEK;
- independent disaster-recovery wrapping;
- unique nonce/IV requirements per encrypted object/part;
- authenticated encryption;
- key material not stored plaintext in bundle.

Streaming can encrypt each part independently so one corrupt part does not require decrypting a huge monolith.

Exact AEAD/KDF/container remains pending evidence.

## 13. Finalization protocol

Partial backup must never masquerade as completed.

Candidate order:
1. create local run/backup UUID;
2. write/capture parts under incomplete namespace/state;
3. verify local part hashes;
4. upload Required/Optional destinations;
5. verify remote parts according to adapter capability;
6. write/upload final authoritative manifest;
7. atomically mark/finalize bundle where provider permits, or upload a small final commit marker last;
8. record V0/V1/V2 verification state.

Restore list ignores unfinalized/incomplete bundle unless user enters forensic/recovery diagnostics.

## 14. Resume journal

Runtime Job Service/local state may track:
- completed part IDs;
- provider upload session IDs as Vault/connection-safe references;
- retry attempts;
- verified remote checksums/sizes;
- current capture cursor;
- manifest generation.

Journal is operational runtime data, not authoritative backup content.

If runtime journal is lost but remote parts exist, reconciliation can inspect bundle/object state without assuming completion.

## 15. Provider mapping

### Filesystem/SFTP/WebDAV
Bundle maps to directory-like namespace.

### Object storage
Bundle maps to prefix/object keys.

### Drive providers
Bundle maps to folder/files or provider-native resumable objects according to adapter.

Adapters must not change logical manifest semantics.

## 16. Required vs Optional destination copies

One Backup Set can have many Destination Copies.

Manifest/runtime records each copy separately.

Backup Plan policy decides whether overall job requires:
- every Required destination;
- at least N verified copies;
- local verified + one remote verified;
- Optional mirrors best-effort.

No green “fully protected” state if Required destination failed.

## 17. Verification levels

Retain existing model:
- **V0 Generated** — artifacts produced;
- **V1 Local Verified** — local bundle/hash verification;
- **V2 Remote Verified** — at least policy-required destination copy verified;
- **V3 Restore Tested** — certified restore test for applicable path.

Finalized manifest alone does not imply V3.

## 18. Restore bootstrap

Restore flow:
1. read outer header;
2. validate format/version/feature flags;
3. obtain/unwrap DEK if encrypted;
4. decrypt/authenticate authoritative manifest;
5. verify manifest schema;
6. verify Required part inventory;
7. stream/download parts;
8. verify stored hash then decrypt/authenticate/decompress;
9. restore by manifest dependency/order;
10. verify logical/post-restore state.

Unsupported future format fails before destructive restore.

## 19. Compatibility/versioning

Bundle schema version is independent from WPE plugin version.

Reader declares:
- supported manifest versions;
- supported encryption/compression profiles;
- supported DB/file artifact versions;
- required feature flags.

Unknown Required feature => fail-safe with upgrade instruction.

Unknown Optional artifact can be ignored only when manifest explicitly permits without breaking requested restore scope.

## 20. Security

Restore parser treats bundle as untrusted until validated.

Must defend against:
- path traversal;
- archive bombs/declared size abuse;
- huge JSON/depth/object counts;
- duplicate conflicting paths;
- symlink escape;
- corrupted/missing parts;
- unsupported algorithms;
- malicious SQL/content records;
- checksum confusion;
- decompression resource exhaustion.

## 21. Future executable evidence — NOT AUTHORIZED

After explicit consent:
- compare ZIP/TAR/custom-record part containers;
- low-memory streaming;
- 1GB/10GB/100GB synthetic bundle behavior where environment permits;
- many-small-files fixture;
- huge single-file fixture;
- missing/corrupt/out-of-order part;
- resume after crash;
- S3 multipart/WebDAV/SFTP/Drive mappings;
- encrypted cross-server disaster restore;
- manifest parser limits;
- DB payload candidates;
- upload finalization atomicity/provider edge cases.

## Paper recommendation

Accept a **manifest-first, independently verifiable multipart logical bundle**. Keep final physical file-record/DB payload/compression profiles evidence-gated.