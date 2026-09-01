<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

final class FieldAdminCatalogProjector
{
    /** @var list<string> */
    private const SUPPORTED_V1_TYPES = [
        'text',
        'textarea',
        'number',
        'checkbox',
        'date',
        'time',
        'datetime',
    ];

    /**
     * @param array<string,mixed> $catalog
     * @return array<string,mixed>
     */
    public function project(array $catalog): array
    {
        $types = $catalog['types'] ?? [];
        if (!is_array($types) || !array_is_list($types)) {
            return [
                ...$catalog,
                'types' => [],
                'admin_policy' => $this->policy(),
            ];
        }

        $projected = [];
        foreach ($types as $type) {
            if (!is_array($type) || array_is_list($type)) {
                continue;
            }

            $key = $type['key'] ?? null;
            if (!is_string($key) || $key === '') {
                continue;
            }

            $available = in_array($key, self::SUPPORTED_V1_TYPES, true);
            $projected[] = [
                ...$type,
                'admin_available' => $available,
                'admin_unavailable_reason' => $available
                    ? null
                    : 'This field type is preserved read-only until its canonical V1 admin owner/editor contract is certified.',
            ];
        }

        return [
            ...$catalog,
            'types' => $projected,
            'admin_policy' => $this->policy(),
        ];
    }

    /** @return array<string,mixed> */
    private function policy(): array
    {
        return [
            'version' => 1,
            'supported_types' => self::SUPPORTED_V1_TYPES,
            'unsupported_behavior' => 'preserve_read_only',
            'persist_via_abilities_only' => true,
            'persisted_key_mutation' => false,
            'persisted_uuid_mutation' => false,
        ];
    }
}
