<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Definitions\Definition;

final readonly class FieldStorageKeyMigrationResult
{
    /** @param list<string> $postTypes */
    public function __construct(
        public Definition $definition,
        public string $fieldUuid,
        public string $sourceKey,
        public string $destinationKey,
        public array $postTypes,
        public int $migratedObjects,
        public bool $changed,
    ) {
    }
}
