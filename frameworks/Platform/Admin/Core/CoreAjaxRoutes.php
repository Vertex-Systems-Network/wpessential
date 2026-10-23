<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\Connect\AiConnect;
use WPEssential\Platform\Admin\Ui\Shell;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AbilityAjaxHandler;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;
use WPEssential\Platform\WordPress\Ajax\AjaxRoute;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Security\NonceOperation;

/**
 * AJAX routes for the toolkit's in-place controls (module, element and AI switches, colour mode).
 * The admin-post forms stay as the no-JS fallback; ui.ts upgrades them to these routes.
 */
final class CoreAjaxRoutes {

	public const ROUTES = [
		'core.module.toggle'  => 'wpessential/framework-modules/toggle',
		'core.element.toggle' => 'wpessential/framework-elements/toggle',
		'core.ui.mode'        => 'wpessential/framework-settings/update',
	];
	public const AI_SWITCH = 'core.ai.switch';

	public function __construct(
		private readonly AjaxRouteRegistry $routes,
		private readonly AjaxDispatcher $dispatcher,
		private readonly AbilityRegistry $abilities,
		private readonly WordPressExecutionContextFactory $contexts,
		private readonly AiConnect $aiConnect,
		private readonly string $ajaxAction,
	) {}

	public function register(): void {
		foreach ( self::ROUTES as $type => $ability ) {
			$this->routes->register( new AjaxRoute( $type, new AbilityAjaxHandler( $this->abilities, $ability, $this->contexts ), NonceOperation::Update ) );
		}
		$this->routes->register( new AjaxRoute( self::AI_SWITCH, new AiSwitchAjaxHandler( $this->aiConnect ), NonceOperation::Update, 'manage_options' ) );
		Shell::configureAjax( $this );
	}

	/** @return array{url:string,action:string,nonces:array<string,string>} Bootstrap for ui.ts; nonces are per user. */
	public function bootstrap(): array {
		$nonces = [];
		foreach ( array_merge( array_keys( self::ROUTES ), [ self::AI_SWITCH ] ) as $type ) {
			$nonces[ $type ] = $this->dispatcher->createNonce( $type );
		}

		return [
			'url'    => function_exists( 'admin_url' ) ? (string) admin_url( 'admin-ajax.php' ) : 'admin-ajax.php',
			'action' => $this->ajaxAction,
			'nonces' => $nonces,
		];
	}
}
