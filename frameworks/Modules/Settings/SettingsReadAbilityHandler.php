<?php

declare(strict_types=1);

namespace WPEssential\Modules\Settings;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class SettingsReadAbilityHandler implements AbilityHandlerInterface
{
    public const GET = 'get';
    public const CATALOG = 'catalog';

    public function __construct(private SettingsReadService $service, private string $action)
    {
        if (!in_array($this->action, [self::GET, self::CATALOG], true)) {
            throw new InvalidArgumentException('Unsupported Settings read ability action.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        return match ($this->action) {
            self::GET => $this->get($input),
            self::CATALOG => $this->catalog($input),
            default => throw new RuntimeException('Unsupported Settings read ability action.'),
        };
    }

    private function get(array $input): ?array
    {
        $id = $input['id'] ?? null;
        if (!is_string($id) || trim($id) === '') {
            throw new InvalidArgumentException('Settings get requires a non-empty definition id.');
        }
        return $this->service->get(trim($id));
    }

    private function catalog(array $input): array
    {
        if ($input !== []) {
            throw new InvalidArgumentException('Settings catalog does not accept input.');
        }
        return $this->service->catalog();
    }
}
