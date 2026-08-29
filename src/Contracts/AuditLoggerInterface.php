<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

use WPEssential\Platform\Audit\AuditRecord;

interface AuditLoggerInterface
{
    public function record(AuditRecord $record): void;
}
