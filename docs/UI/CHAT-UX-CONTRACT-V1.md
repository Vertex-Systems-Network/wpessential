# Chat — Provisional UX Contract V1

Surface: **21 / Chat**  
Planning issue: **#587**  
Supervisor wave: **#583**  
Exact-main claim anchor: `b48f83f346293690e4f76d8b8ade082a22db1dee`

Lifecycle status: **PROVISIONAL PLANNING ONLY**. Surface 21 remains `UNSEEDED / 0` in the Master Options Bank. This document guides later review; it does not promote `UX_CONTRACT_COMPLETE` or authorize message persistence/realtime transport.

## Product model

The UI keeps these concepts separate:

- **Conversation Definition** — reusable policy for who can start/join/chat and which features are available;
- **Conversation** — one active resource/team/direct/support thread;
- **Participant** — authorized member with current conversation/resource policy;
- **Message** — immutable identity plus current safe body/state and optional bounded edit history;
- **Attachment** — protected file reference, never assumed public media;
- **Read state** — per-participant cursor/unread state;
- **Report / Moderation action** — scoped safety workflow, not blanket admin access.

## Information architecture

Primary surfaces:

1. Conversation Definitions
2. Conversations
3. Conversation detail
4. Messages / Moderation
5. Settings
6. Diagnostics
7. Frontend conversation widget / Dashboard route

## Progressive disclosure

### Essential

Conversation Definition editor exposes:

- name/key/type/status;
- who may start;
- participant source/limits;
- linked resource/surface;
- message composer mode;
- attachment enabled + safe limits;
- edit/delete policy;
- notifications/mute baseline;
- retention preset;
- Validate / Preview / Save.

No arbitrary HTML/JS, executable callbacks, public-file bypass or unbounded participant/message controls.

### Advanced

Adds:

- duplicate prevention / one-conversation-per-resource rules;
- approval/operating-hours options for support mode;
- participant invitation/add/remove/leave rules;
- mentions/reactions/reply-to;
- read receipts/privacy;
- search scope;
- moderation/report configuration;
- membership/team access history policy;
- conversation state transitions;
- per-definition abuse/rate ceilings;
- dependency/usage summary.

### Expert

Adds declarative operational controls:

- registered transport profile;
- polling/reconnect/backoff/offline-send behavior;
- attachment scanner/storage-provider health refs;
- search-index adapter ref;
- retention/anonymization details;
- bounded edit-history retention;
- event payload privacy profile;
- diagnostics/log verbosity;
- import/export dependency refs.

Expert must not expose raw SQL, callbacks, JS, unrestricted moderator search or secret/provider credentials.

### System / Diagnostics

Read-only diagnostics show:

- Definition revision;
- storage/index health;
- transport capability and actual mode (polling vs realtime adapter);
- Notification dependency health;
- protected attachment provider health;
- membership/role/resource-policy dependency state;
- queue/event errors;
- retention cleanup health;
- rate-limit/abuse subsystem health;
- current compatibility/degraded state.

## Conversations list

Columns:

- conversation/title;
- type;
- linked resource;
- participant count;
- last activity;
- unread/admin-attention state;
- status;
- moderation flags;
- retention policy;
- owner/module;
- actions.

Filters include lifecycle, type, linked module/resource, membership/team, reported, participant/user, date and retention state.

High-privilege filters/search must not leak private conversation existence/content to unauthorized admins.

## Conversation detail

Show:

- title/resource context;
- participant summary;
- current access/degraded state;
- paginated messages;
- unread marker;
- composer if allowed;
- attachment/reaction/reply controls if enabled;
- moderation/close/archive controls only with Policy;
- deterministic empty/error/access-revoked states.

Never load the entire conversation by default.

## Participant UX

Participant picker only searches users visible/eligible under current policy.

Actions:

- add/invite;
- remove;
- leave;
- assignment/staff pool where supported.

Before removal, show consequences for access/unread/notifications. Guard against removing the last required owner/staff principal.

Stored participant membership alone does not override a newly revoked hard resource/membership access rule.

## Composer UX

Controls are definition-driven:

- plain/rich safe text;
- emoji;
- links;
- mentions;
- attachments;
- reply/quote;
- reactions;
- edit/delete windows;
- send-on-enter user preference.

Show remaining length/attachment limits before send. Offline/unknown-send state must distinguish pending, confirmed and retryable without duplicate-send ambiguity.

## Attachments

Attachment UI shows:

- allowed MIME/type;
- size/count limits;
- upload/scan state only if real scanner exists;
- private-access warning;
- failed/retry state.

Every download is re-authorized against conversation access. Public media URLs are not presented as secure private-chat storage.

## Read/unread

Per participant:

- unread count;
- last-read cursor;
- mark read/unread;
- optional per-message receipts.

Read receipts default toward privacy-preserving behavior and can be disabled per definition/site/user where the later contract allows.

Read-state mutation never grants conversation access.

## Presence and typing

Off by default candidate. If enabled, UI clearly labels ephemeral status and privacy preference. Do not expose precise long-term activity history by default.

## Transport/realtime UX

The UI must state the actual transport mode:

- polling;
- Heartbeat-like baseline;
- WebSocket/SSE/provider adapter where genuinely available.

Do not label slow polling as realtime. On disconnect show degraded state, reconnect policy and pending-send state without duplicating messages.

## Notifications and mute

Per conversation/definition user controls may include:

- every message;
- mentions only;
- digest;
- mute;
- mute until.

Notifications remain Surface 19. Private message bodies are hidden/redacted in notification preview unless explicit site/user policy permits disclosure.

Mandatory security/system messages can bypass mute only under narrowly defined policy.

## Mentions

Mention search is restricted to visible/eligible users. `@everyone` is off by default and separately permissioned.

A mention cannot silently add an unauthorized participant. If invitation semantics exist, they require explicit policy/action.

## Search

Scopes:

- current conversation;
- user's accessible conversations;
- dedicated admin moderation search.

Search results are authorization-filtered at retrieval time. Index presence is never itself authorization.

## Moderation and reports

Participant report flow:

- reason category;
- optional note;
- rate limit;
- acknowledgement.

Moderator UX:

- scoped queue;
- assigned moderator;
- open/reviewing/actioned/dismissed;
- message hide/redact where authorized;
- user warn/block-from-conversation;
- close conversation;
- Workflow escalation;
- internal note/audit.

Report/moderation privileges do not automatically unlock unrelated conversations.

## Membership/team access

Show the entitlement/team dependency and what happens after access expires. Default candidate for protected membership channels: deny new and historical access after expiry unless explicit policy states otherwise.

Seat/member removal should invalidate access promptly and visibly.

## Retention/privacy UX

Definition editor exposes an understandable retention summary and impact:

- indefinite;
- N days/months;
- after-close retention;
- attachment retention;
- edit-history/moderation retention;
- anonymization behavior.

Admin diagnostics must distinguish policy from actual cleanup health.

Privacy export/erase behavior should explain when shared history is anonymized rather than deleted because other participants/audit obligations exist.

## Accessibility

Required:

- keyboard-operable conversation list/composer/actions;
- no drag-only participant/message interactions;
- semantic message list and status labels;
- accessible unread announcements without noise;
- focus after send/edit/delete/report/moderation actions;
- error messages linked to composer/upload controls;
- mobile keyboard-safe composer/layout;
- no color-only moderation/unread/status indicators;
- sufficient touch target sizes.

## Multisite

Future normalized contract must explicitly define:

- site-local vs network conversation definitions;
- user vs site-membership participant eligibility;
- network moderation boundaries;
- attachment/storage scope;
- notification scope;
- export/retention/privacy behavior across sites.

No cross-site access may be inferred from Super Admin UI alone.

## Portability

Definition import/export may contain configuration and dependency references, never private conversation/message bodies by default. Data export/migration is a separately governed Surface 26/owner operation with privacy/retention safeguards.

## Lifecycle decision

This is implementation-ready interaction guidance only. Because exact-main Master Options Bank status is still `UNSEEDED / 0`, it must be re-reviewed after Bank review and schema-valid Atomic Option Contracts. No runtime or product-parity certification follows from this document.
