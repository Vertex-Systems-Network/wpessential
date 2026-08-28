# ADR-0121 — Message & Chat Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP05`

## Context

Message & Chat already has accepted Phase 0 product and runtime architecture:
- canonical Chat state is transport-independent;
- conversations, participants, messages, personal read state, moderation and private attachment references are separate runtime concerns;
- message order is server-authoritative/deterministic;
- client retries require idempotency;
- private attachments use Protected Asset semantics rather than permanent public URLs;
- search is a rebuildable candidate projection and every result is reauthorized;
- Membership/team/resource revocation can terminate derived access;
- read-state optimization cannot become access authority;
- Notifications own notification delivery, not canonical message truth;
- CRT1/PT-D is the first future benchmark baseline and CRT2/PT-E is mandatory before final topology selection.

The remaining gap was a bounded executable protocol proving these semantics across concurrency, revocation, attachments, search, realtime transport, moderation, privacy, restore and Multisite.

## Decision

Chat production-readiness claims require the applicable fixtures in:

`docs/QUALITY/MESSAGE-CHAT-EXECUTABLE-EVIDENCE-PROTOCOL.md`

The protocol fixes **CH-01…CH-142** evidence covering:
- Definition/revision and conversation creation Policy;
- participant add/remove/leave and Membership/team/resource revocation races;
- server-authoritative message ordering and client idempotency;
- edit/delete/tombstone/reply/reaction semantics;
- mention privacy and user-enumeration boundaries;
- private attachment MIME/origin/download/finalization/orphan safety;
- last-read/unread/personal-state concurrency and cache isolation;
- search projection reauthorization, stale-index deletion/revoke behavior and cross-site isolation;
- polling/realtime reconnect, long-lived authorization refresh and transport-independence;
- Notification integration without private-body leakage or message rollback;
- moderation/report/block/rate-limit scope;
- privacy export/erasure/anonymization/retention;
- clone/restore/Site Lifecycle behavior;
- CRT1/PT-D and CRT2/PT-E Multisite/topology/scale comparison;
- explicit MUST-NOT/stop-the-line gates.

## Negative requirements locked

A certified Chat runtime MUST NOT:
- expose conversation/message/participant/attachment/search content through direct-ID manipulation;
- treat stale participant membership, cache or realtime subscription as authority after a controlling access revoke;
- use public/permanent origin URLs as protection for private attachments;
- duplicate a logical message on accepted retry under the same idempotency contract;
- use client clocks or transport event order as canonical message ordering;
- trust search-index/provider ACL as the sole authorization boundary;
- leak inaccessible conversation existence/content through unread counts, caches or search totals;
- allow a long-lived realtime connection to preserve revoked access indefinitely;
- treat transport/provider state as canonical Chat data;
- let moderation/report privileges become unrestricted private-chat access;
- leak private message body into Notifications by default;
- blindly reactivate copied participants, transport subscriptions or pending notifications after clone/restore;
- cross site/network boundaries through IDs, indexes, caches, asset references or realtime channels;
- claim data was hard-deleted while retained body/asset/index/provider copies remain contrary to disclosed retention policy.

## Transport/search truth

This ADR does **not** select a realtime transport or search provider.

- REST polling remains the conservative baseline candidate.
- SSE/WebSocket/managed realtime adapters remain evidence-gated.
- Search may use SQL/FULLTEXT/rebuildable external projection only if the selected profile preserves request-time reauthorization and privacy.
- Presence/typing is ephemeral and not durable message/audit truth by default.

## Physical topology

This ADR does **not** finalize CRT1 vs CRT2.

- `CRT1/PT-D` remains first future benchmark baseline.
- `CRT2/PT-E` remains mandatory comparison.
- Final selection requires executed authorization, revocation, attachment, search, noisy-neighbor, migration, Backup/lifecycle and scale evidence.

## Current state

CH fixtures documented: **142**.  
CH executed: **0/142**.  
Chat runtime certification: **none**.  
Realtime transport certification: **none**.  
Search adapter certification: **none**.  
Final Chat physical topology: **OPEN / evidence-gated**.

No Chat table/index, conversation, participant, message, private asset, search document, realtime connection, notification dispatch, retention job, migration, benchmark or runtime test was executed.

## Development gate

This is planning-only acceptance. Execution remains blocked until explicit scoped owner consent under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md`.