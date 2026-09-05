<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use LogicException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\FieldValueWriteConsumerInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\AdminColumns\AdminColumnsFieldValueWriteAdapter;
use WPEssential\Modules\AdminColumns\AdminColumnsModule;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;

final class AdminColumnsFieldValueWriteAdapterWiringTest extends TestCase
{
    public function testRegistersAdapterWhenCertifiedFieldsWriteServiceIsAvailable(): void
    {
        $services = $this->services();
        $services->set('module.custom-fields.values.write-consumer', $this->writer());

        (new AdminColumnsModule())->register($services);

        self::assertTrue($services->has(AdminColumnsModule::SERVICE_FIELD_WRITE_ADAPTER));
        self::assertInstanceOf(
            AdminColumnsFieldValueWriteAdapter::class,
            $services->get(AdminColumnsModule::SERVICE_FIELD_WRITE_ADAPTER),
        );
    }

    public function testLeavesAdapterUnregisteredWhenOptionalFieldsWriteServiceIsAbsent(): void
    {
        $services = $this->services();

        (new AdminColumnsModule())->register($services);

        self::assertFalse($services->has(AdminColumnsModule::SERVICE_FIELD_WRITE_ADAPTER));
    }

    public function testFailsClosedWhenOptionalFieldsWriteServiceHasWrongType(): void
    {
        $services = $this->services();
        $services->set('module.custom-fields.values.write-consumer', new \stdClass());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Fields value write service must implement the certified contract');
        (new AdminColumnsModule())->register($services);
    }

    private function services(): ServiceRegistry
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

        $services->set('platform.definitions', new InMemoryDefinitionRepository());
        $services->set('module.query.read-consumer', new class implements QueryReadConsumerInterface {
            public function describe(string $sourceRef, ExecutionContext $context): array { return []; }
            public function read(array $request, ExecutionContext $context): array { return []; }
        });
        $services->set('platform.abilities', new AbilityRegistry($policy));
        $services->set('platform.abilities.contexts', new WordPressExecutionContextFactory($environment));
        $services->set('platform.ajax.routes', new AjaxRouteRegistry());

        return $services;
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
