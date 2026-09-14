<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminMenu;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class AdminMenuReadAbilityHandler implements AbilityHandlerInterface
{
    public const GET = 'get';
    public const CATALOG = 'catalog';

    public function __construct(
        private AdminMenuReadService $service,
        private string $action,
    ) {
        if (!in_array($this->action, [self::GET, self::CATALOG], true)) {
            throw new InvalidArgumentException('Unsupported Admin Menu read ability action.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        return match ($this->action) {
            self::GET => $this->get($input),
            self::CATALOG => $this->catalog($input),
            default => throw new RuntimeException('Unsupported Admin Menu read ability action.'),
        };
    }

    /** @param array<string,mixed> $input */
    private function get(array $input): ?array
    {
        $id = $input['id'] ?? null;
        if (!is_string($id) || trim($id) === '') {
            throw new InvalidArgumentException('Admin Menu get requires a non-empty definition id.');
        }

        return $this->service->get(trim($id));
    }

    /** @param array<string,mixed> $input */
    private function catalog(array $input): array
    {
        if ($input !== []) {
            throw new InvalidArgumentException('Admin Menu catalog does not accept input.');
        }

        return $this->service->catalog();
    }
}
