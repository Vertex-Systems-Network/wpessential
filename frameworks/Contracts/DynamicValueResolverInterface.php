<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\DynamicValues\DynamicValueRequest;
use WPEssential\Platform\DynamicValues\DynamicValueResult;

/** Shared typed consumer boundary for owner-backed dynamic values. */
interface DynamicValueResolverInterface
{
    public const CONTRACT_VERSION = 1;

    public function resolve(DynamicValueRequest $request, ExecutionContext $context): DynamicValueResult;
}
