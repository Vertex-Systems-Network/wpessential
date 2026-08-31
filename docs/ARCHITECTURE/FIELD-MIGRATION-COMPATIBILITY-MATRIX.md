# WPEssential — Field Migration Compatibility Matrix

Status: **Phase 0 planning — no importer/runtime implementation authorized**  
Source families: ACF/SCF, Meta Box, JetEngine.

This matrix defines the intended semantic mapping strategy, not a claim that migration code exists.

## Fidelity legend

- **E — Exact candidate:** WPEssential has the same core data semantics.
- **C — Convertible:** deterministic conversion is possible but representation/settings change.
- **L — Lossy:** main value can move but behavior/UI/settings may not fully survive.
- **X — External/adapter:** preservation requires provider/builder/integration adapter.
- **U — Unsupported by default:** no safe semantic mapping currently planned.
- **UI — UI-only:** no runtime data migration required.

The final fidelity is determined per source version and field settings, not only by type name.

---

# Common scalar fields

| Source concept | ACF/SCF | Meta Box | JetEngine | WPE target | Candidate | Notes |
|---|---|---|---|---|---|---|
| Single-line text | Text | Text | Text | Text | E | Preserve default, placeholder, min/max length/pattern where compatible. |
| Multi-line text | Textarea | Textarea | Textarea | Textarea | E | Rows/formatting settings may be presentation-only. |
| Number | Number | Number | Number | Number | E | Preserve min/max/step; normalize numeric/null semantics. |
| Range/slider numeric | Range | Range / Slider | Number with UI differences | Range/Number | C | JetEngine Number has no necessarily equivalent slider semantics. |
| Email | Email | Email | Text-like source in meta-fields | Email | E/C | JetEngine requires source-setting inspection; do not infer validation from label. |
| URL | URL | URL | Text-like source | URL | E/C | Same warning for JetEngine. |
| Telephone | custom/text in ACF family | Text/HTML5 strategy | Text | Tel | C | Add explicit tel validation only when source intended it. |
| Password | Password | Password | no direct standard meta type | Password/Secret policy | C | Ordinary password-like fields are not automatically Vault secrets; source purpose must be reviewed. |
| Hidden | hidden/custom | Hidden | no direct standard type | Hidden | E/C | Preserve value semantics; UI visibility is not authorization. |
| True/False | True/False | Checkbox / Switch | Switcher | Boolean | E | Normalize source-specific `0/1`, bool and missing-value semantics. |
| Key/value | custom/group | Key Value | repeater-like workaround | Key/Value or Repeater | C | Preserve ordering/duplicate-key rules explicitly. |

---

# Choice fields

| Source concept | ACF/SCF | Meta Box | JetEngine | WPE target | Candidate | Notes |
|---|---|---|---|---|---|---|
| Select single | Select | Select / Select Advanced | Select | Select | E | Preserve stored value vs display label separately. |
| Select multiple | Select multiple | Select multiple | Select multiple/settings-dependent | Multi-select | E/C | Inspect source return/storage format. |
| Radio | Radio | Radio | Radio | Radio | E | Preserve value/label pairs. |
| Checkbox single | True/False or Checkbox | Checkbox | Switcher/Checkbox | Boolean/Checkbox | C | Source type alone does not guarantee single vs multiple semantics. |
| Checkbox list | Checkbox | Checkbox List | Checkbox | Multi-checkbox | E/C | Preserve array vs delimited storage semantics through normalization. |
| Button Group | Button Group | Button Group | no exact core equivalent | Button Group/Radio | E/C | If only single value + labels, Radio semantics are equivalent; UI can preserve button style. |
| Autocomplete | select-style integrations | Autocomplete | Query-backed selection | Select with async source | C | Migrate data source/query separately. |
| Image Select | image choices/custom | Image Select | no exact | Choice with image option renderer | C | Requires option media mapping. |
| Dynamic choice source | AJAX/filter/custom | callbacks/custom options | Glossary / Query Builder | Dynamic Options Source | C/X | Callback PHP is not imported; source is converted only if represented by a supported Query/Data Source. |
| Allow custom choice | select/checkbox settings | settings-dependent | Allow Custom / Save Custom | Custom-choice policy | C | Must define whether new value mutates global options or record only. |

---

# Date/time fields

| Source concept | ACF/SCF | Meta Box | JetEngine | WPE target | Candidate | Notes |
|---|---|---|---|---|---|---|
| Date | Date Picker | Date picker | Date | Date | E/C | Normalize storage format vs display/return format. JetEngine may store timestamp by option. |
| Time | Time Picker | Time picker | Time | Time | E | Normalize seconds/timezone assumptions. |
| DateTime | Date/Time Picker | Datetime picker | Datetime | DateTime | E/C | Explicit timezone policy required. |
| Advanced/repeating dates | custom/repeater | custom/group | Advanced Date | Date schedule/repeater | C/L | Map only after Advanced Date recurrence/settings semantics are fixture-tested. |

---

# Rich/editor/content fields

| Source concept | ACF/SCF | Meta Box | JetEngine | WPE target | Candidate | Notes |
|---|---|---|---|---|---|---|
| Rich text | WYSIWYG | WYSIWYG | WYSIWYG | Rich Text | E | Preserve raw value; re-sanitize according to target context. |
| Block editor | no standard field type | Block Editor | no standard | Block Content/Editor adapter | C/X | Requires WordPress block content semantics; not plain rich text. |
| oEmbed | oEmbed | oEmbed | HTML/URL strategy | oEmbed/URL renderer | E/C | Never embed unsanitized source HTML during import. |
| Custom HTML display | Message/custom | Custom HTML | HTML | Message/HTML-safe UI field | UI/L | If field stores no data, migrate as UI-only message. Stored executable/script HTML is not migrated as executable code. |
| Message/instructions | Message | Custom HTML/Heading | HTML | Message | UI | No runtime data. |
| Link object | Link | Link | text/custom | Link | E/C | ACF/SCF link title/url/target object can map exactly; scalar URLs convert. |

---

# Media/upload fields

| Source concept | ACF/SCF | Meta Box | JetEngine | WPE target | Candidate | Notes |
|---|---|---|---|---|---|---|
| Image single | Image | Image / Single Image / Image Advanced | Media | Image/Media | E/C | Normalize attachment ID vs URL vs object return format. |
| Gallery | Gallery | Image Advanced / Image Upload patterns | Gallery | Gallery | E/C | Preserve order; external URLs require controlled media-fetch decision. |
| File | File | File / File Advanced / File Upload | Media | File/Media | E/C | Validate attachment/file type and source ownership. |
| File input/path | custom | File Input | no exact | File reference | C/X | External/local paths may not be portable. |
| Video | File/oEmbed | Video | Media/HTML | Media/Video | C | Detect attachment vs URL/embed semantics. |
| Background composite | group/custom | Background | no exact | Group/Composite | C | Split color/image/position/repeat/size fields into typed composite schema. |

---

# Color/icon/map/location fields

| Source concept | ACF/SCF | Meta Box | JetEngine | WPE target | Candidate | Notes |
|---|---|---|---|---|---|---|
| Color | Color Picker | Color | Colorpicker | Color | E | Preserve alpha only if source/target setting supports it. |
| Icon | Icon Picker | Icon | Iconpicker | Icon | C/X | Source icon library identifiers may not exist in Lucide/WP icon vocabulary; preserve source provider/name. |
| Google Map | Google Map | Google Maps | Map | Location/Map adapter | C/X | Normalize lat/lng/address; provider-specific place IDs remain external metadata. |
| OpenStreetMap | custom | Open Street Maps | Map provider-dependent | Location/Map adapter | C/X | Preserve coordinates independent of display provider where possible. |

---

# WordPress entity/reference fields

| Source concept | ACF/SCF | Meta Box | JetEngine | WPE target | Candidate | Notes |
|---|---|---|---|---|---|---|
| Post reference | Post Object | Post | Posts | Entity Reference | E/C | Preserve single/multiple cardinality and allowed post types. |
| Relationship | Relationship | Post / MB Relationships extension | Relation/Posts | Relation or Reference | C | Decide whether source represents durable domain relation or field-local reference. Do not automatically promote every post selector to global Relation. |
| Page link | Page Link | Post/link strategy | Posts/text | Entity Link | C | Return may be URL rather than ID. |
| Taxonomy term | Taxonomy | Taxonomy / Taxonomy Advanced | select/query/glossary or taxonomy meta context | Term Reference | E/C | ACF taxonomy field can optionally create/load/save terms; preserve side-effect policy explicitly. |
| User | User | User | user meta/selection strategy | User Reference | E/C | Preserve role filters and return format; never expose protected user fields. |
| Sidebar | custom | Sidebar | no exact | External WP object reference | X/L | May remain WordPress sidebar ID reference; not a core application-data field. |
| Nav Menu | Nav Menu/custom/SCF docs | custom | no exact | Nav Menu Reference | X/C | Requires WP nav-menu adapter and supported current nav architecture. |

---

# Structural/repeating fields

| Source concept | ACF/SCF | Meta Box | JetEngine | WPE target | Candidate | Notes |
|---|---|---|---|---|---|---|
| Group | Group | Group | repeater/meta structures | Group | E/C | Preserve nesting and key path. |
| Repeater | Repeater | Group cloneable / extensions | Repeater | Repeater | C | Source storage layouts differ materially; semantic schema can map, runtime storage requires versioned adapter. |
| Flexible layouts | Flexible Content | group/layout patterns | no direct same concept | Flexible Layouts | C/L | ACF/SCF layouts have names/keys/min/max and nested fields; preserve exact layout identity. |
| Clone/reuse schema | Clone | field-group reuse/code | no exact standard | Schema Reference/Clone | C | Prefer shared schema reference over materialized duplicates when source intent is reuse. |
| Fieldset text | group-like | Fieldset Text | no exact | Group | C | Convert subkeys to child fields. |
| Text list | repeater/group | Text List | repeater | Repeater/Text List | C | Preserve tuple/order semantics. |

---

# Layout/UI-only fields

| Source concept | ACF/SCF | Meta Box | JetEngine | WPE target | Candidate | Notes |
|---|---|---|---|---|---|---|
| Tab | Tab | Tab | layout/tab in CCT contexts | Tab | UI | No business data. |
| Accordion | Accordion | group/layout alternatives | layout/accordion in CCT contexts | Accordion/Section | UI/C | UI organization only. |
| Separator/divider | Separator | Divider | layout elements | Separator | UI | No runtime data. |
| Heading | Message/layout | Heading | HTML/layout | Heading/Message | UI | Sanitize labels/help. |
| Button | custom | Button | no standard | Action/Button UI | U/C | A field-builder button may execute custom JS/PHP; only migrate static UI when semantics are safe. |

---

# Source-specific special semantics

## ACF/SCF return formats
Image/File/Post/User/Taxonomy/Link fields can return IDs, URLs or arrays/objects depending on configuration. WPEssential stores canonical typed values and treats presentation/return format as renderer/API settings where appropriate. Importer must not copy a source return-format choice as if it changed the underlying entity identity.

## ACF/SCF bidirectional relationships
Where source field supports bidirectional update, importer should propose a WPE Relation only if the source semantics represent a real two-way relation. It must not leave two independently writable fields that can drift.

## ACF/SCF Local JSON and keys
Preserve `group_*` / `field_*` source keys as migration metadata. WPE target identities use WPE UUID/key contracts.

## Meta Box cloneable groups
Meta Box group/clone semantics and serialized/storage details require fixture-level validation. Schema mapping can be exact while runtime-data mapping remains only convertible until tested.

## Meta Box upload variants
`image`, `image_advanced`, `single_image`, `image_upload`, `file`, `file_advanced`, `file_upload`, `file_input` have different UI/storage behavior. Do not collapse them solely by names; normalize each stored value to a typed media/file reference.

## JetEngine selection storage
Checkbox/Select can use source options from manual input, bulk input, glossary or Query Builder and can have custom-choice behavior. The option-source definition must migrate separately from record values.

## JetEngine checkbox arrays
JetEngine settings can influence whether values are stored/used as arrays. Runtime adapter must normalize value shape rather than copying serialized form blindly.

## JetEngine date timestamp mode
Date field can be configured as timestamp. WPE importer must know whether a numeric source value is Unix time vs ordinary numeric metadata.

## JetEngine relation fields
Relation metadata belongs to WPE Relations pivot/meta schema, not ordinary post meta, when importing actual JetEngine relation definitions.

---

# Settings-level migration checklist per field

For each source field, importer records and maps where applicable:
- source type/version;
- source key/name;
- label;
- instructions/description;
- required;
- default;
- placeholder;
- min/max;
- length;
- step;
- pattern/mask;
- prepend/append;
- choices value/label;
- single/multiple;
- allow-null;
- return/display format;
- storage mode;
- conditional logic;
- wrapper width/class/id as presentation metadata only;
- REST exposure;
- revision support;
- quick-edit support;
- role/post-type/taxonomy filters;
- media MIME/library restrictions;
- date/time display/storage/timezone settings;
- reference cardinality;
- clone/repeater/flexible child schemas;
- source callback/dynamic option dependencies;
- source-specific side effects such as taxonomy term save/create;
- migration fidelity and warning.

Unknown source settings are retained in source metadata/report and never silently ignored in an `exact` conversion.

---

# Runtime value normalization rules

1. Preserve `null`, missing, empty string, `0`, `'0'`, `false` and empty array distinctions where source semantics distinguish them.
2. Normalize IDs only after source object mapping exists.
3. Media URLs are not assumed to be local attachments.
4. Serialized arrays are parsed only through safe, version-specific source rules; never unrestricted object unserialization.
5. Date values are parsed with explicit source format/timezone/timestamp mode.
6. Choice values preserve stored key independently from label.
7. Repeater rows preserve source order and stable row identity where the source provides one.
8. Relation/reference fields fail or defer when referenced source entities have not mapped yet; they do not keep accidental old-site numeric IDs as target IDs.
9. Rich HTML is treated as untrusted input and sanitized according to target permissions/context.
10. Password-like values are not upgraded into Secrets Vault automatically without explicit source semantics.

---

# Initial certification scope recommendation

## ACF/SCF Level 1 definition migration
Target first:
Text, Textarea, Number, Range, Email, URL, Select, Checkbox, Radio, Button Group, True/False, Date, DateTime, Time, Color, Image, File, Gallery, WYSIWYG, Group, Repeater, Post Object, Relationship, Taxonomy, User, Link, UI fields; then Flexible Content/Clone/Map/oEmbed/Icon after fixtures.

## Meta Box Level 1
Target first:
Text, Textarea, Number, Range, Email, URL, Checkbox, Checkbox List, Radio, Select, Date/Datetime/Time, Color, WYSIWYG, Post, Taxonomy, User, core file/image variants, Group and layout fields. Advanced provider/widget fields remain later certification.

## JetEngine Level 1
Target first:
Text, Textarea, WYSIWYG, Number, Media, Gallery, Date, Time, Datetime, Switcher, Checkbox, Radio, Select, Colorpicker, Iconpicker, Posts, Repeater; Map/Advanced Date/dynamic Query/Glossary-backed options are later sub-certifications.

Runtime-data certification follows separately.

---

# Required future executable fixtures

Before any row in this document can become a customer-supported `Exact` certification, an authorized adapter test must prove:
- source schema import;
- source runtime save representation;
- WPE normalized target value;
- round-trip/display meaning where appropriate;
- empty/null/false/zero cases;
- multi-value ordering;
- malformed input;
- source version variance;
- source deactivation after migration where claimed.

This planning matrix does not authorize those executable fixtures under ADR-0014.