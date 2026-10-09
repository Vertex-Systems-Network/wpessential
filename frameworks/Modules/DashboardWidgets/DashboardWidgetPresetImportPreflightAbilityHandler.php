<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class DashboardWidgetPresetImportPreflightAbilityHandler implements AbilityHandlerInterface
{
    public function __construct(private DashboardWidgetPresetImportPreflightService $preflight) {}

    public function handle(array $input, ExecutionContext $context): mixed
    {
        if (array_keys($input) !== ['snapshot'] || !is_array($input['snapshot'])) {
            throw new InvalidArgumentException('Dashboard preset import preflight requires exactly one snapshot object.');
        }

        return $this->preflight->preflight($input['snapshot']);
    }
}
