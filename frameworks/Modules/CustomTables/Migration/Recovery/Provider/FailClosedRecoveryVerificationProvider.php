<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery\Provider;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\CustomTables\Migration\Recovery\Binding\BoundRecoveryEvidence;

final class FailClosedRecoveryVerificationProvider implements TrustedRecoveryVerificationProviderInterface
{
    public function verify(string $artifactId, string $planFingerprint): ?BoundRecoveryEvidence
    {
        if (preg_match('/^[a-z0-9][a-z0-9._:-]{2,127}$/', $artifactId) !== 1) {
            throw new InvalidArgumentException('Custom Tables recovery artifact id is invalid.');
        }
        if (preg_match('/^[a-f0-9]{64}$/', $planFingerprint) !== 1) {
            throw new InvalidArgumentException('Custom Tables recovery verification plan fingerprint is invalid.');
        }

        return null;
    }
}
