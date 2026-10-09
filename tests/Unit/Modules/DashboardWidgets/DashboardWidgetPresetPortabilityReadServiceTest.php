<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetReadService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetResolver;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityReadService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityAbilityHandler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRoleMembershipProviderInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetPresetPortabilityReadServiceTest extends TestCase
{
    public function testStableEnvelopeMatchesCanonicalPayloadAndHasNonSigningFingerprint(): void
    {
        $repo = $this->repository();
        $export = $this->service($repo);
        $first = $export->snapshot(self::PRESET);
        self::assertNotNull($first);
        self::assertSame($first, $export->snapshot(self::PRESET));
        self::assertSame('wpessential-dashboard-preset', $first['format']);
        self::assertSame(1, $first['version']);
        self::assertSame(self::PRESET, $first['payload']['definition_id']);
        self::assertSame([self::WIDGET_A, self::WIDGET_B], $first['payload']['widget_definition_ids']);
        self::assertSame(['administrator', 'editor'], $first['payload']['assignment']['roles']);
        self::assertSame(['roles', 'network_default'], array_keys($first['payload']['assignment']));
        self::assertSame(['format', 'version', 'payload', 'sha256'], array_keys($first));
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $first['sha256']);
        self::assertSame(hash('sha256', json_encode(
            $first['payload'],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        )), $first['sha256']);
    }

    public function testRevisionsAndOrderedReferencesChangeFingerprintWithoutSavingAnything(): void
    {
        $repo = $this->repository();
        $service = $this->service($repo);
        $first = $service->snapshot(self::PRESET);
        $before = $repo->get(self::PRESET);
        self::assertSame($before, $repo->get(self::PRESET));

        $repo->save($this->preset(self::PRESET, 2, [self::WIDGET_A, self::WIDGET_B]));
        $revision = $service->snapshot(self::PRESET);
        self::assertNotSame($first['sha256'], $revision['sha256']);
        self::assertSame(2, $revision['payload']['revision']);

        $repo->save($this->preset(self::PRESET, 2, [self::WIDGET_B, self::WIDGET_A]));
        $order = $service->snapshot(self::PRESET);
        self::assertNotSame($revision['sha256'], $order['sha256']);
        self::assertSame([self::WIDGET_B, self::WIDGET_A], $order['payload']['widget_definition_ids']);
    }

    public function testDraftMissingForeignAndBrokenWidgetReferencesStayUnavailableOrFailClosed(): void
    {
        $repo = $this->repository();
        $service = $this->service($repo);
        self::assertNull($service->snapshot(self::DRAFT));
        self::assertNull($service->snapshot('99999999-9999-4999-8999-999999999999'));
        self::assertNull($service->snapshot(self::WIDGET_A));

        $repo->save($this->preset(self::PRESET, 1, [self::WIDGET_A], owner: 9));
        self::assertNull($service->snapshot(self::PRESET));

        $repo->save($this->preset(self::PRESET, 3, ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa']));
        $this->expectException(InvalidArgumentException::class);
        $service->snapshot(self::PRESET);
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
                id: $id,
                slug: 'widget-' . $index,
                type: DashboardWidgetDefinition::TYPE,
                schemaVersion: 1,
                ownerSurfaceId: 10,
                status: DefinitionStatus::Published,
                payload: [],
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
            id: $id,
            slug: $id === self::PRESET ? 'published-preset' : 'draft-preset',
            type: DashboardWidgetPresetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: $owner,
            status: $status,
            payload: ['preset' => [
                'label' => 'Fixture Export',
                'widget_definition_ids' => $widgets,
                'assignment' => ['roles' => ['editor', 'administrator']],
            ]],
            revision: $revision,
        );
    }

    private function service(InMemoryDefinitionRepository $repo): DashboardWidgetPresetPortabilityReadService
    {
        $provider = new class implements DashboardWidgetRoleMembershipProviderInterface {
            public function isCurrentUserContext(ExecutionContext $context): bool { return true; }
            public function hasAnyRole(ExecutionContext $context, array $roles): bool { return false; }
        };
        $compiler = new DashboardWidgetPresetCompiler($repo);
        $reader = new DashboardWidgetPresetReadService(
            $repo,
            $compiler,
            new DashboardWidgetPresetResolver($repo, $compiler, $provider),
        );
        return new DashboardWidgetPresetPortabilityReadService($reader);
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 3);
    }
}
