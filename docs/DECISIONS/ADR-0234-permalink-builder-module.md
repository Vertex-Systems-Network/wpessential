# ADR-0234 — URL formats in the CPT/Taxonomy Builders; Permalink Builder and Redirects/404/Error Monitor mapped to their existing owners

Status: **ACCEPTED**
Date: **2026-10-23**
Approval: `GOV-OWNER-CONSENT-002` (full autonomy; decision recorded instead of asked)

## Context

The owner asked for two things:
- In the CPT and Taxonomy builders, the user should be able to show or hide the base in the URL and build a custom URL pattern, as WordPress's Permalinks screen does.
- A full Permalink Builder module, aligned with the plan. The owner named Custom Permalinks and Custom Permalink Editor as competitors.

## Decision

1. **Free core, in the builders (shipped now).**
   - **Payload:** a new `permalink` payload key.
     - Post types: `{mode: default|hide_base|custom, pattern?}`.
     - Taxonomies: `{mode: default|hide_base}`.
   - **Validation:** projectors check it. A custom pattern ends with `%postname%`, has 1–7 parts before it, and each part is `a-z 0-9 - _` or a `%tag%`. Hiding the base or a custom pattern needs `rewrite` on.
   - **Compilation:** a custom pattern compiles to the rewrite slug. The runtime choice travels in the compiled args under `wpessential_permalink`, and the registrars take it out before WordPress sees the args.
   - **Runtime:** `Platform\WordPress\Registrations\PermalinkRuntime` handles it at init 30, after the registrars:
     - It registers the rewrite tags.
     - Its `post_type_link` and `term_link` filters fill in the tags or drop the base.
     - Its `request` filter resolves hidden-base URLs only when no real page or post has that path.
     - It flushes rewrite rules once, when the signature of all URL settings changes.
2. **No new surfaces.** Following the Option duplication test (CANONICAL-OPTION-OWNERSHIP-INDEX §4):
   - The Permalink Builder extends **Surface 44 Redirect/Routing**, for per-item URLs and core/WooCommerce patterns, and Surfaces 1/2 own the builder URL formats (`MODULE-CATALOG.md` §31).
   - Redirects and 404s are Surface 44. The Error Monitor extends **Surface 31 Diagnostics (E01)**, and its alerts go through Surface 19 (§32).
   - A first draft of this ADR planned a separate redirect module. It was withdrawn when the existing Surface 44 spec was found: it would have been a second redirect engine.
3. **Competitor gaps added to those owners.**
   - Surface 44: a simple redirect table, 404 fallback, and smart 404 to similar posts (opt-in, with a threshold).
   - Surface 31 E01: an Error Monitor with a debug.log viewer.
   - Importers.
4. **Link Shortener & Link Manager** (`MODULE-CATALOG.md` §33, ROADMAP 2Z), owner-requested and researched by us.
   - Plugins researched: Pretty Links, ThirstyAffiliates, BetterLinks, URL Shortify, Easy Affiliate Links and Lasso; services: Bitly and Rebrandly.
   - It is a Surface 44 extension: a short link is a redirect rule, with link metadata and click analytics.
5. **AI plan.** The same `permalink` key is part of the CPT/Taxonomy option contract, so AI prompts can set it. The Pro module's abilities follow the AI-write switch.

## Consequences

The free builders now match Custom Post Type Permalinks and the remove-base plugins. Per-item URLs and redirects stay Pro.
