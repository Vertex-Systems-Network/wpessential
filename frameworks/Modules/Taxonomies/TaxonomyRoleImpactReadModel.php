<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Bootstrap\Plugin;
use WPEssential\Modules\Roles\RolesModule;
use WPEssential\Modules\Roles\RolesReadService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

final readonly class TaxonomyRoleImpactReadModel
{
    /** @var array<string,string> */
    private const DEFAULT_CAPABILITIES = [
        'manage_terms' => 'manage_categories',
        'edit_terms' => 'manage_categories',
        'delete_terms' => 'manage_categories',
        'assign_terms' => 'edit_posts',
    ];

    public function __construct(private ?RolesReadService $roles = null) {}

    /**
     * @return array{
     *   state:'healthy'|'degraded'|'unavailable',
     *   operations:list<array{operation:string,capability:string,impact:array<string,mixed>}>,
     *   caveats:list<string>
     * }
     */
    public function forDefinition(Definition $definition, ?ExecutionContext $context = null): array
    {
        return $this->forCapabilities($this->effectiveCapabilities($definition->payload), $context);
    }

    /**
     * @param array<string,string> $capabilities
     * @return array{
     *   state:'healthy'|'degraded'|'unavailable',
     *   operations:list<array{operation:string,capability:string,impact:array<string,mixed>}>,
     *   caveats:list<string>
     * }
     */
    public function forCapabilities(array $capabilities, ?ExecutionContext $context = null): array
    {
        $service = $this->roles ?? $this->canonicalRolesService();
        $context ??= $this->currentExecutionContext();
        if (!$service instanceof RolesReadService || !$context instanceof ExecutionContext) {
            return [
                'state' => 'unavailable',
                'operations' => array_map(
                    static fn (string $operation, string $capability): array => [
                        'operation' => $operation,
                        'capability' => $capability,
                        'impact' => [
                            'state' => 'unavailable',
                            'capability' => $capability,
                            'explicit_allow_roles' => [],
                            'explicit_deny_roles' => [],
                            'absent_roles' => [],
                            'caveats' => ['Canonical Surface 30 role-impact service or execution context is unavailable.'],
                        ],
                    ],
                    array_keys($capabilities),
                    array_values($capabilities),
                ),
                'caveats' => [
                    'Canonical Surface 30 role-impact service or execution context is unavailable; Taxonomy does not fall back to direct WordPress role-store inspection.',
                ],
            ];
        }

        /** @var array<string,array<string,mixed>> $byCapability */
        $byCapability = [];
        foreach ($capabilities as $capability) {
            if (!isset($byCapability[$capability])) {
                $byCapability[$capability] = $service->capabilityImpact($capability, $context);
            }
        }

        $operations = [];
        $state = 'healthy';
        $caveats = [
            'Role impact is diagnostic role-entry truth from canonical Surface 30, not final user authorization.',
        ];
        foreach ($capabilities as $operation => $capability) {
            $impact = $byCapability[$capability];
            $impactState = $impact['state'] ?? 'unavailable';
            if ($impactState === 'unavailable') {
                $state = 'unavailable';
            } elseif ($impactState === 'degraded' && $state === 'healthy') {
                $state = 'degraded';
            }

            $impactCaveats = $impact['caveats'] ?? [];
            if (is_array($impactCaveats)) {
                foreach ($impactCaveats as $caveat) {
                    if (is_string($caveat) && $caveat !== '') {
                        $caveats[] = $caveat;
                    }
                }
            }

            $operations[] = [
                'operation' => $operation,
                'capability' => $capability,
                'impact' => $impact,
            ];
        }

        return [
            'state' => $state,
            'operations' => $operations,
            'caveats' => array_values(array_unique($caveats)),
        ];
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,string>
     */
    public function effectiveCapabilities(array $payload): array
    {
        $effective = self::DEFAULT_CAPABILITIES;
        $authored = $payload['capabilities'] ?? null;
        if (!is_array($authored) || array_is_list($authored)) {
            return $effective;
        }

        foreach (array_keys(self::DEFAULT_CAPABILITIES) as $operation) {
            $value = $authored[$operation] ?? null;
            if (is_string($value) && trim($value) !== '') {
                $effective[$operation] = trim($value);
            }
        }

        return $effective;
    }

    private function canonicalRolesService(): ?RolesReadService
    {
        $kernel = Plugin::kernel();
        if ($kernel === null) {
            return null;
        }

        $services = $kernel->services();
        if (!$services->has(RolesModule::SERVICE_READ)) {
            return null;
        }

        $service = $services->get(RolesModule::SERVICE_READ);
        return $service instanceof RolesReadService ? $service : null;
    }

    private function currentExecutionContext(): ?ExecutionContext
    {
        $kernel = Plugin::kernel();
        if ($kernel === null) {
            return null;
        }

        $services = $kernel->services();
        if (!$services->has('platform.abilities.contexts')) {
            return null;
        }

        $factory = $services->get('platform.abilities.contexts');
        return $factory instanceof WordPressExecutionContextFactory ? $factory->current() : null;
    }
}
