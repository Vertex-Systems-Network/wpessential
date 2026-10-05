<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetReadAbilityHandler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetReadService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetResolver;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRoleMembershipProviderInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardPresetAbilityRoleProvider implements DashboardWidgetRoleMembershipProviderInterface
{
    public function isCurrentUserContext(ExecutionContext $context): bool
    {
        return true;
    }

    public function hasAnyRole(ExecutionContext $context, array $roles): bool
    {
        return in_array('editor', $roles, true);
    }
}

final class DashboardWidgetPresetReadAbilityHandlerTest extends TestCase
{
    private const WIDGET = '11111111-1111-4111-8111-111111111111';
    private const PRESET = '22222222-2222-4222-8222-222222222222';

    public function testRoutesGetCatalogAndEffectiveReads(): void
    {
        $service = $this->service();
        $context = new ExecutionContext(new Principal(7), 3);

        $get = (new DashboardWidgetPresetReadAbilityHandler(
            $service,
            DashboardWidgetPresetReadAbilityHandler::GET,
        ))->handle(['id' => self::PRESET], $context);
        self::assertSame(self::PRESET, $get['definition_id']);

        $catalog = (new DashboardWidgetPresetReadAbilityHandler(
            $service,
            DashboardWidgetPresetReadAbilityHandler::CATALOG,
        ))->handle([], $context);
        self::assertCount(1, $catalog);

        $effective = (new DashboardWidgetPresetReadAbilityHandler(
            $service,
            DashboardWidgetPresetReadAbilityHandler::EFFECTIVE,
        ))->handle([], $context);
        self::assertSame('resolved', $effective['status']);
        self::assertSame('role_default', $effective['source']);
    }

    public function testRejectsMalformedInputsAndUnknownAction(): void
    {
        $service = $this->service();
        $context = new ExecutionContext(new Principal(7), 3);

        foreach ([
            fn () => new DashboardWidgetPresetReadAbilityHandler($service, 'unknown'),
            fn () => (new DashboardWidgetPresetReadAbilityHandler(
                $service,
                DashboardWidgetPresetReadAbilityHandler::GET,
            ))->handle([], $context),
            fn () => (new DashboardWidgetPresetReadAbilityHandler(
                $service,
                DashboardWidgetPresetReadAbilityHandler::GET,
            ))->handle(['id' => self::PRESET, 'extra' => true], $context),
            fn () => (new DashboardWidgetPresetReadAbilityHandler(
                $service,
                DashboardWidgetPresetReadAbilityHandler::GET,
            ))->handle(['id' => 'not-a-uuid'], $context),
            fn () => (new DashboardWidgetPresetReadAbilityHandler(
                $service,
                DashboardWidgetPresetReadAbilityHandler::CATALOG,
            ))->handle(['extra' => true], $context),
            fn () => (new DashboardWidgetPresetReadAbilityHandler(
                $service,
                DashboardWidgetPresetReadAbilityHandler::EFFECTIVE,
            ))->handle(['roles' => ['administrator']], $context),
        ] as $case) {
            try {
                $case();
                self::fail('Expected invalid preset read input to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    private function service(): DashboardWidgetPresetReadService
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
        $repo->save(new Definition(
            id: self::PRESET,
            slug: 'editors',
            type: DashboardWidgetPresetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetPresetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: ['preset' => [
                'label' => 'Editors',
                'widget_definition_ids' => [self::WIDGET],
                'assignment' => ['roles' => ['editor']],
            ]],
            revision: 1,
            dependencies: [],
        ));

        $compiler = new DashboardWidgetPresetCompiler($repo);
        $resolver = new DashboardWidgetPresetResolver(
            $repo,
            $compiler,
            new DashboardPresetAbilityRoleProvider(),
        );

        return new DashboardWidgetPresetReadService($repo, $compiler, $resolver);
    }
}
