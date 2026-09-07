<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\Portability;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;

final readonly class ListingPortabilityService
{
    private const OWNER_SURFACE_ID = 9;
    private const TYPE = 'listing';
    private const MAX_PACKAGE_BYTES = 65536;

    public function __construct(
        private QueryReadConsumerInterface $query,
        private ComponentBlueprintRegistryInterface $blueprints,
    ) {
    }

    /** @throws JsonException */
    public function export(Definition $definition, ExecutionContext $context): ListingPortablePackage
    {
        if ($definition->type !== self::TYPE || $definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Only canonical Listing definitions may be exported.');
        }
        $this->assertSafePayload($definition->payload);

        $querySourceRef = $definition->payload['query_source_ref'] ?? null;
        $blueprint = $definition->payload['blueprint'] ?? null;
        if (!is_string($querySourceRef) || !is_array($blueprint) || array_is_list($blueprint)) {
            throw new InvalidArgumentException('Listing portability requires explicit Query and Blueprint dependencies.');
        }
        $blueprintId = $blueprint['id'] ?? null;
        $blueprintRevision = $blueprint['revision'] ?? null;
        if (!is_string($blueprintId) || !is_int($blueprintRevision)) {
            throw new InvalidArgumentException('Listing portability Blueprint dependency is invalid.');
        }

        $this->assertDependenciesAvailable($querySourceRef, $blueprintId, $blueprintRevision, $context);

        $package = new ListingPortablePackage(
            definitionId: $definition->id,
            revision: $definition->revision,
            schemaVersion: $definition->schemaVersion,
            payload: $definition->payload,
            querySourceRef: $querySourceRef,
            blueprintId: $blueprintId,
            blueprintRevision: $blueprintRevision,
            siteId: $context->siteId,
            networkId: $context->networkId,
        );
        $this->assertPackageSize($package);

        return $package;
    }

    /** @param array<string,mixed> $data
     *  @throws JsonException
     */
    public function validateImport(array $data, ExecutionContext $context): ListingPortablePackage
    {
        $allowed = ['package_version', 'definition_id', 'revision', 'schema_version', 'payload', 'dependencies', 'scope'];
        foreach (array_keys($data) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException('Listing package contains an unsupported top-level key.');
            }
        }
        if (($data['package_version'] ?? null) !== 1) {
            throw new InvalidArgumentException('Listing package version is unsupported.');
        }
        $payload = $data['payload'] ?? null;
        $dependencies = $data['dependencies'] ?? null;
        $scope = $data['scope'] ?? null;
        if (!is_array($payload) || !is_array($dependencies) || array_is_list($dependencies) || !is_array($scope) || array_is_list($scope)) {
            throw new InvalidArgumentException('Listing package payload, dependencies and scope must be object/maps.');
        }
        $this->assertSafePayload($payload);

        $querySourceRef = $dependencies['query_source_ref'] ?? null;
        $blueprint = $dependencies['blueprint'] ?? null;
        if (!is_string($querySourceRef) || !is_array($blueprint) || array_is_list($blueprint)) {
            throw new InvalidArgumentException('Listing package dependency manifest is incomplete.');
        }
        $blueprintId = $blueprint['id'] ?? null;
        $blueprintRevision = $blueprint['revision'] ?? null;
        $siteId = $scope['site_id'] ?? null;
        $networkId = $scope['network_id'] ?? null;
        if (!is_string($blueprintId) || !is_int($blueprintRevision) || !is_int($siteId) || ($networkId !== null && !is_int($networkId))) {
            throw new InvalidArgumentException('Listing package dependency or scope metadata is invalid.');
        }
        if ($siteId !== $context->siteId || ($networkId !== null && $networkId !== $context->networkId)) {
            throw new InvalidArgumentException('Listing package scope does not match the server execution context.');
        }

        $this->assertDependenciesAvailable($querySourceRef, $blueprintId, $blueprintRevision, $context);

        $package = new ListingPortablePackage(
            definitionId: is_string($data['definition_id'] ?? null) ? $data['definition_id'] : '',
            revision: is_int($data['revision'] ?? null) ? $data['revision'] : 0,
            schemaVersion: is_int($data['schema_version'] ?? null) ? $data['schema_version'] : 0,
            payload: $payload,
            querySourceRef: $querySourceRef,
            blueprintId: $blueprintId,
            blueprintRevision: $blueprintRevision,
            siteId: $siteId,
            networkId: $networkId,
        );
        $this->assertPackageSize($package);

        return $package;
    }

    private function assertDependenciesAvailable(string $querySourceRef, string $blueprintId, int $blueprintRevision, ExecutionContext $context): void
    {
        $description = $this->query->describe($querySourceRef, $context);
        if (($description['source_ref'] ?? null) !== $querySourceRef || ($description['available'] ?? false) !== true) {
            throw new InvalidArgumentException('Listing package Query dependency is unavailable.');
        }
        if ($this->blueprints->get($blueprintId, $blueprintRevision) === null) {
            throw new InvalidArgumentException('Listing package Blueprint dependency is unavailable.');
        }
    }

    private function assertSafePayload(mixed $value): void
    {
        if (is_string($value) && preg_match('/<\?(?:php|=)|<script\b|javascript:/i', $value)) {
            throw new InvalidArgumentException('Executable authored channels are forbidden in Listing packages.');
        }
        if (!is_array($value)) {
            return;
        }
        foreach ($value as $item) {
            $this->assertSafePayload($item);
        }
    }

    /** @throws JsonException */
    private function assertPackageSize(ListingPortablePackage $package): void
    {
        if (strlen(json_encode($package->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) > self::MAX_PACKAGE_BYTES) {
            throw new InvalidArgumentException('Listing portable package exceeds the bounded V1 size limit.');
        }
    }
}
