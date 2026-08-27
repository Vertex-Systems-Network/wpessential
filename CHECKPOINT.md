# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-28**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**  
Production development authorization: **NOT GRANTED**

## Hard consent gate

Explicit owner consent is required before runtime/source/build/migration/test implementation, dependencies, executable spikes/benchmarks, queue execution, provider/API interactions, service transmission, SMTP/email sends, Backup/Restore operations or release packaging.

`continue` and planning acceptance do **not** authorize development.

Source of truth: `/DEVELOPMENT-CONSENT.md`, `AGENTS.md`, ADR-0014.

## Product milestone

- **31/31 Exhaustive product-option maturity**
- **31/31 Multisite scope behavior mapped**
- **0/31 Authorized**
- **0 surfaces Multisite runtime-certified at MS1+**
- Implemented: none
- Runtime verified: none

## Accepted architecture

Accepted decisions now extend through **ADR-0085**.

Latest milestones:
- ADR-0081 — Audit AU1/PT-D retention/index/integrity profile.
- ADR-0082 — Workflow WF1/PT-D first P-011 benchmark; WF2/PT-E mandatory comparison.
- ADR-0083 — JobService J1/J2/J3 PT-C/PT-D physical mapping for future P-003.
- ADR-0084 — Backup Remote Copy BR1/BR2/BR3 physical comparison for P-013.
- **ADR-0085 — Vault V1/PT-C physical envelope profile; V2 Multisite comparison.**

Earlier active physical baselines include Definition D1, Relations R1, Forms FRT1, Chat CRT1, Membership M1, Notification/Email NE1 and Event Inbox EI1.

## Physical benchmark map

- Definition: D1/PT-C first; D2/D3/D4 comparisons — **0 executed**.
- Relations: R1/PT-D first; R2/PT-E mandatory; R3 exceptional — **0 executed**.
- Forms: FRT1/PT-D first; FRT2/PT-E mandatory — **0 executed**.
- Chat: CRT1/PT-D first; CRT2/PT-E mandatory — **0 executed**.
- Membership: M1/PT-D first; M2/PT-E mandatory — **0 executed**.
- Notification/Email: NE1/PT-D first; NE2/PT-E mandatory — **0 executed**.
- Event Inbox: EI1/PT-D first; EI2/PT-E mandatory — **0 executed**.
- Audit: AU1/PT-D favored — **0 executed**.
- Workflow: WF1/PT-D first; WF2/PT-E mandatory — **0 executed**.
- JobService: J1/PT-D Jobs+Attempts first; J2 split mandatory; J3 PT-C low-volume control — **0 executed**.
- Backup Remote Copy: BR1/PT-D first; BR2 split mandatory; BR3/PT-E comparison — **0 executed**.
- Vault: V1/PT-C favored first; V2 per-site + separate network Vault mandatory — **0 executed**.

No DDL, migration, table, index, cryptographic fixture or database benchmark has been executed.

## Workflow — ADR-0082

Workflow Runtime remains the durable source of Run/Step/Wait/Approval truth; JobService is execution opportunity only.

Future P-011 correctness gates include:
- duplicate trigger/Job cannot duplicate logical side effect under valid node contract;
- enqueue failure after committed Workflow state is reconcilable;
- waits cannot double-resume under at-least-once events;
- concurrent approval/join cannot commit twice;
- unknown external outcome is reconciled rather than blindly retried;
- cancellation/compensation remain explicit states;
- Restore does not replay terminal Runs or reinterpret pinned revision.

**P-011 executed: 0.**

## JobService — ADR-0083

WPE-owned Job/Attempt history remains independent from Action Scheduler/backend tables.

Profiles:
- J1 PT-D Jobs+Attempts;
- J2 PT-C current Job + PT-D Attempts/history;
- J3 PT-C all as low-volume control.

Backend status cannot fabricate WPE success; stale lease never proves no side effect; Action Scheduler `unique` is not business idempotency; Restore does not blindly reactivate copied backend rows.

**Action Scheduler 4.1.0 remains reviewed candidate only. P-003 executed: 0.**

## Backup Remote Copy — ADR-0084

Remote Copy commit, verification and deletion remain separate truth states.

Profiles:
- BR1 PT-D shared scoped Backup runtime metadata;
- BR2 PT-C current Backup/Copy + PT-D parts/objects/attempts;
- BR3 PT-E per-site comparison where large-network isolation evidence requires.

Critical gates:
- `commit_unknown` is reconciled;
- manifest-last remains required;
- provider object identity/path cannot bypass WPE manifest/scope/integrity validation;
- provider delete success maps truthfully to trash/version/lock/delete-confirmed semantics;
- prune cannot remove only known-good recovery point due to newer unverified backup;
- Restore validates actual manifest/object/hash/crypto/target scope.

**Backup targets: 34 / 0 C-certified / 0 C3 Supported. P-013 executed: 0.**

## Vault — ADR-0085

V1/PT-C is the favored first physical profile. V2 per-site Vault + separate network Vault is mandatory Multisite security/operations comparison.

V1 separates:
- Secret Identity/current metadata;
- immutable encrypted Secret Versions;
- VRK Generations;
- VRK Key Slots;
- explicit network-secret Use Grants/Bindings.

Critical gates:
- each VRK belongs to explicit site/network Vault Security Domain;
- no plaintext secret or wrapping key in DB;
- AAD binds secret identity/version/scope/purpose/key generation;
- row/ciphertext swap across context must fail authentication;
- missing/wrong key fails closed and preserves ciphertext;
- network secret use requires explicit grant + current target-site Policy/Connection authorization;
- clone/staging does not auto-activate production integrations;
- normal Backup does not package the only external/recovery wrapping key plaintext beside ciphertext;
- full PHP/server compromise remains outside DB-only secrecy claim.

**P-005 crypto/physical evidence: 0. Independent security review not yet executed.**

## Existing provider/runtime state

- Membership Billing: **4 BE3 / 0 MB-certified**.
- Email: **6 EE3 / 0 ET-certified**.
- Event adapters: **0 I4/I5 certified**.
- Backup: **34 / 0 C-certified**.
- Remote privacy: **30 fixtures / 0 executed**.
- Product License API/service: **0 fixtures**.
- Site Lifecycle: **40 fixtures / 0 executed**.
- Multisite: **0 MS1+**.

## Platform evidence blockers

P-001 compatibility/Multisite; P-002 UI; P-003 Job; P-004 Definition; P-005 Vault; P-006 Free↔Pro/Product License; P-007 CI; P-008 build; P-009 Query; P-010 Relations; P-011 Workflow; P-012 Membership; P-013 Backup remain executable blockers.

## Verification state

Verified planning/documentation only:
- branch remains `planning/master-architecture`;
- **31/31 Exhaustive / 0/31 Authorized**;
- **31/31 Multisite scopes mapped / 0 MS1+**;
- governance synchronized through **ADR-0085**;
- Workflow/Job/Backup/Vault physical paper profiles committed;
- all newly described executable evidence remains zero;
- no implementation/build/test/provider-certification success claimed.

Not performed: dependency installation, Multisite runtime setup, lifecycle hooks, Action Scheduler bootstrap, PHP/React source, DB tables/migrations/indexes, queue execution, crypto/KDF/key generation, provider/API/webhook/SMTP calls, commerce transactions, Email sends, Backup transfer/Restore/prune, PHPUnit/Playwright, DB/performance benchmarks, release/deployment.

## Next allowed planning-only priorities

1. Query P-009 storage-adapter/cost/cache/security benchmark profile without execution.
2. Field Storage + Custom Tables PT-D/PT-E physical/migration profiles without DDL.
3. Settings PT-A/PT-B inheritance/autoload/concurrency profile.
4. Membership protected-file delivery topology/evidence protocol.
5. Product License exact OpenAPI component schema refinement only where static review reduces ambiguity.
6. Keep P-001…P-013 executable gates intact.
7. Keep governance/Draft PR synchronized.

Before any executable work, explicit owner consent is required.

## Resume order
1. `/DEVELOPMENT-CONSENT.md`
2. `AGENTS.md`
3. `CHECKPOINT.md`
4. `docs/MODULES/OPTION-COVERAGE-MATURITY.md`
5. `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`
6. `docs/IMPLEMENTATION-READINESS-MATRIX.md`
7. `docs/OPEN-DECISIONS-REGISTER.md`
8. `docs/DECISIONS/README.md`
9. relevant architecture/security/module/provider docs

Repository evidence overrides conversational memory.