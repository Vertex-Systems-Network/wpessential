<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class AdminColumnsReadAbilityHandler implements AbilityHandlerInterface
{
    public function __construct(private AdminColumnsReadAdapter $reads)
    {
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        $viewId = $input['view_id'] ?? null;
        if (!is_string($viewId)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $viewId) !== 1
        ) {
            throw new InvalidArgumentException('view_id must be a lowercase RFC 4122 UUID.');
        }

        unset($input['view_id']);

        return $this->reads->read($viewId, $input, $context);
    }
}
