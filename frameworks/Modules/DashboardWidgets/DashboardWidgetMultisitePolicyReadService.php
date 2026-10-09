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

final readonly class DashboardWidgetMultisitePolicyReadService
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private DashboardWidgetMultisitePolicyCompiler $compiler,
        private DashboardWidgetMultisitePolicyResolver $resolver,
    ) {}

    /** @return array<string,mixed>|null */
    public function get(string $id): ?array
    {
        $definition = $this->definitions->get($id);
        if (
            !$definition instanceof Definition
            || $definition->ownerSurfaceId !== DashboardWidgetMultisitePolicyDefinition::OWNER_SURFACE_ID
            || $definition->type !== DashboardWidgetMultisitePolicyDefinition::TYPE
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
            $this->definitions->byType(DashboardWidgetMultisitePolicyDefinition::TYPE),
            static fn (Definition $definition): bool => $definition->status === DefinitionStatus::Published,
        ));
        usort($definitions, static fn (Definition $a, Definition $b): int =>
            ($a->slug <=> $b->slug) ?: ($a->id <=> $b->id));
        return array_map(fn (Definition $definition): array =>
            $this->compiler->compile($definition)->toArray(), $definitions);
    }

    /** @return array<string,mixed> */
    public function effective(ExecutionContext $context): array
    {
        return $this->resolver->resolve($context)->toArray();
    }
}
