<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\FieldValueWriteConsumerInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Modules\AdminColumns\AdminColumnsFieldValueWriteAdapter;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class AdminColumnsFieldValueWriteAdapterTest extends TestCase
{
    private const FIELD_REF = 'fields.01990f6e-1f30-4000-8000-000000000200.01990f6e-1f30-4000-8000-000000000202';

    public function testDelegatesExactlyOnceAfterExactQueryTargetProof(): void
    {
        $query = new class implements QueryReadConsumerInterface {
            /** @var array<string,mixed>|null */
            public ?array $lastRequest = null;

            public function describe(string $sourceRef, ExecutionContext $context): array
            {
                return [];
            }

            public function read(array $request, ExecutionContext $context): array
            {
                $this->lastRequest = $request;
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'ok' => true,
                    'source_ref' => 'wordpress.posts',
                    'projection' => ['post.id', 'post.type'],
                    'rows' => [['post.id' => 41, 'post.type' => 'post']],
                    'returned' => 1,
                    'error' => null,
                ];
            }
        };
        $fields = new class implements FieldValueWriteConsumerInterface {
            public int $calls = 0;
            public ?string $fieldReference = null;
            public ?int $postId = null;
            public ?int $revision = null;
            public mixed $value = null;
            public ?ExecutionContext $context = null;

            public function writeValue(
                string $fieldReference,
                int $postId,
                int $expectedGroupRevision,
                mixed $value,
                ExecutionContext $context,
            ): array {
                ++$this->calls;
                $this->fieldReference = $fieldReference;
                $this->postId = $postId;
                $this->revision = $expectedGroupRevision;
                $this->value = $value;
                $this->context = $context;
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
                    'value' => 'Hello',
                ];
            }
        };
        $views = $this->views();
        $view = $views->save($this->payload(), DefinitionStatus::Published);
        $context = $this->context();

        $result = (new AdminColumnsFieldValueWriteAdapter($views, $query, $fields))->write(
            $view->id,
            'headline',
            41,
            4,
            'Hello',
            $context,
        );

        self::assertSame(1, $fields->calls);
        self::assertSame(self::FIELD_REF, $fields->fieldReference);
        self::assertSame(41, $fields->postId);
        self::assertSame(4, $fields->revision);
        self::assertSame('Hello', $fields->value);
        self::assertSame($context, $fields->context);
        self::assertSame([
            'contract_version' => QueryReadConsumerInterface::CONTRACT_VERSION,
            'source_ref' => 'wordpress.posts',
            'projection' => ['post.id', 'post.type'],
            'filters' => [
                ['field_ref' => 'post.id', 'operator' => 'eq', 'value' => 41],
                ['field_ref' => 'post.type', 'operator' => 'eq', 'value' => 'post'],
            ],
            'order_by' => [],
            'page_size' => 1,
            'offset' => 0,
        ], $query->lastRequest);
        self::assertSame(1, $result['contract_version']);
        self::assertSame($view->id, $result['view_id']);
        self::assertSame($view->revision, $result['view_revision']);
        self::assertSame('headline', $result['column_key']);
        self::assertSame('fields', $result['source_owner']);
        self::assertSame('written', $result['write']['status']);
        self::assertTrue($result['write']['changed']);
    }

    public function testNullAndUnchangedOwnerOutcomePassesThroughWithoutReinterpretation(): void
    {
        $views = $this->views();
        $view = $views->save($this->payload(), DefinitionStatus::Published);
        $fields = new class implements FieldValueWriteConsumerInterface {
            public mixed $received = 'sentinel';

            public function writeValue(
                string $fieldReference,
                int $postId,
                int $expectedGroupRevision,
                mixed $value,
                ExecutionContext $context,
            ): array {
                $this->received = $value;
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'field_ref' => $fieldReference,
                    'group_revision' => $expectedGroupRevision,
                    'field_uuid' => '01990f6e-1f30-4000-8000-000000000202',
                    'logical_type' => 'string',
                    'storage_owner' => 'native_post_meta',
                    'post_id' => $postId,
                    'post_type' => 'post',
                    'status' => 'unchanged',
                    'changed' => false,
                    'value' => null,
                ];
            }
        };

        $result = (new AdminColumnsFieldValueWriteAdapter($views, $this->matchingQuery(), $fields))->write(
            $view->id,
            'headline',
            41,
            4,
            null,
            $this->context(),
        );

        self::assertNull($fields->received);
        self::assertSame('unchanged', $result['write']['status']);
        self::assertFalse($result['write']['changed']);
        self::assertNull($result['write']['value']);
    }

    public function testDraftDisabledAndNonPostViewsFailBeforeOwnerWrite(): void
    {
        $cases = [
            [$this->payload(), DefinitionStatus::Draft, 'published View'],
            [$this->payload(enabled: false), DefinitionStatus::Published, 'enabled View'],
            [$this->payload(targetType: 'taxonomy', targetKey: 'category'), DefinitionStatus::Published, 'post_type Views'],
        ];

        foreach ($cases as [$payload, $status, $message]) {
            $views = $this->views();
            $view = $views->save($payload, $status);
            $fields = $this->countingWriter();

            try {
                (new AdminColumnsFieldValueWriteAdapter($views, $this->matchingQuery(), $fields))->write(
                    $view->id,
                    'headline',
                    41,
                    4,
                    'Nope',
                    $this->context(),
                );
                self::fail('Unsupported runtime mutation View unexpectedly reached the owner writer.');
            } catch (InvalidArgumentException $error) {
                self::assertStringContainsString($message, $error->getMessage());
                self::assertSame(0, $fields->calls);
            }
        }
    }

    public function testUnknownDisabledAndNonFieldsColumnsFailBeforeOwnerWrite(): void
    {
        $cases = [
            [$this->payload(), 'missing', 'not available'],
            [$this->payload(columnEnabled: false), 'headline', 'disabled'],
            [$this->payload(columnOwner: 'native', reference: 'post.title'), 'headline', 'not owned by Fields'],
        ];

        foreach ($cases as [$payload, $columnKey, $message]) {
            $views = $this->views();
            $view = $views->save($payload, DefinitionStatus::Published);
            $fields = $this->countingWriter();

            try {
                (new AdminColumnsFieldValueWriteAdapter($views, $this->matchingQuery(), $fields))->write(
                    $view->id,
                    $columnKey,
                    41,
                    4,
                    'Nope',
                    $this->context(),
                );
                self::fail('Unsupported mutation column unexpectedly reached the owner writer.');
            } catch (InvalidArgumentException $error) {
                self::assertStringContainsString($message, $error->getMessage());
                self::assertSame(0, $fields->calls);
            }
        }
    }

    public function testInvalidPostOrRevisionFailsBeforeQueryAndOwnerWrite(): void
    {
        $query = new class implements QueryReadConsumerInterface {
            public int $calls = 0;
            public function describe(string $sourceRef, ExecutionContext $context): array { return []; }
            public function read(array $request, ExecutionContext $context): array { ++$this->calls; return []; }
        };
        $fields = $this->countingWriter();
        $views = $this->views();
        $view = $views->save($this->payload(), DefinitionStatus::Published);
        $adapter = new AdminColumnsFieldValueWriteAdapter($views, $query, $fields);

        foreach ([[0, 4], [41, 0]] as [$postId, $revision]) {
            try {
                $adapter->write($view->id, 'headline', $postId, $revision, 'Nope', $this->context());
                self::fail('Invalid mutation identity unexpectedly passed validation.');
            } catch (InvalidArgumentException) {
                self::assertSame(0, $query->calls);
                self::assertSame(0, $fields->calls);
            }
        }
    }

    public function testTargetMismatchBlocksOwnerWrite(): void
    {
        $query = new class implements QueryReadConsumerInterface {
            public function describe(string $sourceRef, ExecutionContext $context): array { return []; }
            public function read(array $request, ExecutionContext $context): array
            {
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'ok' => true,
                    'source_ref' => 'wordpress.posts',
                    'projection' => ['post.id', 'post.type'],
                    'rows' => [],
                    'returned' => 0,
                    'error' => null,
                ];
            }
        };
        $fields = $this->countingWriter();
        $views = $this->views();
        $view = $views->save($this->payload(), DefinitionStatus::Published);

        $this->expectException(InvalidArgumentException::class);
        try {
            (new AdminColumnsFieldValueWriteAdapter($views, $query, $fields))->write(
                $view->id,
                'headline',
                41,
                4,
                'Nope',
                $this->context(),
            );
        } finally {
            self::assertSame(0, $fields->calls);
        }
    }

    public function testOwnerFailurePropagatesWithoutSuccessTranslation(): void
    {
        $views = $this->views();
        $view = $views->save($this->payload(), DefinitionStatus::Published);
        $fields = new class implements FieldValueWriteConsumerInterface {
            public function writeValue(
                string $fieldReference,
                int $postId,
                int $expectedGroupRevision,
                mixed $value,
                ExecutionContext $context,
            ): array {
                throw new RuntimeException('owner revision conflict');
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('owner revision conflict');
        (new AdminColumnsFieldValueWriteAdapter($views, $this->matchingQuery(), $fields))->write(
            $view->id,
            'headline',
            41,
            4,
            'Nope',
            $this->context(),
        );
    }

    public function testMalformedOwnerEvidenceFailsClosedAfterOwnerCall(): void
    {
        $views = $this->views();
        $view = $views->save($this->payload(), DefinitionStatus::Published);
        $fields = new class implements FieldValueWriteConsumerInterface {
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
                    'post_id' => $postId,
                    'post_type' => 'wrong-type',
                    'status' => 'written',
                    'changed' => true,
                    'value' => $value,
                ];
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('malformed or inconsistent mutation evidence');
        (new AdminColumnsFieldValueWriteAdapter($views, $this->matchingQuery(), $fields))->write(
            $view->id,
            'headline',
            41,
            4,
            'Nope',
            $this->context(),
        );
    }

    private function matchingQuery(): QueryReadConsumerInterface
    {
        return new class implements QueryReadConsumerInterface {
            public function describe(string $sourceRef, ExecutionContext $context): array { return []; }
            public function read(array $request, ExecutionContext $context): array
            {
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'ok' => true,
                    'source_ref' => 'wordpress.posts',
                    'projection' => ['post.id', 'post.type'],
                    'rows' => [['post.id' => 41, 'post.type' => 'post']],
                    'returned' => 1,
                    'error' => null,
                ];
            }
        };
    }

    private function countingWriter(): FieldValueWriteConsumerInterface
    {
        return new class implements FieldValueWriteConsumerInterface {
            public int $calls = 0;

            public function writeValue(
                string $fieldReference,
                int $postId,
                int $expectedGroupRevision,
                mixed $value,
                ExecutionContext $context,
            ): array {
                ++$this->calls;
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

    private function views(): AdminColumnsViewDefinitionService
    {
        return new AdminColumnsViewDefinitionService(
            new InMemoryDefinitionRepository(),
            new AdminColumnsViewDefinitionNormalizer(),
            static fn (): string => '01990f6e-1f30-4000-8000-000000000399',
        );
    }

    /** @return array<string,mixed> */
    private function payload(
        bool $enabled = true,
        string $targetType = 'post_type',
        string $targetKey = 'post',
        string $columnOwner = 'fields',
        string $reference = self::FIELD_REF,
        bool $columnEnabled = true,
    ): array {
        return [
            'view_key' => 'posts_fields_write',
            'name' => 'Posts Fields write',
            'enabled' => $enabled,
            'target' => ['type' => $targetType, 'key' => $targetKey],
            'columns' => [[
                'uuid' => '01990f6e-1f30-4000-8000-000000000303',
                'key' => 'headline',
                'label' => 'Headline',
                'enabled' => $columnEnabled,
                'source' => ['owner' => $columnOwner, 'reference' => $reference],
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
