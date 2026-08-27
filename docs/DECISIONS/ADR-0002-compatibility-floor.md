# ADR-0002 — WordPress / PHP Compatibility Floor

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27  
Static research refreshed: 2026-08-27

## Context

WPEssential is a new AI-native platform, not a legacy plugin required to preserve decade-old runtime support. Current WordPress is 7.1. WordPress core supports a wider PHP range than a new application platform necessarily should.

The WordPress Abilities API that WPEssential plans to use as a first-class typed action contract is available only in WordPress 6.9+. Supporting older WordPress versions would either remove that architectural primitive or require a compatibility layer that increases maintenance/testing burden.

PHP support is a commercial tradeoff: a lower minimum increases installable market size but keeps WPEssential tied to older runtimes and expands the security/compatibility matrix. A higher minimum improves security/maintainability but excludes sites that have not upgraded hosting.

Static evidence is recorded in `docs/RESEARCH/COMPATIBILITY-UI-TOOLCHAIN-STATIC-RESEARCH.md`.

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

**Updated preferred minimum candidate: PHP 8.3 from the first production codebase.**

This replaces the earlier preference to start development/beta on PHP 8.2.

Why the recommendation changed:
- WordPress currently recommends PHP 8.3+;
- PHP 8.2 security support ends on 2026-12-31, only months after this planning date;
- PHP 8.3 security support runs through 2027-12-31;
- starting a new long-lived platform on a soon-to-be-EOL runtime would likely force an early minimum-version raise;
- PHP 8.4 and 8.5 can remain forward-compatibility CI targets while 8.3 preserves more market reach than making 8.4/8.5 the minimum immediately.

## Current official-platform facts

At this research date:
- WordPress Requirements recommends PHP 8.3+;
- WordPress 7.0/7.1 technically supports PHP 7.4+;
- WordPress 6.9/7.0/7.1 support current PHP 8.x branches according to the Core compatibility table;
- WordPress 6.9 is the minimum for native Abilities API availability.

WPEssential intentionally does not inherit WordPress's broad minimum PHP range automatically.

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

## Acceptance evidence required

Before this ADR becomes Accepted:

1. collect current WordPress/PHP usage data close to beta/release;
2. verify selected Composer dependencies across PHP 8.3/8.4/8.5;
3. create install/activation/CI proof on WordPress 6.9 and current stable;
4. verify page-builder/WooCommerce/integration minimums do not impose stricter requirements unexpectedly;
5. estimate installable-market impact of PHP 8.3 versus older candidate runtimes;
6. confirm PHP security-support windows for the intended public launch date;
7. verify plugin header, Composer platform requirement, CI and release docs can be kept automatically consistent.

These runtime checks are executable development/research-spike work and require explicit owner consent under ADR-0014 before being performed.

## Consequences if accepted as WP 6.9 + PHP 8.3

- no pre-Abilities compatibility implementation;
- smaller, more secure support matrix than legacy compatibility;
- some older hosting environments cannot activate WPEssential;
- activation must fail gracefully with a precise requirements notice;
- PHP 8.4/8.5 become mandatory forward-compatibility CI targets;
- dependency choices must not silently raise the PHP minimum beyond the documented floor;
- plugin header/Composer/CI/release metadata consistency becomes a quality gate.

## Review trigger

Mandatory:
- before the first executable platform spike;
- before first public beta;
- before first stable WordPress.org release;
- whenever WordPress or PHP support policy materially changes.
