<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Authorization;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
use WPEssential\Modules\CustomTables\Migration\Readiness\MigrationExecutionReadinessDecision;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;

final readonly class MigrationExecutionAuthorizationDecision
{
    /** @param list<string> $reasons */
    private function __construct(
        public bool $authorized,
        public array $reasons,
    ) {
    }

    public static function evaluate(
        MigrationRun $run,
        MigrationExecutionReadinessDecision $readiness,
        MigrationExecutionAuthorizationRequest $request,
    ): self {
        $reasons = [];

        if (strtolower($request->runId) !== strtolower($run->id)) {
            $reasons[] = 'run_id_mismatch';
        }
        if ($request->readinessStateRevision !== $run->stateRevision) {
            $reasons[] = 'readiness_stale';
        }
        if (!$readiness->ready) {
            $reasons[] = 'readiness_not_ready';
        }
        if (!$request->capabilityAllowed) {
            $reasons[] = 'capability_denied';
        }
        if (!$request->confirmationProvided) {
            $reasons[] = 'confirmation_missing';
        }
        if (in_array($request->risk, [MigrationRisk::R3, MigrationRisk::R4], true)) {
            $reasons[] = 'unsupported_risk';
        }

        sort($reasons);

        return new self($reasons === [], $reasons);
    }

    /** @return array{authorized:bool,reasons:list<string>} */
    public function canonical(): array
    {
        return [
            'authorized' => $this->authorized,
            'reasons' => $this->reasons,
        ];
    }
}
