<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class CompiledRegistrationScope
{
    public function __construct(
        public int $networkId,
        public ?int $siteId,
    ) {
        if ($this->networkId < 1 || ($this->siteId !== null && $this->siteId < 1)) {
            throw new InvalidArgumentException('Compiled registration scope requires positive network/site identifiers.');
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

    public function key(): string
    {
        return sprintf('network:%d:site:%s', $this->networkId, $this->siteId === null ? '*' : (string) $this->siteId);
    }
}
