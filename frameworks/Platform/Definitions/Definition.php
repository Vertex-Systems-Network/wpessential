<?php

declare(strict_types=1);

namespace WPEssential\Platform\Definitions;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;

final readonly class Definition
{
    /**
     * @param array<string, mixed> $payload
     * @param list<string> $dependencies
     */
    public function __construct(
        public string $id,
        public string $slug,
        public string $type,
        public int $schemaVersion,
        public int $ownerSurfaceId,
        public DefinitionStatus $status,
        public array $payload,
        public int $revision = 1,
        public array $dependencies = [],
        public ?string $checksum = null,
    ) {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->id)) {
            throw new InvalidArgumentException('Definition id must be a lowercase RFC 4122 UUID.');
        }
        if (!preg_match('/^[a-z0-9][a-z0-9-]*$/', $this->slug)) {
            throw new InvalidArgumentException('Definition slug must be a lowercase slug.');
        }
        if (!preg_match('/^[a-z0-9][a-z0-9._-]*$/', $this->type)) {
            throw new InvalidArgumentException('Definition type must be a stable lowercase identifier.');
        }
        if ($this->schemaVersion < 1 || $this->revision < 1) {
            throw new InvalidArgumentException('Definition schema version and revision must be positive.');
        }
        if ($this->ownerSurfaceId < 1 || $this->ownerSurfaceId > 56) {
            throw new InvalidArgumentException('Definition owner must be a canonical surface id 1..56.');
        }

        $seen = [];
        foreach ($this->dependencies as $dependency) {
            if (!is_string($dependency) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $dependency)) {
                throw new InvalidArgumentException('Definition dependencies must be lowercase RFC 4122 UUIDs.');
            }
            if ($dependency === $this->id) {
                throw new InvalidArgumentException('Definition cannot depend on itself.');
            }
            if (isset($seen[$dependency])) {
                throw new InvalidArgumentException('Definition dependencies must be unique.');
            }
            $seen[$dependency] = true;
        }

        if ($this->checksum !== null && !preg_match('/^[0-9a-f]{64}$/', $this->checksum)) {
            throw new InvalidArgumentException('Definition checksum must be a lowercase SHA-256 hex string.');
        }
    }

    /** @throws JsonException */
    public function computedChecksum(): string
    {
        $canonical = self::canonicalize($this->payload);
        return hash('sha256', json_encode($canonical, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
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
