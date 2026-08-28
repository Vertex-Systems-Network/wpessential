# ADR-0002 — WordPress / PHP / Database Compatibility Floor

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27  
Static research refreshed: 2026-08-28

## Context

WPEssential is a new AI-native platform, not a legacy plugin required to preserve decade-old runtime support. Current WordPress is 7.1. WordPress core supports a wider PHP/database range than a new application platform necessarily should.

The WordPress Abilities API that WPEssential plans to use as a first-class typed action contract is available only in WordPress 6.9+. Supporting older WordPress versions would either remove that architectural primitive or require a compatibility layer that increases maintenance/testing burden.

PHP/database support is a commercial and security tradeoff: lower minimums increase installable market size but keep WPEssential tied to older runtimes and expand the compatibility matrix. Higher minimums improve security/maintainability but exclude sites that have not upgraded hosting.

Static evidence is recorded in `docs/RESEARCH/COMPATIBILITY-UI-TOOLCHAIN-STATIC-RESEARCH.md`. The fixed future runtime protocol is `docs/QUALITY/P001-COMPATIBILITY-FLOOR-EXECUTABLE-EVIDENCE-PROTOCOL.md` under ADR-0123.

## Updated proposed launch target

### WordPress

**Minimum candidate: WordPress 6.9**  
**Primary current/reference target: WordPress 7.1**

Rationale:
- keeps the Abilities API natively available;
- avoids maintaining a pre-Abilities compatibility layer;
- supports more than the current WordPress release;
- aligns AI/automation architecture with a real Core primitive rather than a WPEssential-only imitation.

### PHP

**Preferred minimum candidate: PHP 8.3 from the first production codebase.**

Why:
- WordPress currently recommends PHP 8.3+;
- PHP 8.2 security support ends on 2026-12-31, only months after this planning date;
- PHP 8.3 security support runs through 2027-12-31;
- starting a new long-lived platform on a soon-to-be-EOL runtime would likely force an early minimum-version raise;
- PHP 8.4 and 8.5 can remain forward-compatibility CI targets while 8.3 preserves more market reach than making 8.4/8.5 the minimum immediately.

### Database

**Final WPE database floor: not yet selected.**

Current official WordPress Requirements recommends:
- **MySQL 8.0+**, or
- **MariaDB 10.11+**.

WordPress itself retains lower absolute minimums for backward compatibility, but WPEssential does not automatically inherit those legacy floors. P-001 must compare the proposed modern minimum and current/forward MySQL/MariaDB profiles before a WPE database requirement is accepted.

The database decision must account for:
- upstream security-support runway;
- WordPress hosting reality;
- charset/collation and index behavior;
- strict SQL modes;
- migration/locking behavior;
- MySQL vs MariaDB differences;
- Multisite scale/lifecycle;
- selected WPE query/schema primitives.

## Current official-platform facts

At this research date:
- WordPress 7.1 was released on 2026-08-19 and is the current reference release;
- WordPress Requirements recommends PHP 8.3+;
- WordPress Requirements recommends MySQL 8.0+ or MariaDB 10.11+;
- WordPress 7.0/7.1 technically supports PHP 7.4+;
- WordPress 7.1 is documented compatible with PHP 7.4 through 8.5;
- WordPress 6.9 is the minimum for native Abilities API availability;
- WordPress Hosting guidance for new 7.1 installs favors currently supported modern PHP/database branches, which is distinct from Core's broad backward-compatible minimums.

WPEssential intentionally does not inherit WordPress's broad minimum runtime range automatically.

## Options considered

### PHP 8.1 minimum

Rejected as default recommendation. It is already end-of-life and is not an appropriate foundation for a new production-grade platform absent exceptional market evidence.

### PHP 8.2 minimum

Superseded as the preferred recommendation because its security-support window ends 2026-12-31. It may still be measured for installable-market impact during research, but the burden of supporting a soon-EOL runtime now outweighs the default benefit.

### PHP 8.3 minimum

Current preferred candidate:
- aligns with WordPress's recommended baseline;
- gives a materially longer support runway;
- remains broadly compatible with WordPress 6.9/7.x;
- reduces risk of an early breaking minimum-version change.

### PHP 8.4 minimum

Technically attractive, but may exclude too much of the installable market for the first public release. Keep as a required CI target and revisit after market/hosting evidence.

### WordPress older than 6.9

Not recommended because it removes the native Abilities primitive and creates a compatibility layer with ongoing test/support cost.

### Legacy database minimums

Not accepted merely because WordPress Core can still run on them. A new WPE platform floor should prefer maintained modern database branches unless executable market/compatibility evidence justifies a wider support promise.

## Acceptance evidence required

Before this ADR becomes Accepted:

1. refresh current WordPress/PHP/database versions, support lifecycles and hosting guidance close to beta/release;
2. verify selected Composer dependencies across the intended PHP matrix;
3. execute CF-01…CF-112 through the accepted P-001 protocol;
4. prove install/activation/boot on WordPress 6.9 latest maintenance and current stable/reference branches;
5. verify MySQL/MariaDB candidate floors, charset/collation/index/strict-mode and migration behavior;
6. verify page-builder/WooCommerce/integration minimums do not impose stricter requirements unexpectedly;
7. estimate installable-market impact of PHP/database floor choices;
8. confirm PHP/database security-support windows for the intended public launch date;
9. verify plugin header, Composer platform requirement, runtime guard, CI and release docs can be kept automatically consistent;
10. prove Multisite, existing-project adoption, Free↔Pro mismatch and release-artifact behavior on required cells.

These runtime checks are executable development/research-spike work and require explicit owner consent under ADR-0014 before being performed.

## Consequences if accepted as WP 6.9 + PHP 8.3

- no pre-Abilities compatibility implementation;
- smaller, more secure support matrix than legacy compatibility;
- some older hosting environments cannot activate WPEssential;
- activation must fail gracefully with a precise requirements notice before unsafe side effects;
- PHP 8.4/8.5 become mandatory forward-compatibility CI targets for the current planning snapshot;
- database support must be declared explicitly after P-001 evidence rather than inherited silently from WordPress minimums;
- dependency choices must not silently raise the PHP/database minimum beyond the documented floor;
- plugin header/Composer/runtime/CI/release metadata consistency becomes a quality gate.

## Review trigger

Mandatory:
- before the first executable platform spike;
- before first public beta;
- before first stable WordPress.org release;
- whenever WordPress, PHP or database support policy materially changes.

ADR-0123 accepts the evidence protocol only. **ADR-0002 remains Proposed until CF evidence is executed and reviewed.**
