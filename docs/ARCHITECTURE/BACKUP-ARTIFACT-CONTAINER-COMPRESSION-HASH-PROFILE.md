# WPEssential — Backup Artifact, Container, Compression & Hash Profile

Status: **Phase 0 paper architecture / no archive generation authorized**  
Date: 2026-08-28  
Related: Backup manifest/chunk candidate, Backup encryption, Remote Copy, Provider Certification, Restore Semantics, ADR-0014.

## 1. Purpose

Narrow the canonical WPE backup bundle beyond “multipart manifest” so future implementation cannot accidentally make ZIP/provider ETag/one giant SQL dump the product contract.

This profile defines required artifact identities, framing candidates, integrity, compression negotiation, chunk sizing rules and finalization semantics while keeping final byte-level file/DB record encoding evidence-gated.

## 2. Canonical bundle rule

The canonical WPE Backup Set is:

`Outer Header + Authoritative Manifest + Required/Optional Artifact Streams + Independently Verifiable Parts + Final Commit State`

It is **not** defined as one ZIP/TAR file.

A convenience ZIP/TAR can be generated/imported as a transport/export adapter only when its runtime capability and security limits are verified.

## 3. Artifact family registry

Initial artifact families:
- `manifest`;
- `db-schema`;
- `db-data`;
- `file-index`;
- `file-data`;
- `wpe-runtime` scoped module records where not already covered by DB capture mode;
- `metadata` safe restore metadata;
- `diagnostics` optional/non-required;
- `commit` finalization object/marker where provider semantics need it.

Each artifact has:
- artifact UUID;
- artifact schema version;
- logical type;
- owning restore phase;
- required/optional;
- compression profile;
- encryption profile;
- ordered Part descriptors;
- logical/stored byte summaries;
- integrity profile;
- dependency references.

## 4. Part identity

Each part has an immutable descriptor:
- part UUID;
- artifact UUID;
- ordinal;
- logical stream offset/record range where applicable;
- stored object key/name;
- stored length;
- logical length when known;
- record/file/row count where meaningful;
- compression profile/version;
- encryption profile/version;
- SHA-256 of stored bytes;
- optional protected logical/plaintext SHA-256;
- capture/result state;
- required flag.

Part ordinal alone is not global identity.

## 5. Hash profile

### H-B1 — SHA-256 stored-object digest — accepted baseline

Every finalized Required part records SHA-256 over the exact stored bytes uploaded/written to the destination.

Why:
- cryptographic integrity better than CRC/Adler as sole mechanism;
- provider-independent;
- usable across local/remote copies;
- supports cross-copy verification;
- PHP Hash framework provides SHA-256/incremental/file hashing in current supported PHP families.

### Protected logical hash

For encrypted backups, optional plaintext/logical hashes live only inside authenticated/encrypted manifest metadata where they improve restore/content verification.

### Provider checksum

Provider checksum/ETag is supplemental evidence only unless provider profile proves exact semantics for that operation/object. Multipart ETag is never generically interpreted as MD5.

### AEAD distinction

AEAD authentication proves encrypted record authenticity/integrity under the cryptographic profile. SHA-256 remains transport/object identity/integrity evidence; neither is silently substituted for the other.

## 6. Compression profiles

Compression is per artifact/part profile, not one global assumption.

### CMP0 — none
Mandatory correctness/interoperability fallback. Useful for already-compressed media and environments without a certified compression adapter.

### CMP1 — gzip/DEFLATE streaming candidate
Preferred first general-purpose comparison because PHP zlib supports gzip-compatible streaming primitives when available.

Rules:
- capability probe required;
- stream/chunk operation, not `gzencode()` of giant in-memory backup;
- compression level policy balances CPU/time/size;
- exact level default benchmarked;
- decompression limits enforced on restore.

### CMP2 — ZIP convenience adapter
Not canonical compression/container.

Only for bounded export/import convenience when `ext-zip`/libzip capability is available and archive-bomb/path security is enforced.

### CMP3 — zstd/high-performance optional adapter
Future optional profile only if runtime/dependency/licensing/portability evidence is accepted. A host lacking CMP3 must still restore canonical bundles through supported required profiles.

### Per-file bypass

Already-compressed formats can use CMP0 within a file-data stream to avoid waste; the enclosing record/part profile records exact choice.

## 7. Compression-before-encryption

Normal encrypted pipeline:

`logical records → compression → encryption/authentication → stored-byte SHA-256`

Reasons:
- encrypted data is not meaningfully compressible;
- stored hash verifies exact remote bytes before/while decrypting;
- AEAD authenticates encrypted content.

Sensitive compression side-channel risk is low for offline backup artifacts compared with attacker-adaptive web compression, but secrets/data classifications still remain protected by encryption.

## 8. File-data container candidates

### FR1 — WPE length-delimited record stream — first correctness candidate

Conceptual record:
- record type/version;
- normalized relative path reference/ID;
- metadata flags;
- declared logical length;
- content bytes or continuation reference;
- optional per-record logical hash.

Benefits:
- streamable;
- bounded parser;
- large file continuation possible;
- no dependence on ZIP extension;
- exact restore metadata can be versioned.

Risks:
- custom format needs rigorous parser/fuzz/interoperability tests.

### FR2 — TAR-compatible stream

Mandatory comparison candidate where reliable streaming implementation/library is available.

Benefits: mature archive semantics/tooling.  
Risks: path/symlink/metadata edge cases, PHP/runtime library differences and need for split-part mapping.

### FR3 — ZIP
Convenience/compatibility only, not first canonical internal profile.

## 9. File record path rules

Canonical path is relative to a declared restore root and normalized before capture.

Reject/flag:
- absolute paths;
- `..` traversal after normalization;
- NUL/control ambiguities;
- duplicate paths after case/normalization semantics;
- symlink escape;
- device/special files unless an advanced profile explicitly supports them.

Restore never concatenates untrusted path text directly into destination without containment check.

## 10. File-index profile

Large inventories use separate `file-index` artifact parts.

Index record fields:
- path ID/normalized relative path;
- source root class;
- file type;
- logical bytes;
- mtime/permissions only where restore policy supports;
- logical content SHA-256 policy result;
- file-data artifact/part/record reference;
- capture status/warning;
- symlink target metadata only when permitted.

Index can be streamed/split; manifest references all index parts.

## 11. Large-file strategy

A huge file is not buffered into memory.

Profiles:
- dedicated file-data part;
- continuation records across multiple parts;
- adapter-native multipart happens below WPE logical part when provider benefits from it.

Provider multipart part boundaries are not automatically WPE bundle-part identities. Remote adapter can map one WPE part to one/many provider upload parts.

## 12. Chunk sizing profile

No one fixed chunk size is canonical.

`ChunkProfile` derives target stored part size from:
- memory limit;
- execution budget;
- stream buffer size;
- provider minimum/maximum multipart sizes;
- maximum part count;
- network reliability;
- object/listing overhead;
- compression/encryption characteristics;
- restore seek/retry cost.

Candidate benchmark classes:
- CP-S: ~8 MiB class;
- CP-M: ~32 MiB class;
- CP-L: ~64 MiB class;
- CP-XL: ~128 MiB+ class where provider/host benefits.

These are benchmark labels, not accepted defaults.

Runtime can choose a certified profile per destination while logical artifact ordering remains provider-neutral.

## 13. Database artifact profiles

### DB1 — typed schema + typed row-record stream — first security/portability candidate

Separate schema descriptor from row records.

Potential benefits:
- parser controls operations;
- safer table/site mapping;
- no blind arbitrary SQL execution;
- typed validation/rewrite during migration restore.

Cost: more implementation work; exact fidelity must be proven for WordPress/plugin data.

### DB2 — controlled SQL logical stream

Mandatory comparison for compatibility/performance.

Requirements:
- generated only by certified WPE capture path;
- manifest authentication/hash verification before destructive restore;
- strict statement/parser policy;
- no treating arbitrary imported SQL as trusted because file is named backup;
- prefix/domain/site remap semantics explicit.

### DB3 — hybrid
Schema metadata + efficient bulk data stream.

P-013 must decide per supported DB/runtime profile; no final DB payload format is accepted by this document.

## 14. Capture consistency

Manifest records capture consistency class, e.g.:
- BC0 best-effort live files/data;
- BC1 DB transaction/snapshot-consistent where supported;
- BC2 coordinated application quiesce/freeze profile;
- BC3 host/provider snapshot adapter certified.

Backup UI cannot claim “consistent point-in-time” without certified capture class.

File volatility during capture is recorded/retried according to policy.

## 15. Manifest finalization

Canonical order:
1. create Backup Set/run + incomplete namespace;
2. capture parts;
3. compute stored SHA-256 per finalized local part;
4. perform local verification;
5. upload destination copies under staging/incomplete state;
6. verify required remote parts according to destination capability;
7. produce final authoritative manifest containing exact part descriptors;
8. encrypt/authenticate manifest when encryption enabled;
9. upload/finalize manifest;
10. publish provider commit marker/final state last;
11. Remote Copy transitions only according to ADR-0056/0084.

A manifest generated before all required descriptors are known is not final.

## 16. Outer header

Outer header stays minimal and bounded:
- magic/format family;
- bundle schema version;
- backup UUID;
- manifest reference;
- encryption profile/key-slot descriptors needed to bootstrap recovery;
- required feature flags;
- finalization/commit reference.

Sensitive paths/content/site inventory remain inside protected manifest when encryption enabled.

## 17. Encryption mapping

Per-backup DEK from accepted Backup crypto architecture.

Preferred streaming mapping remains evidence-gated but must provide:
- unique nonce/header semantics per encrypted stream/part;
- authenticated final record/end marker;
- AAD binding Backup Set/artifact/part identity/profile;
- no key/plaintext in manifest/header;
- independent recovery key slots.

Corrupt/missing encrypted part fails verification; restore does not continue with silently truncated plaintext.

## 18. Resume/reconciliation

Operational journal tracks capture/upload progress but is not authoritative bundle truth.

After crash/lost journal:
- inspect local/remote object descriptors;
- hash/size verify existing candidate part;
- match immutable part identity;
- resume/re-upload without declaring bundle final;
- rebuild final manifest only from verified current parts.

## 19. Parser/resource limits

Before restore/import reader allocates resources, enforce:
- max manifest bytes/depth/objects;
- max part count;
- max declared logical/stored size policy;
- compression expansion ratio/absolute output limits;
- path length/count limits;
- file count limits;
- DB record/table limits according to expected scope;
- algorithm/profile allowlist;
- duplicate/conflicting part/path detection.

Metadata declarations are untrusted until validated.

## 20. Single-file export

For user download/transport, WPE may package logical bundle into one convenience archive.

Rules:
- canonical inner manifest/part hashes retained;
- outer archive checksum is supplemental;
- extraction limits/path safety mandatory;
- inability to create ZIP does not make Backup engine unavailable if canonical multipart storage works;
- one-file export need not be provider-upload format.

## 21. Restore verification order

1. validate header/profile limits;
2. locate manifest;
3. unwrap key/decrypt/authenticate manifest when encrypted;
4. validate manifest schema/feature flags;
5. verify required Part inventory;
6. verify stored SHA-256 before/while consumption;
7. decrypt/authenticate;
8. decompress under limits;
9. parse logical records under limits;
10. restore according to dependency plan;
11. verify logical/post-restore state.

## 22. Future P-013 evidence — NOT AUTHORIZED

Compare:
- FR1 vs FR2 for file-data stream;
- DB1/DB2/DB3;
- CMP0/CMP1 and optional later CMP3;
- CP-S/M/L/XL;
- encrypted/unencrypted paths;
- local/S3-family/WebDAV/SFTP/Drive mappings.

Fixtures:
- 1GB/10GB/100GB classes where infrastructure permits;
- 1M tiny files;
- one huge file;
- incompressible media-heavy site;
- compressible text/code-heavy site;
- DB with large options/meta/custom tables;
- corrupt/truncated/missing/reordered part;
- zip/tar path traversal/bomb corpus;
- crash before/after final manifest;
- lost local journal with remote staged parts;
- cross-server encrypted disaster restore;
- low memory/time budget.

Metrics:
- CPU;
- peak memory;
- wall time;
- compression ratio;
- upload object/part count;
- retry bytes after failure;
- restore throughput;
- parser/failure behavior.

## 23. Paper recommendation

Accept:
- manifest-first multipart logical bundle;
- **SHA-256 H-B1** stored-byte integrity baseline;
- CMP0 mandatory fallback;
- CMP1 gzip streaming first general compression comparison where available;
- ZIP as convenience adapter, not canonical engine;
- FR1 vs FR2 and DB1/DB2/DB3 as P-013 comparisons;
- provider multipart boundaries below WPE logical part identity.

Exact default chunk size, file-record byte encoding, DB payload and compression level remain evidence-gated.

## 24. Development gate

No archive, hash scan, compression stream, database dump, file capture, upload, encryption, restore or P-013 benchmark may execute before explicit owner consent under ADR-0014.