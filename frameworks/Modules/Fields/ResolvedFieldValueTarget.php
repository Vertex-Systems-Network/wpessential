<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class ResolvedFieldValueTarget
{
    /** @param array<string,mixed> $field */
    public function __construct(
        public string $groupId,
        public int $groupRevision,
        public string $fieldUuid,
        public string $fieldKey,
        public int $postId,
        public string $postType,
        public array $field,
    ) {
        if ($this->groupId === '' || $this->fieldUuid === '' || $this->fieldKey === '' || $this->postType === '') {
            throw new InvalidArgumentException('Resolved Field value target identity must be non-empty.');
        }
        if ($this->groupRevision < 1 || $this->postId < 1) {
            throw new InvalidArgumentException('Resolved Field value target requires positive group revision and post ID.');
        }
    }
}
