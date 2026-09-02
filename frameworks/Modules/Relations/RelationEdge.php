<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class RelationEdge
{
    public function __construct(
        public string $edgeId,
        public string $relationDefinitionId,
        public int $fromObjectId,
        public int $toObjectId,
        public string $createdAt,
        public string $updatedAt,
    ) {
        if (!$this->isUuid($this->edgeId)) {
            throw new InvalidArgumentException('Relation edge id must be a lowercase RFC 4122 UUID.');
        }
        if (!$this->isUuid($this->relationDefinitionId)) {
            throw new InvalidArgumentException('Relation definition id must be a lowercase RFC 4122 UUID.');
        }
        if ($this->fromObjectId < 1 || $this->toObjectId < 1) {
            throw new InvalidArgumentException('Relation edge object ids must be positive integers.');
        }
        if (!$this->isDatabaseTimestamp($this->createdAt) || !$this->isDatabaseTimestamp($this->updatedAt)) {
            throw new InvalidArgumentException('Relation edge timestamps must use UTC database datetime format with microseconds.');
        }
    }

    private function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) === 1;
    }

    private function isDatabaseTimestamp(string $value): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $value) === 1;
    }
}
