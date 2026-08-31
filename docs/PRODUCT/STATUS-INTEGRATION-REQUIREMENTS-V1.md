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
- shared smoke tests or market/native audit schemas;
- cross-surface semantic registries unless a later formal Bank Review proves an alias/effective-derivation entry is necessary.

## Integration Requirements

The designated integrator must reconcile these in order:

1. **Candidate seed validation**
   - validate all four Status Bank shards against `options-bank.schema.json`;
   - assert 129 unique record IDs and 129 unique `option_path` values;
   - confirm all records map to canonical Surface 5 and all records are classified;
   - confirm native/market audit references point to existing Status records.

2. **Shared progress promotion**
   - after the candidate seed is executable-test-clean, update shared progress from `UNSEEDED / 0` to `BANK_SURFACE_SEEDED / 129`;
   - update derived Bank STATUS/README rollups atomically;
   - do not promote beyond `BANK_SURFACE_SEEDED` at this step.

3. **Status Native Audit certification**
   - add a Status-specific executable native-audit smoke (or safely generalize existing infrastructure without weakening Fields/Relations certificates);
   - register it in shared Composer smoke wiring;
   - require every native item to have a valid disposition, all referenced Bank records to exist, Core-internal/private members to remain non-authorable, and `UNRESOLVED=0`;
   - only then change `status-native-wordpress.json` to `NATIVE_AUDITED` and shared progress to `NATIVE_AUDITED`.

4. **Status Market Audit certification**
   - after Native Audit is certified, validate the Status market audit against the generalized market schema;
   - require all nine families to be mapped or explicitly non-applicable for every primary provider, all specialist references to exist, all extra dispositions to be owned, and `UNRESOLVED=0`;
   - only then change `status-market-ecosystem.json` to `MARKET_AUDITED` and shared progress to `MARKET_AUDITED`.

5. **Formal Bank Review**
   - run semantic duplicate/alias/effective-derivation review over all 129 records plus any evidence-backed gaps discovered during certification;
   - add cross-record semantic registry entries only when independently proved;
   - verify WPE-exceed/deferred/rejected policies and owner boundaries;
   - only then promote to `BANK_REVIEWED`.

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

Do not merge this surface-worker branch as-is while shared progress still says `UNSEEDED / 0`, because the canonical Bank shards would make whole-repository progress truth inconsistent. Integrate shared truth and exact executable gates on a designated integration lane first.
