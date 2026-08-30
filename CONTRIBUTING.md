# Contributing to WPEssential

WPEssential is a modular WordPress application platform maintained by **VSN Team**.

Project website: https://wpessential.org

## Development baseline

Current implementation baseline:

- WordPress 6.9+
- PHP 8.2+
- MySQL 8.0+ or MariaDB 10.11+
- Composer with PSR-4
- Node.js 24 LTS for approved frontend/build work

Repository planning and implementation are milestone-gated. A passing unit or smoke test does not authorize a production deployment or a destructive live-site/provider operation.

## Mandatory engineering conventions

All production contributions must preserve the accepted WPEssential engineering contract:

- Namespace: `WPEssential`
- Canonical PSR-4 production source root: `frameworks/`
- Global functions: `wpessential_*`
- Constants: `WPE_*`
- Custom filters: exactly `wpesential/apply_*`
- Custom actions: `wpessential/hook_*`
- The asymmetric `wpesential` filter spelling is intentional public API and must not be silently corrected.
- `WPE_VERSION`, the canonical AJAX action and the canonical nonce action must be constants.
- Production PHP files must fail closed on direct access with an `ABSPATH` guard.
- Do not add a parallel production `src/` runtime tree.

## Direct-access security guard

Every PHP file shipped as production plugin source must contain a direct-access guard:

```php
if (!defined('ABSPATH')) {
    exit;
}
```

For namespaced PHP files, keep PHP namespace syntax valid: the namespace declaration comes before the executable guard. Test/bootstrap entrypoints may define `ABSPATH` before loading production code; production source must not add a testing bypass around the guard.

The engineering validator enforces this rule.

## AJAX and nonce rules

WPEssential uses one canonical WordPress AJAX front door.

- Do not register scattered feature-specific `wp_ajax_*` handlers.
- Requests enter through the canonical WPEssential action and provide an allowlisted typed `type`.
- Missing or unknown request types fail safely.
- Request data must never select an arbitrary class or method.
- Use the shared nonce service for the operation scopes `apply`, `create`, `update`, `reset`, and `delete`.
- A valid nonce is CSRF protection only. Authentication, capability, Policy, ownership, and site/network scope checks are separate requirements.

## Definitions and WordPress registrations

Do not create request-time designs that scan and process the full historical population of CPT, taxonomy, metabox, settings-page, or comparable definitions.

Mutation paths normalize and compile definitions into bounded active generations. Runtime paths consume the active compiled projection. Changes to compiled-registration storage must preserve scope isolation, immutable generation history, transactional publication, checksum validation, corruption recovery, and generation high-watermark semantics.

## Data ownership and module boundaries

- Each semantic operation has one canonical owner.
- Do not create a private shadow engine when a shared WPEssential service owns the concern.
- Modules do not reach directly into another module's private storage.
- Search/index/cache projections are derived state, not source truth or authorization.
- Provider results and transport success do not become business truth unless the owning contract says so.
- Multisite network/site identity must be resolved by trusted runtime context, not accepted from request parameters as authority.

## Database migrations

- Use registered code-defined migrations; do not expose an arbitrary SQL migration interface to users.
- Prefer additive/non-destructive changes.
- A destructive migration requires an explicit recovery plan and separate applicable authorization.
- Migration state must be durable and idempotent.
- Never report a failed or partial migration as applied.
- Production/live database migration remains separately privileged from ordinary source development.

## Audit and observability

Audit is append-oriented explanation evidence, not business truth and not generic analytics.

Runtime Observatory/debug instrumentation should support correlation, class/data flow, checkpoints, timings, and the last-successful-to-failed call boundary while remaining bounded and secret-aware.

Never persist passwords, API secrets, reusable authorization headers/cookies, private keys, card secrets, reset tokens, or private signed URLs in ordinary logs, audit metadata, or debug graphs.

## Tests and CI

Before a contribution can be accepted, applicable gates must remain green:

- Composer metadata validation
- canonical architecture validator
- engineering-contract validator
- PHP syntax checks
- relevant smoke suites
- applicable MySQL/WordPress integration fixtures
- performance/evidence gates when the change makes a performance claim

Do not convert a failed executable gate into a documentation-only PASS.

## WordPress.org release checklist

Before a stable WordPress.org submission or update:

1. Synchronize plugin header version, package version, changelog, and WordPress.org stable tag.
2. Replace development versions such as `0.1.0-dev` with the explicitly approved release version.
3. Reconfirm `Requires at least`, `Requires PHP`, and `Tested up to` against the actual release evidence.
4. Populate `Contributors:` in `readme.txt` with the real WordPress.org usernames of approved contributors. Do not invent usernames.
5. Keep `Plugin URI` and `Author URI` aligned to `https://wpessential.org` unless an approved release decision changes them.
6. Keep the repository license, plugin header, Composer metadata, and `readme.txt` license declarations consistent.
7. Run WordPress.org readme/header validation and all WPEssential release gates.
8. Build the production package from an explicit allowlist. Do not assume tests, CI files, internal planning documents, fixtures, or development-only tooling belong in the distributed ZIP.
9. Verify every shipped PHP source file has the required `ABSPATH` direct-access guard.
10. Verify the packaged Composer autoloader/runtime dependencies are present and no development-only dependency is shipped accidentally.
11. Verify migrations on supported clean-install and upgrade fixtures before any production release.
12. Do not publish, tag, merge, or deploy a stable release unless the applicable release authorization has been granted.

## Reporting issues

For ordinary bugs, use the repository issue workflow with reproducible steps and sanitized diagnostics. Do not post credentials, private customer data, tokens, payment details, or reusable exploit secrets in a public issue. For sensitive security findings, contact the maintainers through the project-controlled channel published at https://wpessential.org rather than disclosing exploitable secrets publicly.
