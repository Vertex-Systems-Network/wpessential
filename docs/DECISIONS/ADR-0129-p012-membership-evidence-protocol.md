# ADR-0129 — P-012 Membership Runtime / Access / Protected Files / Provider Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP12`

## Context

Membership semantics are already accepted across ADR-0013, ADR-0015, ADR-0016, ADR-0019 and ADR-0020: WordPress roles, Membership Enrollment, billing sources and Entitlements are separate domains; Enrollment owns canonical lifecycle; outer security denials cannot be bypassed; same-specificity deny wins; multiple valid memberships union normalized entitlements; Draft Plan edits do not alter live access; teams/seats remain Membership data; role synchronization is optional and provenance-safe.

ADR-0057/0062/0066 further establish that billing providers emit verified commercial source facts and require reconciliation before WPE Membership policy changes Enrollment/Entitlement truth. Current Manual/Woo Core/Woo Subscriptions/SureCart profiles are paper/static evidence only and do not grant MB certification.

ADR-0078 accepts M1/PT-D as the first physical benchmark baseline with M2/PT-E mandatory comparison. ADR-0090 accepts protected-file delivery profiles PD1/PD2/PD3 (and future PD4) plus PC0–PC4 certification semantics. A page/button restriction is not protected-file security if bytes remain reachable at origin.

The generic P-012 spike did not fix enough evidence around Plan revisions, lifecycle races, policy precedence, access-generation invalidation, teams/seats, role-sync provenance, billing reconciliation, protected-byte delivery, privacy, restore and Multisite physical scale.

## Decision

Accept `docs/QUALITY/P012-MEMBERSHIP-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the fixed executable evidence contract for P-012.

It defines **MBR-01…MBR-160** covering:

- Plan identity, immutable revisions, grandfather/follow-current/scheduled changes;
- canonical Enrollment lifecycle, idempotency, source ordering, terminal-state and time semantics;
- Entitlement derivation, specificity, deny-wins, overrides and explainability;
- cache/access-generation correctness and immediate revoke/expiry/hard-deny behavior;
- exclusive Plan Groups, upgrades, teams, seats, invitation security and concurrency;
- WordPress role-sync provenance, conservative removal and anti-escalation;
- Manual/WooCommerce Core/Woo Subscriptions/SureCart source facts, replay/order/reconciliation and MB certification boundaries;
- PD1/PD2/PD3 protected-file authorization, direct-origin bypass, Range/cache/signed-URL/migration/restore behavior and PC certification boundaries;
- privacy/export/erase/retention/user deletion/site lifecycle/Audit;
- M1/PT-D vs M2/PT-E physical, Multisite, query-plan, concurrency and large-scale evidence;
- independent adversarial security review.

## Preserved invariants

1. Role ≠ Membership ≠ billing source ≠ Entitlement ≠ WPE Product License.
2. Enrollment is authoritative lifecycle state; Entitlements are normalized derived/current grants.
3. Raw provider event/status never directly authorizes a protected request.
4. Outer WordPress/WPE security denial cannot be overridden by Membership.
5. Same-effective-specificity deny wins.
6. Cache is never authority; stale allow after committed revoke/hard deny is a security failure.
7. Timestamp expiry remains authoritative when Jobs/Cron are late.
8. Ordinary access hot path makes no provider API call.
9. Terminal Enrollment history is not resurrected by late ordinary provider events.
10. Team/seat capacity and exclusive Plan Groups cannot be violated by concurrency.
11. Role sync is optional, provenance-aware and cannot become Membership source of truth.
12. Claimed protected-file profiles must prevent unauthenticated origin-byte bypass within the certified configuration.
13. Provider BE3 static evidence is not MB certification.
14. One PC level/profile does not certify another storage/delivery environment.
15. Restore/clone requires authorization/reconciliation before stale access or production provider use can resume.

## Evidence state

- MBR fixtures documented: **160**
- MBR fixtures executed: **0/160**
- Membership runtime certifications: **0**
- M1/M2 physical benchmarks executed: **0**
- Membership billing provider profiles: **4 BE3 paper profiles / 0 MB-certified**
- Protected-file certifications: **0 PC1+**
- independent P-012 security review executed: **NO**

ADR-0078 remains a benchmark baseline, not final physical selection. ADR-0090 remains a delivery/security profile, not a PC certification.

## Stop-the-line examples

P-012 cannot certify if committed revoke/expiry/force-deny survives stale cache; provider state directly becomes access authority; outer security is bypassed; wrong-site/team IDOR succeeds; exclusive membership or final-seat race overbooks; role sync escalates privilege/removes ambiguous pre-existing role; direct origin bypass exposes a protected file; restore resurrects stale access; WPE Product License becomes Membership authorization; or secrets/card data leak through persistence/log/export/diagnostics.

## Development gate

This ADR authorizes no Membership runtime/schema/migration, cache, Enrollment/Entitlement/team/role mutation, provider integration, webhook/API call, protected-file move/download/token, privacy job, benchmark or test.

ADR-0014 explicit scoped owner consent remains required before every executable P-012 action.