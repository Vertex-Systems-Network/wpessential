<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\Core\ModuleToggleStore;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\WordPress\Ajax\AjaxRoute;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Security\NonceOperation;

/**
 * Site Kit (Premium): site-wide colours, fonts, buttons, forms, layout, dark mode, motion, custom
 * code and SEO basics for any theme, in Gutenberg and Elementor (ADR-0232).
 */
final class SiteKitModule implements ModuleInterface {

	public const ID = 'site-kit';

	public function manifest(): ModuleManifest {
		return new ModuleManifest( id: self::ID, name: 'Site Kit', version: '0.1.0', edition: 'pro' );
	}

	public function register( ServiceRegistryInterface $services ): void {
		if ( ! ( new ModuleToggleStore() )->isEnabled( self::ID ) ) {
			return;
		}
		$store    = new SiteKitStore();
		$snippets = new SiteKitSnippets();

		$abilities = $services->has( 'platform.abilities' ) ? $services->get( 'platform.abilities' ) : null;
		if ( $abilities instanceof AbilityRegistry ) {
			( new SiteKitAbilities( $store ) )->register( $abilities );
		}
		$routes = $services->has( 'platform.ajax.routes' ) ? $services->get( 'platform.ajax.routes' ) : null;
		if ( $routes instanceof AjaxRouteRegistry ) {
			foreach ( [ SiteKitAdmin::AJAX_SAVE, SiteKitAdmin::AJAX_RESET, SiteKitAdmin::AJAX_SNIPPETS ] as $type ) {
				$routes->register( new AjaxRoute( $type, new SiteKitAjaxHandler( $type, $store, $snippets ), NonceOperation::Update, SiteKitAbilities::CAPABILITY ) );
			}
		}
		if ( ! function_exists( 'add_action' ) || ! function_exists( 'add_filter' ) ) {
			return;
		}
		( new SiteKitFrontend( $store ) )->register();
		$snippets->register();
		( new SiteKitSeo( $store ) )->register();
		SiteKitElementor::register();
		( new SiteKitAdmin( $store, $snippets ) )->register();
	}

	public function boot( ServiceRegistryInterface $services ): void {}
}
