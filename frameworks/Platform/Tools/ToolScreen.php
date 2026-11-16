<?php

declare(strict_types=1);

namespace WPEssential\Platform\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Closure;
use WPEssential\Platform\Admin\Core\AdminScreen;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\Admin\Ui\AdminUrl;
use WPEssential\Platform\Admin\Ui\Button;
use WPEssential\Platform\Admin\Ui\Check;
use WPEssential\Platform\Admin\Ui\Field;
use WPEssential\Platform\Admin\Ui\Notice;
use WPEssential\Platform\Admin\Ui\OptionGroup;
use WPEssential\Platform\Admin\Ui\PageHeader;
use WPEssential\Platform\Admin\Ui\Screen;
use WPEssential\Platform\Admin\Ui\Shell;
use WPEssential\Platform\Admin\Ui\StatusPill;

/**
 * Admin screen of one Free site-tools module: Import / Export, Reset Tools or REST API Control.
 */
final class ToolScreen implements AdminScreen {

	public const IMPORT_EXPORT = 'import-export';
	public const RESET         = 'reset';
	public const REST_API      = 'rest-api';

	public const SLUGS = [
		self::IMPORT_EXPORT => 'wpessential-import-export',
		self::RESET         => 'wpessential-reset',
		self::REST_API      => 'wpessential-rest-api',
	];

	private const RESULT = 'wpessential_tool_result_';

	/**
	 * @param Closure(): ImportExport $importExport
	 * @param Closure(): Reset        $reset
	 * @param Closure(string): bool   $moduleOn
	 */
	public function __construct(
		private readonly string $module,
		private readonly Closure $importExport,
		private readonly Closure $reset,
		private readonly Closure $moduleOn,
	) {}

	public function slug(): string {
		return self::SLUGS[ $this->module ];
	}

	public function title(): string {
		return match ( $this->module ) {
			self::IMPORT_EXPORT => __( 'Import / Export', 'wpessential' ),
			self::RESET         => __( 'Reset Tools', 'wpessential' ),
			default             => __( 'REST API Control', 'wpessential' ),
		};
	}

	private function action(): string {
		return 'wpessential_tool_' . str_replace( '-', '_', $this->module );
	}

	public function register(): void {
		if ( function_exists( 'add_action' ) ) {
			add_action( 'admin_post_' . $this->action(), [ $this, 'handle' ] );
		}
	}

	public function handle(): void {
		if ( ! current_user_can( PlatformAdminController::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to manage WPEssential.', 'wpessential' ), 'WPEssential', [ 'response' => 403 ] );
		}
		check_admin_referer( $this->action() );
		if ( ! ( $this->moduleOn )( $this->module ) ) {
			$this->back( 'off' );
		}
		$op = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( (string) $_POST['op'] ) ) : '';

		match ( $this->module ) {
			self::IMPORT_EXPORT => $this->handleImportExport( $op ),
			self::RESET         => $this->handleReset(),
			default             => $this->handleRest(),
		};
	}

	private function handleImportExport( string $op ): void {
		$tool = ( $this->importExport )();
		if ( $op === 'export' ) {
			$parts = isset( $_POST['parts'] ) && is_array( $_POST['parts'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['parts'] ) ) : []; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked in handle().
			$data  = $tool->export( array_values( array_intersect( $parts, [ 'post_types', 'taxonomies', 'settings' ] ) ), (string) home_url() );
			nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="wpessential-export-' . gmdate( 'Y-m-d' ) . '.json"' );
			echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
			exit;
		}

		$user = get_current_user_id();
		if ( $op === 'preview' ) {
			$file = isset( $_FILES['file']['tmp_name'] ) && is_string( $_FILES['file']['tmp_name'] ) ? sanitize_text_field( $_FILES['file']['tmp_name'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- Nonce checked in handle(); path checked with is_uploaded_file().
			if ( $file === '' || ! is_uploaded_file( $file ) || filesize( $file ) > 5 * MB_IN_BYTES ) {
				$this->back( 'nofile' );
			}
			$data = ImportExport::parse( (string) file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Uploaded temp file.
			if ( is_string( $data ) ) {
				set_transient( self::RESULT . $user, [ 'error' => $data ], 600 );
				$this->back( 'invalid' );
			}
			set_transient(
				self::RESULT . $user,
				[
					'pending' => $data,
					'plan'    => $tool->preview( $data ),
				],
				1800
			);
			$this->back( 'preview' );
		}
		if ( $op === 'import' ) {
			$stored = get_transient( self::RESULT . $user );
			if ( ! is_array( $stored ) || ! is_array( $stored['pending'] ?? null ) ) {
				$this->back( 'expired' );
			}
			set_transient( self::RESULT . $user, [ 'done' => $tool->import( $stored['pending'] ) ], 600 );
			$this->back( 'imported' );
		}
		if ( $op === 'cancel' ) {
			delete_transient( self::RESULT . $user );
		}
		$this->back( '' );
	}

	private function handleReset(): void {
		$parts   = isset( $_POST['parts'] ) && is_array( $_POST['parts'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['parts'] ) ) : []; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked in handle().
		$parts   = array_values( array_intersect( $parts, Reset::PARTS ) );
		$confirm = isset( $_POST['confirm'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['confirm'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked in handle().
		if ( $parts === [] ) {
			$this->back( 'nothing' );
		}
		if ( strtoupper( $confirm ) !== 'RESET' ) {
			$this->back( 'confirm' );
		}
		set_transient( self::RESULT . get_current_user_id(), [ 'reset' => ( $this->reset )()->run( $parts ) ], 600 );
		$this->back( 'reset' );
	}

	private function handleRest(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Checked in handle().
		$namespaces = isset( $_POST['disabled_namespaces'] ) && is_array( $_POST['disabled_namespaces'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['disabled_namespaces'] ) ) : [];
		$settings   = RestControl::normalize(
			[
				'logged_in_only'      => isset( $_POST['logged_in_only'] ),
				'hide_users'          => isset( $_POST['hide_users'] ),
				'disabled_namespaces' => $namespaces,
				'rate_limit'          => isset( $_POST['rate_limit'] ) ? absint( $_POST['rate_limit'] ) : 0,
			]
		);
		// phpcs:enable
		update_option( RestControl::OPTION, $settings, false );
		$this->back( 'saved' );
	}

	private function back( string $notice ): never {
		wp_safe_redirect( AdminUrl::page( $this->slug(), $notice !== '' ? [ 'wpe-notice' => $notice ] : [] ) );
		exit;
	}

	private function form( string $op, string $inner, bool $upload = false ): string {
		$html  = '<form method="post" class="wpe-ui-form" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"' . ( $upload ? ' enctype="multipart/form-data"' : '' ) . '>';
		$html .= wp_nonce_field( $this->action(), '_wpnonce', true, false );
		$html .= '<input type="hidden" name="action" value="' . esc_attr( $this->action() ) . '"><input type="hidden" name="op" value="' . esc_attr( $op ) . '">';

		return $html . $inner . '</form>';
	}

	private static function submit( string $label, string $variant = 'primary' ): string {
		return '<button type="submit" class="' . esc_attr( Button::classes( $variant ) ) . '">' . esc_html( $label ) . '</button>';
	}

	public function render(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice routing.
		$notice = isset( $_GET['wpe-notice'] ) ? sanitize_key( wp_unslash( (string) $_GET['wpe-notice'] ) ) : '';
		$html   = PageHeader::render( $this->title(), __( 'Site tools', 'wpessential' ), $this->intro() );

		if ( ! ( $this->moduleOn )( $this->module ) ) {
			$html .= Notice::render( 'info', __( 'This module is switched off. Turn it on under WPEssential → Modules.', 'wpessential' ) )
				. '<p>' . Button::link( __( 'Open Modules', 'wpessential' ), AdminUrl::page( Shell::MODULES_SLUG ), 'secondary' ) . '</p>';
			Screen::render( $this->slug(), $html );
			return;
		}

		$html .= match ( $this->module ) {
			self::IMPORT_EXPORT => $this->renderImportExport(),
			self::RESET         => $this->renderReset(),
			default             => $this->renderRest(),
		};

		$messages = [
			'saved'    => [ 'success', __( 'Saved.', 'wpessential' ) ],
			'preview'  => [ 'info', __( 'Check what will change, then import.', 'wpessential' ) ],
			'imported' => [ 'success', __( 'Import finished. See the results below.', 'wpessential' ) ],
			'reset'    => [ 'success', __( 'Reset finished. See the results below.', 'wpessential' ) ],
			'nofile'   => [ 'error', __( 'Choose a WPEssential export file (JSON, up to 5 MB).', 'wpessential' ) ],
			'invalid'  => [ 'error', __( 'That file could not be imported.', 'wpessential' ) ],
			'expired'  => [ 'error', __( 'The preview expired. Upload the file again.', 'wpessential' ) ],
			'nothing'  => [ 'error', __( 'Choose at least one thing to reset.', 'wpessential' ) ],
			'confirm'  => [ 'error', __( 'Type RESET to confirm.', 'wpessential' ) ],
			'off'      => [ 'error', __( 'This module is switched off.', 'wpessential' ) ],
		];

		Screen::render( $this->slug(), $html, isset( $messages[ $notice ] ) ? Notice::render( ...$messages[ $notice ] ) : '' );
	}

	private function intro(): string {
		return match ( $this->module ) {
			self::IMPORT_EXPORT => __( 'Move your post types, taxonomies and WPEssential settings between sites as one JSON file.', 'wpessential' ),
			self::RESET         => __( 'Start over safely. Only WPEssential data is touched, and you see what will go first.', 'wpessential' ),
			default             => __( 'Choose what the REST API shows to visitors who are not signed in.', 'wpessential' ),
		};
	}

	private function renderImportExport(): string {
		$parts  = [
			'post_types' => __( 'Custom post types', 'wpessential' ),
			'taxonomies' => __( 'Taxonomies', 'wpessential' ),
			'settings'   => __( 'Module and element settings', 'wpessential' ),
		];
		$checks = '';
		foreach ( $parts as $key => $label ) {
			$checks .= Check::render(
				$label,
				[
					'name'  => 'parts[]',
					'value' => $key,
					'id'    => 'wpe-export-' . $key,
				],
				true
			);
		}
		$export = OptionGroup::render(
			'wpe-tool-export',
			__( 'Export', 'wpessential' ),
			'',
			'',
			[
				$this->form(
					'export',
					OptionGroup::row( __( 'Include', 'wpessential' ), __( 'Licence keys, AI sessions and passwords are never exported.', 'wpessential' ), '<div class="wpe-ui-stack">' . $checks . '</div>' )
					. OptionGroup::row( __( 'Download', 'wpessential' ), '', self::submit( __( 'Download export file', 'wpessential' ) ) )
				),
			]
		);

		$state = get_transient( self::RESULT . get_current_user_id() );
		$rows  = [];
		if ( is_array( $state ) && isset( $state['error'] ) ) {
			$rows[] = OptionGroup::row( __( 'File problem', 'wpessential' ), (string) $state['error'], StatusPill::render( 'waiting', __( 'Not imported', 'wpessential' ) ) );
			delete_transient( self::RESULT . get_current_user_id() );
		}
		if ( is_array( $state ) && is_array( $state['plan'] ?? null ) ) {
			$rows[] = self::table( [ __( 'Type', 'wpessential' ), __( 'Name', 'wpessential' ), __( 'Will be', 'wpessential' ) ], array_map( static fn ( array $row ): array => [ $row['kind'], $row['slug'], $row['action'] ], $state['plan'] ) );
			$rows[] = $this->form( 'import', OptionGroup::row( __( 'Import', 'wpessential' ), __( 'Existing items with the same name are updated; others are created.', 'wpessential' ), self::submit( __( 'Import now', 'wpessential' ) ) ) )
				. $this->form( 'cancel', self::submit( __( 'Cancel', 'wpessential' ), 'ghost' ) );
		} else {
			if ( is_array( $state ) && is_array( $state['done'] ?? null ) ) {
				$rows[] = self::table( [ __( 'Type', 'wpessential' ), __( 'Name', 'wpessential' ), __( 'Result', 'wpessential' ), __( 'Message', 'wpessential' ) ], array_map( static fn ( array $row ): array => [ $row['kind'], $row['slug'], $row['action'], $row['error'] ], $state['done'] ) );
				delete_transient( self::RESULT . get_current_user_id() );
			}
			$rows[] = $this->form(
				'preview',
				OptionGroup::row( __( 'Export file', 'wpessential' ), __( 'A .json file made by WPEssential Export. You will see a preview before anything changes.', 'wpessential' ), '<input type="file" name="file" accept=".json,application/json" required>' )
				. OptionGroup::row( __( 'Preview', 'wpessential' ), '', self::submit( __( 'Upload and preview', 'wpessential' ), 'secondary' ) ),
				true
			);
		}

		return $export . OptionGroup::render( 'wpe-tool-import', __( 'Import', 'wpessential' ), '', '', $rows );
	}

	private function renderReset(): string {
		$tool    = ( $this->reset )();
		$preview = $tool->preview();
		$names   = array_map( static fn ( array $item ): string => $item['slug'] . ( $item['locked'] ? ' (' . __( 'locked, kept', 'wpessential' ) . ')' : '' ), $preview['definitions'] );
		$parts   = [
			'settings'    => [ __( 'WPEssential settings', 'wpessential' ), __( 'Module and element switches and AI Connect settings go back to defaults. Licence and AI sessions stay.', 'wpessential' ) ],
			'definitions' => [
				__( 'Post types and taxonomies', 'wpessential' ),
				$names === [] ? __( 'None defined.', 'wpessential' ) : sprintf(
					/* translators: %s: list of definition names. */
					__( 'Deletes these definitions (your posts and terms stay in the database): %s', 'wpessential' ),
					implode( ', ', $names )
				),
			],
			'demo'        => [
				__( 'Imported demo content', 'wpessential' ),
				sprintf(
					/* translators: %d: number of items. */
					__( 'Removes the %d posts, pages, menus and terms the Demo Library imported.', 'wpessential' ),
					$preview['demo']
				),
			],
		];
		$rows = [];
		foreach ( $parts as $key => [ $label, $help ] ) {
			$rows[] = OptionGroup::row(
				$label,
				$help,
				Check::render(
					__( 'Reset', 'wpessential' ),
					[
						'name'  => 'parts[]',
						'value' => $key,
						'id'    => 'wpe-reset-' . $key,
					]
				)
			);
		}
		$rows[] = OptionGroup::row(
			__( 'Confirm', 'wpessential' ),
			__( 'This cannot be undone. Export first if you may need it.', 'wpessential' ),
			Field::input(
				'wpe-reset-confirm',
				__( 'Type RESET', 'wpessential' ),
				[
					'name'         => 'confirm',
					'autocomplete' => 'off',
				]
			)
		);
		$rows[] = OptionGroup::row( __( 'Run', 'wpessential' ), '', self::submit( __( 'Reset selected', 'wpessential' ), 'danger' ) );
		$html   = OptionGroup::render( 'wpe-tool-reset', __( 'Reset', 'wpessential' ), '', '', [ $this->form( 'reset', implode( '', $rows ) ) ] );

		$state = get_transient( self::RESULT . get_current_user_id() );
		if ( is_array( $state ) && is_array( $state['reset'] ?? null ) ) {
			delete_transient( self::RESULT . get_current_user_id() );
			$html .= OptionGroup::render( 'wpe-tool-reset-log', __( 'Results', 'wpessential' ), '', '', [ self::table( [ __( 'Part', 'wpessential' ), __( 'Item', 'wpessential' ), __( 'Result', 'wpessential' ) ], array_map( static fn ( array $row ): array => [ $row['part'], $row['item'], ( $row['ok'] ? '✓ ' : '✗ ' ) . $row['message'] ], $state['reset'] ) ) ] );
		}

		return $html;
	}

	private function renderRest(): string {
		$settings   = RestControl::settings();
		$namespaces = [];
		if ( function_exists( 'rest_get_server' ) ) {
			$namespaces = array_values( array_diff( rest_get_server()->get_namespaces(), RestControl::ALWAYS_ON ) );
		}
		$namespaces = array_values( array_unique( array_merge( $namespaces, $settings['disabled_namespaces'] ) ) );
		sort( $namespaces );
		$checks = '';
		foreach ( $namespaces as $namespace ) {
			$checks .= Check::render(
				$namespace,
				[
					'name'  => 'disabled_namespaces[]',
					'value' => $namespace,
					'id'    => 'wpe-ns-' . sanitize_key( $namespace ),
				],
				in_array( $namespace, $settings['disabled_namespaces'], true )
			);
		}
		$rows = [
			OptionGroup::row(
				__( 'Signed-in users only', 'wpessential' ),
				__( 'Visitors get "401" from every REST route. Headless front ends and some plugins need public REST, so test after turning this on.', 'wpessential' ),
				Check::render(
					__( 'On', 'wpessential' ),
					[
						'name'  => 'logged_in_only',
						'id'    => 'wpe-rest-login',
						'value' => '1',
					],
					$settings['logged_in_only']
				)
			),
			OptionGroup::row(
				__( 'Hide the user list', 'wpessential' ),
				__( 'Stops visitors listing usernames through /wp/v2/users.', 'wpessential' ),
				Check::render(
					__( 'On', 'wpessential' ),
					[
						'name'  => 'hide_users',
						'id'    => 'wpe-rest-users',
						'value' => '1',
					],
					$settings['hide_users']
				)
			),
			OptionGroup::row( __( 'Switch off for visitors', 'wpessential' ), __( 'Ticked namespaces answer "404" to visitors. Editors and admins keep access so the block editor works. WPEssential and oEmbed always stay on.', 'wpessential' ), '<div class="wpe-ui-stack">' . ( $checks !== '' ? $checks : esc_html__( 'No other namespaces.', 'wpessential' ) ) . '</div>' ),
			OptionGroup::row(
				__( 'Rate limit', 'wpessential' ),
				__( 'Requests per minute per visitor IP. 0 turns it off.', 'wpessential' ),
				Field::input(
					'wpe-rest-rate',
					__( 'Per minute', 'wpessential' ),
					[
						'type'  => 'number',
						'name'  => 'rate_limit',
						'min'   => '0',
						'max'   => '10000',
						'value' => (string) $settings['rate_limit'],
					]
				)
			),
			OptionGroup::row( __( 'Save', 'wpessential' ), '', self::submit( __( 'Save changes', 'wpessential' ) ) ),
		];

		return OptionGroup::render( 'wpe-tool-rest', __( 'REST API for visitors', 'wpessential' ), '', '', [ $this->form( 'save', implode( '', $rows ) ) ] );
	}

	/**
	 * @param list<string>       $headings
	 * @param list<list<string>> $rows
	 */
	private static function table( array $headings, array $rows ): string {
		$html = '<div class="wpe-ui-table-wrap"><table class="widefat striped"><thead><tr>';
		foreach ( $headings as $heading ) {
			$html .= '<th scope="col">' . esc_html( $heading ) . '</th>';
		}
		$html .= '</tr></thead><tbody>';
		foreach ( $rows as $row ) {
			$html .= '<tr>';
			foreach ( $row as $cell ) {
				$html .= '<td>' . esc_html( (string) $cell ) . '</td>';
			}
			$html .= '</tr>';
		}

		return $html . '</tbody></table></div>';
	}
}
