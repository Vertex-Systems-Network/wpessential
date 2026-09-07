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
        public int $siteId,
        public ?int $networkId = null,
    ) {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->definitionId)) {
            throw new InvalidArgumentException('Portable Listing definition id must be a lowercase RFC 4122 UUID.');
        }
        if ($this->revision < 1 || $this->schemaVersion < 1 || $this->blueprintRevision < 1 || $this->siteId < 1) {
            throw new InvalidArgumentException('Portable Listing revisions, schema and site id must be positive.');
        }
        if ($this->networkId !== null && $this->networkId < 1) {
            throw new InvalidArgumentException('Portable Listing network id must be positive when provided.');
        }
        if (!preg_match('/^[a-z][a-z0-9._-]{0,127}$/', $this->querySourceRef)) {
            throw new InvalidArgumentException('Portable Listing Query source reference is invalid.');
        }
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->blueprintId)) {
            throw new InvalidArgumentException('Portable Listing Blueprint id must be a lowercase RFC 4122 UUID.');
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
            'scope' => ['site_id' => $this->siteId, 'network_id' => $this->networkId],
        ];
    }
}
