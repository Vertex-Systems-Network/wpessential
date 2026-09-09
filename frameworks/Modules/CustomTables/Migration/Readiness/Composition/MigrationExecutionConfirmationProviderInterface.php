<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Composition;

if (!defined('ABSPATH')) {
    exit;
}

interface MigrationExecutionConfirmationProviderInterface
{
    public function confirmationFor(string $runId): ?BoundMigrationExecutionConfirmation;
}
