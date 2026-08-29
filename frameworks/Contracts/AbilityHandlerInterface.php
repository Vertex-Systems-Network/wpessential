<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

use WPEssential\Platform\Auth\ExecutionContext;

interface AbilityHandlerInterface
{
    /** @param array<string, mixed> $input */
    public function handle(array $input, ExecutionContext $context): mixed;
}
