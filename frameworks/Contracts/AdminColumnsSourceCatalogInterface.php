<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Optional read-only source discovery seam for Admin Columns authoring.
 *
 * Implementations own discovery and certification. Consumers must treat the
 * returned rows as untrusted bootstrap metadata and fail closed when malformed.
 */
interface AdminColumnsSourceCatalogInterface
{
    /** @return list<array<string,mixed>> */
    public function adminColumnSources(): array;
}
