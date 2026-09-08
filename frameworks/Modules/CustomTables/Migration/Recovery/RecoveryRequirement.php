<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class RecoveryRequirement
{
    public function __construct(
        public RecoveryClass $class,
        public bool $retainedCopyRequired = false,
        public bool $verifiedBackupRequired = false,
        public ?int $recoveryWindowHours = null,
    ) {
        if ($this->recoveryWindowHours !== null && $this->recoveryWindowHours < 1) {
            throw new InvalidArgumentException('Custom Tables recovery window must be positive.');
        }

        if ($this->class === RecoveryClass::ReversibleWhileRetainedCopyExists && !$this->retainedCopyRequired) {
            throw new InvalidArgumentException('Custom Tables retained-copy recovery class requires retained copy evidence.');
        }
        if ($this->class === RecoveryClass::VerifiedBackupRequired && !$this->verifiedBackupRequired) {
            throw new InvalidArgumentException('Custom Tables Backup recovery class requires verified Backup evidence.');
        }
        if ($this->class === RecoveryClass::IrreversibleAfterRecoveryWindow
            && (!$this->verifiedBackupRequired || $this->recoveryWindowHours === null)
        ) {
            throw new InvalidArgumentException('Custom Tables irreversible recovery class requires verified Backup and a recovery window.');
        }
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        return [
            'class' => $this->class->value,
            'retained_copy_required' => $this->retainedCopyRequired,
            'verified_backup_required' => $this->verifiedBackupRequired,
            'recovery_window_hours' => $this->recoveryWindowHours,
        ];
    }
}
