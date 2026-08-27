# WPEssential — Per-Module Data / Privacy / Retention Matrix

Status: **Phase 0 planning — no runtime implementation authorized**

This matrix applies the P0–P4 classification contract from `docs/PRIVACY-DATA-CLASSIFICATION-RETENTION.md` to every WPEssential module/platform surface.

## Retention shorthand

- **Definition retention** — keep until explicit definition deletion/uninstall cleanup; revisions follow module revision policy.
- **Operational retention** — configurable period/volume; no indefinite default assumption until evidence/product requirement exists.
- **User-linked retention** — module-specific export/erase/anonymize rules plus integrity constraints.
- **External-reference retention** — retain only while needed for reconciliation/history; never retain remote secrets in ordinary tables.
- **Archive retention** — user-defined Backup retention policy + destination behavior.

No row below sets jurisdiction-specific legal retention periods.

---

| # | Surface | Primary classes | Owned data | Retention / erase direction |
|---|---|---|---|---|
| 1 | CPT Builder | P0/P1 | CPT definitions, revisions, dependencies | Definition retention. No post content owned by CPT Builder. Deleting a CPT definition must not silently delete posts. |
| 2 | Taxonomy Builder | P0/P1 | taxonomy definitions/revisions | Definition retention. Terms/term data remain WordPress runtime content and require explicit content deletion policy. |
| 3 | Custom Fields | P0/P1; values may P2/P4 | field schemas/locations; runtime values belong target Data Source | Field definitions retained separately from values. Personal-data erase delegates to Data Source/entity owner. P3 field types use Vault references, not ordinary field value storage. |
| 4 | Relations | P1; links may P2/P4 | relation definitions + relation links/pivot metadata | Definition retention; runtime link retention follows connected entities/delete policy. Personal relationship data participates in exporter/eraser only where meaningful. |
| 5 | Status Manager | P0/P1; history may P2 | status definitions, transition history | Definitions retained; transition history follows owner entity/audit retention. Do not erase history in a way that makes workflow/security state unverifiable. |
| 6 | Query Builder | P0/P1; saved params may P2 | query AST/params/cache metadata | Definition retention. Avoid persisting result sets by default. Cached personal/private results inherit source data classification and short invalidation/retention policy. |
| 7 | Custom Tables | P1; row data P0–P4 | table schema + WPE table rows | Schema definition retention; row privacy defined per table/column. Table builder must expose field classification/erase mapping; destructive table deletion explicitly gated. |
| 8 | Admin Columns | P0/P1; rendered data may P2/P4 | column-view definitions/user view prefs | Definitions retained. Do not persist displayed row values except bounded cache. Per-user view preferences are P2-lite and erasable. |
| 9 | Dynamic Listings | P0/P1 | listing templates/query bindings/cache metadata | Definitions retained. Rendered source content is not copied into listing storage by default. Cache inherits source classification. |
| 10 | Dashboard Widgets | P0/P1; dismiss state P2 | widget definitions, per-user dismiss/preferences | Definitions retained; per-user UI state erasable. Remote-content cache classified by payload. |
| 11 | Admin Menu Builder | P1; per-user profiles P2 | menu profiles/rules/preferences | Definitions retained. Per-user explicit assignments/preferences erasable; role/capability rules are configuration. |
| 12 | Settings Page Builder | P0/P1; values P0–P4; secrets P3 | page definitions + settings values | Definitions retained. Each generated setting declares classification/retention. Secret fields store Vault refs only. Personal setting values participate in privacy export/erase where applicable. |
| 13 | Dashboard Builder | P0/P1; route user prefs P2 | dashboard/navigation/page definitions | Definitions retained. Route access logs not stored by default. User-specific dashboard preferences erasable. Embedded source data remains source-owned. |
| 14 | User Profile Builder | P1/P2/P4 | profile templates; user profile fields via user storage | Templates retained. User data exported/erased through WordPress privacy APIs subject to protected-history rules. Never expose password/session/application-password material. |
| 15 | Membership | P1/P2/P3 refs/P4 | plans/rules, enrollments, entitlement cache, teams/seats, invites, billing refs, reconciliation metadata | Definitions retained. Current entitlement cache short-lived/derived; enrollment history separately retained. Billing IDs only, no card data. Invitation/recovery tokens P3 and expire. Export/erase must preserve necessary access/accounting history via anonymize where deletion would corrupt integrity. |
| 16 | Builder Widgets | P0/P1 | component blueprints/adapter mappings | Definition retention. No arbitrary source code secrets. Builder document references inherit external system ownership. |
| 17 | Forms & Workflows | P1/P2/P4/P3 refs | form/workflow defs, entries, uploads, run/step logs | Per-form entry/file retention options required. Can choose no entry storage. Workflow logs operational retention; secrets referenced only. User-linked entries participate in export/erase subject to business integrity. |
| 18 | Cron Builder | P1; run logs P1/P2 | schedules, safe args, run history | Schedule definition retention. Run logs operational retention. Job payloads must not contain secrets/raw personal content when references suffice. |
| 19 | Notifications | P1/P2 | rules, notifications, read state, delivery metadata/preferences | Rules retained. User notification/read/preferences exportable/erasable as appropriate. Delivery logs operational retention; email/SMS body copying minimized. |
| 20 | Emails | P0/P1; logs P2 | templates, branding, test/delivery metadata | Templates retained. Delivery logs operational retention and recipient personal data minimized. Message bodies not logged by default. |
| 21 | Chat | P2/P4; tokens P3 | conversations, participants, messages, read state, moderation, attachments | Explicit site/conversation retention policy. Participant privacy exporter/eraser must account for other participants/moderation history. Attachments share conversation authorization. Search index cannot outlive authorization/retention source. |
| 22 | REST API Builder | P1; logs P1/P2; auth refs P3 | endpoint definitions, rate/audit metadata | Definitions retained. Request/response bodies not generically logged. Logs operational/minimized. Credentials/auth tokens Vault-owned. |
| 23 | Webhooks & Connections | P1/P2/P3 | connection metadata, credential refs, webhook/delivery logs | Connection defs retained. Vault owns credentials. Raw provider payload retention bounded/minimized; signatures/event IDs retained only as required for verification/idempotency. |
| 24 | Backup Manager | P1/P2/P3/P4 | backup schedules/destinations/manifests/catalog; archives may contain everything | Archive retention user-defined; manifests/catalog operational. Destination secrets Vault-only. Privacy erasure caveat: historical backups may still contain erased live data until retention expires. |
| 25 | Reset Manager | P1/P2/P4 snapshot refs | reset profiles, impact/audit/restore-point refs | Profiles retained. Execution records operational/audit retention. Restore-point archives owned by Backup Manager. No full content copies in reset logs. |
| 26 | Import / Export | P1/P2/P4 | import mappings, batch metadata, source-target maps, reports, temporary artifacts | Batch reports operational retention; mapping history retained as needed for re-import/rollback. Uploaded artifacts have explicit temp retention and secure deletion best-effort. Secrets excluded by default. |
| 27 | Protector | P1/P2/P3 | protection rules, IP/security logs, recovery refs/tokens | Rules retained. Access/IP logs purpose-limited operational retention. Recovery secrets P3 and rotated/expired. Privacy-sensitive IP/proxy data not kept indefinitely by default. |
| 28 | Watermark | P0/P1/P4 media | rules, derivative metadata | Rules retained. Original media remains source-owned; WPE derivative metadata removed/rebuilt safely. Does not create separate personal-data ownership merely by rendering watermark. |
| 29 | XML-RPC Manager | P1/P2 logs | rules/method inventory/request diagnostics | Rules retained. Request logs minimized/operational; do not log credentials or bodies by default. |
| 30 | Role & Capability Manager | P1/P2 | role definitions, capability assignments, change history | Current auth configuration retained. User-role assignments are personal/account data and follow WordPress user erase/reassignment policy. Security change history may need anonymized retention. |
| 31 | Platform / Account / Support / Docs | P0/P1/P2/P3 refs | module state, local account/license refs, support metadata, diagnostics, docs/changelog state | Module/docs settings retained. Account tokens P3/Vault. Support identity/tickets follow service + local cache policy. Diagnostics require preview/redaction/consent; no automatic sensitive upload. |

---

# Module implementation checklist extension

Before a module may leave `Specified` status, its implementation spec must enumerate each persisted field/table/option/cache/log category with:

- P0/P1/P2/P3/P4 classification;
- purpose;
- owner;
- storage location;
- encryption/Vault requirement;
- default retention mode;
- configurable retention options;
- personal-data exporter behavior;
- eraser behavior (`delete`, `anonymize`, `retain-with-reason`, `unlink`);
- backup implications;
- import/export inclusion;
- audit/log inclusion/exclusion;
- external processor/provider;
- access capability/policy;
- cleanup job semantics;
- test cases.

A table or JSON blob containing mixed classifications must document field-level classification instead of labeling the entire store generically.

---

# Default privacy UX rules

1. No hidden telemetry.
2. Diagnostics upload is preview + redaction + explicit consent.
3. Forms do not collect IP/user-agent merely because technically available.
4. Logs store IDs/safe summaries instead of full bodies/content.
5. Secrets are write-only references in ordinary UI after save.
6. CSV export neutralizes spreadsheet-formula injection.
7. Protected/private content never appears in generic debug exports/support bundles.
8. External AI outbound context is separately classified; P3 prohibited, P2/P4 explicit purpose/opt-in.
9. Module disable pauses processing without deleting retained data.
10. Plugin uninstall preserves data by default unless explicit cleanup level chosen.

---

# Cross-module erase ordering

Personal-data erase/anonymize may span modules. Candidate orchestration order:

1. identify user/principal and site scope;
2. inventory module-owned records;
3. evaluate retention/integrity holds;
4. revoke active access/session/invite tokens where appropriate;
5. anonymize/delete module records through owner APIs;
6. remove derived caches/search indexes;
7. unlink external references where policy allows;
8. record privacy operation summary without copying erased content;
9. report retained categories + reason to administrator where appropriate.

One module must not directly delete another module's records during privacy cleanup.

---

# Backups and erasure

WPE must explain clearly that erasing live personal data does not retroactively rewrite every existing backup archive. Restoration of an old backup can reintroduce previously erased data; restore procedures therefore need post-restore privacy reconciliation guidance/automation where practical.

Do not claim cryptographic/secure erasure from third-party backup destinations when their provider/filesystem semantics cannot guarantee it.

---

# Development gate

This matrix is planning only. No privacy exporter/eraser, cleanup job, DB schema or retention cron implementation is authorized before explicit owner development consent under ADR-0014.