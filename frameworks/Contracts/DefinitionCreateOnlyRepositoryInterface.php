<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Definitions\Definition;

/**
 * Opt-in, insert-only Definition persistence capability.
 *
 * Implementations must never rewrite an existing id or type+slug. A conflict
 * throws, including if a concurrent insert wins. Callers must NOT emulate
 * this guarantee using get() followed by save().
 */
interface DefinitionCreateOnlyRepositoryInterface extends DefinitionRepositoryInterface
{
    public function create(Definition $definition): void;
}
