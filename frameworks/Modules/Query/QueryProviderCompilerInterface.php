<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

interface QueryProviderCompilerInterface
{
    public function supports(QueryDefinition $definition): bool;

    public function compile(QueryDefinition $definition): QueryProviderPlan;
}
