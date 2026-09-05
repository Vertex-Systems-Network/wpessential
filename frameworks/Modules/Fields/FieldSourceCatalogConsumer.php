<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\FieldSourceCatalogConsumerInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class FieldSourceCatalogConsumer implements FieldSourceCatalogConsumerInterface
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
    private const SCALAR_TYPES = ['string', 'boolean', 'integer', 'number'];
    private const MAX_LABEL_BYTES = 191;

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private FieldGroupDefinitionNormalizer $groups,
        private FieldGroupRuntimeStorageProjection $storage,
        private FieldGroupPostTypeTargetCompiler $postTypes,
        private PostMetaRegistrationCompiler $compiler,
    ) {}

    public function listSources(): array
    {
        $sources = [];

        foreach ($this->definitions->byType(FieldGroupDefinitionNormalizer::DEFINITION_TYPE) as $definition) {
            if (!$definition instanceof Definition
                || $definition->ownerSurfaceId !== FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID
                || $definition->status !== DefinitionStatus::Published
            ) {
                continue;
            }
            if (preg_match(self::UUID_PATTERN, $definition->id) !== 1 || $definition->revision < 1) {
                throw new RuntimeException('Published Field source catalog definition identity is malformed.');
            }

            $payload = $this->groups->normalize($definition->payload, true);
            $storage = $payload['storage'] ?? null;
            if (!is_array($storage)
                || ($storage['mode'] ?? null) !== FieldGroupRuntimeStorageProjection::NATIVE_POST_META
            ) {
                continue;
            }

            try {
                $storageProjection = $this->storage->projectGroup($payload);
                $postTypes = $this->postTypes->compile($payload);
            } catch (InvalidArgumentException) {
                // A valid Field Group may intentionally use storage/location
                // semantics outside the bounded native post-meta source catalog.
                continue;
            }

            $fields = $payload['fields'] ?? null;
            if (!is_array($fields) || !array_is_list($fields)) {
                throw new RuntimeException('Published Field source catalog definition has malformed fields.');
            }

            foreach ($fields as $field) {
                if (!is_array($field)) {
                    throw new RuntimeException('Published Field source catalog contains a malformed Field.');
                }

                $fieldUuid = $field['uuid'] ?? null;
                if (!is_string($fieldUuid) || preg_match(self::UUID_PATTERN, $fieldUuid) !== 1) {
                    continue;
                }

                try {
                    $registration = $this->compiler->compile(
                        $field,
                        $postTypes[0],
                        $storageProjection['show_in_rest'],
                        $storageProjection['revisions_enabled'],
                    );
                } catch (InvalidArgumentException) {
                    continue;
                }

                $args = $registration['args'] ?? null;
                $logicalType = is_array($args) ? ($args['type'] ?? null) : null;
                if (($args['single'] ?? null) !== true
                    || !is_string($logicalType)
                    || !in_array($logicalType, self::SCALAR_TYPES, true)
                ) {
                    continue;
                }

                $fieldRef = sprintf('fields.%s.%s', $definition->id, $fieldUuid);
                $sources[$fieldRef] = [
                    'contract_version' => self::CONTRACT_VERSION,
                    'field_ref' => $fieldRef,
                    'group_id' => $definition->id,
                    'group_revision' => $definition->revision,
                    'field_uuid' => $fieldUuid,
                    'label' => $this->label($payload, $field),
                    'logical_type' => $logicalType,
                    'storage_owner' => FieldGroupRuntimeStorageProjection::NATIVE_POST_META,
                    'post_types' => $postTypes,
                ];
            }
        }

        ksort($sources, SORT_STRING);
        return array_slice(array_values($sources), 0, self::MAX_SOURCES);
    }

    /** @param array<string,mixed> $group @param array<string,mixed> $field */
    private function label(array $group, array $field): string
    {
        $groupTitle = $group['title'] ?? null;
        $fieldLabel = $field['label'] ?? null;
        $fieldKey = $field['key'] ?? null;
        if (!is_string($groupTitle) || trim($groupTitle) === '' || !is_string($fieldKey) || trim($fieldKey) === '') {
            throw new RuntimeException('Published Field source catalog label metadata is malformed.');
        }

        $fieldName = is_string($fieldLabel) && trim($fieldLabel) !== '' ? trim($fieldLabel) : trim($fieldKey);
        $label = trim($groupTitle) . ' — ' . $fieldName;
        return substr($label, 0, self::MAX_LABEL_BYTES);
    }
}
