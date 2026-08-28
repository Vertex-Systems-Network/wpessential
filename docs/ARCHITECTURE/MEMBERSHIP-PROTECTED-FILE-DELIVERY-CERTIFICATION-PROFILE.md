# WPEssential Membership — Protected File Delivery & Origin-Bypass Certification Profile

Status: **Phase 0 paper security/delivery profile / executable evidence defined / no file move, server config, signed URL or download runtime authorized**  
Date: 2026-08-28  
Related: Membership Protected File Architecture, ADR-0090, Membership ADR-0078/0129, Vault ADR-0085/0124, Protector ADR-0159, Watermarker/Media ADR-0168, Backup ADR-0130, Privacy ADR-0144/0171, Error Taxonomy ADR-0145, Contract Versioning ADR-0147, Rate Limit ADR-0153, Cache ADR-0154, Multisite/Site Lifecycle ADR-0141.

## Purpose

Define what WPE must prove before calling an asset **protected**.

A hidden button, protected attachment page or obscure URL is not file protection if the original bytes remain publicly reachable.

This profile preserves the accepted PD1–PD4 delivery profiles and PC0–PC4 maturity ladder while adding a fixed executable evidence namespace: **PC-F001…PC-F176**.

Current executable truth:
- PC-F documented: **176**;
- PC-F executed: **0/176**;
- PC1+ runtime-certified protected-file profiles: **0**;
- no delivery profile is runtime-certified;
- no storage/provider support claim is promoted to protected-file certification by this document.

## Core invariant

For a supported protected-asset deployment, there must be **no unauthenticated bypass path to origin bytes** within the certified storage/delivery configuration.

Every new download initiation is authorized against current WordPress/WPE/Membership Policy unless a separately accepted short-lived token represents that authorization within its documented limits.

Hard separations:
- storage possession **≠** authorization;
- attachment/page visibility **≠** origin-byte protection;
- Membership allow **≠** bypass of outer WordPress/Protector security;
- signed token/URL issuance **≠** durable Membership entitlement;
- Backup-provider support **≠** protected-file delivery support;
- object/CDN reachability **≠** WPE authorization;
- static provider documentation **≠** PC1+ runtime certification.

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

A previously authorized or cached decision cannot outlive a force-deny/revoke boundary beyond the explicitly accepted token/cache semantics of the certified profile.

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

Watermarker/Media may create derivatives, but derivative possession or generation never grants Membership access.

## Vault / provider / Safe HTTP boundary

- local filesystem paths and server mappings are private runtime configuration, not public identifiers;
- object/CDN credentials and signing secrets are Vault-managed according to their sensitivity class;
- provider signing/fetch requests use the certified adapter/Safe HTTP boundary where applicable;
- provider credential loss, expiry or permission downgrade fails closed;
- provider error text, signed URLs, object keys and secrets are redacted from public/admin logs according to privacy/error policy;
- a storage provider certified for Backup is independently certified for protected-file delivery.

## Error / observability truth

Protected delivery failure must distinguish at minimum:
- authorization deny;
- missing/corrupt Protected Asset metadata;
- origin isolation failure;
- storage unavailable;
- token/signature failure;
- provider unavailable/unknown outcome;
- transfer/range failure;
- rate/resource rejection;
- lifecycle/migration inconsistency.

Unknown authorization or origin-isolation state fails closed. Operational errors must not expose private paths, secrets, bearer URLs or cross-site identifiers.

## Multisite

Protected Asset ownership includes trusted site/network scope.

Rules:
- site principal cannot fetch another site's asset by UUID/object ID collision;
- shared network asset requires explicit network ownership + use policy;
- current-blog context is not durable ownership or authorization;
- site Backup exports only owned asset metadata/bytes or references according to storage adapter;
- site deletion/transfer does not remove another site's shared/private objects;
- cloned/restored sites cannot reuse production signing/provider identity blindly.

# Fixed executable evidence protocol — PC-F001…PC-F176 — NOT AUTHORIZED

The following fixture IDs are fixed planning evidence. The 16 groups contain 11 ordered fixtures each, totaling **176**. Within each group, the listed scenarios map sequentially to the group's ID range.

No fixture is considered passed by inspection, documentation, implementation existence, provider marketing material or a successful happy-path download. Runtime execution requires explicit scoped owner consent under ADR-0014.

## Group 1 — Protected Asset identity / canonical binding — PC-F001…PC-F011

1. valid opaque asset resolves exact canonical object;
2. raw filesystem path input rejected;
3. raw public uploads URL cannot substitute for asset identity;
4. unknown asset UUID fails closed;
5. deleted/tombstoned asset fails closed;
6. attachment ID collision cannot cross-bind storage object;
7. checksum/size metadata mismatch enters safe error/reconciliation state;
8. storage-adapter mismatch cannot silently switch origin;
9. policy/rule revision binding is explainable and deterministic;
10. object/path key is never authorization truth;
11. public API/log output does not expose private path, credential or reusable signed URL.

## Group 2 — Origin isolation / direct bypass — PC-F012…PC-F022

1. direct original public URL denied;
2. attachment page protection cannot be bypassed by file URL;
3. generated thumbnail/preview direct URL follows explicit derivative policy;
4. alternate image size/derivative path cannot expose protected bytes;
5. directory-index/path guessing cannot reach origin;
6. encoded/traversal path variant denied;
7. stale pre-migration public URL denied after cutover;
8. cache/CDN old public object does not survive protected cutover unintentionally;
9. backup/temp/staging copy is not web reachable;
10. server misconfiguration is detected and profile becomes unsupported/fail-closed;
11. origin-isolation health recheck catches regression after configuration change.

## Group 3 — Authorization / Membership / outer policy — PC-F023…PC-F033

1. active eligible principal can initiate;
2. anonymous principal denied;
3. authenticated but ineligible principal denied;
4. expired Enrollment denied;
5. revoked Enrollment denied;
6. force-deny overrides Membership allow;
7. outer WordPress/Protector deny overrides Membership allow;
8. stale authorization cache cannot preserve allow after revoke;
9. concurrent revoke vs initiation has documented deterministic boundary;
10. wrong user/subject-bound token cannot be replayed by another principal;
11. unknown/corrupt policy state fails closed without serving bytes.

## Group 4 — Local signed token / initiation token semantics — PC-F034…PC-F044

1. valid purpose/resource-bound token accepted;
2. expired token rejected;
3. future-issued/clock-skew-invalid token handled by bounded policy;
4. asset substitution invalidates token;
5. purpose substitution invalidates token;
6. subject-bound token rejects different user;
7. tampered signature rejected;
8. token key rotation behavior proven;
9. one-time token race cannot over-redeem when one-time mode advertised;
10. token/referrer/log handling does not create unintended durable bearer leak;
11. WordPress nonce alone cannot satisfy protected-file token certification.

## Group 5 — PD1 PHP streaming correctness / resource safety — PC-F045…PC-F055

1. authorized complete small-file stream succeeds;
2. zero-byte file behavior explicit;
3. missing/unreadable file fails closed;
4. read error mid-stream produces truthful partial-failure result;
5. client disconnect releases resources safely;
6. large-file memory use remains bounded;
7. execution-time/worker occupancy policy enforced;
8. no arbitrary path read via crafted asset metadata/request;
9. byte count/content length truth matches served range/body where known;
10. retry/reconnect reauthorizes according to profile;
11. PD1 failure never falls back to public origin URL.

## Group 6 — PD2 server-accelerated local delivery — PC-F056…PC-F066

1. Nginx/internal or equivalent external direct request denied;
2. authorized internal redirect serves exact asset;
3. internal path traversal rejected;
4. request header/path injection cannot select arbitrary internal file;
5. acceleration health detection distinguishes supported vs unavailable;
6. server config removal causes fail-closed/degraded profile, not public fallback;
7. Range semantics match advertised capability;
8. Content-Disposition/type headers remain safe through acceleration layer;
9. cache behavior does not make internal resource public;
10. wrong-site asset cannot map into another site's internal root;
11. server/profile version change triggers recertification/review state.

## Group 7 — PD3 private object storage / signed URL — PC-F067…PC-F077

1. unsigned canonical object URL denied;
2. authorized request receives bounded short-lived signed delivery;
3. expired signed URL rejected by provider semantics;
4. tampered object/key/signature rejected;
5. copied bearer URL behavior is documented truthfully;
6. Membership revoke blocks new issuance but does not overclaim already-issued URL revocation;
7. reconnect after URL expiry matches provider semantics;
8. object remains private after signing failure/credential failure;
9. provider permission downgrade fails closed;
10. signed URL is never persisted as permanent asset metadata;
11. test/staging provider identity cannot authorize production object access.

## Group 8 — PD4 private CDN / stronger revocation — PC-F078…PC-F088

1. unsigned/public CDN path denied;
2. edge token/cookie validates intended asset/resource;
3. expiry enforced at edge according to advertised semantics;
4. revocation/key-generation change invalidates future access as claimed;
5. cached object bytes remain protected from unsigned requests;
6. query/header normalization cannot strip authorization material;
7. alternate hostname/origin path cannot bypass token gate;
8. fail-open CDN configuration is detected and certification rejected;
9. key rotation propagation window measured/documented;
10. regional/edge inconsistency does not exceed advertised revocation semantics;
11. generic CDN presence never implies PD4 certification.

## Group 9 — HTTP headers / MIME / Range / resume / abuse — PC-F089…PC-F099

1. safe Content-Type from verified metadata;
2. Content-Disposition filename sanitizes CRLF/control/path characters;
3. anti-sniff/cache headers match profile;
4. valid single range returns correct bounded bytes if advertised;
5. invalid/unsatisfiable range handled safely;
6. oversized/malformed Range cannot cause excessive memory/CPU;
7. multi-range rejected unless explicitly certified;
8. resume/reconnect requires valid current authorization/token semantics;
9. compressed/encoded response does not corrupt range/security semantics;
10. HEAD/conditional request behavior cannot bypass authorization;
11. method confusion/non-download verbs cannot expose content.

## Group 10 — Cache / CDN / intermediary isolation — PC-F100…PC-F110

1. authenticated authorization response not shared across users;
2. stale allow invalidated on force-deny/revoke generation change;
3. stale deny recovery follows accepted cache policy;
4. cache key includes required site/principal/policy dimensions;
5. CDN query normalization cannot remove signed parameters;
6. cache plugin anonymous hit cannot replay prior protected response;
7. Vary/private/no-store behavior matches transfer profile;
8. redirect responses containing bearer URLs are not cached unsafely;
9. signed object/CDN cache cannot expose unsigned canonical URL;
10. purge failure is observable and does not silently claim immediate revocation;
11. cache backend outage follows fail-closed authorization truth rather than stale allow.

## Group 11 — Preview / derivative / Media / Watermarker boundary — PC-F111…PC-F121

1. public teaser is intentionally public and contains no protected original bytes beyond policy;
2. protected thumbnail requires authorization;
3. unavailable derivative cannot be generated/fetched anonymously;
4. regenerated thumbnail inherits explicit derivative policy, not accidental public default;
5. Watermarker derivative creation does not change Membership authority;
6. original immutable source policy remains intact;
7. alternate-format conversion (WebP/AVIF/PDF preview/etc.) cannot leak protected content;
8. attachment metadata does not reveal private storage secret/path;
9. media offload plugin cannot silently replace certified private origin with public origin;
10. derivative deletion/retention remains scoped to owner/site;
11. provider/media adapter version change triggers certification review.

## Group 12 — Download limits / redemption / concurrency / rate control — PC-F122…PC-F132

1. no-limit mode performs no hidden browser-only enforcement claim;
2. single-redemption check+consume is race-safe when advertised;
3. N-download limit cannot be exceeded by concurrent initiations beyond accepted strictness;
4. byte quota accounting semantics documented;
5. failed initiation does not consume incorrectly;
6. aborted transfer accounting semantics documented;
7. Range/retry does not double-consume incorrectly;
8. anonymous abuse is bounded by shared RLT policy where enabled;
9. authenticated burst limit cannot become authorization decision;
10. rate-limit backend failure does not grant otherwise-denied access;
11. quota/rate counters are scoped correctly across Multisite/network ownership.

## Group 13 — Public→private migration / cutover / rollback — PC-F133…PC-F143

1. copy-to-private preserves intended public original and protects private copy;
2. move-to-private removes/breaks old public origin only after safe cutover;
3. protect-origin server-rule mode proves old direct URL denial;
4. derivative inventory included in migration;
5. known content/link dependencies reported before destructive move;
6. partial migration failure is recoverable and does not expose new public bypass;
7. retry is idempotent and does not duplicate/confuse Protected Assets;
8. rollback class preserves authorization truth;
9. migration from third-party/offload source preserves provenance;
10. configuration/package import cannot silently bind to missing/public storage;
11. post-migration origin regression scan proves old public URLs remain denied where required.

## Group 14 — Backup / Restore / clone / deletion / key lifecycle — PC-F144…PC-F154

1. Backup captures required protected metadata/bytes/reference according to profile;
2. Restore does not make private bytes public;
3. restore to new host revalidates private path/server capability;
4. clone/staging disables or isolates production object/CDN issuance as policy requires;
5. cloned site cannot reuse production token/signing identity blindly;
6. Vault/storage key rotation preserves or deliberately invalidates delivery according to runbook;
7. lost credential fails closed with recoverable diagnostics;
8. site deletion removes only owned protected objects/references according to retention policy;
9. network-shared asset survives deletion of one consuming site;
10. backup expiry/deletion is distinct from live protected-asset deletion;
11. restore reconciliation detects missing/orphaned storage objects and prevents false Protected status.

## Group 15 — Privacy / Audit / Error / observability / recovery — PC-F155…PC-F165

1. allow/deny/download logging stores only approved fields;
2. IP/User-Agent retention follows configured privacy policy;
3. signed URL/token/private path/secret redacted from logs;
4. exporter includes applicable local protected-file activity/metadata without secrets;
5. eraser respects legal/retention/backup/provider boundaries;
6. operational error does not expose cross-site asset existence unnecessarily;
7. storage/provider unknown outcome is labeled unknown, not success;
8. correlation IDs connect authorization/transfer/provider events safely;
9. health check detects origin exposure regression;
10. incident runbook supports fail-closed containment and evidence preservation;
11. production certification report states exact unverified capabilities and provider/version scope.

## Group 16 — Multisite / Site Lifecycle / provider-version / scale certification — PC-F166…PC-F176

1. same UUID/object-key collision across sites cannot cross-authorize;
2. current-blog switch cannot substitute for durable ownership check;
3. explicit network-shared Protected Asset follows network policy;
4. site create/clone does not inherit another site's protected object authority accidentally;
5. site archive/suspend behavior follows accepted access policy;
6. site transfer/domain change revalidates delivery/cache/token assumptions;
7. provider/plugin/server version outside certified range becomes unverified/degraded, not silently Supported;
8. 100 concurrent initiations preserve authorization/resource bounds;
9. 1,000 concurrent initiations expose bounded backpressure/failure truth;
10. large-asset/provider latency test validates timeout/retry/resource policy without duplicate authorization grants;
11. final PC level is awarded only to the exact delivery/storage/provider/version/deployment profile whose required fixtures passed.

## Certification mapping rules

- **PC0** may exist after configuration/schema validation only; it is **not** a protected-delivery claim.
- **PC1** requires all applicable origin-isolation fixtures, including derivative/public-path regression checks.
- **PC2** additionally requires authorization, scope, stale-cache and token-boundary fixtures.
- **PC3** additionally requires the advertised transfer profile's PD1/PD2/PD3/PD4 fixtures plus HTTP/Range/cache/resource/error evidence.
- **PC4** additionally requires migration, Backup/Restore/clone, key/credential lifecycle, retention/privacy, Multisite/Site Lifecycle and production runbook evidence.
- A profile cannot skip a lower maturity level by passing only a higher-level happy path.
- Provider/version/deployment scope is part of the certification identity.
- A failure in direct-origin isolation is stop-the-line for any Protected/Supported claim.

## Stop-the-line conditions for future execution

Immediately stop certification and mark the profile failed/unverified if any fixture shows:
- unauthenticated access to protected origin bytes or protected derivative;
- cross-user/site/network authorization leakage;
- path traversal/arbitrary file read;
- stale cache serving after a force-deny/revoke beyond accepted bounded token semantics;
- secret/private-path/bearer-URL exposure in unauthorized output/logs;
- public fallback after private storage/provider/server failure;
- clone/restore creating a public or cross-environment origin bypass;
- destructive migration with unverifiable rollback/recovery;
- claimed immediate revocation semantics that the selected provider/CDN cannot actually enforce.

## Required future certification report

Every executed protected-file certification must record:
- exact work/fixture IDs and execution date;
- WPE/WordPress/PHP/web-server/runtime versions;
- delivery profile PD1/PD2/PD3/PD4 and storage/provider/version identity;
- site/network topology;
- Membership/Policy/cache/rate-limit mode;
- asset sizes/types/derivative policy;
- pass/fail/not-run evidence;
- resource/performance observations;
- security/privacy findings;
- PC level actually earned;
- known limitations and not-verified claims;
- recovery/rollback notes;
- reviewer class and artifacts.

## Future executable fixtures — NOT AUTHORIZED

The fixed PC-F001…PC-F176 protocol supersedes the earlier unnumbered fixture sketch while preserving its semantics.

No file has been moved, streamed, signed, downloaded or server-configured by this document. No object-storage/CDN API call, server rule, Range implementation, migration, Backup/Restore or benchmark has been executed.

## Paper recommendation

Use **PD1 as universal correctness baseline**, prefer **PD2** for certified efficient local delivery, and use **PD3** for private object storage with explicit bearer-URL expiry limitations. PD4 is future provider-specific stronger revocation/edge delivery.

Do not market an asset as protected until the applicable origin-bypass/authorization certification evidence exists.

## Development gate

**No file move/copy, server configuration, download endpoint, token/signature implementation, provider/storage/CDN API call, protected-object mutation, test download, migration, Backup/Restore, benchmark or runtime fixture is authorized until explicit scoped owner development consent is recorded under ADR-0014.**