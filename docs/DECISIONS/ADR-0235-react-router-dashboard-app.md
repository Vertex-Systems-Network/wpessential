# ADR-0235 — Dashboard screens as a React Router app

Status: **ACCEPTED**
Date: **2026-10-23**
Approval: `GOV-OWNER-CONSENT-002` (full autonomy; decision recorded instead of asked)

## Context

The owner asked for the dashboard navigation items to be child components switched by React Router. They also asked for AI Connect to move to the WordPress menu as a module, and for Account to become an icon menu.

## Decision

1. **Routes.** Dashboard, Modules, Demo Library and Elements are React components in one tree (`admin-ui/src/app`), switched by React Router 7 (`BrowserRouter`, keyed on `?page=`) without a page reload. The address bar, Back/Forward, the top-bar highlight and the document title follow the route.
2. **React is WordPress's own.**
   - Script handles: `react` and `react-dom`, through the `app` entry's asset dependencies.
   - Build: esbuild maps `react`/`react-dom` to those globals, and only `react-router` is bundled (47 KB).
   - Toolchain: `react-router`, `@types/react` and `@types/react-dom` are pinned in the approved toolchain.
3. **Data.**
   - Each screen's PHP prints its view model as `<script type="application/json" data-wpe-app-data>`.
   - The server markup stays as the first paint and the no-JavaScript fallback.
   - A screen opened through the router loads its data from that page's HTML.
   - Icons, badges and toggle forms travel as escaped toolkit HTML, so AJAX toggles, design and translations stay single-sourced in PHP.
4. **Other pages are unchanged.** In-place navigation (`core/nav.ts`) still serves the rest of the screens. It leaves Back/Forward between app screens to the router, and it signals `wpe:screen` so the app mounts on a screen that arrived in place.
5. **Account menu.** Account is an icon beside light/dark, with a chevron and an arrow-tip menu: Log in / Register, Docs, System Status.
6. **AI Connect.** It sits in the WordPress sidebar while its module is on.

## Consequences

- WordPress without the `react` script handle (older than 6.2) keeps the server-rendered screens.
- New dashboard screens must provide a view model and a route.
