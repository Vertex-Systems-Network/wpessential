# WPEssential — Custom Fields Storage Architecture Alternatives

Status: **Phase 0 paper architecture / no storage adapter implementation authorized**  
Related: Custom Fields exhaustive spec, Definition Repository, Query AST v1, Relations runtime, Secrets Vault.

## Goal

Choose storage by **data semantics, target object and query needs**, not by one universal EAV/JSON format.

A Field Definition remains portable because logical type/editor/storage/presentation are separate concerns.

## Storage adapter contract

Every adapter declares:
- adapter ID/version;
- supported target object types/subtypes;
- supported logical field/cardinality types;
- canonical storage shape;
- null/empty semantics;
- read/write/delete API;
- batch API;
- query/filter/sort/aggregate capabilities;
- uniqueness/constraint capabilities;
- revisions support;
- transaction/concurrency semantics;
- REST/meta registration behavior;
- privacy/export/erase behavior;
- migration/import behavior;
- performance class;
- incompatibilities.

The UI must show limitations before Publish.

---

# A. WordPress metadata adapters

## Post Meta

Preferred default for ordinary post/CPT fields when:
- value naturally belongs to a WordPress post object;
- scale/query requirements are ordinary;
- interoperability with WordPress/plugins is valuable.

Use registered metadata where compatible.

Capabilities:
- typed metadata registration;
- object subtype scoping;
- sanitize/auth callbacks through registered adapters;
- defaults;
- single/multiple values;
- REST schema exposure;
- post-meta revisions where WordPress supports them.

Limitations:
- meta-query performance can degrade with large datasets/complex joins;
- schema-level uniqueness/foreign keys are not native semantics;
- structured repeaters need explicit encoding/storage strategy;
- revisions support is not universal across all metadata object types.

## User Meta

Use for ordinary user profile/application fields when values truly belong to a WordPress user.

Rules:
- protected/security-sensitive core keys denylisted from generic editing;
- roles/capabilities/password/session data are not generic fields;
- privacy exporter/eraser behavior explicitly mapped;
- query-heavy segmentation at scale may justify custom storage later.

## Term Meta

Use for taxonomy-term fields.

Rules mirror post-meta principles but without assuming post revision semantics.

## Comment Meta

Use only when real comment-domain fields are required; not a generic storage shortcut.

---

# B. Options / Settings storage

## Site option

Good for bounded site configuration, not growing row-per-business-record data.

Modes:
- one option per logical setting/group;
- structured group option where atomic configuration unit is useful.

Rules:
- autoload policy explicit;
- large options not autoloaded by default;
- secret values stored as Vault references;
- revisions handled by WPE Definition/settings revision layer rather than assuming WP option revisions.

## Network option

Only for explicitly network-scoped settings with Super Admin/network capabilities.

Site settings never silently promote to network scope.

---

# C. WPE Custom Table adapter

Preferred when one or more are true:
- high row volume;
- strong typed schema/index requirements;
- heavy filtering/sorting/aggregates;
- uniqueness/concurrency constraints;
- records are application entities rather than natural WP posts/users/terms;
- repeatable child rows require first-class row identity/queryability.

Benefits:
- explicit physical types/indexes;
- optimized access patterns;
- row-level versioning/concurrency;
- better predictable query model.

Costs:
- migrations/backup/import/privacy responsibilities;
- lower native interoperability than WordPress meta;
- physical schema compatibility evidence required.

A field can map to a Custom Table column only after table/schema dependency is valid.

---

# D. Structured/repeatable storage strategies

Repeater/Flexible/Group are logical structures, not one forced serialization format.

Candidate strategies:

## D1. Single structured value

Store one JSON-compatible/serialized canonical document in meta/option/custom JSON/text column.

Good for:
- bounded configuration-like structures;
- read/write as a unit;
- low need to query child rows.

Limitations:
- child filters/sorts/indexes weak or unavailable;
- partial updates/concurrency less granular;
- DB-native JSON support differs by compatibility floor.

WPE package/export uses normalized JSON regardless of physical representation.

## D2. Repeated metadata rows

Only for simple repeated scalar values where native metadata multi-row behavior fits semantics.

Not suitable as universal repeater-with-subfields format.

## D3. Child custom-table rows

Preferred candidate for queryable repeaters/structured application data.

Child row contains:
- stable child UUID/internal ID;
- parent typed reference;
- field/container identity;
- order;
- typed columns or structured payload according to schema.

Provides queryability/order/row identity but adds runtime tables and relation/integrity concerns.

## D4. Relations/Pivot storage

Entity-reference many-to-many/pivot data belongs in Relations runtime, not encoded into a text/meta blob.

---

# E. Entity-reference fields

Canonical value is a typed stable reference, not display label/URL.

Storage options:
- WordPress object numeric ID for local WP entity where appropriate;
- WPE runtime UUID/reference for custom entities;
- Relations Engine edge for multi/reference relationships requiring reverse lookup/pivot/cardinality.

Presentation may return object/URL/label without changing canonical storage.

Cross-site/network references require explicit future semantics; not assumed.

---

# F. Media fields

Canonical local WordPress media field stores attachment identity where possible.

Do not store generated URL as canonical identity for normal local media because URLs can change on migration/offload.

External asset/provider adapter may store provider object reference through an explicit media/storage adapter.

Gallery/multi-media values use typed list/relation strategy, not comma-separated IDs.

---

# G. Secret fields

Canonical persisted field value is a **Secrets Vault reference**, never plaintext option/meta/custom-table field.

Field schema may store:
- secret reference UUID;
- masked metadata safe for UI;
- rotation/status reference.

Generic exports/logs/tokens never resolve secret content.

---

# H. Computed/derived fields

Modes:
- runtime computed: no persisted value;
- materialized computed: stored derived value with source/version/invalidation semantics;
- DB generated column: advanced custom-table capability only.

UI must distinguish them.

Materialized value needs recompute trigger/event contract.

---

# I. Search/index projection

Do not distort source-of-truth storage merely to make search fast.

Optional future projection/index adapters can materialize searchable values for:
- full-text;
- faceting;
- reporting;
- remote search services.

Projection remains derived and rebuildable.

---

# Storage selection defaults by target

| Target | Default candidate | Escalation path |
|---|---|---|
| Post/CPT scalar | registered post meta | custom table for scale/schema/query need |
| User scalar/profile | user meta | custom table for high-volume app-domain records |
| Term scalar | term meta | custom table if domain warrants |
| Comment scalar | comment meta | custom table if domain warrants |
| Settings bounded config | option/network option | custom table for growing/queryable records |
| Application entity | custom table | WordPress post only if content/editor ecosystem is actually desired |
| Many-to-many entity refs | Relations Engine | simple single ID in native field only for trivial one-reference case |
| Queryable repeater | child/custom table candidate | structured blob only when child querying not needed |
| Secret | Vault reference | none |

These are defaults, not forced conversions.

---

# Queryability classes

Each stored field advertises:

### Q0 — not queryable
Examples: secret, runtime computed, UI-only.

### Q1 — equality/basic existence

### Q2 — typed range/order/filter

### Q3 — indexed high-volume

### Q4 — aggregate/join optimized

Builder shows queryability impact. A field cannot promise efficient filtering simply because UI can technically issue a meta query.

---

# Uniqueness

Adapters declare guarantee level:

- validation-only best effort;
- transactional/application lock;
- DB unique constraint;
- unsupported.

WordPress meta should not be marketed as strong concurrent uniqueness unless an accepted locking/index strategy proves it.

Custom table is preferred when hard uniqueness is a business invariant.

---

# Revision semantics

Revision support classes:
- native WordPress revision compatible;
- WPE value-revision log;
- Definition-only revision (schema changes, not runtime values);
- no value revision.

Post metadata can use current WordPress registered-meta revision support where compatible; other targets must not silently claim the same behavior.

---

# REST exposure

Storage does not automatically imply REST exposure.

Registered meta can map to WordPress REST schema where compatible, but WPE still applies explicit read/write policy and target support requirements.

Custom Table/Relation/Vault data reaches REST only through WPE Data Source/REST Builder contracts.

---

# Field-type change migration

Changing logical type/storage generates a Field Migration Plan:

- source adapter/type;
- target adapter/type;
- compatibility/fidelity class;
- affected records;
- null/invalid values;
- transformation;
- dependency/query impact;
- write freeze/dual-write needs if any;
- backup requirement;
- verification;
- rollback/recovery.

Examples:
- text→integer: invalid parse report;
- single→multiple: exact wrapping may be safe;
- multiple→single: conflict strategy required;
- meta→custom-table: mapping/index/relation verification;
- structured blob→child rows: item identity/order strategy;
- attachment ID→external URL: generally lossy identity migration unless source mapping retained.

No silent data rewrite when field setting changes.

---

# Storage migration strategies

Candidate modes:
- offline bounded migration for small datasets;
- chunked resumable migration;
- staged read-old/write-old during copy;
- temporary dual-read / controlled dual-write only if correctness model is formally defined;
- cutover after verification;
- old-store retention for bounded rollback window.

Dual-write is not default because it introduces consistency complexity.

---

# Cache rules

Canonical reads go through Data Source/Field adapter APIs so caching can be centralized.

Cache key must account for entity/version/context. Secret values never enter generic object/page caches without a specific security review.

---

# Privacy/export/erase

Every field has classification and adapter behavior.

For WordPress personal-data tools:
- WPE modules register exporter/eraser callbacks where they own PII;
- custom-table values are not exempt merely because they are outside core tables;
- erase may delete/anonymize/retain with reason according to module policy.

---

# Current paper recommendation

Use a **plural storage architecture**:

1. WordPress-native metadata/options for data that naturally belongs there and benefits from interoperability.
2. WPE Custom Tables for application-scale/query/constraint-heavy domains.
3. Relations Engine for first-class relationships/pivot semantics.
4. Vault for secrets.
5. structured blob only for bounded non-query-heavy structures.
6. derived projections only as rebuildable optimization.

Reject:
- universal EAV for all data;
- universal JSON blob for all data;
- making every custom field a custom-table column by default;
- encoding relations/secret credentials into generic meta strings.

## Future benchmark protocol — NOT AUTHORIZED

After explicit development consent compare representative workloads:
- native meta scalar CRUD/query;
- 10k/100k/1M entity meta filters;
- custom-table typed/indexed equivalents;
- repeater structured blob vs child rows;
- relation reverse lookup;
- migration meta→custom table;
- REST serialization;
- revisions;
- privacy export/erase batching;
- object-cache behavior.

Measure correctness/query count/latency/index size/migration duration/write amplification—not just one synthetic SELECT.

No adapter/table/migration/benchmark has been implemented or run.