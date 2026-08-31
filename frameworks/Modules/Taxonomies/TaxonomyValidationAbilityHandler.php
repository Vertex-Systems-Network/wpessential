<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class TaxonomyValidationAbilityHandler implements AbilityHandlerInterface
{
    public function __construct(private TaxonomyValidationService $validation) {}

    public function handle(array $input, ExecutionContext $context): mixed
    {
        return $this->validation->validate($input);
    }
}
