<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class PostMetaRegistrationCompiler
{
    private const STRING_TYPES = [
        'text', 'textarea', 'email', 'url', 'date', 'time', 'datetime', 'color', 'phone',
    ];

    private const BOOLEAN_TYPES = ['true_false', 'switcher', 'checkbox'];

    private const INTEGER_REFERENCE_TYPES = ['image', 'file', 'media', 'video', 'post_object'];

    private const INTEGER_LIST_TYPES = ['gallery', 'file_advanced', 'posts'];

    public function __construct(
        private FieldValueNormalizer $values = new FieldValueNormalizer(),
        private FieldValuePersistenceGuard $persistence = new FieldValuePersistenceGuard(),
    ) {}

    /**
     * Compile one normalized Field definition into a WordPress post-meta registration contract.
     *
     * This compiler intentionally supports only the first certified scalar/list tranche. Complex,
     * provider-owned, Relations-owned, secret, mixed and structured values remain fail-closed.
     *
     * @param array<string,mixed> $field
     * @return array{
     *     post_type:string,
     *     field_uuid:string,
     *     meta_key:string,
     *     args:array{
     *         type:string,
     *         label:string,
     *         description:string,
     *         single:bool,
     *         sanitize_callback:callable,
     *         auth_callback:callable,
     *         show_in_rest:bool|array<string,mixed>,
     *         revisions_enabled:bool
     *     }
     * }
     */
    public function compile(
        array $field,
        string $postType,
        bool $showInRest = false,
        bool $revisionsEnabled = false,
    ): array {
        $this->assertNormalizedField($field);
        $this->assertPostType($postType);

        if ($field['stores_value'] !== true) {
            throw new InvalidArgumentException('UI-only Fields cannot compile to post-meta storage.');
        }

        $uuid = $field['uuid'];
        if (!is_string($uuid) || $uuid === '') {
            throw new InvalidArgumentException('Post-meta storage requires a persisted stable Field UUID.');
        }

        $shape = $this->storageShape($field);
        $metaKey = $field['key'];
        $label = $field['label'];
        if (!is_string($metaKey) || !is_string($label)) {
            throw new InvalidArgumentException('Normalized Field key and label must be strings.');
        }

        return [
            'post_type' => $postType,
            'field_uuid' => $uuid,
            'meta_key' => $metaKey,
            'args' => [
                'type' => $shape['type'],
                'label' => $label,
                'description' => sprintf('WPEssential Field value (%s).', $uuid),
                'single' => $shape['single'],
                'sanitize_callback' => $this->sanitizer($field, $shape['multi_row']),
                'auth_callback' => static function (
                    bool $allowed,
                    string $registeredMetaKey,
                    int $objectId,
                    int $userId,
                    string $capability,
                    array $caps,
                ): bool {
                    unset($allowed, $registeredMetaKey, $userId, $capability, $caps);
                    return $objectId > 0
                        && function_exists('current_user_can')
                        && current_user_can('edit_post', $objectId);
                },
                'show_in_rest' => $this->restExposure($showInRest, $shape),
                'revisions_enabled' => $revisionsEnabled,
            ],
        ];
    }

    /**
     * @param array<string,mixed> $field
     * @return array{type:string,single:bool,item_type:?string,multi_row:bool}
     */
    private function storageShape(array $field): array
    {
        $type = $field['type'];
        if (!is_string($type)) {
            throw new InvalidArgumentException('Normalized Field type must be a string.');
        }

        if (in_array($type, self::INTEGER_LIST_TYPES, true)) {
            return ['type' => 'array', 'single' => true, 'item_type' => 'integer', 'multi_row' => false];
        }

        $scalarType = match (true) {
            in_array($type, self::STRING_TYPES, true) => 'string',
            in_array($type, self::BOOLEAN_TYPES, true) => 'boolean',
            in_array($type, self::INTEGER_REFERENCE_TYPES, true) => 'integer',
            $type === 'number' || $type === 'range' => (($field['settings']['integer'] ?? false) === true ? 'integer' : 'number'),
            default => null,
        };

        if ($scalarType === null) {
            throw new InvalidArgumentException(sprintf(
                'Field type "%s" is not certified for registered post-meta storage V1.',
                $type,
            ));
        }

        $repeatability = $field['repeatability'];
        if (!is_array($repeatability) || array_is_list($repeatability)) {
            throw new InvalidArgumentException('Normalized Field repeatability must be a named map.');
        }

        if (($repeatability['enabled'] ?? false) !== true) {
            return ['type' => $scalarType, 'single' => true, 'item_type' => null, 'multi_row' => false];
        }

        if (($repeatability['store_as_multiple'] ?? false) === true) {
            return ['type' => $scalarType, 'single' => false, 'item_type' => null, 'multi_row' => true];
        }

        return ['type' => 'array', 'single' => true, 'item_type' => $scalarType, 'multi_row' => false];
    }

    /**
     * @param array{type:string,single:bool,item_type:?string,multi_row:bool} $shape
     * @return bool|array<string,mixed>
     */
    private function restExposure(bool $showInRest, array $shape): bool|array
    {
        if (!$showInRest) {
            return false;
        }

        $schema = ['type' => $shape['type']];
        if ($shape['type'] === 'array') {
            $itemType = $shape['item_type'];
            if (!is_string($itemType) || $itemType === '') {
                throw new InvalidArgumentException('REST-visible array meta requires an explicit item schema.');
            }
            $schema['items'] = ['type' => $itemType];
        }

        return ['schema' => $schema];
    }

    /** @param array<string,mixed> $field */
    private function sanitizer(array $field, bool $multiRow): callable
    {
        if ($multiRow) {
            $field['repeatability']['enabled'] = false;
            $field['repeatability']['sortable'] = false;
        }

        return function (mixed $value) use ($field): mixed {
            return $this->persistence->assertSafe($this->values->normalize($field, $value));
        };
    }

    /** @param array<string,mixed> $field */
    private function assertNormalizedField(array $field): void
    {
        foreach (['uuid', 'key', 'label', 'type', 'stores_value', 'settings', 'repeatability'] as $required) {
            if (!array_key_exists($required, $field)) {
                throw new InvalidArgumentException(sprintf('Normalized Field is missing "%s".', $required));
            }
        }
        if (!is_array($field['settings']) || array_is_list($field['settings'])) {
            throw new InvalidArgumentException('Normalized Field settings must be a named map.');
        }
    }

    private function assertPostType(string $postType): void
    {
        if (preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $postType) !== 1) {
            throw new InvalidArgumentException('Post type must be a lowercase WordPress machine key up to 20 characters.');
        }
    }
}
