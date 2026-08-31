# Fields / Field Groups — Formal Bank Review V2

Snapshot: 2026-09-01

Surface: 3 — Fields / Field Groups / Control Registry

Decision: **`BANK_REVIEWED` — certified on its original exact head**

Fields record count: **618**

Original closing-review global snapshot: **796 total Bank records**. That global number was true when Surface 3 was certified, but it is not a permanent Fields invariant: later canonical surfaces are expected to increase the global Bank total.

This is the closing review requested by `FIELDS-BANK-REVIEW-V1.md`. It does not repeat discovery or add count-only records. It verifies that every blocker identified in V1 now has an explicit, machine-enforced resolution and that the WPE-future/exceed layer is internally consistent enough to feed downstream Atomic Option Contracts.

## 1. V1 blocker closure

| V1 blocker | Closing evidence | V2 result |
| --- | --- | --- |
| Semantic duplicate families | `config/product/options-bank-semantic-relations.json` | CLOSED — 6 machine relationships: 3 aliases + 3 effective derivations |
| Native `register_meta()` / Block Bindings completeness | `config/product/options-bank-audits/fields-native-wordpress.json` | CLOSED — `NATIVE_AUDITED`, 61 dispositions, 0 unresolved |
| Provider-by-provider market completeness | `config/product/options-bank-audits/fields-market-ecosystem.json` | CLOSED — `MARKET_AUDITED`, 12 primary + 15 specialist ecosystems, 0 unresolved |
| Genuine gaps discovered during audit | `fields--native-audit-v1.json` + `fields--market-audit-v1.json` | CLOSED — only evidence-backed gaps added |
| WPE exceed layer pending canonical base | all Fields shards + V2 review contract | CLOSED for Bank review — future/exceed semantics remain future, deliberate and non-duplicative under current contracts |

No new capability gap was proven during this closing pass, so the Fields Bank remains at **618 records**.

## 2. Semantic closure

The six cross-wave semantic overlaps remain explicit rather than deleting discovery evidence:

### Aliases

- `fields.behavior.validation_required` → `fields.field.required`
- `fields.field.cloneable` → `fields.behavior.repeat_clone`
- `fields.field.sortable` → `fields.behavior.repeat_sort`

### Effective derivations

- `fields.behavior.rest_schema` → `fields.field.rest_schema`
- `fields.behavior.value_escape` → `fields.field.escape_html`
- `fields.behavior.value_formatted` → `fields.field.format_value`

Alias/effective source records remain discovery evidence and must not be emitted as independent duplicate authored controls downstream. Consumers are required to resolve the semantic registry before generating Atomic Option Contracts.

## 3. Native audit dependency

`FIELDS-NATIVE-WORDPRESS-AUDIT-V1.md` and the machine audit certificate close the V1 native silent-omission blocker.

The current certificate must remain:

- status `NATIVE_AUDITED`;
- zero unresolved dispositions;
- canonical Surface 3 ownership;
- linked to real Bank/provider mappings;
- explicit about runtime-only and out-of-surface WordPress APIs.

If that audit becomes stale or unresolved, the V2 review contract must fail rather than silently preserving `BANK_REVIEWED`.

## 4. Market audit dependency

`FIELDS-MARKET-AUDIT-V1.md` and the machine market matrix close the V1 market-completeness blocker.

The current certificate covers:

- 12 primary provider ecosystems across six canonical capability families;
- 15 specialist/long-tail ecosystems;
- current ACF 6.8, Pods REST and ACPT custom-table gaps;
- explicit implementation-pattern, out-of-surface and unsafe-rejection dispositions;
- zero unresolved market dispositions.

If any provider/family loses coverage or an unresolved item is introduced, the final review contract must fail.

## 5. WPE-future / exceed review

The Bank intentionally includes possibilities that are not current-market parity. These are planning inputs, not shipped-feature claims.

The closing review treats WPE exceed ideas as valid when they remain explicitly future-oriented and consistently classified. Major reviewed families include:

- schema versioning, aliases, drift/conflict detection and deprecation windows;
- location/conditional/access/value-pipeline diagnostics;
- accessibility contract checks;
- safe formula ASTs and registered computed-value providers;
- choice/relationship/cache/index performance policy;
- migration dual-read/dual-write, cutover guards and rollback snapshots;
- sensitivity, retention, masking, write-once and change-audit governance;
- WP-CLI generation/validation/migration;
- fixture and generated contract-test support;
- typed provider SDK and extension sandboxing;
- Block Binding preview and per-context policy.

The dedicated exceed shard is required to remain `WPE_EXCEED` + `WPE_FUTURE` + `P1_EXCEED`. Across all Fields shards, WPE-exceed adoption must remain future/P1, while deferred and unsafe-rejected records must retain their explicit lifecycle semantics.

## 6. Rejection and deferral are valid reviewed outcomes

`BANK_REVIEWED` does **not** mean every discovered possibility is adopted.

The Bank preserves deliberate outcomes such as:

- `REJECTED_UNSAFE` for arbitrary executable PHP/JS/callback configuration;
- `DEFERRED` for evidenced possibilities that should not enter the current implementation horizon;
- provider-safe replacements where executable extension points are useful but raw code configuration is unsafe;
- out-of-surface ownership where another canonical surface owns the capability.

The V2 machine gate checks that rejected and deferred states are internally consistent instead of being ambiguous leftovers.

## 7. Machine review certificate

Canonical final-review certificate:

`config/product/options-bank-reviews/fields-bank-review-v2.json`

Schema:

`config/product/options-bank-review.schema.json`

Smoke gate:

`tests/Smoke/options-bank-review-contract.php`

The Fields-local smoke gate verifies:

- the review schema is parseable;
- Surface 3 / Fields ownership and `BANK_REVIEWED` decision;
- exactly the semantic, native-audit and market-audit upstream artifacts are bound;
- six semantic relationships remain 3 aliases + 3 effective derivations;
- native audit remains `NATIVE_AUDITED` with zero unresolved;
- market audit remains `MARKET_AUDITED` with zero unresolved;
- all Fields shards together still contain exactly 618 records;
- no Fields record remains `UNREVIEWED`;
- `REJECTED_UNSAFE` records map to `REJECT` / `NOT_SCHEDULED`;
- `DEFERRED` records remain `WPE_FUTURE` / `LATER` / `P3_LATER`;
- `WPE_EXCEED` records remain `WPE_FUTURE` / `WPE_EXCEED` / `P1_EXCEED`;
- the dedicated WPE exceed shard contains only canonical exceed records;
- canonical progress still records Surface 3 itself as `BANK_REVIEWED` with 618 Fields records.

Global Bank totals and lifecycle counters are **not** permanent Fields-review invariants. They are derived from every canonical surface and are validated exactly by `tests/Smoke/options-bank-progress-contract.php`. This separation allows later surfaces to be seeded/audited/reviewed without invalidating an already-certified Fields snapshot.

The existing Options Bank, progress, semantic, native-audit, and market-audit smoke contracts remain active in the same suite.

## 8. Lifecycle decision

Fields-local review result:

| Gate | Result |
| --- | --- |
| Machine Bank integrity | PASS dependency |
| Semantic canonicalization | PASS dependency |
| Native audit | PASS dependency |
| Market audit | PASS dependency |
| WPE-future/exceed consistency | PASS |
| Rejected/deferred policy consistency | PASS |
| Unresolved review items | 0 |
| Fields record count | 618 |
| Original global Bank snapshot | 796 |
| `BANK_REVIEWED` | **YES** |

The original 796 global total is historical certification context only; current global truth must always come from `config/product/options-bank-progress.json` and its progress smoke contract.

## 9. What `BANK_REVIEWED` unlocks

Surface 3 may feed implementation planning artifacts such as:

- canonical Atomic Option Contracts;
- provider contracts;
- storage/API contracts;
- UI/control contracts;
- compatibility/migration mappings;
- test vectors and fixtures.

Generation must resolve semantic aliases/effective derivations first and preserve rejected/deferred/out-of-surface decisions.

## 10. Non-claims

- `BANK_REVIEWED` is not runtime implementation.
- It is not a claim that all 618 records become UI settings.
- It is not a claim that all WPE-future ideas ship in v1.
- It is not a production migration certificate.
- It is not a license to copy competitor implementation code.
- It does not promote CPT, Taxonomy, Relations, or any later surface; their audits remain separate.

## 11. Subsequent surfaces

Surface 3 discovery/review is closed for this snapshot. Later canonical surfaces must run their own discovery, native audit, market audit and final review lifecycle. Their records and lifecycle promotions may legitimately increase global Bank totals/counters; that growth must not invalidate Surface 3's local 618-record certification.
