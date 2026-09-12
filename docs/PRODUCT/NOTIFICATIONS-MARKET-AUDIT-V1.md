# Notifications — Market Audit V1

Status: `RESEARCH_COMPLETE / SUPERVISOR_INTEGRATION_PENDING`

Surface: 19 — Notifications  
Snapshot: 2026-09-12  
Parent: #713  
Worker issue: #722  
Base: `54e4e70095f74a6ebff15da83cc2bbf3d447a49c`

## 1. Scope and boundary

This evidence-only audit reconciles all 16 current Notifications records. Notifications owns durable rule/occurrence/recipient/inbox semantics. Roles/Policy, Query, Membership, Cron, Emails and Webhook/provider transports remain canonical inputs/owners. Raw authored hooks/callbacks remain rejected.

## 2. Current market evidence

### E1 — Ultimate Member Real-time Notifications
Official evidence:
- https://docs.ultimatemember.com/article/91-real-time-notifications
- https://docs.ultimatemember.com/article/1561-web-notifications

Verified: notification event types, AJAX polling/check interval, notification page, all/unread states, mark read/remove actions, per-user notification preferences and Web Notifications controls.

### E2 — Ultimate Member Groups notifications
Official evidence:
- https://docs.ultimatemember.com/article/1668-groups-email-and-real-time-notifications

Verified current documentation: configurable real-time/email notification types, templates and user-facing notification behavior for group events.

### E3 — PublishPress Notifications / Planner
Official evidence:
- https://publishpress.com/knowledge-base/notifications/
- https://publishpress.com/knowledge-base/notification-workflows/

Verified: configurable notification rules around editorial/content events, recipient selection and customizable notification content/workflows. Provider guidance also warns against treating editorial notifications as a high-volume bulk-email engine.

## 3. Record reconciliation

| Record | Disposition |
|---|---|
| `notifications.rule.identity` | MARKET_EVIDENCED — E3 proves named/configurable notification rules/workflows. |
| `notifications.trigger.binding` | MARKET_EVIDENCED_WITH_TYPED_ADAPTER_BOUNDARY — event triggers are common; WPE accepts registered event adapters only. |
| `notifications.condition.reference` | MARKET_EVIDENCED_WITH_OWNER_BOUNDARY — conditional rule behavior is common; reusable condition engine remains canonical/delegated. |
| `notifications.recipient.policy` | MARKET_EVIDENCED — E3 recipient selection and E1 user notification behavior prove resolution family. |
| `notifications.recipient.references` | MARKET_EVIDENCED_WITH_OWNER_BOUNDARY — roles/users/groups can feed recipients; Query/Roles/Membership remain read-only sources. |
| `notifications.eligibility.policy` | MARKET_EVIDENCED / WPE_HARDENED — E1 user preferences exist; authorization/quiet-hours eligibility stays explicit. |
| `notifications.priority.class` | KEEP_WPE_HARD — priority/requiredness needs a portable canonical model and cannot be inferred from provider labels. |
| `notifications.channel.routing` | MARKET_EVIDENCED_WITH_TRANSPORT_OWNERS — E1/E2 show in-app/web/email; Email/Webhook execution remains delegated. |
| `notifications.content.tokens` | MARKET_EVIDENCED / WPE_HARDENED — templates/content exist; WPE allows typed/allowlisted tokens only. |
| `notifications.schedule.policy` | PARTIAL_MARKET / CRON_OWNER — delayed/scheduled workflows are market patterns; execution stays Cron-owned. |
| `notifications.dedupe.frequency` | KEEP_WPE_EXCEED — generic dedupe/frequency-cap guarantees are not established by reviewed provider evidence. |
| `notifications.digest.policy` | KEEP_WPE_EXCEED — digest/grouping/overflow needs explicit semantics; not inferred from ordinary notifications. |
| `notifications.instance.state` | MARKET_EVIDENCED — E1 all/unread/read/remove states prove durable user-instance family. |
| `notifications.delivery.reliability` | KEEP_WPE_HARD / PROVIDER_VARIANT — retries/idempotency/delivery evidence differ by channel/provider. |
| `notifications.preference.reliability` | MARKET_EVIDENCED / WPE_HARDENED — E1 per-user preferences exist; retention/health/fan-out limits stay explicit. |
| `notifications.safety.raw-hook` | KEEP `REJECTED_UNSAFE` — provider hook extensibility is not evidence to store arbitrary executable hooks/callbacks. |

Coverage: **16 / 16**. Unresolved research dispositions: **0**. Worker Bank/progress mutations: **0**.

## 4. WPE-exceed / safety

- Recipient expansion must be bounded, authorization-aware and observable before fan-out.
- In-app read state is WPE-owned durable state; email/webhook acceptance does not automatically equal end-user delivery.
- Quiet hours, priority, dedupe, frequency caps and digest behavior require deterministic policies rather than provider guesswork.
- Raw hook names/callbacks remain rejected; only registered typed events/actions may bind rules.

## 5. Supervisor integration requirements

A later Supervisor may add E1–E3 market provenance and classify rule/recipient/channel/content/inbox/preference families while retaining explicit WPE reliability/dedupe/digest and canonical transport-owner boundaries. `MARKET_AUDITED` requires exact-head Bank integration and CI.

No external dispatch, recipient fan-out, inbox/preference mutation, `BANK_REVIEWED`, runtime/product certification, deployment or release is authorized.