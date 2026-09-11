# Chat — Bank Entry Readiness V1

Surface: **21 / Chat**  
Planning issue: **#587**  
Supervisor wave: **#583**  
Exact-main claim anchor: `b48f83f346293690e4f76d8b8ade082a22db1dee`

Status: **BANK ENTRY READY FOR RESEARCH/SEEDING — NOT BANK_REVIEWED — NO OPTION CONTRACT PROMOTION**

This document is planning evidence only. It does not seed/review the Master Options Bank, create schema-valid Atomic Option Contracts, persist messages, expose realtime transport, mutate moderation state or authorize runtime implementation.

## Current machine truth

- Surface 21 `chat` is `UNSEEDED` with **0 normalized Master Options Bank records**.
- The atomic planning ledger marks Surface 21 `ATOMIC_INVENTORY_COMPLETE`; that is planning inventory, not `OPTION_CONTRACT_COMPLETE` or `UX_CONTRACT_COMPLETE`.
- The canonical ownership index assigns conversation/message storage and Chat semantics to Surface 21, while attachments/files, Notifications, Membership, Roles/Policy and shared anti-abuse services remain delegated owners.
- Exact-main `frameworks/Modules` has no dedicated Chat runtime module.

The next valid product gate is Master Options Bank seeding + native/market review, not message persistence or realtime implementation.

## Existing in-repo evidence

Primary sources:

- `docs/MODULES/MESSAGE-CHAT-EXHAUSTIVE-SPEC.md`;
- `docs/MODULES/OPTION-INVENTORY.md`;
- `docs/PRODUCT/56-SURFACE-COMPETITOR-PARITY-MATRIX.md`;
- `docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md`.

The exhaustive spec already establishes strong safety boundaries: Chat is an authenticated/application messaging primitive, not automatically a public social network, E2E messenger, video-call system or generic omnichannel helpdesk; public anonymous live chat is advanced/off-by-default because identity, abuse, retention and escalation semantics differ.

## Candidate Bank families

Normalize at least these families independently:

1. **Conversation definition identity/lifecycle** — name/key/type/status/tags/linked surface.
2. **Conversation creation policy** — who may start, duplicate-prevention, subject/title, approval, operating hours.
3. **Participant policy** — derivation, invitation, add/remove/leave, max participants, last-owner/staff guard.
4. **Conversation states** — active/closed/archived/moderation hold/tombstone semantics.
5. **Composer/message policy** — plain/rich/Markdown-safe formatting, emoji, links, mentions, attachments, length, edit/delete windows, reactions/reply-to.
6. **Message schema/storage semantics** — UUID/conversation/author/type/body/timestamps/reply/idempotency/moderation/schema version.
7. **Deletion/redaction/history** — hide/redact/hard-delete conditions, edit-history retention.
8. **Attachments** — MIME/extension/size/count/private storage/download authorization/scan-state/orphan cleanup.
9. **Read state** — unread counts, last-read cursor, mark-read/unread, optional read receipts.
10. **Presence/typing** — ephemeral/privacy-controlled state, off-by-default candidates.
11. **Transport profile** — polling/Heartbeat/WebSocket/SSE/provider adapter capability and degraded-state semantics.
12. **Notifications** — new message/mention/digest/mute/escalation refs to Surface 19.
13. **Mentions** — audience, privacy-safe user search, role/team/all-channel controls, max mentions.
14. **Reactions** — allowlist, uniqueness/removal/moderation inheritance.
15. **Search** — conversation/user/admin scopes with authorization-filtered retrieval/index maintenance.
16. **Moderation/reporting** — report reasons/rates/queues/status/actions/internal notes/audit.
17. **Blocking/user safety** — direct-conversation block semantics without rewriting unrelated resource permissions.
18. **Support queue mode** — staff pools, categories, assignment, hours, auto-response, close/reopen, Workflow escalation.
19. **Membership/team mode** — entitlement/team derivation, access expiry/history policy, seat removal effects.
20. **Retention/privacy** — message/attachment/edit/moderation/read/IP retention, exporter/eraser/anonymization.
21. **Anti-abuse/limits** — messages/conversations/invites/uploads/participants/content/link limits.
22. **Frontend composition** — Dashboard/resource placement and safe UI tokens, not arbitrary JS/CSS.
23. **Permissions/Abilities** — admin and participant actions separated from resource Policy.
24. **Events** — privacy-minimized event payloads.
25. **Diagnostics/degraded states** — storage/transport/search/notification/attachment/queue health.
26. **Performance/reliability** — stable pagination, unread indexes, idempotency, concurrency, no N+1.
27. **Portability/dependencies** — definition refs only; conversation/message data movement separately governed.

## Native WordPress audit required before Bank review

A future native audit must explicitly classify what WordPress provides and what it does not:

- users, roles/capabilities and site membership as identity inputs, not a Chat authorization substitute;
- comments as a distinct content primitive, not an assumed private-message backend;
- REST/admin-AJAX/Heartbeat as possible transport primitives with nonce/session/capability constraints;
- media uploads as storage primitives only when private-chat access control can be enforced;
- user meta/options/transients only where scale/privacy semantics fit;
- privacy exporter/eraser integration and shared-conversation anonymization constraints;
- multisite user/site membership boundaries;
- cron/job primitives for async cleanup/notifications, not realtime truth.

Native capabilities must not be promoted into Chat semantics without an explicit typed policy/storage contract.

## Market audit required before Bank review

The future market audit should compare current WordPress/community/chat/messaging products and broader application-messaging patterns for:

- 1:1/group/resource/support/team conversation models;
- participant policy and authorization;
- message edits/deletes/replies/reactions/mentions;
- private attachments;
- unread/read receipt behavior;
- realtime/polling degradation;
- moderation/reporting/blocking;
- notifications/mutes;
- search;
- retention/privacy/export/erasure;
- abuse/rate limits;
- mobile/accessibility;
- high-volume pagination/indexing.

Market features are benchmarks, not automatic acceptance criteria. Public anonymous chat, hidden presence tracking, unrestricted moderator access, raw HTML/JS and unsafe public attachment URLs should be rejected, deferred or tightly bounded.

## Canonical ownership decisions

| Concern | Canonical owner |
|---|---|
| Conversation/message semantics, participant membership, read state, moderation state | **Surface 21 Chat** |
| Notification occurrence/routing/preferences | **Surface 19 Notifications** |
| Files/media storage and protected delivery | owning File/Media resource surface; Chat stores refs and enforces conversation authorization |
| Membership/team entitlement truth | **Surface 15 Membership** |
| Roles/capability membership + shared authorization | **Surface 30 / Policy** |
| Complex escalation/business orchestration | **Surface 17 Forms & Workflows** |
| Shared request/rate security floor | **Surface 27 Protector/shared rate service**, while Chat owns product limits |
| Search/index engine | **Surface 34 Search** when external index is used; Chat remains authorization authority |
| Generic package/data import/export orchestration | **Surface 26 Import/Export** |

## Rejected-unsafe / bounded candidates

The Bank should reject or tightly bound:

- arbitrary HTML/script in message bodies;
- public Media Library URLs as protection for private attachments;
- message/search APIs that trust stored participant ID without current Policy where hard access can be revoked;
- unrestricted moderator/global search access;
- precise long-term presence/activity tracking by default;
- mention search that enumerates unauthorized users;
- `@everyone` by default;
- arbitrary frontend CSS/JS fields;
- unbounded conversation/message loading;
- message send without idempotency/retry protection;
- realtime claims when only slow polling is active;
- event payloads containing full private message bodies by default;
- AI access to private message bodies/actions by default.

## Readiness decision

Surface 21 has sufficient product semantics to start a disciplined Master Options Bank seeding/native/market review, but exact-main `UNSEEDED / 0` blocks canonical Atomic Option Contract, UX lifecycle and runtime promotion.

Next gate:

1. normalize candidate Bank records;
2. complete native WordPress audit;
3. complete evidence-backed market audit;
4. resolve ownership/unsafe/deferred/duplicate items;
5. promote Bank only with zero unresolved review items;
6. derive schema-valid Atomic Option Contracts;
7. re-review the provisional UX before runtime authorization.
