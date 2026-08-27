# ADR-0104 — Admin Menu Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28

## Decision

Custom Admin Menu Builder cannot be called production-ready until a future implementation passes `docs/QUALITY/ADMIN-MENU-EXECUTABLE-EVIDENCE-PROTOCOL.md`.

The evidence contract preserves the accepted architecture:

`WordPress/plugin runtime menu registration → normalized discovered registry → stable WPE transformation rules → capability/recovery validation → transformed presentation + diagnostics`.

Required evidence covers:
- Site vs Network vs optional User Admin scope;
- current WordPress `custom_menu_order`/`menu_order` composition;
- unmentioned/late third-party entries;
- rename/reorder/hide/move/add/link/group transformations;
- ambiguous/missing/changed target identity;
- conflicting WPE/third-party rules;
- direct URL authorization independence from menu visibility;
- unsafe external URL rejection;
- WPE recovery-page invariant and fail-open original-navigation safe mode;
- role/user/global precedence;
- Multisite same-slug isolation;
- import/deactivation/reactivation behavior;
- per-admin-request performance and asset scoping.

Menu hiding remains presentation only and never becomes a screen-security claim.

## Current state

AM-01…AM-40 documented. **0/40 executed.**

## Development gate

No `admin_menu`, `network_admin_menu`, `custom_menu_order`, `menu_order`, raw menu transformation, safe-mode runtime or benchmark is authorized before explicit owner consent under ADR-0014.