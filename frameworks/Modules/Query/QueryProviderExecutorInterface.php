<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

interface QueryProviderExecutorInterface
{
    public function supports(QueryProviderPlan $plan): bool;

    public function execute(QueryProviderPlan $plan): QueryExecutionResult|QueryExecutionError;
}
