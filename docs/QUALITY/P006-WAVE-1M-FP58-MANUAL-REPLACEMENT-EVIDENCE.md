# P-006 Wave 1M — FP-58 WordPress Manual Replacement Evidence

Issue: #1046

Authorization: `GOV-P001-CF-TEMP-011`

Fixture: **FP-58 — manual upload/replacement order gets the same compatibility guarantees as automated update**

Status: **TERMINAL BOUNDED PASS / NON-CERTIFYING**

## Scope

This tranche executes only FP-58 through the WordPress-owned local ZIP overwrite path already validated as a prerequisite by Issue #1043 / PR #1045:

`Plugin_Upgrader::install($localZip, ['overwrite_package' => true])`

The live plugin roots are real directories and CI uses `FS_METHOD=direct`.

The Wave 1K/1L symlink-target transport is not used as FP-58 evidence.

## Runtime cells

- WordPress 6.9 / PHP 8.2 / MySQL 8.4
- WordPress 7.1 / PHP 8.5 / MySQL 8.4

## Authorized subpaths

Each runtime cell runs four isolated disposable scenarios:

1. compatible Free-first: F0/P0 → F1/P0, expected `compatible`;
2. compatible Pro-first: F0/P0 → F0/P1, expected `compatible`;
3. breaking Free-first: F1/P0 → F2/P0, expected `free_version_too_new`;
4. breaking Pro-first: F1/P0 → F1/P2, expected `free_version_too_old`.

Every scenario starts from a fresh disposable WordPress + MySQL environment and uses a fresh PHP process for baseline and post-overwrite observations.

## Deterministic graph

The exact-source NON-RELEASE / TEST-ONLY graph contains:

- F0 — Free 0.1.0-dev / Platform API 0.1.0 / schema 1;
- F1 — Free 0.1.1-test-overlap / Platform API 0.1.0 / schema 1;
- F2 — Free 0.2.0-test-breaking / Platform API 0.2.0 / schema 1;
- P0 — Pro 0.1.0-dev supporting Free 0.1.0-dev…0.1.1-test-overlap / Platform API 0.1.0 / schemas 1;
- P1 — Pro 0.1.1-test-overlap with the same overlap contract;
- P2 — Pro 0.2.0-test-breaking supporting exact Free 0.2.0-test-breaking / Platform API 0.2.0 / schemas 1.

Only the main plugin entry may differ from the canonical source package for each test variant.

## Required compatible guarantees

For compatible manual replacement paths:

- compatibility state remains `compatible`;
- premium boot remains allowed;
- premium migration admission remains allowed;
- expected premium module set remains registered;
- Free `custom-post-types` and `taxonomies` remain registered;
- Free and Pro remain active;
- exact artifact and payload-tree identity matches the pinned graph;
- no compatibility persistence keys appear;
- outbound WordPress HTTP attempts remain zero.

## Required breaking guarantees

For breaking manual replacement paths:

- exact mismatch state is reported;
- premium boot is denied;
- premium migration admission is denied;
- premium modules remain inert/empty;
- Free kernel remains booted;
- Free `custom-post-types` and `taxonomies` remain registered;
- Free and Pro remain active as plugin registrations while Pro fails closed internally;
- exact artifact and payload-tree identity matches the pinned graph;
- no compatibility persistence keys appear;
- outbound WordPress HTTP attempts remain zero;
- no fatal is observed.

## Transport evidence

Each scenario records:

- exact target ZIP SHA-256;
- exact before/after live payload-tree digest;
- counterpart payload-tree preservation;
- exact Free/Pro pair identity;
- WordPress/PHP/MySQL versions;
- WordPress filesystem method;
- `Plugin_Upgrader::install()` result;
- active plugin state;
- non-symlink live plugin roots;
- temporary plugin-backup residue;
- network attempts;
- compatibility persistence scan.

## Timeout-safe CI

- combined graph build: <=15 minutes;
- each scenario/runtime cell: <=18 minutes;
- `fail-fast:false`;
- stale-run cancellation;
- immutable per-scenario artifacts;
- terminal FP-58 marker only after the full matrix succeeds.

## Non-promotion boundary

This tranche does not authorize or certify:

- FP-49…52;
- partial/truncated/missing-file interruption handling;
- automatic updater/TUF;
- rollback/downgrade;
- migration recovery;
- stale/concurrent requests;
- provider/license/billing/allocation behavior;
- multisite;
- production/live systems;
- destructive actions;
- deploy/release;
- a certified Free/Pro pair;
- runtime certification;
- ADR-0010 acceptance.

Before terminal execution the formal P-006 accounting remains:

**144 documented / 45 executed / 44 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

If and only if the entire authorized FP-58 matrix reaches bounded PASS, closeout accounting becomes:

**144 documented / 46 executed / 45 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.


## Terminal execution result

Initial exact evidence head:

`400efc86ef2bceecfbc138e8678519371f7d1924`

Initial exact-head checks:

- Governance Gate run **35396546234 — PASS**;
- P-006 Wave 1M FP-58 run **35396546267 — PASS**;
- deterministic combined graph build — **PASS**;
- all eight authorized scenario/runtime cells — **PASS**;
- FP-58 terminal aggregate — **PASS**.

### Pinned graph identities

| Node | ZIP SHA-256 | Payload tree SHA-256 | Version / API |
| --- | --- | --- | --- |
| F0 | `2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80` | `0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9` | 0.1.0-dev / 0.1.0 |
| F1 | `f9401f81b4a65d6f610b05cd445d3d8bdec75b2996296e0deaa13a8a2a265185` | `3e8e56d3887b1f6ed29a66beb0a489d44e547d8b5104c6819e41b91bb6518460` | 0.1.1-test-overlap / 0.1.0 |
| F2 | `92e54db0220ea76323fcf2b0e3772ea31e379dac47ed62690b027edbccbff4dc` | `a19b300607420d77b7751334a9a4ea2ad1570ff9078aaa6938e5d32755cce42e` | 0.2.0-test-breaking / 0.2.0 |
| P0 | `0b12ada8265f3032641e09a80ba09f9950d3aef8b526b63c2b3665663aca4178` | `08ef083162109a15899dba7b14d1e420f327323b20cd1434cf9e7cfa23af09a7` | 0.1.0-dev / API 0.1.0 |
| P1 | `96acfb8527e62f3ee52f8ef61b801f770315a3a633502ead845c5c4f145b30ac` | `d9a2d208871d684d50e4eabcdb7d4ffb0e2f51cff160ebb311e47d210c3d2a46` | 0.1.1-test-overlap / API 0.1.0 |
| P2 | `2a56eb653a3e685a76a6c43e5f22cebfe92b0827b77475bdf2242afd094478c3` | `16e4a2a6fe6ca1b1c29d39e47186c83af6a9ad7e51bd09e8e0765bb8be639289` | 0.2.0-test-breaking / API 0.2.0 |

Pinned pair identities:

- F0/P0 — `c564b2fdc079a02dbba57bee0d90dd64fd683605c0fdbca89ab48323b394f2a0` — `compatible`;
- F1/P0 — `f5157c46d4a29af6df4929cafeaad831fc50a8d38e469c2df73340766bb3faef` — `compatible`;
- F0/P1 — `532f93f984bb5ecee1793a4c325c904e84945e35f1f5f682e20b59cbdbc58b26` — `compatible`;
- F2/P0 — `72f260a4882bb04aaf05a8a4960aaaf7fa2bba0f83591aeb79f5ece4f303480c` — `free_version_too_new`;
- F1/P2 — `a3bb184caaef86f4c303c2cf73ba5f7631d711cae4e280cbca035e95937f6501` — `free_version_too_old`.

### Initial immutable evidence artifacts

- candidate graph: id **10567872128**, digest `sha256:72ba53aba032bf37c26d4ec2a2ccee29d96d834ea906d2bf377083aa1730c6c8`;
- minimum compatible Free: id **10567429483**, digest `sha256:f14d2d90388d17162c37ca6fb6022e67e33e5d54cb594518988dada03fdc8317`;
- minimum compatible Pro: id **10567144590**, digest `sha256:0350ab08d7a8a9ea56f363f12c99a50a51f93135f35a306244659d3171a45180`;
- minimum breaking Free: id **10568142044**, digest `sha256:98193c8d08c306c3213f9342f3076f503c338c2972452b0c85c605558a1e09c7`;
- minimum breaking Pro: id **10567862207**, digest `sha256:8a9591e81f0a9b34475b3679f2a2dc83e8cca271d0af67fdc279b953597d0944`;
- reference compatible Free: id **10567234817**, digest `sha256:13d32734484b7d97d1516abec4281ff2d7270ddae4103588af4f28e4b025004b`;
- reference compatible Pro: id **10568142097**, digest `sha256:47ec5c193d5ecaa464c5974eec16d78040746f139e9e6eccc922c7bf86302594`;
- reference breaking Free: id **10567717372**, digest `sha256:954b7885abe0299dc460057643fdc9d28e6a60b6a055fad1ca48a53a18231325`;
- reference breaking Pro: id **10568117088**, digest `sha256:8c235eebd3102cdf9710788d816d7682f22633b020909ebd02db8a77406c5132`;
- terminal marker: id **10567559315**, digest `sha256:e486f57f66826eb6b3cccf898895a4cdd3048492587ff667c9a7b6884cf0231a`.

### Formal FP-58 outcome

All eight authorized cells reached bounded **PASS**.

Compatible manual replacement paths:

- F0/P0 → F1/P0 remained `compatible` on minimum and reference;
- F0/P0 → F0/P1 remained `compatible` on minimum and reference;
- premium boot remained allowed;
- premium migration admission remained allowed;
- the full expected premium module set remained registered;
- Free `custom-post-types` and `taxonomies` remained registered.

Breaking manual replacement paths:

- F1/P0 → F2/P0 failed closed as `free_version_too_new` on minimum and reference;
- F1/P0 → F1/P2 failed closed as `free_version_too_old` on minimum and reference;
- premium boot was denied;
- premium migration admission was denied;
- premium modules were inert/empty;
- Free kernel remained booted;
- Free `custom-post-types` and `taxonomies` remained registered.

Across all paths:

- WordPress core `Plugin_Upgrader::install()` returned success;
- filesystem method was `direct`;
- exact target payload trees matched the pinned graph;
- counterpart payload trees remained unchanged;
- live plugin roots were real directories;
- Free and Pro remained active as plugin registrations;
- compatibility persistence keys remained empty;
- temporary plugin-backup residue remained empty;
- outbound WordPress HTTP attempts were **0**;
- no fatal was observed.

Formal P-006 accounting after this bounded result:

**144 documented / 46 executed / 45 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified Free/Pro pairs / 0 runtime certifications**.

`GOV-P001-CF-TEMP-011` is consumed by this tranche and is non-reusable.

FP-58 PASS does not promote a certified pair/runtime, automatic updater/TUF behavior, rollback/migration behavior, partial/interrupted package safety, ADR-0010 acceptance, GA, release or deployment authority.
