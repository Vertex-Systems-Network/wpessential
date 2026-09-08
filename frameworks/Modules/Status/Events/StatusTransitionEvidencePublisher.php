<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Events;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Contracts\AuditLoggerInterface;
use WPEssential\Modules\Status\Execution\StatusTransitionExecutionResult;
use WPEssential\Modules\Status\Execution\StatusTransitionObservationException;
use WPEssential\Platform\Audit\AuditOutcome;
use WPEssential\Platform\Audit\AuditRecord;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Events\DomainEvent;
use WPEssential\Platform\Events\EventBus;

final readonly class StatusTransitionEvidencePublisher
{
    public const EVENT_NAME = 'status.transition.completed';
    public const AUDIT_ACTION = 'status.transition';

    private const OWNER_SURFACE_ID = 5;

    public function __construct(
        private EventBus $events,
        private AuditLoggerInterface $audit,
    ) {}

    public function publish(ExecutionContext $context, StatusTransitionExecutionResult $result): void
    {
        $auditRecorded = false;

        try {
            $this->audit->record(new AuditRecord(
                id: $this->uuid(),
                context: $context,
                ownerSurfaceId: self::OWNER_SURFACE_ID,
                action: self::AUDIT_ACTION,
                outcome: AuditOutcome::Success,
                resourceType: 'post',
                resourceId: $result->postId,
                reason: $result->reason,
                metadata: [
                    'post_type' => $result->postType,
                    'from_status' => $result->fromStatus,
                    'to_status' => $result->toStatus,
                    'programmatic' => $result->programmatic,
                    'reason_provided' => $result->reason !== null,
                ],
            ));
            $auditRecorded = true;

            $this->events->dispatch(new DomainEvent(
                name: self::EVENT_NAME,
                payload: [
                    'owner_surface_id' => self::OWNER_SURFACE_ID,
                    'post_id' => $result->postId,
                    'post_type' => $result->postType,
                    'from_status' => $result->fromStatus,
                    'to_status' => $result->toStatus,
                    'programmatic' => $result->programmatic,
                    'reason_provided' => $result->reason !== null,
                    'actor_type' => $context->principal->actorType,
                    'actor_user_id' => $context->principal->userId,
                    'site_id' => $context->siteId,
                    'network_id' => $context->networkId,
                    'channel' => $context->channel->value,
                ],
                correlationId: $context->correlationId,
            ));
        } catch (Throwable $exception) {
            throw new StatusTransitionObservationException(
                committedResult: $result,
                auditRecorded: $auditRecorded,
                eventDispatchCompleted: false,
                previous: $exception,
            );
        }
    }

    private function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}
