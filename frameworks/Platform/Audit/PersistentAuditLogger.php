<?php

declare(strict_types=1);

namespace WPEssential\Platform\Audit;

use RuntimeException;
use Throwable;
use WPEssential\Contracts\AuditLoggerInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class PersistentAuditLogger implements AuditLoggerInterface
{
    private readonly AuditTableNames $tables;

    public function __construct(
        private readonly DatabaseAdapterInterface $database,
        private readonly AuditRowCodec $codec = new AuditRowCodec(),
    ) {
        $this->tables = new AuditTableNames($database);
    }

    public function record(AuditRecord $record): void
    {
        try {
            $row = $this->codec->encode($record);
            $this->database->insert($this->tables->events, $row, [
                '%s', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s',
            ]);
        } catch (Throwable $exception) {
            throw new RuntimeException('Persistent audit append failed.', 0, $exception);
        }
    }
}
