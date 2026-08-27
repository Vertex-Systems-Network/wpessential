# WPEssential — XML-RPC Manager Exhaustive Option Specification

Status: **Phase 0 — Exhaustive Option Spec / planning only / no implementation authorized**  
Edition: **Pro**

## 1. Purpose
XML-RPC Manager makes WordPress XML-RPC exposure understandable and controllable without falsely equating one `xmlrpc_enabled` switch with the entire endpoint.

Important WordPress behavior: the core `xmlrpc_enabled` filter controls XML-RPC methods requiring authentication; it does **not** by itself disable pingbacks or every custom/non-authenticated method. Granular method exposure is available through the `xmlrpc_methods` filter. WPEssential therefore models **endpoint reachability, authenticated methods, pingback methods and per-method policy separately**.

Primary official references for planning:
- WordPress XML-RPC API handbook;
- `xmlrpc_enabled` hook;
- `xmlrpc_methods` hook;
- `wp_xmlrpc_server` reference.

---

# 2. Positioning
XML-RPC Manager is a compatibility/security control surface, not a replacement REST API and not a generic firewall.

It must:
- preserve integrations administrators intentionally rely on;
- expose exact methods and ownership;
- allow safe disable/allowlist/denylist policy;
- coordinate rate limiting with Protector;
- make pingback behavior explicit;
- preserve diagnostics/recovery;
- avoid claiming the endpoint is fully disabled when only authenticated methods are disabled.

---

# 3. Screens

## 3.1 Overview
Cards:
- XML-RPC endpoint detected/reachable;
- authenticated methods policy;
- pingback policy;
- exposed method count;
- WPE-managed blocked method count;
- third-party/custom method count;
- rate-limit policy;
- recent denied/fault activity;
- compatibility warnings;
- external server/CDN block detection where possible.

Quick actions:
- Inspect Methods;
- Apply Preset;
- Test Endpoint;
- Configure Rate Limits;
- View Logs;
- Restore Recommended Defaults.

## 3.2 Methods
Columns:
- method name;
- family WordPress/Blogger/MetaWeblog/MovableType/Pingback/Custom;
- authenticated yes/no/unknown;
- owner/core/third-party hint;
- current state allowed/blocked/inherited;
- rate-limit profile;
- risk/use description;
- last observed call optional;
- actions.

Filters:
- allowed/blocked;
- authenticated;
- pingback;
- core/custom;
- family;
- observed/unobserved;
- conflict/unknown owner.

## 3.3 Presets
## 3.4 Rate Limits
## 3.5 Logs / Diagnostics

---

# 4. Master policy

Do **not** offer one ambiguous `Disable XML-RPC` toggle without explanation.

Top-level modes:
1. **WordPress Default** — WPE does not filter methods.
2. **Disable authenticated XML-RPC methods** — maps conceptually to core authenticated-method enable control; pingbacks/custom unauthenticated methods remain separately governed.
3. **Block selected methods** — denylist.
4. **Allow selected methods only** — allowlist.
5. **Compatibility preset** — curated method policy for known use case when certified.
6. **External endpoint blocked** — informational/degraded state if server/CDN blocks before WordPress; WPE cannot guarantee method-level policy executes.

Mode changes require impact preview.

---

# 5. Endpoint reachability

Status states:
- reachable and WordPress responding;
- reachable but XML-RPC fault/disabled authenticated methods;
- blocked by WPE method policy;
- blocked before WordPress suspected;
- loopback/self-test unavailable;
- endpoint URL customized by server/proxy unknown;
- test inconclusive.

Controls:
- Run local/loopback endpoint diagnostic;
- show endpoint URL;
- show HTTP/status/fault safe summary;
- do not send real credentials during generic health test;
- optional authenticated test only with explicit test account/application credential future and never stored casually.

---

# 6. Method inventory

WPE builds inventory from the methods actually registered in `wp_xmlrpc_server` after filters where introspection is safe, and may compare with known core families.

Each method record/display includes:
- method ID/name;
- family;
- registered handler owner hint;
- core/custom;
- authentication expectation where known;
- mutating/read/pingback classification;
- current effective WPE policy;
- dependency/integration notes;
- last policy change;
- observed usage only if logging enabled.

Unknown/custom methods are never silently assumed safe or malicious.

---

# 7. Method selection controls

Rule modes:
- inherit;
- allow;
- deny;
- rate-limit only;
- deny for selected subjects/network advanced only if request identity is reliably available before method execution.

Selectors:
- exact method;
- method family/prefix only through normalized known group;
- all authenticated methods;
- all pingback methods;
- all core publishing methods;
- all custom methods advanced.

No arbitrary regex method matching in normal mode.

---

# 8. Pingbacks

Separate controls:
- `pingback.ping` allow/deny;
- `pingback.extensions.getPingbacks` allow/deny;
- rate-limit pingback calls;
- logging;
- compatibility warning for features relying on pingbacks/trackbacks.

XML-RPC pingback controls are separate from whether post ping/trackback features are enabled elsewhere in WordPress/content settings.

WPE should not say “XML-RPC disabled” solely because authenticated publishing methods are disabled if pingback methods remain exposed.

---

# 9. Publishing / authenticated method groups

Curated groups may include:
- Posts/pages/CPT read;
- Posts/pages/CPT create/edit/delete;
- Taxonomies/terms;
- Media upload/read;
- Comments;
- Users/profile;
- Options;
- method discovery/capabilities;
- legacy Blogger/MetaWeblog/MovableType compatibility.

Each group expands to actual registered methods in preview. Version drift/new methods appear as Unreviewed until mapping updated.

---

# 10. Presets

Candidate presets:

## WordPress Default
No WPE filtering.

## Hardened — No authenticated publishing
Authenticated XML-RPC methods disabled; pingbacks explicitly configured separately.

## Hardened — No XML-RPC methods accepted by WordPress where controllable
Deny all registered methods through granular method filter strategy, with clear note that server-level endpoint reachability may still return a response and third-party timing/filters need certification.

## Pingbacks Off / Publishing On
Keeps authenticated publishing methods, blocks pingback family.

## Selected Integration
Allowlist only required certified methods for an integration profile.

## Custom
Explicit rule set.

Preset apply preview:
- methods changing allow→deny;
- methods changing deny→allow;
- unknown custom methods;
- detected recent usage;
- compatibility warnings;
- rate-limit changes.

Preset does not hide generated rules.

---

# 11. Third-party/custom method behavior

Controls:
- default custom-method policy under allowlist/denylist mode;
- show owner/plugin hint if discoverable;
- mark trusted integration manually;
- notes;
- exact allow/deny.

If plugin registers method after WPE inventory snapshot:
- effective policy must follow published rule semantics dynamically;
- UI refresh discovers it;
- allowlist default means new unknown method denied candidate;
- denylist default means new unknown method allowed but flagged candidate.

This tradeoff is shown clearly.

---

# 12. Authentication semantics

XML-RPC uses WordPress/core method authentication behavior. WPEssential does not invent a second password store.

Controls may include:
- disable authenticated XML-RPC methods;
- require additional Protector network/rate policy;
- user/role-specific XML-RPC denial only if enforced reliably after authentication and before operation;
- Application Password relationship shown informational where relevant to other APIs, but do not falsely imply all XML-RPC clients use them.

Credentials are never logged.

---

# 13. Rate limiting

Delegates storage/algorithm to Protector Rate Limit service where enabled.

Targets:
- entire XML-RPC request endpoint;
- pingback methods;
- authenticated attempts;
- selected exact method;
- method family.

Fields:
- enabled;
- key strategy IP / authenticated user+IP when reliably known / composite;
- attempts;
- window;
- block duration;
- trusted networks;
- response/fault strategy compatible with XML-RPC;
- logging sampling;
- separate lower thresholds for expensive/high-abuse operations.

Rate-limit failure cannot accidentally convert denied methods to allowed.

---

# 14. Request body/parser protections

Planning controls only where WordPress exposes safe hooks:
- XML element/input complexity limit integration if supported by current core hook;
- maximum HTTP body handled primarily by server/PHP config; WPE can report limits but not promise enforcement above server;
- malformed XML safe fault;
- no entity expansion/custom parser tricks;
- request timeout belongs server/runtime.

Any parser-limit feature must be based on documented current core hooks and tested before marketing.

---

# 15. Logging

Disabled/minimal by default candidate because XML-RPC may contain sensitive payloads.

Safe log fields:
- timestamp;
- method name if parsed;
- allowed/denied/fault;
- authenticated user ID only after successful auth and policy allows logging;
- client IP according privacy/proxy policy;
- HTTP status/fault code class;
- duration;
- request size;
- matched WPE rule;
- correlation ID.

Never log:
- username/password;
- auth tokens;
- raw request body;
- post/media content;
- base64 uploaded media;
- cookies/authorization headers;
- full XML payload.

Retention configurable.

---

# 16. Compatibility dependencies

Potential integrations to test/certify before presets claim support:
- WordPress mobile/remote publishing clients;
- Jetpack/WordPress.com features where XML-RPC may be involved depending current architecture;
- legacy publishing applications;
- external automation tools;
- pingback consumers;
- security/CDN/server blocks.

WPE displays “unknown integration impact” unless actually certified against current versions.

---

# 17. Server/CDN ownership

If Nginx/Apache/CDN/security product blocks `xmlrpc.php` before WordPress:
- WPE shows endpoint externally blocked/inconclusive when detectable;
- method rules remain configuration but may never execute;
- WPE does not attempt to rewrite external firewall rules;
- documentation shows where enforcement appears to occur;
- no duplicate/conflicting ownership claim.

---

# 18. Multisite

Controls/scoping:
- per-site policy default;
- network policy only through dedicated network capability;
- network rule can establish floor/deny that subsite cannot weaken if product adopts network governance;
- Super Admin recovery;
- site-specific logging/usage;
- integrations may operate at network/site boundaries and require tests.

Network semantics remain technical acceptance blocker before support claim.

---

# 19. Permissions

Candidate:
- `wpe_xmlrpc_read`
- `wpe_xmlrpc_manage`
- `wpe_xmlrpc_publish_policy`
- `wpe_xmlrpc_manage_rate_limits`
- `wpe_xmlrpc_view_logs`
- `wpe_xmlrpc_run_diagnostics`
- `wpe_xmlrpc_network_manage`

Changing allowlist/preset can break integrations and requires publish capability + impact preview.

---

# 20. Abilities

- `wpessential/xmlrpc.status`
- `wpessential/xmlrpc.method_list`
- `wpessential/xmlrpc.policy_get`
- `wpessential/xmlrpc.policy_validate`
- `wpessential/xmlrpc.policy_update`
- `wpessential/xmlrpc.test`
- `wpessential/xmlrpc.log_list`

AI default:
- status/list/explain/test-safe only;
- policy mutation disabled by default.

---

# 21. Events

- xmlrpc.policy.published;
- xmlrpc.method.blocked sampled/audit class;
- xmlrpc.rate_limit.triggered;
- xmlrpc.endpoint.health_changed;
- xmlrpc.unknown_method_detected;
- xmlrpc.compatibility_warning_detected.

No raw XML/request payload in generic events.

---

# 22. Empty/error/degraded states

- WordPress default/no WPE policy;
- endpoint externally blocked;
- method inventory unavailable;
- custom methods detected;
- preset stale against newly registered methods;
- rate-limit service unavailable;
- proxy IP resolution unhealthy;
- compatibility dependency unknown;
- loopback test failure;
- WordPress core hook behavior changed in unsupported future version.

---

# 23. Performance

- method policy compiled to hash/set lookup;
- no DB query per method call when cached generation current;
- rate limits bounded;
- logging async/sampled where safe;
- inventory screen pagination if custom method set huge;
- diagnostics never call remote clients automatically.

---

# 24. Required tests after development consent

- core `xmlrpc_enabled` behavior proves authenticated-method scope only;
- pingback remains independently controlled;
- exact method allow/deny;
- allowlist unknown new custom method behavior;
- denylist unknown method behavior;
- third-party registered method;
- publishing client compatibility fixture;
- malformed XML;
- rate-limit race;
- proxy spoof;
- raw body/credential never logged;
- server-level blocked endpoint detection;
- multisite boundary when supported;
- module disable/license expiry policy does not unexpectedly widen exposure;
- direct-request bypass impossible.

## Maturity
**Exhaustive Option Spec.** Exact hook ordering, complete-disable semantics, parser-limit support, network/multisite policy and third-party integration certifications remain implementation/evidence blockers requiring explicit owner development consent.