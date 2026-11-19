# Free + Pro product audit and market gap — 2026-11-16

Status: **AUDIT / owner-requested**. Method: static trace of Free `main` and Pro `main` (admin page registration, abilities, editor UI, front-end output), compared with the published feature lists of JetEngine, Meta Box AIO, ACF Pro, CPT UI and Redux. No live WordPress run was possible in this session, so "works" below means "code path exists", not "verified in a browser".

## 1. Why "Open builder" said "Sorry, you are not allowed to access this page"

Pro builder pages (Fields, Relations, Custom Tables, Query, Admin Columns, Site Kit) are registered by the Pro module's `boot()`. Pro boots its modules only when Pro is active, licensed and the Free version is inside `WPE_PRO_MAX_FREE_VERSION`. Free's module page decided whether to show **Open builder** from Free's own toggle and entitlement probe, not from whether the page exists. When Pro did not boot (for example Free 3.0.1 with Pro max 3.0.0, or no licence), the button linked to a slug WordPress never registered, and WordPress answers that with "not allowed".

Fixed in this PR: the button only appears when the page is registered on that request; otherwise the module page explains why the builder did not load, and the status pill reads **Not loaded** instead of **Running**.

## 2. Real depth of each module

| Module | Admin screen | Saves data | Shows on the site | Honest state |
|---|---|---|---|---|
| CPT (Free) | Yes, full editor | Yes | Registers post types | Usable; close to CPT UI |
| Taxonomies (Free) | Yes, full editor + CPT UI import | Yes | Registers taxonomies | Usable; ahead of CPT UI on import |
| Import / Export, Reset, REST API (Free) | Yes | Yes | Yes | Usable V1 |
| AI Connect (Free, Premium-gated) | Yes | Yes | MCP + OAuth | Usable; not yet live-tested with claude.ai |
| Custom Fields (Pro) | Yes (field groups) | Yes | Post meta registered; **no meta box on the edit screen**, complex types (repeater, group, gallery, relationship) read-only | Not usable as an ACF/Meta Box replacement yet |
| Relations (Pro) | Yes | Read paths | No front-end output | Foundation only |
| Custom Tables (Pro) | Yes | Additive migrations | No front-end output | Under development |
| Query (Pro) | Yes | — | No listing/grid output | Foundation only |
| Admin Columns (Pro) | Yes | Views | Admin list tables | Partly usable |
| Site Kit (Pro) | Yes | Yes | Yes | Usable |
| Pro elements | Via Elements screen | — | Blocks + Elementor | Usable |
| Status, Listings, Dashboard Widgets | No screen | Partial | No | Engine only |
| Admin Menu, Settings Pages, Frontend Dashboard, Profiles, Membership, Builder Widgets, Forms, Notifications, Emails, Chat, Cron, Roles | No screen | Read-only abilities | No | Skeleton (4–5 files each) |
| Backup, Media, Protector, Connections | — | — | — | Not started |

Count: 9 modules a user can actually use, 6 with partial screens, 15 engine-only or skeleton, 4 not started.

## 3. Against the market

- **JetEngine** ships listing templates and the Listing Grid with grid, slider, map, calendar and carousel layouts, dynamic visibility in Elementor/Gutenberg/Bricks, a Relationship Builder across CPT, CCT, terms and users, a Query Builder, Profile Builder, front-end forms, Tables and Charts builders and REST API listings. WPEssential today has none of the front-end output side (listings, dynamic tags, visibility, forms, profiles, maps, calendar, charts).
- **Meta Box AIO** ships 40+ field types with a builder, meta boxes on posts, terms, users, comments and settings pages, conditional logic, custom tables, MB Views templates, MB Blocks, front-end submission and user profile forms, admin columns, and (since MB Builder 5.4) WordPress Abilities. WPEssential has the field catalog and storage, but no edit-screen meta boxes, no conditional logic UI, no views/templates and no blocks from fields.
- **ACF Pro**: field groups on any edit screen, repeater/flexible content/gallery/clone, options pages, ACF blocks, `get_field()` API. Same gap as Meta Box.
- **CPT UI**: WPEssential already matches or beats it (builder, block template, CPT UI import).
- **Redux**: options panels with sections, many field types, import/export of options. WPEssential's Settings Pages module is a read-only skeleton.

Where WPEssential is ahead: one AI-native MCP server with OAuth, audit log and Ability permissions across modules; definitions as validated, revisioned, exportable records; a single shell for every module; a Free tier with Import/Export, Reset and REST control built in.

## 4. Is the plan sound?

The planning base is strong: 56 canonical surfaces, Options Banks (hundreds of audited options per surface), Atomic Option Contracts and UX contracts, an Ability layer and strict CI. The gap is in execution order. Most effort went into contracts, evidence and governance for many surfaces in parallel, while the parts users compare first (field meta boxes, front-end output, forms, listings) were left as foundations. Delivery should switch from "every surface to contract-complete" to "a few surfaces to user-complete".

## 5. Recommended order (each phase ends with a live-site test)

1. **Fields user-complete**: meta boxes on post, term and user edit screens; repeater, group, gallery, relationship editable; conditional logic; options pages; `wpe_field()` API and a shortcode. Target: replace ACF/Meta Box on a normal site.
2. **Front-end output**: Dynamic Field block and Elementor widget, dynamic tags, Listing Grid (grid/carousel) from Query, dynamic visibility. Target: JetEngine's core listing workflow.
3. **Relations + Query on the front end**: related items in listings, relation editor in the post screen, query filters.
4. **Forms + Profiles**: front-end submission, register/login/edit profile, profile pages.
5. **Settings Pages** (Redux-style options panels), then Custom Tables to user-complete.
6. Then Maps/Calendar/Charts/Tables, Membership, Notifications/Emails, Backup, Media, Protector.

Skeleton modules stay tagged **Under development** until they reach their phase, so nothing claims more than it does.
