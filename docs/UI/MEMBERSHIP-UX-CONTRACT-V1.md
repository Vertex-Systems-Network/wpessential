# Membership — UX Contract V1

Surface: **15 / Membership**  
Issue: **#498**  
Lifecycle: planning-only; no runtime promotion.

## Essential

- **Plan identity** — stable plan name/key, lifecycle state and definition version.
- **Pricing model** — free, one-time or recurring representation with currency/interval display; no payment execution.

## Advanced

- **Membership state lifecycle** — active, pending, expired, cancelled and scheduled state/effective-date visibility.
- **Plan-change policy** — upgrade/downgrade/scheduled-change rules with preview and conflict feedback.
- **Restriction rules** — content/resource access policy references with effective-entitlement diagnostics.
- **Discount policy** — bounded coupon/discount definitions without exposing payment-provider internals.
- **Reporting summary** — privacy-aware membership and revenue aggregates with bounded date/status filters.

## Expert

- **Transaction reference** — read-only/redacted provider transaction and reconciliation identifiers, provider-health status and ownership mapping.

## Interaction contract

- Search/focus reaches all settings; states expose validation, provider-missing, conflict, saved and recovery feedback.
- Pricing and lifecycle changes show effective dates and downstream consequences before save.
- Restriction settings distinguish authored rule from effective authorization.
- Keyboard operation, semantic labels, visible focus and screen-reader status announcements are required.

## Security / multisite / compatibility / performance

- Entitlement and resource authorization remain server authoritative.
- No credentials, payment tokens or live provider actions are exposed in definitions or diagnostics.
- Site/network membership scope is explicit; cross-site entitlement inheritance is never assumed.
- Missing payment/provider integrations degrade safely without deleting membership state.
- Reports use bounded/paginated aggregation and avoid unbounded transaction scans.
- Optional assets load only on Membership surfaces.

This contract does not authorize payment execution, runtime implementation, deployment or certification.