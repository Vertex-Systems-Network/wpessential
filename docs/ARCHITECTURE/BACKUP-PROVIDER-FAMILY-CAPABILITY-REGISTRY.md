# WPEssential — Backup Provider Family & Capability Registry

Status: **Phase 0 planning / static documentation evidence only / 0 providers certified**  
Related: ADR-0053, ADR-0056, `BACKUP-PROVIDER-CERTIFICATION-CONTRACT.md`, `BACKUP-REMOTE-COPY-LIFECYCLE.md`, `../MODULES/BACKUP-PROVIDER-CERTIFICATION-MATRIX.md`.

## 1. Purpose

This document fixes two planning problems before implementation:

1. earlier documents assigned the same numeric `PF-xx` identifiers to different protocol families;
2. a provider saying “S3 compatible”, “WebDAV”, or “Drive” is not enough to infer the exact resumability, checksum, finalization, delete, retention or restore behavior WPE requires.

Canonical provider identity is therefore:

`family_key + provider_key + provider_profile_version + adapter_version`

Example:

`bf.s3 + cloudflare-r2 + 2026-07-profile + wpe-s3-adapter-v1`

**Numeric `PF-xx` codes are legacy documentation aliases only. They MUST NOT be persisted, serialized, used as API identifiers or used to select an adapter.**

---

# 2. Stable family keys

| Family key | Meaning | Baseline large-transfer model |
|---|---|---|
| `bf.local-filesystem` | Local/private filesystem or certified mounted filesystem | streamed temp file + certified finalization |
| `bf.browser-export` | Authenticated browser/manual archive delivery | HTTP stream/download; not durable remote storage |
| `bf.ftp` | FTP legacy transfer | stream/restart only when exact server/client profile proves it |
| `bf.ftps` | FTP over TLS | stream/restart only when exact server/client profile proves it |
| `bf.sftp` | SFTP over SSH | offset resume/temp→rename only after library/server certification |
| `bf.webdav` | RFC 4918-style WebDAV baseline | PUT/temp→MOVE; no generic resumability claim |
| `bf.s3` | S3 API family | multipart create/parts/complete/abort when provider profile supports required subset |
| `bf.gcs` | Google Cloud Storage native | resumable session URI + chunk/status/final object |
| `bf.azure-blob` | Azure Blob native | staged blocks + block-list commit |
| `bf.google-drive` | Google Drive API | resumable session + final File resource |
| `bf.msgraph-drive` | Microsoft Graph Drive/DriveItem | upload session + final DriveItem |
| `bf.dropbox` | Dropbox files API | upload session append + finish/finish_batch |
| `bf.swift` | OpenStack Swift | prefer Static Large Object segments + explicit SLO manifest |
| `bf.native` | Provider-native API with no safe shared-family fit | provider-specific; no inherited capability |

Provider-specific extensions do not create a new family unless the adapter semantics are genuinely different. Example: Nextcloud remains `bf.webdav` with a Nextcloud capability profile that may certify its chunk-upload extension.

---

# 3. Legacy PF alias conflict

Earlier planning docs contain two incompatible numeric namespaces.

## Matrix-era aliases

| Legacy matrix label | Canonical key |
|---|---|
| PF-01 Filesystem/browser | `bf.local-filesystem` or `bf.browser-export` depending destination |
| PF-02 FTP/FTPS | `bf.ftp` / `bf.ftps` |
| PF-03 SFTP | `bf.sftp` |
| PF-04 WebDAV | `bf.webdav` |
| PF-05 S3-compatible | `bf.s3` |
| PF-06 GCS | `bf.gcs` |
| PF-07 Azure Blob | `bf.azure-blob` |
| PF-08 Google Drive | `bf.google-drive` |
| PF-09 Microsoft Graph Drives | `bf.msgraph-drive` |
| PF-10 Dropbox | `bf.dropbox` |
| PF-11 provider-native | `bf.native` |
| PF-12 Swift | `bf.swift` |

## Contract-era aliases

| Legacy contract label | Canonical key |
|---|---|
| PF-01 Local Filesystem | `bf.local-filesystem` |
| PF-02 S3-compatible | `bf.s3` |
| PF-03 GCS | `bf.gcs` |
| PF-04 Azure Blob | `bf.azure-blob` |
| PF-05 Google Drive | `bf.google-drive` |
| PF-06 Microsoft Graph Drives | `bf.msgraph-drive` |
| PF-07 Dropbox | `bf.dropbox` |
| PF-08 SFTP | `bf.sftp` |
| PF-09 WebDAV | `bf.webdav` |
| PF-10 provider-native | `bf.native` |

Any future migration/import of a planning-era identifier must know **which source namespace** produced it. Bare `PF-02` is ambiguous and must fail validation rather than guess FTP vs S3.

---

# 4. Capability value vocabulary

Provider profiles use explicit values rather than optimistic booleans where semantics matter:

- `yes` — static official documentation supports the capability concept; runtime certification still required;
- `provider_extension` — not in generic family baseline; provider-specific extension exists;
- `conditional` — depends on exact endpoint/version/configuration;
- `non_resumable` — current profile intentionally makes no crash-resume claim;
- `unknown` — not established by current official-doc research;
- `unsupported` — current official profile explicitly lacks it.

Static documentation evidence is **not C0–C4 certification**.

---

# 5. Static evidence maturity

These levels describe only paper research quality:

- **SE0 — Unreviewed/insufficient current official evidence**
- **SE1 — Official protocol/compatibility statement reviewed**
- **SE2 — Official upload/finalization/limits semantics reviewed**
- **SE3 — Upload + integrity/lifecycle/deviation semantics reviewed**

SE3 is still **not** C0. No credential, connection, transfer or restore has been executed.

---

# 6. Family baseline capabilities

| Family | Crash-resume baseline | Explicit provider commit point | Secondary provider integrity | Stable remote identity | Key limitation |
|---|---|---|---|---|---|
| `bf.local-filesystem` | conditional | conditional temp→rename | filesystem metadata only unless WPE read-back | path/inode semantics vary | durability/cross-filesystem rename/mount semantics runtime-only |
| `bf.browser-export` | no | HTTP response completion only | WPE hash | no durable remote identity | not a remote Backup destination |
| `bf.ftp` | conditional | no universal transactional commit | size/read-back | path | plaintext FTP discouraged; server variance |
| `bf.ftps` | conditional | no universal transactional commit | size/read-back | path | TLS/certificate + restart semantics vary |
| `bf.sftp` | conditional | certified rename only | size/read-back/WPE hash | path | host key + library/server semantics must be proven |
| `bf.webdav` | non_resumable baseline | MOVE only if certified | ETag/size/read-back only by profile | URL/path | RFC 4918 has no universal resumable upload session |
| `bf.s3` | yes when multipart subset exists | CompleteMultipartUpload | provider checksum by profile + WPE hash | bucket/key/version where available | “S3 compatible” feature subsets differ |
| `bf.gcs` | yes | completed resumable object | CRC32C/other documented checksum + WPE hash | bucket/object/generation | session URI is bearer-like secret |
| `bf.azure-blob` | yes via staged blocks | Commit/Put Block List | provider metadata/checksum + WPE hash | container/blob/version where configured | committed vs uncommitted blocks must be distinguished |
| `bf.google-drive` | yes | final File resource after resumable completion | size/hash metadata where applicable + WPE hash/read-back | file ID | session URI secret; user-space/quota semantics |
| `bf.msgraph-drive` | yes | final DriveItem / explicit commit where profile uses it | size/hash metadata where available + WPE hash/read-back | drive ID + item ID | uploadUrl is preauthenticated secret; tenant variants |
| `bf.dropbox` | yes | finish/finish_batch terminal result | Dropbox content_hash + WPE hash | path/id metadata | namespace write contention; async finish_batch possible |
| `bf.swift` | yes at segment level | SLO manifest publication | segment ETag/size + WPE hash/read-back | container/object paths | deploy-time middleware/config limits vary |
| `bf.native` | unknown | provider-specific | provider-specific | provider-specific | capability inheritance prohibited |

---

# 7. 34-target provider capability profiles

All rows remain **Planned / Not Certified**.

| # | Provider key | Destination | Family key | Static evidence | Large-transfer override | Critical override / limitation |
|---:|---|---|---|---|---|---|
| 1 | `local-server` | Local server | `bf.local-filesystem` | SE0 | streamed/chunked WPE write | same-filesystem atomic rename, fsync/durability, disk-full and restore path require runtime proof |
| 2 | `browser-export` | Browser/manual download | `bf.browser-export` | SE0 | HTTP streaming | delivery-only; MUST NOT count as durable remote copy/C3 destination |
| 3 | `ftp-generic` | FTP | `bf.ftp` | SE0 | conditional REST/restart | insecure without TLS; no resumable/finalization claim before client/server fixture |
| 4 | `ftps-generic` | FTPS | `bf.ftps` | SE0 | conditional REST/restart | TLS/cert validation + data-channel/firewall + resume semantics profile-specific |
| 5 | `sftp-generic` | SFTP | `bf.sftp` | SE0 | conditional offset resume | host-key verification mandatory; temp→rename/resume must be certified against chosen PHP/SSH library + server |
| 6 | `webdav-generic` | WebDAV | `bf.webdav` | SE1 | non_resumable baseline | PUT/read-back; MOVE finalization only if server proves it; RFC 4918 alone gives no large-upload session |
| 7 | `nextcloud` | Nextcloud | `bf.webdav` | SE3 | `provider_extension` chunk upload v2 | current docs: numeric chunks 1–10000, 5 MB–5 GB except last, assembled by MOVE, upload dir expires after 24h inactivity; exact server-version profile required |
| 8 | `owncloud` | ownCloud | `bf.webdav` | SE2 | `provider_extension` | Classic capabilities expose chunking/big-file features; current clients also have NG/TUS-related behavior; Classic vs Infinite Scale/version profile MUST be separated |
| 9 | `amazon-s3` | Amazon S3 | `bf.s3` | SE3 | multipart | reference profile: create/upload/list/complete/abort; full/composite checksums available; multipart ETag not whole-object MD5 |
| 10 | `s3-custom` | Generic S3-compatible | `bf.s3` | SE0 | conditional multipart | no AWS feature inherited without probe/profile; custom endpoint needs backup-specific trust/SSRF policy |
| 11 | `cloudflare-r2` | Cloudflare R2 | `bf.s3` | SE3 | multipart yes | region `auto`; 5 MiB–5 GiB parts, max 10k; non-final parts uniform; incomplete multipart auto-abort 7d default; S3 feature/checksum matrix differs from AWS; strong read/write/delete consistency documented |
| 12 | `backblaze-b2-s3` | Backblaze B2 S3 API | `bf.s3` | SE3 | multipart yes | SigV4 only; HTTPS; versioning/delete behavior differs; IAM roles/object tagging/website unsupported; profile must interpret versioned deletion truthfully |
| 13 | `wasabi` | Wasabi | `bf.s3` | SE3 | multipart yes | incomplete parts retained/billed up to documented window (31d in current guidance); Object Lock can prevent prune/delete; S3 variations remain profile-specific |
| 14 | `digitalocean-spaces` | DigitalOcean Spaces | `bf.s3` | SE2 | multipart yes | official matrix says partial S3 support; cross-region/cluster copy restrictions; CDN endpoint is not storage API identity |
| 15 | `minio` | MinIO / AIStor-compatible endpoint | `bf.s3` | SE1 | conditional multipart | self-hosted version/config/TLS variability; endpoint profile cannot inherit cloud-provider operational assumptions |
| 16 | `google-cloud-storage` | Google Cloud Storage | `bf.gcs` | SE3 | native resumable yes | resumable session URI acts as auth token; chunk multiples 256 KiB; final whole-object CRC32C can be supplied/validated; cancel/session behavior explicit |
| 17 | `google-drive` | Google Drive | `bf.google-drive` | SE3 | resumable yes | chunks multiple of 256 KiB; session URI expires after one week in current docs; status query via empty PUT; 200/201 final File; pre-generated IDs can reduce duplicate-create ambiguity |
| 18 | `dropbox` | Dropbox | `bf.dropbox` | SE3 | upload sessions yes | start/append_v2/finish; finish_batch may be async; content_hash is secondary integrity evidence; namespace lock/contention and batching matter |
| 19 | `onedrive-personal` | OneDrive Personal | `bf.msgraph-drive` | SE3 | upload session yes | upload ranges sequential; fragments multiples of 320 KiB; `nextExpectedRanges`; session expiry; uploadUrl preauthenticated/P3; final DriveItem is commit evidence |
| 20 | `onedrive-business-sharepoint` | OneDrive Business / SharePoint | `bf.msgraph-drive` | SE3 | upload session yes | same session mechanics plus tenant/site/drive/admin-consent and national-cloud/profile differences; certify separately from Personal |
| 21 | `azure-blob` | Azure Blob Storage | `bf.azure-blob` | SE3 | staged blocks yes | uncommitted vs committed block list is explicit; CommitBlockList defines blob composition; missing/uncommitted block reconciliation possible |
| 22 | `box` | Box | `bf.native` | SE0 | unknown pending current official-session review | do not claim chunk/resume until exact upload-session API limits/finalization and PHP client strategy are reviewed |
| 23 | `pcloud` | pCloud | `bf.native` | SE2 | `non_resumable` until stronger evidence | `uploadfile` exposes progress and partial-file handling; progress != crash-resume session; file/folder IDs preferable to path; checksum API exists separately |
| 24 | `mega` | MEGA | `bf.native` | SE0 | unknown | official API/SDK, server-side identity, license/maintenance/security model require review before adapter commitment |
| 25 | `openstack-swift` | OpenStack Swift | `bf.swift` | SE3 | segmented upload | prefer SLO over DLO for explicit manifest segment list/verification; deployment defaults/limits vary; SLO manifest is provider commit artifact, not WPE Backup manifest |
| 26 | `rackspace-swift` | Rackspace / Swift-compatible | `bf.swift` | SE0 | conditional segmented | provider viability/current API/auth/profile must be refreshed; no automatic inheritance from OpenStack reference |
| 27 | `oracle-object-storage-s3` | Oracle Object Storage S3 API | `bf.s3` | SE2 | multipart yes | official S3 compatibility supports initiate/upload/list/complete/abort; bucket/object feature subset remains narrower than AWS |
| 28 | `akamai-linode-object-storage` | Akamai/Linode Object Storage | `bf.s3` | SE1 | conditional multipart | official service describes S3 compatibility; exact current multipart/checksum/version/delete matrix still requires provider-profile refresh |
| 29 | `vultr-object-storage` | Vultr Object Storage | `bf.s3` | SE2 | multipart yes | current compatibility matrix explicitly lists multipart, lifecycle and object versions; unsupported AWS features must remain disabled |
| 30 | `scaleway-object-storage` | Scaleway Object Storage | `bf.s3` | SE3 | multipart yes | current docs: **1–1000 parts**, 5 MB–5 GB/part except last, object up to 5 TB; MUST NOT inherit AWS 10,000-part assumption |
| 31 | `hetzner-object-storage` | Hetzner Object Storage | `bf.s3` | SE0 | unknown pending current official matrix | provider profile must be researched before multipart/checksum/versioning claims |
| 32 | `storj-s3-gateway` | Storj S3-compatible | `bf.s3` | SE2 | multipart yes | compatibility table exposes per-operation support; hosted vs self-hosted gateway are distinct profiles; lifecycle/other S3 APIs differ from AWS |
| 33 | `idrive-e2` | IDrive e2 | `bf.s3` | SE3 | multipart yes | current official API documents create/upload/list/complete/abort and part checksums; exact checksum/full-object/version/delete semantics still need fixture |
| 34 | `bunny-storage` | Bunny Storage | `bf.native` | SE0 | unknown | native Storage API; no S3/resumable assumption; exact upload/replace/delete/download/finalization semantics require dedicated current API review |

---

# 8. High-impact provider overrides

## 8.1 S3 family is capability-negotiated, not feature-inherited

A provider profile MUST explicitly declare at least:
- `CreateMultipartUpload` equivalent;
- upload/list parts;
- Complete/Abort;
- HEAD/metadata;
- GET/range GET;
- delete/delete-version behavior;
- provider checksum support and algorithm/type;
- maximum part count;
- minimum/maximum part size;
- incomplete-upload cleanup/lifecycle behavior;
- versioning/object lock/lifecycle interactions;
- endpoint/region/addressing rules;
- signature/auth profile;
- consistency behavior relevant to verification.

Examples already proving why inheritance is unsafe:
- R2 has its own checksum feature matrix and uniform non-final part rule;
- Backblaze has SigV4-only and versioned deletion/provider feature differences;
- Scaleway documents 1000 max multipart parts instead of Amazon's 10,000 model;
- Storj publishes an operation-by-operation compatibility table;
- DigitalOcean explicitly calls its support partial.

## 8.2 Nextcloud/ownCloud are WebDAV profiles, not generic WebDAV guarantees

Generic `bf.webdav` remains non-resumable by default.

Nextcloud chunk upload is a provider extension and needs a versioned profile. Current static docs establish chunk upload v2 semantics and inactivity expiry, but C2 requires interruption/process-restart/final-MOVE/read-back fixtures.

ownCloud has multiple product generations and chunking mechanisms. A single `ownCloud` capability flag is too broad; provider profile must identify Classic/Infinite Scale/server version and detected capability document.

## 8.3 Graph Drive profiles are split

OneDrive Personal and OneDrive Business/SharePoint share `bf.msgraph-drive` mechanics but are distinct provider profiles because:
- permissions/scopes differ;
- tenant/site/drive resolution differs;
- admin-consent and organizational policy can differ;
- national-cloud availability/URLs may differ;
- retention/recycle/version behavior may differ.

## 8.4 Swift prefers SLO for WPE large objects

Static Large Objects are the preferred planning baseline because the provider-side manifest explicitly names ordered segments and can validate optional segment size/ETag. DLO depends on prefix/listing semantics and is a weaker default for a deterministic Backup provider adapter.

This is only a paper preference until runtime/restore evidence exists.

## 8.5 pCloud remains non-resumable in WPE claims

Current official docs expose upload progress and partial-file controls. Those are not equivalent to a durable provider upload session that a new PHP process can query and resume from a confirmed offset. Therefore WPE must show `Integrity Certified / Non-resumable` at C2 if future evidence proves integrity but not crash-resume.

---

# 9. Provider profile minimum schema

Conceptual profile fields:

- `family_key`;
- `provider_key`;
- `profile_version`;
- `adapter_min/max_version`;
- provider API/version/date reviewed;
- endpoint/region model;
- auth types/scopes;
- stable remote identity shape;
- simple upload limits;
- multipart/resumable mode;
- part/chunk min/max/count;
- session/upload-ID secrecy and expiry;
- status/list-parts/range query;
- abort/cancel;
- explicit commit point;
- ambiguous-commit reconciliation method;
- provider checksum algorithms/types;
- WPE read-back policy;
- list/head/get/range/delete support;
- versioning/trash/object-lock/retention semantics;
- consistency behavior;
- rate/quota signals;
- known unsupported APIs;
- SDK/library requirements;
- current static evidence level SE0–SE3;
- certification state C0–C4;
- last certified provider/API version;
- downgrade/expiry policy when provider behavior changes.

Unknown values remain `unknown`; they are not defaulted from the family when doing so would overstate capability.

---

# 10. Support-claim rule

A destination row can be:
- Target / Not Certified;
- Experimental/Connected after C0;
- Upload Certified after C1;
- Resumable & Integrity Certified (or explicit Non-resumable Integrity) after C2;
- Supported Backup Destination after C3;
- Disaster Restore Certified after C4.

**SE0–SE3 never changes the public certification label. Current certified count remains 0.**

---

# 11. Current official-document research snapshot

Reviewed 2026-08-27. These are static research inputs, not provider tests.

- Amazon S3 multipart/checksum: `https://docs.aws.amazon.com/AmazonS3/latest/userguide/mpuoverview.html` and `checking-object-integrity-upload.html`
- Cloudflare R2 S3 compatibility/multipart/limits: `https://developers.cloudflare.com/r2/api/s3/api/`, `/r2/objects/upload-objects/`, `/r2/platform/limits/`
- Backblaze B2 S3 API: `https://www.backblaze.com/apidocs/introduction-to-the-s3-compatible-api`
- Wasabi S3/multipart/Object Lock: `https://docs.wasabi.com/apidocs/wasabi-api`, `https://docs.wasabi.com/docs/how-does-wasabi-handle-multipart-uploads`, object-lock API docs
- DigitalOcean Spaces S3 compatibility: `https://docs.digitalocean.com/products/spaces/reference/s3-compatibility/`
- Google Cloud Storage resumable/checksum: `https://docs.cloud.google.com/storage/docs/resumable-uploads`, `/storage/docs/data-validation`
- Google Drive resumable upload: `https://developers.google.com/workspace/drive/api/guides/manage-uploads`
- Microsoft Graph Drive upload session: `https://learn.microsoft.com/en-us/graph/api/driveitem-createuploadsession?view=graph-rest-1.0`
- Dropbox upload sessions/content hash: `https://developers.dropbox.com/dbx-performance-guide`, `https://developers.dropbox.com/dbx-file-access-guide`
- Azure block blobs: Microsoft `Get Block List` / StageBlock / CommitBlockList documentation
- Nextcloud chunk v2: `https://docs.nextcloud.com/server/stable/developer_manual/client_apis/WebDAV/chunking.html`
- ownCloud WebDAV/capabilities/chunking: current `doc.owncloud.com` server/desktop docs
- OpenStack Swift large objects: `https://docs.openstack.org/swift/latest/overview_large_objects`
- pCloud upload/progress/checksum/file-ID APIs: `https://docs.pcloud.com/`
- Oracle Object Storage S3 compatibility: current Oracle Object Storage S3 Compatibility API support documentation
- Vultr S3 compatibility matrix: `https://docs.vultr.com/products/storage/object-storage/s3-compatibility-matrix`
- Scaleway multipart: `https://www.scaleway.com/en/docs/object-storage/api-cli/multipart-uploads/`
- Storj S3 compatibility: `https://storj.dev/dcs/api/s3/s3-compatibility`
- IDrive e2 S3 API: `https://learn.idrive.com/s3-storage-e2/s3-compatible-api`

Missing/currently insufficient official research remains explicit for Box, MEGA, Hetzner, Bunny and exact Akamai/Linode feature matrices. Those rows stay SE0/SE1 rather than being guessed.

---

# 12. Future evidence — NOT AUTHORIZED

After explicit owner consent, every profile must execute the ADR-0053/P-013 fixtures for:
- auth/least privilege/rotation;
- small/large upload;
- interrupted process and resume/restart;
- part/session expiry;
- explicit commit and commit-unknown reconciliation;
- checksum/read-back corruption detection;
- list/discovery;
- range/full download;
- retention/version/object-lock/trash/delete;
- external lifecycle/deletion;
- restore and interrupted restore;
- encrypted restore;
- C4 fresh/disaster restore;
- provider/API-version regression.

No provider credential, connection, upload, delete, API probe or restore has been executed while producing this registry.
