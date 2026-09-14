<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Kernel\Kernel;
use WPEssential\Platform\Entitlements\EntitlementAwareModuleActivationPolicy;
use WPEssential\Platform\Entitlements\ProductEntitlementState;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\Modules\ModuleState;
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
        $moduleInventory = $this->moduleInventory();

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
            'modules' => [
                'count' => count($moduleInventory),
                'inventory' => $moduleInventory,
                'compatibility_certification' => 'adr_0010_not_certified',
                'read_only' => true,
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

    /**
     * @return list<array{
     *   id:string,
     *   module:string,
     *   edition:string,
     *   package:string,
     *   compatibility:string,
     *   entitlement:string,
     *   runtime_state:string,
     *   reason:string
     * }>
     */
    private function moduleInventory(): array
    {
        $registry = $this->kernel->modules();
        $rows = [];

        foreach ($registry->all() as $module) {
            $manifest = $module->manifest();
            $state = $registry->state($manifest->id);
            $entitlement = $this->entitlementFor($manifest);

            $rows[] = [
                'id' => $manifest->id,
                'module' => $manifest->name,
                'edition' => $manifest->edition,
                'package' => $manifest->edition === 'pro' ? 'wpessential-pro' : 'wpessential',
                'compatibility' => $this->compatibilityFor($manifest),
                'entitlement' => $entitlement,
                'runtime_state' => $state?->value ?? 'unavailable',
                'reason' => $this->runtimeReason($manifest, $state, $entitlement),
            ];
        }

        return $rows;
    }

    private function compatibilityFor(ModuleManifest $manifest): string
    {
        if (version_compare(PHP_VERSION, $manifest->minimumPhpVersion, '<')) {
            return 'incompatible_php';
        }

        $wordpress = function_exists('get_bloginfo') ? (string) get_bloginfo('version') : '';
        if ($wordpress !== '' && version_compare($wordpress, $manifest->minimumWordPressVersion, '<')) {
            return 'incompatible_wordpress';
        }

        $platform = defined('WPE_VERSION') ? (string) WPE_VERSION : '';
        if ($platform !== '' && version_compare($platform, $manifest->minimumPlatformVersion, '<')) {
            return 'incompatible_platform';
        }

        return $manifest->edition === 'pro'
            ? 'local_prerequisites_met_adr_0010_not_certified'
            : 'local_prerequisites_met';
    }

    private function entitlementFor(ModuleManifest $manifest): string
    {
        if ($manifest->edition !== 'pro') {
            return 'not_applicable';
        }

        $policy = $this->kernel->moduleActivationPolicy();
        if (!$policy instanceof EntitlementAwareModuleActivationPolicy) {
            return 'unknown';
        }

        return $policy->entitlementSnapshot()->state->value;
    }

    private function runtimeReason(ModuleManifest $manifest, ?ModuleState $state, string $entitlement): string
    {
        if ($state === ModuleState::Degraded) {
            return 'dependency_unavailable';
        }
        if ($state === ModuleState::Registered) {
            return 'registered_not_booted';
        }
        if ($state !== ModuleState::Booted) {
            return 'runtime_state_unavailable';
        }
        if ($manifest->edition !== 'pro') {
            return 'booted';
        }

        $activeMutationStates = [
            ProductEntitlementState::TrialActive->value,
            ProductEntitlementState::ProActive->value,
            ProductEntitlementState::Grace->value,
        ];
        if (!in_array($entitlement, $activeMutationStates, true)) {
            return 'read_safe_entitlement_' . $entitlement;
        }

        return 'booted';
    }
}
