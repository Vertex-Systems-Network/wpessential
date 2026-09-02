# WPEssential — WordPress.org Plugin Check & Directory Compliance

Status: **MANDATORY QUALITY / RELEASE POLICY**  
Last reviewed against official sources: **2026-09-02**

This policy extends `AGENTS.md`, `CONTRIBUTING.md`, `docs/QUALITY-GATES.md`, the engineering execution governance, and the release/recovery governance. It does not replace stronger WPEssential security, architecture, ownership, accessibility, compatibility, migration, or recovery requirements.

## 1. Source authorities

For WordPress.org-facing code and packages, use current official/primary sources in this order:

1. WordPress Plugin Check: https://github.com/WordPress/plugin-check
2. Detailed Plugin Guidelines: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
3. Plugin Handbook / Directory documentation: https://developer.wordpress.org/plugins/wordpress-org/
4. Common review issues: https://developer.wordpress.org/plugins/wordpress-org/common-issues/
5. WordPress Coding Standards and security documentation.

These sources are versioned/changing external rules. Recheck them before a stable WordPress.org submission or when Plugin Check materially changes.

## 2. Mandatory Plugin Check gate

Official WordPress Plugin Check is a required release/package evidence gate for any candidate intended for WordPress.org distribution.

For code or package changes that can affect WordPress.org acceptance, run the applicable Plugin Check checks before merge/release certification when the execution environment supports them. At a stable-release boundary, both applicable **static** and **runtime** checks must be executed against the exact packaged candidate, not merely against a source-tree approximation.

A grep, manual review, PHPCS-only run, or a previous Plugin Check run is supplemental evidence and does not substitute for exact-candidate Plugin Check evidence.

If Plugin Check cannot execute, report `NOT VERIFIED` with the exact reason. Do not claim WordPress.org readiness and do not convert the missing gate into a documentation-only PASS.

Do not disable, ignore, filter out, or downgrade a valid Plugin Check finding merely to obtain a green result. Any intentional exception must identify the exact check/finding, explain why it is a false positive or non-applicable, cite authoritative evidence, record reviewer/owner acceptance when required, and preserve an alternative verification path.

## 3. Plugin Check coverage that WPEssential must preserve

Treat the current default Plugin Check repository as an evolving minimum. Current categories include, among others:

- internationalization usage;
- PHP error-reporting behavior;
- script/style size and enqueue performance;
- code obfuscation and plugin content/file types;
- plugin header fields and `readme.txt`;
- late escaping and safe redirects;
- plugin updater behavior;
- uninstall behavior;
- Plugin Review PHPCS rules;
- direct database access/query review;
- performant `WP_Query` parameters;
- resource enqueue scope/footer/non-blocking behavior;
- localhost/development references;
- unfiltered-upload behavior;
- trademarks;
- offloading/writing files;
- Settings API sanitization;
- prefixing/namespacing;
- minified-file/source expectations;
- direct-file access protection;
- external admin-menu links;
- WordPress function compatibility;
- AI-provider integration checks where applicable.

WPEssential-specific tests may be stricter. Passing Plugin Check never authorizes bypassing WPEssential Policy/Ability ownership, Multisite isolation, data integrity, package, or security gates.

## 4. WordPress.org Directory rules — mandatory engineering interpretation

### Licensing and third-party contents

- Everything shipped to the WordPress.org directory — code, data, images, libraries, fonts/assets where applicable — must be GPL-compatible.
- Verify licenses and third-party service/API terms before inclusion.
- Keep repository license, plugin header, Composer/package metadata, and `readme.txt` declarations consistent.
- Do not copy proprietary/nulled code or assets into the distributable.

### Human-readable and build-source transparency

- Shipped code must remain mostly human-readable.
- When minified/compiled files are distributed, make the corresponding non-compressed source and build instructions publicly available and easy to find; document the development/source location in `readme.txt` as required by WordPress.org guidance.
- Do not use obfuscation to conceal behavior.

### No trialware / correct service boundaries

- Do not ship locally implemented functionality that becomes disabled merely because a trial/quota/payment expires.
- Paid external services are allowed only when they provide substantive remote service functionality and are documented appropriately.
- A remote license/key validator alone does not transform local premium functionality into permitted SaaS.
- Free and Pro packaging must preserve the approved WPEssential edition architecture and WordPress.org rules simultaneously.

### Privacy, tracking, and external transmission

- No telemetry/tracking without explicit user consent where WordPress.org requires consent.
- Clearly disclose material external services/data transmission in the WordPress.org-facing documentation as required.
- Do not silently transmit site/user content, identifiers, diagnostics, prompts, or usage data to third parties.
- Never transmit secrets unnecessarily.

### No remote executable code

- Do not download, install, update, or execute plugin/theme/add-on code from third-party systems through the WordPress.org-hosted plugin.
- Do not use remote systems to deliver executable code that belongs in the plugin package.
- External APIs/services may return data; they must not become an alternate executable-code delivery channel.
- Use WordPress-bundled libraries where required instead of replacing them with externally hosted copies.

### Links, credits, admin UX, and promotions

- No unsolicited public-site credits/links.
- Do not hijack the WordPress dashboard with excessive notices, nags, redirects, or promotions.
- External admin-menu links and upsells must satisfy current Plugin Directory guidance and Plugin Check.
- `readme.txt` and WordPress.org-facing metadata must not contain spam or keyword stuffing.

### Filesystem, updater, and uninstall behavior

- Do not implement a private updater for the WordPress.org-distributed plugin that bypasses the directory update mechanism.
- Filesystem writes/offloading must use WordPress-safe APIs and have a legitimate plugin function; never write arbitrary executable payloads.
- Uninstall/deactivation behavior must match documented data-retention policy and must not destroy user data unexpectedly.
- Direct access to shipped PHP source must fail closed under the accepted WPEssential `ABSPATH` rule.

### Database and settings safety

- Direct database access/query behavior must satisfy Plugin Check and WPEssential ownership/migration rules; prefer WordPress APIs where appropriate.
- Dynamic identifiers and values require strict validation, allowlisting, preparation/escaping, and ownership boundaries.
- Settings and request data require server-side validation/sanitization; output requires context-appropriate escaping.

### Naming, trademarks, and releases

- Respect WordPress and third-party trademarks/project names in plugin name, slug, branding, copy, and metadata.
- Increment the plugin version for every stable release.
- Keep plugin header version, package version, changelog, `Stable tag`, supported WordPress/PHP metadata, and release evidence synchronized.
- WordPress.org SVN is a release/distribution repository, not the normal high-frequency development branch.
- Submit a complete functional plugin, not a placeholder or incomplete shell represented as a stable release.

## 5. Required evidence at stable release

A WordPress.org release candidate is not ready until all applicable evidence is attached to the exact package/head:

1. Official Plugin Check static checks — PASS or documented accepted non-applicable/false-positive findings.
2. Official Plugin Check runtime checks — PASS or documented accepted non-applicable/false-positive findings.
3. WordPress Coding Standards / Plugin Review PHPCS — PASS.
4. WPEssential FAST/FULL gates applicable to release — PASS.
5. Supported WordPress/PHP/database compatibility evidence — PASS.
6. Clean install, activation, deactivation, uninstall/data-retention behavior — VERIFIED.
7. Upgrade/migration/recovery paths — VERIFIED where applicable.
8. Production package allowlist/content audit — PASS.
9. License/source/build disclosure audit — PASS.
10. `wpessential.php`, `readme.txt`, Composer/package and release metadata consistency — PASS.
11. No secrets, development-only files, tests, internal planning artifacts, or Pro-only source accidentally included in the Free WordPress.org package.
12. Required release authorization — GRANTED.

Any unresolved blocking Plugin Check or Directory-guideline issue makes the candidate `NOT WORDPRESS.ORG READY`.

## 6. Pull request expectations

Any PR that changes plugin bootstrap/header, `readme.txt`, packaging, update/uninstall behavior, filesystem writes, admin links/notices, telemetry/external services, executable-code loading, database behavior, settings handling, enqueue behavior, minified/build artifacts, licensing, trademarks, or WordPress.org release metadata must explicitly state:

- WordPress.org / Plugin Check impact;
- which Plugin Check evidence ran on the exact head/package;
- any finding and its disposition;
- whether the change affects stable-release readiness.

## 7. Audit language

Use only evidence-backed labels:

- `PLUGIN CHECK VERIFIED` — required applicable checks actually executed successfully on the stated exact candidate.
- `PLUGIN CHECK NOT VERIFIED` — checks were not executed or runtime/package evidence is unavailable.
- `WORDPRESS.ORG GUIDELINE REVIEWED` — current official guidance was reviewed for the affected scope; this is not a Plugin Check PASS.
- `NOT WORDPRESS.ORG READY` — a blocking rule, package, metadata, authorization, or verification gap remains.
