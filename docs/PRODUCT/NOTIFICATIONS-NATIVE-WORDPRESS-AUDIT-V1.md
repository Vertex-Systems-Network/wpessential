# Notifications — Native WordPress Audit V1

Surface: **19 / Notifications**  
Issue: **#695**  
Parent: **#686**  
Exact-main audit anchor: `3839142cc8a225f443fcf9f6e0c26eb9641742e7`  
Seed: `config/product/options-bank/notifications.json` — **16 records / BANK_SURFACE_SEEDED**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE; WORDPRESS HAS NOTICE/EVENT/MAIL/SCHEDULING PRIMITIVES BUT NO PERSISTENT NOTIFICATION-RULE/INBOX ENGINE.** No delivery occurs and no lifecycle promotion is made here.

## Current native sources

- `admin_notices` — https://developer.wordpress.org/reference/hooks/admin_notices/
- `all_admin_notices` — https://developer.wordpress.org/reference/hooks/all_admin_notices/
- `network_admin_notices` — https://developer.wordpress.org/reference/hooks/network_admin_notices/
- `user_admin_notices` — https://developer.wordpress.org/reference/hooks/user_admin_notices/
- Roles & Capabilities — https://developer.wordpress.org/apis/security/user-roles-and-capabilities/
- WP-Cron — https://developer.wordpress.org/plugins/cron/
- `wp_mail()` is an Email transport/output primitive owned by Surface 20 — https://developer.wordpress.org/reference/functions/wp_mail/
- accepted readiness evidence: `docs/PRODUCT/NOTIFICATIONS-BANK-ENTRY-READINESS-V1.md`

Core admin notices are screen-rendered notices, not durable per-user Notification Instances. WordPress lifecycle hooks can be wrapped by reviewed typed Event adapters, but arbitrary user-entered hook names/callbacks remain unsafe.

## Seed-record disposition

| Seed record | Native disposition | Integration action |
|---|---|---|
| `notifications.rule.identity` | **WPE PRODUCT SEMANTIC** | no native rule-definition engine claim |
| `notifications.trigger.binding` | **NATIVE EVENT SUBSTRATE + WPE TYPED-ADAPTER CONTRACT** | registered typed adapters may wrap core lifecycle hooks; never expose arbitrary hooks |
| `notifications.condition.reference` | **WPE/SHARED CONDITION OWNER** | no native condition engine |
| `notifications.recipient.policy` | **WPE POLICY** | users/roles are native inputs; recipient-resolution semantics remain Notifications-owned |
| `notifications.recipient.references` | **CROSS-OWNER REFERENCES** | Query/Roles/Membership truth consumed read-only |
| `notifications.eligibility.policy` | **WPE POLICY + NATIVE AUTH SUBSTRATE** | capabilities/session are inputs; preferences/quiet hours are not native core |
| `notifications.priority.class` | **WPE/MARKET-ONLY** | no native notification priority/requiredness model |
| `notifications.channel.routing` | **NATIVE-PARTIAL + CROSS-OWNER** | admin notice contexts are native presentation; Email/Webhook routing belongs to their owners |
| `notifications.content.tokens` | **WPE CONTENT/TOKEN CONTRACT** | no native persistent notification template/token engine |
| `notifications.schedule.policy` | **NATIVE CRON SUBSTRATE / CRON OWNER** | delay/date scheduling references Cron/Job owner; no scheduling execution here |
| `notifications.dedupe.frequency` | **WPE RELIABILITY** | no native dedupe/frequency-cap engine |
| `notifications.digest.policy` | **WPE/MARKET-ONLY** | no native digest engine |
| `notifications.instance.state` | **WPE PRODUCT SEMANTIC** | no native durable inbox/read/dismiss state |
| `notifications.delivery.reliability` | **WPE/CROSS-OWNER RELIABILITY** | provider acceptance/delivery evidence remains channel-provider specific |
| `notifications.preference.reliability` | **WPE PRODUCT/PRIVACY** | user meta/options may store bounded values but do not define a native notification-preference engine |
| `notifications.safety.raw-hook` | **REJECTED-UNSAFE CONFIRMED** | keep `REJECT`; raw hook/callback configuration remains forbidden |

## Required Supervisor Bank integration

1. Populate `snapshot.native_sources` with admin/user/network notice hooks plus capability/WP-Cron references.
2. Enrich `trigger.binding` with the **registered typed native Event adapter** boundary.
3. Enrich `channel.routing` to distinguish ephemeral admin/user/network notices from durable Notification Instances and from Email/Webhook transports.
4. Enrich `schedule.policy` as a reference to Cron/Job execution rather than Notifications-owned scheduling.
5. Preserve all rule/inbox/preferences/digest/dedupe/delivery state as WPE/market semantics.
6. Keep `safety.raw-hook` rejected.

## Native completeness / unresolved items

No additional V1 Bank family is required. Native WordPress contributes event, admin-notice, identity/capability and scheduling substrates only; it does not supply a persistent notification product. Multisite context is covered through network/user admin notice separation.

External dispatch, recipient fan-out, durable inbox writes, preference mutation and retries remain runtime/provider gates.

## Gate boundary

Worker conclusion: **native evidence complete; Bank provenance/boundary integration pending**.  
Not promoted here: `NATIVE_AUDITED`, market review, `BANK_REVIEWED`, dispatch runtime, Atomic Option Contract, UX or product parity.