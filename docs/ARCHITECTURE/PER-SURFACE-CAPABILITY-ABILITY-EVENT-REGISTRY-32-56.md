# WPEssential — Capability, Ability & Event Registry — Surfaces 32–56

Status: **Canonical supplement to the per-module registry / planning-only / no runtime authorization**  
Date: **2026-08-29**

## 1. Purpose

`PER-MODULE-CAPABILITY-ABILITY-EVENT-REGISTRY.md` covers the original surfaces through Platform 31. This supplement closes the current registry for **Surfaces 32–56**.

Rules inherited from the base registry:
- capability != menu visibility;
- Ability registration != REST/Workflow/CLI/AI exposure;
- every mutation rechecks resource Policy;
- event is a fact, not command;
- payloads never contain secrets/unnecessary PII;
- destructive/high-impact actions require distinct capability, preconditions, impact/recovery evidence;
- AI/MCP gets only separately allowlisted Abilities under the same principal/Policy.

## 32. Solution Blueprint & Application Composer (`solutions`)

**Capabilities**  
`wpe_solutions_read`, `wpe_solutions_create`, `wpe_solutions_update`, `wpe_solutions_publish`, `wpe_solutions_install_plan`, `wpe_solutions_install`, `wpe_solutions_upgrade_plan`, `wpe_solutions_upgrade`, `wpe_solutions_detach`, `wpe_solutions_export`.

**Abilities**  
`wpessential/solutions.list|get|create|update|duplicate|validate|publish|archive|export`,  
`wpessential/solutions.install-plan.preview|validate`,  
`wpessential/solutions.install.run|status|cancel`,  
`wpessential/solutions.mapping.preview`,  
`wpessential/solutions.drift.get`,  
`wpessential/solutions.upgrade-plan.preview|run`,  
`wpessential/solutions.detach.preview|run`.

**Events emitted**  
`wpessential.solution.blueprint.created|updated|published|archived`,  
`wpessential.solution.install.started|completed|failed|cancelled`,  
`wpessential.solution.binding.created|remapped`,  
`wpessential.solution.drift.detected`,  
`wpessential.solution.upgrade.started|completed|failed`,  
`wpessential.solution.detached`.

**Consumes** module definition/dependency/version events through public Definition contracts.  
**Boundary:** never mutate peer private storage; installation invokes owner import/create/map Abilities.

## 33. Analytics, Event Tracking & Journey Intelligence (`analytics`)

**Capabilities**  
`wpe_analytics_read`, `wpe_analytics_events_manage`, `wpe_analytics_tracking_manage`, `wpe_analytics_metrics_manage`, `wpe_analytics_funnels_manage`, `wpe_analytics_cohorts_manage`, `wpe_analytics_attribution_manage`, `wpe_analytics_export`, elevated `wpe_analytics_backfill`.

**Abilities**  
`wpessential/analytics.event-definition.list|get|create|update|validate|publish`,  
`wpessential/analytics.metric.list|get|create|update|preview|explain`,  
`wpessential/analytics.funnel.list|get|create|update|run`,  
`wpessential/analytics.cohort.list|get|create|update|run`,  
`wpessential/analytics.query.run`,  
`wpessential/analytics.backfill.preview|run`,  
`wpessential/analytics.export`.

**Events emitted**  
`wpessential.analytics.definition.created|updated`,  
`wpessential.analytics.ingestion.rejected`,  
`wpessential.analytics.backfill.started|completed|failed`,  
`wpessential.analytics.data_quality.degraded`.

Behavioral event occurrences are analytics data, not general Event Bus commands.  
**Boundary:** Audit/Observability events may be sampled/imported only by explicit data contract; analytics never grants authorization.

## 34. Search & Indexing Engine (`search`)

**Capabilities**  
`wpe_search_read`, `wpe_search_indexes_manage`, `wpe_search_rules_manage`, `wpe_search_reindex`, `wpe_search_logs_read`.

**Abilities**  
`wpessential/search.index.list|get|create|update|validate|publish|archive`,  
`wpessential/search.index.preview-schema`,  
`wpessential/search.reindex.preview|run|status|cancel`,  
`wpessential/search.query`,  
`wpessential/search.explain`,  
`wpessential/search.synonyms.list|get|create|update`,  
`wpessential/search.rules.list|get|create|update`.

**Events**  
`wpessential.search.index.created|updated|published`,  
`wpessential.search.reindex.started|completed|failed`,  
`wpessential.search.document.indexed|removed` only where volume policy permits,  
`wpessential.search.query.zero_result` analytics-class when enabled.

**Consumes** source entity change events for incremental indexing.  
**Boundary:** result delivery reauthorizes against source Policy; search index never source truth.

## 35. Decision, Formula, Scoring & Ranking Studio (`decision`)

**Capabilities**  
`wpe_decision_read`, `wpe_decision_definitions_manage`, `wpe_decision_evaluate`, `wpe_decision_simulate`, `wpe_decision_traces_read`.

**Abilities**  
`wpessential/decision.formula.list|get|create|update|validate|publish`,  
`wpessential/decision.scorecard.list|get|create|update|validate`,  
`wpessential/decision.table.list|get|create|update|validate`,  
`wpessential/decision.ranking.list|get|create|update`,  
`wpessential/decision.evaluate`,  
`wpessential/decision.simulate`,  
`wpessential/decision.explain`.

**Events**  
`wpessential.decision.definition.created|updated|published`,  
`wpessential.decision.evaluation.completed|rejected` as bounded/audit-class evidence,  
`wpessential.decision.simulation.completed`.

**Boundary:** evaluate returns typed result/trace only; it cannot directly grant access, post Ledger movements, reserve resources, mutate orders or execute arbitrary code.

## 36. Ledger, Balance & Movement Engine (`ledger`)

**Capabilities**  
`wpe_ledger_read`, `wpe_ledger_definitions_manage`, `wpe_ledger_post`, `wpe_ledger_hold`, `wpe_ledger_reverse`, `wpe_ledger_adjust`, `wpe_ledger_reconcile`, `wpe_ledger_export`.

**Abilities**  
`wpessential/ledger.list|get|create|update|validate`,  
`wpessential/ledger.account.list|get|create|update`,  
`wpessential/ledger.post.preview|run`,  
`wpessential/ledger.hold.create|release|commit`,  
`wpessential/ledger.reverse.preview|run`,  
`wpessential/ledger.balance.get|explain`,  
`wpessential/ledger.reconcile.preview|run`,  
`wpessential/ledger.statement.get|export`.

**Events**  
`wpessential.ledger.posting.committed|rejected`,  
`wpessential.ledger.hold.created|released|committed|expired`,  
`wpessential.ledger.transaction.reversed`,  
`wpessential.ledger.reconciliation.drift_detected|resolved`.

**Boundary:** no `set-balance` Ability; provider/bank/Woo settlement remains external/canonical adapter truth.

## 37. Resource Scheduling, Availability & Reservation Engine (`reservations`)

**Capabilities**  
`wpe_reservations_read`, `wpe_reservations_resources_manage`, `wpe_reservations_availability_manage`, `wpe_reservations_create`, `wpe_reservations_confirm`, `wpe_reservations_cancel`, `wpe_reservations_override`, `wpe_reservations_reconcile`.

**Abilities**  
`wpessential/reservations.resource.list|get|create|update`,  
`wpessential/reservations.availability.query|explain`,  
`wpessential/reservations.hold.create|extend|release`,  
`wpessential/reservations.booking.create|confirm|cancel|reschedule`,  
`wpessential/reservations.waitlist.join|leave|promote`,  
`wpessential/reservations.reconcile.preview|run`.

**Events**  
`wpessential.reservation.hold.created|expired|released`,  
`wpessential.reservation.created|confirmed|cancelled|rescheduled|completed`,  
`wpessential.reservation.capacity.conflict_detected`,  
`wpessential.reservation.reconciliation.drift_detected|resolved`.

**Boundary:** no Cron-based locking; confirmed reservation does not imply payment/order/ledger truth.

## 38. Experience Placement & Personalization Manager (`placement`)

**Capabilities**  
`wpe_placement_read`, `wpe_placement_slots_manage`, `wpe_placement_experiences_manage`, `wpe_placement_rules_manage`, `wpe_placement_preview`.

**Abilities**  
`wpessential/placement.slot.list|get|create|update`,  
`wpessential/placement.experience.list|get|create|update|validate|publish`,  
`wpessential/placement.resolve`,  
`wpessential/placement.preview|explain`,  
`wpessential/placement.dismiss`.

**Events**  
`wpessential.placement.experience.created|updated|published`,  
`wpessential.placement.resolved` only under bounded analytics policy,  
`wpessential.placement.dismissed`.

**Boundary:** resolution/visibility never authorizes protected content; actual renderer/data owner rechecks Policy.

## 39. Experimentation & Feature Rollout Manager (`experiments`)

**Capabilities**  
`wpe_experiments_read`, `wpe_experiments_manage`, `wpe_experiments_start_stop`, `wpe_experiments_rollout_manage`, `wpe_experiments_results_read`.

**Abilities**  
`wpessential/experiments.list|get|create|update|validate`,  
`wpessential/experiments.assignment.resolve`,  
`wpessential/experiments.exposure.record`,  
`wpessential/experiments.start|pause|stop`,  
`wpessential/experiments.rollout.update`,  
`wpessential/experiments.results.get|explain`,  
`wpessential/experiments.decision.record`.

**Events**  
`wpessential.experiment.started|paused|stopped`,  
`wpessential.experiment.assignment.created`,  
`wpessential.experiment.exposure.recorded` analytics-class,  
`wpessential.experiment.rollout.changed`,  
`wpessential.experiment.decision.recorded`.

**Boundary:** assignment != exposure != consent != entitlement; no security/authorization experiments.

## 40. Documents, Records & Template Generation (`documents`)

**Capabilities**  
`wpe_documents_read`, `wpe_documents_templates_manage`, `wpe_documents_generate`, `wpe_documents_issue`, `wpe_documents_amend`, `wpe_documents_share`, `wpe_documents_retention_manage`, high-risk `wpe_documents_legal_hold_manage` only where product policy permits.

**Abilities**  
`wpessential/documents.template.list|get|create|update|validate|preview|publish`,  
`wpessential/documents.generate.preview|run|status`,  
`wpessential/documents.record.get|list`,  
`wpessential/documents.issue.preview|run`,  
`wpessential/documents.amend|supersede|void`,  
`wpessential/documents.download.authorize`,  
`wpessential/documents.share.create|revoke`,  
`wpessential/documents.verify-checksum`.

**Events**  
`wpessential.document.generated`,  
`wpessential.document.record.issued|amended|superseded|voided`,  
`wpessential.document.share.created|revoked|expired`,  
`wpessential.document.external_signature.status_changed` only from verified provider adapter.

**Boundary:** generated/issued != legal signature, payment/order or source-record mutation.

## 41. Data Sync, ETL & Integration Pipelines (`sync`)

**Capabilities**  
`wpe_sync_read`, `wpe_sync_definitions_manage`, `wpe_sync_run`, `wpe_sync_pause`, `wpe_sync_replay`, `wpe_sync_conflicts_manage`, `wpe_sync_delete_propagation_manage`.

**Abilities**  
`wpessential/sync.list|get|create|update|validate`,  
`wpessential/sync.full.preview|run`,  
`wpessential/sync.incremental.run`,  
`wpessential/sync.pause|resume|cancel|status`,  
`wpessential/sync.conflict.list|get|resolve-preview|resolve`,  
`wpessential/sync.dead-letter.list|get|replay-preview|replay`,  
`wpessential/sync.reconcile.preview|run`.

**Events**  
`wpessential.sync.run.started|completed|failed|paused`,  
`wpessential.sync.item.applied|rejected` only under volume policy,  
`wpessential.sync.conflict.detected|resolved`,  
`wpessential.sync.outcome.unknown`,  
`wpessential.sync.reconciliation.completed`,  
`wpessential.sync.dead_letter.created|replayed`.

**Boundary:** connection credentials/Safe HTTP ->23; one-time import ->26; copy != source truth without explicit field/entity authority contract.

## 42. Geospatial, Location & Territory Engine (`geo`)

**Capabilities**  
`wpe_geo_read`, `wpe_geo_locations_manage`, `wpe_geo_territories_manage`, `wpe_geo_providers_manage`, `wpe_geo_precise_location_read`.

**Abilities**  
`wpessential/geo.location.normalize`,  
`wpessential/geo.geocode|reverse-geocode`,  
`wpessential/geo.distance.calculate`,  
`wpessential/geo.radius.query`,  
`wpessential/geo.territory.list|get|create|update|validate`,  
`wpessential/geo.territory.match|explain`,  
`wpessential/geo.route.estimate` through certified provider profile.

**Events**  
`wpessential.geo.location.geocoded`,  
`wpessential.geo.territory.created|updated`,  
`wpessential.geo.provider.degraded`,  
`wpessential.geo.match.completed` only where operationally justified.

**Boundary:** provider confidence/match is not identity/address/legal/serviceability proof; precise location Policy required.

## 43. AI Gateway, Knowledge & Copilot Studio (`ai`)

**Capabilities**  
`wpe_ai_read`, `wpe_ai_providers_manage`, `wpe_ai_models_manage`, `wpe_ai_prompts_manage`, `wpe_ai_knowledge_manage`, `wpe_ai_run`, `wpe_ai_evaluations_manage`, `wpe_ai_budgets_manage`, elevated `wpe_ai_mutation_allowlist_manage`.

**Abilities**  
`wpessential/ai.provider.list|get|test`,  
`wpessential/ai.model.list|get`,  
`wpessential/ai.task.list|get|create|update|validate`,  
`wpessential/ai.knowledge.list|get|create|update|reindex`,  
`wpessential/ai.run.preview|run|status`,  
`wpessential/ai.evaluation.run|get`,  
`wpessential/ai.budget.get|update`,  
`wpessential/ai.action-draft.validate|submit-for-approval`.

**Events**  
`wpessential.ai.run.started|completed|failed|blocked`,  
`wpessential.ai.budget.threshold_reached`,  
`wpessential.ai.evaluation.completed`,  
`wpessential.ai.action_draft.created|approved|rejected`.

**Boundary:** AI run never becomes privileged mutation; approved action invokes the target owner's Ability under same Policy.

## 44. URL Redirection & Routing Manager (`redirects`)

**Capabilities**  
`wpe_redirects_read`, `wpe_redirects_manage`, `wpe_redirects_publish`, `wpe_redirects_import`, `wpe_redirects_logs_read`.

**Abilities**  
`wpessential/redirects.list|get|create|update|duplicate|validate|simulate|publish|archive|import|export`,  
`wpessential/redirects.chain.check`,  
`wpessential/redirects.resolve|explain`.

**Events**  
`wpessential.redirect.rule.created|updated|published|archived`,  
`wpessential.redirect.loop.detected`,  
`wpessential.redirect.rule.matched` only under sampled diagnostics/analytics policy.

**Boundary:** generic redirect execution belongs here; access/auth requirement belongs Protector/Policy.

## 45. Search, Replace & Data Transformation (`transform`)

**Capabilities**  
`wpe_transform_read`, `wpe_transform_plans_manage`, `wpe_transform_preview`, high-risk `wpe_transform_execute`, `wpe_transform_rollback`.

**Abilities**  
`wpessential/transform.list|get|create|update|validate`,  
`wpessential/transform.dry-run`,  
`wpessential/transform.diff.get`,  
`wpessential/transform.execute|status|cancel`,  
`wpessential/transform.rollback.preview|run`,  
`wpessential/transform.reconcile`.

**Events**  
`wpessential.transform.run.started|completed|failed|cancelled`,  
`wpessential.transform.change.applied` bounded summary,  
`wpessential.transform.rollback.completed|failed`,  
`wpessential.transform.outcome.unknown`.

**Boundary:** no arbitrary PHP/eval/raw unbounded SQL; migration55/media28/import26 call this owner instead of local replace engines.

## 46. Dummy Data & Fixture Studio (`fixtures`)

**Capabilities**  
`wpe_fixtures_read`, `wpe_fixtures_manage`, `wpe_fixtures_generate`, `wpe_fixtures_export`, high-risk `wpe_fixtures_cleanup`.

**Abilities**  
`wpessential/fixtures.list|get|create|update|validate|preview`,  
`wpessential/fixtures.generate|status|cancel`,  
`wpessential/fixtures.export`,  
`wpessential/fixtures.cleanup.preview|run`.

**Events**  
`wpessential.fixture.definition.created|updated`,  
`wpessential.fixture.generation.started|completed|failed`,  
`wpessential.fixture.cleanup.completed|failed`.

**Boundary:** generated data marked synthetic/provenance; target writes use Data Source owner; no production truth claims.

## 47. Link Health & Crawl Intelligence (`link-health`)

**Capabilities**  
`wpe_link_health_read`, `wpe_link_health_profiles_manage`, `wpe_link_health_run`, `wpe_link_health_ignore`, `wpe_link_health_fix_plan_manage`, `wpe_link_health_provider_manage`.

**Abilities**  
`wpessential/link-health.profile.list|get|create|update`,  
`wpessential/link-health.scan.preview|run|status|cancel`,  
`wpessential/link-health.issue.list|get`,  
`wpessential/link-health.ignore|snooze|recheck`,  
`wpessential/link-health.fix-plan.preview|create`,  
`wpessential/link-health.result.explain`.

**Events**  
`wpessential.link_health.scan.started|completed|failed`,  
`wpessential.link_health.issue.detected|resolved|ignored`,  
`wpessential.link_health.result.inconclusive`,  
`wpessential.link_health.fix_plan.created`.

**Boundary:** fix execution goes Redirect44/Transform45/Media28; HTTP transport obeys Connections/Safe HTTP.

## 48. Database Maintenance & Cleanup (`db-maintenance`)

**Capabilities**  
`wpe_db_maintenance_read`, `wpe_db_maintenance_profiles_manage`, `wpe_db_maintenance_preview`, high-risk `wpe_db_maintenance_execute`, `wpe_db_maintenance_optimize`, `wpe_db_maintenance_owner_cleanup_approve`.

**Abilities**  
`wpessential/db-maintenance.health.get`,  
`wpessential/db-maintenance.candidates.scan|explain`,  
`wpessential/db-maintenance.plan.list|get|create|update|dry-run`,  
`wpessential/db-maintenance.run|status|cancel`,  
`wpessential/db-maintenance.optimize.preview|run`.

**Events**  
`wpessential.db_maintenance.candidates.scanned`,  
`wpessential.db_maintenance.run.started|completed|failed`,  
`wpessential.db_maintenance.owner_approval.required`.

**Boundary:** physical suspicion never authorizes deletion of domain records; owner cleanup contract/recovery required.

## 49. Admin Theme, Branding & Experience Manager (`admin-theme`)

**Capabilities**  
`wpe_admin_theme_read`, `wpe_admin_theme_manage`, `wpe_admin_theme_publish`, `wpe_admin_theme_assign`, `wpe_admin_theme_network_manage`.

**Abilities**  
`wpessential/admin-theme.list|get|create|update|validate|preview|publish|archive|export|import`,  
`wpessential/admin-theme.assignment.list|set|remove`,  
`wpessential/admin-theme.accessibility.check`,  
`wpessential/admin-theme.compatibility.check`.

**Events**  
`wpessential.admin_theme.created|updated|published`,  
`wpessential.admin_theme.assignment.changed`,  
`wpessential.admin_theme.compatibility.degraded`.

**Boundary:** branding/UI hiding does not alter authorization; fonts reference Surface53; no wp-admin template fork as authority.

## 50. Safe Script, Tag & Code Injection Manager (`safe-script`)

**Capabilities**  
`wpe_safe_script_read`, `wpe_safe_script_manage`, `wpe_safe_script_publish`, elevated `wpe_safe_script_inline_privileged`, `wpe_safe_script_emergency_pause`, `wpe_safe_script_import`.

**Abilities**  
`wpessential/safe-script.list|get|create|update|validate|preview|publish|pause|archive|export|import`,  
`wpessential/safe-script.conflicts.check`,  
`wpessential/safe-script.csp-plan.preview`.

**Events**  
`wpessential.safe_script.created|updated|published|paused|archived`,  
`wpessential.safe_script.blocked_by_policy`,  
`wpessential.safe_script.conflict.detected`.

**Boundary:** browser-side typed content only; PHP/eval/arbitrary SQL/shell/server-code rejected.

## 51. Content Order & Sequence Manager (`content-order`)

**Capabilities**  
`wpe_content_order_read`, `wpe_content_order_manage`, `wpe_content_order_reorder`, `wpe_content_order_publish`, `wpe_content_order_import`.

**Abilities**  
`wpessential/content-order.list|get|create|update|validate|publish|archive|export|import`,  
`wpessential/content-order.items.get`,  
`wpessential/content-order.reorder.preview|run`,  
`wpessential/content-order.explain`.

**Events**  
`wpessential.content_order.definition.created|updated|published`,  
`wpessential.content_order.sequence.changed`,  
`wpessential.content_order.conflict.detected`.

**Boundary:** owns persistent order only; Query sort stays6 and entity duplication follows source-owner Clone Plan.

## 52. Security Integrity, Malware & Vulnerability Scanner (`security-scanner`)

**Capabilities**  
`wpe_security_scanner_read`, `wpe_security_scanner_profiles_manage`, `wpe_security_scanner_run`, `wpe_security_scanner_findings_manage`, high-risk `wpe_security_scanner_quarantine`, `wpe_security_scanner_repair`.

**Abilities**  
`wpessential/security-scanner.baseline.list|get|create|refresh`,  
`wpessential/security-scanner.scan.preview|run|status|cancel`,  
`wpessential/security-scanner.finding.list|get|suppress|unsuppress`,  
`wpessential/security-scanner.quarantine.preview|run`,  
`wpessential/security-scanner.repair-plan.preview|run`,  
`wpessential/security-scanner.report.get`.

**Events**  
`wpessential.security_scan.started|completed|failed`,  
`wpessential.security_finding.detected|updated|suppressed`,  
`wpessential.security_quarantine.completed|failed`,  
`wpessential.security_repair.completed|failed`.

**Boundary:** finding/confidence != certainty; Protector27 owns request/access hardening; destructive remediation needs recovery.

## 53. Font Library, Typography & Delivery Manager (`fonts`)

**Capabilities**  
`wpe_fonts_read`, `wpe_fonts_manage`, `wpe_fonts_upload`, `wpe_fonts_provider_manage`, `wpe_fonts_assign`, `wpe_fonts_optimize`.

**Abilities**  
`wpessential/fonts.list|get|create|update|validate|archive|export|import`,  
`wpessential/fonts.upload.preview|run`,  
`wpessential/fonts.provider.sync`,  
`wpessential/fonts.assignment.list|set|remove`,  
`wpessential/fonts.subset.preview|run`,  
`wpessential/fonts.delivery.explain`,  
`wpessential/fonts.license.get`.

**Events**  
`wpessential.font.created|updated|archived`,  
`wpessential.font.asset.generated`,  
`wpessential.font.assignment.changed`,  
`wpessential.font.provider.degraded`.

**Boundary:** provenance/local hosting != legal redistribution authority; consumers store font reference IDs only.

## 54. User Data Stores, Favorites & Collections (`user-stores`)

**Capabilities**  
`wpe_user_stores_read`, `wpe_user_stores_definitions_manage`, `wpe_user_stores_use`, `wpe_user_stores_shared_manage`, `wpe_user_stores_privacy_manage`.

**Abilities**  
`wpessential/user-stores.definition.list|get|create|update|validate`,  
`wpessential/user-stores.items.list|add|remove|toggle|reorder`,  
`wpessential/user-stores.merge.preview|run`,  
`wpessential/user-stores.share.create|revoke`,  
`wpessential/user-stores.export|erase`.

**Events**  
`wpessential.user_store.item.added|removed|reordered`,  
`wpessential.user_store.guest_merged`,  
`wpessential.user_store.share.created|revoked`,  
`wpessential.user_store.expired`.

**Boundary:** favorite/wishlist/compare state != Woo cart/order/payment/stock or Membership entitlement.

## 55. Staging, Clone & Migration Manager (`staging`)

**Capabilities**  
`wpe_staging_read`, `wpe_staging_environments_manage`, `wpe_staging_clone`, `wpe_staging_push`, `wpe_staging_pull`, `wpe_staging_cutover`, `wpe_staging_recover`, high-risk `wpe_staging_production_write`.

**Abilities**  
`wpessential/staging.environment.list|get|create-plan|create|status`,  
`wpessential/staging.clone.preview|run`,  
`wpessential/staging.push.preview|run`,  
`wpessential/staging.pull.preview|run`,  
`wpessential/staging.drift.scan|explain`,  
`wpessential/staging.cutover.preview|run`,  
`wpessential/staging.recover.preview|run`.

**Events**  
`wpessential.staging.environment.created|disabled|deleted`,  
`wpessential.staging.clone.started|completed|failed`,  
`wpessential.staging.transfer.started|completed|failed`,  
`wpessential.staging.drift.detected`,  
`wpessential.staging.cutover.started|completed|failed`.

**Boundary:** Backup24 owns artifacts; Transform45 serialized/data replacement; cloned provider credentials/webhooks/identity quarantined; clone != same environment.

## 56. Theme Workspace, Child Theme & Theme Customization Manager (`theme-workspace`)

**Capabilities**  
`wpe_theme_workspace_read`, `wpe_theme_workspace_analyze`, `wpe_theme_workspace_child_manage`, `wpe_theme_workspace_styles_manage`, `wpe_theme_workspace_templates_manage`, `wpe_theme_workspace_package`, high-risk `wpe_theme_workspace_activate`, `wpe_theme_workspace_network_manage`.

**Abilities**  
`wpessential/theme-workspace.themes.list|get|analyze`,  
`wpessential/theme-workspace.child.create-plan|create|duplicate`,  
`wpessential/theme-workspace.css.validate|preview|update`,  
`wpessential/theme-workspace.theme-json.validate|preview|update`,  
`wpessential/theme-workspace.template.list|get|override-preview|override`,  
`wpessential/theme-workspace.drift.scan|explain`,  
`wpessential/theme-workspace.package.export|import-preview|import`,  
`wpessential/theme-workspace.activation.preview|run|recover`.

**Events**  
`wpessential.theme_workspace.child.created|updated`,  
`wpessential.theme_workspace.styles.changed`,  
`wpessential.theme_workspace.template.overridden`,  
`wpessential.theme_workspace.parent_drift.detected`,  
`wpessential.theme_workspace.package.imported|exported`,  
`wpessential.theme_workspace.theme.activated|activation_failed|recovered`.

**Boundary:** no arbitrary live PHP/eval editor; Fonts53/Media28 referenced; Safe Script50 owns injected runtime snippets; Admin Theme49 owns wp-admin branding.

## 57. Cross-surface invocation law

Although the numbering ends at 56, the following law applies to the complete 1–56 registry:

`Presentation channel (UI/REST/Workflow/CLI/AI) → registered Ability descriptor → capability + resource Policy → canonical execution owner → Event/Audit`

No channel may call a peer's private service/table because it has equivalent credentials.

## 58. Exposure matrices required before implementation

A future implementation milestone must separately mark each Ability as:
- internal only;
- UI callable;
- REST exposed;
- Workflow action/trigger eligible;
- WP-CLI eligible;
- AI/MCP read/draft/mutate eligible;
- destructive/high-risk;
- async-only/sync-allowed;
- provider certification dependent.

Default for mutation/destructive AI exposure is **off**.
