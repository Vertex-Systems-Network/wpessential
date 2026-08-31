# WPEssential — Staging, Clone & Migration Manager

Status: **Phase 0 exhaustive planning / no development authorization**  
Edition: **Pro**  
Surface: **55**

## 1. Purpose

Own persistent staging environments, cloning, selective environment synchronization and site migration. Backup Manager supplies recovery artifacts/restore points; this surface owns environment topology and promotion semantics.

Market bar audited from WPvivid, Backuply and BackWPup includes clone/migration, staging, push-to-live, remote migration and Multisite profiles.

## 2. Screens

- Environments
- Create Staging
- Clone / Migrate
- Push / Pull Plans
- Migration Packages
- URL / Path Mapping
- Database Mapping
- File Mapping
- Diff / Drift
- Preflight
- Runs
- Recovery Points
- Remote Targets
- Multisite
- Logs / Diagnostics
- Settings

## 3. Environment model

Fields:
- environment ID;
- type: production/staging/development/clone/migration-target;
- site/network identity;
- URL/home URL;
- filesystem root;
- DB connection/profile reference without exposing secrets;
- table prefix/site mapping;
- source environment;
- created-from revision/backup;
- last sync;
- isolation state;
- search-engine indexing policy;
- outgoing email/payment/webhook safety profile;
- owner/access Policy.

## 4. Create staging

Targets:
- subdirectory;
- subdomain;
- separate domain;
- separate server/remote target through certified transport;
- Multisite subsite/network profiles where technically valid.

Wizard:
1. choose source and target;
2. compatibility/preflight;
3. choose DB/files scope;
4. environment safety controls;
5. URL/path mapping;
6. secrets/provider strategy;
7. create source recovery point;
8. dry-run plan/fingerprint;
9. execute through JobService;
10. verify target WordPress health;
11. generate completion/drift baseline.

## 5. Safety defaults

Staging defaults should prevent accidental production effects:
- search indexing discouraged/noindex where appropriate;
- transactional email disabled or redirected to safe sink unless operator enables;
- payment gateways switched to adapter-defined sandbox/disabled profile where possible;
- outbound webhooks disabled/redirected unless explicitly allowed;
- analytics/marketing snippets disabled through environment condition integration;
- production cron/background side effects reviewed;
- environment identity banner via Admin Theme;
- production secrets not copied blindly where provider supports separate credentials.

These are adapter-backed controls, not promises for unknown plugins.

## 6. Copy scope

Database:
- all selected WP tables;
- selected tables;
- WPE/custom tables;
- network/site-specific tables;
- exclude logs/cache/analytics tables through explicit profile;
- dependency warning.

Files:
- uploads;
- plugins;
- themes;
- mu-plugins;
- language files;
- selected wp-content dirs;
- core optional;
- excludes.

## 7. URL / serialized data mapping

- source URL → target URL;
- home/site URL;
- upload base;
- filesystem path;
- domain/email mapping where explicitly configured;
- serialization-safe replacements;
- block/editor JSON aware replacements;
- builder-specific transformations only through certified adapters;
- dry-run occurrence counts;
- protected/signed/encrypted values excluded by default.

Use Search/Replace engine as the transformation owner, not ad-hoc unsafe SQL regex replacement.

## 8. Push staging → live

Push modes:
- full replace after backup;
- files only;
- selected DB tables;
- selected content/entity changes through future differential adapter;
- selected uploads;
- configuration definitions;
- no automatic generic DB merge claim.

Preflight:
- production changed since staging baseline;
- source/target versions;
- schema migrations;
- plugin/theme drift;
- user/order/form-entry/live-data risk;
- Woo/commerce live-data boundary;
- backup recovery readiness.

When both environments changed, WPE must surface conflict rather than overwrite silently.

## 9. Pull live → staging

- refresh all;
- selected database/files;
- anonymization/redaction profile for personal data;
- preserve staging-only config/secrets where mapped;
- preserve environment safety profile;
- rebuild URL/path mappings;
- reset outbound integrations as configured.

## 10. Migration

Modes:
- package/download + import;
- direct remote transfer;
- remote-storage handoff;
- target pull using signed one-time migration token;
- restore-from-certified backup into target with migration mapping.

Migration completion requires target verification, not just transfer success.

## 11. Clone

- create independent clone identity;
- new URL/path;
- optional new DB/prefix;
- clone-specific secrets/provider bindings;
- clone metadata records source lineage;
- no shared session/cookie secret assumptions;
- update GUID-like values only according WordPress semantic rules, never blanket replacement.

## 12. Incremental/differential sync

Future certified profiles may compare:
- changed files by hash;
- changed tables/rows where stable identity exists;
- WPE definition revisions;
- media asset graph;
- content revisions.

No generic “incremental push” is exposed until conflict, delete and identity semantics are certified.

## 13. Remote target connector

- endpoint/transport;
- authenticated one-time pairing;
- capability/version negotiation;
- TLS required;
- source/target fingerprint;
- transfer chunking/checksum;
- resumable checkpoints;
- target storage/disk preflight;
- expiry/revocation;
- no reusable migration secret in URL/logs.

## 14. Multisite

Profiles:
- clone whole network;
- clone selected site into another network when supported;
- migrate subsite → single site only through explicit mapping;
- single site → network site through explicit mapping;
- domain mapping;
- upload path/site IDs;
- network-active plugin/theme constraints;
- Super Admin handling;
- user identity collision mapping.

## 15. Recovery

Every destructive push/migration cutover requires a certified recovery point unless a dedicated override policy exists.

Track:
- pre-run backup;
- target backup;
- rollback plan;
- DNS/cutover state external step;
- verification checklist;
- recovery expiration.

## 16. WP-CLI / Abilities / AI

Abilities:
- environment list/get;
- create staging plan;
- preflight;
- clone/migration plan;
- push/pull dry run;
- run/cancel authorized job;
- status/logs;
- verify target.

WP-CLI calls the same abilities.

AI/MCP may inspect, plan, explain drift and prepare migration maps. Production push/cutover, destructive replacement and credential changes are approval-gated.

## 17. Permissions

Candidate:
- `wpe_staging_read`
- `wpe_staging_create`
- `wpe_staging_sync`
- `wpe_staging_push_live`
- `wpe_migration_create`
- `wpe_migration_run`
- `wpe_environment_credentials_manage`
- `wpe_staging_network_manage`
- `wpe_staging_unsafe_override`

## 18. MUST NOT

- no push-to-live without drift/recovery preflight;
- no blind production secret copying;
- no generic live DB merge claim;
- no staging side effects silently hitting production providers;
- no serialization-breaking replacement;
- no migration success until target verification;
- no reusable public migration token;
- no Multisite identity conversion by guessing.

## 19. Evidence

Reserved namespace: **STG-001…STG-176**, executed **0/176**.

Evidence groups cover environment model, staging creation, safety controls, DB/files copy, URL/serialization mapping, push/pull drift, remote transfer, clone identity, migration cutover, recovery, Multisite, privacy redaction, WP-CLI/MCP, scale and golden migration/regression scenarios.