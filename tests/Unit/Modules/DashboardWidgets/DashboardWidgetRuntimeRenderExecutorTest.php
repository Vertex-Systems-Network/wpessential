<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentBlueprintCatalog;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetQueryBindingExecutor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRoleMembershipProviderInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRuntimeRenderExecutor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRuntimeRenderResult;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityDecision;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityEvaluator;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\DataSources\DataSourceAuthorizationMapping;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\Rendering\RenderFailureCode;
use WPEssential\Platform\Rendering\RenderInput;
use WPEssential\Platform\Rendering\RenderOutput;

final class DashboardWidgetRuntimeRenderExecutorTest extends TestCase
{
    public function testMissingDefinitionShortCircuitsBeforeRenderer(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $renderer = new RuntimeRenderCapturingRenderer(new RenderOutput(true, '<p>unused</p>'));
        $roles = new RuntimeRenderRoleProvider();
        $executor = $this->executor($repository, $renderer, $roles);

        $result = $executor->render('11111111-1111-4111-8111-111111111111', $this->context());

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_MISSING_DEFINITION, $result->status);
        self::assertSame(0, $renderer->calls);
        self::assertNull($roles->seenContext);
    }

    public function testRegistrationRejectionFailsClosedWithoutRenderer(): void
    {
        $repository = $this->repositoryWith($this->definition(title: '<script>bad</script>'));
        $renderer = new RuntimeRenderCapturingRenderer(new RenderOutput(true, '<p>unused</p>'));
        $executor = $this->executor($repository, $renderer, new RuntimeRenderRoleProvider());

        $result = $executor->render($this->definitionId(), $this->context());

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_INVALID_DEFINITION, $result->status);
        self::assertSame(0, $renderer->calls);
    }

    public function testVisibilityCompileRejectionFailsClosedWithoutRenderer(): void
    {
        $repository = $this->repositoryWith($this->definition(visibility: ['roles' => ['Administrator']]));
        $renderer = new RuntimeRenderCapturingRenderer(new RenderOutput(true, '<p>unused</p>'));
        $executor = $this->executor($repository, $renderer, new RuntimeRenderRoleProvider());

        $result = $executor->render($this->definitionId(), $this->context());

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_INVALID_DEFINITION, $result->status);
        self::assertSame(0, $renderer->calls);
    }

    public function testRenderSourceCompileRejectionFailsClosedWithoutRenderer(): void
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $kpi = $catalog->forContentType('kpi');
        self::assertNotNull($kpi);

        $repository = $this->repositoryWith($this->definition(renderSource: [
            'kind' => 'component_blueprint',
            'blueprint_id' => $kpi->id,
            'blueprint_revision' => $kpi->revision,
            'bindings' => [
                'label' => ['source' => 'literal', 'value' => 'Orders'],
                'value' => ['source' => 'literal', 'value' => '12'],
            ],
        ]));
        $renderer = new RuntimeRenderCapturingRenderer(new RenderOutput(true, '<p>unused</p>'));
        $executor = $this->executor($repository, $renderer, new RuntimeRenderRoleProvider());

        $result = $executor->render($this->definitionId(), $this->context());

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_INVALID_DEFINITION, $result->status);
        self::assertSame(0, $renderer->calls);
    }

    public function testVisibilityDenialPreservesBoundedReasonAndDoesNotRender(): void
    {
        $repository = $this->repositoryWith($this->definition(visibility: ['users' => [999]]));
        $renderer = new RuntimeRenderCapturingRenderer(new RenderOutput(true, '<p>unused</p>'));
        $roles = new RuntimeRenderRoleProvider();
        $executor = $this->executor($repository, $renderer, $roles);
        $context = $this->context();

        $result = $executor->render($this->definitionId(), $context);

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_VISIBILITY_DENIED, $result->status);
        self::assertSame(DashboardWidgetVisibilityDecision::REASON_USER_MISMATCH, $result->visibilityReason);
        self::assertSame('', $result->html);
        self::assertSame([], $result->assetHandles);
        self::assertSame(0, $renderer->calls);
        self::assertSame($context, $roles->seenContext);
    }

    public function testUnexpectedRepositoryFailureBecomesOpaqueRuntimeFailure(): void
    {
        $repository = new class implements DefinitionRepositoryInterface {
            public function save(Definition $definition): void {}
            public function get(string $id): ?Definition { throw new RuntimeException('secret-provider-token'); }
            public function byType(string $type): array { return []; }
            public function dependentsOf(string $id): array { return []; }
        };
        $renderer = new RuntimeRenderCapturingRenderer(new RenderOutput(true, '<p>unused</p>'));
        $executor = $this->executor($repository, $renderer, new RuntimeRenderRoleProvider());

        $result = $executor->render($this->definitionId(), $this->context());

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RUNTIME_FAILURE, $result->status);
        self::assertSame('', $result->html);
        self::assertSame([], $result->assetHandles);
        self::assertNull($result->visibilityReason);
        self::assertNull($result->renderFailure);
        self::assertSame(0, $renderer->calls);
    }

    public function testRendererThrowableBecomesOpaqueRuntimeFailure(): void
    {
        $repository = $this->repositoryWith($this->definition());
        $renderer = new RuntimeRenderCapturingRenderer(
            new RenderOutput(true, '<p>unused</p>'),
            new RuntimeException('sensitive-renderer-detail'),
        );
        $executor = $this->executor($repository, $renderer, new RuntimeRenderRoleProvider());

        $result = $executor->render($this->definitionId(), $this->context());

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RUNTIME_FAILURE, $result->status);
        self::assertSame('', $result->html);
        self::assertSame([], $result->assetHandles);
        self::assertNull($result->visibilityReason);
        self::assertNull($result->renderFailure);
        self::assertSame(1, $renderer->calls);
    }

    public function testRendererFailurePreservesOnlySharedFailureCode(): void
    {
        $repository = $this->repositoryWith($this->definition());
        $renderer = new RuntimeRenderCapturingRenderer(
            new RenderOutput(false, '', [], RenderFailureCode::DependencyMismatch),
        );
        $executor = $this->executor($repository, $renderer, new RuntimeRenderRoleProvider());

        $result = $executor->render($this->definitionId(), $this->context());

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RENDERER_FAILED, $result->status);
        self::assertSame(RenderFailureCode::DependencyMismatch, $result->renderFailure);
        self::assertSame('', $result->html);
        self::assertSame([], $result->assetHandles);
        self::assertNull($result->visibilityReason);
        self::assertSame(1, $renderer->calls);
    }

    public function testRenderedSuccessForwardsExactContextAndReturnsDataOnlyAssets(): void
    {
        $repository = $this->repositoryWith($this->definition());
        $renderer = new RuntimeRenderCapturingRenderer(
            new RenderOutput(true, '<p>safe widget</p>', ['wpe-dashboard-widget']),
        );
        $roles = new RuntimeRenderRoleProvider();
        $executor = $this->executor($repository, $renderer, $roles);
        $context = $this->context();

        $result = $executor->render($this->definitionId(), $context);

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RENDERED, $result->status);
        self::assertSame('<p>safe widget</p>', $result->html);
        self::assertSame(['wpe-dashboard-widget'], $result->assetHandles);
        self::assertNull($result->visibilityReason);
        self::assertNull($result->renderFailure);
        self::assertSame(1, $renderer->calls);
        self::assertSame($context, $roles->seenContext);
        self::assertSame($context, $renderer->seenContext);
        self::assertNotNull($renderer->seenInput);
    }


    public function testVisibilityDenialOccursBeforeAnyQueryRead(): void
    {
        $consumer = new RuntimeRenderQueryConsumer($this->querySuccessResult());
        $queryBindings = new DashboardWidgetQueryBindingExecutor($this->queryRegistry(), $consumer);
        $repository = $this->repositoryWith($this->definition(
            visibility: ['users' => [999]],
            renderSource: $this->queryRenderSource(),
        ));
        $renderer = new RuntimeRenderCapturingRenderer(new RenderOutput(true, '<p>unused</p>'));
        $executor = $this->executor($repository, $renderer, new RuntimeRenderRoleProvider(), $queryBindings);

        $result = $executor->render($this->definitionId(), $this->context());

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_VISIBILITY_DENIED, $result->status);
        self::assertSame(0, $consumer->calls);
        self::assertSame(0, $renderer->calls);
    }

    public function testQuerySuccessForwardsExactContextToQueryAndRenderer(): void
    {
        $consumer = new RuntimeRenderQueryConsumer($this->querySuccessResult());
        $queryBindings = new DashboardWidgetQueryBindingExecutor($this->queryRegistry(), $consumer);
        $repository = $this->repositoryWith($this->definition(renderSource: $this->queryRenderSource()));
        $renderer = new RuntimeRenderCapturingRenderer(new RenderOutput(true, '<p>safe query widget</p>'));
        $roles = new RuntimeRenderRoleProvider();
        $executor = $this->executor($repository, $renderer, $roles, $queryBindings);
        $context = $this->context();

        $result = $executor->render($this->definitionId(), $context);

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RENDERED, $result->status);
        self::assertSame($context, $consumer->seenContext);
        self::assertSame($context, $renderer->seenContext);
        self::assertSame(['content' => 'Query content'], $renderer->seenInput?->bindings);
        self::assertSame(1, $consumer->calls);
        self::assertSame(1, $renderer->calls);
    }

    public function testQueryFailureIsOpaqueAndPreventsRendererCall(): void
    {
        $consumer = new RuntimeRenderQueryConsumer([
            'contract_version' => 1,
            'ok' => false,
            'source_ref' => 'wordpress.posts',
            'projection' => [],
            'rows' => [],
            'returned' => 0,
            'error' => ['message' => 'secret-provider-detail'],
        ]);
        $queryBindings = new DashboardWidgetQueryBindingExecutor($this->queryRegistry(), $consumer);
        $repository = $this->repositoryWith($this->definition(renderSource: $this->queryRenderSource(withEmptyState: true)));
        $renderer = new RuntimeRenderCapturingRenderer(new RenderOutput(true, '<p>unused</p>'));
        $executor = $this->executor($repository, $renderer, new RuntimeRenderRoleProvider(), $queryBindings);

        $result = $executor->render($this->definitionId(), $this->context());

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RUNTIME_FAILURE, $result->status);
        self::assertSame('', $result->html);
        self::assertSame(0, $renderer->calls);
        self::assertSame(1, $consumer->calls);
    }

    public function testValidZeroRowQueryRendersTrustedEmptyStateWithExactContext(): void
    {
        $consumer = new RuntimeRenderQueryConsumer([
            'contract_version' => 1,
            'ok' => true,
            'source_ref' => 'wordpress.posts',
            'projection' => ['post.title'],
            'rows' => [],
            'returned' => 0,
            'error' => null,
        ]);
        $queryBindings = new DashboardWidgetQueryBindingExecutor($this->queryRegistry(), $consumer);
        $repository = $this->repositoryWith($this->definition(
            renderSource: $this->queryRenderSource(withEmptyState: true),
        ));
        $renderer = new RuntimeRenderCapturingRenderer(new RenderOutput(true, '<p>trusted empty state</p>'));
        $executor = $this->executor($repository, $renderer, new RuntimeRenderRoleProvider(), $queryBindings);
        $context = $this->context();

        $result = $executor->render($this->definitionId(), $context);

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RENDERED, $result->status);
        self::assertSame(1, $consumer->calls);
        self::assertSame(1, $renderer->calls);
        self::assertSame($context, $consumer->seenContext);
        self::assertSame($context, $renderer->seenContext);
        self::assertSame(
            ['text' => 'Nothing to display yet.', 'title' => 'No results'],
            $renderer->seenInput?->bindings,
        );
    }

    public function testTypedPrimaryRendererFailureUsesOneShotTrustedErrorState(): void
    {
        $repository = $this->repositoryWith($this->definition(
            renderSource: $this->literalRenderSource(withErrorState: true),
        ));
        $renderer = new RuntimeRenderSequenceRenderer([
            new RenderOutput(false, '', [], RenderFailureCode::DependencyMismatch),
            new RenderOutput(true, '<p>trusted error state</p>', ['wpe-dashboard-error']),
        ]);
        $executor = $this->executor($repository, $renderer, new RuntimeRenderRoleProvider());
        $context = $this->context();

        $result = $executor->render($this->definitionId(), $context);

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RENDERED_ERROR, $result->status);
        self::assertSame('<p>trusted error state</p>', $result->html);
        self::assertSame(['wpe-dashboard-error'], $result->assetHandles);
        self::assertSame(RenderFailureCode::DependencyMismatch, $result->renderFailure);
        self::assertSame(2, $renderer->calls);
        self::assertSame($context, $renderer->seenContexts[0]);
        self::assertSame($context, $renderer->seenContexts[1]);
        self::assertSame(
            ['text' => 'Please try again later.', 'title' => 'Widget unavailable'],
            $renderer->seenInputs[1]->bindings,
        );
    }

    public function testErrorStateDoesNotRunForQueryFailureOrPrimaryThrowable(): void
    {
        $consumer = new RuntimeRenderQueryConsumer([
            'contract_version' => 1,
            'ok' => false,
            'source_ref' => 'wordpress.posts',
            'projection' => [],
            'rows' => [],
            'returned' => 0,
            'error' => ['message' => 'secret-provider-detail'],
        ]);
        $queryBindings = new DashboardWidgetQueryBindingExecutor($this->queryRegistry(), $consumer);
        $repository = $this->repositoryWith($this->definition(
            renderSource: $this->queryRenderSource(withEmptyState: false, withErrorState: true),
        ));
        $renderer = new RuntimeRenderSequenceRenderer([
            new RenderOutput(true, '<p>must not render</p>'),
        ]);
        $executor = $this->executor($repository, $renderer, new RuntimeRenderRoleProvider(), $queryBindings);

        $result = $executor->render($this->definitionId(), $this->context());

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RUNTIME_FAILURE, $result->status);
        self::assertSame(0, $renderer->calls);

        $throwingRepository = $this->repositoryWith($this->definition(
            renderSource: $this->literalRenderSource(withErrorState: true),
        ));
        $throwingRenderer = new RuntimeRenderSequenceRenderer([
            new RuntimeException('sensitive-primary-renderer-detail'),
            new RenderOutput(true, '<p>fallback must not run</p>'),
        ]);
        $throwingExecutor = $this->executor(
            $throwingRepository,
            $throwingRenderer,
            new RuntimeRenderRoleProvider(),
        );

        $throwingResult = $throwingExecutor->render($this->definitionId(), $this->context());

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RUNTIME_FAILURE, $throwingResult->status);
        self::assertSame(1, $throwingRenderer->calls);
    }

    public function testFallbackFailureOrThrowableFailsClosedWithoutRecursion(): void
    {
        $repository = $this->repositoryWith($this->definition(
            renderSource: $this->literalRenderSource(withErrorState: true),
        ));

        $failedFallback = new RuntimeRenderSequenceRenderer([
            new RenderOutput(false, '', [], RenderFailureCode::DependencyMismatch),
            new RenderOutput(false, '', [], RenderFailureCode::MissingBlueprint),
            new RenderOutput(true, '<p>third call forbidden</p>'),
        ]);
        $failedResult = $this->executor(
            $repository,
            $failedFallback,
            new RuntimeRenderRoleProvider(),
        )->render($this->definitionId(), $this->context());

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RENDERER_FAILED, $failedResult->status);
        self::assertSame(RenderFailureCode::MissingBlueprint, $failedResult->renderFailure);
        self::assertSame('', $failedResult->html);
        self::assertSame(2, $failedFallback->calls);

        $throwingFallback = new RuntimeRenderSequenceRenderer([
            new RenderOutput(false, '', [], RenderFailureCode::DependencyMismatch),
            new RuntimeException('sensitive-fallback-detail'),
            new RenderOutput(true, '<p>third call forbidden</p>'),
        ]);
        $throwingResult = $this->executor(
            $repository,
            $throwingFallback,
            new RuntimeRenderRoleProvider(),
        )->render($this->definitionId(), $this->context());

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RUNTIME_FAILURE, $throwingResult->status);
        self::assertSame('', $throwingResult->html);
        self::assertSame(2, $throwingFallback->calls);
    }

    public function testEmptyStateResolvedPrimaryPreservesErrorStateFallback(): void
    {
        $consumer = new RuntimeRenderQueryConsumer([
            'contract_version' => 1,
            'ok' => true,
            'source_ref' => 'wordpress.posts',
            'projection' => ['post.title'],
            'rows' => [],
            'returned' => 0,
            'error' => null,
        ]);
        $queryBindings = new DashboardWidgetQueryBindingExecutor($this->queryRegistry(), $consumer);
        $repository = $this->repositoryWith($this->definition(
            renderSource: $this->queryRenderSource(withEmptyState: true, withErrorState: true),
        ));
        $renderer = new RuntimeRenderSequenceRenderer([
            new RenderOutput(false, '', [], RenderFailureCode::InvalidInput),
            new RenderOutput(true, '<p>error after empty-state failure</p>'),
        ]);
        $context = $this->context();
        $result = $this->executor(
            $repository,
            $renderer,
            new RuntimeRenderRoleProvider(),
            $queryBindings,
        )->render($this->definitionId(), $context);

        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RENDERED_ERROR, $result->status);
        self::assertSame(RenderFailureCode::InvalidInput, $result->renderFailure);
        self::assertSame(2, $renderer->calls);
        self::assertSame(
            ['text' => 'Nothing to display yet.', 'title' => 'No results'],
            $renderer->seenInputs[0]->bindings,
        );
        self::assertSame(
            ['text' => 'Please try again later.', 'title' => 'Widget unavailable'],
            $renderer->seenInputs[1]->bindings,
        );
        self::assertSame($context, $renderer->seenContexts[0]);
        self::assertSame($context, $renderer->seenContexts[1]);
    }

    private function executor(
        DefinitionRepositoryInterface $definitions,
        RendererInterface $renderer,
        RuntimeRenderRoleProvider $roles,
        ?DashboardWidgetQueryBindingExecutor $queryBindings = null,
    ): DashboardWidgetRuntimeRenderExecutor {
        $blueprints = new ComponentBlueprintRegistry();
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        foreach ($catalog->all() as $blueprint) {
            $blueprints->register($blueprint);
        }

        $visibilityCompiler = new DashboardWidgetVisibilityCompiler();
        $renderSourceCompiler = new DashboardWidgetRenderSourceCompiler(
            $blueprints,
            new DashboardWidgetContentClassCompiler(),
            $catalog,
        );
        $registrationCompiler = new DashboardWidgetRegistrationCompiler(
            $visibilityCompiler,
            new DashboardWidgetContentClassCompiler(),
            $renderSourceCompiler,
        );
        $capabilities = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return true;
            }
        };

        return new DashboardWidgetRuntimeRenderExecutor(
            $definitions,
            $registrationCompiler,
            $visibilityCompiler,
            new DashboardWidgetVisibilityEvaluator($capabilities, $roles),
            $renderSourceCompiler,
            $renderer,
            $queryBindings,
        );
    }

    private function repositoryWith(Definition $definition): InMemoryDefinitionRepository
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($definition);

        return $repository;
    }

    private function definition(
        array $visibility = [],
        ?array $renderSource = null,
        string $title = 'Safe Widget',
    ): Definition {
        $renderSource ??= $this->literalRenderSource();

        return new Definition(
            id: $this->definitionId(),
            slug: 'runtime-widget',
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: [
                'widget' => [
                    'key' => 'runtime_widget',
                    'title' => $title,
                    'type' => 'rich_text',
                    'context' => 'normal',
                    'priority' => 'default',
                    'network_dashboard' => false,
                    'visibility' => $visibility,
                    'render_source' => $renderSource,
                ],
            ],
            revision: 1,
            dependencies: [],
        );
    }


    /** @return array<string,mixed> */
    private function literalRenderSource(bool $withErrorState = false): array
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $richText = $catalog->forContentType('rich_text');
        self::assertNotNull($richText);

        $renderSource = [
            'kind' => 'component_blueprint',
            'blueprint_id' => $richText->id,
            'blueprint_revision' => $richText->revision,
            'bindings' => [
                'content' => ['source' => 'literal', 'value' => 'Safe content'],
            ],
        ];

        if ($withErrorState) {
            $announcement = $catalog->forContentType('announcement');
            self::assertNotNull($announcement);
            $renderSource['error_state'] = [
                'kind' => 'component_blueprint',
                'blueprint_id' => $announcement->id,
                'blueprint_revision' => $announcement->revision,
                'bindings' => [
                    'title' => ['source' => 'literal', 'value' => 'Widget unavailable'],
                    'text' => ['source' => 'literal', 'value' => 'Please try again later.'],
                ],
            ];
        }

        return $renderSource;
    }

    /** @return array<string,mixed> */
    private function querySuccessResult(): array
    {
        return [
            'contract_version' => 1,
            'ok' => true,
            'source_ref' => 'wordpress.posts',
            'projection' => ['post.title'],
            'rows' => [['post.title' => 'Query content']],
            'returned' => 1,
            'error' => null,
        ];
    }

    private function queryRegistry(): DataSourceRegistry
    {
        $registry = new DataSourceRegistry();
        $registry->register(new DataSourceDescriptor(
            id: 'wordpress.posts',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: ['post.title' => 'string'],
            predicates: ['eq'],
            sortModes: ['field'],
            paginationModes: ['offset'],
            maxPageSize: 50,
            authorization: new DataSourceAuthorizationMapping('wpessential/query/execute', 'read', 'post'),
        ));

        return $registry;
    }

    /** @return array<string,mixed> */
    private function queryRenderSource(bool $withEmptyState = false, bool $withErrorState = false): array
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $richText = $catalog->forContentType('rich_text');
        self::assertNotNull($richText);

        $renderSource = [
            'kind' => 'component_blueprint',
            'blueprint_id' => $richText->id,
            'blueprint_revision' => $richText->revision,
            'query' => [
                'contract_version' => 1,
                'source_ref' => 'wordpress.posts',
                'page_size' => 1,
            ],
            'bindings' => [
                'content' => ['source' => 'query', 'field_ref' => 'post.title', 'mode' => 'first'],
            ],
        ];

        if ($withEmptyState) {
            $announcement = $catalog->forContentType('announcement');
            self::assertNotNull($announcement);
            $renderSource['empty_state'] = [
                'kind' => 'component_blueprint',
                'blueprint_id' => $announcement->id,
                'blueprint_revision' => $announcement->revision,
                'bindings' => [
                    'title' => ['source' => 'literal', 'value' => 'No results'],
                    'text' => ['source' => 'literal', 'value' => 'Nothing to display yet.'],
                ],
            ];
        }

        if ($withErrorState) {
            $announcement = $catalog->forContentType('announcement');
            self::assertNotNull($announcement);
            $renderSource['error_state'] = [
                'kind' => 'component_blueprint',
                'blueprint_id' => $announcement->id,
                'blueprint_revision' => $announcement->revision,
                'bindings' => [
                    'title' => ['source' => 'literal', 'value' => 'Widget unavailable'],
                    'text' => ['source' => 'literal', 'value' => 'Please try again later.'],
                ],
            ];
        }

        return $renderSource;
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 3);
    }

    private function definitionId(): string
    {
        return '77777777-7777-4777-8777-777777777777';
    }
}

final class RuntimeRenderCapturingRenderer implements RendererInterface
{
    public int $calls = 0;
    public ?ExecutionContext $seenContext = null;
    public ?RenderInput $seenInput = null;

    public function __construct(
        private RenderOutput $output,
        private ?Throwable $throwable = null,
    ) {}

    public function render(RenderInput $input, ExecutionContext $context): RenderOutput
    {
        ++$this->calls;
        $this->seenInput = $input;
        $this->seenContext = $context;

        if ($this->throwable !== null) {
            throw $this->throwable;
        }

        return $this->output;
    }
}

final class RuntimeRenderSequenceRenderer implements RendererInterface
{
    public int $calls = 0;
    /** @var list<ExecutionContext> */
    public array $seenContexts = [];
    /** @var list<RenderInput> */
    public array $seenInputs = [];

    /** @param list<RenderOutput|Throwable> $sequence */
    public function __construct(private array $sequence) {}

    public function render(RenderInput $input, ExecutionContext $context): RenderOutput
    {
        $index = $this->calls;
        ++$this->calls;
        $this->seenInputs[] = $input;
        $this->seenContexts[] = $context;

        $next = $this->sequence[$index] ?? null;
        if ($next instanceof Throwable) {
            throw $next;
        }
        if (!$next instanceof RenderOutput) {
            throw new RuntimeException('Unexpected renderer invocation beyond the bounded test sequence.');
        }

        return $next;
    }
}

final class RuntimeRenderRoleProvider implements DashboardWidgetRoleMembershipProviderInterface
{
    public ?ExecutionContext $seenContext = null;

    public function isCurrentUserContext(ExecutionContext $context): bool
    {
        $this->seenContext = $context;

        return true;
    }

    public function hasAnyRole(ExecutionContext $context, array $roles): bool
    {
        $this->seenContext = $context;

        return true;
    }
}


final class RuntimeRenderQueryConsumer implements QueryReadConsumerInterface
{
    public int $calls = 0;
    public ?ExecutionContext $seenContext = null;

    /** @param array<string,mixed> $result */
    public function __construct(private array $result) {}

    public function describe(string $sourceRef, ExecutionContext $context): array
    {
        return [];
    }

    public function read(array $request, ExecutionContext $context): array
    {
        ++$this->calls;
        $this->seenContext = $context;

        return $this->result;
    }
}
