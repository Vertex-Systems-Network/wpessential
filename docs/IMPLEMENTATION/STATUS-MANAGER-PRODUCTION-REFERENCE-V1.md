# Status Manager Production Reference V1

Issue: #367  
Claim anchor: `main @ 79d12395e78d8d194011c35eba99e2378a172fd3`

## Purpose

This reference proves the promoted bounded Status runtime through a real WordPress request lifecycle. It does not substitute private registries, authorization helpers, transition executors or direct registrar calls for production composition.

## Two-request fixture

The dedicated workflow deliberately uses two PHP processes:

1. `prepare-wordpress-status-reference.php` installs the clean WordPress fixture and creates the test administrator/contributor identities.
2. `wordpress-status-reference-application.php` starts a fresh WordPress request with a fixture-only mu-plugin. The mu-plugin:
   - loads the repository autoloader;
   - explicitly admits the Pro Status module while preserving Free module admission;
   - contributes the production `StatusModule` before WPEssential boot;
   - at `plugins_loaded` priority `-200`, creates only the existing Definition persistence tables and seeds canonical revision-1 Published `status` and `status-transition-policy` Definitions;
   - loads the normal `wpessential.php` entrypoint, whose `plugins_loaded` priority `-100` callback boots the production Plugin/Kernel;
   - allows the production Status registrar installed by `StatusModule` to run naturally on WordPress `init` priority 20.

This ordering exists so registration evidence is lifecycle-real rather than produced by manually invoking `StatusNativeRegistrar::registerActive()` after WordPress has already initialized.

## Production evidence

The reference asserts:

- `Plugin::kernel()` is the production Kernel and the explicitly admitted `status` module reaches `ModuleState::Booted`;
- Status consumes the production persistent Definition repository, Ability registry, shared WordPress capability checker and shared post-resource authorizer;
- `module.status.transition-executor` and `wpessential/status/transition` are published by the production module;
- `review-ready` is registered and discoverable through real WordPress status APIs with its canonical label;
- Core `publish`, `future`, `trash` and `inherit` registrations remain present;
- a real draft post transitions to `review-ready` only through the registered Status Ability and verified `wp_update_post()` path;
- stale expected state, undeclared reverse transition, Core-trash lifecycle misuse and post-type applicability mismatch fail closed without mutation;
- a contributor cannot mutate another user's post through the Status Ability;
- a server-context site mismatch fails through canonical WordPress authorization without changing post state.

## Compatibility matrix

`.github/workflows/status-reference-application.yml` runs the exact PR head on:

- WordPress 6.9 and 7.1 × PHP 8.2, 8.3, 8.4 and 8.5 with MySQL 8.4;
- WordPress 6.9 and 7.1 × PHP 8.4 with MariaDB 10.11.

All jobs verify exact checkout SHA before installing the locked Composer graph and running the two-process reference.

## Boundaries

This reference does not certify Status admin authoring, portability, Event/Audit service composition, Workflow/Notifications/Cron ownership, arbitrary provider/domain status semantics, full Atomic Option parity, deployment or release readiness. Those remain separately gated.
