<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\Portability;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class ListingPortablePackage
{
    /** @param array<string,mixed> $payload */
    public function __construct(
        public string $definitionId,
        public int $revision,
        public int $schemaVersion,
        public array $payload,
        public string $querySourceRef,
        public string $blueprintId,
        public int $blueprintRevision,
        public int $sourceSiteId,
        public ?int $sourceNetworkId = null,
    ) {
        if ($this->revision < 1 || $this->schemaVersion < 1 || $this->blueprintRevision < 1 || $this->sourceSiteId < 1) {
            throw new InvalidArgumentException('Portable Listing numeric metadata must be positive.');
        }
        if ($this->sourceNetworkId !== null && $this->sourceNetworkId < 1) {
            throw new InvalidArgumentException('Portable Listing source network id must be positive when provided.');
        }
        if (array_is_list($this->payload)) {
            throw new InvalidArgumentException('Portable Listing payload must remain an object/map.');
        }
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->definitionId)) {
            throw new InvalidArgumentException('Portable Listing definition id is invalid.');
        }
        if (!preg_match('/^[a-z][a-z0-9._-]{0,127}$/', $this->querySourceRef)) {
            throw new InvalidArgumentException('Portable Listing Query source reference is invalid.');
        }
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->blueprintId)) {
            throw new InvalidArgumentException('Portable Listing Blueprint id is invalid.');
        }
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'package_version' => 1,
            'definition_id' => $this->definitionId,
            'revision' => $this->revision,
            'schema_version' => $this->schemaVersion,
            'payload' => $this->payload,
            'dependencies' => [
                'query_source_ref' => $this->querySourceRef,
                'blueprint' => ['id' => $this->blueprintId, 'revision' => $this->blueprintRevision],
            ],
            'source_scope' => [
                'site_id' => $this->sourceSiteId,
                'network_id' => $this->sourceNetworkId,
            ],
        ];
    }
}
