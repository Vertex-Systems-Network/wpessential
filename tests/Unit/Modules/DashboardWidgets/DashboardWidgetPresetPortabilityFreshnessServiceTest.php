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

final class DashboardWidgetPresetPortabilityFreshnessServiceTest extends TestCase
{
    public function testPublishedFingerprintMatchesDeterministicallyWithoutMutation(): void
    {
        $repo = $this->repository();
        $snapshot = $this->snapshotService($repo)->snapshot(self::PRESET);
        self::assertNotNull($snapshot);
        $before = $repo->get(self::PRESET);
        $service = $this->freshness($repo);
        self::assertSame(['status' => 'match', 'current' => true], $service->check(self::PRESET, $snapshot['sha256']));
        self::assertSame(['status' => 'match', 'current' => true], $service->check(self::PRESET, $snapshot['sha256']));
        self::assertSame($before, $repo->get(self::PRESET));
    }

    public function testRevisionAndOrderingChangesReportStaleWithoutReturningCurrentHash(): void
    {
        $repo = $this->repository();
        $snapshot = $this->snapshotService($repo)->snapshot(self::PRESET);
        self::assertNotNull($snapshot);
        $service = $this->freshness($repo);

        $repo->save($this->preset(self::PRESET, 2, [self::WIDGET_A, self::WIDGET_B]));
        self::assertSame(['status' => 'stale', 'current' => false], $service->check(self::PRESET, $snapshot['sha256']));
        $newSnapshot = $this->snapshotService($repo)->snapshot(self::PRESET);
        self::assertNotNull($newSnapshot);
        self::assertSame(['status' => 'match', 'current' => true], $service->check(self::PRESET, $newSnapshot['sha256']));

        $repo->save($this->preset(self::PRESET, 3, [self::WIDGET_B, self::WIDGET_A]));
        self::assertSame(['status' => 'stale', 'current' => false], $service->check(self::PRESET, $newSnapshot['sha256']));
    }

    public function testUnavailableAndMalformedPublishedCatalogAreDistinctAndFailClosed(): void
    {
        $repo = $this->repository();
        $service = $this->freshness($repo);
        $digest = str_repeat('a', 64);
        self::assertSame(['status' => 'unavailable', 'current' => false], $service->check(self::DRAFT, $digest));
        self::assertSame(['status' => 'unavailable', 'current' => false], $service->check(self::WIDGET_A, $digest));
        self::assertSame(['status' => 'unavailable', 'current' => false], $service->check('99999999-9999-4999-8999-999999999999', $digest));

        $repo->save($this->preset(self::PRESET, 2, [self::WIDGET_A], owner: 9));
        self::assertSame(['status' => 'unavailable', 'current' => false], $service->check(self::PRESET, $digest));
        $repo->save($this->preset(self::PRESET, 3, ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa']));
        self::assertSame(['status' => 'invalid_catalog', 'current' => false], $service->check(self::PRESET, $digest));
    }

    public function testServiceBoundaryRejectsMalformedAndUppercaseInputs(): void
    {
        $service = $this->freshness($this->repository());
        foreach ([
            ['not-a-uuid', str_repeat('a', 64)],
            [strtoupper(self::PRESET), str_repeat('a', 64)],
            [self::PRESET, str_repeat('A', 64)],
            [self::PRESET, 'bad'],
        ] as [$id, $digest]) {
            try {
                $service->check($id, $digest);
                self::fail('Invalid fingerprint input accepted.');
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
