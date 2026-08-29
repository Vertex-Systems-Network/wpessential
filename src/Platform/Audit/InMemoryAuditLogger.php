<?php

declare(strict_types=1);

namespace WPEssential\Platform\Audit;

use RuntimeException;
use WPEssential\Contracts\AuditLoggerInterface;

final class InMemoryAuditLogger implements AuditLoggerInterface
{
    /** @var array<string, AuditRecord> */
    private array $records = [];

    public function record(AuditRecord $record): void
    {
        if (isset($this->records[$record->id])) {
            throw new RuntimeException(sprintf('Audit record "%s" is already committed.', $record->id));
        }
        $this->records[$record->id] = $record;
    }

    /** @return list<AuditRecord> */
    public function all(): array
    {
        return array_values($this->records);
    }
}
