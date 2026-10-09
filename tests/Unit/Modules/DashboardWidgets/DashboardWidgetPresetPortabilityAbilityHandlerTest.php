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

final class DashboardWidgetPresetPortabilityAbilityHandlerTest extends TestCase
{
    public function testAbilityEmitsOnlyValidatedSnapshotForPublishedPreset(): void
    {
        $repo = $this->repository();
        $handler = new DashboardWidgetPresetPortabilityAbilityHandler($this->service($repo));
        $old = $repo->get(self::PRESET);
        $result = $handler->handle(['id' => self::PRESET], $this->context());
        self::assertSame(self::PRESET, $result['payload']['definition_id']);
        self::assertSame($old, $repo->get(self::PRESET));
        self::assertNull($handler->handle(['id' => self::DRAFT], $this->context()));
    }

    public function testAbilityRejectsMissingExtraMalformedAndCaseChangedInput(): void
    {
        $handler = new DashboardWidgetPresetPortabilityAbilityHandler($this->service($this->repository()));
        foreach ([
            [],
            ['id' => self::PRESET, 'site_id' => 99],
            ['id' => 'INVALID'],
            ['id' => strtoupper(self::PRESET)],
            ['id' => ['dangerous']],
            ['site_id' => 3],
        ] as $input) {
            try {
                $handler->handle($input, $this->context());
                self::fail('Invalid preset portability request accepted.');
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
