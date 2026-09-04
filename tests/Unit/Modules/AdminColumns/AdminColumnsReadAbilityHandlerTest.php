<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Modules\AdminColumns\AdminColumnsReadAbilityHandler;
use WPEssential\Modules\AdminColumns\AdminColumnsReadAdapter;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class AdminColumnsReadAbilityHandlerTest extends TestCase
{
    public function testDelegatesBoundedInputToQueryBackedReadAdapter(): void
    {
        $query = new class implements QueryReadConsumerInterface {
            /** @var array<string,mixed>|null */
            public ?array $lastRequest = null;

            public function describe(string $sourceRef, ExecutionContext $context): array
            {
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
                    'predicates' => ['eq', 'neq'],
                    'sort_modes' => ['field'],
                    'pagination_modes' => ['offset'],
                    'max_page_size' => 100,
                ];
            }

            public function read(array $request, ExecutionContext $context): array
            {
                $this->lastRequest = $request;
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'ok' => true,
                    'source_ref' => 'wordpress.posts',
                    'projection' => $request['projection'],
                    'rows' => [[
                        'post.id' => 7,
                        'post.title' => 'Ability row',
                    ]],
                    'returned' => 1,
                    'error' => null,
                ];
            }
        };

        $views = $this->views();
        $view = $views->save($this->payload(), DefinitionStatus::Published);
        $handler = new AdminColumnsReadAbilityHandler(new AdminColumnsReadAdapter($views, $query));

        $result = $handler->handle([
            'view_id' => $view->id,
            'filters' => [[
                'column_key' => 'title',
                'operator' => 'neq',
                'value' => 'Draft',
            ]],
            'search' => 'Ability',
            'order_by' => [[
                'column_key' => 'title',
                'direction' => 'asc',
            ]],
            'page_size' => 25,
            'offset' => 50,
        ], $this->context());

        self::assertTrue($result['ok']);
        self::assertSame($view->id, $result['view_id']);
        self::assertSame([['id' => 7, 'title' => 'Ability row']], $result['rows']);
        self::assertSame(1, $result['returned']);
        self::assertNotNull($query->lastRequest);
        self::assertArrayNotHasKey('view_id', $query->lastRequest);
        self::assertSame('Ability', $query->lastRequest['search']);
        self::assertSame(25, $query->lastRequest['page_size']);
        self::assertSame(50, $query->lastRequest['offset']);
    }

    public function testRejectsMalformedViewIdBeforeQueryExecution(): void
    {
        $query = $this->passiveQuery();
        $handler = new AdminColumnsReadAbilityHandler(new AdminColumnsReadAdapter($this->views(), $query));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('view_id');
        $handler->handle(['view_id' => 'not-a-uuid'], $this->context());
    }

    public function testDraftViewFailsClosedThroughExistingAdapterBoundary(): void
    {
        $views = $this->views();
        $view = $views->save($this->payload(), DefinitionStatus::Draft);
        $handler = new AdminColumnsReadAbilityHandler(new AdminColumnsReadAdapter($views, $this->passiveQuery()));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('published View');
        $handler->handle(['view_id' => $view->id], $this->context());
    }

    private function passiveQuery(): QueryReadConsumerInterface
    {
        return new class implements QueryReadConsumerInterface {
            public function describe(string $sourceRef, ExecutionContext $context): array
            {
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

    private function views(): AdminColumnsViewDefinitionService
    {
        return new AdminColumnsViewDefinitionService(
            $this->repository(),
            new AdminColumnsViewDefinitionNormalizer(),
            static fn (): string => '01990f6e-1f30-4000-8000-000000000399',
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
            'view_key' => 'ability_posts',
            'name' => 'Ability posts',
            'enabled' => true,
            'target' => ['type' => 'post_type', 'key' => 'post'],
            'columns' => [
                [
                    'uuid' => '01990f6e-1f30-4000-8000-000000000301',
                    'key' => 'id',
                    'label' => 'ID',
                    'source' => ['owner' => 'native', 'reference' => 'post.id'],
                    'format' => 'number',
                    'primary' => true,
                ],
                [
                    'uuid' => '01990f6e-1f30-4000-8000-000000000302',
                    'key' => 'title',
                    'label' => 'Title',
                    'source' => ['owner' => 'native', 'reference' => 'post.title'],
                    'format' => 'text',
                ],
            ],
        ];
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}
