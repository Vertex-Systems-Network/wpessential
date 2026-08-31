# WPEssential — Connection Adapter Provider Certification Executable Evidence Protocol

Status: **Phase 0 planning only / NOT AUTHORIZED FOR EXECUTION**  
Date: 2026-08-28  
Work package: `P0-M00-WP59`  
Related: ADR-0040, ADR-0055, ADR-0080, ADR-0122, Connection & Integration Adapter Certification Contract, Webhooks/Connections/Event Inbox protocol, Vault, Safe HTTP, Policy/Abilities, JobService, Workflow, Privacy, Error Taxonomy, Contract Versioning, Multisite/Site Lifecycle.

## 1. Purpose

Define the provider-profile certification overlay required to award I0–I5 to a specific Connection adapter/provider/API/capability profile.

This protocol does **not** replace `WC-01…WC-156`. WC remains the generic Webhooks/Connections/Event Inbox runtime evidence domain. `ICP-F001…ICP-F176` proves the exact provider/profile/capability certification layer that sits on top of those generic contracts.

Current truth:
- ICP-F documented: **176**;
- ICP-F executed: **0/176**;
- I4-certified provider profiles: **0**;
- I5 Production Profile Certified provider profiles: **0**;
- no provider/API capability is promoted to runtime certification by static documentation or connection success.

## 2. Hard truth boundaries

- adapter installed **≠** provider compatible;
- schema/configuration valid **≠** provider connected;
- Test Connection success **≠** read/write/event capability certification;
- authentication certified **≠** authorization to perform a WPE Ability;
- I2 read certification **≠** I3 write certification;
- one write action certified **≠** every mutation certified;
- webhook endpoint reachable **≠** webhook authenticity certified;
- webhook signature valid **≠** business fact correct/current;
- provider event accepted **≠** owning business-domain transition complete;
- HTTP 2xx **≠** downstream provider/business completion when protocol does not guarantee it;
- JobService at-least-once **≠** exactly-once external mutation;
- provider documentation/changelog **≠** runtime I-level certification;
- generic Connection certification **≠** Backup C-level, Email ET-level or Membership MB-level certification.

## 3. Certification identity

Every future certification result is scoped to:

`adapter_key + adapter_version + provider_key + provider_profile_version + provider_api_version + environment/region + auth_profile + capability_key`

Record also:
- WordPress/PHP/runtime matrix;
- Vault/credential profile;
- Safe HTTP policy version;
- webhook signature/event profile where applicable;
- Event Inbox profile;
- JobService/Workflow profile for asynchronous capability;
- Multisite ownership profile;
- privacy/retention configuration;
- certification date, evidence IDs and review class.

A provider brand never receives one permanent global certification badge detached from capability/version/profile.

## 4. I0–I5 ladder — preserved

### I0 — Detected / Configurable
Adapter loads, schema/version is known, capability declarations validate. **No provider connectivity claim.**

### I1 — Authentication Certified
Exact authentication profile proves valid/invalid/expired/revoked behavior, safe Vault storage/redaction, refresh/revoke where applicable, and correct provider account/tenant identity.

### I2 — Read Certified
Specific read/list/get/search/query capability is certified, including pagination, field mapping, provider scopes, rate limits and data-shape/version handling.

### I3 — Write / Action Certified
Specific mutation/action is certified independently, including typed input, WPE authorization, provider permission, idempotency/unknown outcome/reconciliation, errors and audit.

### I4 — Event / Reconciliation Certified
Specific provider event capability proves authenticity, duplicate/replay/out-of-order handling, subscription lifecycle, Event Inbox normalization and source-of-truth reconciliation.

### I5 — Production Profile Certified
All capabilities WPE publicly advertises for the exact profile have their required evidence; unsupported capabilities remain explicit; failure/recovery/runbook/version/privacy/security evidence is closed for that profile.

# Fixed executable evidence protocol — ICP-F001…ICP-F176

The following 16 groups contain 11 ordered fixtures each, totaling **176**. Within each group, scenarios map sequentially to the stated ID range.

No fixture is passed by code inspection, provider docs, a saved secret, OAuth success, Test Connection success, one API call or one webhook receipt. Execution requires explicit owner consent under ADR-0014.

## Group 1 — Adapter/provider/profile identity — ICP-F001…ICP-F011

1. adapter key/version resolves deterministically;
2. provider key/profile version resolves deterministically;
3. provider API/schema version captured;
4. environment/test/live identity captured;
5. region/national-cloud/tenant variant captured;
6. auth profile/version captured;
7. capability key is explicit and typed;
8. unsupported capability cannot be inferred from neighboring capability;
9. newer unverified provider/API version becomes unverified, not silently certified;
10. deprecated/security-blocked profile cannot preserve Production Certified label;
11. certification result cannot be reused across materially different provider/profile/capability identity.

## Group 2 — I0 adapter/configuration/dependency evidence — ICP-F012…ICP-F022

1. adapter loads without provider-connectivity claim;
2. configuration schema validates required non-secret fields;
3. missing adapter dependency produces explicit degraded state;
4. unsupported API/profile blocks capability publish/use;
5. invalid capability declaration rejected;
6. Connection Definition cannot reference arbitrary unregistered adapter class/callback;
7. adapter registration grants no Policy/Vault/Safe HTTP bypass;
8. disabled adapter blocks new operations safely;
9. adapter upgrade with contract incompatibility marks profile unverified;
10. extension/license/dependency loss preserves data but blocks unsupported runtime action truthfully;
11. I0 UI label never implies Connected/Supported/Production Certified.

## Group 3 — I1 authentication / Vault / account identity — ICP-F023…ICP-F033

1. valid credential authenticates exact declared provider profile;
2. invalid credential is normalized without secret leakage;
3. expired credential/token becomes explicit reauth/degraded state;
4. revoked credential becomes explicit reauth/degraded state;
5. credential is stored only through Vault reference;
6. saved credential remains write-only in ordinary UI/API;
7. least required scope set is verified where provider exposes scopes;
8. insufficient scope downgrades only affected capabilities;
9. provider account/tenant identity is verified and shown safely;
10. valid token for wrong tenant/account cannot silently bind expected Connection;
11. I1 certification is limited to the exact auth method/profile tested.

## Group 4 — OAuth / credential lifecycle / rotation — ICP-F034…ICP-F044

1. OAuth state validation rejects mismatch/replay;
2. public-client profile uses PKCE S256 where applicable;
3. redirect/callback target is exact/allowlisted;
4. issuer/authorization/token-origin mix-up is rejected;
5. authorization code/access/refresh tokens never enter ordinary logs/URLs;
6. refresh flow updates newest token safely;
7. concurrent refresh cannot overwrite newer token with stale result;
8. rotating refresh-token replay/stale token behavior follows provider profile;
9. credential/API-key rotation has deterministic cutover;
10. disconnect/revoke distinguishes local removal from remote revoke truth;
11. provider auth mechanism change triggers affected I-level recertification.

## Group 5 — Safe HTTP / endpoint / SSRF / redirect profile — ICP-F045…ICP-F055

1. fixed provider host allowlist accepts only intended host;
2. custom endpoint uses stricter trust/SSRF policy;
3. loopback/private/link-local/cloud-metadata destinations are blocked by generic external profile;
4. alternate numeric/encoded IP forms cannot bypass classification;
5. DNS rebinding cannot move approved public host to blocked destination undetected;
6. every redirect is independently revalidated;
7. bearer/API credential is not forwarded to untrusted redirect host;
8. TLS certificate/hostname verification cannot be disabled by ordinary configuration;
9. unexpected port/scheme/method is rejected unless certified capability requires it;
10. request/response/decompression/time limits are bounded;
11. Safe HTTP policy bypass by provider adapter fails certification immediately.

## Group 6 — I2 read/list/get/query capability — ICP-F056…ICP-F066

1. declared list/read capability returns mapped typed data;
2. get-by-stable-ID returns correct resource;
3. unsupported read endpoint remains unavailable;
4. provider pagination first page maps correctly;
5. continuation/cursor pagination completes without skip/duplicate beyond provider semantics;
6. malformed/unknown response schema fails safely;
7. sensitive provider fields are filtered/classified before generic exposure;
8. rate-limit/quota response is normalized and bounded;
9. partial provider outage does not fabricate empty-success data;
10. provider object/account scope cannot cross WPE site/network policy;
11. I2 badge attaches to exact read capability key, not entire provider.

## Group 7 — I3 create/update/delete/action authorization — ICP-F067…ICP-F077

1. typed create/action input validates before provider call;
2. WPE Capability/target Policy authorizes operation separately from provider auth;
3. provider scope/permission is checked for declared action;
4. create success records stable provider resource/request identity;
5. update targets exact intended resource/version where provider supports concurrency control;
6. delete/revoke high-risk action has separate certification from benign write;
7. mass-assignment/unmapped fields cannot reach provider mutation;
8. cross-site Connection/resource IDOR is denied;
9. provider validation/conflict error maps to stable WPE error without raw secret leakage;
10. Audit records safe action metadata/correlation without becoming business truth;
11. one I3 action cannot automatically certify another mutation.

## Group 8 — I3 idempotency / unknown outcome / JobService — ICP-F078…ICP-F088

1. provider idempotency key is used only according to certified semantics;
2. duplicate Job before send does not duplicate mutation;
3. worker crash before send retries safely;
4. timeout/lost response after send becomes outcome-unknown, not assumed failure;
5. status/query/reconciliation resolves unknown outcome where provider supports it;
6. blind retry is blocked when mutation cannot be proven idempotent/reconcilable;
7. duplicate provider response/event maps to one logical action where intended;
8. retry/backoff honors 429/Retry-After/5xx profile;
9. retry budget prevents unbounded duplicate external effects;
10. cancellation after possible send preserves truthful unknown/reconciliation state;
11. JobService at-least-once never becomes exactly-once provider claim.

## Group 9 — Inbound webhook authenticity / replay — ICP-F089…ICP-F099

1. exact endpoint/provider profile is identified before business dispatch;
2. required raw-body signature verifies known-good request;
3. invalid signature is rejected before processable business event;
4. signature algorithm/key-version mismatch is rejected;
5. signing-key rotation overlap follows bounded provider profile;
6. stale timestamp is rejected where provider protocol supplies timestamp semantics;
7. excessive future skew is rejected;
8. nonce/replay token is enforced where available;
9. repeated provider event/delivery ID dedupes idempotently;
10. same trusted event ID with materially conflicting payload enters conflict/reconciliation;
11. unsigned provider requires separately certified alternative authenticity profile and is never mislabeled verified.

## Group 10 — I4 Event Inbox / normalization / ordering — ICP-F100…ICP-F110

1. verified ingress is durable before asynchronous consumer dispatch;
2. site/network scope comes from trusted endpoint/Connection binding;
3. provider/profile/version is pinned in Event Inbox envelope;
4. raw provider payload validates into typed normalized event schema;
5. unknown event type is quarantined/ignored safely without arbitrary action execution;
6. duplicate delivery cannot repeat owning-domain transition;
7. out-of-order events do not automatically become business chronology;
8. same provider event ID across sites cannot collide;
9. consumer crash/retry preserves inbox and consumer idempotency separation;
10. raw payload retention follows privacy/minimization policy;
11. Event Inbox accepted event remains evidence, not owning Membership/Email/Workflow/business truth.

## Group 11 — I4 provider source-of-truth reconciliation — ICP-F111…ICP-F121

1. ambiguous event triggers provider current-state reconciliation where supported;
2. provider API current object can supersede stale event chronology according to domain rule;
3. reconciliation uses exact Connection/provider/account scope;
4. provider API unavailable leaves explicit pending/unknown state;
5. deleted/missing provider resource is distinguished from permission/auth failure;
6. list-events/status API gaps are documented as limitation rather than guessed;
7. duplicate reconciliation Job is idempotent at consumer boundary;
8. provider event loss/outage recovery reconstructs declared state where capability allows;
9. webhook subscription recreation does not duplicate downstream logical subscription unnoticed;
10. reconciliation cannot bypass owning-domain Policy/transition rules;
11. I4 award requires both event authenticity and declared reconciliation evidence, not webhook receipt alone.

## Group 12 — Webhook subscription lifecycle / clone / restore — ICP-F122…ICP-F132

1. subscription create uses exact provider account/environment/profile;
2. duplicate create avoids duplicate active subscription where provider supports discovery/idempotency;
3. subscription renew/expiry behavior is handled before lapse where advertised;
4. subscription delete result is represented truthfully;
5. unknown delete outcome reconciles;
6. disabled Connection stops/reconciles subscription according to lifecycle policy;
7. restored site does not blindly reactivate copied production subscription;
8. cloned/staging site does not receive/send production provider events accidentally;
9. endpoint URL/domain change triggers subscription health/rebind workflow;
10. site deletion cleans only owned provider subscription according to policy;
11. network-shared subscription routing remains explicit and cannot infer target from attacker payload.

## Group 13 — Provider errors / rate / quota / backpressure / resource limits — ICP-F133…ICP-F143

1. provider 400 validation maps to stable typed error;
2. 401/403 distinguishes auth vs scope/permission where provider semantics allow;
3. 404/resource missing does not automatically mean provider outage;
4. 409/concurrency/conflict follows declared recovery action;
5. 429 respects provider backoff guidance;
6. 5xx/transient outage retries only safe/reconcilable operations;
7. quota exhausted becomes explicit degraded/capability state;
8. oversized provider response is bounded before memory exhaustion;
9. slow provider cannot monopolize unbounded workers;
10. queue/backpressure protects noisy provider/tenant from starving unrelated work;
11. degraded provider health never silently preserves an unqualified I5 claim.

## Group 14 — Privacy / security / audit / data handling — ICP-F144…ICP-F154

1. per-capability sent fields are documented/classified;
2. received PII/sensitive fields are minimized and access controlled;
3. request/response bodies are off/redacted in ordinary logs by default;
4. secrets/tokens/signatures/query credentials are redacted;
5. provider request/correlation IDs are retained only as safe metadata;
6. exporter includes applicable local Connection metadata without secrets;
7. eraser/disconnect/local delete does not falsely claim provider-side deletion;
8. retention rules distinguish Event Inbox, logs, domain facts and provider data;
9. provider error body cannot inject executable admin HTML/JS;
10. security incident can block affected profile/capability without corrupting unrelated Connections;
11. audit/diagnostics never become authorization or provider-source truth.

## Group 15 — Multisite / ownership / provider tenant routing — ICP-F155…ICP-F165

1. site-owned Connection cannot be used by another site via UUID manipulation;
2. network-owned Connection requires explicit network authority;
3. delegated site use follows explicit allowlist/policy;
4. current-blog context cannot substitute for durable ownership;
5. same provider resource/event IDs across sites remain isolated;
6. shared provider tenant routing uses trusted mapping, not payload-supplied site ID;
7. ambiguous site routing quarantines rather than guesses;
8. site transfer/domain change preserves durable Connection ownership separately from hostname;
9. site clone creates explicit environment/rebind state rather than copying live external effects;
10. site deletion does not revoke/delete another site's shared external resource;
11. network-wide capability certification does not imply every site is authorized to use it.

## Group 16 — I5 closure / version drift / production profile — ICP-F166…ICP-F176

1. every publicly advertised capability key has required I-level evidence;
2. unsupported capability remains explicit and unavailable;
3. provider plan/tier limitation is recorded in exact profile;
4. API deprecation/version sunset produces warning/downgrade/recertification path;
5. adapter major change triggers affected profile recertification;
6. provider auth/signature/event-schema change triggers affected evidence rerun;
7. failure/recovery runbook exists for advertised capabilities;
8. privacy/security/Multisite lifecycle limitations are closed or explicitly unsupported;
9. regression can downgrade one capability without falsifying unrelated certified capabilities;
10. production certification report records Pass/Fail/Not Run and exact version/profile scope;
11. I5 is awarded only to exact profile whose advertised capability set is fully closed—never to a provider brand globally.

## 5. Certification mapping rules

- **I0** requires applicable Groups 1–2 evidence and makes no connectivity claim.
- **I1** additionally requires Groups 3–5 as applicable to the auth/endpoint profile.
- **I2** additionally requires Group 6 for each advertised read capability.
- **I3** additionally requires Groups 7–8 for each advertised write/action capability.
- **I4** additionally requires Groups 9–12 for each advertised event/reconciliation capability.
- **I5** additionally requires applicable Groups 13–16 and closure of all publicly advertised capability keys.
- A provider profile cannot skip lower required evidence because a higher-level happy path worked.
- A narrow I5 profile is allowed; unsupported capabilities must remain explicit.
- WC generic evidence and ICP-F provider evidence are both required where applicable; neither automatically passes the other.

## 6. Stop-the-line conditions for future execution

Stop certification immediately if evidence shows:
- secret/token/signing-key leakage;
- TLS/hostname/SSRF/redirect policy bypass;
- provider adapter bypassing Vault/Safe HTTP/Policy;
- cross-site/network Connection or provider-resource leakage;
- unauthenticated/unverified webhook causing business mutation;
- replay/duplicate creating repeated irreversible side effect;
- blind retry after unknown non-idempotent external mutation;
- provider event payload directly selecting trusted WPE scope;
- static provider docs/Test Connection used as justification for runtime I-level award;
- provider/API version outside certified scope presented as Production Certified.

## 7. Required future certification report

Every executed provider-profile certification records:
- work and ICP-F/WC fixture IDs;
- adapter/provider/API/auth/environment/region versions;
- capability keys and I-level reached per capability;
- WordPress/PHP/runtime/Multisite profile;
- Vault/Safe HTTP/Event Inbox/JobService profiles;
- Pass/Fail/Not Run evidence;
- retry/reconciliation/unknown-outcome findings;
- security/privacy findings;
- known limitations/unsupported capabilities;
- version/deprecation/recertification trigger;
- reviewer class and artifact references;
- exact UI/marketing label permitted.

No secret or reusable credential appears in the report.

## 8. Runtime state / development gate

**NOT EXECUTED.** ICP-F executed **0/176**. I4-certified profiles **0**. I5-certified profiles **0**.

No provider account, credential exchange, OAuth flow, network/API request, webhook registration/delivery, Event Inbox runtime operation, Job, external mutation, reconciliation or benchmark is authorized until explicit scoped owner development consent is recorded under ADR-0014.