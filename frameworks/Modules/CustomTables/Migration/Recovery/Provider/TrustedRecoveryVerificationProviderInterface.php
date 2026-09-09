<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery\Provider;

if (!defined('ABSPATH')) {
    exit;
}

interface TrustedRecoveryVerificationProviderInterface extends RecoveryVerificationProviderInterface
{
}
