# WPEssential — Reset Manager Exhaustive Option Specification

Status: **Phase 0 — Exhaustive Option Spec / planning only / no implementation authorized**  
Edition: **Pro**

Composes Backup Manager, Module Lifecycle, Audit, Capability/Policy and Definition Repository contracts.

## 1. Product rule
Reset is a destructive, auditable workflow. There is no normal one-click reset action. Every execution has explicit scope, impact preview, recovery-point policy, re-auth/confirmation and post-reset verification.

---

# 2. Screens

## 2.1 Reset Profiles list
Columns:
- name;
- key;
- status Draft/Active/Archived;
- reset scope summary;
- preservation summary;
- backup requirement;
- last executed;
- last result;
- created/updated;
- actions.

Filters:
- status;
- scope type;
- backup required;
- last result;
- date.

Actions:
- Edit;
- Duplicate;
- Preview impact;
- Run;
- Export;
- Archive/Delete definition.

No bulk Run.

## 2.2 Reset History
Columns:
- run ID;
- profile/manual;
- operator;
- requested scope;
- actual scope;
- restore-point reference;
- started/completed;
- phase;
- result;
- recovery state;
- actions.

## 2.3 Recovery
Shows only when reset/restore has unresolved failure or system is in recovery state.

Cards:
- failure summary;
- safe diagnostic ID;
- restore point health;
- current site boot health;
- recommended recovery actions;
- support bundle action.

---

# 3. Create/Edit Reset Profile

## Identity
- name required;
- key generated/stable;
- description;
- status Draft/Active;
- tags;
- internal note.

## Reset type/preset
Presets:
- WPEssential configuration only;
- WPEssential module data + config;
- Content cleanup;
- Settings cleanup;
- Development/staging reset;
- Near-factory site reset;
- Custom.

A preset only populates explicit scope options; it does not hide what will be deleted.

---

# 4. WPEssential scope

Controls:
- all WPE modules;
- selected modules;
- definitions only;
- runtime data optional per module;
- audit history preserve default on;
- account connection preserve default on;
- support ticket local cache preserve default on;
- Vault credentials preserve default on unless explicitly selected;
- module activation state preserve/reset;
- onboarding state reset toggle;
- license/product entitlement local cache reset toggle;
- diagnostics/local caches clear toggle;
- generated assets/cache clear toggle.

Per-module runtime delete requires module-specific delete contract; no generic table wipe.

---

# 5. Content scope

Selectors:
- post types;
- post statuses;
- author/user;
- taxonomy/term filter;
- date range;
- Query Builder definition;
- explicit IDs;
- relation-driven selection when supported.

Controls:
- delete selected posts;
- trash vs permanent delete where WP entity semantics support;
- revisions;
- autosaves;
- comments tied to selected content;
- post meta;
- term relationships;
- featured/media detach only vs delete attachments;
- child posts/attachments behavior;
- orphan relationship cleanup through owning modules.

Media handling:
- keep attachments default;
- delete only media exclusively attached to selected objects advanced;
- delete selected media regardless of usage only with dependency impact preview;
- original files/derivatives deletion follows WordPress media ownership rules.

---

# 6. Taxonomy scope

Controls:
- selected taxonomies;
- selected terms;
- term meta;
- relationships only vs term delete;
- preserve default/core terms where required;
- reassign posts to fallback term where taxonomy/core behavior requires;
- hierarchy child behavior;
- WPE relation dependency impact.

---

# 7. Comments

Controls:
- all comments;
- status/type;
- linked to selected content only;
- date range;
- comment meta;
- trash/spam/permanent behavior.

Do not delete comments outside scope through broad SQL shortcuts.

---

# 8. Settings/options

Normal mode exposes only registered/known option groups:
- WPE module settings;
- rewrite/permalink-related generated caches only through safe repair actions;
- transients/cache groups;
- selected integration settings through owner adapters.

Controls:
- setting group selection;
- preserve current site identity/URLs default;
- preserve admin email/default role/timezone default unless full baseline intentionally changes them;
- reset to WordPress defaults only where deterministic.

Arbitrary option-name wildcard deletion is not normal product UI.

---

# 9. Users

User deletion excluded from standard presets.

If advanced user reset is accepted later, controls must include:
- selected users/roles/date/query;
- preserve current operator mandatory;
- preserve at least one recovery administrator mandatory;
- multisite Super Admin protection;
- reassign authored content to selected user vs delete content;
- user meta;
- membership/enrollment impact;
- team/seat impact;
- role/capability impact;
- personal-data retention conflicts.

Administrator-equivalent deletion requires highest confirmation/re-auth.

---

# 10. Plugins/themes

Reset controls are configuration/state actions, not package-manager shortcuts.

Options:
- preserve installed packages default;
- preserve currently active theme default;
- preserve active plugin state default;
- deactivate selected plugins after reset;
- reactivate selected plugins after reset;
- reset WPE module activation;
- clear plugin/theme caches via certified adapter only.

Package file deletion/uninstallation is not part of ordinary reset profile.

---

# 11. Database/custom tables

Controls only through registered owners:
- WPE module-owned runtime tables;
- Custom Tables Builder tables explicitly selected;
- rows matched through typed Query/filters;
- truncate/drop only if owning contract explicitly allows and impact is known.

Raw `DROP DATABASE`, arbitrary wildcard table deletion and unrestricted SQL are prohibited standard behavior.

---

# 12. Preserve/exclusion rules

Common preservation toggles:
- current admin/recovery admin mandatory;
- site URL/home URL;
- salts/secrets file untouched;
- account connection;
- Vault;
- backup catalog/restore point;
- WPE audit;
- support history local cache;
- selected posts/users/terms/tables;
- specified option groups;
- uploads/custom paths.

Conflict resolution:
- explicit preservation beats broad reset preset;
- mandatory safety preservation cannot be disabled without separately accepted unsafe mode.

---

# 13. Backup / Restore Point policy

Modes:
- Required — default;
- Required at minimum V1/V2 according environment policy;
- Use existing recent verified restore point if within configured age;
- Create new restore point;
- Unsafe override only when dedicated capability/policy permits.

Fields:
- minimum verification tier;
- maximum restore-point age;
- preferred backup destination;
- protect restore point from retention until reset closed;
- remove protection after successful verification toggle;
- unsafe override reason required;
- typed confirmation phrase.

If backup engine is unhealthy and policy says required, reset is blocked—not silently downgraded.

---

# 14. Impact Preview

Preview shows exact current counts/estimates:
- posts/revisions/comments;
- terms/relationships;
- media/files;
- users if applicable;
- options;
- WPE definitions;
- WPE/custom runtime rows;
- tables;
- active jobs/workflows affected;
- membership/entitlement effects;
- plugin/theme activation changes;
- retained items;
- restore point state.

Preview fingerprint/version records relevant source counts/versions.

Before execution, safety-critical scope is re-evaluated. Material drift can:
- require new preview/confirmation;
- proceed within declared tolerance only for non-critical count changes;
- block if protected user/backup/dependency conditions changed.

---

# 15. Confirmation levels

Level 1: harmless cache/local WPE UI-state reset.

Level 2: content/config deletion with verified recovery point.
- explicit summary;
- checkbox acknowledgement;
- confirmation button.

Level 3: high-impact/full-site/user/table reset.
- recent re-auth;
- typed site/profile phrase;
- restore point proof;
- exact impact summary;
- reason/audit note;
- countdown UI optional accessibility-safe, but not relied on as security.

---

# 16. Execution phases

Candidate phases:
- queued;
- preflight;
- validating restore point;
- locking destructive operations;
- entering maintenance/recovery state if required;
- content cleanup;
- taxonomy/comments cleanup;
- module/runtime cleanup;
- settings reset;
- user actions if supported;
- plugin/theme state actions;
- cache/rewrite regeneration;
- post-reset verification;
- completed;
- completed_with_warnings;
- failed_recoverable;
- failed_recovery_required.

Each phase is idempotency/recovery-aware where possible.

---

# 17. Concurrency / locks

Block concurrent:
- another Reset;
- Restore;
- destructive Import;
- schema migration touching same stores;
- conflicting module cleanup.

Backup creation may be a prerequisite phase, not a concurrent unrelated job.

Stale lock recovery requires explicit diagnostics and safe ownership/expiry rules.

---

# 18. Post-reset actions

Configurable safe actions:
- flush rewrite rules once if relevant;
- clear WPE caches;
- rebuild Definition caches;
- rebuild Membership entitlements if retained source data changed;
- regenerate selected derived media only if required;
- reactivate selected WPE modules/plugins according validated policy;
- redirect operator to onboarding/Home/Recovery;
- send notification;
- generate reset report.

No arbitrary PHP/shell/custom code action.

---

# 19. Screenshot / visual snapshot

Optional planning feature only:
- browser-generated screenshot before reset;
- selected URLs only;
- explicit capture;
- stored as auxiliary evidence, not recovery artifact;
- privacy warning for authenticated/private pages;
- retention policy.

Video recording is not a standard server feature and must not be promised until a separate client/browser architecture exists.

---

# 20. Permissions

Candidate:
- `wpe_reset_read`
- `wpe_reset_profile_create`
- `wpe_reset_profile_update`
- `wpe_reset_profile_delete`
- `wpe_reset_preview`
- `wpe_reset_run`
- `wpe_reset_view_history`
- `wpe_reset_recover`
- `wpe_reset_execute`
- `wpe_reset_unsafe_override`
- `wpe_reset_users`
- `wpe_reset_schema`

High-risk capabilities are never part of ordinary content/experience presets.

---

# 21. Abilities

Candidate:
- `wpessential/reset.profile_list`
- `wpessential/reset.profile_get`
- `wpessential/reset.preview`
- `wpessential/reset.validate`
- `wpessential/reset.run`
- `wpessential/reset.status`
- `wpessential/reset.recovery_status`

AI default exposure:
- read/preview/explain only;
- run/recovery mutations disabled by default.

---

# 22. Events

- reset.previewed;
- reset.requested;
- reset.started;
- reset.phase_changed;
- reset.completed;
- reset.failed;
- reset.recovery_required;
- reset.recovered;
- restore_point.created/failed linkage through Backup events.

Generic events contain counts/IDs, not deleted private record payloads.

---

# 23. Failure states

Explicit:
- no verified restore point;
- restore point stale/missing;
- insufficient disk/storage;
- DB permissions missing;
- operator would remove last recovery admin;
- dependency owner unavailable;
- protected module data cannot be safely reset;
- lock conflict;
- midway deletion failure;
- post-reset boot/health failure;
- plugin/theme reactivation failure;
- multisite unsupported scope;
- backup/recovery system unavailable.

Failure never implies rollback completed unless restore verification proves it.

---

# 24. Multisite

Default v1 planning stance:
- network-wide/full multisite reset requires separate explicit support/certification;
- subsite reset cannot touch network users/network options blindly;
- Super Admin and network-active packages preserved;
- network/site ownership must be explicit;
- unsupported mode blocked with explanation rather than best-effort destructive action.

---

# 25. Acceptance tests after development consent

- preview count fidelity;
- material drift invalidates critical preview;
- no restore point blocks protected profile;
- unsafe override authorization/re-auth;
- last recovery admin cannot be deleted;
- selected post type/date/term boundary;
- attachment preserve/delete semantics;
- module config vs runtime deletion separation;
- account/Vault preservation;
- failure midway enters recoverable state;
- restore point remains protected after failed reset;
- concurrency lock;
- direct-request privilege escalation;
- multisite boundary;
- post-reset health checks;
- audit contains safe summary only.

## Maturity
**Exhaustive Option Spec.** Destructive runtime implementation, transaction/rollback behavior and multisite/full-site certification remain blocked until explicit owner development consent and technical evidence.