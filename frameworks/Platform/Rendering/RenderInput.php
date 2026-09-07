<?php

declare(strict_types=1);

namespace WPEssential\Platform\Rendering;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class RenderInput
{
    /**
     * @param array<string, scalar|list<scalar>|null> $bindings
     */
    public function __construct(
        public string $blueprintId,
        public int $blueprintRevision,
        public array $bindings = [],
    ) {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $this->blueprintId)) {
            throw new InvalidArgumentException('Blueprint id must be an RFC 4122 UUID.');
        }
        if ($this->blueprintRevision < 1) {
            throw new InvalidArgumentException('Blueprint revision must be positive.');
        }
        if (count($this->bindings) > 128) {
            throw new InvalidArgumentException('Render bindings exceed the bounded V1 limit.');
        }
        foreach ($this->bindings as $key => $value) {
            if (!is_string($key) || !preg_match('/^[a-z][a-z0-9_.-]{0,127}$/', $key)) {
                throw new InvalidArgumentException('Render binding keys must be stable semantic identifiers.');
            }
            if (is_string($value) && preg_match('/<\?(?:php|=)|<script\b|javascript:/i', $value)) {
                throw new InvalidArgumentException('Executable authored channels are not accepted as render bindings.');
            }
        }
    }
}
