<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;

if (!defined('ABSPATH')) {
    exit;
}

enum JobAttemptState: string
{
    case Leased = 'leased';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Abandoned = 'abandoned';

    public function isTerminal(): bool
    {
        return $this !== self::Leased;
    }
}
