<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyDecision;

interface InputAuthorizingAbilityHandlerInterface extends AbilityHandlerInterface
{
    /** @param array<string,mixed> $input */
    public function authorizeInput(array $input, ExecutionContext $context): PolicyDecision;
}
