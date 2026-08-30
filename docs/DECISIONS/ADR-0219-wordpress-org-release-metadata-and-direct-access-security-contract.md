# ADR-0219 — WordPress.org Release Metadata and Direct-Access Security Contract

Status: **ACCEPTED**  
Date: **2026-08-29**  
Scope: WP121 Platform Foundation / release metadata / shipped PHP direct-access security

## Context

The owner requires WPEssential development and future WordPress.org packaging to use a consistent public identity and a fail-closed direct-access convention across shipped PHP source.

The repository already has a broader engineering contract in ADR-0216. This ADR extends that contract with WordPress.org-facing metadata, contribution/release documentation, license consistency, and machine-enforced `ABSPATH` guards.

## Decision

### Public plugin metadata

The main plugin header uses:

- Plugin Name: `WPEssential`
- Plugin URI: `https://wpessential.org`
- Author: `VSN Team`
- Author URI: `https://wpessential.org`
- Text Domain: `wpessential`
- current development version: `0.1.0-dev`
- Requires at least: WordPress `6.9`
- Requires PHP: `8.2`
- License metadata: `GPL-3.0-or-later`

Current header description:

> Modular WordPress application platform for structured data, automation, integrations, admin tooling, workflows, and AI-ready operations.

The description explains the product without claiming unfinished business modules are production-certified.

### License consistency

The repository LICENSE is GNU GPL version 3. Plugin header and Composer metadata are aligned to `GPL-3.0-or-later`; WordPress.org `readme.txt` uses `GPLv3 or later`.

### WordPress.org documentation

Repository root contains a WordPress.org-format `readme.txt` with the current development baseline and an explicit statement that `0.1.0-dev` is not a stable directory release.

`CONTRIBUTING.md` contains engineering, CI, migration, security and WordPress.org release requirements. WordPress.org contributor usernames must be populated from real approved accounts before a stable directory submission; usernames are not invented by the implementation process.

### Direct-access guard

Every PHP file shipped as production plugin source must contain:

```php
if (!defined('ABSPATH')) {
    exit;
}
```

For namespaced PHP files, the guard appears after the namespace declaration so PHP namespace syntax remains valid. Non-namespaced shipped runtime files place the guard after the strict-types declaration/opening bootstrap section.

Test/integration entrypoints define `ABSPATH` before loading guarded production source. Production source must not contain a testing bypass for the guard.

### Machine enforcement

`tools/architecture/validate-engineering-contracts.php` now fails when:

- main plugin metadata required by this ADR drifts;
- Composer license metadata drifts from GPL-3.0-or-later;
- WordPress.org readme/contribution contract is absent or missing required markers;
- the main entrypoint lacks its direct-access guard;
- any `frameworks/**/*.php` production file lacks the canonical `ABSPATH` guard;
- a smoke/integration entrypoint fails to bootstrap `ABSPATH` before guarded source is loaded.

A deterministic maintenance transformer was used to migrate the existing production source tree. The temporary write-enabled workflow used for the one-time migration was removed after the transformation completed. The maintenance transformer remains development tooling; the permanent security guarantee is the engineering validator.

## Evidence

One-time guard application workflow run `33265809474` completed SUCCESS and produced commit:

`a8e758a70fbdc0f3cf58206bc61350b9cb80f66d` — `security: enforce ABSPATH direct-access guards`.

Permanent hosted validation run:

`33265874634` / run #138 — **SUCCESS** on GitHub-hosted Ubuntu 24.04 / PHP 8.2 / MySQL 8.4.

Job-level PASS includes:

- Composer metadata validation;
- canonical architecture validator;
- engineering validator including release metadata + ABSPATH checks;
- PHP syntax;
- existing smoke suite;
- MySQL compiled-registration integration;
- MySQL Definition/Audit persistence integration.

## Boundaries / non-certifications

ADR-0219 does **not** mean WPEssential has been submitted to or approved by WordPress.org.

It does not authorize or perform a stable release, SVN directory submission, production deployment, live database migration or destructive/provider operation.

`Tested up to` and stable tag/version must be revalidated and synchronized at each future release. Actual WordPress.org contributor usernames remain a release-time approved input.

## Consequences

Future production PHP source without the direct-access guard is a CI failure.

Future changes to public plugin identity, license metadata or WordPress.org compatibility fields require deliberate update of the release contract and executable validators rather than silent drift.
