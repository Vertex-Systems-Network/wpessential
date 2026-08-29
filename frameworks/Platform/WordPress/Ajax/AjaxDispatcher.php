<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Ajax;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Platform\WordPress\Security\NonceManager;

final class AjaxDispatcher
{
    /** @param callable(string):bool $capabilityChecker */
    public function __construct(
        private readonly AjaxRouteRegistry $routes,
        private readonly NonceManager $nonces,
        private readonly mixed $capabilityChecker,
    ) {}

    /** @param array<string,mixed> $request */
    public function dispatch(array $request, bool $authenticated): AjaxResponse
    {
        $type = isset($request['type']) && is_string($request['type']) ? trim($request['type']) : '';
        if ($type === '') {
            return AjaxResponse::error('missing_request_type', 'Request type is required.', 400);
        }

        $route = $this->routes->get($type);
        if ($route === null) {
            return AjaxResponse::error('unknown_request_type', 'Request type is not registered.', 404);
        }

        if (!$authenticated && !$route->allowGuests) {
            return AjaxResponse::error('authentication_required', 'Authentication is required.', 401);
        }

        if ($route->capability !== null && !($this->capabilityChecker)($route->capability)) {
            return AjaxResponse::error('capability_denied', 'The current principal is not authorized.', 403);
        }

        if ($route->requiresNonce) {
            $nonce = isset($request['nonce']) && is_string($request['nonce']) ? $request['nonce'] : '';
            if (!$this->nonces->verify($nonce, $route->operation, $route->type)) {
                return AjaxResponse::error('invalid_nonce', 'Request verification failed.', 403);
            }
        }

        $payload = $request['payload'] ?? [];
        if (!is_array($payload)) {
            return AjaxResponse::error('invalid_payload', 'Request payload must be an object.', 422);
        }

        try {
            return AjaxResponse::success($route->handler->handle($payload));
        } catch (AjaxAuthorizationException $exception) {
            return AjaxResponse::error(
                'policy_denied',
                'The current principal is not authorized for this operation.',
                403,
                ['reason' => $exception->reason],
            );
        } catch (Throwable) {
            return AjaxResponse::error('handler_failure', 'The request could not be completed.', 500);
        }
    }
}
