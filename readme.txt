=== WPEssential ===
Contributors: wpessential
Tags: custom post types, custom taxonomy, cpt, post types, taxonomy
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 3.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Create custom post types and taxonomies without code. Visual builder for labels, permalinks, REST API, archives, block templates and more.

== Description ==

**WPEssential** is the easiest way to create **custom post types** and **custom taxonomies** in WordPress without writing code. Build a portfolio, a product catalogue, events, team members, recipes or any other content type from one clear admin screen, then organise it with your own categories and tags.

Every setting WordPress offers for `register_post_type()` and `register_taxonomy()` is in the builder, explained in plain words, with safe defaults. WPEssential validates each change before you save it and shows you the exact arguments WordPress will receive.

The builders and core screens work locally and need no account or licence. Only the Demo Library, and the account and licence features you choose to use, connect to wpessential.org (see External services).

= Custom Post Type builder =

* Labels generated for you from the singular and plural name, and editable one by one.
* Menu position and Dashicon, public and admin visibility, search and navigation menus.
* Supports: title, editor, featured image, excerpt, comments, revisions, custom fields, page attributes, post formats and more.
* Archives, permalinks and rewrite slug, with a choice of keeping or hiding the base slug.
* REST API and block editor settings, including a **block template** and **template lock** for new posts.
* Capabilities, export and delete-with-user settings.
* A preview of the compiled `register_post_type()` arguments, so you see exactly what WordPress gets.

= Custom Taxonomy builder =

* Hierarchical (like categories) or flat (like tags) taxonomies, attached to any post types.
* Labels, visibility, admin columns, quick edit and the tag cloud.
* Permalinks, rewrite slug and hierarchical URLs.
* REST API settings, term counting and term sorting.
* **Import from Custom Post Type UI**: preview your existing taxonomies and bring them across in one step.

= Also included =

* **Dashboard and Modules**: see what is active and switch modules on or off.
* **System Status**: WordPress, PHP, runtime and module diagnostics to share when you ask for help.
* **Demo Library**: browse ready-made demo sites.
* **Docs**: open the documentation from inside the admin.
* **AI Connect** (optional): an MCP endpoint for AI assistants, off until an administrator turns it on.

= Fast and safe by design =

* Post types and taxonomies are compiled when you save them, so normal page loads read one small compiled list instead of every saved definition.
* Capability and policy checks always run on the server; hiding a button is never the permission check.
* All admin requests use one typed, allow-listed AJAX gateway, and nonces are checked separately from permissions.
* Diagnostics and audit data are bounded and never store secrets.

= WPEssential Pro =

Custom fields, relationships between posts, admin columns and more are in the separate **WPEssential Pro** add-on, sold and delivered from [wpessential.org](https://wpessential.org/pricing/). The free plugin never downloads or installs Pro.

== External services ==

The free plugin talks to the WPEssential service at wpessential.org only in these cases:

* **Demo Library**: when an administrator opens the Demo Library screen, the plugin downloads the list of available demos from `https://wpessential.org/wp-json/wpessential-server/v1/demos`. No personal or site data is sent.
* **Account**: only when an administrator chooses to sign in to or create a WPEssential account. The name, email address and password they type are sent to wpessential.org to create the account or sign in. When they then connect the site, the site's address (home URL) is sent, and wpessential.org may return a Pro licence that the plugin activates for this site.
* **Licence**: when an administrator enters a Pro licence key, or a connected account assigns one. The key and the site's domain are sent to wpessential.org to activate, check or deactivate the licence.

Documentation and support links open wpessential.org in the browser only when clicked.

The service is provided by WPEssential: [Terms of Service](https://wpessential.org/terms/), [Privacy Policy](https://wpessential.org/privacy-policy-2/).

== Installation ==

1. In WordPress, go to **Plugins → Add New**, search for **WPEssential** and click **Install Now**. Or download the zip and upload it from **Plugins → Add New → Upload Plugin**.
2. Activate **WPEssential**.
3. Open **WPEssential** in the admin menu and create your first post type or taxonomy.

Requirements: WordPress 6.9 or newer and PHP 8.2 or newer. If the site does not meet them, WPEssential stops safely and shows an admin notice instead of loading.

== Frequently Asked Questions ==

= Do I need to write code to create a custom post type? =

No. Fill in the name, choose the options you need and save. WPEssential registers the post type for you.

= Do I need an account or licence to use the free plugin? =

No. The Custom Post Type and Taxonomy builders and the other free screens work without an account or a licence. The only free feature that needs a connection to wpessential.org is the Demo Library, which downloads its list of demos when you open it.

= What happens to my content if I deactivate WPEssential? =

Your post types and taxonomies stop being registered while the plugin is inactive, the same as with any plugin that registers them. Your posts and terms stay in the database and appear again when you reactivate WPEssential.

= Can I move from Custom Post Type UI? =

Yes. The Taxonomy builder can preview and import taxonomies from Custom Post Type UI.

= Does it work with the block editor and the REST API? =

Yes. Turn on "Show in REST API" for a post type to use it in the block editor, and set a block template and template lock for new posts.

= Is it fast on large sites? =

Yes. Definitions are compiled when you save them, so normal page loads read one small compiled list.

= Where can I get help? =

Read the [documentation](https://wpessential.org/docs/) or ask on the WordPress.org support forum for this plugin. Project website: https://wpessential.org

== Screenshots ==

1. Dashboard with active modules and quick actions.
2. Custom Post Type builder: names, labels and menu settings.
3. Custom Post Type builder: supports, visibility, REST API and archives.
4. Custom Post Type builder: block template, template lock and the compiled arguments preview.
5. Custom Taxonomy builder: post types, hierarchy, labels and permalinks.
6. Import taxonomies from Custom Post Type UI.
7. System Status diagnostics.
8. Demo Library.

== Changelog ==

= 3.0.0 =

* New: Custom Post Type builder with block template, template lock and a compiled arguments preview.
* New: Custom Taxonomy builder with permalinks, term counting and sorting, and Custom Post Type UI import.
* New: Dashboard, Modules, System Status, Demo Library and Docs screens.
* New: optional WPEssential account, Pro licence connection and AI Connect (off by default).

== Upgrade Notice ==

= 3.0.0 =

First release of WPEssential with the Custom Post Type and Taxonomy builders.
