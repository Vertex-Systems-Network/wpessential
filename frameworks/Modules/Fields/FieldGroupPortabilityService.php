<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class FieldGroupPortabilityService
{
    public const FORMAT = 'wpessential.fields.field-groups';
    public const FORMAT_VERSION = 1;

    private FieldIdentityAssigner $identities;

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private FieldGroupDefinitionNormalizer $normalizer,
        private FieldGroupValidationService $validation,
        ?FieldIdentityAssigner $identities = null,
    ) {
        $this->identities = $identities ?? new FieldIdentityAssigner($definitions);
    }

    /**
     * @param list<string> $definitionIds
     * @return array{format:string,version:int,definitions:list<array<string,mixed>>}
     */
    public function export(array $definitionIds = []): array
    {
        $selected = $this->selectDefinitions($definitionIds);

        return [
            'format' => self::FORMAT,
            'version' => self::FORMAT_VERSION,
            'definitions' => array_map($this->exportDefinition(...), $selected),
        ];
    }

    /**
     * @param array<string,mixed> $envelope
     * @return array{created:list<string>,unchanged:list<string>}
     */
    public function import(array $envelope): array
    {
        $records = $this->validateEnvelope($envelope);
        $prepared = [];
        $bundleIds = [];
        $bundleSlugs = [];
        $bundleKeys = [];
        $bundleFieldUuids = [];

        foreach ($records as $record) {
            $candidate = $this->prepareImport($record);
            if (isset($bundleIds[$candidate->id])) {
                throw new InvalidArgumentException(sprintf('Portable Field Group id "%s" is duplicated in the import bundle.', $candidate->id));
            }
            $bundleIds[$candidate->id] = true;

            if (isset($bundleSlugs[$candidate->slug])) {
                throw new InvalidArgumentException(sprintf('Portable Field Group slug "%s" is duplicated in the import bundle.', $candidate->slug));
            }
            $bundleSlugs[$candidate->slug] = true;

            $groupKey = $candidate->payload['group_key'] ?? null;
            if (!is_string($groupKey) || $groupKey === '') {
                throw new InvalidArgumentException('Portable Field Group payload is missing a canonical group_key.');
            }
            if (isset($bundleKeys[$groupKey])) {
                throw new InvalidArgumentException(sprintf('Portable Field Group key "%s" is duplicated in the import bundle.', $groupKey));
            }
            $bundleKeys[$groupKey] = true;

            foreach ($this->collectFieldUuids($candidate->payload['fields'] ?? []) as $fieldUuid) {
                if (isset($bundleFieldUuids[$fieldUuid])) {
                    throw new InvalidArgumentException(sprintf('Portable Field uuid "%s" is duplicated across the import bundle.', $fieldUuid));
                }
                $bundleFieldUuids[$fieldUuid] = true;
            }

            $prepared[] = $candidate;
        }

        $persistedBySlug = [];
        foreach ($this->definitions->byType(FieldGroupDefinitionNormalizer::DEFINITION_TYPE) as $definition) {
            $persistedBySlug[$definition->slug] = $definition;
        }

        $created = [];
        $unchanged = [];
        foreach ($prepared as $candidate) {
            $existing = $this->definitions->get($candidate->id);
            if ($existing instanceof Definition) {
                $this->assertIdenticalExisting($candidate, $existing);
                $unchanged[] = $candidate->id;
                continue;
            }

            $slugOwner = $persistedBySlug[$candidate->slug] ?? null;
            if ($slugOwner instanceof Definition && $slugOwner->id !== $candidate->id) {
                throw new InvalidArgumentException(sprintf(
                    'Portable Field Group slug "%s" is already owned by definition "%s"; create-only import will not remap it.',
                    $candidate->slug,
                    $slugOwner->id,
                ));
            }

            $this->assertCanCreate($candidate);
        }

        foreach ($prepared as $candidate) {
            $existing = $this->definitions->get($candidate->id);
            if ($existing instanceof Definition) {
                $this->assertIdenticalExisting($candidate, $existing);
                if (!in_array($candidate->id, $unchanged, true)) {
                    $unchanged[] = $candidate->id;
                }
                continue;
            }

            $this->definitions->save($candidate);
            $created[] = $candidate->id;
        }

        return ['created' => $created, 'unchanged' => $unchanged];
    }

    /** @param list<string> $definitionIds @return list<Definition> */
    private function selectDefinitions(array $definitionIds): array
    {
        $owned = array_values(array_filter(
            $this->definitions->byType(FieldGroupDefinitionNormalizer::DEFINITION_TYPE),
            static fn (Definition $definition): bool => $definition->ownerSurfaceId === FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
        ));

        if ($definitionIds !== []) {
            $requested = [];
            foreach ($definitionIds as $id) {
                if (!is_string($id) || !$this->isUuid($id)) {
                    throw new InvalidArgumentException('Portable export definition_ids must contain lowercase RFC 4122 UUIDs.');
                }
                if (isset($requested[$id])) {
                    throw new InvalidArgumentException(sprintf('Portable export definition id "%s" is duplicated.', $id));
                }
                $requested[$id] = true;
            }
            $owned = array_values(array_filter(
                $owned,
                static fn (Definition $definition): bool => isset($requested[$definition->id]),
            ));
            if (count($owned) !== count($requested)) {
                throw new RuntimeException('One or more requested Field Group definitions are not owned by canonical Surface 3.');
            }
        }

        usort($owned, static fn (Definition $left, Definition $right): int => [$left->slug, $left->id] <=> [$right->slug, $right->id]);
        return $owned;
    }

    /** @return array<string,mixed> */
    private function exportDefinition(Definition $definition): array
    {
        if ($definition->dependencies !== []) {
            throw new RuntimeException('Portable Field Group V1 cannot export definitions with unresolved cross-definition dependencies.');
        }

        $normalized = $this->normalizer->normalize(
            $definition->payload,
            $definition->status === DefinitionStatus::Published,
        );
        $portable = new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: $definition->status,
            payload: $normalized,
            revision: 1,
            dependencies: [],
        );
        $checksum = $portable->computedChecksum();
        if ($definition->checksum !== null && $definition->checksum !== $checksum) {
            throw new RuntimeException(sprintf('Field Group "%s" checksum does not match its canonical payload.', $definition->id));
        }

        $this->assertPortableFieldIdentities($normalized['fields'] ?? []);

        return [
            'id' => $definition->id,
            'slug' => $definition->slug,
            'type' => $definition->type,
            'schema_version' => $definition->schemaVersion,
            'owner_surface_id' => $definition->ownerSurfaceId,
            'status' => $definition->status->value,
            'payload' => $normalized,
            'source_revision' => $definition->revision,
            'dependencies' => [],
            'checksum' => $checksum,
        ];
    }

    /** @param array<string,mixed> $envelope @return list<array<string,mixed>> */
    private function validateEnvelope(array $envelope): array
    {
        if (($envelope['format'] ?? null) !== self::FORMAT) {
            throw new InvalidArgumentException('Unsupported Custom Fields portability format.');
        }
        if (($envelope['version'] ?? null) !== self::FORMAT_VERSION) {
            throw new InvalidArgumentException('Unsupported Custom Fields portability format version; an explicit compatibility adapter is required.');
        }
        $records = $envelope['definitions'] ?? null;
        if (!is_array($records) || !array_is_list($records)) {
            throw new InvalidArgumentException('Portable Field Group definitions must be a list.');
        }
        foreach ($records as $record) {
            if (!is_array($record) || array_is_list($record)) {
                throw new InvalidArgumentException('Every portable Field Group definition must be an object/map.');
            }
        }
        /** @var list<array<string,mixed>> $records */
        return $records;
    }

    /** @param array<string,mixed> $record */
    private function prepareImport(array $record): Definition
    {
        $id = $record['id'] ?? null;
        $slug = $record['slug'] ?? null;
        $type = $record['type'] ?? null;
        $schemaVersion = $record['schema_version'] ?? null;
        $ownerSurfaceId = $record['owner_surface_id'] ?? null;
        $statusValue = $record['status'] ?? null;
        $payload = $record['payload'] ?? null;
        $sourceRevision = $record['source_revision'] ?? null;
        $dependencies = $record['dependencies'] ?? null;
        $checksum = $record['checksum'] ?? null;

        if (!is_string($id) || !$this->isUuid($id)) {
            throw new InvalidArgumentException('Portable Field Group id must be a lowercase RFC 4122 UUID.');
        }
        if (!is_string($slug) || $slug === '') {
            throw new InvalidArgumentException('Portable Field Group slug must be a string.');
        }
        if ($type !== FieldGroupDefinitionNormalizer::DEFINITION_TYPE) {
            throw new InvalidArgumentException('Portable definition type is not the canonical Surface 3 Field Group type.');
        }
        if ($ownerSurfaceId !== FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Portable definition owner is not canonical Surface 3.');
        }
        if ($schemaVersion !== 1) {
            throw new InvalidArgumentException('Unsupported Field Group definition schema version; an explicit compatibility adapter is required.');
        }
        if (!is_string($statusValue)) {
            throw new InvalidArgumentException('Portable Field Group status is invalid.');
        }
        $status = DefinitionStatus::tryFrom($statusValue);
        if (!$status instanceof DefinitionStatus) {
            throw new InvalidArgumentException('Portable Field Group status is invalid.');
        }
        if (!is_array($payload) || array_is_list($payload)) {
            throw new InvalidArgumentException('Portable Field Group payload must be an object/map.');
        }
        if (!is_int($sourceRevision) || $sourceRevision < 1) {
            throw new InvalidArgumentException('Portable Field Group source_revision must be a positive integer.');
        }
        if ($dependencies !== []) {
            throw new InvalidArgumentException('Portable Field Group V1 does not import unresolved cross-definition dependencies.');
        }
        if (!is_string($checksum) || preg_match('/^[0-9a-f]{64}$/', $checksum) !== 1) {
            throw new InvalidArgumentException('Portable Field Group checksum must be a lowercase SHA-256 string.');
        }

        $this->assertPortableFieldIdentities($payload['fields'] ?? []);
        $normalized = $this->normalizer->normalize($payload, $status === DefinitionStatus::Published);
        $candidate = new Definition(
            id: $id,
            slug: $slug,
            type: $type,
            schemaVersion: $schemaVersion,
            ownerSurfaceId: $ownerSurfaceId,
            status: $status,
            payload: $normalized,
            revision: 1,
            dependencies: [],
        );
        if ($candidate->computedChecksum() !== $checksum) {
            throw new InvalidArgumentException(sprintf('Portable Field Group "%s" checksum verification failed.', $id));
        }

        return new Definition(
            id: $candidate->id,
            slug: $candidate->slug,
            type: $candidate->type,
            schemaVersion: $candidate->schemaVersion,
            ownerSurfaceId: $candidate->ownerSurfaceId,
            status: $candidate->status,
            payload: $candidate->payload,
            revision: 1,
            dependencies: [],
            checksum: $checksum,
        );
    }

    private function assertCanCreate(Definition $candidate): void
    {
        $payload = $this->identities->assign($candidate->payload, null, $candidate->id);
        $report = $this->validation->validate([
            'payload' => $payload,
            'status' => $candidate->status->value,
        ]);
        if (!$report['valid']) {
            foreach ($report['issues'] as $issue) {
                if ($issue['severity'] === 'blocked') {
                    throw new InvalidArgumentException($issue['message']);
                }
            }
            throw new InvalidArgumentException('Portable Field Group import failed canonical validation.');
        }
    }

    private function assertIdenticalExisting(Definition $candidate, Definition $existing): void
    {
        $identical = $existing->id === $candidate->id
            && $existing->slug === $candidate->slug
            && $existing->type === $candidate->type
            && $existing->schemaVersion === $candidate->schemaVersion
            && $existing->ownerSurfaceId === $candidate->ownerSurfaceId
            && $existing->status === $candidate->status
            && $existing->dependencies === []
            && $existing->computedChecksum() === $candidate->computedChecksum();
        if (!$identical) {
            throw new InvalidArgumentException(sprintf(
                'Portable Field Group "%s" collides with an existing non-identical definition; create-only import will not overwrite it.',
                $candidate->id,
            ));
        }
    }

    private function assertPortableFieldIdentities(mixed $rows): void
    {
        if (!is_array($rows) || !array_is_list($rows)) {
            throw new InvalidArgumentException('Portable Field Group fields must be a list.');
        }
        $seen = [];
        foreach ($this->collectFieldUuids($rows) as $uuid) {
            if (isset($seen[$uuid])) {
                throw new InvalidArgumentException(sprintf('Portable Field uuid "%s" is duplicated inside a Field Group.', $uuid));
            }
            $seen[$uuid] = true;
        }
    }

    /** @return list<string> */
    private function collectFieldUuids(mixed $rows): array
    {
        if (!is_array($rows) || !array_is_list($rows)) {
            throw new InvalidArgumentException('Portable Field Group fields must be a list.');
        }
        $uuids = [];
        foreach ($rows as $row) {
            if (!is_array($row) || array_is_list($row)) {
                throw new InvalidArgumentException('Every portable Field must be an object/map.');
            }
            $uuid = $row['uuid'] ?? null;
            if (!is_string($uuid) || !$this->isUuid($uuid)) {
                throw new InvalidArgumentException('Every portable Field requires an existing stable lowercase RFC 4122 UUID.');
            }
            $uuids[] = $uuid;
            $subfields = $row['subfields'] ?? [];
            if (!is_array($subfields) || !array_is_list($subfields)) {
                throw new InvalidArgumentException('Portable Field subfields must be a list.');
            }
            foreach ($this->collectFieldUuids($subfields) as $nested) {
                $uuids[] = $nested;
            }
        }
        return $uuids;
    }

    private function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) === 1;
    }
}
