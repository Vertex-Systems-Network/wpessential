<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class DashboardWidgetPresetPortabilityAbilityHandler implements AbilityHandlerInterface
{
    public function __construct(private DashboardWidgetPresetPortabilityReadService $service) {}

    public function handle(array $input, ExecutionContext $context): mixed
    {
        if (array_keys($input) !== ['id']) {
            throw new InvalidArgumentException('Dashboard preset portability snapshot requires exactly one id.');
        }

        $id = $input['id'];
        if (
            !is_string($id)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id) !== 1
        ) {
            throw new InvalidArgumentException('Dashboard preset portability requires a lowercase RFC4122 UUID.');
        }
        return $this->service->snapshot($id);
    }
}
