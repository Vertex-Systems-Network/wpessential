# Chat — Native WordPress Audit V1

Surface: **21 / Chat**  
Issue: **#697**  
Parent: **#686**  
Exact-main audit anchor: `3839142cc8a225f443fcf9f6e0c26eb9641742e7`  
Seed: `config/product/options-bank/chat.json` — **17 records / BANK_SURFACE_SEEDED**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE; WORDPRESS CORE HAS NO PRIVATE/REALTIME CHAT CONVERSATION ENGINE.** No new Chat-owned native family is required for this V1 Bank. Native users/capabilities, REST/AJAX/Heartbeat, comments, media, privacy and Cron are adjacent substrates or canonical-owner references only. No message dispatch, persistence or lifecycle promotion occurs here.

## Current native sources

- Roles & Capabilities — https://developer.wordpress.org/apis/security/user-roles-and-capabilities/
- REST API — https://developer.wordpress.org/rest-api/
- AJAX — https://developer.wordpress.org/plugins/javascript/ajax/
- Heartbeat API / AJAX handler — https://developer.wordpress.org/plugins/javascript/heartbeat-api/ and https://developer.wordpress.org/reference/functions/wp_ajax_heartbeat/
- Comments — https://developer.wordpress.org/plugins/comments/
- Media — https://developer.wordpress.org/apis/media/
- Privacy exporter/eraser extension points — WordPress personal-data tooling
- WP-Cron — https://developer.wordpress.org/plugins/cron/
- accepted readiness evidence: `docs/PRODUCT/CHAT-BANK-ENTRY-READINESS-V1.md`

Comments are a distinct public/content discussion primitive and must not be silently reused as private-chat persistence. REST, AJAX and Heartbeat can support bounded invocation/polling transports, but none provides conversation authorization, message schema, delivery guarantees, unread state, presence, moderation or realtime provider semantics by itself.

## Seed-record disposition

| Seed record | Native disposition | Integration action |
|---|---|---|
| `chat.conversation.definition` | **WPE PRODUCT SEMANTIC** | no native conversation-definition lifecycle claim |
| `chat.creation.policy` | **WPE POLICY** | users/session/capabilities are inputs only; no native duplicate-prevention/invite engine |
| `chat.participant.policy` | **WPE PRODUCT/POLICY** | user/site membership can be referenced; participant derivation/invites/leaves remain Chat-owned |
| `chat.state.lifecycle` | **WPE PRODUCT SEMANTIC** | no native active/closed/archived/moderation-hold conversation state machine |
| `chat.message.format` | **WPE SAFETY/RENDER CONTRACT** | WordPress sanitization utilities are substrate only; no native chat message format model |
| `chat.message.mentions-attachments` | **WPE + CROSS-OWNER REFERENCES** | mentions remain authorization-filtered; attachments stay Media/File-owned refs |
| `chat.message.edit-delete` | **WPE PRODUCT SEMANTIC** | no native private-message edit/delete/redaction window contract |
| `chat.message.reply-reactions` | **WPE/MARKET-ONLY** | no native chat reply/reaction engine |
| `chat.schema.reliability` | **WPE STORAGE/RELIABILITY** | REST pagination/idempotent request patterns are substrate; message schema/concurrency remains Chat-owned |
| `chat.read.state` | **WPE PRODUCT SEMANTIC** | no native unread cursor/read-receipt model |
| `chat.presence.typing` | **WPE/PROVIDER SEMANTIC** | Heartbeat may carry ephemeral polling data but does not define presence/typing privacy truth |
| `chat.transport.profile` | **NATIVE-COMPATIBLE SUBSTRATE + PROVIDER BOUNDARY** | document REST/AJAX/Heartbeat polling adapters as bounded transport options; realtime WebSocket/SSE remains provider-specific and must degrade honestly |
| `chat.notification.policy` | **SURFACE 19 REFERENCE** | Notifications owns occurrence/routing/mute/digest semantics; Chat stores references only |
| `chat.moderation.safety` | **WPE PRODUCT/POLICY** | native capability checks are authorization inputs, not a moderation queue/product engine |
| `chat.attachment.authorization` | **MEDIA/FILE + POLICY CROSS-OWNER** | private attachment access must re-check conversation/resource authorization; public Media URLs are insufficient protection |
| `chat.search-privacy-portability` | **WPE + CROSS-OWNER** | privacy exporter/eraser hooks are native integration substrate; search/index/import-export remain delegated where applicable |
| `chat.safety.raw-html` | **REJECTED-UNSAFE CONFIRMED** | keep `REJECT`; arbitrary HTML/script in private message bodies remains forbidden |

## Required Supervisor Bank integration

1. Populate `snapshot.native_sources` with Roles/Capabilities, REST, AJAX, Heartbeat, Comments, Media, privacy and WP-Cron substrate sources.
2. Enrich `chat.transport.profile` to distinguish native-compatible REST/AJAX/Heartbeat polling from provider realtime transports; no transport may be labelled realtime unless the selected provider actually supplies that property.
3. Add notes to `conversation.definition`/message records that WordPress comments are explicitly **not** the canonical private-chat storage model.
4. Enrich `attachment.authorization`, `notification.policy` and `search-privacy-portability` with their canonical-owner references.
5. Keep all conversation/message/read/presence/moderation semantics Chat/WPE-owned rather than promoting adjacent WordPress primitives as a native Chat product.
6. Preserve `chat.safety.raw-html` as `REJECTED_UNSAFE / REJECT`.

## Native completeness / unresolved items

No additional Chat-specific V1 Bank family is required. Native WordPress contributes identity/authorization, invocation/polling, content/media/privacy and scheduling substrates only. A future market audit must cover actual private/group/support messaging semantics and provider transport breadth.

Actual message persistence, participant mutation, moderation actions, private attachment delivery, notifications, search and realtime transport remain later runtime/provider gates.

## Gate boundary

Worker conclusion: **native evidence complete; Bank provenance/boundary integration pending**.  
Not promoted here: `NATIVE_AUDITED`, market review, `BANK_REVIEWED`, Chat runtime, Atomic Option Contract, UX or product parity.