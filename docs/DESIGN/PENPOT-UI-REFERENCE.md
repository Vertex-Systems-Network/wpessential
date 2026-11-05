# Penpot UI reference

Design source for the WPEssential admin UI. The designs live in Penpot; this file records where they are, what exists, and the rules they follow, so later design and implementation work starts from the same place.

Status: in progress. Reference only: nothing here is a runtime or shipped-parity claim.

## Source

- Tool: Penpot (design.penpot.app)
- File: `WPEssential` (file id `08b470c5-dda4-812a-8008-e56238f344de`)
- Built through the Penpot MCP server from the option contracts in `config/product/option-contracts/`.

## Design system

Taken from the repository, not invented:

| Part | Source |
|---|---|
| Colour tokens | `admin-ui/src/ui/_tokens.scss` (`--wpe-ink`, `--wpe-orange`, `--wpe-indigo`, surfaces, status colours) |
| Type | Bricolage Grotesque (headings), Plus Jakarta Sans (body, labels), JetBrains Mono (keys, slugs, code, values only) |
| Icons | `frameworks/Platform/Admin/Ui/Icon.php` (24px line icons) |
| Components | `admin-ui/src/ui/_components.scss` and `_core.scss`: button, badge, status pill, count, chip, toggle, tabs, input, panel, module card |
| Shell | WordPress admin bar and menu, WPEssential top bar (`frameworks/Platform/Admin/Ui/Shell.php`) |

The 22 colours are also saved as a Penpot library (`WPE/*`).

## Screen pattern

Every module settings screen uses one template:

1. Breadcrumb, module header (icon tile, name, edition badge, status, option and group counts, module toggle, docs).
2. Definition bar: which definition is being edited, plus an Essential / Advanced / Expert filter.
3. Left: option-group navigation with counts. Right: one collapsible group per contract feature group.
4. Each option row: label, one-line description, chips for anything that differs from the group (tier, required on create, migration required, rejected), and the real control for that option's sub-fields.
5. Save bar with unsaved-change summary, preview args, discard and save.

Every group in a contract is designed open. No option is left collapsed or implied.

## Rules applied (Hallmark app-screen polish)

- No eyebrow above headings; labels and table heads in sentence case, never tracked capitals.
- No middle-dot joined meta text; commas instead.
- No arrow glued to link text; no decorative dot inside status pills.
- No card inside a card.
- A level chip on a row only when it differs from the group's level.
- Segmented controls fill their width.
- Colour scheme unchanged from `_tokens.scss`.

## Pages and boards

| Penpot page | Boards | Options covered |
|---|---|---|
| 00 Foundations | Colour, typography, components | n/a |
| 01 Overview | 01 Dashboard | n/a |
| 02 Modules | 02 Modules (31 modules, 7 categories, real option counts) | n/a |
| 03 Custom Post Types | 03 Module, Custom Post Types | 23 options, 13 groups (`cpt.json`) |
| 04 Taxonomies | 04 Module, Taxonomies | 20 options, 13 groups (`taxonomy.json`) |
| 05 Relations | 05 Module, Relations | 18 options, 6 groups (`relations.json`) |
| 06 Custom Fields | F1 Field groups list, F2 Field group editor, F3 Field type picker (82 types) | group-level options |
| 07 Fields, Field editor | F4 Field editor | 92 common field options |
| 08 Fields, Type settings | F5a Text to Static content, F5b Markdown to Media UX, F5c Media storage to Color, F5d Typography to Font size, F5 Specialized types | 141 type-specific options |
| 09 Fields, Layout fields | F6a Group, F6b Repeater, F6c Flexible Content, F6d Clone | 43 options |
| 10 Fields, Relational fields | F7a Relational, F7b Relational UX and objects | 36 options |
| 11 Fields, Integrations | F8a Block bindings, F8b REST and GraphQL, F8c Custom table storage, F8d AI, schema, network, settings | 71 options |
| 12 Fields, Tools | F9a Import adapters, F9b Migration and sync, F9c Diagnostics, F9d Governance and performance, F9e Developer and control registry | 105 options |

Custom Fields (`fields.json`, 612 options) is fully covered across F1 to F9.

## Known open items

- 00 Foundations: the components and typography sections were being rebuilt to the rules above when work stopped; check them in Penpot before use.
- 08 Type settings: the section nav in F5b to F5d still highlights "Text"; it should highlight the first group of each board.
- Not yet designed: Admin Columns and every surface after it, dark mode (`_dark.scss`), and loading, empty and error states for each screen.

## Working notes

- The file now lives in a self-hosted Penpot (http://localhost:9001, file id `29deb493-29da-8167-8008-e62ae56b3f29`); design.penpot.app blocks API tokens behind Cloudflare. Claude reaches it through the Penpot MCP plugin, proxied by the Claude desktop app as the local MCP server `penpot-local`.

- The Penpot plugin runs inside the browser tab; the tab must stay open and visible. Large boards (over about 30 groups) freeze the tab when edited, so keep boards to 8 to 10 groups.
- Helper libraries used to build the screens are stored in the Penpot file's plugin data (`wpelib`, `wpelib2`).
