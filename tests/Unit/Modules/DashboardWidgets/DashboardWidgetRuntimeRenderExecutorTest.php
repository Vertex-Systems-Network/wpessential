<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentBlueprintCatalog;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
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

    private function executor(
        DefinitionRepositoryInterface $definitions,
        RuntimeRenderCapturingRenderer $renderer,
        RuntimeRenderRoleProvider $roles,
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
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $richText = $catalog->forContentType('rich_text');
        self::assertNotNull($richText);

        $renderSource ??= [
            'kind' => 'component_blueprint',
            'blueprint_id' => $richText->id,
            'blueprint_revision' => $richText->revision,
            'bindings' => [
                'content' => ['source' => 'literal', 'value' => 'Safe content'],
            ],
        ];

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
