# WPEssential — Message & Chat Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package: `P0-M00-WP05`  
Project state: `PLANNED_EXISTING_PROJECT`  
Execution mode: `PLANNER_ONLY`  
Governs: ADR-0027, ADR-0077, Message & Chat specifications, Chat Runtime storage/search architecture, Membership/Teams, Notifications, Protected Assets, Policy/Abilities, Protector/Rate Limit, Multisite/Site Lifecycle, ADR-0014.

## 1. Purpose

Define bounded executable evidence required before WPEssential can claim production-ready private messaging, conversation authorization, participant lifecycle, message ordering/idempotency, private attachments, unread/read state, search, moderation, notifications or realtime transport.

This protocol tests the accepted Chat architecture. It does not select a final transport, search provider or physical topology and does not authorize runtime execution.

## 2. Canonical invariants

A future certified implementation must preserve:

1. **Canonical Chat state is transport-independent.** REST polling, SSE/WebSocket or managed realtime adapters never become accidental message authority.
2. **Conversation/message/attachment UUID knowledge never grants access.** Every read/write/download/search action reauthorizes current scope and resource policy.
3. **Membership/team/resource revocation can terminate derived access immediately according to explicit history policy.** Stale participant rows/caches/transport subscriptions are not sufficient authority.
4. **Messages use server-authoritative deterministic order/sequence.** Client clocks never decide canonical ordering.
5. **Client retry is idempotent.** Duplicate send after timeout resolves to the same accepted logical message when the idempotency key matches.
6. **Private attachments use Protected Asset semantics.** Permanent public upload/object URLs are not accepted protection.
7. **Search is a rebuildable candidate projection.** Every candidate is reauthorized before message content or metadata is returned.
8. **Read state is personal state, not access authority.** Optimized unread counters/caches must not reveal inaccessible conversation existence/content.
9. **Notification delivery is separate from Chat message truth.** Notification failure cannot roll back an accepted message; notification preview cannot leak private body by default.
10. **Presence/typing/realtime cursor state is ephemeral.** It is not durable chat/audit truth unless explicitly modeled for a separate purpose.
11. **Moderation does not imply unrestricted chat access.** Moderator/report access remains capability + scope/policy constrained.
12. **Retention/privacy operations preserve shared-conversation integrity truthfully.** Anonymization, tombstones and evidence retention must not be presented as hard deletion when it is not.
13. **Site/network scope is explicit.** Search, cache, attachments, transport channels and IDs cannot collide across sites.
14. **Restore/clone never blindly replays pending transport/notification state or grants copied participants access without revalidation.**

## 3. Future certification profile

Every future run records:
- WordPress/PHP/database versions and P-001 status;
- single-site/Multisite topology;
- Chat runtime schema/version;
- physical profile (`CRT1/PT-D`, `CRT2/PT-E`, or later accepted profile);
- Policy/Ability/Membership/Team versions;
- Protected Asset profile;
- Notification protocol/certification state;
- transport adapter/version/capability profile;
- search adapter/index profile;
- persistent object cache profile;
- retention/privacy configuration;
- relevant rate-limit/Protector configuration;
- representative conversation/message/group/attachment workloads.

# 4. Definition and conversation creation fixtures

### CH-01 — Draft Definition not active
Draft/disabled Conversation Definition cannot authorize new production conversation creation.

### CH-02 — Definition publish validation
Invalid participant policy, unsafe attachment configuration, unsupported transport dependency or impossible retention configuration fails closed.

### CH-03 — Definition revision pinning
Conversation stores the intended Definition revision/policy identity where behavior requires historical determinism.

### CH-04 — Definition changed after conversation creation
Existing conversation behavior follows explicit follow-current vs pinned semantics; no silent security weakening.

### CH-05 — Unauthorized conversation create
Direct REST/Ability call cannot create a conversation when current Policy denies the principal.

### CH-06 — Resource-linked creation IDOR
Forged resource/site/team IDs cannot create a conversation against another resource/scope.

### CH-07 — Duplicate conversation key
Concurrent one-conversation-per-user/resource creation admits one logical conversation.

### CH-08 — Direct 1:1 creation
Both participants are validated/eligible before activation; arbitrary user ID injection is denied.

### CH-09 — Private group creation
Participant cap and add-authority are enforced server-side.

### CH-10 — Team/resource derived conversation
Derived eligibility is based on authoritative Membership/team/resource Policy, not display data.

### CH-11 — Support-style creation
Staff assignment/pool semantics do not grant unrelated staff global chat access.

### CH-12 — Anonymous/public mode disabled
Guest endpoints fail closed unless a separately accepted anonymous-chat profile explicitly enables them.

# 5. Participant lifecycle and authorization fixtures

### CH-13 — Participant add authorization
Only authorized principal can add an eligible participant.

### CH-14 — Participant enumeration resistance
Participant picker/search does not expose users outside the caller's allowed audience.

### CH-15 — Concurrent duplicate participant add
Unique active participation is preserved under races.

### CH-16 — Participant removal
Removed principal immediately loses new-message and protected-resource access according to policy.

### CH-17 — Remove vs send race
A send racing with participant removal resolves according to authoritative transaction/version/precondition boundary; no post-revoke accepted message without policy.

### CH-18 — Remove vs read race
A read/list request crossing revocation does not return unauthorized content from stale cache/snapshot.

### CH-19 — Remove vs attachment download race
Protected Asset authorization is evaluated at delivery boundary; stale signed/session state cannot extend access beyond accepted policy.

### CH-20 — Membership entitlement revoke
Derived conversation access ends promptly under default protected-team policy.

### CH-21 — Team seat removal
Seat removal invalidates derived access and relevant caches/transport subscriptions.

### CH-22 — History-after-revoke policy
Configured deny-history vs alumni/history access is explicit and tested; accidental stale access is rejected.

### CH-23 — User leaves conversation
Leave semantics preserve shared history while denying future actions according to conversation type.

### CH-24 — Last owner/staff guard
Required owner/staff role cannot be removed in a way that leaves conversation unrecoverable where policy forbids it.

### CH-25 — Conversation close
Closed conversation denies ordinary send but preserves allowed reads/history.

### CH-26 — Conversation reopen
Only authorized reopen-capable principal can restore send eligibility.

### CH-27 — Moderation hold
Restricted operations fail closed while permitted moderator/recovery paths remain bounded.

### CH-28 — Conversation existence concealment
Unauthorized lookups do not leak sensitive conversation existence through divergent response/body/count behavior beyond accepted policy.

# 6. Message admission, ordering and idempotency fixtures

### CH-29 — Authorized message send
Current conversation and send Policy are checked at server boundary.

### CH-30 — Non-participant send IDOR
Knowing conversation UUID cannot send.

### CH-31 — Client idempotency key retry
Same sender/conversation/key and same material payload returns existing logical message.

### CH-32 — Idempotency key conflict
Same key with materially different payload yields conflict/anomaly, not overwrite or second hidden message.

### CH-33 — Concurrent duplicate send
Two workers processing same client key admit one logical message.

### CH-34 — Server-authoritative sequence
Canonical sequence/order is generated server-side.

### CH-35 — Equal timestamp ordering
Messages with same timestamps remain deterministically ordered by sequence/cursor.

### CH-36 — Client clock skew
Future/past client time cannot reorder canonical history.

### CH-37 — Pagination before/after cursor
No duplicate/skip under stable cursor when new messages arrive concurrently within accepted semantics.

### CH-38 — Deleted/hidden message pagination
Visibility changes do not corrupt cursor correctness or leak hidden bodies.

### CH-39 — Large message rejection
Length/payload budgets are enforced before durable admission.

### CH-40 — Unsafe rich text
Script/style/event-handler/unsafe URL content is rejected/sanitized according to renderer contract.

### CH-41 — Shortcode/PHP execution denial
Message body cannot execute shortcode/PHP/template code.

### CH-42 — Link safety
Rendered links follow safe escaping/target policy; link-preview fetch remains off unless Safe HTTP profile is certified.

### CH-43 — System message authority
Ordinary user cannot forge privileged/system/audit message type.

### CH-44 — Message persisted, notification enqueue fails
Message remains canonical accepted state; Notification recovery is separate.

# 7. Editing, deletion, replies and reactions fixtures

### CH-45 — Edit-own authorization
Only allowed author/moderator path can edit.

### CH-46 — Edit window boundary
Before/at/after window behavior is deterministic and server-time based.

### CH-47 — Concurrent edit conflict
Stale edit cannot silently overwrite newer revision without accepted conflict semantics.

### CH-48 — Edit sanitization
Edited content passes same canonical validation/sanitization as original.

### CH-49 — Delete-own authorization
Delete window and ownership are enforced server-side.

### CH-50 — Tombstone semantics
Where deletion keeps tombstone, body removal and continuity are truthful and bounded.

### CH-51 — Hard delete eligibility
Hard deletion occurs only where retention/moderation/audit references permit.

### CH-52 — Reply-to authorization
Reply target must belong to visible authorized conversation/message scope.

### CH-53 — Deleted quoted context
Reply/quote UX follows explicit policy without resurrecting deleted private body.

### CH-54 — Reaction authorization
Reaction requires current message visibility.

### CH-55 — Reaction uniqueness race
Same user/type reaction is unique/idempotent under concurrent requests.

### CH-56 — Hidden/moderated message reaction
Reaction endpoints cannot disclose or mutate inaccessible message.

# 8. Mentions and user-discovery fixtures

### CH-57 — Mention audience authorization
Only currently eligible/visible users are discoverable for mentions.

### CH-58 — Mention cannot auto-add unauthorized participant
Mention reference alone never grants conversation membership.

### CH-59 — Role/team mention authority
Special mention groups require explicit permission.

### CH-60 — `@everyone` disabled default
Ordinary user cannot bypass disabled/global mention policy.

### CH-61 — Mention cap
Message-level mention fan-out is bounded.

### CH-62 — Mention notification privacy
Notification preview obeys private-message minimization and current recipient access.

# 9. Private attachment fixtures

### CH-63 — MIME + extension validation
Executable/script/polyglot or disallowed archive content is rejected according to file policy.

### CH-64 — Size/count limits
Server-side limits enforce both per-file and per-message budgets.

### CH-65 — SVG default denial/sanitization
SVG is rejected by default or accepted only through certified sanitization profile.

### CH-66 — Private storage origin
Private attachment is not retrievable via permanent public Media Library/origin URL.

### CH-67 — Authorized download
Every download rechecks current conversation/message authorization.

### CH-68 — Attachment IDOR
Forged asset/message/conversation/site IDs cannot cross scope.

### CH-69 — Revoked participant download
Previously authorized participant loses access promptly after revoke according to history policy.

### CH-70 — Attachment upload retry idempotency
Retry does not create uncontrolled duplicate assets/message links.

### CH-71 — Message commit vs asset finalization crash
Partial state is reconciled; incomplete attachment is not exposed as healthy message asset.

### CH-72 — Orphan draft cleanup
Unattached private uploads expire via bounded cleanup without deleting referenced assets.

### CH-73 — Safe filename/download headers
User-supplied names cannot inject headers/paths/scripts.

### CH-74 — Malware scan truth
UI never labels attachment scanned/clean unless a certified scanner/provider produced that evidence.

# 10. Read state, unread counts and personal state fixtures

### CH-75 — Mark read authorization
User can only mutate own readable conversation state unless high-privilege delegated capability exists.

### CH-76 — Last-read sequence monotonicity
Stale request cannot move last-read cursor backwards unless explicit mark-unread semantics use separate state.

### CH-77 — Concurrent read updates
Racing devices preserve monotonic accepted read state.

### CH-78 — Unread count correctness
Unread derives from visible message sequence semantics and does not count inaccessible/hidden content incorrectly.

### CH-79 — Revocation unread cache
Unread/count cache cannot reveal revoked conversation existence/count.

### CH-80 — Conversation list cache isolation
Private conversation list/count cache is keyed by principal/site/access generation as required.

### CH-81 — Mark unread optional state
Manual mark-unread does not rewrite historical receipt truth or access authority.

### CH-82 — Detailed read receipts privacy
Per-message/group receipt visibility respects configured privacy policy.

### CH-83 — Mute/pin/archive personal state
One participant's personal state does not alter others or conversation authorization.

# 11. Search/index fixtures

### CH-84 — Search only accessible conversations
Query scope is bounded to authorized candidate conversation set or equivalent safe strategy.

### CH-85 — Search candidate reauthorization
Every search hit is reauthorized before body/metadata return.

### CH-86 — Stale index after participant revoke
Stale search document cannot return content after access revoke.

### CH-87 — Stale index after message edit
Index update lag cannot expose old protected content beyond accepted controlled behavior.

### CH-88 — Deleted/redacted message index
Deleted/redacted body is removed/rebuilt and cannot remain searchable indefinitely.

### CH-89 — Cross-site index collision
Same IDs/terms across sites cannot return another site's content.

### CH-90 — External search privacy boundary
If external adapter is tested, exported fields are minimized, scoped and documented; provider index ACL is never sole authorization.

### CH-91 — Search outage
Direct authorized message browsing remains functional; search degrades safely.

### CH-92 — Admin moderation search scope
Moderator capability does not automatically grant unrelated conversation access outside accepted moderation scope.

### CH-93 — Search query abuse
Expensive/wildcard/parser inputs are bounded and cannot cause unsafe SQL/provider query injection.

### CH-94 — Search pagination stability
Results use deterministic bounded pagination/cursors and do not leak total counts across inaccessible scope.

# 12. Realtime/transport fixtures

### CH-95 — REST polling baseline
Polling returns only messages/events current principal can access and uses bounded cursor/page size.

### CH-96 — Poll after revoke
Revoked principal stops receiving new content even with stale cursor/session.

### CH-97 — Reconnect duplicate protection
Transport reconnect/replay cannot duplicate canonical message creation.

### CH-98 — Out-of-order transport signal
Signal order cannot reorder canonical DB message order.

### CH-99 — Duplicate realtime event
Client/server dedupe prevents duplicate visible logical event where appropriate.

### CH-100 — Transport unavailable
Canonical send/history behavior degrades to accepted fallback; transport outage does not lose durable messages.

### CH-101 — WebSocket/SSE auth refresh
Long-lived connection cannot retain access indefinitely after membership/team/capability revoke.

### CH-102 — Cross-site channel isolation
Realtime channel/topic identifiers cannot subscribe to another site by forged ID.

### CH-103 — Transport service data authority denial
External realtime provider state is never treated as canonical message DB unless a future ADR explicitly changes architecture.

### CH-104 — Presence TTL
Presence expires as ephemeral state and is not used as durable authorization/audit evidence.

### CH-105 — Typing privacy
Typing indicator exposes only currently authorized participants and is not retained as detailed history by default.

# 13. Notification integration fixtures

### CH-106 — New-message Notification occurrence
One accepted message can emit the configured logical notification occurrence without changing message truth.

### CH-107 — Duplicate event notification dedupe
At-least-once Chat event delivery does not produce uncontrolled duplicate notifications.

### CH-108 — Recipient revoked before notification delivery
Notification suppresses/rechecks according to access-sensitive policy.

### CH-109 — Private body preview default
Notification payload avoids private message body by default unless explicit safe policy permits preview.

### CH-110 — Mention notification
Mention recipient must still have current conversation access.

### CH-111 — Muted conversation
Mute affects optional notification routing, not message visibility or required security/system behavior.

### CH-112 — Notification failure
Notification/provider failure does not roll back accepted Chat message.

# 14. Moderation, reports, blocking and abuse fixtures

### CH-113 — Report creation authorization
Only eligible participant/authorized reporter can report visible message/conversation.

### CH-114 — Report rate limit
Concurrent report abuse is bounded atomically/appropriately.

### CH-115 — Moderator scope
Moderator sees only data allowed by moderation scope/policy.

### CH-116 — Redaction/moderation action
High-risk moderation mutation is audited and preserves required evidence/tombstone semantics.

### CH-117 — Block direct-chat creation
User block policy can prevent new direct conversation where feature enabled.

### CH-118 — Block does not override mandatory team policy
Blocking does not silently rewrite authoritative team/resource communication rules outside defined semantics.

### CH-119 — Message rate-limit race
Concurrent sends cannot bypass configured per-user/conversation/site ceilings through race.

### CH-120 — Attachment upload rate limit
Upload abuse is bounded without leaking private asset state.

### CH-121 — Conversation creation/invite rate limits
Abusive fan-out is bounded and independently observable.

### CH-122 — Moderation event recursion
Moderation/notification/workflow events cannot recursively create unbounded chat/report loops.

# 15. Privacy, retention, lifecycle and recovery fixtures

### CH-123 — Privacy export
Exporter returns authorized data-subject Chat data without exposing unrelated participant/private data.

### CH-124 — User erasure/anonymization
Shared conversation integrity is preserved while personal identity/body is removed/anonymized according to policy.

### CH-125 — Attachment erasure alignment
Attachment lifecycle follows message/privacy decision; no orphan public/private remnants contrary to policy.

### CH-126 — Moderation retention exception
Required moderation evidence can outlive ordinary body retention only under explicit documented policy.

### CH-127 — Retention cleanup concurrency
Chunked purge cannot delete newly referenced/held data due stale scan.

### CH-128 — Search cleanup reconciliation
Retention/anonymization removes or rebuilds derived search projection.

### CH-129 — Site delete with Chat data
Site Lifecycle blocks new unsafe operations and applies explicit retention/delete policy before/after destructive site lifecycle.

### CH-130 — Site clone
Definitions may clone as configured; participants/messages/private attachments/transport subscriptions are not blindly activated in clone.

### CH-131 — Backup/restore
Restored Chat data revalidates site identity, participant access, search projection and transport/notification state before use.

### CH-132 — Restore pending notification/transport state
Historical pending signals/jobs do not resend/replay private message notifications blindly.

# 16. Multisite, topology and scale fixtures

### CH-133 — CRT1/PT-D scope predicates
Shared runtime rows use authoritative network/site scope in conversations/participants/messages/moderation/assets/search identities.

### CH-134 — Wrong-site direct IDs
Conversation/message/asset/search IDs from another site are denied even when internal numeric IDs collide.

### CH-135 — CRT2/PT-E provisioning
Per-site tables provision/migrate/lifecycle correctly and cannot drift silently across large networks.

### CH-136 — 100k conversations
Conversation list/participant lookup/activity ordering remains bounded and authorization-safe.

### CH-137 — Million-message history
Cursor pagination, UUID lookup, retention and message append remain within measured budgets.

### CH-138 — 1k-participant group
Participant authorization/unread/fan-out/mention behavior avoids unbounded request-time loops.

### CH-139 — Concurrent hot conversation
High write/read concurrency measures sequence allocation, idempotency, lock contention and latency without correctness loss.

### CH-140 — Search projection scale
Large index/rebuild/update/deletion workloads remain authorization-safe and recoverable.

### CH-141 — Polling/realtime load
Transport profile measures concurrent clients, cursor traffic, reconnect storms and fallback behavior without making transport canonical.

### CH-142 — 100/1k/10k-site CRT1 vs CRT2 comparison
Measure noisy-neighbor behavior, scope isolation, provisioning/migration, Backup/restore, lifecycle, diagnostics and operational cost.

# 17. MUST NOT / stop-the-line gates

Chat certification fails if any fixture demonstrates:
- unauthorized conversation/message/participant/attachment/search access by ID manipulation;
- stale Membership/team/resource access granting post-revoke private content contrary to policy;
- public/permanent origin URL bypass for private attachments;
- duplicate retry creating repeated logical messages under the same idempotency contract;
- client clock or transport order becoming canonical message order;
- search index/provider ACL being treated as sole authorization;
- private search result/body surviving revoke/delete due stale index without request-time denial;
- unread/count/cache leaking inaccessible conversation existence/content;
- long-lived realtime connection retaining revoked access without bounded reauthorization;
- transport/provider state replacing canonical Chat DB truth;
- moderator/report capability granting broad unrelated private-chat access;
- notification preview leaking private body contrary to policy;
- restore/clone replaying private transport/notification state or copied participant authority blindly;
- cross-site ID/cache/index/channel collision exposing another site's Chat data;
- retention/privacy UI claiming hard deletion while body/asset/index/provider copy remains without disclosed policy.

These are stop-the-line defects for the affected certification scope.

# 18. Performance evidence

Capture at minimum:
- conversation list p50/p95/p99;
- message append p50/p95/p99;
- per-conversation sequence contention;
- idempotency-key contention;
- participant add/remove/revoke latency;
- unread count/update latency;
- cursor page latency/query count;
- private attachment authorization/download overhead;
- search query + reauthorization cost;
- search indexing/rebuild/delete lag;
- polling/realtime request/event rates;
- reconnect amplification;
- notification fan-out from Chat;
- moderation queue query latency;
- retention/anonymization throughput;
- DB locks/index sizes/storage growth;
- one-hot-conversation and one-hot-site noisy-neighbor impact;
- CRT1/CRT2 migration/provisioning/diagnostics cost.

Performance optimization must not weaken authorization, revocation, attachment privacy, idempotency, deterministic ordering, search reauthorization or site isolation.

# 19. Required future Chat report

Include:
- exact runtime/topology/transport/search/asset profiles;
- CH-01…CH-142 pass/fail/NA with rationale;
- conversation/participant authorization evidence;
- message ordering/idempotency/concurrency evidence;
- edit/delete/reaction/mention evidence;
- private attachment origin/access evidence;
- read/unread/cache evidence;
- search projection + reauthorization evidence;
- transport/reconnect/revoke evidence;
- Notification integration evidence;
- moderation/abuse evidence;
- privacy/retention/restore/lifecycle evidence;
- CRT1/CRT2 physical and scale measurements;
- unsupported/degraded profiles;
- final Chat runtime/search/transport/topology recommendation.

## 20. Current state

**CH fixtures documented: 142.**  
**CH fixtures executed: 0/142.**  
**Chat runtime certified: none.**  
**Realtime transport certified: none.**  
**Search adapter certified: none.**  
**Final Chat physical topology: open / evidence-gated.**

CRT1/PT-D remains the first future benchmark baseline. CRT2/PT-E remains a mandatory comparison before final topology selection.

No Chat table/index/message/conversation/participant/private asset, realtime connection, search document, notification dispatch, retention job, migration, benchmark or runtime test has been executed.

## 21. Development gate

Execution requires explicit scoped owner consent under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md`, plus applicable P-001 and supporting asset/Job/Notification prerequisites.

Until then this protocol remains planning evidence only: `NOT EXECUTED`.