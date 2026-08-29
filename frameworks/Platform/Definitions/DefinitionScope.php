<?php

declare(strict_types=1);

namespace WPEssential\Platform\Definitions;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class DefinitionScope
{
    private function __construct(
        public int $networkId,
        public ?int $siteId,
    ) {
        if ($this->networkId < 1) {
            throw new InvalidArgumentException('Definition network scope must be positive.');
        }
        if ($this->siteId !== null && $this->siteId < 1) {
            throw new InvalidArgumentException('Definition site scope must be positive when provided.');
        }
    }

    public static function site(int $networkId, int $siteId): self
    {
        return new self($networkId, $siteId);
    }

    public static function network(int $networkId): self
    {
        return new self($networkId, null);
    }

    public function databaseSiteId(): int
    {
        return $this->siteId ?? 0;
    }
}
