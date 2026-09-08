<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Run\Persistence;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;

final class MigrationRunRecordCodec
{
    public const STORAGE_VERSION = 1;

    private const KEYS = [
        'storage_version',
        'id',
        'plan_fingerprint',
        'table_key',
        'target_definition_id',
        'target_revision',
        'target_schema_version',
        'state',
        'state_revision',
    ];

    /** @return array<string,int|string> */
    public function encode(MigrationRun $run): array
    {
        $canonical = $run->canonical();

        return [
            'storage_version' => self::STORAGE_VERSION,
            'id' => $canonical['id'],
            'plan_fingerprint' => $canonical['plan_fingerprint'],
            'table_key' => $canonical['table_key'],
            'target_definition_id' => $canonical['target_definition_id'],
            'target_revision' => $canonical['target_revision'],
            'target_schema_version' => $canonical['target_schema_version'],
            'state' => $canonical['state'],
            'state_revision' => $canonical['state_revision'],
        ];
    }

    /** @param array<string,mixed> $record */
    public function decode(array $record): MigrationRun
    {
        $keys = array_keys($record);
        sort($keys);
        $expected = self::KEYS;
        sort($expected);

        if ($keys !== $expected) {
            throw new InvalidArgumentException('Custom Tables migration run storage record shape is invalid.');
        }
        if (!is_int($record['storage_version']) || $record['storage_version'] !== self::STORAGE_VERSION) {
            throw new InvalidArgumentException('Custom Tables migration run storage record version is unsupported.');
        }

        foreach (['id', 'plan_fingerprint', 'table_key', 'target_definition_id', 'state'] as $key) {
            if (!is_string($record[$key])) {
                throw new InvalidArgumentException('Custom Tables migration run storage record string field is invalid.');
            }
        }
        foreach (['target_revision', 'target_schema_version', 'state_revision'] as $key) {
            if (!is_int($record[$key])) {
                throw new InvalidArgumentException('Custom Tables migration run storage record revision field is invalid.');
            }
        }

        $state = MigrationRunState::tryFrom($record['state']);
        if ($state === null) {
            throw new InvalidArgumentException('Custom Tables migration run storage record state is unsupported.');
        }

        return new MigrationRun(
            id: $record['id'],
            planFingerprint: $record['plan_fingerprint'],
            tableKey: $record['table_key'],
            targetDefinitionId: $record['target_definition_id'],
            targetRevision: $record['target_revision'],
            targetSchemaVersion: $record['target_schema_version'],
            state: $state,
            stateRevision: $record['state_revision'],
        );
    }
}
