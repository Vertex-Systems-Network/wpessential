<?php

declare(strict_types=1);

namespace WPEssential\Platform\Observability;

if (!defined('ABSPATH')) {
    exit;
}

interface TraceSnapshotReaderInterface
{
    /** @return list<array<string,mixed>> */
    public function all(): array;
}
