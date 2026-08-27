# WPEssential — Backup Provider Target & Certification Matrix

Status: **Phase 0 planning — no storage/provider implementation authorized**  
Canonical certification architecture: `../ARCHITECTURE/BACKUP-PROVIDER-CERTIFICATION-CONTRACT.md` + ADR-0053.  
Stable family/provider registry: `../ARCHITECTURE/BACKUP-PROVIDER-FAMILY-CAPABILITY-REGISTRY.md` + ADR-0061.  
Remote copy lifecycle: `../ARCHITECTURE/BACKUP-REMOTE-COPY-LIFECYCLE.md` + ADR-0056.

## Goal

Target 25+ destinations without building unrelated upload engines, while refusing to call a destination “supported” until restore-oriented certification exists.

**Current target catalog: 34 destinations.**  
**Current certified provider count: 0.**

A listed provider is a roadmap candidate only. Static research, provider logos, protocol-compatibility statements and successful future connection tests do not by themselves imply Backup support.

---

# Canonical family identity

ADR-0061 resolves an earlier planning collision where numeric `PF-xx` labels meant different families in different documents.

New canonical family identifiers are semantic `bf.*` keys. Numeric `PF-xx` values are legacy prose aliases only and MUST NOT be used as persistent/API/adapter identity.

Canonical family keys used by this matrix:
- `bf.local-filesystem`
- `bf.browser-export`
- `bf.ftp`
- `bf.ftps`
- `bf.sftp`
- `bf.webdav`
- `bf.s3`
- `bf.gcs`
- `bf.azure-blob`
- `bf.google-drive`
- `bf.msgraph-drive`
- `bf.dropbox`
- `bf.swift`
- `bf.native`

Provider-specific capability details and legacy PF alias mappings live in `BACKUP-PROVIDER-FAMILY-CAPABILITY-REGISTRY.md`.

---

# Canonical certification levels

- **C0 — Detected / Connectable**
- **C1 — Upload Certified**
- **C2 — Resumable & Integrity Certified**, or explicit `Integrity Certified / Non-resumable`
- **C3 — Restore Certified**
- **C4 — Disaster Restore Certified**

Normal public `Supported Backup Destination` labeling requires **C3 or higher**.

All 34 rows below remain **Planned / Not Certified**.

---

# Static evidence maturity

Static official-document research is tracked separately from certification:

- **SE0** — insufficient current official evidence;
- **SE1** — official protocol/compatibility statement reviewed;
- **SE2** — upload/finalization/limits reviewed;
- **SE3** — upload + integrity/lifecycle/deviation semantics reviewed.

SE0–SE3 never changes a provider's C0–C4 state. A provider can be SE3 and still be **Not Certified**.

---

# Provider target matrix

Priority legend:
- **P0** — first reference/proof providers
- **P1** — high-value expansion after family proof
- **P2** — later expansion
- **Review** — API/library/legal/maintenance review required before adapter commitment

| # | Provider key | Destination | Canonical family | Static evidence | Priority | Large-transfer / key profile constraint |
|---:|---|---|---|---|---|---|
| 1 | `local-server` | Local server | `bf.local-filesystem` | SE0 | P0 | streamed/chunked WPE write; durability/rename/disk-full/restore require runtime proof |
| 2 | `browser-export` | Browser/manual download | `bf.browser-export` | SE0 | P0 | HTTP stream only; not durable remote backup storage |
| 3 | `ftp-generic` | FTP | `bf.ftp` | SE0 | P2 | resume/restart conditional; insecure without TLS |
| 4 | `ftps-generic` | FTPS | `bf.ftps` | SE0 | P2 | TLS/certificate/data-channel + restart semantics profile-specific |
| 5 | `sftp-generic` | SFTP | `bf.sftp` | SE0 | P1 | host-key verification mandatory; offset resume/temp→rename only after client/server certification |
| 6 | `webdav-generic` | WebDAV | `bf.webdav` | SE1 | P1 | generic baseline non-resumable; PUT/read-back and MOVE only where certified |
| 7 | `nextcloud` | Nextcloud | `bf.webdav` | SE3 | P1 | provider chunk-upload extension; exact server/version profile required |
| 8 | `owncloud` | ownCloud | `bf.webdav` | SE2 | P2 | Classic/Infinite Scale/version capability profiles must remain separate |
| 9 | `amazon-s3` | Amazon S3 | `bf.s3` | SE3 | P0 | reference multipart/checksum/abort/list/download/restore profile |
| 10 | `s3-custom` | Generic S3-compatible | `bf.s3` | SE0 | P0/P1 | no AWS capability inherited without explicit profile/proof; custom endpoint trust/SSRF boundary |
| 11 | `cloudflare-r2` | Cloudflare R2 | `bf.s3` | SE3 | P1 | multipart; provider-specific checksum/part/lifecycle/consistency semantics |
| 12 | `backblaze-b2-s3` | Backblaze B2 S3 API | `bf.s3` | SE3 | P1 | SigV4/profile/version-delete and unsupported AWS features must be explicit |
| 13 | `wasabi` | Wasabi | `bf.s3` | SE3 | P1 | multipart plus provider retention/Object Lock/incomplete-upload semantics |
| 14 | `digitalocean-spaces` | DigitalOcean Spaces | `bf.s3` | SE2 | P1 | partial S3 support; region/endpoint/CDN and copy limitations |
| 15 | `minio` | MinIO / compatible endpoint | `bf.s3` | SE1 | P1 | self-hosted endpoint/TLS/version variability |
| 16 | `google-cloud-storage` | Google Cloud Storage | `bf.gcs` | SE3 | P1 | native resumable session, chunk/checksum/session secrecy semantics |
| 17 | `google-drive` | Google Drive | `bf.google-drive` | SE3 | P0 | resumable session, expiry/status/resume/quota/final File identity |
| 18 | `dropbox` | Dropbox | `bf.dropbox` | SE3 | P0/P1 | upload sessions, finish/finalize, content hash and namespace contention |
| 19 | `onedrive-personal` | OneDrive Personal | `bf.msgraph-drive` | SE3 | P0/P1 | upload session, expected ranges, expiry, preauthenticated upload URL |
| 20 | `onedrive-business-sharepoint` | OneDrive Business / SharePoint | `bf.msgraph-drive` | SE3 | P1 | tenant/site/drive/admin-consent/national-cloud profile differences |
| 21 | `azure-blob` | Azure Blob Storage | `bf.azure-blob` | SE3 | P1 | staged blocks + block-list commit; committed/uncommitted reconciliation |
| 22 | `box` | Box | `bf.native` | SE0 | P2 | current upload-session limits/finalization/client strategy still needs official-profile review |
| 23 | `pcloud` | pCloud | `bf.native` | SE2 | P2 | progress/partial upload does not prove crash-resumable provider session; no resumable claim yet |
| 24 | `mega` | MEGA | `bf.native` | SE0 | Review | official API/SDK/license/maintenance/security review required |
| 25 | `openstack-swift` | OpenStack Swift | `bf.swift` | SE3 | P2 | segmented object; SLO preferred paper baseline with explicit manifest semantics |
| 26 | `rackspace-swift` | Rackspace / Swift-compatible | `bf.swift` | SE0 | P2 | current provider viability/auth/large-object profile refresh needed |
| 27 | `oracle-object-storage-s3` | Oracle Object Storage | `bf.s3` | SE2 | P2 | S3 multipart subset supported; AWS feature inheritance prohibited |
| 28 | `akamai-linode-object-storage` | Akamai/Linode Object Storage | `bf.s3` | SE1 | P2 | S3 compatibility stated; exact multipart/checksum/version/delete matrix still needs refresh |
| 29 | `vultr-object-storage` | Vultr Object Storage | `bf.s3` | SE2 | P2 | current matrix documents multipart/lifecycle/versioning; unsupported AWS features stay disabled |
| 30 | `scaleway-object-storage` | Scaleway Object Storage | `bf.s3` | SE3 | P2 | provider documents 1–1000 multipart parts; MUST NOT inherit Amazon 10,000-part assumption |
| 31 | `hetzner-object-storage` | Hetzner Object Storage | `bf.s3` | SE0 | P2 | exact current multipart/checksum/versioning profile not yet reviewed |
| 32 | `storj-s3-gateway` | Storj S3-compatible | `bf.s3` | SE2 | P2 | operation-level compatibility; hosted/self-hosted gateway profiles separate |
| 33 | `idrive-e2` | IDrive e2 | `bf.s3` | SE3 | P2 | multipart/checksum documented; full runtime/version/delete semantics still require fixtures |
| 34 | `bunny-storage` | Bunny Storage | `bf.native` | SE0 | P2 | native API; no S3 or resumability assumption |

The list exceeds the original 25-destination requirement, but support claims remain certification-driven.

---

# Critical family rules

## S3 profiles

`bf.s3` is a reusable transfer family, not an “all AWS features true” flag.

Every provider profile must explicitly declare:
- multipart operations;
- min/max part sizes and maximum part count;
- upload/list/complete/abort behavior;
- provider checksum algorithms/semantics;
- HEAD/GET/range support;
- versioning/delete/Object Lock/lifecycle behavior;
- incomplete multipart cleanup;
- endpoint/region/addressing/signature requirements;
- consistency relevant to final verification;
- known unsupported AWS APIs.

## WebDAV profiles

Generic `bf.webdav` is non-resumable by default. Provider chunking, including Nextcloud/ownCloud extensions, is a versioned provider capability and cannot be inferred from RFC 4918.

## Graph profiles

OneDrive Personal and OneDrive Business/SharePoint share `bf.msgraph-drive` but remain separate provider profiles because authorization, tenant/site resolution and organizational policy differ.

## Native providers

`bf.native` inherits no large-transfer, integrity, deletion or restore capability by default.

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
- isolated connection/write/read/delete probe after future consent.

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
- isolated write/read/rename/delete probe after future consent.

## WebDAV
- HTTPS endpoint;
- credential ref;
- base path;
- certificate validation;
- provider profile;
- finalization strategy;
- connection/PROPFIND/write/read/MOVE/delete probes according capability after future consent.

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

Integrity/lifecycle/restore:
- size/checksum mismatch;
- corrupt/missing remote part;
- stale manifest;
- encrypted corruption;
- provider-native checksum interpretation;
- list/discovery;
- retention prune;
- delete/trash/versioning/retention lock;
- external deletion/lifecycle rule;
- complete/interrupted/encrypted restore;
- fresh disaster restore for C4;
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
