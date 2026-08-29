<?php

declare(strict_types=1);

namespace WPEssential\Platform\Auth;

use WPEssential\Contracts\CapabilityCheckerInterface;

final readonly class PolicyEngine
{
    public function __construct(private CapabilityCheckerInterface $capabilities) {}

    public function authorize(AuthorizationRequest $request): PolicyDecision
    {
        if (!$request->context->principal->isAuthenticated()) {
            return PolicyDecision::deny('unauthenticated');
        }

        if (!$this->capabilities->can($request->context, $request->capability)) {
            return PolicyDecision::deny('capability_denied');
        }

        return PolicyDecision::allow('capability_allowed');
    }
}
