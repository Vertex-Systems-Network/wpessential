<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\FieldQueryConsumerInterface;
use WPEssential\Contracts\FieldValueWriteConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class FieldValueWriteConsumer implements FieldValueWriteConsumerInterface
{
    private const REFERENCE_PATTERN = '/^fields\.([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})\.([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})$/';

    public function __construct(
        private FieldQueryConsumerInterface $fields,
        private FieldValueTargetResolver $targets,
        private PostMetaValueStore $values,
        private WordPressPostResourceAuthorizer $authorization,
    ) {}

    public function writeValue(
        string $fieldReference,
        int $postId,
        int $expectedGroupRevision,
        mixed $value,
        ExecutionContext $context,
    ): array {
        [$groupId, $fieldUuid] = $this->referenceIdentity($fieldReference);
        if ($postId < 1) {
            throw new InvalidArgumentException('Field value write post id must be a positive integer.');
        }
        if ($expectedGroupRevision < 1) {
            throw new InvalidArgumentException('Field value write expected group revision must be a positive integer.');
        }

        // Authorization intentionally precedes target resolution/owner lookup so
        // an unauthorized caller cannot probe per-resource Field applicability.
        $this->authorization->assertCanWrite($context, $postId);

        $description = $this->fields->describe($fieldReference, $context);
        $this->assertDescription($description, $fieldReference, $fieldUuid);
        if ($expectedGroupRevision !== $description['group_revision']) {
            throw new RuntimeException(sprintf(
                'Field value schema revision conflict: expected %d, current revision is %d.',
                $expectedGroupRevision,
                $description['group_revision'],
            ));
        }

        $target = $this->targets->resolve($groupId, $fieldUuid, $postId);
        if ($target->groupRevision !== $description['group_revision']) {
            throw new RuntimeException('Field value write consumer detected a Field Group revision change during the owner mutation.');
        }

        $result = $this->values->write($target->field, $target->postType, $target->postId, $value);

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'field_ref' => $fieldReference,
            'group_revision' => $target->groupRevision,
            'field_uuid' => $target->fieldUuid,
            'logical_type' => $description['logical_type'],
            'storage_owner' => $description['storage_owner'],
            'post_id' => $target->postId,
            'post_type' => $target->postType,
            'status' => $result->status,
            'changed' => $result->changed(),
            'value' => $result->value,
        ];
    }

    /** @return array{0:string,1:string} */
    private function referenceIdentity(string $fieldReference): array
    {
        if (preg_match(self::REFERENCE_PATTERN, $fieldReference, $matches) !== 1) {
            throw new InvalidArgumentException('Field value write reference must use fields.<group-uuid>.<field-uuid>.');
        }

        return [$matches[1], $matches[2]];
    }

    /** @param array<string,mixed> $description */
    private function assertDescription(array $description, string $fieldReference, string $fieldUuid): void
    {
        if (($description['contract_version'] ?? null) !== FieldQueryConsumerInterface::CONTRACT_VERSION
            || ($description['field_ref'] ?? null) !== $fieldReference
            || ($description['field_uuid'] ?? null) !== $fieldUuid
            || !is_int($description['group_revision'] ?? null)
            || $description['group_revision'] < 1
            || !is_string($description['logical_type'] ?? null)
            || !in_array($description['logical_type'], ['string', 'boolean', 'integer', 'number'], true)
            || ($description['storage_owner'] ?? null) !== FieldGroupRuntimeStorageProjection::NATIVE_POST_META
        ) {
            throw new RuntimeException('Field value write consumer received malformed or unsupported owner metadata.');
        }
    }
}
