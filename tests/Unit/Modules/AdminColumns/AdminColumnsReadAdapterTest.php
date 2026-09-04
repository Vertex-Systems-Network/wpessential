<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
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

final class AdminColumnsReadAdapterTest extends TestCase
{
    public function testPublishedPostViewMapsColumnKeysThroughPublicQueryContract(): void
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
                        'post.status' => 'string',
                        'post.type' => 'string',
                    ],
                    'predicates' => ['eq', 'neq', 'in', 'not_in', 'contains'],
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
                        'post.id' => 21,
                        'post.title' => 'Bounded row',
                    ]],
                    'returned' => 1,
                    'error' => null,
                ];
            }
        };

        $views = $this->views();
        $view = $views->save($this->nativePayload(), DefinitionStatus::Published);
        $adapter = new AdminColumnsReadAdapter($views, $query);

        $result = $adapter->read($view->id, [
            'filters' => [[
                'column_key' => 'title',
                'operator' => 'neq',
                'value' => 'Draft',
            ]],
            'search' => 'Bounded',
            'order_by' => [[
                'column_key' => 'title',
                'direction' => 'asc',
            ]],
            'page_size' => 25,
            'offset' => 50,
        ], $this->context());

        self::assertTrue($result['ok']);
        self::assertSame([['id' => 21, 'title' => 'Bounded row']], $result['rows']);
        self::assertSame(1, $result['returned']);
        self::assertSame(['post.id', 'post.title'], $query->lastRequest['projection']);
        self::assertSame([
            ['field_ref' => 'post.type', 'operator' => 'eq', 'value' => 'post'],
            ['field_ref' => 'post.title', 'operator' => 'neq', 'value' => 'Draft'],
        ], $query->lastRequest['filters']);
        self::assertSame([
            ['field_ref' => 'post.title', 'direction' => 'asc'],
        ], $query->lastRequest['order_by']);
        self::assertSame('Bounded', $query->lastRequest['search']);
        self::assertSame(25, $query->lastRequest['page_size']);
        self::assertSame(50, $query->lastRequest['offset']);
        self::assertArrayNotHasKey('ast', $query->lastRequest);
        self::assertArrayNotHasKey('wp_query', $query->lastRequest);
    }

    public function testFieldsOwnedColumnFailsClosedInsteadOfReadingPrivateStorage(): void
    {
        $query = $this->passiveQuery();
        $views = $this->views();
        $payload = $this->nativePayload();
        $payload['columns'][1]['source'] = [
            'owner' => 'fields',
            'reference' => 'fields.01990f6e-1f30-4000-8000-000000000200.01990f6e-1f30-4000-8000-000000000202',
        ];
        $view = $views->save($payload, DefinitionStatus::Published);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not readable through Query V1');
        (new AdminColumnsReadAdapter($views, $query))->read($view->id, [], $this->context());
    }

    public function testDraftOrDisabledViewCannotExecuteRuntimeRead(): void
    {
        $views = $this->views();
        $draft = $views->save($this->nativePayload(), DefinitionStatus::Draft);

        try {
            (new AdminColumnsReadAdapter($views, $this->passiveQuery()))->read($draft->id, [], $this->context());
            self::fail('Expected draft View runtime read to fail closed.');
        } catch (InvalidArgumentException $error) {
            self::assertStringContainsString('published View', $error->getMessage());
        }

        $payload = $this->nativePayload();
        $payload['view_key'] = 'disabled_posts';
        $payload['enabled'] = false;
        $disabled = $views->save($payload, DefinitionStatus::Published);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('enabled View');
        (new AdminColumnsReadAdapter($views, $this->passiveQuery()))->read($disabled->id, [], $this->context());
    }

    public function testQueryFailurePropagatesNormalizedWithoutRenderingRows(): void
    {
        $query = new class implements QueryReadConsumerInterface {
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
                    'ok' => false,
                    'source_ref' => 'wordpress.posts',
                    'projection' => [],
                    'rows' => [],
                    'returned' => 0,
                    'error' => [
                        'code' => 'wpe_query_policy_denied',
                        'path' => '$.source',
                        'message' => 'Query Policy denied the read.',
                    ],
                ];
            }
        };

        $views = $this->views();
        $view = $views->save($this->nativePayload(), DefinitionStatus::Published);
        $result = (new AdminColumnsReadAdapter($views, $query))->read($view->id, [], $this->context());

        self::assertFalse($result['ok']);
        self::assertSame([], $result['rows']);
        self::assertSame(0, $result['returned']);
        self::assertSame('wpe_query_policy_denied', $result['error']['code']);
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
        $uuids = [
            '01990f6e-1f30-4000-8000-000000000398',
            '01990f6e-1f30-4000-8000-000000000399',
            '01990f6e-1f30-4000-8000-000000000397',
        ];
        return new AdminColumnsViewDefinitionService(
            $this->repository(),
            new AdminColumnsViewDefinitionNormalizer(),
            static function () use (&$uuids): string {
                $uuid = array_shift($uuids);
                if (!is_string($uuid)) {
                    throw new \RuntimeException('Test UUIDs exhausted.');
                }
                return $uuid;
            },
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
    private function nativePayload(): array
    {
        return [
            'view_key' => 'posts_runtime',
            'name' => 'Posts runtime',
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
            'visibility' => [
                'mode' => 'hidden',
                'reason' => 'Presentation-only preference must not become authorization.',
            ],
        ];
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}
