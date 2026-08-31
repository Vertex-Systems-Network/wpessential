<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class TaxonomyValidationService
{
    private const PREVIEW_ID = '00000000-0000-4000-8000-000000000002';

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private TaxonomyDefinitionProjector $projector,
    ) {}

    /**
     * @param array<string,mixed> $input
     * @return array{valid:bool,issues:list<array{id:string,severity:string,field:string,message:string}>,candidate:array{taxonomy_key:?string}}
     */
    public function validate(array $input): array
    {
        $issues = [];
        $payload = $input['payload'] ?? null;
        if (!is_array($payload) || array_is_list($payload)) {
            $issues[] = $this->issue('payload_invalid', 'blocked', 'payload', 'Taxonomy payload must be an object/map.');
            return $this->report(null, $issues);
        }

        $current = $this->currentDefinition($input, $issues);
        $keyValue = $payload['taxonomy_key'] ?? null;
        $key = is_string($keyValue) ? trim($keyValue) : null;

        $candidate = new Definition(
            id: $current?->id ?? self::PREVIEW_ID,
            slug: $current?->slug ?? ('taxonomy-preview-' . ($key !== null && $key !== '' ? str_replace('_', '-', $key) : 'candidate')),
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: $current?->schemaVersion ?? 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: $payload,
            revision: ($current?->revision ?? 0) + 1,
            dependencies: $current?->dependencies ?? [],
        );

        try {
            $this->projector->project($candidate);
        } catch (InvalidArgumentException $exception) {
            $issues[] = $this->issue('registration_schema_invalid', 'blocked', 'payload', $exception->getMessage());
        }

        if ($key !== null && $key !== '') {
            $this->validateCanonicalOwnership($key, $current?->id, $issues);
            $this->validateRuntimeOwnership($key, $current, $issues);
        }
        $this->validateObjectTypeDependencies($payload, $issues);

        return $this->report($key, $issues);
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
            $issues[] = $this->issue('definition_id_invalid', 'blocked', 'id', 'Taxonomy id must be a string.');
            return null;
        }

        $definition = $this->definitions->get($id);
        if (!$definition instanceof Definition
            || $definition->type !== TaxonomyDefinitionProjector::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
        ) {
            $issues[] = $this->issue('definition_not_found', 'blocked', 'id', 'Taxonomy definition was not found in the canonical Surface 2 owner.');
            return null;
        }

        $existingKey = $definition->payload['taxonomy_key'] ?? null;
        $requestedKey = $input['payload']['taxonomy_key'] ?? null;
        if (is_string($existingKey) && is_string($requestedKey) && trim($existingKey) !== trim($requestedKey)) {
            $issues[] = $this->issue(
                'runtime_key_immutable',
                'blocked',
                'taxonomy_key',
                'Existing Taxonomy taxonomy_key is immutable; create a new definition for a different runtime key.',
            );
        }

        return $definition;
    }

    /** @param list<array{id:string,severity:string,field:string,message:string}> $issues */
    private function validateCanonicalOwnership(string $key, ?string $currentDefinitionId, array &$issues): void
    {
        foreach ($this->definitions->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE) as $definition) {
            if ($definition->ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
                || $definition->id === $currentDefinitionId
            ) {
                continue;
            }

            $existingKey = $definition->payload['taxonomy_key'] ?? null;
            if (is_string($existingKey) && trim($existingKey) === $key) {
                $issues[] = $this->issue(
                    'duplicate_definition',
                    'blocked',
                    'taxonomy_key',
                    sprintf('Taxonomy key "%s" is already owned by another canonical Taxonomy definition.', $key),
                );
                return;
            }
        }
    }

    /** @param list<array{id:string,severity:string,field:string,message:string}> $issues */
    private function validateRuntimeOwnership(string $key, ?Definition $current, array &$issues): void
    {
        if (!function_exists('taxonomy_exists') || !taxonomy_exists($key)) {
            return;
        }

        $currentKey = $current?->payload['taxonomy_key'] ?? null;
        $isCurrentPublishedRegistration = $current instanceof Definition
            && $current->status === DefinitionStatus::Published
            && is_string($currentKey)
            && trim($currentKey) === $key;
        if ($isCurrentPublishedRegistration) {
            $issues[] = $this->issue(
                'runtime_registration_present',
                'info',
                'taxonomy_key',
                sprintf('Taxonomy key "%s" is active at runtime. WordPress does not reliably identify the registering owner, so ownership is not inferred from runtime presence alone.', $key),
            );
            return;
        }

        $issues[] = $this->issue(
            'runtime_registration_collision',
            'blocked',
            'taxonomy_key',
            sprintf('Taxonomy key "%s" is already registered by WordPress or another runtime owner.', $key),
        );
    }

    /**
     * @param array<string,mixed> $payload
     * @param list<array{id:string,severity:string,field:string,message:string}> $issues
     */
    private function validateObjectTypeDependencies(array $payload, array &$issues): void
    {
        $objectTypes = $payload['object_types'] ?? null;
        if (!is_array($objectTypes) || !array_is_list($objectTypes) || !function_exists('post_type_exists')) {
            return;
        }

        foreach ($objectTypes as $objectType) {
            if (!is_string($objectType) || trim($objectType) === '') {
                continue;
            }
            $objectType = trim($objectType);
            if (!post_type_exists($objectType)) {
                $issues[] = $this->issue(
                    'missing_object_type',
                    'compatibility_warning',
                    'object_types',
                    sprintf('Object type "%s" is not currently registered; the Taxonomy can remain defined but this relationship is degraded.', $objectType),
                );
            }
        }
    }

    /**
     * @param list<array{id:string,severity:string,field:string,message:string}> $issues
     * @return array{valid:bool,issues:list<array{id:string,severity:string,field:string,message:string}>,candidate:array{taxonomy_key:?string}}
     */
    private function report(?string $key, array $issues): array
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
            'candidate' => ['taxonomy_key' => $key],
        ];
    }

    /** @return array{id:string,severity:string,field:string,message:string} */
    private function issue(string $id, string $severity, string $field, string $message): array
    {
        return [
            'id' => $id,
            'severity' => $severity,
            'field' => $field,
            'message' => $message,
        ];
    }
}
