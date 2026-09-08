<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Schema;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class Ct1ManagedTableIdentity
{
    public function __construct(
        public string $tableKey,
        public string $sitePrefix,
        public string $physicalName,
    ) {
        if (!preg_match('/^[a-z][a-z0-9_]{0,47}$/', $this->tableKey)) {
            throw new InvalidArgumentException('CT1 logical table key is invalid.');
        }
        if ($this->sitePrefix === ''
            || strlen($this->sitePrefix) > 48
            || preg_match('/^[A-Za-z0-9_]+$/', $this->sitePrefix) !== 1
        ) {
            throw new InvalidArgumentException('CT1 trusted WordPress site prefix is invalid.');
        }
        if ($this->physicalName === ''
            || strlen($this->physicalName) > 64
            || preg_match('/^[A-Za-z0-9_]+$/', $this->physicalName) !== 1
        ) {
            throw new InvalidArgumentException('CT1 managed physical table name is invalid.');
        }
        if (!str_starts_with($this->physicalName, $this->sitePrefix . 'wpe_')) {
            throw new InvalidArgumentException('CT1 managed physical table name is outside the trusted WPE namespace.');
        }
    }
}
