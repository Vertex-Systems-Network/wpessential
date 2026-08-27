# WPEssential — Remote Service Privacy & Retention Evidence Protocol

Status: **Phase 0 planning only / executable protocol NOT AUTHORIZED**  
Governing decisions: ADR-0034, ADR-0050, ADR-0054, ADR-0060, ADR-0014.

## 1. Purpose

Define the future executable evidence required before WPEssential can claim that its optional remote Account/Entitlement/Catalog/Support/Docs/Release/Status services match the accepted privacy, consent, minimization and retention contracts.

This protocol is intentionally written before implementation so a happy-path API demo cannot be treated as privacy verification.

**Nothing in this document authorizes execution.**

## 2. Core claims to prove

Future evidence must prove at least:
1. Free activation does not contact WPE-controlled remote services.
2. Public remote resources do not secretly attach site/account/install identifiers when not required.
3. Account-link disclosure matches the actual fields transmitted.
4. Account connection does not silently enroll telemetry/analytics.
5. OAuth credentials and one-time artifacts never enter browser/frontend logs, generic diagnostics or normal exports.
6. Diagnostics upload requires separate explicit preview/approval.
7. Remote/application logs minimize secrets and request bodies.
8. RR0–RR6 retention behavior is implemented per resource.
9. Disconnect, account deletion, support-record deletion and commercial/security retention are represented distinctly.
10. Support/private attachments enforce access and deletion/export semantics truthfully.
11. Cloned/restored WordPress sites do not silently impersonate the original activation.
12. Signed entitlement/update trust remains separate from ordinary REST assertions.

## 3. Evidence environment

After future consent, establish isolated environments:
- WordPress Free-only site;
- WordPress Free + Pro site where authorized;
- WPE remote-service test tenant/account;
- isolated test domain/origin;
- network capture/proxy suitable for recording destination/headers/body safely;
- service-side structured logs with redaction controls;
- disposable Support records/attachments;
- test OAuth client/authorization environment;
- clone/restore fixture;
- no real customer/user/payment data.

Production credentials/data are prohibited in certification fixtures.

## 4. Evidence artifacts

Each run should preserve privacy-safe artifacts:
- test profile/version;
- client/plugin/service version;
- resource/API schema version;
- expected transmitted-field manifest;
- captured request field names and safe synthetic values;
- destination host/path/method;
- response/problem type;
- service log field names/redaction result;
- retention timestamps/state transitions;
- cleanup/delete/export result;
- screenshots only where useful and redacted;
- pass/fail per fixture;
- deviations/known risks.

Do not archive real secrets in evidence artifacts.

## 5. RS-001 — Free activation no-call proof

### Setup
Fresh WordPress install, WPE Free installed/activated, no WPE account connection.

### Observe
Capture all outbound DNS/HTTP(S) connections attributable to WPE during:
- plugin activation;
- first admin load;
- module listing;
- CPT/Taxonomy usage;
- scheduled idle period;
- WordPress cron invocation;
- plugin update metadata check where WordPress.org handles normal plugin metadata.

### Pass
No request to WPE-controlled account/catalog/analytics/licensing/support/diagnostics endpoints occurs solely because Free was installed/activated/used locally.

### Fail
Any hidden registration, telemetry, personalized-offer request, heartbeat or site inventory transmission without an explicit user-initiated remote function.

## 6. RS-002 — Public resource minimization

Test Catalog/Docs/Changelog/Release Notes/Status public requests.

Expected baseline fields should be restricted to what the selected resource needs, such as:
- resource path/query;
- locale/version/product where necessary;
- ordinary network-layer metadata inherently present in HTTP.

### Pass
No hidden:
- account ID;
- activation ID;
- installation UUID;
- site origin;
- plugin/theme inventory;
- current wp-admin route;
- user ID;
- content identifiers;
- usage counters
unless the request is explicitly authenticated/personalized and the UI/service contract says so.

## 7. RS-003 — Account-link disclosure vs transmission

Before redirect, record the disclosure shown to admin.

Capture fields transmitted during:
- link transaction creation;
- OAuth authorization initiation;
- fixed callback completion;
- site-bound completion artifact exchange;
- activation registration;
- initial entitlement fetch.

### Pass
Every material field/category actually transmitted is covered by the disclosed purpose; no unrelated inventory/telemetry is piggybacked.

### Fail
Undisclosed site/user/plugin/content analytics or broad diagnostics data in the account-link flow.

## 8. RS-004 — OAuth secret handling

Inspect:
- browser URL/history;
- WordPress frontend/admin page source;
- localized JS/state;
- PHP/app logs;
- reverse-proxy logs;
- service logs;
- diagnostics bundle;
- generic settings export.

Secrets include:
- PKCE verifier;
- OAuth state secret;
- authorization code;
- access token;
- refresh token;
- one-time completion artifact;
- pre-signed support attachment upload URL/token.

### Pass
Reusable/one-time secrets are absent from places not explicitly required for protocol handling. Refresh credentials are Vault-owned; browser return does not expose reusable access/refresh credentials.

## 9. RS-005 — Token rotation/revocation/disconnect

Fixtures:
- normal refresh;
- refresh rotation where service supports;
- revoked refresh token;
- expired access token;
- service outage during disconnect;
- reconnect after local credential deletion.

### Pass
- local credentials are removed on disconnect;
- remote revocation is attempted where supported;
- uncertain remote revocation is surfaced safely;
- disconnect is not falsely labeled account deletion;
- Free remains functional.

## 10. RS-006 — No telemetry piggyback

Enable account/service functions but leave any future analytics/telemetry consent disabled.

Exercise:
- entitlement refresh;
- catalog;
- support list;
- docs search;
- changelog;
- status;
- account summary.

### Pass
No usage event stream, page-view analytics, module usage counters, content inventory or arbitrary event batch is transmitted through these calls.

If future product analytics exists, it must use the separately accepted disclosed mechanism and be independently disabled/tested.

## 11. RS-007 — Diagnostics separate consent

Open Support and create a ticket **without** diagnostics.

### Pass
No diagnostics bundle is uploaded.

Then explicitly choose diagnostics:
- show category/file/field preview;
- show redaction summary;
- allow deselection where contract permits;
- require explicit submit/approval.

### Fail
Opening Support, linking account, creating a ticket or selecting a category automatically uploads diagnostics.

## 12. RS-008 — Diagnostics redaction

Seed synthetic secrets/private data in controlled fixtures:
- fake API tokens;
- fake OAuth credentials;
- fake salts/password-like strings;
- private form/chat/member content;
- test wp-config secret markers;
- private backup path/metadata.

Run future diagnostics preview/export.

### Pass
Default diagnostics excludes/appropriately redacts protected classes according to ADR-0060 and the privacy matrix.

The evidence should show both:
- what was included;
- what was deliberately excluded/redacted.

## 13. RS-009 — Support record authority/cache

Create synthetic Support ticket/message/attachment through authorized test flow.

Verify:
- service is authoritative;
- local cache contains only intended bounded metadata/content;
- stale/offline cache is labeled;
- local cache deletion does not falsely claim remote deletion;
- remote allowed actions are still subject to local capability.

## 14. RS-010 — Support attachment access

Fixtures:
- owner/admin authorized download;
- unauthorized WordPress user;
- expired short-lived URL/token;
- reused upload token;
- oversized/prohibited type;
- deleted/deletion-requested attachment;
- attachment from another account/site/ticket.

### Pass
No permanent public URL or cross-ticket/account access. Pre-signed tokens are short-lived/secrets and absent from generic logs.

## 15. RS-011 — Service log redaction

Inspect representative logs for:
- account link;
- entitlement;
- catalog;
- support;
- attachment upload/download;
- diagnostics;
- errors/429/5xx.

### Pass
Logs retain safe operational fields such as request ID/error class/timing while omitting:
- Authorization bearer values;
- refresh/access tokens;
- OAuth codes/state/verifiers;
- pre-signed URLs;
- private attachment contents;
- full request/response bodies by default;
- unrelated personal content.

## 16. RS-012 — RFC 9457 error privacy

Force validation/auth/conflict/rate-limit/service failures.

Verify Problem Details responses do not expose:
- stack traces;
- SQL;
- internal host topology;
- private keys/tokens;
- raw provider errors containing secrets;
- hidden account/site resources outside caller scope.

Machine codes remain stable; human `detail` is not parsed as business authority.

## 17. RS-013 — RR0 one-request data

Select future resources classified RR0.

### Pass
Application-level persisted state does not retain the data after request completion, except separately documented minimal infrastructure/security metadata.

Evidence must distinguish application storage from infrastructure logging rather than pretending network infrastructure stores nothing.

## 18. RS-014 — RR1 one-time transaction cleanup

Test OAuth state, PKCE/link transactions, attachment upload sessions and idempotency/replay records.

Verify:
- explicit expiry;
- one-time use where required;
- replay rejection;
- cleanup after bounded window;
- expired transaction cannot be silently reused.

## 19. RS-015 — RR2 bounded caches

Test Account summary, public Catalog/Docs/Status/Release caches according to resource policy.

Verify:
- TTL/freshness policy;
- cache key isolation by account/site/environment where applicable;
- stale labeling;
- explicit refresh after mutation where required;
- no cache extending security authority beyond signed entitlement/OAuth validity.

## 20. RS-016 — RR3 active connection state

Verify Site Activation/account connection data exists only as required for active lifecycle plus documented bounded tombstone/reconciliation needs.

Test disconnect/reconnect/site transfer/clone.

## 21. RS-017 — RR4 reconciliation/history

Test commercial/entitlement/idempotency/audit records required for reconciliation.

Pass criteria:
- retention purpose is explicit;
- fields minimized;
- no card/payment secrets;
- user-facing deletion/disconnect text does not falsely promise removal of records legitimately retained for reconciliation/security.

## 22. RS-018 — RR5 user-created service records

Test Support tickets/messages/attachments:
- create;
- list/read;
- export where product supports;
- close;
- delete/delete-request lifecycle;
- local cache cleanup;
- remote retention state.

UI must distinguish `deleted`, `deletion requested`, `retained by policy`, `not deletable` as actually supported.

## 23. RS-019 — RR6 security evidence

Test replay/revocation/auth abuse/security-event records.

Pass criteria:
- minimum fields;
- restricted access;
- explicit retention purpose;
- no secret values needed to prove event;
- retention does not become indefinite generic request logging.

## 24. RS-020 — Account/support export boundaries

Where a remote account/support export exists, verify caller authorization and included field categories.

Do not treat local WordPress personal-data exporter as automatic authority to download every remote account/commercial/security record.

Document local vs remote export responsibilities.

## 25. RS-021 — Deletion semantics

Test separately:
- disconnect site;
- revoke activation;
- delete local cache;
- delete support attachment/message/ticket where supported;
- request WPE account deletion where service supports;
- commercial/security history retention.

### Pass
No UI action uses one generic `Delete account/data` label for materially different operations.

## 26. RS-022 — Clone / restore isolation

Clone a database/site containing prior activation IDs to a different origin/install identity.

### Pass
Clone does not silently become an authorized duplicate of original production activation. It enters explicit transfer/clone/staging resolution according to future commercial policy.

No entitlement or token is accepted solely because copied DB contains old IDs.

## 27. RS-023 — Signed entitlement trust separation

Tamper ordinary REST account response to claim:
- Pro active;
- extra modules;
- longer expiry.

### Pass
Local entitlement authority remains the separately verified signed entitlement artifact; unsigned REST convenience fields cannot grant Pro.

## 28. RS-024 — Update trust separation

Tamper Catalog/Release REST data to advertise malicious/incorrect Pro package/version.

### Pass
Automated update acceptance remains governed by verified TUF metadata/profile, never ordinary REST JSON.

## 29. RS-025 — Service outage/degraded mode

Simulate Account/Catalog/Support/Docs/Status outages separately.

Verify:
- Free local modules remain functional;
- cached public data is labeled stale if shown;
- account outage does not become entitlement expiry by itself;
- support outage preserves unsent draft locally only if design explicitly supports it;
- retries/backoff do not hammer service;
- no hidden fallback telemetry endpoint.

## 30. RS-026 — Rate-limit behavior

Return 429/Retry-After and malformed/missing rate metadata.

Verify bounded retry/backoff, no page-load retry storms and no leaking sensitive response bodies.

## 31. RS-027 — Multisite/network isolation

Test site-scoped and network-scoped connection profiles if both become supported.

Verify:
- child site cannot read another site's connection token/support data;
- network authority is explicit;
- site-origin/install identifiers are scoped correctly;
- disconnecting one site does not revoke unrelated site credentials unless network policy says so.

## 32. RS-028 — Privacy-policy/disclosure text truth

Compare generated/suggested privacy disclosure and plugin readme/service disclosure against actual observed transmission.

### Pass
Documentation names service purpose/data categories and links terms/privacy where required, without understating actual behavior.

## 33. RS-029 — Support search/docs query privacy

Seed a query containing synthetic PII/private text.

Verify UI clearly indicates remote search where applicable and does not automatically append current post/content/logs/admin form state.

Server-side search analytics is not assumed from account connection and must follow separate policy if ever introduced.

## 34. RS-030 — Data backup/restore implications

Verify documentation explains that deleted live data may persist in encrypted backups until backup retention expires and may require post-restore cleanup/reconciliation.

Do not promise immediate erasure from immutable/provider-retained backups when technically impossible.

## 35. Failure rule

Any Critical privacy/security fixture failure blocks the relevant remote-service production profile. A feature cannot be marked `Verified` because its primary API call works while disclosure, token handling, logging, retention or deletion semantics fail.

## 36. Required report format after future execution

For each fixture:
- ID/name;
- environment/version;
- expected behavior;
- observed behavior;
- evidence artifact refs;
- Pass/Fail/Blocked;
- severity;
- remediation;
- retest status.

Overall report states:
- Verified;
- Not Verified;
- Known Risk;
- Next Action.

## Development-consent gate

**Do not run network capture, account linking, OAuth, service API calls, diagnostics upload, retention cleanup, support attachment actions or any other fixture in this protocol until the owner explicitly grants development/executable-spike consent under ADR-0014.**
