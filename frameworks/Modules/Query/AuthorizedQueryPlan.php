<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class AuthorizedQueryPlan
{
    public function __construct(
        public QueryProviderPlan $providerPlan,
        public string $ability,
        public string $capability,
        public ?string $resourceType,
        public string $policyReason,
        public ?QueryExecutionResult $shortCircuitResult = null,
    ) {
    }
}
