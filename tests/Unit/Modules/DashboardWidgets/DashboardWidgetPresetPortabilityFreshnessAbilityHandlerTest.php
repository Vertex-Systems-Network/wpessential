<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityReadService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityFreshnessService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityFreshnessAbilityHandler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetReadService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetResolver;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRoleMembershipProviderInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetPresetPortabilityFreshnessAbilityHandlerTest extends TestCase
{
    public function testAbilityUsesReadOnlyPolicyBoundServiceWithStrictInputKeys(): void
    {
        $repo = $this->repository();
        $snapshot = $this->snapshotService($repo)->snapshot(self::PRESET);
        self::assertNotNull($snapshot);
        $before = $repo->get(self::PRESET);
        $handler = new DashboardWidgetPresetPortabilityFreshnessAbilityHandler($this->freshness($repo));
        self::assertSame(
            ['status' => 'match', 'current' => true],
            $handler->handle(['sha256' => $snapshot['sha256'], 'id' => self::PRESET], $this->context()),
        );
        self::assertSame(['status' => 'stale', 'current' => false], $handler->handle(
            ['id' => self::PRESET, 'sha256' => str_repeat('0', 64)], $this->context(),
        ));
        self::assertSame($before, $repo->get(self::PRESET));
    }

    public function testRejectsMissingExtraAndMalformedInputsWithoutFallback(): void
    {
        $handler = new DashboardWidgetPresetPortabilityFreshnessAbilityHandler($this->freshness($this->repository()));
        foreach ([
            [],
            ['id' => self::PRESET],
            ['sha256' => str_repeat('a', 64)],
            ['id' => self::PRESET, 'sha256' => str_repeat('a', 64), 'site_id' => 9],
            ['id' => strtoupper(self::PRESET), 'sha256' => str_repeat('a', 64)],
            ['id' => self::PRESET, 'sha256' => str_repeat('A', 64)],
            ['id' => self::PRESET, 'sha256' => ['untrusted']],
            ['id' => ['untrusted'], 'sha256' => str_repeat('a', 64)],
        ] as $input) {
            try {
                $handler->handle($input, $this->context());
                self::fail('Invalid input accepted.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    private const WIDGET_A = '11111111-1111-4111-8111-111111111111';
    private const WIDGET_B = '22222222-2222-4222-8222-222222222222';
    private const PRESET = '33333333-3333-4333-8333-333333333333';
    private const DRAFT = '44444444-4444-4444-8444-444444444444';

    private function repository(): InMemoryDefinitionRepository
    {
        $repo = new InMemoryDefinitionRepository();
        foreach ([self::WIDGET_A, self::WIDGET_B] as $index => $id) {
            $repo->save(new Definition(
                id: $id, slug: 'widget-' . $index, type: DashboardWidgetDefinition::TYPE,
                schemaVersion: 1, ownerSurfaceId: 10, status: DefinitionStatus::Published, payload: [],
            ));
        }
        $repo->save($this->preset(self::PRESET, 1, [self::WIDGET_A, self::WIDGET_B]));
        $repo->save($this->preset(self::DRAFT, 1, [self::WIDGET_A], DefinitionStatus::Draft));
        return $repo;
    }

    /** @param list<string> $widgets */
    private function preset(
        string $id,
        int $revision,
        array $widgets,
        DefinitionStatus $status = DefinitionStatus::Published,
        int $owner = 10,
    ): Definition {
        return new Definition(
            id: $id, slug: $id === self::PRESET ? 'published-preset' : 'draft-preset',
            type: DashboardWidgetPresetDefinition::TYPE, schemaVersion: 1,
            ownerSurfaceId: $owner, status: $status,
            payload: ['preset' => [
                'label' => 'A preset',
                'widget_definition_ids' => $widgets,
                'assignment' => ['roles' => ['editor']],
            ]], revision: $revision,
        );
    }

    private function snapshotService(InMemoryDefinitionRepository $repo): DashboardWidgetPresetPortabilityReadService
    {
        $roles = new class implements DashboardWidgetRoleMembershipProviderInterface {
            public function isCurrentUserContext(ExecutionContext $context): bool { return true; }
            public function hasAnyRole(ExecutionContext $context, array $roles): bool { return false; }
        };
        $compiler = new DashboardWidgetPresetCompiler($repo);
        return new DashboardWidgetPresetPortabilityReadService(new DashboardWidgetPresetReadService(
            $repo, $compiler, new DashboardWidgetPresetResolver($repo, $compiler, $roles),
        ));
    }

    private function freshness(InMemoryDefinitionRepository $repo): DashboardWidgetPresetPortabilityFreshnessService
    {
        return new DashboardWidgetPresetPortabilityFreshnessService($this->snapshotService($repo));
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 3);
    }
}
