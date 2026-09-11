<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinition;

final readonly class TaxonomyValidationService
{
    private const PREVIEW_ID = '00000000-0000-4000-8000-000000000002';
    private const POST_TYPE_DEFINITION_TYPE = 'post_type';
    private const POST_TYPE_OWNER_SURFACE_ID = 1;

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private TaxonomyDefinitionProjector $projector,
    ) {}

    /**
     * @param array<string,mixed> $input
     * @return array{
     *   valid:bool,
     *   issues:list<array{id:string,severity:string,field:string,message:string}>,
     *   candidate:array{taxonomy_key:?string},
     *   diagnostics:?array<string,mixed>
     * }
     */
    public function validate(array $input): array
    {
        $issues = [];
        $payload = $input['payload'] ?? null;
        if (!is_array($payload) || array_is_list($payload)) {
            $issues[] = $this->issue('payload_invalid', 'blocked', 'payload', 'Taxonomy payload must be an object/map.');
            return $this->report(null, $issues, null);
        }

        $current = $this->currentDefinition($input, $issues);
        $keyValue = $payload['taxonomy_key'] ?? null;
        $key = is_string($keyValue) ? trim($keyValue) : null;

        $candidate = new Definition(
            id: $current?->id ?? self::PREVIEW_ID,
            slug: $current?->slug ?? ('taxonomy-preview-' . ($key !== null && $key !== '' ? str_replace('_', '-', $key) : 'candidate')),
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: $current?->schemaVersion ?? 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: $payload,
            revision: ($current?->revision ?? 0) + 1,
            dependencies: $current?->dependencies ?? [],
        );

        $registration = null;
        try {
            $registration = $this->projector->project($candidate);
        } catch (InvalidArgumentException $exception) {
            $issues[] = $this->issue('registration_schema_invalid', 'blocked', 'payload', $exception->getMessage());
        }

        if ($key !== null && $key !== '') {
            $this->validateCanonicalOwnership($key, $current?->id, $issues);
            $this->validateRuntimeOwnership($key, $current, $issues);
        }
        $this->validateObjectTypeDependencies($payload, $issues);
        if ($registration instanceof RegistrationDefinition) {
            $this->validateRoutingCollisions($candidate, $registration, $issues);
            $this->validateBlockEditorCompatibility($registration, $issues);
        }

        return $this->report(
            $key,
            $issues,
            $registration instanceof RegistrationDefinition ? $this->diagnostics($candidate, $registration) : null,
        );
    }

    /**
     * @param array<string,mixed> $input
     * @param list<array{id:string,severity:string,field:string,message:string}> $issues
     */
    private function currentDefinition(array $input, array &$issues): ?Definition
    {
        $id = $input['id'] ?? null;
        if ($id === null || $id === '') {
            return null;
        }
        if (!is_string($id)) {
            $issues[] = $this->issue('definition_id_invalid', 'blocked', 'id', 'Taxonomy id must be a string.');
            return null;
        }

        $definition = $this->definitions->get($id);
        if (!$definition instanceof Definition
            || $definition->type !== TaxonomyDefinitionProjector::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
        ) {
            $issues[] = $this->issue('definition_not_found', 'blocked', 'id', 'Taxonomy definition was not found in the canonical Surface 2 owner.');
            return null;
        }

        $existingKey = $definition->payload['taxonomy_key'] ?? null;
        $requestedKey = $input['payload']['taxonomy_key'] ?? null;
        if (is_string($existingKey) && is_string($requestedKey) && trim($existingKey) !== trim($requestedKey)) {
            $issues[] = $this->issue(
                'runtime_key_immutable',
                'blocked',
                'taxonomy_key',
                'Existing Taxonomy taxonomy_key is immutable; create a new definition for a different runtime key.',
            );
        }

        return $definition;
    }

    /** @param list<array{id:string,severity:string,field:string,message:string}> $issues */
    private function validateCanonicalOwnership(string $key, ?string $currentDefinitionId, array &$issues): void
    {
        foreach ($this->definitions->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE) as $definition) {
            if ($definition->ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
                || $definition->id === $currentDefinitionId
            ) {
                continue;
            }

            $existingKey = $definition->payload['taxonomy_key'] ?? null;
            if (is_string($existingKey) && trim($existingKey) === $key) {
                $issues[] = $this->issue(
                    'duplicate_definition',
                    'blocked',
                    'taxonomy_key',
                    sprintf('Taxonomy key "%s" is already owned by another canonical Taxonomy definition.', $key),
                );
                return;
            }
        }
    }

    /** @param list<array{id:string,severity:string,field:string,message:string}> $issues */
    private function validateRuntimeOwnership(string $key, ?Definition $current, array &$issues): void
    {
        if (!function_exists('taxonomy_exists') || !taxonomy_exists($key)) {
            return;
        }

        $currentKey = $current?->payload['taxonomy_key'] ?? null;
        $isCurrentPublishedRegistration = $current instanceof Definition
            && $current->status === DefinitionStatus::Published
            && is_string($currentKey)
            && trim($currentKey) === $key;
        if ($isCurrentPublishedRegistration) {
            $issues[] = $this->issue(
                'runtime_registration_present',
                'info',
                'taxonomy_key',
                sprintf('Taxonomy key "%s" is active at runtime. WordPress does not reliably identify the registering owner, so ownership is not inferred from runtime presence alone.', $key),
            );
            return;
        }

        $issues[] = $this->issue(
            'runtime_registration_collision',
            'blocked',
            'taxonomy_key',
            sprintf('Taxonomy key "%s" is already registered by WordPress or another runtime owner.', $key),
        );
    }

    /**
     * @param array<string,mixed> $payload
     * @param list<array{id:string,severity:string,field:string,message:string}> $issues
     */
    private function validateObjectTypeDependencies(array $payload, array &$issues): void
    {
        $objectTypes = $payload['object_types'] ?? null;
        if (!is_array($objectTypes) || !array_is_list($objectTypes) || !function_exists('post_type_exists')) {
            return;
        }

        foreach ($objectTypes as $objectType) {
            if (!is_string($objectType) || trim($objectType) === '') {
                continue;
            }
            $objectType = trim($objectType);
            if (!post_type_exists($objectType)) {
                $issues[] = $this->issue(
                    'missing_object_type',
                    'compatibility_warning',
                    'object_types',
                    sprintf('Object type "%s" is not currently registered; the Taxonomy can remain defined but this relationship is degraded.', $objectType),
                );
            }
        }
    }

    /** @param list<array{id:string,severity:string,field:string,message:string}> $issues */
    private function validateRoutingCollisions(
        Definition $candidate,
        RegistrationDefinition $candidateRegistration,
        array &$issues,
    ): void {
        $candidateRestRoute = $this->effectiveRestRoute($candidateRegistration);
        $candidateRewriteBase = $this->effectiveRewriteBase($candidateRegistration);
        $candidateQueryVar = $this->effectiveQueryVar($candidateRegistration);

        foreach ($this->definitions->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE) as $definition) {
            if ($definition->ownerSurfaceId !== TaxonomyDefinitionProjector::OWNER_SURFACE_ID
                || $definition->id === $candidate->id
                || $definition->status !== DefinitionStatus::Published
            ) {
                continue;
            }

            try {
                $registration = $this->projector->project($definition);
            } catch (InvalidArgumentException) {
                continue;
            }

            if ($candidateRestRoute !== null
                && $candidateRestRoute === $this->effectiveRestRoute($registration)
            ) {
                $issues[] = $this->issue(
                    'rest_route_collision',
                    'compatibility_warning',
                    'rest_base',
                    sprintf(
                        'REST route "%s" is also used by published taxonomy "%s"; REST requests may be ambiguous.',
                        $candidateRestRoute,
                        $registration->key,
                    ),
                );
            }

            if ($candidateRewriteBase !== null
                && $candidateRewriteBase === $this->effectiveRewriteBase($registration)
            ) {
                $issues[] = $this->issue(
                    'rewrite_route_collision',
                    'compatibility_warning',
                    'rewrite',
                    sprintf(
                        'Rewrite base "%s" is also used by published taxonomy "%s"; route resolution may be ambiguous.',
                        $candidateRewriteBase,
                        $registration->key,
                    ),
                );
            }

            if ($candidateQueryVar !== null
                && $candidateQueryVar === $this->effectiveQueryVar($registration)
            ) {
                $issues[] = $this->issue(
                    'query_var_collision',
                    'compatibility_warning',
                    'query_var',
                    sprintf(
                        'Query variable "%s" is also used by published taxonomy "%s"; requests may be ambiguous.',
                        $candidateQueryVar,
                        $registration->key,
                    ),
                );
            }
        }
    }

    /** @param list<array{id:string,severity:string,field:string,message:string}> $issues */
    private function validateBlockEditorCompatibility(
        RegistrationDefinition $registration,
        array &$issues,
    ): void {
        $args = is_array($registration->payload['args'] ?? null) ? $registration->payload['args'] : [];
        if (($args['show_in_rest'] ?? false) === true || !function_exists('use_block_editor_for_post_type')) {
            return;
        }

        $objectTypes = is_array($registration->payload['object_types'] ?? null)
            ? array_values(array_filter($registration->payload['object_types'], 'is_string'))
            : [];
        $blockEditorTypes = [];
        foreach ($objectTypes as $objectType) {
            if (function_exists('post_type_exists') && !post_type_exists($objectType)) {
                continue;
            }
            if (use_block_editor_for_post_type($objectType)) {
                $blockEditorTypes[] = $objectType;
            }
        }
        if ($blockEditorTypes === []) {
            return;
        }

        $issues[] = $this->issue(
            'block_editor_rest_disabled',
            'compatibility_warning',
            'show_in_rest',
            sprintf(
                'REST exposure is disabled while block-editor object type(s) "%s" are associated; REST-backed taxonomy controls will be unavailable there.',
                implode(', ', $blockEditorTypes),
            ),
        );
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

    /** @return array<string,mixed> */
    private function diagnostics(Definition $candidate, RegistrationDefinition $registration): array
    {
        $registrationPayload = $registration->payload;
        $args = is_array($registrationPayload['args'] ?? null) ? $registrationPayload['args'] : [];
        $objectTypes = is_array($registrationPayload['object_types'] ?? null)
            ? array_values(array_filter($registrationPayload['object_types'], 'is_string'))
            : [];
        $providerIds = is_array($registrationPayload['provider_ids'] ?? null)
            ? $registrationPayload['provider_ids']
            : [];

        return [
            'effective_args' => $args,
            'overrides' => $this->explicitOverrides($candidate->payload, $args, $providerIds),
            'provider_ids' => $providerIds,
            'association_health' => $this->associationHealth($objectTypes),
            'runtime' => [
                'registered' => function_exists('taxonomy_exists') ? taxonomy_exists($registration->key) : null,
            ],
            'previews' => [
                'rest' => $this->restPreview($registration->key, $args),
                'rewrite' => $this->rewritePreview($registration->key, $args),
            ],
        ];
    }

    /**
     * @param array<string,mixed> $payload
     * @param array<string,mixed> $args
     * @param array<string,mixed> $providerIds
     * @return array<string,mixed>
     */
    private function explicitOverrides(array $payload, array $args, array $providerIds): array
    {
        $overrides = [];
        $effectiveLabels = is_array($args['labels'] ?? null) ? $args['labels'] : [];
        $labelOverrides = [];
        foreach (['name', 'singular_name'] as $key) {
            if (array_key_exists($key, $effectiveLabels)) {
                $labelOverrides[$key] = $effectiveLabels[$key];
            }
        }
        $authoredLabels = is_array($payload['labels'] ?? null) ? $payload['labels'] : [];
        foreach (array_keys($authoredLabels) as $key) {
            if (is_string($key) && array_key_exists($key, $effectiveLabels)) {
                $labelOverrides[$key] = $effectiveLabels[$key];
            }
        }
        if ($labelOverrides !== []) {
            ksort($labelOverrides, SORT_STRING);
            $overrides['labels'] = $labelOverrides;
        }

        foreach ([
            'description', 'public', 'publicly_queryable', 'hierarchical', 'show_ui', 'show_in_menu',
            'show_in_nav_menus', 'show_tagcloud', 'show_in_quick_edit', 'show_admin_column',
            'show_in_rest', 'rest_base', 'rest_namespace', 'query_var', 'rewrite', 'sort',
            'capabilities', 'default_term', 'args',
        ] as $key) {
            if (array_key_exists($key, $payload) && array_key_exists($key, $args)) {
                $overrides[$key] = $args[$key];
            }
        }

        if ($providerIds !== []) {
            $overrides['provider_ids'] = $providerIds;
        }
        ksort($overrides, SORT_STRING);
        return $overrides;
    }

    /**
     * @param list<string> $objectTypes
     * @return list<array{key:string,state:string,canonical:bool,canonical_status:?string,runtime_registered:?bool}>
     */
    private function associationHealth(array $objectTypes): array
    {
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

        $health = [];
        foreach ($objectTypes as $key) {
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

            $health[] = [
                'key' => $key,
                'state' => $state,
                'canonical' => $definition instanceof Definition,
                'canonical_status' => $canonicalStatus,
                'runtime_registered' => $runtimeRegistered,
            ];
        }

        return $health;
    }

    private function isBuiltInPostType(string $key): bool
    {
        if (!function_exists('get_post_type_object')) {
            return false;
        }
        $object = get_post_type_object($key);
        return is_object($object) && ($object->_builtin ?? false) === true;
    }

    /** @param array<string,mixed> $args @return array<string,mixed> */
    private function restPreview(string $taxonomyKey, array $args): array
    {
        if (($args['show_in_rest'] ?? false) !== true) {
            return ['enabled' => false, 'route' => null];
        }

        $namespace = is_string($args['rest_namespace'] ?? null) ? trim($args['rest_namespace'], '/') : 'wp/v2';
        $base = is_string($args['rest_base'] ?? null) ? trim($args['rest_base'], '/') : $taxonomyKey;
        return [
            'enabled' => true,
            'namespace' => $namespace,
            'base' => $base,
            'route' => '/' . $namespace . '/' . $base,
        ];
    }

    /** @param array<string,mixed> $args @return array<string,mixed> */
    private function rewritePreview(string $taxonomyKey, array $args): array
    {
        $rewrite = $args['rewrite'] ?? true;
        if ($rewrite === false) {
            return ['enabled' => false, 'path_pattern' => null];
        }

        $slug = $taxonomyKey;
        $hierarchical = false;
        if (is_array($rewrite)) {
            if (is_string($rewrite['slug'] ?? null) && $rewrite['slug'] !== '') {
                $slug = $rewrite['slug'];
            }
            $hierarchical = ($rewrite['hierarchical'] ?? false) === true;
        }

        return [
            'enabled' => true,
            'slug' => $slug,
            'hierarchical' => $hierarchical,
            'path_pattern' => '/' . trim($slug, '/') . '/' . ($hierarchical ? '{parent/.../}' : '') . '{term-slug}/',
        ];
    }

    /**
     * @param list<array{id:string,severity:string,field:string,message:string}> $issues
     * @param array<string,mixed>|null $diagnostics
     * @return array{
     *   valid:bool,
     *   issues:list<array{id:string,severity:string,field:string,message:string}>,
     *   candidate:array{taxonomy_key:?string},
     *   diagnostics:?array<string,mixed>
     * }
     */
    private function report(?string $key, array $issues, ?array $diagnostics): array
    {
        $valid = true;
        foreach ($issues as $issue) {
            if ($issue['severity'] === 'blocked') {
                $valid = false;
                break;
            }
        }

        return [
            'valid' => $valid,
            'issues' => $issues,
            'candidate' => ['taxonomy_key' => $key],
            'diagnostics' => $diagnostics,
        ];
    }

    /** @return array{id:string,severity:string,field:string,message:string} */
    private function issue(string $id, string $severity, string $field, string $message): array
    {
        return [
            'id' => $id,
            'severity' => $severity,
            'field' => $field,
            'message' => $message,
        ];
    }
}
