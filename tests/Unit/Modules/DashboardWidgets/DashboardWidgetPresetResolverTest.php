<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetResolution;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetResolver;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRoleMembershipProviderInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardPresetTestRoleProvider implements DashboardWidgetRoleMembershipProviderInterface
{
    /** @param list<string> $currentRoles */
    public function __construct(
        private readonly bool $current,
        private readonly array $currentRoles,
    ) {}

    public function isCurrentUserContext(ExecutionContext $context): bool
    {
        return $this->current;
    }

    public function hasAnyRole(ExecutionContext $context, array $roles): bool
    {
        foreach ($roles as $role) {
            if (in_array($role, $this->currentRoles, true)) {
                return true;
            }
        }

        return false;
    }
}

final class DashboardWidgetPresetResolverTest extends TestCase
{
    private const WIDGET = '11111111-1111-4111-8111-111111111111';

    public function testRoleDefaultPrecedesNetworkDefault(): void
    {
        $repo = $this->repository();
        $repo->save($this->preset(
            '22222222-2222-4222-8222-222222222222',
            'network',
            ['network_default' => true],
        ));
        $repo->save($this->preset(
            '33333333-3333-4333-8333-333333333333',
            'editor',
            ['roles' => ['editor']],
        ));

        $resolution = $this->resolver($repo, ['editor'])->resolve($this->context());

        self::assertSame(DashboardWidgetPresetResolution::STATUS_RESOLVED, $resolution->status);
        self::assertSame(DashboardWidgetPresetResolution::SOURCE_ROLE_DEFAULT, $resolution->source);
        self::assertSame('33333333-3333-4333-8333-333333333333', $resolution->presetId);
        self::assertSame([self::WIDGET], $resolution->widgetDefinitionIds);
    }

    public function testFallsBackToSingleNetworkDefaultWhenNoRolePresetMatches(): void
    {
        $repo = $this->repository();
        $repo->save($this->preset(
            '22222222-2222-4222-8222-222222222222',
            'network',
            ['network_default' => true],
        ));
        $repo->save($this->preset(
            '33333333-3333-4333-8333-333333333333',
            'editor',
            ['roles' => ['editor']],
        ));

        $resolution = $this->resolver($repo, ['subscriber'])->resolve($this->context());

        self::assertSame(DashboardWidgetPresetResolution::STATUS_RESOLVED, $resolution->status);
        self::assertSame(DashboardWidgetPresetResolution::SOURCE_NETWORK_DEFAULT, $resolution->source);
        self::assertSame('22222222-2222-4222-8222-222222222222', $resolution->presetId);
    }

    public function testConflictsFailClosedWithoutPresetEvidence(): void
    {
        $roleRepo = $this->repository();
        $roleRepo->save($this->preset(
            '22222222-2222-4222-8222-222222222222',
            'editors-a',
            ['roles' => ['editor']],
        ));
        $roleRepo->save($this->preset(
            '33333333-3333-4333-8333-333333333333',
            'editors-b',
            ['roles' => ['editor']],
        ));

        $roleConflict = $this->resolver($roleRepo, ['editor'])->resolve($this->context());
        self::assertSame(DashboardWidgetPresetResolution::STATUS_CONFLICT, $roleConflict->status);
        self::assertNull($roleConflict->presetId);
        self::assertSame([], $roleConflict->widgetDefinitionIds);

        $networkRepo = $this->repository();
        $networkRepo->save($this->preset(
            '44444444-4444-4444-8444-444444444444',
            'network-a',
            ['network_default' => true],
        ));
        $networkRepo->save($this->preset(
            '55555555-5555-4555-8555-555555555555',
            'network-b',
            ['network_default' => true],
        ));

        $networkConflict = $this->resolver($networkRepo, [])->resolve($this->context());
        self::assertSame(DashboardWidgetPresetResolution::STATUS_CONFLICT, $networkConflict->status);
        self::assertNull($networkConflict->source);
        self::assertNull($networkConflict->presetId);
    }

    public function testMalformedPublishedCatalogFailsClosed(): void
    {
        $repo = $this->repository();
        $repo->save(new Definition(
            id: '66666666-6666-4666-8666-666666666666',
            slug: 'broken',
            type: DashboardWidgetPresetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetPresetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: ['preset' => [
                'label' => 'Broken',
                'widget_definition_ids' => ['77777777-7777-4777-8777-777777777777'],
            ]],
            revision: 1,
            dependencies: [],
        ));

        $resolution = $this->resolver($repo, [])->resolve($this->context());

        self::assertSame(DashboardWidgetPresetResolution::STATUS_INVALID_CATALOG, $resolution->status);
        self::assertNull($resolution->presetId);
    }

    public function testUnavailableAndNoneAreDistinct(): void
    {
        $repo = $this->repository();

        $unavailable = $this->resolver($repo, [], current: false)->resolve($this->context());
        self::assertSame(DashboardWidgetPresetResolution::STATUS_UNAVAILABLE, $unavailable->status);

        $none = $this->resolver($repo, [])->resolve($this->context());
        self::assertSame(DashboardWidgetPresetResolution::STATUS_NONE, $none->status);
    }

    /** @param list<string> $roles */
    private function resolver(
        InMemoryDefinitionRepository $repo,
        array $roles,
        bool $current = true,
    ): DashboardWidgetPresetResolver {
        return new DashboardWidgetPresetResolver(
            $repo,
            new DashboardWidgetPresetCompiler($repo),
            new DashboardPresetTestRoleProvider($current, $roles),
        );
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

        return $repo;
    }

    /** @param array<string,mixed> $assignment */
    private function preset(string $id, string $slug, array $assignment): Definition
    {
        return new Definition(
            id: $id,
            slug: $slug,
            type: DashboardWidgetPresetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetPresetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: ['preset' => [
                'label' => ucfirst($slug),
                'widget_definition_ids' => [self::WIDGET],
                'assignment' => $assignment,
            ]],
            revision: 2,
            dependencies: [],
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 3);
    }
}
