<?php

declare(strict_types=1);

namespace WPEssential\Modules\Roles;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class RolesReadAbilityHandler implements AbilityHandlerInterface
{
    public const CATALOG = 'catalog';
    public const IMPACT = 'impact';

    public function __construct(
        private RolesReadService $service,
        private string $action,
    ) {
        if (!in_array($this->action, [self::CATALOG, self::IMPACT], true)) {
            throw new InvalidArgumentException('Unsupported Roles read ability action.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        return match ($this->action) {
            self::CATALOG => $this->service->roleCatalog($context),
            self::IMPACT => $this->impact($input, $context),
            default => throw new RuntimeException('Unsupported Roles read ability action.'),
        };
    }

    /** @param array<string,mixed> $input */
    private function impact(array $input, ExecutionContext $context): array
    {
        $capability = $input['capability'] ?? null;
        if (!is_string($capability)) {
            throw new InvalidArgumentException('Roles capability-impact requires a capability string.');
        }

        return $this->service->capabilityImpact($capability, $context);
    }
}
