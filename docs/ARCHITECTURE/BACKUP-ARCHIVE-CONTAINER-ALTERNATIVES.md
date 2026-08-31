# WPEssential — Backup Archive / Container Alternatives

Status: **Phase 0 paper architecture / no archive implementation authorized**  
Related: Backup Manager exhaustive spec, Backup Restore Semantics, ADR-0021, provider certification matrix.

## Goal

Define a portable, streamable and verifiable Backup Set container that can survive large sites, multipart remote destinations and disaster recovery without requiring the original WordPress database to understand what was stored.

## Non-negotiable requirements

A WPE Backup Set must support:
- versioned manifest readable independently of WordPress DB;
- chunk/part inventory;
- checksums/integrity;
- DB + filesystem artifacts;
- optional authenticated encryption;
- large-file streaming;
- partial/multipart remote transfer;
- deterministic restore ordering;
- source environment metadata;
- corruption/missing-part detection;
- provider-neutral logical structure;
- no requirement to load whole archive into PHP memory.

---

# Logical Backup Set structure

Regardless of physical container:

- `manifest` — authoritative metadata/index;
- `database` artifacts;
- `files` artifacts/chunks;
- `metadata` safe environment inventory;
- optional `wpe-config` portable package;
- encryption/key-wrap metadata that contains no plaintext recovery secret;
- integrity tree/checksum list;
- optional signatures/authenticity metadata future.

One Backup Set can map to one file or many parts.

---

# Manifest requirements

Manifest fields candidate:
- format ID + schema version;
- backup UUID;
- site/network identity snapshot;
- created/completed timestamps;
- source WP/PHP/DB versions;
- source URL/path/table prefix;
- scope requested vs captured;
- DB table/artifact list;
- filesystem root mappings;
- entries/parts with sizes/checksums/order;
- compression algorithms;
- encryption mode/key-wrap IDs;
- exclusions/skips/errors;
- plugin/theme inventory;
- WPE Free/Pro/schema versions;
- destination copies/certification metadata references;
- restore compatibility flags;
- manifest checksum/signature reference.

Manifest must remain small enough to fetch/read before downloading full Backup Set.

---

# Alternative A — Single ZIP

Pros:
- ubiquitous tooling;
- simple user mental model;
- browser/manual download friendly.

Cons:
- large-site central-directory/streaming constraints;
- remote resumability often becomes whole-file/multipart provider concern;
- encrypted ZIP interoperability/security varies by method/library;
- partial corruption can affect large archive;
- append/update is poor fit.

Candidate role:
- small/manual local backups only if capability/size limits pass;
- not universal internal architecture assumption.

---

# Alternative B — TAR stream + compression

Pros:
- naturally streamable sequential archive;
- simple file payload model;
- gzip/zstd-like compression can stream when host support exists.

Cons:
- random access weaker;
- one giant stream still awkward for resumable remote transfer;
- native PHP/server tooling varies;
- compression algorithm support portability.

Candidate role:
- internal artifact stream that is subsequently chunked.

---

# Alternative C — Manifest + independent content chunks

Current paper preference.

Structure:
- small root manifest;
- DB chunks/segments;
- filesystem chunks/pack files;
- each part independently checksummed;
- optional per-part compression/encryption;
- logical entry map from source path/table range to part offsets/objects.

Pros:
- resumable upload/download;
- retry only failed chunks;
- corruption localized;
- remote object-storage friendly;
- large-site scaling;
- provider can keep parts as separate objects;
- restore can retrieve selected scopes/parts.

Costs:
- more complex manifest/index;
- cleanup must delete all set parts safely;
- manual download UX may need packaging/export convenience;
- consistency and part ordering need rigorous design.

---

# Alternative D — Content-addressed/deduplicated chunk store

Potential future advanced mode.

Pros:
- incremental backups and deduplication;
- efficient unchanged media sets.

Costs/security:
- much more complex garbage collection/refcounts;
- encryption/dedup interaction;
- privacy/data-deletion semantics;
- restore depends on many shared objects;
- provider portability/support burden.

Decision: **defer**. First reliable Backup release should not make global deduplication a core dependency.

---

# Current paper recommendation

Use **logical Manifest + independently verifiable chunks/parts** as WPE internal Backup Set model.

A convenience ZIP/TAR export can exist for small/manual workflows, but restore engine should consume WPE manifest/artifact model rather than infer truth from archive filenames.

---

# Chunk semantics

Each part candidate fields:
- part UUID/index;
- type: DB/files/metadata/config;
- logical range/path group;
- uncompressed size;
- compressed size;
- checksum of stored ciphertext/artifact;
- optional checksum of canonical plaintext inside authenticated container where safe/useful;
- compression profile;
- encryption profile;
- nonce/algorithm metadata according to accepted container;
- remote object key/reference;
- dependency/order.

Chunk size is adaptive/provider/environment configuration, not hardcoded universal number.

---

# Database artifact alternatives

## SQL logical stream
Portable text statements/data rows but must be generated from trusted WPE dumper and restored through controlled parser/import process.

## Typed row segments
Manifest-described tables/columns + row chunks in structured format.

Benefits:
- safer transform/filter/selective restore;
- avoid treating raw SQL as executable artifact.

Costs:
- own serializer/parser;
- data type/charset edge cases;
- bulk insert performance.

Paper direction:
- evaluate typed row-segment format vs controlled SQL dump in future spike;
- do not accept arbitrary SQL backup as trusted merely by extension.

---

# Files artifact packing

Do not create one remote object per tiny WordPress file by default.

Candidate:
- group many files into bounded pack chunks;
- very large files can become dedicated chunks;
- manifest maps source relative path → pack/chunk + entry metadata.

Record:
- relative canonical path;
- size;
- mtime optional/advisory;
- checksum;
- permissions conservative subset if restored;
- symlink handling state;
- MIME optional.

No absolute source path required for ordinary restore mapping beyond safe source-root metadata.

---

# Compression

Per part profile declares:
- none;
- ZIP-deflate-like profile if using ZIP convenience;
- gzip/other supported profile after capability evidence.

Do not choose modern algorithm solely for benchmark speed if shared-host portability is poor.

Compression is before encryption in normal design.

---

# Encryption integration

ADR-0021 accepted per-backup DEK + independent recovery wrapping.

Container must support:
- AEAD-authenticated part encryption;
- part-specific nonce uniqueness;
- AAD binding to backup UUID/part index/type/format version;
- wrapped DEK metadata outside ciphertext as needed;
- no plaintext passphrase/recovery key;
- tampering detected before restore of corrupted part.

Exact algorithm/KDF/container remains pending.

---

# Integrity

Minimum:
- checksum every stored part;
- manifest checksum;
- expected size;
- no missing/unexpected part accepted silently.

Potential:
- Merkle/tree integrity for huge sets only if it materially improves verification; not required by fashion.

Verification tiers remain V0/V1/V2/V3 from Backup semantics.

---

# Remote destination object layout

Logical example:
- backup-set prefix/UUID;
- manifest object;
- parts directory/prefix;
- completion marker only after required parts + manifest verify.

Important:
- incomplete upload never gets normal “complete” marker;
- destination listing should not mistake orphan parts for valid Backup Set;
- retention deletes completion marker/manifests/parts transactionally as provider permits;
- cleanup can recover abandoned multipart uploads.

Exact object naming must avoid leaking site-sensitive names where unnecessary.

---

# Resume

Transfer state stores locally/safely:
- Backup UUID;
- destination;
- part status;
- provider upload session IDs;
- uploaded byte/part state;
- checksums;
- expiry.

Resume always rechecks local artifact checksum and provider session validity.

If local artifact changed/corrupt, do not resume into same completed logical set.

---

# Selective restore

Manifest/chunk model supports selecting:
- DB only;
- selected table groups;
- uploads;
- plugin/theme path;
- WPE config;
- individual file/path groups when pack index supports.

But selective availability in container does not imply semantic safety. Dependency preflight still controls whether selective restore is offered.

---

# Disaster recovery bootstrap

Manifest must be readable with minimal standalone recovery tooling even when WP DB unavailable.

Recovery needs:
- format parser;
- key-unwrapping input;
- destination credentials/backup file access;
- checksum verification;
- extraction/import engine.

This does not authorize a standalone executable yet; it is a format requirement.

---

# Forward/backward compatibility

Restore client reads manifest format version.

Behavior:
- known compatible version → proceed;
- newer unknown version → fail safe with exact requirement, never guess;
- older supported version → parser adapter/migration;
- unknown required compression/encryption profile → fail before destructive restore.

---

# Security

Protect against:
- path traversal;
- archive bombs/decompression ratio abuse;
- duplicate path overwrite tricks;
- symlink escape;
- malformed sizes/offsets;
- forged manifest/part substitution;
- chosen-ciphertext/tamper according to AEAD profile;
- remote object confusion between Backup Sets.

Never extract before path/size/manifest validation.

---

# Retention/cleanup

A Backup Set is prunable only when:
- not protected/restore point;
- retention policy says eligible;
- no active restore/transfer;
- required replacement backup verified if policy depends on replacement;
- deletion plan knows every remote/local part.

Partial cleanup failure leaves explicit orphan status/retry, not false deleted state.

---

# Future spike — NOT AUTHORIZED

Compare:
- single ZIP small/medium;
- TAR/stream + chunks;
- manifest+pack/chunk model;
- typed DB segments vs controlled SQL dump.

Fixtures:
- 1GB/10GB/100GB synthetic media where infrastructure permits;
- millions of small files candidate;
- huge single file;
- DB large tables;
- interrupted upload/download;
- missing/corrupt part;
- wrong key;
- selective restore;
- provider multipart expiry;
- low disk/memory.

Measure memory, CPU, compression, part overhead, resume efficiency, restore speed and failure recovery.

No archive/chunk/encryption/runtime implementation has been created or executed.