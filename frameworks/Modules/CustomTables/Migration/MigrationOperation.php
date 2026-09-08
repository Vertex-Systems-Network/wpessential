<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class MigrationOperation
{
    /** @param list<string> $preconditions */
    public function __construct(
        public string $type,
        public string $target,
        public MigrationRisk $risk,
        public array $preconditions = [],
        public bool $recoveryRequired = false,
        public bool $blocked = false,
    ) {
        if (!in_array($this->type, [
            'create_table',
            'add_column',
            'alter_column_default',
            'alter_column_nullability',
            'alter_column_type',
            'add_index',
            'add_unique_constraint',
            'drop_index',
            'drop_unique_constraint',
            'replace_primary_key',
            'drop_column',
        ], true)) {
            throw new InvalidArgumentException('Custom Tables Migration Plan operation type is unsupported.');
        }
        if ($this->target === '' || strlen($this->target) > 191) {
            throw new InvalidArgumentException('Custom Tables Migration Plan operation target is invalid.');
        }
        if (count($this->preconditions) > 16) {
            throw new InvalidArgumentException('Custom Tables Migration Plan operation has too many preconditions.');
        }
        foreach ($this->preconditions as $precondition) {
            if (!is_string($precondition)
                || !preg_match('/^[a-z][a-z0-9_]{0,63}$/', $precondition)
            ) {
                throw new InvalidArgumentException('Custom Tables Migration Plan precondition key is invalid.');
            }
        }
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        return [
            'type' => $this->type,
            'target' => $this->target,
            'risk' => 'R' . $this->risk->value,
            'preconditions' => $this->preconditions,
            'recovery_required' => $this->recoveryRequired,
            'blocked' => $this->blocked,
        ];
    }
}
