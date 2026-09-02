<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class RelationEdgeTableNames
{
    public string $edges;
    public string $state;

    public function __construct(DatabaseAdapterInterface $database)
    {
        $prefix = $database->networkTablePrefix();
        if (preg_match('/^[A-Za-z0-9_]+$/', $prefix) !== 1) {
            throw new RuntimeException('Database table prefix contains unsupported characters.');
        }

        $this->edges = $prefix . 'wpe_relation_edges';
        $this->state = $prefix . 'wpe_relation_edge_state';
    }
}
