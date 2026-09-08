<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Definition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class StatusDefinitionCompiler
{
    public const OWNER_SURFACE_ID = 5;
    public const TYPE = 'status';
    public const SCHEMA_VERSION = 1;

    /** @var list<string> */
    private const PAYLOAD_KEYS = ['key', 'labels', 'visibility', 'admin', 'date_floating', 'post_types'];

    /** @var list<string> */
    private const LABEL_KEYS = ['label', 'count_singular', 'count_plural'];

    /** @var list<string> */
    private const VISIBILITY_KEYS = [
        'public',
        'internal',
        'protected',
        'private',
        'publicly_queryable',
        'exclude_from_search',
    ];

    /** @var list<string> */
    private const ADMIN_KEYS = ['show_in_all_list', 'show_in_status_list'];

    /** @var list<string> */
    private const RESERVED_KEYS = [
        'publish',
        'future',
        'draft',
        'pending',
        'private',
        'trash',
        'auto-draft',
        'inherit',
        'request-pending',
        'request-confirmed',
        'request-failed',
        'request-completed',
    ];

    /** @throws JsonException */
    public function compile(Definition $definition): StatusRegistrationDescriptor
    {
        if ($definition->type !== self::TYPE || $definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Definition is not owned by the canonical Status surface.');
        }
        if ($definition->schemaVersion !== self::SCHEMA_VERSION) {
            throw new InvalidArgumentException('Status definition schema version is unsupported.');
        }
        if ($definition->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Only Published Status definitions may compile for runtime registration.');
        }
        if ($definition->dependencies !== []) {
            throw new InvalidArgumentException('Status definition dependencies are outside the bounded V1 registration contract.');
        }

        $payload = $definition->payload;
        if (array_is_list($payload)) {
            throw new InvalidArgumentException('Status definition payload must be an object/map.');
        }
        $this->assertKnownKeys($payload, self::PAYLOAD_KEYS, 'Status definition payload');

        $key = $this->statusKey($payload['key'] ?? null);
        $labels = $this->labels($payload['labels'] ?? null);
        $visibility = $this->visibility($payload['visibility'] ?? null);
        $admin = $this->admin($payload['admin'] ?? null, $visibility['internal']);
        $dateFloating = $payload['date_floating'] ?? false;
        if (!is_bool($dateFloating)) {
            throw new InvalidArgumentException('Status date_floating must be boolean.');
        }
        $postTypes = $this->postTypes($payload['post_types'] ?? null);

        $fingerprintData = [
            'definition_id' => $definition->id,
            'revision' => $definition->revision,
            'key' => $key,
            'labels' => $labels,
            'visibility' => $visibility,
            'admin' => $admin,
            'date_floating' => $dateFloating,
            'post_types' => $postTypes,
        ];

        return new StatusRegistrationDescriptor(
            definitionId: $definition->id,
            revision: $definition->revision,
            key: $key,
            label: $labels['label'],
            countSingular: $labels['count_singular'],
            countPlural: $labels['count_plural'],
            public: $visibility['public'],
            internal: $visibility['internal'],
            protected: $visibility['protected'],
            private: $visibility['private'],
            publiclyQueryable: $visibility['publicly_queryable'],
            excludeFromSearch: $visibility['exclude_from_search'],
            showInAdminAllList: $admin['show_in_all_list'],
            showInAdminStatusList: $admin['show_in_status_list'],
            dateFloating: $dateFloating,
            postTypes: $postTypes,
            compatibilityFingerprint: hash(
                'sha256',
                json_encode($fingerprintData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ),
        );
    }

    private function statusKey(mixed $value): string
    {
        if (!is_string($value) || !preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $value)) {
            throw new InvalidArgumentException('Status key must be canonical lowercase WordPress-safe text within 20 characters.');
        }
        if (in_array($value, self::RESERVED_KEYS, true)) {
            throw new InvalidArgumentException('WordPress built-in/reserved Status keys cannot be authored by WPEssential.');
        }
        return $value;
    }

    /** @return array{label:string,count_singular:string,count_plural:string} */
    private function labels(mixed $value): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException('Status labels must be an object/map.');
        }
        $this->assertKnownKeys($value, self::LABEL_KEYS, 'Status labels');

        $label = $value['label'] ?? null;
        $singular = $value['count_singular'] ?? null;
        $plural = $value['count_plural'] ?? null;
        foreach ([$label, $singular, $plural] as $item) {
            if (!is_string($item) || $item === '' || strlen($item) > 160) {
                throw new InvalidArgumentException('Status labels must be non-empty bounded strings.');
            }
        }

        return ['label' => $label, 'count_singular' => $singular, 'count_plural' => $plural];
    }

    /**
     * @return array{
     *   public:bool,
     *   internal:bool,
     *   protected:bool,
     *   private:bool,
     *   publicly_queryable:bool,
     *   exclude_from_search:bool
     * }
     */
    private function visibility(mixed $value): array
    {
        if ($value !== null && (!is_array($value) || array_is_list($value))) {
            throw new InvalidArgumentException('Status visibility must be an object/map when provided.');
        }
        $input = is_array($value) ? $value : [];
        $this->assertKnownKeys($input, self::VISIBILITY_KEYS, 'Status visibility');

        foreach ($input as $item) {
            if (!is_bool($item)) {
                throw new InvalidArgumentException('Status visibility values must be boolean.');
            }
        }

        $classificationSpecified = array_key_exists('public', $input)
            || array_key_exists('internal', $input)
            || array_key_exists('protected', $input)
            || array_key_exists('private', $input);

        if (!$classificationSpecified) {
            $public = false;
            $internal = true;
            $protected = false;
            $private = false;
        } else {
            $public = $input['public'] ?? false;
            $internal = $input['internal'] ?? false;
            $protected = $input['protected'] ?? false;
            $private = $input['private'] ?? false;
        }

        if (count(array_filter([$public, $internal, $protected, $private])) > 1) {
            throw new InvalidArgumentException('Status visibility classification must be unambiguous.');
        }

        $publiclyQueryable = $input['publicly_queryable'] ?? $public;
        $excludeFromSearch = $input['exclude_from_search'] ?? $internal;
        if ($internal && $publiclyQueryable) {
            throw new InvalidArgumentException('Internal Status definitions cannot be publicly queryable.');
        }

        return [
            'public' => $public,
            'internal' => $internal,
            'protected' => $protected,
            'private' => $private,
            'publicly_queryable' => $publiclyQueryable,
            'exclude_from_search' => $excludeFromSearch,
        ];
    }

    /** @return array{show_in_all_list:bool,show_in_status_list:bool} */
    private function admin(mixed $value, bool $internal): array
    {
        if ($value !== null && (!is_array($value) || array_is_list($value))) {
            throw new InvalidArgumentException('Status admin settings must be an object/map when provided.');
        }
        $input = is_array($value) ? $value : [];
        $this->assertKnownKeys($input, self::ADMIN_KEYS, 'Status admin settings');
        foreach ($input as $item) {
            if (!is_bool($item)) {
                throw new InvalidArgumentException('Status admin settings must be boolean.');
            }
        }

        return [
            'show_in_all_list' => $input['show_in_all_list'] ?? !$internal,
            'show_in_status_list' => $input['show_in_status_list'] ?? !$internal,
        ];
    }

    /** @return list<string> */
    private function postTypes(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value) || $value === [] || count($value) > 64) {
            throw new InvalidArgumentException('Status post_types must be a bounded non-empty list.');
        }

        $postTypes = [];
        foreach ($value as $postType) {
            if (!is_string($postType) || !preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $postType)) {
                throw new InvalidArgumentException('Status post_types entries must be bounded WordPress keys.');
            }
            if (in_array($postType, $postTypes, true)) {
                throw new InvalidArgumentException('Status post_types entries must be unique.');
            }
            $postTypes[] = $postType;
        }
        sort($postTypes, SORT_STRING);
        return $postTypes;
    }

    /**
     * @param array<string,mixed> $value
     * @param list<string> $allowed
     */
    private function assertKnownKeys(array $value, array $allowed, string $label): void
    {
        foreach (array_keys($value) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException($label . ' contains an unsupported key.');
            }
        }
    }
}
