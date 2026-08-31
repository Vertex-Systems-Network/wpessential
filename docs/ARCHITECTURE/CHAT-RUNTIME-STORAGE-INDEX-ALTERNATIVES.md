# WPEssential — Chat Runtime Storage, Index & Attachment Alternatives

Status: **Phase 0 paper architecture / no chat runtime authorized**  
Related: Message & Chat Exhaustive Spec, Policy Engine, Membership/Teams, Notifications, Protected Asset architecture.

## Purpose

Define durable chat/conversation storage and authorization boundaries without turning WordPress posts/comments into an accidental messaging database or allowing search/attachments/read-state to bypass conversation access policy.

## Domain separation

### Chat Definition/configuration
Definition Repository:
- allowed conversation types;
- who may start/invite;
- moderation rules;
- retention policy;
- attachment policy;
- transport/runtime configuration.

### Chat Runtime
Owns:
- conversations;
- participants;
- messages;
- message revisions/tombstones if enabled;
- read state;
- reactions/mentions if in scope;
- moderation/report records;
- attachment references;
- transport cursors/events.

Notifications owns push/email/in-app notification delivery, not canonical message state.

---

# Conversation model

Candidate fields:
- internal ID + UUID;
- type: direct/group/resource/team/support-like registered type;
- status: active/archived/locked/deleted tombstone;
- title optional;
- created_by;
- created/updated/last_message timestamps;
- resource context type/ref nullable;
- team/Membership context nullable;
- policy version/reference;
- retention policy reference;
- participant generation/version;
- last message sequence/counter;
- metadata safe bounded document.

## Conversation identity

Public/API identity uses UUID/non-enumerable stable ID. Numeric DB IDs can remain internal optimization.

Conversation existence itself may be sensitive; unauthorized caller should not be able to distinguish forbidden vs nonexistent where policy chooses concealment.

---

# Participant model

Fields:
- conversation ID;
- user/principal ID;
- chat-domain role: owner/admin/member/observer where type supports;
- state: invited/active/left/removed/banned;
- joined/left/removed timestamps;
- notification preference snapshot/reference;
- last-read sequence/message;
- mute/archive/pin personal state;
- generation/version.

Unique active membership per conversation/user.

WordPress role is not chat participant role.

## Derived participants

Resource/team conversations may derive eligibility from:
- Membership team seat;
- relation ownership;
- Dashboard/workspace policy.

Paper recommendation:
- materialize current participant membership when user actually joins/needs message history;
- reauthorize conversation access against current policy on every message/read/search/attachment action;
- source entitlement revoke can remove/suspend derived access through event/reconciliation.

Do not rely only on stale materialized participant row for protected team/resource chat.

---

# Message model

Candidate fields:
- message ID + UUID;
- conversation ID;
- monotonic per-conversation sequence candidate;
- sender principal ID/system actor;
- message type;
- body canonical sanitized representation;
- reply_to message UUID optional;
- thread/root reference only if thread feature accepted;
- created/edited/deleted timestamps;
- edit revision/version;
- moderation state;
- visibility state;
- attachment count;
- metadata bounded safe document;
- idempotency/client message key.

## Message body format

Preferred v1:
- sanitized text + constrained rich structured marks/links/mentions;
- no arbitrary HTML/script/style;
- no PHP/shortcode execution;
- links rendered safely;
- mentions store user/reference IDs separate from display text where possible.

Builder content is not a normal chat message format.

---

# Ordering

Use server-authoritative per-conversation sequence or equivalent deterministic cursor in addition to timestamps.

Reason:
- equal/clock-skew timestamps;
- pagination;
- unread calculation;
- transport retries.

Client local timestamp never determines canonical order.

---

# Idempotency

Client submit carries high-entropy client message key scoped to conversation/sender.

Retry after timeout must return existing accepted message when key matches instead of duplicating.

Attachments finalization participates in message idempotency contract.

---

# Read state

Do not create one “read row” per message per user by default.

Candidate efficient model:
- participant `last_read_sequence` for standard linear conversation;
- explicit per-message exception only for features that truly need it;
- unread count = max/current sequence minus last-read with visibility/deletion adjustments.

If threads/partial visibility complicate this later, benchmark alternative read-receipt model before changing canonical design.

Read receipts visible to others are separate privacy/UX option from internal last-read state.

---

# Attachments

Chat attachments are private by default.

Canonical record stores Protected Asset reference, not public uploads URL.

Attachment metadata:
- asset UUID;
- message/conversation owner;
- uploader;
- MIME/size;
- scan status if provider supports;
- created/retention;
- display filename safe value.

Every download reauthorizes current conversation/message policy.

Direct permanent public object URL is not accepted protection.

Orphan draft uploads cleaned by bounded retention.

---

# Search architecture alternatives

## A. SQL text search
Simple initial fallback for small installs; bounded and authorized conversation set required.

## B. DB FULLTEXT
Only if compatibility/index semantics accepted.

## C. Rebuildable search projection/index
Preferred scalable abstraction:
- message UUID;
- conversation UUID;
- normalized searchable text;
- policy/participant generation metadata as needed;
- provider-specific index document.

Critical rule: search result candidate must be reauthorized before returning message/content.

Never trust search index ACL alone as sole authorization.

External search service is optional future adapter and requires privacy/data-export review.

---

# Pagination

Cursor-based pagination preferred for message history:
- before/after sequence/message cursor;
- bounded page size;
- stable deterministic order.

Avoid OFFSET deep pagination as primary large-chat strategy.

Conversation list can use last_message/activity cursor and indexed participant lookup.

---

# Editing/deletion

Configurable per conversation type:
- edit window;
- delete-own window;
- moderator delete;
- preserve revision/tombstone;
- hard delete after retention.

Default candidate:
- edit allowed for bounded window where product enables;
- deletion produces tombstone for conversation continuity/audit, body removed according to policy;
- moderator action audited.

Do not silently rewrite quoted/replied context without defined UX.

---

# Moderation/reporting

Separate runtime records:
- report UUID;
- message/conversation;
- reporter;
- reason/category;
- status;
- moderator/action;
- timestamps;
- safe notes.

Reports may preserve evidence longer than normal visible message retention according to site policy; this must be disclosed/configured.

Blocking one user from another requires product semantics per conversation/resource and must not accidentally bypass mandatory team/admin communication if not intended.

---

# Retention

Per conversation type:
- indefinite;
- duration after message;
- duration after conversation terminal;
- archive then purge;
- legal/admin hold future adapter only when real requirement exists.

Separate attachment retention from message body.

Deletion/anonymization job:
- chunked;
- rechecks holds/moderation references;
- keeps authorization/search index synchronized;
- removes derived search documents.

---

# Privacy/export/erase

Chat contains P2/P4 data.

Exporter:
- user's conversation participation/messages where policy permits;
- does not expose other participants' private data beyond context legitimately part of conversation;
- attachment export links require authorized bounded delivery.

Eraser can:
- anonymize sender identity;
- remove message body where policy allows;
- preserve tombstone/moderation evidence with reason;
- delete personal preferences/read state;
- remove orphan attachments.

Group conversation integrity may require anonymization rather than deleting whole conversation.

---

# Access policy / IDOR defenses

Every operation checks:
- conversation read;
- send message;
- edit/delete own;
- moderate;
- add/remove participant;
- view participant list;
- search;
- download attachment;
- mark/read receipt visibility.

Knowing message UUID never grants access.

API queries never fetch message then expose body before policy check.

---

# Team/Membership revocation

When Membership/team entitlement is source of conversation access:
- revoke event increments participant/access generation;
- pending message sends after revocation fail policy;
- future notifications suppressed;
- old read/search/attachment endpoints deny according to configured history policy;
- whether former member keeps historical read access is explicit conversation policy, not accidental.

Default candidate for private team/resource chat: access ends when entitlement/seat ends unless conversation type explicitly grants alumni/history access.

---

# Transport abstraction

Canonical DB state is transport-independent.

Possible transport modes:
- REST polling baseline;
- long polling/SSE only if infrastructure supports;
- WebSocket/realtime external service adapter future.

Transport can signal “new message available” but does not become source of truth.

No requirement that WordPress PHP request remains open for realtime.

---

# Caching

Allowed:
- request-local conversation policy;
- participant generation;
- recent message pages with access-context key.

Never globally cache private conversation HTML without user/policy context.

Revocation invalidates/unreaches prior access caches.

---

# Performance/index needs

Hot queries:
- conversations for user by activity;
- participants by conversation/user;
- messages by conversation sequence;
- unread counts;
- message UUID lookup + conversation;
- mentions;
- moderation queue;
- retention cleanup.

Avoid message body in wide covering indexes.

---

# Failure behavior

## Message persisted, notification enqueue fails
Message remains canonical; notification retry/recovery separate.

## Attachment upload fails
Required attachment message not marked complete; optional failed draft can retry.

## Transport unavailable
REST/poll fallback according to product tier; messages remain stored.

## Search index unavailable
Conversation/message direct browsing still works; search degraded, not authorization bypass.

## Membership event missed
Request-time policy remains defense; reconciliation fixes materialized participant state.

---

# Paper recommendation

Dedicated runtime stores for:
- conversations;
- participants/personal state;
- messages;
- moderation/reports;
- protected asset refs;
- optional derived search projection.

Reject:
- WordPress comments as universal chat message store;
- public uploads attachment URLs;
- read row per message/user as default;
- search index as authorization source;
- transport service as canonical message store by accident.

## Future benchmark — NOT AUTHORIZED

After consent:
- 100k conversations / millions of messages candidate;
- 2-person and 1k-participant groups;
- cursor pagination;
- unread counts;
- concurrent send idempotency;
- participant revoke while send/download/search occurs;
- attachment access;
- search projection reauthorization;
- retention/anonymization;
- polling load;
- object cache.

No chat table/index/transport/runtime has been created or run.