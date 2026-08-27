# WPEssential — Backup Provider Static Evidence Refresh — 2026-08-27

Status: **Phase 0 static research only / no provider execution / 0 providers certified**  
Related: ADR-0053, ADR-0061, `BACKUP-PROVIDER-FAMILY-CAPABILITY-REGISTRY.md`, `../MODULES/BACKUP-PROVIDER-CERTIFICATION-MATRIX.md`.

## 1. Purpose

Refresh selected low-evidence provider profiles using current official documentation without performing credentials, network probes, uploads, deletes, restores, SDK installation or certification fixtures.

This file is a **versioned static-evidence overlay**. For the provider keys listed here, its SE maturity and static capability observations supersede the older row in `BACKUP-PROVIDER-FAMILY-CAPABILITY-REGISTRY.md` until the next consolidated registry revision.

It does **not** change C0–C4 certification. Current certified count remains **0**.

## 2. Evidence-level rule

Static evidence levels remain:
- SE0 — insufficient current official evidence;
- SE1 — official protocol/compatibility statement reviewed;
- SE2 — official upload/finalization/limits semantics reviewed;
- SE3 — upload plus integrity/lifecycle/deviation semantics reviewed.

SE3 is not C0. No provider connection has been made.

## 3. Refresh summary

| Provider key | Prior SE | New SE | Large-transfer/static profile | Key newly documented constraint |
|---|---:|---:|---|---|
| `box` | SE0 | **SE3** | native chunked upload sessions | >=20 MB chunk API; session 7 days; immutable uploaded parts; explicit commit with digest; commit may return 202 + Retry-After |
| `minio` | SE1 | **SE3** | S3 multipart supported | explicit Create/Upload/List/Complete/Abort; lifecycle `AbortIncompleteMultipartUpload` unsupported in standard AIStor profile; S3 Express mode differs |
| `rackspace-swift` | SE0 | **SE2** | segmented large objects | >5 GB must be segmented; manifest object composes segments; CDN >10 GB limitation is distinct from storage object semantics |
| `akamai-linode-object-storage` | SE1 | **SE2** | S3 upload/multipart candidate with compatibility constraints | AWS CLI/SDK releases on/after 2025-01-15 can fail PutObject/UploadPart due unsupported newer integrity defaults; byte-range GET preferred over multipart-download partNumber |
| `hetzner-object-storage` | SE0 | **SE3** | S3 multipart | 5 GB single PUT; 5 GB/part; 10,000 parts; 5 TB object; versioning/Object Lock/lifecycle documented; incomplete multipart cleanup via lifecycle |
| `bunny-storage` | SE0 | **SE2** | native Storage API; currently **non-resumable claim** | HTTP API/FTP/SFTP upload supported; official PHP storage client exposes upload/async + optional checksum; no official crash-resumable Storage upload session established in this review |
| `mega` | SE0 | **SE1** | provider-native SDK; transfer capability exists but WPE adapter model unresolved | official MEGA C++ SDK/public API is maintained and user-controlled encryption is intrinsic; no PHP-native provider adapter/finalization profile selected |

## 4. Box — `box`

Canonical family remains: `bf.native`.

### Static evidence
Current Box Developer documentation defines a Chunked Upload API for large files:
1. create upload session;
2. upload parts;
3. commit session.

Documented properties relevant to WPE:
- intended for files >=20 MB;
- upload session has an explicit expiration, documented as 7 days in current guide;
- session returns part size/count and explicit endpoints for upload, commit, abort, list parts and status;
- uploaded parts are immutable within a session;
- each part is sent with digest and Content-Range metadata;
- final commit includes the uploaded part list plus full-file digest;
- commit may return `201 Created` or `202 Accepted` with `Retry-After`, so commit acceptance can be asynchronous.

### WPE static profile
- crash-resume candidate: **yes**, subject to session persistence/re-query evidence;
- explicit provider commit: **yes**;
- abort: **yes**;
- list/status: **yes**;
- provider integrity: **SHA-1 digest semantics documented**, but WPE hash remains authoritative;
- stable file identity: Box File ID after commit;
- ambiguous commit must honor 202/Retry-After and query/retry semantics rather than treat first response as failure.

### Still unverified
- actual OAuth scopes/token refresh;
- crash after part upload;
- expired-session behavior;
- parallel chunk edge cases;
- duplicate commit outcome;
- delete/trash/versioning/retention interactions;
- download/read-back/restore;
- PHP dependency strategy.

Static maturity: **SE3**. Runtime certification: **none**.

Official references:
- https://developer.box.com/guides/uploads/chunked/
- https://developer.box.com/guides/uploads/chunked/create-session/
- https://developer.box.com/guides/uploads/chunked/upload-part/
- https://developer.box.com/guides/uploads/chunked/commit-session/

## 5. MinIO / AIStor — `minio`

Canonical family remains: `bf.s3`.

### Static evidence
Current MinIO AIStor compatibility documentation explicitly lists standard S3 multipart operations:
- CreateMultipartUpload;
- UploadPart / UploadPartCopy;
- ListMultipartUploads;
- ListParts;
- CompleteMultipartUpload;
- AbortMultipartUpload.

It also documents a notable deviation: the S3 lifecycle action `AbortIncompleteMultipartUpload` is not supported in the standard compatibility profile shown by current docs.

AIStor can also run S3 Express mode, whose supported feature surface differs from standard S3 mode. Therefore a WPE profile must record server mode/version rather than treating `MinIO` as one immutable capability set.

### WPE static profile
- multipart: **yes** in documented standard profile;
- explicit provider commit: CompleteMultipartUpload;
- explicit abort: yes;
- list parts/uploads: yes;
- object versioning/lock APIs are documented in standard mode;
- automatic incomplete-multipart lifecycle cleanup: **not inherited** from AWS baseline;
- S3 Express mode: separate provider-profile variant required if supported later.

### Still unverified
- exact deployed server version/mode;
- TLS/cert profile;
- checksum algorithms and SDK interoperability selected by WPE;
- cluster durability/consistency behavior relevant to verification;
- upgrade/profile migration;
- restore/load evidence.

Static maturity: **SE3**. Runtime certification: **none**.

Official references:
- https://docs.min.io/aistor/developers/s3-api-compatibility/
- https://docs.min.io/aistor/reference/aistor-server/http-endpoints/
- https://docs.min.io/aistor/reference/aistor-server/s3-express/

## 6. Rackspace Cloud Files — `rackspace-swift`

Canonical family remains: `bf.swift`.

### Static evidence
Current Rackspace Cloud Files documentation remains available and describes Cloud Files as OpenStack-powered storage.

For large files it documents segmented upload plus a manifest object:
- files larger than 5 GB must be segmented;
- segments are uploaded first;
- a manifest maps them into one logical large object;
- documentation recommends relatively large segment sizes rather than tiny fragments;
- CDN cannot serve files above its separate documented threshold, which is a delivery/CDN limitation rather than the same thing as storage-side segmented-object creation.

### WPE static profile
- segmented large object: **yes**;
- manifest-based commit concept: **yes**;
- family: Swift-compatible, but exact DLO/SLO/API/auth profile must be refreshed before implementation;
- CDN endpoint must never be used as canonical Backup storage API identity.

### Still unverified
- current authentication API/version profile;
- SLO vs DLO recommendation/current provider support;
- segment checksum semantics;
- deletion/manifest orphan behavior;
- service longevity/regions/account policy;
- restore/read-back fixtures.

Static maturity: **SE2**. Runtime certification: **none**.

Official references:
- https://docs.rackspace.com/docs/cloud-files
- https://docs.rackspace.com/cm/docs/cloud-files-uploading-large-files

## 7. Akamai / Linode Object Storage — `akamai-linode-object-storage`

Canonical family remains: `bf.s3`.

### Static evidence
Akamai Cloud Object Storage documents S3-compatible clients and AWS SDK use, including AWS SDK for PHP.

A current compatibility advisory is material to WPE:
- AWS CLI/SDK releases on or after 2025-01-15 may enable newer S3 data-integrity protections by default;
- Akamai documents PutObject and UploadPart failures such as SignatureDoesNotMatch/MissingContentLength/NotImplemented in affected versions;
- Akamai currently recommends older SDK/CLI versions or a request-checksum configuration workaround;
- multipart **downloads** through `partNumber` are not supported as AWS S3 would expose them; byte-range requests should be used instead.

### WPE static profile
- S3 API family: yes;
- UploadPart existence/support path: documented, but modern SDK checksum compatibility has a provider-specific deviation;
- range GET: preferred for ranged restore/download profile;
- AWS SDK version cannot be blindly upgraded without provider certification;
- WPE must not pin a stale vulnerable SDK merely to satisfy this provider; capability negotiation/adapter strategy must resolve the conflict safely.

### Still unverified
- exact multipart create/list/complete/abort matrix;
- provider checksum support under WPE-selected SDK;
- current versioning/lifecycle/delete semantics;
- endpoint/region behavior;
- long-running upload/restore.

Static maturity: **SE2**. Runtime certification: **none**.

Official references:
- https://techdocs.akamai.com/cloud-computing/docs/clients-and-tooling
- https://techdocs.akamai.com/cloud-computing/docs/aws-cli-sdks-support-details
- https://techdocs.akamai.com/cloud-computing/docs/using-the-aws-sdk-for-php-with-object-storage

## 8. Hetzner Object Storage — `hetzner-object-storage`

Canonical family remains: `bf.s3`.

### Static evidence
Current Hetzner Object Storage documentation defines an S3-compatible API and publishes explicit limits:
- single PUT: up to 5 GB;
- multipart part: up to 5 GB;
- multipart upload: up to 10,000 parts;
- object: up to 5 TB;
- parallel/request/storage limits are separately documented.

Current docs also expose:
- list/abort multipart operations;
- lifecycle cleanup of incomplete multipart uploads;
- object versioning;
- Object Lock legal hold/retention;
- lifecycle policies;
- SSE-C support with documented S3 feature exceptions.

### WPE static profile
- multipart: **yes**;
- max parts: **10,000**;
- max part: **5 GB**;
- max object: **5 TB**;
- incomplete multipart lifecycle cleanup: yes;
- versioning/delete-marker behavior must be represented truthfully;
- Object Lock can block WPE retention prune/delete;
- unsupported S3 APIs remain false rather than inherited from AWS.

### Still unverified
- selected SDK/version behavior;
- provider checksums used by WPE;
- exact endpoint/addressing behavior in all locations;
- commit-unknown reconciliation;
- version/delete/lock restore tests;
- large encrypted restore.

Static maturity: **SE3**. Runtime certification: **none**.

Official references:
- https://docs.hetzner.com/storage/object-storage/overview/
- https://docs.hetzner.com/storage/object-storage/supported-actions/
- https://docs.hetzner.com/storage/object-storage/faq/general/
- https://docs.hetzner.com/storage/object-storage/faq/buckets-objects/

## 9. Bunny Storage — `bunny-storage`

Canonical family remains: `bf.native`.

### Static evidence
Current Bunny documentation/support material shows Storage Zone upload through:
- HTTP Storage API;
- FTP;
- SFTP;
- web file manager.

The official `bunnycdn/storage` PHP package exposes Storage API upload and asynchronous upload helpers, with optional checksum behavior.

Current Storage documentation reviewed here does **not** establish a durable provider-native resumable upload session equivalent to S3 multipart/Drive sessions for ordinary Bunny Storage objects. Bunny Stream TUS/video upload capabilities are a different product surface and must not be inherited by Bunny Storage Backup.

### WPE static profile
- native HTTP upload: yes;
- official PHP client exists;
- async client call: yes, but async does **not** imply crash-resume;
- checksum option exists in official PHP client;
- current WPE large-transfer claim: **non-resumable until stronger Storage-specific evidence**;
- FTP/SFTP are alternative transport families and should not silently change the `bunny-storage` native API profile.

### Still unverified
- exact HTTP Storage API size limits;
- overwrite/finalization atomicity;
- checksum semantics;
- Range/download behavior;
- delete/list consistency;
- replication delay and restore implications;
- native API crash-resume mechanism, if any.

Static maturity: **SE2**. Runtime certification: **none**.

Official/provider references:
- https://support.bunny.net/hc/en-us/articles/115003780169-How-to-upload-files-to-your-Bunny-Storage-zone
- https://bunny.net/storage/
- official PHP package: `bunnycdn/storage` on Packagist/source repository

## 10. MEGA — `mega`

Canonical family remains: `bf.native`.

### Static evidence
MEGA maintains an official open-source C++ SDK/public API client engine. Current official repository documents:
- MEGA user-controlled/end-to-end encryption as an intrinsic storage model;
- public API access through `megaapi.h` and SDK examples;
- MEGAcmd can expose higher-level scriptable access, including from PHP/Python processes.

This is enough to establish that a maintained provider-native integration surface exists, but not enough to choose a production WordPress/PHP adapter.

### WPE static profile
- maintained official provider SDK/API: yes;
- provider-side/client-controlled encryption semantics: materially different from ordinary object stores and must coexist with WPE Backup encryption intentionally;
- PHP-native direct SDK strategy: **unresolved**;
- MEGAcmd subprocess integration is not automatically acceptable for shared hosting/security/dependency reasons;
- no resumable/finalization/integrity claim is made by this review.

### Still unverified/static-open
- appropriate official integration surface for PHP/WordPress;
- app-key/account auth lifecycle;
- upload session/resume/final commit semantics;
- remote file/node identity and duplicate handling;
- delete/version/recovery behavior;
- dependency license/distribution/support implications;
- shared-host compatibility.

Static maturity: **SE1**. Runtime certification: **none**.

Official reference:
- https://github.com/meganz/sdk

## 11. Remaining low-evidence targets after this refresh

Still intentionally low/uncertified include:
- `local-server` / `browser-export` — runtime-environment behavior dominates static provider docs;
- `ftp-generic` / `ftps-generic` / `sftp-generic` — exact client/server semantics need protocol/library profile review and later runtime proof;
- `s3-custom` — intentionally cannot inherit arbitrary S3 capabilities before endpoint profile/probe;
- any provider capability not explicitly reviewed in this or the canonical registry.

The next static pass should focus on protocol/library contracts rather than trying to make every row SE3 artificially.

## 12. Certification invariant

After this research refresh:
- provider target count: **34**;
- provider runtime C0–C4 certified count: **0**;
- normal Supported Backup Destination count: **0**.

No credential, provider endpoint, SDK, upload, delete, restore or certification fixture was executed.
