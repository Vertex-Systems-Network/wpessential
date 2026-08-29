# WPEssential — Work Coordination Ledger

Status: **Active implementation governance ledger**  
Last reviewed: **2026-08-29**

Current classification: `GREENFIELD_IMPLEMENTATION_WITH_EXISTING_ACCEPTED_PLAN`; execution **`IMPLEMENTATION_GATED`**; lifecycle **`IMPLEMENTING_PLATFORM_FOUNDATION`**; accepted scope **56/56**; source development approval **GOV-OWNER-CONSENT-001 ACTIVE / 56/56 milestone-gated**.

## Planning closure retained

- WP117 / ADR-0212 — final planning closure PASS.
- WP118 / ADR-0213 — Module/Option/UI/System structural-integrity audit PASS after remediation.
- Known planning/semantic-owner gap: **none known**.

## Implementation sequence

### WP119 — Implementation Baseline / Adoption Gate
**DONE / PASS / ADR-0214**.

### WP120 — Machine-enforced Architecture Guards
**DONE / PASS / ADR-0215**.

### WP121 — Milestone 1 Platform Foundation
**CURRENT / IMPLEMENTING through ADR-0222**.

Accepted shared foundation includes:
- Kernel / Modules / Service Registry;
- Definitions / Context / Policy / Abilities / Events;
- Audit foundation;
- Vault / Assets / Integrations;
- WordPress Capability/Abilities bridge;
- ADR-0216 engineering conventions;
- ADR-0217 atomic compiled registrations;
- ADR-0218 Definition + Audit MySQL persistence;
- ADR-0219 WordPress.org/release metadata + `ABSPATH` security;
- ADR-0220 real WordPress AJAX/nonce/Policy;
- **ADR-0221 Action Scheduler coexistence/backend profile**;
- **ADR-0222 durable Job persistence/attempt/lease/checkpoint foundation**.

## ADR-0221 — Action Scheduler coexistence/backend

Accepted tested profile:
- public `as_*` API adapter only;
- WPE dispatch hook `wpessential/hook_job_dispatch`;
- WPE group `wpessential-jobs`;
- backend args contain only Job UUID;
- exact hook/group/job-id query and cancellation;
- no third-party action mutation;
- Action Scheduler uniqueness is not WPE business idempotency.

Pinned WordPress 7.1 + MySQL 8.4 with Action Scheduler 3.9.3 and 4.1.0 simultaneously proves multiple-copy registration, 4.1.0 latest selection, shared store readiness, WPE action lifecycle isolation and third-party preservation.

Hosted run **33267115851 / #178 SUCCESS**.

Final public packaging mechanism and Multisite profile remain separately uncertified.

## ADR-0222 — Durable Job persistence, leases & checkpoints

Accepted WPE-owned durability model:
- migration `009.create-job-persistence`;
- network-prefixed Job + Attempt tables with explicit network/site scope;
- `PersistentJobService` using SHA-256 stable-key idempotency digest and revision CAS;
- durable payload/state/retry counters across service instances;
- serialized per-Job lease acquisition;
- monotonic attempt numbering;
- raw worker lease token never stored, SHA-256 hash stored instead;
- heartbeat extends valid leases;
- checkpoint sequence strictly increases;
- terminal completion requires valid unexpired lease;
- stale/expired workers cannot heartbeat or complete;
- bounded expired attempt reclaim to `abandoned` and fresh replacement attempts.

Hosted run **33267525349 / #209 SUCCESS** verifies the full prior suite plus real WordPress/MySQL durable Job persistence/lease semantics.

## Current non-certifications

Do not overclaim:
- WordPress.org submission/stable release not performed;
- live production DB migration/rollback not performed;
- final Action Scheduler distribution packaging pending;
- Multisite AJAX/queue switch/network-admin matrix pending;
- automatic AS dispatch → Ability → attempt lifecycle wiring pending;
- queue fairness/resource admission/high-concurrency evidence pending;
- Job checkpoint retention/privacy workflows pending;
- Audit UI/retention/privacy/export/legal-hold workflows pending;
- Runtime Observatory/admin shell pending;
- 10K/100K compiled-registration performance evidence pending;
- business modules remain downstream of foundation readiness.

## Next WP121 bounded sequence

1. **minimal Platform admin shell + Runtime Observatory diagnostics surface**;
2. executable 10K/100K compiled-registration scale evidence;
3. shared-foundation readiness gate;
4. first business-module tranche after that gate.

## Privileged exclusions

Current source-development approval does not authorize production deployment/release, destructive live-site/customer-data mutation, chargeable/irreversible provider operations, live payment/communication side effects, or destructive production reset/restore/migration/rescue.
