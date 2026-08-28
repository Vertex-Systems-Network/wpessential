# WPEssential — XML-RPC Manager Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package: `P0-M00-WP43`  
Related: ADR-0052, ADR-0111, Protector/ADR-0159, RLT, CAC, KPA, ERR, VER, MLC, Safe HTTP, Multisite, ADR-0014.

## 1. Purpose

Define evidence required before WPEssential can claim XML-RPC endpoint/method policy, Complete Deny, authentication compatibility, pingback handling, parser/request abuse controls, integration compatibility, Multisite isolation or observability support.

The terminology invariant is fixed:

**`xmlrpc_enabled = false` is not equivalent to “XML-RPC endpoint disabled”. Endpoint reachability, method registry, authenticated-method enable state, pingback/custom methods, Protector outer gating and host/WAF behavior are separate layers.**

## 2. Canonical enforcement model

`host/edge → Protector outer request policy + shared RLT → WordPress XML-RPC bootstrap/parser → effective method registry → WPE XML-RPC method policy → native method authentication/capability/object checks → method implementation/result`

A WPE allow never grants authentication/capability/object authorization. A Protector allow never enables a denied XML-RPC method. A host/WAF deny is not evidence that WPE policy executed.

## 3. Runtime certification profile

Every future certification records:
- WordPress/PHP/XML parser versions;
- single-site/Multisite topology;
- effective XML-RPC method inventory and registration priorities;
- plugins/integrations that register/depend on methods;
- host/CDN/WAF/reverse-proxy endpoint behavior;
- Protector/trusted-proxy/RLT profile;
- WPE XML-RPC policy Definition/revision/version;
- parser/request/body/element-limit environment;
- authentication mode and Application Password availability;
- Safe HTTP profile relevant to pingbacks/outbound verification;
- CAC profile for any cached inventory/diagnostic state;
- logging/PDL/Audit/ERR profile;
- MLC/VER lifecycle state.

Certification is configuration/version specific. New plugin-added methods or changed host behavior can invalidate prior support claims.

# 4. Original canonical fixtures — preserved

### XR-01 — Endpoint reachable baseline
WordPress receives a controlled XML-RPC request in the certified baseline; host-level block is distinguished from WPE policy.

### XR-02 — Externally blocked endpoint
Host/WAF block is diagnosed as external/earlier-layer behavior; WPE does not falsely claim its method policy executed.

### XR-03 — Core method inventory
Effective registered core method set is captured for the tested WordPress version before WPE filtering.

### XR-04 — Plugin-added method inventory
A fixture plugin-added method appears as custom/unknown or certified owner and participates in policy resolution.

### XR-05 — Late/priority registration conflict
Method added/changed at relevant filter priority is detected or explicitly documented; WPE does not assume a static pre-plugin list.

### XR-06 — `xmlrpc_enabled=false` semantics
Authenticated methods are affected according to observed core behavior while endpoint/custom/pingback registry state is reported separately.

### XR-07 — Pingback inventory
`pingback.ping` and `pingback.extensions.getPingbacks` are independently inventoried/policy-addressable when present.

### XR-08 — Unknown custom method
Unknown method is not silently marked safe; denylist/allowlist behavior follows explicit rule mode.

### XR-09 — Deny one method
Denied method is not callable while an unrelated allowed method remains available according to native authorization.

### XR-10 — Allowlist unknown-default deny
In allowlist mode, newly discovered/unreviewed method defaults denied unless explicitly/certifiably allowed.

### XR-11 — Denylist unknown-default allow
In denylist mode, unknown method follows documented inherited/allow behavior and UI warns about exposure semantics.

### XR-12 — Complete Deny registry
All methods present in the effective tested registry resolve denied/removed under WPE Complete Deny policy.

### XR-13 — Complete Deny with later custom method
Unexpected newly registered method still resolves denied in the tested ordering/profile or the limitation is surfaced as certification failure.

### XR-14 — Complete Deny + Protector endpoint gate
Optional outer endpoint deny blocks before method execution and diagnostics identify Protector as first WPE denial layer.

### XR-15 — Policy preset impact preview
Preview reports current/proposed method counts, exact affected methods, custom/unreviewed methods, integration warnings and recovery path before apply.

### XR-16 — Policy rollback/recovery
Authenticated administrator can disable/revert WPE overlay through approved recovery path without anonymous auth bypass.

### XR-17 — Allowed authenticated read method
Allowed method still requires native WordPress authentication/capability semantics; WPE allow does not grant permission.

### XR-18 — Wrong credentials
Authentication failure cannot become method success due to WPE policy and produces bounded/redacted observability.

### XR-19 — Authenticated method disabled globally
WPE accurately reports native authenticated-method disable state distinct from method-registry/endpoint state.

### XR-20 — Object authorization
Authorized user for one object cannot use an allowed method to mutate/read another protected object without native checks.

### XR-21 — User/account rate scope
Where enabled, repeated authenticated failures trigger shared atomic rate policy using trusted identity/IP inputs only.

### XR-22 — Spoofed forwarded header
Untrusted forwarded-header input cannot evade XML-RPC rate limits; trusted-proxy resolution is shared with Protector.

### XR-23 — Pingback denied
Pingback method can be blocked independently while other permitted XML-RPC methods remain operational.

### XR-24 — Pingback allowed baseline
If allowed by a certified profile, normal native permission/request behavior remains intact and WPE does not weaken safe HTTP handling.

### XR-25 — Pingback rate limit
Repeated pingback attempts trigger configured shared rate policy without unbounded state growth.

### XR-26 — Endpoint request-rate limit
High-rate XML-RPC requests are bounded atomically across concurrent workers under the certified rate-store profile.

### XR-27 — Rate-store failure
Failure of shared rate store follows explicit fail-open/fail-closed/degraded policy; diagnostics state actual behavior.

### XR-28 — Batched/multicall abuse profile
Large/many-method XML-RPC calls are bounded by certified parser/rate/request limits without claiming an unsupported universal body-size hook.

### XR-29 — `xmlrpc_element_limit` below threshold
Configured supported element limit rejects over-limit request according to tested WordPress behavior.

### XR-30 — `xmlrpc_element_limit` compatibility
Normal certified integration request stays within configured limit and does not break silently.

### XR-31 — Malformed XML
Parser failure is safe, bounded and does not expose stack traces/secrets.

### XR-32 — Oversized/deep request environment boundary
Host/PHP/Protector constraints are measured separately from WordPress element limit; WPE does not claim controls it does not own.

### XR-33 — Method-name normalization
Malformed/unexpected method names cannot bypass exact registry rule matching.

### XR-34 — Invalid encoding/entity behavior
Parser behavior is tested for supported XML parser profile; no arbitrary external entity/file retrieval is introduced by WPE.

### XR-35 — WordPress default profile
Default-compatible preset preserves required tested baseline methods/behavior within declared scope.

### XR-36 — Publishing-on/pingbacks-off profile
Certified remote publishing flow passes while pingback methods remain denied.

### XR-37 — Authenticated publishing off
Publishing/authenticated-method deny behavior is truthful and unrelated endpoint/custom method state remains visible.

### XR-38 — Jetpack-compatible profile
When claimed, exact tested Jetpack/plugin version completes critical connectivity/workflow fixtures; if a narrow method set cannot be proven, profile does not invent one.

### XR-39 — Jetpack + Complete Deny warning
Detected certified/known Jetpack dependency causes hard compatibility warning before Complete Deny apply.

### XR-40 — Remote/mobile publishing profile
When claimed, representative certified client workflow passes through allowed methods/native auth and survives unrelated deny rules.

### XR-41 — Integration version out of range
New/unsupported integration version becomes uncertified/degraded rather than silently inheriting old compatibility claim.

### XR-42 — Multisite site isolation
Site-scoped XML-RPC rule/inventory state does not cross same-slug/site IDs unless network policy explicitly owns it.

### XR-43 — Network policy floor
Child site cannot weaken network-enforced Complete Deny/method floor under the certified resolution order.

### XR-44 — Site-specific integration
Integration requirement bound to one site does not automatically loosen network/other-site policy.

### XR-45 — Logging redaction
Logs contain method/result/correlation and approved minimal metadata only; passwords/tokens/raw request bodies/sensitive arguments are absent by default.

### XR-46 — Observability hook coverage
Logging coverage is measured honestly; methods not visible to chosen hook remain enforced at registry/outer layers and diagnostics state limitation.

### XR-47 — Method inventory drift
Plugin activation/update adding/removing methods changes health/inventory state and triggers review warning without silently reclassifying support.

### XR-48 — Pro expiry/degraded runtime
Existing safe policy runtime follows ADR-0007; editing entitlement changes do not unexpectedly re-expose methods or lock administrator recovery.

# 5. Endpoint, HTTP and request-surface fixtures

### XR-49 — Exact XML-RPC endpoint classification
Canonical WordPress XML-RPC route is identified without broad path patterns that capture unrelated resources.

### XR-50 — Alternate scheme behind trusted proxy
HTTPS/proto detection follows Protector trusted-proxy policy; spoofed proto cannot change XML-RPC security behavior.

### XR-51 — Host-header normalization
Host ambiguity/injection cannot select another site's XML-RPC policy or create unsafe redirects/log data.

### XR-52 — Path encoding normalization
Encoded/double-encoded slash/dot variants cannot bypass Protector outer XML-RPC endpoint policy.

### XR-53 — POST requirement
Unexpected GET/HEAD/OPTIONS behavior is documented and cannot become method execution through WPE routing.

### XR-54 — Content-Type variants
Supported XML content types behave intentionally; malformed/unsupported types fail safely.

### XR-55 — Content-Length absent/chunked
Request-size/stream behavior is tested under supported server profile without assuming one header always exists.

### XR-56 — Oversized header set
Header abuse is bounded by host/Protector profile and cannot corrupt XML-RPC method identity/policy.

### XR-57 — Empty body
Empty XML-RPC request fails safely with stable error behavior.

### XR-58 — Trailing bytes/multiple XML documents
Parser does not accept attacker-controlled extra document content in a way that bypasses method policy.

### XR-59 — Encoding declaration mismatch
Declared vs actual encoding failure is bounded and does not produce unsafe parser fallback.

### XR-60 — Compression environment
If upstream/request compression is supported, decompressed-size limits remain explicit; WPE does not claim unverified bomb protection.

### XR-61 — First-denial-layer truth
Host/Protector/parser/method-policy/native-auth denials are distinguishable enough for diagnostics without leaking secrets.

### XR-62 — Endpoint health probe
Diagnostics can determine reachable/blocked/degraded state without authenticating or mutating content.

### XR-63 — Page-cache exclusion
XML-RPC mutation/auth responses are not incorrectly served from shared page cache under certified profile.

### XR-64 — CDN/proxy limitation
Edge blocking/challenge behavior is reported as external layer evidence rather than XML-RPC Manager certification.

# 6. Registry, policy Definition and lifecycle fixtures

### XR-65 — Draft policy not executable
Draft/editing XML-RPC policy cannot alter live method behavior.

### XR-66 — Published policy revision
Runtime uses intended published revision and stable method policy identity.

### XR-67 — Atomic-ish policy changeover
Requests do not observe a partially compiled allow/deny registry during publish.

### XR-68 — Invalid policy publication
Unknown malformed rule/preset configuration blocks publication rather than silently allowing more methods.

### XR-69 — Stable method key identity
Policy refers to canonical method keys; UI labels/descriptions cannot alter enforcement identity.

### XR-70 — Duplicate method registration
When plugins overwrite/re-register same method key, effective owner/callback drift is detected/reviewed.

### XR-71 — Callback ownership drift
Method key unchanged but callback/provider changed triggers compatibility evidence invalidation.

### XR-72 — Allowlist stale inventory
Newly added methods remain denied by allowlist until explicitly reviewed.

### XR-73 — Denylist stale inventory
New method under denylist is surfaced as new exposure and cannot be silently certified safe.

### XR-74 — Complete Deny late registration
Late additions remain denied or certification fails explicitly.

### XR-75 — Network floor merge
Site-specific policy merges deterministically with mandatory network method floor.

### XR-76 — Preset versioning
Preset semantics/version changes use VER and never silently reinterpret an existing published policy.

### XR-77 — Policy import
Imported policy revalidates method inventory/integrations/scope and does not import secrets/runtime counters.

### XR-78 — Policy export
Export contains portable configuration, version/dependency metadata and no passwords/auth tokens/raw request logs.

### XR-79 — Policy disable vs delete
MLC disable stops WPE method overlay while preserving configuration/history as declared; delete is distinct.

### XR-80 — Corrupt/missing policy
Degraded behavior follows explicit security/recovery rule and never becomes hidden permissive state for Complete Deny claim.

# 7. Authentication, capability and object authorization fixtures

### XR-81 — Native username/password flow
Where core XML-RPC credentials remain supported, WPE never stores/logs plaintext credentials and native auth stays authoritative.

### XR-82 — Application Password flow
Certified Application Password auth succeeds/fails according to WordPress semantics; WPE policy never exposes stored secret/hash.

### XR-83 — Revoked Application Password
Revocation takes effect within native/CAC correctness semantics; stale WPE state cannot preserve access.

### XR-84 — User deletion
Deleted user credentials cannot remain authorized through cached XML-RPC method decisions.

### XR-85 — Role/capability revoke
RA authority change invalidates dependent cached access and allowed method still rechecks native/object authorization.

### XR-86 — Password change/session boundary
Password/session changes follow WordPress semantics; WPE does not invent unsupported session coupling for XML-RPC.

### XR-87 — Brute-force account target
Repeated failures can use composite account/IP RLT policy without relying on spoofable user identity.

### XR-88 — Username enumeration
Error/log/rate behavior does not unnecessarily create high-confidence account-existence oracle beyond native accepted constraints.

### XR-89 — Object IDOR read
Allowed read method cannot retrieve another protected object merely by changing ID.

### XR-90 — Object IDOR mutation
Allowed write/delete method cannot mutate unauthorized object.

### XR-91 — Site scope in Multisite auth
Authenticated network user cannot use site selector/context confusion to act on another site without authority.

### XR-92 — Super Admin boundary
Network authority is recognized only through WordPress core semantics; `switch_to_blog()` does not mint it.

### XR-93 — Method allow + native deny
WPE allow returns/permits method dispatch but native deny remains authoritative and visible as distinct result.

### XR-94 — Method deny before side effect
Denied method cannot run callback side effects before policy decision.

### XR-95 — Auth failure redaction
Wrong credentials never appear in logs/errors/support artifacts.

### XR-96 — Auth provider/plugin coexistence
Third-party auth changes are version-scoped; unknown plugin behavior is uncertified rather than assumed compatible.

# 8. Shared RLT, multicall and request-abuse fixtures

### XR-97 — RLT endpoint-policy reference
XML-RPC references registered versioned RLT policy; missing reference cannot silently disable required high-risk limit.

### XR-98 — RLT method dimension
Endpoint-wide and method-specific counters compose deterministically without accidental key collisions.

### XR-99 — RLT site isolation
Same IP/method on two sites does not collide unless explicit network-shared policy.

### XR-100 — RLT account/IP composite
Authenticated-failure throttling cannot be trivially reset by IP or account-key switching outside declared policy.

### XR-101 — Multicall outer admission
One multicall request consumes endpoint-level admission before individual method execution where configured.

### XR-102 — Multicall inner method accounting
Individual method attempts can be bounded/accounted without allowing one request to perform unbounded protected operations.

### XR-103 — Multicall mixed allow/deny
Batch containing allowed and denied methods follows explicit per-call/fail policy and cannot use allowed wrapper to invoke denied callback.

### XR-104 — Multicall duplicate mutation
Repeated same mutation inside one batch does not receive fictional exactly-once guarantees; owning method/domain idempotency remains authoritative.

### XR-105 — RLT concurrent workers
Parallel XML-RPC requests respect shared atomic limiter semantics.

### XR-106 — RLT store outage
Fail policy is explicit by risk; diagnostics do not silently present limiter as active when unavailable.

### XR-107 — RLT cleanup/cardinality
Rotating IP/method inputs cannot create unbounded persistent limiter key growth under certified profile.

### XR-108 — IPv6 rotation abuse
IPv6 key strategy documents prefix/privacy trade-offs and remains bounded.

### XR-109 — Parser element-limit + multicall
Element limits and method-count limits compose without a large bypass through nested arrays/structs.

### XR-110 — XML nesting depth
Deep nested payload is bounded by tested parser/environment behavior; unsupported control is not marketed.

### XR-111 — Large string/base64 payload
Memory/size behavior is measured and bounded by actual stack controls, especially upload/media-related methods.

### XR-112 — Abuse logging amplification
High-rate denied/parser-failed requests cannot generate unbounded DB/log writes beyond Audit/observability profile.

# 9. Pingback and outbound-request security fixtures

### XR-113 — Pingback source URL validation
Malformed/unsupported source URL fails safely before unsafe outbound processing.

### XR-114 — Pingback target validation
Target resource/site semantics are verified; arbitrary target does not create unintended privileged action.

### XR-115 — Safe HTTP delegation
Any WPE-owned outbound verification uses shared Safe HTTP rules rather than raw unrestricted request primitive.

### XR-116 — SSRF localhost
Loopback/private/link-local/metadata-service targets are denied unless a separately certified explicit use case exists.

### XR-117 — SSRF DNS rebinding
Hostname resolution/redirect behavior follows Safe HTTP policy and does not trust initial benign resolution forever.

### XR-118 — Redirect chain
Outbound redirects are bounded and each destination is revalidated.

### XR-119 — Scheme restriction
Unsafe/local schemes such as `file:` are not introduced/allowed by WPE pingback handling.

### XR-120 — Credential-bearing URL
userinfo/embedded credentials are rejected/redacted according to Safe HTTP policy.

### XR-121 — Outbound response size
Pingback verification cannot download unbounded response body under WPE-owned adapter.

### XR-122 — Outbound timeout
Timeout is bounded and failure remains truthful; no endless PHP worker hold.

### XR-123 — TLS verification
HTTPS verification follows certified WordPress/Safe HTTP policy; insecure bypass is not default.

### XR-124 — Redirect to private address
Public URL redirect cannot reach forbidden internal destination.

### XR-125 — Pingback reflection/amplification truth
Product documentation states application-level mitigation limits and does not claim universal DDoS prevention.

### XR-126 — Pingback denial + RLT
Method deny remains authoritative even if limiter would otherwise allow request.

### XR-127 — Pingback allowed + native checks
If enabled, native/site target validation and permissions still apply; WPE allow alone is insufficient.

### XR-128 — Pingback privacy/logging
Outbound URLs and remote identifiers follow PDL minimization/redaction and do not leak credentials/query secrets unnecessarily.

# 10. Integration compatibility and versioning fixtures

### XR-129 — Jetpack connectivity discovery
Certified version's actual required workflow is observed/tested rather than inferred from stale method list.

### XR-130 — Jetpack authorization workflow
Critical auth/connect steps pass only in supported policy profile; native/provider auth remains authoritative.

### XR-131 — Jetpack publishing/workflow
Representative required actions pass under certified version; unrelated denied methods remain denied.

### XR-132 — Jetpack disconnect/reconnect
Policy changes do not leave WPE reporting compatibility when integration is actually broken.

### XR-133 — WordPress mobile/client profile
Exact tested client/version workflow is recorded; generic “mobile compatible” claim is not inferred.

### XR-134 — Remote publishing client update
New client version outside certified range becomes unverified until evidence refresh.

### XR-135 — Plugin custom-method profile
Specific plugin-added method family is certified only with owner/version/callback inventory recorded.

### XR-136 — Plugin deactivation
Removed methods disappear from inventory; WPE retains safe config history but does not claim callable compatibility.

### XR-137 — Plugin reactivation/version drift
Returning/changed methods trigger revalidation before old support badge returns.

### XR-138 — WordPress core upgrade
Core method/parser/auth changes invalidate or refresh certification profile under VER/CF.

### XR-139 — PHP/XML library upgrade
Parser behavior changes are certification dimensions, not assumed transparent.

### XR-140 — Host/WAF rule change
External endpoint blocking/challenge changes integration support state even when WPE policy is unchanged.

### XR-141 — Protector rule change
Outer gate/RLT changes can invalidate XML-RPC compatibility evidence and trigger diagnostics.

### XR-142 — Complete Deny dependency warning
Known integration requirements create explicit impact preview; operator cannot mistake denial for compatible mode.

### XR-143 — Unknown integration
Unknown plugin/provider depending on XML-RPC is surfaced as uncertified risk, not silently classified safe.

### XR-144 — Versioned compatibility registry
Compatibility claims bind WordPress/plugin/client/WPE policy versions and expire/reassess on material drift.

# 11. Multisite, lifecycle, cache and observability fixtures

### XR-145 — Site-owned policy scope
Site policy has explicit durable site identity and cannot bind through request-provided site ID alone.

### XR-146 — Network-owned floor authority
Only authorized network principal can create/change network XML-RPC floor.

### XR-147 — Site stricter override
Site can add stricter rules where allowed without mutating network policy.

### XR-148 — Site cannot weaken floor
Allowlist/denylist/import/UI action cannot reduce mandatory network denial.

### XR-149 — New-site provisioning
New site receives documented network/default policy and no unrelated site's runtime counters/diagnostic state.

### XR-150 — Site clone
Clone copies only permitted configuration; RLT counters/auth facts/integration certification are revalidated, not cloned as truth.

### XR-151 — Site domain change
Host/proxy/integration assumptions are revalidated after domain/topology change.

### XR-152 — Site deletion
Site-owned policy/inventory/operational state cleans under LC without deleting shared network policy.

### XR-153 — Restore
Restored policy/inventory/cache state requires revalidation; stale integration certification is not blindly restored.

### XR-154 — CAC inventory cache
Cached method inventory keys include relevant plugin/core/policy generation and scope.

### XR-155 — CAC policy revocation
Published deny/Complete Deny bypasses stale cached allow/inventory within certified correctness semantics.

### XR-156 — CAC site isolation
One site's cached method/policy/diagnostic result cannot serve another site.

### XR-157 — Health/inventory freshness
Diagnostics show when inventory is stale/unknown and do not treat cache age as runtime method proof.

### XR-158 — First-denial correlation
Protector/RLT/XML parser/method-policy/native auth events can share safe correlation without duplicating secrets.

### XR-159 — MLC disable
Disabling XML-RPC Manager removes only WPE overlay as declared and does not falsely claim host/core endpoint disabled.

### XR-160 — Pro expiry
Safe deployed policy follows ADR-0007 and editing restrictions cannot silently re-expose denied methods or break native admin recovery.

# 12. Privacy, errors, recovery and scale fixtures

### XR-161 — Stable ERR codes
Endpoint blocked, method denied, auth failed, rate-limited, parser failed and integration-degraded states use distinct machine categories.

### XR-162 — Public error redaction
XML faults/responses never expose stack traces, SQL, filesystem paths, secrets or another site's details.

### XR-163 — Audit configuration changes
Policy/preset/network-floor/recovery changes record safe actor/scope/diff/result metadata.

### XR-164 — Request-log minimization
Raw XML body is not retained by default; any diagnostic capture is bounded, privileged, redacted and retention-scoped.

### XR-165 — Credential redaction fuzz
Passwords/Application Passwords/tokens embedded in nested arrays/structs never leak to WPE logs/support bundle.

### XR-166 — Recovery mode
Protector/WPE recovery can restore manageability but does not authenticate or override XML-RPC native auth/capabilities.

### XR-167 — Corrupt XML-RPC config
Safe recovery path exists without anonymous public bypass and without silently enabling Complete Deny-excluded methods.

### XR-168 — Support bundle
Reports endpoint/inventory/policy/RLT/proxy/integration state with preview/redaction and no sensitive request bodies.

### XR-169 — 0/10/100/custom-method inventory cost
Method discovery/policy compilation/matching overhead remains bounded.

### XR-170 — High request-rate profile
Concurrent endpoint load measures CPU/memory/parser/RLT/log cost without weakening security for performance.

### XR-171 — Large multicall profile
Controlled large batch proves method-count/element/request bounds and truthful partial results/fail policy.

### XR-172 — Parser-failure flood
Malformed XML flood does not cause unbounded logging/state amplification in supported application profile.

### XR-173 — Large-network policy lookup
100/1k/10k-site policy inheritance/inventory metadata remains site-safe and bounded.

### XR-174 — Noisy-neighbor Multisite
One site's XML-RPC abuse does not corrupt another site's limiter/cache/policy namespace; shared resource pressure is measured honestly.

### XR-175 — Compatibility regression suite
Certified WP/plugin/client profiles remain functional while prohibited methods/pingbacks continue denied after upgrades within supported range.

### XR-176 — End-to-end security truth
Representative host/Protector/RLT/parser/method/native-auth/Safe-HTTP/Multisite scenarios show zero policy bypass, zero cross-site authorization leakage and no false endpoint/WAF/control claims.

# 13. Pass / stop-the-line gates

Certification fails if:
- UI equates `xmlrpc_enabled=false` with full endpoint disable;
- Complete Deny leaves a discovered callable method in certified profile;
- late/custom method bypasses allowlist/Complete Deny claim;
- allowed method bypasses native WordPress authentication/capability/object authorization;
- spoofed proxy headers bypass RLT;
- multicall invokes denied callback or defeats advertised abuse bounds;
- WPE-owned pingback/outbound behavior permits SSRF/local-file/internal metadata access;
- credentials/raw sensitive body leak to logs/support artifacts;
- child site weakens network floor or crosses another site's policy/cache/RLT namespace;
- cache preserves denied/revoked access outside CAC contract;
- integration compatibility is claimed without exact executed version/workflow evidence;
- parser/request-limit marketing claims controls not actually enforced by tested stack;
- recovery requires anonymous/public auth bypass;
- WPE claims host/WAF/edge denial that never reached WPE as its own enforcement evidence.

# 14. Required future evidence report

Include:
- exact runtime/host/proxy/RLT/cache/plugin/client profile;
- pre/post-policy effective method inventories;
- XR-01…XR-176 pass/fail/NA;
- first-denial-layer traces;
- auth/object-IDOR tests;
- multicall/parser/rate-limit results;
- pingback/Safe HTTP SSRF evidence;
- versioned integration workflow evidence;
- Multisite floor/lifecycle/cache isolation;
- privacy/redaction/error/recovery results;
- inventory/request/multicall/large-network performance measurements;
- unsupported/degraded integrations and limitations.

# 15. Current state

**XR fixtures executed: 0/176.**  
XML-RPC runtime/integration certifications: **0**.  
Protector PR remains **0/176**, RLT **0/176**, CAC **0/176**; shared/outer evidence never auto-certifies XML-RPC.

No XML-RPC request/filter/endpoint block, parser test, rate-limit mutation, pingback/outbound call, integration workflow, Multisite operation, cache mutation or benchmark has executed.

# 16. Development gate

Execution requires explicit owner consent under ADR-0014. This protocol is planning/evidence only.