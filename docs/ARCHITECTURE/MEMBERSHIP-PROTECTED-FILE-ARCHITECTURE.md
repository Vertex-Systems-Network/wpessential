# WPEssential Membership — Protected File Architecture

Status: **Phase 0 paper design / Proposed / no file-serving implementation exists**  
Related: Membership System, M-005, Protector, Media Rules, Backup, Connections

A Membership rule does not truly protect a file if the original file is still reachable through a public WordPress uploads URL.

This document defines the architecture required before WPEssential can market a download/file as protected.

## Primary research references

- Nginx `internal` locations and `X-Accel-Redirect`: https://nginx.org/en/docs/http/ngx_http_core_module.html
- Nginx FastCGI processing of `X-Accel-Redirect`: https://nginx.org/en/docs/http/ngx_http_fastcgi_module.html
- Amazon S3 presigned URLs: https://docs.aws.amazon.com/AmazonS3/latest/userguide/using-presigned-url.html
- AWS presigned URL guardrails: https://docs.aws.amazon.com/prescriptive-guidance/latest/presigned-url-best-practices/additional-guardrails.html
- WordPress direct attachment file URL behavior: https://developer.wordpress.org/reference/functions/get_attachment_link/

---

# 1. Core rule

A protected asset must have **no unauthenticated bypass path to its origin bytes** within the supported deployment configuration.

Therefore these are not sufficient by themselves:
- hiding the file link;
- protecting only the attachment page;
- checking Membership before rendering a download button;
- generating an obscure filename;
- relying on `robots.txt`;
- storing the attachment in ordinary public `/uploads/` while only gating a shortcode.

WordPress exposes direct attachment file URLs separately from attachment pages, so protecting an attachment post does not inherently protect the physical media file.

---

# 2. Protected Asset abstraction

Membership/other modules should reference a **Protected Asset**, not a raw URL.

Candidate logical fields:
- asset UUID;
- optional WordPress attachment ID for metadata/library linkage;
- storage adapter;
- storage object/path key;
- original filename;
- MIME type;
- size/checksum;
- protection policy/rule UUID;
- preview/thumbnail policy;
- download filename/content-disposition policy;
- Range support flag/capability;
- created/updated metadata;
- migration/source metadata.

The protected asset can still appear in Media Library through an adapter, but the public attachment URL must not become the authorization mechanism.

---

# 3. Storage modes

## Mode A — Private filesystem outside public web root — preferred universal local mode

Store protected bytes in a directory that the web server cannot serve by normal URL.

Request flow:
1. user requests WPEssential protected-download endpoint;
2. authenticate request where necessary;
3. evaluate WordPress/resource security;
4. evaluate Membership access policy;
5. resolve asset safely;
6. deliver through accepted transfer strategy;
7. log optional privacy-safe download event.

Benefits:
- no public-origin URL bypass;
- works conceptually across Apache/Nginx if PHP can read the private path.

Risks:
- PHP streaming large files can consume workers/resources;
- host filesystem/storage limitations;
- shared hosting path/permission differences.

## Mode B — Nginx internal location + `X-Accel-Redirect`

Nginx documents that a location marked `internal` returns 404 to external requests and can be entered by an internal redirect including `X-Accel-Redirect` from upstream/FastCGI.

Candidate flow:
1. PHP performs Membership/access check;
2. PHP returns safe `X-Accel-Redirect` to an internal-only file location;
3. Nginx serves file efficiently without routing all bytes through PHP.

Requirements:
- administrator/server config;
- path mapping cannot be user-injectable;
- internal location must not expose directory traversal;
- health check verifies configuration before mode is considered active.

WPEssential cannot silently assume host supports this.

## Mode C — Apache/private filesystem accelerated delivery

A comparable efficient server-assisted method may use server modules/configuration such as X-Sendfile where hosting supports it.

Because Apache X-Sendfile is not a universal WordPress/core capability, it must be an adapter/capability mode with explicit detection/documentation rather than a default promise.

Fallback remains private-path PHP streaming where safe.

## Mode D — Private object storage + short-lived signed URL

For S3-compatible/private object storage:
1. origin bucket/object remains private;
2. WordPress evaluates access first;
3. server creates a short-lived download URL or token using Storage adapter credentials;
4. client downloads directly from object storage/CDN.

AWS documents that S3 presigned URLs are bearer-style URLs valid until their configured expiry or underlying credentials expire. They should therefore be short lived and treated as temporary credentials/links, not permanent member URLs.

Benefits:
- scalable large-file delivery;
- offloads PHP/web server.

Risks:
- anyone receiving an unexpired signed URL can generally use it according to the signature permissions;
- membership revocation cannot necessarily invalidate an already-issued URL immediately unless provider/key/policy supports revocation;
- URLs may leak into browser history, logs, referrers or support screenshots;
- CDN caching must not turn a signed/private object into public content.

## Mode E — provider-specific private CDN/media offload

Only supported after adapter-specific evidence.

Examples may include:
- S3/CloudFront private delivery;
- other object-store signed URL systems;
- WordPress media-offload plugins exposing supported private APIs.

Do not claim generic compatibility with every CDN/offload plugin merely because the media exists remotely.

---

# 4. Existing public Media Library migration

If an existing attachment lives at a public URL, adding a Membership rule cannot retroactively make the bytes private unless origin access changes.

Candidate migration options:

## Copy-to-private
- preserve original public attachment for ordinary site use;
- create protected private copy as a new Protected Asset;
- Membership link references private asset only.

Useful when same source image/file is also legitimately public elsewhere.

## Move-to-private
- move original bytes to protected storage;
- update attachment integration/metadata according to adapter;
- any existing public URLs must return unavailable/non-bypass response.

High impact: require dependency/use scan and broken-link warning.

## Protect-origin-via-server-rule
- keep physical file location but configure server so direct public path is inaccessible except internal authenticated transfer.

Only acceptable if health test proves direct origin bypass is blocked.

---

# 5. Download endpoint

Candidate route should use opaque Protected Asset identity, not raw filesystem path.

Example conceptual flow:

`/wpe-download/<opaque-token-or-asset-ref>`

Server resolves:
- asset UUID;
- principal;
- requested operation;
- access policy;
- storage adapter;
- transfer strategy.

Never accept an arbitrary `file=/var/www/...` or raw uploads path from request.

---

# 6. Authorization and caching

Every new protected-download initiation performs current authorization unless an explicitly accepted signed-token design safely represents recent authorization.

Hard requirements:
- revoked/expired Enrollment cannot start a new download;
- `force_deny` applies immediately according to accepted cache model;
- Membership allow does not bypass WordPress/Protector outer security;
- cache key/version cannot preserve stale allow after revocation;
- protected download and page/component access use the same Access Policy contract.

---

# 7. Signed download token candidate

For local protected endpoint, WPEssential may issue a short-lived signed token rather than exposing predictable asset UUID in a reusable public link.

Candidate claims:
- asset UUID;
- subject/user ID where link is user-bound;
- issued time;
- expiry;
- purpose `download`;
- optional nonce/token ID;
- policy/access generation snapshot.

Security properties needed:
- HMAC/AEAD using dedicated signing key material;
- no plaintext secrets inside token;
- short TTL;
- reject purpose/resource substitution;
- optional one-time token only where business requirement justifies state cost.

This is separate from WordPress nonce semantics; WP nonces are not designed as a generic long-lived signed file authorization system.

Exact token format remains open.

---

# 8. Signed URL expiry and revocation limitation

For object-storage presigned URLs, UI/docs must state that an already-issued URL can remain usable until expiry according to provider semantics.

Candidate default:
- use the shortest TTL compatible with download initiation and user experience;
- default minutes, not days;
- never store permanent presigned URL as attachment metadata;
- generate on authorized request;
- underlying private object remains inaccessible without signature.

Large downloads may continue after URL expiry if the request began before expiration depending on provider behavior; reconnection after expiry may fail. Product UI should not promise byte-stream termination exactly at Membership expiration.

If a use case requires near-instant revocation of in-flight/signed access, choose a proxy/tokenized CDN architecture capable of stronger revocation rather than overclaiming S3 presigned URLs.

---

# 9. HTTP Range requests

Large media/PDF/video may require Range support.

Transfer adapter declares:
- Range supported yes/no;
- multi-range support if relevant;
- streaming/resume behavior;
- authorization behavior on resumed request.

For local PHP streaming, Range parsing/serving must be carefully bounded and tested; malformed Range headers must not lead to arbitrary reads/memory abuse.

Server/object-storage accelerated modes should be preferred for very large assets.

---

# 10. Content headers

Server controls:
- validated `Content-Type`;
- `Content-Length` where known;
- safe `Content-Disposition`;
- sanitized filename;
- anti-sniff header where appropriate;
- cache policy;
- Range headers if supported.

Filename cannot inject CRLF/header content.

Never trust user-uploaded MIME label alone; store verified media/file type metadata during upload/import.

---

# 11. Cache/CDN policy

Default for direct authenticated local responses:
- private/no shared public cache;
- no cache key that ignores user/policy context.

For signed object/CDN URLs:
- CDN/origin may cache bytes privately/publicly according to provider design, but request authorization remains enforced through signed URL/cookie/key;
- unsigned canonical public URL must not exist for protected object;
- query-string stripping/CDN normalization must not remove signature semantics;
- avoid caching WPEssential authorization response across users.

Health/diagnostics should warn about obvious cache plugins/CDNs bypassing dynamic download authorization where detectable.

---

# 12. Preview and derivative policy

Protecting original PDF/video while leaving generated thumbnail/previews public can leak content.

Per asset/rule decide:
- preview public;
- preview member-only;
- generated sanitized teaser public;
- no preview.

Watermarker/Media Rules can generate derivatives, but Membership decides access to each protected derivative.

Do not assume WordPress attachment thumbnails inherit private status automatically.

---

# 13. Download limits / benefits

Optional future Membership benefits:
- N downloads per period;
- total bytes quota;
- asset-specific redemption count;
- one-time entitlement.

These require transactional counters and race-safe consumption.

Do not implement download count enforcement only in browser/UI.

If added, counter check/update must be atomic enough to prevent concurrent over-redemption according to configured strictness.

---

# 14. Logging/privacy

Download logging is optional and privacy-classified.

Potential log fields:
- user/subject ID;
- asset UUID;
- timestamp;
- result allow/deny;
- safe reason code;
- transfer mode;
- bytes sent/result where available;
- correlation ID.

Do not store IP/User-Agent indefinitely by default merely for analytics. Retention and privacy exporter/eraser behavior must be defined.

---

# 15. Failure behavior

## Private storage unavailable
- deny download safely;
- do not fall back to public source URL;
- diagnostics identify storage health failure.

## Access Policy unavailable/corrupt
- protected asset fails closed;
- do not serve because rule could not load.

## Object-storage signing credentials unavailable
- no signed link;
- show safe operational error;
- do not make bucket/object public as fallback.

## Server acceleration misconfigured
- health check marks unsupported/unhealthy;
- use verified private PHP streaming fallback only if configured;
- otherwise deny rather than expose origin.

## Pro/license management unavailable
Security/access enforcement for already-protected assets remains active according to ADR-0007/0013. License failure must not expose them.

---

# 16. Admin configuration surfaces

Protected Files screen/profile should eventually expose options such as:

### Storage
- storage connection/mode;
- private base path/bucket/container;
- migration/copy mode;
- server acceleration detected/selected;
- fallback strategy;
- health/test.

### Access
- Membership rule;
- unauthenticated behavior;
- signed-token TTL;
- object signed-URL TTL;
- user-bound link yes/no if transfer mode supports it;
- preview policy;
- optional download limits.

### Delivery
- Content-Disposition inline/attachment;
- download filename template;
- Range support;
- cache behavior presets;
- large-file warning/threshold.

### Diagnostics
- direct origin bypass test;
- private path readability;
- Nginx internal/X-Accel health where selected;
- object private ACL/policy check where provider supports test;
- signing test;
- cache/CDN warning;
- last successful transfer.

Every option requires capability, validation and test semantics before implementation.

---

# 17. Support matrix semantics

Do not use one generic badge `Protected files supported`.

Advertise supported transfer profiles explicitly, for example only after verification:
- Private local + PHP streaming;
- Private local + Nginx X-Accel;
- Private local + Apache server acceleration adapter;
- Amazon S3 private presigned download;
- S3-compatible provider X tested;
- CDN/provider Y private adapter.

A storage provider being usable for Backup does not automatically mean it is certified for protected Membership media delivery.

---

# 18. Future evidence — NOT AUTHORIZED

Before release claims, executable tests must prove supported profiles:
- direct origin URL denied;
- authorized access succeeds;
- anonymous denied;
- expired/revoked user denied;
- stale cache cannot retain access;
- signed URL/token expires;
- object remains private;
- Range/resume behavior;
- large-file resource usage;
- filename/MIME/header injection defenses;
- CDN/cache behavior;
- preview derivatives do not leak unintended protected content;
- provider credentials unavailable fails closed.

No server configuration, protected file copy, PHP stream endpoint or provider integration may be created before explicit owner development/spike consent.

---

# 19. Current recommendation

WPEssential should model protected media as an adapter-driven **Protected Asset** with a private origin.

Preferred delivery order:
1. private storage outside public web root;
2. server-assisted internal transfer (Nginx X-Accel / supported equivalent) for local large files;
3. PHP streaming as verified universal fallback with resource limits;
4. private object storage + short-lived signed delivery for scalable remote assets;
5. provider-specific CDN/offload adapters only after explicit certification.

The product must never claim a public `/uploads/` file is protected merely because its link or attachment page is hidden.
