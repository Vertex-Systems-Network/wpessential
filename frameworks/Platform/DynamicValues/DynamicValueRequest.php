<?php

declare(strict_types=1);

namespace WPEssential\Platform\DynamicValues;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class DynamicValueRequest
{
    public function __construct(
        public string $sourceRef,
        public string $valueRef,
        public string $resourceType,
        public string|int $resourceId,
    ) {
        foreach (['sourceRef' => $this->sourceRef, 'valueRef' => $this->valueRef, 'resourceType' => $this->resourceType] as $name => $value) {
            if ($value === '' || strlen($value) > 160 || !preg_match('/^[a-zA-Z0-9_.:-]+$/', $value)) {
                throw new InvalidArgumentException($name . ' must be a bounded semantic reference.');
            }
        }
        if ((is_int($this->resourceId) && $this->resourceId < 1) || (is_string($this->resourceId) && ($this->resourceId === '' || strlen($this->resourceId) > 160))) {
            throw new InvalidArgumentException('Resource id must be a bounded positive identifier.');
        }
    }
}
