<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyReadService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyResolver;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRoleMembershipProviderInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetMultisitePolicyReadServiceTest extends TestCase
{
    private const WIDGET = '11111111-1111-4111-8111-111111111111';
    private const PRESET = '22222222-2222-4222-8222-222222222222';
    private const POLICY = '33333333-3333-4333-8333-333333333333';

    public function testReadsPublishedOnlyAndDeterministicCatalog(): void
    {
        $repo = $this->repository();
        $repo->save($this->policy($this->data()));
        $repo->save($this->policy($this->data(), '44444444-4444-4444-8444-444444444444', DefinitionStatus::Draft));
        $service = $this->service($repo);
        self::assertSame(self::POLICY, $service->get(self::POLICY)['definition_id']);
        self::assertNull($service->get(self::PRESET));
        self::assertNull($service->get('44444444-4444-4444-8444-444444444444'));
        self::assertSame([self::POLICY], array_column($service->catalog(), 'definition_id'));
    }

    public function testEffectiveReturnsTypedReadOnlyPolicyState(): void
    {
        $repo = $this->repository();
        $repo->save($this->policy(array_replace($this->data(), ['exclude_site_ids' => []])));
        $result = $this->service($repo)->effective(new ExecutionContext(new Principal(7), 5, networkId: 4));
        self::assertSame('resolved', $result['status']);
        self::assertSame(self::PRESET, $result['network_preset_id']);
        self::assertTrue($result['can_manage_override']);
    }

    private function repository(): InMemoryDefinitionRepository
    {
        $repo = new InMemoryDefinitionRepository();
        $repo->save(new Definition(
            id: self::WIDGET, slug: 'widget', type: DashboardWidgetDefinition::TYPE, schemaVersion: 1,
            ownerSurfaceId: 10, status: DefinitionStatus::Published, payload: [], revision: 1,
        ));
        $repo->save(new Definition(
            id: self::PRESET, slug: 'preset', type: DashboardWidgetPresetDefinition::TYPE, schemaVersion: 1,
            ownerSurfaceId: 10, status: DefinitionStatus::Published,
            payload: ['preset' => ['label' => 'Network baseline',
                'widget_definition_ids' => [self::WIDGET],
                'assignment' => ['network_default' => true]]], revision: 2,
        ));
        return $repo;
    }

    /** @param array<string,mixed> $data */
    private function policy(array $data, string $id = self::POLICY, DefinitionStatus $status = DefinitionStatus::Published): Definition
    {
        return new Definition(
            id: $id, slug: $id === self::POLICY ? 'policy' : 'other-policy',
            type: DashboardWidgetMultisitePolicyDefinition::TYPE, schemaVersion: 1,
            ownerSurfaceId: 10, status: $status, payload: ['multisite' => $data], revision: 3,
        );
    }

    /** @return array<string,mixed> */
    private function data(): array
    {
        return [
            'blueprint_site_id' => 2,
            'network_preset_id' => self::PRESET,
            'exclude_site_ids' => [9, 5],
            'subsite_override' => true,
            'settings_capability' => 'manage_options',
        ];
    }

    private function service(InMemoryDefinitionRepository $repo): DashboardWidgetMultisitePolicyReadService
    {
        $compiler = new DashboardWidgetMultisitePolicyCompiler($repo, new DashboardWidgetPresetCompiler($repo));
        $user = new class implements DashboardWidgetRoleMembershipProviderInterface {
            public function isCurrentUserContext(ExecutionContext $context): bool { return true; }
            public function hasAnyRole(ExecutionContext $context, array $roles): bool { return false; }
        };
        $checker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool { return true; }
        };
        return new DashboardWidgetMultisitePolicyReadService($repo, $compiler,
            new DashboardWidgetMultisitePolicyResolver($repo, $compiler, $user, $checker,
                static fn (): bool => true, static fn (): int => 4));
    }
}
