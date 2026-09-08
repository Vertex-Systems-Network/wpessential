<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class RecoveryEvidence
{
    public function __construct(
        public string $planFingerprint,
        public RecoveryClass $class,
        public bool $retainedCopyAvailable = false,
        public bool $verifiedBackupAvailable = false,
        public ?int $backupAgeHours = null,
    ) {
        if (preg_match('/^[a-f0-9]{64}$/', $this->planFingerprint) !== 1) {
            throw new InvalidArgumentException('Custom Tables recovery evidence plan fingerprint is invalid.');
        }
        if ($this->backupAgeHours !== null && $this->backupAgeHours < 0) {
            throw new InvalidArgumentException('Custom Tables recovery evidence Backup age cannot be negative.');
        }
        if (!$this->verifiedBackupAvailable && $this->backupAgeHours !== null) {
            throw new InvalidArgumentException('Custom Tables recovery evidence Backup age requires verified Backup availability.');
        }
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        return [
            'plan_fingerprint' => $this->planFingerprint,
            'class' => $this->class->value,
            'retained_copy_available' => $this->retainedCopyAvailable,
            'verified_backup_available' => $this->verifiedBackupAvailable,
            'backup_age_hours' => $this->backupAgeHours,
        ];
    }
}
