<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyResolver;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRoleMembershipProviderInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetMultisitePolicyResolverTest extends TestCase
{
    private const WIDGET = '11111111-1111-4111-8111-111111111111';
    private const PRESET = '22222222-2222-4222-8222-222222222222';
    private const POLICY = '33333333-3333-4333-8333-333333333333';

    public function testResolvedContainsNetworkPresetEvidenceAndCapabilityDecision(): void
    {
        $repo = $this->repository();
        $repo->save($this->policy(array_replace($this->data(), ['exclude_site_ids' => [8]])));
        $resolution = $this->resolver($repo)->resolve($this->context())->toArray();
        self::assertSame('resolved', $resolution['status']);
        self::assertSame(self::POLICY, $resolution['policy_id']);
        self::assertSame(self::PRESET, $resolution['network_preset_id']);
        self::assertSame(2, $resolution['network_preset_revision']);
        self::assertSame([self::WIDGET], $resolution['widget_definition_ids']);
        self::assertTrue($resolution['can_manage_override']);

        $denied = $this->resolver($repo, allowed: false)->resolve($this->context())->toArray();
        self::assertSame('resolved', $denied['status']);
        self::assertFalse($denied['can_manage_override']);
    }

    public function testExcludedSiteNeverExposesOrAppliesPreset(): void
    {
        $repo = $this->repository();
        $repo->save($this->policy($this->data()));
        $result = $this->resolver($repo)->resolve($this->context())->toArray();
        self::assertSame('excluded', $result['status']);
        self::assertNull($result['network_preset_id']);
        self::assertSame([], $result['widget_definition_ids']);
        self::assertFalse($result['can_manage_override']);
    }

    public function testNoneConflictAndMalformedCatalogFailClosed(): void
    {
        $repo = $this->repository();
        self::assertSame('none', $this->resolver($repo)->resolve($this->context())->status);
        $repo->save($this->policy($this->data()));
        $repo->save($this->policy($this->data(), '44444444-4444-4444-8444-444444444444'));
        self::assertSame('conflict', $this->resolver($repo)->resolve($this->context())->status);
        $bad = $this->repository();
        $bad->save($this->policy(['blueprint_site_id' => 2]));
        self::assertSame('invalid_catalog', $this->resolver($bad)->resolve($this->context())->status);
    }

    public function testRejectsNonCurrentOrNonMultisiteContext(): void
    {
        $repo = $this->repository();
        $repo->save($this->policy($this->data()));
        self::assertSame('unavailable', $this->resolver($repo)->resolve($this->context(null))->status);
        self::assertSame('unavailable', $this->resolver($repo, current: false)->resolve($this->context())->status);
        self::assertSame('unavailable', $this->resolver($repo, multisite: false)->resolve($this->context())->status);
        self::assertSame('unavailable', $this->resolver($repo, network: 99)->resolve($this->context())->status);
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

    private function resolver(InMemoryDefinitionRepository $repo, bool $current = true, bool $allowed = true, bool $multisite = true, int $network = 4): DashboardWidgetMultisitePolicyResolver
    {
        $user = new class($current) implements DashboardWidgetRoleMembershipProviderInterface {
            public function __construct(private bool $current) {}
            public function isCurrentUserContext(ExecutionContext $context): bool { return $this->current && $context->principal->userId === 7 && $context->siteId === 5; }
            public function hasAnyRole(ExecutionContext $context, array $roles): bool { return false; }
        };
        $checker = new class($allowed) implements CapabilityCheckerInterface {
            public function __construct(private bool $allowed) {}
            public function can(ExecutionContext $context, string $capability): bool
            {
                return $this->allowed && $capability === 'manage_options';
            }
        };
        return new DashboardWidgetMultisitePolicyResolver(
            $repo, new DashboardWidgetMultisitePolicyCompiler($repo, new DashboardWidgetPresetCompiler($repo)),
            $user, $checker,
            static fn (): bool => $multisite,
            static fn (): int => $network,
        );
    }

    private function context(?int $network = 4): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 5, networkId: $network);
    }
}
