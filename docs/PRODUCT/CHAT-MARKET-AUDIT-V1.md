# Chat — Market Audit V1

Status: `RESEARCH_COMPLETE / SUPERVISOR_INTEGRATION_PENDING`

Surface: 21 — Chat  
Snapshot: 2026-09-12  
Parent: #713  
Worker issue: #724  
Base: `54e4e70095f74a6ebff15da83cc2bbf3d447a49c`

## 1. Scope and boundary

This evidence-only audit reconciles all 17 current Chat records. Chat owns private/group conversation and message state. Roles/Policy supplies authorization; Media/File owns resources; Notifications owns notification occurrence/routing; Search/Import-Export/Privacy remain canonical integrations. Realtime transport is provider-specific. Arbitrary HTML/script in messages remains rejected.

## 2. Current market evidence

### E1 — Better Messages
Official evidence:
- https://www.better-messages.com/
- https://www.better-messages.com/docs/conversations/groups/
- https://www.better-messages.com/docs/conversations/chat-rooms/
- https://www.better-messages.com/docs/realtime/
- https://www.better-messages.com/docs/file-sharing/

Verified: private one-to-one/group conversations, rooms, participant management, replies, reactions, mentions/files, typing/presence, read receipts, moderation controls, notifications and file-sharing controls.

### E2 — Better Messages realtime/provider behavior
Official realtime documentation distinguishes AJAX polling from WebSocket transport, documents reconnect/provider dependency, and associates typing/presence behavior with realtime transport. This is direct evidence that transport capability and degraded state must be modeled rather than assuming every installation is realtime.

### E3 — Better Messages data/security features
Official documentation covers group-thread message editing/replies/reactions/read receipts, protected/proxied file delivery and file restrictions, room moderation/reporting, data storage/portability and optional end-to-end encryption capabilities.

## 3. Record reconciliation

| Record | Disposition |
|---|---|
| `chat.conversation.definition` | MARKET_EVIDENCED — private/group/room conversation identities/types are baseline market functionality. |
| `chat.creation.policy` | MARKET_EVIDENCED / WPE_HARDENED — conversation creation is common; duplicate prevention remains explicit server policy. |
| `chat.participant.policy` | MARKET_EVIDENCED — E1 groups support participant add/remove/leave and group membership behavior. |
| `chat.state.lifecycle` | MARKET_EVIDENCED / WPE_HARDENED — rooms/conversations have lifecycle/moderation states; WPE normalizes active/closed/archived/hold. |
| `chat.message.format` | MARKET_EVIDENCED_WITH_SAFE_RENDERER — text/links/emoji-style messaging is parity; rendering remains sanitized. |
| `chat.message.mentions-attachments` | MARKET_EVIDENCED_WITH_MEDIA_OWNER — E1/E3 prove mentions/files; resources remain Media/File-owned and authorization-filtered. |
| `chat.message.edit-delete` | MARKET_EVIDENCED / WPE_HARDENED — message editing is evidenced; WPE keeps deterministic edit/delete/redaction windows. |
| `chat.message.reply-reactions` | MARKET_EVIDENCED — E1/E3 directly prove replies and reactions. |
| `chat.schema.reliability` | KEEP_WPE_HARD — provider messaging proves pagination/state, but schema versioning/idempotency/concurrency guarantees require explicit WPE contracts. |
| `chat.read.state` | MARKET_EVIDENCED — read receipts/unread behavior are established. |
| `chat.presence.typing` | MARKET_EVIDENCED_WITH_PRIVACY_BOUNDARY — typing/presence exist, especially with realtime transport; WPE retains privacy policy and degraded behavior. |
| `chat.transport.profile` | PROVIDER_EVIDENCED — E2 proves AJAX polling vs WebSocket/reconnect/provider dependency and therefore explicit transport profiles. |
| `chat.notification.policy` | MARKET_EVIDENCED_WITH_NOTIFICATIONS_OWNER — messaging notifications/mute behavior exists; Surface 19 owns routing/digest semantics. |
| `chat.moderation.safety` | MARKET_EVIDENCED — E1/E3 prove moderators, kick/mute/ban/reporting; WPE adds rate-limit/audit guarantees. |
| `chat.attachment.authorization` | MARKET_EVIDENCED / WPE_HARDENED — E3 protected proxy/file restrictions support private attachment authorization; every access still re-checks conversation/resource policy. |
| `chat.search-privacy-portability` | MARKET_EVIDENCED_WITH_OWNER_BOUNDARIES — E3 data storage/portability exists; authorized search, retention/erasure and import/export stay canonical integrations. |
| `chat.safety.raw-html` | KEEP `REJECTED_UNSAFE` — rich messaging is not evidence to accept arbitrary HTML/script message bodies. |

Coverage: **17 / 17**. Unresolved research dispositions: **0**. Worker Bank/progress mutations: **0**.

## 4. WPE-exceed / safety

- UI visibility/member lists never substitute for per-conversation/per-message authorization.
- Protected attachments are served only after fresh resource + conversation authorization; public Media URLs are insufficient for private chat.
- Realtime is an explicit provider profile. Polling fallback/degraded state must be honestly exposed when WebSocket/realtime service is absent.
- Presence/typing/read receipts are privacy-sensitive and configurable; absence of realtime transport must not fabricate live status.
- Arbitrary HTML/script remains rejected; safe renderer formats only.

## 5. Supervisor integration requirements

A later Supervisor may add E1–E3 market/provider provenance and classify conversation/message/read/presence/transport/moderation/attachment families while retaining reliability, privacy and owner boundaries. `MARKET_AUDITED` requires exact-head Bank reconciliation and CI.

No message persistence/dispatch, participant/moderation mutation, realtime-provider execution, protected-file delivery, `BANK_REVIEWED`, runtime/product certification, deployment or release is authorized.