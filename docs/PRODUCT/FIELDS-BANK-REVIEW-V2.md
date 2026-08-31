# Fields / Field Groups — Formal Bank Review V2

Snapshot: 2026-09-01

Surface: 3 — Fields / Field Groups / Control Registry

Decision: **`BANK_REVIEWED` candidate — subject to exact-head CI certification**

Record count: **618 Fields / 796 total Bank**

This is the closing review requested by `FIELDS-BANK-REVIEW-V1.md`. It does not repeat discovery or add count-only records. It verifies that every blocker identified in V1 now has an explicit, machine-enforced resolution and that the WPE-future/exceed layer is internally consistent enough to feed downstream Atomic Option Contracts.

## 1. V1 blocker closure

| V1 blocker | Closing evidence | V2 result |
| --- | --- | --- |
| Semantic duplicate families | `config/product/options-bank-semantic-relations.json` | CLOSED — 6 machine relationships: 3 aliases + 3 effective derivations |
| Native `register_meta()` / Block Bindings completeness | `config/product/options-bank-audits/fields-native-wordpress.json` | CLOSED — `NATIVE_AUDITED`, 61 dispositions, 0 unresolved |
| Provider-by-provider market completeness | `config/product/options-bank-audits/fields-market-ecosystem.json` | CLOSED — `MARKET_AUDITED`, 12 primary + 15 specialist ecosystems, 0 unresolved |
| Genuine gaps discovered during audit | `fields--native-audit-v1.json` + `fields--market-audit-v1.json` | CLOSED — only evidence-backed gaps added |
| WPE exceed layer pending canonical base | all Fields shards + V2 review contract | CLOSED for Bank review — future/exceed semantics remain future, deliberate and non-duplicative under current contracts |

No new capability gap was proven during this closing pass, so the Bank remains at **618 Fields records**.

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

The smoke gate verifies:

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
- canonical progress truth says Fields `BANK_REVIEWED`, 618 records, total Bank 796, and one native/market/reviewed surface.

The existing Options Bank, progress, semantic, native-audit, and market-audit smoke contracts remain active in the same suite.

## 8. Lifecycle decision

Candidate status after this review:

| Gate | Result |
| --- | --- |
| Machine Bank integrity | PASS dependency |
| Semantic canonicalization | PASS dependency |
| Native audit | PASS dependency |
| Market audit | PASS dependency |
| WPE-future/exceed consistency | PASS candidate |
| Rejected/deferred policy consistency | PASS candidate |
| Unresolved review items | 0 |
| Fields record count | 618 |
| Total Bank record count | 796 |
| `BANK_REVIEWED` | **YES, only after exact-head CI** |

## 9. What `BANK_REVIEWED` unlocks

After exact-head CI certification, Surface 3 may feed implementation planning artifacts such as:

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
- It does not promote CPT or Taxonomy; their audits remain separate.

## 11. Next canonical surface

Once this exact head is green and merged, Surface 3 discovery/review is closed for the current snapshot. The next unseeded canonical surface is **Surface 4 — `relations`**. Its work must begin with fresh native/market discovery and its own evidence lifecycle rather than inheriting Fields completeness by assumption.
