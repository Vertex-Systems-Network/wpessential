<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetMultisitePolicyCompilerTest extends TestCase
{
    private const WIDGET = '11111111-1111-4111-8111-111111111111';
    private const PRESET = '22222222-2222-4222-8222-222222222222';
    private const POLICY = '33333333-3333-4333-8333-333333333333';

    public function testCompilesNetworkPolicyAndNormalizesExclusions(): void
    {
        $repo = $this->repository();
        $policy = (new DashboardWidgetMultisitePolicyCompiler($repo, new DashboardWidgetPresetCompiler($repo)))
            ->compile($this->policy($this->data()));
        self::assertSame(self::POLICY, $policy->definitionId);
        self::assertSame(2, $policy->blueprintSiteId);
        self::assertSame(self::PRESET, $policy->networkPresetId);
        self::assertSame(2, $policy->networkPresetRevision);
        self::assertSame([self::WIDGET], $policy->widgetDefinitionIds);
        self::assertSame([5, 9], $policy->excludeSiteIds);
        self::assertTrue($policy->subsiteOverride);
        self::assertSame('manage_options', $policy->settingsCapability);
        self::assertTrue($policy->excludesSite(5));
        self::assertFalse($policy->excludesSite(2));
    }

    public function testRejectsMalformedAndUnauthorizedPolicyFields(): void
    {
        $repo = $this->repository();
        $compiler = new DashboardWidgetMultisitePolicyCompiler($repo, new DashboardWidgetPresetCompiler($repo));
        $cases = [
            ['blueprint_site_id' => 0], ['blueprint_site_id' => '2'],
            ['network_preset_id' => 'wrong'], ['exclude_site_ids' => [0]],
            ['exclude_site_ids' => [2]], ['exclude_site_ids' => [5, 5]],
            ['exclude_site_ids' => ['5']], ['exclude_site_ids' => range(10, 110)],
            ['exclude_site_ids' => 'bogus'], ['subsite_override' => 1],
            ['settings_capability' => 'Manage Options'],
            ['settings_capability' => 'manage options'],
            ['unexpected' => 'no'],
        ];
        foreach ($cases as $change) {
            try {
                $compiler->compile($this->policy(array_replace($this->data(), $change)));
                self::fail('Expected invalid Multisite policy to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRejectsNonNetworkPresetAndMissingPreset(): void
    {
        $repo = $this->repository();
        $compiler = new DashboardWidgetMultisitePolicyCompiler($repo, new DashboardWidgetPresetCompiler($repo));
        $data = $this->data();
        $data['network_preset_id'] = '44444444-4444-4444-8444-444444444444';
        $this->expectException(InvalidArgumentException::class);
        $compiler->compile($this->policy($data));
    }

    public function testRejectsWrongOwnerOrType(): void
    {
        $repo = $this->repository();
        $compiler = new DashboardWidgetMultisitePolicyCompiler($repo, new DashboardWidgetPresetCompiler($repo));
        $wrong = new Definition(
            id: self::POLICY, slug: 'wrong', type: DashboardWidgetMultisitePolicyDefinition::TYPE,
            schemaVersion: 1, ownerSurfaceId: 9, status: DefinitionStatus::Published,
            payload: ['multisite' => $this->data()],
        );
        $this->expectException(InvalidArgumentException::class);
        $compiler->compile($wrong);
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
}
