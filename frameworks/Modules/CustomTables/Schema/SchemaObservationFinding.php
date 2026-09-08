<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Schema;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class SchemaObservationFinding
{
    public function __construct(
        public string $code,
        public string $path,
        public string $observed,
    ) {
        if (!preg_match('/^[a-z][a-z0-9_]{0,63}$/', $this->code)) {
            throw new InvalidArgumentException('Custom Tables observation finding code is invalid.');
        }
        if ($this->path === '' || strlen($this->path) > 191) {
            throw new InvalidArgumentException('Custom Tables observation finding path is invalid.');
        }
        if (strlen($this->observed) > 255) {
            throw new InvalidArgumentException('Custom Tables observation finding value exceeds the bounded evidence limit.');
        }
    }

    /** @return array{code:string,path:string,observed:string} */
    public function canonical(): array
    {
        return [
            'code' => $this->code,
            'path' => $this->path,
            'observed' => $this->observed,
        ];
    }
}
