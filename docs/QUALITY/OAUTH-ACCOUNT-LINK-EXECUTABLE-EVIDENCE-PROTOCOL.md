# WPEssential — OAuth Account-Link Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0034, ADR-0101, RFC 9700, PKCE/RFC 7636, Product License, Vault, Remote Service, PDL, ERR, VER, MLC, Multisite, ADR-0014.

## 1. Purpose

Predefine evidence required before WPEssential can claim Account Linking is secure and production-ready.

Accepted first architecture:

`Authorized WP admin → local one-time transaction → WPE browser authorization → fixed WPE callback → one-time site-bound completion artifact → local redemption with PKCE S256 → short-lived access token + rotated refresh credential → signed entitlement/resource fetch`

No OAuth client/service endpoint, token, browser redirect, network request or mock server execution is authorized by this protocol.

## 2. Trust separation

These are independent truths:

`WordPress authenticated principal ≠ WPE Account identity ≠ OAuth link/connection ≠ Product entitlement ≠ Site Allocation ≠ WordPress Role/Capability ≠ Membership access ≠ update/package authority`

Linking an Account never grants WordPress administration or Membership-protected access by itself.

## 3. Public-client/security profile

The WordPress plugin is treated as a public OAuth client:
- no reusable confidential client secret shipped;
- authorization-code flow uses transaction-specific PKCE;
- `S256` required in first profile;
- redirect URIs are fixed/exactly matched by registered policy;
- state/PKCE/issuer transaction binding protects CSRF/code injection/mix-up;
- completion artifact is one-time, short-lived and site/installation/transaction bound;
- access tokens are short-lived/least-scope/server-to-server;
- refresh credential is a Vault secret with rotation/replay handling;
- device flow, if enabled, is separate fallback certification.

## 4. Fixed fixture matrix

### A. Original OA-01…OA-32 — preserved
- **OA-01** Happy-path authorized admin links correct Account/site once.
- **OA-02** State replay rejected.
- **OA-03** Completion-artifact replay rejected without duplicate allocation/link.
- **OA-04** Stolen artifact/code without verifier cannot redeem.
- **OA-05** Wrong PKCE verifier rejected.
- **OA-06** PKCE downgrade/plain/missing challenge rejected.
- **OA-07** Constant/reused PKCE challenge detected/rejected per profile.
- **OA-08** Open redirect/return URI tamper rejected.
- **OA-09** Production HTTPS return scheme downgrade rejected.
- **OA-10** Wrong issuer/environment rejected.
- **OA-11** OAuth mix-up rejected.
- **OA-12** Lower-privilege initiation denied.
- **OA-13** Capability revoked mid-flow revalidated before link completion.
- **OA-14** Different admin browser/session completion follows explicit anti-swap policy.
- **OA-15** Two simultaneous same-user/site transactions isolated.
- **OA-16** Two simultaneous admins cannot swap Account/site binding.
- **OA-17** Service unavailable before authorization expires local transaction safely.
- **OA-18** Outage after approval before redemption reconciles same logical transaction.
- **OA-19** Local persistence failure after remote success reconciles operation identity.
- **OA-20** Access-token expiry uses safe refresh path.
- **OA-21** Refresh-token rotation makes old credential non-current.
- **OA-22** Rotated-token replay detected/quarantined per service policy.
- **OA-23** Lost refresh response handles rotation ambiguity safely.
- **OA-24** Online disconnect revokes/removes usable local secret truthfully.
- **OA-25** Offline disconnect removes local usable secret and shows remote revoke pending.
- **OA-26** DB-only theft does not reveal refresh secret under certified Vault profile.
- **OA-27** Tokens absent from browser URL/referrer/history; one-time artifact cleaned.
- **OA-28** Logs/support diagnostics contain no OAuth transaction/token secrets.
- **OA-29** Site domain/host change uses Product License reconciliation; domain ≠ authentication.
- **OA-30** Staging clone cannot silently reuse production credential/binding.
- **OA-31** Reverse-proxy callback canonicalization resists Host/forwarded-header spoof.
- **OA-32** Device Authorization fallback covers expiry/poll interval/slow_down/cancel/site binding/phishing UX.

### B. Client registration, metadata and issuer binding
- **OA-33** Exact client ID/profile is environment-specific and server-owned.
- **OA-34** No client secret exists in plugin source/build/options/browser.
- **OA-35** Authorization Server Metadata issuer exactly matches configured expected issuer.
- **OA-36** Authorization endpoint comes from trusted configured/discovered metadata, not arbitrary request input.
- **OA-37** Token endpoint likewise bound to expected issuer/profile.
- **OA-38** Metadata TLS/host mismatch rejected.
- **OA-39** Metadata cache partitions production/staging/environment.
- **OA-40** Metadata expiry/version/refresh failure cannot redirect to attacker endpoint.
- **OA-41** PKCE support detection confirms S256 before flow.
- **OA-42** Missing S256 support blocks first-profile Account Link.
- **OA-43** Multiple issuers use explicit issuer response/profile binding against mix-up.
- **OA-44** Authorization response issuer mismatch fails before token exchange.
- **OA-45** Dynamic client registration is unsupported unless separately designed/certified; request cannot trigger it implicitly.
- **OA-46** Client ID cannot be chosen/overridden by browser/query payload.
- **OA-47** Environment switch invalidates/requires new transaction; no cross-environment redemption.
- **OA-48** Unknown future OAuth/service profile becomes degraded/unsupported, not loosely accepted.

### C. Local transaction/state/PKCE lifecycle
- **OA-49** State/transaction entropy meets accepted randomness profile.
- **OA-50** PKCE verifier entropy/length/charset validated.
- **OA-51** S256 challenge calculated from exact verifier bytes.
- **OA-52** Verifier stored only in private local transaction state.
- **OA-53** Verifier absent from authorization URL/browser JS/logs.
- **OA-54** Transaction is bound to installation UUID.
- **OA-55** Transaction is bound to exact target network/site scope.
- **OA-56** Transaction is bound to initiating WordPress user/actor policy as selected.
- **OA-57** Transaction expiry enforced server-side/local redemption.
- **OA-58** Consumed transaction cannot be resurrected by DB/session replay.
- **OA-59** Failed terminal transaction cannot later complete silently.
- **OA-60** Cleanup deletes/retains transaction metadata according to bounded retention without secret leakage.
- **OA-61** Concurrent cleanup never deletes active valid transaction incorrectly.
- **OA-62** Local DB rollback/restore of consumed transaction does not automatically make remote artifact reusable.
- **OA-63** Stale browser tab completion after newer link intent is conflict/rejected.
- **OA-64** Transaction fingerprint/actor capability revalidation occurs at final mutation boundary.

### D. Redirect/callback/browser safety
- **OA-65** Registered redirect URI uses exact matching; wildcard/prefix confusion rejected.
- **OA-66** Path case/encoding/dot-segment normalization cannot create alternate redirect destination.
- **OA-67** Query parameter cannot select arbitrary return host/path.
- **OA-68** Fragment-based token/artifact delivery is not used in first architecture.
- **OA-69** Client callback is not an open redirect on success.
- **OA-70** Client callback is not an open redirect on error/denial.
- **OA-71** Intended local return destination is allowlisted/canonicalized.
- **OA-72** Referrer Policy/browser history prevents sensitive artifact propagation as designed.
- **OA-73** Third-party scripts on local completion page cannot read access/refresh token because tokens are never browser-delivered.
- **OA-74** Completion page uses safe CSP/no-store/cache behavior where required.
- **OA-75** Browser back/refresh after consumption cannot repeat link mutation.
- **OA-76** Callback CSRF without valid transaction/PKCE/state fails.
- **OA-77** Authorization code injection from attacker transaction fails PKCE binding.
- **OA-78** Login CSRF/account-link swap corpus fails.
- **OA-79** Reverse proxy trusted configuration uses explicit trusted proxy model; untrusted forwarded headers ignored.
- **OA-80** Local-development callback exception cannot leak into production profile.

### E. Completion artifact and redemption
- **OA-81** Completion artifact is not an access token/refresh token/entitlement.
- **OA-82** Artifact entropy and one-time server state sufficient for profile.
- **OA-83** Artifact expiration enforced.
- **OA-84** Artifact bound to original local transaction ID.
- **OA-85** Artifact bound to installation/site allocation intent.
- **OA-86** Artifact bound to expected issuer/environment.
- **OA-87** Artifact cannot be redeemed with another transaction's verifier.
- **OA-88** Artifact cannot be redeemed by another installation/site.
- **OA-89** Same artifact concurrent redemption yields one logical success.
- **OA-90** Remote success/local timeout enters reconciliation, not duplicate new link.
- **OA-91** Token response returned only to server-side redemption channel.
- **OA-92** Error response never reflects artifact/verifier/token plaintext.
- **OA-93** Completion artifact removed from user-facing URL after processing.
- **OA-94** Completion server record terminal-state retention supports replay defense without indefinite personal-data retention.

### F. Access/refresh token and Vault lifecycle
- **OA-95** Access token stored only as long as required by service/client profile.
- **OA-96** Access token never persisted in generic options/transients/logs.
- **OA-97** Refresh token stored via Vault reference/encrypted secret envelope.
- **OA-98** Refresh token read is not exposed to UI/REST/export.
- **OA-99** Token scopes are least-privilege and service-enforced.
- **OA-100** Missing scope receives denied service response without client-side privilege inference.
- **OA-101** Refresh operation serializes/handles concurrent rotations.
- **OA-102** Two concurrent refreshes cannot leave client with silently invalid older token as current.
- **OA-103** Rotated token family replay response invalidates/quarantines appropriate local state.
- **OA-104** Vault unavailable blocks token use safely without plaintext fallback.
- **OA-105** Vault key rotation preserves valid connection or explicit degraded recovery.
- **OA-106** Database restore with stale refresh token requires reconciliation; no blind repeated use.
- **OA-107** Account password/security event remote invalidation is reflected via auth errors/reconnect flow.
- **OA-108** Connection revoke deletes local usable secret before claiming disconnected.
- **OA-109** Remote revoke unknown outcome remains pending/reconciliation, not confirmed.
- **OA-110** Token endpoint/network retries use bounded/idempotent-safe semantics appropriate to operation.

### G. WordPress authority, Product License and site allocation separation
- **OA-111** Successful Account link does not grant WordPress capability/role.
- **OA-112** Successful Account link does not grant Membership access.
- **OA-113** Successful Account link does not itself activate Pro entitlement without signed entitlement/resource truth.
- **OA-114** Product entitlement fetch after link is separately verified/signed/fresh according to license contract.
- **OA-115** Site Allocation creation/update uses server-owned resource semantics and idempotency.
- **OA-116** Site Allocation unknown outcome reconciles rather than duplicates.
- **OA-117** Local site ID/URL cannot impersonate another allocation.
- **OA-118** Site admin cannot mutate network-owned Account/allocation absent network authority.
- **OA-119** Super Admin/network authority still comes from WordPress, not Account role.
- **OA-120** Account with multiple contracts/products selects only authorized resource through service policy.
- **OA-121** Account unlink does not delete local WPE definitions/content.
- **OA-122** Entitlement expiry/outage does not invalidate OAuth credentials using fabricated equivalence; each state handled independently.
- **OA-123** OAuth outage does not become entitlement expiry.
- **OA-124** TUF/update package authority remains separate from OAuth/entitlement bearer access.

### H. Device Authorization fallback
- **OA-125** Device flow enabled only by explicit profile/feature support.
- **OA-126** Device code secret remains server-side/local private state.
- **OA-127** User code displayed with anti-phishing context and exact WPE verification origin.
- **OA-128** Poll interval enforced.
- **OA-129** `slow_down` honored.
- **OA-130** Authorization pending handled without aggressive polling.
- **OA-131** Expired token/device code stops polling and cannot complete.
- **OA-132** User denial/cancel terminal state honored.
- **OA-133** Device authorization binds installation/site/link intent.
- **OA-134** Another site/device cannot steal completed device authorization.
- **OA-135** Device flow still revalidates local WordPress actor before committing link.
- **OA-136** Device flow tokens receive same Vault/rotation/scope protections as browser flow.

### I. Multisite, clone/lifecycle and environment
- **OA-137** Network-level Account link ownership explicit in Multisite.
- **OA-138** Site-level allocation/reference cannot expose network refresh credential to Site Admin UI.
- **OA-139** Same local numeric site ID in another network cannot collide with installation/network identity.
- **OA-140** Subsite clone does not copy usable OAuth credential as a new independent connection.
- **OA-141** Whole-network clone/staging restore follows environment clone policy and revalidation.
- **OA-142** Site creation does not auto-link remote account without explicit allocation policy.
- **OA-143** Site deletion removes/updates allocation according to Product License lifecycle without deleting Account itself.
- **OA-144** Domain change updates projected site metadata through reconciliation, not identity replacement.
- **OA-145** Module disable preserves/removes active credential use according to explicit lifecycle while preventing hidden background calls.
- **OA-146** Plugin deactivation stops WPE remote token usage.
- **OA-147** Pro expiry does not expose/delete OAuth secrets incorrectly.
- **OA-148** Uninstall cleanup follows explicit local/remote deletion/revocation policy; remote deletion is not assumed from local uninstall.

### J. Privacy, abuse, observability and regression
- **OA-149** Pre-link disclosure states exact data/purpose transmission.
- **OA-150** Account link does not silently enable unrelated telemetry.
- **OA-151** Only documented site/account fields transmitted for linking/allocation.
- **OA-152** IP/user-agent/request metadata retention follows remote-service privacy policy.
- **OA-153** RLT protects initiation/redemption/device polling without becoming authorization.
- **OA-154** Brute-force artifact/state/code attempts are bounded and non-enumerating.
- **OA-155** Error messages do not reveal whether arbitrary Account/site/token exists beyond authorized context.
- **OA-156** Audit records actor/site/operation/result/correlation with secrets redacted.
- **OA-157** Support bundle contains connection status/public IDs only per policy, no token/verifier/artifact.
- **OA-158** Error taxonomy distinguishes denied/expired/replay/mix-up/issuer/network/Vault/reconciliation/entitlement states.
- **OA-159** Service 429/5xx/outage respects backoff and truthful state.
- **OA-160** CAC/cache state cannot serve stale connected/entitled truth as authority.
- **OA-161** 100 simultaneous link initiations remain isolated/bounded.
- **OA-162** 1k/10k-site network connection/allocation metadata remains scope-safe.
- **OA-163** High-latency/partial network failure injection preserves one logical link identity.
- **OA-164** Browser matrix validates referrer/history/cookie/redirect behavior on supported browsers.
- **OA-165** Reverse proxy/CDN matrix validates exact callback and trusted-header semantics.
- **OA-166** Service/API version upgrade remains VER-scoped; unknown version degrades.
- **OA-167** OAuth library upgrade regression retains PKCE/state/issuer/redirect guarantees.
- **OA-168** Restore/clone/replay adversarial corpus yields zero unauthorized usable production credential.
- **OA-169** Open-redirect/mix-up/code-injection/CSRF corpus yields zero unauthorized link.
- **OA-170** Token leak scan covers source/options/cache/log/URL/browser/support/export.
- **OA-171** Vault/DB theft model verifies no plaintext refresh credential under certified key profile.
- **OA-172** Disconnect/reconnect race yields deterministic single current connection state.
- **OA-173** Account switch from A→B requires explicit unlink/relink/ownership policy; no silent token substitution.
- **OA-174** Remote service deletion request remains distinct from disconnect/local secret deletion.
- **OA-175** Full privacy/retention/export/erase regression preserves required security replay records without secret retention.
- **OA-176** Final certification pins exact OAuth server/client/API/Vault/browser/proxy/Multisite/environment profile; no generic “OAuth secure” claim beyond executed evidence.

## 5. Independent certification classes

Future reports record separately:
- `OA-C` client/metadata/issuer/PKCE;
- `OA-B` browser/callback/redirect safety;
- `OA-T` token/Vault lifecycle;
- `OA-L` Account/Product License/site-allocation reconciliation;
- `OA-D` Device Authorization;
- `OA-M` Multisite/clone/lifecycle;
- `OA-P` privacy/abuse/observability/regression.

Passing one class never promotes another.

## 6. Stop-the-line gates

Account linking remains uncertified if any tested profile:
- requires a reusable shipped client secret;
- accepts `plain`/missing PKCE in first profile;
- accepts arbitrary/non-exact return redirect;
- accepts wrong issuer/mix-up response;
- allows artifact/code replay to create another usable connection;
- completes after actor authority revocation without required revalidation;
- exposes access/refresh token/verifier/artifact in URL/browser/log/export/support;
- stores refresh credential outside certified Vault semantics;
- lets cloned/stolen DB silently obtain usable production credential contrary to clone policy;
- treats Account link as WordPress/Membership/Product entitlement authority.

## 7. Required future evidence report

Include exact client/service/OAuth server/API versions, PKCE/issuer/redirect metadata, token lifetimes/rotation/replay behavior, OA-01…OA-176 pass/fail/NA, browser/proxy matrix, Vault profile, Product License/site allocation reconciliation, Device flow if enabled, Multisite/clone/privacy findings and independent security review status.

## 8. Current state

**OA fixtures documented: 176.**  
**OA fixtures executed: 0/176.**  
OAuth Account-Link runtime certifications: **0**.

No OAuth endpoint, transaction, browser redirect, token exchange, refresh/revoke, Site Allocation mutation, Device Authorization poll, Vault secret operation or network request has run.

## 9. Development gate

Execution requires explicit owner consent under ADR-0014 and the Approval Ledger. This document authorizes planning only.
