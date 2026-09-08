<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Definition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class TableColumnDescriptor
{
    public function __construct(
        public string $key,
        public string $type,
        public bool $nullable,
        public mixed $defaultValue,
        public bool $autoIncrement,
        public ?int $length = null,
        public ?int $precision = null,
        public ?int $scale = null,
    ) {
        if (!preg_match('/^[a-z][a-z0-9_]{0,63}$/', $this->key)) {
            throw new InvalidArgumentException('Custom Tables column key must be a canonical lowercase identifier within 64 characters.');
        }
        if (!in_array($this->type, ['bigint', 'integer', 'decimal', 'varchar', 'text', 'datetime', 'boolean', 'json'], true)) {
            throw new InvalidArgumentException('Custom Tables column type is outside the bounded V1 vocabulary.');
        }
        if ($this->autoIncrement && !in_array($this->type, ['bigint', 'integer'], true)) {
            throw new InvalidArgumentException('Only integer-family Custom Tables columns may auto increment.');
        }
        if ($this->autoIncrement && $this->nullable) {
            throw new InvalidArgumentException('Auto-increment Custom Tables columns cannot be nullable.');
        }
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type,
            'nullable' => $this->nullable,
            'default' => $this->defaultValue,
            'auto_increment' => $this->autoIncrement,
            'length' => $this->length,
            'precision' => $this->precision,
            'scale' => $this->scale,
        ];
    }
}
