# WPEssential

**Contributors:** wpessential\
**Tags:** custom post types, custom taxonomy, cpt, post types, taxonomy\
**Requires at least:** 6.9\
**Tested up to:** 7.1\
**Requires PHP:** 8.2\
**Stable tag:** 3.2.0\
**License:** GPLv3 or later\
**License URI:** https://www.gnu.org/licenses/gpl-3.0.html

Create custom post types and taxonomies without code. Visual builder for labels, permalinks, REST API, archives, block templates and more.

## Description

**WPEssential** is the easiest way to create **custom post types** and **custom taxonomies** in WordPress without writing code. Build a portfolio, a product catalogue, events, team members, recipes or any other content type from one clear admin screen, then organise it with your own categories and tags.

The settings you need for `register_post_type()` and `register_taxonomy()` are in the builder, explained in plain words, with safe defaults. WPEssential validates each change before you save it and shows you the exact arguments WordPress will receive.

The builders and core screens work locally and need no account or licence. Only the Demo Library, and the account and licence features you choose to use, connect to wpessential.org (see External services).

### Custom Post Type builder

- Labels generated for you from the singular and plural name, and editable one by one.
- Menu position and Dashicon, public and admin visibility, search and navigation menus.
- Supports: title, editor, featured image, excerpt, comments, revisions, custom fields, page attributes, post formats and more.
- Archives, permalinks and rewrite slug, with a choice of keeping or hiding the base slug.
- REST API and block editor settings, including a **block template** and **template lock** for new posts.
- Capabilities, export and delete-with-user settings.
- A preview of the compiled `register_post_type()` arguments, so you see exactly what WordPress gets.

### Custom Taxonomy builder

- Hierarchical (like categories) or flat (like tags) taxonomies, attached to any post types.
- Labels, visibility, admin columns, quick edit and the tag cloud.
- Permalinks, rewrite slug and hierarchical URLs.
- REST API settings, term counting and term sorting.
- **Import from Custom Post Type UI**: preview your existing taxonomies and bring them across in one step.

### Also included

- **Dashboard and Modules**: see what is active and switch modules on or off.
- **System Status**: WordPress, PHP, runtime and module diagnostics to share when you ask for help.
- **Demo Library**: browse ready-made demo sites.
- **Docs**: open the documentation from inside the admin.
- **AI Connect** (optional): an MCP endpoint for AI assistants, off until an administrator turns it on.

### Fast and safe by design

- Post types and taxonomies are compiled when you save them, so normal page loads read one small compiled list instead of every saved definition.
- Capability and policy checks always run on the server; hiding a button is never the permission check.
- All admin requests use one typed, allow-listed AJAX gateway, and nonces are checked separately from permissions.
- Diagnostics and audit data are bounded and never store secrets.

### WPEssential Pro

Custom fields, relationships between posts, admin columns and more are in the separate **WPEssential Pro** add-on, sold and delivered from [wpessential.org](https://wpessential.org/pricing/). The free plugin never downloads or installs Pro.

### Third-party libraries

The Lottie element bundles [lottie-web](https://github.com/airbnb/lottie-web) 5.12.2 (MIT), light build. Unminified source: https://github.com/airbnb/lottie-web/tree/v5.12.2/player

## External services

The free plugin talks to the WPEssential service at wpessential.org only in these cases:

- **Demo Library**: when an administrator opens the Demo Library screen, the plugin downloads the list of available demos from `https://wpessential.org/wp-json/wpessential-server/v1/demos`. No personal or site data is sent.
- **Account**: only when an administrator chooses to sign in to or create a WPEssential account. The name, email address and password they type are sent to wpessential.org to create the account or sign in. When they then connect the site, the site's address (home URL) is sent, and wpessential.org may return a Pro licence that the plugin activates for this site.
- **Licence**: when an administrator enters a Pro licence key, or a connected account assigns one. The key and the site's domain are sent to wpessential.org to activate, check or deactivate the licence.

Documentation and support links open wpessential.org in the browser only when clicked.

The service is provided by WPEssential: [Terms of Service](https://wpessential.org/terms/), [Privacy Policy](https://wpessential.org/privacy-policy-2/).

## Installation

1. In WordPress, go to **Plugins → Add New**, search for **WPEssential** and click **Install Now**. Or download the zip and upload it from **Plugins → Add New → Upload Plugin**.
2. Activate **WPEssential**.
3. Open **WPEssential** in the admin menu and create your first post type or taxonomy.

Requirements: WordPress 6.9 or newer and PHP 8.2 or newer. If the site does not meet them, WPEssential stops safely and shows an admin notice instead of loading.

## Frequently Asked Questions

### Do I need to write code to create a custom post type?

No. Fill in the name, choose the options you need and save. WPEssential registers the post type for you.

### Do I need an account or licence to use the free plugin?

No. The Custom Post Type and Taxonomy builders and the other free screens work without an account or a licence. The only free feature that needs a connection to wpessential.org is the Demo Library, which downloads its list of demos when you open it.

### What happens to my content if I deactivate WPEssential?

Your post types and taxonomies stop being registered while the plugin is inactive, the same as with any plugin that registers them. Your posts and terms stay in the database and appear again when you reactivate WPEssential.

### Can I move from Custom Post Type UI?

Yes. The Taxonomy builder can preview and import taxonomies from Custom Post Type UI.

### Does it work with the block editor and the REST API?

Yes. Turn on "Show in REST API" for a post type to use it in the block editor, and set a block template and template lock for new posts.

### Is it fast on large sites?

Yes. Definitions are compiled when you save them, so normal page loads read one small compiled list.

### How do I import a demo, and can I undo it?

Open **WPEssential → Demo Library**, choose a demo and click **Import** for Gutenberg or Elementor. If the import stops (a timeout or a closed tab), click **Resume import**. **Remove demo** deletes the pages, posts, products, images and menus the demo added and restores your previous settings; content you created yourself is not touched.

### What are the Pro elements and Pro demos?

They come with WPEssential Pro. Without it they are listed with a Pro badge so you can see what is available, and they stay locked. A 30-day Pro trial can be started from your WPEssential account.

### Will the SEO tags clash with my SEO plugin?

No. The SEO fallbacks turn themselves off when Yoast SEO, Rank Math, SEOPress, All in One SEO or The SEO Framework is active.

### Where do I add custom CSS or JavaScript?

In **Appearance → Custom Code**. Snippets survive theme updates, can be limited to devices (CSS) or placed in the head or footer (JavaScript), and an administrator can open any page with `?wpe-safe-mode=1` to load it without snippets.

### Which theme works best with WPEssential?

Any theme works. The free **Hi WPEssential** theme is designed for it: its demos, styles and elements share one design.

### Where can I get help?

Read the [documentation](https://wpessential.org/docs/) or ask on the WordPress.org support forum for this plugin. Project website: https://wpessential.org

## Screenshots

1. Dashboard with active modules and quick actions.
2. Custom Post Type builder: key, names and option groups.
3. Custom Post Type builder: visibility, editor supports, block template and template lock.
4. Custom Post Type builder: archives, rewrite slug, permalinks and capabilities.
5. Custom Taxonomy builder: key, names and option groups.
6. Custom Taxonomy builder: key migration, import and export, and the compiled arguments preview.
7. System Status diagnostics.
8. Demo Library.

## Changelog

### 3.2.0

- New: 17 elements for Gutenberg and Elementor: Breadcrumbs, Steps, Marquee, Back to Top, Social Share, Content Switcher, Comparison Table, Login / Register, Notification Bar, Cookie Notice, Animated Headline, Hotspots, Post Meta, Post Navigation, Author Box, Related Posts and Table of Contents.
- New: Swipe layout per device for grids (carousel, slider, swipe cards, card stack) to cut scrolling on phones.
- New: Container can use the header, footer, main and nav HTML tags.
- New: REST API keys and JWT sign-in for apps, and AI Connect works with application passwords.
- New: post type, taxonomy and field lists gain Trash and Disabled views, column filters and a select with search.
- Fixed: elements enabled only for Elementor now load their styles and scripts.
- Fixed: demo import can use the demo's own cart, checkout and account pages.

### 3.1.1

* Fixed: WPEssential wrote a new copy of its post type and taxonomy registrations on every page load. On busy sites the table grew without limit and could run PHP out of memory.
* Fixed: a notice about translations loading too early when saving a taxonomy.
* Changed: arrows and pager icons use the shared SVG icon set instead of text characters.

### 3.1.0

* New: Import / Export. Download your post types, taxonomies and module settings as one file and import it on another site, with a preview of what will change.
* New: Reset Tools. Reset WPEssential settings, post type and taxonomy definitions, or imported demo content after a preview and a typed confirmation. Posts and users are never touched.
* New: REST API Control. Limit the REST API to signed-in users, hide the user list, switch off namespaces for visitors and rate-limit anonymous requests.
* New: AI Connect works with Claude (claude.ai and Claude Desktop) and ChatGPT connectors through OAuth: paste the server URL, then click Allow on your site.
* New: AI Connect activity log with details for every call and a CSV download.
* New: Live Preview on WordPress.org through a Playground blueprint.
* Fixed: AI Connect sign-in on WordPress 6.8 and later.
* Fixed: a PHP warning when the theme palette has entries without a name.

### 3.0.1

* New: one-click demo import in the Demo Library for Gutenberg and Elementor, with progress, resume, cancel and Remove demo.
* New: Elements, the WPEssential blocks and Elementor widgets (29 in the free plugin); Pro elements are listed as locked until Pro is active.
* New: site tools that work with any theme: SEO fallbacks, a WPML / Polylang language switcher with the [wpe_language_switcher] shortcode, Appearance → Custom Code, and a quieter Elementor admin with the Hi WPEssential theme.
* New: Pro licences are read from your WPEssential account, with a 30-day Pro trial (also on local and staging copies of a site).
* Improved: screens show a loading skeleton while they open, and buttons that start a page load or a request show a spinner.
* Improved: links and buttons no longer show a focus ring after a mouse click; keyboard focus is unchanged.
* Fixed: locked rows in the Elements screen keep readable text contrast.

### 3.0.0

- New: Custom Post Type builder with block template, template lock and a compiled arguments preview.
- New: Custom Taxonomy builder with permalinks, term counting and sorting, and Custom Post Type UI import.
- New: Dashboard, Modules, System Status, Demo Library and Docs screens.
- New: optional WPEssential account, Pro licence connection and AI Connect (off by default).

## Upgrade Notice

### 3.2.0

17 new elements, a per-device Swipe layout for grids, REST API keys and fixes. Recommended for every site.

### 3.1.1

Fixes registrations being rewritten on every page load and a translation notice. Recommended for every site.

### 3.1.0

Adds Import / Export, Reset Tools and REST API Control, and one-click Claude and ChatGPT connections for AI Connect.

### 3.0.1

Adds one-click demo import, the WPEssential elements and site tools (SEO, language switcher, custom code).

### 3.0.0

The new WPEssential platform with the Custom Post Type and Taxonomy builders. Requires PHP 8.2 and WordPress 6.9.
