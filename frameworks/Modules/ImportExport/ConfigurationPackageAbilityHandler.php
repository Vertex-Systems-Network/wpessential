<?php

declare(strict_types=1);

namespace WPEssential\Modules\ImportExport;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class ConfigurationPackageAbilityHandler implements AbilityHandlerInterface
{
    public const EXPORT = 'export';
    public const PREFLIGHT = 'preflight';
    public const IMPORT = 'import';

    public function __construct(
        private ConfigurationPackageService $packages,
        private string $action,
    ) {
        if (!in_array($this->action, [self::EXPORT, self::PREFLIGHT, self::IMPORT], true)) {
            throw new InvalidArgumentException('Unsupported configuration package ability action.');
        }
    }

    /** @param array<string,mixed> $input */
    public function handle(array $input, ExecutionContext $context): mixed
    {
        return match ($this->action) {
            self::EXPORT => $this->packages->export($input, $context),
            self::PREFLIGHT => $this->packages->preflight($input, $context),
            self::IMPORT => $this->packages->import($input, $context),
            default => throw new RuntimeException('Unsupported configuration package ability action.'),
        };
    }
}
