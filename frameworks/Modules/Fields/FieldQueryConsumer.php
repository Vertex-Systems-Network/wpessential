<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\FieldQueryConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class FieldQueryConsumer implements FieldQueryConsumerInterface
{
    private const REFERENCE_PATTERN = '/^fields\.([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})\.([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})$/';
    private const OPERATORS = ['eq', 'neq', 'in', 'not_in'];

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private FieldGroupDefinitionNormalizer $groups,
        private FieldGroupRuntimeStorageProjection $storage,
        private FieldValueTargetResolver $targets,
        private PostMetaRegistrationCompiler $compiler,
        private PostMetaValueStore $values,
        private FieldValueNormalizer $normalizer,
        private WordPressPostResourceAuthorizer $authorization,
    ) {}

    public function describe(string $fieldReference, ExecutionContext $context): array
    {
        $this->assertAuthenticatedContext($context);
        $resolved = $this->resolveField($fieldReference);

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'field_ref' => $fieldReference,
            'group_revision' => $resolved['definition']->revision,
            'field_uuid' => $resolved['field_uuid'],
            'logical_type' => $resolved['logical_type'],
            'operators' => self::OPERATORS,
            'max_candidate_ids' => self::MAX_CANDIDATE_IDS,
            'max_result_ids' => self::MAX_RESULT_IDS,
            'storage_owner' => FieldGroupRuntimeStorageProjection::NATIVE_POST_META,
        ];
    }

    public function matchingPostIds(
        string $fieldReference,
        string $operator,
        mixed $value,
        array $candidatePostIds,
        int $limit,
        ExecutionContext $context,
    ): array {
        $this->assertAuthenticatedContext($context);
        if (!in_array($operator, self::OPERATORS, true)) {
            throw new InvalidArgumentException('Field Query operator is outside the bounded V1 contract.');
        }
        $this->assertCandidateIds($candidatePostIds);
        if ($limit < 1 || $limit > self::MAX_RESULT_IDS) {
            throw new InvalidArgumentException('Field Query result limit is outside the bounded V1 contract.');
        }

        $resolved = $this->resolveField($fieldReference);
        $operand = $this->normalizeOperand($resolved['field'], $operator, $value);
        $matches = [];

        foreach ($candidatePostIds as $postId) {
            $this->authorization->assertCanRead($context, $postId);
            try {
                $target = $this->targets->resolve(
                    $resolved['definition']->id,
                    $resolved['field_uuid'],
                    $postId,
                );
            } catch (FieldValueTargetMismatchException) {
                continue;
            }
            $this->assertTargetStorage($target, $resolved['logical_type']);
            $current = $this->values->read($target->field, $target->postType, $postId);

            if ($this->matches($current, $operator, $operand)) {
                $matches[] = $postId;
                if (count($matches) >= $limit) {
                    break;
                }
            }
        }

        return $matches;
    }

    /**
     * @return array{definition:Definition,field_uuid:string,field:array<string,mixed>,logical_type:string}
     */
    private function resolveField(string $fieldReference): array
    {
        if (preg_match(self::REFERENCE_PATTERN, $fieldReference, $matches) !== 1) {
            throw new InvalidArgumentException('Field Query reference must use fields.<group-uuid>.<field-uuid>.');
        }
        $groupId = $matches[1];
        $fieldUuid = $matches[2];
        $definition = $this->definitions->get($groupId);
        if (!$definition instanceof Definition
            || $definition->type !== FieldGroupDefinitionNormalizer::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID
            || $definition->status !== DefinitionStatus::Published
        ) {
            throw new RuntimeException('Published Field Query definition is not available.');
        }

        try {
            $payload = $this->groups->normalize($definition->payload, true);
            $this->storage->projectGroup($payload);
        } catch (InvalidArgumentException $error) {
            throw new RuntimeException('Field Query definition is outside the certified native V1 storage contract.', 0, $error);
        }

        $fields = $payload['fields'] ?? null;
        if (!is_array($fields) || !array_is_list($fields)) {
            throw new RuntimeException('Published Field Query definition has malformed fields.');
        }
        foreach ($fields as $field) {
            if (!is_array($field) || ($field['uuid'] ?? null) !== $fieldUuid) {
                continue;
            }
            $logicalType = $this->assertScalarStorage($field);
            return [
                'definition' => $definition,
                'field_uuid' => $fieldUuid,
                'field' => $field,
                'logical_type' => $logicalType,
            ];
        }

        throw new RuntimeException('Field Query reference does not resolve to a published top-level Field.');
    }

    /** @param array<string,mixed> $field */
    private function assertScalarStorage(array $field): string
    {
        try {
            $registration = $this->compiler->compile($field, 'post');
        } catch (InvalidArgumentException $error) {
            throw new RuntimeException('Field is not certified for native scalar Query consumption.', 0, $error);
        }
        $args = $registration['args'] ?? null;
        $logicalType = is_array($args) ? ($args['type'] ?? null) : null;
        $single = is_array($args) ? ($args['single'] ?? null) : null;
        if ($single !== true || !is_string($logicalType) || !in_array($logicalType, ['string', 'boolean', 'integer', 'number'], true)) {
            throw new RuntimeException('Field Query V1 requires a single scalar native post-meta value.');
        }
        return $logicalType;
    }

    private function assertTargetStorage(ResolvedFieldValueTarget $target, string $expectedType): void
    {
        try {
            $registration = $this->compiler->compile($target->field, $target->postType);
        } catch (InvalidArgumentException $error) {
            throw new RuntimeException('Resolved Field target is outside the native scalar Query contract.', 0, $error);
        }
        $args = $registration['args'] ?? null;
        if (!is_array($args) || ($args['single'] ?? null) !== true || ($args['type'] ?? null) !== $expectedType) {
            throw new RuntimeException('Resolved Field target storage no longer matches the certified Query descriptor.');
        }
    }

    /** @param array<string,mixed> $field */
    private function normalizeOperand(array $field, string $operator, mixed $value): mixed
    {
        if (in_array($operator, ['in', 'not_in'], true)) {
            if (!is_array($value) || !array_is_list($value) || $value === [] || count($value) > self::MAX_CANDIDATE_IDS) {
                throw new InvalidArgumentException('Field Query set operand must be a non-empty bounded list.');
            }
            $normalized = [];
            foreach ($value as $item) {
                $canonical = $this->normalizer->normalize($field, $item);
                if ($canonical === null || is_array($canonical) || is_object($canonical)) {
                    throw new InvalidArgumentException('Field Query set operand requires canonical scalar values.');
                }
                $normalized[] = $canonical;
            }
            return $normalized;
        }

        $canonical = $this->normalizer->normalize($field, $value);
        if ($canonical === null || is_array($canonical) || is_object($canonical)) {
            throw new InvalidArgumentException('Field Query comparison operand requires a canonical scalar value.');
        }
        return $canonical;
    }

    private function matches(mixed $current, string $operator, mixed $operand): bool
    {
        return match ($operator) {
            'eq' => $current === $operand,
            'neq' => $current !== $operand,
            'in' => is_array($operand) && in_array($current, $operand, true),
            'not_in' => is_array($operand) && !in_array($current, $operand, true),
            default => false,
        };
    }

    /** @param list<int> $candidatePostIds */
    private function assertCandidateIds(array $candidatePostIds): void
    {
        if (!array_is_list($candidatePostIds)
            || $candidatePostIds === []
            || count($candidatePostIds) > self::MAX_CANDIDATE_IDS
        ) {
            throw new InvalidArgumentException('Field Query candidate ids must be a non-empty bounded list.');
        }
        $seen = [];
        foreach ($candidatePostIds as $postId) {
            if (!is_int($postId) || $postId < 1 || isset($seen[$postId])) {
                throw new InvalidArgumentException('Field Query candidate ids must be unique positive integers.');
            }
            $seen[$postId] = true;
        }
    }

    private function assertAuthenticatedContext(ExecutionContext $context): void
    {
        if (!$context->principal->isAuthenticated() || $context->principal->actorType !== 'user') {
            throw new RuntimeException('Field Query consumer requires an authenticated user execution context.');
        }
    }
}
