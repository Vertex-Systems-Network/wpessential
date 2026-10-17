# Local PHPUnit follow-up: zero errors, 22 assertion failures

Local branch: `agent/local-phpunit-bootstrap-repair-v1`. PHP 8.5.4 / PHPUnit 11.5.56. The historical `LOCAL-PHPUNIT-FAILURE-TRIAGE-2026-10-13.md` records the earlier 24-error / 24-failure snapshot; it is not the current result.

## Latest verified full suite

`Tests: 1421, Assertions: 7436, Failures: 22, Warnings: 1, PHPUnit Deprecations: 7.`
**PHPUnit exit code 1: NOT GREEN.** Architecture Guard, TypeScript typecheck, and npm admin assets build passed on the same working tree.

## Scoped fixes since previous local commit

- Admin Columns required service fail-closed guards and optional transport resolution; canonical Ability naming verified by focused tests.
- Relations repeat `boot()` validates and reuses the canonical controller, preserving idempotent migration execution and rejecting mismatched admin service types.
- Dashboard Widgets module checks rendering dependencies before attempting registration; missing dependencies cause explicit `LogicException`.
- Test fixtures for checksum mismatch simulate externally corrupted read state via reflection, leaving all repository checked-write integrity/monotonic revision safeguards untouched.
- Preset portability read-only tests now advance fixture revisions strictly monotonically.
- This is not parity certification, production authorization, or permission to merge with failed PHPUnit.

## Remaining 22 failing test methods

1. `WPEssential\Tests\Unit\Modules\AdminColumns\AdminColumnsFrontendFieldsBulkEditContractTest::testBulkMutationKeepsOwnerAndSavedViewGuardsForEverySelectedRow`
2. `WPEssential\Tests\Unit\Modules\AdminColumns\AdminColumnsFrontendFieldsInlineEditContractTest::testFrontendMutationRemainsBoundedToExplicitPostIdAndFieldsOwner`
3. `WPEssential\Tests\Unit\Modules\CustomTables\ObservedSchemaMigrationPlanTest::testUnsupportedObservedDriftBlocksPlanningInsteadOfSynthesizingCorrection`
4. `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetPersonalPreferenceAbilityHandlerTest::testResetDelegatesOnlyCurrentUserAndScreen`
5. `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetPresetDraftPublishReviewServiceTest::testOnlySiteLocalDraftPresetCanBeReviewed`
6. `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityAbilityHandlerTest::testAbilityRejectsMissingExtraMalformedAndCaseChangedInput`
7. `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityFreshnessAbilityHandlerTest::testRejectsMissingExtraAndMalformedInputsWithoutFallback`
8. `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityFreshnessServiceTest::testServiceBoundaryRejectsMalformedAndUppercaseInputs`
9. `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompilerTest::testRejectsWrongDefinitionOwnerTypeSchemaStatusOrContentClass`
10. `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompilerTest::testRejectsMissingUnknownOrMalformedRenderSource`
11. `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetRuntimeRenderExecutorTest::testQueryResolvesBeforeDynamicAndRendererReceivesBothBindings`
12. `WPEssential\Tests\Unit\Modules\Fields\PostMetaValueStoreTest::testRequiredNullIsRejectedBeforeMutation`
13. `WPEssential\Tests\Unit\Modules\Listings\ListingServerRendererTest::testFailsClosedWhenResourceIdRowFieldIsMissing`
14. `WPEssential\Tests\Unit\Modules\Query\QueryFieldPredicateResolverTest::testFieldExecutionRequiresFiniteAnchor`
15. `WPEssential\Tests\Unit\Modules\Query\QueryReadConsumerTest::testBoundedReadUsesCanonicalValidationPolicyPlanningAndExecutor`
16. `WPEssential\Tests\Unit\Modules\Query\QueryRelationPredicateResolverTest::testRelationExecutionRequiresExplicitFinitePostIdAnchorSet`
17. `WPEssential\Tests\Unit\Modules\Relations\RelationDefinitionAbilityHandlerTest::testPartialBasicUpdatePreservesExplicitAdvancedPolicy`
18. `WPEssential\Tests\Unit\Modules\Status\StatusTransitionExecutorTest::testStaleExpectedStatusFailsBeforeAuthorizationOrMutation`
19. `WPEssential\Tests\Unit\Modules\Status\StatusTransitionExecutorTest::testWpeStatusPostTypeApplicabilityIsEnforced`
20. `WPEssential\Tests\Unit\Modules\Taxonomies\TaxonomyDefinitionProjectorTest::testProjectsPublishedSurfaceTwoDefinitionToTypedTaxonomyRegistration`
21. `WPEssential\Tests\Unit\Modules\Taxonomies\TaxonomyValidationServiceTest::testDiagnosticsExposeCanonicalEffectiveArgsOverridesAndSafePreviews`
22. `WPEssential\Tests\Unit\Platform\Abilities\InputValidation\AbilityInputValidatorTest::testRejectsRuntimeDepthSizeSparseArraysAndUnsupportedValues`

## Known warning / follow-up

- `RelationDefinitionAbilityHandlerTest.php:85` reads missing `parent_relation` on partial-update expectation; investigate preservation of existing advanced policy without broadening mutation scope.
- Inspect Dashboard render-source and runtime validation discrepancies, Query finite anchor guards, Status checks before mutation, Taxonomy/Fields/Platform validation contract differences individually.
- Keep protected main unchanged until scoped fixes pass full exact-head testing. No GitHub push/PR/merge performed for this local WIP.
