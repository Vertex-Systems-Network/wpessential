<?php
/**
 * Resolve authorized Dashboard widget preset defaults.
 *
 * @package WPEssential\Modules\DashboardWidgets
 */

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

/** Determine effective presets without granting access on catalog errors. */
final readonly class DashboardWidgetPresetResolver {

	/**
	 * Construct the preset resolver from canonical dependencies.
	 *
	 * @param DefinitionRepositoryInterface                  $definitions Canonical definitions.
	 * @param DashboardWidgetPresetCompiler                  $compiler Trusted preset compiler.
	 * @param DashboardWidgetRoleMembershipProviderInterface $roles Role checker.
	 */
	public function __construct(
		private DefinitionRepositoryInterface $definitions,
		private DashboardWidgetPresetCompiler $compiler,
		private DashboardWidgetRoleMembershipProviderInterface $roles,
	) {}

	/**
	 * Resolve exactly one authorized preset by role or network fallback.
	 *
	 * @param ExecutionContext $context Current authenticated execution context.
	 * @return DashboardWidgetPresetResolution
	 */
	public function resolve( ExecutionContext $context ): DashboardWidgetPresetResolution {
		if ( ! $this->roles->isCurrentUserContext( $context ) ) {
			return DashboardWidgetPresetResolution::unavailable();
		}

		$role_matches     = array();
		$network_defaults = array();

		try {
			foreach ( $this->definitions->byType( DashboardWidgetPresetDefinition::TYPE ) as $definition ) {
				if ( ! $definition instanceof Definition || DefinitionStatus::Published !== $definition->status ) {
					continue;
				}

				$preset = $this->compiler->compile( $definition );
				if ( $preset->isRoleDefault() && $this->roles->hasAnyRole( $context, $preset->roles ) ) {
					$role_matches[] = $preset;
				}
                if ($preset->networkDefault) {
					$network_defaults[] = $preset;
				}
			}
		} catch ( Throwable ) {
			return DashboardWidgetPresetResolution::invalidCatalog();
		}

		if ( count( $role_matches ) > 1 ) {
			return DashboardWidgetPresetResolution::conflict();
		}
		if ( count( $role_matches ) === 1 ) {
			return DashboardWidgetPresetResolution::resolved(
				$role_matches[0],
				DashboardWidgetPresetResolution::SOURCE_ROLE_DEFAULT,
			);
		}

		if ( count( $network_defaults ) > 1 ) {
			return DashboardWidgetPresetResolution::conflict();
		}
		if ( count( $network_defaults ) === 1 ) {
			return DashboardWidgetPresetResolution::resolved(
				$network_defaults[0],
				DashboardWidgetPresetResolution::SOURCE_NETWORK_DEFAULT,
			);
		}

		return DashboardWidgetPresetResolution::none();
	}
}
