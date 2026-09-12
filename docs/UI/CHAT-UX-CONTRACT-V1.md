# Chat — UX Contract V1

Surface: **21 / Chat**  
Machine source: `config/product/option-contracts/chat.json`  
Lifecycle: **UX certification candidate**. Machine truth is `OPTION_CONTRACT_COMPLETE`; this contract does not authorize message delivery, moderation execution or realtime-provider operations.

## Lifecycle preconditions

- Machine status must remain `OPTION_CONTRACT_COMPLETE` or later with `missing=0` and `unclassified=0`.
- All 17 current Atomic Option IDs are mapped exactly once below.
- Conversation, participant, message, read-state, moderation and provider behavior remains server-authoritative and authorization-aware.

## Canonical route and information architecture

Canonical admin location: **WPEssential → Communication → Chat**.

The IA separates **Conversation Definition**, **Creation & Participants**, **Conversation State**, **Composer & Message Policy**, **Message Schema & Reliability**, **Read & Presence**, **Transport & Notifications**, **Moderation & Safety**, **Search/Privacy/Portability**, and **Safety**. Authored policy is distinct from realtime/provider observations.

## UX state classes

### Authored definition
Conversation/creation/participant/lifecycle policy, safe message format, edit/reaction policy, reliability, read/presence privacy and moderation policy are revisioned Chat-owned definitions.

### Effective/runtime state
Conversation membership, message delivery/order, unread counts, read receipts, presence, typing and moderation results are effective runtime state and never locally fabricated.

### Diagnostic/provider state
Media attachments, realtime transport, notification routing, authorized Search and Privacy/portability owners are canonical references with explicit available/degraded/offline state.

### Deferred / prohibited state
Arbitrary HTML/script in message bodies is prohibited. Realtime failure must degrade honestly; polling fallback, delivery/read state and presence cannot be claimed unless supported by actual provider/runtime evidence.

## Atomic Option → UX map

- `chat.conversation.definition` — Conversation Definition → conversation type/lifecycle/linked-resource definition.
- `chat.creation.policy` — Creation & Participants → creation eligibility/duplicate-prevention policy.
- `chat.participant.policy` — Creation & Participants → participant derivation/invite/leave/maximum policy.
- `chat.state.lifecycle` — Conversation State → active/closed/archived/moderation-hold policy and effective state.
- `chat.message.format` — Composer & Message Policy → safe text/Markdown/link/emoji policy with sanitized preview.
- `chat.message.mentions-attachments` — Composer & Message Policy → mention policy plus Media/File-owned attachment references.
- `chat.message.edit-delete` — Composer & Message Policy → edit/delete/redaction windows and permission policy.
- `chat.message.reply-reactions` — Composer & Message Policy → reply-to/reaction policy and accessibility behavior.
- `chat.schema.reliability` — Message Schema & Reliability → schema version/idempotency/concurrency/pagination policy and diagnostics.
- `chat.read.state` — Read & Presence → last-read cursor/unread/read-receipt policy and effective state.
- `chat.presence.typing` — Read & Presence → presence/typing privacy policy with unsupported/degraded state.
- `chat.transport.profile` — Transport & Notifications → polling/realtime provider profile, reconnect policy and explicit offline/degraded status.
- `chat.notification.policy` — Transport & Notifications → Notifications-owned mute/digest/routing reference.
- `chat.moderation.safety` — Moderation & Safety → reporting/moderation/blocking/rate-limit policy; actions remain separately authorized/audited.
- `chat.attachment.authorization` — Moderation & Safety → private attachment authorization state rechecked against conversation/resource access.
- `chat.search-privacy-portability` — Search/Privacy/Portability → authorized Search, retention/erasure, diagnostics and definition-portability owner references.
- `chat.safety.raw-html` — Safety → prohibited arbitrary HTML/script message-body evidence; never an authored control.

## Interaction and persistence

Definition editing uses draft → validate → save revision. Conversation/message previews are non-delivering simulations. Participant and moderation actions display target/current scope and require separate authorized runtime actions. Realtime UI switches to explicit reconnecting/offline/degraded states and never marks unsent local content as delivered.

## Loading, empty, validation, conflict and recovery

Required states include no conversations, conversation loading, participant source unavailable, attachment authorization denied, send pending/failed/unknown, realtime reconnecting/offline, presence unsupported, unread/read state stale, moderation pending/failed, search/privacy provider unavailable, stale revision, saved and recovery. Unknown delivery/read state remains unknown.

## Security and ownership

Media/File owns protected attachment authorization. Notifications owns notification routing/digest semantics. Search and Privacy owners retain search/retention/erasure behavior. Chat owns conversation/message policy and durable chat state. Arbitrary HTML/script message bodies are prohibited. Client state cannot grant participation, moderation authority, attachment access, delivery or read truth.

## Accessibility

Conversation/message lists expose semantic structure and keyboard navigation. Composer controls have labels and visible focus. New-message announcements are rate-limited and respect user preferences. Reactions and reply context are screen-reader understandable. Offline/reconnecting state is textual, not color-only, and focus is not stolen by incoming events.

## Multisite and scope

Conversation, linked-resource and provider scope are explicit. Network/global scope is server-derived. Imports cannot silently move private conversations, participant policies or provider references across sites.

## Portability and reference remapping

Exports are definition-only and secret-free unless a separately scoped data-export workflow owns message data. Imports validate linked resource, Media, Notifications, Search, Privacy and transport-provider references before save. Presence/read/runtime observations are never imported as authored configuration.

## Performance and scale

Conversation/message history paginates with stable cursors. Realtime/polling updates are bounded and back off under failure. Attachments and presence avoid N+1 authorization/provider checks. Admin configuration assets remain scoped.

## Degraded/provider states

Missing realtime, Media, Notifications, Search or Privacy providers show explicit unavailable/degraded states. Polling fallback is labeled when used. The surface never fabricates participant access, attachment authorization, presence, message delivery, receipt, read state or moderation success.

## UX lifecycle exit criteria

Certification requires complete 17-ID mapping, zero missing/unclassified machine semantics, raw-HTML/script rejection, truthful realtime/delivery/read semantics, owner/provider/accessibility/portability/performance/degraded states and exact-head validator CI. Shared lifecycle promotion remains Supervisor-only.

## Non-certifications

Runtime implementation is not certified by this document. Product parity is not certified. Production deployment or release is not verified. No message send, participant/moderation mutation, realtime-provider action, attachment mutation or external dispatch is authorized by this UX contract.
