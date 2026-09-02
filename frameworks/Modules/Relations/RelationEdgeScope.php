<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class RelationEdgeScope
{
    public function __construct(
        public int $networkId,
        public int $siteId,
    ) {
        if ($this->networkId < 1) {
            throw new InvalidArgumentException('Relation edge network id must be positive.');
        }
        if ($this->siteId < 0) {
            throw new InvalidArgumentException('Relation edge site id cannot be negative.');
        }
    }

    public static function site(int $networkId, int $siteId): self
    {
        if ($siteId < 1) {
            throw new InvalidArgumentException('Site-scoped Relation edge persistence requires a positive site id.');
        }

        return new self($networkId, $siteId);
    }

    public static function network(int $networkId): self
    {
        return new self($networkId, 0);
    }
}
