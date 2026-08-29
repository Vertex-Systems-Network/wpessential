<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

use RuntimeException;

final class RegistrationCompiler
{
    public function __construct(private readonly CompiledRegistrationStoreInterface $store) {}

    /** @param iterable<RegistrationDefinition> $definitions */
    public function compileAndPublish(iterable $definitions): CompiledRegistrationManifest
    {
        $entries = [];
        foreach ($definitions as $definition) {
            if (!$definition instanceof RegistrationDefinition) {
                throw new RuntimeException('Registration compiler received an invalid definition.');
            }
            if (!$definition->enabled) {
                continue;
            }
            $kind = $definition->kind->value;
            if (isset($entries[$kind][$definition->key])) {
                throw new RuntimeException(sprintf('Duplicate active registration key "%s:%s".', $kind, $definition->key));
            }
            $entries[$kind][$definition->key] = [
                'id' => $definition->id,
                'revision' => $definition->revision,
                'payload' => self::canonicalize($definition->payload),
            ];
        }
        ksort($entries, SORT_STRING);
        foreach ($entries as &$items) {
            ksort($items, SORT_STRING);
        }
        unset($items);

        $generation = $this->store instanceof CompiledRegistrationGenerationSequenceInterface
            ? $this->store->nextGeneration()
            : (($this->store->active()?->generation ?? 0) + 1);

        $manifest = new CompiledRegistrationManifest(
            $generation,
            $entries,
            CompiledRegistrationManifestIntegrity::checksum($generation, $entries),
        );
        $this->store->publish($manifest);
        return $manifest;
    }

    private static function canonicalize(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = self::canonicalize($item);
            }
        }
        if (!array_is_list($value)) {
            ksort($value, SORT_STRING);
        }
        return $value;
    }
}
