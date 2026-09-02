<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class QueryValidationResult
{
    /** @param list<QueryValidationIssue> $issues */
    public function __construct(
        public ?QueryDefinition $definition,
        public array $issues,
    ) {
    }

    public function isValid(): bool
    {
        return $this->definition !== null && $this->issues === [];
    }
}
