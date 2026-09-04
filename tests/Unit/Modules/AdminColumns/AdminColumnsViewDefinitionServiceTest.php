<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\AdminColumns\AdminColumnsAdminController;
use WPEssential\Modules\AdminColumns\AdminColumnsModule;
use WPEssential\Modules\AdminColumns\AdminColumnsReadAdapter;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionService;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;

final class AdminColumnsViewDefinitionServiceTest extends TestCase
{
    public function testCreatesAndRevisionUpdatesOwnedView(): void
    {
        $repository = $this->repository();
        $service = new AdminColumnsViewDefinitionService(
            $repository,
            new AdminColumnsViewDefinitionNormalizer(),
            static fn (): string => '01990f6e-1f30-4000-8000-000000000399',
        );

        $created = $service->save($this->payload(), DefinitionStatus::Draft);
        self::assertSame(1, $created->revision);
        self::assertSame(DefinitionStatus::Draft, $created->status);
        self::assertSame(AdminColumnsViewDefinitionNormalizer::OWNER_SURFACE_ID, $created->ownerSurfaceId);
        self::assertSame($created->computedChecksum(), $created->checksum);

        $payload = $this->payload();
        $payload['name'] = 'Posts overview revised';
        $updated = $service->save($payload, DefinitionStatus::Published, $created->id, 1);
        self::assertSame(2, $updated->revision);
        self::assertSame('Posts overview revised', $updated->payload['name']);
        self::assertSame(DefinitionStatus::Published, $updated->status);
    }

    public function testRejectsStaleRevisionAndImmutableViewKeyMutation(): void
    {
        $repository = $this->repository();
        $service = new AdminColumnsViewDefinitionService(
            $repository,
            new AdminColumnsViewDefinitionNormalizer(),
            static fn (): string => '01990f6e-1f30-4000-8000-000000000399',
        );
        $created = $service->save($this->payload());

        try {
            $service->save($this->payload(), DefinitionStatus::Draft, $created->id, 2);
            self::fail('Expected stale revision to fail closed.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('write conflict', $error->getMessage());
        }

        $payload = $this->payload();
        $payload['view_key'] = 'renamed_view';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('immutable');
        $service->save($payload, DefinitionStatus::Draft, $created->id, 1);
    }

    public function testRejectsDuplicateViewKeyAcrossOwnedDefinitions(): void
    {
        $repository = $this->repository();
        $uuids = [
            '01990f6e-1f30-4000-8000-000000000398',
            '01990f6e-1f30-4000-8000-000000000399',
        ];
        $service = new AdminColumnsViewDefinitionService(
            $repository,
            new AdminColumnsViewDefinitionNormalizer(),
            static function () use (&$uuids): string {
                $uuid = array_shift($uuids);
                if (!is_string($uuid)) {
                    throw new RuntimeException('Test UUIDs exhausted.');
                }
                return $uuid;
            },
        );
        $service->save($this->payload());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already owned');
        $service->save($this->payload());
    }

    public function testModuleRegistersFoundationViewAbilitiesAjaxAndReadOnlyAdmin(): void
    {
        $services = $this->servicesWithRequiredDependencies();
        $module = new AdminColumnsModule();

        self::assertSame(['query'], $module->manifest()->dependencies);
        $module->register($services);
        $module->boot($services);

        self::assertInstanceOf(
            AdminColumnsViewDefinitionNormalizer::class,
            $services->get(AdminColumnsModule::SERVICE_NORMALIZER),
        );
        self::assertInstanceOf(
            AdminColumnsViewDefinitionService::class,
            $services->get(AdminColumnsModule::SERVICE_VIEWS),
        );
        self::assertInstanceOf(
            AdminColumnsReadAdapter::class,
            $services->get(AdminColumnsModule::SERVICE_READ_ADAPTER),
        );
        self::assertInstanceOf(
            AdminColumnsAdminController::class,
            $services->get(AdminColumnsModule::SERVICE_ADMIN),
        );

        $abilities = $services->get('platform.abilities');
        self::assertInstanceOf(AbilityRegistry::class, $abilities);
        foreach ([
            'list-views' => false,
            'get-view' => false,
            'save-view' => true,
            'status-view' => true,
        ] as $action => $mutates) {
            $descriptor = $abilities->descriptor('wpessential/admin-columns/' . $action);
            self::assertNotNull($descriptor);
            self::assertSame(8, $descriptor->ownerSurfaceId);
            self::assertSame('manage_options', $descriptor->capability);
            self::assertSame($mutates, $descriptor->mutates);
            self::assertTrue($descriptor->allows(ExecutionChannel::Internal));
            self::assertTrue($descriptor->allows(ExecutionChannel::Ui));
            self::assertFalse($descriptor->allows(ExecutionChannel::Rest));
        }

        $routes = $services->get('platform.ajax.routes');
        self::assertInstanceOf(AjaxRouteRegistry::class, $routes);
        self::assertSame([
            'admin-columns.get.view',
            'admin-columns.list.views',
            'admin-columns.save.view',
            'admin-columns.status.view',
        ], $routes->types());

        self::assertFalse($services->has('module.admin-columns.rest'));
        self::assertFalse($services->has('module.admin-columns.export'));
        self::assertFalse($services->has('module.admin-columns.row-mutation'));
    }

    public function testModuleRequiresSharedDefinitionQueryAbilityContextAndAjaxServices(): void
    {
        $missingDefinitions = $this->servicesWithRegisterDependencies(includeDefinitions: false);
        try {
            (new AdminColumnsModule())->register($missingDefinitions);
            self::fail('Expected missing Definition repository to fail closed.');
        } catch (\LogicException) {
            self::assertFalse($missingDefinitions->has(AdminColumnsModule::SERVICE_READ_ADAPTER));
        }

        $missingQuery = $this->servicesWithRegisterDependencies(includeQuery: false);
        try {
            (new AdminColumnsModule())->register($missingQuery);
            self::fail('Expected missing Query read consumer to fail closed.');
        } catch (\LogicException) {
            self::assertFalse($missingQuery->has(AdminColumnsModule::SERVICE_NORMALIZER));
        }

        $missingAbilities = $this->servicesWithRegisterDependencies(includeAbilities: false);
        try {
            (new AdminColumnsModule())->register($missingAbilities);
            self::fail('Expected missing Ability registry to fail closed.');
        } catch (\LogicException) {
            self::assertFalse($missingAbilities->has(AdminColumnsModule::SERVICE_NORMALIZER));
        }
    }

    public function testAdminBootFailsClosedWithoutSharedAssetsOrDataSources(): void
    {
        $services = $this->servicesWithRegisterDependencies();
        $module = new AdminColumnsModule();
        $module->register($services);

        try {
            $module->boot($services);
            self::fail('Expected missing admin dependencies to fail closed.');
        } catch (\LogicException) {
            self::assertFalse($services->has(AdminColumnsModule::SERVICE_ADMIN));
        }
    }

    private function servicesWithRequiredDependencies(): ServiceRegistry
    {
        $services = $this->servicesWithRegisterDependencies();
        $services->set('platform.data-sources', $this->dataSources());
        $services->set('platform.admin.assets', new AdminAssetManifest('/tmp/wpessential-test', 'https://example.test/wpessential'));
        return $services;
    }

    private function servicesWithRegisterDependencies(
        bool $includeDefinitions = true,
        bool $includeQuery = true,
        bool $includeAbilities = true,
    ): ServiceRegistry {
        $services = new ServiceRegistry();
        if ($includeDefinitions) {
            $services->set('platform.definitions', $this->repository());
        }
        if ($includeQuery) {
            $services->set('module.query.read-consumer', $this->queryConsumer());
        }
        if ($includeAbilities) {
            $services->set('platform.abilities', $this->abilities());
        }
        $services->set('platform.abilities.contexts', $this->contexts());
        $services->set('platform.ajax.routes', new AjaxRouteRegistry());
        return $services;
    }

    private function abilities(): AbilityRegistry
    {
        return new AbilityRegistry(new PolicyEngine(
            new class implements CapabilityCheckerInterface {
                public function can(ExecutionContext $context, string $capability): bool
                {
                    return true;
                }
            },
        ));
    }

    private function contexts(): WordPressExecutionContextFactory
    {
        return new WordPressExecutionContextFactory(
            new class implements WordPressAbilityEnvironmentInterface {
                public function abilitiesApiAvailable(): bool { return true; }
                public function doingAction(string $hook): bool { return false; }
                public function currentUserId(): ?int { return 7; }
                public function currentSiteId(): int { return 1; }
                public function currentNetworkId(): ?int { return null; }
                public function currentUserCan(string $capability): bool { return true; }
                public function isRestRequest(): bool { return false; }
                public function isCli(): bool { return false; }
                public function registerCategory(string $slug, array $args): bool { return true; }
                public function registerAbility(string $name, array $args): bool { return true; }
            },
        );
    }

    private function dataSources(): DataSourceRegistry
    {
        $registry = new DataSourceRegistry();
        $registry->register(new DataSourceDescriptor(
            id: 'wordpress.posts',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: ['post.title' => 'string', 'post.type' => 'string'],
            predicates: ['eq'],
            sortModes: ['field'],
            paginationModes: ['offset'],
        ));
        return $registry;
    }

    private function queryConsumer(): QueryReadConsumerInterface
    {
        return new class implements QueryReadConsumerInterface {
            public function describe(string $sourceRef, ExecutionContext $context): array
            {
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'source_ref' => $sourceRef,
                    'source_type' => $sourceRef,
                    'capability_version' => 1,
                    'available' => true,
                    'field_schema' => ['post.title' => 'string', 'post.type' => 'string'],
                    'predicates' => ['eq'],
                    'sort_modes' => ['field'],
                    'pagination_modes' => ['offset'],
                    'max_page_size' => 100,
                ];
            }

            public function read(array $request, ExecutionContext $context): array
            {
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'ok' => true,
                    'source_ref' => 'wordpress.posts',
                    'projection' => $request['projection'] ?? [],
                    'rows' => [],
                    'returned' => 0,
                    'error' => null,
                ];
            }
        };
    }

    private function repository(): DefinitionRepositoryInterface
    {
        return new class implements DefinitionRepositoryInterface {
            /** @var array<string,Definition> */
            private array $definitions = [];

            public function save(Definition $definition): void
            {
                $this->definitions[$definition->id] = $definition;
            }

            public function get(string $id): ?Definition
            {
                return $this->definitions[$id] ?? null;
            }

            public function byType(string $type): array
            {
                return array_values(array_filter(
                    $this->definitions,
                    static fn (Definition $definition): bool => $definition->type === $type,
                ));
            }

            public function dependentsOf(string $id): array
            {
                return [];
            }
        };
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'view_key' => 'posts_overview',
            'name' => 'Posts overview',
            'target' => ['type' => 'post_type', 'key' => 'post'],
            'columns' => [
                [
                    'uuid' => '01990f6e-1f30-4000-8000-000000000301',
                    'key' => 'title',
                    'label' => 'Title',
                    'source' => ['owner' => 'native', 'reference' => 'post.title'],
                    'format' => 'text',
                    'primary' => true,
                ],
            ],
        ];
    }
}
