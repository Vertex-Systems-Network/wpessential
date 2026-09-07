<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\Scope;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Auth\ExecutionContext;

final class ListingScopeGuard
{
    /** @param array<string,mixed> $publicParameters */
    public function fromContext(ExecutionContext $context, array $publicParameters = []): ListingScope
    {
        foreach (array_keys($publicParameters) as $key) {
            if (!is_string($key)) {
                continue;
            }
            if (in_array(strtolower($key), ['site_id', 'siteid', 'blog_id', 'blogid', 'network_id', 'networkid'], true)) {
                throw new InvalidArgumentException('Listing public parameters must not select site or network scope.');
            }
        }

        return new ListingScope($context->siteId, $context->networkId);
    }

    public function assertMatches(ListingScope $required, ExecutionContext $context): void
    {
        if ($required->siteId !== $context->siteId) {
            throw new InvalidArgumentException('Listing execution site scope does not match the server context.');
        }
        if ($required->networkId !== null && $required->networkId !== $context->networkId) {
            throw new InvalidArgumentException('Listing execution network scope does not match the server context.');
        }
    }
}
