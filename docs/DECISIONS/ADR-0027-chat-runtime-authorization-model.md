# ADR-0027 — Chat Runtime & Authorization Architecture

Status: **Accepted architecture / physical storage and transport pending evidence**  
Date: 2026-08-27

## Decision

WPEssential Chat uses dedicated runtime domains for conversations, participants, messages, read state, moderation and private attachment references.

Canonical chat state is **transport-independent**. REST polling, SSE/WebSocket or external realtime adapters can signal/update transport but do not become accidental source of truth.

Search indexes/projections may locate candidate messages, but every result and attachment is reauthorized through current conversation/resource policy before content is returned.

## Security consequences

- message/conversation UUID knowledge never grants access;
- private attachments use Protected Asset references, not permanent public upload URLs;
- Membership/team revocation can terminate derived conversation access;
- search cannot bypass authorization;
- read-state optimization cannot leak conversation existence/content;
- message idempotency prevents duplicate send after retry.

## Data-model consequences

Preferred concepts:
- conversation;
- participant + personal state;
- message + server-authoritative order/sequence;
- moderation/report;
- protected asset reference;
- optional rebuildable search projection.

A per-message-per-user read row is not the default architecture; participant last-read sequence is preferred where linear conversation semantics allow.

## Rejected defaults

- WordPress comments as universal chat runtime;
- public media URLs for private files;
- search provider ACL as sole authorization;
- transport service as canonical message DB by accident;
- WordPress roles as conversation participant roles.

## Evidence pending

Exact tables/indexes, large group/message scale, unread counts, realtime transport, retention, Membership revocation races and search projection behavior require future consent-gated evidence.

Supporting: `docs/ARCHITECTURE/CHAT-RUNTIME-STORAGE-INDEX-ALTERNATIVES.md`.

ADR-0014 remains controlling.