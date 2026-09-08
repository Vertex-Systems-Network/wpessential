<?php

declare(strict_types=1);

namespace WPEssential\Platform\Audit;

if (!defined('ABSPATH')) {
    exit;
}

final class AuditServices
{
    public const LOGGER = 'platform.audit';

    private function __construct() {}
}
