# WPEssential — Protector Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0045, Protector exhaustive spec, XML-RPC, REST, Policy, Multisite, ADR-0014.

## 1. Product boundary

Protector is an application-layer access protection/hardening helper, **not a complete WAF/DDoS/security suite**.

Future implementation must not claim edge-level protection when traffic already reached PHP/WordPress.

## 2. Accepted security architecture under test

`Request context → trusted proxy resolution → normalized target → Protector rule/policy → shared atomic rate-limit service where applicable → WordPress/native auth/endpoint → response`

Recovery mode can disable WPE gates but never bypass WordPress authentication/capabilities.

## 3. Trusted proxy profile

Default client network identity begins with direct peer/`REMOTE_ADDR` semantics.

Forwarded headers are security-relevant only when:
- direct peer matches explicitly configured trusted proxy CIDR/profile;
- header chain syntax validates;
- documented proxy-hop selection algorithm is used;
- malformed/private/unexpected hops are handled deterministically;
- trusted proxy config itself is privileged/audited.

No generic “trust X-Forwarded-For” toggle.

## 4. Rate-limit service requirements

Rate-limited operations declare:
- operation/rule ID;
- scope site/network;
- key class: IP/user/credential/composite;
- window;
- capacity/attempts;
- block duration/backoff;
- burst semantics;
- atomic increment/check requirement;
- expiry/cleanup;
- bypass policy;
- privacy/log class.

Ordinary non-atomic transients are not presumed sufficient for security enforcement under concurrency.

## 5. Fixture matrix

### PR-01 — Direct client IP
No proxy; direct source resolves correctly.

### PR-02 — Spoofed X-Forwarded-For from direct internet client
Ignored for security identity.

### PR-03 — Trusted single reverse proxy
Configured trusted peer; expected client extraction works.

### PR-04 — Multiple trusted proxy chain
Exact hop selection deterministic.

### PR-05 — Untrusted hop inserted
Cannot spoof client identity.

### PR-06 — Malformed/multiple header forms
Safe reject/fallback.

### PR-07 — IPv4/IPv6/CIDR boundaries
Correct matching.

### PR-08 — Proxy config stale after infrastructure change
Diagnostics/recovery path; no silent broad trust.

### PR-09 — Concurrent rate-limit race
Many parallel attempts cannot exceed configured atomic allowance materially beyond accepted algorithm semantics.

### PR-10 — Window boundary race
No large bypass at exact window rollover.

### PR-11 — Shared NAT conservative defaults
One abusive user does not create an unrecoverable permanent lockout for entire NAT population under default profile.

### PR-12 — Authenticated user composite key
IP change does not bypass user/account-targeted limit where configured.

### PR-13 — Rate store unavailable
Fail-open/fail-closed behavior explicit per protected operation; login protection must not create permanent site lockout by accident.

### PR-14 — Rate-store cleanup
Expired buckets removed without deleting live counters.

### PR-15 — Login brute-force
Throttle works while valid login remains recoverable.

### PR-16 — Password-gate brute-force
Same shared atomic service semantics.

### PR-17 — XML-RPC limit
Delegates to Protector/Rate Limit while XML-RPC Manager remains method-policy source of truth.

### PR-18 — REST endpoint limit
REST Builder rule cannot bypass endpoint's own auth/Policy/idempotency.

### PR-19 — Site Gate shared password
Password is never stored/logged plaintext; cookie/security flags and TTL behavior proven.

### PR-20 — Site Gate admin recovery
Configured recovery principal can regain management access without public bypass URL.

### PR-21 — Guest/login-required gate
Correct redirect without loop.

### PR-22 — Redirect loop
Detected/blocked.

### PR-23 — Open redirect input
Rejected; external redirect off by default.

### PR-24 — 403 vs 404 concealment
Selected response does not alter underlying authorization truth.

### PR-25 — Protected post/CPT resource
Uses resource Policy, not URL-string approximation alone.

### PR-26 — Path normalization attack
Encoded slashes/dot segments/case/query ambiguity cannot bypass exact/prefix rule semantics.

### PR-27 — Regex complexity
Advanced regex rejected/bounded against catastrophic behavior.

### PR-28 — `/wp-admin` blanket rule
AJAX/REST/core recovery dependencies remain explicit; incompatible blanket config warns/blocks according to profile.

### PR-29 — Login alias normal flow
Login, logout, lost password, reset and redirect semantics work under certified profile.

### PR-30 — Login alias plugin incompatibility
Detected limitation does not claim primary security; original recovery path remains documented.

### PR-31 — Security headers duplicate/conflict
WPE does not emit destructive duplicate HSTS/CSP/etc. without reviewed profile.

### PR-32 — HSTS unsafe enablement
HTTP/mixed/subdomain impact preview prevents casual global lock-in.

### PR-33 — CSP report-only/advanced
No default global CSP that breaks site/admin; exact header composition tested.

### PR-34 — Recovery mode
Disables WPE-specific blocking layer as documented while preserving WordPress authentication/capabilities.

### PR-35 — Lower-privilege Protector editor attempt
Denied.

### PR-36 — Multisite Site rule
Cannot protect/block another site through forged site ID.

### PR-37 — Network floor policy
Site override cannot weaken enforced network minimum where network profile declares a floor.

### PR-38 — Cache/CDN interaction
Application gate does not claim protection when upstream cache serves protected page without reaching WordPress; diagnostics/documentation identifies limitation.

### PR-39 — High-volume attack limitation
Evidence documents PHP/application cost and distinguishes WAF/edge recommendation; no false DDoS claim.

### PR-40 — Privacy/log retention
IP/path/auth metadata follow configured classification, anonymization/retention; no passwords/tokens.

### PR-41 — Rule precedence conflict
Allow/deny specificity deterministic and outer security deny cannot be bypassed.

### PR-42 — Rule store corrupt
Safe recovery/degraded behavior; no permanent wp-admin lockout.

### PR-43 — Plugin deactivation
WPE gate removal behavior truthful; no server-level rules secretly left unless separately managed/certified and reported.

### PR-44 — Pro expiry
Safe deployed protection follows ADR-0007; editing can lock without silently disabling active security gate.

## 6. Performance/abuse evidence

Capture:
- request overhead with 0/10/100/1000 rules;
- rate-store latency under parallel load;
- PHP memory/time;
- proxy-chain parsing cost;
- DB/cache contention;
- login/XML-RPC/REST throughput before/after limit;
- cleanup cost.

Performance cannot justify trusting spoofable headers or non-atomic security counters.

## 7. Pass gates

Fail production profile if:
- untrusted forwarded header controls security identity;
- rate limit trivially bypasses under concurrency;
- recovery mode bypasses WordPress auth;
- rule can create open redirect;
- path normalization bypasses protection;
- password/token logged plaintext;
- Site rule mutates another site's protection;
- product claims WAF/DDoS prevention beyond actual application-layer evidence.

## 8. Current state

**PR fixtures executed: 0/44.**

No Protector hook, rate-limit counter, proxy parse, login alias, header or request gate has run.

## 9. Development gate

Execution requires explicit owner consent under ADR-0014.