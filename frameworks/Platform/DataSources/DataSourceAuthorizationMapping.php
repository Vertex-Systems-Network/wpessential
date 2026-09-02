<?php

declare(strict_types=1);

namespace WPEssential\Platform\DataSources;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class DataSourceAuthorizationMapping
{
    private const ABILITY_PATTERN = '#^wpessential/[a-z0-9][a-z0-9-]*/[a-z0-9][a-z0-9-]*$#';
    private const CAPABILITY_PATTERN = '/^[a-z0-9_]+$/';
    private const RESOURCE_TYPE_PATTERN = '/^[a-z][a-z0-9._-]{0,127}$/';

    public function __construct(
        public string $ability,
        public string $capability,
        public ?string $resourceType = null,
    ) {
        if (preg_match(self::ABILITY_PATTERN, $this->ability) !== 1) {
            throw new InvalidArgumentException('Data Source authorization ability must use wpessential/<domain>/<action>.');
        }
        if (preg_match(self::CAPABILITY_PATTERN, $this->capability) !== 1) {
            throw new InvalidArgumentException('Data Source authorization capability must be a stable WordPress capability key.');
        }
        if ($this->resourceType !== null && preg_match(self::RESOURCE_TYPE_PATTERN, $this->resourceType) !== 1) {
            throw new InvalidArgumentException('Data Source authorization resource type must be a stable semantic identifier.');
        }
    }
}
