<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Run;

if (!defined('ABSPATH')) {
    exit;
}

enum MigrationRunState: string
{
    case Planned = 'planned';
    case AwaitingReview = 'awaiting_review';
    case BlockedPrecondition = 'blocked_precondition';
    case Approved = 'approved';
    case Queued = 'queued';
    case Revalidating = 'revalidating';
    case Running = 'running';
    case PausedSafePoint = 'paused_safe_point';
    case Verifying = 'verifying';
    case Applied = 'applied';
    case AppliedWithWarnings = 'applied_with_warnings';
    case FailedRecoverable = 'failed_recoverable';
    case FailedRecoveryRequired = 'failed_recovery_required';
    case Superseded = 'superseded';
    case CancelledBeforeMutation = 'cancelled_before_mutation';

    public function terminal(): bool
    {
        return in_array($this, [
            self::Applied,
            self::AppliedWithWarnings,
            self::FailedRecoverable,
            self::FailedRecoveryRequired,
            self::Superseded,
            self::CancelledBeforeMutation,
        ], true);
    }
}
