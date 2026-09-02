<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class QueryValidationBudget
{
    public function __construct(
        public int $maxAstBytes,
        public int $maxGroupDepth,
        public int $maxPredicateCount,
        public int $maxInListSize,
        public int $maxPageSize,
        public int $maxRelationDepth,
    ) {
        foreach (get_object_vars($this) as $name => $value) {
            if (!is_int($value) || $value < 1) {
                throw new InvalidArgumentException(sprintf('Query validation budget %s must be a positive integer.', $name));
            }
        }
    }
}
