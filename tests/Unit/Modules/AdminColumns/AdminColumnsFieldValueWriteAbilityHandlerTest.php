<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\FieldValueWriteConsumerInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Modules\AdminColumns\AdminColumnsFieldValueWriteAbilityHandler;
use WPEssential\Modules\AdminColumns\AdminColumnsFieldValueWriteAdapter;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use RuntimeException;

final class AdminColumnsFieldValueWriteAbilityHandlerTest extends TestCase
{
    private const FIELD_REF = 'fields.01990f6e-1f30-4000-8000-000000000200.01990f6e-1f30-4000-8000-000000000202';

    public function testDelegatesValidatedMutationExactlyOnceThroughOwnerAdapter(): void
    {
        $query = new class implements QueryReadConsumerInterface {
            public int $reads = 0;

            public function describe(string $sourceRef, ExecutionContext $context): array
            {
                return [];
            }

            public function read(array $request, ExecutionContext $context): array
            {
                ++$this->reads;
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'ok' => true,
                    'source_ref' => 'wordpress.posts',
                    'projection' => ['post.id', 'post.type'],
                    'rows' => [['post.id' => 21, 'post.type' => 'post']],
                    'returned' => 1,
                    'error' => null,
                ];
            }
        };
        $writer = new class implements FieldValueWriteConsumerInterface {
            public int $writes = 0;
            /** @var array<string,mixed>|null */
            public ?array $captured = null;

            public function writeValue(
                string $fieldReference,
                int $postId,
                int $expectedGroupRevision,
                mixed $value,
                ExecutionContext $context,
            ): array {
                ++$this->writes;
                $this->captured = compact('fieldReference', 'postId', 'expectedGroupRevision', 'value');

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

        $views = $this->views();
        $view = $views->save($this->payload(), DefinitionStatus::Published);
        $handler = new AdminColumnsFieldValueWriteAbilityHandler(
            new AdminColumnsFieldValueWriteAdapter($views, $query, $writer),
        );

        $result = $handler->handle([
            'view_id' => $view->id,
            'column_key' => 'headline',
            'post_id' => 21,
            'expected_group_revision' => 4,
            'value' => 'Updated headline',
        ], $this->context());

        self::assertSame(1, $query->reads);
        self::assertSame(1, $writer->writes);
        self::assertSame([
            'fieldReference' => self::FIELD_REF,
            'postId' => 21,
            'expectedGroupRevision' => 4,
            'value' => 'Updated headline',
        ], $writer->captured);
        self::assertSame('headline', $result['column_key']);
        self::assertSame('fields', $result['source_owner']);
        self::assertSame('written', $result['write']['status']);
        self::assertSame('Updated headline', $result['write']['value']);
    }

    public function testNullValueIsRequiredButDelegatedAsExplicitOwnerValue(): void
    {
        $views = $this->views();
        $view = $views->save($this->payload(), DefinitionStatus::Published);
        $writer = $this->writer();
        $handler = new AdminColumnsFieldValueWriteAbilityHandler(
            new AdminColumnsFieldValueWriteAdapter($views, $this->query(), $writer),
        );

        $result = $handler->handle([
            'view_id' => $view->id,
            'column_key' => 'headline',
            'post_id' => 21,
            'expected_group_revision' => 4,
            'value' => null,
        ], $this->context());

        self::assertNull($result['write']['value']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('value is required');
        $handler->handle([
            'view_id' => $view->id,
            'column_key' => 'headline',
            'post_id' => 21,
            'expected_group_revision' => 4,
        ], $this->context());
    }

    public function testRejectsUnknownOrMalformedInputBeforeOwnerMutation(): void
    {
        $views = $this->views();
        $view = $views->save($this->payload(), DefinitionStatus::Published);
        $handler = new AdminColumnsFieldValueWriteAbilityHandler(
            new AdminColumnsFieldValueWriteAdapter($views, $this->query(), $this->writer()),
        );

        try {
            $handler->handle([
                'view_id' => $view->id,
                'column_key' => 'headline',
                'post_id' => 21,
                'expected_group_revision' => 4,
                'value' => 'x',
                'storage_key' => '_private',
            ], $this->context());
            self::fail('Unknown mutation input must fail closed.');
        } catch (InvalidArgumentException $error) {
            self::assertStringContainsString('unsupported option', $error->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('post_id');
        $handler->handle([
            'view_id' => $view->id,
            'column_key' => 'headline',
            'post_id' => 0,
            'expected_group_revision' => 4,
            'value' => 'x',
        ], $this->context());
    }

    private function query(): QueryReadConsumerInterface
    {
        return new class implements QueryReadConsumerInterface {
            public function describe(string $sourceRef, ExecutionContext $context): array
            {
                return [];
            }

            public function read(array $request, ExecutionContext $context): array
            {
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'ok' => true,
                    'source_ref' => 'wordpress.posts',
                    'projection' => ['post.id', 'post.type'],
                    'rows' => [['post.id' => 21, 'post.type' => 'post']],
                    'returned' => 1,
                    'error' => null,
                ];
            }
        };
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
                    'status' => $value === null ? 'deleted' : 'written',
                    'changed' => true,
                    'value' => $value,
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
            'view_key' => 'fields_write_ability',
            'name' => 'Fields write ability',
            'enabled' => true,
            'target' => ['type' => 'post_type', 'key' => 'post'],
            'columns' => [[
                'uuid' => '01990f6e-1f30-4000-8000-000000000303',
                'key' => 'headline',
                'label' => 'Headline',
                'source' => ['owner' => 'fields', 'reference' => self::FIELD_REF],
                'format' => 'text',
                'primary' => true,
            ]],
        ];
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}
