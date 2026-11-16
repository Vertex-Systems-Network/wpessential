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

	private const RESULT         = 'wpessential_tool_result_';
	private const RESTORE_OPTION = 'wpessential_reset_restore_point';
	private const HISTORY_OPTION = 'wpessential_reset_history';

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
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Checked in handle().
		$op   = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( (string) $_POST['op'] ) ) : '';
		$user = get_current_user_id();
		if ( $op === 'cleanup' ) {
			$tools = isset( $_POST['tools'] ) && is_array( $_POST['tools'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['tools'] ) ) : [];
			$tools = array_values( array_intersect( $tools, array_keys( Cleanup::tools() ) ) );
			if ( $tools === [] ) {
				$this->back( 'nothing' );
			}
			$log     = [];
			$cleanup = new Cleanup();
			foreach ( $tools as $tool ) {
				$log[] = [
					'part'    => 'cleanup',
					'item'    => Cleanup::tools()[ $tool ][0],
					'ok'      => true,
					'message' => (string) $cleanup->run( $tool ) . ' ' . __( 'items', 'wpessential' ),
				];
			}
			$this->remember( $tools, $log );
			set_transient( self::RESULT . $user, [ 'reset' => $log ], 600 );
			$this->back( 'reset' );
		}
		if ( $op === 'restore' ) {
			$point = get_option( self::RESTORE_OPTION );
			if ( ! is_array( $point ) || ! is_array( $point['data'] ?? null ) ) {
				$this->back( 'expired' );
			}
			$done = ( $this->importExport )()->import( $point['data'] );
			$log  = array_map(
				static fn ( array $row ): array => [
					'part'    => 'restore',
					'item'    => $row['slug'],
					'ok'      => $row['error'] === '',
					'message' => $row['action'] . ( $row['error'] !== '' ? ': ' . $row['error'] : '' ),
				],
				$done
			);
			$this->remember( [ 'restore' ], $log );
			set_transient( self::RESULT . $user, [ 'reset' => $log ], 600 );
			$this->back( 'reset' );
		}

		$parts   = isset( $_POST['parts'] ) && is_array( $_POST['parts'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['parts'] ) ) : [];
		$parts   = array_values( array_intersect( $parts, Reset::PARTS ) );
		$confirm = isset( $_POST['confirm'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['confirm'] ) ) : '';
		// phpcs:enable
		if ( $parts === [] ) {
			$this->back( 'nothing' );
		}
		if ( strtoupper( $confirm ) !== 'RESET' ) {
			$this->back( 'confirm' );
		}
		if ( array_intersect( $parts, [ 'settings', 'definitions' ] ) !== [] ) {
			// Restore point: the same file Export makes, so a reset of WPEssential data can be undone.
			update_option(
				self::RESTORE_OPTION,
				[
					'time' => time(),
					'data' => ( $this->importExport )()->export( [ 'post_types', 'taxonomies', 'settings' ], (string) home_url() ),
				],
				false
			);
		}
		$log = ( $this->reset )()->run( $parts );
		$this->remember( $parts, $log );
		set_transient( self::RESULT . $user, [ 'reset' => $log ], 600 );
		$this->back( 'reset' );
	}

	/**
	 * @param list<string>                     $parts
	 * @param list<array{ok:bool}|array<string,mixed>> $log
	 */
	private function remember( array $parts, array $log ): void {
		$history   = get_option( self::HISTORY_OPTION, [] );
		$history   = is_array( $history ) ? array_values( $history ) : [];
		$ok        = count( array_filter( $log, static fn ( array $row ): bool => (bool) ( $row['ok'] ?? false ) ) );
		$user      = wp_get_current_user();
		$history[] = [
			'time'   => time(),
			'user'   => (string) $user->user_login,
			'parts'  => implode( ', ', $parts ),
			'ok'     => $ok,
			'failed' => count( $log ) - $ok,
		];
		update_option( self::HISTORY_OPTION, array_slice( $history, -20 ), false );
	}

	private function handleRest(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Checked in handle().
		$op = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( (string) $_POST['op'] ) ) : '';
		if ( $op === 'clear_log' ) {
			delete_option( RestControl::LOG_OPTION );
			$this->back( 'saved' );
		}
		$list     = static fn ( string $key ): array => isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) ) : [];
		$text     = static fn ( string $key ): string => isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST[ $key ] ) ) : '';
		$settings = RestControl::normalize(
			[
				'logged_in_only'      => isset( $_POST['logged_in_only'] ),
				'allow_routes'        => array_merge( $list( 'allow_known' ), preg_split( '/[\r\n,]+/', $text( 'allow_routes' ) ) ?: [] ),
				'restricted_roles'    => $list( 'restricted_roles' ),
				'hide_users'          => isset( $_POST['hide_users'] ),
				'disabled_namespaces' => $list( 'disabled_namespaces' ),
				'rate_limit'          => isset( $_POST['rate_limit'] ) ? absint( $_POST['rate_limit'] ) : 0,
				'rate_exempt_ips'     => $text( 'rate_exempt_ips' ),
				'remove_discovery'    => isset( $_POST['remove_discovery'] ),
				'disable_jsonp'       => isset( $_POST['disable_jsonp'] ),
				'disable_xmlrpc'      => isset( $_POST['disable_xmlrpc'] ),
				'log_blocked'         => isset( $_POST['log_blocked'] ),
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
			__( 'A restore point of your WPEssential settings and definitions is saved first, so you can undo those. Demo content cannot be restored.', 'wpessential' ),
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
		$html   = OptionGroup::render( 'wpe-tool-reset', __( 'WPEssential data', 'wpessential' ), '', '', [ $this->form( 'reset', implode( '', $rows ) ) ] );

		$counts  = ( new Cleanup() )->counts();
		$cleanup = [];
		foreach ( Cleanup::tools() as $id => [ $label, $help ] ) {
			$cleanup[] = OptionGroup::row(
				$label,
				$help,
				StatusPill::render( $counts[ $id ] > 0 ? 'waiting' : 'off', number_format_i18n( $counts[ $id ] ) )
				. Check::render(
					__( 'Clean', 'wpessential' ),
					[
						'name'     => 'tools[]',
						'value'    => $id,
						'id'       => 'wpe-clean-' . $id,
						'disabled' => $counts[ $id ] === 0 ? 'disabled' : null,
					]
				)
			);
		}
		$cleanup[] = OptionGroup::row(
			__( 'Run', 'wpessential' ),
			sprintf(
				/* translators: %d: batch size. */
				__( 'Removed items cannot be restored. Large tables are cleaned %d rows at a time; run again to continue.', 'wpessential' ),
				Cleanup::BATCH
			),
			Check::render(
				__( 'I understand', 'wpessential' ),
				[
					'name'     => 'ack',
					'id'       => 'wpe-clean-ack',
					'required' => 'required',
				]
			) . ' ' . self::submit( __( 'Clean selected', 'wpessential' ), 'danger' )
		);
		$html .= OptionGroup::render( 'wpe-tool-cleanup', __( 'Database cleanup', 'wpessential' ), '', '', [ $this->form( 'cleanup', implode( '', $cleanup ) ) ] );

		$point = get_option( self::RESTORE_OPTION );
		if ( is_array( $point ) && isset( $point['time'] ) ) {
			$when  = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $point['time'] );
			$html .= OptionGroup::render(
				'wpe-tool-restore',
				__( 'Restore point', 'wpessential' ),
				'',
				'',
				[
					$this->form(
						'restore',
						OptionGroup::row(
							sprintf(
								/* translators: %s: date and time. */
								__( 'Saved %s', 'wpessential' ),
								(string) $when
							),
							__( 'Brings back the post types, taxonomies and settings as they were before the last reset.', 'wpessential' ),
							self::submit( __( 'Restore', 'wpessential' ), 'secondary' )
						)
					),
				]
			);
		}

		$state = get_transient( self::RESULT . get_current_user_id() );
		if ( is_array( $state ) && is_array( $state['reset'] ?? null ) ) {
			delete_transient( self::RESULT . get_current_user_id() );
			$html .= OptionGroup::render( 'wpe-tool-reset-log', __( 'Results', 'wpessential' ), '', '', [ self::table( [ __( 'Part', 'wpessential' ), __( 'Item', 'wpessential' ), __( 'Result', 'wpessential' ) ], array_map( static fn ( array $row ): array => [ (string) $row['part'], (string) $row['item'], ( $row['ok'] ? '✓ ' : '✗ ' ) . $row['message'] ], $state['reset'] ) ) ] );
		}
		$history = get_option( self::HISTORY_OPTION, [] );
		if ( is_array( $history ) && $history !== [] ) {
			$rows  = array_map(
				static fn ( array $row ): array => [ (string) wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $row['time'] ), (string) $row['user'], (string) $row['parts'], $row['ok'] . ' ✓ / ' . $row['failed'] . ' ✗' ],
				array_reverse( array_values( $history ) )
			);
			$html .= OptionGroup::render( 'wpe-tool-history', __( 'History', 'wpessential' ), '', '', [ self::table( [ __( 'When', 'wpessential' ), __( 'User', 'wpessential' ), __( 'What', 'wpessential' ), __( 'Result', 'wpessential' ) ], $rows ) ], false );
		}

		return $html;
	}

	private function renderRest(): string {
		$settings   = RestControl::settings();
		$namespaces = function_exists( 'rest_get_server' ) ? rest_get_server()->get_namespaces() : [];
		$others     = array_values( array_unique( array_merge( array_diff( $namespaces, RestControl::ALWAYS_ON ), $settings['disabled_namespaces'] ) ) );
		sort( $others );
		$checks = static function ( string $name, array $values, array $selected, string $prefix ): string {
			$html = '';
			foreach ( $values as $value => $label ) {
				$html .= Check::render(
					(string) $label,
					[
						'name'  => $name . '[]',
						'value' => (string) $value,
						'id'    => $prefix . sanitize_key( (string) $value ),
					],
					in_array( (string) $value, $selected, true )
				);
			}

			return '<div class="wpe-ui-stack">' . ( $html !== '' ? $html : esc_html__( 'None found.', 'wpessential' ) ) . '</div>';
		};
		$known  = [];
		foreach ( RestControl::KNOWN_PUBLIC as $namespace => $label ) {
			if ( in_array( $namespace, $namespaces, true ) ) {
				$known[ $namespace ] = $label . ' (' . $namespace . ')';
			}
		}
		$custom = array_diff( $settings['allow_routes'], array_keys( $known ) );
		$roles  = [];
		foreach ( wp_roles()->get_names() as $role => $name ) {
			if ( $role !== 'administrator' ) {
				$roles[ $role ] = translate_user_role( $name );
			}
		}
		$on     = static fn ( string $key, string $id ): string => Check::render(
			__( 'On', 'wpessential' ),
			[
				'name'  => $key,
				'id'    => $id,
				'value' => '1',
			],
			(bool) $settings[ $key ]
		);
		$access = [
			OptionGroup::row( __( 'Signed-in users only', 'wpessential' ), __( 'Visitors get "401" from every REST route except the exceptions below. Test your forms and checkout after turning this on.', 'wpessential' ), $on( 'logged_in_only', 'wpe-rest-login' ) ),
			OptionGroup::row( __( 'Exceptions for plugins found', 'wpessential' ), __( 'Public routes these active plugins need (forms, cart, checkout). Ticked ones stay open to visitors.', 'wpessential' ), $checks( 'allow_known', $known, $settings['allow_routes'], 'wpe-allow-' ) ),
			OptionGroup::row( __( 'Other exceptions', 'wpessential' ), __( 'One route or namespace per line, for example wp/v2/posts or my-plugin/v1.', 'wpessential' ), '<textarea name="allow_routes" rows="3" class="large-text code" aria-label="' . esc_attr__( 'Other exceptions', 'wpessential' ) . '">' . esc_textarea( implode( "\n", $custom ) ) . '</textarea>' ),
			OptionGroup::row( __( 'Restricted roles', 'wpessential' ), __( 'Signed-in users who only have these roles are treated like visitors (for example Subscriber or Customer). Administrators are never restricted.', 'wpessential' ), $checks( 'restricted_roles', $roles, $settings['restricted_roles'], 'wpe-role-' ) ),
			OptionGroup::row( __( 'Hide the user list', 'wpessential' ), __( 'Stops visitors listing usernames through /wp/v2/users.', 'wpessential' ), $on( 'hide_users', 'wpe-rest-users' ) ),
			OptionGroup::row( __( 'Switch off for visitors', 'wpessential' ), __( 'Ticked namespaces answer "404" to visitors. Editors keep access so the block editor works. WPEssential and oEmbed always stay on.', 'wpessential' ), $checks( 'disabled_namespaces', array_combine( $others, $others ) ?: [], $settings['disabled_namespaces'], 'wpe-ns-' ) ),
		];
		$harden = [
			OptionGroup::row(
				__( 'Rate limit', 'wpessential' ),
				__( 'REST requests per minute per visitor IP. 0 turns it off.', 'wpessential' ),
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
			OptionGroup::row( __( 'Never limit these IPs', 'wpessential' ), __( 'One IP per line, for your own office, uptime monitors or a headless front end.', 'wpessential' ), '<textarea name="rate_exempt_ips" rows="2" class="large-text code" aria-label="' . esc_attr__( 'Never limit these IPs', 'wpessential' ) . '">' . esc_textarea( implode( "\n", $settings['rate_exempt_ips'] ) ) . '</textarea>' ),
			OptionGroup::row( __( 'Hide REST discovery links', 'wpessential' ), __( 'Removes the api.w.org link from page headers and HTML. The API still works for those allowed.', 'wpessential' ), $on( 'remove_discovery', 'wpe-rest-discovery' ) ),
			OptionGroup::row( __( 'Turn off JSONP', 'wpessential' ), __( 'Old cross-site callback format nobody needs today.', 'wpessential' ), $on( 'disable_jsonp', 'wpe-rest-jsonp' ) ),
			OptionGroup::row( __( 'Turn off XML-RPC', 'wpessential' ), __( 'Blocks xmlrpc.php, a common brute-force target. Leave on if you use the Jetpack app or old publishing apps.', 'wpessential' ), $on( 'disable_xmlrpc', 'wpe-rest-xmlrpc' ) ),
			OptionGroup::row( __( 'Log blocked requests', 'wpessential' ), __( 'Keeps the last 100 blocked requests with a masked IP.', 'wpessential' ), $on( 'log_blocked', 'wpe-rest-log' ) ),
			OptionGroup::row( __( 'Save', 'wpessential' ), '', self::submit( __( 'Save changes', 'wpessential' ) ) ),
		];
		$html   = $this->form(
			'save',
			OptionGroup::render( 'wpe-tool-rest', __( 'Who can use the REST API', 'wpessential' ), '', '', $access )
			. OptionGroup::render( 'wpe-tool-harden', __( 'Hardening', 'wpessential' ), '', '', $harden )
		);

		$blocked = RestControl::blocked();
		$rows    = array_map(
			static fn ( array $row ): array => [ (string) wp_date( get_option( 'time_format' ) . ', ' . get_option( 'date_format' ), (int) $row['time'] ), (string) $row['method'] . ' ' . (string) $row['route'], (string) $row['status'] . ' ' . (string) $row['code'], (string) $row['ip'] ],
			array_slice( $blocked, 0, 50 )
		);
		$log     = $rows === []
			? [ OptionGroup::row( __( 'Nothing blocked yet.', 'wpessential' ), '', '' ) ]
			: [ self::table( [ __( 'Time', 'wpessential' ), __( 'Request', 'wpessential' ), __( 'Answer', 'wpessential' ), __( 'IP', 'wpessential' ) ], $rows ), $this->form( 'clear_log', self::submit( __( 'Clear log', 'wpessential' ), 'ghost' ) ) ];

		return $html . OptionGroup::render( 'wpe-tool-rest-log', __( 'Blocked requests', 'wpessential' ), '', '', $log, true, count( $blocked ) );
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
