# WPEssential — Protector Exhaustive Option Specification

Status: **Phase 0 — Exhaustive Option Spec / planning only / no implementation authorized**  
Edition: **Pro**

Protector is an access-control/hardening orchestration module, **not** a full WAF, malware scanner or hosting firewall.

## 1. Security principle
- UI hiding is never authorization.
- Login URL obfuscation is not authentication.
- IP allow/deny is supplemental, not identity.
- Membership/Role/Capability checks compose with the shared Policy Engine.
- XML-RPC policy remains owned by XML-RPC Manager.
- REST route authorization remains owned by each REST/Ability definition; Protector can add an outer deny/rate rule but cannot grant missing permission.

---

# 2. Screens

## 2.1 Overview
Cards:
- Site Gate state;
- active rules;
- blocked requests recent count;
- login protection state;
- recovery readiness;
- trusted proxy configuration health;
- XML-RPC delegated status;
- high-risk warnings;
- rate limiter health;
- latest policy change;
- security logs retention status.

Quick actions:
- Enable maintenance/private Site Gate;
- Create Rule;
- Review Lockout Risk;
- Test Current Access;
- Recovery Instructions;
- View Logs.

## 2.2 Rules list
Columns:
- name;
- status;
- priority;
- scope;
- subject summary;
- effect;
- schedule;
- hit count optional/retention-aware;
- last matched;
- health/conflict;
- actions.

Filters:
- enabled/disabled;
- effect allow/deny/challenge/redirect;
- site/frontend/admin/login/REST/custom resource;
- subject type;
- scheduled;
- conflicts;
- date.

Actions:
- Edit;
- Duplicate;
- Test/Simulate;
- Enable/Disable;
- View matches/logs;
- Export;
- Archive/Delete.

Bulk:
- enable/disable only for safe groups;
- archive;
- export.

No bulk high-risk recovery/login-alias action.

## 2.3 Site Gate
Dedicated simple management surface for common whole-site protection.

## 2.4 Login / Admin Protection
Dedicated surface for login alias, admin access policy, rate limits and recovery.

## 2.5 Rate Limits
Policies and current blocks.

## 2.6 Security Headers
Safe helper/preset surface.

## 2.7 Logs / Blocks
Privacy-aware event list.

## 2.8 Recovery
Read-only instructions + carefully authorized recovery state management.

---

# 3. Site Gate exhaustive options

## Mode
- Off default;
- Shared password;
- Authentication required;
- Role/Capability policy;
- Membership/Entitlement policy;
- Combined policy/Condition Engine;
- Maintenance/private-site mode with custom renderer.

## Scope
- entire frontend;
- selected post types;
- selected routes/path groups;
- exclude public resources;
- REST unaffected by default unless explicitly included;
- feeds;
- search;
- sitemaps;
- media direct delivery only where Protector actually owns/proxies resource; never claim protection of public static files it cannot intercept.

## Exclusions
- login/lost-password by default where needed for recovery;
- current admin/recovery session;
- health/loopback endpoints required by WP when safely identifiable;
- payment/webhook callbacks only through explicit adapters, never blanket bypass;
- robots/sitemap if configured public;
- selected IP/network;
- selected paths/resources.

Every exclusion is visible in preview.

---

# 4. Shared password gate

Fields:
- password set/replace; never reveal existing;
- confirm password;
- hint optional but warning against exposing password-derived hint;
- session duration default candidate 24h;
- remember access toggle;
- cookie scope path/domain automatic safest default;
- secure cookie required on HTTPS;
- HttpOnly on;
- SameSite candidate Lax by default;
- invalidate all gate sessions action;
- session generation/version;
- max failed attempts;
- failure window;
- temporary block duration;
- CAPTCHA adapter optional after threshold;
- bypass authenticated administrators default on with dedicated capability;
- custom gate title/message/logo only safe local media;
- custom footer/privacy link;
- response code for gate page;
- noindex default on for protected site.

Password storage:
- one-way password hash appropriate to WP/password library;
- plaintext never in options/logs/export.

---

# 5. Rule editor

## Identity
- name;
- key;
- enabled;
- description;
- priority;
- tags;
- schedule.

## Scope types
- whole site/frontend;
- exact URL path;
- path prefix;
- bounded wildcard pattern;
- advanced regex only if separately accepted/validated;
- WP post/resource;
- post type;
- taxonomy/term;
- WPE Dashboard route;
- wp-admin screen/route group;
- login surface;
- REST route family/exact route as outer policy only;
- registered Ability action;
- XML-RPC delegated policy reference;
- custom registered resource type from SDK.

URL matching uses normalized path, not raw untrusted Host header semantics.

## Subject types
- anonymous;
- authenticated;
- specific users;
- roles;
- capabilities;
- membership plans;
- entitlements;
- team membership;
- IP address;
- CIDR;
- trusted network group;
- Query/Policy-derived principal group only if bounded;
- request conditions through shared Conditions.

## Conditions
Candidate safe facts:
- authentication state;
- capability;
- membership/entitlement;
- route/resource;
- request method;
- site/network scope;
- schedule/time window;
- IP/network;
- selected request headers only through allowlisted predicates;
- country/geo only via explicit provider integration, not assumed built-in;
- environment flag staging/production if configured;
- user/device remembered gate session.

Do not allow arbitrary PHP expressions.

## Effect
- Deny;
- Allow only within Protector layer;
- Require authentication;
- Require Site Gate password;
- Redirect to login/local page;
- Conceal as 404;
- Custom protected renderer/message;
- Rate-limit/challenge action through registered adapter.

An Allow cannot bypass an earlier WordPress/core/WPE capability denial.

## Response
- status 401/403/404 according semantic;
- local redirect target;
- external redirect advanced off by default + allowlist;
- custom message/template;
- JSON error for REST/API;
- `Retry-After` for rate limit where applicable;
- cache-control private/no-store where sensitive;
- no sensitive policy details to anonymous visitor.

## Schedule
- always;
- start/end UTC rendered local;
- recurring local time windows;
- weekdays;
- maintenance window;
- DST preview.

Expired rules remain visible/disabled by schedule, not silently deleted.

---

# 6. Rule precedence / explainability

Candidate order:
1. emergency recovery bypass with strict context;
2. immutable platform/core safety requirements;
3. exact-resource rules;
4. subresource/action rules;
5. broader type/path rules;
6. site defaults.

At same specificity explicit deny wins unless dedicated recovery override applies.

Admin simulation must answer:
- matched rules;
- normalized request/resource;
- subject facts;
- winning rule;
- final Protector decision;
- outer WordPress/Module policy result where available;
- redirect/response outcome.

---

# 7. wp-admin controls

Modes:
- no additional admin restriction default;
- require login (already WP baseline, shown as informational);
- capability-based screen restrictions;
- role/profile-based menu visibility delegated to Admin Menu Builder for presentation;
- restrict specific WPE/high-risk screens through policy;
- network admin separately governed.

Warnings:
- blanket `/wp-admin` blocking can break AJAX/admin-post/plugin callbacks;
- `admin-ajax.php` and `admin-post.php` cannot be disabled globally without route/action awareness;
- REST used by Gutenberg/builders must not be blanket blocked.

Controls:
- selected screens/actions;
- subject policy;
- recovery administrator exemption;
- AJAX action allowlist/denylist only through registered adapter metadata;
- audit denied admin requests.

---

# 8. Login alias / login surface

Positioned as noise reduction.

Controls:
- enable alias;
- alias slug;
- block/redirect original `wp-login.php` direct navigation behavior;
- preserve POST/auth mechanics according compatible strategy;
- lost-password link rewrite;
- register link rewrite;
- logout URL rewrite;
- password reset key URLs;
- multisite/network login handling;
- WooCommerce/account login unaffected unless explicitly integrated;
- XML-RPC authentication unaffected unless XML-RPC Manager changes it;
- Application Passwords/REST unaffected;
- recovery bypass mechanism;
- compatibility test status.

Validation:
- reserved WP paths;
- current page/slug conflict;
- redirect loop;
- invalid characters;
- alias cannot equal blocked original route semantics.

Change alias requires active session/re-auth + recovery instructions.

---

# 9. Login protection

Controls:
- failed login rate limit enable;
- attempt count;
- rolling/fixed window;
- temporary block duration;
- identifier key: IP / username+IP / account-sensitive strategy;
- unknown-user behavior normalized to avoid enumeration;
- trusted network bypass optional;
- administrator lockout policy;
- CAPTCHA/challenge adapter threshold;
- notification on suspicious threshold;
- reset block action;
- block list pagination/search;
- retention.

Do not create permanent automatic IP bans by default.

---

# 10. Rate Limit policy

Targets:
- WP login;
- Site Gate;
- selected REST route;
- selected WPE Ability;
- XML-RPC delegated target;
- form submit via Forms module;
- chat/send action;
- webhook receive endpoint where owner adapter permits;
- custom registered action.

Fields:
- policy name;
- enabled;
- target;
- method/action;
- key strategy;
- limit;
- interval/window;
- burst if algorithm supports;
- temporary block duration;
- response code;
- `Retry-After` behavior;
- subject bypass;
- trusted networks;
- logging sampling;
- storage backend health.

Algorithms are implementation decision; UI semantics must not promise strict distributed limits unless backend supports them.

Concurrency correctness required after implementation consent.

---

# 11. Client IP / trusted proxies

Default:
- direct remote address only.

Proxy mode controls:
- enable trusted proxy parsing;
- trusted proxy CIDRs list;
- supported forwarding header selected from allowlist according deployment (`X-Forwarded-For`, standardized `Forwarded`, provider adapter header);
- trusted hop strategy;
- test current request chain;
- diagnostics displays resolved client IP + trusted proxy path safely;
- invalid/spoofed header ignored.

Never trust forwarded headers merely because they exist.

---

# 12. IP/CIDR lists

Named network groups:
- name;
- entries IPv4/IPv6/CIDR;
- notes;
- expiration optional;
- source manual/import;
- enabled;
- usage count;
- validate overlap/duplicates.

Import:
- preview;
- invalid entries report;
- dedupe;
- no remote threat feed ingestion unless separately accepted integration exists.

---

# 13. Security headers helper

Presets are advisory and conflict-aware.

Potential controls:
- `X-Content-Type-Options: nosniff`;
- Referrer-Policy selection;
- Permissions-Policy structured directives;
- frame protection: CSP `frame-ancestors` preferred direction / legacy X-Frame-Options compatibility note;
- HSTS enable only HTTPS-safe + max-age/includeSubDomains/preload warnings;
- CSP Report-Only / Enforce advanced;
- CSP directives structured, not arbitrary concatenated string in beginner mode;
- report endpoint only if actual receiver exists;
- selected frontend/admin scope;
- duplicate header detection status.

Do not auto-enable HSTS preload or strict CSP.

Server/CDN ownership warning if headers are already set upstream.

---

# 14. REST/API protection

Protector can:
- outer deny route family;
- require extra subject/policy;
- add rate-limit policy;
- log denied attempts;
- disable discovery exposure only if semantically safe.

Protector cannot:
- grant access that route permission callback denied;
- replace nonce/application-password/OAuth authentication;
- disable all REST on Gutenberg-heavy site without impact warning.

Route preview shows registered dependent modules/builders if known.

---

# 15. XML-RPC integration

Protector delegates to XML-RPC Manager:
- global authenticated-method state;
- pingback method state;
- method allow/deny;
- per-method rate-limit hooks.

Protector may surface summary/deep link but does not maintain duplicate XML-RPC truth.

---

# 16. Recovery architecture

Required recovery paths before high-risk rule activation:
- recovery admin capability/session;
- documented `wp-config.php`/server-side safe-disable constant candidate;
- plugin deactivation behavior does not corrupt data;
- optional time-limited recovery token only if accepted design exists;
- no permanent secret query-string bypass;
- Site Health/diagnostic state.

Rule publish preflight detects:
- operator would lock own current route;
- login + recovery both blocked;
- admin/API dependencies;
- redirect loop;
- all known admins excluded.

High-risk publish may require re-auth and acknowledgement.

---

# 17. Logs

Fields:
- event ID;
- timestamp;
- normalized action/resource/path;
- matched rule ID;
- result;
- principal/user ID if authenticated;
- IP handling according privacy policy;
- rate-limit key hash where appropriate;
- response class;
- correlation ID;
- user agent category only if needed, not full fingerprint by default.

Controls:
- logging enabled;
- retention;
- anonymize/truncate IP after period;
- include allowed requests off by default;
- denied/security events default candidate on with bounded retention;
- export capability;
- erase/anonymize integration where applicable.

No passwords, cookies, auth headers, query secrets or full request bodies.

---

# 18. Permissions

Candidate:
- `wpe_protector_read`
- `wpe_protector_rule_create`
- `wpe_protector_rule_update`
- `wpe_protector_rule_delete`
- `wpe_protector_publish`
- `wpe_protector_view_logs`
- `wpe_protector_manage_rate_limits`
- `wpe_protector_manage_networks`
- `wpe_protector_manage_headers`
- `wpe_protector_manage_login_alias`
- `wpe_protector_recovery_manage`
- `wpe_protector_bypass`

Recovery/bypass/login-alias/high-impact header permissions are separate from ordinary rule editing.

---

# 19. Abilities

- `wpessential/protector.rule_list/get/create/update/delete/validate/publish`
- `wpessential/protector.simulate`
- `wpessential/protector.site_gate_status`
- `wpessential/protector.rate_limit_status`
- `wpessential/protector.block_list`
- `wpessential/protector.recovery_status`

AI default:
- read/simulate/explain only;
- publish/recovery/network/header/login changes disabled by default.

---

# 20. Events

- protector.rule.published/disabled/deleted;
- protector.request.denied sampled/audit class;
- protector.rate_limit.triggered;
- protector.site_gate.enabled/disabled;
- protector.recovery.used;
- protector.login_alias.changed;
- protector.proxy_config.changed;
- protector.health.degraded/recovered.

---

# 21. Empty/error/degraded states

- no rules;
- Site Gate off;
- recovery not configured;
- trusted proxy misconfigured;
- rate-limit backend unavailable;
- redirect loop detected;
- current user lockout risk;
- CSP/header conflict;
- XML-RPC ownership delegated/unavailable;
- module/license expired: last-known security enforcement must not silently disappear according license-expiry contract;
- unsupported server behavior;
- logs disabled/expired.

---

# 22. Performance

- compile published rules into efficient match structure;
- do not scan every rule/definition on every request;
- exact/path/type indexes/caches;
- request-local memoization;
- versioned cache invalidation;
- rate-limit writes bounded;
- logs sampled/async where safe but deny decision must not depend on log write success;
- no remote service call in request authorization path.

---

# 23. Required tests after development consent

- UI hidden/direct request authorization parity;
- exact/prefix/wildcard normalization;
- rule specificity/deny precedence;
- current-admin lockout preflight;
- recovery path;
- login/lost-password/reset/logout flows;
- multisite login/network admin;
- REST/Gutenberg compatibility;
- `admin-ajax`/`admin-post` compatibility;
- Membership composition;
- direct/static media limitation correctly reported;
- forwarded-header spoofing;
- IPv4/IPv6 CIDR;
- rate-limit race;
- NAT false-positive behavior;
- redirect/open-redirect/loop;
- CSP/HSTS unsafe activation warning;
- license/module degradation preserves protection;
- no credential leakage in logs.

## Maturity
**Exhaustive Option Spec.** Exact request interception, rate-limit storage algorithm, recovery constant/token design, CSP/header compatibility and server/proxy certifications remain implementation/evidence decisions requiring explicit owner development consent.