# Status Native WordPress Audit — Candidate V1

Snapshot: 2026-09-01  
Surface: 5 — `status`  
Candidate status: **`NATIVE_AUDIT_IN_PROGRESS`**  
Audit items: **35**  
Unresolved research items: **0**

## Why certification remains in progress

The native evidence has been dispositioned with zero unresolved research items, but Surface 5 has no dedicated exact executable native-audit smoke registered in shared Composer/test wiring. Under the one-writer/no-shared-race rules, this surface worker does not mutate that shared infrastructure. Therefore this branch deliberately does **not** claim `NATIVE_AUDITED`.

## Coverage

| Disposition | Count |
| --- | ---: |
| `BANK_RECORD` | 32 |
| `SYSTEM_RUNTIME` | 1 |
| `CORE_INTERNAL` | 2 |
| `PROVIDER_MAPPING` | 0 |
| `OUT_OF_SURFACE` | 0 |
| `LEGACY_COMPATIBILITY` | 0 |
| `UNRESOLVED` | 0 |

## Native conclusions

1. `register_post_status()` is the canonical registration source, but it does not own per-post-type applicability or complete editor integration.
2. WPE must preserve Core's default-resolution semantics rather than force-writing every visibility flag.
3. `_builtin` is Core-only; authoring it is rejected.
4. Persistable status keys are bounded by Core's `wp_posts.post_status varchar(20)` schema.
5. Core transition hooks are event sources, not permission/state-machine enforcement. Same-status post updates must not be treated as actual transitions.
6. REST status discovery is a read surface; Status mutation must remain an owner-validated transition operation.
7. `future`, trash/untrash, and attachment `inherit` are special compatibility paths with explicit native behavior.
8. Direct use of private `_transition_post_status()` is rejected as an extension contract.

## Evidence set

- https://developer.wordpress.org/reference/functions/register_post_status/
- https://developer.wordpress.org/reference/functions/wp_get_db_schema/
- https://developer.wordpress.org/reference/functions/get_post_stati/
- https://developer.wordpress.org/reference/functions/wp_count_posts/
- https://developer.wordpress.org/reference/hooks/transition_post_status/
- https://developer.wordpress.org/reference/hooks/old_status_to_new_status/
- https://developer.wordpress.org/reference/hooks/new_status_post-post_type/
- https://developer.wordpress.org/reference/classes/wp_rest_post_statuses_controller/
- https://developer.wordpress.org/reference/classes/wp_rest_posts_controller/handle_status_param/
- https://developer.wordpress.org/reference/functions/create_initial_post_types/
- https://developer.wordpress.org/reference/functions/check_and_publish_future_post/
- https://developer.wordpress.org/reference/functions/wp_trash_post/
- https://developer.wordpress.org/reference/functions/wp_untrash_post/

Machine-readable candidate: `config/product/options-bank-audits/status-native-wordpress.json`.
