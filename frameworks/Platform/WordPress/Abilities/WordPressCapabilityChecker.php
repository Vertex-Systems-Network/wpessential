<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Abilities;


if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final class WordPressCapabilityChecker implements CapabilityCheckerInterface
{
    public function __construct(private readonly WordPressAbilityEnvironmentInterface $environment) {}

    public function can(ExecutionContext $context, string $capability): bool
    {
        if (!$context->principal->isAuthenticated()) return false;
        if ($context->principal->actorType !== 'user') return false;
        if ($context->principal->userId !== $this->environment->currentUserId()) return false;
        if ($context->siteId !== $this->environment->currentSiteId()) return false;

        return $this->environment->currentUserCan($capability);
    }
}
