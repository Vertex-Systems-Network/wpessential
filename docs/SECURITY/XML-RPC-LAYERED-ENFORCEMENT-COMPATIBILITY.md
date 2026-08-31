# WPEssential — XML-RPC Layered Enforcement & Compatibility Model

Status: **Phase 0 security/compatibility architecture / no implementation authorized**  
Related: XML-RPC Manager Exhaustive Spec, Protector ADR-0045, ADR-0014.

## Core principle

WordPress XML-RPC exposure has multiple distinct layers. WPEssential must never label XML-RPC “disabled” merely because `xmlrpc_enabled` is false.

Core WordPress behavior verified for planning:
- `xmlrpc_enabled` controls methods requiring authentication;
- it does not by itself disable pingbacks or other custom/non-authenticated methods;
- `xmlrpc_methods` filters the actual method registry and can add/remove methods;
- core registers pingback methods in the XML-RPC method table;
- plugins can add methods through the same registry filter.

Therefore WPE models endpoint reachability, registered methods, authenticated-method policy, pingback policy and outer request gating separately.

## Enforcement layers

### Layer 0 — Host/CDN/Web Server

The host, WAF, CDN or reverse proxy may block `xmlrpc.php` before WordPress runs.

WPE can diagnose/suspect this state but cannot claim its method-level policy executed when WordPress never received the request.

States:
- reachable;
- externally blocked;
- externally rate-limited/challenged;
- inconclusive.

### Layer 1 — Protector outer request gate

ADR-0045 can deny or rate-limit the XML-RPC HTTP surface before method processing according to trusted-proxy/client policy.

A Layer-1 deny means WPE intentionally blocks requests from reaching XML-RPC handling. It is stronger than removing individual methods but can break integrations that require endpoint reachability.

### Layer 2 — XML-RPC method registry policy

WPE filters the effective method map after WordPress/core/plugin method registration semantics are understood.

Rule result for every actual method:
- allowed;
- denied;
- inherited;
- unreviewed/custom;
- compatibility-required by a certified integration profile.

This is the primary granular control for both built-in and plugin-added methods.

### Layer 3 — Authenticated XML-RPC enable state

`xmlrpc_enabled` is represented accurately as **Authenticated Methods Enabled/Disabled**, not master endpoint state.

When disabled, methods that rely on server login/authentication are denied by WordPress's XML-RPC server semantics. This does not remove pingback/custom unauthenticated methods from the registry.

### Layer 4 — Method-native WordPress authorization

For methods that remain exposed:
- core/plugin authentication runs normally;
- WordPress capabilities/object checks remain authoritative;
- WPE does not replace them with a weaker XML-RPC role switch.

A method being allowlisted does not grant permission to perform it.

## Effective policy resolution

Conceptual order:

`Host/WAF → Protector surface rule → Effective XML-RPC method registry → authenticated-method state → method-native auth/capability → execution`

WPE diagnostics explain the first layer that caused deny/degraded behavior.

## Complete Deny preset

WPE's **Complete Deny** means:

1. WPE policy denies/removes every method present in the effective XML-RPC method registry, including pingback and custom methods where the registry hook can control them;
2. Protector can optionally/strongly deny the `xmlrpc.php` request surface as part of the preset when the administrator wants endpoint-level blocking;
3. diagnostics verify no callable registered method remains under the tested WordPress/plugin stack;
4. external host/WAF state is reported separately;
5. compatibility impact is previewed before apply.

`xmlrpc_enabled = false` alone never satisfies WPE's Complete Deny definition.

### Complete Deny verification language

Before runtime certification WPE may say:
- `Configured to deny all discovered XML-RPC methods`.

Only after executable method-inventory/request fixtures pass may UI say:
- `All registered XML-RPC methods blocked by WPE for this tested configuration`.

Do not make an impossible universal claim that no earlier/later third-party code or host behavior can ever re-expose XML-RPC without runtime evidence.

## Pingback profile

Pingback controls are separate:
- `pingback.ping`;
- `pingback.extensions.getPingbacks`;
- Protector/rate-limit policy;
- logging/observability.

Core `pingback.ping` performs an outbound source fetch using WordPress safe HTTP behavior. WPE still treats it as a distinct abuse/compatibility surface and can block/rate-limit it independently.

Disabling post ping/trackback settings is not assumed identical to removing XML-RPC pingback methods.

## Method inventory

WPE inventory is based on the effective registered method set for the running site, not only a static hard-coded WordPress list.

For each method capture safe metadata:
- method name;
- core/custom/unknown owner hint;
- family;
- authentication expectation known/unknown;
- read/write/pingback/discovery classification;
- WPE effective rule;
- certified integration requirements;
- observed use only when logging enabled.

Unknown plugin methods remain visible. They are not silently categorized as safe or malicious.

## Custom methods and policy modes

### Denylist mode

New/unknown methods are allowed unless matching deny policy; UI warns that future plugin-added methods may be exposed.

### Allowlist mode

New/unknown methods default denied unless explicitly/certifiably allowed; safer for locked-down installations but higher compatibility risk.

### Complete Deny

New effective registered methods also resolve denied dynamically; inventory/diagnostic marks unexpected additions.

## Compatibility profiles

Profiles are adapter/certification data, never guessed permanent method lists.

Candidate profiles:
- WordPress Default;
- Pingbacks Off / Publishing On;
- Authenticated Publishing Off;
- Complete Deny;
- Jetpack-compatible;
- WordPress mobile/remote-publishing compatible;
- custom integration profile.

A provider profile declares:
- integration/plugin and supported version range;
- endpoint reachability requirement;
- method allowlist/requirements if reliably known;
- network/IP requirements only from official machine-readable/provider data where appropriate;
- authentication model notes;
- tests proving connection/critical workflows.

If exact required methods cannot be certified, WPE says the profile requires broader endpoint access rather than inventing a narrow allowlist.

Jetpack currently documents that it relies on XML-RPC for WordPress.com communication, so endpoint blocking can break Jetpack. WPE therefore surfaces a hard compatibility warning when Jetpack is detected and a deny preset is selected.

## Preset impact preview

Before applying a restrictive preset show:
- current → proposed allowed method count;
- exact discovered methods being blocked/allowed;
- custom/unreviewed methods;
- detected integrations/plugins that may depend on XML-RPC;
- recent observed method usage if logging was enabled;
- external host/WAF block status;
- Protector changes;
- recovery path.

No one-click hidden destructive hardening without preview.

## Rate limiting

XML-RPC uses ADR-0045 shared atomic Rate Limit service.

Potential scopes:
- endpoint requests;
- authenticated failures per trusted client IP;
- method family;
- pingback method;
- user/account after safely identified;
- certified integration principal where reliable.

Do not parse spoofable proxy headers independently inside XML-RPC module.

## Parser / request complexity

WordPress exposes `xmlrpc_element_limit` for the number of parsed XML-RPC elements.

WPE can expose a bounded parser-element limit control only after supported WordPress-version behavior is tested.

Other request body/HTTP size/time limits may belong to:
- web server/WAF;
- Protector;
- WordPress/PHP environment.

Do not invent an unverified “XML request byte limit hook” if WordPress does not provide one.

## Logging

Default is privacy/security-minimized metadata:
- timestamp;
- method;
- allow/deny/fault result category;
- trusted client-network hash/metadata only if enabled;
- authenticated user ID only where legitimately known and required;
- request/correlation ID;
- safe fault class.

Never log:
- passwords;
- raw XML request bodies by default;
- auth tokens;
- full sensitive method arguments.

## Method-call observability limitation

Core `xmlrpc_call` is useful for many built-in authenticated method calls, but WPE must not assume it is a universal pre-execution hook for every custom/pingback method in every integration. Registry-layer policy remains primary enforcement; logging hooks are supplemental and must be certified.

## Multisite

Policy scope must distinguish:
- site-level XML-RPC behavior;
- network-enforced policy;
- integration connected to one site vs network;
- Super Admin configuration authority.

A child site cannot weaken network-enforced Complete Deny without explicit network policy.

Exact network storage/filter ordering remains future evidence.

## Recovery

XML-RPC rules never become the only path to wp-admin recovery.

ADR-0045 WPE recovery mode can disable WPE outer overlays/rules for an authenticated administrator to repair configuration, without creating anonymous auth bypass.

External server/WAF blocks require host-level recovery and are not modified blindly by plugin.

## Failure / degraded states

- external endpoint block;
- XML parser unavailable/error;
- method inventory changed since policy publish;
- third-party method registered unexpectedly;
- certified integration version out of range;
- rate-store unavailable;
- Protector conflict;
- policy prevents detected Jetpack/remote integration;
- loopback self-test unavailable.

## Future executable evidence — NOT AUTHORIZED

- effective core method inventory across supported WP versions;
- plugin-added method before/after WPE filtering;
- `xmlrpc_enabled=false` with pingbacks/custom method still present;
- Complete Deny method calls;
- optional Protector endpoint deny;
- pingback calls/outbound behavior;
- `xmlrpc_element_limit` behavior;
- authentication failure rate limits;
- Jetpack connection profile;
- remote/mobile publishing profile;
- multisite/network policy;
- host/WAF blocked endpoint diagnostics;
- logging redaction;
- rule/filter priority conflicts.

No XML-RPC filter, rate limit, endpoint block or compatibility adapter has been implemented.