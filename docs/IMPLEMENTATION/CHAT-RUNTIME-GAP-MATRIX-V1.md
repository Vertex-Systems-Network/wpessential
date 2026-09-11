# Chat — Runtime Gap Matrix V1

Surface: **21 / Chat**  
Planning issue: **#587**  
Supervisor wave: **#583**  
Exact-main claim anchor: `b48f83f346293690e4f76d8b8ade082a22db1dee`

Status: **PLANNING GAP MATRIX / RUNTIME NOT AUTHORIZED**

Surface 21 remains `UNSEEDED / 0` in the Master Options Bank and exact main has no dedicated Chat runtime module. This matrix records prerequisites and owner boundaries only.

## Gap matrix

| Area | Exact-main state | Future requirement | Boundary / gate |
|---|---|---|---|
| Master Options Bank | **BLOCKED — UNSEEDED / 0** | Native/market reviewed normalized records. | Product gate before option contracts/runtime. |
| Atomic Option Contracts | **PLANNING INVENTORY ONLY** | Schema-valid contracts after Bank review. | No lifecycle promotion from inventory alone. |
| Chat runtime module | **ABSENT** | Canonical conversation/message/participant/read/moderation owner. | Runtime issue only after product gates. |
| Conversation Definition persistence/revision/CAS | **ABSENT** | Stable definition identity, lifecycle and concurrency. | Surface 21. |
| Conversation/message storage | **ABSENT** | Scalable normalized storage; no giant serialized conversation blob. | Surface 21; physical schema separately reviewed. |
| Authorization/IDOR protection | **ABSENT** | Server-authoritative Policy on every conversation/message/search/attachment action. | Shared Policy + resource owners. |
| Participant lifecycle | **ABSENT** | Safe add/remove/leave/derived membership with concurrency guards. | Surface 21 consumes Membership/Roles/resource truth. |
| Message send/edit/delete | **ABSENT** | Safe canonical format, idempotency, edit/delete windows and system-message protection. | Surface 21. |
| Attachments | **DEPENDENCY / NO CHAT CONTRACT** | Private storage/download authorization, limits, scanner status only when real provider exists. | File/Media owner + Chat conversation Policy. |
| Read/unread | **ABSENT** | Per-participant cursor/count with concurrency-safe bounded bulk actions. | Surface 21. |
| Presence/typing | **ABSENT/OPTIONAL** | Ephemeral privacy-controlled state. | No long-term activity fingerprinting by default. |
| Transport/realtime | **ABSENT** | Polling baseline and optional versioned realtime adapter with reconnect/idempotency. | Transport abstraction; UI must report actual mode. |
| Notifications/mute | **CROSS-SURFACE** | Message/mention/digest/mute requests. | Surface 19 Notifications owns delivery/routing/preferences. |
| Mentions | **ABSENT** | Privacy-safe eligible-user search + bounded special mentions. | Surface 21 + Policy. |
| Reactions/replies | **ABSENT** | Message-visibility-authorized mutations. | Surface 21. |
| Search | **ABSENT** | Current/user/admin scopes with authorization at retrieval/index update/delete. | Chat authorization remains source of truth; Search 34 may index. |
| Moderation/reporting | **ABSENT** | Scoped report queue/actions/audit without blanket conversation access. | Surface 21 + Policy/Workflow escalation. |
| Blocking | **ABSENT/PRODUCT DECISION** | Direct-chat safety semantics without overriding required shared-resource access. | Requires reviewed product contract. |
| Support queue mode | **ABSENT** | staff pool/assignment/hours/close-reopen/escalation. | Chat semantics; complex escalation Workflow 17. |
| Membership/team chat | **ABSENT** | entitlement/team derived access with prompt revocation. | Membership 15 is truth owner. |
| Retention/privacy | **ABSENT** | message/attachment/edit/moderation/read retention, anonymization, exporter/eraser. | Surface 21 + WP privacy integration. |
| Anti-abuse/rate limits | **ABSENT** | Product ceilings and race-safe enforcement. | Chat owns semantics; Protector/shared rate service may enforce. |
| Frontend widget | **ABSENT** | Dashboard/resource composition with safe UI tokens. | No arbitrary CSS/JS config. |
| Events | **ABSENT** | Privacy-minimized typed events by ID/safe summary. | No private body in generic event payload by default. |
| Portability | **ABSENT** | Definition config only by default; message-data movement separately governed. | Generic import/export Surface 26. |
| Multisite | **UNSPECIFIED** | site/network definition, participant, moderation, attachment, retention scope. | Contract before implementation. |
| Accessibility | **NO SURFACE UI** | Keyboard/mobile/focus/status/error requirements. | Browser/axe evidence later. |
| Compatibility | **NO SURFACE RUNTIME** | WP/PHP/adapter/provider degradation. | Exact-head matrix later. |
| Reliability | **NO SURFACE RUNTIME** | idempotency, concurrent participant changes, reconnect duplicate safety, queue/search consistency. | Deterministic tests later. |
| Performance | **NO SURFACE RUNTIME** | cursor pagination, indexed unread/participants, no N+1, bounded search/events. | Budget tests later. |

## Security/privacy hard gates

Future implementation must reject or fail closed on:

- fetching another conversation/message/attachment by guessed ID without Policy;
- trusting client-supplied participant/author/resource identity;
- private attachments served from unauthenticated public URLs;
- arbitrary HTML/script message content;
- unauthorized mention-user enumeration;
- moderation/search capabilities granting unrelated conversation access;
- public anonymous chat by default;
- hidden long-term presence/activity tracking;
- unbounded conversation/message loading;
- duplicate-send retries without idempotency;
- search index results that bypass source authorization;
- private message bodies in generic events/notifications/AI context by default;
- destructive hard delete where retention/audit policy forbids it.

## Policy / Ability model required later

At minimum separate server-side abilities for:

- conversation list/get/create/close/reopen/archive;
- participant add/remove/leave;
- message list/send/edit/delete;
- read-state mutation;
- attachment upload/download authorization;
- report creation;
- scoped moderation actions;
- search;
- definition management;
- retention/export/delete-data administration.

Frontend participant authorization remains resource Policy, not merely a global WordPress capability. AI defaults should exclude private message bodies and all send/edit/delete/moderation actions unless explicit user/context Policy permits them.

## Accessibility evidence required later

- keyboard conversation navigation/composer/actions;
- focus after send/edit/delete/report/moderation;
- semantic chronological message structure;
- unread/status live regions without noise;
- accessible upload progress/errors;
- no drag-only participant controls;
- responsive/mobile keyboard-safe composer;
- no color-only state;
- axe checks for conversation, empty/error, moderation and degraded transport states.

## Multisite evidence required later

- site-local vs network definitions;
- network users vs site membership participant eligibility;
- cross-site resource/conversation access;
- network moderation privacy;
- attachment provider/site scope;
- Notification/provider scope;
- retention/export/erase per site/network.

No Super Admin shortcut should bypass canonical conversation/resource Policy.

## Reliability/performance evidence required later

1. IDOR tests for conversation/message/attachment/search;
2. participant removal during read/send invalidates subsequent action correctly;
3. entitlement/team revocation invalidates access promptly;
4. duplicate message send with same client idempotency key does not duplicate;
5. concurrent participant add/remove remains consistent;
6. edit/delete window boundaries are server-authoritative;
7. private attachment direct URL cannot bypass authorization;
8. search indexes update/remove after edits/retention and enforce access;
9. reconnect/offline retry does not duplicate messages;
10. conversation/message pagination stays bounded on large histories;
11. unread counters remain concurrency-safe/indexed;
12. participant/profile rendering avoids N+1 growth;
13. rate limits remain race-safe;
14. retention/anonymization and attachment cleanup are bounded/async where necessary;
15. Notification events are idempotent and privacy-minimized.

## Future implementation order

Only after Bank review + schema-valid option contracts:

1. Conversation Definition + validation/Policy/revision;
2. normalized conversation/participant/message storage contract;
3. read-only list/get + authorization tests;
4. send/edit/delete + idempotency/concurrency;
5. read/unread and protected attachments;
6. participant lifecycle + membership/team/resource access;
7. Notifications/mentions/reactions;
8. moderation/reporting/retention/privacy;
9. search and transport/realtime adapters;
10. multisite/portability/degraded-state closure;
11. exact-head security/accessibility/compatibility/performance certification audit.

Anonymous/public chat, realtime providers and broad moderation search should remain later explicit slices rather than being bundled into baseline persistence.

## Exit decision

Exact main has a mature exhaustive Chat specification and clear ownership boundaries, but the Master Options Bank is still `UNSEEDED / 0` and there is no dedicated Chat runtime. `runtime_allowed=false` remains correct.

The next valid action is a separate Bank seeding/native/market review, then schema-valid option contracts and UX re-review — not Chat persistence or realtime code from this planning lane.
