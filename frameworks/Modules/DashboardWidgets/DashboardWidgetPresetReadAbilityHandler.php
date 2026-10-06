<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class DashboardWidgetPresetReadAbilityHandler implements AbilityHandlerInterface
{
    public const GET = 'get';
    public const CATALOG = 'catalog';
    public const EFFECTIVE = 'effective';

    public function __construct(
        private DashboardWidgetPresetReadService $service,
        private string $action,
    ) {
        if (!in_array($this->action, [self::GET, self::CATALOG, self::EFFECTIVE], true)) {
            throw new InvalidArgumentException('Unsupported Dashboard Widget preset read action.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        return match ($this->action) {
            self::GET => $this->get($input),
            self::CATALOG => $this->catalog($input),
            self::EFFECTIVE => $this->effective($input, $context),
            default => throw new RuntimeException('Unsupported Dashboard Widget preset read action.'),
        };
    }

    /** @param array<string,mixed> $input */
    private function get(array $input): ?array
    {
        if (array_keys($input) !== ['id']) {
            throw new InvalidArgumentException('Dashboard Widget preset get requires exactly one id field.');
        }

        $id = $input['id'];
        if (
            !is_string($id)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id) !== 1
        ) {
            throw new InvalidArgumentException('Dashboard Widget preset get requires a lowercase RFC 4122 UUID.');
        }

        return $this->service->get($id);
    }

    /** @param array<string,mixed> $input */
    private function catalog(array $input): array
    {
        if ($input !== []) {
            throw new InvalidArgumentException('Dashboard Widget preset catalog does not accept input.');
        }

        return $this->service->catalog();
    }

    /** @param array<string,mixed> $input */
    private function effective(array $input, ExecutionContext $context): array
    {
        if ($input !== []) {
            throw new InvalidArgumentException('Dashboard Widget effective preset does not accept input.');
        }

        return $this->service->effective($context);
    }
}
