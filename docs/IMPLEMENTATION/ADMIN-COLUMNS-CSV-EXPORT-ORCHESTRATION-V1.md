# Admin Columns CSV Export Orchestration Core V1

Status: **INTERNAL FOUNDATION / GATE D REMAINS ACTIVE**  
Parent tracker: GitHub Issue #66  
Implementation issue: GitHub Issue #238  
Source base: `main @ c1b5cdf678f10d83e7874005ea4b331fab00406c`

## Purpose

This tranche composes already-authorized Admin Columns read pages into CSV without introducing a second query/source engine or any HTTP/download surface.

`AdminColumnsCsvExportService` delegates:

- every page read to `AdminColumnsReadAdapter`, which retains Query-owned backend row selection/sort/filter/search/pagination and existing Policy/source-owner authorization;
- every serialization decision to the certified `AdminColumnsCsvExportEncoder`, including deterministic quoting and spreadsheet formula neutralization.

## Bounded V1 contract

- Caller supplies a canonical View UUID plus only existing non-pagination read controls: `filters`, `search`, and `order_by`.
- Caller-provided `page_size` or `offset` is rejected; export orchestration owns pagination.
- Reads use pages of at most 100 rows.
- One export is capped at 1,000 rows by default and may only be configured lower within this V1 service.
- If the ceiling is exactly filled, a one-row authorized probe at the next offset determines whether more data exists; if so, the export fails closed instead of silently truncating.
- Query/read failures abort the whole export; no partial CSV is returned.
- The first successful page establishes the exact read contract version, View id, View revision and ordered column metadata.
- Every later page, including the overflow probe, must preserve the same View revision and exact ordered column contract. Drift fails closed so multiple snapshots are never mixed into one export.
- Returned row count must exactly match the page row list and never exceed the requested page size.
- Only ordered `{key,label}` column descriptors from the certified read response are passed to the encoder.

## Ownership and security boundary

The service intentionally does **not**:

- read Query, Fields, Relations, WordPress meta/tables or provider storage directly;
- infer authorization from View assignment, visibility, UI availability or export presentation state;
- invent a second redaction or source capability engine;
- expose AJAX, REST, admin-post, browser, download or streaming HTTP transport;
- create background jobs, temporary files or persistent export artifacts;
- perform imports, bulk mutation or preferences work;
- modify `AdminColumnsAdminController.php`, `admin-ui/**`, Query, Fields or Relations production internals.

Any unsupported/non-scalar value that reaches serialization remains fail-closed under the encoder contract. A later transport/UI tranche must be separately authorized and must consume this service rather than duplicating its read/encoding behavior.

## Focused evidence

`tests/Unit/Modules/AdminColumns/AdminColumnsCsvExportServiceTest.php` covers:

- deterministic multi-page composition through the public read adapter;
- preservation of View column order in CSV;
- reuse of encoder formula neutralization;
- 100-row page ownership by the orchestration service;
- explicit finite-ceiling overflow probing and rejection;
- normalized upstream read/Policy failure propagation without partial CSV;
- rejection of caller pagination overrides.

Repository-wide exact-head CI remains authoritative for syntax, coding standards, static analysis, unit/smoke/integration compatibility and packaging.

## Non-claim

This is an **internal orchestration core**, not a complete export product. No download route, browser UI, background/streaming export, broader redaction model, product parity, Gate D PASS, Gate E start, release or deployment is claimed.
