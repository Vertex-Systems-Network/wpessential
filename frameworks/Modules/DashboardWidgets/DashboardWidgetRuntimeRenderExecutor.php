<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class DashboardWidgetRuntimeRenderExecutor
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private DashboardWidgetRegistrationCompiler $registrationCompiler,
        private DashboardWidgetVisibilityCompiler $visibilityCompiler,
        private DashboardWidgetVisibilityEvaluator $visibilityEvaluator,
        private DashboardWidgetRenderSourceCompiler $renderSourceCompiler,
        private RendererInterface $renderer,
        private ?DashboardWidgetQueryBindingExecutor $queryBindingExecutor = null,
    ) {}

    public function render(
        string $definitionId,
        ExecutionContext $context,
    ): DashboardWidgetRuntimeRenderResult {
        try {
            $definition = $this->definitions->get($definitionId);
        } catch (Throwable) {
            return DashboardWidgetRuntimeRenderResult::runtimeFailure();
        }

        if ($definition === null) {
            return DashboardWidgetRuntimeRenderResult::missingDefinition();
        }

        try {
            $this->registrationCompiler->compile($definition);
            $visibility = $this->visibilityCompiler->compile($definition);
        } catch (InvalidArgumentException) {
            return DashboardWidgetRuntimeRenderResult::invalidDefinition();
        } catch (Throwable) {
            return DashboardWidgetRuntimeRenderResult::runtimeFailure();
        }

        try {
            $visibilityDecision = $this->visibilityEvaluator->evaluate($visibility, $context);
        } catch (Throwable) {
            return DashboardWidgetRuntimeRenderResult::runtimeFailure();
        }

        if (!$visibilityDecision->allowed) {
            return DashboardWidgetRuntimeRenderResult::visibilityDenied($visibilityDecision->reason);
        }

        try {
            $renderSource = $this->renderSourceCompiler->compile($definition);
        } catch (InvalidArgumentException) {
            return DashboardWidgetRuntimeRenderResult::invalidDefinition();
        } catch (Throwable) {
            return DashboardWidgetRuntimeRenderResult::runtimeFailure();
        }

        if ($renderSource->query !== null) {
            if ($this->queryBindingExecutor === null) {
                return DashboardWidgetRuntimeRenderResult::runtimeFailure();
            }

            try {
                $renderSource = $this->queryBindingExecutor->resolve($renderSource, $context);
            } catch (Throwable) {
                return DashboardWidgetRuntimeRenderResult::runtimeFailure();
            }

            if ($renderSource->query !== null || $renderSource->emptyState !== null) {
                return DashboardWidgetRuntimeRenderResult::runtimeFailure();
            }
        }

        try {
            $output = $this->renderer->render($renderSource->toRenderInput(), $context);
        } catch (Throwable) {
            return DashboardWidgetRuntimeRenderResult::runtimeFailure();
        }

        if (!$output->success) {
            if ($output->failure === null) {
                return DashboardWidgetRuntimeRenderResult::runtimeFailure();
            }

            $primaryFailure = $output->failure;
            if ($renderSource->errorState === null) {
                return DashboardWidgetRuntimeRenderResult::rendererFailed($primaryFailure);
            }

            try {
                $fallback = $this->renderer->render($renderSource->errorState->toRenderInput(), $context);
            } catch (Throwable) {
                return DashboardWidgetRuntimeRenderResult::runtimeFailure();
            }

            if (!$fallback->success) {
                if ($fallback->failure === null) {
                    return DashboardWidgetRuntimeRenderResult::runtimeFailure();
                }

                return DashboardWidgetRuntimeRenderResult::rendererFailed($fallback->failure);
            }

            return DashboardWidgetRuntimeRenderResult::renderedError(
                $fallback->html,
                $fallback->assetHandles,
                $primaryFailure,
            );
        }

        return DashboardWidgetRuntimeRenderResult::rendered(
            $output->html,
            $output->assetHandles,
        );
    }
}
