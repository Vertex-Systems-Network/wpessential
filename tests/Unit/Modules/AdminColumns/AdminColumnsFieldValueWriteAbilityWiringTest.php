<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\FieldValueWriteConsumerInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\AdminColumns\AdminColumnsFieldValueWriteAbilityHandler;
use WPEssential\Modules\AdminColumns\AdminColumnsModule;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Security\NonceOperation;

final class AdminColumnsFieldValueWriteAbilityWiringTest extends TestCase
{
    public function testRegistersBoundedMutatingAbilityAndUpdateRouteWhenOwnerWriteSeamExists(): void
    {
        [$services, $abilities, $routes] = $this->services();
        $services->set('module.custom-fields.values.write-consumer', $this->writer());

        (new AdminColumnsModule())->register($services);

        $descriptor = $abilities->descriptor(AdminColumnsFieldValueWriteAbilityHandler::ABILITY);
        self::assertNotNull($descriptor);
        self::assertTrue($descriptor->mutates);
        self::assertSame(8, $descriptor->ownerSurfaceId);
        self::assertSame('manage_options', $descriptor->capability);
        self::assertSame(
            ['view_id', 'column_key', 'post_id', 'expected_group_revision', 'value'],
            $descriptor->inputSchema['required'] ?? null,
        );
        self::assertFalse($descriptor->inputSchema['additionalProperties'] ?? true);
        self::assertSame(1, $descriptor->inputSchema['properties']['post_id']['minimum'] ?? null);
        self::assertSame(1, $descriptor->inputSchema['properties']['expected_group_revision']['minimum'] ?? null);
        self::assertSame([], $descriptor->inputSchema['properties']['value'] ?? null);

        $route = $routes->get(AdminColumnsFieldValueWriteAbilityHandler::AJAX_TYPE);
        self::assertNotNull($route);
        self::assertSame(NonceOperation::Update, $route->operation);
        self::assertTrue($route->requiresNonce);
        self::assertFalse($route->allowGuests);
    }

    public function testDoesNotExposeMutationAbilityOrRouteWithoutOptionalOwnerWriteSeam(): void
    {
        [$services, $abilities, $routes] = $this->services();

        (new AdminColumnsModule())->register($services);

        self::assertNull($abilities->descriptor(AdminColumnsFieldValueWriteAbilityHandler::ABILITY));
        self::assertNull($routes->get(AdminColumnsFieldValueWriteAbilityHandler::AJAX_TYPE));
        self::assertFalse($services->has(AdminColumnsModule::SERVICE_FIELD_WRITE_ADAPTER));

        // Existing bounded read behavior remains exposed independently.
        self::assertNotNull($abilities->descriptor('wpessential/admin-columns/read-rows'));
        self::assertNotNull($routes->get('admin-columns.read.rows'));
    }

    /** @return array{ServiceRegistry,AbilityRegistry,AjaxRouteRegistry} */
    private function services(): array
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
        $routes = new AjaxRouteRegistry();

        $services->set('platform.definitions', new InMemoryDefinitionRepository());
        $services->set('module.query.read-consumer', new class implements QueryReadConsumerInterface {
            public function describe(string $sourceRef, ExecutionContext $context): array
            {
                return [];
            }

            public function read(array $request, ExecutionContext $context): array
            {
                return [];
            }
        });
        $services->set('platform.abilities', $abilities);
        $services->set('platform.abilities.contexts', new WordPressExecutionContextFactory($environment));
        $services->set('platform.ajax.routes', $routes);

        return [$services, $abilities, $routes];
    }

    private function writer(): FieldValueWriteConsumerInterface
    {
        return new class implements FieldValueWriteConsumerInterface {
            public function writeValue(
                string $fieldReference,
                int $postId,
                int $expectedGroupRevision,
                mixed $value,
                ExecutionContext $context,
            ): array {
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'field_ref' => $fieldReference,
                    'group_revision' => $expectedGroupRevision,
                    'field_uuid' => '01990f6e-1f30-4000-8000-000000000202',
                    'logical_type' => 'string',
                    'storage_owner' => 'native_post_meta',
                    'post_id' => $postId,
                    'post_type' => 'post',
                    'status' => 'written',
                    'changed' => true,
                    'value' => $value,
                ];
            }
        };
    }
}
