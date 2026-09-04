<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\AdminColumns\AdminColumnsModule;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionService;
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

    public function testModuleRegistersOnlyFoundationServicesAndRequiresSharedDefinitions(): void
    {
        $services = new ServiceRegistry();
        $services->set('platform.definitions', $this->repository());
        $module = new AdminColumnsModule();
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
        self::assertFalse($services->has('module.admin-columns.admin'));
        self::assertFalse($services->has('module.admin-columns.query-adapter'));

        $missing = new ServiceRegistry();
        $this->expectException(\LogicException::class);
        (new AdminColumnsModule())->register($missing);
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
