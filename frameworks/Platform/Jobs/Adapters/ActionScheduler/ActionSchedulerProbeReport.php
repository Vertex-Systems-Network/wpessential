<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs\Adapters\ActionScheduler;


if (!defined('ABSPATH')) {
    exit;
}

final readonly class ActionSchedulerProbeReport
{
    /**
     * @param list<string> $missingFunctions
     * @param array<string, bool|null> $features
     */
    public function __construct(
        public ActionSchedulerProbeState $state,
        public array $missingFunctions,
        public array $features,
        public bool $coexistenceCertified = false,
        public bool $multisiteCertified = false,
    ) {}
}
