<?php

declare(strict_types=1);

namespace WPEssential\Platform\DataSources;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;

final readonly class DataSourceDescriptor
{
    private const IDENTIFIER_PATTERN = '/^[a-z][a-z0-9._-]{1,127}$/';
    private const FIELD_PATTERN = '/^[a-z][a-z0-9._-]{0,127}$/';
    private const ALLOWED_SCOPES = ['site', 'network', 'user', 'resource'];

    /**
     * @param array<string,string> $fieldSchema Stable semantic field reference => logical type.
     * @param list<string> $predicates
     * @param list<string> $sortModes
     * @param list<string> $paginationModes
     * @param list<string> $aggregationModes
     * @param list<string> $scopes
     * @param list<string> $cacheGenerationKeys
     */
    public function __construct(
        public string $id,
        public string $sourceType,
        public int $capabilityVersion,
        public array $fieldSchema,
        public array $predicates = [],
        public array $sortModes = [],
        public array $paginationModes = [],
        public array $aggregationModes = [],
        public bool $supportsRelations = false,
        public bool $policyRequired = true,
        public array $scopes = ['site'],
        public int $maxPageSize = 100,
        public int $maxBatchSize = 100,
        public bool $cacheable = false,
        public array $cacheGenerationKeys = [],
        public bool $diagnosticsAvailable = false,
        public DataSourceAvailability $availability = DataSourceAvailability::Available,
        public ?string $degradedReason = null,
        public ?DataSourceAuthorizationMapping $authorization = null,
    ) {
        $this->assertIdentifier($this->id, 'Data Source id');
        $this->assertIdentifier($this->sourceType, 'Data Source type');
        if ($this->capabilityVersion < 1) {
            throw new InvalidArgumentException('Data Source capability version must be positive.');
        }
        if ($this->fieldSchema === []) {
            throw new InvalidArgumentException('Data Source field schema cannot be empty.');
        }
        foreach ($this->fieldSchema as $fieldRef => $logicalType) {
            if (!is_string($fieldRef) || preg_match(self::FIELD_PATTERN, $fieldRef) !== 1) {
                throw new InvalidArgumentException('Data Source field references must be stable lowercase semantic identifiers.');
            }
            if (!is_string($logicalType)) {
                throw new InvalidArgumentException('Data Source logical field types must be strings.');
            }
            $this->assertIdentifier($logicalType, 'Data Source logical field type');
        }

        $this->assertIdentifierList($this->predicates, 'predicate');
        $this->assertIdentifierList($this->sortModes, 'sort mode');
        $this->assertIdentifierList($this->paginationModes, 'pagination mode');
        $this->assertIdentifierList($this->aggregationModes, 'aggregation mode');
        $this->assertIdentifierList($this->cacheGenerationKeys, 'cache generation key');

        if (!$this->policyRequired) {
            throw new InvalidArgumentException('Data Source descriptors must require canonical Policy authorization.');
        }
        if ($this->scopes === []) {
            throw new InvalidArgumentException('Data Source descriptors must declare at least one scope.');
        }
        $seenScopes = [];
        foreach ($this->scopes as $scope) {
            if (!is_string($scope) || !in_array($scope, self::ALLOWED_SCOPES, true)) {
                throw new InvalidArgumentException('Data Source scope must be site, network, user, or resource.');
            }
            if (isset($seenScopes[$scope])) {
                throw new InvalidArgumentException('Data Source scopes must be unique.');
            }
            $seenScopes[$scope] = true;
        }
        if ($this->maxPageSize < 1 || $this->maxBatchSize < 1) {
            throw new InvalidArgumentException('Data Source page and batch limits must be positive.');
        }
        if (!$this->cacheable && $this->cacheGenerationKeys !== []) {
            throw new InvalidArgumentException('Non-cacheable Data Sources cannot declare cache generation keys.');
        }
        if ($this->availability === DataSourceAvailability::Degraded) {
            if (!is_string($this->degradedReason) || trim($this->degradedReason) === '') {
                throw new InvalidArgumentException('Degraded Data Sources must declare a reason.');
            }
        } elseif ($this->degradedReason !== null) {
            throw new InvalidArgumentException('Available Data Sources cannot declare a degraded reason.');
        }
    }

    public function isAvailable(): bool
    {
        return $this->availability === DataSourceAvailability::Available;
    }

    public function hasAuthorizationMapping(): bool
    {
        return $this->authorization !== null;
    }

    public function requireAuthorizationMapping(): DataSourceAuthorizationMapping
    {
        return $this->authorization ?? throw new RuntimeException(sprintf(
            'Data Source "%s" has no canonical Policy authorization mapping.',
            $this->id,
        ));
    }

    private function assertIdentifier(string $value, string $label): void
    {
        if (preg_match(self::IDENTIFIER_PATTERN, $value) !== 1) {
            throw new InvalidArgumentException($label . ' must be a stable lowercase semantic identifier.');
        }
    }

    /** @param list<string> $values */
    private function assertIdentifierList(array $values, string $label): void
    {
        $seen = [];
        foreach ($values as $value) {
            if (!is_string($value)) {
                throw new InvalidArgumentException(sprintf('Data Source %s values must be strings.', $label));
            }
            $this->assertIdentifier($value, 'Data Source ' . $label);
            if (isset($seen[$value])) {
                throw new InvalidArgumentException(sprintf('Data Source %s values must be unique.', $label));
            }
            $seen[$value] = true;
        }
    }
}
