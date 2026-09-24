<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use LogicException;
use WPEssential\Platform\Rendering\RenderInput;

final readonly class DashboardWidgetRenderSourceDescriptor
{
    /**
     * @param array<string, scalar|list<scalar>> $bindings
     * @param array<string,array{source_ref:string,value_ref:string,resource:string,binding_type:string}> $dynamicBindings
     */
    public function __construct(
        public string $definitionId,
        public int $definitionRevision,
        public string $blueprintId,
        public int $blueprintRevision,
        public array $bindings,
        public ?DashboardWidgetQueryBindingDescriptor $query = null,
        public ?DashboardWidgetEmptyStateDescriptor $emptyState = null,
        public ?DashboardWidgetErrorStateDescriptor $errorState = null,
        public array $dynamicBindings = [],
    ) {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->definitionId)) {
            throw new InvalidArgumentException('Dashboard Widget render-source descriptor definition id must be a lowercase RFC 4122 UUID.');
        }
        if ($this->definitionRevision < 1) {
            throw new InvalidArgumentException('Dashboard Widget render-source descriptor definition revision must be positive.');
        }
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->blueprintId)) {
            throw new InvalidArgumentException('Dashboard Widget render-source Blueprint id must be a lowercase RFC 4122 UUID.');
        }
        if ($this->blueprintRevision < 1) {
            throw new InvalidArgumentException('Dashboard Widget render-source Blueprint revision must be positive.');
        }
        $queryBindingCount = $this->query !== null ? count($this->query->bindings) : 0;
        if (count($this->bindings) + $queryBindingCount + count($this->dynamicBindings) > 128) {
            throw new InvalidArgumentException('Dashboard Widget render-source bindings exceed the bounded V1 limit.');
        }

        if ($this->emptyState !== null && $this->query === null) {
            throw new InvalidArgumentException('Dashboard Widget empty-state metadata requires unresolved Query bindings.');
        }

        foreach ($this->bindings as $key => $value) {
            if (!is_string($key) || preg_match('/^[a-z][a-z0-9_.-]{0,127}$/', $key) !== 1) {
                throw new InvalidArgumentException('Dashboard Widget render-source binding keys must be stable semantic identifiers.');
            }
            $this->assertSafeValue($value);
            if ($this->query !== null && array_key_exists($key, $this->query->bindings)) {
                throw new InvalidArgumentException('Dashboard Widget literal and Query bindings cannot target the same Blueprint binding key.');
            }
            if (array_key_exists($key, $this->dynamicBindings)) {
                throw new InvalidArgumentException('Dashboard Widget literal and Dynamic bindings cannot target the same Blueprint binding key.');
            }
        }

        foreach ($this->dynamicBindings as $key => $binding) {
            if (!is_string($key) || preg_match('/^[a-z][a-z0-9_.-]{0,127}$/', $key) !== 1) {
                throw new InvalidArgumentException('Dashboard Widget Dynamic binding keys must be stable semantic identifiers.');
            }
            if ($this->query !== null && array_key_exists($key, $this->query->bindings)) {
                throw new InvalidArgumentException('Dashboard Widget Query and Dynamic bindings cannot target the same Blueprint binding key.');
            }
            foreach (['source_ref', 'value_ref'] as $refKey) {
                $ref = $binding[$refKey] ?? null;
                if (!is_string($ref) || $ref === '' || strlen($ref) > 160 || preg_match('/^[a-zA-Z0-9_.:-]+$/', $ref) !== 1) {
                    throw new InvalidArgumentException('Dashboard Widget Dynamic binding references must be bounded semantic identifiers.');
                }
            }
            if (!in_array($binding['resource'] ?? null, ['site', 'user', 'network'], true)) {
                throw new InvalidArgumentException('Dashboard Widget Dynamic binding resource is unsupported.');
            }
            if (!in_array($binding['binding_type'] ?? null, ['string', 'int', 'float', 'bool', 'string_list', 'int_list'], true)) {
                throw new InvalidArgumentException('Dashboard Widget Dynamic binding type is unsupported.');
            }
        }
    }

    public function toRenderInput(): RenderInput
    {
        if ($this->query !== null || $this->dynamicBindings !== []) {
            throw new LogicException('Dashboard Widget unresolved Query or Dynamic bindings must be resolved before renderer invocation.');
        }

        return new RenderInput(
            $this->blueprintId,
            $this->blueprintRevision,
            $this->bindings,
        );
    }

    /**
     * @param array<string, scalar|list<scalar>> $bindings
     */
    public function resolvedWith(array $bindings): self
    {
        return new self(
            definitionId: $this->definitionId,
            definitionRevision: $this->definitionRevision,
            blueprintId: $this->blueprintId,
            blueprintRevision: $this->blueprintRevision,
            bindings: $bindings,
            query: null,
            emptyState: null,
            errorState: $this->errorState,
            dynamicBindings: $this->dynamicBindings,
        );
    }

    /**
     * @param array<string, scalar|list<scalar>> $bindings
     */
    public function resolvedDynamicWith(array $bindings): self
    {
        if ($this->query !== null) {
            throw new LogicException('Dashboard Widget Query bindings must resolve before Dynamic bindings.');
        }

        return new self(
            definitionId: $this->definitionId,
            definitionRevision: $this->definitionRevision,
            blueprintId: $this->blueprintId,
            blueprintRevision: $this->blueprintRevision,
            bindings: $bindings,
            query: null,
            emptyState: null,
            errorState: $this->errorState,
            dynamicBindings: [],
        );
    }

    public function resolvedEmptyState(): self
    {
        if ($this->emptyState === null || $this->query === null) {
            throw new LogicException('Dashboard Widget empty-state resolution requires unresolved Query bindings and authored empty state.');
        }

        return new self(
            definitionId: $this->definitionId,
            definitionRevision: $this->definitionRevision,
            blueprintId: $this->emptyState->blueprintId,
            blueprintRevision: $this->emptyState->blueprintRevision,
            bindings: $this->emptyState->bindings,
            query: null,
            emptyState: null,
            errorState: $this->errorState,
        );
    }

    private function assertSafeValue(mixed $value): void
    {
        if (is_string($value)) {
            if (preg_match('/<\?(?:php|=)|<script\b|javascript:/i', $value)) {
                throw new InvalidArgumentException('Executable authored channels are not accepted as Dashboard Widget render bindings.');
            }
            return;
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return;
        }

        if (is_array($value) && array_is_list($value)) {
            foreach ($value as $item) {
                if (!is_string($item) && !is_int($item) && !is_float($item) && !is_bool($item)) {
                    throw new InvalidArgumentException('Dashboard Widget render-source binding lists must contain scalars only.');
                }
                if (is_string($item) && preg_match('/<\?(?:php|=)|<script\b|javascript:/i', $item)) {
                    throw new InvalidArgumentException('Executable authored channels are not accepted as Dashboard Widget render binding list values.');
                }
            }
            return;
        }

        throw new InvalidArgumentException('Dashboard Widget render-source bindings must be non-null scalars or scalar lists.');
    }
}
