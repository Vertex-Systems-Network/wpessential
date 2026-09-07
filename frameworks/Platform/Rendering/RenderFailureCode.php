<?php

declare(strict_types=1);

namespace WPEssential\Platform\Rendering;

if (!defined('ABSPATH')) {
    exit;
}

enum RenderFailureCode: string
{
    case InvalidInput = 'invalid_render_input';
    case MissingBlueprint = 'missing_blueprint';
    case UnsupportedValueSource = 'unsupported_value_source';
    case DependencyMismatch = 'dependency_mismatch';
}
