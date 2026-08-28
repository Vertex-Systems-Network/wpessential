# ADR-0200 — F06 Resource Scheduling & Reservation Detailed Executable Evidence Protocol

Status: **Accepted — planning/evidence only**  
Date: **2026-08-29**  
Work package: **WP68**  
Supersedes: none

## Context

ADR-0177 accepted F06 — Resource Scheduling, Availability & Reservation Engine as a reusable universal foundation. The universal technical evidence master plan reserved `RSV-001…RSV-176` as 16 groups × 11 fixtures, but those IDs remained group-level envelopes.

WP68 must freeze fixture-level evidence before implementation so scheduling correctness, timezone/DST semantics, capacity/overbooking safety, provider synchronization and reservation lifecycle cannot later be claimed from UI completeness or happy-path booking tests.

## Decision

Accept `docs/QUALITY/RESOURCE-SCHEDULING-RESERVATION-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical detailed evidence protocol for F06.

The protocol fully enumerates **RSV-001…RSV-176** across:

1. resource/calendar/capacity schemas;
2. timezone/DST/recurrence/blackout/holiday semantics;
3. availability, duration and buffers;
4. atomic hold/confirm/release/expiry concurrency;
5. capacity >1, shared pools and multi-resource requirements;
6. reschedule/cancel/no-show/extension lifecycle;
7. payment/approval external-prerequisite reconciliation;
8. overbooking prevention, crash safety and delayed-job handling;
9. waitlist, alternatives and priority policy;
10. resource permissions, private calendars and data minimization;
11. calendar/provider connector synchronization and conflicts;
12. cache invalidation and stale-availability defense;
13. Multisite/location/tenant isolation;
14. backup/restore/clone/site lifecycle continuity;
15. 10K/100K/1M-scale and hot-resource concurrency evidence;
16. deterministic appointment/rental/capacity/multi-resource/DST/provider/AI adversarial golden regressions.

## Architecture invariants

- Availability/search output is derived/advisory and is not a reservation.
- Final hold/confirmation must atomically revalidate current resource rules, ownership and capacity.
- Hold is not confirmed booking; waitlist position is not booking.
- F06 resource capacity/reservation is distinct from F05 ledger hold/balance semantics.
- Reservation state does not prove payment settlement, order completion, entitlement or external-calendar truth.
- Payment/provider success does not force reservation success when the slot/hold is no longer confirmable.
- Unknown external/provider outcome is not ordinary failure; reconcile before any replay that could duplicate effects.
- Canonical instants are unambiguous; local recurrence rules require explicit IANA timezone and deterministic DST gap/fold behavior.
- Recurrence is bounded and rule/blackout/holiday precedence is explicit.
- Shared pools and multi-resource bookings require certified aggregate/atomic or explicit compensating semantics; partial allocation cannot be labelled fully confirmed.
- Reschedule cannot blindly cancel a valid old slot before safely securing/reconciling the new one when atomic semantics are claimed.
- Private calendar/busy information follows Policy and data minimization; busy does not imply disclosure of event/customer details.
- External calendar events retain source/provider identity and cannot silently become native WPE reservation identity.
- Cached availability can improve reads but never bypass final booking authorization/capacity checks.
- Multisite ownership is durable and server-resolved; callers cannot widen authority with site/resource IDs.
- Backup/restore/clone cannot roll back external providers; cloned/stale mappings are quarantined/reconciled before writes.
- AI/MCP may draft schedules or alternatives but receives the same Policy, current-state, approval and atomic capacity gates as human-authored actions.

## Evidence truth

At acceptance:

- RSV documented: **176/176**;
- RSV executed: **0/176**;
- F06 runtime certification: **0**;
- implementation authorization: **not granted**;
- current product denominator remains **56/56 planned, 0/56 authorized**.

No resource table, recurrence evaluator, availability query, hold, reservation transaction, provider/calendar/payment request, queue/job, benchmark, AI/MCP call or database mutation was executed by this ADR.

## Consequences

WP68 is complete as a detailed planning/evidence package. Future implementation cannot claim F06 runtime readiness until the applicable RSV fixtures are executed with retained evidence under separate development authorization.

The universal evidence sequence may advance to **WP69 — F07 Placement & Personalization (`PLC-001…PLC-176`)** without changing the reserved meanings of WP69…WP74.

## Next safe planning action

Start WP69 by expanding the fixed PLC group envelope into a fixture-level executable-evidence protocol. This remains documentation/specification only until explicit scoped development consent is recorded.
