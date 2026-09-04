<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\AdminColumns\AdminColumnsViewAbilityHandler;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;

final class AdminColumnsViewAbilityHandlerTest extends TestCase
{
    public function testCreateListGetUpdateAndStatusUseRevisionedViewService(): void
    {
        $repository = $this->repository();
        $views = new AdminColumnsViewDefinitionService(
            $repository,
            new AdminColumnsViewDefinitionNormalizer(),
            static fn (): string => '01990f6e-1f30-4000-8000-000000000511',
        );
        $context = new ExecutionContext(new Principal(7), 1);

        $save = new AdminColumnsViewAbilityHandler($views, AdminColumnsViewAbilityHandler::SAVE);
        $created = $save->handle([
            'payload' => $this->payload(),
            'status' => 'draft',
        ], $context)['definition'];
        self::assertSame('01990f6e-1f30-4000-8000-000000000511', $created['id']);
        self::assertSame(1, $created['revision']);
        self::assertSame('draft', $created['status']);

        $listed = (new AdminColumnsViewAbilityHandler($views, AdminColumnsViewAbilityHandler::LIST))
            ->handle([], $context);
        self::assertCount(1, $listed['definitions']);
        self::assertSame($created['id'], $listed['definitions'][0]['id']);

        $got = (new AdminColumnsViewAbilityHandler($views, AdminColumnsViewAbilityHandler::GET))
            ->handle(['id' => $created['id']], $context)['definition'];
        self::assertSame($created['checksum'], $got['checksum']);

        $payload = $this->payload();
        $payload['name'] = 'Posts overview revised';
        $updated = $save->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'payload' => $payload,
            'status' => 'draft',
        ], $context)['definition'];
        self::assertSame(2, $updated['revision']);
        self::assertSame('Posts overview revised', $updated['payload']['name']);

        $published = (new AdminColumnsViewAbilityHandler($views, AdminColumnsViewAbilityHandler::STATUS))
            ->handle([
                'id' => $created['id'],
                'expected_revision' => 2,
                'status' => 'published',
            ], $context)['definition'];
        self::assertSame(3, $published['revision']);
        self::assertSame('published', $published['status']);
    }

    public function testUpdateRequiresExpectedRevisionAndPreservesImmutableViewKey(): void
    {
        $repository = $this->repository();
        $views = new AdminColumnsViewDefinitionService(
            $repository,
            new AdminColumnsViewDefinitionNormalizer(),
            static fn (): string => '01990f6e-1f30-4000-8000-000000000512',
        );
        $context = new ExecutionContext(new Principal(7), 1);
        $save = new AdminColumnsViewAbilityHandler($views, AdminColumnsViewAbilityHandler::SAVE);
        $created = $save->handle(['payload' => $this->payload()], $context)['definition'];

        try {
            $save->handle([
                'id' => $created['id'],
                'payload' => $this->payload(),
            ], $context);
            self::fail('Expected optimistic revision requirement.');
        } catch (InvalidArgumentException $error) {
            self::assertStringContainsString('expected_revision', $error->getMessage());
        }

        $payload = $this->payload();
        $payload['view_key'] = 'renamed_view';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('immutable');
        $save->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'payload' => $payload,
        ], $context);
    }

    public function testForeignDefinitionCannotBeReadThroughViewAbility(): void
    {
        $repository = $this->repository();
        $repository->save(new Definition(
            id: '01990f6e-1f30-4000-8000-000000000513',
            slug: 'foreign',
            type: 'foreign_definition',
            schemaVersion: 1,
            ownerSurfaceId: 7,
            payload: ['foreign' => true],
        ));
        $views = new AdminColumnsViewDefinitionService($repository, new AdminColumnsViewDefinitionNormalizer());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not available');
        (new AdminColumnsViewAbilityHandler($views, AdminColumnsViewAbilityHandler::GET))->handle(
            ['id' => '01990f6e-1f30-4000-8000-000000000513'],
            new ExecutionContext(new Principal(7), 1),
        );
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
            'enabled' => true,
            'target' => ['type' => 'post_type', 'key' => 'post'],
            'columns' => [
                [
                    'uuid' => '01990f6e-1f30-4000-8000-000000000501',
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
