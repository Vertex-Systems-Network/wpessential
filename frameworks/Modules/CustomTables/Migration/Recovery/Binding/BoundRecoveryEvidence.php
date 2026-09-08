<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery\Binding;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryEvidence;

final readonly class BoundRecoveryEvidence
{
    public function __construct(
        public RecoveryEvidence $evidence,
        public string $provider,
        public string $providerVersion,
        public int $observedAgeHours,
    ) {
        if (!in_array($this->provider, ['mysql', 'mariadb'], true)) {
            throw new InvalidArgumentException('Custom Tables bound recovery evidence provider is unsupported.');
        }
        if (preg_match('/^\d+\.\d+\.\d+$/', $this->providerVersion) !== 1) {
            throw new InvalidArgumentException('Custom Tables bound recovery evidence provider version is invalid.');
        }
        if ($this->observedAgeHours < 0) {
            throw new InvalidArgumentException('Custom Tables bound recovery evidence age cannot be negative.');
        }
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        return [
            'evidence' => $this->evidence->canonical(),
            'provider' => $this->provider,
            'provider_version' => $this->providerVersion,
            'observed_age_hours' => $this->observedAgeHours,
        ];
    }
}
