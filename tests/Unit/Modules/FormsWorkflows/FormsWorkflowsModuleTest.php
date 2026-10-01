<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\FormsWorkflows;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\FormsWorkflows\FormWorkflowDefinition;
use WPEssential\Modules\FormsWorkflows\FormsWorkflowsModule;
use WPEssential\Modules\FormsWorkflows\FormsWorkflowsReadAbilityHandler;
use WPEssential\Modules\FormsWorkflows\FormsWorkflowsReadService;
use WPEssential\Modules\FormsWorkflows\FormsWorkflowsSetEnabledAbilityHandler;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

final class FormsWorkflowsModuleTest extends TestCase
{
    public function testModuleRegistersReadAbilitiesAndNonRestSetEnabledMutation(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $services = new ServiceRegistry();
        $abilities = new AbilityRegistry(new PolicyEngine(
            new class implements CapabilityCheckerInterface {
                public function can(ExecutionContext $context, string $capability): bool
                {
                    return true;
                }
            },
        ));

        $environment = new class implements WordPressAbilityEnvironmentInterface {
            /** @var array<string,array<string,mixed>> */
            public array $registered = [];

            public function abilitiesApiAvailable(): bool
            {
                return true;
            }

            public function doingAction(string $hook): bool
            {
                return $hook === 'wp_abilities_api_init';
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
                return null;
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
                $this->registered[$name] = $args;
                return true;
            }
        };

        $bridge = new WordPressAbilityBridge(
            $abilities,
            $environment,
            new WordPressExecutionContextFactory($environment),
        );

        $services->set('platform.definitions', $definitions);
        $services->set('platform.abilities', $abilities);
        $services->set('platform.abilities.wordpress', $bridge);

        (new FormsWorkflowsModule())->register($services);

        self::assertInstanceOf(
            FormsWorkflowsReadService::class,
            $services->get(FormsWorkflowsModule::SERVICE_READ),
        );

        foreach ([
            FormsWorkflowsModule::ABILITY_GET,
            FormsWorkflowsModule::ABILITY_CATALOG,
        ] as $name) {
            $descriptor = $abilities->descriptor($name);
            self::assertNotNull($descriptor);
            self::assertFalse($descriptor->mutates);
            self::assertSame(FormWorkflowDefinition::OWNER_SURFACE_ID, $descriptor->ownerSurfaceId);
            self::assertSame(FormsWorkflowsModule::CAPABILITY, $descriptor->capability);
            self::assertTrue($descriptor->allows(ExecutionChannel::Internal));
            self::assertTrue($descriptor->allows(ExecutionChannel::Ui));
            self::assertTrue($descriptor->allows(ExecutionChannel::Rest));
        }

        $setEnabled = $abilities->descriptor(FormsWorkflowsModule::ABILITY_SET_ENABLED);
        self::assertNotNull($setEnabled);
        self::assertTrue($setEnabled->mutates);
        self::assertSame(FormWorkflowDefinition::OWNER_SURFACE_ID, $setEnabled->ownerSurfaceId);
        self::assertSame(FormsWorkflowsModule::CAPABILITY, $setEnabled->capability);
        self::assertTrue($setEnabled->allows(ExecutionChannel::Internal));
        self::assertTrue($setEnabled->allows(ExecutionChannel::Ui));
        self::assertFalse($setEnabled->allows(ExecutionChannel::Rest));
        self::assertSame(
            FormsWorkflowsSetEnabledAbilityHandler::inputSchema(),
            $setEnabled->inputSchema,
        );
        self::assertSame(
            FormsWorkflowsSetEnabledAbilityHandler::outputSchema(),
            $setEnabled->outputSchema,
        );

        $registered = $bridge->registerAbilities();
        self::assertCount(3, $registered);
        self::assertCount(3, $environment->registered);

        $readExposures = [];
        $mutationExposures = [];
        foreach ($environment->registered as $args) {
            $readonly = $args['meta']['annotations']['readonly'] ?? null;
            if ($readonly === true) {
                $readExposures[] = $args;
            }
            if ($readonly === false) {
                $mutationExposures[] = $args;
            }
        }

        self::assertCount(2, $readExposures);
        foreach ($readExposures as $args) {
            self::assertTrue($args['meta']['show_in_rest']);
        }

        self::assertCount(1, $mutationExposures);
        self::assertFalse($mutationExposures[0]['meta']['show_in_rest']);
        self::assertSame(
            FormsWorkflowsSetEnabledAbilityHandler::inputSchema(),
            $mutationExposures[0]['input_schema'],
        );
    }

    public function testHandlerDelegatesReadOnlyGetAndCatalog(): void
    {
        $service = new FormsWorkflowsReadService(new InMemoryDefinitionRepository());
        $context = new ExecutionContext(new Principal(1), 1);

        self::assertNull(
            (new FormsWorkflowsReadAbilityHandler(
                $service,
                FormsWorkflowsReadAbilityHandler::GET,
            ))->handle(['id' => 'missing'], $context),
        );

        self::assertSame(
            [],
            (new FormsWorkflowsReadAbilityHandler(
                $service,
                FormsWorkflowsReadAbilityHandler::CATALOG,
            ))->handle([], $context),
        );
    }

    public function testHandlerRejectsMutationShapedCatalogInput(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $service = new FormsWorkflowsReadService(new InMemoryDefinitionRepository());

        (new FormsWorkflowsReadAbilityHandler(
            $service,
            FormsWorkflowsReadAbilityHandler::CATALOG,
        ))->handle(
            ['save' => true],
            new ExecutionContext(new Principal(1), 1),
        );
    }
}
