<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\QueryBinding;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class ListingQueryReader
{
    public function __construct(private QueryReadConsumerInterface $query)
    {
    }

    /** @param array<string,mixed> $parameters */
    public function read(ListingQueryBinding $binding, array $parameters, ExecutionContext $context): ListingQueryResultEnvelope
    {
        $description = $this->query->describe($binding->sourceRef, $context);
        $this->assertDescriptionCompatible($binding, $description);

        $allowedParameters = array_keys($binding->filterParameters);
        if ($binding->searchParameter !== null) {
            $allowedParameters[] = $binding->searchParameter;
        }
        $allowedParameters[] = 'offset';

        foreach (array_keys($parameters) as $parameter) {
            if (!is_string($parameter) || !in_array($parameter, $allowedParameters, true)) {
                throw new InvalidArgumentException('Listing Query received an undeclared public parameter.');
            }
        }

        $filters = [];
        foreach ($binding->filterParameters as $parameter => $fieldRef) {
            if (!array_key_exists($parameter, $parameters)) {
                continue;
            }
            $value = $parameters[$parameter];
            if (is_array($value) || is_object($value) || is_resource($value)) {
                throw new InvalidArgumentException('Listing Query filter parameters must be scalar or null.');
            }
            $filters[] = [
                'field_ref' => $fieldRef,
                'operator' => 'eq',
                'value' => $value,
            ];
        }

        $search = null;
        if ($binding->searchParameter !== null && array_key_exists($binding->searchParameter, $parameters)) {
            $candidate = $parameters[$binding->searchParameter];
            if (!is_string($candidate)) {
                throw new InvalidArgumentException('Listing Query search parameter must be a string.');
            }
            $candidate = trim($candidate);
            if ($candidate === '' || strlen($candidate) > QueryReadConsumerInterface::MAX_SEARCH_LENGTH) {
                throw new InvalidArgumentException('Listing Query search parameter is outside the Query consumer bounds.');
            }
            $search = $candidate;
        }

        $offset = $parameters['offset'] ?? 0;
        if (!is_int($offset) || $offset < 0 || $offset > QueryReadConsumerInterface::MAX_OFFSET) {
            throw new InvalidArgumentException('Listing Query offset exceeds the Query consumer contract.');
        }

        $result = $this->query->read([
            'contract_version' => QueryReadConsumerInterface::CONTRACT_VERSION,
            'source_ref' => $binding->sourceRef,
            'projection' => $binding->projection,
            'filters' => $filters,
            'search' => $search,
            'order_by' => $binding->orderBy,
            'page_size' => $binding->pageSize,
            'offset' => $offset,
        ], $context);

        if (($result['ok'] ?? false) !== true) {
            $error = is_array($result['error'] ?? null) ? $result['error'] : [];
            return new ListingQueryResultEnvelope(
                ok: false,
                sourceRef: is_string($result['source_ref'] ?? null) ? $result['source_ref'] : $binding->sourceRef,
                projection: [],
                rows: [],
                returned: 0,
                errorCode: is_string($error['code'] ?? null) ? $error['code'] : 'wpe_query_unknown_failure',
                errorPath: is_string($error['path'] ?? null) ? $error['path'] : '$',
                errorMessage: is_string($error['message'] ?? null) ? $error['message'] : 'Query execution failed.',
            );
        }

        $projection = $result['projection'] ?? null;
        $rows = $result['rows'] ?? null;
        $returned = $result['returned'] ?? null;
        if (
            !is_array($projection)
            || !array_is_list($projection)
            || $projection !== $binding->projection
            || !is_array($rows)
            || !array_is_list($rows)
            || !is_int($returned)
            || $returned !== count($rows)
            || $returned > $binding->pageSize
            || ($result['source_ref'] ?? null) !== $binding->sourceRef
        ) {
            return new ListingQueryResultEnvelope(
                ok: false,
                sourceRef: $binding->sourceRef,
                projection: [],
                rows: [],
                returned: 0,
                errorCode: 'wpe_listings_query_contract_mismatch',
                errorPath: '$.result',
                errorMessage: 'Query result did not match the declared Listing binding.',
            );
        }

        foreach ($rows as $row) {
            if (!is_array($row) || array_is_list($row)) {
                return new ListingQueryResultEnvelope(
                    ok: false,
                    sourceRef: $binding->sourceRef,
                    projection: [],
                    rows: [],
                    returned: 0,
                    errorCode: 'wpe_listings_query_contract_mismatch',
                    errorPath: '$.rows',
                    errorMessage: 'Query rows must be object/maps for Listing consumption.',
                );
            }
        }

        /** @var list<array<string,mixed>> $rows */
        return new ListingQueryResultEnvelope(
            ok: true,
            sourceRef: $binding->sourceRef,
            projection: $binding->projection,
            rows: $rows,
            returned: $returned,
        );
    }

    /** @param array<string,mixed> $description */
    private function assertDescriptionCompatible(ListingQueryBinding $binding, array $description): void
    {
        if (($description['contract_version'] ?? null) !== QueryReadConsumerInterface::CONTRACT_VERSION) {
            throw new InvalidArgumentException('Listing Query source contract version is incompatible.');
        }
        if (($description['source_ref'] ?? null) !== $binding->sourceRef || ($description['available'] ?? false) !== true) {
            throw new InvalidArgumentException('Listing Query source is unavailable or mismatched.');
        }
        $fieldSchema = $description['field_schema'] ?? null;
        if (!is_array($fieldSchema)) {
            throw new InvalidArgumentException('Listing Query source does not expose a field schema.');
        }
        foreach (array_merge($binding->projection, array_values($binding->filterParameters), array_column($binding->orderBy, 'field_ref')) as $fieldRef) {
            if (!is_string($fieldRef) || !array_key_exists($fieldRef, $fieldSchema)) {
                throw new InvalidArgumentException('Listing Query binding references a field outside the source schema.');
            }
        }
        $maxPageSize = $description['max_page_size'] ?? null;
        if (!is_int($maxPageSize) || $binding->pageSize > $maxPageSize) {
            throw new InvalidArgumentException('Listing Query page size exceeds the source capability.');
        }
    }
}
