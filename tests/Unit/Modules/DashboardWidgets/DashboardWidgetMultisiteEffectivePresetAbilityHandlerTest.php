<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisiteEffectivePresetAbilityHandler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisiteEffectivePresetResolver;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyResolver;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRoleMembershipProviderInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetSubsitePresetOverrideCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetSubsitePresetOverrideDefinition;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetMultisiteEffectivePresetAbilityHandlerTest extends TestCase
{
    private const WIDGET_NETWORK = '11111111-1111-4111-8111-111111111111';
    private const PRESET_NETWORK = '22222222-2222-4222-8222-222222222222';
    private const PRESET_SITE = '33333333-3333-4333-8333-333333333333';
    private const POLICY = '44444444-4444-4444-8444-444444444444';
    private const OVERRIDE = '55555555-5555-4555-8555-555555555555';
    private const WIDGET_SITE = '77777777-7777-4777-8777-777777777777';

    public function testHandlerOnlyReadsEffectiveSelectionWithoutMutation(): void
    {
        $repo = $this->repository();
        $repo->save($this->networkPolicy());
        $repo->save($this->override($this->overrideData()));
        $handler = new DashboardWidgetMultisiteEffectivePresetAbilityHandler($this->resolver($repo));
        $before = $repo->get(self::OVERRIDE);
        $result = $handler->handle([], $this->context());
        self::assertSame('subsite_override', $result['source']);
        self::assertSame([self::WIDGET_SITE, self::WIDGET_NETWORK], $result['widget_definition_ids']);
        self::assertSame($before, $repo->get(self::OVERRIDE));
    }

    public function testHandlerRejectsInjectedInput(): void
    {
        $repo = $this->repository();
        $handler = new DashboardWidgetMultisiteEffectivePresetAbilityHandler($this->resolver($repo));
        $this->expectException(InvalidArgumentException::class);
        $handler->handle(['site_id' => 99], $this->context());
    }
    private function repository(): InMemoryDefinitionRepository
    {
        $repo = new InMemoryDefinitionRepository();
        foreach ([self::WIDGET_NETWORK, self::WIDGET_SITE] as $index => $id) {
            $repo->save(new Definition(
                id: $id, slug: 'widget-' . $index, type: DashboardWidgetDefinition::TYPE,
                schemaVersion: 1, ownerSurfaceId: 10, status: DefinitionStatus::Published, payload: [],
            ));
        }
        $repo->save($this->preset(self::PRESET_NETWORK, true, [self::WIDGET_NETWORK]));
        $repo->save($this->preset(self::PRESET_SITE, false, [self::WIDGET_SITE, self::WIDGET_NETWORK]));
        return $repo;
    }

    /** @param list<string> $widgetIds */
    private function preset(
        string $id,
        bool $networkDefault,
        array $widgetIds,
        array $roles = [],
        DefinitionStatus $status = DefinitionStatus::Published,
    ): Definition {
        return new Definition(
            id: $id,
            slug: $networkDefault ? 'network-preset' : 'site-preset',
            type: DashboardWidgetPresetDefinition::TYPE, schemaVersion: 1, ownerSurfaceId: 10,
            status: $status,
            payload: ['preset' => [
                'label' => 'Fixture', 'widget_definition_ids' => $widgetIds,
                'assignment' => ['network_default' => $networkDefault, 'roles' => $roles],
            ]],
            revision: 2,
        );
    }

    /** @param array<string,mixed> $data */
    private function override(array $data, string $id = self::OVERRIDE, DefinitionStatus $status = DefinitionStatus::Published): Definition
    {
        return new Definition(
            id: $id,
            slug: $id === self::OVERRIDE ? 'local-override' : 'another-override',
            type: DashboardWidgetSubsitePresetOverrideDefinition::TYPE, schemaVersion: 1,
            ownerSurfaceId: 10, status: $status, payload: ['subsite_preset_override' => $data], revision: 1,
        );
    }

    /** @return array<string,mixed> */
    private function overrideData(): array
    {
        return [
            'network_id' => 4, 'site_id' => 5,
            'policy_id' => self::POLICY, 'preset_id' => self::PRESET_SITE,
        ];
    }

    /** @param array<string,mixed> $changes */
    private function networkPolicy(array $changes = []): Definition
    {
        return new Definition(
            id: self::POLICY, slug: 'network-policy',
            type: DashboardWidgetMultisitePolicyDefinition::TYPE,
            schemaVersion: 1, ownerSurfaceId: 10, status: DefinitionStatus::Published,
            payload: ['multisite' => array_replace([
                'blueprint_site_id' => 2,
                'network_preset_id' => self::PRESET_NETWORK,
                'exclude_site_ids' => [],
                'subsite_override' => true,
                'settings_capability' => 'manage_options',
            ], $changes)],
            revision: 3,
        );
    }

    private function resolver(
        InMemoryDefinitionRepository $repo,
        bool $current = true,
        bool $allowed = true,
    ): DashboardWidgetMultisiteEffectivePresetResolver {
        $role = new class($current) implements DashboardWidgetRoleMembershipProviderInterface {
            public function __construct(private readonly bool $current) {}
            public function isCurrentUserContext(ExecutionContext $context): bool { return $this->current; }
            public function hasAnyRole(ExecutionContext $context, array $roles): bool { return false; }
        };
        $capability = new class($allowed) implements CapabilityCheckerInterface {
            public function __construct(private readonly bool $allowed) {}
            public function can(ExecutionContext $context, string $capability): bool
            {
                return $this->allowed && $capability === 'manage_options';
            }
        };
        $presetCompiler = new DashboardWidgetPresetCompiler($repo);
        $policyResolver = new DashboardWidgetMultisitePolicyResolver(
            $repo, new DashboardWidgetMultisitePolicyCompiler($repo, $presetCompiler),
            $role, $capability, static fn (): bool => true, static fn (): int => 4,
        );
        return new DashboardWidgetMultisiteEffectivePresetResolver(
            $policyResolver, $repo, new DashboardWidgetSubsitePresetOverrideCompiler($repo, $presetCompiler),
        );
    }

    private function context(int $siteId = 5, ?int $networkId = 4): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), $siteId, networkId: $networkId);
    }
}
