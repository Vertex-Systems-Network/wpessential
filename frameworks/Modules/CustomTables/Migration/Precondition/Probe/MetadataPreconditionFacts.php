<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition\Probe;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class MetadataPreconditionFacts
{
    /** @param array<string,bool> $features */
    /** @param array<string,string> $columnFingerprints */
    public function __construct(
        public bool $tableExists,
        public array $features = [],
        public array $columnFingerprints = [],
    ) {
        foreach ($this->features as $feature => $available) {
            if (preg_match('/^[a-z][a-z0-9_.-]{0,63}$/', $feature) !== 1 || !is_bool($available)) {
                throw new InvalidArgumentException('Custom Tables metadata precondition feature fact is invalid.');
            }
        }
        foreach ($this->columnFingerprints as $target => $fingerprint) {
            if (preg_match('/^[a-z][a-z0-9_.]{0,127}$/', $target) !== 1 || preg_match('/^[a-f0-9]{64}$/', $fingerprint) !== 1) {
                throw new InvalidArgumentException('Custom Tables metadata precondition column fingerprint fact is invalid.');
            }
        }
    }
}
