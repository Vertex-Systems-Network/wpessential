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

    /** @var list<string> */
    private const PAYLOAD_KEYS = ['query_source_ref', 'blueprint', 'layout', 'assets', 'bindings', 'presentation'];

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
        $this->assertPayload($definition->payload);

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
            sourceSiteId: $context->siteId,
            sourceNetworkId: $context->networkId,
        );
        $this->assertPackageSize($package);
        return $package;
    }

    /**
     * @param array<string,mixed> $data
     * @throws JsonException
     */
    public function validateImport(array $data, ExecutionContext $targetContext): ListingPortablePackage
    {
        $this->assertKnownKeys(
            $data,
            ['package_version', 'definition_id', 'revision', 'schema_version', 'payload', 'dependencies', 'source_scope'],
            'Listing package',
        );
        if (($data['package_version'] ?? null) !== 1) {
            throw new InvalidArgumentException('Listing package version is unsupported.');
        }

        $payload = $data['payload'] ?? null;
        $dependencies = $data['dependencies'] ?? null;
        $sourceScope = $data['source_scope'] ?? null;
        if (
            !is_array($payload) || array_is_list($payload)
            || !is_array($dependencies) || array_is_list($dependencies)
            || !is_array($sourceScope) || array_is_list($sourceScope)
        ) {
            throw new InvalidArgumentException('Listing package payload, dependencies and source scope must be object/maps.');
        }
        $this->assertPayload($payload);
        $this->assertKnownKeys($dependencies, ['query_source_ref', 'blueprint'], 'Listing dependency manifest');
        $this->assertKnownKeys($sourceScope, ['site_id', 'network_id'], 'Listing source scope');

        $querySourceRef = $dependencies['query_source_ref'] ?? null;
        $blueprint = $dependencies['blueprint'] ?? null;
        if (!is_string($querySourceRef) || !is_array($blueprint) || array_is_list($blueprint)) {
            throw new InvalidArgumentException('Listing package dependency manifest is incomplete.');
        }
        $this->assertKnownKeys($blueprint, ['id', 'revision'], 'Listing Blueprint dependency');

        $blueprintId = $blueprint['id'] ?? null;
        $blueprintRevision = $blueprint['revision'] ?? null;
        $sourceSiteId = $sourceScope['site_id'] ?? null;
        $sourceNetworkId = $sourceScope['network_id'] ?? null;
        if (
            !is_string($blueprintId) || !is_int($blueprintRevision) || !is_int($sourceSiteId)
            || ($sourceNetworkId !== null && !is_int($sourceNetworkId))
        ) {
            throw new InvalidArgumentException('Listing package dependency or source-scope metadata is invalid.');
        }

        // Source scope is evidence only. Target availability is revalidated in the server-owned target context.
        $this->assertDependenciesAvailable($querySourceRef, $blueprintId, $blueprintRevision, $targetContext);

        $package = new ListingPortablePackage(
            definitionId: is_string($data['definition_id'] ?? null) ? $data['definition_id'] : '',
            revision: is_int($data['revision'] ?? null) ? $data['revision'] : 0,
            schemaVersion: is_int($data['schema_version'] ?? null) ? $data['schema_version'] : 0,
            payload: $payload,
            querySourceRef: $querySourceRef,
            blueprintId: $blueprintId,
            blueprintRevision: $blueprintRevision,
            sourceSiteId: $sourceSiteId,
            sourceNetworkId: $sourceNetworkId,
        );
        $this->assertPackageSize($package);
        return $package;
    }

    private function assertDependenciesAvailable(
        string $querySourceRef,
        string $blueprintId,
        int $blueprintRevision,
        ExecutionContext $context,
    ): void {
        $description = $this->query->describe($querySourceRef, $context);
        if (($description['source_ref'] ?? null) !== $querySourceRef || ($description['available'] ?? false) !== true) {
            throw new InvalidArgumentException('Listing package Query dependency is unavailable.');
        }
        if ($this->blueprints->get($blueprintId, $blueprintRevision) === null) {
            throw new InvalidArgumentException('Listing package Blueprint dependency is unavailable.');
        }
    }

    /** @param array<string,mixed> $payload */
    private function assertPayload(array $payload): void
    {
        if (array_is_list($payload)) {
            throw new InvalidArgumentException('Listing package payload must be an object/map.');
        }
        $this->assertKnownKeys($payload, self::PAYLOAD_KEYS, 'Listing package payload');
        $this->assertSafeValue($payload);
    }

    private function assertSafeValue(mixed $value): void
    {
        if (is_string($value) && preg_match('/<\?(?:php|=)|<script\b|javascript:/i', $value)) {
            throw new InvalidArgumentException('Executable authored channels are forbidden in Listing packages.');
        }
        if (!is_array($value)) {
            return;
        }
        foreach ($value as $item) {
            $this->assertSafeValue($item);
        }
    }

    /**
     * @param array<string,mixed> $value
     * @param list<string> $allowed
     */
    private function assertKnownKeys(array $value, array $allowed, string $label): void
    {
        foreach (array_keys($value) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException($label . ' contains an unsupported key.');
            }
        }
    }

    /** @throws JsonException */
    private function assertPackageSize(ListingPortablePackage $package): void
    {
        $encoded = json_encode(
            $package->toArray(),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
        if (strlen($encoded) > self::MAX_PACKAGE_BYTES) {
            throw new InvalidArgumentException('Listing portable package exceeds the bounded V1 size limit.');
        }
    }
}
