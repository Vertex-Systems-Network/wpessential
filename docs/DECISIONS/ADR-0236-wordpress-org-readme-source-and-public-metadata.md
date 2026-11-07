# ADR-0236 — WordPress.org readme source and public metadata

Status: **ACCEPTED**
Date: **2026-11-07**
Approval: owner request of 2026-11-07, `GOV-OWNER-CONSENT-002`
Amends: ADR-0219 (public plugin metadata)

## Context

The owner set the public identity for the WordPress.org Free release:

- The company name is WPEssential.
- The WordPress.org account is `wpessential`.
- The version is 3.0.0.
- The description must be written for search.

The owner also asked for `readme.txt` to be generated from a Markdown file during the artifact build, so that only one readme is ever edited.

## Decision

1. **Single source.** `wordpress-org/readme.md` is the only readme to edit. `tools/release/readme-to-txt.php` turns it into the WordPress.org `readme.txt` format:
   - `#` becomes `=== ===`, `##` becomes `== ==` and `###` becomes `= =`.
   - `**Header:** value` lines become header fields.
   - `-` list items become `*`.
2. **Generated readme.txt.**
   - The release build writes `readme.txt` into the Free package from the Markdown source.
   - The committed root `readme.txt` is a generated copy, and `validate-engineering-contracts.php` fails when it is stale.
   - Regenerate it with `php tools/release/readme-to-txt.php`.
3. **Listing rules checked offline.** On every build and validation the tool checks:
   - the required header fields are present, including `Contributors`;
   - `Stable tag` equals the plugin `Version`;
   - there are at most 5 tags;
   - the short description is no longer than 150 characters;
   - the Description, Installation, Changelog and External services sections exist.
4. **Public metadata** (replaces ADR-0219 §Public plugin metadata, Author and description):
   - Author: `WPEssential`.
   - Contributors: `wpessential`.
   - Header description: "Create custom post types and taxonomies without code. Visual builder for labels, permalinks, REST API, archives, block templates and more."
5. **Listing assets.** Screenshots (`screenshot-N.png`), banners and icons for the WordPress.org SVN `assets/` folder live in `wordpress-org/assets/`. Their captions are the numbered `## Screenshots` list in `readme.md`.

## Consequences

- Editing `readme.txt` by hand fails validation; edit `wordpress-org/readme.md` instead.
- `wordpress-org/` is not part of the plugin zip.
