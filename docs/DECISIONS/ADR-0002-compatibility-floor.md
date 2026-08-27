# ADR-0002 — WordPress / PHP Compatibility Floor

Status: **Proposed — Phase 0 blocker**  
Date: 2026-08-27

## Context

WPEssential is a new AI-native platform, not a legacy plugin required to preserve decade-old runtime support. Current WordPress is 7.1. WordPress core supports a wider PHP range than a new application platform necessarily should.

The WordPress Abilities API that WPEssential plans to use as a first-class typed action contract landed in WordPress 6.9. Supporting older WordPress versions would either remove that architectural primitive or require a compatibility layer that increases maintenance/testing burden.

PHP support is a commercial tradeoff: a low minimum increases installable market size but keeps WPEssential tied to end-of-life runtimes and limits language/tooling quality. A high minimum improves security/maintainability but excludes sites that have not upgraded hosting.

## Proposed launch target

### WordPress
**Minimum: WordPress 6.9**  
**Primary development/current target: WordPress 7.1**

Rationale:
- keeps the server-side Abilities API natively available;
- substantially reduces compatibility shims for an AI-native architecture;
- still supports multiple recent WordPress generations rather than current-only lock-in.

### PHP
**Preferred minimum: PHP 8.2 for the initial development/beta window, with a mandatory pre-public-release review.**

Do not mark this accepted yet.

Reasons for the 8.2 proposal:
- materially modernizes the codebase versus the legacy project;
- preserves a larger installable market than 8.3-only during initial validation;
- allows modern dependency/tooling choices.

However PHP 8.2 is near the end of its security-support lifecycle in late 2026. Therefore a production launch after that lifecycle window should strongly consider **PHP 8.3 minimum** instead of shipping new code that formally targets an unsupported runtime.

## Options considered

### PHP 8.1 minimum
Commercially broader but not preferred for a new production-grade platform because PHP 8.1 is already end-of-life. Rejected as the default recommendation unless market validation demonstrates a compelling requirement and security policy explicitly addresses it.

### PHP 8.2 minimum
Current proposal for development/beta compatibility. Better market reach, but requires a scheduled review before public launch because of its support lifecycle.

### PHP 8.3 minimum
Security/maintainability preference for a public launch if market/support data permits. WordPress currently recommends PHP 8.3. It reduces compatibility burden but excludes more existing sites.

### WordPress older than 6.9
Would increase reachable sites, but requires an Abilities compatibility abstraction or reduced AI-native behavior. Not recommended unless install-market evidence materially changes the tradeoff.

## Acceptance evidence required

Before this ADR becomes Accepted:

1. collect current WordPress/PHP usage data close to beta/release;
2. verify all selected Composer dependencies across candidate PHP versions;
3. create CI proof on WordPress 6.9, current stable and target PHP matrix;
4. verify page-builder/WooCommerce integration minimums do not impose stricter requirements unexpectedly;
5. estimate installable-market impact of PHP 8.2 vs 8.3;
6. confirm the security support window for the intended launch date.

## Consequences if accepted as WP 6.9 + PHP 8.2/8.3

- no need to emulate pre-Abilities core architecture;
- smaller support/test matrix than legacy compatibility;
- some older hosting environments cannot activate WPEssential;
- activation must fail gracefully with a clear requirements notice;
- plugin header, Composer platform constraint, CI matrix and docs must be automatically checked for consistency.

## Review trigger

Mandatory before first public beta and again before first stable WordPress.org release.
