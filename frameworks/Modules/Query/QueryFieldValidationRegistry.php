<?php
/**
 * Read-only data source registry for Query Field validation.
 *
 * @package WPEssential\Modules\Query
 */

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use LogicException;
use RuntimeException;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Platform\DataSources\DataSourceDescriptor;

/** Overlay a projected data-source descriptor without mutating the registry. */
final readonly class QueryFieldValidationRegistry implements DataSourceRegistryInterface {

	/**
	 * Construct a read-only registry overlay for one source.
	 *
	 * @param DataSourceRegistryInterface $inner Original registry.
	 * @param string                      $sourceRef Source identifier.
	 * @param DataSourceDescriptor        $projected Projected descriptor.
	 */
	public function __construct(
		private DataSourceRegistryInterface $inner,
		private string $sourceRef,
		private DataSourceDescriptor $projected,
	) {
	}

	/**
	 * Reject writes to the validation-only overlay.
	 *
	 * @param DataSourceDescriptor $descriptor Descriptor that cannot be registered.
	 * @throws LogicException Always: the overlay is read-only.
	 */
	public function register( DataSourceDescriptor $descriptor ): void {
		throw new LogicException( 'Query Field validation registry is read-only.' );
	}

	/**
	 * Determine whether a source is available in the overlay.
	 *
	 * @param string $id Source identifier.
	 * @return bool
	 */
	public function has( string $id ): bool {
		return $id === $this->sourceRef || $this->inner->has($id);
	}

	/**
	 * Resolve a projected or underlying data-source descriptor.
	 *
	 * @param string $id Source identifier.
	 * @return DataSourceDescriptor|null
	 */
	public function find( string $id ): ?DataSourceDescriptor {
		return $id === $this->sourceRef ? $this->projected : $this->inner->find($id);
	}

	/**
	 * Require a projected or underlying data-source descriptor.
	 *
	 * @param string $id Source identifier.
	 * @return DataSourceDescriptor
	 * @throws RuntimeException When no descriptor is registered for the source.
	 */
	public function require( string $id ): DataSourceDescriptor {
		return $this->find($id) ?? throw new RuntimeException(sprintf('Data Source "%s" is not registered.', $id));
	}

	/**
	 * Return all data-source descriptors with the projected source replaced.
	 *
	 * @return array<int,DataSourceDescriptor>
	 */
	public function all(): array {
		$all   = $this->inner->all();
		$found = false;
		foreach ( $all as $index => $descriptor ) {
			if ($descriptor->id === $this->sourceRef) {
				$all[ $index ] = $this->projected;
				$found         = true;
			}
		}
		if ( ! $found ) {
			$all[] = $this->projected;
		}
		return array_values( $all );
	}
}
