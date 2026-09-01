<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;

final readonly class RelationEndpointSupport
{
    private Closure $postTypeExists;
    private Closure $taxonomyExists;

    public function __construct(?Closure $postTypeExists = null, ?Closure $taxonomyExists = null)
    {
        $this->postTypeExists = $postTypeExists ?? static fn (string $postType): bool => function_exists('post_type_exists')
            && post_type_exists($postType);
        $this->taxonomyExists = $taxonomyExists ?? static fn (string $taxonomy): bool => function_exists('taxonomy_exists')
            && taxonomy_exists($taxonomy);
    }

    /** @param array{object_type:string,object_subtype:?string,label:string} $endpoint */
    public function supports(array $endpoint): bool
    {
        $type = $endpoint['object_type'];
        $subtype = $endpoint['object_subtype'];

        return match ($type) {
            'post' => is_string($subtype) && ($this->postTypeExists)($subtype),
            'term' => is_string($subtype) && ($this->taxonomyExists)($subtype),
            'user', 'comment', 'media' => true,
            'custom_table', 'registered_entity' => false,
            default => false,
        };
    }

    /** @param array{object_type:string,object_subtype:?string,label:string} $endpoint */
    public function unsupportedReason(array $endpoint): string
    {
        return match ($endpoint['object_type']) {
            'post' => sprintf('Post endpoint subtype "%s" is not registered.', (string) $endpoint['object_subtype']),
            'term' => sprintf('Term endpoint taxonomy "%s" is not registered.', (string) $endpoint['object_subtype']),
            'custom_table' => 'Custom-table Relation endpoints require a separately certified Tables adapter.',
            'registered_entity' => 'Registered-entity Relation endpoints require a separately certified endpoint adapter.',
            default => sprintf('Relation endpoint type "%s" is not publishable.', $endpoint['object_type']),
        };
    }
}
