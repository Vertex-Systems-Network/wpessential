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
                'platform_api' => defined('WPE_PLATFORM_API_VERSION')
                    ? (string) WPE_PLATFORM_API_VERSION
                    : 'unknown',
                'platform_schema' => defined('WPE_PLATFORM_SCHEMA_GENERATION')
                    ? WPE_PLATFORM_SCHEMA_GENERATION
                    : 'unknown',
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
                'pro_compatibility' => $this->proCompatibility(),
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

        $platformApi = defined('WPE_PLATFORM_API_VERSION') ? (string) WPE_PLATFORM_API_VERSION : '';
        if ($platformApi !== '' && version_compare($platformApi, $manifest->minimumPlatformVersion, '<')) {
            return 'incompatible_platform_api';
        }

        if ($manifest->edition !== 'pro') {
            return 'local_prerequisites_met';
        }

        $pair = $this->proCompatibility();
        if ($pair['state'] === 'unavailable') {
            return 'compatibility_state_unavailable_adr_0010_not_certified';
        }
        if ($pair['state'] !== 'compatible') {
            return 'pair_' . $pair['state'];
        }

        return 'compatible_adr_0010_not_certified';
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

    /**
     * @return array{
     *   state:string,
     *   dimension:string,
     *   reason:string,
     *   remediation:string,
     *   free_version:string,
     *   platform_api:string,
     *   platform_schema:int|string,
     *   pro_schema:int|string,
     *   premium_boot_allowed:bool,
     *   premium_migrations_allowed:bool,
     *   certification:string
     * }
     */
    private function proCompatibility(): array
    {
        $result = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
        if (!is_array($result)) {
            $result = [];
        }

        return [
            'state' => $this->safeCompatibilityToken($result['state'] ?? null, 'unavailable'),
            'dimension' => $this->safeCompatibilityToken($result['dimension'] ?? null, 'unavailable'),
            'reason' => $this->safeCompatibilityToken($result['reason'] ?? null, 'unavailable'),
            'remediation' => $this->safeCompatibilityToken($result['remediation'] ?? null, 'unavailable'),
            'free_version' => $this->safeCompatibilityVersion($result['free_version'] ?? null),
            'platform_api' => $this->safeCompatibilityVersion($result['platform_api'] ?? null),
            'platform_schema' => $this->safeCompatibilityGeneration($result['platform_schema'] ?? null),
            'pro_schema' => $this->safeCompatibilityGeneration($result['pro_schema'] ?? null),
            'premium_boot_allowed' => ($result['premium_boot_allowed'] ?? false) === true,
            'premium_migrations_allowed' => ($result['premium_migrations_allowed'] ?? false) === true,
            'certification' => 'adr_0010_not_certified',
        ];
    }

    private function safeCompatibilityToken(mixed $value, string $fallback): string
    {
        if (!is_string($value)) {
            return $fallback;
        }
        if (preg_match('/^[a-z0-9_]+$/', $value) !== 1) {
            return 'invalid';
        }

        return $value;
    }

    private function safeCompatibilityVersion(mixed $value): string
    {
        if (!is_string($value)) {
            return 'unknown';
        }
        if (preg_match('/^[0-9A-Za-z.-]+$/', $value) !== 1) {
            return 'invalid';
        }

        return $value;
    }

    private function safeCompatibilityGeneration(mixed $value): int|string
    {
        return is_int($value) && $value >= 0 ? $value : 'unknown';
    }
}
