<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class FieldTypeDescriptor
{
    /** @param list<string> $modes */
    public function __construct(
        public string $key,
        public string $label,
        public string $category,
        public string $logicalType,
        public string $editorControl,
        public string $repeatabilityMode = 'supported',
        public bool $sortableClones = true,
        public bool $storesValue = true,
        public bool $enhancedControlRequired = false,
        public array $modes = [],
    ) {
        if (preg_match('/^[a-z0-9][a-z0-9_]*$/', $this->key) !== 1) {
            throw new InvalidArgumentException('Field type key must be a lowercase machine key.');
        }
        if ($this->label === '' || $this->logicalType === '' || $this->editorControl === '') {
            throw new InvalidArgumentException('Field type label, logical type, and editor control are required.');
        }
        if (!in_array($this->repeatabilityMode, ['supported', 'container_managed', 'inapplicable'], true)) {
            throw new InvalidArgumentException('Unknown field repeatability mode.');
        }
        if ($this->repeatabilityMode === 'inapplicable' && $this->sortableClones) {
            throw new InvalidArgumentException('An inapplicable repeatability mode cannot advertise sortable clones.');
        }
        if (!$this->storesValue && $this->repeatabilityMode !== 'inapplicable') {
            throw new InvalidArgumentException('UI-only fields cannot use value repeatability.');
        }

        $seen = [];
        foreach ($this->modes as $mode) {
            if (!is_string($mode) || preg_match('/^[a-z0-9][a-z0-9_-]*$/', $mode) !== 1 || isset($seen[$mode])) {
                throw new InvalidArgumentException('Field type modes must be unique lowercase machine keys.');
            }
            $seen[$mode] = true;
        }
    }

    public function supportsCloneableValues(): bool
    {
        return $this->repeatabilityMode === 'supported';
    }

    public function managesItsOwnRows(): bool
    {
        return $this->repeatabilityMode === 'container_managed';
    }
}
