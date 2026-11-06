=== WPEssential ===
Tags: custom post types, taxonomy, custom fields, admin, developer-tools
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 3.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Build custom post types and taxonomies from the WordPress admin, with one modular platform that grows into fields, relations, admin columns and more.

== Description ==

WPEssential is a modular WordPress platform. The free plugin gives you a Custom Post Type builder and a Taxonomy builder, plus the dashboard, module manager, diagnostics and docs that every other WPEssential module builds on. Everything in the free plugin works locally; no account or licence is needed.

= What the free plugin includes =

* **Custom Post Types builder** – create and edit post types from the admin: labels, menu placement and icon, supports (title, editor, thumbnail, custom fields, page attributes, post formats and more), visibility, REST API, archives, permalinks, block template and template lock, capabilities and the compiled arguments preview.
* **Taxonomy builder** – create and edit taxonomies: labels, attached post types, hierarchy, visibility, REST API, permalinks, term counting and sorting, and import from Custom Post Type UI.
* **Dashboard and Modules** – one place to see what is active and switch modules on or off.
* **System Status** – runtime, WordPress, PHP and module diagnostics you can share when asking for help.
* **Demo Library and Docs** – browse ready-made demos and open the documentation from the admin.
* **Optional account and AI Connect** – connect a WPEssential account only if you choose to. AI Connect (an MCP endpoint for AI assistants) is off until an administrator turns it on.

Premium modules such as Custom Fields, Relations and Admin Columns come in the separate WPEssential Pro add-on, which is sold and delivered from https://wpessential.org. The free plugin never downloads or installs Pro.

= How it is built =

* Each feature has one owner and shared platform contracts, instead of many separate mini-frameworks.
* Hiding something in the UI is never the permission check: capability and policy checks always run on the server.
* Post types and taxonomies are compiled when you save them, so normal page loads read a small compiled list instead of every saved definition.
* Admin requests go through one typed, allow-listed AJAX gateway, and nonces are checked separately from permissions.
* Diagnostics and audit data are bounded and never store secrets.

Project website: https://wpessential.org

== External services ==

The free plugin talks to the WPEssential service at wpessential.org only in these cases:

* **Demo Library** – when an administrator opens the Demo Library screen, the plugin downloads the list of available demos from `https://wpessential.org/wp-json/wpessential-server/v1/demos`. No personal or site data is sent.
* **Account** – only when an administrator chooses to sign in to or create a WPEssential account. The name, email address and password they type are sent to wpessential.org to create the account or sign in.
* **Licence** – only when an administrator enters a Pro licence key. The key and the site's domain are sent to wpessential.org to activate, check or deactivate the licence.

Documentation and support links open wpessential.org in the browser only when clicked.

The service is provided by WPEssential: [Terms of Service](https://wpessential.org/terms/), [Privacy Policy](https://wpessential.org/privacy-policy-2/).

== Installation ==

1. In WordPress, go to **Plugins → Add New**, search for **WPEssential** and click **Install Now**, or upload the plugin zip from **Plugins → Add New → Upload Plugin**.
2. Activate WPEssential.
3. Open **WPEssential** in the admin menu to create your first post type or taxonomy.

Requirements: WordPress 6.9 or newer and PHP 8.2 or newer. If the site does not meet them, WPEssential stops safely and shows an admin notice instead of loading.

== Frequently Asked Questions ==

= Do I need an account or licence to use the free plugin? =

No. The Custom Post Type and Taxonomy builders and all other free features work without an account, a licence or any connection to wpessential.org.

= What happens to my post types if I deactivate WPEssential? =

They stop being registered while the plugin is inactive, the same as post types registered by any other plugin. Your posts and terms stay in the database and appear again when you reactivate WPEssential.

= Can I move my post types and taxonomies from Custom Post Type UI? =

Yes. The Taxonomy builder can preview and import taxonomies from Custom Post Type UI.

= Is WPEssential a collection of unrelated plugins? =

No. It is one modular platform. Modules share the same contracts for data ownership, permissions, background jobs, integrations, audit and WordPress registration.

= Does a valid nonce authorize an operation? =

No. A nonce only protects against request forgery. WPEssential separately checks login, capability, policy, ownership and site or network scope.

= Where is the project website? =

https://wpessential.org

== Changelog ==

= 3.0.0 =
* New: Custom Post Types builder with block template and template lock, and a preview of the compiled register_post_type() arguments.
* New: Taxonomy builder with routing, term counting and sorting, and Custom Post Type UI import.
* New: Dashboard, Modules, System Status, Demo Library and Docs screens.
* New: optional WPEssential account, licence connection for the Pro add-on, and AI Connect (off by default).
* Platform: governed kernel, definitions, policies, abilities, events, compiled registrations, audit and job foundations.

== Upgrade Notice ==

= 3.0.0 =
First release of the WPEssential platform with the Custom Post Type and Taxonomy builders.
