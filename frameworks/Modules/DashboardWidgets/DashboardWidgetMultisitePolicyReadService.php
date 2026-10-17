<?php
/**
 * Read-only Dashboard multisite policy projections.
 *
 * @package WPEssential\Modules\DashboardWidgets
 */

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

/** Provide validated published Dashboard multisite policy reads. */
final readonly class DashboardWidgetMultisitePolicyReadService {

	/**
	 * Construct the canonical Dashboard multisite policy reader.
	 *
	 * @param DefinitionRepositoryInterface          $definitions Canonical definition repository.
	 * @param DashboardWidgetMultisitePolicyCompiler $compiler Trusted projection compiler.
	 * @param DashboardWidgetMultisitePolicyResolver $resolver Effective policy resolver.
	 */
	public function __construct(
		private DefinitionRepositoryInterface $definitions,
		private DashboardWidgetMultisitePolicyCompiler $compiler,
		private DashboardWidgetMultisitePolicyResolver $resolver,
	) {}

	/**
	 * Read a published and correctly owned multisite policy.
	 *
	 * @param string $id Canonical definition identifier.
	 * @return array<string,mixed>|null
	 */
	public function get( string $id ): ?array {
		$definition = $this->definitions->get( $id );
		if (
			! $definition instanceof Definition
			|| $definition->ownerSurfaceId !== DashboardWidgetMultisitePolicyDefinition::OWNER_SURFACE_ID
			|| DashboardWidgetMultisitePolicyDefinition::TYPE !== $definition->type
			|| DefinitionStatus::Published !== $definition->status
		) {
			return null;
		}
		return $this->compiler->compile( $definition )->toArray();
	}

	/**
	 * List published multisite policy definitions sorted by slug and ID.
	 *
	 * @return list<array<string,mixed>>
	 */
	public function catalog(): array {
		$definitions = array_values(
			array_filter(
				$this->definitions->byType( DashboardWidgetMultisitePolicyDefinition::TYPE ),
				static fn ( Definition $definition ): bool => DefinitionStatus::Published === $definition->status,
			)
		);
		usort(
			$definitions,
			static function ( Definition $a, Definition $b ): int {
				$slugOrder = $a->slug <=> $b->slug;
				return 0 !== $slugOrder ? $slugOrder : ( $a->id <=> $b->id );
			}
		);
		return array_map(
			fn ( Definition $definition ): array =>
			$this->compiler->compile( $definition )->toArray(),
			$definitions
		);
	}

	/**
	 * Resolve the effective multisite policy for an authorized context.
	 *
	 * @param ExecutionContext $context Authorized context.
	 * @return array<string,mixed>
	 */
	public function effective( ExecutionContext $context ): array {
		return $this->resolver->resolve( $context )->toArray();
	}
}
