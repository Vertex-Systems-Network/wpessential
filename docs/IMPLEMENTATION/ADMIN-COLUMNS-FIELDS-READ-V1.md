# Admin Columns Fields Read Composition V1

## Status

Gate D read-only integration tranche.

This tranche composes the accepted Surface 3 `FieldValueReadConsumerInterface` into Admin Columns runtime reads without transferring Field storage, authorization, filtering, sorting or mutation ownership to Surface 8.

## Composition boundary

Admin Columns continues to use Query as the authoritative source of the base post row set:

- target post type;
- search;
- Query-owned filters;
- Query-owned ordering;
- page size / offset;
- final ordered post IDs.

A View column with `source.owner = fields` and canonical `source.reference = fields.<group-uuid>.<field-uuid>` is not sent to the `wordpress.posts` Data Source projection. Instead:

1. Admin Columns validates the View and Query source metadata;
2. native/query columns are projected normally;
3. when any enabled Fields column exists, `post.id` is added as an **internal** Query projection if the View does not already require it;
4. Query returns the bounded ordered page;
5. the exact ordered unique positive post IDs are passed to the optional owner-certified Field value read consumer;
6. Field values are overlaid by post ID while preserving Query row order and pagination;
7. internal `post.id` does not leak into the rendered row unless the View itself declares a visible post-id column.

## Optional module law

Admin Columns retains its manifest dependency on `query` only.

If `module.custom-fields.values.read-consumer` is registered and implements `FieldValueReadConsumerInterface`, the adapter receives it. Sites without Custom Fields keep the existing native/query read path unchanged.

A published View that actually declares a Fields-owned column fails closed if the owner service is unavailable. There is no private post-meta fallback and no hard dependency added to the module manifest.

## Bounded V1 rules

- maximum enabled Fields columns per View: `16`;
- Fields references must use the canonical two-UUID `fields.<group>.<field>` shape;
- duplicate Fields references in one enabled View are rejected;
- Query rows used for Fields composition must expose unique positive integer `post.id` values;
- owner responses must match the requested contract version, exact Field reference, row count and requested post-ID order;
- incomplete, reordered or malformed owner evidence fails the complete Admin Columns read;
- zero-row Query results do not call the Fields consumer;
- Field values are never partially returned after an owner failure.

## Filtering and ordering

Fields columns are renderable in this V1 tranche, but they are **not** automatically filterable or sortable.

Admin Columns filter/order requests continue to resolve only against Query-owned native/query columns. A request that targets a Fields column fails closed with an explicit Query-ownership message.

This avoids silently duplicating Field predicate/sort semantics or inventing storage-specific SQL inside Admin Columns. A later separately certified Query integration may widen exact capabilities.

## Authorization

Admin Columns read-row Ability authorization remains unchanged. Surface 3 then independently enforces per-post resource authorization inside `FieldValueReadConsumerInterface` before target resolution/value projection.

The Query page, post ID, View definition, Field reference or rendered visibility setting never grants Field read or write authority.

## Performance evidence

`tests/Integration/wordpress-admin-columns-fields-read-performance.php` runs against real WordPress and real owner components.

It builds a mixed View containing:

- one native `post.title` column;
- one Fields-owned scalar text column.

The test compares 5-row and 50-row reads after cache flush and requires:

- exactly requested row counts;
- View-only output keys (`title`, `headline`), proving internal `post.id` does not leak;
- owner-certified Field values on every row;
- large SQL count no more than `small + 4`;
- absolute large-read ceiling of `18` SQL queries.

The existing Query Native Execution Reference matrix runs this proof across the retained WordPress/PHP/MySQL and MariaDB lanes. This is a bounded query-count/no-N+1 invariant, not a latency SLA.

## Explicit non-scope

This tranche does not add:

- Fields source discovery/catalog entries to the Admin Columns builder;
- `edit=true` capability metadata;
- inline or bulk editing;
- mapping to `wpessential/fields/write-value`;
- Fields-owned sorting/order implementation;
- export;
- View-schema widening;
- REST exposure;
- schema/migrations;
- direct post-meta access from Admin Columns.

## Next dependency-safe step

After this read composition is accepted, the next safe tranche may expose a **Fields-owned source catalog/capability projection** to the Admin Columns builder so users can select certified Field sources without hard-coded Surface 8 knowledge. Mutation must remain a later separate tranche and must route through the existing Surface 3 `write-value` Ability with revision/resource authorization intact.
