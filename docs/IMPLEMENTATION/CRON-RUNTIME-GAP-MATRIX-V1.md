# Cron — Runtime Gap Matrix V1

Surface: **18 / Cron**  
Issue: **#501**  
Runtime authorization: **false**.

| Family | Planning state | Runtime gap | Evidence required before runtime promotion |
|---|---|---|---|
| Event inspector | Seeded | No canonical read-only hook+args event inventory | WordPress runtime + multisite + performance |
| Schedule identity | Seeded | No revisioned Schedule Definition / registered target resolver | unit + integration + portability |
| Timing | Seeded | No timezone-aware bounded recurrence/effective-next-run service | unit + WordPress runtime |
| Concurrency | Seeded | No lock/lease/overlap policy implementation | unit + integration + concurrency tests |
| Retry | Seeded | No idempotent retry/backoff/timeout/catch-up state machine | unit + recovery + security |
| Operations | Seeded | No privileged preview/confirmation execution service | security + integration + recovery |
| Provider health | Seeded | No redacted registered scheduler/provider health catalog | compatibility + security |
| Queue diagnostics | Seeded | No bounded attempt/log/stale-claim diagnostic store | performance + privacy + browser |

## Cross-cutting requirements

**Security:** registered Job/Ability/provider targets only; reject arbitrary PHP/callback/class/shell command input; server-authoritative capability/Policy checks for operations.  
**Reliability:** retries/catch-up require target idempotency classification; no duplicate side effects hidden behind automatic replay.  
**Multisite:** explicit site/network scope with no cross-site argument or diagnostic leakage.  
**Accessibility:** semantic controls/tables, keyboard support, visible focus, clear status/live-region announcements.  
**Compatibility:** WP-Cron remains best-effort/traffic-triggered; provider health must accurately represent system cron/CLI/other registered schedulers without claiming unavailable guarantees.  
**Performance:** bounded event/log listings, indexed lookup where persistence exists, rate-limited health/polling and isolated assets.  
**Portability:** portable schedule definitions contain stable target/provider IDs only; environment-specific providers require remapping.

Runtime scheduling/operations, destructive event mutation, deployment/release and certification claims remain forbidden until a later exact-main gate.