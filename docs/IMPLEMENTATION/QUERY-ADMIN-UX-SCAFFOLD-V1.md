# Query Admin UX Scaffold V1

Status: non-runtime admin scaffold for Surface 6 / Gate C. This tranche does not register a WordPress admin route, enqueue a Query bundle, save definitions, or expose Query execution.

## Purpose

The first Query admin scaffold establishes the client-side authoring contract without duplicating backend authorization or provider semantics.

`admin-ui/src/query.ts` consumes a future server-provided bootstrap projection containing:

- canonical Query identity;
- registered Data Source references/types/capability versions;
- source-owned field references and logical types;
- source-advertised predicate names;
- source-owned maximum page size;
- relation-support capability metadata.

The client does not discover tables, infer provider SQL, duplicate Policy decisions, or call a Query execution endpoint.

## Authoring scope

The scaffold can author a read-only AST preview for the currently certified V1 shape:

- one registered source;
- explicit projection fields;
- one optional bounded comparison or set-membership filter using source-advertised `eq`, `neq`, `in`, or `not_in` support;
- one optional field ordering clause with `asc` / `desc`;
- offset pagination bounded by the selected source's `maxPageSize`;
- fixed `select`, empty parameters, `distinct=false`, and empty execution/cache policies.

Integer field values are normalized to safe integers locally. All other values remain strings. Local structure checks are UX assistance only; the canonical server-side Query validator remains authoritative before any future save or execution operation.

## Explicit unsupported states

The UI communicates rather than approximates unsupported capabilities:

- Relation authoring is not exposed in this first scaffold even when a source advertises Relations support.
- Source predicates outside the four scaffold controls remain unavailable until a dedicated canonical control exists.
- Invalid or missing bootstrap data fails closed with an unavailable state.
- No source or no projection produces a visible validation status rather than an executable draft.

## Accessibility and responsive behavior

The scaffold uses native labels, fieldsets/legends, checkboxes, selects, number inputs and buttons. The AST preview is keyboard focusable, validation status is an `aria-live` status region, unsupported capability text is a note, and the disabled execution control is associated with explanatory help text.

Styles are namespaced under `.wpessential-query` and collapse multi-column controls to one column below the existing WordPress admin mobile breakpoint.

## Execution boundary

The `Preview execution unavailable` button is intentionally disabled. There is no `fetch`, AJAX route, REST route, nonce handling, save action, or provider invocation in this source.

This avoids representing the internal Query executor as an admin/public execution boundary before that boundary is separately authorized and certified.

## Supervisor integration requirements

A later serialized integration tranche must:

1. create the canonical Query admin route/page using the shared admin shell;
2. project registered Data Source metadata into a validated `wpessential-query-bootstrap` payload;
3. add `admin-ui/src/query` to the shared production build entry list;
4. enqueue the generated Query asset only on the Query admin screen;
5. define canonical definition list/validate/save/status server contracts before enabling persistence;
6. keep execution/preview disabled until a separately certified admin execution endpoint exists;
7. add packaged browser accessibility evidence for the integrated route.

Those shared build/navigation/PHP changes are deliberately excluded from this parallel worker tranche.

## Verification in this tranche

Existing repository checks triggered by `admin-ui/**` changes provide TypeScript strict typechecking, JavaScript/style linting, admin build regression checks and Browser E2E/accessibility regression coverage for already integrated surfaces. Query-specific packaged-route E2E is deferred until the Supervisor integration requirements above are implemented.
