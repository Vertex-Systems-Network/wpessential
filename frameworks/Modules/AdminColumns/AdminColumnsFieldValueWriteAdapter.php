<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\FieldValueWriteConsumerInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class AdminColumnsFieldValueWriteAdapter
{
    public const CONTRACT_VERSION = 1;

    private const QUERY_SOURCE = 'wordpress.posts';
    private const FIELD_REFERENCE_PATTERN = '/^fields\.[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
    private const COLUMN_KEY_PATTERN = '/^[a-z0-9][a-z0-9_-]{0,63}$/';
    private const TARGET_KEY_PATTERN = '/^[a-z][a-z0-9_-]{0,63}$/';

    public function __construct(
        private AdminColumnsViewDefinitionService $views,
        private QueryReadConsumerInterface $query,
        private FieldValueWriteConsumerInterface $fields,
    ) {
    }

    /**
     * @return array{
     *     contract_version:int,
     *     view_id:string,
     *     view_revision:int,
     *     column_key:string,
     *     source_owner:string,
     *     write:array<string,mixed>
     * }
     */
    public function write(
        string $viewId,
        string $columnKey,
        int $postId,
        int $expectedGroupRevision,
        mixed $value,
        ExecutionContext $context,
    ): array {
        if (preg_match(self::COLUMN_KEY_PATTERN, $columnKey) !== 1) {
            throw new InvalidArgumentException('Admin Columns mutation column key is malformed.');
        }
        if ($postId < 1) {
            throw new InvalidArgumentException('Admin Columns mutation post id must be positive.');
        }
        if ($expectedGroupRevision < 1) {
            throw new InvalidArgumentException('Admin Columns mutation expected Field Group revision must be positive.');
        }

        $view = $this->views->get($viewId);
        $targetKey = $this->targetKey($view);
        $fieldReference = $this->fieldReference($view, $columnKey);

        $this->assertTargetRow($postId, $targetKey, $context);

        $write = $this->fields->writeValue(
            $fieldReference,
            $postId,
            $expectedGroupRevision,
            $value,
            $context,
        );
        $this->assertOwnerWriteResult($write, $fieldReference, $postId, $targetKey);

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'view_id' => $view->id,
            'view_revision' => $view->revision,
            'column_key' => $columnKey,
            'source_owner' => 'fields',
            'write' => $write,
        ];
    }

    private function targetKey(Definition $view): string
    {
        if ($view->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Admin Columns runtime mutation requires a published View.');
        }
        if (($view->payload['enabled'] ?? false) !== true) {
            throw new InvalidArgumentException('Admin Columns runtime mutation requires an enabled View.');
        }

        $target = $view->payload['target'] ?? null;
        if (!is_array($target) || ($target['type'] ?? null) !== 'post_type') {
            throw new InvalidArgumentException('Admin Columns Fields mutation V1 supports only post_type Views.');
        }
        $targetKey = $target['key'] ?? null;
        if (!is_string($targetKey) || preg_match(self::TARGET_KEY_PATTERN, $targetKey) !== 1) {
            throw new InvalidArgumentException('Admin Columns mutation post_type target key is malformed.');
        }

        return $targetKey;
    }

    private function fieldReference(Definition $view, string $columnKey): string
    {
        $columns = $view->payload['columns'] ?? null;
        if (!is_array($columns) || !array_is_list($columns)) {
            throw new RuntimeException('Published Admin Columns View contains malformed column metadata.');
        }

        foreach ($columns as $column) {
            if (!is_array($column) || ($column['key'] ?? null) !== $columnKey) {
                continue;
            }
            if (($column['enabled'] ?? true) !== true) {
                throw new InvalidArgumentException('Admin Columns mutation column is disabled.');
            }

            $source = $column['source'] ?? null;
            if (!is_array($source) || ($source['owner'] ?? null) !== 'fields') {
                throw new InvalidArgumentException('Admin Columns mutation column is not owned by Fields.');
            }
            $reference = $source['reference'] ?? null;
            if (!is_string($reference) || preg_match(self::FIELD_REFERENCE_PATTERN, $reference) !== 1) {
                throw new InvalidArgumentException('Admin Columns mutation Fields reference is malformed.');
            }

            return $reference;
        }

        throw new InvalidArgumentException('Admin Columns mutation column is not available in the selected View.');
    }

    private function assertTargetRow(int $postId, string $targetKey, ExecutionContext $context): void
    {
        $result = $this->query->read([
            'contract_version' => QueryReadConsumerInterface::CONTRACT_VERSION,
            'source_ref' => self::QUERY_SOURCE,
            'projection' => ['post.id', 'post.type'],
            'filters' => [
                ['field_ref' => 'post.id', 'operator' => 'eq', 'value' => $postId],
                ['field_ref' => 'post.type', 'operator' => 'eq', 'value' => $targetKey],
            ],
            'order_by' => [],
            'page_size' => 1,
            'offset' => 0,
        ], $context);

        if (($result['ok'] ?? false) !== true) {
            throw new RuntimeException('Admin Columns mutation target verification failed through the Query owner contract.');
        }
        $rows = $result['rows'] ?? null;
        if (!is_array($rows) || !array_is_list($rows) || count($rows) !== 1 || !is_array($rows[0])) {
            throw new InvalidArgumentException('Admin Columns mutation target row is not available in the selected View target.');
        }
        if (($rows[0]['post.id'] ?? null) !== $postId || ($rows[0]['post.type'] ?? null) !== $targetKey) {
            throw new RuntimeException('Admin Columns mutation target verification returned inconsistent row evidence.');
        }
    }

    /** @param array<string,mixed> $write */
    private function assertOwnerWriteResult(array $write, string $fieldReference, int $postId, string $targetKey): void
    {
        if (($write['contract_version'] ?? null) !== FieldValueWriteConsumerInterface::CONTRACT_VERSION
            || ($write['field_ref'] ?? null) !== $fieldReference
            || ($write['post_id'] ?? null) !== $postId
            || ($write['post_type'] ?? null) !== $targetKey
            || !is_int($write['group_revision'] ?? null)
            || !is_string($write['status'] ?? null)
            || !is_bool($write['changed'] ?? null)
            || !array_key_exists('value', $write)
        ) {
            throw new RuntimeException('Fields owner write consumer returned malformed or inconsistent mutation evidence.');
        }
    }
}
