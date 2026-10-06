<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class DashboardWidgetPresetReadService
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private DashboardWidgetPresetCompiler $compiler,
        private DashboardWidgetPresetResolver $resolver,
    ) {}

    /** @return array<string,mixed>|null */
    public function get(string $id): ?array
    {
        $definition = $this->definitions->get($id);
        if (
            !$definition instanceof Definition
            || $definition->type !== DashboardWidgetPresetDefinition::TYPE
            || $definition->ownerSurfaceId !== DashboardWidgetPresetDefinition::OWNER_SURFACE_ID
            || $definition->status !== DefinitionStatus::Published
        ) {
            return null;
        }

        return $this->compiler->compile($definition)->toArray();
    }

    /** @return list<array<string,mixed>> */
    public function catalog(): array
    {
        $definitions = array_values(array_filter(
            $this->definitions->byType(DashboardWidgetPresetDefinition::TYPE),
            static fn (Definition $definition): bool => $definition->status === DefinitionStatus::Published,
        ));
        usort(
            $definitions,
            static fn (Definition $left, Definition $right): int =>
                ($left->slug <=> $right->slug) ?: ($left->id <=> $right->id),
        );

        $result = [];
        foreach ($definitions as $definition) {
            $result[] = $this->compiler->compile($definition)->toArray();
        }

        return $result;
    }

    /** @return array<string,mixed> */
    public function effective(ExecutionContext $context): array
    {
        return $this->resolver->resolve($context)->toArray();
    }
}
