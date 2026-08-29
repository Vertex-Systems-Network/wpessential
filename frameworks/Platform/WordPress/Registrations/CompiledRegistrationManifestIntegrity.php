<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;


if (!defined('ABSPATH')) {
    exit;
}

final class CompiledRegistrationManifestIntegrity
{
    /** @param array<string,array<string,array<string,mixed>>> $entries */
    public static function checksum(int $generation, array $entries): string
    {
        $json = json_encode(
            ['generation' => $generation, 'entries' => self::canonicalEntries($entries)],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
        );
        return hash('sha256', $json);
    }

    public static function verify(CompiledRegistrationManifest $manifest): bool
    {
        return hash_equals(
            $manifest->checksum,
            self::checksum($manifest->generation, $manifest->entries),
        );
    }

    /**
     * @param array<string,array<string,array<string,mixed>>> $entries
     * @return array<string,array<string,array<string,mixed>>>
     */
    public static function canonicalEntries(array $entries): array
    {
        /** @var array<string,array<string,array<string,mixed>>> $canonical */
        $canonical = self::canonicalize($entries);
        return $canonical;
    }

    private static function canonicalize(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        if (array_is_list($value)) {
            return array_map(self::canonicalize(...), $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = self::canonicalize($item);
        }
        return $value;
    }
}
