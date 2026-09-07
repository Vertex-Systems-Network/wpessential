<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class AdminColumnsEffectiveStateProjector
{
    public const CONTRACT_VERSION = 1;

    private const MAX_SOURCES = 100;
    private const MAX_PAGE_SIZE = 100;
    private const MAX_ROW_COUNT = 1000000;
    private const MAX_COUNTER = 100000;
    private const STATES = ['available', 'unavailable', 'provider_required', 'expensive', 'degraded'];
    private const CAPABILITY_KEYS = ['sort', 'filter', 'edit'];
    private const NODE_KEYS = [
        'state',
        'reason',
        'capabilities',
        'batchable',
        'provider_compatible',
        'storage_compatible',
    ];
    private const DIAGNOSTIC_KEYS = [
        'row_count',
        'page_size',
        'query_count',
        'remote_call_count',
        'cache_state',
    ];
    private const CACHE_STATES = ['hit', 'miss', 'unknown'];
    private const FORBIDDEN_KEY_PATTERN = '/(?:secret|token|password|credential|authorization|cookie|nonce|sql|stack|trace|private)/i';

    /**
     * Project bounded read-only effective and diagnostic state from evidence that
     * has already been supplied by canonical owners/adapters.
     *
     * This method performs no discovery, authorization, Query execution,
     * persistence or mutation. Availability is never an authorization grant.
     *
     * @param array<string,mixed> $targetEvidence
     * @param list<array<string,mixed>> $sourceEvidence
     * @param array<string,mixed> $diagnostics
     * @return array{
     *   contract_version:int,
     *   target:array<string,mixed>,
     *   sources:list<array<string,mixed>>,
     *   diagnostics:array<string,int|string>
     * }
     */
    public function project(array $targetEvidence, array $sourceEvidence, array $diagnostics): array
    {
        $this->assertNoForbiddenKeys($targetEvidence, 'Target evidence');
        $this->assertNoForbiddenKeys($sourceEvidence, 'Source evidence');
        $this->assertNoForbiddenKeys($diagnostics, 'Diagnostic evidence');

        $target = $this->node($targetEvidence, 'Target evidence');
        if (!array_is_list($sourceEvidence) || count($sourceEvidence) > self::MAX_SOURCES) {
            throw new InvalidArgumentException('Source evidence must be a bounded ordered list.');
        }

        $sources = [];
        $seenReferences = [];
        foreach ($sourceEvidence as $index => $candidate) {
            if (!is_array($candidate) || array_is_list($candidate)) {
                throw new InvalidArgumentException(sprintf('Source evidence %d must be an object/map.', $index));
            }
            $this->assertKnownKeys(
                $candidate,
                array_merge(['reference'], self::NODE_KEYS),
                sprintf('Source evidence %d', $index),
            );

            $reference = $candidate['reference'] ?? null;
            if (!is_string($reference) || preg_match('/^[a-z0-9][a-z0-9._:-]{0,191}$/', $reference) !== 1) {
                throw new InvalidArgumentException(sprintf('Source evidence %d reference is malformed.', $index));
            }
            if (isset($seenReferences[$reference])) {
                throw new InvalidArgumentException('Source evidence references must be unique.');
            }
            $seenReferences[$reference] = true;

            unset($candidate['reference']);
            $sources[] = ['reference' => $reference] + $this->node($candidate, sprintf('Source evidence %d', $index));
        }

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'target' => $target,
            'sources' => $sources,
            'diagnostics' => $this->diagnostics($diagnostics),
        ];
    }

    /** @param array<string,mixed> $evidence @return array<string,mixed> */
    private function node(array $evidence, string $label): array
    {
        $this->assertKnownKeys($evidence, self::NODE_KEYS, $label);

        $state = $evidence['state'] ?? null;
        if (!is_string($state) || !in_array($state, self::STATES, true)) {
            throw new InvalidArgumentException($label . ' state is unsupported.');
        }

        $reason = $evidence['reason'] ?? null;
        if ($reason !== null && (!is_string($reason) || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $reason) !== 1)) {
            throw new InvalidArgumentException($label . ' reason must be null or a bounded machine reason code.');
        }

        $capabilities = $evidence['capabilities'] ?? null;
        if (!is_array($capabilities) || array_is_list($capabilities)) {
            throw new InvalidArgumentException($label . ' capabilities must be an object/map.');
        }
        $this->assertKnownKeys($capabilities, self::CAPABILITY_KEYS, $label . ' capabilities');
        foreach (self::CAPABILITY_KEYS as $key) {
            if (!array_key_exists($key, $capabilities) || !is_bool($capabilities[$key])) {
                throw new InvalidArgumentException($label . ' capabilities must explicitly declare sort/filter/edit booleans.');
            }
        }

        $batchable = $evidence['batchable'] ?? null;
        $providerCompatible = $evidence['provider_compatible'] ?? null;
        $storageCompatible = $evidence['storage_compatible'] ?? null;
        if (!is_bool($batchable) || !is_bool($providerCompatible) || !is_bool($storageCompatible)) {
            throw new InvalidArgumentException($label . ' compatibility and batchability evidence must be boolean.');
        }

        $hasCapability = in_array(true, $capabilities, true);
        if ($state === 'available') {
            if ($reason !== null || !$providerCompatible || !$storageCompatible) {
                throw new InvalidArgumentException($label . ' available state contradicts its reason/provider/storage evidence.');
            }
        } elseif ($state === 'expensive') {
            if ($reason === null || !$providerCompatible || !$storageCompatible || !$hasCapability) {
                throw new InvalidArgumentException($label . ' expensive state requires a reason, compatible provider/storage and at least one available capability.');
            }
        } elseif ($state === 'unavailable') {
            if ($reason === null || $hasCapability || $batchable) {
                throw new InvalidArgumentException($label . ' unavailable state cannot advertise capabilities or batchability and requires a reason.');
            }
        } elseif ($state === 'provider_required') {
            if ($reason === null || $providerCompatible || $hasCapability || $batchable) {
                throw new InvalidArgumentException($label . ' provider_required state requires an incompatible/missing provider and cannot advertise capabilities or batchability.');
            }
        } elseif ($state === 'degraded') {
            if ($reason === null) {
                throw new InvalidArgumentException($label . ' degraded state requires a reason.');
            }
            if ($providerCompatible && $storageCompatible && $batchable && count(array_filter($capabilities)) === count(self::CAPABILITY_KEYS)) {
                throw new InvalidArgumentException($label . ' degraded state contradicts fully healthy supplied evidence.');
            }
        }

        return [
            'state' => $state,
            'reason' => $reason,
            'capabilities' => [
                'sort' => $capabilities['sort'],
                'filter' => $capabilities['filter'],
                'edit' => $capabilities['edit'],
            ],
            'batchable' => $batchable,
            'provider_compatible' => $providerCompatible,
            'storage_compatible' => $storageCompatible,
        ];
    }

    /** @param array<string,mixed> $diagnostics @return array<string,int|string> */
    private function diagnostics(array $diagnostics): array
    {
        $this->assertKnownKeys($diagnostics, self::DIAGNOSTIC_KEYS, 'Diagnostic evidence');
        foreach (self::DIAGNOSTIC_KEYS as $key) {
            if (!array_key_exists($key, $diagnostics)) {
                throw new InvalidArgumentException('Diagnostic evidence must explicitly declare every V1 diagnostic field.');
            }
        }

        $rowCount = $this->boundedInt($diagnostics['row_count'], 0, self::MAX_ROW_COUNT, 'Diagnostic row_count');
        $pageSize = $this->boundedInt($diagnostics['page_size'], 1, self::MAX_PAGE_SIZE, 'Diagnostic page_size');
        $queryCount = $this->boundedInt($diagnostics['query_count'], 0, self::MAX_COUNTER, 'Diagnostic query_count');
        $remoteCallCount = $this->boundedInt(
            $diagnostics['remote_call_count'],
            0,
            self::MAX_COUNTER,
            'Diagnostic remote_call_count',
        );
        $cacheState = $diagnostics['cache_state'];
        if (!is_string($cacheState) || !in_array($cacheState, self::CACHE_STATES, true)) {
            throw new InvalidArgumentException('Diagnostic cache_state is unsupported.');
        }

        return [
            'row_count' => $rowCount,
            'page_size' => $pageSize,
            'query_count' => $queryCount,
            'remote_call_count' => $remoteCallCount,
            'cache_state' => $cacheState,
        ];
    }

    private function boundedInt(mixed $value, int $minimum, int $maximum, string $label): int
    {
        if (!is_int($value) || $value < $minimum || $value > $maximum) {
            throw new InvalidArgumentException(sprintf('%s must be %d..%d.', $label, $minimum, $maximum));
        }
        return $value;
    }

    /** @param array<string,mixed> $map @param list<string> $allowed */
    private function assertKnownKeys(array $map, array $allowed, string $label): void
    {
        foreach (array_keys($map) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException($label . ' contains unsupported evidence.');
            }
        }
    }

    private function assertNoForbiddenKeys(mixed $value, string $label, int $depth = 0): void
    {
        if ($depth > 16) {
            throw new InvalidArgumentException($label . ' exceeds the bounded evidence depth.');
        }
        if (!is_array($value)) {
            return;
        }
        foreach ($value as $key => $child) {
            if (is_string($key) && preg_match(self::FORBIDDEN_KEY_PATTERN, $key) === 1) {
                throw new InvalidArgumentException($label . ' contains secret/private diagnostic evidence.');
            }
            if (is_array($child)) {
                $this->assertNoForbiddenKeys($child, $label, $depth + 1);
            }
        }
    }
}
