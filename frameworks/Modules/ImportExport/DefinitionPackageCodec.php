<?php

declare(strict_types=1);

namespace WPEssential\Modules\ImportExport;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;

final class DefinitionPackageCodec
{
    public const FORMAT = 'wpessential-package';
    public const FORMAT_VERSION = 1;
    public const PACKAGE_TYPE = 'definition';
    public const MAX_BYTES = 1_048_576;
    public const MAX_DEFINITIONS = 500;

    /**
     * @param list<array<string,mixed>> $definitions
     * @return array<string,mixed>
     */
    public function create(array $definitions): array
    {
        $records = array_map($this->portableRecord(...), $definitions);
        usort($records, static fn (array $left, array $right): int => [
            $left['owner_surface_id'],
            $left['type'],
            $left['slug'],
            $left['id'],
        ] <=> [
            $right['owner_surface_id'],
            $right['type'],
            $right['slug'],
            $right['id'],
        ]);

        if (count($records) > self::MAX_DEFINITIONS) {
            throw new InvalidArgumentException('Definition package exceeds the WP122 definition-count limit.');
        }

        return [
            'manifest' => [
                'format' => self::FORMAT,
                'format_version' => self::FORMAT_VERSION,
                'package_type' => self::PACKAGE_TYPE,
                'package_id' => $this->uuid(),
                'created_at_utc' => gmdate('Y-m-d\TH:i:s\Z'),
                'product_version' => defined('WPE_VERSION') ? (string) WPE_VERSION : '0.1.0-dev',
                'secret_policy' => 'excluded',
                'runtime_data_included' => false,
                'definition_count' => count($records),
                'definitions_checksum' => $this->checksum($records),
            ],
            'definitions' => $records,
        ];
    }

    /** @param array<string,mixed> $package */
    public function encode(array $package): string
    {
        $this->verify($package);
        try {
            $json = json_encode(
                $package,
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Definition package could not be encoded.', 0, $exception);
        }

        if (strlen($json) > self::MAX_BYTES) {
            throw new InvalidArgumentException('Definition package exceeds the WP122 JSON size limit.');
        }
        return $json . "\n";
    }

    /** @return array<string,mixed> */
    public function decode(string $json): array
    {
        if ($json === '' || strlen($json) > self::MAX_BYTES) {
            throw new InvalidArgumentException('Definition package JSON is empty or exceeds the size limit.');
        }
        try {
            $package = json_decode($json, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Definition package JSON is malformed.', 0, $exception);
        }
        if (!is_array($package) || array_is_list($package)) {
            throw new InvalidArgumentException('Definition package root must be an object/map.');
        }
        $this->verify($package);
        return $package;
    }

    /** @param array<string,mixed> $package */
    public function verify(array $package): void
    {
        $manifest = $package['manifest'] ?? null;
        $definitions = $package['definitions'] ?? null;
        if (!is_array($manifest) || array_is_list($manifest) || !is_array($definitions) || !array_is_list($definitions)) {
            throw new InvalidArgumentException('Definition package requires a manifest object and definition list.');
        }

        $packageId = $manifest['package_id'] ?? null;
        $createdAt = $manifest['created_at_utc'] ?? null;
        $productVersion = $manifest['product_version'] ?? null;
        if (($manifest['format'] ?? null) !== self::FORMAT
            || ($manifest['format_version'] ?? null) !== self::FORMAT_VERSION
            || ($manifest['package_type'] ?? null) !== self::PACKAGE_TYPE
            || ($manifest['secret_policy'] ?? null) !== 'excluded'
            || ($manifest['runtime_data_included'] ?? null) !== false
            || !is_string($packageId)
            || !$this->isUuid($packageId)
            || !is_string($createdAt)
            || preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $createdAt) !== 1
            || !is_string($productVersion)
            || trim($productVersion) === ''
        ) {
            throw new InvalidArgumentException('Definition package format, identity, or security policy is unsupported.');
        }
        if (($manifest['definition_count'] ?? null) !== count($definitions)
            || count($definitions) > self::MAX_DEFINITIONS
        ) {
            throw new InvalidArgumentException('Definition package count does not match its manifest.');
        }

        $seenIds = [];
        foreach ($definitions as $definition) {
            if (!is_array($definition) || array_is_list($definition)) {
                throw new InvalidArgumentException('Definition package records must be objects/maps.');
            }
            $this->verifyPortableRecord($definition);
            $id = $definition['id'];
            if (isset($seenIds[$id])) {
                throw new InvalidArgumentException('Definition package contains duplicate definition UUIDs.');
            }
            $seenIds[$id] = true;
        }
        $this->verifyPackageGraphAndIdentity($definitions, $seenIds);

        $expected = $manifest['definitions_checksum'] ?? null;
        if (!is_string($expected)
            || preg_match('/^[0-9a-f]{64}$/', $expected) !== 1
            || !hash_equals($expected, $this->checksum($definitions))
        ) {
            throw new InvalidArgumentException('Definition package integrity checksum does not match its records.');
        }
    }

    /** @param array<string,mixed> $package */
    public function packageChecksum(array $package): string
    {
        $this->verify($package);
        $manifest = $package['manifest'];
        if (!is_array($manifest)) {
            throw new InvalidArgumentException('Definition package manifest is invalid.');
        }
        return hash('sha256', $this->canonicalJson([
            'format' => $manifest['format'],
            'format_version' => $manifest['format_version'],
            'package_type' => $manifest['package_type'],
            'package_id' => $manifest['package_id'],
            'definitions_checksum' => $manifest['definitions_checksum'],
        ]));
    }

    /** @param array<string,mixed> $definition @return array<string,mixed> */
    private function portableRecord(array $definition): array
    {
        $record = [
            'id' => $definition['id'] ?? null,
            'slug' => $definition['slug'] ?? null,
            'type' => $definition['type'] ?? null,
            'schema_version' => $definition['schema_version'] ?? null,
            'owner_surface_id' => $definition['owner_surface_id'] ?? null,
            'status' => $definition['status'] ?? null,
            'payload' => $definition['payload'] ?? null,
            'source_revision' => $definition['revision'] ?? null,
            'dependencies' => $definition['dependencies'] ?? [],
            'checksum' => $definition['checksum'] ?? null,
        ];
        $this->verifyPortableRecord($record);
        return $record;
    }

    /** @param array<string,mixed> $record */
    private function verifyPortableRecord(array $record): void
    {
        $id = $record['id'] ?? null;
        $slug = $record['slug'] ?? null;
        $type = $record['type'] ?? null;
        $schemaVersion = $record['schema_version'] ?? null;
        $ownerSurfaceId = $record['owner_surface_id'] ?? null;
        $status = $record['status'] ?? null;
        $payload = $record['payload'] ?? null;
        $sourceRevision = $record['source_revision'] ?? 1;
        $dependencies = $record['dependencies'] ?? null;
        $checksum = $record['checksum'] ?? null;

        $supportedOwner = ($ownerSurfaceId === 1 && $type === 'post_type')
            || ($ownerSurfaceId === 2 && $type === 'taxonomy');
        if (!is_string($id)
            || !$this->isUuid($id)
            || !is_string($slug)
            || preg_match('/^[a-z0-9][a-z0-9-]*$/', $slug) !== 1
            || !$supportedOwner
            || $schemaVersion !== 1
            || !is_string($status)
            || !in_array($status, ['draft', 'published', 'disabled', 'archived'], true)
            || !is_array($payload)
            || array_is_list($payload)
            || !is_int($sourceRevision)
            || $sourceRevision < 1
            || !is_array($dependencies)
            || !array_is_list($dependencies)
            || !is_string($checksum)
            || preg_match('/^[0-9a-f]{64}$/', $checksum) !== 1
            || !hash_equals($checksum, $this->checksum($payload))
        ) {
            throw new InvalidArgumentException('Definition package contains unsupported or checksum-invalid definition metadata.');
        }

        $seenDependencies = [];
        foreach ($dependencies as $dependency) {
            if (!is_string($dependency) || !$this->isUuid($dependency)) {
                throw new InvalidArgumentException('Definition package dependency references must be UUIDs.');
            }
            if ($dependency === $id || isset($seenDependencies[$dependency])) {
                throw new InvalidArgumentException('Definition package dependencies must be unique and cannot reference self.');
            }
            $seenDependencies[$dependency] = true;
        }
    }

    /**
     * @param list<array<string,mixed>> $definitions
     * @param array<string,bool> $packageIds
     */
    private function verifyPackageGraphAndIdentity(array $definitions, array $packageIds): void
    {
        $runtimeKeys = [];
        $typeSlugs = [];
        $graph = [];

        foreach ($definitions as $definition) {
            $id = (string) $definition['id'];
            $owner = (int) $definition['owner_surface_id'];
            $type = (string) $definition['type'];
            $slug = (string) $definition['slug'];
            $payload = $definition['payload'];
            $dependencies = $definition['dependencies'];
            if (!is_array($payload) || !is_array($dependencies)) {
                throw new InvalidArgumentException('Definition package graph metadata is invalid.');
            }

            $keyField = $owner === 1 ? 'post_type_key' : 'taxonomy_key';
            $runtimeKey = $payload[$keyField] ?? null;
            if (is_string($runtimeKey) && trim($runtimeKey) !== '') {
                $runtimeIdentity = $owner . ':' . trim($runtimeKey);
                if (isset($runtimeKeys[$runtimeIdentity])) {
                    throw new InvalidArgumentException('Definition package contains duplicate owner runtime keys.');
                }
                $runtimeKeys[$runtimeIdentity] = true;
            }

            $slugIdentity = $type . ':' . $slug;
            if (isset($typeSlugs[$slugIdentity])) {
                throw new InvalidArgumentException('Definition package contains duplicate type/slug identities.');
            }
            $typeSlugs[$slugIdentity] = true;

            $graph[$id] = [];
            foreach ($dependencies as $dependency) {
                if (is_string($dependency) && isset($packageIds[$dependency])) {
                    $graph[$id][] = $dependency;
                }
            }
        }

        $visiting = [];
        $visited = [];
        foreach (array_keys($graph) as $id) {
            $this->visitDependencyNode($id, $graph, $visiting, $visited);
        }
    }

    /**
     * @param array<string,list<string>> $graph
     * @param array<string,bool> $visiting
     * @param array<string,bool> $visited
     */
    private function visitDependencyNode(
        string $id,
        array $graph,
        array &$visiting,
        array &$visited,
    ): void {
        if (isset($visited[$id])) {
            return;
        }
        if (isset($visiting[$id])) {
            throw new InvalidArgumentException('Definition package contains a circular dependency graph.');
        }

        $visiting[$id] = true;
        foreach ($graph[$id] ?? [] as $dependency) {
            $this->visitDependencyNode($dependency, $graph, $visiting, $visited);
        }
        unset($visiting[$id]);
        $visited[$id] = true;
    }

    private function checksum(mixed $value): string
    {
        return hash('sha256', $this->canonicalJson($value));
    }

    private function canonicalJson(mixed $value): string
    {
        try {
            return json_encode(
                $this->canonicalize($value),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Definition package contains non-encodable data.', 0, $exception);
        }
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

    private function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) === 1;
    }

    private function uuid(): string
    {
        if (function_exists('wp_generate_uuid4')) {
            $uuid = strtolower((string) wp_generate_uuid4());
            if ($this->isUuid($uuid)) {
                return $uuid;
            }
        }
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}
