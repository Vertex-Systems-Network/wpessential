# WPEssential — Operations & Protection Detailed Specifications

Status: **Phase 0 — specified with storage/server compatibility blockers**

Applies `COMMON-OPTION-CONTRACTS.md`. Resolves Backup Manager, Reset Manager, Protector, Watermarker / Media Rules and XML-RPC Manager.

---

# 24. Backup Manager — Pro

## Principle
A backup is successful only after archive generation + manifest/checksum + destination persistence are verified. “Job completed without exception” is insufficient.

## Backup definition/plan
- name/key/status;
- scope;
- archive options;
- destination(s);
- schedule;
- retention;
- notification policy;
- concurrency/locking.

## Scope defaults
New manual backup wizard defaults:
- database: included;
- uploads: included;
- active themes/plugins: included only when user chooses full-site preset;
- WordPress core: excluded by default because recoverable from distribution and increases size, but full-file preset may include;
- WPE config: included;
- exclusions: cache/temp/log directories suggested where safe.

Presets:
- Database only;
- Content + uploads;
- Full site;
- WPEssential configuration;
- Custom.

## Database
- all WordPress tables default for full DB;
- selected tables custom;
- non-prefix tables not included automatically;
- views/triggers/stored routines support requires compatibility research;
- chunked export;
- consistent snapshot/transaction strategy where storage engine permits;
- DB charset/collation recorded.

## Files
- canonical WP roots selectable;
- symlink policy explicit: do not follow outside site root by default;
- path traversal blocked;
- unreadable file reported;
- exclusion globs validated/bounded;
- huge single file warning.

## Archive
Logical options:
- compression: Zip default where environment supports, alternative tar/gzip adapter;
- compression level: balanced default;
- split archive size optional;
- encryption optional only with reviewed cryptographic implementation;
- manifest always;
- checksum always.

Password-based encryption must not imply strong key management without KDF/key strategy ADR.

## Manifest
Includes:
- backup UUID;
- created time/site ID;
- WPE/WP/PHP/DB versions;
- scope;
- file list/size/checksum or chunk manifest strategy;
- DB table metadata;
- archive parts;
- destination status;
- schema version.

Secrets in config are excluded/masked according to Vault backup strategy ADR; disaster recovery implications documented.

## Destinations
A plan can target multiple destinations. Success policy:
- default overall success requires all required destinations;
- destination can be optional mirror;
- partial success visible.

Connection-specific fields delegated to adapter/Vault.

## Protocol adapters
Initial proof set:
- Local/filesystem;
- manual browser download;
- S3-compatible;
- SFTP;
- WebDAV;
- one OAuth drive provider.

Branded provider adapters reuse protocols where possible. The 30+ catalog is target coverage, not release claim.

## Upload
- multipart/resumable when provider supports;
- chunk size adapter default;
- retry idempotent chunks;
- final remote existence/size/checksum/ETag verification where semantics allow;
- temporary/incomplete object naming to avoid treating partial upload as restoreable backup.

## Schedule
Delegates Job Service.
- daily/weekly/monthly/custom interval;
- site timezone UI;
- missed run policy once ASAP default;
- overlap prevented;
- reliable external runner health warning.

## Retention
Modes per destination:
- keep last N;
- keep for N days/weeks/months;
- GFS-like daily/weekly/monthly advanced future;
- protect manual/restore-point backups.

Deletion after new successful verified backup by default, not before.

## Restore point
Special immutable/protected flag until operation completes. Reset/upgrade flows may request.

## Restore wizard
Stages:
1. select backup;
2. download/read manifest;
3. verify checksum/archive;
4. compatibility/space/preflight;
5. select scope;
6. URL/path/prefix mapping;
7. create pre-restore point when possible;
8. maintenance lock;
9. restore files/DB in defined order;
10. post-restore rewrite/cache/health;
11. unlock/report.

## URL/domain replacement
Uses serialization-aware replacement strategy for WordPress data, not raw blind SQL string replace. Exact tool implementation requires spike.

## Selective restore
- DB selected tables;
- uploads;
- themes/plugins;
- WPE config;
- selected archive paths only if safe mapping.

## Local backup security
Store outside public web root where possible. If inside uploads/site, use server-deny protection + unpredictable directory, but UI warns server config may not honor application-level protection. Protected storage design required.

## Email/Gmail
Email destination only supports small artifact/manifest/link according to mail limits; not marketed as robust large backup storage.

## Logs/metrics
- phase progress;
- bytes/files/tables;
- archive duration;
- upload duration;
- compression ratio;
- retry count;
- destination error.

## Tests
- restore fixture checksum equality;
- interrupted archive/upload resume;
- partial destination failure;
- low memory/large file;
- symlink escape;
- URL serialized replacement;
- retention never deletes only good backup before replacement verified.

---

# 25. Reset Manager — Pro

## Principle
Reset is a destructive workflow, not one button.

## Reset profile
- name/key/status;
- scope;
- exclusions/preservation;
- plugin/theme behavior;
- backup policy;
- post-reset actions.

Profiles with destructive scope cannot auto-run without separate explicit scheduled-reset ADR; v1 manual only recommended.

## Scopes
### WPEssential only
- definitions by modules/all;
- runtime data optional separate selection;
- Vault credentials separate warning;
- audit/support/account connection preservation default.

### Content
- selected post types;
- statuses;
- date/query filter;
- media attachments relationship semantics;
- comments tied to content selectable;
- taxonomies/terms separate.

### Settings
Only known/options selected through registry. Raw options wildcard deletion excluded from normal UI.

### Users
Excluded from normal presets. If future advanced scope allows, preserve current recovery admin/Super Admin and require Level 3.

### Full site/database
Recreates WordPress content/config state according to defined reset baseline while preserving mandatory install/admin access. Exact multisite behavior separate.

## Impact preview
Must show counts:
- posts/terms/comments/media/users where selected;
- tables/options;
- WPE definitions;
- plugin/theme impact;
- estimated irreversible elements.

Preview has fingerprint; execution rechecks material changes when safety-critical.

## Mandatory restore point
Default cannot bypass. Privileged override only if backup engine impossible, with Level 3 explicit phrase and audit; production recommendation remains block unsafe reset.

Restore point includes:
- verified backup ref;
- environment snapshot;
- plugin/theme versions;
- active state;
- WPE config export;
- site URLs;
- DB prefix;
- checksums where useful.

## Theme/plugin actions
- Keep installed + current active state default;
- deactivate all non-required;
- reactivate selected after;
- delete package/files excluded from ordinary reset because unrelated destructive package management.

## Execution
Background/maintenance flow with lock preventing concurrent WPE destructive operation.

If failure mid-reset:
- stop;
- preserve logs;
- offer restore point;
- do not pretend rollback succeeded unless verified.

## Screenshots/video
Not authoritative. Optional pre-reset screenshot may be client-generated later; no server video promise.

## Tests
- restore point unavailable;
- reset failure midpoint;
- current admin preserved;
- selected content boundaries;
- multisite blocked until semantics implemented;
- concurrent reset rejected.

---

# 27. Protector — Pro

## Positioning
Access protection/hardening helper, not a complete WAF/security suite. UI distinguishes access-control guarantees from obscurity/noise-reduction features.

## Rule model
- name/key/status;
- scope/path/resource;
- subjects;
- conditions;
- effect;
- response;
- schedule;
- priority.

## Site gate
Modes:
- Off default;
- shared password;
- login required;
- policy/role/membership/entitlement.

### Password gate
- password stored as secure secret/hash strategy, not plaintext option;
- cookie/session TTL default 24h planning value;
- secure/httponly/samesite cookie where HTTPS/context permits;
- bypass authenticated administrators configurable default true for management recovery;
- brute-force rate limit.

Shared password is site access gate, not user identity.

## Path/resource rule
Match types:
- exact path;
- prefix;
- safe wildcard/pattern;
- post/CPT resource through Policy integration;
- admin area subset.

Regex advanced only with validation/complexity guard.

Subjects:
- guests/authenticated;
- users;
- roles/capabilities;
- memberships/entitlements;
- IP/CIDR supplemental.

Effect allow/deny. Precedence documented by Policy engine; avoid contradictory hidden rules.

## Response
- 403 default deny;
- 404 concealment optional;
- login redirect;
- validated internal redirect;
- custom renderer/message.

External redirect off by default. Loop detection mandatory.

## wp-admin
Rule can restrict nonessential admin access by policy but must preserve AJAX/REST endpoints required by frontend/plugins according to path semantics. Blanket `/wp-admin` block requires compatibility warning.

## Login alias
Optional advanced noise reduction:
- custom alias slug;
- original login handling strategy;
- lost password/register/logout links rewritten compatibly;
- emergency bypass;
- explicit wording that this is not primary security.

Must test common plugin assumptions.

## Rate limiting
Targets:
- login attempts;
- password gate;
- selected REST endpoint integration;
- XML-RPC adapter;
- custom registered action.

Fields:
- attempts;
- window;
- block duration;
- key IP/user/composite;
- trusted proxy config;
- bypass trusted network optional.

Avoid permanent lockouts from shared NAT by conservative defaults and recovery.

## IP handling
Default trust direct remote address. Proxy mode requires explicit trusted proxy CIDRs; do not trust arbitrary `X-Forwarded-For` from internet.

## Security headers
Helper presets only if header not better managed by server/CDN. Detect duplicate/conflicting headers where possible.
Potential fields after standards research:
- frame policy/CSP frame-ancestors;
- content type nosniff;
- referrer policy;
- permissions policy;
- HSTS only when HTTPS/site deployment safe;
- CSP advanced/report-only.

Do not auto-enable HSTS/CSP globally without impact preview.

## Endpoint switches
Protector may call dedicated module/API hooks to disable features, not hack routes blindly. XML-RPC controls remain XML-RPC Manager source of truth.

## Recovery
- documented constant/config bypass;
- signed/time-limited recovery mechanism candidate;
- always logged;
- no secret in public URL long-term.

## Logs
Privacy-aware:
- event type;
- rule;
- outcome;
- hashed/truncated IP strategy considered;
- user ID if authenticated;
- timestamp;
- request path normalized.

Retention default conservative and configurable.

## Tests
- proxy spoof;
- redirect loop/open redirect;
- admin recovery;
- login/lost-password flow;
- membership access;
- REST/AJAX compatibility;
- rate limiter race.

---

# 28. Watermarker / Media Rules — Pro

## Non-destructive invariant
Original uploaded source file is never modified by standard WPE watermark processing. Output is derived rendition/sub-size.

## Rule identity
- name/key/status;
- priority;
- target conditions;
- watermark source/style;
- placement;
- output sizes;
- quality;
- batch behavior.

Multiple matching rules require explicit strategy. Default first/highest-priority rule, not cumulative stacking unless user enables composition.

## Target conditions
- MIME/format;
- min width/height;
- max optional;
- media attachment metadata;
- upload context/post type;
- taxonomy/relation context if deterministic;
- uploader role/user;
- include/exclude attachment IDs.

If context is unavailable at upload time, rule may defer to background relation processing; UI must state timing.

## Text watermark
Fields:
- text required;
- font adapter/source;
- size px/relative;
- weight/style if font supports;
- color;
- opacity 0–100;
- rotation -180–180;
- optional stroke/shadow only if image library support consistent.

Bundled font licensing must be reviewed; system fonts may not be consistently available server-side.

## Image watermark
- attachment/uploaded WPE asset;
- PNG/WebP raster preferred for broad image-library compatibility;
- SVG requires sanitization then safe raster/render strategy;
- scale relative to source or fixed max;
- opacity;
- rotation.

## Placement
9 presets:
- top-left/top-center/top-right;
- center-left/center/center-right;
- bottom-left/bottom-center/bottom-right.

Custom:
- X/Y offset;
- px/% units;
- margins;
- anchor.

## Tiled
- enabled false;
- horizontal/vertical spacing;
- stagger optional;
- max repeated instances safety cap.

## Output
Choose registered WordPress image sizes + WPE custom rendition. Default does not watermark every size blindly.

Quality:
- inherit WordPress/editor default;
- explicit 1–100 advanced;
- format conversion only through Media adapter and never implicit.

Metadata:
- preserve/remove EXIF follows site/privacy/media policy, not watermark feature default.

## Preview
Uses non-destructive temp/preview rendering. Shows selected representative image dimensions and exact rule match reason.

## Batch regenerate
- Query/selection;
- affected count;
- overwrite derived watermark output allowed;
- original untouched;
- batch/job;
- pause/resume/cancel;
- failed item report.

## Remove watermark
Deletes/recreates only WPE-generated derived renditions or regenerates from original. Never tries lossy reversal of modified original because original invariant prevents need.

## CDN/offload
Adapter must ensure source retrieval and derived upload. Local-only operations marked incompatible if media offload hides file path.

## Tests
- original checksum unchanged;
- EXIF orientation;
- transparent PNG;
- huge image memory;
- SVG malicious content;
- offload failure;
- batch resume.

---

# 29. XML-RPC Manager — Pro

## Principle
Expose what WordPress XML-RPC hooks can actually control. Do not label `xmlrpc_enabled` as a universal endpoint kill switch.

## Overview
Shows:
- XML-RPC endpoint URL;
- authenticated XML-RPC enabled/filter result;
- pingback method availability;
- method count;
- detected compatibility integrations when possible;
- Protector/rate-limit integration state.

## Method inventory
Runtime method list with:
- method name;
- category;
- source hint;
- auth requirement known/unknown;
- current allowed state;
- override source.

## Method rule
Modes:
- inherit;
- allow;
- deny.

Applying allow/deny uses `xmlrpc_methods`-style filtering where supported. Unknown third-party methods preserved until explicit deny.

## Categories/presets
Categories are convenience selectors, but UI expands exact methods before save.
Potential categories:
- system/demo;
- posts/pages;
- media;
- comments;
- users/profile;
- pingbacks;
- Blogger/MetaWeblog legacy;
- third-party.

Presets:
- Compatibility/default;
- Disable pingbacks;
- Restrictive authenticated publishing;
- Custom.

No “Disable all XML-RPC” preset claims completeness unless implementation uses an earlier request-level block and compatibility impact is explicit.

## Request controls
Where integrated through Protector/server hooks:
- rate limit;
- IP/CIDR rule;
- max request size;
- XML parser limits;
- logging level.

XML external entities disabled by parser/platform defaults where controllable; never re-enable.

## Pingback
Separate toggle disables pingback methods and related exposure according to supported hooks. Explain possible DDoS abuse context without claiming all attacks solved.

## Compatibility
Warnings for Jetpack/mobile/remote publishing if methods needed. Detection is advisory; user can override knowingly.

## Logs
Off by default or metadata-only because XML-RPC payloads can contain credentials/content.
Fields when enabled:
- method;
- authenticated/anonymous result class;
- user ID if safely known;
- status/error;
- duration;
- source IP privacy policy;
- no password/body storage.

## Tests
- `xmlrpc_enabled` wording/behavior;
- denied method removed;
- pingback disabled;
- Jetpack compatibility fixture where feasible;
- rate limit/proxy;
- sensitive payload never logged.

---

# Operations & Protection specification status

These modules are **Specified at Phase 0 behavioral level**. Backup archive/encryption, cross-server restore, protected local storage, login-alias compatibility, image-library/font support and XML-RPC compatibility require implementation spikes/ADRs before their relevant code ships.
