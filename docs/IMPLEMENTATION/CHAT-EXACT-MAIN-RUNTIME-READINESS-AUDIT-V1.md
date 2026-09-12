# Chat — Exact-Main Runtime-Readiness Audit V1

Issue: #647  
Parent: #636  
Audit anchor: `35874640a62b9cb08cec5268e949dacd55c8427f`  
Surface: 21 / `chat`

## Decision

**NOT RUNTIME-READY.** Keep `runtime_allowed=false`.

Surface 21 is `ATOMIC_INVENTORY_COMPLETE` in the machine tracker but the accepted Chat gap matrix still blocks runtime: no reviewed Bank/runtime promotion, no dedicated Chat module, and no canonical conversation/message/participant/read/moderation owner. Exact-main `frameworks/Modules` has no Chat module.

## Exact-main classification

- Bank/product runtime gate: **BLOCKED**.
- Atomic option contracts: **PLANNING INVENTORY ONLY / BLOCKED FOR RUNTIME**.
- Conversation Definition persistence/revision/CAS and normalized conversation/message storage: **ABSENT**.
- Authorization/IDOR, participant lifecycle, send/edit/delete, read/unread and anti-abuse: **ABSENT**.
- Attachments, Notifications, Membership/Roles, Search and Workflow escalation: **DEPENDENCY / NO CHAT CONTRACT**.
- Transport/realtime, moderation/reporting, retention/privacy, multisite, accessibility, reliability and performance evidence: **ABSENT / UNCONTRACTED**.

## Ownership boundaries

Chat must use server-authoritative resource Policy for every conversation/message/search/attachment operation and consume File/Media, Notifications, Membership/Roles, Search and Workflow seams. Client-supplied identity, public private-file URLs, arbitrary HTML/script messages and hidden long-term presence tracking remain forbidden.

## Smallest next valid slice

**Not runtime code.** Next valid work is Bank seeding/native/market review, schema-valid option contracts and UX re-review.

Only after those gates, candidate first runtime slice: **read-only Conversation Definition + list/get authorization diagnostics**, with no message mutation, realtime transport or external notifications.

Candidate files if separately authorized:
- `frameworks/Modules/Chat/ChatModule.php`
- `frameworks/Modules/Chat/ConversationDefinition.php`
- `frameworks/Modules/Chat/ChatReadService.php`
- unit tests under `tests/Unit/Modules/Chat/`
- IDOR-focused integration tests for conversation/message/attachment read paths

## Exit

Issue #647 is evidence-accounted as **BLOCKED / NOT RUNTIME-READY**. No chat persistence, message dispatch/mutation, realtime provider work, certification or shared-truth changes are introduced.
