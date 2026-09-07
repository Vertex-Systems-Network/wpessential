<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\State;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryBinding;

final class ListingPublicStateCodec
{
    /**
     * @param array<string,mixed> $input
     */
    public function normalize(ListingQueryBinding $binding, string $namespace, array $input): ListingPublicState
    {
        if (!preg_match('/^[a-z][a-z0-9_-]{0,31}$/', $namespace)) {
            throw new InvalidArgumentException('Listing public-state namespace is invalid.');
        }

        $prefix = 'wpe_' . $namespace . '_';
        $declared = array_keys($binding->filterParameters);
        if ($binding->searchParameter !== null) {
            $declared[] = $binding->searchParameter;
        }
        $declared[] = 'offset';

        foreach (array_keys($input) as $key) {
            if (!is_string($key) || !str_starts_with($key, $prefix)) {
                continue;
            }
            $parameter = substr($key, strlen($prefix));
            if (!in_array($parameter, $declared, true)) {
                throw new InvalidArgumentException('Listing public state contains an undeclared namespaced parameter.');
            }
        }

        $parameters = [];
        $encoded = [];

        foreach (array_keys($binding->filterParameters) as $parameter) {
            $key = $prefix . $parameter;
            if (!array_key_exists($key, $input)) {
                continue;
            }
            $value = $input[$key];
            if ($value === null || $value === '') {
                continue;
            }
            if (!is_scalar($value)) {
                throw new InvalidArgumentException('Listing public filter values must be scalar.');
            }
            if (is_string($value) && strlen($value) > 512) {
                throw new InvalidArgumentException('Listing public filter value exceeds the bounded V1 limit.');
            }
            $parameters[$parameter] = $value;
            $encoded[$key] = $this->encodeScalar($value);
        }

        if ($binding->searchParameter !== null) {
            $parameter = $binding->searchParameter;
            $key = $prefix . $parameter;
            if (array_key_exists($key, $input) && $input[$key] !== null) {
                if (!is_string($input[$key])) {
                    throw new InvalidArgumentException('Listing public search value must be a string.');
                }
                $search = trim($input[$key]);
                if ($search !== '') {
                    if (strlen($search) > QueryReadConsumerInterface::MAX_SEARCH_LENGTH) {
                        throw new InvalidArgumentException('Listing public search value exceeds the Query contract.');
                    }
                    $parameters[$parameter] = $search;
                    $encoded[$key] = $search;
                }
            }
        }

        $offsetKey = $prefix . 'offset';
        if (array_key_exists($offsetKey, $input) && $input[$offsetKey] !== null && $input[$offsetKey] !== '') {
            $offset = $this->parseOffset($input[$offsetKey]);
            if ($offset > 0) {
                $parameters['offset'] = $offset;
                $encoded[$offsetKey] = (string) $offset;
            }
        }

        return new ListingPublicState(
            namespace: $namespace,
            parameters: $parameters,
            queryString: http_build_query($encoded, '', '&', PHP_QUERY_RFC3986),
        );
    }

    private function parseOffset(mixed $value): int
    {
        if (is_int($value)) {
            $offset = $value;
        } elseif (is_string($value) && preg_match('/^(?:0|[1-9][0-9]{0,4})$/', $value)) {
            $offset = (int) $value;
        } else {
            throw new InvalidArgumentException('Listing public offset must be a canonical non-negative integer.');
        }

        if ($offset < 0 || $offset > QueryReadConsumerInterface::MAX_OFFSET) {
            throw new InvalidArgumentException('Listing public offset exceeds the Query contract.');
        }

        return $offset;
    }

    private function encodeScalar(string|int|float|bool $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        return (string) $value;
    }
}
