<?php

declare(strict_types=1);

namespace WPEssential\Platform\Definitions;


if (!defined('ABSPATH')) {
    exit;
}

use JsonException;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\DefinitionTableGatewayInterface;

final class PersistentDefinitionRepository implements DefinitionRepositoryInterface
{
    public function __construct(
        private readonly DefinitionTableGatewayInterface $gateway,
        private readonly DefinitionRowCodec $codec = new DefinitionRowCodec(),
    ) {
    }

    public function save(Definition $definition): void
    {
        $row = $this->codec->encode($definition);
        $existingRow = $this->gateway->find($definition->id);

        if ($existingRow === null) {
            if ($definition->revision !== 1) {
                throw new RuntimeException('New persisted definitions must begin at revision 1.');
            }
            $this->gateway->insert($row, $definition->dependencies);
            return;
        }

        $existing = $this->decodeGatewayRow($existingRow);
        if ($definition->revision <= $existing->revision) {
            throw new RuntimeException('Definition revision must advance monotonically.');
        }

        if (!$this->gateway->updateIfCurrentRevision(
            $definition->id,
            $existing->revision,
            $row,
            $definition->dependencies,
        )) {
            throw new RuntimeException('Definition write conflict: persisted revision changed concurrently.');
        }
    }

    public function get(string $id): ?Definition
    {
        $row = $this->gateway->find($id);
        return $row === null ? null : $this->decodeGatewayRow($row);
    }

    public function byType(string $type): array
    {
        return array_map($this->decodeGatewayRow(...), $this->gateway->findByType($type));
    }

    public function dependentsOf(string $id): array
    {
        return array_map($this->decodeGatewayRow(...), $this->gateway->findDependents($id));
    }

    /** @param array<string, scalar|null> $row */
    private function decodeGatewayRow(array $row): Definition
    {
        $dependenciesJson = $row['_dependencies_json'] ?? '[]';
        unset($row['_dependencies_json']);

        if (!is_string($dependenciesJson)) {
            throw new RuntimeException('Persisted definition dependency projection is invalid.');
        }

        try {
            $dependencies = json_decode($dependenciesJson, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Persisted definition dependency projection is invalid.', 0, $exception);
        }

        if (!is_array($dependencies) || array_filter($dependencies, static fn ($item): bool => !is_string($item)) !== []) {
            throw new RuntimeException('Persisted definition dependencies must be a list of UUID strings.');
        }

        return $this->codec->decode($row, array_values($dependencies));
    }
}
