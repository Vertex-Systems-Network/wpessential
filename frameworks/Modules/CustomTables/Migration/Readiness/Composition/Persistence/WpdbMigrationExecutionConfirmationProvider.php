<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\ExecutionActorType;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\BoundMigrationExecutionConfirmation;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\TrustedMigrationExecutionConfirmationProviderInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class WpdbMigrationExecutionConfirmationProvider implements TrustedMigrationExecutionConfirmationProviderInterface
{
    private string $table;

    public function __construct(
        private DatabaseAdapterInterface $database,
        private int $siteId,
    ) {
        if ($this->siteId < 1) {
            throw new InvalidArgumentException('Custom Tables execution confirmation provider site id must be positive.');
        }

        $prefix = $database->networkTablePrefix();
        if (preg_match('/^[A-Za-z0-9_]+$/', $prefix) !== 1) {
            throw new RuntimeException('Custom Tables execution confirmation provider prefix is invalid.');
        }

        $this->table = $prefix . 'wpe_custom_table_migration_confirmations';
    }

    public function confirmationFor(string $runId): ?BoundMigrationExecutionConfirmation
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $runId) !== 1) {
            throw new InvalidArgumentException('Custom Tables execution confirmation lookup run id must be a canonical UUID.');
        }

        $query = $this->database->prepare(
            "SELECT run_id, plan_fingerprint, readiness_state_revision, actor_type, actor_user_id FROM `{$this->table}` WHERE site_id = %d AND run_id = %s LIMIT 1",
            $this->siteId,
            strtolower($runId),
        );
        $row = $this->database->getRow($query);
        if ($row === null) {
            return null;
        }

        $actorType = ExecutionActorType::tryFrom($this->string($row, 'actor_type'));
        if (!$actorType instanceof ExecutionActorType) {
            throw new RuntimeException('Custom Tables stored execution confirmation actor type is invalid.');
        }

        return new BoundMigrationExecutionConfirmation(
            runId: $this->string($row, 'run_id'),
            planFingerprint: $this->string($row, 'plan_fingerprint'),
            readinessStateRevision: $this->positiveInt($row, 'readiness_state_revision'),
            actorType: $actorType,
            actorUserId: $this->nullablePositiveInt($row, 'actor_user_id'),
        );
    }

    /** @param array<string,mixed> $row */
    private function string(array $row, string $key): string
    {
        $value = $row[$key] ?? null;
        if (!is_string($value)) {
            throw new RuntimeException('Custom Tables stored execution confirmation string field is invalid.');
        }

        return $value;
    }

    /** @param array<string,mixed> $row */
    private function positiveInt(array $row, string $key): int
    {
        $value = $row[$key] ?? null;
        if (is_int($value) && $value > 0) {
            return $value;
        }
        if (is_string($value) && preg_match('/^[1-9]\d*$/', $value) === 1) {
            return (int) $value;
        }

        throw new RuntimeException('Custom Tables stored execution confirmation integer field is invalid.');
    }

    /** @param array<string,mixed> $row */
    private function nullablePositiveInt(array $row, string $key): ?int
    {
        if (!array_key_exists($key, $row) || $row[$key] === null) {
            return null;
        }

        return $this->positiveInt($row, $key);
    }
}
