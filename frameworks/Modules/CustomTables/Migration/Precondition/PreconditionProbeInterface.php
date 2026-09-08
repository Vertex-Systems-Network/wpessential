<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition;

if (!defined('ABSPATH')) {
    exit;
}

interface PreconditionProbeInterface
{
    public function evaluate(PreconditionRequirement $requirement): PreconditionEvaluation;
}
