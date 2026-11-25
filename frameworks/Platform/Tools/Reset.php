<?php

declare(strict_types=1);

namespace WPEssential\Platform\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Closure;
use Throwable;

/**
 * Guarded reset of WPEssential's own data. Never touches WordPress posts, users or other plugins,
 * except the demo content WPEssential itself imported (removed through the importer's own record).
 */
final class Reset {

	public const PARTS = [ 'settings', 'definitions', 'demo' ];

	/** WPEssential options restored to defaults by the "settings" part. */
	public const SETTINGS = [
		'wpessential_module_toggles',
		'wpessential_module_optins',
		'wpessential_coming_soon',
		'wpessential_elements_state',
		'wpessential_ai_connect',
	];

	/**
	 * @param Closure(string, array<string,mixed>): mixed $run        Runs an ability as the current user.
	 * @param Closure(string): void                       $delete     Deletes an option.
	 * @param Closure(): int                              $demoCount  Items the demo importer would remove.
	 * @param Closure(): int                              $removeDemo Removes imported demo content, returns the count.
	 */
	public function __construct(
		private readonly Closure $run,
		private readonly Closure $delete,
		private readonly Closure $demoCount,
		private readonly Closure $removeDemo,
	) {}

	/**
	 * What each part would remove.
	 *
	 * @return array{settings:int,definitions:list<array{kind:string,slug:string,locked:bool}>,demo:int}
	 */
	public function preview(): array {
		$definitions = [];
		foreach ( array_reverse( ImportExport::KINDS, true ) as $key => $ability ) {
			foreach ( $this->definitions( $ability ) as $definition ) {
				$definitions[] = [
					'kind'   => $key,
					'slug'   => (string) $definition['slug'],
					'locked' => ( $definition['payload']['locked'] ?? false ) === true,
				];
			}
		}

		return [
			'settings'    => count( self::SETTINGS ),
			'definitions' => $definitions,
			'demo'        => ( $this->demoCount )(),
		];
	}

	/**
	 * @param list<string> $parts
	 * @return list<array{part:string,item:string,ok:bool,message:string}>
	 */
	public function run( array $parts ): array {
		$log = [];
		if ( in_array( 'demo', $parts, true ) ) {
			try {
				$log[] = [
					'part'    => 'demo',
					'item'    => 'demo content',
					'ok'      => true,
					'message' => (string) ( $this->removeDemo )() . ' items removed',
				];
			} catch ( Throwable $error ) {
				$log[] = [
					'part'    => 'demo',
					'item'    => 'demo content',
					'ok'      => false,
					'message' => $error->getMessage(),
				];
			}
		}
		if ( in_array( 'definitions', $parts, true ) ) {
			// Taxonomies first: post types they attach to cannot be deleted while linked.
			foreach ( array_reverse( ImportExport::KINDS, true ) as $ability ) {
				foreach ( $this->definitions( $ability ) as $definition ) {
					$log[] = $this->deleteDefinition( $ability, $definition );
				}
			}
		}
		if ( in_array( 'settings', $parts, true ) ) {
			foreach ( self::SETTINGS as $option ) {
				( $this->delete )( $option );
				$log[] = [
					'part'    => 'settings',
					'item'    => $option,
					'ok'      => true,
					'message' => 'reset',
				];
			}
		}

		return $log;
	}

	/**
	 * @param array<string,mixed> $definition
	 * @return array{part:string,item:string,ok:bool,message:string}
	 */
	private function deleteDefinition( string $ability, array $definition ): array {
		$slug = (string) $definition['slug'];
		if ( ( $definition['payload']['locked'] ?? false ) === true ) {
			return [
				'part'    => 'definitions',
				'item'    => $slug,
				'ok'      => false,
				'message' => 'locked, kept',
			];
		}
		try {
			$revision = (int) $definition['revision'];
			if ( ( $definition['payload']['trashed'] ?? false ) !== true ) {
				$trashed  = ( $this->run )(
					$ability . '/trash',
					[
						'id'                => (string) $definition['id'],
						'expected_revision' => $revision,
					]
				);
				$revision = is_array( $trashed ) && is_array( $trashed['definition'] ?? null ) ? (int) $trashed['definition']['revision'] : $revision + 1;
			}
			( $this->run )(
				$ability . '/delete',
				[
					'id'                => (string) $definition['id'],
					'expected_revision' => $revision,
				]
			);

			return [
				'part'    => 'definitions',
				'item'    => $slug,
				'ok'      => true,
				'message' => 'deleted',
			];
		} catch ( Throwable $error ) {
			return [
				'part'    => 'definitions',
				'item'    => $slug,
				'ok'      => false,
				'message' => $error->getMessage(),
			];
		}
	}

	/** @return list<array<string,mixed>> */
	private function definitions( string $ability ): array {
		$list = ( $this->run )( $ability . '/list', [] );
		$list = is_array( $list ) && is_array( $list['definitions'] ?? null ) ? $list['definitions'] : [];

		return array_values( array_filter( $list, static fn ( mixed $item ): bool => is_array( $item ) && is_string( $item['slug'] ?? null ) ) );
	}
}
