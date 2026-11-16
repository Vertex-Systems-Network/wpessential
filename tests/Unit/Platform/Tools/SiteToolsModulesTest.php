<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Tools;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Platform\Tools\ImportExport;
use WPEssential\Platform\Tools\Reset;
use WPEssential\Platform\Tools\RestControl;

final class SiteToolsModulesTest extends TestCase {

	/** @var array<string,list<array<string,mixed>>> */
	private array $store = [];

	/** @var list<array{0:string,1:array<string,mixed>}> */
	private array $calls = [];

	/** @var array<string,mixed> */
	private array $options = [];

	protected function setUp(): void {
		$this->store   = [
			'wpessential/cpt'      => [ $this->def( 'c1', 'book', [ 'label' => 'Books' ] ) ],
			'wpessential/taxonomy' => [ $this->def( 't1', 'genre', [ 'label' => 'Genres' ] ), $this->def( 't2', 'old', [ 'trashed' => true ] ) ],
		];
		$this->calls   = [];
		$this->options = [ 'wpessential_module_toggles' => [ 'reset' ] ];
	}

	/** @param array<string,mixed> $payload @return array<string,mixed> */
	private function def( string $id, string $slug, array $payload ): array {
		return [
			'id'       => $id,
			'slug'     => $slug,
			'status'   => 'published',
			'payload'  => $payload,
			'revision' => 3,
		];
	}

	private function runner(): \Closure {
		return function ( string $ability, array $input ): mixed {
			$this->calls[] = [ $ability, $input ];
			$kind          = substr( $ability, 0, (int) strrpos( $ability, '/' ) );
			if ( str_ends_with( $ability, '/list' ) ) {
				return [ 'definitions' => $this->store[ $kind ] ];
			}
			if ( str_ends_with( $ability, '/save' ) && ( $input['payload']['bad'] ?? false ) ) {
				throw new RuntimeException( 'Invalid payload' );
			}
			if ( str_ends_with( $ability, '/trash' ) ) {
				return [ 'definition' => [ 'revision' => $input['expected_revision'] + 1 ] ];
			}

			return [];
		};
	}

	private function tool(): ImportExport {
		return new ImportExport(
			$this->runner(),
			fn ( string $o ): mixed => $this->options[ $o ] ?? [],
			function ( string $o, mixed $v ): void {
				$this->options[ $o ] = $v;
			}
		);
	}

	public function test_export_skips_trashed_and_secrets(): void {
		$data = $this->tool()->export( [ 'post_types', 'taxonomies', 'settings' ] );
		self::assertSame( ImportExport::FORMAT, $data['format'] );
		self::assertSame( [ 'book' ], array_column( $data['post_types'], 'slug' ) );
		self::assertSame( [ 'genre' ], array_column( $data['taxonomies'], 'slug' ) );
		self::assertSame( [ 'module_toggles', 'elements' ], array_keys( $data['settings'] ) );
	}

	public function test_preview_and_import_match_by_slug(): void {
		$data = [
			'format'     => ImportExport::FORMAT,
			'version'    => 1,
			'post_types' => [
				[
					'slug'    => 'book',
					'status'  => 'published',
					'payload' => [ 'label' => 'Books' ],
				],
				[
					'slug'    => 'movie',
					'payload' => [ 'label' => 'Movies' ],
				],
				[
					'slug'    => 'broken',
					'payload' => [ 'bad' => true ],
				],
			],
			'taxonomies' => [
				[
					'slug'    => 'genre',
					'payload' => [ 'label' => 'Kinds' ],
				],
			],
		];
		$plan = array_column( $this->tool()->preview( $data ), 'action', 'slug' );
		self::assertSame(
			[
				'book'   => 'unchanged',
				'movie'  => 'create',
				'broken' => 'create',
				'genre'  => 'update',
			],
			$plan
		);

		$result = array_column( $this->tool()->import( $data ), 'action', 'slug' );
		self::assertSame( 'updated', $result['book'] );
		self::assertSame( 'created', $result['movie'] );
		self::assertSame( 'failed', $result['broken'] );
		$genre = array_values( array_filter( $this->calls, static fn ( array $c ): bool => $c[0] === 'wpessential/taxonomy/save' ) )[0][1];
		self::assertSame( 't1', $genre['id'] );
		self::assertSame( 3, $genre['expected_revision'] );
	}

	public function test_parse_rejects_foreign_files(): void {
		self::assertIsString( ImportExport::parse( '{"hello":1}' ) );
		self::assertIsString( ImportExport::parse( '{"format":"wpessential-export","version":99}' ) );
		self::assertIsArray( ImportExport::parse( '{"format":"wpessential-export","version":1}' ) );
	}

	public function test_reset_trashes_then_deletes_and_keeps_locked(): void {
		$this->store['wpessential/cpt'][] = $this->def( 'c2', 'keep', [ 'locked' => true ] );
		$deleted                          = [];
		$reset                            = new Reset(
			$this->runner(),
			function ( string $o ) use ( &$deleted ): void {
				$deleted[] = $o;
			},
			static fn (): int => 4,
			static fn (): int => 4
		);
		self::assertSame( 4, $reset->preview()['demo'] );
		$log = $reset->run( [ 'definitions', 'settings' ] );
		$by  = array_column( $log, 'message', 'item' );
		self::assertSame( 'locked, kept', $by['keep'] );
		self::assertSame( 'deleted', $by['book'] );
		self::assertSame( Reset::SETTINGS, $deleted );
		$book = array_values( array_filter( $this->calls, static fn ( array $c ): bool => $c[0] === 'wpessential/cpt/delete' && $c[1]['id'] === 'c1' ) )[0][1];
		self::assertSame( 4, $book['expected_revision'] );
		$first = array_values( array_filter( $this->calls, static fn ( array $c ): bool => str_ends_with( $c[0], '/delete' ) ) )[0][0];
		self::assertSame( 'wpessential/taxonomy/delete', $first, 'Taxonomies go before post types.' );
	}

	public function test_rest_decisions(): void {
		$settings = RestControl::normalize(
			[
				'logged_in_only'      => true,
				'hide_users'          => true,
				'disabled_namespaces' => [ 'wc/v3', 'wpessential/v1' ],
			]
		);
		self::assertSame( [ 'wc/v3' ], $settings['disabled_namespaces'] );
		self::assertNull( RestControl::decide( $settings, '/wpessential/v1/mcp', false, false ) );
		self::assertSame( [ 'wpessential_rest_login_required', 401 ], RestControl::decide( $settings, '/wp/v2/posts', false, false ) );
		self::assertNull( RestControl::decide( $settings, '/wp/v2/posts', true, true ) );
		$open = RestControl::normalize( [ 'disabled_namespaces' => [ 'wc/v3' ] ] );
		self::assertNull( RestControl::decide( $open, '/wp/v2/posts', false, false ) );
		self::assertSame( 404, RestControl::decide( $open, '/wc/v3/products', true, false )[1] ?? 0 );
		self::assertNotNull( RestControl::decide( $open, '/wp/v2/users', false, false ) );
	}
}
