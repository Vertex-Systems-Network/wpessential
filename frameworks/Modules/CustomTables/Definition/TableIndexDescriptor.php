<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Definition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class TableIndexDescriptor
{
    /** @param list<string> $columns */
    public function __construct(
        public string $key,
        public array $columns,
        public bool $unique,
    ) {
        if (!preg_match('/^[a-z][a-z0-9_]{0,63}$/', $this->key)) {
            throw new InvalidArgumentException('Custom Tables index key must be a canonical lowercase identifier within 64 characters.');
        }
        if ($this->columns === [] || count($this->columns) > 8) {
            throw new InvalidArgumentException('Custom Tables indexes must reference between one and eight columns.');
        }
        $seen = [];
        foreach ($this->columns as $column) {
            if (!is_string($column) || !preg_match('/^[a-z][a-z0-9_]{0,63}$/', $column)) {
                throw new InvalidArgumentException('Custom Tables index columns must be canonical identifiers.');
            }
            if (isset($seen[$column])) {
                throw new InvalidArgumentException('Custom Tables index columns must be unique within an index.');
            }
            $seen[$column] = true;
        }
    }

    /** @return array{key:string,columns:list<string>,unique:bool} */
    public function canonical(): array
    {
        return [
            'key' => $this->key,
            'columns' => $this->columns,
            'unique' => $this->unique,
        ];
    }
}
