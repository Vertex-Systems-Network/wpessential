<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class TaxonomyAbilityHandler implements AbilityHandlerInterface
{
    public const LIST = 'list';
    public const GET = 'get';
    public const SAVE = 'save';
    public const STATUS = 'status';
    public const IMPORT = 'import';

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private TaxonomyDefinitionProjector $projector,
        private TaxonomyValidationService $validation,
        private string $action,
        private ?TaxonomyRewriteRefreshCoordinator $rewriteRefresh = null,
    ) {
        if (!in_array($this->action, [self::LIST, self::GET, self::SAVE, self::STATUS, self::IMPORT], true)) {
            throw new InvalidArgumentException('Unsupported Taxonomy ability action.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        return match ($this->action) {
            self::LIST => $this->list(),
            self::GET => $this->get($input),
            self::SAVE => $this->save($input),
            self::STATUS => $this->changeStatus($input),
            self::IMPORT => $this->importDefinition($input),
            default => throw new RuntimeException('Unsupported Taxonomy ability action.'),
        };
    }

    /** @return array{definitions:list<array<string,mixed>>} */
    private function list(): array
    {
        $definitions = array_values(array_filter(
            $this->definitions->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE),
            static fn (Definition $definition): bool => $definition->ownerSurfaceId === TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
        ));
        usort($definitions, static fn (Definition $left, Definition $right): int => [$left->slug, $left->id] <=> [$right->slug, $right->id]);
        $readModel = new TaxonomyDefinitionReadModel($this->definitions);

        return ['definitions' => array_map(
            fn (Definition $definition): array => $this->serialize($definition, $readModel),
            $definitions,
        )];
    }

    /** @param array<string,mixed> $input @return array{definition:array<string,mixed>} */
    private function get(array $input): array
    {
        return ['definition' => $this->serialize($this->owned($this->requiredUuid($input, 'id')))];
    }

    /** @param array<string,mixed> $input @return array{definition:array<string,mixed>} */
    private function save(array $input): array
    {
        $payload = $input['payload'] ?? null;
        if (!is_array($payload) || array_is_list($payload)) {
            throw new InvalidArgumentException('Taxonomy payload must be an object/map.');
        }

        $id = $input['id'] ?? null;
        $existing = null;
        if ($id !== null) {
            if (!is_string($id) || !$this->isUuid($id)) {
                throw new InvalidArgumentException('Taxonomy id must be a lowercase RFC 4122 UUID.');
            }
            $existing = $this->owned($id);
            $this->assertExpectedRevision($input, $existing);
        }

        $validationInput = ['payload' => $payload];
        if ($existing instanceof Definition) {
            $validationInput['id'] = $existing->id;
        }
        $this->assertValidationAllowsMutation($this->validation->validate($validationInput));

        $key = $this->requiredTaxonomyKey($payload);
        if ($existing instanceof Definition) {
            $this->assertTaxonomyKeyUnchanged($existing, $key);
        }

        $status = $this->statusFromInput($input, $existing?->status ?? DefinitionStatus::Draft);
        $candidate = new Definition(
            id: $existing?->id ?? $this->uuid(),
            slug: $existing?->slug ?? ('taxonomy-' . str_replace('_', '-', $key)),
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: $existing?->schemaVersion ?? 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: $status,
            payload: $payload,
            revision: ($existing?->revision ?? 0) + 1,
            dependencies: $existing?->dependencies ?? [],
        );
        $candidate = $this->persistMutation($existing, $candidate);

        return ['definition' => $this->serialize($candidate)];
    }

    /** @param array<string,mixed> $input @return array{definition:array<string,mixed>} */
    private function changeStatus(array $input): array
    {
        $existing = $this->owned($this->requiredUuid($input, 'id'));
        $this->assertExpectedRevision($input, $existing);
        $status = $this->statusFromInput($input, $existing->status, required: true);

        if ($status === DefinitionStatus::Published) {
            $this->assertValidationAllowsMutation($this->validation->validate([
                'id' => $existing->id,
                'payload' => $existing->payload,
            ]));
        }

        $candidate = new Definition(
            id: $existing->id,
            slug: $existing->slug,
            type: $existing->type,
            schemaVersion: $existing->schemaVersion,
            ownerSurfaceId: $existing->ownerSurfaceId,
            status: $status,
            payload: $existing->payload,
            revision: $existing->revision + 1,
            dependencies: $existing->dependencies,
        );
        $candidate = $this->persistMutation($existing, $candidate);

        return ['definition' => $this->serialize($candidate)];
    }

    /**
     * @param array<string,mixed> $input
     * @return array{action:'created'|'updated'|'no_change',definition:array<string,mixed>}
     */
    private function importDefinition(array $input): array
    {
        $record = $input['definition'] ?? null;
        if (!is_array($record) || array_is_list($record)) {
            throw new InvalidArgumentException('Imported Taxonomy definition must be an object/map.');
        }

        $strategy = $input['strategy'] ?? 'create_only';
        if (!is_string($strategy) || !in_array($strategy, ['create_only', 'update_existing'], true)) {
            throw new InvalidArgumentException('Taxonomy import strategy must be create_only or update_existing.');
        }

        $source = $this->importedDefinition($record);
        $found = $this->definitions->get($source->id);
        $existing = null;
        if ($found instanceof Definition) {
            if ($found->type !== TaxonomyDefinitionProjector::DEFINITION_TYPE
                || $found->ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
            ) {
                throw new RuntimeException('Taxonomy import UUID is already owned by another canonical definition surface.');
            }
            $existing = $found;
        }

        if ($existing instanceof Definition && $this->sameSemanticDefinition($existing, $source)) {
            return [
                'action' => 'no_change',
                'definition' => $this->serialize($existing),
            ];
        }

        if ($existing instanceof Definition) {
            if ($strategy !== 'update_existing') {
                throw new RuntimeException('Taxonomy import found the same UUID with different target content.');
            }
            $this->assertExpectedRevision($input, $existing);
            if ($existing->slug !== $source->slug) {
                throw new InvalidArgumentException('Taxonomy definition slug cannot be changed through portability import.');
            }
        }

        $this->assertPortableIdentityAvailable($source);
        $key = $this->requiredTaxonomyKey($source->payload);
        if ($existing instanceof Definition) {
            $this->assertTaxonomyKeyUnchanged($existing, $key);
        }

        $validationInput = ['payload' => $source->payload];
        if ($existing instanceof Definition) {
            $validationInput['id'] = $existing->id;
        }
        $this->assertValidationAllowsMutation($this->validation->validate($validationInput));

        $candidate = new Definition(
            id: $source->id,
            slug: $source->slug,
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: $source->status,
            payload: $source->payload,
            revision: ($existing?->revision ?? 0) + 1,
            dependencies: $source->dependencies,
        );
        $candidate = $this->persistMutation($existing, $candidate);

        return [
            'action' => $existing instanceof Definition ? 'updated' : 'created',
            'definition' => $this->serialize($candidate),
        ];
    }

    /** @param array<string,mixed> $record */
    private function importedDefinition(array $record): Definition
    {
        $id = $record['id'] ?? null;
        $slug = $record['slug'] ?? null;
        $type = $record['type'] ?? null;
        $schemaVersion = $record['schema_version'] ?? null;
        $ownerSurfaceId = $record['owner_surface_id'] ?? null;
        $statusValue = $record['status'] ?? null;
        $payload = $record['payload'] ?? null;
        $revision = $record['revision'] ?? null;
        $dependencies = $record['dependencies'] ?? null;
        $checksum = $record['checksum'] ?? null;

        if (!is_string($id)
            || !is_string($slug)
            || $type !== TaxonomyDefinitionProjector::DEFINITION_TYPE
            || $schemaVersion !== 1
            || $ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
            || !is_string($statusValue)
            || !is_array($payload)
            || array_is_list($payload)
            || !is_int($revision)
            || $revision < 1
            || !is_array($dependencies)
            || !array_is_list($dependencies)
            || !is_string($checksum)
        ) {
            throw new InvalidArgumentException('Imported Taxonomy definition metadata is invalid or unsupported.');
        }

        $status = DefinitionStatus::tryFrom($statusValue);
        if (!$status instanceof DefinitionStatus) {
            throw new InvalidArgumentException('Imported Taxonomy definition status is invalid.');
        }

        $source = new Definition(
            id: $id,
            slug: $slug,
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: $status,
            payload: $payload,
            revision: $revision,
            dependencies: $dependencies,
            checksum: $checksum,
        );
        if (!hash_equals($source->computedChecksum(), $checksum)) {
            throw new InvalidArgumentException('Imported Taxonomy definition checksum does not match its payload.');
        }

        return $source;
    }

    private function assertPortableIdentityAvailable(Definition $source): void
    {
        $key = $this->requiredTaxonomyKey($source->payload);
        foreach ($this->definitions->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE) as $candidate) {
            if ($candidate->ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
                || $candidate->id === $source->id
            ) {
                continue;
            }
            if ($candidate->slug === $source->slug) {
                throw new RuntimeException(sprintf(
                    'Taxonomy import slug collision: "%s" belongs to a different definition UUID.',
                    $source->slug,
                ));
            }
            $candidateKey = $candidate->payload['taxonomy_key'] ?? null;
            if (is_string($candidateKey) && trim($candidateKey) === $key) {
                throw new RuntimeException(sprintf(
                    'Taxonomy import key collision: "%s" belongs to a different definition UUID.',
                    $key,
                ));
            }
        }
    }

    private function sameSemanticDefinition(Definition $target, Definition $source): bool
    {
        return $target->slug === $source->slug
            && $target->status === $source->status
            && $target->dependencies === $source->dependencies
            && hash_equals($target->computedChecksum(), $source->computedChecksum());
    }

    /** @param array<string,mixed> $input */
    private function assertExpectedRevision(array $input, Definition $existing): void
    {
        $expected = $input['expected_revision'] ?? null;
        if (!is_int($expected) || $expected < 1) {
            throw new InvalidArgumentException('Updating a Taxonomy requires a positive expected_revision.');
        }
        if ($expected !== $existing->revision) {
            throw new RuntimeException(sprintf(
                'Taxonomy write conflict: expected revision %d, current revision is %d.',
                $expected,
                $existing->revision,
            ));
        }
    }

    private function assertTaxonomyKeyUnchanged(Definition $existing, string $candidateKey): void
    {
        $existingKey = $existing->payload['taxonomy_key'] ?? null;
        if (!is_string($existingKey) || trim($existingKey) !== $candidateKey) {
            throw new InvalidArgumentException(
                'Taxonomy key cannot be changed through the canonical save path; use the separately authorized key-migration workflow.',
            );
        }
    }

    /** @param array<string,mixed> $payload */
    private function requiredTaxonomyKey(array $payload): string
    {
        $key = $payload['taxonomy_key'] ?? null;
        if (!is_string($key) || trim($key) === '') {
            throw new InvalidArgumentException('Taxonomy payload requires taxonomy_key.');
        }
        return trim($key);
    }

    /**
     * @param array{valid:bool,issues:list<array{id:string,severity:string,field:string,message:string}>,candidate:array{taxonomy_key:?string}} $report
     */
    private function assertValidationAllowsMutation(array $report): void
    {
        if ($report['valid']) {
            return;
        }

        foreach ($report['issues'] as $issue) {
            if ($issue['severity'] === 'blocked') {
                throw new InvalidArgumentException($issue['message']);
            }
        }

        throw new InvalidArgumentException('Taxonomy validation blocked the requested mutation.');
    }

    /** @param array<string,mixed> $input */
    private function statusFromInput(
        array $input,
        DefinitionStatus $default,
        bool $required = false,
    ): DefinitionStatus {
        $value = $input['status'] ?? null;
        if ($value === null && !$required) {
            return $default;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException('Taxonomy status must be a string.');
        }
        $status = DefinitionStatus::tryFrom($value);
        if (!$status instanceof DefinitionStatus) {
            throw new InvalidArgumentException('Taxonomy status must be draft, published, disabled, or archived.');
        }
        return $status;
    }

    private function owned(string $id): Definition
    {
        $definition = $this->definitions->get($id);
        if (!$definition instanceof Definition
            || $definition->type !== TaxonomyDefinitionProjector::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
        ) {
            throw new RuntimeException('Taxonomy definition was not found in the canonical Surface 2 owner.');
        }
        return $definition;
    }

    private function persistMutation(?Definition $existing, Definition $candidate): Definition
    {
        $this->validatePayload($candidate);
        $candidate = $this->withChecksum($candidate);
        $this->definitions->save($candidate);
        $this->rewriteRefresh?->scheduleForMutation($existing, $candidate);
        return $candidate;
    }

    private function validatePayload(Definition $definition): void
    {
        $this->projector->project(new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: DefinitionStatus::Published,
            payload: $definition->payload,
            revision: $definition->revision,
            dependencies: $definition->dependencies,
        ));
    }

    private function withChecksum(Definition $definition): Definition
    {
        return new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: $definition->status,
            payload: $definition->payload,
            revision: $definition->revision,
            dependencies: $definition->dependencies,
            checksum: $definition->computedChecksum(),
        );
    }

    /** @return array<string,mixed> */
    private function serialize(
        Definition $definition,
        ?TaxonomyDefinitionReadModel $readModel = null,
    ): array {
        $readModel ??= new TaxonomyDefinitionReadModel($this->definitions);

        return [
            'id' => $definition->id,
            'slug' => $definition->slug,
            'type' => $definition->type,
            'schema_version' => $definition->schemaVersion,
            'owner_surface_id' => $definition->ownerSurfaceId,
            'status' => $definition->status->value,
            'payload' => $definition->payload,
            'revision' => $definition->revision,
            'dependencies' => $definition->dependencies,
            'checksum' => $definition->checksum,
            'read_model' => $readModel->summary($definition),
        ];
    }

    /** @param array<string,mixed> $input */
    private function requiredUuid(array $input, string $field): string
    {
        $value = $input[$field] ?? null;
        if (!is_string($value) || !$this->isUuid($value)) {
            throw new InvalidArgumentException(sprintf('%s must be a lowercase RFC 4122 UUID.', $field));
        }
        return $value;
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

        return sprintf('%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}
