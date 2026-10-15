<?php
/**
 * Read-only Dashboard widget preset projections.
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

/** Provide validated published Dashboard widget preset reads. */
final readonly class DashboardWidgetPresetReadService {

	/**
	 * Construct the canonical Dashboard widget preset reader.
	 *
	 * @param DefinitionRepositoryInterface $definitions Canonical definition repository.
	 * @param DashboardWidgetPresetCompiler $compiler Trusted projection compiler.
	 * @param DashboardWidgetPresetResolver $resolver Effective preset resolver.
	 */
	public function __construct(
		private DefinitionRepositoryInterface $definitions,
		private DashboardWidgetPresetCompiler $compiler,
		private DashboardWidgetPresetResolver $resolver,
	) {}

	/**
	 * Read a published and correctly owned widget preset.
	 *
	 * @param string $id Canonical definition identifier.
	 * @return array<string,mixed>|null
	 */
	public function get( string $id ): ?array {
		$definition = $this->definitions->get( $id );
		if (
			! $definition instanceof Definition
			|| DashboardWidgetPresetDefinition::TYPE !== $definition->type
			|| $definition->ownerSurfaceId !== DashboardWidgetPresetDefinition::OWNER_SURFACE_ID
			|| DefinitionStatus::Published !== $definition->status
		) {
			return null;
		}

		return $this->compiler->compile( $definition )->toArray();
	}

	/**
	 * List published widget preset definitions sorted by slug and ID.
	 *
	 * @return list<array<string,mixed>>
	 */
	public function catalog(): array {
		$definitions = array_values(
			array_filter(
				$this->definitions->byType( DashboardWidgetPresetDefinition::TYPE ),
				static fn ( Definition $definition ): bool => DefinitionStatus::Published === $definition->status,
			)
		);
		usort(
			$definitions,
			static function ( Definition $left, Definition $right ): int {
				$slug_order = $left->slug <=> $right->slug;
				return 0 !== $slug_order ? $slug_order : ( $left->id <=> $right->id );
			}
		);

		$result = array();
		foreach ( $definitions as $definition ) {
			$result[] = $this->compiler->compile( $definition )->toArray();
		}

		return $result;
	}

	/**
	 * Resolve the effective widget preset for an authorized context.
	 *
	 * @param ExecutionContext $context Authorized context.
	 * @return array<string,mixed>
	 */
	public function effective( ExecutionContext $context ): array {
		return $this->resolver->resolve( $context )->toArray();
	}
}
