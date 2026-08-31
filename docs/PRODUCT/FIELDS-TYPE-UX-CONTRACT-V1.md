# Fields / Field Groups — Type & Editor UX Contract V1

Status: implementation slice / Surface 3
Base: `88e5e90b273ddc61b4a0e2e36249c541576fc8fc`

## Decision

WPEssential keeps **logical value type**, **editor control**, **storage adapter**, and **return format** separate. User-facing catalog entries may therefore be presets that compile to one canonical type instead of creating duplicate storage semantics.

## User-requested catalog reconciliation

| Requested catalog entry | Canonical implementation |
| --- | --- |
| Text | `text` |
| Textarea | `textarea` |
| Email | `email` |
| Range | `range` |
| Search | `search` preset → `text` + search presentation |
| Date | `date` + WPE date picker |
| Time | `time` + WPE time picker |
| DateTime | `datetime` + WPE date-time picker |
| Week | `week` preset → date-family ISO week precision |
| Month | `month` preset → date-family month precision |
| Days | two explicit presets: Duration (Days) → Number+day unit; Weekdays → Checkbox List source |
| Slider | `slider` preset → `range` + WPE slider |
| Number | `number` |
| Group | `group` with recursive subfields; compatible with common clone/sort behavior |
| Section | UI-only `section` preset → Heading/section presentation; stores no value |
| Repeater | `repeater` with recursive subfields and its own row duplicate/sort contract |
| Color | `color`; HEX/HEXA/RGB/RGBA/HSL/HSLA modes; WPE picker |
| Code | `code_editor`; HTML/CSS/JS/JSON/PHP syntax modes; stored text only, never executed |
| WYSIWYG | `wysiwyg` |
| Gutenberg | `block_editor` |
| Video | local Media/Upload via `video`; social/provider URL via `oembed` preset path |
| oEmbed | `oembed` |
| TinyMCE | `tinymce` preset → `wysiwyg` using WordPress editor adapter |
| Select | `select` |
| Multi Select | preset → `select` + `multiple=true` |
| Checkboxes | `checkbox_list` |
| Radios | `radio` |
| Button Set | `button_group` |
| WordPress data choices | typed Post/Term/User fields with select/multiselect/checkbox/radio presentation modes |

## Common clone / sort contract

Meta Box-style cloning is a **capability, not a separate field type**. Compatible stored-value fields expose:

- Cloneable / Repeatable toggle;
- Sortable toggle visible only after cloning is enabled;
- clone default value;
- store clones as separate rows when the storage adapter supports that queryability trade-off;
- minimum/maximum clones;
- empty-start mode;
- Add button label.

`Group` participates in this common behavior and can repeat the whole group with its subfields.

`Repeater`, `Flexible Content`, `Gallery`, multi-file and other row/list containers manage repeat/order internally. Their duplicate/sort controls belong to the container row contract rather than adding a second nested common clone switch by default.

UI-only fields such as Heading/Section, Divider, Tab, Accordion and Message do not create repeatable stored values. Field definitions themselves remain reorderable/duplicable in the builder.

## Enhanced editor control policy

WPEssential will **not rely on browser-native picker UX** for Date, Time, DateTime, Week, Month, Color or Slider/Range. These use WPE/WordPress-integrated accessible editor components so appearance and behavior stay consistent across supported browsers.

This does not prohibit semantic HTML underneath an accessible component. It prohibits making browser-specific native picker behavior the product contract. Canonical validation/storage is server-authoritative and independent of the visual picker.

## WordPress editor policy

`TinyMCE` is not a second stored field type. WordPress `wp_editor()` is the compatibility adapter for the WYSIWYG field where appropriate and can provide TinyMCE and/or Quicktags. Gutenberg/Block Content remains a distinct structured-content field because its value semantics differ from rich HTML text.

## Safety decisions

- PHP in Code Editor means **syntax/highlighting mode only**. Fields never execute PHP/JS/CSS source.
- Social video URLs are previewed through the oEmbed/provider path; third-party iframe HTML is not canonical stored data.
- Password/credentials use Secrets references; no general reusable plaintext-password meta preset.
- Dynamic WordPress choices use typed Data Source/Query contracts and server-side authorization.

## Additional catalog already present in Surface 3 planning

The existing Bank/spec also includes URL, password/secret, hidden, booleans/switches, autocomplete/combobox, key-value, advanced date/ranges, image/gallery/file/media, Post/Term/User/Relationship selectors, maps, icon/image choice, background, gradient, dimensions, spacing, border, shadows, typography, palette, phone, currency, unit, angle, flexible content, clone composition, tabs, accordions, dividers, messages and extension field types.

## Implementation boundary for V1

This slice materializes the server-side Field Type Registry, user-facing preset registry, common clone/sort normalization, recursive Group/Repeater normalization, enhanced-picker policy metadata and code-execution rejection.

Admin React renderers, persistence adapters, Field Group CRUD/Abilities and runtime metadata registration are subsequent bounded Surface 3 slices. They must consume this registry rather than creating private type semantics.
