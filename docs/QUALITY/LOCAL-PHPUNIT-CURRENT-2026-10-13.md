# WPEssential local PHPUnit current checkpoint — 2026-10-13

Status: **LOCAL WIP; NOT FULL-SUITE PASS; NOT MERGE-READY.**

- Full PHPUnit 11.5 on PHP 8.5.4: **1,421 tests, 7,445 assertions, 14 assertion failures, 7 PHPUnit deprecations**. No PHPUnit runtime errors or PHP warnings in this run. PHPUnit exit 1.
- First local full run before WIP correction: 1,418 tests, 6,042 assertions, 187 errors, 41 failures.
- TypeScript typecheck, Architecture Guards and npm build passed on this same local working tree; focused regressions are green.
- Recently fixed: Dashboard reset meta fixture uses normalized `dashboard_network` key; three uppercase-ID tests now use a UUID with alphabetic hex to make uppercasing material; required post-meta fixture correctly places `required` inside Field settings.
- Relation Definition handler now preserves authored Direction, including parent relation and traversal settings, when a basic-edit payload omits the `direction` object entirely. Explicit authored Direction still passes through canonical validation.
- Query Field/Relation resolvers now provide consistent finite post-id anchor error diagnostics without changing the fail-closed anchor requirement.
- No repository checksum, revision, capability, provider, migration or production safety gate was weakened.
- Previous checkpoint docs retain historical counts; this is the latest local truth.

## Remaining 14 failing test cases

1. `WPEssential\Tests\Unit\Modules\AdminColumns\AdminColumnsFrontendFieldsBulkEditContractTest::testBulkMutationKeepsOwnerAndSavedViewGuardsForEverySelectedRow`
2. `WPEssential\Tests\Unit\Modules\AdminColumns\AdminColumnsFrontendFieldsInlineEditContractTest::testFrontendMutationRemainsBoundedToExplicitPostIdAndFieldsOwner`
3. `WPEssential\Tests\Unit\Modules\CustomTables\ObservedSchemaMigrationPlanTest::testUnsupportedObservedDriftBlocksPlanningInsteadOfSynthesizingCorrection`
4. `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetPresetDraftPublishReviewServiceTest::testOnlySiteLocalDraftPresetCanBeReviewed`
5. `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompilerTest::testRejectsWrongDefinitionOwnerTypeSchemaStatusOrContentClass`
6. `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompilerTest::testRejectsMissingUnknownOrMalformedRenderSource`
7. `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetRuntimeRenderExecutorTest::testQueryResolvesBeforeDynamicAndRendererReceivesBothBindings`
8. `WPEssential\Tests\Unit\Modules\Listings\ListingServerRendererTest::testFailsClosedWhenResourceIdRowFieldIsMissing`
9. `WPEssential\Tests\Unit\Modules\Query\QueryReadConsumerTest::testBoundedReadUsesCanonicalValidationPolicyPlanningAndExecutor`
10. `WPEssential\Tests\Unit\Modules\Status\StatusTransitionExecutorTest::testStaleExpectedStatusFailsBeforeAuthorizationOrMutation`
11. `WPEssential\Tests\Unit\Modules\Status\StatusTransitionExecutorTest::testWpeStatusPostTypeApplicabilityIsEnforced`
12. `WPEssential\Tests\Unit\Modules\Taxonomies\TaxonomyDefinitionProjectorTest::testProjectsPublishedSurfaceTwoDefinitionToTypedTaxonomyRegistration`
13. `WPEssential\Tests\Unit\Modules\Taxonomies\TaxonomyValidationServiceTest::testDiagnosticsExposeCanonicalEffectiveArgsOverridesAndSafePreviews`
14. `WPEssential\Tests\Unit\Platform\Abilities\InputValidation\AbilityInputValidatorTest::testRejectsRuntimeDepthSizeSparseArraysAndUnsupportedValues`

## Safe next work

- Group remaining failures into query/listing validation, Dashboard render-source/runtime, Status authorization ordering, Taxonomy projections, schema planning, input validator and source-level UI contract checks. Avoid broad changes based on a single expected string.
- Re-run affected focused tests first and verify the full suite at pinned CI PHP 8.2 before promoting a merge.
- No GitHub PR/merge or live deployment performed by this local WIP.

## Local terminal quality checkpoint — 2026-10-13

- `vendor/bin/phpunit -c phpunit.xml.dist --display-phpunit-deprecations --no-progress`: **0 exit, 1,421 tests, 7,489 assertions, no failures, errors, warnings or PHPUnit deprecation notices** on PHP 8.5.4, PHPUnit 11.5.56. Replaced six legacy `@dataProvider` annotations with PHPUnit attributes and migrated PHPUnit schema.
- `php tools/architecture/validate.php`, `npm run typecheck`, `npm run build` and `git diff --check`: PASS.
- Scope includes actual source guard fixes (fail-closed shared service checks, preserved authored relation direction, strict object/map validation) and legacy test-contract fixture repairs.
- **Important:** global `phpcs` and `phpstan` commands were run in the local environment and reported large numbers of pre-existing/whole-tree violations (e.g., PHPStan 458 diagnostics including PHP 8.5-specific constant range analyses); neither is claimed green. Exact-head GitHub CI pinned to PHP 8.2 is required before merge. This local result is not production or full 56-module certification.
- A terminal PHPUnit result supersedes earlier baseline and historical triage counts in previous quality reports.
