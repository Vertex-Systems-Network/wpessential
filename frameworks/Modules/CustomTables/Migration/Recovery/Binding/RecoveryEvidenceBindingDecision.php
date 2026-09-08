<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery\Binding;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;

final readonly class RecoveryEvidenceBindingDecision
{
    /** @param list<string> $reasons */
    private function __construct(
        public bool $valid,
        public array $reasons,
    ) {
    }

    public static function evaluate(
        MigrationGenerationStamp $reviewed,
        ?BoundRecoveryEvidence $boundEvidence,
        int $maxEvidenceAgeHours,
    ): self {
        if ($maxEvidenceAgeHours < 0) {
            throw new InvalidArgumentException('Custom Tables recovery evidence maximum age cannot be negative.');
        }

        if ($boundEvidence === null) {
            return new self(false, ['missing_evidence']);
        }

        $reasons = [];

        if ($boundEvidence->evidence->planFingerprint !== $reviewed->planFingerprint) {
            $reasons[] = 'plan_fingerprint_mismatch';
        }
        if ($boundEvidence->provider !== $reviewed->provider) {
            $reasons[] = 'provider_mismatch';
        }
        if ($boundEvidence->providerVersion !== $reviewed->providerVersion) {
            $reasons[] = 'provider_version_mismatch';
        }
        if ($boundEvidence->observedAgeHours > $maxEvidenceAgeHours) {
            $reasons[] = 'evidence_stale';
        }

        sort($reasons);

        return new self($reasons === [], $reasons);
    }

    /** @return array{valid:bool,reasons:list<string>} */
    public function canonical(): array
    {
        return [
            'valid' => $this->valid,
            'reasons' => $this->reasons,
        ];
    }
}
