<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Kernel\Kernel;
use WPEssential\Platform\Observability\TraceSnapshotReaderInterface;

final readonly class RuntimeDiagnosticsSnapshot
{
    public function __construct(
        private Kernel $kernel,
        private TraceSnapshotReaderInterface $traces,
        private bool $debugEnabled,
        private int $maxTraces = 20,
    ) {}

    /** @return array<string,mixed> */
    public function build(): array
    {
        $allTraces = $this->traces->all();
        $visibleTraces = array_slice($allTraces, -max(1, $this->maxTraces));

        return [
            'app' => [
                'name' => 'WPEssential',
                'version' => defined('WPE_VERSION') ? (string) WPE_VERSION : 'unknown',
                'surface_id' => 31,
            ],
            'context' => [
                'site_id' => function_exists('get_current_blog_id') ? max(1, (int) get_current_blog_id()) : 1,
                'network_id' => function_exists('get_current_network_id') ? max(1, (int) get_current_network_id()) : 1,
                'multisite' => function_exists('is_multisite') && is_multisite(),
            ],
            'runtime' => [
                'wordpress' => function_exists('get_bloginfo') ? (string) get_bloginfo('version') : 'unknown',
                'php' => PHP_VERSION,
                'kernel_booted' => $this->kernel->isBooted(),
                'debug_enabled' => $this->debugEnabled,
                'trace_capture' => $this->debugEnabled ? 'bounded_in_memory' : 'disabled',
            ],
            'observability' => [
                'captured_trace_count' => count($allTraces),
                'visible_trace_count' => count($visibleTraces),
                'traces' => $visibleTraces,
                'retention' => 'request_bounded_non_authoritative',
            ],
            'capabilities' => [
                'read_diagnostics' => 'manage_options',
                'mutations_available' => false,
            ],
        ];
    }
}
