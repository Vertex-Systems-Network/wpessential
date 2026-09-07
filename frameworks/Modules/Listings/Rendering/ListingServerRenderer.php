<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\Rendering;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use Throwable;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Modules\Listings\Definition\ListingCompiledDescriptor;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryBinding;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryReader;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Rendering\RenderInput;

final readonly class ListingServerRenderer
{
    public function __construct(
        private ListingQueryReader $queryReader,
        private ComponentBlueprintRegistryInterface $blueprints,
        private RendererInterface $renderer,
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

        $blueprint = $this->blueprints->get($descriptor->blueprintId, $descriptor->blueprintRevision);
        if ($blueprint === null || $blueprint->id !== $descriptor->blueprintId || $blueprint->revision !== $descriptor->blueprintRevision) {
            return $this->failure('missing_blueprint');
        }

        foreach ($binding->projection as $fieldRef) {
            if (!array_key_exists($fieldRef, $blueprint->bindingSchema)) {
                return $this->failure('blueprint_projection_mismatch');
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
            foreach ($binding->projection as $fieldRef) {
                $value = $row[$fieldRef] ?? null;
                $expectedType = $blueprint->bindingSchema[$fieldRef];
                if (!$this->matchesBindingType($value, $expectedType)) {
                    return $this->failure('blueprint_binding_type_mismatch');
                }
                /** @var scalar|list<scalar>|null $value */
                $bindings[$fieldRef] = $value;
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
        return new ListingRenderResult(false, '', [], 0, $code);
    }
}
