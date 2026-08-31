# WPEssential — Protector Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package: `P0-M00-WP42`  
Related: ADR-0045, ADR-0105, RLT, CAC, KPA, ERR, VER, MLC, REST, XML-RPC, Webhooks, Multisite, ADR-0014.

## 1. Purpose and product boundary

Define executable evidence required before Protector can claim safe request gating, client identity, rate/abuse enforcement, password/login gates, redirects, security headers, recovery, integration compatibility, Multisite isolation or scale behavior.

Protector is an **application-layer WordPress protection/hardening surface**, not a complete edge WAF/DDoS platform. Traffic that already reached PHP/WordPress has already consumed application resources.

## 2. Canonical security invariant

`request → canonical request context → trusted client identity → surface/route classification → applicable Protector rule set → shared RLT where configured → WordPress/native endpoint authentication + capability/resource Policy → response`

The following never grant WordPress authorization by themselves:
- a Protector allow;
- an IP/CIDR match;
- a gate-password session;
- a rate-limit allow;
- a cache hit/miss;
- a hidden login URL;
- a successful redirect;
- a security-header policy.

Recovery mode may disable WPE-specific overlays only. It must not authenticate a user, mint capability/Super Admin authority, bypass WordPress nonces/passwords or expose Membership-protected resources.

## 3. Certification profile

Every future evidence report records at minimum:
- WordPress/PHP/web-server/database versions;
- single-site/Multisite topology;
- active cache/CDN/reverse-proxy/load-balancer profile;
- trusted-proxy configuration and forwarded-header contract;
- Protector Definition/rule schema version;
- KPA/Policy/Capability profile;
- RLT algorithm/state-adapter/version profile;
- CAC backend/generation profile where Protector state/results interact with caches;
- REST/XML-RPC/Webhook/cron/loopback integration profile;
- login/password-reset/Application Password environment;
- security-header ownership/conflict profile;
- MLC/VER/recovery configuration;
- retention/privacy/Audit profile.

Certification is scoped to the recorded profile. Unknown proxy/cache/provider/server versions are not silently certified.

# 4. Original canonical fixtures — preserved

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
Delegates to Protector/RLT while XML-RPC Manager remains method-policy source of truth.

### PR-18 — REST endpoint limit
REST rule cannot bypass endpoint auth/Policy/idempotency.

### PR-19 — Site Gate shared password
Password is never stored/logged plaintext; cookie/security flags and TTL behavior proven.

### PR-20 — Site Gate admin recovery
Authorized recovery path can regain management access without public bypass URL.

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
Application gate does not claim protection when upstream cache serves protected page without reaching WordPress; diagnostics identify limitation.

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

# 5. Request-context, target and identity fixtures

### PR-45 — Scheme authority behind proxy
HTTPS detection follows certified trusted-proxy/server semantics; spoofed forwarded proto cannot alter security decisions.

### PR-46 — Host canonicalization
Host/header normalization cannot bypass domain-specific Protector rules or create host-header injection behavior.

### PR-47 — Port canonicalization
Default/non-default port treatment is deterministic and cannot split equivalent protected targets unexpectedly.

### PR-48 — Raw path vs decoded path
Rule matching uses one documented canonical target; alternate decode order cannot bypass a deny.

### PR-49 — Repeated percent encoding
Double/mixed encoding cannot turn a blocked target into an allowed target after later WordPress decoding.

### PR-50 — Slash/backslash normalization
Alternate slash forms and duplicate slashes do not create rule ambiguity on supported servers.

### PR-51 — Dot-segment normalization
`.`/`..` and encoded equivalents cannot escape protected prefix semantics.

### PR-52 — Query-string authority
Query parameters participate only when explicitly configured; sensitive rule scope is not accidentally broadened by ignored/decoded query forms.

### PR-53 — Fragment non-authority
Client-only URL fragments never influence server Protector decisions.

### PR-54 — Method classification
GET/HEAD/POST/PUT/PATCH/DELETE/OPTIONS behavior is explicit; method confusion cannot bypass a write gate.

### PR-55 — HEAD parity
HEAD cannot reveal protected representation metadata when equivalent GET is denied beyond accepted concealment policy.

### PR-56 — OPTIONS/preflight boundary
CORS/preflight success never grants application authorization or bypasses Protector/endpoint policy.

### PR-57 — Content-type confusion
Form, JSON, multipart and unusual content types cannot route around a protected state-changing surface.

### PR-58 — Request body not generic rule language
Untrusted body fields cannot select arbitrary Protector rule/policy/handler identities.

### PR-59 — Trusted identity unavailable
Ambiguous client identity follows explicit conservative/fallback policy; no favorable guessed IP.

### PR-60 — IPv6 privacy/rotation semantics
Policies depending on network identity document prefix/rotation limitations and do not claim account identity from IP.

# 6. Rule definition, lifecycle and precedence fixtures

### PR-61 — Draft rule not executable
Unpublished/draft Protector Definition cannot affect runtime traffic.

### PR-62 — Published revision pinning
Runtime resolves the intended published rule revision, not an editor draft.

### PR-63 — Atomic-ish rule-set publication
Readers do not observe a partially compiled rule set during publish/changeover.

### PR-64 — Invalid rule publication
Malformed/unsupported rule fails publication instead of silently widening access.

### PR-65 — Rule stable identity
Rule identity survives label/order edits without creating stale duplicate enforcement state.

### PR-66 — Disable vs delete
Disabling a rule stops its WPE enforcement while preserving configuration/history per MLC; delete remains distinct.

### PR-67 — Dependency missing
Missing KPA/RLT/CAC/registered resolver dependency enters explicit degraded behavior rather than fatal or silent allow.

### PR-68 — Version migration
Older supported rule schema migrates explicitly under VER and preserves deny/allow semantics or blocks safely.

### PR-69 — Unknown future rule version
Unsupported newer schema is not executed as best-effort permissive policy.

### PR-70 — Exact vs prefix precedence
Equivalent overlapping exact/prefix rules resolve deterministically.

### PR-71 — Method-specific precedence
Method-specific deny/allow semantics are deterministic against generic route rules.

### PR-72 — Principal-specific precedence
User/role/network/gate-session conditions cannot weaken an enforced outer/network deny unexpectedly.

### PR-73 — Schedule boundary
Time-based rule activation uses declared timezone and deterministic boundary behavior.

### PR-74 — Conflicting equal-specificity rules
Tie policy is explicit and security-floor deny cannot be overridden by ordering accident.

### PR-75 — Bulk rule reorder
Reordering display/UI does not silently change precedence unless precedence is explicitly order-based and reviewed.

### PR-76 — Rule deletion with dependents
Deletion checks references/dependencies; stale compiled artifacts cannot keep hidden enforcement indefinitely.

# 7. Shared Rate Limit / abuse-control integration fixtures

### PR-77 — RLT policy reference resolution
Protector references a versioned/registered RLT policy; unknown reference does not become unlimited access silently.

### PR-78 — RLT key site isolation
Same client/rule on two sites does not collide unless explicit network-shared policy says so.

### PR-79 — RLT rule isolation
Two rules cannot corrupt/consume each other's counters accidentally.

### PR-80 — RLT principal normalization
IP/user/credential/composite keys match shared RLT canonical encoding and collision rules.

### PR-81 — Trusted proxy feeds RLT identity
Protector client identity handed to RLT is the same certified identity, not reparsed independently with weaker rules.

### PR-82 — Anonymous-to-authenticated transition
Limiter key transition on login cannot reset a high-risk account-targeted control improperly.

### PR-83 — IPv6 key cardinality abuse
Attacker cannot create unbounded trivial limiter state solely by rotating arbitrary IPv6 addresses without bounded policy/cleanup.

### PR-84 — NAT fairness profile
Conservative shared-IP handling remains recoverable and diagnostics explain account/IP composite options.

### PR-85 — Retry-After/reset metadata
Throttle response metadata matches shared RLT policy without exposing sensitive internal state.

### PR-86 — Progressive cooldown
If configured, cooldown/backoff remains bounded/versioned and cannot create permanent lockout through clock/storage anomalies.

### PR-87 — Exemption authorization
Bypass/exemption requires explicit trusted principal/config authority; request-provided flag/header cannot self-exempt.

### PR-88 — Network-floor limiter
Site policy cannot weaken network-mandated RLT minimum in Multisite.

### PR-89 — RLT backend migration
Counter-backend/profile change has explicit reset/transition semantics; migration cannot silently create false certification.

### PR-90 — RLT clock anomaly
Clock skew/backward/forward changes cannot grant effectively unlimited attempts or indefinite lockout outside documented behavior.

### PR-91 — RLT diagnostics privacy
Limiter diagnostics expose safe key class/reason/state without raw credentials, auth headers or unnecessary IP retention.

### PR-92 — Consumer-certification separation
Passing shared RLT fixtures alone never marks login/gate/XML-RPC/REST/Protector consumer behavior certified.

# 8. Authentication, gate and recovery fixtures

### PR-93 — Gate-password hash upgrade
Supported hash verification/rehash changes do not expose plaintext or invalidate active policy unexpectedly.

### PR-94 — Gate-session fixation
Attacker cannot preselect/fix a gate-session identifier and inherit another user's successful gate.

### PR-95 — Gate-session rotation
Successful authentication/revalidation rotates/creates safe session identity according to profile.

### PR-96 — Gate cookie scope
Domain/path/Secure/HttpOnly/SameSite settings are bounded to intended routes and HTTPS profile.

### PR-97 — Gate-session revoke one
Revocation prevents further access without needing plaintext password.

### PR-98 — Gate-session revoke all
Credential rotation or explicit revoke-all invalidates prior gate sessions within declared correctness window.

### PR-99 — Gate CSRF
Gate mutation/session actions requiring CSRF protection reject forged state changes.

### PR-100 — Gate timing/enumeration
Failure responses do not materially reveal valid password/session state beyond accepted profile.

### PR-101 — Login alias action coverage
`login`, `logout`, `lostpassword`, `rp/resetpass`, registration and core-generated links remain explicitly mapped/tested.

### PR-102 — Login alias canonical redirect
Alias/direct legacy behavior avoids redirect loops and does not leak unsafe external redirect targets.

### PR-103 — Application Password boundary
Login alias/site gate does not pretend to secure or disable Application Password/REST auth unless that surface has explicit policy.

### PR-104 — Recovery config authorization
Changing recovery-related Protector configuration requires dedicated capability/recent-auth where classified high-risk.

### PR-105 — Recovery mode scope
Recovery disables only WPE overlays required for repair; native WordPress auth/capability checks remain active.

### PR-106 — Recovery mode audit/diagnostic
Recovery activation is observable to authorized administrators without publishing a bypass secret/URL.

### PR-107 — Recovery after corrupt rule set
Authorized native/CLI recovery can restore manageability when compiled/rule storage is corrupt.

### PR-108 — Membership/private-resource recovery boundary
Recovery mode never converts resource access from denied/private to public solely because Protector is bypassed.

# 9. Redirect, response, security-header and cache/CDN fixtures

### PR-109 — Relative redirect canonicalization
Allowed same-origin redirects normalize safely and reject control-character/header injection.

### PR-110 — External redirect allowlist
Explicit external destinations use exact reviewed allowlist semantics; suffix/userinfo/Unicode tricks cannot bypass it.

### PR-111 — Redirect status semantics
301/302/303/307/308 method/body effects are intentional and cannot transform protected write behavior unexpectedly.

### PR-112 — Deny response content
403/404/login/gate response is safe, bounded and does not echo attacker-controlled markup unsafely.

### PR-113 — Security-header owner discovery
Observed host/CDN/plugin headers are detected where feasible and ownership/conflict state is surfaced.

### PR-114 — CSP composition
Multiple CSP policies are not naively concatenated into unintended/broken behavior; report-only/enforce roles remain explicit.

### PR-115 — HSTS deployment state
HSTS output requires HTTPS-safe profile; preload/includeSubDomains consequences remain explicit.

### PR-116 — Frame policy conflict
`X-Frame-Options`/CSP `frame-ancestors` conflicts are detected or documented; admin/editor embedding is not silently broken.

### PR-117 — Referrer/permissions policies
Header values validate against supported syntax/version and do not claim browser behavior without evidence.

### PR-118 — Header injection
Untrusted rule labels/URLs/config cannot inject response headers or split responses.

### PR-119 — Page-cache protected response
Application-generated protected page is not cached/replayed publicly under a certified cache profile unless cache key/authorization semantics prove safety.

### PR-120 — Cache bypass header trust
Client cannot forge an internal cache/proxy bypass header to evade Protector or gain privileged representation.

### PR-121 — CAC generation on rule change
Protector-dependent cached decisions/representations invalidate/version-bypass after published rule changes according to CAC.

### PR-122 — CAC generation on principal revoke
Capability/Membership/gate-session revocation cannot leave stale protected output accessible beyond declared correctness window.

### PR-123 — CDN edge limitation
Diagnostics distinguish edge cache/WAF behavior from WordPress Protector behavior; application evidence cannot certify unseen edge policy.

### PR-124 — Error-page cache isolation
Denied/authenticated/private error responses do not become shared cached responses for unrelated principals/sites incorrectly.

# 10. WordPress core and integration compatibility fixtures

### PR-125 — `admin-ajax.php` compatibility
Protected admin policy does not blindly block authorized core/plugin AJAX dependencies without explicit rule coverage.

### PR-126 — REST bootstrap compatibility
Protector outer gate composes with REST permission callbacks; neither layer substitutes the other.

### PR-127 — XML-RPC method-policy composition
Outer XML-RPC gate/RLT composes with XML-RPC Manager method allow/deny/auth semantics without duplicated contradictory authority.

### PR-128 — Webhook ingress composition
Webhook endpoint gating/rate policy preserves verified signature/replay/Event Inbox semantics and cannot drop/accept by path alone.

### PR-129 — WP-Cron/loopback compatibility
Protector rules do not accidentally break required loopback/cron routes without explicit diagnostics/profile.

### PR-130 — system cron/WP-CLI boundary
Server-local CLI/system-cron runners are not treated as anonymous web clients merely because web gate policy exists.

### PR-131 — Heartbeat API
Admin/frontend heartbeat behavior is explicitly tested under broad admin/public rules.

### PR-132 — Site Health loopback
Site Health/loopback checks surface Protector-caused failures diagnostically rather than hiding them.

### PR-133 — password reset email links
Core reset links remain functional under login alias/gates according to certified route mapping.

### PR-134 — logout nonce flow
Core logout/nonces remain WordPress-owned; Protector redirect/gate cannot weaken validation.

### PR-135 — registration/multisite signup
If core registration/signup enabled, policy is explicit and does not accidentally expose blocked admin/network routes.

### PR-136 — media/private downloads
Path rules do not substitute for resource/private-file authorization and cannot make protected media public.

### PR-137 — feeds/sitemaps
Public/private feed/sitemap handling is explicit; cache or concealment does not leak protected content existence/details beyond policy.

### PR-138 — robots/security.txt/static paths
Static/public special files have explicit policy and are not accidentally tied to WordPress user authorization claims.

### PR-139 — third-party login/security plugin coexistence
Hook/redirect/header conflicts degrade visibly; WPE does not claim compatibility without versioned evidence.

### PR-140 — reverse proxy/CDN challenge coexistence
Provider challenges/bot controls are treated as external layers; Protector neither bypasses nor falsely claims ownership of them.

# 11. Multisite, lifecycle, portability and versioning fixtures

### PR-141 — Site-scoped rule ownership
A Site rule stores/resolves explicit site scope rather than current-blog accident.

### PR-142 — Network-scoped rule authorization
Creating/editing network Protector policy requires current network authority, not site-admin status.

### PR-143 — Network floor inheritance
Effective site policy includes mandatory network floor with deterministic merge/precedence semantics.

### PR-144 — Site stricter override
Site may add stricter controls where allowed without mutating network-owned Definition.

### PR-145 — Site cannot weaken floor
Any UI/API/import attempt to reduce mandatory floor is rejected or represented as ineffective override.

### PR-146 — Site switch isolation
`switch_to_blog()` changes context but not durable ownership or network authority.

### PR-147 — New-site provisioning
Lifecycle-created site receives only documented default/network inherited policy; no copied unrelated site's gate sessions/counters.

### PR-148 — Site clone
Clone remaps site-owned rule identities/config as policy allows but does not copy active gate sessions/RLT state/recovery assertions.

### PR-149 — Site transfer/domain change
Host/path/proxy-dependent rules require revalidation after domain/topology change; stale allowlists are not silently trusted.

### PR-150 — Site deletion
Deleting site cleans/drains site-owned Protector control state under LC without deleting shared network policy.

### PR-151 — Restore
Restored rule definitions/caches/gate sessions/RLT state follow explicit restore/revalidation semantics; stale security sessions are not blindly resurrected.

### PR-152 — Import/export configuration
Portable Protector configuration excludes passwords/session secrets/counters and revalidates target scope/proxy/header assumptions.

### PR-153 — Cross-version import
Older supported rule schemas migrate through VER; unsupported versions block rather than best-effort weaken policy.

### PR-154 — Module disable
MLC disable behavior is explicit and recoverable; disabling Protector cannot secretly leave application hooks/server artifacts active contrary to report.

### PR-155 — Dependency disable
Loss of RLT/CAC/Policy integration follows defined security/degraded policy and does not silently widen high-risk protection.

### PR-156 — Free/Pro transition
Edition/entitlement changes preserve safe deployed protection per ADR-0007 and never corrupt WordPress native recovery/auth paths.

# 12. Privacy, audit, error and diagnostics fixtures

### PR-157 — IP minimization
Stored/logged network identifiers follow declared privacy purpose, hashing/truncation/retention profile where appropriate.

### PR-158 — Sensitive request redaction
Authorization headers, cookies, passwords, reset tokens, gate secrets, Vault secrets and raw sensitive payloads are excluded from generic logs/Audit/support bundles.

### PR-159 — Audit actor/scope/reason
Privileged rule/config/recovery changes record safe actor, target scope, change class, result and correlation identity.

### PR-160 — Deny-event sampling
High-volume deny events use bounded/sampled aggregation without losing required security diagnostics or causing log-DoS.

### PR-161 — Stable machine reason codes
Protector outcomes expose stable machine/error categories separate from localized message text under ERR.

### PR-162 — Fail-open/fail-closed observability
Any degraded decision records enough safe diagnostic context to explain which risk policy applied.

### PR-163 — Configuration validation error
Invalid CIDR/regex/header/redirect/RLT reference is rejected with safe actionable error and no partial publish.

### PR-164 — Runtime exception
Unexpected resolver/backend failure cannot leak stack trace, filesystem path, SQL, secret or another site's details to public response.

### PR-165 — Support bundle redaction
Diagnostics can report effective rule/proxy/RLT/cache/header state while excluding secrets and excessive PII.

### PR-166 — Health check
Site Health can identify common misconfiguration/degraded dependencies without itself granting access or weakening enforcement.

### PR-167 — Audit retention
Protector security audit/operational logs follow PDL retention and remain distinct from canonical rule Definitions/RLT state.

### PR-168 — Privacy erase boundary
User privacy erasure does not blindly delete security records whose retention is independently justified; eligible user-linked metadata is handled according to PDL.

# 13. Performance, abuse and scale fixtures

### PR-169 — Zero-rule overhead
Baseline request cost with Protector enabled but no applicable rules remains bounded and measured.

### PR-170 — 10/100/1000-rule match cost
Representative exact/prefix/principal/schedule rule sets meet bounded CPU/memory/query budgets.

### PR-171 — Regex worst-case budget
Certified regex profile cannot create catastrophic CPU behavior under hostile input.

### PR-172 — Proxy-chain abuse budget
Oversized/malformed forwarded-header chains are bounded before excessive parse/memory cost.

### PR-173 — Deny-flood/log amplification
High-rate denied traffic cannot create unbounded DB/log writes beyond configured sampling/backpressure profile.

### PR-174 — RLT backend contention
Parallel limiter use measures atomic-store contention/latency without weakening correctness to improve benchmark numbers.

### PR-175 — Large Multisite network
100/1k/10k-site policy metadata lookup/inheritance remains scope-correct and bounded; one noisy site cannot mutate another site's rule/RLT namespace.

### PR-176 — End-to-end security regression profile
Certified representative public/login/admin/REST/XML-RPC/webhook/cache/proxy scenarios show zero authorization bypass, zero cross-site leakage and truthful application-layer security limits.

# 14. Pass / stop-the-line gates

Protector certification fails immediately if any supported profile allows:
- untrusted forwarded headers to control security identity;
- path/host/method normalization bypass of enforced policy;
- non-atomic/race-trivial bypass of advertised high-risk RLT behavior;
- Protector allow/gate/cache state to grant WordPress capability/resource authorization;
- open redirect/header injection;
- anonymous/public recovery authority;
- recovery mode to bypass WordPress auth or expose Membership/private content;
- a Site policy to mutate another site or weaken mandatory network floor;
- stale cache to serve privileged/protected output outside certified CAC correctness semantics;
- logged plaintext passwords/tokens/cookies/Authorization/Vault secrets;
- rule/dependency/version corruption to silently fail open on a high-risk surface;
- false WAF/DDoS/edge-protection claims unsupported by application-layer evidence.

# 15. Required future evidence report

Include:
- exact runtime/proxy/cache/server/RLT/KPA/MSI profile;
- PR-01…PR-176 pass/fail with artifacts;
- canonicalization/trusted-proxy spoof matrix;
- RLT concurrency/failure/fairness evidence;
- gate/login/recovery/browser-flow evidence;
- redirect/header conflict evidence;
- REST/XML-RPC/Webhook/cron/loopback compatibility;
- CAC/cache/CDN isolation/revocation evidence;
- Multisite network-floor/lifecycle evidence;
- privacy/redaction/error/diagnostic evidence;
- 0/10/100/1000-rule and abuse/load measurements;
- unsupported/degraded profiles and explicit limitations.

# 16. Current state

**PR fixtures executed: 0/176.**  
Protector runtime certifications: **0**.  
Shared RLT remains **0/176** and CAC remains **0/176**; those shared protocols do not auto-certify Protector.

No Protector hook/request gate, proxy parse, rule publication, rate-limit counter, gate session, login alias, redirect, security header, cache/CDN integration, Multisite operation, benchmark or runtime test has been executed.

# 17. Development gate

Execution requires explicit owner consent under ADR-0014. This document is planning/evidence only.