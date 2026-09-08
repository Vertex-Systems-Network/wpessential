<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class RecoveryReadinessDecision
{
    /** @param list<string> $reasons */
    private function __construct(
        public bool $ready,
        public array $reasons,
    ) {
    }

    public static function evaluate(
        RecoveryRequirement $requirement,
        RecoveryEvidence $evidence,
        string $expectedPlanFingerprint,
    ): self {
        $reasons = [];

        if ($evidence->planFingerprint !== $expectedPlanFingerprint) {
            $reasons[] = 'plan_fingerprint_mismatch';
        }
        if ($evidence->class !== $requirement->class) {
            $reasons[] = 'recovery_class_mismatch';
        }
        if ($requirement->retainedCopyRequired && !$evidence->retainedCopyAvailable) {
            $reasons[] = 'retained_copy_missing';
        }
        if ($requirement->verifiedBackupRequired && !$evidence->verifiedBackupAvailable) {
            $reasons[] = 'verified_backup_missing';
        }
        if ($requirement->recoveryWindowHours !== null) {
            if (!$evidence->verifiedBackupAvailable || $evidence->backupAgeHours === null) {
                $reasons[] = 'backup_age_unavailable';
            } elseif ($evidence->backupAgeHours > $requirement->recoveryWindowHours) {
                $reasons[] = 'verified_backup_stale';
            }
        }

        return new self($reasons === [], $reasons);
    }

    /** @return array{ready:bool,reasons:list<string>} */
    public function canonical(): array
    {
        $reasons = $this->reasons;
        sort($reasons);

        return [
            'ready' => $this->ready,
            'reasons' => $reasons,
        ];
    }
}
