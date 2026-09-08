<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Portability;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\Status\Definition\StatusDefinitionCompiler;
use WPEssential\Modules\Status\Transition\Definition\StatusTransitionPolicyDefinitionCompiler;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class StatusPortabilityService
{
    private const PACKAGE_VERSION = 1;
    private const OWNER_SURFACE_ID = 5;
    private const MAX_DEFINITIONS = 256;
    private const MAX_PACKAGE_BYTES = 262144;

    public function __construct(private DefinitionRepositoryInterface $definitions)
    {
    }

    /**
     * @param list<Definition> $definitions
     * @return array<string,mixed>
     * @throws JsonException
     */
    public function export(array $definitions): array
    {
        if ($definitions === [] || count($definitions) > self::MAX_DEFINITIONS) {
            throw new InvalidArgumentException('Status portability requires a bounded non-empty Definition list.');
        }

        $items = [];
        $seen = [];
        foreach ($definitions as $definition) {
            if (!$definition instanceof Definition) {
                throw new InvalidArgumentException('Status portability exports typed Definitions only.');
            }
            $this->assertCanonicalDefinition($definition);
            if (isset($seen[$definition->id])) {
                throw new InvalidArgumentException('Status portability Definition ids must be unique.');
            }
            $seen[$definition->id] = true;
            $items[] = $this->definitionToArray($definition);
        }

        usort(
            $items,
            static fn (array $left, array $right): int => [$left['type'], $left['id']] <=> [$right['type'], $right['id']],
        );

        $body = [
            'package_version' => self::PACKAGE_VERSION,
            'surface_id' => self::OWNER_SURFACE_ID,
            'definitions' => $items,
        ];
        $package = $body + ['checksum' => $this->checksum($body)];
        $this->assertPackageSize($package);
        return $package;
    }

    /**
     * Validate the complete package and persist only after every conflict check succeeds.
     * Existing semantically-identical same-id Definitions are idempotent.
     *
     * @param array<string,mixed> $package
     * @return list<Definition>
     * @throws JsonException
     */
    public function import(array $package): array
    {
        $this->assertKnownKeys($package, ['package_version', 'surface_id', 'definitions', 'checksum'], 'Status package');
        if (($package['package_version'] ?? null) !== self::PACKAGE_VERSION || ($package['surface_id'] ?? null) !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Status portability package version or surface is unsupported.');
        }
        $items = $package['definitions'] ?? null;
        $checksum = $package['checksum'] ?? null;
        if (!is_array($items) || !array_is_list($items) || $items === [] || count($items) > self::MAX_DEFINITIONS) {
            throw new InvalidArgumentException('Status portability definitions must be a bounded non-empty list.');
        }
        if (!is_string($checksum) || !preg_match('/^[0-9a-f]{64}$/', $checksum)) {
            throw new InvalidArgumentException('Status portability checksum is invalid.');
        }
        $body = [
            'package_version' => self::PACKAGE_VERSION,
            'surface_id' => self::OWNER_SURFACE_ID,
            'definitions' => $items,
        ];
        if (!hash_equals($this->checksum($body), $checksum)) {
            throw new InvalidArgumentException('Status portability package checksum mismatch.');
        }
        $this->assertPackageSize($package);

        $incoming = [];
        $seenIds = [];
        foreach ($items as $item) {
            if (!is_array($item) || array_is_list($item)) {
                throw new InvalidArgumentException('Status portability Definition entry must be an object/map.');
            }
            $definition = $this->definitionFromArray($item);
            $this->assertCanonicalDefinition($definition);
            if (isset($seenIds[$definition->id])) {
                throw new InvalidArgumentException('Status portability package contains a duplicate Definition id.');
            }
            $seenIds[$definition->id] = true;
            $incoming[] = $definition;
        }

        $this->assertNoIdentityOrSemanticConflicts($incoming);

        $result = [];
        foreach ($incoming as $definition) {
            $existing = $this->definitions->get($definition->id);
            if ($existing instanceof Definition) {
                $result[] = $existing;
                continue;
            }
            $this->definitions->save($definition);
            $result[] = $definition;
        }
        return $result;
    }

    /** @param list<Definition> $incoming */
    private function assertNoIdentityOrSemanticConflicts(array $incoming): void
    {
        $incomingById = [];
        foreach ($incoming as $definition) {
            $incomingById[$definition->id] = $definition;
            $existing = $this->definitions->get($definition->id);
            if ($existing instanceof Definition && !$this->equivalent($existing, $definition)) {
                throw new InvalidArgumentException('Status portability Definition id conflicts with divergent local content.');
            }
        }

        $statusKeys = [];
        foreach ($this->definitions->byType(StatusDefinitionCompiler::TYPE) as $existing) {
            $key = $existing->payload['key'] ?? null;
            if (is_string($key)) {
                $statusKeys[$key] = $existing->id;
            }
        }
        foreach ($incoming as $definition) {
            if ($definition->type !== StatusDefinitionCompiler::TYPE) {
                continue;
            }
            $key = $definition->payload['key'] ?? null;
            if (!is_string($key)) {
                throw new InvalidArgumentException('Status portability Status definition key is invalid.');
            }
            if (isset($statusKeys[$key]) && $statusKeys[$key] !== $definition->id) {
                throw new InvalidArgumentException('Status portability status key conflicts with another Definition.');
            }
            $statusKeys[$key] = $definition->id;
        }

        $edgeOwners = [];
        foreach ($this->definitions->byType(StatusTransitionPolicyDefinitionCompiler::TYPE) as $existing) {
            if (isset($incomingById[$existing->id])) {
                continue;
            }
            foreach ($this->edgeKeys($existing) as $edge) {
                $edgeOwners[$edge] = $existing->id;
            }
        }
        foreach ($incoming as $definition) {
            if ($definition->type !== StatusTransitionPolicyDefinitionCompiler::TYPE) {
                continue;
            }
            foreach ($this->edgeKeys($definition) as $edge) {
                if (isset($edgeOwners[$edge]) && $edgeOwners[$edge] !== $definition->id) {
                    throw new InvalidArgumentException('Status portability transition edge conflicts with another Definition.');
                }
                $edgeOwners[$edge] = $definition->id;
            }
        }
    }

    /** @return list<string> */
    private function edgeKeys(Definition $definition): array
    {
        $edges = $definition->payload['edges'] ?? [];
        if (!is_array($edges) || !array_is_list($edges)) {
            return [];
        }
        $keys = [];
        foreach ($edges as $edge) {
            if (!is_array($edge)) {
                continue;
            }
            $from = $edge['from'] ?? null;
            $to = $edge['to'] ?? null;
            if (is_string($from) && is_string($to)) {
                $keys[] = $from . '->' . $to;
            }
        }
        return $keys;
    }

    private function assertCanonicalDefinition(Definition $definition): void
    {
        if ($definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Status portability accepts only Surface 5 Definitions.');
        }
        if (!in_array($definition->type, [StatusDefinitionCompiler::TYPE, StatusTransitionPolicyDefinitionCompiler::TYPE], true)) {
            throw new InvalidArgumentException('Status portability Definition type is unsupported.');
        }
        if ($definition->dependencies !== []) {
            throw new InvalidArgumentException('Status portability bounded V1 does not accept Definition dependencies.');
        }
        $this->assertSafeValue($definition->payload);

        $published = $definition->status === DefinitionStatus::Published
            ? $definition
            : new Definition(
                id: $definition->id,
                slug: $definition->slug,
                type: $definition->type,
                schemaVersion: $definition->schemaVersion,
                ownerSurfaceId: $definition->ownerSurfaceId,
                status: DefinitionStatus::Published,
                payload: $definition->payload,
                revision: $definition->revision,
                dependencies: [],
            );

        if ($definition->type === StatusDefinitionCompiler::TYPE) {
            (new StatusDefinitionCompiler())->compile($published);
            return;
        }
        (new StatusTransitionPolicyDefinitionCompiler())->compile($published);
    }

    /** @return array<string,mixed> */
    private function definitionToArray(Definition $definition): array
    {
        return [
            'id' => $definition->id,
            'slug' => $definition->slug,
            'type' => $definition->type,
            'schema_version' => $definition->schemaVersion,
            'source_revision' => $definition->revision,
            'status' => $definition->status->value,
            'payload' => $definition->payload,
            'dependencies' => [],
            'payload_checksum' => $definition->computedChecksum(),
        ];
    }

    /** @param array<string,mixed> $item */
    private function definitionFromArray(array $item): Definition
    {
        $this->assertKnownKeys(
            $item,
            ['id', 'slug', 'type', 'schema_version', 'source_revision', 'status', 'payload', 'dependencies', 'payload_checksum'],
            'Status portability Definition',
        );
        $payload = $item['payload'] ?? null;
        $dependencies = $item['dependencies'] ?? null;
        $statusValue = $item['status'] ?? null;
        $sourceRevision = $item['source_revision'] ?? null;
        if (!is_array($payload) || array_is_list($payload) || $dependencies !== [] || !is_string($statusValue) || !is_int($sourceRevision) || $sourceRevision < 1) {
            throw new InvalidArgumentException('Status portability Definition metadata is invalid.');
        }
        $status = DefinitionStatus::tryFrom($statusValue);
        if (!$status instanceof DefinitionStatus) {
            throw new InvalidArgumentException('Status portability Definition lifecycle status is invalid.');
        }

        $definition = new Definition(
            id: is_string($item['id'] ?? null) ? $item['id'] : '',
            slug: is_string($item['slug'] ?? null) ? $item['slug'] : '',
            type: is_string($item['type'] ?? null) ? $item['type'] : '',
            schemaVersion: is_int($item['schema_version'] ?? null) ? $item['schema_version'] : 0,
            ownerSurfaceId: self::OWNER_SURFACE_ID,
            status: $status,
            payload: $payload,
            revision: 1,
            dependencies: [],
        );
        $payloadChecksum = $item['payload_checksum'] ?? null;
        if (!is_string($payloadChecksum) || !hash_equals($definition->computedChecksum(), $payloadChecksum)) {
            throw new InvalidArgumentException('Status portability Definition payload checksum mismatch.');
        }
        return $definition;
    }

    private function equivalent(Definition $existing, Definition $incoming): bool
    {
        return $existing->id === $incoming->id
            && $existing->slug === $incoming->slug
            && $existing->type === $incoming->type
            && $existing->schemaVersion === $incoming->schemaVersion
            && $existing->ownerSurfaceId === $incoming->ownerSurfaceId
            && $existing->status === $incoming->status
            && $existing->dependencies === $incoming->dependencies
            && hash_equals($existing->computedChecksum(), $incoming->computedChecksum());
    }

    private function assertSafeValue(mixed $value): void
    {
        if (is_string($value) && preg_match('/<\?(?:php|=)|<script\b|javascript:/i', $value)) {
            throw new InvalidArgumentException('Executable channels are forbidden in Status portability payloads.');
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

    /** @param array<string,mixed> $value */
    private function checksum(array $value): string
    {
        return hash('sha256', json_encode($this->canonicalize($value), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function canonicalize(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        if (array_is_list($value)) {
            return array_map($this->canonicalize(...), $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->canonicalize($item);
        }
        return $value;
    }

    /** @param array<string,mixed> $package */
    private function assertPackageSize(array $package): void
    {
        $encoded = json_encode($package, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (strlen($encoded) > self::MAX_PACKAGE_BYTES) {
            throw new InvalidArgumentException('Status portability package exceeds the bounded V1 size limit.');
        }
    }
}
