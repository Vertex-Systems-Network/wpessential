=== WPEssential ===
Tags: automation, structured-data, workflows, integrations, developer-tools
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 0.1.0-dev
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Modular WordPress application platform for structured data, automation, integrations, admin tooling, workflows, and AI-ready operations.

== Description ==

WPEssential is a modular WordPress application platform designed to provide one governed foundation for structured data, administration, automation, integrations, workflows, security-aware operations, and AI-ready tooling.

The project is built around explicit module ownership and shared platform contracts rather than many disconnected mini-frameworks. Its architecture includes typed definitions, policy and capability checks, abilities, events, scoped persistence, background-job contracts, integrations, compiled WordPress registrations, audit infrastructure, and runtime diagnostics.

WPEssential is currently on its platform-foundation development line. Business-facing modules are introduced only after their shared dependencies and executable evidence gates are ready. Features that are still under development are not represented here as production-certified functionality.

Project website: https://wpessential.org

== Architecture principles ==

* One canonical owner for each semantic operation and data source.
* WordPress Multisite scope is explicit; request-provided IDs are not authorization.
* UI hiding is never authorization. Capability and Policy checks remain server-side.
* Dynamic WordPress registrations use compile-on-write runtime projections rather than scanning large historical definition populations on every request.
* AJAX requests use one typed, allowlisted WPEssential gateway rather than scattered arbitrary handlers.
* Nonce verification is centralized and remains separate from capability, Policy, ownership, and site-scope authorization.
* Audit and Runtime Observatory data are bounded and secret-aware; diagnostic data is not business truth.
* External provider outcomes that are unknown are not silently treated as success or failure.

== Installation ==

This is currently a development-line package and is not yet published as a stable WordPress.org release.

For an approved development build:

1. Install the packaged WPEssential plugin directory into `/wp-content/plugins/`.
2. Ensure the production package contains its Composer autoloader and required runtime dependencies.
3. Activate WPEssential from the WordPress Plugins screen.
4. WPEssential will stop safely and show an administrative compatibility notice when its required runtime baseline is not available.

Minimum current development baseline:

* WordPress 6.9 or newer.
* PHP 8.2 or newer.
* MySQL 8.0+ or MariaDB 10.11+ is the project database baseline for platform evidence.

== Frequently Asked Questions ==

= Is WPEssential a collection of unrelated plugins? =

No. WPEssential is planned as one modular application platform. Modules compose shared contracts for data ownership, authorization, jobs, integrations, audit, WordPress registration, and other cross-cutting concerns.

= Is every planned WPEssential module already production ready? =

No. The project uses milestone and evidence gates. The current development line is building and certifying the shared platform foundation before business-module implementation is promoted as ready.

= Does a valid nonce authorize an operation? =

No. A nonce is CSRF protection. WPEssential separately evaluates authentication, capability, Policy, ownership, and site/network scope where applicable.

= Does WPEssential process every saved custom definition on every request? =

No. Dynamic WordPress registrations are designed around compile-on-write active generations so ordinary runtime loading can consume a bounded compiled projection.

= Where is the project website? =

https://wpessential.org

== Changelog ==

= 0.1.0-dev =
* Platform-foundation development line.
* Added governed kernel, definitions, policy/abilities/events foundations, persistence contracts, compiled-registration architecture, audit/job/integration foundations, and machine-enforced engineering contracts.
* This development tag is not a stable WordPress.org release declaration.

== Upgrade Notice ==

= 0.1.0-dev =
Development build only. Review project release notes and migration evidence before using a future packaged release on a production site.
