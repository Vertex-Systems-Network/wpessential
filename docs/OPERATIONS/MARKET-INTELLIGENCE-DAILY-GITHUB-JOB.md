# WPEssential — Daily Market Intelligence GitHub Job

Status: **Planned / disabled / NOT INSTALLED as executable workflow**
Date: 2026-08-29

## Purpose

Run the Market Intelligence & Capability Radar once per day and produce non-runtime planning artifacts. This job must never become an implementation/deployment job.

GitHub scheduled workflows run from the default branch and use cron scheduling; scheduled execution can be delayed. The product contract is therefore **daily scan**, not exact wall-clock 24-hour SLA.

## Default behavior

1. Fetch public WordPress.org plugin metadata through the official plugin information API.
2. Refresh comparator/watchlist metadata.
3. Fetch allowed public changelog/release/source metadata.
4. Compare against previous market snapshot.
5. Extract candidate capability signals.
6. Run deterministic dedupe/scoring.
7. Optionally invoke configured planning AI for summaries/capability classification.
8. Produce Markdown + JSON report.
9. Create/update a GitHub planning issue.
10. Optionally create a **Draft docs-only planning PR** when explicitly enabled.
11. Never merge, never modify runtime/source code, never grant development consent.

## Least-privilege permissions

Default report-only mode:
- `contents: read`
- `issues: write`

Optional Draft planning-PR mode additionally needs:
- `contents: write`
- `pull-requests: write`

No Actions secret should be available to untrusted external code.

## Proposed workflow YAML

The following is the exact planned workflow shape. It is kept in documentation because ADR-0014 currently prohibits executable workflow installation.

```yaml
name: WPE Market Intelligence Daily

on:
  schedule:
    - cron: '17 3 * * *'
      timezone: 'Asia/Karachi'
  workflow_dispatch:
    inputs:
      full_scan:
        description: 'Run full comparator refresh'
        required: false
        default: false
        type: boolean

concurrency:
  group: wpe-market-intelligence-daily
  cancel-in-progress: false

permissions:
  contents: read
  issues: write

jobs:
  market-scan:
    runs-on: ubuntu-latest
    timeout-minutes: 45

    steps:
      - name: Checkout repository
        uses: actions/checkout@v4
        with:
          fetch-depth: 1

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          coverage: none

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '22'
          cache: 'npm'

      - name: Install market-radar dependencies
        run: ./tools/market-radar/install-ci-deps.sh

      - name: Fetch public ecosystem snapshots
        run: ./tools/market-radar/scan.sh --mode=${{ inputs.full_scan && 'full' || 'daily' }}
        env:
          WPE_MARKET_HTTP_USER_AGENT: 'WPEssential-Market-Radar/1.0'

      - name: Validate provenance and candidate schema
        run: ./tools/market-radar/validate.sh

      - name: Produce deterministic capability diff
        run: ./tools/market-radar/diff.sh

      - name: Optional AI classification
        if: ${{ secrets.WPE_PLANNING_AI_TOKEN != '' }}
        run: ./tools/market-radar/ai-classify.sh
        env:
          WPE_PLANNING_AI_TOKEN: ${{ secrets.WPE_PLANNING_AI_TOKEN }}

      - name: Build daily report
        run: ./tools/market-radar/report.sh

      - name: Upload report artifact
        uses: actions/upload-artifact@v4
        with:
          name: market-radar-${{ github.run_id }}
          path: |
            .market-radar/output/report.md
            .market-radar/output/report.json
            .market-radar/output/sources.json
          retention-days: 30

      - name: Create or update planning issue
        env:
          GH_TOKEN: ${{ github.token }}
        run: ./tools/market-radar/publish-issue.sh
```

## Optional docs-only Draft PR mode

This is **off by default**.

If later approved, use a second job with narrower explicit intent:
- create branch `market-radar/YYYY-MM-DD`;
- write only `docs/RESEARCH/CANDIDATES/**` and market snapshot metadata;
- open Draft PR;
- label it `market-radar`, `planning-only`;
- do not touch `src/`, plugin bootstrap, DB migrations, dependency manifests, release files or executable workflows;
- do not auto-merge.

The optional job requires `contents: write` and `pull-requests: write`; these permissions must not be added to report-only mode.

## Data sources

Deterministic collection adapters should prefer:
- WordPress.org Plugin Information API;
- official plugin pages;
- official WordPress.org SVN/Trac metadata where necessary;
- official GitHub repository/release metadata;
- Developer.WordPress.org / Make WordPress;
- approved standards/vendor sources.

Search-engine discovery can nominate sources, but canonical market facts require traceable source URLs.

## Snapshots

Store compact normalized snapshots, not full copyrighted webpages/source trees.

Suggested snapshot fields:
- slug/name;
- version;
- update date;
- active-installs bucket;
- rating/review count;
- WP/PHP compatibility;
- tags;
- business model;
- repository URL;
- changelog/release hash;
- extracted capability hashes;
- retrieval time;
- provenance URLs.

## Idempotency

Daily run identity:
`market-radar:<UTC-date>:<source-manifest-version>`

Repeated run on same snapshot should update the same daily issue/report, not create duplicate candidates.

Candidate identity:
`source-family + normalized-capability-key + source-version-band`.

## Failure policy

- one source failure does not invalidate successful sources;
- source failure is reported, never converted to empty/no-change truth;
- WordPress.org API failure blocks popularity/new-plugin conclusions for that run;
- AI failure leaves deterministic report available;
- publishing issue failure preserves artifact;
- timeout produces partial/failure report, not “scan complete”.

## Security

- no executing code from discovered plugins;
- no installing competitor plugins in the market-scan job;
- no checkout of arbitrary untrusted repositories into trusted script path;
- no secrets in generated reports;
- Safe HTTP/domain allowlist strategy;
- sanitize Markdown/issue content;
- pin/approve third-party Actions versions before executable adoption;
- untrusted candidate data is data, never shell arguments/code.

## Development gate

This document is an exact implementation plan only. No `.github/workflows/market-intelligence*.yml` executable file may be installed/enabled until explicit development authorization is granted and CI/security review approves the workflow.
