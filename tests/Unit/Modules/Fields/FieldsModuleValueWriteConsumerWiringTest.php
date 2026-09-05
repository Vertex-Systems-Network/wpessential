<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\FieldValueWriteConsumerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Fields\FieldsModule;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;

final class FieldsModuleValueWriteConsumerWiringTest extends TestCase
{
    public function testRegistersOwnerWriteConsumerUnderExplicitServiceIdWithPublicContractType(): void
    {
        $services = new ServiceRegistry();
        $environment = new class implements WordPressAbilityEnvironmentInterface {
            public function abilitiesApiAvailable(): bool { return false; }
            public function doingAction(string $hook): bool { return false; }
            public function currentUserId(): ?int { return 7; }
            public function currentSiteId(): int { return 1; }
            public function currentNetworkId(): ?int { return 1; }
            public function currentUserCan(string $capability): bool { return true; }
            public function isRestRequest(): bool { return false; }
            public function isCli(): bool { return false; }
            public function registerCategory(string $slug, array $args): bool { return true; }
            public function registerAbility(string $name, array $args): bool { return true; }
        };
        $policy = new PolicyEngine(new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return true;
            }
        });
        $abilities = new AbilityRegistry($policy);
        $contexts = new WordPressExecutionContextFactory($environment);

        $services->set('platform.definitions', new InMemoryDefinitionRepository());
        $services->set('platform.abilities', $abilities);
        $services->set('platform.abilities.contexts', $contexts);
        $services->set('platform.abilities.wordpress', new WordPressAbilityBridge($abilities, $environment, $contexts));
        $services->set('platform.ajax.routes', new AjaxRouteRegistry());

        (new FieldsModule())->register($services);

        self::assertTrue($services->has('module.custom-fields.values.write-consumer'));
        self::assertInstanceOf(
            FieldValueWriteConsumerInterface::class,
            $services->get('module.custom-fields.values.write-consumer'),
        );
    }
}
