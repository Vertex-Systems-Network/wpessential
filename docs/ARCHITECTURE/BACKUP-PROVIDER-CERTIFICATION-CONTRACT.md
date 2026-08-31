# WPEssential — Backup Provider Certification Contract

Status: **Phase 0 planning only / no implementation authorized**  
Related: ADR-0021, ADR-0033, ADR-0043, P-013, Backup Manager Exhaustive Spec.

## 1. Purpose

WPEssential will not treat a provider logo, successful OAuth connection, or one successful upload as proof that a Backup destination is supported.

A destination becomes supportable through two layers:

1. **Protocol Family Adapter** — reusable transfer semantics shared by compatible providers.
2. **Provider Capability Profile** — provider-specific authentication, limits, deviations, consistency, retention and restore behavior.

This avoids maintaining dozens of unrelated upload engines while also avoiding the false assumption that every nominally compatible provider implements every protocol feature identically.

The existing 34 destination catalog entries remain **targets** until they pass certification.

---

## 2. Provider architecture

Canonical flow:

`Backup Set → Logical Bundle/Manifest → Provider Adapter → Provider Capability Profile → Remote Staging → Remote Finalization → Remote Verification → Restore Source`

Provider storage is transport/persistence only. It does not become the canonical definition of Backup integrity.

WPE Backup manifest remains authoritative for:
- Backup Set identity;
- expected parts;
- logical sizes;
- WPE hashes/checksums;
- encryption profile/key-slot metadata;
- source/site scope;
- created/completed state;
- provider copies;
- restore compatibility metadata.

Provider-native checksums/ETags are additional evidence only and are interpreted according to that provider/profile.

---

## 3. Initial protocol families

### PF-01 — Local Filesystem / Mounted Storage
Examples:
- local private directory;
- externally mounted/NFS-like storage only where host semantics are certified.

Capabilities to prove:
- atomic/replace behavior;
- fsync/durability assumptions where available;
- free-space detection;
- temporary/staging file handling;
- cross-filesystem rename limitation;
- permissions/ownership;
- large-file support;
- restore read path.

### PF-02 — S3-compatible Object Storage
Examples may include:
- Amazon S3;
- Cloudflare R2;
- Backblaze B2 S3 API;
- Wasabi;
- DigitalOcean Spaces;
- MinIO;
- other providers only after profile certification.

Family model:
- object key/prefix namespace;
- multipart upload for large parts;
- upload ID persisted for crash resume;
- individual part retries;
- explicit CompleteMultipartUpload/finalization;
- explicit AbortMultipartUpload cleanup;
- Head/Get/List/Delete operations;
- provider checksum capability when available.

Important: S3 multipart ETag is **not assumed to be the MD5 of the complete object**. WPE integrity remains based on its own manifest digest plus a provider checksum when the provider has a trustworthy documented checksum API.

### PF-03 — Google Cloud Storage Native
Although GCS can expose interoperability surfaces, native GCS resumable sessions are modeled separately when native API semantics are used.

Family model:
- resumable session URI;
- persisted upload session state;
- chunk offsets discovered/confirmed from provider response;
- upload completes only when provider returns final object;
- incomplete session expiration handled explicitly.

### PF-04 — Azure Blob Storage
Family model:
- Block Blob;
- staged blocks;
- block-list commit/finalization;
- provider concurrency/ETag conditions where used;
- download/range/checksum metadata where certified.

### PF-05 — Google Drive
Family model:
- Drive file/folder IDs, not path strings as canonical remote identity;
- OAuth refresh credentials in Vault;
- resumable upload session URI;
- chunked Content-Range upload;
- provider upload status query/resume;
- session expiration awareness;
- uploaded File resource returned only after final completion;
- restore by immutable/stable remote file identity where practical.

Google Drive folder names are presentation; stored file/folder IDs are remote identity.

### PF-06 — Microsoft Graph Drives
Targets:
- OneDrive Personal/Business;
- SharePoint document libraries through Graph Drives when certified.

Family model:
- Drive/DriveItem stable IDs;
- OAuth credentials in Vault;
- createUploadSession;
- preauthenticated upload URL treated as secret P3 and never logged;
- expirationDateTime;
- nextExpectedRanges/missing-range resume;
- optional deferred commit where provider profile supports;
- conflict behavior explicit;
- final DriveItem identity recorded after commit.

### PF-07 — Dropbox
Family model:
- namespace/path identity according to Dropbox API;
- OAuth credentials in Vault;
- upload_session/start;
- append_v2 or supported batch/concurrent form;
- finish/finish_batch as final commit;
- provider content hash used as secondary integrity evidence when documented;
- upload session expiry persisted;
- async batch finalization polled to terminal result where used.

A successful append is not a completed remote Backup object.

### PF-08 — SFTP
SFTP is treated as an implementation-interoperability family rather than a single modern RFC-backed resumable-upload guarantee.

The historic IETF SFTP specifications remain Internet-Drafts rather than a final SFTP RFC. Implementations commonly expose offset-based reads/writes, but WPE must certify the actual chosen PHP/client library and servers.

Required profile behavior:
- SSH host-key verification required;
- password/private-key credential refs in Vault;
- no `StrictHostKeyChecking=no` equivalent default;
- upload to temporary remote name;
- resume only after verifying remote size/identity and adapter capability;
- final rename to committed name only when rename semantics are certified;
- download/read-back verification;
- no shell-command fallback disguised as SFTP.

### PF-09 — WebDAV
RFC 4918 defines WebDAV resource/collection/property/locking semantics, but does **not** provide a universal cross-server resumable large-file upload protocol equivalent to S3 multipart or Drive upload sessions.

Therefore generic WebDAV baseline certification requires:
- HTTPS by default;
- authenticated PUT/GET/HEAD or equivalent documented operations;
- collection creation/listing/deletion;
- temporary-resource upload + final MOVE where supported/certified;
- read-back integrity;
- no generic claim of resumable upload unless a provider-specific extension/profile proves it;
- LOCK support is not assumed necessary merely because WebDAV defines it.

### PF-10 — Provider-native APIs
Providers that do not fit a reusable family can implement a native adapter, but must still implement this certification contract and must justify why an existing family is insufficient.

Examples can include pCloud, MEGA, Box, or future providers depending current API terms/capabilities.

---

## 4. Capability descriptor

Every family and provider profile declares a machine-readable capability conceptually equivalent to:

### Identity / namespace
- stable remote object/file ID available;
- path/prefix namespace;
- case sensitivity known;
- Unicode normalization behavior known;
- duplicate-name semantics known;
- overwrite conflict strategy supported;
- rename/move supported;
- folders/collections real vs virtual.

### Authentication
- OAuth 2.0;
- API key/token;
- access/secret key;
- service credential;
- SSH password;
- SSH private key;
- WebDAV Basic/Digest/OAuth provider profile;
- credential rotation/refresh capability;
- scopes/permissions documented.

Credentials are Vault references only.

### Upload
- simple upload;
- multipart/resumable upload;
- crash-resumable after PHP process termination;
- parallel part upload;
- maximum object/file size;
- minimum/maximum part size;
- provider session expiry;
- upload offset/status query;
- explicit abort/cancel;
- explicit finalize/commit;
- deferred commit;
- idempotent create/commit behavior.

### Integrity
- provider request checksum;
- provider persisted-object checksum;
- checksum algorithm/type;
- read-after-write metadata;
- range GET;
- full GET;
- remote size;
- ETag semantics documented;
- WPE manifest hash verified independently.

### Lifecycle
- list;
- head/metadata;
- download;
- range download;
- delete;
- bulk delete;
- retention/object lock;
- versioning;
- lifecycle rules;
- trash/recycle-bin semantics;
- immutable/WORM semantics;
- stale-session/multipart cleanup.

### Operational behavior
- rate-limit headers/codes;
- quota reporting;
- storage-full signal;
- retries safe/unsafe classification;
- eventual/strong consistency behavior relevant to WPE;
- regional/national cloud variants;
- API deprecation/versioning policy;
- maintenance/outage behavior.

### Security
- TLS requirements;
- custom endpoint SSRF policy;
- certificate/host-key validation;
- provider-side encryption capability informational;
- client-side WPE ciphertext supported;
- preauthenticated/signed URL secrecy;
- least-privilege scope/profile.

---

## 5. Certification levels

Certification levels are provider/profile specific, not module-global.

### C0 — Detected / Connectable
Proves only:
- adapter can identify provider/profile;
- credentials/auth connection can be validated safely;
- destination namespace can be inspected.

**Marketing:** Experimental/Connected only. Not Backup Supported.

### C1 — Upload Certified
Proves:
- create/upload one WPE part;
- expected remote size/identity;
- error normalization;
- delete cleanup;
- credentials refresh where relevant.

Still not enough for large-site backup support.

### C2 — Resumable & Integrity Certified
Proves where provider supports it:
- interrupted large upload resumes correctly;
- process crash does not corrupt final artifact;
- stale session cleanup;
- provider finalization semantics;
- WPE manifest hash/read-back verification;
- retry/idempotency behavior;
- provider checksum interpreted correctly.

For a family without true resumable uploads (for example generic WebDAV), C2 can be `Integrity Certified / Non-resumable` and UI must display that limitation rather than inventing resumability.

### C3 — Restore Certified
Required before WPE can market a provider as a normal **Supported Backup Destination**.

Proves:
- complete Backup Set upload;
- remote manifest/parts discovery;
- clean download/read-back;
- restore from that remote copy;
- missing/corrupt part detected;
- authentication refresh during long restore;
- cancellation/retry;
- retention/delete behavior;
- encrypted Backup restore when encryption is enabled/certified.

Corresponds at minimum to provider-level evidence supporting Backup verification semantics through **V2 Remote Verified**, plus an actual restore fixture.

### C4 — Disaster Restore Certified
Strongest provider profile.

Proves a **V3 Restore Tested** scenario from a clean/fresh environment with original application state unavailable according to the test profile.

Includes:
- recover remote credentials through documented recovery procedure;
- recover Backup encryption key/recovery kit when applicable;
- restore from remote source;
- post-restore health verification;
- documented provider-specific recovery limitations.

WPE can label this profile `Disaster Restore Certified` only after repeatable automated/manual evidence exists.

---

## 6. Remote transaction states

A remote Backup Set uses explicit states; HTTP success alone is insufficient.

Candidate state machine:

`planned → staging → uploading → uploaded_parts → finalizing → remote_committed → verifying → remote_verified`

Terminal/error states:
- cancelled;
- failed_retryable;
- failed_terminal;
- orphaned_remote_upload;
- integrity_failed;
- remote_missing;
- credentials_required;
- quota_exceeded;
- provider_session_expired.

Only `remote_verified` can count as a V2 provider copy.

---

## 7. Finalization contract

WPE never marks a destination complete merely because all byte writes returned success.

Provider family defines a **Commit Point**:

- S3-compatible: successful multipart Complete plus final object identity/metadata check.
- Google Drive: final File resource after resumable upload completes.
- Microsoft Graph: committed final DriveItem after upload session.
- Dropbox: successful finish/finish_batch terminal result.
- Azure Block Blob: committed Block List/final blob.
- GCS: completed resumable object.
- SFTP/WebDAV: final temporary→committed rename/move only where certified; otherwise complete PUT followed by verified final resource.

Manifest publication order must prevent an incomplete collection of parts from appearing as a valid completed Backup Set.

Recommended logical approach:
1. upload data parts under staging/non-final identity;
2. verify each expected part metadata/hash as supported;
3. publish/finalize manifest or completion marker last;
4. re-read final manifest/completion identity;
5. transition destination to `remote_verified` only after integrity checks.

Exact provider ordering requires implementation evidence.

---

## 8. Integrity contract

WPE maintains at least:
- logical part byte size;
- WPE cryptographic hash of stored part/ciphertext representation;
- bundle/manifest hash;
- provider remote size;
- provider native checksum where reliable and documented;
- provider stable identity/version metadata where available.

Rules:
- multipart S3 ETag is not treated as full-object MD5;
- provider checksum algorithms are recorded with algorithm/type;
- encrypted Backups verify ciphertext transport integrity independently from successful later decryption/authentication;
- a provider `200/201` alone is never V2 verification;
- random or full read-back may be part of certification; restore path must eventually exercise full data.

---

## 9. Resume contract

A resumable provider must persist only safe resume metadata:
- provider upload/session ID or secret URL reference;
- destination/profile ID;
- Backup Set/part ID;
- total size;
- confirmed remote offset/part list;
- session expiry;
- provider-specific opaque state;
- last confirmation time.

Secrets/preauthenticated upload URLs are Vault/P3 data and never plain logs/support bundles.

On resume:
1. reauthorize provider connection;
2. verify session belongs to expected Backup part;
3. query provider-confirmed state rather than trusting local last offset;
4. resume only missing/confirmed ranges/parts;
5. restart safely when session expired;
6. orphan cleanup scheduled and auditable.

---

## 10. Retry / unknown outcome

Every provider operation classifies failures:
- safe immediate retry;
- retry with backoff;
- refresh credentials then retry;
- query state before retry because outcome unknown;
- terminal configuration error;
- quota/storage full;
- integrity failure;
- authorization revoked;
- provider session expired.

Commit/finalize calls must handle the classic ambiguous state: connection lost after provider may have committed. WPE queries final remote state before issuing a duplicate destructive/commit operation when provider semantics allow.

---

## 11. Retention and deletion

Retention policy is separate from upload success.

Provider profile declares:
- hard delete vs trash/recycle bin;
- versioned delete marker behavior;
- object lock/immutability conflicts;
- lifecycle policies outside WPE control;
- eventual delete visibility;
- API permission required to delete;
- stale multipart/session cleanup.

WPE `Deleted` means provider deletion was confirmed according to certified semantics. Otherwise use truthful states such as:
- deletion_requested;
- retention_locked;
- moved_to_trash;
- delete_unverified;
- delete_failed.

---

## 12. Restore source contract

Restore begins by resolving a **Remote Backup Copy**, not by browsing arbitrary provider files as if every file were a backup.

Copy record includes:
- Backup Set UUID;
- provider/profile UUID;
- remote manifest identity;
- remote part identities;
- provider version IDs where applicable;
- verification level;
- encryption profile/key-slot metadata;
- last verified timestamp.

Preflight checks:
- manifest readable;
- expected parts present;
- sizes/hashes valid according to level;
- credentials sufficient for full read;
- encryption recovery material available;
- target compatibility checked;
- free disk/staging requirements checked.

---

## 13. Provider profile overrides

“S3-compatible” is not enough to inherit every Amazon S3 feature.

Each provider profile can override:
- multipart limits;
- checksum API support;
- versioning/object lock;
- lifecycle rules;
- region/endpoint requirements;
- path-style vs virtual-host-style addressing;
- signature/auth quirks;
- consistency behavior;
- storage classes;
- egress/API costs surfaced informationally;
- unsupported APIs.

Unsupported capability must be `false/unsupported`, not silently emulated when semantics would be weaker.

---

## 14. Initial family capability observations from static research

These are research inputs, **not certification results**:

### Amazon S3
Official S3 documentation supports multipart upload with independent parts, explicit completion/abort, retry of individual parts, and stored checksum mechanisms. Multipart ETag is not necessarily object MD5. This makes S3 a strong candidate for C2/C3 once executable adapter and restore evidence exists.

### Google Drive
Official Drive API supports resumable upload sessions with session URI, status/resume and chunked Content-Range uploads; sessions expire. Strong C2 candidate after implementation evidence.

### Google Cloud Storage
Official GCS docs recommend resumable uploads for large/unstable transfers; sessions can resume after failure and only the completed object appears in the bucket. Strong C2 candidate after evidence.

### Microsoft Graph Drives
Official Graph `createUploadSession` supports byte-range uploads, resumability, expiry and missing-range state; the upload URL is preauthenticated and must be treated as a secret. Strong C2 candidate after evidence.

### Dropbox
Official Dropbox guidance supports upload sessions, append, finish/final commit, large-file chunking and content hash. Strong C2 candidate after evidence.

### WebDAV
RFC 4918 defines WebDAV resource/collection/property/locking methods, but generic WPE WebDAV must not claim a standardized resumable large-file session model. Baseline should favor verified upload/read-back; provider extensions can raise capability level.

### SFTP
SFTP’s widely implemented file-transfer protocol has historic/ongoing draft specifications rather than one final IETF SFTP RFC. Offset writes exist in common protocol drafts, but actual resume/rename/fsync/host-key behavior must be library/server certified.

---

## 15. Certification fixture classes — future only

After explicit development consent, every provider marketed as Supported must run the applicable fixtures:

### Authentication
- valid credentials;
- invalid/revoked credentials;
- refresh/rotation;
- least privilege;
- expired OAuth;
- host-key/certificate mismatch where applicable.

### Transfer
- zero/small/large object;
- multipart/resumable;
- network interruption after random part;
- PHP worker/process crash;
- resume on new request/process;
- rate limit;
- provider 5xx;
- quota exceeded;
- session expiry;
- duplicate finalize/unknown outcome;
- cancellation and orphan cleanup.

### Integrity
- remote byte corruption simulation where feasible;
- missing part;
- wrong size;
- wrong checksum;
- stale/old manifest;
- encrypted part corruption;
- provider-native checksum interpretation.

### Lifecycle
- list/discover;
- retention prune;
- delete/trash;
- object lock/versioning;
- provider lifecycle interference;
- renamed/moved remote object where supported.

### Restore
- complete remote restore;
- partial download interruption/resume;
- credential refresh mid-restore;
- encrypted restore;
- clean/fresh-site disaster restore for C4;
- post-restore health verification.

No fixture above has been executed.

---

## 16. Support/marketing truth

UI and documentation use explicit labels:
- Planned;
- Experimental;
- Connected;
- Upload Certified;
- Integrity Certified;
- Restore Certified;
- Disaster Restore Certified;
- Deprecated/Blocked.

A provider logo must never imply certification level by itself.

“Supports 34 destinations” must not be used publicly until the corresponding provider profiles reach the agreed release certification level. Until then wording is “target/provider roadmap” or an exact certified count.

---

## 17. Sources reviewed for this planning decision

Primary references reviewed include:
- Amazon S3 multipart upload and object-integrity documentation;
- Google Drive API resumable upload documentation;
- Google Cloud Storage resumable upload documentation;
- Microsoft Graph `driveItem:createUploadSession` documentation;
- Dropbox upload-session/performance guidance;
- RFC 4918 WebDAV;
- IETF SFTP protocol Internet-Draft history/current draft state.

External behavior must be refreshed during future provider certification because API limits and capabilities can change.

## 18. Development status

**No provider adapter, authentication flow, upload, restore, API call, test fixture or benchmark has been implemented/executed.**

ADR-0014 requires explicit owner development consent before executable certification work begins.
