<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs\Adapters\ActionScheduler;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;

final readonly class ActionSchedulerBackend
{
    public const DISPATCH_HOOK = 'wpessential/hook_job_dispatch';
    public const GROUP = 'wpessential-jobs';

    public function __construct(private ActionSchedulerBackendEnvironmentInterface $environment) {}

    public function enqueueJob(string $jobId): int
    {
        $args = $this->args($jobId);
        $actionId = $this->environment->enqueueAsync(self::DISPATCH_HOOK, $args, self::GROUP, true);

        if ($actionId > 0) {
            return $actionId;
        }

        $existing = $this->environment->firstScheduledActionId(self::DISPATCH_HOOK, $args, self::GROUP);
        if ($existing !== null) {
            return $existing;
        }

        throw new RuntimeException('Action Scheduler did not materialize the WPEssential job action.');
    }

    public function scheduleJobAt(string $jobId, int $timestamp): int
    {
        if ($timestamp <= 0) {
            throw new InvalidArgumentException('Action Scheduler timestamp must be positive.');
        }

        $args = $this->args($jobId);
        $actionId = $this->environment->scheduleSingle($timestamp, self::DISPATCH_HOOK, $args, self::GROUP, true);

        if ($actionId > 0) {
            return $actionId;
        }

        $existing = $this->environment->firstScheduledActionId(self::DISPATCH_HOOK, $args, self::GROUP);
        if ($existing !== null) {
            return $existing;
        }

        throw new RuntimeException('Action Scheduler did not materialize the scheduled WPEssential job action.');
    }

    public function hasJob(string $jobId): bool
    {
        return $this->environment->firstScheduledActionId(
            self::DISPATCH_HOOK,
            $this->args($jobId),
            self::GROUP,
        ) !== null;
    }

    /** @return list<int> */
    public function actionIds(string $jobId): array
    {
        return $this->environment->scheduledActionIds(
            self::DISPATCH_HOOK,
            $this->args($jobId),
            self::GROUP,
        );
    }

    public function cancelJob(string $jobId): ?int
    {
        return $this->environment->unschedule(
            self::DISPATCH_HOOK,
            $this->args($jobId),
            self::GROUP,
        );
    }

    /** @return array{job_id:string} */
    private function args(string $jobId): array
    {
        $jobId = strtolower(trim($jobId));
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $jobId) !== 1) {
            throw new InvalidArgumentException('WPEssential Job ID must be a canonical UUIDv4.');
        }

        return ['job_id' => $jobId];
    }
}
