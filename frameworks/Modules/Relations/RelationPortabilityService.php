<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class RelationPortabilityService
{
    public const FORMAT = 'wpessential.relations.definitions';
    public const FORMAT_VERSION = 1;

    private const ENVELOPE_KEYS = ['format', 'version', 'definitions'];
    private const RECORD_KEYS = [
        'id',
        'slug',
        'type',
        'schema_version',
        'owner_surface_id',
        'status',
        'payload',
        'source_revision',
        'dependencies',
        'checksum',
    ];

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private RelationDefinitionNormalizer $normalizer,
        private RelationDefinitionValidationService $validation,
    ) {}

    /**
     * @param list<string> $definitionIds
     * @return array{format:string,version:int,definitions:list<array<string,mixed>>}
     */
    public function export(array $definitionIds = []): array
    {
        return [
            'format' => self::FORMAT,
            'version' => self::FORMAT_VERSION,
            'definitions' => array_map($this->exportDefinition(...), $this->selectDefinitions($definitionIds)),
        ];
    }

    /**
     * Create-only import. Existing non-identical definitions are never overwritten.
     *
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

        foreach ($records as $record) {
            $candidate = $this->prepareImport($record);
            if (isset($bundleIds[$candidate->id])) {
                throw new InvalidArgumentException(sprintf(
                    'Portable Relation id "%s" is duplicated in the import bundle.',
                    $candidate->id,
                ));
            }
            if (isset($bundleSlugs[$candidate->slug])) {
                throw new InvalidArgumentException(sprintf(
                    'Portable Relation slug "%s" is duplicated in the import bundle.',
                    $candidate->slug,
                ));
            }

            $relationKey = $this->relationKey($candidate);
            if (isset($bundleKeys[$relationKey])) {
                throw new InvalidArgumentException(sprintf(
                    'Portable Relation key "%s" is duplicated in the import bundle.',
                    $relationKey,
                ));
            }

            $bundleIds[$candidate->id] = true;
            $bundleSlugs[$candidate->slug] = true;
            $bundleKeys[$relationKey] = true;
            $prepared[] = $candidate;
        }

        $persistedBySlug = [];
        $persistedByKey = [];
        foreach ($this->ownedDefinitions() as $definition) {
            $persistedBySlug[$definition->slug] = $definition;
            $key = $definition->payload['relation_key'] ?? null;
            if (is_string($key) && $key !== '') {
                $persistedByKey[$key] = $definition;
            }
        }

        $unchanged = [];
        foreach ($prepared as $candidate) {
            $existing = $this->definitions->get($candidate->id);
            if ($existing instanceof Definition) {
                $this->assertIdenticalExisting($candidate, $existing);
                $unchanged[] = $candidate->id;
            }

            $slugOwner = $persistedBySlug[$candidate->slug] ?? null;
            if ($slugOwner instanceof Definition && $slugOwner->id !== $candidate->id) {
                throw new InvalidArgumentException(sprintf(
                    'Portable Relation slug "%s" is already owned by definition "%s"; create-only import will not remap it.',
                    $candidate->slug,
                    $slugOwner->id,
                ));
            }

            $relationKey = $this->relationKey($candidate);
            $keyOwner = $persistedByKey[$relationKey] ?? null;
            if ($keyOwner instanceof Definition && $keyOwner->id !== $candidate->id) {
                throw new InvalidArgumentException(sprintf(
                    'Portable Relation key "%s" is already owned by definition "%s"; create-only import will not remap it.',
                    $relationKey,
                    $keyOwner->id,
                ));
            }

            if (!$existing instanceof Definition) {
                $this->assertCanCreate($candidate);
            }
        }

        $created = [];
        foreach ($prepared as $candidate) {
            $existing = $this->definitions->get($candidate->id);
            if ($existing instanceof Definition) {
                $this->assertIdenticalExisting($candidate, $existing);
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
        $owned = $this->ownedDefinitions();
        if ($definitionIds === []) {
            return $owned;
        }

        $requested = [];
        foreach ($definitionIds as $id) {
            if (!is_string($id) || !$this->isUuid($id)) {
                throw new InvalidArgumentException(
                    'Portable Relation export definition_ids must contain lowercase RFC 4122 UUIDs.',
                );
            }
            if (isset($requested[$id])) {
                throw new InvalidArgumentException(sprintf(
                    'Portable Relation export definition id "%s" is duplicated.',
                    $id,
                ));
            }
            $requested[$id] = true;
        }

        $owned = array_values(array_filter(
            $owned,
            static fn (Definition $definition): bool => isset($requested[$definition->id]),
        ));
        if (count($owned) !== count($requested)) {
            throw new RuntimeException(
                'One or more requested Relation definitions are not owned by canonical Surface 4.',
            );
        }

        return $owned;
    }

    /** @return list<Definition> */
    private function ownedDefinitions(): array
    {
        $owned = array_values(array_filter(
            $this->definitions->byType(RelationDefinitionNormalizer::DEFINITION_TYPE),
            static fn (Definition $definition): bool =>
                $definition->ownerSurfaceId === RelationDefinitionNormalizer::OWNER_SURFACE_ID,
        ));
        usort(
            $owned,
            static fn (Definition $left, Definition $right): int =>
                [$left->slug, $left->id] <=> [$right->slug, $right->id],
        );
        return $owned;
    }

    /** @return array<string,mixed> */
    private function exportDefinition(Definition $definition): array
    {
        if ($definition->schemaVersion !== 1) {
            throw new RuntimeException(
                'Portable Relation V1 cannot export a definition schema without an explicit compatibility adapter.',
            );
        }
        if ($definition->dependencies !== []) {
            throw new RuntimeException(
                'Portable Relation V1 cannot export definitions with unresolved cross-definition dependencies.',
            );
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
            throw new RuntimeException(sprintf(
                'Relation "%s" checksum does not match its canonical payload.',
                $definition->id,
            ));
        }

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
        $this->assertKnownKeys($envelope, self::ENVELOPE_KEYS, 'Relation portability envelope');
        if (($envelope['format'] ?? null) !== self::FORMAT) {
            throw new InvalidArgumentException('Unsupported Relations portability format.');
        }
        if (($envelope['version'] ?? null) !== self::FORMAT_VERSION) {
            throw new InvalidArgumentException(
                'Unsupported Relations portability format version; an explicit compatibility adapter is required.',
            );
        }

        $records = $envelope['definitions'] ?? null;
        if (!is_array($records) || !array_is_list($records)) {
            throw new InvalidArgumentException('Portable Relation definitions must be a list.');
        }
        foreach ($records as $record) {
            if (!is_array($record) || array_is_list($record)) {
                throw new InvalidArgumentException('Every portable Relation definition must be an object/map.');
            }
        }

        /** @var list<array<string,mixed>> $records */
        return $records;
    }

    /** @param array<string,mixed> $record */
    private function prepareImport(array $record): Definition
    {
        $this->assertKnownKeys($record, self::RECORD_KEYS, 'portable Relation definition');

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
            throw new InvalidArgumentException('Portable Relation id must be a lowercase RFC 4122 UUID.');
        }
        if (!is_string($slug) || $slug === '') {
            throw new InvalidArgumentException('Portable Relation slug must be a string.');
        }
        if ($type !== RelationDefinitionNormalizer::DEFINITION_TYPE) {
            throw new InvalidArgumentException('Portable definition type is not the canonical Surface 4 Relation type.');
        }
        if ($ownerSurfaceId !== RelationDefinitionNormalizer::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Portable definition owner is not canonical Surface 4.');
        }
        if ($schemaVersion !== 1) {
            throw new InvalidArgumentException(
                'Unsupported Relation definition schema version; an explicit compatibility adapter is required.',
            );
        }
        if (!is_string($statusValue)) {
            throw new InvalidArgumentException('Portable Relation status is invalid.');
        }
        $status = DefinitionStatus::tryFrom($statusValue);
        if (!$status instanceof DefinitionStatus) {
            throw new InvalidArgumentException('Portable Relation status is invalid.');
        }
        if (!is_array($payload) || array_is_list($payload)) {
            throw new InvalidArgumentException('Portable Relation payload must be an object/map.');
        }
        if (!is_int($sourceRevision) || $sourceRevision < 1) {
            throw new InvalidArgumentException('Portable Relation source_revision must be a positive integer.');
        }
        if ($dependencies !== []) {
            throw new InvalidArgumentException(
                'Portable Relation V1 does not import unresolved cross-definition dependencies.',
            );
        }
        if (!is_string($checksum) || preg_match('/^[0-9a-f]{64}$/', $checksum) !== 1) {
            throw new InvalidArgumentException('Portable Relation checksum must be a lowercase SHA-256 string.');
        }

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
            throw new InvalidArgumentException(sprintf(
                'Portable Relation "%s" checksum verification failed.',
                $id,
            ));
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
        $report = $this->validation->validate([
            'payload' => $candidate->payload,
            'status' => $candidate->status->value,
        ]);
        if ($report['valid']) {
            return;
        }
        foreach ($report['issues'] as $issue) {
            if ($issue['severity'] === 'blocked') {
                throw new InvalidArgumentException($issue['message']);
            }
        }
        throw new InvalidArgumentException('Portable Relation import failed canonical validation.');
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
                'Portable Relation "%s" collides with an existing non-identical definition; create-only import will not overwrite it.',
                $candidate->id,
            ));
        }
    }

    /** @param array<string,mixed> $value @param list<string> $allowed */
    private function assertKnownKeys(array $value, array $allowed, string $label): void
    {
        foreach (array_keys($value) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Unsupported %s option "%s".',
                    $label,
                    (string) $key,
                ));
            }
        }
    }

    private function relationKey(Definition $definition): string
    {
        $key = $definition->payload['relation_key'] ?? null;
        if (!is_string($key) || $key === '') {
            throw new InvalidArgumentException('Portable Relation payload is missing a canonical relation_key.');
        }
        return $key;
    }

    private function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) === 1;
    }
}
