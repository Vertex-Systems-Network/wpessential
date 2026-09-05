<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Contracts\AdminColumnsSourceCatalogInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

/**
 * Fields-owned, read-only discovery catalog for Admin Columns authoring.
 *
 * This deliberately advertises only published Surface 3 groups backed by the
 * already-certified native post-meta compiler and a finite post-type target set.
 * No value read/write authority is exposed here.
 */
final readonly class FieldAdminColumnsSourceCatalog implements AdminColumnsSourceCatalogInterface
{
    private const MAX_SOURCES = 100;
    private const MAX_LABEL_BYTES = 191;

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private FieldGroupDefinitionNormalizer $groups,
        private FieldGroupRuntimeStorageProjection $storage,
        private FieldGroupPostTypeTargetCompiler $postTypes,
        private PostMetaRegistrationCompiler $postMeta,
    ) {}

    /** @return list<array<string,mixed>> */
    public function adminColumnSources(): array
    {
        $sources = [];
        foreach ($this->definitions->byType(FieldGroupDefinitionNormalizer::DEFINITION_TYPE) as $definition) {
            if (!$this->eligibleDefinition($definition)) {
                continue;
            }

            try {
                $group = $this->groups->normalize($definition->payload, true);
                $storage = $this->storage->projectGroup($group);
                if (($storage['mode'] ?? null) !== FieldGroupRuntimeStorageProjection::NATIVE_POST_META) {
                    continue;
                }
                $postTypes = $this->postTypes->compile($group);
            } catch (Throwable) {
                // Discovery is fail-closed: a malformed owner definition is not
                // allowed to take down the Admin Columns authoring bootstrap.
                continue;
            }

            foreach ($group['fields'] as $field) {
                $source = $this->source($definition, $field, $postTypes);
                if ($source === null) {
                    continue;
                }
                $sources[$source['reference']] = $source;
            }
        }

        ksort($sources, SORT_STRING);
        return array_slice(array_values($sources), 0, self::MAX_SOURCES);
    }

    private function eligibleDefinition(Definition $definition): bool
    {
        return $definition->ownerSurfaceId === FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID
            && $definition->status === DefinitionStatus::Published;
    }

    /**
     * @param array<string,mixed> $field
     * @param list<string> $postTypes
     * @return array<string,mixed>|null
     */
    private function source(Definition $definition, array $field, array $postTypes): ?array
    {
        $uuid = $field['uuid'] ?? null;
        $logicalType = $field['logical_type'] ?? null;
        $label = $field['label'] ?? null;
        $repeatability = $field['repeatability'] ?? null;
        $subfields = $field['subfields'] ?? null;

        if (!is_string($uuid)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1
            || !is_string($logicalType)
            || !is_string($label)
            || trim($label) === ''
            || !is_array($repeatability)
            || (($repeatability['enabled'] ?? false) === true)
            || !is_array($subfields)
            || $subfields !== []
            || $postTypes === []
        ) {
            return null;
        }

        try {
            $compiled = $this->postMeta->compile($field, $postTypes[0]);
        } catch (Throwable) {
            return null;
        }

        $args = $compiled['args'] ?? null;
        if (!is_array($args)
            || ($args['single'] ?? null) !== true
            || !is_string($args['type'] ?? null)
            || $args['type'] === 'array'
        ) {
            return null;
        }

        return [
            'owner' => 'fields',
            'reference' => sprintf('fields.%s.%s', $definition->id, $uuid),
            'label' => substr(trim($label), 0, self::MAX_LABEL_BYTES),
            'formats' => $this->formats($args['type']),
            'capabilities' => $this->readOnlyCapabilities(),
            'ownerMetadata' => [
                'groupRevision' => $definition->revision,
                'fieldUuid' => $uuid,
                'logicalType' => $logicalType,
                'storageOwner' => FieldGroupRuntimeStorageProjection::NATIVE_POST_META,
                'postTypes' => $postTypes,
            ],
        ];
    }

    /** @return list<string> */
    private function formats(string $storageType): array
    {
        return match ($storageType) {
            'integer', 'number' => ['number', 'text'],
            'boolean' => ['boolean', 'text'],
            default => ['text'],
        };
    }

    /** @return array{sort:bool,filter:bool,edit:bool,export:bool} */
    private function readOnlyCapabilities(): array
    {
        return [
            'sort' => false,
            'filter' => false,
            'edit' => false,
            'export' => false,
        ];
    }
}
