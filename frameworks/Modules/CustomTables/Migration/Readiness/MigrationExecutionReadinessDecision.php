<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionOutcome;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionReport;
use WPEssential\Modules\CustomTables\Migration\ProviderMigrationPreview;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationRevalidationDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryReadinessDecision;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;

final readonly class MigrationExecutionReadinessDecision
{
    /** @param list<string> $reasons */
    private function __construct(
        public bool $ready,
        public array $reasons,
    ) {
    }

    public static function evaluate(
        MigrationRun $run,
        PreconditionReport $preconditions,
        MigrationRevalidationDecision $revalidation,
        RecoveryReadinessDecision $recovery,
        ProviderMigrationPreview $preview,
    ): self {
        $reasons = [];

        if ($run->state !== MigrationRunState::Revalidating) {
            $reasons[] = 'run_not_revalidating';
        }
        if ($preconditions->outcome() !== PreconditionOutcome::Satisfied) {
            $reasons[] = 'preconditions_not_satisfied';
        }
        if (!$revalidation->allowed) {
            $reasons[] = 'revalidation_failed';
        }
        if (!$recovery->ready) {
            $reasons[] = 'recovery_not_ready';
        }
        if ($preview->planFingerprint !== $run->planFingerprint) {
            $reasons[] = 'preview_plan_fingerprint_mismatch';
        }
        if ($preview->isNoOp()) {
            $reasons[] = 'preview_has_no_statements';
        }

        sort($reasons);

        return new self($reasons === [], $reasons);
    }

    /** @return array{ready:bool,reasons:list<string>} */
    public function canonical(): array
    {
        return [
            'ready' => $this->ready,
            'reasons' => $this->reasons,
        ];
    }
}
