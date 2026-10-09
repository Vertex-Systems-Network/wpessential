<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class DashboardWidgetPresetPortabilityMappingPreviewAbilityHandler implements AbilityHandlerInterface
{
    public function __construct(private DashboardWidgetPresetPortabilityMappingPreviewService $preview) {}

    public function handle(array $input, ExecutionContext $context): mixed
    {
        $keys = array_keys($input);
        sort($keys, SORT_STRING);
        if (
            $keys !== ['snapshot', 'target_id', 'widget_id_map']
            || !is_array($input['snapshot'])
            || !is_string($input['target_id'])
            || !is_array($input['widget_id_map'])
        ) {
            throw new InvalidArgumentException('Preset mapping preview requires exactly snapshot, target_id and widget_id_map.');
        }

        return $this->preview->preview($input['snapshot'], $input['target_id'], $input['widget_id_map']);
    }
}
