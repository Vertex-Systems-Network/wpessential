# WPEssential — Backup Provider Target & Certification Matrix

Status: **Phase 0 planning — no storage/provider implementation authorized**  
Canonical certification architecture: `../ARCHITECTURE/BACKUP-PROVIDER-CERTIFICATION-CONTRACT.md` + ADR-0053.  
Remote copy lifecycle: `../ARCHITECTURE/BACKUP-REMOTE-COPY-LIFECYCLE.md` + ADR-0056.

## Goal

Target 25+ destinations without building unrelated upload engines, while refusing to call a destination “supported” until restore-oriented certification exists.

**Current target catalog: 34 destinations.**  
**Current certified provider count: 0.**

A listed provider is a roadmap candidate only. Provider logos do not imply support.

---

# Canonical certification levels

The old B0–B3 planning levels are **superseded** by ADR-0053.

- **C0 — Detected / Connectable**
- **C1 — Upload Certified**
- **C2 — Resumable & Integrity Certified** or explicitly `Integrity Certified / Non-resumable`
- **C3 — Restore Certified**
- **C4 — Disaster Restore Certified**

Normal public `Supported Backup Destination` labeling requires **C3 or higher**.

C4 corresponds to a repeatable fresh/disaster restore profile and supports the strongest V3 confidence claim.

---

# Protocol families

## PF-01 — Local Filesystem / browser delivery
- Local server filesystem
- Browser/manual download

Browser/manual download is a delivery mechanism, not durable remote backup storage.

## PF-02 — FTP / FTPS legacy family
- FTP
- FTPS

FTP is legacy/insecure without TLS and should be discouraged when stronger alternatives are available. Resume/finalization varies by server/client and must be certified.

## PF-03 — SFTP
- SFTP over SSH

SFTP is not “FTP over SSL”. Host-key verification is mandatory. Resume/rename behavior is certified against actual client/server profile.

## PF-04 — WebDAV
- generic WebDAV
- Nextcloud profile
- ownCloud profile

RFC 4918 does not provide a universal large-file resumable-upload session equivalent to S3 multipart/Drive sessions. Generic WebDAV cannot be marketed resumable without a certified extension/profile.

## PF-05 — S3-compatible object storage
Reference semantics: Amazon S3 multipart.

Provider profiles may omit AWS features. Required operations/capabilities are declared explicitly rather than inferred from “S3 compatible”.

## PF-06 — Google Cloud Storage native
Native GCS resumable session semantics when native adapter is selected.

## PF-07 — Azure Blob Storage
Block Blob/staged-block semantics when native adapter is selected.

## PF-08 — Google Drive
OAuth + resumable Drive upload sessions.

## PF-09 — Microsoft Graph Drives
OneDrive Personal, OneDrive Business and SharePoint document libraries via certified Graph Drive profiles.

## PF-10 — Dropbox
OAuth + upload-session append/finalize semantics.

## PF-11 — Other provider-native APIs
Box, pCloud, Bunny Storage, MEGA or future providers where a shared family cannot satisfy requirements.

## PF-12 — OpenStack Swift family
OpenStack Swift and selected compatible providers after large-object/auth profile certification.

---

# Provider target matrix

Priority legend:
- **P0** — first reference/proof providers
- **P1** — high-value expansion after family proof
- **P2** — later expansion
- **Review** — API/library/legal/maintenance review required before adapter commitment

Certification state for every row below is currently **Planned / Not Certified**.

| # | Destination | Family | Large-transfer candidate | Auth/secret class | Priority | Key certification concern |
|---:|---|---|---|---|---|---|
| 1 | Local server | PF-01 Filesystem | streamed/chunked write + safe finalization | filesystem policy | P0 | disk/free-space/permissions/rename/durability/restore |
| 2 | Browser/manual download | PF-01 HTTP/local temp | generated archive stream | WP auth/nonces | P0 | size/time/shared-host limits; not durable storage |
| 3 | FTP | PF-02 FTP | streamed/restart only if proven | P3 | P2 | insecure without TLS; server resume/finalization variance |
| 4 | FTPS | PF-02 FTP/TLS | streamed/restart only if proven | P3 | P2 | certificate/TLS validation and resume variance |
| 5 | SFTP | PF-03 SFTP | offset resume if client/server certified | P3 key/password | P1 | host-key verification, resume, temp→final rename |
| 6 | WebDAV | PF-04 | verified PUT/temp→MOVE; resume only if profile proves | P3 | P1 | no generic resumable claim; TLS/read-back/finalization |
| 7 | Nextcloud | PF-04 profile | provider chunk extension only after certification | P3/OAuth | P1 | version/chunk/quota/path/lock semantics |
| 8 | ownCloud | PF-04 profile | provider-specific | P3 | P2 | certify separately from Nextcloud |
| 9 | Amazon S3 | PF-05 S3 | multipart | P3 key/role | P0 | reference multipart/checksum/abort/list/download/restore |
| 10 | Generic S3-compatible | PF-05 profile | multipart only if required subset exists | P3 | P0/P1 | custom endpoint SSRF/trust; capability probing |
| 11 | Cloudflare R2 | PF-05 profile | multipart | P3 | P1 | AWS feature differences; checksum/lifecycle profile |
| 12 | Backblaze B2 S3 API | PF-05 profile | multipart | P3 | P1 | S3 differences/auth/checksum/profile |
| 13 | Wasabi | PF-05 profile | multipart candidate | P3 | P1 | current compatibility/retention/provider semantics |
| 14 | DigitalOcean Spaces | PF-05 profile | multipart candidate | P3 | P1 | region/endpoint/CDN distinction |
| 15 | MinIO | PF-05 profile | multipart candidate | P3 | P1 | self-hosted endpoint/TLS/version variability |
| 16 | Google Cloud Storage | PF-06 native or certified interoperability | native resumable | P3 OAuth/service credential | P1 | native-vs-S3 maintenance choice; session/checksum/restore |
| 17 | Google Drive | PF-08 | resumable session | P3 OAuth refresh | P0 | session expiry/status/resume/quota/read-back/restore |
| 18 | Dropbox | PF-10 | upload session | P3 OAuth | P0/P1 | append/finalize/session expiry/content hash/restore |
| 19 | OneDrive Personal | PF-09 Graph | upload session | P3 OAuth | P0/P1 | nextExpectedRanges/expiry/conflicts/token refresh |
| 20 | OneDrive Business / SharePoint | PF-09 Graph | upload session | P3 OAuth | P1 | tenant/site/drive/admin-consent/profile differences |
| 21 | Azure Blob Storage | PF-07 | staged blocks + block-list commit | P3 SAS/key/OAuth | P1 | commit/concurrency/checksum/auth/versioning/restore |
| 22 | Box | PF-11 native | chunk/upload-session candidate | P3 OAuth/JWT profile | P2 | current API/session limits/version/support |
| 23 | pCloud | PF-11 native | standard/progress; resumability unproven | P3 OAuth/token | P2 | no resumable claim before interruption fixture |
| 24 | MEGA | PF-11 native/library candidate | undecided | P3 | Review | official API/library/license/maintenance/security |
| 25 | OpenStack Swift | PF-12 | segmented object candidate | P3 | P2 | auth variants/large object/manifest/restore |
| 26 | Rackspace/Swift-compatible | PF-12 profile | segmented candidate | P3 | P2 | provider viability/profile refresh |
| 27 | Oracle Object Storage | PF-05 profile or native | multipart candidate | P3 | P2 | prefer shared S3 only if certified subset works |
| 28 | Akamai/Linode Object Storage | PF-05 profile | multipart candidate | P3 | P2 | endpoint/region/provider profile |
| 29 | Vultr Object Storage | PF-05 profile | multipart candidate | P3 | P2 | endpoint/profile certification |
| 30 | Scaleway Object Storage | PF-05 profile | multipart candidate | P3 | P2 | endpoint/profile certification |
| 31 | Hetzner Object Storage | PF-05 profile | multipart candidate | P3 | P2 | current product/API/profile refresh |
| 32 | Storj S3-compatible | PF-05 profile | multipart candidate | P3 | P2 | gateway semantics/performance/restore |
| 33 | IDrive e2 | PF-05 profile | multipart candidate | P3 | P2 | provider profile/checksum/restore |
| 34 | Bunny Storage | PF-11 native | provider-specific | P3 API key | P2 | do not assume S3; native API/finalization/restore |

The list exceeds the original 25-destination requirement, but support claims remain certification-driven.

---

# Static research observations — not certification

## Amazon S3
Official S3 documentation supports independent multipart parts, retry, explicit completion/abort and object checksum mechanisms. Multipart ETag is not necessarily a full-object MD5.

## Google Drive
Official Drive API supports resumable sessions, byte-range chunk upload, status querying and resume; session expiry is part of provider semantics.

## Google Cloud Storage
Official GCS documentation recommends resumable uploads for large/unstable transfers. Only a completed resumable upload appears as the final object.

## Microsoft Graph Drives
Official Graph upload sessions expose expiry and expected/missing byte ranges and can resume interrupted large uploads.

## Dropbox
Official guidance exposes upload-session start/append/finish and content-hash semantics for large transfers.

## WebDAV
RFC 4918 defines DAV resource/collection/property/locking extensions, not a universal resumable upload-session protocol.

## SFTP
SFTP has historic/current Internet-Draft protocol specifications rather than one final IETF SFTP RFC. Common drafts support offset writes, but actual resume/rename durability must be client/server certified.

---

# Family UI contracts

## S3 profile
- provider preset / Custom S3;
- endpoint;
- region;
- bucket;
- prefix;
- access/secret/session credential refs;
- path-style/virtual-host setting only where needed;
- mandatory TLS validation;
- provider-certified storage class/SSE options;
- multipart threshold/part size/concurrency as bounded Advanced controls;
- retry/timeouts;
- retention/delete behavior;
- isolated connection/write/read/delete probe.

AWS-only options are hidden/disabled for profiles that do not support them.

## OAuth Drives
- Connect/Reconnect;
- safe account identity;
- target folder/drive ID + display label;
- token/scopes health;
- quota/permission diagnostics when available;
- provider resumable settings only when certified;
- disconnect without remote deletion.

## SFTP
- host/port;
- username;
- password OR private-key Vault ref;
- private-key passphrase ref;
- host-key fingerprint/trust policy;
- base directory;
- timeouts/keepalive;
- resume toggle only if certified;
- isolated write/read/rename/delete probe.

## WebDAV
- HTTPS endpoint;
- credential ref;
- base path;
- certificate validation;
- provider profile;
- finalization strategy;
- connection/PROPFIND/write/read/MOVE/delete probes according capability.

---

# Integrity and Remote Copy truth

The provider matrix inherits ADR-0056 Remote Copy states and ADR-0053 certification.

Rules:
- provider API `2xx` alone is not remote verification;
- final provider Commit Point must be reached;
- final WPE manifest/completion marker is published last where architecture permits;
- WPE part hash remains authoritative transport-integrity evidence;
- provider checksum is recorded with algorithm/semantics;
- encrypted parts verify ciphertext hash plus AEAD during restore;
- `remote_verified` is required for V2;
- C3 requires actual restore fixture;
- C4 requires disaster/fresh-environment restore fixture.

---

# Acceptance fixtures per provider — future only

Authentication:
- bad/missing/read-only/revoked credential;
- token refresh/rotation;
- wrong bucket/folder/path;
- host-key/certificate mismatch.

Transfer:
- zero/small/large object;
- network interruption;
- process crash;
- resume/new process;
- 429/rate limit;
- 5xx/timeout;
- quota/storage full;
- session expiry;
- duplicate/conflict;
- cancellation/orphan cleanup;
- unknown commit outcome.

Integrity:
- size/checksum mismatch;
- corrupt/missing remote part;
- stale manifest;
- encrypted corruption;
- provider-native checksum interpretation.

Lifecycle:
- list/discovery;
- retention prune;
- delete/trash/versioning/retention lock;
- external deletion/lifecycle rule;
- credential reauthorization.

Restore:
- complete download;
- interrupted restore/resume;
- auth refresh mid-restore;
- encrypted restore;
- clean/fresh disaster restore for C4;
- post-restore health verification.

No fixture has been executed.

---

# Security boundaries

1. Credentials are Vault refs and write-only in normal UI.
2. OAuth callbacks use accepted provider/account-link security profiles.
3. Custom S3/WebDAV/SFTP endpoints require explicit destination trust/SSRF policy; intentional private backup endpoints are distinguished from arbitrary webhook URLs.
4. TLS validation is mandatory by default.
5. SFTP host-key verification is mandatory.
6. Provider error bodies are sanitized/redacted.
7. WPE client-side encryption is separate from provider SSE/TLS.
8. Disconnect never means delete remote backups.
9. Delete/prune is explicit, audited and provider-semantics-aware.
10. SDK/library dependencies require license/maintenance/security review.

---

# Future proof order after development consent

Recommended evidence order, not authorization:
1. Local filesystem;
2. Amazon S3 reference;
3. one non-AWS S3 profile to prove compatibility boundaries;
4. SFTP;
5. WebDAV/Nextcloud;
6. Google Drive;
7. Microsoft Graph Drives;
8. Dropbox;
9. Azure/GCS native where justified;
10. expand S3/provider-native profiles based on demand and evidence.

## Development gate

**No provider SDK, auth flow, connection test, upload, download, delete, restore or certification fixture is authorized before explicit owner development consent under ADR-0014.**
