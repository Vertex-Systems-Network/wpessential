<?php

declare(strict_types=1);

namespace WPEssential\Platform\Secrets;

use InvalidArgumentException;

final readonly class SecretReference
{
    /** @param array<string, scalar|null> $metadata */
    public function __construct(
        public string $id,
        public string $name,
        public int $ownerSurfaceId,
        public int $version,
        public array $metadata = [],
    ) {
        if (!preg_match('/^[a-f0-9]{32}$/', $this->id)) {
            throw new InvalidArgumentException('Secret reference id must be a 32-character opaque lowercase hex identifier.');
        }
        if (!preg_match('/^[a-z][a-z0-9._-]{1,127}$/', $this->name)) {
            throw new InvalidArgumentException('Secret name must be a stable non-secret key.');
        }
        if ($this->ownerSurfaceId < 1 || $this->ownerSurfaceId > 56) {
            throw new InvalidArgumentException('Secret owner must be a canonical surface id 1..56.');
        }
        if ($this->version < 1) {
            throw new InvalidArgumentException('Secret version must be positive.');
        }
    }
}
