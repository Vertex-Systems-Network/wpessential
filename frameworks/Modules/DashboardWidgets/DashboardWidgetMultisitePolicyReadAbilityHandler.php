<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class DashboardWidgetMultisitePolicyReadAbilityHandler implements AbilityHandlerInterface
{
    public const GET = 'get';
    public const CATALOG = 'catalog';
    public const EFFECTIVE = 'effective';

    public function __construct(
        private DashboardWidgetMultisitePolicyReadService $service,
        private string $action,
    ) {
        if (!in_array($action, [self::GET, self::CATALOG, self::EFFECTIVE], true)) {
            throw new InvalidArgumentException('Unsupported Dashboard Multisite policy read action.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        return match ($this->action) {
            self::GET => $this->get($input),
            self::CATALOG => $this->catalog($input),
            self::EFFECTIVE => $this->effective($input, $context),
        };
    }

    /** @param array<string,mixed> $input */
    private function get(array $input): ?array
    {
        if (array_keys($input) !== ['id']
            || !is_string($input['id'])
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $input['id']) !== 1
        ) {
            throw new InvalidArgumentException('Dashboard Multisite policy get requires one lowercase RFC 4122 UUID.');
        }
        return $this->service->get($input['id']);
    }
    /** @param array<string,mixed> $input */
    private function catalog(array $input): array
    {
        if ($input !== []) {
            throw new InvalidArgumentException('Dashboard Multisite policy catalog accepts no input.');
        }
        return $this->service->catalog();
    }
    /** @param array<string,mixed> $input */
    private function effective(array $input, ExecutionContext $context): array
    {
        if ($input !== []) {
            throw new InvalidArgumentException('Dashboard Multisite effective policy accepts no input.');
        }
        return $this->service->effective($context);
    }
}
