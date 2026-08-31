<?php

declare(strict_types=1);

namespace WPEssential\Platform\Audit;


if (!defined('ABSPATH')) {
    exit;
}

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use JsonException;

final class AuditRowCodec
{
    /** @return array<string, mixed> */
    public function encode(AuditRecord $record): array
    {
        $correlationId = $record->context->correlationId;
        if ($correlationId !== null && strlen($correlationId) > 191) {
            throw new InvalidArgumentException('Audit correlation id exceeds the 191-byte persistence boundary.');
        }
        if ($record->resourceType !== null && strlen($record->resourceType) > 191) {
            throw new InvalidArgumentException('Audit resource type exceeds the 191-byte persistence boundary.');
        }
        $resourceId = $record->resourceId === null ? null : (string) $record->resourceId;
        if ($resourceId !== null && strlen($resourceId) > 255) {
            throw new InvalidArgumentException('Audit resource id exceeds the 255-byte persistence boundary.');
        }
        if (strlen($record->privacyClass) > 64) {
            throw new InvalidArgumentException('Audit privacy class exceeds the 64-byte persistence boundary.');
        }

        $metadata = self::canonicalize($record->metadata);
        $metadataJson = json_encode(
            $metadata,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
        );

        $occurredAt = $record->occurredAt
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d H:i:s.u');

        $semantic = [
            'event_uuid' => $record->id,
            'network_id' => $record->context->networkId ?? 0,
            'site_id' => $record->context->siteId,
            'occurred_at' => $occurredAt,
            'actor_type' => $record->context->principal->actorType,
            'actor_user_id' => $record->context->principal->userId,
            'channel' => $record->context->channel->value,
            'correlation_id' => $correlationId,
            'owner_surface_id' => $record->ownerSurfaceId,
            'action' => $record->action,
            'outcome' => $record->outcome->value,
            'resource_type' => $record->resourceType,
            'resource_id' => $resourceId,
            'reason' => $record->reason,
            'metadata_json' => $metadataJson,
            'retention_class' => $record->retentionClass,
            'privacy_class' => $record->privacyClass,
            'schema_version' => 1,
        ];

        try {
            $hashPayload = json_encode(
                self::canonicalize($semantic),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
            );
        } catch (JsonException $exception) {
            throw $exception;
        }

        return $semantic + [
            'recorded_at' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s.u'),
            'content_hash' => hash('sha256', $hashPayload),
        ];
    }

    private static function canonicalize(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        if (array_is_list($value)) {
            return array_map(self::canonicalize(...), $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = self::canonicalize($item);
        }
        return $value;
    }
}
