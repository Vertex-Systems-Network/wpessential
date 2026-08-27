# WPEssential — Protector Request Gate, Client Identity & Rate-Limit Runtime

Status: **Phase 0 security architecture / no implementation authorized**  
Related: Protector Exhaustive Spec, ADR-0014, Admin/Role recovery architecture.

## Core rule

Protector is an outer request-policy layer. It can restrict entry to selected WordPress surfaces, but it does not replace WordPress authentication/capabilities and does not create anonymous recovery authority.

## Request surfaces

Every Protector rule targets an explicit surface/context:
- public frontend;
- selected public paths/resources;
- `wp-login.php`/login actions;
- wp-admin;
- REST;
- XML-RPC;
- feeds/sitemaps;
- cron/loopback/webhook routes only through explicit integration-aware exceptions;
- registered custom surface.

One giant “block everything” boolean is not the internal policy model.

## Evaluation pipeline

1. establish canonical request context;
2. determine client-network identity through Trusted Proxy policy;
3. resolve emergency WPE recovery bypass state;
4. classify route/surface;
5. apply explicit allow/deny/bypass rules;
6. apply authentication/password gate if configured;
7. apply rate/abuse policy;
8. hand request to WordPress;
9. WordPress capability/Policy remains authoritative for application actions.

A Protector allow never grants a WordPress capability.

## Trusted proxy / client IP

### Baseline

`REMOTE_ADDR` is authoritative unless it belongs to a configured trusted proxy/network adapter.

Headers such as:
- `X-Forwarded-For`;
- `X-Real-IP`;
- provider-specific client-IP headers

are ignored for security decisions unless the immediate peer is in an explicitly trusted proxy profile.

### Proxy profiles

A profile declares:
- trusted proxy CIDRs/addresses;
- accepted forwarding header;
- chain parsing semantics;
- maximum chain length;
- private/reserved-address policy;
- expected provider/network optional.

No “trust all X-Forwarded-For” option in normal UI.

If client identity is ambiguous, security-sensitive allowlisting fails closed or falls back to immediate peer according to the rule; it never guesses the most favorable address.

## Access-rule principals

Rules can match:
- authenticated user/role/capability after WordPress identity exists;
- network address/CIDR from trusted client identity;
- validated gate session;
- explicit path/surface;
- time schedule;
- registered policy resolver.

IP identity is not a user identity and is not used as sole authorization for sensitive WordPress admin actions.

## Password gate

Site/path password is a separate gate credential, not a WordPress user password.

Requirements:
- password stored as a modern one-way password hash, never plaintext;
- submission CSRF/rate protection;
- timing-safe/WordPress-supported verification strategy;
- gate session represented by a server-verifiable random session/token, not raw password in cookie;
- secure/HttpOnly/SameSite cookie policy appropriate to route;
- expiry/idle policy;
- explicit “remember gate” duration;
- logout/revoke all gate sessions;
- no password in logs/URLs.

Gate success only means request passed Protector; protected application content still checks its own authorization.

## Rate Limit Service

Protector, REST, login gates, webhooks and future integrations use one shared Rate Limit service rather than incompatible counters.

A rate-limit definition includes:
- stable policy ID;
- scope/surface/action;
- principal key strategy (trusted IP, user, activation, API credential, composite);
- algorithm profile;
- capacity/limit;
- window/refill period;
- burst behavior;
- penalty/backoff;
- response behavior;
- audit sampling/privacy retention.

### Atomicity requirement

Security counters require an **atomic state adapter**.

Candidate adapters:
1. dedicated DB table/row update with atomic compare/update semantics;
2. certified persistent object-cache adapter providing required atomic add/increment/TTL behavior;
3. provider edge/WAF adapter for selected high-volume cases.

Ordinary WordPress transients are not assumed to be an authoritative atomic security counter across concurrent workers/object-cache implementations.

### Algorithms

V1 can expose a simple bounded fixed-window or token-bucket profile only after concurrency tests. The product contract is the observable capacity/backoff behavior, not a fashionable algorithm name.

Login/brute-force policy may layer progressive cooldown over base rate limits.

## Rate-limit failure modes

Storage unavailable:
- high-risk authentication/write endpoints default fail-closed or conservative according to documented policy;
- low-risk public reads may fail-open with warning only if explicitly safe;
- never silently disable login/brute-force protection without diagnostics.

Clock anomalies and cleanup lag cannot create unlimited access.

## Login URL alias

A login alias is optional routing/UX obscurity, not primary authentication security.

Rules:
- native login flow/cookies/nonces remain WordPress-owned;
- password reset/logout/recovery actions keep working;
- direct legacy path behavior explicit (404/redirect/deny);
- XML-RPC/Application Password/REST auth are separate surfaces;
- WPE recovery bypass can restore native login route without authenticating anyone.

Do not market hidden login URL as brute-force protection by itself.

## Recovery / safe mode

A server-side `wp-config.php` style WPE recovery constant/profile may bypass **WPE Protector overlays only**.

It must not:
- authenticate a user;
- grant administrator capability;
- bypass WordPress password/nonces;
- expose Membership-protected resources as public.

Purpose:
- recover from bad WPE path/menu/protector configuration;
- restore native WordPress login/admin navigation so a legitimate administrator can authenticate and repair configuration.

WP-CLI with normal server authority is the preferred deep recovery path when available.

## REST/XML-RPC delegation

Protector owns outer surface gating/rate policy. REST API Builder and XML-RPC Manager own endpoint/method semantics.

Do not duplicate XML-RPC method filtering inside generic path rules or bypass REST permission callbacks because the outer Protector allowed the request.

## Security headers

Headers such as CSP/HSTS/frame/referrer policies require merge/conflict awareness with host, CDN and security plugins.

Protector must expose:
- effective observed header;
- WPE proposed contribution;
- conflict state;
- report-only where supported;
- safe disable per header.

No blind duplicate CSP that breaks wp-admin/frontend.

## Audit/privacy

Audit security decisions with normalized reason codes but minimize IP/PII retention.

Never log:
- gate passwords;
- auth cookies;
- Authorization headers;
- reset tokens;
- Vault secrets.

## Future executable evidence — NOT AUTHORIZED

- WordPress hook/request ordering for each surface;
- login/reset/logout aliases;
- trusted proxy IPv4/IPv6 chains and spoofing;
- atomic limiter under concurrency/multiple PHP workers;
- object-cache adapters;
- rate-store outage behavior;
- multisite/network admin;
- REST/XML-RPC/cron/loopback/webhook compatibility;
- recovery constant and WP-CLI repair;
- CSP/header conflict fixtures;
- Membership/private route non-bypass.

No request gate, cookie, limiter table or hook has been implemented.