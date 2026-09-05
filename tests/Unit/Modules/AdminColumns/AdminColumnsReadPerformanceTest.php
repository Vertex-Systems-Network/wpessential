<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Modules\AdminColumns\AdminColumnsReadAdapter;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class AdminColumnsReadPerformanceTest extends TestCase
{
    public function testHundredRowReadUsesOneDescribeAndOnePublicQueryRead(): void
    {
        $query = new class implements QueryReadConsumerInterface {
            public int $describeCalls = 0;
            public int $readCalls = 0;

            public function describe(string $sourceRef, ExecutionContext $context): array
            {
                $this->describeCalls++;

                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'source_ref' => 'wordpress.posts',
                    'source_type' => 'wordpress.posts',
                    'capability_version' => 1,
                    'available' => true,
                    'field_schema' => [
                        'post.id' => 'integer',
                        'post.title' => 'string',
                        'post.type' => 'string',
                    ],
                    'predicates' => ['eq'],
                    'sort_modes' => ['field'],
                    'pagination_modes' => ['offset'],
                    'max_page_size' => 100,
                ];
            }

            public function read(array $request, ExecutionContext $context): array
            {
                $this->readCalls++;
                $rows = [];
                for ($index = 1; $index <= 100; $index++) {
                    $rows[] = [
                        'post.id' => $index,
                        'post.title' => 'Performance row ' . $index,
                    ];
                }

                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'ok' => true,
                    'source_ref' => 'wordpress.posts',
                    'projection' => $request['projection'],
                    'rows' => $rows,
                    'returned' => count($rows),
                    'error' => null,
                ];
            }
        };

        $views = new AdminColumnsViewDefinitionService(
            $this->repository(),
            new AdminColumnsViewDefinitionNormalizer(),
            static fn (): string => '01990f6e-1f30-4000-8000-000000000450',
        );
        $view = $views->save($this->payload(), DefinitionStatus::Published);
        $result = (new AdminColumnsReadAdapter($views, $query))->read(
            $view->id,
            ['page_size' => 100, 'offset' => 0],
            new ExecutionContext(new Principal(7), 1),
        );

        self::assertTrue($result['ok']);
        self::assertSame(100, $result['returned']);
        self::assertCount(100, $result['rows']);
        self::assertSame(1, $query->describeCalls, 'Adapter must not rediscover Query metadata per row.');
        self::assertSame(1, $query->readCalls, 'Adapter must not invoke Query once per row or column.');
        self::assertSame(['id' => 1, 'title' => 'Performance row 1'], $result['rows'][0]);
        self::assertSame(['id' => 100, 'title' => 'Performance row 100'], $result['rows'][99]);
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
            'view_key' => 'posts_performance',
            'name' => 'Posts performance',
            'enabled' => true,
            'target' => ['type' => 'post_type', 'key' => 'post'],
            'columns' => [
                [
                    'uuid' => '01990f6e-1f30-4000-8000-000000000451',
                    'key' => 'id',
                    'label' => 'ID',
                    'source' => ['owner' => 'native', 'reference' => 'post.id'],
                    'format' => 'number',
                    'primary' => true,
                ],
                [
                    'uuid' => '01990f6e-1f30-4000-8000-000000000452',
                    'key' => 'title',
                    'label' => 'Title',
                    'source' => ['owner' => 'native', 'reference' => 'post.title'],
                    'format' => 'text',
                    'primary' => false,
                ],
            ],
        ];
    }
}
