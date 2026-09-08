<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Schema;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class Ct1ManagedTableIdentity
{
    private const NAMESPACE = 'wpe_ct_';
    private const TOKEN_LENGTH = 24;

    public function __construct(
        public string $definitionId,
        public string $tableKey,
        public string $sitePrefix,
        public string $physicalName,
    ) {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->definitionId)) {
            throw new InvalidArgumentException('CT1 Definition identity is invalid.');
        }
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

        $expected = self::physicalNameFor($this->definitionId, $this->tableKey, $this->sitePrefix);
        if (!hash_equals($expected, $this->physicalName)) {
            throw new InvalidArgumentException('CT1 managed physical table name does not match the canonical trusted mapping.');
        }
    }

    public static function derive(string $definitionId, string $tableKey, string $sitePrefix): self
    {
        return new self(
            definitionId: $definitionId,
            tableKey: $tableKey,
            sitePrefix: $sitePrefix,
            physicalName: self::physicalNameFor($definitionId, $tableKey, $sitePrefix),
        );
    }

    private static function physicalNameFor(string $definitionId, string $tableKey, string $sitePrefix): string
    {
        $token = substr(hash('sha256', $definitionId . ':' . $tableKey), 0, self::TOKEN_LENGTH);
        return $sitePrefix . self::NAMESPACE . $token;
    }
}
