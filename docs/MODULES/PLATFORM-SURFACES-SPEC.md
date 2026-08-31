# WPEssential — Platform Surfaces Detailed Specification

Status: **Phase 0 — specified with service API/auth blockers**

Applies `COMMON-OPTION-CONTRACTS.md`. These surfaces are platform UX, not separately sellable modules.

---

# 31. WPEssential Home / Modules / Onboarding / Account / Docs / Changelog / Support / Diagnostics

## 1. First-run wizard

The wizard appears after successful activation only when onboarding state is incomplete. It never blocks wp-admin globally if the wizard fails.

### Step 1 — Welcome
Controls:
- product summary;
- current Free/Pro package detection;
- Continue button;
- Skip setup / Continue Free action;
- documentation/privacy links.

No account request is required to use Free CPT/Taxonomy.

### Step 2 — Account choice
Options:
- Continue Free without account;
- Sign in;
- Create account;
- Recover password.

Remote account forms must clearly state data sent to WPEssential service before submission.

### Step 3 — Sign in
Fields:
- email/username according to service API;
- password;
- remember connection/session behavior according to final auth protocol;
- sign-in button;
- forgot password;
- switch to sign up;
- service status/error area.

Passwords are sent only to the authenticated WPEssential service over HTTPS and are never stored in WordPress options/logs by WPEssential. Preferred architecture is token/session exchange rather than retaining account password locally.

### Step 4 — Sign up
Fields depend on service contract but must be minimal:
- name if required;
- email;
- password/confirmation if service directly owns credentials;
- terms/privacy acknowledgement when legally required;
- marketing consent separate and optional;
- create account;
- already have account.

No dark-pattern prechecked marketing consent.

### Step 5 — Pro/trial offer
Shown only when service reports eligible offer.

Display:
- trial duration from API, not hardcoded when plans are remotely managed;
- modules/features included;
- trial end/billing semantics;
- whether payment method is required;
- Continue Free;
- Start Trial / View Plans.

WordPress.org Free plugin never contains hidden locked premium code solely for trial activation.

### Step 6 — Pro add-on acquisition
If user chooses Pro/trial:
- service returns entitlement/package metadata;
- installation/update mechanism must satisfy WordPress security/distribution rules;
- package signature/checksum verification strategy required before implementation;
- filesystem credentials flow follows WordPress conventions;
- explicit install/activate result;
- recovery if download/install fails.

Never execute an arbitrary package URL supplied by untrusted response without trusted-origin/signature validation.

### Step 7 — Module preset
Options:
- Recommended essentials;
- Content/Data suite;
- Membership/Portal suite;
- Automation suite;
- Custom selection.

Preset is only UI convenience; it expands exact module enable/disable choices before finish. Required dependencies auto-selected and explained.

### Step 8 — Finish
Shows:
- enabled modules;
- Free/Pro state;
- account state;
- next recommended action;
- go to WPEssential Home.

Wizard completion is local state and does not imply all remote account setup succeeded.

---

## 2. WPEssential Home

### Cards
- setup/onboarding progress;
- module health;
- latest backup state if module enabled;
- workflow/job failures if enabled;
- membership sync/access warnings if enabled;
- license/account summary if connected;
- updates/changelog;
- documentation search;
- support shortcut;
- recent audit/activity summary subject to permission.

Cards appear only when relevant module/service exists. Empty Home must not show broken placeholders for disabled Pro modules.

### Quick actions
Potential actions:
- create CPT;
- create taxonomy;
- create field group;
- create query;
- create form;
- create membership plan;
- run backup;
- diagnostics.

Each action is capability and entitlement checked; unavailable action shows why rather than silently disappearing when discoverability is useful.

---

## 3. Modules screen

### Module card fields
- icon/name;
- description;
- edition Free/Pro/Platform;
- installed/available state;
- enabled/disabled/degraded/read-only/paused/unhealthy state;
- version;
- dependencies;
- last error/health badge where relevant;
- Settings/Open action;
- Docs action;
- Enable/Disable;
- Upgrade/Install Pro when relevant.

### Filters
- All;
- Enabled;
- Disabled;
- Free;
- Pro;
- Needs attention;
- suite/category.

### Enable
Preflight:
- required package installed;
- WPE version compatibility;
- dependency modules;
- migrations;
- license entitlement where Pro.

Enable is transactional where practical: if migration/init fails, module returns prior safe state and diagnostic.

### Disable
Shows:
- dependencies that will degrade;
- background work effect;
- whether minimal enforcement remains for access/security module;
- data retained statement.

Disable never means delete data.

### Delete data
Not a card shortcut. Lives in module advanced/uninstall area with impact preview and high confirmation level.

---

## 4. Account & License

## Account connection states
- disconnected;
- connecting;
- connected;
- token expired/auth required;
- service unreachable;
- account suspended/restricted if service reports;
- local Pro entitlement cached/degraded.

### Account summary
- display name/email safe summary;
- account ID opaque reference where useful;
- connection time;
- service region/environment if relevant;
- disconnect.

Do not display access/refresh tokens.

### License/entitlement summary
- product/plan name;
- status;
- trial status/end;
- subscription/renewal date if service owns it;
- site activation identifier;
- entitled module groups;
- last verified timestamp;
- refresh entitlement;
- manage billing/renew link to trusted service;
- disconnect/deactivate site if supported.

### Offline/grace
Remote entitlement checks cannot make site runtime depend on every page request.
- cache signed/validated entitlement response;
- bounded grace/offline policy defined by commercial/security ADR;
- last-known security/access enforcement continues;
- admin shows stale verification warning.

### Plans screen
Plans loaded dynamically from WPEssential service.
Fields rendered only from trusted schema/allowlisted properties:
- name;
- billing interval;
- price/currency display provided by service;
- features/module entitlements;
- trial eligibility;
- current plan;
- CTA.

Checkout/purchase occurs through trusted service/commerce flow; WordPress plugin does not collect raw payment-card data.

### Renew/upgrade/downgrade
Plugin initiates trusted service URL/API session. Final billing confirmation belongs to service. Local entitlement changes only after verified service response/webhook/poll.

---

## 5. Documentation

### Local vs remote
- bundled quick-start and version-critical docs can ship locally;
- full docs may load/search via service/browser link;
- remote failure leaves local help usable.

### Search
- query text;
- module/category;
- current WPE version context;
- results title/summary/source/version;
- open article.

No arbitrary remote HTML injection into wp-admin. Remote content is sanitized/rendered through defined schema or opened on trusted docs site.

### Contextual help
Module screens can deep-link to documentation article IDs, not hardcoded fragile URLs where service can resolve version-aware links.

---

## 6. Changelog

### Data
- installed version;
- latest known version;
- release date;
- categories Added / Changed / Fixed / Security / Deprecated / Removed;
- migration notes;
- compatibility notes;
- read-more link.

Security entries may intentionally omit exploit detail until disclosure timing permits, but must not falsely claim issue fixed without release evidence.

### Source
Bundled changelog for installed release is authoritative offline. Remote service can show newer release metadata.

---

## 7. Support Tickets

Remote support system is source of truth. Local UI is a client/cache, not a separate incompatible ticket database.

### Ticket list
Columns:
- ticket number/reference;
- subject;
- category;
- status;
- priority if service supports;
- last reply;
- updated;
- unread state.

Filters:
- open/closed/all;
- status;
- category;
- date;
- search subject/reference.

### Create ticket
Fields:
- subject required;
- category required if service defines;
- priority optional/service-controlled;
- description required;
- related module/version auto-suggested but previewed;
- diagnostics attachment optional only after explicit selection/preview;
- file attachments.

### Attachments
- MIME allowlist;
- file count/size bounded by service + local server;
- no executable scripts;
- filename sanitized;
- malware/provider scanning may occur service-side;
- upload errors per file;
- private authenticated download.

### Diagnostics attachment
Before upload show exact fields/files collected and redacted. Default off.

Never auto-send:
- database dump;
- user/customer records;
- passwords/tokens;
- full `wp-config.php`;
- raw logs likely containing secrets;
- plugin/theme source.

### Ticket thread
- message author/time;
- sanitized message content;
- attachments;
- status changes;
- reply form;
- close/reopen.

### Reply
- text/rich-safe body;
- attachments;
- optimistic UI only after service confirms;
- retry must avoid duplicate reply using request id/idempotency if service supports.

### Delete
Remote permanent ticket deletion is not promised. Local unsent draft can be deleted. Service retention governs submitted tickets.

---

## 8. System Status / Diagnostics

### Environment
- WordPress version;
- PHP version/SAPI;
- DB server/version;
- web server summary when safely known;
- site/multisite;
- memory limits;
- max execution/upload/post sizes;
- timezone;
- HTTPS;
- filesystem method/write checks.

### WPEssential
- Free version;
- Pro version;
- schema versions;
- enabled modules;
- module health;
- migrations pending;
- job queue/backlog;
- WP-Cron/system runner health;
- REST loopback;
- Abilities availability;
- asset/build manifest health;
- service/account connectivity;
- integration health summary.

### Security/redaction
Diagnostics never expose:
- AUTH salts;
- DB password;
- secret values;
- OAuth tokens;
- Application Passwords;
- full user PII.

Paths may be normalized/redacted for export if exposing absolute server paths is unnecessary.

### Copy/download report
- human-readable text/JSON diagnostic package;
- generated timestamp/version;
- explicit redaction summary;
- user can review before attaching to support.

### Repair actions
Only registered safe operations:
- refresh rewrite rules when justified;
- rebuild definition cache;
- re-run safe migration;
- rebuild entitlements;
- retry failed jobs;
- refresh remote entitlement;
- validate storage connections.

Each repair shows impact and authorization.

---

## 9. Remote service contract

All WPEssential account/license/plans/support/update-service calls require:
- HTTPS;
- trusted base host allowlist/configuration;
- explicit timeout;
- versioned API;
- authenticated token where needed;
- secrets in Vault;
- response schema validation;
- rate-limit handling;
- retry only where idempotent;
- correlation/request ID;
- safe local caching;
- privacy-aware logs.

The remote service must not return executable PHP/JS to run in WordPress.

Package/update metadata requires stronger trust controls than normal content APIs: signed package/release verification strategy is an implementation blocker.

---

## 10. Platform acceptance tests

- Free onboarding works fully offline/no account;
- wizard failure does not trap wp-admin;
- account password/token never logged;
- expired remote session recovery;
- remote service outage with cached local state;
- Pro trial/expiry does not affect Free modules;
- module enable dependency failure rolls back/degrades safely;
- access/security module disable warning/enforcement contract;
- plan API malformed response rejected;
- support attachment MIME/size enforcement;
- diagnostics redaction snapshot;
- duplicate ticket reply retry safety;
- docs remote HTML cannot inject scripts;
- package URL/trust validation;
- keyboard/focus accessibility of wizard/dialogs.

---

# Platform surface specification status

These platform surfaces are **Specified at Phase 0 behavioral level**. Final account authentication/token protocol, entitlement signature/offline grace, package update signing, support API schema and service privacy/retention contract require dedicated server/API design before implementation.
