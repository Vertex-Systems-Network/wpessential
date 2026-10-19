<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;

/**
 * ADR-0228-B: the Custom Tables migration screen.
 *
 * The page is the only place that mints nonces for the custom-tables.migration.* admin-AJAX routes.
 */
final class CustomTablesAdminController {

	public const PAGE_SLUG  = 'wpessential-custom-tables';
	public const CAPABILITY = CustomTablesModule::CAPABILITY;
	public const SURFACE    = 'custom-tables';

	private ?string $hookSuffix = null;

	public function __construct(
		private readonly AjaxDispatcher $ajax,
		private readonly AdminAssetManifest $assets,
		private readonly DefinitionRepositoryInterface $definitions,
		private readonly string $ajaxAction,
	) {
		if ( trim( $this->ajaxAction ) === '' ) {
			throw new InvalidArgumentException( 'Custom Tables admin requires the WPEssential AJAX action.' );
		}
	}

	public function register(): void {
		if ( ! function_exists( 'add_action' ) ) {
			return;
		}

		add_action( 'admin_menu', [ $this, 'registerMenu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
	}

	public function registerMenu(): void {
		if ( ! function_exists( 'add_submenu_page' ) ) {
			return;
		}

		$hook = add_submenu_page(
			PlatformAdminController::PAGE_SLUG,
			__( 'Custom Tables', 'wpessential' ),
			__( 'Custom Tables', 'wpessential' ),
			self::CAPABILITY,
			self::PAGE_SLUG,
			[ $this, 'render' ],
		);

		$this->hookSuffix = is_string( $hook ) && $hook !== '' ? $hook : null;
	}

	public function enqueueAssets( string $hookSuffix ): void {
		if ( $this->hookSuffix === null || $hookSuffix !== $this->hookSuffix ) {
			return;
		}

		$entry = $this->assets->entry( 'custom-tables' );
		if ( $entry === null ) {
			return;
		}

		$version = $entry['version'] ?? ( defined( 'WPE_VERSION' ) ? (string) WPE_VERSION : null );
		foreach ( $entry['styles'] as $index => $style ) {
			wp_enqueue_style( 'wpessential-custom-tables-admin-' . $index, $style, [], $version );
		}
		wp_enqueue_script(
			'wpessential-custom-tables-admin',
			$entry['script'],
			$entry['dependencies'],
			$version,
			true,
		);
		if ( function_exists( 'wp_script_add_data' ) ) {
			wp_script_add_data( 'wpessential-custom-tables-admin', 'strategy', 'defer' );
		}
	}

	/**
	 * Bootstrap payload for the TypeScript client: one nonce per migration route, and the table Definitions.
	 *
	 * @return array<string,mixed>
	 */
	public function bootstrap( string $ajaxUrl ): array {
		$routes = [];
		foreach ( CustomTablesMigrationAbilityHandler::ACTIONS as $action ) {
			$type             = CustomTablesModule::routeType( $action );
			$routes[ $action ] = [
				'type'  => $type,
				'nonce' => $this->ajax->createNonce( $type ),
			];
		}

		$tables = [];
		foreach ( $this->definitions->byType( TableDefinitionCompiler::TYPE ) as $definition ) {
			if ( ! $definition instanceof Definition || $definition->ownerSurfaceId !== TableDefinitionCompiler::OWNER_SURFACE_ID ) {
				continue;
			}
			$label    = $definition->payload['label'] ?? $definition->slug;
			$tables[] = [
				'definition_id' => strtolower( $definition->id ),
				'key'           => $definition->slug,
				'label'         => is_string( $label ) && $label !== '' ? $label : $definition->slug,
				'revision'      => $definition->revision,
			];
		}

		return [
			'surface'    => self::SURFACE,
			'ajaxUrl'    => $ajaxUrl,
			'ajaxAction' => $this->ajaxAction,
			'routes'     => $routes,
			'tables'     => $tables,
		];
	}

	public function render(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to manage Custom Tables migrations.', 'wpessential' ), '', [ 'response' => 403 ] );
		}

		$flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
		$data  = $this->bootstrap( admin_url( 'admin-ajax.php' ) );
		$json  = function_exists( 'wp_json_encode' ) ? wp_json_encode( $data, $flags ) : json_encode( $data, $flags );
		if ( ! is_string( $json ) ) {
			$json = '{}';
		}

		echo '<div class="wrap wpessential-custom-tables-wrap">';
		echo '<section id="wpessential-custom-tables-root" aria-labelledby="wpessential-custom-tables-title">';
		echo '<h1 id="wpessential-custom-tables-title">' . esc_html__( 'Custom Tables', 'wpessential' ) . '</h1>';
		echo '<p class="description">' . esc_html__( 'Review and apply additive schema migrations. Every step is recorded in the audit log, and execution requires a verified database backup bound to the reviewed plan.', 'wpessential' ) . '</p>';
		echo '<div id="wpessential-custom-tables-notice" class="notice" role="status" aria-live="polite" hidden><p></p></div>';
		echo '<div id="wpessential-custom-tables-app"></div>';
		echo '<noscript><p>' . esc_html__( 'The Custom Tables migration screen requires JavaScript.', 'wpessential' ) . '</p></noscript>';
		echo '</section>';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON encoded with JSON_HEX_* flags for a non-executable script element.
		echo '<script id="wpessential-custom-tables-bootstrap" type="application/json">' . $json . '</script>';
		echo '</div>';
	}
}
