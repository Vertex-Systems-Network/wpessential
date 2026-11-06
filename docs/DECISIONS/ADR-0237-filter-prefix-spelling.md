# ADR-0237 — Filter prefix spelling `wpessential/apply_`

Status: **ACCEPTED**
Date: **2026-11-07**
Approval: owner request of 2026-11-07
Amends: ADR-0216 (custom filter prefix)

## Context

ADR-0216 fixed the custom filter prefix as `wpesential/apply_*`, spelled with one "s". The action prefix was `wpessential/hook_*`. The asymmetry was recorded as intentional public API that must not be silently corrected. The owner has now asked for it to be corrected before the 3.0.0 release.

## Decision

- `HookNames::FILTER_PREFIX` is `wpessential/apply_`, matching `HookNames::ACTION_PREFIX` (`wpessential/hook_`) and the plugin slug.
- The engineering-contracts validator, smoke test and conventions documents use the new spelling.

## Consequences

- Any code that hooked a `wpesential/apply_*` filter must use `wpessential/apply_*`. 3.0.0 is the first release of the platform, so no released version used the old prefix.
