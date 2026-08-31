# WPEssential — Database Maintenance, Cleanup & Storage Health Manager — Exhaustive Product Specification

Status: **Phase 0 planning / no development authorization**
Date: 2026-08-29

## 1. Purpose

Provide safe, owner-aware cleanup and storage-health tooling for WordPress/WPE data without becoming a blind SQL-deletion utility. The module should compete with database-cleanup plugins while integrating WPE lifecycle, Backup, Jobs, Audit, Multisite and module-owned cleanup contracts.

## 2. Module identity

Pro module candidate: **Database Maintenance & Cleanup Manager**

Navigation:
`WPEssential → Data Tools → Maintenance`
- Overview
- Cleanup Candidates
- Storage Health
- Autoload
- Tables
- Scheduled Cleanup
- Plans / Dry Runs
- Runs / History
- Rules
- Settings
- Diagnostics

Dependencies:
- Data Source Registry
- Module Lifecycle
- Backup
- JobService
- Audit/Observability
- Privacy
- Error Taxonomy
- Multisite/Site Lifecycle
- AI Prompt Runtime

## 3. Cleanup candidate classes

WordPress-native candidates:
- post revisions;
- auto-drafts;
- trashed posts/pages/CPTs subject to owning policy;
- trashed/spam/unapproved comments by retention profile;
- expired transients;
- non-expired transients only in explicit cache-reset profile;
- orphan postmeta/commentmeta/termmeta/usermeta only after identity validation;
- orphan term relationships;
- orphan options only through known owner signatures;
- old temporary upgrade/cache artifacts through registered providers;
- media derivative orphans only through Media service;
- expired sessions only through owning adapter.

WPE candidates:
- expired Job/Attempt history;
- old Workflow history per retention policy;
- notification/email/chat delivery history;
- analytics raw-event retention/downsampling;
- audit log retention only through Audit service;
- Search index generations/tombstones;
- stale import/export journals;
- old generated fixture runs;
- archived caches/projections;
- old provider event inbox rows;
- orphan definitions/revisions only through Definition Repository dependency graph;
- module-specific cleanup callbacks.

Domain adapters:
- Woo sessions/transients/lookup rebuild candidates only through certified Woo adapter;
- Action Scheduler old actions/logs only through certified JobService adapter;
- third-party tables never cleaned by guess.

## 4. Owner-aware cleanup registry

Every cleanup provider declares:
- provider key/version;
- object/storage owner;
- candidate query;
- identity keys;
- eligibility rules;
- minimum age/retention;
- dependencies;
- delete/archive/compact action;
- batch semantics;
- rollback class;
- post-check;
- privacy implications;
- Multisite scope;
- certification state.

Unknown table/row ownership cannot be treated as safe-to-delete merely because data appears orphaned.

## 5. Cleanup Plan

Fields:
- name/key/revision;
- selected candidate classes;
- site/network scope;
- retention thresholds;
- age cutoff;
- max records/bytes;
- backup requirement;
- schedule;
- allowed maintenance window;
- resource budget;
- owner capability;
- failure policy;
- verification profile.

## 6. Dry Run

Dry Run is default and shows:
- candidate count per class;
- estimated bytes recoverable;
- affected tables/sites/modules;
- oldest/newest record;
- owner/provider;
- dependency warnings;
- false-orphan uncertainty;
- active-reference warnings;
- backup requirement;
- rollback class;
- estimated duration/locks;
- autoload/storage impact;
- schema/table health warnings.

No deletes/writes.

## 7. Revisions / content retention

Options:
- keep all;
- keep newest N per object;
- keep revisions newer than duration;
- preserve named/milestone revisions;
- exclude post types;
- only delete trash older than duration;
- archive instead of delete where provider supports.

Must respect legal/compliance retention profiles from owning module.

## 8. Transients / cache-like data

- expired transients safe profile;
- site/network transient distinction;
- object-cache presence awareness;
- cache flush action separate from DB cleanup;
- no assumption every option name matching `_transient_*` is deletable outside verified WordPress semantics;
- WPE Cache service owns its generations.

## 9. Orphan detection

Candidate types:
- metadata parent missing;
- relation endpoint missing;
- attachment derivative missing source;
- source missing derivative;
- user/site-owned rows after site deletion;
- job/event child missing parent;
- index document/tombstone stale;
- module removed but retained data.

Orphan confidence:
- CERTAIN_BY_FK/OWNER API;
- CERTAIN_BY_WORDPRESS IDENTITY;
- PROBABLE;
- UNKNOWN.

Automatic cleanup allowed only for certified candidate classes. Probable/unknown requires review/provider adapter.

## 10. Autoload health

Inspect:
- total autoload bytes;
- largest autoloaded options;
- owner/plugin guess only as advisory unless registered;
- duplicate/obsolete keys;
- autoload flag compatibility;
- option update frequency;
- known WPE settings policy.

Actions:
- change autoload only through owning settings/provider contract;
- migrate large WPE settings to correct storage if approved;
- never blindly toggle third-party core-critical options.

## 11. Table health

Report:
- row count/estimated size;
- data/index size;
- engine/collation/charset;
- unused/fragmentation advisory where DB supports;
- missing expected indexes through owning schema contract;
- unexpected schema drift;
- high-growth tables;
- stale temporary tables;
- integrity/check status where safe.

Optimize/repair operations are DB-engine-specific and evidence-gated; not generic “fix everything” buttons.

## 12. Scheduled maintenance

Triggers:
- manual;
- daily/weekly/monthly;
- after import/migration;
- after site deletion;
- after module uninstall/cleanup;
- storage threshold alert.

Uses JobService with:
- batch size;
- maintenance window;
- CPU/DB budget;
- pause under site load;
- locks/resource keys;
- durable checkpoint;
- notifications.

## 13. Backup / rollback

Risk profiles:
- C0 inspect only;
- C1 cache-like/expired certified cleanup;
- C2 reversible via archive/journal;
- C3 destructive business/history cleanup requiring verified backup;
- C4 provider-specific irreversible cleanup blocked unless explicit policy.

Before destructive cleanup:
- verify backup requirement;
- stale backup detection;
- show irreversible classes;
- require reauth/confirmation token.

## 14. Run journal

Store:
- Plan revision/fingerprint;
- actor;
- site scope;
- start/end;
- candidate counts;
- deleted/archived/skipped/errors;
- batches/checkpoints;
- bytes estimate/actual where available;
- provider versions;
- post-verification;
- backup reference;
- rollback class.

Avoid storing deleted sensitive payloads unless rollback profile explicitly needs protected encrypted journal data.

## 15. AI Prompt

Examples:
- “Database cleanup audit karo, sirf safe expired/transient/revision candidates show karo.”
- “Autoload bloat explain karo aur owner-aware remediation plan banao.”
- “30 din se purane generated dummy data aur completed Job history ka dry run do.”

AI can rank/explain/propose Plans; it cannot run destructive cleanup without capability/approval.

## 16. REST / Abilities / MCP / CLI

Abilities:
- inspect storage health;
- list cleanup providers;
- create Plan Draft;
- run Dry Run;
- get redacted candidate report;
- execute authorized Plan;
- pause/resume/cancel;
- verify;
- inspect history.

CLI useful for dry-run/report and controlled maintenance windows. MCP mutation opt-in only.

## 17. Multisite

- distinguish site tables/network/global tables;
- site cleanup cannot delete global user/network data without explicit authority;
- network maintenance plan lists every target site;
- site deletion lifecycle owns post-delete cleanup;
- batch/fairness across sites;
- noisy-site storage reports;
- 100/1k/10k-site evidence profiles.

## 18. Security / failure safety

- no arbitrary DELETE/TRUNCATE SQL UI;
- schema identifiers from validated registry;
- never trust table name from public input;
- concurrent writes can invalidate candidates;
- re-check preconditions at delete time;
- fail closed on uncertain ownership for destructive action;
- partial batches reported honestly;
- DB errors do not silently advance checkpoint;
- provider/module disable pauses its cleanup class.

## 19. Evidence namespace

Future protocol: `DBM-001…DBM-176`, executed 0 until development consent.

Groups cover candidate ownership, WP-native cleanup, WPE retention providers, orphan certainty, autoload, table health, dry-run/Plan, Backup/rollback, Job concurrency, Multisite, privacy, failure recovery, REST/Abilities/MCP/CLI, scale and third-party coexistence.

## 20. MUST NOT

- delete unknown third-party data by heuristic alone;
- arbitrary raw SQL cleanup;
- clear Audit/privacy/legal records contrary to owner retention policy;
- claim bytes reclaimed without evidence;
- optimize/repair tables generically across unsupported DB engines;
- treat orphan probability as certainty;
- let AI bypass destructive approvals;
- cleanup production data automatically merely because a market rule recommends it.
