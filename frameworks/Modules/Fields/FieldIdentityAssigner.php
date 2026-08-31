<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;

final readonly class FieldIdentityAssigner
{
    public function __construct(private ?DefinitionRepositoryInterface $definitions = null) {}

    /**
     * Adds/preserves stable UUIDs for every field and nested subfield in a Field Group payload.
     *
     * Existing identity is matched by submitted UUID first and machine key second. A UUID already
     * attached to the same existing machine key cannot be silently replaced. Missing identities are
     * generated only in this save-time boundary, never during pure definition normalization.
     *
     * @param array<string,mixed> $payload
     * @param array<string,mixed>|null $existingPayload
     * @return array<string,mixed>
     */
    public function assign(array $payload, ?array $existingPayload = null, ?string $currentDefinitionId = null): array
    {
        $rows = $payload['fields'] ?? [];
        if (!is_array($rows) || !array_is_list($rows)) {
            throw new InvalidArgumentException('Field Group fields must be a list before assigning identities.');
        }

        $existingRows = $existingPayload['fields'] ?? [];
        if (!is_array($existingRows) || !array_is_list($existingRows)) {
            $existingRows = [];
        }

        $seen = [];
        $payload['fields'] = $this->assignRows($rows, $existingRows, $seen);
        $this->assertRepositoryUniqueness(array_keys($seen), $currentDefinitionId);

        return $payload;
    }

    /**
     * @param list<mixed> $rows
     * @param list<mixed> $existingRows
     * @param array<string,bool> $seen
     * @return list<array<string,mixed>>
     */
    private function assignRows(array $rows, array $existingRows, array &$seen): array
    {
        $existingByKey = [];
        $existingByUuid = [];
        foreach ($existingRows as $existing) {
            if (!is_array($existing) || array_is_list($existing)) {
                continue;
            }
            $key = $existing['key'] ?? null;
            $uuid = $existing['uuid'] ?? null;
            if (is_string($key) && $key !== '') {
                $existingByKey[$key] = $existing;
            }
            if (is_string($uuid) && $this->isUuid($uuid)) {
                $existingByUuid[$uuid] = $existing;
            }
        }

        $assigned = [];
        foreach ($rows as $row) {
            if (!is_array($row) || array_is_list($row)) {
                throw new InvalidArgumentException('Every Field Group field must be an object/map before assigning identity.');
            }
            $key = $row['key'] ?? null;
            if (!is_string($key) || trim($key) === '') {
                throw new InvalidArgumentException('Field key is required before assigning identity.');
            }
            $key = trim($key);

            $submittedUuid = $row['uuid'] ?? null;
            if ($submittedUuid !== null && (!is_string($submittedUuid) || !$this->isUuid($submittedUuid))) {
                throw new InvalidArgumentException(sprintf('Field "%s" uuid must be a lowercase RFC 4122 UUID.', $key));
            }

            $existingBySubmittedUuid = is_string($submittedUuid) ? ($existingByUuid[$submittedUuid] ?? null) : null;
            $existingBySameKey = $existingByKey[$key] ?? null;
            $existing = is_array($existingBySubmittedUuid) ? $existingBySubmittedUuid : $existingBySameKey;
            $existingUuid = is_array($existing) && isset($existing['uuid']) && is_string($existing['uuid']) && $this->isUuid($existing['uuid'])
                ? $existing['uuid']
                : null;

            if (is_array($existingBySameKey)) {
                $sameKeyUuid = $existingBySameKey['uuid'] ?? null;
                if (is_string($submittedUuid)
                    && is_string($sameKeyUuid)
                    && $this->isUuid($sameKeyUuid)
                    && $submittedUuid !== $sameKeyUuid
                ) {
                    throw new InvalidArgumentException(sprintf(
                        'Field "%s" already has stable uuid "%s"; identity cannot be replaced in-place.',
                        $key,
                        $sameKeyUuid,
                    ));
                }
            }

            $uuid = is_string($submittedUuid)
                ? $submittedUuid
                : ($existingUuid ?? $this->uuid());
            if (isset($seen[$uuid])) {
                throw new InvalidArgumentException(sprintf('Field uuid "%s" is duplicated inside the Field Group.', $uuid));
            }
            $seen[$uuid] = true;
            $row['uuid'] = $uuid;

            $subfields = $row['subfields'] ?? [];
            if (!is_array($subfields) || !array_is_list($subfields)) {
                throw new InvalidArgumentException(sprintf('Field "%s" subfields must be a list.', $key));
            }
            $existingSubfields = is_array($existing) ? ($existing['subfields'] ?? []) : [];
            if (!is_array($existingSubfields) || !array_is_list($existingSubfields)) {
                $existingSubfields = [];
            }
            if ($subfields !== []) {
                $row['subfields'] = $this->assignRows($subfields, $existingSubfields, $seen);
            }

            $assigned[] = $row;
        }

        return $assigned;
    }

    /** @param list<string> $uuids */
    private function assertRepositoryUniqueness(array $uuids, ?string $currentDefinitionId): void
    {
        if (!$this->definitions instanceof DefinitionRepositoryInterface || $uuids === []) {
            return;
        }
        $requested = array_fill_keys($uuids, true);
        foreach ($this->definitions->byType(FieldGroupDefinitionNormalizer::DEFINITION_TYPE) as $definition) {
            if (!$definition instanceof Definition
                || $definition->ownerSurfaceId !== FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID
                || $definition->id === $currentDefinitionId
            ) {
                continue;
            }
            foreach ($this->collectUuids($definition->payload['fields'] ?? []) as $uuid) {
                if (isset($requested[$uuid])) {
                    throw new InvalidArgumentException(sprintf(
                        'Field uuid "%s" is already owned by another canonical Field Group.',
                        $uuid,
                    ));
                }
            }
        }
    }

    /** @return list<string> */
    private function collectUuids(mixed $rows): array
    {
        if (!is_array($rows) || !array_is_list($rows)) {
            return [];
        }
        $uuids = [];
        foreach ($rows as $row) {
            if (!is_array($row) || array_is_list($row)) {
                continue;
            }
            $uuid = $row['uuid'] ?? null;
            if (is_string($uuid) && $this->isUuid($uuid)) {
                $uuids[] = $uuid;
            }
            foreach ($this->collectUuids($row['subfields'] ?? []) as $nested) {
                $uuids[] = $nested;
            }
        }
        return $uuids;
    }

    private function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) === 1;
    }

    private function uuid(): string
    {
        if (function_exists('wp_generate_uuid4')) {
            $uuid = strtolower((string) wp_generate_uuid4());
            if ($this->isUuid($uuid)) {
                return $uuid;
            }
        }

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
