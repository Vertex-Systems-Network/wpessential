# Chat — Post-UX Runtime Readiness Re-Audit V2

Issue: #828  
Parent: #817  
Audit anchor: `8f796aaf95f0957def439586eb1eb8fb2fd49d91`  
Surface: 21 / `chat`

## Decision

**READY_FOR_BOUNDED_RUNTIME_SLICE.**

Surface 21 is now `UX_CONTRACT_COMPLETE`; the Bank/Atomic/UX gates that blocked V1 are closed. Dedicated Chat owner runtime remains absent, while shared Auth/Policy, Query/Relations/Roles inputs, Assets/Media references, Notifications/Integrations, Audit/Observability and WordPress substrate are available. This is sufficient for a read-only Conversation Definition plus authorization/list/get diagnostic slice. It is not sufficient to authorize message persistence, realtime delivery or participant mutation.

## Runtime-family classification

| Family | V2 classification | Boundary |
|---|---|---|
| Module admission / definitions | REUSABLE_DEPENDENCY | Shared module and Definitions foundation. |
| Conversation identity / definition | MISSING | Surface-owned first-slice definition responsibility. |
| Participant/resource authorization | REUSABLE_DEPENDENCY + MISSING OWNER ADAPTER | Auth/Policy/Roles provide substrate; Chat must own conversation-resource composition. |
| Conversation list/get read model | MISSING | Candidate first-slice owner runtime. |
| Message persistence / revision / delete | MISSING / BLOCKED FOR FIRST SLICE | Mutation requires separate durable-storage and policy tranche. |
| Read/unread / presence | MISSING | Presence is especially privacy/retention-sensitive and later-gated. |
| Attachments | REUSABLE_DEPENDENCY / OUT_OF_SURFACE | Canonical File/Media owner; private access must remain server-authoritative. |
| Notifications / escalation | OUT_OF_SURFACE / BLOCKED FOR FIRST SLICE | Delegated channel/workflow owners. |
| Search / moderation / reporting | REUSABLE_DEPENDENCY + MISSING CHAT CONTRACT | Later bounded seams only. |
| Realtime transport | OUT_OF_SURFACE / BLOCKED | Provider execution remains separate. |
| Client-supplied identity, public private-file URLs, arbitrary HTML/script messages | BLOCKED | Explicitly prohibited. |

## Smallest bounded first runtime slice

**Read-only Conversation Definition + list/get authorization diagnostics.**

Candidate later paths:
- `frameworks/Modules/Chat/ChatModule.php`
- `frameworks/Modules/Chat/ConversationDefinition.php`
- `frameworks/Modules/Chat/ChatReadService.php`
- `frameworks/Modules/Chat/ChatAbilityHandler.php`
- `tests/Unit/Modules/Chat/`
- IDOR-focused real WordPress integration fixtures for conversation/participant/attachment read paths

The slice may parse schema-valid conversation definitions, resolve canonical participant/resource references, evaluate server-authoritative read policy, list/get only authorized conversation metadata and report missing dependency/provider health. It must not create conversations, persist/edit/delete messages, alter participants, expose private attachment URLs, track presence or open realtime provider connections.

## Required future exact-head evidence

- deny-by-default IDOR tests for conversation, participant and attachment reads;
- client-supplied user/role/participant identity never overrides server authority;
- explicit absent/denied/degraded states without resource-existence leakage;
- canonical private media/file authorization with no public URL shortcut;
- multisite/site/network isolation and safe cross-site reference rejection;
- bounded list pagination/search and large-thread performance constraints;
- privacy/retention proof that first slice stores no presence/message-content telemetry;
- arbitrary HTML/script payloads cannot become executable output;
- missing Notifications/Search/realtime providers degrade without direct fallback execution;
- Architecture Guards, PHP quality, Platform Compatibility Matrix and applicable integration/package/browser gates.

## Reliability, rollback and portability

Portable definitions may contain stable conversation policy/type identifiers and canonical resource/reference IDs only. Message bodies, transient presence, provider connection IDs and private signed URLs are not part of the first slice and must not be exported. Because the first slice is read-only and creates no chat data, rollback is module disablement/removal. Authorization dependency failure is fail-closed, never fail-open.

## Non-certifications

No conversation/message/participant mutation, message persistence, realtime/provider execution, external notification dispatch, presence tracking, public private-file access, runtime/product certification, production migration, deployment or release is authorized. Atomic lifecycle remains `UX_CONTRACT_COMPLETE`.
