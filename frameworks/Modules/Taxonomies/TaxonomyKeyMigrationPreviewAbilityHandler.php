<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class TaxonomyKeyMigrationPreviewAbilityHandler implements AbilityHandlerInterface
{
    public function __construct(private TaxonomyKeyMigrationPreviewService $preview) {}

    public function handle(array $input, ExecutionContext $context): mixed
    {
        unset($context);

        $id = $input['id'] ?? null;
        $targetKey = $input['target_key'] ?? null;
        if (!is_string($id)) {
            throw new InvalidArgumentException('Taxonomy key migration preview requires an id string.');
        }
        if (!is_string($targetKey)) {
            throw new InvalidArgumentException('Taxonomy key migration preview requires a target_key string.');
        }

        return $this->preview->preview($id, $targetKey);
    }
}
