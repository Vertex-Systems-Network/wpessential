<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery;

if (!defined('ABSPATH')) {
    exit;
}

enum RecoveryClass: string
{
    case TriviallyReversible = 'trivially_reversible';
    case ReversibleWhileRetainedCopyExists = 'reversible_while_retained_copy_exists';
    case VerifiedBackupRequired = 'verified_backup_required';
    case IrreversibleAfterRecoveryWindow = 'irreversible_after_recovery_window';
}
