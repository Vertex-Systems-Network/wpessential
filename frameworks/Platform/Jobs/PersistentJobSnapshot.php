<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class PersistentJobSnapshot
{
    public function __construct(
        public JobRecord $record,
        public int $revision,
        public ?string $idempotencyHash,
    ) {}
}
