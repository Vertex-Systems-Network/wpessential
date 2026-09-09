<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery\Provider;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Modules\CustomTables\Migration\Recovery\Binding\BoundRecoveryEvidence;

interface RecoveryVerificationProviderInterface
{
    public function verify(string $artifactId, string $planFingerprint): ?BoundRecoveryEvidence;
}
