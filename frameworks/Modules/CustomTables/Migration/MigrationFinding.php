<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class MigrationFinding
{
    public function __construct(
        public string $code,
        public string $path,
        public MigrationRisk $risk,
        public bool $blocking,
        public string $detail,
    ) {
        if (!preg_match('/^[a-z][a-z0-9_]{0,63}$/', $this->code)) {
            throw new InvalidArgumentException('Custom Tables Migration Plan finding code is invalid.');
        }
        if ($this->path === '' || strlen($this->path) > 191) {
            throw new InvalidArgumentException('Custom Tables Migration Plan finding path is invalid.');
        }
        if (strlen($this->detail) > 255) {
            throw new InvalidArgumentException('Custom Tables Migration Plan finding detail exceeds the bounded evidence limit.');
        }
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        return [
            'code' => $this->code,
            'path' => $this->path,
            'risk' => 'R' . $this->risk->value,
            'blocking' => $this->blocking,
            'detail' => $this->detail,
        ];
    }
}
