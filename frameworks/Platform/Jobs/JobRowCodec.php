<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;

if (!defined('ABSPATH')) {
    exit;
}

use DateTimeImmutable;
use DateTimeZone;
use JsonException;
use RuntimeException;

final class JobRowCodec
{
    /** @return array<string, scalar|null> */
    public function encode(JobRecord $record, ?string $idempotencyHash, int $revision): array
    {
        try {
            $payload = json_encode($record->payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to encode Job payload.', 0, $exception);
        }

        return [
            'id' => $record->id,
            'type_key' => $record->typeKey,
            'state' => $record->state->value,
            'payload_json' => $payload,
            'idempotency_hash' => $idempotencyHash,
            'attempts' => $record->attempts,
            'last_failure' => $record->lastFailure?->value,
            'retry_after' => $record->retryAfter?->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s.u'),
            'revision' => $revision,
            'created_at' => $record->createdAt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s.u'),
        ];
    }

    /** @param array<string, scalar|null> $row */
    public function decode(array $row): PersistentJobSnapshot
    {
        try {
            $payload = json_decode((string) ($row['payload_json'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($payload)) {
                throw new RuntimeException('Persisted Job payload must decode to an array.');
            }

            $record = new JobRecord(
                id: (string) ($row['id'] ?? ''),
                typeKey: (string) ($row['type_key'] ?? ''),
                siteId: (int) ($row['site_id'] ?? 0),
                networkId: (int) ($row['network_id'] ?? 0),
                payload: $payload,
                idempotencyKey: null,
                createdAt: new DateTimeImmutable((string) ($row['created_at'] ?? 'now'), new DateTimeZone('UTC')),
            );
            $record->state = JobState::from((string) ($row['state'] ?? ''));
            $record->attempts = (int) ($row['attempts'] ?? 0);

            $failure = (string) ($row['last_failure'] ?? '');
            $record->lastFailure = $failure === '' ? null : JobFailureClass::from($failure);

            $retryAfter = (string) ($row['retry_after'] ?? '');
            $record->retryAfter = $retryAfter === '' ? null : new DateTimeImmutable($retryAfter, new DateTimeZone('UTC'));

            return new PersistentJobSnapshot(
                record: $record,
                revision: max(1, (int) ($row['revision'] ?? 1)),
                idempotencyHash: ($row['idempotency_hash'] ?? null) === null ? null : (string) $row['idempotency_hash'],
            );
        } catch (JsonException|\ValueError $exception) {
            throw new RuntimeException('Persisted Job row is invalid.', 0, $exception);
        }
    }
}
