<?php

declare(strict_types=1);

namespace WPEssential\Modules\Roles;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Platform\Auth\AuthorizationRequest;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;

final readonly class RolesReadService
{
    public const OWNER_SURFACE_ID = 30;
    public const CAPABILITY = 'manage_options';
    public const ABILITY_CATALOG = 'wpessential/roles/catalog';
    public const ABILITY_IMPACT = 'wpessential/roles/capability-impact';

    /** @var list<string> */
    private const CONTEXTUAL_META_CAPABILITIES = [
        'delete_post',
        'delete_page',
        'edit_post',
        'edit_page',
        'read_post',
        'read_page',
        'edit_comment',
        'edit_term',
        'delete_term',
        'assign_term',
        'edit_user',
        'delete_user',
        'remove_user',
        'promote_user',
    ];

    public function __construct(
        private PolicyEngine $policy,
        private RoleRuntimeEnvironmentInterface $environment,
    ) {}

    /**
     * @return array{
     *   state:'healthy'|'degraded'|'unavailable',
     *   scope_context:array{site_id:int,network_id:?int,multisite:bool,super_admin_special_authority:bool},
     *   roles:list<array{key:string,name:string,provenance:'unknown',capabilities:array<string,bool>}>,
     *   capability_registry:list<array{key:string,classification:string,provenance:'unknown',explicit_allow_role_count:int,explicit_deny_role_count:int}>,
     *   caveats:list<string>
     * }
     */
    public function roleCatalog(ExecutionContext $context): array
    {
        $this->authorize($context, self::ABILITY_CATALOG);
        return $this->catalogSnapshot($context);
    }

    /**
     * @return array{
     *   state:'healthy'|'degraded'|'unavailable',
     *   capability:string,
     *   classification:string,
     *   meta_capability:bool,
     *   context_required:bool,
     *   scope_context:array{site_id:int,network_id:?int,multisite:bool,super_admin_special_authority:bool},
     *   explicit_allow_roles:list<string>,
     *   explicit_deny_roles:list<string>,
     *   absent_roles:list<string>,
     *   unknown_or_external_roles:list<string>,
     *   caveats:list<string>
     * }
     */
    public function capabilityImpact(string $capability, ExecutionContext $context): array
    {
        $this->authorize($context, self::ABILITY_IMPACT);
        $capability = trim($capability);
        if ($capability === '' || strlen($capability) > 191 || preg_match('/[\x00-\x1F\x7F]/', $capability) === 1) {
            throw new InvalidArgumentException('Capability must be a non-empty bounded identifier without control characters.');
        }

        $catalog = $this->catalogSnapshot($context);
        $allows = [];
        $denies = [];
        $absent = [];

        if ($catalog['state'] === 'healthy') {
            foreach ($catalog['roles'] as $role) {
                if (!array_key_exists($capability, $role['capabilities'])) {
                    $absent[] = $role['key'];
                    continue;
                }

                if ($role['capabilities'][$capability] === true) {
                    $allows[] = $role['key'];
                } else {
                    $denies[] = $role['key'];
                }
            }
        }

        $contextRequired = in_array($capability, self::CONTEXTUAL_META_CAPABILITIES, true);
        $caveats = $catalog['caveats'];
        $caveats[] = 'Static role entries do not include individual user capability overrides or dynamic map_meta_cap/user_has_cap filters.';
        $caveats[] = 'This impact result is diagnostic role-entry truth, not a final authorization decision.';
        if ($contextRequired) {
            $caveats[] = 'The requested capability is contextual/meta and requires object or user context for an effective authorization explanation.';
        } else {
            $caveats[] = 'Provider-defined capabilities may still apply dynamic context; unknown provider semantics remain external to the static role matrix.';
        }

        return [
            'state' => $catalog['state'],
            'capability' => $capability,
            'classification' => $contextRequired ? 'contextual_meta' : 'primitive_or_provider',
            'meta_capability' => $contextRequired,
            'context_required' => $contextRequired,
            'scope_context' => $catalog['scope_context'],
            'explicit_allow_roles' => $allows,
            'explicit_deny_roles' => $denies,
            'absent_roles' => $absent,
            'unknown_or_external_roles' => [],
            'caveats' => array_values(array_unique($caveats)),
        ];
    }

    /**
     * @return array{
     *   state:'healthy'|'degraded'|'unavailable',
     *   scope_context:array{site_id:int,network_id:?int,multisite:bool,super_admin_special_authority:bool},
     *   roles:list<array{key:string,name:string,provenance:'unknown',capabilities:array<string,bool>}>,
     *   capability_registry:list<array{key:string,classification:string,provenance:'unknown',explicit_allow_role_count:int,explicit_deny_role_count:int}>,
     *   caveats:list<string>
     * }
     */
    private function catalogSnapshot(ExecutionContext $context): array
    {
        $multisite = $this->environment->isMultisite();
        $networkId = $this->environment->networkIdForSite($context->siteId);
        $scope = [
            'site_id' => $context->siteId,
            'network_id' => $networkId,
            'multisite' => $multisite,
            'super_admin_special_authority' => $multisite,
        ];
        $caveats = [
            'Role provenance remains unknown unless canonical managed/provider metadata can prove ownership.',
        ];
        if ($multisite) {
            $caveats[] = 'Super Admin is special network authority and is not represented as a synthetic site role.';
        }

        if (!$this->environment->available()) {
            $caveats[] = 'WordPress role APIs are unavailable; role truth cannot be resolved.';
            return $this->emptyCatalog('unavailable', $scope, $caveats);
        }

        if (!$this->environment->siteExists($context->siteId)) {
            $caveats[] = 'Requested site context could not be resolved.';
            return $this->emptyCatalog('degraded', $scope, $caveats);
        }

        if ($context->networkId !== null && $networkId !== null && $context->networkId !== $networkId) {
            $caveats[] = 'Execution context network does not match the requested site network.';
            return $this->emptyCatalog('degraded', $scope, $caveats);
        }

        $sourceRoles = $this->environment->rolesForSite($context->siteId);
        $roles = [];
        foreach ($sourceRoles as $key => $definition) {
            $roles[] = [
                'key' => $key,
                'name' => $definition['name'],
                'provenance' => 'unknown',
                'capabilities' => $definition['capabilities'],
            ];
        }

        return [
            'state' => 'healthy',
            'scope_context' => $scope,
            'roles' => $roles,
            'capability_registry' => $this->capabilityRegistry($sourceRoles),
            'caveats' => $caveats,
        ];
    }

    /**
     * @param array<string,array{name:string,capabilities:array<string,bool>}> $roles
     * @return list<array{key:string,classification:string,provenance:'unknown',explicit_allow_role_count:int,explicit_deny_role_count:int}>
     */
    private function capabilityRegistry(array $roles): array
    {
        /** @var array<string,array{allow:int,deny:int}> $counts */
        $counts = [];
        foreach ($roles as $role) {
            foreach ($role['capabilities'] as $capability => $granted) {
                $counts[$capability] ??= ['allow' => 0, 'deny' => 0];
                $granted ? $counts[$capability]['allow']++ : $counts[$capability]['deny']++;
            }
        }
        ksort($counts, SORT_STRING);

        $registry = [];
        foreach ($counts as $capability => $count) {
            $registry[] = [
                'key' => $capability,
                'classification' => in_array($capability, self::CONTEXTUAL_META_CAPABILITIES, true)
                    ? 'contextual_meta'
                    : 'primitive_or_provider',
                'provenance' => 'unknown',
                'explicit_allow_role_count' => $count['allow'],
                'explicit_deny_role_count' => $count['deny'],
            ];
        }

        return $registry;
    }

    /**
     * @param 'degraded'|'unavailable' $state
     * @param array{site_id:int,network_id:?int,multisite:bool,super_admin_special_authority:bool} $scope
     * @param list<string> $caveats
     * @return array{
     *   state:'degraded'|'unavailable',
     *   scope_context:array{site_id:int,network_id:?int,multisite:bool,super_admin_special_authority:bool},
     *   roles:list<array{key:string,name:string,provenance:'unknown',capabilities:array<string,bool>}>,
     *   capability_registry:list<array{key:string,classification:string,provenance:'unknown',explicit_allow_role_count:int,explicit_deny_role_count:int}>,
     *   caveats:list<string>
     * }
     */
    private function emptyCatalog(string $state, array $scope, array $caveats): array
    {
        return [
            'state' => $state,
            'scope_context' => $scope,
            'roles' => [],
            'capability_registry' => [],
            'caveats' => $caveats,
        ];
    }

    private function authorize(ExecutionContext $context, string $ability): void
    {
        $decision = $this->policy->authorize(new AuthorizationRequest(
            context: $context,
            ability: $ability,
            capability: self::CAPABILITY,
        ));
        if (!$decision->allowed) {
            throw new RuntimeException('Roles read service access denied: ' . $decision->reason . '.');
        }
    }
}
