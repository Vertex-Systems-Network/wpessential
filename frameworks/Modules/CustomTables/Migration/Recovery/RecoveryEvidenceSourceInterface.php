<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery;

if (!defined('ABSPATH')) {
    exit;
}

interface RecoveryEvidenceSourceInterface
{
    public function get(string $planFingerprint): ?RecoveryEvidence;
}
