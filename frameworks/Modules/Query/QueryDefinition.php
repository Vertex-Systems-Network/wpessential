<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class QueryDefinition
{
    /**
     * @param array{uuid:string,key:string,name:string,revision:int,lifecycle:string} $identity
     * @param list<string> $projection
     * @param array<string,array<string,mixed>> $parameters
     * @param list<QueryOrderClause> $orderBy
     * @param array<string,mixed> $executionPolicy
     * @param array<string,mixed> $cachePolicy
     * @param array<string,mixed>|null $metadata
     */
    public function __construct(
        public array $identity,
        public int $astVersion,
        public QuerySourceReference $source,
        public string $operation,
        public array $projection,
        public array $parameters,
        public ?QueryPredicate $filter,
        public array $orderBy,
        public QueryPagination $pagination,
        public bool $distinct,
        public array $executionPolicy,
        public array $cachePolicy,
        public ?array $metadata = null,
    ) {
    }
}
