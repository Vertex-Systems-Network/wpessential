<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class JobTableNames
{
    public string $jobs;
    public string $attempts;

    public function __construct(DatabaseAdapterInterface $database)
    {
        $prefix = $database->networkTablePrefix();
        $this->jobs = $prefix . 'wpe_jobs';
        $this->attempts = $prefix . 'wpe_job_attempts';
    }
}
