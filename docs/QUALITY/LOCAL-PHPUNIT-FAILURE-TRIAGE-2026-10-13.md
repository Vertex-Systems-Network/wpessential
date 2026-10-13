# Local PHPUnit failure triage — 2026-10-13

**Reproduced on PHP 8.5.4 after two local commits.** These results are a failing baseline and not certification.

## Current result

`Tests: 1421, Assertions: 7206, Errors: 24, Failures: 24, Warnings: 1, PHPUnit Deprecations: 7.`

Earlier baseline: `Tests: 1418, Assertions: 6042, Errors: 187, Failures: 41, PHPUnit Deprecations: 7.`

Run: `vendor/bin/phpunit -c phpunit.xml.dist --no-progress` from `/vercel/wpessential`.

## Remaining error categories

| Count | Error category |
|---:|---|
| 9 | InvalidArgumentException: Ability name must use wpessential/<domain>/<action>. |
| 3 | RuntimeException: CT1 schema observation cannot resolve the trusted database provider identity. |
| 2 | RuntimeException: Admin Columns personal preference write-back verification failed. |
| 2 | RuntimeException: Definition checksum does not match canonical payload. |
| 2 | RuntimeException: Definition revision must advance monotonically. |
| 1 | ArgumentCountError: WPEssential\Platform\Definitions\Definition::__construct(): Argument #6 ($status) not passed |
| 1 | RuntimeException: Service "platform.definitions" is not registered. |
| 1 | Error: Class "WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetContentClassDescriptor" not found |
| 1 | ArgumentCountError: Too few arguments to function WPEssential\Modules\DashboardWidgets\DashboardWidgetFormActionPresenter::__construct(), 2 passed in /vercel/wpessential/tests/Unit/Modu |
| 1 | ArgumentCountError: Too few arguments to function WPEssential\Modules\DashboardWidgets\DashboardWidgetFormActionPresenter::__construct(), 2 passed in /vercel/wpessential/tests/Unit/Modu |
| 1 | RuntimeException: Service "module.relations.admin" is already registered. |

## Failing test cases (24)

- `WPEssential\Tests\Unit\Modules\AdminColumns\AdminColumnsFrontendFieldsBulkEditContractTest::testBulkMutationKeepsOwnerAndSavedViewGuardsForEverySelectedRow`
- `WPEssential\Tests\Unit\Modules\AdminColumns\AdminColumnsFrontendFieldsInlineEditContractTest::testFrontendMutationRemainsBoundedToExplicitPostIdAndFieldsOwner`
- `WPEssential\Tests\Unit\Modules\CustomTables\ObservedSchemaMigrationPlanTest::testUnsupportedObservedDriftBlocksPlanningInsteadOfSynthesizingCorrection`
- `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetPersonalPreferenceAbilityHandlerTest::testResetDelegatesOnlyCurrentUserAndScreen`
- `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetPresetDraftPublishReviewServiceTest::testOnlySiteLocalDraftPresetCanBeReviewed`
- `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityAbilityHandlerTest::testAbilityRejectsMissingExtraMalformedAndCaseChangedInput`
- `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityFreshnessAbilityHandlerTest::testRejectsMissingExtraAndMalformedInputsWithoutFallback`
- `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityFreshnessServiceTest::testServiceBoundaryRejectsMalformedAndUppercaseInputs`
- `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompilerTest::testRejectsWrongDefinitionOwnerTypeSchemaStatusOrContentClass`
- `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompilerTest::testRejectsMissingUnknownOrMalformedRenderSource`
- `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetRuntimeRenderExecutorTest::testQueryResolvesBeforeDynamicAndRendererReceivesBothBindings`
- `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetsModuleTest::testModuleFailsClosedWithoutCanonicalRenderingServices`
- `WPEssential\Tests\Unit\Modules\DashboardWidgets\DashboardWidgetsModuleTest::testModuleFailsClosedWhenRendererServiceIsMissing`
- `WPEssential\Tests\Unit\Modules\Fields\PostMetaValueStoreTest::testRequiredNullIsRejectedBeforeMutation`
- `WPEssential\Tests\Unit\Modules\Listings\ListingServerRendererTest::testFailsClosedWhenResourceIdRowFieldIsMissing`
- `WPEssential\Tests\Unit\Modules\Query\QueryFieldPredicateResolverTest::testFieldExecutionRequiresFiniteAnchor`
- `WPEssential\Tests\Unit\Modules\Query\QueryReadConsumerTest::testBoundedReadUsesCanonicalValidationPolicyPlanningAndExecutor`
- `WPEssential\Tests\Unit\Modules\Query\QueryRelationPredicateResolverTest::testRelationExecutionRequiresExplicitFinitePostIdAnchorSet`
- `WPEssential\Tests\Unit\Modules\Relations\RelationDefinitionAbilityHandlerTest::testPartialBasicUpdatePreservesExplicitAdvancedPolicy`
- `WPEssential\Tests\Unit\Modules\Status\StatusTransitionExecutorTest::testStaleExpectedStatusFailsBeforeAuthorizationOrMutation`
- `WPEssential\Tests\Unit\Modules\Status\StatusTransitionExecutorTest::testWpeStatusPostTypeApplicabilityIsEnforced`
- `WPEssential\Tests\Unit\Modules\Taxonomies\TaxonomyDefinitionProjectorTest::testProjectsPublishedSurfaceTwoDefinitionToTypedTaxonomyRegistration`
- `WPEssential\Tests\Unit\Modules\Taxonomies\TaxonomyValidationServiceTest::testDiagnosticsExposeCanonicalEffectiveArgsOverridesAndSafePreviews`
- `WPEssential\Tests\Unit\Platform\Abilities\InputValidation\AbilityInputValidatorTest::testRejectsRuntimeDepthSizeSparseArraysAndUnsupportedValues`

## Constraints and next actions

- Do not change the canonical Ability name contract or silently alter existing name constants: platform enforces `wpessential/<domain>/<action>` while Admin Columns preference constants use `wpessential/admin-columns/personal-preference/<verb>`, with matching hard-coded test expectations. Decide a backwards-compatible owner contract first.
- Prioritize a focused issue per error category; avoid weakening capability/auth/storage policy to make tests green.
- Preserve `frameworks/functions.php` Composer autoload guard so PHPUnit actually executes and reports nonzero test count.
- Reproduce at pinned PHP 8.2 CI runtime before treating PHP 8.5-only diagnostics as release blockers.
- No PR, protected merge, production deployment, or full 56-module parity claim follows from this local triage alone.
