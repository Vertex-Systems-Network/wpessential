# WPEssential — Data Ownership & Lifecycle Registry — Surfaces 32–56

Status: **Canonical supplement / planning-only / no development authorization**  
Date: **2026-08-29**

## 1. Purpose

The original Module Dependency & Data Ownership contract documents original modules. This supplement closes explicit ownership/lifecycle semantics for current **Surfaces 32–56**.

Shared rule:
- definition/config owner != referenced source-record owner;
- derived/index/cache/output data != source truth unless an explicit profile says otherwise;
- disable preserves owned data by default;
- uninstall/delete-data is separately consented, dependency-aware and cannot delete peer/external data;
- Multisite/site/tenant ownership is server-resolved;
- import/clone/restore never silently changes external authority.

## 2. Ownership matrix

| # | Surface | Owns configuration/definitions | Owns operational/derived data | References but does NOT own | Disable/deletion boundary |
|---:|---|---|---|---|---|
| 32 | Solutions | Blueprint, version, dependencies, variables, install/upgrade mapping plans | installed-solution binding/provenance/drift metadata | every module definition/entity bound into solution | disabling Solution stops solution-level orchestration/UI only; owner definitions remain; detach removes binding not module data |
| 33 | Analytics | event definitions, tracking profiles, metric/funnel/cohort/attribution definitions | analytics occurrences, sessions/identity links, aggregates/snapshots as declared | source business entities, Audit records, operational Event Bus | disable collection/query; retain per policy; delete analytics data never deletes source business/audit records |
| 34 | Search | index definitions, schema/analyzer/synonym/ranking/rules | index documents, inverted/derived index data, search logs if enabled | source Data Source records and current Policy | disabling index makes consumers degraded/fallback only if explicit; deleting index never deletes source entities |
| 35 | Decision | formula, scorecard, decision table, ranking, threshold definitions | optional bounded evaluation traces/simulation results | input source records, Policy, ledger/order/reservation records | delete definition blocked by consumers; traces follow retention; no downstream business mutation is deleted/reversed automatically |
| 36 | Ledger | ledger/account/posting-policy definitions | authoritative WPE-owned posting/transaction/hold entries, balance snapshots, reconciliation records for that profile | external provider settlement/order/payment facts | ledger history is append-oriented; ordinary delete-data cannot silently erase governed movement history; reversal/retention rules explicit |
| 37 | Reservations | resource/calendars/availability/capacity/reservation policy definitions | holds, reservations, waitlist and reconciliation state for WPE-owned reservation profile | payment/order/ledger/provider calendar truth | disable stops new booking/holds while preserving reservations; deletion cannot cancel external bookings without provider flow |
| 38 | Placement | slot/experience/rule/frequency definitions | user dismissal/frequency state, bounded resolution/exposure references as declared | component/listing/source content, Experiment assignment | deleting experience does not delete source component/content; disable stops placements and invalidates caches safely |
| 39 | Experiments | experiment/variant/allocation/rollout/metric-link definitions | assignment/exposure and decision records | Analytics metric events, source experiences/features | stopping experiment preserves assignments/exposure history; delete does not delete analytics/source content |
| 40 | Documents | template/document/profile/numbering/retention definitions | generated artifacts, immutable/issued record snapshots, amendments, checksum/share/download metadata where WPE-owned | source business data, external legal signing/timestamp/payment/order facts | issued/immutable records follow retention/legal-hold rules, not normal module delete; source records untouched |
| 41 | Sync | pipeline, mapping, field authority, conflict/delete/retry profiles | cursors/checkpoints, operation identities, run/item status, conflicts, dead letters, reconcile metadata | source/destination authoritative records, Connection credentials | disable stops new sync/replay; remote/local records stay owners; delete sync state cannot claim rollback of providers |
| 42 | Geo | location/territory/service-zone/geocoder/provider profile definitions | WPE-owned normalized/geocode/cache/spatial-index metadata where profile declares | external geocoder/router facts, protected subject identity/address authority | privacy/retention applies precise coordinates; deleting cache does not delete source address/entity |
| 43 | AI | providers/model profiles, task/prompt/knowledge/retrieval/eval/budget definitions | AI run/evaluation/usage/audit metadata within retention | Vault secrets, source evidence, target module data/actions | disabling AI blocks runs; target module data untouched; knowledge/index deletion follows source/derived rules |
| 44 | Redirects | redirect/routing rule definitions/import provenance | optional hit/log/diagnostic metadata | source content, Menu/Protector/Search/Link definitions | disabling rule stops routing effect; deleting rule never deletes source/target content |
| 45 | Transform | transform/search-replace plan definitions | run/checkpoint/diff/change/rollback/reconciliation metadata | target source records owned by Data Source/module; Backup artifact | plan deletion never rolls back data; run mutation occurs through target owner contract and recovery evidence |
| 46 | Fixtures | fixture definitions/generator/scenario/seed profiles | generation run/provenance/cleanup mapping | target generated records after creation remain target Data Source owned, though marked synthetic | cleanup can remove only records with verified fixture provenance and target-owner Policy; no broad guessed deletion |
| 47 | Link Health | scan/check profile, saved view/ignore/snooze/fix-plan definitions | occurrence/check result/history/provider evidence | source content/URL fields, Redirect/Transform/Media owners | deleting scan data does not edit source; fixes require owner Ability |
| 48 | DB Maintenance | maintenance/cleanup/optimization profile definitions | candidate scans/run/history/diagnostics | domain records/tables owned by modules/WP, Backup | owner approval/hook required before domain destructive cleanup; physical maintenance cannot infer business deletion authority |
| 49 | Admin Theme | admin theme/token/branding/assignment/revision definitions | per-user selected theme preference if supported, compatibility diagnostics | Fonts53, Media28, WordPress user/auth state | disable returns certified native/default presentation without changing access; no deletion of referenced fonts/media |
| 50 | Safe Script | browser snippet/tag/placement/condition/consent/CSP/SRI/environment/revision definitions | optional activation/error/diagnostic metadata | external script providers, consent authority data, theme source files | disable stops injection; import/delete never modifies theme/plugin PHP; no secrets copied into snippets |
| 51 | Content Order | sequence/context/strategy definitions | independent sequence membership/ranks/revision/concurrency metadata where selected | underlying posts/terms/entities, hierarchy source objects | delete sequence removes only WPE sequence data; underlying entities/hierarchy remain; native `menu_order` restore follows recorded explicit mutation history |
| 52 | Security Scanner | scan/baseline/provider/suppression/remediation-plan definitions | baseline/checksum inventories, findings, scan/reputation/vulnerability evidence, quarantine metadata if executed | source files/packages, Protector policies, external vulnerability feed truth | deleting findings does not repair files; quarantine/repair separate high-risk lifecycle with Backup/recovery |
| 53 | Fonts | family/face/axis/provider/assignment/delivery/license-provenance definitions | WPE-managed font assets/subsets/cache/fingerprint metadata when imported/generated | legal licensing authority, external provider catalog truth, Theme/AdminTheme settings | delete blocked by consumers or requires remap; no deleting system/provider source; license evidence retained as policy requires |
| 54 | User Stores | store definitions/type/limits/share/expiry policy | store instances/items/order/meta, guest identity/merge/share state | target products/posts/entities, Woo cart/order/payment/stock, Membership | deleting a favorite never deletes target object; disabling preserves store data by policy; guest/user erasure follows privacy |
| 55 | Staging | environment/clone/migration/push-pull/cutover/drift/recovery plan definitions | environment registry, transfer/checkpoint/mapping/drift/cutover metadata | Backup artifacts, target/source site content, provider credentials/webhook registrations, Transform operations | deleting environment registry is not deleting remote site unless explicit provider action; clone gets new identity; credentials remain Vault/provider owned |
| 56 | Theme Workspace | workspace/child-theme metadata, analysis profile, CSS/theme.json/template override/package/activation plan definitions | child-theme files/assets created by the workspace when explicitly WPE-owned; drift/analyzer/package metadata | parent theme, Fonts53, Media28, Safe Script50, Admin Theme49 | parent theme never silently mutated/deleted; child deletion has dependency/active-theme/recovery checks; no arbitrary PHP live editor |

## 3. Shared-service ownership reminders

### Definition Repository
Owns definition identity/revisions/dependency edges for registered definition types. It does not own each domain's operational business records.

### Audit/Observability
Owns audit/operational evidence storage according to Audit policy. It does not own domain records and must not be used as Analytics33 primary store.

### Vault
Owns encrypted secret material/secret metadata. Surfaces store stable secret references only.

### Job Service
Owns execution scheduling/claim/retry/progress mechanics. Domain operation result semantics stay with the submitting owner.

### S03 Protected Asset
Owns protected-delivery/access mediation metadata and storage references for configured private assets. Membership/Documents/etc. remain Policy/source-domain owners.

### S04 Context Resolver
Owns typed runtime context resolution, not business state.

### S05 Money/Decimal/Unit
Owns type/normalization/rounding/conversion semantic libraries, not money/account balances.

### S06 Approval Policy
Owns reusable approval-policy definitions; Workflow17 records/executes workflow process, while the protected domain owner executes the approved mutation.

## 4. Cross-owner deletion checklist

Before any delete/erase/uninstall operation:
1. resolve canonical owner;
2. enumerate dependent definitions/records;
3. distinguish owner data vs references vs derived/cache;
4. identify external/provider facts that cannot be rolled back;
5. resolve legal/privacy retention/hold;
6. require backup/recovery for high-impact mutation;
7. execute through owner Ability;
8. verify result and dangling refs;
9. invalidate caches/search indexes;
10. audit without sensitive payload leakage.

## 5. Clone/restore/import boundary

- Backup restore restores only what the manifest actually owns/captures; external provider state is reconciled, not rolled back.
- Staging clone creates a new environment identity and quarantines live provider side effects.
- Solution Blueprint install creates/maps canonical owner definitions; no private Solution storage clone.
- Import normalizes records/definitions into target owner schemas.
- DUP/entity clone produces new target entity identity unless the target contract explicitly models a retained identity (rare and separately governed).

## 6. Implementation acceptance

No runtime table/schema may be introduced until its row identifies:
- surface/shared-service owner;
- record classes;
- authoritative vs derived status;
- site/network/tenant scope;
- lifecycle/retention/privacy;
- disable behavior;
- uninstall/delete behavior;
- Backup/Restore behavior;
- import/export/migration authority;
- external/provider reconciliation.

Any unowned table/option/meta record is a stop-the-line architecture defect.
