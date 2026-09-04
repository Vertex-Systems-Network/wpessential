<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\AdminColumns\AdminColumnsAdminController;
use WPEssential\Modules\AdminColumns\AdminColumnsModule;
use WPEssential\Modules\AdminColumns\AdminColumnsReadAdapter;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionService;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

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

    public function testModuleRegistersFoundationReadAdapterAndReadOnlyAdmin(): void
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
        self::assertFalse($services->has('module.admin-columns.rest'));
        self::assertFalse($services->has('module.admin-columns.ajax'));
        self::assertFalse($services->has('module.admin-columns.export'));
        self::assertFalse($services->has('module.admin-columns.mutation'));
    }

    public function testModuleRequiresSharedDefinitionsAndBoundedQueryConsumer(): void
    {
        $missingDefinitions = new ServiceRegistry();
        $missingDefinitions->set('module.query.read-consumer', $this->queryConsumer());
        try {
            (new AdminColumnsModule())->register($missingDefinitions);
            self::fail('Expected missing Definition repository to fail closed.');
        } catch (\LogicException) {
            self::assertFalse($missingDefinitions->has(AdminColumnsModule::SERVICE_READ_ADAPTER));
        }

        $missingQuery = new ServiceRegistry();
        $missingQuery->set('platform.definitions', $this->repository());
        try {
            (new AdminColumnsModule())->register($missingQuery);
            self::fail('Expected missing Query read consumer to fail closed.');
        } catch (\LogicException) {
            self::assertFalse($missingQuery->has(AdminColumnsModule::SERVICE_NORMALIZER));
            self::assertFalse($missingQuery->has(AdminColumnsModule::SERVICE_VIEWS));
            self::assertFalse($missingQuery->has(AdminColumnsModule::SERVICE_READ_ADAPTER));
        }
    }

    public function testAdminBootFailsClosedWithoutSharedAssetsOrDataSources(): void
    {
        $services = new ServiceRegistry();
        $services->set('platform.definitions', $this->repository());
        $services->set('module.query.read-consumer', $this->queryConsumer());
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
        $services = new ServiceRegistry();
        $services->set('platform.definitions', $this->repository());
        $services->set('module.query.read-consumer', $this->queryConsumer());
        $services->set('platform.data-sources', $this->dataSources());
        $services->set('platform.admin.assets', new AdminAssetManifest('/tmp/wpessential-test', 'https://example.test/wpessential'));
        return $services;
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
