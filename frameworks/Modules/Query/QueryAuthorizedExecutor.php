<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class QueryAuthorizedExecutor
{
    public function __construct(
        private QueryAuthorizedPlanner $planner,
        private QueryProviderExecutorInterface $providerExecutor,
    ) {
    }

    public function execute(
        QueryDefinition $definition,
        ExecutionContext $context,
    ): QueryExecutionResult|QueryExecutionError {
        try {
            $authorized = $this->planner->plan($definition, $context);
        } catch (QueryPlanningException|QueryProviderCompilationException $exception) {
            return new QueryExecutionError(
                errorCode: $exception->errorCode,
                path: $exception->path,
                message: $exception->getMessage(),
            );
        } catch (Throwable) {
            return new QueryExecutionError(
                errorCode: 'wpe_query_provider_failed',
                path: '$.source',
                message: 'Authorized Query planning failed before provider execution.',
            );
        }

        if (!$this->providerExecutor->supports($authorized->providerPlan)) {
            return new QueryExecutionError(
                errorCode: 'wpe_query_dependency_unavailable',
                path: '$.source',
                message: 'Authorized provider plan has no registered execution adapter.',
            );
        }

        try {
            return $this->providerExecutor->execute($authorized->providerPlan);
        } catch (Throwable) {
            return new QueryExecutionError(
                errorCode: 'wpe_query_provider_failed',
                path: '$.source',
                message: 'Provider execution failed without a normalized result.',
            );
        }
    }
}
