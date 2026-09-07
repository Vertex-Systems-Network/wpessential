<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\Rendering;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use Throwable;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Modules\Listings\Definition\ListingCompiledDescriptor;
use WPEssential\Modules\Listings\Definition\ListingRenderBinding;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryBinding;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryReader;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\DynamicValues\DynamicValueRequest;
use WPEssential\Platform\Rendering\RenderInput;

final readonly class ListingServerRenderer
{
    public function __construct(
        private ListingQueryReader $queryReader,
        private ComponentBlueprintRegistryInterface $blueprints,
        private RendererInterface $renderer,
        private DynamicValueResolverInterface $dynamicValues,
    ) {
    }

    /** @param array<string,mixed> $parameters */
    public function render(
        ListingCompiledDescriptor $descriptor,
        ListingQueryBinding $binding,
        array $parameters,
        ExecutionContext $context,
    ): ListingRenderResult {
        if ($binding->sourceRef !== $descriptor->querySourceRef) {
            return $this->failure('listing_query_source_mismatch');
        }

        if ($descriptor->renderBindings === []) {
            return $this->failure('missing_render_binding_plan');
        }

        $blueprint = $this->blueprints->get($descriptor->blueprintId, $descriptor->blueprintRevision);
        if ($blueprint === null || $blueprint->id !== $descriptor->blueprintId || $blueprint->revision !== $descriptor->blueprintRevision) {
            return $this->failure('missing_blueprint');
        }

        $projection = array_fill_keys($binding->projection, true);
        foreach ($descriptor->renderBindings as $renderBinding) {
            if (!array_key_exists($renderBinding->bindingKey, $blueprint->bindingSchema)) {
                return $this->failure('blueprint_binding_plan_mismatch');
            }
            if ($blueprint->bindingSchema[$renderBinding->bindingKey] !== $renderBinding->expectedType) {
                return $this->failure('blueprint_binding_type_mismatch');
            }

            $requiredField = $renderBinding->kind === 'query_field'
                ? $renderBinding->queryFieldRef
                : $renderBinding->resourceIdFieldRef;
            if ($requiredField === null || !isset($projection[$requiredField])) {
                return $this->failure('query_projection_binding_mismatch');
            }
        }

        try {
            $queryResult = $this->queryReader->read($binding, $parameters, $context);
        } catch (InvalidArgumentException) {
            return $this->failure('invalid_query_binding');
        }

        if (!$queryResult->ok) {
            return $this->failure($queryResult->errorCode ?? 'query_failure');
        }

        /** @var array<string,true> $assets */
        $assets = [];
        foreach (array_merge($descriptor->assetHandles, $blueprint->assetHandles) as $handle) {
            $assets[$handle] = true;
        }

        if ($queryResult->rows === []) {
            return new ListingRenderResult(
                success: true,
                html: '<p class="wpe-listing__empty" role="status">No results.</p>',
                assetHandles: array_keys($assets),
                returned: 0,
            );
        }

        /** @var list<string> $items */
        $items = [];
        foreach ($queryResult->rows as $row) {
            /** @var array<string, scalar|list<scalar>|null> $bindings */
            $bindings = [];
            foreach ($descriptor->renderBindings as $renderBinding) {
                $resolved = $this->resolveBinding($renderBinding, $row, $context);
                if (!$resolved['ok']) {
                    return $this->failure($resolved['failure']);
                }
                $value = $resolved['value'];
                if (!$this->matchesBindingType($value, $renderBinding->expectedType)) {
                    return $this->failure('blueprint_binding_type_mismatch');
                }
                /** @var scalar|list<scalar>|null $value */
                $bindings[$renderBinding->bindingKey] = $value;
            }

            try {
                $output = $this->renderer->render(
                    new RenderInput($descriptor->blueprintId, $descriptor->blueprintRevision, $bindings),
                    $context,
                );
            } catch (Throwable) {
                return $this->failure('renderer_failure');
            }

            if (!$output->success) {
                return $this->failure($output->failure?->value ?? 'renderer_failure');
            }

            foreach ($output->assetHandles as $handle) {
                $assets[$handle] = true;
            }
            $items[] = $output->html;
        }

        $html = $descriptor->layoutMode === 'list'
            ? '<ul class="wpe-listing wpe-listing--list"><li class="wpe-listing__item">' . implode('</li><li class="wpe-listing__item">', $items) . '</li></ul>'
            : '<div class="wpe-listing wpe-listing--grid">' . implode('', array_map(
                static fn (string $item): string => '<div class="wpe-listing__item">' . $item . '</div>',
                $items,
            )) . '</div>';

        return new ListingRenderResult(
            success: true,
            html: $html,
            assetHandles: array_keys($assets),
            returned: count($items),
        );
    }

    /**
     * @param array<string,mixed> $row
     * @return array{ok:bool,value:mixed,failure:string}
     */
    private function resolveBinding(ListingRenderBinding $binding, array $row, ExecutionContext $context): array
    {
        if ($binding->kind === 'query_field') {
            $fieldRef = $binding->queryFieldRef;
            if ($fieldRef === null || !array_key_exists($fieldRef, $row)) {
                return ['ok' => false, 'value' => null, 'failure' => 'missing_query_binding_value'];
            }
            return ['ok' => true, 'value' => $row[$fieldRef], 'failure' => ''];
        }

        $resourceIdField = $binding->resourceIdFieldRef;
        if ($resourceIdField === null || !array_key_exists($resourceIdField, $row)) {
            return ['ok' => false, 'value' => null, 'failure' => 'missing_dynamic_resource_id'];
        }
        $resourceId = $row[$resourceIdField];
        if (!is_string($resourceId) && !is_int($resourceId)) {
            return ['ok' => false, 'value' => null, 'failure' => 'invalid_dynamic_resource_id'];
        }

        try {
            $result = $this->dynamicValues->resolve(
                new DynamicValueRequest(
                    sourceRef: (string) $binding->sourceRef,
                    valueRef: (string) $binding->valueRef,
                    resourceType: (string) $binding->resourceType,
                    resourceId: $resourceId,
                ),
                $context,
            );
        } catch (Throwable) {
            return ['ok' => false, 'value' => null, 'failure' => 'dynamic_value_failure'];
        }

        if (!$result->resolved) {
            return [
                'ok' => false,
                'value' => null,
                'failure' => $result->failure?->value ?? 'dynamic_value_failure',
            ];
        }

        return ['ok' => true, 'value' => $result->value, 'failure' => ''];
    }

    private function matchesBindingType(mixed $value, string $type): bool
    {
        if ($value === null) {
            return true;
        }

        return match ($type) {
            'string' => is_string($value),
            'int' => is_int($value),
            'float' => is_float($value),
            'bool' => is_bool($value),
            'string_list' => $this->isTypedList($value, 'string'),
            'int_list' => $this->isTypedList($value, 'int'),
            default => false,
        };
    }

    private function isTypedList(mixed $value, string $type): bool
    {
        if (!is_array($value) || !array_is_list($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (($type === 'string' && !is_string($item)) || ($type === 'int' && !is_int($item))) {
                return false;
            }
        }
        return true;
    }

    private function failure(string $code): ListingRenderResult
    {
        if (!preg_match('/^[a-z][a-z0-9_.-]{0,127}$/', $code)) {
            $code = 'listing_failure';
        }

        return new ListingRenderResult(false, '', [], 0, $code);
    }
}
