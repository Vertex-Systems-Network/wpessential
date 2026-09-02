<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Relations\RelationDefinitionNormalizer;
use WPEssential\Modules\Relations\RelationDefinitionValidationService;
use WPEssential\Modules\Relations\RelationEndpointSupport;
use WPEssential\Modules\Relations\RelationPortabilityService;
use WPEssential\Modules\Relations\RelationsModule;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressCapabilityChecker;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;

final class RelationsModuleRegistrationTest extends TestCase
{
    public function testManifestRemainsProAndRegistrationUsesSharedPlatformServices(): void
    {
        $module = new RelationsModule();
        $manifest = $module->manifest();

        self::assertSame('relations', $manifest->id);
        self::assertSame('pro', $manifest->edition);

        $environment = $this->environment();
        $contexts = new WordPressExecutionContextFactory($environment);
        $abilities = new AbilityRegistry(new PolicyEngine(new WordPressCapabilityChecker($environment)));
        $bridge = new WordPressAbilityBridge($abilities, $environment, $contexts);
        $routes = new AjaxRouteRegistry();
        $services = new ServiceRegistry();
        $services->set('platform.definitions', new InMemoryDefinitionRepository());
        $services->set('platform.abilities', $abilities);
        $services->set('platform.abilities.wordpress', $bridge);
        $services->set('platform.abilities.contexts', $contexts);
        $services->set('platform.ajax.routes', $routes);

        $module->register($services);

        self::assertInstanceOf(
            RelationDefinitionNormalizer::class,
            $services->get('module.relations.definition-normalizer'),
        );
        self::assertInstanceOf(
            RelationEndpointSupport::class,
            $services->get('module.relations.endpoint-support'),
        );
        self::assertInstanceOf(
            RelationDefinitionValidationService::class,
            $services->get('module.relations.definition-validation'),
        );
        self::assertInstanceOf(
            RelationPortabilityService::class,
            $services->get('module.relations.portability'),
        );

        $expected = [
            'wpessential/relations/list-definitions' => false,
            'wpessential/relations/get-definition' => false,
            'wpessential/relations/validate-definition' => false,
            'wpessential/relations/save-definition' => true,
            'wpessential/relations/status-definition' => true,
            'wpessential/relations/export-definitions' => false,
            'wpessential/relations/import-definitions' => true,
            'wpessential/relations/diagnostics' => false,
        ];
        self::assertCount(count($expected), $abilities->descriptors());
        foreach ($expected as $name => $mutates) {
            $descriptor = $abilities->descriptor($name);
            self::assertNotNull($descriptor);
            self::assertSame(4, $descriptor->ownerSurfaceId);
            self::assertSame('manage_options', $descriptor->capability);
            self::assertSame($mutates, $descriptor->mutates);
        }

        self::assertSame([
            'relations.diagnostics',
            'relations.export.definitions',
            'relations.get.definition',
            'relations.import.definitions',
            'relations.list.definitions',
            'relations.save.definition',
            'relations.status.definition',
            'relations.validate.definition',
        ], $routes->types());
    }

    private function environment(): WordPressAbilityEnvironmentInterface
    {
        return new class implements WordPressAbilityEnvironmentInterface {
            public function abilitiesApiAvailable(): bool
            {
                return true;
            }

            public function doingAction(string $hook): bool
            {
                return false;
            }

            public function currentUserId(): ?int
            {
                return 1;
            }

            public function currentSiteId(): int
            {
                return 1;
            }

            public function currentNetworkId(): ?int
            {
                return 1;
            }

            public function currentUserCan(string $capability): bool
            {
                return true;
            }

            public function isRestRequest(): bool
            {
                return false;
            }

            public function isCli(): bool
            {
                return false;
            }

            public function registerCategory(string $slug, array $args): bool
            {
                return true;
            }

            public function registerAbility(string $name, array $args): bool
            {
                return true;
            }
        };
    }
}
