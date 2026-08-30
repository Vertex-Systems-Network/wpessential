<?php

declare(strict_types=1);

namespace WPEssential\Contracts;


if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Audit\AuditRecord;

interface AuditLoggerInterface
{
    public function record(AuditRecord $record): void;
}
