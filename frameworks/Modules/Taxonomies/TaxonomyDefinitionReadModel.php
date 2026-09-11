<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class TaxonomyDefinitionReadModel
{
    private const POST_TYPE_DEFINITION_TYPE = 'post_type';
    private const POST_TYPE_OWNER_SURFACE_ID = 1;

    public function __construct(private DefinitionRepositoryInterface $definitions) {}

    /** @return array<string,mixed> */
    public function summary(Definition $definition): array
    {
        return [
            'runtime_health' => $this->runtimeHealth($definition),
            'dependency_usage' => $this->dependencyUsage($definition),
        ];
    }

    /**
     * @return array{
     *   count:int,
     *   declared:list<array{id:string,resolved:bool,slug:?string,type:?string,owner_surface_id:?int,status:?string}>,
     *   dependents:list<array{id:string,resolved:bool,slug:?string,type:?string,owner_surface_id:?int,status:?string}>,
     *   object_types:list<array{key:string,state:string,canonical:bool,canonical_definition_id:?string,canonical_status:?string,runtime_registered:?bool}>
     * }
     */
    public function dependencyUsage(Definition $definition): array
    {
        $declared = [];
        $references = [];
        foreach ($definition->dependencies as $dependencyId) {
            $declared[] = $this->reference($dependencyId);
            $references['definition:' . $dependencyId] = true;
        }
        usort($declared, static fn (array $left, array $right): int => $left['id'] <=> $right['id']);

        $dependents = [];
        foreach ($this->definitions->dependentsOf($definition->id) as $dependent) {
            $dependents[] = $this->referenceFromDefinition($dependent);
            $references['definition:' . $dependent->id] = true;
        }
        usort(
            $dependents,
            static fn (array $left, array $right): int => [
                $left['owner_surface_id'] ?? 0,
                $left['type'] ?? '',
                $left['slug'] ?? '',
                $left['id'],
            ] <=> [
                $right['owner_surface_id'] ?? 0,
                $right['type'] ?? '',
                $right['slug'] ?? '',
                $right['id'],
            ],
        );

        $objectTypes = $this->objectTypeImpact($definition->payload['object_types'] ?? []);
        foreach ($objectTypes as $objectType) {
            $canonicalId = $objectType['canonical_definition_id'];
            $references[$canonicalId !== null
                ? 'definition:' . $canonicalId
                : 'object-type:' . $objectType['key']] = true;
        }

        return [
            'count' => count($references),
            'declared' => $declared,
            'dependents' => $dependents,
            'object_types' => $objectTypes,
        ];
    }

    /**
     * @param list<string> $objectTypes
     * @return list<array{key:string,state:string,canonical:bool,canonical_definition_id:?string,canonical_status:?string,runtime_registered:?bool}>
     */
    public function associationHealth(array $objectTypes): array
    {
        return $this->objectTypeImpact($objectTypes);
    }

    /** @return array{state:string,registered:?bool,definition_status:string} */
    public function runtimeHealth(Definition $definition): array
    {
        $key = $definition->payload['taxonomy_key'] ?? null;
        $key = is_string($key) ? trim($key) : '';
        $registered = $key !== '' && function_exists('taxonomy_exists')
            ? taxonomy_exists($key)
            : null;

        if ($definition->status !== DefinitionStatus::Published) {
            $state = 'inactive';
        } elseif ($registered === true) {
            $state = 'healthy';
        } elseif ($registered === false) {
            $state = 'degraded';
        } else {
            $state = 'unavailable';
        }

        return [
            'state' => $state,
            'registered' => $registered,
            'definition_status' => $definition->status->value,
        ];
    }

    /**
     * @param mixed $value
     * @return list<array{key:string,state:string,canonical:bool,canonical_definition_id:?string,canonical_status:?string,runtime_registered:?bool}>
     */
    private function objectTypeImpact(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            return [];
        }

        /** @var array<string,Definition> $canonical */
        $canonical = [];
        foreach ($this->definitions->byType(self::POST_TYPE_DEFINITION_TYPE) as $definition) {
            if ($definition->ownerSurfaceId !== self::POST_TYPE_OWNER_SURFACE_ID) {
                continue;
            }
            $key = $definition->payload['post_type_key'] ?? null;
            if (is_string($key) && trim($key) !== '') {
                $canonical[trim($key)] = $definition;
            }
        }

        $impact = [];
        $seen = [];
        foreach ($value as $rawKey) {
            if (!is_string($rawKey) || trim($rawKey) === '') {
                continue;
            }
            $key = trim($rawKey);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $definition = $canonical[$key] ?? null;
            $runtimeRegistered = function_exists('post_type_exists') ? post_type_exists($key) : null;
            $canonicalStatus = $definition instanceof Definition ? $definition->status->value : null;

            if ($definition instanceof Definition && $definition->status !== DefinitionStatus::Published) {
                $state = 'disabled';
            } elseif ($runtimeRegistered === false) {
                $state = 'missing';
            } elseif ($definition instanceof Definition) {
                $state = $runtimeRegistered === null ? 'unavailable' : 'healthy';
            } elseif ($runtimeRegistered === true) {
                $state = $this->isBuiltInPostType($key) ? 'healthy' : 'external';
            } else {
                $state = 'unavailable';
            }

            $impact[] = [
                'key' => $key,
                'state' => $state,
                'canonical' => $definition instanceof Definition,
                'canonical_definition_id' => $definition?->id,
                'canonical_status' => $canonicalStatus,
                'runtime_registered' => $runtimeRegistered,
            ];
        }

        return $impact;
    }

    /** @return array{id:string,resolved:bool,slug:?string,type:?string,owner_surface_id:?int,status:?string} */
    private function reference(string $id): array
    {
        $definition = $this->definitions->get($id);
        if (!$definition instanceof Definition) {
            return [
                'id' => $id,
                'resolved' => false,
                'slug' => null,
                'type' => null,
                'owner_surface_id' => null,
                'status' => null,
            ];
        }

        return $this->referenceFromDefinition($definition);
    }

    /** @return array{id:string,resolved:bool,slug:string,type:string,owner_surface_id:int,status:string} */
    private function referenceFromDefinition(Definition $definition): array
    {
        return [
            'id' => $definition->id,
            'resolved' => true,
            'slug' => $definition->slug,
            'type' => $definition->type,
            'owner_surface_id' => $definition->ownerSurfaceId,
            'status' => $definition->status->value,
        ];
    }

    private function isBuiltInPostType(string $key): bool
    {
        if (!function_exists('get_post_type_object')) {
            return false;
        }
        $object = get_post_type_object($key);
        return is_object($object) && ($object->_builtin ?? false) === true;
    }
}
