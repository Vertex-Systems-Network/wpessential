# 2026-11-28 — WP8 responsive menu handoff / companion lint correction

- Theme `dc7a8f4f` strict WPCS failed on seven alignment warnings. Successor `0036bf9c` corrects alignment and accepts the separate responsive menu handshake. Free `5b30589c` adds guarded menu size/gap transfer through the stable numeric mapping, retaining legacy source and rejecting differing canonical values; fresh exact paired fixture pinned. Unit/browser regression and README/plan included. RB-0134 remains open, no merge/release or full legacy parity claim.

# 2026-11-28 — WP8 canonical breakpoint bridge candidate

- Free `b00694a3` passed 8/8 workflows, including WordPress E2E Audit 46085048723; existing theme `c53b27b6` CI previously passed. New Free bridge source `3a742bb7` pins theme `dc7a8f4f` and adds versioned breakpoint ownership, frozen numeric device mapping, canonical conflict deferral and live theme control synchronization; legacy field values stay unchanged. New unit and paired browser regression, plan and README update included in this batch. Exact-head CI remains required; RB-0134 open. No release or production migration.

# 2026-11-28 — WP8 smallest-phone representation and CI correction

- Free `640a16ad` failed PHP Quality and Architecture due to PHPCS multiline-call formatting; corrected the reported formatting errors in `f25a2e26`. Added disabled-by-default `mobile_tiny` slot (359px maximum) to preserve both legacy 320/360 boundaries, retaining existing canonical `mobile_small` behavior. Shared controls, compiler/unit tests and paired review updated; added native Customizer preview/save regression for independent device values. Legacy responsive field ownership remains review-only, no migration or merge. Exact final candidate CI pending; RB-0134 open.

# 2026-11-28 — WP8 consolidated local-cache candidate

- Free `9257bf5c` passed 8/8 workflows and 10/10 checks; theme `c53b27b6` CI passed. Free #1603 advanced to `18637d86` (font reference guard); its exact verification is not assumed. Local-cache handoff candidate validates existing files/metadata and requires confirmed canonical readback before ownership. No production migration. Legacy breakpoint ownership remains deliberately deferred due to incompatible numeric slots and responsive values; preserve original data. Final consolidated CI required; RB-0134 open.

# 2026-11-27 — WP8 shared Customizer and theme palette companion

- WP8 #1602 / #1603 shared scalar, responsive and composite controls use native changesets and the canonical option. `27fa040f` PHPStan rejected inherited constructor arguments (PHP Quality and Architecture); `ba54a806` corrects the constructor. `5323b3be` adds capability/nonce-gated palette-only ownership transfer, keeps legacy values and existing canonical conflicts, and retains reset ownership. Theme companion hi-wpessential #129 (`0ccd33cd`, issue #128) consumes the owned-key contract, suppressing only owned palette controls/legacy compiler values and preserving standalone behavior. Actual combined browser, preview, save/reset and paired CI evidence pending; non-palette legacy formats remain unresolved.
- No production migration, no release. Resume RB-0134; both draft PRs require exact-head and paired evidence before integration.

# 2026-11-27 — Bounded WP7 integrated / WP8 active

- Free #1600 exact head 8a3ea579 passed 8/8 workflows, 10/10 checks, WordPress E2E 45993977471 46/46; zero behind and no review blockers. Merged ab80f77de428f95a530ddc6cc3ec7356179786a3; #1599 closed. PHP #1601 planning-only, no PHP runtime.
- WP8 #1602 / #1603 first source 79a5bae7: schema-derived scalar settings on canonical option paths, sanitizer and save lifecycle. Responsive/composite shared controls, theme legacy reconciliation and real browser acceptance remain. No release or P-006 promotion. Resume RB-0134.

# 2026-11-27 — WP7 bounded browser-language acceptance / PHP follow-up

- Free #1600 `dfbf6d9c` passed 8/8 workflows (WordPress E2E 45992599478). `2163bb82` adds browser regression for scoped output, priority, Safe Mode, admin/login and opaque-row preservation. Fresh exact-head CI required.
- #1601 tracks the separately gated PHP developer-code architecture; no runtime authorization. Bounded browser-language WP7 can integrate after acceptance; WP8 independent of PHP. No production release or product certification.

# 2026-11-27 — ADR-0239 WP6 integrated / WP7 active

- WP6 Free #1598 exact candidate 8340b00c: 8/8 workflows and WordPress E2E 45954508648 44/44 PASS; merged e93386c2f663cdd0cd1027a8513949ad39fe1ba9. #1597 closed. Actual frontend global styling, responsive button scale, local palette override, applied shadow, named shadow admin lifecycle and Elementor registered picker verified. Raw local HEX, full shared React and complete editor/theme parity remain WP9/WP12; no new release or product certification.
- WP7 #1599 / draft #1600 first commit 7e259e39 adds conditions, priority, protected rows and disabled new snippets. First PHP Quality/Architecture failed on one PHPDoc blank-line error; 21e66481 corrects it and adds scoped body/content/admin/login hooks and KSES-filtered HTML. Legacy CSS remains an enqueued stylesheet; admin/login require all-pages conditions and privileged writes. Exact-head CI and browser acceptance pending. SCSS-lite, richer manager, PHP Pro-only boundary and runtime acceptance remain. Free must not execute PHP.
- Resume RB-0133 and keep unrelated formal P-006 and UI lanes separate.

# 2026-11-27 — ADR-0239 WP5 integrated / WP6 first slice

- Free #1596 exact candidate 60a79623: 8/8 workflows, 10/10 checks, 42/42 wp-env E2E (45945992115). Paired Pro validation #36 pinned that candidate; 49/49 E2E (45948684490), Pro CI passed. Pro #34 merged b8ff3ce0, theme #127 merged 32b75373, Free #1596 merged d44f69672c525468282dd54c28f7a05905d5369a. Temporary Pro #36 closed without merge; WP5 #1595 closed.
- WP5 scope: named light/dark colours and responsive typography, block render references and actual Elementor admin control metadata, entitlement gate; bounded Elementor native mapping and retention of deleted managed globals remain documented limitations. Full React dropdown, editor/frontend/theme parity stay WP9/WP12. No release or formal P-006 certification.
- WP6 #1597 / draft #1598 from merged Free main: first image/link source slice 6646ffaf (responsive radius/caption, border, object fit, link states/underline style and unit tests). Remaining forms, buttons, background, named shadows and integration acceptance pending. Fresh exact-head CI required. Resume RB-0132.

Previous compact checkpoint archived at `docs/CHECKPOINT-ARCHIVE/LAST-CHECKPOINT-2026-11-27-pre-wp6.md`.
