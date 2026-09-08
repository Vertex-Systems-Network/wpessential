<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final class StaticRecoveryEvidenceSource implements RecoveryEvidenceSourceInterface
{
    /** @var array<string,RecoveryEvidence> */
    private array $evidenceByPlan = [];

    /** @param list<RecoveryEvidence> $evidence */
    public function __construct(array $evidence = [])
    {
        foreach ($evidence as $item) {
            if (!$item instanceof RecoveryEvidence) {
                throw new InvalidArgumentException('Custom Tables recovery evidence source requires typed evidence.');
            }
            if (isset($this->evidenceByPlan[$item->planFingerprint])) {
                throw new InvalidArgumentException('Custom Tables recovery evidence source contains duplicate plan evidence.');
            }
            $this->evidenceByPlan[$item->planFingerprint] = $item;
        }
    }

    public function get(string $planFingerprint): ?RecoveryEvidence
    {
        if (preg_match('/^[a-f0-9]{64}$/', $planFingerprint) !== 1) {
            throw new InvalidArgumentException('Custom Tables recovery evidence lookup fingerprint is invalid.');
        }

        return $this->evidenceByPlan[$planFingerprint] ?? null;
    }
}
