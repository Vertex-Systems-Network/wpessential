# Status Surface — Integration Requirements V1

Snapshot: 2026-09-01  
Surface: 5 — `status`

## Reason for this file

This worker owns only the Status surface-local candidate files. Shared/global truth and shared test wiring are integrator-owned under the repository's multi-agent rules.

The branch intentionally does **not** update:

- `config/product/options-bank-progress.json`;
- `config/product/options-bank/STATUS.md`;
- root/shared README rollups;
- `composer.json`;
- generic/shared smoke aggregation or audit/review schemas;
- cross-surface semantic registries unless a certified review proves an alias/effective-derivation entry is necessary.

## Surface-local executable evidence now prepared

The Status worker has prepared all three lifecycle-safe surface validators:

- `tests/Smoke/options-bank-status-native-audit-contract.php`;
- `tests/Smoke/options-bank-status-market-audit-contract.php`;
- `tests/Smoke/options-bank-status-review-contract.php`.

The first two validate the complete native and market research candidates without claiming certification. The review validator consumes `config/product/options-bank-reviews/status-bank-review-v1.json`, which is deliberately `REVIEW_BLOCKED` until native, market and canonical progress prerequisites are certified in order.

These files are surface-owned. Registering them in shared Composer/global smoke aggregation remains a single-writer integration action.

## Integration Requirements

The designated integrator must reconcile these in order:

1. **Candidate seed validation**
   - validate all four Status Bank shards against `options-bank.schema.json`;
   - assert 129 unique record IDs and 129 unique `option_path` values;
   - confirm all records map to canonical Surface 5 and all records are classified;
   - confirm native/market audit references point to existing Status records;
   - preserve the current formal review result of zero Status alias/effective-derivation entries unless later evidence proves otherwise.

2. **Shared progress promotion**
   - after the candidate seed is executable-test-clean, update shared progress from `UNSEEDED / 0` to `BANK_SURFACE_SEEDED / 129`;
   - update derived Bank STATUS/README rollups atomically from integration-time truth;
   - do not promote beyond `BANK_SURFACE_SEEDED` at this step.

3. **Status Native Audit certification**
   - register `options-bank-status-native-audit-contract.php` in shared Composer/global smoke wiring without replacing or weakening existing Fields/Relations/other-surface gates;
   - require the complete 35-item WordPress disposition set, valid primary evidence, valid referenced Status records, correct Core-internal/private handling, canonical safety classifications and `UNRESOLVED=0`;
   - require fresh exact-head CI on the integrated head;
   - only then change `status-native-wordpress.json` to `NATIVE_AUDITED` and shared progress to `NATIVE_AUDITED / 129`.

4. **Status Market Audit certification**
   - only after Native Audit certification, execute `options-bank-status-market-audit-contract.php` through shared wiring;
   - require all nine families to be mapped or explicitly non-applicable for every primary provider, all four specialist provider/consumer references to exist, all ten extra dispositions to preserve canonical ownership, all four unsafe behaviors to remain rejected, and `UNRESOLVED=0`;
   - require fresh exact-head CI after the market promotion;
   - only then change `status-market-ecosystem.json` to `MARKET_AUDITED` and shared progress to `MARKET_AUDITED / 129`.

5. **Formal Bank Review certification**
   - the surface-local review candidate now exists at `config/product/options-bank-reviews/status-bank-review-v1.json` with decision `REVIEW_BLOCKED`, 129 records and two unresolved certification gates;
   - the current semantic registry contains zero Status relationships; do not add an alias/effective derivation unless separately proved;
   - execute `options-bank-status-review-contract.php` through shared wiring only after native and market lifecycle prerequisites are satisfied;
   - preserve zero unreviewed records, zero deferred records, the four explicit `REJECTED_UNSAFE` records, and future-only `WPE_EXCEED` policy consistency;
   - change the review decision to `BANK_REVIEWED`, set review `unresolved` to `0`, and set shared progress to `BANK_REVIEWED / 129` only on an exact head where native=`NATIVE_AUDITED`, market=`MARKET_AUDITED`, review contract passes and canonical progress agrees.

6. **UX projection and implementation contract**
   - only after `BANK_REVIEWED`, create the Status UX projection and downstream Atomic Option/implementation contract;
   - implementation must preserve the canonical no-bypass rule: all protected state mutation passes through Surface 5.

## Expected non-Status ownership during integration

- Forms / Workflow: orchestration only.
- Cron: timed execution only.
- Notifications / Emails: delivery only.
- Query: query composition only.
- Ledger: audit persistence only.
- Analytics: reporting only.
- WooCommerce/domain adapters: domain-specific status meaning only.

## Merge rule

Do not merge this surface-worker branch while canonical shared progress is inconsistent with the 129 Status shards. The designated integration lane must reconcile shared truth and register the prepared executable gates first. Lifecycle promotion remains strictly serialized:

`BANK_SURFACE_SEEDED → NATIVE_AUDITED → MARKET_AUDITED → BANK_REVIEWED`.
