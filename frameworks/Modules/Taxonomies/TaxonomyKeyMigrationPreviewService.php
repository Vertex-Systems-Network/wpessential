<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinition;

final class TaxonomyKeyMigrationPreviewService
{
    /** @var Closure(string):bool */
    private Closure $taxonomyExists;

    /** @param null|callable(string):bool $taxonomyExists */
    public function __construct(
        private readonly DefinitionRepositoryInterface $definitions,
        private readonly TaxonomyDefinitionProjector $projector,
        ?callable $taxonomyExists = null,
    ) {
        $this->taxonomyExists = $taxonomyExists !== null
            ? Closure::fromCallable($taxonomyExists)
            : static fn (string $key): bool => function_exists('taxonomy_exists') && taxonomy_exists($key);
    }

    /** @return array<string,mixed> */
    public function preview(string $definitionId, string $targetKey): array
    {
        $definition = $this->owned($definitionId);
        $sourceKey = $definition->payload['taxonomy_key'] ?? null;
        if (!is_string($sourceKey) || trim($sourceKey) === '') {
            throw new RuntimeException('Canonical Taxonomy source key is missing.');
        }
        $sourceKey = trim($sourceKey);
        $targetKey = trim($targetKey);
        if ($targetKey === '') {
            throw new InvalidArgumentException('Taxonomy migration target_key must be a non-empty string.');
        }

        try {
            $sourceRegistration = $this->projector->project($this->publishedClone($definition, $definition->payload));
        } catch (InvalidArgumentException $exception) {
            throw new RuntimeException('Canonical source Taxonomy cannot be compiled for migration planning: ' . $exception->getMessage(), 0, $exception);
        }

        $blockers = [];
        $warnings = [];
        $candidatePayload = $definition->payload;
        $candidatePayload['taxonomy_key'] = $targetKey;
        $candidateRegistration = null;

        try {
            $candidateRegistration = $this->projector->project($this->publishedClone($definition, $candidatePayload));
        } catch (InvalidArgumentException $exception) {
            $blockers[] = $this->message(
                'target_schema_invalid',
                'target_key',
                $exception->getMessage(),
            );
        }

        if ($candidateRegistration instanceof RegistrationDefinition) {
            $this->targetOwnershipBlockers($definition, $sourceKey, $targetKey, $blockers);
            $this->routingCollisionWarnings($definition, $candidateRegistration, $warnings);
        }

        $dependents = $this->dependentDefinitions($definition->id);
        if ($dependents !== []) {
            $warnings[] = $this->message(
                'dependent_definitions_present',
                'dependencies',
                sprintf(
                    '%d Definition(s) depend on this Taxonomy definition and must be revalidated before any separately authorized migration executes.',
                    count($dependents),
                ),
            );
        }

        $impacts = $this->impacts($sourceRegistration, $candidateRegistration, $dependents);
        if (($impacts['term_urls']['changes'] ?? false) === true) {
            $warnings[] = $this->message(
                'term_urls_change',
                'rewrite',
                'Effective taxonomy term URL paths would change and require redirect/rewrite verification in any future authorized execution.',
            );
        }
        if (($impacts['rest_route']['changes'] ?? false) === true) {
            $warnings[] = $this->message(
                'rest_route_changes',
                'rest_base',
                'Effective REST route would change and API consumers must be verified before any future authorized execution.',
            );
        }
        if (($impacts['query_var']['changes'] ?? false) === true) {
            $warnings[] = $this->message(
                'query_var_changes',
                'query_var',
                'Effective front-end query variable would change and inbound links/integrations must be verified before any future authorized execution.',
            );
        }

        return [
            'preview_only' => true,
            'execution_authorized' => false,
            'definition_id' => $definition->id,
            'source_revision' => $definition->revision,
            'source_status' => $definition->status->value,
            'source_key' => $sourceKey,
            'target_key' => $targetKey,
            'change_required' => $sourceKey !== $targetKey,
            'blocked' => $blockers !== [],
            'blockers' => $blockers,
            'warnings' => $warnings,
            'impacts' => $impacts,
            'recovery_plan' => [
                'execution_available' => false,
                'required_before_execution' => [
                    'Export the canonical source Definition and capture its current revision.',
                    'Snapshot taxonomy terms, term relationships, effective routes, and dependent Definition references.',
                    'Re-run this preview against the same current Definition revision immediately before execution.',
                ],
                'rollback_outline' => [
                    'Restore the original taxonomy key through the same separately authorized migration owner.',
                    'Restore captured term/relationship state if a future executor changed it.',
                    'Revalidate dependents and verify REST, query-var, term URL, and rewrite state before completing recovery.',
                    'Use the canonical deferred soft rewrite refresh coordinator after a successful authorized rollback.',
                ],
            ],
        ];
    }

    private function owned(string $definitionId): Definition
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $definitionId) !== 1) {
            throw new InvalidArgumentException('Taxonomy migration id must be a lowercase RFC 4122 UUID.');
        }

        $definition = $this->definitions->get($definitionId);
        if (!$definition instanceof Definition
            || $definition->type !== TaxonomyDefinitionProjector::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
        ) {
            throw new RuntimeException('Taxonomy definition was not found in the canonical Surface 2 owner.');
        }
        return $definition;
    }

    /** @param array<string,mixed> $payload */
    private function publishedClone(Definition $definition, array $payload): Definition
    {
        return new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: DefinitionStatus::Published,
            payload: $payload,
            revision: $definition->revision,
            dependencies: $definition->dependencies,
            checksum: $definition->checksum,
        );
    }

    /** @param list<array{id:string,field:string,message:string}> $blockers */
    private function targetOwnershipBlockers(
        Definition $current,
        string $sourceKey,
        string $targetKey,
        array &$blockers,
    ): void {
        if ($targetKey === $sourceKey) {
            return;
        }

        foreach ($this->definitions->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE) as $definition) {
            if ($definition->ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
                || $definition->id === $current->id
            ) {
                continue;
            }
            $key = $definition->payload['taxonomy_key'] ?? null;
            if (is_string($key) && trim($key) === $targetKey) {
                $blockers[] = $this->message(
                    'canonical_target_owned',
                    'target_key',
                    sprintf('Target taxonomy key "%s" is already owned by another canonical Taxonomy Definition.', $targetKey),
                );
                break;
            }
        }

        if (($this->taxonomyExists)($targetKey)) {
            $blockers[] = $this->message(
                'runtime_target_registered',
                'target_key',
                sprintf('Target taxonomy key "%s" is already registered at WordPress runtime.', $targetKey),
            );
        }
    }

    /** @param list<array{id:string,field:string,message:string}> $warnings */
    private function routingCollisionWarnings(
        Definition $current,
        RegistrationDefinition $candidate,
        array &$warnings,
    ): void {
        $candidateRest = $this->effectiveRestRoute($candidate);
        $candidateRewrite = $this->effectiveRewriteBase($candidate);
        $candidateQueryVar = $this->effectiveQueryVar($candidate);

        foreach ($this->definitions->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE) as $definition) {
            if ($definition->ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
                || $definition->id === $current->id
                || $definition->status !== DefinitionStatus::Published
            ) {
                continue;
            }

            try {
                $registration = $this->projector->project($definition);
            } catch (InvalidArgumentException) {
                continue;
            }

            if ($candidateRest !== null && $candidateRest === $this->effectiveRestRoute($registration)) {
                $warnings[] = $this->message(
                    'rest_route_collision',
                    'rest_base',
                    sprintf('Proposed REST route "%s" is also used by published taxonomy "%s".', $candidateRest, $registration->key),
                );
            }
            if ($candidateRewrite !== null && $candidateRewrite === $this->effectiveRewriteBase($registration)) {
                $warnings[] = $this->message(
                    'rewrite_route_collision',
                    'rewrite',
                    sprintf('Proposed rewrite base "%s" is also used by published taxonomy "%s".', $candidateRewrite, $registration->key),
                );
            }
            if ($candidateQueryVar !== null && $candidateQueryVar === $this->effectiveQueryVar($registration)) {
                $warnings[] = $this->message(
                    'query_var_collision',
                    'query_var',
                    sprintf('Proposed query variable "%s" is also used by published taxonomy "%s".', $candidateQueryVar, $registration->key),
                );
            }
        }
    }

    /**
     * @param list<array<string,mixed>> $dependents
     * @return array<string,mixed>
     */
    private function impacts(
        RegistrationDefinition $source,
        ?RegistrationDefinition $target,
        array $dependents,
    ): array {
        $sourceObjectTypes = $this->objectTypes($source);
        $targetObjectTypes = $target instanceof RegistrationDefinition ? $this->objectTypes($target) : null;
        $sourceRewrite = $this->effectiveRewriteBase($source);
        $targetRewrite = $target instanceof RegistrationDefinition ? $this->effectiveRewriteBase($target) : null;
        $sourceRest = $this->effectiveRestRoute($source);
        $targetRest = $target instanceof RegistrationDefinition ? $this->effectiveRestRoute($target) : null;
        $sourceQueryVar = $this->effectiveQueryVar($source);
        $targetQueryVar = $target instanceof RegistrationDefinition ? $this->effectiveQueryVar($target) : null;

        return [
            'term_urls' => [
                'source_rewrite_base' => $sourceRewrite,
                'target_rewrite_base' => $targetRewrite,
                'source_pattern' => $this->termPathPattern($source),
                'target_pattern' => $target instanceof RegistrationDefinition ? $this->termPathPattern($target) : null,
                'changes' => $target instanceof RegistrationDefinition ? $sourceRewrite !== $targetRewrite : null,
            ],
            'object_associations' => [
                'source_object_types' => $sourceObjectTypes,
                'target_object_types' => $targetObjectTypes,
                'changes' => $targetObjectTypes !== null ? $sourceObjectTypes !== $targetObjectTypes : null,
            ],
            'rest_route' => [
                'source' => $sourceRest,
                'target' => $targetRest,
                'changes' => $target instanceof RegistrationDefinition ? $sourceRest !== $targetRest : null,
            ],
            'query_var' => [
                'source' => $sourceQueryVar,
                'target' => $targetQueryVar,
                'changes' => $target instanceof RegistrationDefinition ? $sourceQueryVar !== $targetQueryVar : null,
            ],
            'dependent_definitions' => [
                'count' => count($dependents),
                'items' => $dependents,
            ],
        ];
    }

    /** @return list<array<string,mixed>> */
    private function dependentDefinitions(string $definitionId): array
    {
        $items = array_map(
            static fn (Definition $definition): array => [
                'id' => $definition->id,
                'slug' => $definition->slug,
                'type' => $definition->type,
                'owner_surface_id' => $definition->ownerSurfaceId,
                'status' => $definition->status->value,
                'revision' => $definition->revision,
            ],
            $this->definitions->dependentsOf($definitionId),
        );
        usort($items, static fn (array $left, array $right): int => [
            $left['owner_surface_id'], $left['type'], $left['slug'], $left['id'],
        ] <=> [
            $right['owner_surface_id'], $right['type'], $right['slug'], $right['id'],
        ]);
        return $items;
    }

    /** @return list<string> */
    private function objectTypes(RegistrationDefinition $registration): array
    {
        $objectTypes = $registration->payload['object_types'] ?? null;
        return is_array($objectTypes)
            ? array_values(array_filter($objectTypes, 'is_string'))
            : [];
    }

    private function effectiveRestRoute(RegistrationDefinition $registration): ?string
    {
        $args = is_array($registration->payload['args'] ?? null) ? $registration->payload['args'] : [];
        if (($args['show_in_rest'] ?? false) !== true) {
            return null;
        }
        $namespace = is_string($args['rest_namespace'] ?? null) ? trim($args['rest_namespace'], '/') : 'wp/v2';
        $base = is_string($args['rest_base'] ?? null) ? trim($args['rest_base'], '/') : $registration->key;
        return '/' . $namespace . '/' . $base;
    }

    private function effectiveRewriteBase(RegistrationDefinition $registration): ?string
    {
        $args = is_array($registration->payload['args'] ?? null) ? $registration->payload['args'] : [];
        $rewrite = $args['rewrite'] ?? true;
        if ($rewrite === false) {
            return null;
        }
        if (is_array($rewrite) && is_string($rewrite['slug'] ?? null) && trim($rewrite['slug']) !== '') {
            return trim($rewrite['slug'], '/');
        }
        return $registration->key;
    }

    private function effectiveQueryVar(RegistrationDefinition $registration): ?string
    {
        $args = is_array($registration->payload['args'] ?? null) ? $registration->payload['args'] : [];
        $public = ($args['public'] ?? true) === true;
        $publiclyQueryable = is_bool($args['publicly_queryable'] ?? null)
            ? $args['publicly_queryable']
            : $public;
        if (!$publiclyQueryable) {
            return null;
        }
        if (!array_key_exists('query_var', $args)) {
            return $registration->key;
        }
        $queryVar = $args['query_var'];
        if ($queryVar === false) {
            return null;
        }
        if ($queryVar === true) {
            return $registration->key;
        }
        return is_string($queryVar) && $queryVar !== '' ? $queryVar : null;
    }

    private function termPathPattern(RegistrationDefinition $registration): ?string
    {
        $base = $this->effectiveRewriteBase($registration);
        if ($base === null) {
            return null;
        }
        $args = is_array($registration->payload['args'] ?? null) ? $registration->payload['args'] : [];
        $rewrite = is_array($args['rewrite'] ?? null) ? $args['rewrite'] : [];
        $hierarchical = ($rewrite['hierarchical'] ?? false) === true;
        return '/' . trim($base, '/') . '/' . ($hierarchical ? '{parent/.../}' : '') . '{term-slug}/';
    }

    /** @return array{id:string,field:string,message:string} */
    private function message(string $id, string $field, string $message): array
    {
        return [
            'id' => $id,
            'field' => $field,
            'message' => $message,
        ];
    }
}
