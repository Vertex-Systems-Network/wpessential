<?php

declare(strict_types=1);

namespace WPEssential\Platform\Definitions;


if (!defined('ABSPATH')) {
    exit;
}

use JsonException;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\DefinitionTableGatewayInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class WpdbDefinitionTableGateway implements DefinitionTableGatewayInterface
{
    private readonly DefinitionTableNames $tables;

    public function __construct(
        private readonly DatabaseAdapterInterface $database,
        private readonly DefinitionScope $scope,
    ) {
        $this->tables = new DefinitionTableNames($database);
    }

    public function find(string $id): ?array
    {
        $row = $this->database->getRow($this->database->prepare(
            "SELECT id, slug, type, schema_version, owner_surface_id, status, payload_json, revision, checksum FROM `{$this->tables->definitions}` WHERE network_id = %d AND site_id = %d AND id = %s",
            $this->scope->networkId,
            $this->scope->databaseSiteId(),
            $id,
        ));

        return $row === null ? null : $this->withDependencies($row);
    }

    public function insert(array $row, array $dependencies): void
    {
        $this->database->beginTransaction();
        try {
            $now = gmdate('Y-m-d H:i:s');
            $this->database->insert($this->tables->definitions, [
                'network_id' => $this->scope->networkId,
                'site_id' => $this->scope->databaseSiteId(),
                'id' => (string) ($row['id'] ?? ''),
                'slug' => (string) ($row['slug'] ?? ''),
                'type' => (string) ($row['type'] ?? ''),
                'schema_version' => (int) ($row['schema_version'] ?? 0),
                'owner_surface_id' => (int) ($row['owner_surface_id'] ?? 0),
                'status' => (string) ($row['status'] ?? ''),
                'payload_json' => (string) ($row['payload_json'] ?? ''),
                'revision' => (int) ($row['revision'] ?? 0),
                'checksum' => (string) ($row['checksum'] ?? ''),
                'created_at' => $now,
                'updated_at' => $now,
            ], ['%d', '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s']);

            $this->replaceDependencies((string) ($row['id'] ?? ''), $dependencies);
            $this->database->commit();
        } catch (Throwable $exception) {
            $this->safeRollback();
            throw new RuntimeException('Definition insert transaction failed.', 0, $exception);
        }
    }

    public function updateIfCurrentRevision(string $id, int $expectedRevision, array $row, array $dependencies): bool
    {
        $this->database->beginTransaction();
        try {
            $affected = $this->database->query($this->database->prepare(
                "UPDATE `{$this->tables->definitions}` SET slug = %s, type = %s, schema_version = %d, owner_surface_id = %d, status = %s, payload_json = %s, revision = %d, checksum = %s, updated_at = UTC_TIMESTAMP(6) WHERE network_id = %d AND site_id = %d AND id = %s AND revision = %d",
                (string) ($row['slug'] ?? ''),
                (string) ($row['type'] ?? ''),
                (int) ($row['schema_version'] ?? 0),
                (int) ($row['owner_surface_id'] ?? 0),
                (string) ($row['status'] ?? ''),
                (string) ($row['payload_json'] ?? ''),
                (int) ($row['revision'] ?? 0),
                (string) ($row['checksum'] ?? ''),
                $this->scope->networkId,
                $this->scope->databaseSiteId(),
                $id,
                $expectedRevision,
            ));

            if ($affected !== 1) {
                $this->database->rollBack();
                return false;
            }

            $this->replaceDependencies($id, $dependencies);
            $this->database->commit();
            return true;
        } catch (Throwable $exception) {
            $this->safeRollback();
            throw new RuntimeException('Definition update transaction failed.', 0, $exception);
        }
    }

    public function findByType(string $type): array
    {
        $rows = $this->database->getResults($this->database->prepare(
            "SELECT id, slug, type, schema_version, owner_surface_id, status, payload_json, revision, checksum FROM `{$this->tables->definitions}` WHERE network_id = %d AND site_id = %d AND type = %s ORDER BY slug ASC, id ASC",
            $this->scope->networkId,
            $this->scope->databaseSiteId(),
            $type,
        ));

        return array_map($this->withDependencies(...), $rows);
    }

    public function findDependents(string $id): array
    {
        $rows = $this->database->getResults($this->database->prepare(
            "SELECT d.id, d.slug, d.type, d.schema_version, d.owner_surface_id, d.status, d.payload_json, d.revision, d.checksum FROM `{$this->tables->definitions}` d INNER JOIN `{$this->tables->dependencies}` x ON x.network_id = d.network_id AND x.site_id = d.site_id AND x.definition_id = d.id WHERE d.network_id = %d AND d.site_id = %d AND x.dependency_id = %s ORDER BY d.type ASC, d.slug ASC, d.id ASC",
            $this->scope->networkId,
            $this->scope->databaseSiteId(),
            $id,
        ));

        return array_map($this->withDependencies(...), $rows);
    }

    /** @param array<string, mixed> $row @return array<string, scalar|null> */
    private function withDependencies(array $row): array
    {
        $id = (string) ($row['id'] ?? '');
        $dependencyRows = $this->database->getResults($this->database->prepare(
            "SELECT dependency_id FROM `{$this->tables->dependencies}` WHERE network_id = %d AND site_id = %d AND definition_id = %s ORDER BY dependency_id ASC",
            $this->scope->networkId,
            $this->scope->databaseSiteId(),
            $id,
        ));
        $dependencies = array_map(static fn (array $dependency): string => (string) ($dependency['dependency_id'] ?? ''), $dependencyRows);

        try {
            $row['_dependencies_json'] = json_encode($dependencies, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to encode persisted definition dependency projection.', 0, $exception);
        }

        /** @var array<string, scalar|null> $row */
        return $row;
    }

    /** @param list<string> $dependencies */
    private function replaceDependencies(string $definitionId, array $dependencies): void
    {
        $this->database->query($this->database->prepare(
            "DELETE FROM `{$this->tables->dependencies}` WHERE network_id = %d AND site_id = %d AND definition_id = %s",
            $this->scope->networkId,
            $this->scope->databaseSiteId(),
            $definitionId,
        ));

        foreach ($dependencies as $dependencyId) {
            $this->database->insert($this->tables->dependencies, [
                'network_id' => $this->scope->networkId,
                'site_id' => $this->scope->databaseSiteId(),
                'definition_id' => $definitionId,
                'dependency_id' => $dependencyId,
                'created_at' => gmdate('Y-m-d H:i:s'),
            ], ['%d', '%d', '%s', '%s', '%s']);
        }
    }

    private function safeRollback(): void
    {
        try {
            $this->database->rollBack();
        } catch (Throwable) {
        }
    }
}
