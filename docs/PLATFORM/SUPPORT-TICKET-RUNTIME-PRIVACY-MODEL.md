# WPEssential — Support Ticket Runtime, Attachment & Privacy Model

Status: **Phase 0 platform architecture / no service implementation authorized**  
Related: Platform Surfaces Spec, Remote Service API Contract, OAuth ADR-0034, ADR-0014.

## Product boundary

Support Tickets let an authorized WPE customer contact WPE support from wp-admin while keeping the WPE service as the authoritative support system.

The WordPress site is a secure client, not the permanent source of truth for remote support conversations.

## Entities

### Ticket
- opaque ticket UUID/number;
- account/site activation reference;
- subject;
- category;
- product/module context;
- status;
- created/updated timestamps;
- requester identity safe reference;
- assigned support team/agent safe display metadata;
- last activity;
- unread/reply-needed state.

### Message
- immutable message UUID;
- ticket UUID;
- author side (`customer`, `support`, `system`);
- sanitized rich/plain content;
- created timestamp;
- client idempotency key;
- attachment references;
- edit/redaction history only where service policy supports it.

### Attachment
- attachment UUID;
- ticket/message relation;
- original safe filename;
- MIME/type;
- byte size;
- checksum;
- malware/security scan state;
- privacy classification;
- storage object reference;
- signed/authorized download behavior;
- retention state.

## Ticket statuses

Customer-visible canonical statuses:
- `open`;
- `waiting_for_support`;
- `waiting_for_customer`;
- `resolved`;
- `closed`.

Service-only moderation/abuse states may exist but are not invented as ordinary customer workflow.

Transitions are server-authoritative and auditable.

## Ticket list

Local screen fetches paginated ticket summaries from WPE service through account OAuth scope.

Filters:
- status;
- module/product;
- category;
- date;
- unread/reply needed;
- search by safe subject/ticket ID.

Do not download every historical message just to render list.

## Create ticket

Fields:
- subject;
- category;
- affected module/surface;
- description/message;
- optional severity/impact statement;
- optional site diagnostics attachment;
- optional screenshots/files;
- explicit consent to include selected diagnostics.

Category examples:
- General/Product question;
- Installation/Update;
- Bug;
- Billing/License;
- Backup/Restore;
- Security concern;
- Migration;
- Integration/provider.

Security vulnerabilities may route to a dedicated private workflow; normal public issue tracker is not the default disclosure path.

## Reply

Reply request carries a client-generated idempotency UUID.

Network retry with the same key must not create duplicate replies.

Reply can include attachments only after upload/scan/reference succeeds according to service policy.

## Update

Customer-editable ticket metadata is deliberately limited:
- subject before first support response candidate;
- category/context where allowed;
- close/reopen according to status policy;
- add reply/attachments.

Support-owned internal priority/assignment is not writable from WordPress client.

## Close / reopen

Customer can close a ticket when resolved. Reopen policy may be bounded by retention/time; otherwise create linked follow-up.

Closing does not silently delete history.

## Delete semantics

User requirement for delete is supported without falsifying audit/retention:

- unsent local Draft can be hard-deleted locally;
- sent ticket offers `Request deletion`/delete when remote service policy and legal/support retention permit;
- if immediate hard deletion is not permitted, UI must say the real state (`Deletion requested`, retained until date/policy), not pretend it vanished from service;
- attachment deletion follows ticket/privacy policy.

## Authentication/authorization

Remote access requires:
- connected WPE account via ADR-0034;
- dedicated local WPE support capability;
- OAuth scopes such as `support:read` / `support:write`;
- service-side account/site authorization.

A local administrator cannot query arbitrary ticket UUIDs belonging to another account/site.

## Local storage/cache

Default local persistence is minimal:
- ticket list cache/ETag/cursor;
- unread count;
- local unsent draft optional;
- retry/idempotency metadata;
- no permanent mirror of full support conversation by default.

Cache expiry/disconnect clears safe cache without deleting remote ticket.

## Attachments

### Allowed sources
- explicit local upload;
- selected WPE diagnostic bundle;
- screenshot/image;
- exported safe report.

### Requirements
- allowlisted MIME/extensions;
- max file and total ticket size;
- filename normalization;
- no executable archive/script accepted merely by extension;
- malware/scanner pipeline on service where available;
- checksum;
- private object storage;
- authorization on every download;
- signed URLs short-lived when used;
- no public media URL for sensitive support attachment by default.

Archives/SQL/logs are high-risk and require stronger limits/scan/redaction.

## Diagnostics attachment

Before attaching diagnostics:
1. generate structured bundle;
2. classify/redact secrets;
3. show human-readable preview of included categories;
4. allow deselecting optional categories;
5. user confirms upload;
6. service stores as private support attachment.

Never automatically attach:
- passwords;
- OAuth/access/refresh tokens;
- Authorization headers;
- Vault plaintext;
- recovery keys;
- raw unrestricted DB dumps;
- customer content unrelated to issue.

## Message sanitization

Support message renderer allows safe limited formatting.

Reject/escape:
- scripts/event handlers;
- arbitrary iframes;
- unsafe URLs;
- executable embedded HTML;
- tracking HTML controlled by ticket author.

Links open with safe rel/target behavior according to UI policy.

## Notifications

Ticket updates can feed local Notification System after authenticated service poll/push integration is certified.

Do not create two authorities: remote ticket state remains canonical.

Email notifications are service/account preferences, not proof the local ticket state changed unless service API confirms it.

## Offline/service failure

States:
- disconnected account;
- WPE service unavailable;
- request timeout;
- authentication expired;
- attachment upload failed;
- reply submitted but outcome unknown;
- rate limited.

Unknown reply outcome resolves by idempotency lookup/reconciliation, not blind duplicate resend.

Free/local plugin continues functioning if Support service is unavailable.

## Rate/abuse

Service controls:
- ticket creation rate;
- reply/attachment rate;
- attachment size;
- spam/abuse;
- account entitlement/support-plan limits.

Local UI can show limit but remote service enforces it.

## Privacy

Support data may contain personal/business data.

Requirements:
- documented remote-service privacy/retention;
- customer data export/deletion process;
- attachment retention after closure;
- staff-access audit where service supports;
- local cache included in local privacy eraser/export as appropriate;
- do not sell/share ticket data as advertising data.

Exact jurisdictional retention remains service/legal policy, not invented by plugin code.

## Logging

Local logs record:
- correlation/request ID;
- ticket ID;
- normalized result/error;
- timing;
- attachment safe metadata.

Do not log message bodies/attachments by default and never log auth tokens/secrets.

## Abilities / AI

Safe AI exposure candidate:
- list ticket summaries;
- summarize an explicitly selected ticket under user permission;
- draft a reply locally;
- create a draft ticket.

Sending/closing/deletion/attachment upload requires explicit user action/authorization and is not model-autonomous by default.

## Future executable evidence — NOT AUTHORIZED

- OAuth scope/IDOR tests;
- pagination/ETag/cursor;
- reply idempotency/unknown outcome;
- attachment MIME/archive/malware/privacy flow;
- diagnostic redaction;
- service outage/retry;
- close/reopen/delete-request;
- local cache privacy;
- support-plan entitlement limits;
- multisite account/site binding.

No support service endpoint, ticket table or attachment upload has been implemented.