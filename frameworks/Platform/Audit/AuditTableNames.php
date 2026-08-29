<?php

declare(strict_types=1);

namespace WPEssential\Platform\Audit;

use RuntimeException;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class AuditTableNames
{
    public string $events;

    public function __construct(DatabaseAdapterInterface $database)
    {
        $prefix = $database->networkTablePrefix();
        if (preg_match('/^[A-Za-z0-9_]+$/', $prefix) !== 1) {
            throw new RuntimeException('Audit persistence table prefix is invalid.');
        }
        $this->events = $prefix . 'wpe_audit_events';
    }
}
