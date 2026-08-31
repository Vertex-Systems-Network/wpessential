<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class FieldPresetDescriptor
{
    /** @param array<string,mixed> $defaults */
    public function __construct(
        public string $key,
        public string $label,
        public string $canonicalType,
        public array $defaults = [],
    ) {
        if (preg_match('/^[a-z0-9][a-z0-9_]*$/', $this->key) !== 1) {
            throw new InvalidArgumentException('Field preset key must be a lowercase machine key.');
        }
        if ($this->label === '' || $this->canonicalType === '') {
            throw new InvalidArgumentException('Field preset label and canonical type are required.');
        }
    }
}
