<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetPresetCompilerTest extends TestCase
{
    private const WIDGET_A = '11111111-1111-4111-8111-111111111111';
    private const WIDGET_B = '22222222-2222-4222-8222-222222222222';
    private const PRESET = '33333333-3333-4333-8333-333333333333';

    public function testCompilesRoleDefaultAndPreservesWidgetOrder(): void
    {
        $repo = $this->repository();
        $descriptor = (new DashboardWidgetPresetCompiler($repo))->compile(
            $this->preset([
                'label' => 'Editors dashboard',
                'widget_definition_ids' => [self::WIDGET_B, self::WIDGET_A],
                'assignment' => [
                    'roles' => ['editor', 'administrator'],
                ],
            ]),
        );

        self::assertSame('Editors dashboard', $descriptor->label);
        self::assertSame([self::WIDGET_B, self::WIDGET_A], $descriptor->widgetDefinitionIds);
        self::assertSame(['administrator', 'editor'], $descriptor->roles);
        self::assertTrue($descriptor->isRoleDefault());
        self::assertFalse($descriptor->networkDefault);
    }

    public function testCompilesNetworkDefaultAndUnassignedPreset(): void
    {
        $repo = $this->repository();
        $compiler = new DashboardWidgetPresetCompiler($repo);

        $network = $compiler->compile($this->preset([
            'label' => 'Network baseline',
            'widget_definition_ids' => [self::WIDGET_A],
            'assignment' => ['network_default' => true],
        ]));
        self::assertTrue($network->networkDefault);
        self::assertSame([], $network->roles);

        $library = $compiler->compile($this->preset([
            'label' => 'Reusable preset',
            'widget_definition_ids' => [self::WIDGET_A],
        ]));
        self::assertFalse($library->networkDefault);
        self::assertFalse($library->isRoleDefault());
    }

    public function testRejectsInvalidReferencesAssignmentsAndUnknownFields(): void
    {
        $repo = $this->repository();
        $compiler = new DashboardWidgetPresetCompiler($repo);

        $cases = [
            [
                'label' => 'Missing widget',
                'widget_definition_ids' => ['44444444-4444-4444-8444-444444444444'],
            ],
            [
                'label' => 'Duplicate widgets',
                'widget_definition_ids' => [self::WIDGET_A, self::WIDGET_A],
            ],
            [
                'label' => 'Dual assignment',
                'widget_definition_ids' => [self::WIDGET_A],
                'assignment' => [
                    'roles' => ['editor'],
                    'network_default' => true,
                ],
            ],
            [
                'label' => 'Duplicate role',
                'widget_definition_ids' => [self::WIDGET_A],
                'assignment' => ['roles' => ['editor', 'editor']],
            ],
            [
                'label' => 'Unknown field',
                'widget_definition_ids' => [self::WIDGET_A],
                'callback' => 'forbidden',
            ],
            [
                'label' => 'Empty',
                'widget_definition_ids' => [],
            ],
        ];

        foreach ($cases as $payload) {
            try {
                $compiler->compile($this->preset($payload));
                self::fail('Expected invalid Dashboard Widget preset contract to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    private function repository(): InMemoryDefinitionRepository
    {
        $repo = new InMemoryDefinitionRepository();
        $repo->save($this->widget(self::WIDGET_A, 'widget-a'));
        $repo->save($this->widget(self::WIDGET_B, 'widget-b'));

        return $repo;
    }

    private function widget(string $id, string $slug): Definition
    {
        return new Definition(
            id: $id,
            slug: $slug,
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: [],
            revision: 1,
            dependencies: [],
        );
    }

    /** @param array<string,mixed> $preset */
    private function preset(array $preset): Definition
    {
        return new Definition(
            id: self::PRESET,
            slug: 'preset',
            type: DashboardWidgetPresetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetPresetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: ['preset' => $preset],
            revision: 2,
            dependencies: [],
        );
    }
}
