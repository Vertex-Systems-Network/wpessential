<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;

final readonly class FieldGroupValidationService
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private FieldGroupDefinitionNormalizer $normalizer,
    ) {}

    /**
     * @param array<string,mixed> $input
     * @return array{valid:bool,issues:list<array{id:string,severity:string,field:string,message:string}>,candidate:array{group_key:?string,field_count:int}}
     */
    public function validate(array $input): array
    {
        $issues = [];
        $payload = $input['payload'] ?? null;
        if (!is_array($payload) || array_is_list($payload)) {
            $issues[] = $this->issue('payload_invalid', 'blocked', 'payload', 'Field Group payload must be an object/map.');
            return $this->report(null, 0, $issues);
        }

        $current = $this->currentDefinition($input, $issues);
        $publishing = ($input['status'] ?? null) === 'published';
        $normalized = null;
        try {
            $normalized = $this->normalizer->normalize($payload, $publishing);
        } catch (InvalidArgumentException $exception) {
            $issues[] = $this->issue('schema_invalid', 'blocked', 'payload', $exception->getMessage());
        }

        $groupKey = is_array($normalized) && is_string($normalized['group_key'] ?? null)
            ? $normalized['group_key']
            : (is_string($payload['group_key'] ?? null) ? trim($payload['group_key']) : null);
        $fieldCount = is_array($normalized) && is_array($normalized['fields'] ?? null)
            ? count($normalized['fields'])
            : 0;

        if ($current instanceof Definition && $groupKey !== null) {
            $existingKey = $current->payload['group_key'] ?? null;
            if (is_string($existingKey) && $existingKey !== $groupKey) {
                $issues[] = $this->issue(
                    'group_key_immutable',
                    'blocked',
                    'group_key',
                    'Existing Field Group group_key is immutable; use a migration workflow for a new externally referenced key.',
                );
            }
        }
        if ($groupKey !== null && $groupKey !== '') {
            $this->validateUniqueKey($groupKey, $current?->id, $issues);
        }

        return $this->report($groupKey, $fieldCount, $issues);
    }

    /**
     * @param array<string,mixed> $input
     * @param list<array{id:string,severity:string,field:string,message:string}> $issues
     */
    private function currentDefinition(array $input, array &$issues): ?Definition
    {
        $id = $input['id'] ?? null;
        if ($id === null || $id === '') {
            return null;
        }
        if (!is_string($id)) {
            $issues[] = $this->issue('definition_id_invalid', 'blocked', 'id', 'Field Group id must be a string.');
            return null;
        }
        $definition = $this->definitions->get($id);
        if (!$definition instanceof Definition
            || $definition->type !== FieldGroupDefinitionNormalizer::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID
        ) {
            $issues[] = $this->issue('definition_not_found', 'blocked', 'id', 'Field Group definition was not found in canonical Surface 3.');
            return null;
        }
        return $definition;
    }

    /** @param list<array{id:string,severity:string,field:string,message:string}> $issues */
    private function validateUniqueKey(string $key, ?string $currentId, array &$issues): void
    {
        foreach ($this->definitions->byType(FieldGroupDefinitionNormalizer::DEFINITION_TYPE) as $definition) {
            if ($definition->ownerSurfaceId !== FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID || $definition->id === $currentId) {
                continue;
            }
            $existing = $definition->payload['group_key'] ?? null;
            if (is_string($existing) && $existing === $key) {
                $issues[] = $this->issue(
                    'duplicate_group_key',
                    'blocked',
                    'group_key',
                    sprintf('Field Group key "%s" is already owned by another Surface 3 definition.', $key),
                );
                return;
            }
        }
    }

    /**
     * @param list<array{id:string,severity:string,field:string,message:string}> $issues
     * @return array{valid:bool,issues:list<array{id:string,severity:string,field:string,message:string}>,candidate:array{group_key:?string,field_count:int}}
     */
    private function report(?string $key, int $fieldCount, array $issues): array
    {
        $valid = true;
        foreach ($issues as $issue) {
            if ($issue['severity'] === 'blocked') {
                $valid = false;
                break;
            }
        }
        return [
            'valid' => $valid,
            'issues' => $issues,
            'candidate' => ['group_key' => $key, 'field_count' => $fieldCount],
        ];
    }

    /** @return array{id:string,severity:string,field:string,message:string} */
    private function issue(string $id, string $severity, string $field, string $message): array
    {
        return ['id' => $id, 'severity' => $severity, 'field' => $field, 'message' => $message];
    }
}
