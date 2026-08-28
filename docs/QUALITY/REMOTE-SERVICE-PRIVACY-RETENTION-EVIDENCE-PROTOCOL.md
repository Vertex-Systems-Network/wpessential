# WPEssential — Remote Service Privacy & Retention Evidence Protocol

Status: **Phase 0 planning only / executable protocol NOT AUTHORIZED**  
Date: 2026-08-28  
Governing: ADR-0034, ADR-0050, ADR-0054, ADR-0060, PDL, OA, FP, PLT, RLT, CAC, ERR, VER, MLC, Backup, Multisite, ADR-0014.

## 1. Purpose

Define future executable evidence required before any optional WPE remote Account/Entitlement/Catalog/Support/Docs/Release/Status/Diagnostics service can claim privacy, consent, minimization, retention, deletion, export or clone/Multisite correctness.

A successful API call is never privacy verification.

## 2. Trust and lifecycle separation

These remain distinct:

`local WordPress data ≠ remote service resource ≠ Account/OAuth connection ≠ Product entitlement ≠ Site Allocation ≠ Support record ≠ provider record ≠ live remote data ≠ remote backup copy ≠ security/audit retention`

Likewise:
- disconnect ≠ local erase ≠ remote account deletion;
- site deletion ≠ Account deletion;
- local uninstall ≠ remote deletion;
- remote live deletion ≠ backup expiry;
- Account link ≠ telemetry consent;
- signed entitlement/TUF trust remain separate from ordinary REST fields.

## 3. Retention classes

Future implementation maps each remote field/resource to explicit class/purpose. Existing conceptual classes remain:
- `RR0` request-only/minimal transient;
- `RR1` one-time transaction/replay state;
- `RR2` bounded cache;
- `RR3` active connection/allocation state;
- `RR4` reconciliation/commercial operational history;
- `RR5` user-created service records such as Support;
- `RR6` minimized security/fraud/replay evidence.

Exact durations are evidence/service-policy gated and never inferred merely from class names.

## 4. Fixed fixture matrix

### A. Original RS-01…RS-30 — preserved
- **RS-01** Free activation/use produces no WPE-controlled remote call solely by activation/local use.
- **RS-02** Public Catalog/Docs/Changelog/Release/Status requests minimize identifiers.
- **RS-03** Account-link disclosure matches material transmitted fields/purposes.
- **RS-04** OAuth state/verifier/code/tokens/artifact excluded from browser/log/export/support.
- **RS-05** Token rotation/revocation/disconnect truthfully separates local and remote state.
- **RS-06** Account/service usage does not silently piggyback analytics/telemetry.
- **RS-07** Diagnostics upload requires separate preview/explicit approval.
- **RS-08** Diagnostics redacts/excludes synthetic secrets/private classes.
- **RS-09** Support service remains authoritative; local cache bounded/stale-labelled.
- **RS-10** Support attachment access/token/type/deletion/account isolation.
- **RS-11** Service log redaction/minimization.
- **RS-12** Problem/error response privacy.
- **RS-13** RR0 request-only retention truth.
- **RS-14** RR1 transaction expiry/replay/cleanup.
- **RS-15** RR2 cache TTL/freshness/isolation.
- **RS-16** RR3 active connection lifecycle.
- **RS-17** RR4 reconciliation/history purpose/minimization.
- **RS-18** RR5 Support record create/read/export/delete lifecycle.
- **RS-19** RR6 security evidence minimized/restricted.
- **RS-20** Local WordPress exporter ≠ automatic authority over every remote record.
- **RS-21** Disconnect/revoke/cache/support/account deletion semantics remain distinct.
- **RS-22** Clone/restore cannot silently impersonate original activation.
- **RS-23** Signed entitlement trust remains separate from REST assertions.
- **RS-24** TUF update trust remains separate from Catalog/Release REST.
- **RS-25** Service outage/degraded mode preserves Free and truthful freshness.
- **RS-26** 429/Retry-After bounded behavior.
- **RS-27** Multisite/network remote-service isolation.
- **RS-28** Privacy-policy/disclosure text matches observed transmission.
- **RS-29** Remote Docs/Support search does not append unrelated private context.
- **RS-30** Backup retention implications stated truthfully.

### B. Resource/data-classification registry
- **RS-31** Every remote resource has owner/purpose/classification/retention policy.
- **RS-32** Every transmitted field has documented purpose/source/sensitivity.
- **RS-33** Unknown new service field does not silently become collected/stored.
- **RS-34** API schema version change triggers privacy-field diff/review.
- **RS-35** Client version cannot send undeclared diagnostic/inventory fields.
- **RS-36** Server ignores/rejects unknown sensitive client fields instead of retaining them opportunistically.
- **RS-37** Account identifiers separated from installation/network/site identifiers.
- **RS-38** Public resources do not require authenticated personal identity unless function requires it.
- **RS-39** Commercial contract fields separated from WordPress user/profile fields.
- **RS-40** Support content classification distinct from diagnostics payload.
- **RS-41** Provider-derived identifiers/tokens classified by owning adapter.
- **RS-42** Security/fraud signals use minimized fields and explicit purpose.
- **RS-43** IP/user-agent/network metadata has documented retention/access purpose.
- **RS-44** Locale/product/version fields not silently combined into behavioral profile absent explicit analytics policy.
- **RS-45** Field removed from purpose/schema stops new collection after deployed transition.
- **RS-46** Historical retained field follows its prior lawful/declared retention class until deletion/expiry, not endless legacy retention.

### C. Consent/disclosure/provenance
- **RS-47** Remote feature first-use disclosure is shown before material transmission where required.
- **RS-48** Disclosure version/revision is recordable without storing unnecessary user-content proof.
- **RS-49** Acceptance for Account linking does not imply diagnostics consent.
- **RS-50** Diagnostics consent does not imply product analytics consent.
- **RS-51** Support attachment upload disclosure remains separate from text ticket creation where material.
- **RS-52** Optional analytics, if ever designed, has independent enable/disable mechanism and evidence.
- **RS-53** Consent withdrawal stops future optional processing within declared semantics.
- **RS-54** Withdrawal does not falsely promise deletion of legally/security-retained records.
- **RS-55** Site Admin consent cannot authorize network-wide data transmission without network authority.
- **RS-56** Network policy cannot silently impersonate individual consent where individual consent is required.
- **RS-57** Service-side purpose change requires documented migration/new disclosure where applicable.
- **RS-58** UI copy cannot claim “nothing leaves site” when remote function is enabled.
- **RS-59** UI copy cannot claim “anonymous” while stable installation/account IDs are transmitted.
- **RS-60** Terms/privacy links and service identity match actual destination/operator profile.

### D. Request/response minimization and transport
- **RS-61** Authorization header absent from public unauthenticated resources.
- **RS-62** Account-scoped calls carry only required bearer/resource identifiers.
- **RS-63** Request bodies exclude plugin/theme inventory unless explicit endpoint purpose requires it.
- **RS-64** Current admin URL/page/content identifiers are not generic request metadata.
- **RS-65** Referrer/redirect behavior does not leak private local URLs/queries.
- **RS-66** TLS/host/certificate failure does not downgrade to plaintext.
- **RS-67** Safe HTTP destination policy prevents attacker-controlled service host substitution.
- **RS-68** Redirect chain remains within accepted service policy.
- **RS-69** Response size/depth/count bounded before local processing/logging.
- **RS-70** Raw provider/service response body not logged by default.
- **RS-71** Error parser redacts nested token/credential values.
- **RS-72** Correlation/request IDs are non-authoritative and not overloaded with personal content.
- **RS-73** Retry does not duplicate support/account/allocation mutation where idempotency required.
- **RS-74** Unknown outcome becomes reconciliation state, not repeated blind mutation.

### E. OAuth/Account/Product License separation
- **RS-75** OA success stores only approved connection/resource facts locally/remote.
- **RS-76** OAuth refresh credential never appears in remote generic request/application logs.
- **RS-77** Account unlink stops local usable credential without claiming Account deletion.
- **RS-78** Account deletion request does not imply immediate deletion of commercial/security records.
- **RS-79** Site Allocation revoke/delete remains distinct from Account deletion.
- **RS-80** Product entitlement expiry is not inferred from Account service outage.
- **RS-81** Account service outage does not modify signed entitlement artifact.
- **RS-82** Stale signed entitlement follows FP freshness/grace policy, not remote privacy cache semantics.
- **RS-83** Account switch A→B does not merge Support/commercial histories silently.
- **RS-84** Site transfer between accounts/contracts has explicit provenance/reconciliation.
- **RS-85** Product API ETag/idempotency logs do not require request body retention.
- **RS-86** TUF metadata/package repository requests remain update-trust domain and do not piggyback Account analytics.

### F. Support tickets/messages/attachments
- **RS-87** Ticket list/read authorization scoped to correct Account/site/role.
- **RS-88** Cross-ticket IDOR fails.
- **RS-89** Cross-account attachment IDOR fails.
- **RS-90** Attachment upload URL/token expires and one-time/multiuse semantics explicit.
- **RS-91** Attachment download URL short-lived and non-public.
- **RS-92** Attachment MIME/size/count policy validated server-side.
- **RS-93** Malicious filename/path metadata sanitized.
- **RS-94** Attachment malware/content scanning capability claims only when actually certified.
- **RS-95** Ticket text rendering prevents stored XSS in local/admin/service UIs.
- **RS-96** Support email/notification copies do not expose private attachment tokens.
- **RS-97** Staff/internal note visibility separated from customer-visible message if supported.
- **RS-98** Ticket close does not imply deletion.
- **RS-99** Message deletion semantics distinguish tombstone/hard delete/retained audit.
- **RS-100** Attachment deletion unknown/provider outcome remains pending/reconciliation.
- **RS-101** Local Support cache invalidates after remote mutation/revoke.
- **RS-102** Offline cached Support data remains labelled stale and protected locally.
- **RS-103** Diagnostics attached to ticket are separately consented and access-controlled.
- **RS-104** Ticket export excludes staff-only/security fields unless authorized product policy permits.

### G. Diagnostics bundle and uploads
- **RS-105** Diagnostics preview generated locally before upload.
- **RS-106** Preview shows categories and redaction/exclusion status.
- **RS-107** User can deselect optional categories where contract says optional.
- **RS-108** Secrets scan covers Vault/OAuth/Application Password/DB credentials/salts/provider keys.
- **RS-109** Private Forms/Chat/Membership content excluded by default.
- **RS-110** Raw database dumps excluded from ordinary diagnostics.
- **RS-111** Full filesystem lists/path disclosure minimized.
- **RS-112** Plugin/theme/version inventory transmitted only if selected disclosed diagnostic category.
- **RS-113** Multisite site list/domain inventory separately disclosed/authorized.
- **RS-114** Diagnostics archive encrypted/private in transit/storage according to accepted service profile.
- **RS-115** Upload failure leaves local temp bundle cleanup/retry truth explicit.
- **RS-116** Remote diagnostics retention/deletion state visible where product promises it.
- **RS-117** Support staff access to diagnostics is role/audit bounded.
- **RS-118** Diagnostics download/share links expire and remain non-public.

### H. Logs, audit, metrics and analytics
- **RS-119** Application logs have field allowlist/redaction policy.
- **RS-120** Reverse-proxy/edge logs reviewed for query/header secret leakage.
- **RS-121** Database audit/security logs do not store bearer/refresh tokens.
- **RS-122** Full support/message bodies excluded from generic error logs.
- **RS-123** Metrics labels avoid account/site/user high-cardinality personal identifiers unless justified.
- **RS-124** Performance telemetry does not silently become behavioral analytics.
- **RS-125** Analytics endpoint absent/unused when product analytics disabled.
- **RS-126** Error-tracking vendor transmission, if introduced, is separately disclosed/profiled.
- **RS-127** Session replay/screen recording is not silently introduced by account connection.
- **RS-128** Security-event retention remains RR6/minimized rather than indefinite generic traffic history.
- **RS-129** Log access is role/service-operator controlled.
- **RS-130** Log retention job honors class-specific windows.
- **RS-131** Legal/security hold overrides are explicit and auditable, not invisible indefinite retention.
- **RS-132** Support bundle/log export redacts identifiers according to recipient/purpose.

### I. Retention jobs, deletion and tombstones
- **RS-133** RR0 application state absent after request except documented infrastructure/security metadata.
- **RS-134** RR1 expiration removes one-time secret while preserving minimal replay tombstone only if required.
- **RS-135** RR2 cache expiry deletes/invalidates stale personalized material.
- **RS-136** RR3 disconnected resource moves to explicit inactive/tombstone/reconciliation state.
- **RS-137** RR4 retention duration/purpose independently configured/documented.
- **RS-138** RR5 user-created Support deletion lifecycle explicit.
- **RS-139** RR6 security evidence expiry/minimization explicit.
- **RS-140** Retention cleanup is idempotent and resumable.
- **RS-141** Failed cleanup is observable/retryable without silently claiming deletion.
- **RS-142** Remote live deletion status distinguishes requested/processing/deleted/retained/error.
- **RS-143** Local cache deletion never changes remote deletion status automatically.
- **RS-144** Provider-backed deletion requires provider reconciliation rather than optimistic confirmed state.
- **RS-145** Account deletion orchestrates owned resources with explicit exclusions/retention obligations.
- **RS-146** Deletion cannot accidentally remove another Account/site tenant through copied IDs.
- **RS-147** Tombstone contains minimal identity/replay/reconciliation facts only.
- **RS-148** Re-created Account/site resource after prior deletion gets correct new/current identity semantics.

### J. Export/access/erasure orchestration
- **RS-149** Remote Account export requires authenticated Account authority independent from local WP exporter.
- **RS-150** Local WordPress exporter reports/reference remote data responsibility truthfully without overclaiming remote export.
- **RS-151** Support export includes only caller-authorized tickets/messages/attachments metadata/content.
- **RS-152** Commercial invoices/contracts/history follow separate legal/product export rules.
- **RS-153** Security/fraud evidence may be excluded/restricted only with explicit policy/legal basis.
- **RS-154** Export excludes tokens/secrets/pre-signed URLs/internal staff-only data unless explicitly authorized.
- **RS-155** Export artifact is private, expiring, access-controlled and retention-bounded.
- **RS-156** Remote erasure request maps owned data categories/resources before execution.
- **RS-157** Erasure result reports retained-by-policy categories truthfully.
- **RS-158** Repeated erasure request idempotent/reconciled.
- **RS-159** Erasure during active Support/commercial dispute follows explicit policy, not silent loss.
- **RS-160** Restore/replay after erasure triggers required post-restore cleanup/reconciliation.

### K. Backup/restore, clone/environment and Multisite
- **RS-161** Service backup encryption/access/retention profile is documented separately from live DB.
- **RS-162** Live deletion does not claim immediate disappearance from immutable backup before expiry.
- **RS-163** Backup restoration cannot permanently resurrect deleted/expired live resource without reconciliation cleanup.
- **RS-164** Staging clone uses distinct environment/installation identity.
- **RS-165** Production DB clone cannot silently reuse refresh token/site allocation contrary to OA/FP policy.
- **RS-166** Domain change updates projections through reconciliation, not authentication-by-domain.
- **RS-167** Network clone/regeneration distinguishes installation/network/site IDs.
- **RS-168** Same numeric site ID across networks has zero remote tenant collision.
- **RS-169** Site Admin cannot read network Account credential/support data.
- **RS-170** Site deletion updates site-owned remote resources without deleting Account/network resources accidentally.
- **RS-171** Network deletion/uninstall does not claim remote Account deletion unless explicitly requested/supported.
- **RS-172** 100/1k/10k-site allocation/support metadata remains scope-safe and bounded.

### L. Failure injection, privacy regression and certification
- **RS-173** Service timeout/partial write/5xx/429/connection loss preserves truthful unknown/degraded/retry state without duplicate data.
- **RS-174** Adversarial leak scan covers URL/header/body/browser/cache/log/support/export/diagnostics with zero secret/private cross-tenant disclosure.
- **RS-175** API/service/version/privacy-policy migration regression preserves classification/retention/deletion semantics or produces explicit migration risk.
- **RS-176** Final certification pins exact client/service/API/schema/OAuth/Vault/provider/backup/environment/Multisite/privacy-policy profile; no generic “privacy compliant” claim beyond executed evidence.

## 5. Independent certification classes

Future reports certify separately:
- `RS-D` data classification/minimization/disclosure;
- `RS-A` Account/OAuth/Product License trust separation;
- `RS-S` Support/attachments/diagnostics;
- `RS-L` logging/metrics/analytics;
- `RS-R` retention/deletion/tombstone;
- `RS-E` export/erasure;
- `RS-B` backup/restore/clone/environment;
- `RS-M` Multisite/tenant isolation;
- `RS-F` failure/regression/observability.

Passing one class never certifies another.

## 6. Stop-the-line gates

A remote-service profile remains uncertified if evidence shows:
- hidden WPE-controlled call on Free activation/local-only usage;
- undisclosed material data/telemetry transmission;
- OAuth/Vault/provider secret leakage;
- cross-account/site/ticket/private-attachment access;
- diagnostics upload without separate explicit approval;
- Account connection treated as analytics consent;
- disconnect/local erase falsely represented as remote deletion;
- remote live deletion falsely represented as immediate backup deletion;
- clone/restore silently obtaining production identity/credential/entitlement;
- ordinary REST assertion granting signed entitlement/TUF authority;
- deletion/retention state reported more strongly than provider/service evidence supports.

## 7. Required future evidence report

For RS-01…RS-176 include exact client/service/API/schema/environment versions, observed outbound field manifests, disclosure version, privacy-safe traffic/log evidence, retention/deletion timelines, Support/diagnostics access results, OAuth/Product License separation, export/erasure, backup/restore/clone/Multisite evidence, pass/fail/blocked status, severity/remediation/retest and independent review status.

## 8. Current state

**RS fixtures documented: 176.**  
**RS fixtures executed: 0/176.**  
Remote privacy/retention runtime certifications: **0**.

No network capture, Account/OAuth link, remote service API call, diagnostics upload, Support attachment action, retention cleanup, deletion/export/erasure, provider call, backup restore, clone or Multisite runtime fixture has executed.

## 9. Development-consent gate

Execution requires explicit owner consent under ADR-0014 and the Approval Ledger. This protocol authorizes planning/documentation only.
