# WPEssential Membership — Protected File Delivery & Origin-Bypass Certification Profile

Status: **Phase 0 paper security/delivery profile / no file move, server config, signed URL or download runtime authorized**  
Date: 2026-08-28  
Related: Membership Protected File Architecture, Membership ADR-0078, Vault ADR-0085, Protector, Backup, Connections.

## Purpose

Define what WPE must prove before calling an asset **protected**.

A hidden button, protected attachment page or obscure URL is not file protection if the original bytes remain publicly reachable.

## Core invariant

For a supported protected-asset deployment, there must be **no unauthenticated bypass path to origin bytes** within the certified storage/delivery configuration.

Every new download initiation is authorized against current WordPress/WPE/ Membership Policy unless a separately accepted short-lived token represents that authorization within its documented limits.

## Delivery profiles

### PD1 — Private local storage + PHP streaming — universal compatibility baseline

Bytes live outside the public web root or under a configuration proven inaccessible by direct public URL.

Flow:
1. opaque Protected Asset request;
2. authenticate/resolve principal;
3. outer WordPress/Protector authorization;
4. current Membership Policy check;
5. resolve canonical private asset;
6. validate headers/range request;
7. stream bounded bytes through PHP;
8. record optional privacy-safe result.

Advantages:
- strongest generic deployment independence;
- simple origin-bypass reasoning.

Costs:
- PHP worker/memory/time pressure for large files;
- Range/resume implementation burden.

PD1 is the first correctness baseline, not necessarily the preferred high-volume transfer mode.

### PD2 — Server-accelerated private local delivery

Examples: certified Nginx internal redirect/X-Accel profile or Apache/server X-Sendfile-like profile.

PHP authorizes; web server serves bytes only from an internal/private mapping.

Mandatory health evidence:
- direct external origin URL/path is denied;
- internal mapping cannot traverse outside allowed root;
- request cannot inject arbitrary internal path;
- failure does not fall back to public URL;
- Range/cache/header behavior is documented.

PD2 is preferred over PHP streaming for large local files only after server-specific certification.

### PD3 — Private object storage + short-lived signed delivery

Origin object/bucket remains private.

WPE authorizes each initiation and obtains/generates a short-lived provider-supported signed URL/token.

Hard truth:
- an already-issued bearer URL may remain usable until expiry/provider revocation semantics permit otherwise;
- WPE does not promise instant termination of an in-flight download on Membership revoke;
- canonical unsigned public object URL must not provide access;
- permanent presigned URLs are never stored as asset metadata.

TTL defaults require executable evidence; product policy should favor the shortest practical initiation window.

### PD4 — Private CDN/tokenized delivery with stronger revocation controls — future profile

For use cases requiring stronger near-real-time revocation or edge delivery than PD3 can guarantee.

Provider/CDN capability must explicitly prove token/key/cache/revocation behavior. Generic CDN compatibility is not inferred.

## Protected Asset metadata

Canonical metadata contains only safe identity/reference data:
- asset UUID;
- site/network ownership;
- optional attachment linkage;
- storage adapter/object reference;
- MIME/size/checksum;
- protection Rule/Policy reference;
- derivative/preview policy;
- transfer capability profile;
- migration/source metadata.

Raw filesystem path, storage secret or reusable signed URL is never public API identity.

Protected bytes are not stored in Membership DB rows merely to enforce access.

## Origin-bypass certification levels

### PC0 — Configured / unverified

Asset/profile configured. Product must not claim protected delivery.

### PC1 — Origin isolation proven

Fixtures prove unauthenticated direct-origin paths fail for original and protected derivatives.

### PC2 — Authorization gate proven

Fixtures prove:
- active eligible principal can initiate;
- anonymous/ineligible/revoked/force-denied principal cannot initiate;
- wrong-site asset reference cannot cross scope;
- authorization cache generation cannot preserve stale allow after revoke.

### PC3 — Transfer semantics proven

Adds:
- headers/filename/MIME safety;
- Range/resume behavior where advertised;
- large-file/resource limits;
- cache/CDN privacy;
- expired token behavior;
- storage/provider failure fail-closed behavior.

### PC4 — Lifecycle/recovery profile proven

Adds:
- migration from public to private origin;
- site Backup/Restore/clone behavior;
- storage credential/key rotation;
- origin/public-link regression tests;
- retention/delete behavior;
- production runbook and health checks.

Only a profile reaching the required level for its advertised capabilities may be labeled Supported/Protected.

Executed PC1+ certifications: **0**.

## Existing public attachment migration

Adding a Membership Rule to a public attachment is not enough.

Migration Plan must choose:
- copy-to-private;
- move-to-private;
- protect-origin-via-certified-server-rule.

Plan includes:
- source URL/path inventory;
- known content/link dependencies;
- derivative inventory;
- target private identity;
- cutover point;
- public-origin verification after cutover;
- rollback/recovery class;
- Backup requirement for move/destructive cases.

A successful metadata update does not prove old public bytes disappeared.

## Authorization/cache model

Every initiation checks current authorization or validates a bounded signed token tied to:
- purpose;
- asset;
- subject where user-bound;
- issuance/expiry;
- policy/access generation where supported;
- token ID/nonce when one-time semantics are required.

WordPress nonce alone is not the generic protected-file authorization model.

Membership allow never bypasses outer WordPress/Protector security.

## Download counters/limits

If future benefits add download-count/byte/redemption limits, enforcement is a transactional runtime domain:
- check + consume must be race-safe according to strictness;
- browser-only counters are invalid;
- failed initiation/transfer accounting semantics must be explicit;
- retries/resume must not double-consume incorrectly.

No counter schema is approved by this profile.

## Range/resume

Adapter advertises:
- none;
- single-range;
- multi-range if explicitly certified;
- resume/reconnect behavior.

Malformed/oversized Range requests must be bounded. Resumed/reconnected requests reauthorize according to selected delivery profile/token semantics.

## Cache/CDN gates

Reject profile if:
- authorization response can be shared across principals without safe keying;
- CDN normalizes/removes signature inputs and exposes object;
- unsigned canonical URL is public;
- cache plugin can serve prior protected response anonymously;
- protected derivative becomes public unintentionally.

## Preview/derivative policy

Each derivative is classified independently:
- public teaser;
- protected;
- unavailable.

Private original does not automatically make WordPress thumbnails/previews private, and public preview must be intentional.

## Multisite

Protected Asset ownership includes trusted site/network scope.

Rules:
- site principal cannot fetch another site's asset by UUID/object ID collision;
- shared network asset requires explicit network ownership + use policy;
- site Backup exports only owned asset metadata/bytes or references according to storage adapter;
- site deletion/transfer does not remove another site's shared/private objects.

## Future executable fixtures — NOT AUTHORIZED

For PD1/PD2/PD3 and later PD4 where supported:
- direct origin URL/path attempts;
- attachment page vs file URL bypass;
- derivative/thumbnail bypass;
- anonymous/expired/revoked/force-deny;
- stale cache after revoke;
- wrong-site asset IDs;
- path traversal/header injection/Range fuzzing;
- large file/aborted transfer;
- signed URL theft/expiry/reconnect semantics;
- CDN/query normalization/cache leak;
- storage credential loss;
- public→private migration cutover;
- Backup/Restore/clone;
- 100/1k concurrent download initiation and rate/resource behavior.

No file has been moved, streamed, signed, downloaded or server-configured by this document.

## Paper recommendation

Use **PD1 as universal correctness baseline**, prefer **PD2** for certified efficient local delivery, and use **PD3** for private object storage with explicit bearer-URL expiry limitations. PD4 is future provider-specific stronger revocation/edge delivery.

Do not market an asset as protected until the applicable origin-bypass/authorization certification evidence exists.