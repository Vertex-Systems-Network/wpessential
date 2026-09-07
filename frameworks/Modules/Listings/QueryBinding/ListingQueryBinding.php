<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\QueryBinding;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\QueryReadConsumerInterface;

final readonly class ListingQueryBinding
{
    /**
     * @param list<string> $projection
     * @param array<string,string> $filterParameters public parameter => Query field_ref
     * @param list<array{field_ref:string,direction:string}> $orderBy
     */
    public function __construct(
        public string $sourceRef,
        public array $projection,
        public array $filterParameters = [],
        public ?string $searchParameter = null,
        public array $orderBy = [],
        public int $pageSize = 20,
    ) {
        if (!preg_match('/^[a-z][a-z0-9_.:-]{0,159}$/', $this->sourceRef)) {
            throw new InvalidArgumentException('Listing Query source reference must be bounded and semantic.');
        }
        if ($this->projection === [] || count($this->projection) > QueryReadConsumerInterface::MAX_PROJECTION_FIELDS) {
            throw new InvalidArgumentException('Listing Query projection must be non-empty and bounded.');
        }
        $seenProjection = [];
        foreach ($this->projection as $fieldRef) {
            $this->assertSemanticReference($fieldRef, 'Listing Query projection field');
            if (isset($seenProjection[$fieldRef])) {
                throw new InvalidArgumentException('Listing Query projection fields must be unique.');
            }
            $seenProjection[$fieldRef] = true;
        }

        if (count($this->filterParameters) > QueryReadConsumerInterface::MAX_FILTERS) {
            throw new InvalidArgumentException('Listing Query filter parameter map exceeds the bounded V1 limit.');
        }
        foreach ($this->filterParameters as $parameter => $fieldRef) {
            $this->assertParameterName($parameter);
            $this->assertSemanticReference($fieldRef, 'Listing Query filter field');
        }

        if ($this->searchParameter !== null) {
            $this->assertParameterName($this->searchParameter);
            if (array_key_exists($this->searchParameter, $this->filterParameters) || $this->searchParameter === 'offset') {
                throw new InvalidArgumentException('Listing Query search parameter must not collide with another public parameter.');
            }
        }

        if (count($this->orderBy) > QueryReadConsumerInterface::MAX_ORDER_FIELDS) {
            throw new InvalidArgumentException('Listing Query order set exceeds the bounded V1 limit.');
        }
        $seenOrder = [];
        foreach ($this->orderBy as $order) {
            if (!is_array($order) || array_keys($order) !== ['field_ref', 'direction']) {
                throw new InvalidArgumentException('Listing Query order entries must use field_ref and direction only.');
            }
            $fieldRef = $order['field_ref'];
            $direction = $order['direction'];
            $this->assertSemanticReference($fieldRef, 'Listing Query order field');
            if (!in_array($direction, ['asc', 'desc'], true)) {
                throw new InvalidArgumentException('Listing Query order direction must be asc or desc.');
            }
            if (isset($seenOrder[$fieldRef])) {
                throw new InvalidArgumentException('Listing Query order fields must be unique.');
            }
            $seenOrder[$fieldRef] = true;
        }

        if ($this->pageSize < 1 || $this->pageSize > QueryReadConsumerInterface::MAX_PAGE_SIZE) {
            throw new InvalidArgumentException('Listing Query page size exceeds the Query consumer contract.');
        }
    }

    private function assertParameterName(string $parameter): void
    {
        if (!preg_match('/^[a-z][a-z0-9_]{0,63}$/', $parameter) || $parameter === 'offset') {
            throw new InvalidArgumentException('Listing Query public parameter name is unsupported or reserved.');
        }
    }

    private function assertSemanticReference(mixed $value, string $label): void
    {
        if (!is_string($value) || !preg_match('/^[a-z][a-z0-9_.:-]{0,159}$/', $value)) {
            throw new InvalidArgumentException($label . ' must be a bounded semantic reference.');
        }
    }
}
