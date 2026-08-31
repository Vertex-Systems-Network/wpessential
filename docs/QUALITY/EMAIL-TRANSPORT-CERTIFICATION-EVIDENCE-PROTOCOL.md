# WPEssential — Email Transport Certification Evidence Protocol

Status: **Phase 0 protocol only / DO NOT EXECUTE without explicit owner consent**  
Governs: ADR-0026, ADR-0029, ADR-0040, ADR-0058, ADR-0063, ADR-0067, ADR-0079 and ADR-0172.

## Purpose

Define repeatable evidence required before an email transport/provider profile may claim ET0–ET5 capabilities.

This file authorizes **nothing**. SMTP connections, provider credentials, DNS changes, webhook endpoints, sends, queues, provider API calls, fixtures and runtime tests remain blocked by ADR-0014.

The canonical executable evidence matrix is **ET-F001…ET-F176**. Current execution truth is **0/176**.

The existing six provider profiles remain **6 EE3 static evidence profiles / 0 ET-certified runtime profiles** until future scoped execution proves otherwise.

## Two-layer evidence model

The protocol deliberately keeps two different concepts separate:

1. **ET0–ET5** are certification maturity levels for one exact transport/provider profile.
2. **ET-F001…ET-F176** are executable evidence fixtures used to prove or disprove the requirements behind those levels.

Fixture completion never renames or replaces an ET level. Static provider research such as **EE3** never implies ET0. A profile may claim only the highest ET level whose required lower-level evidence is also satisfied for that same pinned profile.

## Non-negotiable delivery truth

These states are never synonyms:

`Rendered Message → Transport Attempt → submission accepted/unknown/rejected → provider queued/accepted → receiving-server accepted → deferred/bounced/dropped/complained/suppressed → engagement signal`

In particular:

- renderer success does not prove submission;
- local transport success does not prove provider acceptance;
- provider acceptance does not prove mailbox or inbox delivery;
- receiving-server acceptance is the strongest standard `Delivered` claim unless stronger certified evidence exists;
- open/click signals do not prove human reading, human viewing, or human intent;
- complaint, suppression and unsubscribe are distinct source facts and policy inputs, not retroactive erasure of prior delivery facts.

## Certification identity

Every report pins:

- WPE version/commit;
- transport adapter version;
- provider/product;
- provider API/event schema version or dated documentation profile;
- SMTP mode/relay when relevant;
- authentication mode;
- event/webhook security mode;
- provider region/account/message-stream/subaccount scope where relevant;
- PHP/WordPress environment;
- Job Service adapter/backend profile;
- Vault/credential profile;
- Notification/Email schema revision;
- Event Inbox/Webhook profile where used;
- Multisite/network/site scope;
- test domain/environment class;
- certification date.

A result is not automatically portable to a materially different provider/product/profile/version/account region/security mode/site scope or adapter.

---

# ET0 — Configured / Connectable

Future evidence:

- credentials/configuration accepted;
- bad credentials rejected safely;
- least privilege/scopes documented;
- secret values never returned to browser/log;
- TLS verification behavior;
- sender/domain configuration prerequisites surfaced;
- connection health distinguishes unknown/unconfigured/invalid/temporarily unavailable where observable;
- site/network scope and provider-account ownership are explicit.

Pass does not authorize a send-support claim.

---

# ET1 — Submission Certified

Future evidence:

- deterministic Rendered Message accepted by adapter;
- invalid From/recipient/header rejected safely;
- synchronous provider/API rejection normalized;
- transport/provider message ID captured where supplied;
- recipient-specific provider ID handling documented;
- attachment and message-size boundary behavior;
- Unicode subject/display-name/header behavior;
- test-send isolation from production Notification workflow;
- `wp_mail()` true maps only to local transport processed;
- generic SMTP acceptance maps only to the certified accepting hop.

Pass criteria:

- no UI/log claim stronger than observed evidence;
- one Transport Attempt is persisted per actual submission try;
- logical Recipient Delivery and physical Transport Attempt remain distinct.

---

# ET2 — Resilient Submission Certified

Fault evidence includes:

- DNS/connect failure before request transmission;
- TLS failure;
- timeout before request body/write;
- connection loss after possible provider acceptance;
- SMTP 4yz transient response;
- SMTP 5yz permanent response;
- provider HTTP 429 + Retry-After;
- provider 5xx;
- provider outage;
- revoked/rotated credential;
- queue backlog;
- Job worker crash during attempt bookkeeping.

Unknown-outcome evidence is mandatory.

Verify:

- `submission_unknown` is preserved where acceptance cannot be known;
- idempotency key is used only where the provider contract supports the required semantics;
- status/event reconciliation is attempted where certified;
- blind immediate retry does not create uncontrolled duplicates;
- retry produces a new Transport Attempt under the same logical Recipient Delivery;
- bounded exponential/backoff and Retry-After behavior is truthful;
- permanent errors do not tight-loop;
- JobService at-least-once execution never becomes an exactly-once email claim.

---

# ET3 — Delivery Truth Certified

Event evidence for every advertised event capability includes:

- provider accepted/queued;
- receiving-server delivery confirmation;
- delivery delayed/deferred;
- temporary failure;
- permanent/hard failure/bounce;
- provider dropped/rejected when distinct;
- multi-recipient event correlation;
- duplicate event;
- out-of-order event;
- delayed/asynchronous failure after earlier acceptance;
- unknown provider event type;
- event for unknown provider message ID;
- late event after local log archival boundary.

Security evidence includes:

- valid webhook auth/signature;
- tampered body/signature;
- wrong provider/profile;
- stale/replayed event where provider profile supports timestamp/replay checks;
- key/signing profile rotation where applicable.

Pass criteria:

- Provider Event Ledger/Event Inbox preserves source facts;
- derived outcome is deterministic and truthful;
- `Delivered to Receiving Server` occurs only from certified destination-server acceptance evidence;
- no inbox/read/human-view claim is inferred.

---

# ET4 — Feedback / Suppression / Reconciliation Certified

Advertised capability evidence includes:

- spam complaint;
- provider suppression before send;
- provider unsubscribe event;
- suppression removal/reactivation policy if provider permits;
- full/partial event outage followed by status/reconciliation/backfill where provider offers it;
- provider event retention expiry;
- webhook disabled/re-enabled;
- bounce then manual address correction;
- complaint after delivery event;
- WPE optional-category preference vs provider-global suppression interaction;
- provider raw classification changes/unknown values.

Tracking, only if advertised:

- open tracking enabled/disabled;
- click tracking enabled/disabled;
- privacy-proxy/scanner-safe UI language;
- retention off/short/default behavior.

Pass criteria:

- complaint/suppression does not erase historical delivery evidence;
- provider suppression does not silently mutate unrelated WPE channels or user preferences;
- open/click never maps to Read/Human Viewed;
- external provider history vs local erasure boundary is truthful.

---

# ET5 — Production Email Profile Certified

Long-running/operational evidence includes:

- declared sender/domain authentication setup and degraded diagnostics;
- production rate limits and large-batch behavior;
- fair queue/backpressure with other Job classes;
- credential rotation without message-loss/double-send state corruption;
- provider event endpoint outage and recovery;
- provider API version/schema drift handling;
- storage/event-log cleanup under retention policy;
- WordPress/site restore and staging clone duplicate-send prevention;
- Multisite profile isolation where supported;
- Pro downgrade/expiry safe runtime behavior;
- support diagnostics redact secrets/body/recipient PII;
- monitoring detects stale event ingress/provider health without false Delivered state.

Certification report must enumerate unsupported capabilities, not only passed ones.

---

# Canonical executable fixture matrix — ET-F001…ET-F176

Every fixture is **predefined but NOT EXECUTED**. Each run must record profile identity, preconditions, expected result, observed result, normalized facts, raw protected evidence reference, privacy/security observations and pass/fail.

## Group A — Profile identity, capability declaration and evidence provenance — ET-F001…ET-F011

- **ET-F001** pin provider/product/adapter/WPE versions and environment.
- **ET-F002** pin provider API/event schema or dated documentation profile.
- **ET-F003** pin auth mode, region/account/subaccount/message-stream scope.
- **ET-F004** pin site/network ownership and environment class.
- **ET-F005** verify static EE3 evidence cannot produce ET0.
- **ET-F006** verify unsupported capability is explicitly declared unsupported/unknown.
- **ET-F007** verify capability drift forces targeted recertification.
- **ET-F008** verify adapter version drift invalidates non-portable evidence.
- **ET-F009** verify provider profile identity cannot be reused across materially different accounts/regions.
- **ET-F010** verify stale certification is surfaced rather than silently treated current.
- **ET-F011** verify certification report retains exact evidence provenance and retest trigger set.

## Group B — Vault, credentials, authentication and sender prerequisites — ET-F012…ET-F022

- **ET-F012** valid Vault-backed credential/configuration accepted.
- **ET-F013** invalid/revoked credential rejected without secret disclosure.
- **ET-F014** browser/admin/API responses never return provider secret material.
- **ET-F015** logs/Audit/diagnostics redact tokens, passwords and signing secrets.
- **ET-F016** least-privilege provider scope is sufficient for declared capabilities.
- **ET-F017** insufficient provider scope fails with truthful capability diagnostics.
- **ET-F018** credential rotation preserves profile identity/history without plaintext fallback.
- **ET-F019** concurrent rotation/send does not mix old/new secret state unsafely.
- **ET-F020** sender/domain verification prerequisite is surfaced before send claim.
- **ET-F021** TLS/certificate verification failure is explicit and fail-closed where required.
- **ET-F022** site/network credential inheritance/isolation follows Vault + Multisite policy.

## Group C — Sender, recipient, headers and message-address safety — ET-F023…ET-F033

- **ET-F023** valid From/Reply-To envelope and header model normalizes correctly.
- **ET-F024** unauthorized sender identity/domain is rejected or truthfully provider-rejected.
- **ET-F025** malformed recipient address is rejected deterministically.
- **ET-F026** To/Cc/Bcc recipient semantics preserve privacy and per-recipient truth.
- **ET-F027** CRLF/header injection payload is rejected/neutralized.
- **ET-F028** Unicode subject/display names remain standards-safe and deterministic.
- **ET-F029** duplicate recipient normalization does not create accidental duplicate sends.
- **ET-F030** empty-recipient/no-recipient message cannot be submitted.
- **ET-F031** per-recipient provider identifiers correlate without leaking Bcc recipients.
- **ET-F032** provider address canonicalization does not become WPE identity authority.
- **ET-F033** invalid custom header names/values cannot bypass reserved/security header policy.

## Group D — Renderer handoff, MIME, bodies and attachments — ET-F034…ET-F044

- **ET-F034** deterministic Email IR/rendered handoff produces expected transport payload.
- **ET-F035** HTML and plaintext alternatives remain distinct and correctly encoded.
- **ET-F036** charset/content-transfer encoding preserves Unicode content.
- **ET-F037** malformed renderer output is rejected before ambiguous transport submission.
- **ET-F038** attachment filename/MIME metadata is safely encoded.
- **ET-F039** attachment count limit is enforced.
- **ET-F040** attachment/message size boundary below provider limit succeeds as declared.
- **ET-F041** over-limit attachment/message fails truthfully without false delivery state.
- **ET-F042** unavailable/private attachment authorization failure prevents unsafe send.
- **ET-F043** inline/CID asset handling preserves message correctness without exposing private source paths.
- **ET-F044** renderer/composition success remains EBR evidence and never auto-promotes ET certification.

## Group E — Submission attempt persistence and provider identifiers — ET-F045…ET-F055

- **ET-F045** one physical submission try creates exactly one Transport Attempt record.
- **ET-F046** logical Recipient Delivery remains stable across retries.
- **ET-F047** synchronous provider acceptance persists provider message ID when supplied.
- **ET-F048** missing provider ID is represented as unknown/not-supplied, not fabricated.
- **ET-F049** multi-recipient submission stores provider correlation at correct granularity.
- **ET-F050** local persistence failure before provider request creates no false submission claim.
- **ET-F051** provider acceptance followed by local persistence failure preserves ambiguous outcome for reconciliation.
- **ET-F052** duplicate bookkeeping callback cannot create duplicate canonical attempts.
- **ET-F053** test-send state is isolated from production Notification occurrence/delivery state.
- **ET-F054** `wp_mail()` true records only local processing truth.
- **ET-F055** generic SMTP 2yz records only acceptance by the certified accepting hop.

## Group F — DNS, TLS, SMTP/API errors and normalization — ET-F056…ET-F066

- **ET-F056** DNS resolution failure before transmission normalizes as non-accepted failure.
- **ET-F057** connection refused/unreachable normalizes without retry storm.
- **ET-F058** TLS negotiation/certificate failure is distinct from credential failure.
- **ET-F059** timeout before request/body write is distinguished from ambiguous post-write timeout.
- **ET-F060** SMTP 4yz is classified transient according to pinned profile.
- **ET-F061** SMTP 5yz is classified permanent according to pinned profile.
- **ET-F062** provider HTTP 429 honors bounded Retry-After policy.
- **ET-F063** provider 5xx becomes transient/unknown according to exact request outcome.
- **ET-F064** provider 4xx validation/auth failures do not tight-loop.
- **ET-F065** provider outage/maintenance state yields truthful degraded health.
- **ET-F066** unknown/new provider error code remains unknown rather than guessed into Delivered/Failed.

## Group G — Unknown outcomes, idempotency, retries and JobService crash windows — ET-F067…ET-F077

- **ET-F067** connection loss after possible provider acceptance becomes `submission_unknown`.
- **ET-F068** timeout after possible acceptance does not blind-immediately retry.
- **ET-F069** provider-native idempotency key is used only under a certified contract.
- **ET-F070** provider without suitable idempotency keeps duplicate-risk explicit.
- **ET-F071** retry creates a new Transport Attempt under the same Recipient Delivery.
- **ET-F072** exponential/backoff policy is bounded and observable.
- **ET-F073** retry exhaustion reaches terminal/manual-reconciliation state truthfully.
- **ET-F074** Job worker crash before provider call can safely resume without false attempt outcome.
- **ET-F075** Job worker crash after possible provider call preserves ambiguity.
- **ET-F076** stale Job lease/duplicate worker cannot create uncontrolled duplicate submissions.
- **ET-F077** manual retry/requeue respects current policy, suppression, cancellation and previous unknown outcome.

## Group H — Queue fairness, throttling, batching and backpressure — ET-F078…ET-F088

- **ET-F078** provider rate-limit budget is isolated from authorization and business policy.
- **ET-F079** burst admission applies configured RLT/transport throttling without silent loss.
- **ET-F080** large recipient batch is chunked within provider and resource constraints.
- **ET-F081** batch partial failure preserves per-recipient truth.
- **ET-F082** queue backlog exposes delayed/pending state rather than Submitted/Delivered.
- **ET-F083** email workload cannot starve higher-priority shared Job classes indefinitely.
- **ET-F084** competing sites on Multisite receive bounded/fair scheduling per accepted policy.
- **ET-F085** provider concurrency cap is respected under multiple workers.
- **ET-F086** dynamic/provider throttling cannot bypass retry budget.
- **ET-F087** cancelled/expired work is not newly submitted merely because backlog drains.
- **ET-F088** backpressure recovery does not replay already-confirmed attempts without precondition checks.

## Group I — Provider event ingress authenticity and scope — ET-F089…ET-F099

- **ET-F089** valid provider webhook authentication/signature is accepted.
- **ET-F090** tampered raw body/signature is rejected before business processing.
- **ET-F091** stale/replayed event is rejected/deduped according to provider security profile.
- **ET-F092** wrong provider/profile endpoint cannot inject source facts.
- **ET-F093** signing-key/profile rotation accepts only currently valid trust configuration.
- **ET-F094** endpoint scope derives site/network/provider profile from trusted routing state, not attacker payload alone.
- **ET-F095** event payload size/type limits fail safely.
- **ET-F096** malformed JSON/form/event payload cannot poison Event Inbox state.
- **ET-F097** authenticated event with unauthorized cross-site correlation is rejected.
- **ET-F098** raw provider event storage follows minimization/redaction/retention policy.
- **ET-F099** webhook acceptance acknowledges according to provider retry contract without claiming business success prematurely.

## Group J — Event correlation, dedupe, ordering and late evidence — ET-F100…ET-F110

- **ET-F100** provider message/event ID correlates to the correct Transport Attempt/Recipient Delivery.
- **ET-F101** duplicate provider event is idempotently represented once in derived state.
- **ET-F102** out-of-order accepted/delivered/bounce events derive deterministically without erasing facts.
- **ET-F103** delayed failure after earlier acceptance updates outcome truthfully.
- **ET-F104** event for unknown provider message ID is retained/quarantined for reconciliation, not misattached.
- **ET-F105** unknown event type is preserved as source fact without unsafe state promotion.
- **ET-F106** multi-recipient provider event correlates at the provider-supported granularity.
- **ET-F107** late event after local archival boundary follows documented reconciliation/retention behavior.
- **ET-F108** duplicate webhook delivery after restore does not duplicate canonical event effects.
- **ET-F109** conflicting provider events remain auditable and use deterministic precedence rules.
- **ET-F110** event normalization schema/version changes do not reinterpret historical raw facts silently.

## Group K — Delivery, defer, bounce, drop, complaint and suppression truth — ET-F111…ET-F121

- **ET-F111** provider queued/accepted maps only to submission/provider-acceptance truth.
- **ET-F112** certified receiving-server acceptance maps to `Delivered to Receiving Server` only.
- **ET-F113** deferred/delayed event remains non-terminal where provider semantics require.
- **ET-F114** temporary failure follows retry/reconciliation policy without false hard bounce.
- **ET-F115** permanent/hard bounce reaches terminal recipient-delivery failure truthfully.
- **ET-F116** provider dropped/rejected remains distinct from bounce where provider distinguishes them.
- **ET-F117** spam complaint is retained as feedback fact without rewriting historical delivery.
- **ET-F118** provider pre-send suppression prevents/blocks submission as profile semantics require.
- **ET-F119** provider suppression after earlier sends does not erase prior attempts/events.
- **ET-F120** unknown provider classification is not guessed into hard/soft bounce or complaint.
- **ET-F121** bounce/complaint/suppression status can be recomputed deterministically from preserved source facts.

## Group L — Unsubscribe, preferences and cross-channel policy boundaries — ET-F122…ET-F132

- **ET-F122** WPE optional-category opt-out blocks only policy-mapped optional email sends.
- **ET-F123** mandatory/security/transactional category behavior requires explicit policy and legal/product classification.
- **ET-F124** provider unsubscribe event is represented separately from WPE preference mutation.
- **ET-F125** provider-global suppression does not silently disable unrelated WPE channels.
- **ET-F126** WPE preference change does not claim provider suppression deletion/removal.
- **ET-F127** suppression removal/reactivation follows explicit provider + WPE policy when supported.
- **ET-F128** corrected recipient address does not silently transfer suppression/history to a different identity.
- **ET-F129** Membership/Notification policy revoke before send is rechecked at execution time.
- **ET-F130** tenant/site policy cannot mutate another site's recipient preferences.
- **ET-F131** provider feedback used as policy input retains source/provider/time provenance.
- **ET-F132** policy conflict resolves explicitly and never converts transport fact into authorization.

## Group M — Engagement signals, privacy proxies and tracking truth — ET-F133…ET-F143

- **ET-F133** open tracking disabled profile emits no WPE open claim.
- **ET-F134** click tracking disabled profile emits no WPE click claim.
- **ET-F135** provider open event maps only to observed tracking signal, never human read.
- **ET-F136** provider click event maps only to observed tracking signal, never human intent.
- **ET-F137** image proxy/prefetch open is represented with scanner/proxy-safe language.
- **ET-F138** security-link scanner click cannot be labeled confirmed human click.
- **ET-F139** repeated opens/clicks dedupe/count according to documented metric semantics.
- **ET-F140** tracking event retention off/short/default modes are enforced independently of delivery truth.
- **ET-F141** tracking identifiers do not expose cross-site recipient identity.
- **ET-F142** engagement data export/diagnostics obey local/remote privacy policy.
- **ET-F143** absence of tracking never downgrades a valid receiving-server delivery fact.

## Group N — Reconciliation, event outage, provider retention and API/schema drift — ET-F144…ET-F154

- **ET-F144** webhook/event endpoint outage leaves affected delivery truth pending/unknown as appropriate.
- **ET-F145** endpoint recovery resumes ingestion without duplicate side effects.
- **ET-F146** provider status API reconciliation backfills only evidence available under certified capability.
- **ET-F147** provider with no reconciliation API keeps unresolved outcomes explicit.
- **ET-F148** provider event retention expiry is surfaced as an evidence limit.
- **ET-F149** local retention expiry does not fabricate provider deletion.
- **ET-F150** provider API version drift triggers compatibility/retest gate.
- **ET-F151** provider event schema drift rejects/quarantines unsafe unknown shape rather than misclassifying.
- **ET-F152** adapter rollback/upgrade preserves old attempt/event interpretation.
- **ET-F153** reconciliation is idempotent under repeated scans/replays.
- **ET-F154** stale provider-health/event-ingress monitoring cannot create false Delivered state.

## Group O — Multisite, lifecycle, restore/clone and Free↔Pro degradation — ET-F155…ET-F165

- **ET-F155** site-scoped profile cannot read/use another site's credentials or sender policy.
- **ET-F156** network-scoped profile inheritance requires explicit accepted scope and target authorization.
- **ET-F157** switched/current-blog context cannot become durable email ownership authority.
- **ET-F158** site create/clone does not automatically inherit unsafe production send state.
- **ET-F159** staging clone is send-safe by default until explicit environment/profile policy permits delivery.
- **ET-F160** backup restore does not replay historical queued/unknown sends blindly.
- **ET-F161** restore with provider events arriving late reconciles by stable identity without cross-site collision.
- **ET-F162** site deletion/uninitialization stops future sends and applies retention policy without false remote deletion claim.
- **ET-F163** module disable pauses/stops new work according to contract without corrupting historical delivery evidence.
- **ET-F164** Pro expiry/downgrade preserves safe deployed/history behavior and never exposes provider secrets.
- **ET-F165** re-enable/upgrade resumes only valid current work; stale queue entries require precondition recheck.

## Group P — Retention, erasure, audit, diagnostics, scale and production behavior — ET-F166…ET-F176

- **ET-F166** recipient/body/provider-event retention follows declared local PDL policy.
- **ET-F167** privacy erase distinguishes local live data, provider-held data, backups and legal/security retention.
- **ET-F168** export includes only authorized/minimized delivery facts and avoids credential leakage.
- **ET-F169** Audit records security-relevant configuration/credential/profile mutations without storing secrets.
- **ET-F170** support diagnostics redact provider secrets, message bodies and recipient PII by default.
- **ET-F171** high-volume attempt/event retention cleanup preserves required aggregate/source truth.
- **ET-F172** large-batch production workload respects Job/RLT/resource budgets and reports partial outcomes.
- **ET-F173** long-running provider degradation surfaces health/backlog without false success.
- **ET-F174** provider-account suspension/quota exhaustion produces truthful operational state and bounded retries.
- **ET-F175** certification report enumerates unsupported/unknown capabilities and failed fixtures, not only passes.
- **ET-F176** full profile rerun proves no renderer/submission/delivery/feedback/engagement truth-boundary regression before ET5 claim.

---

# Fixture-to-level use

The exact certification report maps every executed fixture to one or more ET levels and declares `required`, `not-applicable`, `unsupported`, `pass`, `fail` or `not-executed` with rationale.

Minimum rule:

- ET0 requires all applicable identity/configuration/Vault/security prerequisites needed for connectability.
- ET1 requires ET0 plus submission/address/render-handoff/attempt evidence.
- ET2 requires ET1 plus ambiguous-outcome/retry/Job/backpressure resilience evidence.
- ET3 requires ET2 plus authenticated event ingress, correlation and receiving-server delivery truth evidence.
- ET4 requires ET3 plus complaint/suppression/unsubscribe/reconciliation and any advertised engagement evidence.
- ET5 requires ET4 plus lifecycle, Multisite, restore/clone, retention, diagnostics, scale and long-running operational evidence.

A provider capability declared not applicable may be excluded only with pinned provider-profile evidence. Unsupported capability must remain visibly unsupported; it cannot be waived into a pass.

---

# Provider-specific minimum suites

## WordPress `wp_mail()` baseline

- true/false/error behavior;
- PHPMailer hook interactions where supported;
- no remote delivery claim;
- coexistence with popular SMTP replacement plugins only after separate compatibility profile.

## Generic SMTP

- AUTH modes selected by implementation;
- TLS/certificate behavior;
- 2yz/4yz/5yz classification;
- local relay vs destination-server distinction;
- connection loss after DATA ambiguity;
- DSN capability only if explicitly implemented/certified.

## Amazon SES

- Send/Reject/Delivery/Bounce/Complaint/DeliveryDelay mappings;
- message ID correlation;
- selected event destination ingress security/reliability;
- suppression/account restrictions;
- rate/throttle behavior.

## Twilio SendGrid

- processed/delivered/deferred/bounce/dropped/spam report mappings;
- signed Event Webhook raw-body verification or separately approved OAuth webhook profile;
- event/message ID correlation;
- delayed/asynchronous bounce behavior;
- rate limits.

## Mailgun

- accepted/delivered/temporary_fail/permanent_fail/complained mappings;
- HMAC timestamp+token signature verification;
- replay token/timestamp policy;
- domain/account event profile differences.

## Postmark

- delivery confirmation maps to receiving-server acceptance only;
- bounce/spam complaint profile;
- message stream correlation;
- webhook auth/profile limitations documented.

Provider-specific suites add requirements; they do not weaken the shared ET-F matrix.

---

# MUST NOT / stop-the-line gates

Stop certification and mark the relevant profile failed/blocked if evidence shows any of the following:

- claim inbox delivery, mailbox visibility, read or human view from submission/provider acceptance/open/click alone;
- expose SMTP/API credentials, webhook signing secrets or other Vault material to browser, logs, Audit, diagnostics or exported artifacts;
- blindly retry an ambiguous post-transmission outcome when duplicate-send risk is unmanaged;
- accept unauthenticated/tampered provider events where the provider profile requires authenticity verification;
- correlate event/send state across the wrong site/network/provider profile;
- let provider suppression silently mutate unrelated WPE channels/preferences without explicit policy mapping;
- send from restored/staging/cloned state without duplicate-send/environment controls;
- treat JobService at-least-once execution as exactly-once email delivery;
- treat a renderer/EBR pass, static EE3 review or provider documentation as ET runtime certification;
- certify an ET level while required lower-level evidence is failed, unknown or not executed;
- hide unsupported/unknown provider capabilities from the certification report;
- fabricate provider message IDs, delivery events or reconciliation facts;
- continue after evidence of cross-tenant leakage, secret exposure, authorization bypass, uncontrolled duplicate sends or destructive privacy-policy violation.

Security/data incidents invoke the repository stop-the-line and incident/recovery governance.

---

# Evidence artifact

Each future certification produces a report containing:

- profile identity/version/scope;
- ET level sought and prerequisite levels;
- ET-F fixture IDs with expected vs observed outcomes;
- raw provider facts retained only in protected test artifacts;
- normalized WPE facts;
- Provider Event/Event Inbox correlation evidence where applicable;
- unsupported/not-applicable capabilities with rationale;
- privacy/security observations;
- known provider quirks;
- pass/fail/not-executed per fixture and ET level;
- certification expiry/retest triggers;
- negative-requirement/stop-the-line results.

Retest triggers include provider API/event schema change, adapter dependency change, material WordPress/PHPMailer change, JobService/Vault/Event Inbox/security change, webhook security change, Multisite/lifecycle change, or significant WPE Delivery state-machine change.

## Current evidence truth

- ET-F fixtures documented: **176**.
- ET-F fixtures executed: **0/176**.
- Static provider profiles: **6 EE3**.
- Runtime provider profiles certified: **0 ET-certified**.
- ET0: **0 certified profiles**.
- ET1: **0 certified profiles**.
- ET2: **0 certified profiles**.
- ET3: **0 certified profiles**.
- ET4: **0 certified profiles**.
- ET5: **0 certified profiles**.
- Email sends/provider calls/DNS changes/webhook runtime/queue execution in this planning work package: **none**.

## Gate

No item in this protocol may be executed until the owner gives explicit scoped development/executable-spike consent under ADR-0014 and the approval ledger records that authorization.