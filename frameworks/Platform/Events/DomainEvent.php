<?php

declare(strict_types=1);

namespace WPEssential\Platform\Events;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class DomainEvent
{
    public DateTimeImmutable $occurredAt;

    /** @param array<string, mixed> $payload */
    public function __construct(
        public string $name,
        public array $payload = [],
        public ?string $correlationId = null,
        ?DateTimeImmutable $occurredAt = null,
    ) {
        if (!preg_match('/^[a-z0-9][a-z0-9._-]*$/', $this->name)) {
            throw new InvalidArgumentException('Event name must be a stable lowercase identifier.');
        }
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }
}
