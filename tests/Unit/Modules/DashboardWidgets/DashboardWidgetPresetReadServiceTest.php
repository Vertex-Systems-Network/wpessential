<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetReadService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetResolver;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRoleMembershipProviderInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardPresetReadRoleProvider implements DashboardWidgetRoleMembershipProviderInterface
{
    /** @param list<string> $roles */
    public function __construct(private readonly array $roles) {}

    public function isCurrentUserContext(ExecutionContext $context): bool
    {
        return $context->principal->userId === 7 && $context->siteId === 3;
    }

    public function hasAnyRole(ExecutionContext $context, array $roles): bool
    {
        return array_intersect($roles, $this->roles) !== [];
    }
}

final class DashboardWidgetPresetReadServiceTest extends TestCase
{
    private const WIDGET = '11111111-1111-4111-8111-111111111111';
    private const ROLE_PRESET = '22222222-2222-4222-8222-222222222222';
    private const NETWORK_PRESET = '33333333-3333-4333-8333-333333333333';
    private const DRAFT_PRESET = '44444444-4444-4444-8444-444444444444';

    public function testReadsPublishedPresetCatalogAndEffectiveResolution(): void
    {
        $repo = $this->repository();
        $service = $this->service($repo, ['editor']);

        $role = $service->get(self::ROLE_PRESET);
        self::assertNotNull($role);
        self::assertSame('Editors', $role['label']);
        self::assertSame(['editor'], $role['assignment']['roles']);
        self::assertFalse($role['assignment']['network_default']);

        self::assertNull($service->get(self::WIDGET));
        self::assertNull($service->get(self::DRAFT_PRESET));

        $catalog = $service->catalog();
        self::assertSame(
            [self::ROLE_PRESET, self::NETWORK_PRESET],
            array_column($catalog, 'definition_id'),
        );

        $effective = $service->effective($this->context());
        self::assertSame('resolved', $effective['status']);
        self::assertSame('role_default', $effective['source']);
        self::assertSame(self::ROLE_PRESET, $effective['preset_id']);
        self::assertSame([self::WIDGET], $effective['widget_definition_ids']);
    }

    private function repository(): InMemoryDefinitionRepository
    {
        $repo = new InMemoryDefinitionRepository();
        $repo->save(new Definition(
            id: self::WIDGET,
            slug: 'widget',
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: [],
            revision: 1,
            dependencies: [],
        ));
        $repo->save($this->preset(
            self::ROLE_PRESET,
            'a-editors',
            DefinitionStatus::Published,
            ['roles' => ['editor']],
        ));
        $repo->save($this->preset(
            self::NETWORK_PRESET,
            'b-network',
            DefinitionStatus::Published,
            ['network_default' => true],
        ));
        $repo->save($this->preset(
            self::DRAFT_PRESET,
            'c-draft',
            DefinitionStatus::Draft,
            [],
        ));

        return $repo;
    }

    /** @param list<string> $roles */
    private function service(InMemoryDefinitionRepository $repo, array $roles): DashboardWidgetPresetReadService
    {
        $compiler = new DashboardWidgetPresetCompiler($repo);
        $resolver = new DashboardWidgetPresetResolver(
            $repo,
            $compiler,
            new DashboardPresetReadRoleProvider($roles),
        );

        return new DashboardWidgetPresetReadService($repo, $compiler, $resolver);
    }

    /** @param array<string,mixed> $assignment */
    private function preset(
        string $id,
        string $slug,
        DefinitionStatus $status,
        array $assignment,
    ): Definition {
        $label = match ($slug) {
            'a-editors' => 'Editors',
            'b-network' => 'Network',
            default => 'Draft',
        };

        return new Definition(
            id: $id,
            slug: $slug,
            type: DashboardWidgetPresetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetPresetDefinition::OWNER_SURFACE_ID,
            status: $status,
            payload: ['preset' => [
                'label' => $label,
                'widget_definition_ids' => [self::WIDGET],
                'assignment' => $assignment,
            ]],
            revision: 1,
            dependencies: [],
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 3);
    }
}
