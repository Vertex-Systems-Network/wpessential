# WPEssential — Multisite Scope & Isolation Evidence Protocol

Status: **Phase 0 future evidence plan / NOT AUTHORIZED FOR EXECUTION**  
Date: 2026-08-28  
Related: ADR-0014, ADR-0069, `MULTISITE-SCOPE-OWNERSHIP-MODEL.md`, `MULTISITE-SCOPE-OPTION-MATRIX.md`, P-001/P-003/P-004/P-012/P-013.

## 1. Goal

Define the minimum evidence required before WPEssential can claim WordPress Multisite support for a module/platform surface.

Passing plugin activation or rendering one Network Admin page is not certification.

This protocol verifies:
- scope identity;
- authorization isolation;
- site/network inheritance;
- context switching;
- caches;
- jobs;
- lifecycle;
- shared users/roles;
- Vault usage;
- provider integrations;
- Membership;
- Backup/Restore;
- destructive operations;
- scale/failure recovery.

No fixture in this document has been executed.

---

# 2. Evidence levels

## MS0 — Static Compatible
Proves only:
- module is designed with explicit scope;
- no known single-site-only architectural contradiction;
- required Multisite APIs/contracts are documented.

No runtime claim.

## MS1 — Activation & Site Isolation
Proves:
- network/per-site activation lifecycle;
- site-specific definitions/settings/data stay isolated;
- site admin cannot read/mutate another site through obvious ID changes;
- Network Admin routes authorize correctly.

## MS2 — Scope Runtime Certified
Adds:
- blog switching/restore safety;
- scope-aware caches;
- jobs/cron/workflows target correct site;
- network defaults/site overrides/locks;
- site creation/archive/delete handling;
- shared-user/site-role semantics.

## MS3 — Cross-Site / Network Operations Certified
Adds only for modules that intentionally support network scope:
- bounded fan-out;
- target-site selection;
- network templates/propagation;
- cross-site queries/relations where explicitly supported;
- network connections/shared secrets;
- partial failures and retry/recovery.

A module with no network-operation product requirement can remain fully supported at MS2 for its documented scope.

## MS4 — Large-Network & Disaster Certified
Adds:
- large synthetic network scale;
- Network Backup/Restore where applicable;
- upgrade/version-skew lifecycle;
- disaster recovery and resume;
- long-running fan-out/backpressure;
- operational diagnostics.

MS4 is required before strongest network-scale/disaster marketing claims.

---

# 3. Standard test topology

Future executable matrix must include at least:

### Topology A — Single site control
- normal WordPress single-site;
- proves Multisite abstractions do not break single-site behavior.

### Topology B — Multisite subdirectory
- primary site + at least 4 child sites;
- mixed site admins/members;
- network-active WPE.

### Topology C — Multisite subdomain
- equivalent functional fixtures;
- route/domain/cookie/link generation checked.

### Topology D — large synthetic network
Candidate staged sizes:
- 100 sites;
- 1,000 sites;
- 10,000 sites where environment permits.

Large-network thresholds become evidence-based, not promises in advance.

---

# 4. Actor matrix

Create actors with deliberately asymmetric authority:

- Super Admin A;
- Network-capable WPE admin B without arbitrary bypass;
- Site A Administrator only;
- Site B Administrator only;
- user who belongs to A + B with different roles;
- network user who belongs to no target site;
- subscriber/member on one site only;
- anonymous visitor.

Every sensitive fixture records actor + original site + target scope.

---

# 5. Core scope identity fixtures

### MS-SCOPE-001 — Explicit site ownership
Create same module definition label/key on Site A and Site B.

PASS:
- records have distinct explicit site ownership;
- reads/updates/deletes target only intended site;
- cache/audit IDs include correct scope.

### MS-SCOPE-002 — Network resource identity
Create a network-scoped resource where supported.

PASS:
- exactly one network owner;
- site UI sees only allowed inherited/use state;
- no accidental physical duplication represented as independent authority.

### MS-SCOPE-003 — Current-blog independence
Enqueue/read a site-owned object while request originates from another site/network admin.

PASS:
- durable owner remains target site;
- request context cannot rewrite ownership.

### MS-SCOPE-004 — Invalid scope coordinates
Submit missing/wrong network/site combinations.

PASS:
- validation fails safely;
- no fallback to current site.

---

# 6. Authorization / IDOR fixtures

### MS-AUTH-001
Site A admin requests Site B WPE object UUID directly.

PASS: 403/denied; no existence-sensitive leakage beyond product policy.

### MS-AUTH-002
Site admin alters `site_id`/network ID in REST/Ability request.

PASS: target-site capability + WPE Policy deny unauthorized scope.

### MS-AUTH-003
Network Admin screen is accessed by ordinary site admin.

PASS: denied server-side even if route/menu URL is known.

### MS-AUTH-004
Super Admin performs high-risk action.

PASS:
- dedicated WPE capability/Policy still evaluated;
- impact confirmation/audit remains active;
- no implicit policy bypass.

### MS-AUTH-005
Shared user has different roles on A and B.

PASS: effective permissions differ by target site as designed.

---

# 7. Context-switching fixtures

### MS-SWITCH-001 — balanced switch
Switch A → B → restore.

PASS: original blog/cache context restored.

### MS-SWITCH-002 — nested switch
A → B → C → restore → restore.

PASS: stack order correct.

### MS-SWITCH-003 — exception/failure
Throw/fail inside target-site operation.

PASS: restoration still occurs; boundary diagnostics detect dirty context.

### MS-SWITCH-004 — plugin-code assumption
Target site lacks a per-site plugin/theme integration expected elsewhere.

PASS: WPE does not assume `switch_to_blog()` loads unavailable code.

### MS-SWITCH-005 — worker reuse
Multiple site jobs execute sequentially in same worker/process where supported.

PASS: no context leakage from first job to second.

---

# 8. Settings/inheritance fixtures

### MS-SET-001
Network default with no site override.

PASS: effective site value derives from network and UI shows provenance.

### MS-SET-002
Allowed site override.

PASS: override wins only for target site.

### MS-SET-003
Network locked value.

PASS: site admin cannot mutate via UI, REST, Ability or direct supported action path.

### MS-SET-004
Remove site override.

PASS: site returns to inherited state; network value is not copied as local ownership.

### MS-SET-005
Change network default.

PASS: only inheriting sites change; explicit overrides preserved.

---

# 9. Definition/template fixtures

### MS-DEF-001 — site definitions isolated
Same CPT/Field/Query/Listing definition concept on A/B.

PASS: revisions/dependencies never cross accidentally.

### MS-DEF-002 — network template instantiate
Instantiate into selected sites.

PASS: target copies/reference semantics match selected mode and audit child events exist.

### MS-DEF-003 — linked/pinned revision
Network definition publishes v2 while a site pins v1.

PASS: pinned site remains v1; follow-current site moves only according to contract.

### MS-DEF-004 — draft safety
Network draft change exists.

PASS: live target sites unaffected.

### MS-DEF-005 — target conflict
One site has slug/field/key conflict during rollout.

PASS: conflict is isolated/reported; other sites do not become corrupt.

---

# 10. Cache fixtures

### MS-CACHE-001
Cache allow result on Site A then request equivalent resource on Site B.

PASS: no cross-site authorization reuse.

### MS-CACHE-002
Switch sites repeatedly with object cache enabled.

PASS: resolved definitions/settings/query output remain correct.

### MS-CACHE-003
Network policy changes.

PASS: affected site cache generations invalidate within specified contract.

### MS-CACHE-004
Membership revoke on A.

PASS: A deny latency meets future P-012 threshold; B unaffected unless same network entitlement explicitly applies.

---

# 11. JobService / Cron / Workflow fixtures

### MS-JOB-001
Site A job is enqueued from Network Admin/current Site B.

PASS: target scope is A, not enqueue context.

### MS-JOB-002
Site is archived/deleted before job runs.

PASS: job follows typed skip/cancel/failure policy; no wrong-site fallback.

### MS-JOB-003
Network coordinator fans out 100+ site child jobs.

PASS:
- bounded pagination;
- backpressure;
- checkpointing;
- no unbounded one-request enqueue;
- child site IDs explicit.

### MS-JOB-004
One target site fails repeatedly.

PASS: other sites complete; aggregate outcome truthful; retry isolated.

### MS-JOB-005
Mixed urgency jobs across multiple sites.

PASS: no sustained starvation caused by one noisy site according to P-003 fairness contract.

---

# 12. User/Profile/Role fixtures

### MS-USER-001
Change site-specific profile field.

PASS: only target-site value/layout affected.

### MS-USER-002
Change global email/password/session from site UI.

PASS: UI clearly marks network-wide identity impact and protected identity flow is used.

### MS-ROLE-001
Modify role on Site A.

PASS: Site B role/capability state unchanged.

### MS-ROLE-002
Attempt to remove last recovery-capable authority.

PASS: anti-lockout invariant blocks unsafe change.

### MS-ROLE-003
Super Admin/network authority mutation.

PASS: separate high-risk ability and audit trail.

---

# 13. Vault / shared connection fixtures

### MS-VAULT-001
Site-private secret A referenced from Site B.

PASS: denied.

### MS-VAULT-002
Network-shared connection delegated to A and B.

PASS: sites can use allowed operation without seeing plaintext credential.

### MS-VAULT-003
Revoke Site B use-right.

PASS: A remains usable; shared credential not deleted.

### MS-VAULT-004
Site export/clone.

PASS: network secret plaintext never copied; placeholders/references handled according to policy.

---

# 14. Query / Relations / Listings fixtures

### MS-QUERY-001
Normal site Query attempts arbitrary other-site source.

PASS: blocked.

### MS-QUERY-002
Authorized network aggregate across selected sites.

PASS: bounded sites, per-site Policy, deterministic merge/pagination contract, no unauthorized rows.

### MS-REL-001
Create cross-site relation using normal relation type.

PASS: blocked by default.

### MS-REL-002
Future explicit cross-site relation profile.

PASS only after both endpoint policies, orphan/site-delete and reverse-query behavior are certified.

### MS-LIST-001
Same listing cache key inputs across A/B.

PASS: rendered data remains site-isolated.

---

# 15. Membership fixtures

### MS-MEM-001
User has active Plan on Site A only.

PASS: protected Site B resource denied.

### MS-MEM-002
Site role sync from A Membership.

PASS: only A role changes.

### MS-MEM-003
Billing connection is network-shared.

PASS: source grant maps to correct site Plan/Enrollment; no network-wide grant by provider status alone.

### MS-MEM-004
Site removed/archive.

PASS: Enrollment/Entitlement handling follows explicit lifecycle; shared user account not blindly deleted.

### MS-MEM-005
Network Membership future profile.

PASS requires separate P-012 network profile; not covered by ordinary site Membership certification.

---

# 16. REST / Abilities / Webhooks fixtures

### MS-REST-001
Site REST endpoint receives another site's ID.

PASS: cannot escalate.

### MS-REST-002
Network endpoint invoked by site admin.

PASS: denied.

### MS-ABILITY-001
AI/CLI invokes cross-site destructive ability.

PASS: no special bypass; policy/AI-disable rules hold.

### MS-WEBHOOK-001
Provider event correlates to Site A delivery/source record.

PASS: event cannot mutate Site B similarly named object.

---

# 17. Backup / Restore fixtures

### MS-BACKUP-001 — site backup
Manifest includes exactly target-site artifacts plus declared shared references.

### MS-BACKUP-002 — selected sites
Selected-site Backup records explicit site identities and partial-failure truth.

### MS-BACKUP-003 — network backup
Includes network/global tables/resources/users according to profile plus per-site artifacts.

### MS-RESTORE-001 — same-site restore
Restores site without corrupting siblings.

### MS-RESTORE-002 — site-to-new-site remap
Site IDs/domains/paths/UUID mappings are explicit.

### MS-RESTORE-003 — full-network disaster
Required for strongest MS4/P-013 network restore claim.

No Multisite Backup profile can inherit C3/C4 from single-site provider restore evidence alone.

---

# 18. Reset / Import / Uninstall fixtures

### MS-RESET-001
Site Reset.

PASS: shared users/network options/other sites unaffected unless explicitly selected by supported contract.

### MS-RESET-002
Network Reset.

PASS: highest-impact authorization + verified recovery point + journal + target inventory.

### MS-IMPORT-001
Site package imported to target site.

PASS: cannot overwrite network resources without separate network authorization.

### MS-IMPORT-002
Network package with site mappings.

PASS: dry-run exposes all scope remaps/conflicts before mutation.

### MS-UNINSTALL-001
Per-site disable vs network deactivate vs uninstall.

PASS: lifecycle/data-retention semantics remain distinct and other-site data is not accidentally deleted.

---

# 19. Site lifecycle fixtures

### MS-LIFE-001 — new site
Network defaults/templates apply only according to configured new-site policy.

### MS-LIFE-002 — archive/spam/deactivate
Jobs/access/integrations react according to typed policy.

### MS-LIFE-003 — delete site
WPE site-owned state cleanup/retention plan executes without deleting unrelated network/site state.

### MS-LIFE-004 — site ID/domain changes
Stable WPE UUID ownership survives expected WordPress mapping changes where supported.

---

# 20. Large network / performance fixtures

Future tests record:
- interactive request query count;
- network list pagination;
- coordinator memory/runtime;
- queue growth;
- worker concurrency;
- cache invalidation cost;
- per-site failure distribution;
- audit volume;
- Backup manifest/fan-out size.

Hard rule: no claim such as `supports 10,000 sites` without executed environment + documented limits.

---

# 21. Failure injection

Required fault classes:
- DB failure on one child site;
- object-cache outage;
- worker crash after site switch;
- network option write conflict;
- stale/deleted site ID;
- provider timeout for one site;
- Vault unavailable/locked;
- Free/Pro version mismatch;
- network definition rollout conflict;
- restore interruption;
- permission change during long-running coordinator.

Outcomes must be recoverable/diagnosable and never silently switch target scope.

---

# 22. Evidence artifact per run

Each future Multisite certification run records:
- WPE version/commit;
- WordPress/PHP/DB versions;
- Multisite topology;
- object cache state;
- module/provider versions;
- actor/site/network fixtures;
- executed fixture IDs;
- pass/fail/skipped;
- logs/audit IDs;
- performance observations;
- known limitations;
- certification level MS0–MS4 per surface.

Certification is version/profile scoped and can expire/downgrade after incompatible changes.

---

# 23. Current state

- protocol documented;
- **31/31 product surfaces already have Multisite scope mapping**;
- **0 Multisite runtime fixtures executed**;
- **0 surfaces runtime-certified at MS1+**;
- development authorization remains **NOT GRANTED**.

## Development gate

Do not create networks, install dependencies, activate plugins, run queues, create schemas, execute REST/Ability/provider calls, perform Backup/Restore/Reset, or run performance tests under this protocol until explicit owner consent under ADR-0014.
