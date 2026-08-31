# WPEssential — Documentation, Support & Release Information Architecture

Status: **Phase 0 planning — no service/release implementation authorized**

## Goal
Make help, support and release history part of the product architecture rather than scattered links/pages that become stale as 30+ modules evolve.

---

# 1. Documentation architecture

## Top-level documentation families

### Getting Started
- What WPEssential is / Free vs Pro;
- installation;
- onboarding;
- module enable/disable;
- account connection optionality;
- first CPT/taxonomy;
- first Pro application after Pro is installed;
- system requirements.

### Platform Concepts
- Definitions vs runtime data;
- Data Sources;
- Fields;
- Relations;
- Queries;
- Conditions;
- Policies/capabilities;
- Abilities/events;
- Jobs/workflows;
- Secrets/Connections;
- revisions/dependencies;
- import/export packages;
- Free↔Pro compatibility.

### Module Guides
Grouped by product suites, matching admin IA:
- Content Model;
- Data & Query;
- Admin & Experience;
- Identity & Access;
- Automation & Communication;
- Integration & Data Movement;
- Operations & Protection.

Each module guide follows one predictable order:
1. Overview / when to use;
2. concepts/data ownership;
3. screens/options;
4. step-by-step examples;
5. integrations/cross-module use;
6. permissions/security;
7. performance/limits;
8. import/export/migration;
9. troubleshooting;
10. developer extension hooks/Abilities where public.

### Recipes / Solutions
Cross-module outcome guides:
- directory;
- real-estate listing;
- client portal;
- membership portal;
- CRUD application;
- approval workflow;
- API endpoint;
- verified backup/recovery;
- source-plugin migration.

Recipes reference module docs rather than duplicating option definitions.

### Integrations
Per certified adapter:
- supported versions;
- certification level;
- setup;
- capabilities;
- known limitations;
- troubleshooting;
- deprecation/version policy.

### Migration Center Docs
Per source adapter:
- supported source versions;
- domains migrated;
- fidelity matrix;
- what is not migrated;
- dry-run/conflicts;
- billing/external references;
- source-deactivation readiness;
- rollback/recovery.

### Security & Privacy
- capability model;
- access/policy model;
- Secrets Vault operator guide;
- protected files;
- privacy exporter/eraser behavior;
- support bundle/redaction;
- security reporting;
- backup encryption/recovery caveats.

### Developer SDK / API
- public contracts only;
- Platform API/versioning;
- module manifests;
- Data Source/Field/Builder adapters;
- Abilities/Events;
- Job/action contracts;
- source migration adapters;
- certification suite;
- deprecation policy.

### Troubleshooting
Organized by user-observable symptom + normalized error code, not internal class names.

### Release & Upgrade Notes
- changelog;
- migration notes;
- breaking/deprecation notices;
- Free↔Pro compatibility;
- known issues;
- rollback/recovery guidance.

---

# 2. Documentation identity/versioning

Every article has:
- stable article ID/topic key;
- slug/title;
- category/module;
- language;
- applicable WPE Product/Platform/module version range;
- last reviewed date;
- owner/team;
- source repository/reference;
- deprecation/replacement article where relevant.

Admin contextual help links by stable article ID/topic key where possible. Service resolves current URL/version.

A docs URL changing must not require a plugin update merely to fix every help link.

---

# 3. Local vs remote docs

## Bundled local docs
Keep small, version-critical help available offline:
- quick start;
- recovery path;
- account/service disclosure;
- compatibility requirements;
- critical migration/upgrade note references;
- support/diagnostics privacy overview.

## Remote docs
Full current documentation/search can live on trusted WPE docs service/domain.

Plugin consumes only structured search/link metadata or a separately accepted safe content schema. No arbitrary remote script/style/HTML execution in wp-admin.

Remote outage does not remove local critical help.

---

# 4. Documentation quality gate

A module cannot be public-release-ready without:
- module overview;
- all public screens/options documented;
- examples;
- capability/security implications;
- known limits;
- migration/export behavior;
- error/troubleshooting references;
- release support/version range;
- developer docs for declared public extension contracts.

Screens/options may be generated into docs tables from accepted schemas later, but generated text is reviewed; docs cannot blindly mirror internal implementation names.

---

# 5. In-product documentation UX

## Documentation Home
- search;
- suite/module filters;
- Getting Started;
- popular/current-version topics;
- recent relevant release notes;
- offline status if remote search unavailable.

## Contextual Help Drawer/Popover candidate
On WPE screen:
- current topic summary;
- link to exact article;
- related error/help topics;
- copy diagnostic/error reference when appropriate.

Do not embed a full remote website/iframe inside wp-admin.

## Empty/error states
Empty states link directly to the relevant first-task doc rather than generic documentation homepage.

---

# 6. Support information architecture

## Support entry points
- WPE Home;
- Support Center;
- contextual Help → Contact Support;
- Diagnostics after unresolved issue;
- account/service domain.

Support availability/response SLA can vary by commercial plan, but local UI must show only verified service data and never fabricate priority.

## Ticket categories candidate
- Installation / Activation;
- Free↔Pro / Account / License;
- Content Model;
- Data / Query;
- Dashboard / Experience;
- Membership / Access;
- Forms / Workflow / Cron;
- Notifications / Email / Chat;
- REST / Connections;
- Import / Migration;
- Backup / Restore / Reset;
- Security / Protector;
- Compatibility / Integration;
- Performance;
- Bug Report;
- Feature Request;
- Billing / Account (may hand off to service commerce support).

Service may refine this taxonomy, but module IDs are transmitted separately from user-facing category so category renaming does not break diagnostics.

## Status candidates
- `open`
- `waiting_for_support`
- `waiting_for_customer`
- `resolved`
- `closed`

Status is service source of truth.

## Priority
If offered:
- normal;
- high;
- critical/production-down only under defined support-plan criteria.

Users must not be encouraged to mark every ticket critical; service policy controls eligibility.

---

# 7. Ticket creation UX

Fields:
- subject;
- category;
- affected module(s);
- issue type;
- description;
- reproduction steps optional but prompted for bugs;
- expected/actual result optional structured prompts;
- site/environment summary preview;
- WPE version auto-detected;
- related normalized error/correlation IDs;
- attachments;
- diagnostics bundle toggle/select categories;
- consent/disclosure summary.

Auto-filled technical metadata remains editable/previewable where privacy permits.

No automatic DB dump/full logs/source-code upload.

---

# 8. Bug report quality

For bug-category ticket, encourage:
- exact WPE Free/Pro versions;
- WP/PHP versions;
- affected module;
- steps;
- expected vs actual;
- normalized error code/correlation ID;
- recent upgrade/migration context;
- whether reproducible with relevant integration disabled;
- screenshots/files if safe.

Do not demand sensitive customer data just to submit a ticket.

---

# 9. Security vulnerability reporting

Security reports require a distinct private channel/process, not ordinary public forum/feature-request discussion.

Docs provide:
- security contact/channel;
- what details to include;
- responsible disclosure expectations;
- acknowledgement/triage process;
- supported versions/security-fix policy once defined.

Do not encourage public disclosure of unpatched exploitable issues. WordPress.org's current plugin guidance also recommends private/responsible reporting for serious plugin vulnerabilities.

A future vulnerability disclosure/security.txt/CVE process needs its own operational policy; this document does not promise bounty/CVE handling.

---

# 10. Support diagnostics contract

Ticket can reference diagnostics by:
- local report ID;
- uploaded support bundle attachment;
- correlation IDs.

Before upload:
- categories selected;
- redaction performed locally;
- exact summary preview;
- explicit approval.

Support agent never gets an implied permanent remote-admin/backdoor into the site from a ticket. Any future remote-support access mechanism would require separate high-risk security architecture and explicit per-session consent.

---

# 11. Changelog structure

Every WPE release has machine/human-readable release metadata.

Categories:
- **Added**
- **Changed**
- **Fixed**
- **Security**
- **Deprecated**
- **Removed**
- **Migration**
- **Compatibility**
- **Known Issues**

Each meaningful entry states user impact rather than only internal commit/class names.

Example good style:
- `Fixed: Membership revocation now invalidates entitlement cache before the next protected request.`

Avoid:
- `Fixed membership.php`.

---

# 12. Release note layers

## Changelog
Compact exhaustive user-visible changes.

## Release Notes
Narrative important changes, screenshots/examples, upgrade considerations.

## Migration Notes
Schema/data/config changes and expected duration/recovery.

## Security Advisory
Separate controlled disclosure for security issue where appropriate.

## Developer Upgrade Notes
SDK/API/Ability/Event/deprecation changes.

Do not force all information into one giant changelog entry.

---

# 13. WordPress.org Free release truth

WordPress.org Directory release/readme behavior is tied to `readme.txt` Stable Tag and corresponding SVN tag. Release process must verify:
- plugin header version;
- Free package version;
- `readme.txt` Stable Tag;
- SVN tag;
- bundled changelog;
- release artifact checksum;
- compatibility fields;
- upgrade notice where required;
all point to the same intended release.

A marketing announcement must not precede/claim a version that is not actually available as the verified release artifact except explicitly labeled preview/beta.

---

# 14. Pro release truth

Externally distributed Pro release needs equivalent release identity:
- Pro Product Version;
- compatible Platform API range;
- package checksum/signature once architecture accepted;
- bundled changelog;
- migration versions;
- release metadata on service;
- rollback/recovery information;
- Free version requirements.

Free and Pro may release independently only inside an explicitly supported compatibility window.

---

# 15. Security release handling

Candidate process:
1. private intake;
2. reproduce/triage;
3. severity assessment;
4. develop/test fix after development authorization/process exists;
5. affected-version matrix;
6. release packages;
7. verify artifact availability/update path;
8. publish appropriate advisory/changelog detail;
9. notify users according to severity/channel;
10. postmortem/hardening if warranted.

Do not publish exploit-enabling detail before users have a practical verified fix path when responsible disclosure calls for delay.

---

# 16. Deprecation communication

For a deprecated public feature/contract:
- first deprecated version;
- replacement;
- behavior during deprecation;
- compatibility-only stage;
- earliest removal policy/release;
- migration/tooling;
- affected definitions/integrations;
- developer warning location.

In-product notices are contextual, not global nag spam.

---

# 17. Known Issues registry

Each release can list known issues with:
- issue ID;
- affected versions/modules;
- severity/impact;
- trigger/conditions;
- workaround if safe;
- fixed-in target only when committed/verified;
- link to status/support article.

Known issues must not disclose exploitable security details prematurely.

---

# 18. Documentation/support observability

Measure privacy-safe product signals if later opted/allowed:
- docs searches with no user content if telemetry explicitly opted in;
- article helpful/not helpful feedback;
- support ticket category/module frequency;
- time-to-first-response/resolution from service;
- repeated normalized error categories;
- docs gaps causing support volume.

No hidden telemetry. Operational service metrics on WPE's own support/docs infrastructure are distinct from secretly tracking plugin users.

---

# 19. Content ownership/workflow

Every doc/release article should have an owner and review trigger:
- module behavior changed;
- UI option changed;
- compatibility range changed;
- security model changed;
- source adapter recertified;
- deprecated API changed;
- support issue indicates article stale.

Documentation update is part of Definition of Done for public behavior changes.

---

# 20. Development gate

This is product/service planning only. No docs service, ticket API, release automation, WordPress.org SVN workflow, security-response automation or remote UI client implementation is authorized before explicit owner development consent under ADR-0014.