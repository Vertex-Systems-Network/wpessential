# Canonical Edition Metadata + Local Entitlement Domain V1

Issue: #908

## Scope

This gate introduces provider-neutral local entitlement truth after the physical Free/Pro package split.

States are explicit and non-collapsed: `free`, `trial_active`, `pro_active`, `grace`, `expired`, `suspended`, `verification_stale`, `verification_unavailable`, and `incompatible_version`.

Free module activation is entitlement-independent. Pro module activation is denied only for `free` and `incompatible_version`; expiry, suspension and temporary verification failures may preserve read-safe module boot so owned definitions/data are not destructively hidden. Premium mutation authority is separate: only `trial_active`, `pro_active`, and `grace` permit premium mutation in this V1 policy. Expired, suspended, stale and unavailable states remain distinct and fail closed for premium mutation.

The Pro bootstrap defaults to `verification_unavailable` when no deterministic local state is supplied. It installs the entitlement-aware module activation policy through the existing pre-boot `Plugin::setModuleActivationPolicy()` seam before contributing Pro modules. The optional local override is `WPE_PRO_LOCAL_ENTITLEMENT_STATE`; this is a local deterministic development/integration seam, not a license credential or remote provider response.

## Explicit non-goals

No HTTP licensing, billing/provider integration, secrets/tokens, destructive expiry handling, certified Free/Pro compatibility pair, admin Modules inventory, multisite allocation policy, deployment or release is introduced here.

Membership/user authentication is not entitlement truth.
