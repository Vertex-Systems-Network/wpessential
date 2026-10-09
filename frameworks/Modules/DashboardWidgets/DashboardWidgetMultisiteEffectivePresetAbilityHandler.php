<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class DashboardWidgetMultisiteEffectivePresetAbilityHandler implements AbilityHandlerInterface
{
    public function __construct(private DashboardWidgetMultisiteEffectivePresetResolver $resolver) {}

    public function handle(array $input, ExecutionContext $context): mixed
    {
        if ($input !== []) {
            throw new InvalidArgumentException('Effective Multisite preset read accepts no input.');
        }
        return $this->resolver->resolve($context);
    }
}
