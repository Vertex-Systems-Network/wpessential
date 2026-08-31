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

final readonly class TaxonomyImportAbilityHandler implements AbilityHandlerInterface
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private TaxonomyDefinitionProjector $projector,
        private TaxonomyValidationService $validation,
    ) {}

    /** @param array<string,mixed> $input */
    public function handle(array $input, ExecutionContext $context): mixed
    {
        $record = $input['definition'] ?? null;
        if (!is_array($record) || array_is_list($record)) {
            throw new InvalidArgumentException('Imported Taxonomy definition must be an object/map.');
        }

        $strategy = $input['strategy'] ?? 'create_only';
        if (!is_string($strategy) || !in_array($strategy, ['create_only', 'update_existing'], true)) {
            throw new InvalidArgumentException('Taxonomy import strategy must be create_only or update_existing.');
        }

        $source = $this->sourceDefinition($record);
        $existing = $this->definitions->get($source->id);
        if ($existing instanceof Definition) {
            $this->assertOwned($existing);
            if ($this->sameSemanticDefinition($existing, $source)) {
                return [
                    'action' => 'no_change',
                    'definition' => $this->serialize($existing),
                ];
            }
            if ($strategy !== 'update_existing') {
                throw new RuntimeException('Taxonomy import found the same UUID with different target content.');
            }
            $this->assertExpectedRevision($input, $existing);
        }

        $this->assertKeyAvailable($source, $existing);
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
        $this->validatePayload($candidate);
        $candidate = $this->withChecksum($candidate);
        $this->definitions->save($candidate);

        return [
            'action' => $existing instanceof Definition ? 'updated' : 'created',
            'definition' => $this->serialize($candidate),
        ];
    }

    /** @param array<string,mixed> $record */
    private function sourceDefinition(array $record): Definition
    {
        $id = $record['id'] ?? null;
        $slug = $record['slug'] ?? null;
        $type = $record['type'] ?? null;
        $schemaVersion = $record['schema_version'] ?? null;
        $ownerSurfaceId = $record['owner_surface_id'] ?? null;
        $statusValue = $record['status'] ?? null;
        $payload = $record['payload'] ?? null;
        $dependencies = $record['dependencies'] ?? [];
        $checksum = $record['checksum'] ?? null;

        if (!is_string($id)
            || !is_string($slug)
            || $type !== TaxonomyDefinitionProjector::DEFINITION_TYPE
            || $schemaVersion !== 1
            || $ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
            || !is_string($statusValue)
            || !is_array($payload)
            || array_is_list($payload)
            || !is_array($dependencies)
            || !is_string($checksum)
        ) {
            throw new InvalidArgumentException('Imported Taxonomy definition metadata is invalid or unsupported.');
        }

        $status = DefinitionStatus::tryFrom($statusValue);
        if (!$status instanceof DefinitionStatus) {
            throw new InvalidArgumentException('Imported Taxonomy definition status is invalid.');
        }

        $normalizedDependencies = [];
        foreach ($dependencies as $dependency) {
            if (!is_string($dependency)) {
                throw new InvalidArgumentException('Imported Taxonomy dependencies must contain UUID strings only.');
            }
            $normalizedDependencies[] = $dependency;
        }

        $source = new Definition(
            id: $id,
            slug: $slug,
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: $status,
            payload: $payload,
            revision: 1,
            dependencies: $normalizedDependencies,
            checksum: $checksum,
        );
        if (!hash_equals($source->computedChecksum(), $checksum)) {
            throw new InvalidArgumentException('Imported Taxonomy definition checksum does not match its payload.');
        }

        return $source;
    }

    private function assertOwned(Definition $definition): void
    {
        if ($definition->type !== TaxonomyDefinitionProjector::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
        ) {
            throw new RuntimeException('Taxonomy import UUID is already owned by another canonical definition surface.');
        }
    }

    /** @param array<string,mixed> $input */
    private function assertExpectedRevision(array $input, Definition $existing): void
    {
        $expected = $input['expected_revision'] ?? null;
        if (!is_int($expected) || $expected !== $existing->revision) {
            throw new RuntimeException(sprintf(
                'Taxonomy import write conflict: expected revision %d.',
                $existing->revision,
            ));
        }
    }

    private function assertKeyAvailable(Definition $source, ?Definition $existing): void
    {
        $key = $source->payload['taxonomy_key'] ?? null;
        if (!is_string($key) || trim($key) === '') {
            throw new InvalidArgumentException('Imported Taxonomy payload requires taxonomy_key.');
        }
        $key = trim($key);

        foreach ($this->definitions->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE) as $candidate) {
            if ($candidate->ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
                || $candidate->id === $source->id
                || ($existing instanceof Definition && $candidate->id === $existing->id)
            ) {
                continue;
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
        throw new InvalidArgumentException('Taxonomy validation blocked the imported definition.');
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
    private function serialize(Definition $definition): array
    {
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
        ];
    }
}
