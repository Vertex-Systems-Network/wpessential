<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class FieldGroupRuntimeStorageProjection
{
    public const NATIVE_POST_META = 'native_post_meta';

    /**
     * Compile the normalized Field Group storage controls into the bounded native post-meta runtime contract.
     *
     * @param array<string,mixed> $group
     * @return array{mode:string,show_in_rest:bool,revisions_enabled:bool}
     */
    public function projectGroup(array $group): array
    {
        $storage = $group['storage'] ?? null;
        if (!is_array($storage) || array_is_list($storage)) {
            throw new InvalidArgumentException('Normalized Field Group storage must be a named map.');
        }

        $mode = $storage['mode'] ?? null;
        if (!is_string($mode) || $mode === '') {
            throw new InvalidArgumentException('Normalized Field Group storage mode is required.');
        }
        if ($mode !== self::NATIVE_POST_META) {
            throw new InvalidArgumentException(sprintf(
                'Runtime post-meta binding V1 requires storage mode "%s"; received "%s".',
                self::NATIVE_POST_META,
                $mode,
            ));
        }

        $showInRest = $group['show_in_rest'] ?? null;
        if (!is_bool($showInRest)) {
            throw new InvalidArgumentException('Normalized Field Group show_in_rest must be boolean.');
        }

        $revisionPolicy = $group['revision_policy'] ?? null;
        if (!is_string($revisionPolicy) || !in_array($revisionPolicy, ['disabled', 'enabled'], true)) {
            throw new InvalidArgumentException('Normalized Field Group revision_policy must be disabled or enabled.');
        }

        return [
            'mode' => self::NATIVE_POST_META,
            'show_in_rest' => $showInRest,
            'revisions_enabled' => $revisionPolicy === 'enabled',
        ];
    }

    /**
     * Compile one normalized Field's REST intent without allowing an arbitrary schema override.
     * Group REST exposure is a hard upper bound for the V1 native post-meta binder.
     *
     * @param array<string,mixed> $field
     * @return array{show_in_rest:bool,rest_schema:string}
     */
    public function projectField(array $field, bool $groupShowInRest): array
    {
        $showInRest = $field['show_in_rest'] ?? null;
        if (!is_bool($showInRest)) {
            throw new InvalidArgumentException('Normalized Field show_in_rest must be boolean.');
        }

        $restSchema = $field['rest_schema'] ?? null;
        if (!is_string($restSchema) || $restSchema !== 'auto') {
            throw new InvalidArgumentException('Normalized Field rest_schema V1 must be "auto".');
        }

        return [
            'show_in_rest' => $groupShowInRest && $showInRest,
            'rest_schema' => 'auto',
        ];
    }
}
