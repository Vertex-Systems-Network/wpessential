<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Rendering\RenderInput;
use WPEssential\Platform\Rendering\RenderOutput;

/** Shared deterministic server-render boundary. */
interface RendererInterface
{
    public const CONTRACT_VERSION = 1;

    public function render(RenderInput $input, ExecutionContext $context): RenderOutput;
}
