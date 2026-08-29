<?php

declare(strict_types=1);

namespace WPEssential\Platform\Audit;

use DateTimeImmutable;
use InvalidArgumentException;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class AuditRecord
{
    /** @var array<string|int, mixed> */
    public array $metadata;
    public DateTimeImmutable $occurredAt;

    /** @param array<string|int, mixed> $metadata */
    public function __construct(
        public string $id,
        public ExecutionContext $context,
        public int $ownerSurfaceId,
        public string $action,
        public AuditOutcome $outcome,
        public ?string $resourceType = null,
        public string|int|null $resourceId = null,
        public ?string $reason = null,
        array $metadata = [],
        public string $retentionClass = 'AR-A',
        public string $privacyClass = 'standard',
        ?DateTimeImmutable $occurredAt = null,
    ) {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->id)) {
            throw new InvalidArgumentException('Audit id must be a lowercase RFC 4122 UUID.');
        }
        if ($this->ownerSurfaceId < 1 || $this->ownerSurfaceId > 56) {
            throw new InvalidArgumentException('Audit owner must be a canonical surface id 1..56.');
        }
        if (!preg_match('#^[a-z0-9][a-z0-9._/-]*$#', $this->action)) {
            throw new InvalidArgumentException('Audit action must be a stable lowercase identifier.');
        }
        if (!preg_match('/^AR-[A-Z]$/', $this->retentionClass)) {
            throw new InvalidArgumentException('Audit retention class must use AR-X format.');
        }

        $this->metadata = AuditMetadataSanitizer::sanitize($metadata);
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }
}
