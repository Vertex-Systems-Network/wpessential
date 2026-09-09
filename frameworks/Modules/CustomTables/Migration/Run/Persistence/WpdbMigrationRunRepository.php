<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Run\Persistence;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunRepositoryInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class WpdbMigrationRunRepository implements MigrationRunRepositoryInterface
{
    private string $table;

    public function __construct(
        private DatabaseAdapterInterface $database,
        private MigrationRunRecordCodec $codec,
        private int $siteId,
    ) {
        if ($this->siteId < 1) {
            throw new InvalidArgumentException('Custom Tables Migration Run repository site id must be positive.');
        }

        $prefix = $database->networkTablePrefix();
        if (preg_match('/^[A-Za-z0-9_]+$/', $prefix) !== 1) {
            throw new RuntimeException('Custom Tables Migration Run repository prefix is invalid.');
        }
        $this->table = $prefix . 'wpe_custom_table_migration_runs';
    }

    public function create(MigrationRun $run): MigrationRun
    {
        $record = $this->codec->encode($run);
        $inserted = $this->database->insert($this->table, [
            'site_id' => $this->siteId,
            'run_id' => $record['id'],
            'storage_version' => $record['storage_version'],
            'plan_fingerprint' => $record['plan_fingerprint'],
            'table_key' => $record['table_key'],
            'target_definition_id' => $record['target_definition_id'],
            'target_revision' => $record['target_revision'],
            'target_schema_version' => $record['target_schema_version'],
            'state' => $record['state'],
            'state_revision' => $record['state_revision'],
            'created_at' => gmdate('Y-m-d H:i:s.u'),
            'updated_at' => gmdate('Y-m-d H:i:s.u'),
        ], ['%d','%s','%d','%s','%s','%s','%d','%d','%s','%d','%s','%s']);

        if (!$inserted) {
            throw new RuntimeException('Custom Tables Migration Run could not be created.');
        }

        return $this->get($run->id) ?? throw new RuntimeException('Custom Tables Migration Run create verification failed.');
    }

    public function get(string $id): ?MigrationRun
    {
        $query = $this->database->prepare(
            "SELECT storage_version, run_id, plan_fingerprint, table_key, target_definition_id, target_revision, target_schema_version, state, state_revision FROM `{$this->table}` WHERE site_id = %d AND run_id = %s LIMIT 1",
            $this->siteId,
            strtolower($id),
        );
        $row = $this->database->getRow($query);
        if ($row === null) {
            return null;
        }

        return $this->codec->decode([
            'storage_version' => $this->int($row, 'storage_version'),
            'id' => $this->string($row, 'run_id'),
            'plan_fingerprint' => $this->string($row, 'plan_fingerprint'),
            'table_key' => $this->string($row, 'table_key'),
            'target_definition_id' => $this->string($row, 'target_definition_id'),
            'target_revision' => $this->int($row, 'target_revision'),
            'target_schema_version' => $this->int($row, 'target_schema_version'),
            'state' => $this->string($row, 'state'),
            'state_revision' => $this->int($row, 'state_revision'),
        ]);
    }

    public function compareAndSwap(MigrationRun $next, int $expectedStateRevision): MigrationRun
    {
        if ($expectedStateRevision < 1 || $next->stateRevision !== $expectedStateRevision + 1) {
            throw new InvalidArgumentException('Custom Tables Migration Run compare-and-swap revision contract is invalid.');
        }

        $record = $this->codec->encode($next);
        $query = $this->database->prepare(
            "UPDATE `{$this->table}` SET storage_version = %d, plan_fingerprint = %s, table_key = %s, target_definition_id = %s, target_revision = %d, target_schema_version = %d, state = %s, state_revision = %d, updated_at = UTC_TIMESTAMP(6) WHERE site_id = %d AND run_id = %s AND state_revision = %d",
            $record['storage_version'],
            $record['plan_fingerprint'],
            $record['table_key'],
            $record['target_definition_id'],
            $record['target_revision'],
            $record['target_schema_version'],
            $record['state'],
            $record['state_revision'],
            $this->siteId,
            $record['id'],
            $expectedStateRevision,
        );
        $changed = $this->database->query($query);
        if ($changed !== 1) {
            throw new RuntimeException('Custom Tables Migration Run compare-and-swap failed or was stale.');
        }

        return $this->get($next->id) ?? throw new RuntimeException('Custom Tables Migration Run compare-and-swap verification failed.');
    }

    /** @param array<string,mixed> $row */
    private function string(array $row, string $key): string
    {
        $value = $row[$key] ?? null;
        if (!is_string($value)) {
            throw new RuntimeException('Custom Tables Migration Run stored string field is invalid.');
        }
        return $value;
    }

    /** @param array<string,mixed> $row */
    private function int(array $row, string $key): int
    {
        $value = $row[$key] ?? null;
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && preg_match('/^\d+$/', $value) === 1) {
            return (int) $value;
        }
        throw new RuntimeException('Custom Tables Migration Run stored integer field is invalid.');
    }
}
