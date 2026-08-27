# WPEssential — XML-RPC Manager Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0052, `docs/SECURITY/XML-RPC-LAYERED-ENFORCEMENT-COMPATIBILITY.md`, Protector/Rate Limit, Multisite, ADR-0014.

## 1. Purpose

Define evidence required before WPEssential can claim XML-RPC policy, Complete Deny, compatibility profiles, parser limits or observability behavior is supported on a certified WordPress/plugin/host environment.

The terminology invariant is fixed:

**`xmlrpc_enabled = false` is not equivalent to “XML-RPC endpoint disabled”. Endpoint reachability, method registry, authenticated-method enable state, pingback/custom methods and outer request gating are separate layers.**

## 2. Runtime profile

Every future certification records:
- WordPress/PHP versions;
- single-site/Multisite topology;
- effective XML-RPC method inventory;
- plugins/integrations that register or depend on methods;
- host/CDN/WAF/reverse-proxy endpoint behavior;
- Protector/trusted-proxy/rate-store profile;
- WPE rule mode/preset/revision;
- parser element-limit configuration;
- loopback/self-test availability;
- logging/retention profile.

Certification is configuration-specific. New plugin-added methods can change the effective surface.

## 3. Layer and inventory fixtures

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

## 4. Policy fixtures

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

## 5. Authentication/authorization fixtures

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
Untrusted `X-Forwarded-For`/similar input cannot evade XML-RPC rate limits; trusted-proxy resolution is shared with Protector.

## 6. Pingback/request-abuse fixtures

### XR-23 — Pingback denied
Pingback method can be blocked independently while other permitted XML-RPC methods remain operational.

### XR-24 — Pingback allowed baseline
If allowed by a certified profile, normal native permission/request behavior remains intact and WPE does not weaken safe HTTP handling.

### XR-25 — Pingback rate limit
Repeated pingback attempts trigger configured shared rate policy without unbounded state growth.

### XR-26 — Endpoint request-rate limit
High-rate XML-RPC requests are bounded atomically across concurrent workers under the certified rate-store profile.

### XR-27 — Rate-store failure
Failure of shared rate store follows explicit fail-open/fail-closed/degraded product policy; UI/diagnostics state actual behavior.

### XR-28 — Batched/multicall abuse profile
Large/many-method XML-RPC calls are bounded by certified parser/rate/request limits without claiming an unsupported universal body-size hook.

## 7. Parser and malformed-input fixtures

### XR-29 — `xmlrpc_element_limit` below threshold
Configured supported element limit rejects over-limit request according to tested WordPress behavior.

### XR-30 — `xmlrpc_element_limit` compatibility
Normal certified integration request stays within configured limit and does not break silently.

### XR-31 — Malformed XML
Parser failure is safe, bounded and does not expose stack traces/secrets.

### XR-32 — Oversized/deep request environment boundary
Host/PHP/Protector constraints are measured/documented separately from WordPress element limit; WPE does not claim controls it does not own.

### XR-33 — Method-name normalization
Malformed/unexpected method names cannot bypass exact registry rule matching.

### XR-34 — Invalid encoding/entity behavior
Parser behavior is tested for supported XML parser profile; no arbitrary external entity/file retrieval is introduced by WPE.

## 8. Compatibility fixtures

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

## 9. Multisite/observability fixtures

### XR-42 — Multisite site isolation
Site-scoped XML-RPC rule/inventory state does not cross same-slug/site IDs unless network policy explicitly owns it.

### XR-43 — Network policy floor
Child site cannot weaken network-enforced Complete Deny/method floor under the certified resolution order.

### XR-44 — Site-specific integration
Integration requirement bound to one site does not automatically loosen network/other-site policy.

### XR-45 — Logging redaction
Logs contain method/result/correlation and approved minimal metadata only; passwords/tokens/raw request bodies/sensitive arguments are absent by default.

### XR-46 — Observability hook coverage
Logging coverage is measured honestly; methods not visible to the chosen observation hook remain enforced at registry/outer layers and diagnostics state the limitation.

### XR-47 — Method inventory drift
Plugin activation/update adding/removing methods changes health/inventory state and triggers review warning without silently reclassifying support.

### XR-48 — Pro expiry/degraded runtime
Existing safe policy runtime follows ADR-0007; editing entitlement changes do not unexpectedly re-expose methods or lock administrator recovery.

## 10. Pass gates

XML-RPC support/preset certification fails if:
- UI equates `xmlrpc_enabled=false` with full endpoint disable;
- Complete Deny leaves a discovered callable method in the certified profile;
- allowed method bypasses native WordPress authorization;
- spoofed proxy headers bypass rate policy;
- new custom method silently bypasses allowlist/Complete Deny claim;
- sensitive credentials/raw request bodies leak to logs;
- Jetpack/remote publishing compatibility is claimed without executed critical workflow evidence;
- parser/request-limit marketing claims controls that are not actually enforced by the tested stack;
- child site weakens declared network floor;
- WPE recovery requires anonymous/public bypass.

## 11. Required future evidence report

Include:
- runtime/host/plugin profile;
- effective pre/post-policy method inventories;
- XR-01…XR-48 pass/fail/NA;
- first-denial-layer diagnostics;
- parser/rate-limit results;
- compatibility profile evidence;
- proxy-spoof tests;
- Multisite resolution results;
- logging-redaction sample/schema;
- limitations and uncertified integrations.

## 12. Current state

**XR fixtures executed: 0/48.**

No XML-RPC request, filter, endpoint block, parser test, rate-limit mutation, integration call or runtime compatibility test has been executed.

## 13. Development gate

Execution requires explicit owner consent under ADR-0014.