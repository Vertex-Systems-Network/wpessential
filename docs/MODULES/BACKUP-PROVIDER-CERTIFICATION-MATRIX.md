# WPEssential — Backup Provider Certification Matrix

Status: **Phase 0 planning — no storage/provider implementation authorized**

## Goal
Support 25+ destinations without maintaining 25 unrelated upload engines. WPEssential uses protocol-family adapters plus provider profiles, then certifies each named provider independently.

“Listed” means target candidate. “Certified” will mean automated upload/download/integrity/failure/restore-oriented acceptance has passed for a documented provider/API version.

---

# Protocol families

## A. Local / browser delivery
- Local server filesystem
- browser/manual download

## B. FTP family
- FTP
- FTPS
- SFTP

SFTP is treated as its own SSH file-transfer adapter, not “FTP over SSL.”

## C. WebDAV family
- generic WebDAV
- Nextcloud
- ownCloud

Provider profiles may add path/auth/locking quirks but reuse WebDAV primitives where official provider behavior supports it.

## D. S3 / S3-compatible object storage
Core candidate engine:
- multipart upload;
- abort/retry/resume by persisted upload ID/parts;
- object metadata/checksum verification where provider semantics support it;
- endpoint/region/path-style/virtual-host configuration;
- TLS required;
- provider-specific capability profile instead of assuming every S3 feature exists.

AWS S3 multipart is the reference semantic model. S3-compatible providers can omit features; certification checks the subset WPE actually depends on.

## E. Native OAuth/file-drive APIs
- Google Drive
- Dropbox
- Microsoft OneDrive / OneDrive Business / SharePoint
- Box
- pCloud
- MEGA candidate

Each provider gets a connection adapter + upload-session strategy. Do not hide provider session expiry/quotas/rate limits behind one fake generic API.

## F. Native cloud object APIs where useful
- Google Cloud Storage
- Azure Blob Storage
- OpenStack Swift

A provider may offer both native and S3-compatible access. Prefer the smallest maintained adapter that satisfies Backup acceptance criteria.

---

# Current representative research

## AWS S3
AWS supports multipart upload, independent part retries, completion/abort and checksums. WPE's S3 adapter should model large backup upload around multipart rather than one monolithic PUT.

Official reference: https://docs.aws.amazon.com/AmazonS3/latest/userguide/mpuoverview.html

## Cloudflare R2
R2 exposes an S3-compatible API but documents operation/feature compatibility individually and does not implement every AWS S3 feature. Therefore `S3-compatible` never means WPE may assume full AWS behavior.

Official reference: https://developers.cloudflare.com/r2/api/s3/api/

## Backblaze B2
Backblaze offers a documented S3-compatible API, supports common S3 operations including multipart calls, uses SigV4, and documents meaningful differences from AWS S3.

Official reference: https://www.backblaze.com/apidocs/introduction-to-the-s3-compatible-api

## Google Drive
Drive API supports resumable upload sessions intended for large files/network interruptions. Session URI and byte progress can be queried/resumed; chunk semantics have provider-specific requirements.

Official reference: https://developers.google.com/workspace/drive/api/guides/manage-uploads

## Microsoft OneDrive / SharePoint
Microsoft Graph upload sessions support sliced/resumable large-file transfer and expose session expiry/next expected ranges.

Official references:
- https://learn.microsoft.com/graph/api/driveitem-createuploadsession
- https://learn.microsoft.com/graph/api/resources/uploadsession

## Dropbox
Dropbox official SDK/API exposes upload-session start/append/finish semantics and concurrent upload-session options in current SDK docs. Provider-specific chunk/session rules must be respected.

Reference: https://dropbox.github.io/dropbox-sdk-js/Dropbox.html

## pCloud
pCloud exposes authenticated file upload with progress tracking and partial-file behavior controls. Current planning does not assume true resumable multipart parity with S3/Drive; interruption acceptance must be tested before certification.

Official reference: https://docs.pcloud.com/methods/file/uploadfile.html

---

# Provider target matrix

Legend:
- **P0** first adapter proofs / highest priority
- **P1** high-value expansion after protocol proof
- **P2** later provider/profile expansion
- **Review** include only after API/library/legal/maintenance evidence

| # | Destination | Primary adapter family | Large upload strategy candidate | Auth/secret class | Priority | Certification note |
|---|---|---|---|---|---|---|
| 1 | Local server | Filesystem | chunked file write/rename | filesystem policy | P0 | atomic-finalization/free-space/permissions tests |
| 2 | Browser/manual download | HTTP/local temp | generated archive stream/download | WP auth/nonces | P0 | size/time/shared-host limits; not durable remote storage |
| 3 | FTP | FTP | streamed upload; resume only if proven | P3 credential | P2 | plaintext control/data risk unless TLS; discourage when FTPS/SFTP available |
| 4 | FTPS | FTP/TLS | streamed/restart/resume where server supports | P3 | P2 | explicit certificate/TLS validation |
| 5 | SFTP | SSH/SFTP | chunked stream + remote offset resume if library/server proven | P3 key/password | P1 | host-key verification required; never auto-trust changed host key |
| 6 | WebDAV | WebDAV | PUT/temp-file strategy; chunk extensions only if provider-certified | P3 | P1 | generic WebDAV feature variance; use safe finalization pattern |
| 7 | Nextcloud | WebDAV/provider profile | WebDAV/provider chunk capability only after tests | P3/OAuth where used | P1 | test locks/path/quota/large uploads/version compatibility |
| 8 | ownCloud | WebDAV/provider profile | same principle | P3 | P2 | certify separately from Nextcloud |
| 9 | Amazon S3 | S3 | multipart | P3 access key/role strategy | P0 | reference S3 provider; checksum/multipart/abort/list/download |
| 10 | Generic S3-compatible | S3 profile | multipart if endpoint supports required subset | P3 | P0/P1 | user-configured endpoint requires SSRF/private-endpoint policy distinction |
| 11 | Cloudflare R2 | S3 profile | multipart if certified supported ops | P3 API token keys | P1 | feature subset must follow R2 compatibility table |
| 12 | Backblaze B2 | S3 profile | multipart | P3 app key | P1 | SigV4; provider differences/versions considered |
| 13 | Wasabi | S3 profile | multipart candidate | P3 | P1 | official S3 compatibility must be refreshed before certification |
| 14 | DigitalOcean Spaces | S3 profile | multipart candidate | P3 | P1 | endpoint/region/CDN distinctions |
| 15 | MinIO | S3 profile | multipart candidate | P3 | P1 | self-hosted endpoint/TLS/SSRF/network policy; version variability |
| 16 | Google Cloud Storage | native GCS or S3 interoperability after decision | resumable/native candidate | P3 OAuth/service account | P1 | native vs S3 path chosen on maintenance/feature evidence |
| 17 | Google Drive | Google Drive API | resumable session | P3 OAuth refresh token | P0 | session expiry/resume/quota/rate tests |
| 18 | Dropbox | Dropbox API | upload session | P3 OAuth | P0/P1 | session/chunk/rate-limit tests |
| 19 | OneDrive Personal | Microsoft Graph | upload session | P3 OAuth | P0/P1 | token refresh/session expiry/conflict tests |
| 20 | OneDrive Business / SharePoint | Microsoft Graph | upload session | P3 OAuth | P1 | tenant/site/drive permissions and admin-consent scenarios |
| 21 | Azure Blob Storage | Azure native | block blob staged blocks candidate | P3 SAS/key/OAuth | P1 | exact auth/upload strategy requires dedicated official-doc research before adapter implementation |
| 22 | Box | Box API | chunked upload session candidate | P3 OAuth/JWT profile | P2 | exact current session limits/API to certify before claim |
| 23 | pCloud | pCloud API | standard upload/progress; resume unproven | P3 OAuth/token | P2 | do not market resumable until interrupted-upload test proves it |
| 24 | MEGA | provider/API/library candidate | unknown until maintained API strategy accepted | P3 | Review | licensing/maintenance/security and official API availability review mandatory |
| 25 | OpenStack Swift | Swift native | segmented object upload candidate | P3 | P2 | authentication variants/large-object semantics certification |
| 26 | Rackspace/Swift-compatible | Swift profile | segmented candidate | P3 | P2 | legacy/provider viability review before launch claim |
| 27 | Oracle Object Storage | S3 profile/native | multipart candidate | P3 | P2 | prefer shared S3 path if certified |
| 28 | Akamai/Linode Object Storage | S3 profile | multipart candidate | P3 | P2 | provider endpoint/profile certification |
| 29 | Vultr Object Storage | S3 profile | multipart candidate | P3 | P2 | provider endpoint/profile certification |
| 30 | Scaleway Object Storage | S3 profile | multipart candidate | P3 | P2 | provider endpoint/profile certification |
| 31 | Hetzner Object Storage | S3 profile | multipart candidate | P3 | P2 | current product/API availability refreshed before certification |
| 32 | Storj S3-compatible | S3 profile | multipart candidate | P3 | P2 | S3 gateway semantics/performance certification |
| 33 | IDrive e2 | S3 profile | multipart candidate | P3 | P2 | provider profile certification |
| 34 | Bunny Storage | Bunny native/API | chunk/standard provider-specific | P3 API key | P2 | not S3 by assumption; dedicated official API acceptance needed |

The target catalog now exceeds the original 25-provider minimum, but implementation order remains protocol-first.

---

# Connection options by family

## S3 profile options
Candidate UI fields:
- provider preset / Custom S3;
- endpoint URL;
- region;
- bucket;
- base path/prefix;
- access key reference;
- secret key reference;
- session/temporary credential strategy where supported;
- path-style vs virtual-host style where required;
- TLS verification (cannot be disabled in ordinary production mode);
- storage class where provider-certified;
- server-side encryption mode where provider-certified;
- multipart threshold;
- part size (advanced/validated);
- upload concurrency bounded by host resources;
- request timeout;
- retry policy;
- retention/delete behavior;
- object naming template;
- test connection/write/delete capability.

Do not expose AWS-only options on providers that ignore/reject them without provider-profile filtering.

## OAuth Drive options
- account connection;
- folder/root selection;
- base folder creation strategy;
- filename collision behavior;
- resumable/chunk settings only when provider supports it;
- token health/refresh status;
- connection test;
- quota/permission diagnostics;
- disconnect without deleting backups;
- credential rotation/re-auth.

## SFTP options
- host;
- port;
- username;
- password OR private-key secret reference;
- private-key passphrase ref;
- host-key fingerprint/trust policy;
- remote base directory;
- timeout;
- keepalive;
- resume support only if certified;
- connection/write/rename/delete tests.

## WebDAV options
- base HTTPS URL;
- username/password or provider auth ref;
- base path;
- timeout;
- TLS validation;
- temp upload/finalization strategy;
- connection/PROPFIND/write/read/delete tests.

---

# Backup artifact upload state

Destination transfer record should normalize:
- transfer ID;
- backup ID;
- destination ID;
- remote object/path ID;
- state `queued|uploading|paused|retry_wait|completed|verification_failed|failed|cancelled`;
- bytes total/uploaded;
- provider upload/session ID encrypted or protected as necessary;
- part/chunk state;
- attempt count;
- next retry;
- provider-safe error class;
- remote checksum/etag metadata with provider semantic annotation;
- completed-at;
- verify state.

Never assume every provider's `ETag` equals an MD5 checksum.

---

# Upload integrity verification

Certification must distinguish:

## Local source archive integrity
WPE manifest contains cryptographic checksum(s) of generated archive/chunks.

## Transport completion
Provider API reports upload complete.

## Remote verification
At least one of:
- provider-supported checksum compared to local using known semantics;
- download/range-read and verify selected/complete content according to acceptance tier;
- object size + metadata + stronger provider checksum where available.

“Upload API returned 200” alone is not enough for a verified backup status.

---

# Provider certification levels

## B0 — Connection
Authenticate/list/test target only. Not marketed as backup-supported.

## B1 — Basic Backup
Upload full archive + list + download + delete + local/remote size/integrity evidence.

## B2 — Resilient Large Backup
B1 plus interrupted upload recovery/session resume or safe restart, rate-limit/retry, large fixture and low-memory behavior.

## B3 — Restore Certified
B2 plus automated retrieval feeding verified restore fixture and failure-recovery scenarios.

A provider appears in “Supported Destinations” marketing only at the minimum product certification level defined for launch; current recommendation is at least **B2**, with representative providers reaching **B3** before Backup Manager is marketed as production-ready.

---

# Acceptance tests per provider

- bad/missing credentials;
- read-only credentials;
- wrong bucket/folder/path;
- quota/full storage;
- DNS/network interruption;
- TLS/certificate problem where applicable;
- timeout;
- HTTP/API 429/rate limit;
- provider 5xx;
- interrupted upload after multiple chunks;
- expired resumable session;
- duplicate filename/conflict mode;
- Unicode/long names;
- archive larger than single-request threshold;
- low disk/memory local host;
- checksum mismatch;
- remote object deleted externally;
- retention prune permission denied;
- credential rotation/re-auth;
- download/restore retrieval;
- cancel upload and cleanup orphan multipart/session where provider supports it.

S3 profiles additionally test unsupported AWS features are not sent blindly.

---

# Security boundaries

1. Credentials live in Secrets Vault; UI receives masked/reference state only.
2. OAuth callbacks require state/nonce and exact redirect validation.
3. Custom endpoints (S3/WebDAV/SFTP) need explicit SSRF/network policy. Private LAN destinations may be a legitimate backup use case, so policy must distinguish intentional private endpoints from user-controlled outbound webhook SSRF risk.
4. TLS certificate verification is mandatory by default.
5. SFTP host-key verification is a trust boundary.
6. Provider error responses are sanitized before user display/logging.
7. Backup archive encryption is separate from provider transport/server-side encryption.
8. Disconnecting a provider does not silently delete remote backups.
9. Remote deletion/pruning is an explicit capability and audited.
10. Provider SDK dependency choice is reviewed for license, maintenance, bundle size and CVEs.

---

# Provider implementation strategy

Do not implement 34 destinations independently.

Recommended proof order after future development consent:
1. Local filesystem;
2. AWS S3 reference adapter;
3. one non-AWS S3 provider (Cloudflare R2 or Backblaze B2) to prove compatibility-profile boundaries;
4. SFTP;
5. WebDAV/Nextcloud;
6. Google Drive resumable OAuth;
7. Microsoft Graph upload session;
8. Dropbox upload session;
9. expand S3 provider profiles;
10. add remaining native providers by demand/support evidence.

This order is a planning recommendation, not development authorization.

---

# Development gate

No provider SDK, credential flow, connection test, upload, download or restore code is authorized before explicit owner development consent under ADR-0014.