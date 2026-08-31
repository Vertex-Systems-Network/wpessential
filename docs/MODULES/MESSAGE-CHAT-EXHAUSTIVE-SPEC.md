# WPEssential — Message & Chat System Exhaustive Option Specification

Status: **Phase 0 — Exhaustive Option Spec / planning only / no implementation authorized**  
Edition: **Pro**

## 1. Product scope
WPEssential Chat provides application messaging primitives for authenticated/frontend experiences. It is not automatically a public social network, end-to-end encrypted messenger, video-call system or external omnichannel helpdesk.

It must compose with User Profile, Membership, Dashboard, Notifications, Forms/Workflow, Protector, Files/Media, Policy Engine and Audit.

---

# 2. Chat modes

Supported product modes may include:
- direct 1:1 conversation;
- private group conversation;
- project/resource-linked conversation;
- support-style user ↔ permitted staff queue;
- membership/team channel;
- system/announcement thread with restricted reply;
- embedded conversation widget tied to a WPE Dashboard route/resource.

Public anonymous live chat is a separate advanced mode and remains off by default because identity, abuse, retention and escalation semantics differ.

---

# 3. Admin screens

## 3.1 Conversations
Columns:
- conversation ID/title;
- type;
- linked resource;
- participant count;
- last activity;
- unread/admin-attention state;
- status;
- moderation flag count;
- retention policy;
- owner/module;
- actions.

Filters:
- status active/archived/closed;
- type;
- linked module/resource;
- membership/team;
- reported/flagged;
- participant/user;
- date range;
- retention state.

Actions:
- open;
- inspect participants;
- close/reopen;
- archive;
- moderation actions;
- export where permitted;
- delete/anonymize according retention policy.

## 3.2 Messages / Moderation
Search/filter only with high privilege and policy.
Columns:
- message safe preview;
- conversation;
- author;
- created/edited;
- attachments;
- moderation state;
- report count.

## 3.3 Channels / Conversation Definitions
Used for reusable rules that determine who can create/join/chat.

## 3.4 Settings
Global defaults, storage, retention, notifications, attachment rules, presence/read receipts, abuse limits.

## 3.5 Diagnostics
Delivery/event/job state, websocket/polling adapter health if future real-time adapter exists, attachment storage health, queue errors.

---

# 4. Conversation Definition editor

## Identity
- name;
- key;
- enabled;
- type;
- description;
- tags;
- linked Dashboard/module surface.

## Creation policy
Who may start:
- authenticated users;
- selected roles/capabilities;
- membership/entitlement;
- team members;
- owners/managers of linked resource;
- staff/support capability;
- specific Query/Policy.

Options:
- user may start multiple conversations;
- one conversation per user/resource pair;
- duplicate prevention key;
- require subject/title;
- auto-create from event/workflow;
- approval before activation;
- operating hours optional for support-style mode.

## Participant policy
- creator automatically participant;
- add explicit user(s);
- derive resource owner;
- derive relation participants;
- role/capability audience;
- membership/team audience;
- staff assignment pool;
- max participants;
- participants may invite others toggle;
- managers may add/remove;
- users may leave;
- owner may leave;
- last staff/owner removal guard.

Authorization is recalculated against current policy where intended; stored participant membership alone must not bypass a newly revoked hard access policy without explicit historical-access semantics.

---

# 5. Conversation states

Candidate:
- `active`;
- `closed`;
- `archived`;
- `blocked`/`moderation_hold`;
- `deleted_tombstone` only if retention/audit requires.

Closed:
- read allowed according policy;
- new messages denied except reopen-capable principal.

Archived:
- normal UI hidden/filterable;
- no automatic deletion.

Moderation hold:
- message actions restricted;
- policy explains reason to permitted participants.

---

# 6. Message composer options

Per conversation definition:
- plain text enabled;
- rich text limited safe formatting;
- Markdown candidate only if sanitizer/render contract exists;
- emoji enabled;
- links enabled;
- link preview off by default unless SSRF/privacy-safe fetch service exists;
- mentions enabled;
- attachment enabled;
- max attachments/message;
- max message length;
- edit enabled;
- edit window;
- delete own message enabled;
- delete window;
- reactions enabled;
- quote/reply-to enabled;
- thread replies disabled by default until nested-thread UX accepted;
- send on Enter preference/user control;
- draft persistence local/server configurable.

No arbitrary HTML/script.

---

# 7. Message storage model semantics

Message fields conceptually:
- stable UUID;
- conversation UUID;
- author user ID/system principal;
- type text/system/attachment/action;
- body safe canonical format;
- created_at;
- edited_at;
- deleted/hidden state;
- reply_to reference;
- client idempotency key;
- moderation state;
- schema version.

Do not store one giant serialized conversation blob.

Edits:
- current content plus optional bounded edit history if moderation/audit policy requires;
- edit history retention clearly configured;
- user cannot rewrite system/audit messages.

Deletion modes:
- hide from ordinary participants;
- redact body while preserving tombstone metadata;
- hard delete only when policy permits and no audit/legal dependency.

---

# 8. Attachments

Controls:
- allowed MIME + extension allowlist;
- max size;
- max count;
- image preview;
- private storage mandatory for private chats;
- malware scanning status only if a real scanner/provider is integrated;
- SVG off/default unless sanitized;
- executable/archive types blocked by default;
- download name/content-disposition;
- retention tied to message/conversation;
- orphan cleanup;
- duplicate/upload retry behavior.

Attachment access requires conversation authorization on every download. A public Media Library URL is not valid protection for a private chat attachment unless access-controlled storage/proxy exists.

---

# 9. Read state

Per participant:
- unread count;
- last read message/sequence;
- last read timestamp;
- mark conversation read;
- mark unread manually optional;
- per-message read receipts off by default;
- group read receipt display privacy option;
- system/admin can disable detailed receipt visibility.

Read state update must not grant access to conversation.

---

# 10. Presence / typing

Optional, not core truth.
Controls:
- online presence off by default;
- last seen off by default;
- typing indicator optional;
- presence TTL;
- user privacy preference;
- no precise activity fingerprint history by default.

Presence is ephemeral state and should not be stored as long-term audit data.

---

# 11. Real-time transport

Product contract is transport-agnostic.
Candidate transports:
- REST polling baseline;
- Heartbeat integration where appropriate;
- WebSocket/SSE adapter future;
- managed real-time provider adapter future.

UI options must reflect actual adapter capability:
- refresh interval;
- reconnect/backoff;
- offline pending message state;
- duplicate send protection;
- connection health.

Do not claim “real-time” when only slow polling is active without labeling semantics.

---

# 12. Notifications

Per definition:
- in-app notification for new message;
- email notification;
- digest;
- push/provider future;
- mention notification;
- support escalation;
- after-hours acknowledgement.

User preferences:
- every message;
- mentions only;
- digest;
- mute conversation;
- mute until time;
- security/system messages cannot be muted only when explicitly classified mandatory.

Notification preview must not leak message body for private/sensitive chats unless site setting/user preference allows.

---

# 13. Mentions

Controls:
- mentions enabled;
- searchable mention audience limited to already visible/eligible users;
- role/team/all-channel mention permissions;
- `@everyone` off by default;
- max mentions/message;
- mention notification;
- privacy-safe user search.

Mention cannot add unauthorized user to conversation automatically unless policy explicitly defines invitation flow.

---

# 14. Reactions

Controls:
- enabled;
- allowed emoji/reaction set;
- one reaction per type/user;
- remove own;
- reaction notifications optional;
- moderation inheritance.

Reactions require message visibility authorization.

---

# 15. Search

Scopes:
- current conversation;
- user's accessible conversations;
- admin moderation search with dedicated capability.

Fields:
- query;
- author;
- date;
- attachment type;
- linked resource;
- moderation status admin only.

Search index must enforce authorization at result retrieval and cannot become a private-content bypass.

Index deletion/update follows message edits/retention.

---

# 16. Moderation / reports

Controls:
- allow participant report;
- report reason categories;
- free-text note optional;
- rate-limit reports;
- staff queue;
- assign moderator;
- moderation statuses open/reviewing/actioned/dismissed;
- hide message pending review optional per definition;
- warn user;
- remove/redact message;
- block user from conversation;
- close conversation;
- escalate through Workflow;
- moderator internal note;
- audit.

Moderators cannot gain broad unrelated conversation access solely because report system exists; dedicated scope/policy required.

---

# 17. Blocking / user safety

Candidate optional feature:
- user may block another user for new direct conversations;
- block does not rewrite shared project/team permissions automatically;
- existing mandatory/support conversations follow site policy;
- blocked-user list private;
- unblock.

This requires product decision before v1 if included.

---

# 18. Support queue mode

Options:
- intake audience;
- categories;
- priority;
- automatic staff pool;
- manual assignment;
- SLA metadata informational unless actual automation exists;
- operating hours/timezone;
- auto-response template;
- close reason;
- reopen window;
- convert Form submission into conversation;
- escalate to WPE Workflow;
- linked user/resource/customer data panel permission-filtered.

Not the same as WPE vendor Support Tickets platform surface.

---

# 19. Membership/team chat

Controls:
- required plan/entitlement;
- team-scoped;
- auto-join eligible users vs discover/join;
- access after enrollment expires: deny new access by default;
- historical messages after expiry: configurable, default deny if channel itself is membership-protected;
- grace state behavior follows Membership Policy;
- role-sync irrelevant;
- team seat removal invalidates access promptly.

---

# 20. Retention / privacy

Per definition:
- keep indefinitely;
- retain N days/months after message;
- retain after conversation closed for N;
- anonymize deleted user messages;
- attachment retention;
- edit history retention;
- moderation record retention;
- read receipt retention;
- IP logging off by default unless abuse/security purpose.

WordPress privacy integration:
- exporter returns user's appropriate conversation/message data subject to other-user/privacy policy;
- eraser may anonymize rather than delete shared conversation history;
- attachments handled consistently;
- audit/moderation exceptions explained.

---

# 21. Anti-abuse / limits

Controls:
- messages/minute;
- conversations/day;
- invitations/day;
- attachment upload rate;
- max active conversations/user optional;
- duplicate message detection window;
- max recipients/group size;
- content length;
- spam adapter future;
- link count limit optional.

Protector/Rate Limit service may enforce shared limits, but Chat owns product semantics.

---

# 22. Frontend widget options

Placement:
- Dashboard route;
- shortcode/block/widget adapter;
- linked resource component.

Visual controls through safe UI tokens:
- compact/full layout;
- conversation list visible;
- participant panel;
- header/title/avatar display;
- composer position;
- timestamp format;
- unread badge;
- empty-state text;
- attachment preview;
- mobile responsive drawer/panel behavior.

No arbitrary CSS/JS field in standard UI.

---

# 23. Permissions

Candidate:
- `wpe_chat_read_admin`
- `wpe_chat_manage_definitions`
- `wpe_chat_create_conversation`
- `wpe_chat_message_send`
- `wpe_chat_message_edit_own`
- `wpe_chat_message_delete_own`
- `wpe_chat_manage_participants`
- `wpe_chat_moderate`
- `wpe_chat_view_reports`
- `wpe_chat_export`
- `wpe_chat_delete_data`
- `wpe_chat_manage_retention`

Frontend participant authorization is resource policy, not merely global capability.

---

# 24. Abilities

Candidate:
- `wpessential/chat.conversation_list/get/create/close/reopen`
- `wpessential/chat.participant_add/remove`
- `wpessential/chat.message_list/send/edit/delete`
- `wpessential/chat.read_mark`
- `wpessential/chat.report_create`
- `wpessential/chat.moderation_action`
- `wpessential/chat.search`

AI default exposure:
- no private message bodies by default;
- expose only when explicit user/context permission and data policy allow;
- send/edit/delete/moderation disabled by default;
- never use AI as hidden chat data export channel.

---

# 25. Events

- conversation.created/closed/reopened;
- participant.added/removed/left;
- message.created/edited/deleted;
- message.reported;
- moderation.actioned;
- conversation.read;
- attachment.failed;
- notification requested;
- access revoked for membership/team changes where useful.

Generic event payload avoids full private message body by default; use ID/safe summary and authorized fetch.

---

# 26. Error/empty states

- no conversations;
- no eligible participants;
- access revoked;
- conversation closed;
- message too large;
- attachment rejected;
- storage unavailable;
- send timeout/unknown delivery state;
- duplicate retry;
- rate limited;
- participant removed concurrently;
- membership expired;
- moderation hold;
- search unavailable;
- real-time adapter disconnected with fallback.

---

# 27. Performance

- conversation list paginated/cursor-based candidate;
- messages paginated by stable sequence/time+ID;
- no loading entire conversation;
- unread counts indexed/materialized safely;
- participant lookup indexed;
- attachment metadata separate;
- search index optional/adapter;
- event delivery at-least-once + idempotent notification consumers;
- presence ephemeral;
- no N+1 participant/profile fetching.

---

# 28. Required tests after development consent

- IDOR: user cannot fetch another conversation/message/attachment;
- participant removed while reading/sending;
- membership/team entitlement revoked;
- private attachment direct URL blocked;
- duplicate send idempotency;
- concurrent participant add/remove;
- edit/delete window boundary;
- search authorization;
- mention user enumeration;
- report/moderator scope;
- retention/anonymization;
- notification body privacy;
- rate-limit race;
- real-time reconnect duplication;
- large conversation pagination;
- asset isolation/accessibility/mobile keyboard behavior.

## Maturity
**Exhaustive Option Spec.** Concrete runtime tables, real-time transport, search indexing and anonymous-chat scope remain technical/product blockers and require explicit owner development consent before implementation evidence.