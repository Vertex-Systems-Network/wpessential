<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class TaxonomyCptUiImportPreviewAbilityHandler implements AbilityHandlerInterface
{
    public function __construct(private TaxonomyCptUiImportPreviewService $preview) {}

    public function handle(array $input, ExecutionContext $context): mixed
    {
        $source = $input['source'] ?? null;
        if (!is_array($source) || array_is_list($source)) {
            throw new InvalidArgumentException('CPT UI import preview source must be an object/map.');
        }
        return $this->preview->preview($source);
    }
}
