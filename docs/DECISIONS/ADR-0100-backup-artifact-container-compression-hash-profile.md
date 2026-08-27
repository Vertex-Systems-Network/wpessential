# ADR-0100 — Backup Artifact, Container, Compression & Hash Profile

Status: **Accepted architecture / P-013 evidence pending**  
Date: 2026-08-28

## Decision

WPEssential Backup keeps the manifest-first multipart logical-bundle architecture and accepts these artifact-level rules:

1. canonical Backup is not one ZIP/TAR file;
2. every finalized Required part records **SHA-256 over exact stored bytes** as the provider-neutral integrity baseline;
3. AEAD authentication and SHA-256 integrity remain distinct evidence layers;
4. provider ETag/checksum is supplemental unless the certified provider profile proves equivalent semantics for that operation;
5. **CMP0 no compression** is a mandatory correctness/interoperability fallback;
6. **CMP1 gzip/DEFLATE streaming** is the first general compression comparison where runtime capability exists;
7. ZIP is a bounded convenience import/export adapter, not the canonical Backup engine;
8. file-data container comparison is FR1 WPE bounded record stream vs FR2 TAR-compatible stream; ZIP remains compatibility-only FR3;
9. DB payload comparison remains DB1 typed schema+row stream vs DB2 controlled SQL stream vs DB3 hybrid;
10. chunk sizing is a certified profile derived from host/provider/resource constraints, not one universal constant;
11. provider multipart upload boundaries do not become WPE logical Part identities;
12. compression normally precedes encryption, and stored-byte hash is calculated on final stored representation;
13. final authoritative manifest/commit state is published only after Required part descriptors are complete/verified according to policy;
14. restore parser applies strict size/count/depth/path/compression-expansion/algorithm limits before destructive restore.

## Why

A ZIP-first design couples WPE to optional runtime extensions, large-memory/single-file failure modes and weak resumability. Provider ETags also have provider/operation-specific semantics. A logical multipart bundle gives WPE independent verification, resume and provider portability.

PHP's current Hash extension provides SHA-256/file/incremental hashing as a core facility, making SHA-256 a strong portability candidate. ZIP remains extension/libzip dependent and therefore cannot be the required canonical runtime.

## Evidence pending

Future P-013 must decide:
- FR1 vs FR2;
- DB1/DB2/DB3;
- CMP0/CMP1 and any future CMP3;
- exact chunk-size classes/defaults;
- exact compression level;
- parser limits;
- low-memory/huge-file/many-file behavior;
- encrypted disaster restore;
- provider mappings and crash/finalization windows.

Executed archive/hash/compression/restore cases: **0**.

## Source

`docs/ARCHITECTURE/BACKUP-ARTIFACT-CONTAINER-COMPRESSION-HASH-PROFILE.md`

## Development gate

No archive, compression, hash scan, DB dump, file capture, upload, encryption, restore or P-013 benchmark is authorized before explicit owner consent under ADR-0014.