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
        $this->assertSemanticReference($this->sourceRef, 'Listing Query source reference');
        if ($this->projection === [] || count($this->projection) > QueryReadConsumerInterface::MAX_PROJECTION_FIELDS) {
            throw new InvalidArgumentException('Listing Query projection must be non-empty and bounded.');
        }
        $seenProjection = [];
        foreach ($this->projection as $fieldRef) {
            $this->assertSemanticReference($fieldRef, 'Listing Query projection field');
            if (is_string($fieldRef) && isset($seenProjection[$fieldRef])) {
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
            if (array_key_exists($this->searchParameter, $this->filterParameters)) {
                throw new InvalidArgumentException('Listing Query search parameter must not collide with a filter parameter.');
            }
        }

        if (count($this->orderBy) > QueryReadConsumerInterface::MAX_ORDER_FIELDS) {
            throw new InvalidArgumentException('Listing Query order set exceeds the bounded V1 limit.');
        }
        $seenOrder = [];
        foreach ($this->orderBy as $order) {
            if (!is_array($order) || array_is_list($order)) {
                throw new InvalidArgumentException('Listing Query order entries must be object/maps.');
            }
            foreach (array_keys($order) as $key) {
                if (!is_string($key) || !in_array($key, ['field_ref', 'direction'], true)) {
                    throw new InvalidArgumentException('Listing Query order entries contain an unsupported key.');
                }
            }
            if (!array_key_exists('field_ref', $order) || !array_key_exists('direction', $order)) {
                throw new InvalidArgumentException('Listing Query order entries require field_ref and direction.');
            }
            $fieldRef = $order['field_ref'];
            $direction = $order['direction'];
            $this->assertSemanticReference($fieldRef, 'Listing Query order field');
            if (!is_string($direction) || !in_array($direction, ['asc', 'desc'], true)) {
                throw new InvalidArgumentException('Listing Query order direction must be asc or desc.');
            }
            if (is_string($fieldRef) && isset($seenOrder[$fieldRef])) {
                throw new InvalidArgumentException('Listing Query order fields must be unique.');
            }
            $seenOrder[$fieldRef] = true;
        }

        if ($this->pageSize < 1 || $this->pageSize > QueryReadConsumerInterface::MAX_PAGE_SIZE) {
            throw new InvalidArgumentException('Listing Query page size exceeds the Query consumer contract.');
        }
    }

    private function assertParameterName(mixed $parameter): void
    {
        if (!is_string($parameter) || !preg_match('/^[a-z][a-z0-9_]{0,63}$/', $parameter) || $parameter === 'offset') {
            throw new InvalidArgumentException('Listing Query public parameter name is unsupported or reserved.');
        }
    }

    private function assertSemanticReference(mixed $value, string $label): void
    {
        if (!is_string($value) || !preg_match('/^[a-z][a-z0-9._-]{0,127}$/', $value)) {
            throw new InvalidArgumentException($label . ' must match the canonical Query semantic reference format.');
        }
    }
}
