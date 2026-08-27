# WPEssential — Account-Link OAuth Threat Model & Alternatives

Status: **Phase 0 security planning / no OAuth client or service implementation authorized**  
Related: Remote Service API Contract, ADR-0017, ADR-0018, ADR-0014.

## Goal

Choose a safe account-link architecture for a distributed WordPress plugin that cannot protect a reusable confidential client secret and whose callback URL varies by customer site.

## Security baseline

WPEssential WordPress plugin is treated as a **public client**.

Preferred standards direction:
- Authorization Code flow;
- PKCE with `S256`;
- transaction-specific state/binding;
- exact trusted service endpoints;
- no account password collection/proxy inside WordPress by default.

OAuth Security BCP currently requires public clients using Authorization Code to use PKCE and recommends challenge methods that do not expose verifier; `S256` is the normal choice.

Device Authorization is a fallback for environments where browser callback handling is impractical, not the default on browser-capable wp-admin.

---

# Assets / secrets

## In WordPress site
- one-time `state`;
- PKCE verifier/challenge;
- linking transaction ID;
- local install UUID;
- callback/return binding;
- short-lived access token candidate;
- refresh credential after successful link;
- site activation reference.

## In WPE service
- user account/session;
- authorization transaction;
- site/client registration/binding;
- issued tokens;
- entitlement/site activation state.

## Must not exist as trusted secret in distributed plugin
- static confidential OAuth client secret shared across all installations.

Anything shipped in plugin code should be assumed recoverable by an attacker.

---

# Threat actors

- attacker controls another website/domain;
- attacker can send victim admin crafted links;
- attacker obtains authorization code through browser/history/log/referrer weakness;
- attacker controls a malicious WordPress plugin/theme on same site;
- lower-privilege WP user attempts account linking;
- attacker replays old callback/code/state;
- attacker alters `return_to`/callback;
- attacker controls DNS/domain after site migration;
- compromised WPE service account;
- stolen WP DB but not Vault key;
- full WP server compromise;
- compromised WPE signing/token infrastructure.

A full malicious-plugin/server compromise can often act with WordPress privileges; OAuth architecture cannot claim to defeat a fully compromised site.

---

# Required local authorization

Only principals with dedicated WPE account/license management capability may:
- initiate account link;
- complete callback;
- disconnect;
- activate/deactivate site;
- refresh/revoke service connection.

Link transaction binds initiating admin/user context.

A callback reaching WordPress is not sufficient by itself if local transaction/user context no longer valid.

---

# Alternative A — Direct site callback registration

Flow:
1. WP initiates link;
2. WPE service knows/registers exact site callback URI for one installation;
3. Authorization Code + PKCE;
4. WPE service redirects directly to site callback;
5. site exchanges code.

## Pros
- conventional OAuth shape;
- short path;
- site receives code directly.

## Risks/complexity
- dynamic redirect URI registration/trust;
- attacker must not register arbitrary victim callback/domain;
- site URL can change;
- local HTTPS/certificate/reverse proxy quirks;
- callback may be blocked by security plugins;
- service must strongly bind installation registration.

## Requirements
- exact redirect URI match;
- callback registered only through authenticated/bound installation transaction;
- HTTPS except explicitly unsupported local-dev policy;
- no wildcard domain/path redirect;
- one-time/expiring registration;
- domain change requires relink/reverification.

---

# Alternative B — Fixed WPE service callback + signed one-time site return

Flow candidate:
1. WP creates local transaction + PKCE challenge and opens WPE authorization;
2. OAuth authorization returns to fixed WPE-owned callback;
3. WPE service validates account/auth/code flow;
4. service creates one-time site-link completion token bound to original transaction/install/callback;
5. browser returns to exact original WP site URL with opaque one-time completion artifact;
6. WP redeems artifact server-to-server with PKCE/local transaction proof;
7. tokens/site activation issued.

## Pros
- OAuth authorization server uses fixed trusted callback;
- reduces arbitrary dynamic OAuth redirect registration complexity;
- WPE can validate site return separately from OAuth code flow.

## Risks
- custom return protocol must be designed carefully;
- open redirect risk if `return_to` not strictly bound;
- site-return artifact theft/replay;
- more service state/steps.

## Requirements
- return URI bound at transaction creation and never accepted from final request input;
- completion token one-time, short-lived, audience/install-bound;
- PKCE verifier still participates in final exchange/binding;
- service does not place reusable refresh/access token in browser URL;
- no arbitrary redirect chain.

Current paper preference: **Alternative B deserves first security evaluation** because a fixed OAuth callback can simplify redirect trust for distributed sites, while preserving strict one-time site return binding.

This preference is not Accepted until service-side threat model/profile is completed.

---

# Alternative C — Device Authorization fallback

Flow:
1. plugin requests device/user code;
2. admin opens WPE verification URL;
3. authenticates/approves on WPE domain;
4. plugin polls token endpoint respecting interval/slow_down;
5. receives site-link credentials.

## Pros
- no dynamic browser redirect back into WordPress;
- works around callback/firewall/plugin issues.

## Costs
- polling;
- slower UX;
- device-code phishing/user-code risks;
- must rate-limit and expire;
- not intended to replace normal browser authorization where browser flow works well.

Use as fallback or troubleshooting path after security review.

---

# Threat: Authorization code injection/theft

Mitigations:
- PKCE S256;
- one-time code;
- short lifetime;
- state/transaction binding;
- exact issuer/service endpoint;
- reject callback without matching local transaction;
- code never logged.

Old/replayed code/state fails.

---

# Threat: CSRF / linking wrong WPE account

Mitigations:
- high-entropy transaction-specific state;
- initiating WP principal bound;
- explicit final confirmation can show WPE account identity/site domain before connection if UX needs;
- PKCE;
- callback transaction expires;
- local nonce/capability for initiation and post-return admin action.

Never accept account connection solely because browser has a WPE session.

---

# Threat: Open redirect

Rules:
- no arbitrary `return_to` from callback request;
- return/callback URL stored/bound at transaction creation;
- exact allowed scheme/host/path policy;
- internal WordPress post-link navigation uses safe local redirect utilities/allowlist;
- service does not trust URL supplied by unauthenticated final redirect step.

---

# Threat: Mix-up / wrong issuer

Mitigations candidate:
- one configured trusted authorization issuer/environment;
- issuer validation where profile provides it;
- token endpoint fixed to transaction/service environment;
- environment ID bound to transaction;
- production plugin cannot be redirected to arbitrary authorization server.

---

# Threat: Malicious lower-privilege WordPress user

- initiation capability restricted;
- state transaction records initiating user;
- callback completion verifies capability still valid;
- connection belongs to site, not browser user session by accident;
- audit actor/account/site activation.

---

# Threat: Same-site malicious plugin/theme

A plugin with arbitrary PHP may read process memory/options/hooks and can potentially impersonate admin flows depending privileges.

Mitigations reduce exposure:
- refresh credential only in Vault;
- access token short-lived/server-side;
- no token in JS/localStorage;
- scoped tokens;
- remote revocation;
- site activation binding;
- audit suspicious relink/disconnect.

But WPE must document that full arbitrary-code compromise of WordPress host is outside Vault/OAuth isolation guarantee.

---

# Token profile candidate

## Access token
- short-lived;
- least scopes;
- server-to-server;
- never localized to frontend JS;
- never in URL/log.

## Refresh credential
- rotate where service supports;
- Vault P3 secret;
- site/account connection bound;
- remote revoke on disconnect;
- deleted locally after disconnect even if remote unavailable, with warning/retry remote revoke.

## Scopes
Candidate separate:
- `account:read`
- `entitlements:read`
- `support:read`
- `support:write`
- `sites:manage-self`

No broad account-administration scope just to fetch plans/docs.

---

# Site identity / activation binding

Local installation UUID is not authentication.

Service activation can bind:
- activation UUID;
- account;
- installation UUID;
- normalized site origin/domain;
- product/environment;
- created/revoked state.

Domain migration:
- do not silently treat copied staging site as same licensed production instance;
- future clone/staging policy explicit;
- account relink/activation transfer workflow may be required.

---

# Browser URL hygiene

Never put:
- access token;
- refresh token;
- WPE password;
- long-lived recovery credential
in URL/query/fragment.

One-time code/completion artifact must be short-lived and single-use.

Callback removes sensitive query args from browser history through safe redirect after processing.

Referrer policy/service pages should minimize code leakage.

---

# Local transaction storage

Store only while link pending:
- transaction ID;
- hash/state or secure value as required;
- PKCE verifier secret;
- expected service issuer/environment;
- expected return/callback;
- initiating user;
- created/expires.

Use Vault/private transient storage appropriate to threat profile; destroy on completion/expiry.

Multiple simultaneous transactions are isolated; completion cannot consume another transaction.

---

# Failure/recovery UX

States:
- pending browser approval;
- completed;
- expired;
- state mismatch;
- callback blocked;
- service unavailable;
- token exchange failed;
- account denied;
- site activation limit;
- local capability lost;
- relink required.

Failure never traps wp-admin or disables Free.

Offer Device Authorization fallback only where supported/configured.

---

# Disconnect

1. confirm capability;
2. call remote revoke when reachable;
3. delete local refresh/access credentials;
4. mark activation disconnected/stale;
5. preserve Free config/Pro definitions/data;
6. audit outcome;
7. if remote revoke failed, show remote-revoke pending warning without retaining usable local secret unnecessarily.

---

# Monitoring/abuse

Service can detect:
- excessive failed linking;
- repeated code redemption;
- impossible activation churn;
- token refresh anomalies;
- outdated/blocked client versions.

Do not collect unrelated site/customer analytics under account-link telemetry.

---

# Paper recommendation

Evaluate first:
**Fixed WPE OAuth callback + cryptographically/transaction-bound one-time return to site + PKCE S256**.

Compare against direct dynamic redirect registration.

Retain Device Authorization as fallback candidate.

Do not ship static confidential secret or local WPE password form as default architecture.

## Future evidence — NOT AUTHORIZED

After explicit consent:
- service/local prototype for A and B;
- state/code replay;
- stolen code without verifier;
- arbitrary return URI/open redirect;
- wrong issuer/environment;
- two simultaneous link flows;
- lower-privilege callback;
- site URL changes/reverse proxy;
- HTTPS/callback blocked;
- token rotation/revocation;
- DB theft/Vault key separation;
- disconnect during outage;
- Device Authorization poll/slow_down.

No OAuth client/service endpoint/token implementation has been created or executed.