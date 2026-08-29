<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;

enum JobState: string
{
    case Scheduled = 'scheduled';
    case Available = 'available';
    case Blocked = 'blocked';
    case Running = 'running';
    case RetryWait = 'retry_wait';
    case CancelRequested = 'cancel_requested';
    case Cancelled = 'cancelled';
    case Succeeded = 'succeeded';
    case FailedFinal = 'failed_final';
    case Expired = 'expired';
    case OrphanedAdapter = 'orphaned_adapter';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Cancelled, self::Succeeded, self::FailedFinal, self::Expired], true);
    }
}
