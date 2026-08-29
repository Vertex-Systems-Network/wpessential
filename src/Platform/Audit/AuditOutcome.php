<?php

declare(strict_types=1);

namespace WPEssential\Platform\Audit;

enum AuditOutcome: string
{
    case Success = 'success';
    case Denied = 'denied';
    case Failed = 'failed';
    case Partial = 'partial';
    case Unknown = 'unknown';
}
