<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Kernel\Kernel;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\Admin\Ui\AdminUrl;
use WPEssential\Platform\Admin\Ui\Badge;
use WPEssential\Platform\Admin\Ui\Button;
use WPEssential\Platform\Admin\Ui\Notice;
use WPEssential\Platform\Admin\Ui\PageHeader;
use WPEssential\Platform\Admin\Ui\Shell;
use WPEssential\Platform\Admin\Ui\StatTile;
use WPEssential\Platform\Admin\Ui\StatusPill;
use WPEssential\Platform\Admin\Ui\Toggle;
use WPEssential\Platform\Modules\ModuleState;

/**
 * WPEssential admin: dashboard, module manager (Free + Premium on/off) and one overview
 * page per module. Built only from the `Platform\Admin\Ui` toolkit components.
 */
final class CoreAdminController {

	public const MODULE_PREFIX = 'wpessential-module-';
	public const TOGGLE_ACTION = 'wpessential_module_toggle';

	public function __construct(
		private readonly Kernel $kernel,
		private readonly ModuleToggleStore $toggles,
		private readonly OptionContractReader $contracts,
		private readonly AdminAssetManifest $assets,
	) {}

	public function register(): void {
		if ( ! function_exists( 'add_action' ) ) {
			return;
		}

		add_action( 'admin_menu', [ $this, 'registerMenu' ], 9 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
		add_action( 'admin_post_' . self::TOGGLE_ACTION, [ $this, 'handleToggle' ] );
	}

	public function registerMenu(): void {
		if ( ! function_exists( 'add_menu_page' ) || ! function_exists( 'add_submenu_page' ) ) {
			return;
		}

		$parent = PlatformAdminController::PAGE_SLUG;
		$cap    = PlatformAdminController::CAPABILITY;

		add_menu_page( 'WPEssential', 'WPEssential', $cap, $parent, [ $this, 'renderDashboard' ], self::menuIcon(), 58 );
		add_submenu_page( $parent, __( 'Dashboard', 'wpessential' ), __( 'Dashboard', 'wpessential' ), $cap, $parent, [ $this, 'renderDashboard' ] );
		add_submenu_page( $parent, __( 'Modules', 'wpessential' ), __( 'Modules', 'wpessential' ), $cap, Shell::MODULES_SLUG, [ $this, 'renderModules' ] );

		// Module overview pages stay out of the sidebar; they are reached from the Modules screen.
		foreach ( ModuleCatalog::all() as $module ) {
			add_submenu_page( 'options.php', $module['name'], $module['name'], $cap, self::MODULE_PREFIX . $module['id'], [ $this, 'renderModule' ] );
		}
	}

	/** Loads the toolkit stylesheet on every WPEssential admin screen. */
	public function enqueueAssets(): void {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- asset routing only.
		if ( ! str_starts_with( $page, 'wpessential' ) ) {
			return;
		}

		$entry = $this->assets->entry( 'ui' );
		if ( $entry === null ) {
			return;
		}

		$version = $entry['version'] ?? ( defined( 'WPE_VERSION' ) ? (string) WPE_VERSION : null );
		foreach ( $entry['styles'] as $index => $style ) {
			wp_enqueue_style( 'wpessential-ui-' . $index, $style, [ 'dashicons' ], $version );
		}
		wp_enqueue_script( 'wpessential-ui', $entry['script'], $entry['dependencies'], $version, true );
	}

	public function handleToggle(): void {
		if ( ! current_user_can( PlatformAdminController::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to manage WPEssential modules.', 'wpessential' ), 'WPEssential', [ 'response' => 403 ] );
		}
		check_admin_referer( self::TOGGLE_ACTION );

		$moduleId = isset( $_POST['module'] ) ? sanitize_key( wp_unslash( (string) $_POST['module'] ) ) : '';
		$enabled  = isset( $_POST['enabled'] ) && $_POST['enabled'] === '1';
		$return   = isset( $_POST['return'] ) ? sanitize_key( wp_unslash( (string) $_POST['return'] ) ) : Shell::MODULES_SLUG;
		$module   = ModuleCatalog::find( $moduleId );
		$notice   = 'invalid';

		if ( $module !== null ) {
			if ( $enabled && $this->isLocked( $module ) ) {
				$notice = 'locked';
			} else {
				$this->toggles->set( $moduleId, $enabled );
				$notice = $enabled ? 'enabled' : 'disabled';
			}
		}

		wp_safe_redirect(
			AdminUrl::page(
				str_starts_with( $return, 'wpessential' ) ? $return : Shell::MODULES_SLUG,
				[
					'wpe-notice' => $notice,
					'wpe-module' => $moduleId,
				]
			)
		);
		exit;
	}

	public function renderDashboard(): void {
		$this->requireCapability();

		$modules = ModuleCatalog::all();
		$active  = array_filter( $modules, fn ( array $m ): bool => $this->isActive( $m ) );
		$free    = count( array_filter( $modules, static fn ( array $m ): bool => $m['edition'] === 'free' ) );
		$options = 0;
		foreach ( $modules as $module ) {
			$options += (int) ( $this->contracts->read( $module['contract'] )['coverage']['atomic_options'] ?? 0 );
		}

		$html  = '<section class="wpe-ui-hero"><div>';
		$html .= '<p class="wpe-ui-eyebrow">' . esc_html__( 'WPEssential Framework', 'wpessential' ) . '</p>';
		$html .= '<h1>' . esc_html__( 'Everything your WordPress site needs, in one place.', 'wpessential' ) . '</h1>';
		$html .= '<p>' . esc_html__( 'Switch modules on or off, configure every system and keep your site lean. Free modules are always included; Premium unlocks the full platform.', 'wpessential' ) . '</p>';
		$html .= '<div class="wpe-ui-hero__actions">' . Button::link( __( 'Manage modules', 'wpessential' ), AdminUrl::page( Shell::MODULES_SLUG ) ) . Button::link( __( 'System status', 'wpessential' ), AdminUrl::page( Shell::STATUS_SLUG ), 'ghost' ) . '</div></div>';
		$html .= '<div class="wpe-ui-hero__stats">';
		$html .= StatTile::render( (string) count( $active ), __( 'Active modules', 'wpessential' ) );
		$html .= StatTile::render( (string) count( $modules ), __( 'Total modules', 'wpessential' ) );
		$html .= StatTile::render( (string) $free, __( 'Free modules', 'wpessential' ) );
		$html .= StatTile::render( (string) ( count( $modules ) - $free ), __( 'Premium modules', 'wpessential' ) );
		$html .= StatTile::render( number_format( $options ), __( 'Configurable options', 'wpessential' ) );
		$html .= StatTile::render( (string) count( ModuleCatalog::categories() ), __( 'Categories', 'wpessential' ) );
		$html .= '</div></section>';

		$html .= '<div class="wpe-ui-columns"><section class="wpe-ui-panel"><div class="wpe-ui-panel__head"><h2>' . esc_html__( 'Active modules', 'wpessential' ) . '</h2>';
		$html .= '<a href="' . esc_url( AdminUrl::page( Shell::MODULES_SLUG ) ) . '">' . esc_html__( 'View all', 'wpessential' ) . ' &rarr;</a></div>';
		if ( $active === [] ) {
			$html .= '<p class="wpe-ui-muted">' . esc_html__( 'No modules are active yet. Turn one on from the Modules screen.', 'wpessential' ) . '</p>';
		} else {
			$html .= '<div class="wpe-ui-quick-grid">';
			foreach ( $active as $module ) {
				$html .= '<a class="wpe-ui-quick" href="' . esc_url( AdminUrl::page( self::MODULE_PREFIX . $module['id'] ) ) . '">';
				$html .= $this->icon( $module ) . '<span class="wpe-ui-quick__name">' . esc_html( $module['name'] ) . '</span>' . $this->statusPill( $module ) . '</a>';
			}
			$html .= '</div>';
		}
		$html .= '</section><aside>';
		if ( ! Shell::premiumAvailable() ) {
			$html .= $this->upsell();
		}
		$html .= '<section class="wpe-ui-panel"><h2>' . esc_html__( 'Categories', 'wpessential' ) . '</h2><ul class="wpe-ui-list">';
		foreach ( ModuleCatalog::categories() as $key => $label ) {
			$count = count( array_filter( $modules, static fn ( array $m ): bool => $m['category'] === $key ) );
			$html .= '<li><a href="' . esc_url( AdminUrl::page( Shell::MODULES_SLUG, [ 'category' => $key ] ) ) . '">' . esc_html( $label ) . '</a>' . Badge::count( $count ) . '</li>';
		}
		$html .= '</ul></section></aside></div>';

		$this->output( Shell::DASHBOARD_SLUG, $html );
	}

	public function renderModules(): void {
		$this->requireCapability();

		$modules = ModuleCatalog::all();
		$free    = count( array_filter( $modules, static fn ( array $m ): bool => $m['edition'] === 'free' ) );
		$active  = count( array_filter( $modules, fn ( array $m ): bool => $this->isActive( $m ) ) );
		$counts  = '<span class="wpe-ui-badge wpe-ui-badge--free">' . esc_html( sprintf( /* translators: %d: count. */ __( '%d Free', 'wpessential' ), $free ) ) . '</span> '
			. '<span class="wpe-ui-badge wpe-ui-badge--pro">' . esc_html( sprintf( /* translators: %d: count. */ __( '%d Premium', 'wpessential' ), count( $modules ) - $free ) ) . '</span> '
			. Badge::count( $active );

		$html     = PageHeader::render( __( 'Modules', 'wpessential' ), __( 'Free + Premium modules', 'wpessential' ), __( 'Turn modules on or off. Disabled modules load no code on your site.', 'wpessential' ), $counts );
		$selected = isset( $_GET['category'] ) ? sanitize_key( wp_unslash( (string) $_GET['category'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter.

		$html .= '<div class="wpe-ui-toolbar"><label class="screen-reader-text" for="wpe-module-search">' . esc_html__( 'Search modules', 'wpessential' ) . '</label>';
		$html .= '<input type="search" id="wpe-module-search" class="wpe-ui-input" placeholder="' . esc_attr__( 'Search modules…', 'wpessential' ) . '" data-wpe-search>';
		$html .= '<div class="wpe-ui-tabs" role="group" aria-label="' . esc_attr__( 'Filter by edition', 'wpessential' ) . '">';
		foreach ( [
			'all'      => __( 'All', 'wpessential' ),
			'free'     => __( 'Free', 'wpessential' ),
			'pro'      => __( 'Premium', 'wpessential' ),
			'active'   => __( 'Active', 'wpessential' ),
			'inactive' => __( 'Inactive', 'wpessential' ),
		] as $value => $label ) {
			$html .= '<button type="button" class="wpe-ui-tab" aria-pressed="' . ( $value === 'all' ? 'true' : 'false' ) . '" data-wpe-edition="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</button>';
		}
		$html .= '</div><select class="wpe-ui-select" data-wpe-category aria-label="' . esc_attr__( 'Filter by category', 'wpessential' ) . '"><option value="">' . esc_html__( 'All categories', 'wpessential' ) . '</option>';
		foreach ( ModuleCatalog::categories() as $key => $label ) {
			$html .= '<option value="' . esc_attr( $key ) . '"' . ( $selected === $key ? ' selected' : '' ) . '>' . esc_html( $label ) . '</option>';
		}
		$html .= '</select></div>';

		if ( ! Shell::premiumAvailable() ) {
			$html .= $this->upsell();
		}

		foreach ( ModuleCatalog::categories() as $key => $label ) {
			$html .= '<section data-wpe-category-group><h2 class="wpe-ui-category">' . esc_html( $label ) . '</h2><div class="wpe-ui-module-grid">';
			foreach ( $modules as $module ) {
				if ( $module['category'] === $key ) {
					$html .= $this->moduleCard( $module );
				}
			}
			$html .= '</div></section>';
		}
		$html .= '<div class="wpe-ui-empty" data-wpe-no-results hidden><h2>' . esc_html__( 'No modules match your filters', 'wpessential' ) . '</h2><p>' . esc_html__( 'Try another keyword or clear the filters.', 'wpessential' ) . '</p></div>';

		$this->output( Shell::MODULES_SLUG, $html );
	}

	public function renderModule(): void {
		$this->requireCapability();

		$page   = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- routing only.
		$module = ModuleCatalog::find( (string) substr( $page, strlen( self::MODULE_PREFIX ) ) );
		if ( $module === null ) {
			return;
		}

		$categories = ModuleCatalog::categories();
		$html       = PageHeader::breadcrumb(
			[
				[ __( 'Modules', 'wpessential' ), AdminUrl::page( Shell::MODULES_SLUG ) ],
				[ $categories[ $module['category'] ] ?? '', AdminUrl::page( Shell::MODULES_SLUG, [ 'category' => $module['category'] ] ) ],
				[ $module['name'], null ],
			]
		);

		$html .= '<div class="wpe-ui-module-head">' . str_replace( 'wpe-ui-icon', 'wpe-ui-icon wpe-ui-icon--lg', $this->icon( $module ) );
		$html .= '<div class="wpe-ui-module-head__copy"><h1>' . esc_html( $module['name'] ) . ' ' . Badge::edition( $module['edition'] ) . '</h1>';
		$html .= '<p class="wpe-ui-muted">' . esc_html( $module['description'] ) . '</p>';
		$html .= '<p class="wpe-ui-muted">' . esc_html__( 'Status:', 'wpessential' ) . ' ' . $this->statusPill( $module ) . '</p></div>';
		$html .= '<div class="wpe-ui-module-head__actions">';
		if ( $module['builder'] !== null && $this->isActive( $module ) ) {
			$html .= Button::link( __( 'Open builder', 'wpessential' ), AdminUrl::page( $module['builder'] ), 'secondary' );
		}
		$html .= $this->toggle( $module, $page ) . '</div></div>';

		if ( $this->isLocked( $module ) ) {
			$html .= $this->upsell();
		}

		$contract = $this->contracts->read( $module['contract'] );
		if ( $contract === null || $contract['groups'] === [] ) {
			$html .= '<div class="wpe-ui-empty"><h2>' . esc_html__( 'No options yet', 'wpessential' ) . '</h2></div>';
			$this->output( $page, $html );
			return;
		}

		$tiers = array_fill_keys( OptionContractReader::TIERS, 0 );
		$total = 0;
		foreach ( $contract['groups'] as $group ) {
			foreach ( $group['options'] as $option ) {
				++$tiers[ $option['tier'] ];
				++$total;
			}
		}

		$html .= StatTile::row(
			[
				[ (string) $total, __( 'Options', 'wpessential' ) ],
				[ (string) count( $contract['groups'] ), __( 'Option groups', 'wpessential' ) ],
				[ (string) ( $contract['coverage']['parity'] ?? 0 ), __( 'Market parity', 'wpessential' ) ],
				[ (string) ( $contract['coverage']['exceeds'] ?? 0 ), __( 'Exclusive to WPEssential', 'wpessential' ) ],
			]
		);

		$html .= '<div class="wpe-ui-toolbar"><div class="wpe-ui-tabs" role="tablist" aria-label="' . esc_attr__( 'Option level', 'wpessential' ) . '">';
		$html .= '<button type="button" role="tab" class="wpe-ui-tab" aria-selected="true" data-wpe-tier="">' . esc_html__( 'All', 'wpessential' ) . ' ' . Badge::count( $total ) . '</button>';
		foreach ( $tiers as $tier => $count ) {
			if ( $count > 0 ) {
				$html .= '<button type="button" role="tab" class="wpe-ui-tab" aria-selected="false" data-wpe-tier="' . esc_attr( $tier ) . '">' . esc_html( $tier ) . ' ' . Badge::count( $count ) . '</button>';
			}
		}
		$html .= '</div><label class="screen-reader-text" for="wpe-option-search">' . esc_html__( 'Search options', 'wpessential' ) . '</label>';
		$html .= '<input type="search" id="wpe-option-search" class="wpe-ui-input" placeholder="' . esc_attr( sprintf( /* translators: %d: option count. */ __( 'Search %d options…', 'wpessential' ), $total ) ) . '" data-wpe-option-search></div>';

		foreach ( $contract['groups'] as $index => $group ) {
			$html .= '<details class="wpe-ui-group" data-wpe-group' . ( $index < 2 ? ' open' : '' ) . '><summary><span class="wpe-ui-group__title">' . esc_html( $group['label'] ) . '</span>' . Badge::count( count( $group['options'] ) ) . '</summary>';
			foreach ( $group['options'] as $option ) {
				$html .= '<div class="wpe-ui-option" data-wpe-option data-tier="' . esc_attr( $option['tier'] ) . '" data-search="' . esc_attr( strtolower( $option['label'] . ' ' . $option['id'] ) ) . '">';
				$html .= '<div class="wpe-ui-option__copy"><span class="wpe-ui-option__label">' . esc_html( $option['label'] ) . '</span><code class="wpe-ui-code">' . esc_html( $option['id'] ) . '</code></div>';
				$html .= '<div class="wpe-ui-option__meta"><span class="wpe-ui-chip wpe-ui-chip--' . esc_attr( strtolower( $option['tier'] ) ) . '">' . esc_html( $option['tier'] ) . '</span>';
				$html .= '<span class="wpe-ui-chip">' . esc_html( $option['control'] ) . '</span>';
				$html .= $option['exclusive'] ? '<span class="wpe-ui-chip wpe-ui-chip--accent">' . esc_html__( 'Exclusive', 'wpessential' ) . '</span>' : '';
				$html .= '</div></div>';
			}
			$html .= '</details>';
		}
		$html .= '<div class="wpe-ui-empty" data-wpe-no-options hidden><h2>' . esc_html__( 'No options match your search', 'wpessential' ) . '</h2></div>';

		$this->output( $page, $html );
	}

	/** @param array{id:string,name:string,edition:string,category:string,icon:string,runtime:bool,description:string,contract:string} $module */
	private function moduleCard( array $module ): string {
		$active   = $this->isActive( $module );
		$contract = $this->contracts->read( $module['contract'] );
		$options  = (int) ( $contract['coverage']['atomic_options'] ?? 0 );

		$html  = '<article class="wpe-ui-module' . ( $active ? '' : ' is-off' ) . '" data-wpe-module data-edition="' . esc_attr( $module['edition'] ) . '" data-category="' . esc_attr( $module['category'] ) . '" data-active="' . ( $active ? '1' : '0' ) . '" data-search="' . esc_attr( strtolower( $module['name'] . ' ' . $module['description'] ) ) . '">';
		$html .= '<div class="wpe-ui-module__head">' . $this->icon( $module ) . Badge::edition( $module['edition'] ) . '</div>';
		$html .= '<h3 class="wpe-ui-module__name">' . esc_html( $module['name'] ) . '</h3>';
		$html .= '<p class="wpe-ui-module__desc">' . esc_html( $module['description'] ) . '</p>';
		$html .= '<p class="wpe-ui-module__meta">' . esc_html( sprintf( /* translators: %d: option count. */ _n( '%d option', '%d options', $options, 'wpessential' ), $options ) ) . '</p>';
		$html .= '<div class="wpe-ui-module__foot"><a href="' . esc_url( AdminUrl::page( self::MODULE_PREFIX . $module['id'] ) ) . '">' . esc_html__( 'Settings', 'wpessential' ) . ' &rarr;</a>';

		return $html . $this->toggle( $module, Shell::MODULES_SLUG ) . '</div></article>';
	}

	/** @param array{id:string,name:string,edition:string} $module */
	private function toggle( array $module, string $return ): string {
		if ( $this->isLocked( $module ) ) {
			return Toggle::locked( Shell::UPGRADE_URL );
		}

		return Toggle::form(
			self::TOGGLE_ACTION,
			self::TOGGLE_ACTION,
			$this->toggles->isEnabled( $module['id'] ),
			/* translators: %s: module name. */
			sprintf( __( 'Turn %s on or off', 'wpessential' ), $module['name'] ),
			[
				'module' => $module['id'],
				'return' => $return,
			]
		);
	}

	/** @param array{id:string,edition:string,runtime:bool} $module */
	private function statusPill( array $module ): string {
		if ( $this->isLocked( $module ) ) {
			return StatusPill::render( 'locked', __( 'Requires Premium', 'wpessential' ) );
		}
		if ( ! $this->toggles->isEnabled( $module['id'] ) ) {
			return StatusPill::render( 'off', __( 'Off', 'wpessential' ) );
		}
		if ( ! $module['runtime'] ) {
			return StatusPill::render( 'soon', __( 'Coming soon', 'wpessential' ) );
		}

		return $this->kernel->modules()->state( $module['id'] ) === ModuleState::Degraded
			? StatusPill::render( 'waiting', __( 'Waiting on a dependency', 'wpessential' ) )
			: StatusPill::render( 'running', __( 'Running', 'wpessential' ) );
	}

	/** @param array{edition:string,icon:string} $module */
	private function icon( array $module ): string {
		return '<span class="wpe-ui-icon' . ( $module['edition'] === 'pro' ? ' wpe-ui-icon--pro' : '' ) . '"><span class="dashicons ' . esc_attr( $module['icon'] ) . '" aria-hidden="true"></span></span>';
	}

	private function upsell(): string {
		return '<section class="wpe-ui-upsell"><p class="wpe-ui-eyebrow">' . esc_html__( 'WPEssential Premium', 'wpessential' ) . '</p>'
			. '<h2>' . esc_html__( 'Unlock every module', 'wpessential' ) . '</h2>'
			. '<p>' . esc_html__( 'Custom fields, relations, listings, membership, backups and more — one licence, all Premium modules.', 'wpessential' ) . '</p>'
			. Button::link( __( 'Get Premium', 'wpessential' ), Shell::UPGRADE_URL, 'primary', false, true ) . '</section>';
	}

	private function output( string $current, string $content ): void {
		echo '<div class="wrap wpe-ui" data-wpessential-surface="core">';
		echo Shell::topbar( $current ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		echo $this->notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by Notice::render().
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built only from escaping toolkit components.
		echo '</div>';
	}

	private function notice(): string {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- display-only redirect notice.
		$notice = isset( $_GET['wpe-notice'] ) ? sanitize_key( wp_unslash( (string) $_GET['wpe-notice'] ) ) : '';
		$module = ModuleCatalog::find( isset( $_GET['wpe-module'] ) ? sanitize_key( wp_unslash( (string) $_GET['wpe-module'] ) ) : '' );
		// phpcs:enable
		if ( $module === null ) {
			return '';
		}

		return match ( $notice ) {
			/* translators: %s: module name. */
			'enabled'  => Notice::render( 'success', sprintf( __( '%s is now on. It loads on the next request.', 'wpessential' ), $module['name'] ) ),
			/* translators: %s: module name. */
			'disabled' => Notice::render( 'info', sprintf( __( '%s is now off.', 'wpessential' ), $module['name'] ) ),
			/* translators: %s: module name. */
			'locked'   => Notice::render( 'warning', sprintf( __( '%s needs WPEssential Premium.', 'wpessential' ), $module['name'] ) ),
			default    => '',
		};
	}

	/** @param array{id:string,edition:string} $module */
	private function isLocked( array $module ): bool {
		return $module['edition'] === 'pro' && ! Shell::premiumAvailable();
	}

	/** @param array{id:string,edition:string} $module */
	private function isActive( array $module ): bool {
		return ! $this->isLocked( $module ) && $this->toggles->isEnabled( $module['id'] );
	}

	private function requireCapability(): void {
		if ( ! current_user_can( PlatformAdminController::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to manage WPEssential.', 'wpessential' ), 'WPEssential', [ 'response' => 403 ] );
		}
	}

	private static function menuIcon(): string {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><text x="1" y="16" font-family="Arial,sans-serif" font-weight="800" font-size="16" fill="#a7aaad">W</text><circle cx="17" cy="15" r="2" fill="#ec7d1a"/></svg>';

		return 'data:image/svg+xml;base64,' . base64_encode( $svg ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- WordPress menu icon data URI.
	}
}
