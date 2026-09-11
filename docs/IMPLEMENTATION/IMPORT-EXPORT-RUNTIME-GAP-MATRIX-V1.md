# Import/Export — Runtime Gap Matrix V1

Surface: **26 / Import-Export**  
Planning issue: **#592**  
Supervisor wave: **#583**  
Exact-main claim anchor: `4350862f3f3397abdf64cc897abdb615faac4af5`

Status: **PLANNING GAP MATRIX / RUNTIME NOT AUTHORIZED**

Surface 26 remains `UNSEEDED / 0` in the Master Options Bank and exact main has no dedicated Import/Export runtime module. This matrix records prerequisites and owner boundaries only.

## Gap matrix

| Area | Exact-main state | Future requirement | Boundary / gate |
|---|---|---|---|
| Master Options Bank | **BLOCKED — UNSEEDED / 0** | Native/market reviewed normalized records. | Product gate before option contracts/runtime. |
| Atomic Option Contracts | **PLANNING INVENTORY ONLY** | Schema-valid contracts after Bank review. | No lifecycle promotion from inventory alone. |
| Config package runtime | **ABSENT** | manifest/checksum/schema/dependency/conflict/diff/import strategy. | Surface 26 orchestrates; owner definitions validate/mutate. |
| Data Import definitions | **ABSENT** | stable source/target/mode/mapping/revision lifecycle. | Surface 26. |
| Parsers/source adapters | **ABSENT** | safe CSV/JSON/XML/XLSX/Connection adapters with limits. | No code/SQL execution. |
| Source preview/profiling | **ABSENT** | bounded samples/type/duplicate/encoding diagnostics. | Read-only. |
| Target registry integration | **ABSENT** | canonical Data Source/owner schema and abilities. | No private-table direct writes. |
| Identity/upsert | **ABSENT** | stable match/composite keys, duplicate detection. | Destructive sync needs source ownership marker. |
| Mappings/null semantics | **ABSENT** | explicit typed field mappings and missing/empty/null policy. | No mass assignment. |
| Transform pipeline | **DEPENDENCY/ABSENT** | bounded typed transforms or Surface 45 refs. | No inline PHP/eval. |
| Taxonomy/relation/media/user mapping | **ABSENT** | owner APIs and Policy. | No serialized/meta bypass. |
| Dry run | **ABSENT** | create/update/skip/conflict/error/delete counts without target mutation. | Required before high-risk sync. |
| Conflict/change tracking | **ABSENT** | source fingerprint + target revision where supported. | Safe update policies. |
| Batch/checkpoint/resume | **ABSENT** | source cursor/version + mapping revision compatibility. | Job-backed/bounded. |
| Run/row evidence | **ABSENT** | bounded safe diagnostics, PII retention controls. | No full source-row indefinite storage. |
| Rollback | **ABSENT/PARTIAL BY DESIGN** | explicit recorded deltas/snapshots and coverage limits. | Never promise full generic rollback. |
| Scheduled runs | **DEPENDENCY** | persistent source refs, overlap/failure pause, published revision. | Surface 18/Job. |
| Data Export | **ABSENT** | Policy-aware source/field/format/destination and injection protection. | Secrets prohibited. |
| Remote source/destination | **DEPENDENCY** | approved Connections/Safe HTTP profiles. | Surface 23. |
| Backup protection | **DEPENDENCY** | restore point for high-risk sync when policy requires. | Surface 24. |
| Multisite | **UNSPECIFIED** | site/network identity and data ownership. | Contract before runtime. |
| Accessibility | **NO SURFACE UI** | mapping/conflict/progress/error accessibility. | Browser/axe evidence later. |
| Compatibility | **NO SURFACE RUNTIME** | WP/PHP/parser/provider/schema-version matrix. | Exact-head evidence later. |
| Reliability/performance | **NO SURFACE RUNTIME** | streaming/batching/idempotency/locks/resource limits/resume. | Deterministic tests later. |

## Safety hard gates

Future implementation must reject or fail closed on:

- arbitrary PHP/eval/raw SQL source or transform execution;
- archive traversal/bombs, XML external entities and unsafe remote fetch;
- direct writes into another module's private tables;
- generic mass assignment;
- destructive sync without source ownership marker/identity proof;
- ambiguous/non-unique target identity;
- admin-equivalent user role elevation without dedicated high-risk authorization;
- plaintext password import or generic incompatible hash import;
- uncontrolled remote media URLs outside Safe HTTP/size/MIME policy;
- unbounded batches/concurrency;
- resume after source/mapping revision mismatch;
- claiming rollback coverage beyond recorded reversible changes;
- exporting credentials/passwords/hashes/tokens/secrets;
- CSV formula injection.

## Required Policy / Ability separation

Separate definition read/edit, preview/dry-run, execute, destructive-sync, sensitive-user/role mappings, export-sensitive-fields, failed-row export and rollback abilities. Owner mutations use the target owner's Policy/Ability; Import/Export cannot weaken it.

## Accessibility evidence required later

- keyboard source/mapping/dry-run/run flows;
- accessible mapping/conflict tables;
- focus after parser/validation/conflict errors;
- non-color-only create/update/delete/conflict states;
- progress/status live regions;
- axe coverage for preview, destructive sync, partial errors and rollback-warning states.

## Multisite evidence required later

- site/network source and target scope;
- user/network identities and stable portable keys;
- global vs site tables/content;
- media URL/path mapping;
- network admin capabilities;
- package dependencies across sites;
- no silent mutation of network-global data from subsite import.

## Reliability/performance evidence required later

1. parser limits block oversized/deep/malicious sources;
2. identity/preflight detects duplicate/non-unique keys;
3. dry run performs zero target mutation;
4. destructive sync only touches records owned by the import/source identity unless explicitly widened;
5. zero/false/null/empty semantics do not collapse;
6. owner validation/Policy is applied per mutation;
7. batch/checkpoint resume requires matching source+mapping revisions;
8. duplicate/retry execution is idempotent where possible;
9. relations/media second-pass work is bounded and recoverable;
10. per-row diagnostic retention is privacy bounded;
11. rollback refuses unsafe later-changed records and reports coverage limits;
12. exports block secrets and neutralize spreadsheet formula injection;
13. scheduled runs lock overlap and use durable Job execution.

## Future implementation order

Only after Bank review + schema-valid option contracts:

1. Configuration package verification/diff/conflict read-only pipeline;
2. Data Import definitions + parsers + source preview;
3. target schema/mapping + dry run;
4. bounded create/update execution + run evidence;
5. relations/media/user owner integrations;
6. sync delete/archive + ownership markers + high-risk gates;
7. export formats/destinations/security;
8. checkpoint/resume/scheduling/rollback/Backup integration;
9. multisite/portability/degraded closure;
10. exact-head security/accessibility/compatibility/performance certification audit.

## Exit decision

Exact main has a mature exhaustive Import/Export specification and clear owner boundaries, but the Master Options Bank remains `UNSEEDED / 0` and no runtime module exists. `runtime_allowed=false` remains correct.

The next valid action is Bank seeding/native/market review, then schema-valid option contracts and UX re-review — not data/package mutation from this planning lane.
