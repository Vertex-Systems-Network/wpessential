<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Platform\Admin\Ui\AdminUrl;
use WPEssential\Platform\Admin\Ui\OptionGroup;
use WPEssential\Platform\Admin\Ui\PageHeader;
use WPEssential\Platform\Admin\Ui\SectionNav;
use WPEssential\Platform\Admin\Ui\Shell;

final class PlatformAdminController {

	public const PAGE_SLUG   = 'wpessential';
	public const STATUS_SLUG = 'wpessential-status';
	public const CAPABILITY  = 'manage_options';

	private ?string $hookSuffix = null;

	public function __construct(
		private readonly RuntimeDiagnosticsSnapshot $snapshot,
		private readonly AdminAssetManifest $assets,
	) {}

	public function register(): void {
		if ( ! function_exists( 'add_action' ) ) {
			return;
		}

		add_action( 'admin_menu', [ $this, 'registerMenu' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
	}

	public function registerMenu(): void {
		if ( ! function_exists( 'add_submenu_page' ) ) {
			return;
		}

		// System Status is reached from the WPEssential top navigation, not the WordPress sidebar.
		$hook = add_submenu_page(
			'options.php',
			'System Status',
			'System Status',
			self::CAPABILITY,
			self::STATUS_SLUG,
			[ $this, 'render' ],
		);

		$this->hookSuffix = is_string( $hook ) && $hook !== '' ? $hook : null;
	}

	public function enqueueAssets( string $hookSuffix ): void {
		if ( $this->hookSuffix === null || $hookSuffix !== $this->hookSuffix ) {
			return;
		}

		$entry = $this->assets->entry();
		if ( $entry === null ) {
			return;
		}

		$version = $entry['version'] ?? ( defined( 'WPE_VERSION' ) ? (string) WPE_VERSION : null );

		foreach ( $entry['styles'] as $index => $style ) {
			wp_enqueue_style( 'wpessential-admin-' . $index, $style, [], $version );
		}

		wp_enqueue_script(
			'wpessential-admin',
			$entry['script'],
			$entry['dependencies'],
			$version,
			true,
		);

		if ( function_exists( 'wp_script_add_data' ) ) {
			wp_script_add_data( 'wpessential-admin', 'strategy', 'defer' );
		}
	}

	public function render(): void {
		if ( ! function_exists( 'current_user_can' ) || ! current_user_can( self::CAPABILITY ) ) {
			if ( function_exists( 'wp_die' ) ) {
				wp_die( 'You are not allowed to view WPEssential diagnostics.', 'WPEssential', [ 'response' => 403 ] );
			}
			return;
		}

		$snapshot = $this->snapshot->build();
		$json     = function_exists( 'wp_json_encode' )
			? wp_json_encode( $snapshot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT )
			: json_encode( $snapshot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
		$json     = is_string( $json ) ? $json : '{}';

		$app              = is_array( $snapshot['app'] ?? null ) ? $snapshot['app'] : [];
		$runtime          = is_array( $snapshot['runtime'] ?? null ) ? $snapshot['runtime'] : [];
		$observability    = is_array( $snapshot['observability'] ?? null ) ? $snapshot['observability'] : [];
		$context          = is_array( $snapshot['context'] ?? null ) ? $snapshot['context'] : [];
		$modules          = is_array( $snapshot['modules'] ?? null ) ? $snapshot['modules'] : [];
		$moduleInventory  = is_array( $modules['inventory'] ?? null ) ? $modules['inventory'] : [];
		$proCompatibility = is_array( $modules['pro_compatibility'] ?? null ) ? $modules['pro_compatibility'] : [];

		echo '<div class="wrap wpessential-admin-wrap wpe-ui">';
		echo Shell::topbar( self::STATUS_SLUG ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit component escapes its own output.
		$yes = static fn ( $flag ): string => ! empty( $flag ) ? 'yes' : 'no';
		$row = static fn ( string $label, string $help, string $value ): string => OptionGroup::row( $label, $help, OptionGroup::readonly( $value ) );

		$runtimeRows = [
			$row( __( 'Framework version', 'wpessential' ), __( 'Installed WPEssential framework.', 'wpessential' ), (string) ( $app['version'] ?? 'unknown' ) ),
			$row( __( 'Framework API', 'wpessential' ), __( 'Platform API version modules build against.', 'wpessential' ), (string) ( $app['platform_api'] ?? 'unknown' ) ),
			$row( __( 'Framework schema', 'wpessential' ), __( 'Database schema version.', 'wpessential' ), (string) ( $app['platform_schema'] ?? 'unknown' ) ),
			$row( __( 'WordPress', 'wpessential' ), __( 'WordPress core version.', 'wpessential' ), (string) ( $runtime['wordpress'] ?? 'unknown' ) ),
			$row( __( 'PHP', 'wpessential' ), __( 'Server PHP version.', 'wpessential' ), (string) ( $runtime['php'] ?? PHP_VERSION ) ),
			$row( __( 'Kernel booted', 'wpessential' ), __( 'The framework kernel started on this request.', 'wpessential' ), $yes( $runtime['kernel_booted'] ?? false ) ),
			$row( __( 'Debug enabled', 'wpessential' ), __( 'WP_DEBUG is on.', 'wpessential' ), $yes( $runtime['debug_enabled'] ?? false ) ),
			$row( __( 'Trace capture', 'wpessential' ), __( 'Whether request traces are recorded.', 'wpessential' ), (string) ( $runtime['trace_capture'] ?? 'disabled' ) ),
			$row( __( 'Captured traces', 'wpessential' ), __( 'Traces recorded this request.', 'wpessential' ), (string) ( (int) ( $observability['captured_trace_count'] ?? 0 ) ) ),
			$row( __( 'Visible traces', 'wpessential' ), __( 'Traces you are allowed to see.', 'wpessential' ), (string) ( (int) ( $observability['visible_trace_count'] ?? 0 ) ) ),
			$row( __( 'Site ID', 'wpessential' ), __( 'Current site.', 'wpessential' ), (string) ( (int) ( $context['site_id'] ?? 1 ) ) ),
			$row( __( 'Network ID', 'wpessential' ), __( 'Current network.', 'wpessential' ), (string) ( (int) ( $context['network_id'] ?? 1 ) ) ),
			$row( __( 'Multisite', 'wpessential' ), __( 'WordPress multisite is on.', 'wpessential' ), $yes( $context['multisite'] ?? false ) ),
			$row( __( 'About trace data', 'wpessential' ), __( 'Read before acting on these numbers.', 'wpessential' ), __( 'Trace data covers this request only. Use persistent evidence for release or incident decisions.', 'wpessential' ) ),
		];
		$proRows     = [
			$row( __( 'Pair state', 'wpessential' ), __( 'Result of the local Free / Pro check, run before Pro boots.', 'wpessential' ), (string) ( $proCompatibility['state'] ?? 'unavailable' ) ),
			$row( __( 'Dimension', 'wpessential' ), __( 'What was compared.', 'wpessential' ), (string) ( $proCompatibility['dimension'] ?? 'unavailable' ) ),
			$row( __( 'Reason', 'wpessential' ), __( 'Why this state was reached.', 'wpessential' ), (string) ( $proCompatibility['reason'] ?? 'unavailable' ) ),
			$row( __( 'Recovery', 'wpessential' ), __( 'What to do if the pair is blocked.', 'wpessential' ), (string) ( $proCompatibility['remediation'] ?? 'unavailable' ) ),
			$row( __( 'Certification', 'wpessential' ), __( 'This runtime check is not a certified version pair.', 'wpessential' ), (string) ( $proCompatibility['certification'] ?? 'adr_0010_not_certified' ) ),
		];
		$moduleRows  = [
			$row( __( 'About this list', 'wpessential' ), __( 'State of every module the kernel knows.', 'wpessential' ), __( 'Licence is checked separately from compatibility. Certified Free / Pro pairs are not claimed here.', 'wpessential' ) ),
			$this->moduleInventory( $moduleInventory ),
		];

		$groups = OptionGroup::render( 'wpe-status-runtime', __( 'Runtime', 'wpessential' ), 'System', 'status.runtime', $runtimeRows )
			. OptionGroup::render( 'wpe-status-pro', __( 'Free / Pro compatibility', 'wpessential' ), 'System', 'status.pro_compatibility', $proRows )
			. OptionGroup::render( 'wpe-status-modules', __( 'Modules', 'wpessential' ), 'System', 'status.modules', $moduleRows );

		echo '<section id="wpessential-admin-root" aria-labelledby="wpessential-admin-title" data-wpessential-surface="runtime-observatory">';
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		echo PageHeader::breadcrumb( [ [ __( 'Dashboard', 'wpessential' ), AdminUrl::page( Shell::DASHBOARD_SLUG ) ], [ __( 'System status', 'wpessential' ), null ] ] );
		echo str_replace( '<h1>', '<h1 id="wpessential-admin-title">', PageHeader::render( '', __( 'System status', 'wpessential' ), __( 'Read-only diagnostics for the shared WPEssential runtime. Nothing on this screen changes settings.', 'wpessential' ) ) );
		echo SectionNav::layout(
			__( 'Runtime observatory', 'wpessential' ),
			[
				__( 'Runtime observatory', 'wpessential' ) => [
					[ 'wpe-status-runtime', __( 'Runtime', 'wpessential' ), count( $runtimeRows ) ],
					[ 'wpe-status-pro', __( 'Free / Pro compatibility', 'wpessential' ), count( $proRows ) ],
					[ 'wpe-status-modules', __( 'Modules', 'wpessential' ), count( $moduleInventory ) ],
				],
			],
			$groups
		);
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</section>';
		echo '<script id="wpessential-admin-bootstrap" type="application/json">' . $json . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON encoded with JSON_HEX_* flags.
		echo '</div>';
	}

	/** @param array<int,mixed> $inventory */
	private function moduleInventory( array $inventory ): string {
		$html  = '<div class="wpe-ui-table-wrap"><table class="widefat striped" aria-label="' . esc_attr__( 'WPEssential module commercial inventory', 'wpessential' ) . '">';
		$html .= '<caption class="screen-reader-text">' . esc_html__( 'WPEssential module edition, package, compatibility, entitlement and runtime state.', 'wpessential' ) . '</caption><thead><tr>';
		foreach ( [ __( 'Module', 'wpessential' ), __( 'Edition', 'wpessential' ), __( 'Package', 'wpessential' ), __( 'Compatibility', 'wpessential' ), __( 'Entitlement', 'wpessential' ), __( 'Runtime state', 'wpessential' ), __( 'Reason', 'wpessential' ) ] as $heading ) {
			$html .= '<th scope="col">' . esc_html( $heading ) . '</th>';
		}
		$html .= '</tr></thead><tbody>';

		$rows = array_filter( $inventory, 'is_array' );
		if ( $rows === [] ) {
			$html .= '<tr><td colspan="7">' . esc_html__( 'No runtime-known modules are available.', 'wpessential' ) . '</td></tr>';
		}
		foreach ( $rows as $row ) {
			$html .= '<tr>';
			foreach ( [ 'module', 'edition', 'package', 'compatibility', 'entitlement', 'runtime_state', 'reason' ] as $key ) {
				$html .= '<td><code>' . esc_html( (string) ( $row[ $key ] ?? 'unknown' ) ) . '</code></td>';
			}
			$html .= '</tr>';
		}

		return $html . '</tbody></table></div>';
	}
}
