<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Execution;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use Throwable;

final class StatusTransitionObservationException extends RuntimeException
{
    public function __construct(
        public readonly StatusTransitionExecutionResult $committedResult,
        public readonly bool $auditRecorded,
        public readonly bool $eventDispatchCompleted,
        Throwable $previous,
    ) {
        parent::__construct(
            'Status transition is committed and verified, but post-commit Event/Audit observation failed. The original transition must not be retried.',
            0,
            $previous,
        );
    }

    public function mutationCommitted(): bool
    {
        return true;
    }

    public function retryMutationAllowed(): bool
    {
        return false;
    }
}
