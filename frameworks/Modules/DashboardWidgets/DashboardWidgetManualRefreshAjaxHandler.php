<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxHandlerInterface;

final readonly class DashboardWidgetManualRefreshAjaxHandler implements AjaxHandlerInterface
{
    public const ROUTE_TYPE = 'dashboard-widgets.refresh.manual';

    public const STATE_RENDERED = 'rendered';
    public const STATE_RENDERED_ERROR = 'rendered_error';
    public const STATE_UNAVAILABLE = 'unavailable';
    public const STATE_STALE_DEFINITION = 'stale_definition';
    public const STATE_INVALID_DEFINITION = 'invalid_definition';
    public const STATE_RUNTIME_FAILURE = 'runtime_failure';

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private DashboardWidgetRegistrationCompiler $registrations,
        private DashboardWidgetRuntimeRenderExecutor $renderer,
        private WordPressExecutionContextFactory $contexts,
    ) {}

    public function handle(array $payload): mixed
    {
        $request = $this->validRequest($payload);
        if ($request === null) {
            return $this->response(
                self::STATE_INVALID_DEFINITION,
                'The widget refresh request is invalid. Reload the dashboard and try again.',
            );
        }

        try {
            $current = $this->contexts->current();
            $context = new ExecutionContext(
                principal: $current->principal,
                siteId: $current->siteId,
                channel: ExecutionChannel::Ui,
                networkId: $current->networkId,
                correlationId: $current->correlationId,
            );
        } catch (Throwable) {
            return $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The widget could not be refreshed. Reload the dashboard and try again.',
            );
        }

        if (!$context->principal->isAuthenticated() || $context->principal->actorType !== 'user') {
            return $this->response(
                self::STATE_UNAVAILABLE,
                'This widget is not currently available for refresh.',
            );
        }

        try {
            $definition = $this->definitions->get($request['definition_id']);
        } catch (Throwable) {
            return $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The widget could not be refreshed. Reload the dashboard and try again.',
            );
        }

        if ($definition === null || $definition->revision !== $request['definition_revision']) {
            return $this->response(
                self::STATE_STALE_DEFINITION,
                'This widget changed. Reload the dashboard before refreshing it.',
            );
        }

        try {
            $descriptor = $this->registrations->compile($definition);
        } catch (InvalidArgumentException) {
            return $this->response(
                self::STATE_INVALID_DEFINITION,
                'This widget is not valid for manual refresh.',
            );
        } catch (Throwable) {
            return $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The widget could not be refreshed. Reload the dashboard and try again.',
            );
        }

        if (!$descriptor->manualRefresh || !$this->targetMatches($descriptor, $request['screen'], $context)) {
            return $this->response(
                self::STATE_UNAVAILABLE,
                'This widget is not currently available for refresh.',
            );
        }

        $result = $this->renderer->render($definition->id, $context);

        try {
            $currentDefinition = $this->definitions->get($request['definition_id']);
        } catch (Throwable) {
            return $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The widget could not be refreshed. Reload the dashboard and try again.',
            );
        }
        if ($currentDefinition === null || $currentDefinition->revision !== $request['definition_revision']) {
            return $this->response(
                self::STATE_STALE_DEFINITION,
                'This widget changed. Reload the dashboard before refreshing it.',
            );
        }

        if ($result->assetHandles !== []) {
            return $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The widget refresh requires unavailable assets. Reload the dashboard and try again.',
            );
        }

        return match ($result->status) {
            DashboardWidgetRuntimeRenderResult::STATUS_RENDERED => $this->response(
                self::STATE_RENDERED,
                'Widget refreshed.',
                $result->html,
            ),
            DashboardWidgetRuntimeRenderResult::STATUS_RENDERED_ERROR => $this->response(
                self::STATE_RENDERED_ERROR,
                'Widget refreshed with its configured error state.',
                $result->html,
            ),
            DashboardWidgetRuntimeRenderResult::STATUS_VISIBILITY_DENIED => $this->response(
                self::STATE_UNAVAILABLE,
                'This widget is not currently available for refresh.',
            ),
            DashboardWidgetRuntimeRenderResult::STATUS_INVALID_DEFINITION,
            DashboardWidgetRuntimeRenderResult::STATUS_MISSING_DEFINITION => $this->response(
                self::STATE_INVALID_DEFINITION,
                'This widget is not valid for manual refresh.',
            ),
            default => $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The widget could not be refreshed. Reload the dashboard and try again.',
            ),
        };
    }

    /**
     * @param array<string,mixed> $payload
     * @return array{definition_id:string,definition_revision:int,screen:string}|null
     */
    private function validRequest(array $payload): ?array
    {
        $keys = array_keys($payload);
        sort($keys, SORT_STRING);
        if ($keys !== ['definition_id', 'definition_revision', 'screen']) {
            return null;
        }

        $definitionId = $payload['definition_id'] ?? null;
        $revision = $payload['definition_revision'] ?? null;
        $screen = $payload['screen'] ?? null;
        if (
            !is_string($definitionId)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $definitionId) !== 1
            || !is_int($revision)
            || $revision < 1
            || !is_string($screen)
            || !in_array($screen, ['site', 'network'], true)
        ) {
            return null;
        }

        return [
            'definition_id' => $definitionId,
            'definition_revision' => $revision,
            'screen' => $screen,
        ];
    }

    private function targetMatches(
        DashboardWidgetRegistrationDescriptor $descriptor,
        string $screen,
        ExecutionContext $context,
    ): bool {
        if ($screen === 'network') {
            return $descriptor->networkDashboard && $context->networkId !== null;
        }

        return !$descriptor->networkDashboard && $descriptor->isEligibleForSite($context->siteId);
    }

    /** @return array{state:string,notice:string,html?:string} */
    private function response(string $state, string $notice, ?string $html = null): array
    {
        $result = [
            'state' => $state,
            'notice' => $notice,
        ];
        if ($html !== null) {
            $result['html'] = $html;
        }

        return $result;
    }
}
