<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;
use WPEssential\Platform\Admin\Core\AdminScreen;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\Admin\Ui\AdminUrl;
use WPEssential\Platform\Admin\Ui\Badge;
use WPEssential\Platform\Admin\Ui\Button;
use WPEssential\Platform\Admin\Ui\Icon;
use WPEssential\Platform\Admin\Ui\Notice;
use WPEssential\Platform\Admin\Ui\PageHeader;
use WPEssential\Platform\Admin\Ui\Screen;
use WPEssential\Platform\Admin\Ui\Shell;
use WPEssential\Platform\Admin\Ui\StatusPill;

/**
 * License & Account screen (Figma: "License & Account", "Sign in / Create account").
 * Every action is a nonce-protected admin-post form; keys and tokens never reach the browser.
 */
final class LicenseScreen implements AdminScreen {

	public const ACTION = 'wpessential_license';
	private const OPS   = [ 'connect', 'deactivate', 'refresh', 'login', 'register', 'logout', 'use', 'checkout', 'manage' ];
	private const FLASH = 'wpessential_license_flash_';

	public function __construct( private readonly LicenseService $license, private readonly Account $account ) {}

	public function slug(): string {
		return Shell::LICENSE_SLUG;
	}

	public function title(): string {
		return __( 'Account', 'wpessential' );
	}

	public function register(): void {
		if ( function_exists( 'add_action' ) ) {
			add_action( 'admin_post_' . self::ACTION, [ $this, 'handle' ] );
		}
	}

	public function handle(): void {
		if ( ! current_user_can( PlatformAdminController::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to manage the WPEssential license.', 'wpessential' ), 'WPEssential', [ 'response' => 403 ] );
		}
		check_admin_referer( self::ACTION );

		$op     = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( (string) $_POST['op'] ) ) : '';
		$result = in_array( $op, self::OPS, true ) ? $this->dispatch( $op ) : new WP_Error( 'wpe_op', __( 'Unknown action.', 'wpessential' ) );

		if ( is_string( $result ) && str_starts_with( $result, 'http' ) ) {
			// Checkout URL; Account::checkout() only returns URLs on the licence server host.
			wp_redirect( $result ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- host-checked in Account::checkout().
			exit;
		}

		$this->flash( $result instanceof WP_Error ? [ 'error', $result->get_error_message() ] : [ 'success', (string) $result ] );
		wp_safe_redirect( AdminUrl::page( Shell::LICENSE_SLUG ) );
		exit;
	}

	private function field( string $name ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in handle().
		return isset( $_POST[ $name ] ) ? trim( sanitize_text_field( wp_unslash( (string) $_POST[ $name ] ) ) ) : '';
	}

	private function dispatch( string $op ): string|WP_Error {
		$done = static fn ( mixed $r, string $msg ): string|WP_Error => $r instanceof WP_Error ? $r : $msg;

		return match ( $op ) {
			'connect'    => $done( $this->account->connectSite(), __( 'This site is connected to your subscription.', 'wpessential' ) ),
			'deactivate' => $done( $this->license->deactivate(), __( 'This site was disconnected and its slot is free again.', 'wpessential' ) ),
			'refresh'    => $this->refreshed(),
			'login'      => $done( $this->account->login( $this->field( 'login' ), (string) wp_unslash( $_POST['password'] ?? '' ) ), __( 'Signed in. This site is connected to your subscription when a slot is available.', 'wpessential' ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- passwords are sent as typed.
			'register'   => $done( $this->account->register( $this->field( 'name' ), sanitize_email( $this->field( 'email' ) ), (string) wp_unslash( $_POST['password'] ?? '' ) ), __( 'Account created and this site connected.', 'wpessential' ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- passwords are sent as typed.
			'logout'     => $done( $this->account->logout(), __( 'Signed out.', 'wpessential' ) ),
			'use'        => $done( $this->account->useLicense( (int) $this->field( 'license' ) ), __( 'This site now uses that subscription.', 'wpessential' ) ),
			'checkout'   => $this->account->checkout( sanitize_key( $this->field( 'plan' ) ), (int) $this->field( 'license' ), sanitize_key( $this->field( 'mode' ) ) ?: 'buy' ),
			default      => $done( $this->account->licenseAction( (int) $this->field( 'license' ), sanitize_key( $this->field( 'do' ) ) ), __( 'License updated.', 'wpessential' ) ),
		};
	}

	private function refreshed(): string {
		$this->license->refresh();

		return __( 'Subscription status refreshed.', 'wpessential' );
	}

	/** @param array{0:string,1:string} $message */
	private function flash( array $message ): void {
		if ( $message[1] !== '' && function_exists( 'set_transient' ) ) {
			set_transient( self::FLASH . get_current_user_id(), $message, 60 );
		}
	}

	private function takeFlash(): string {
		if ( ! function_exists( 'get_transient' ) ) {
			return '';
		}
		$message = get_transient( self::FLASH . get_current_user_id() );
		delete_transient( self::FLASH . get_current_user_id() );

		return is_array( $message ) && count( $message ) === 2 ? Notice::render( (string) $message[0], (string) $message[1] ) : '';
	}

	/** @param array<string,string> $fields */
	private static function form( string $op, string $inner, array $fields = [], string $class = '' ): string {
		$html  = '<form method="post" class="' . esc_attr( trim( 'wpe-ui-form ' . $class ) ) . '" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		$html .= wp_nonce_field( self::ACTION, '_wpnonce', true, false );
		$html .= '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '"><input type="hidden" name="op" value="' . esc_attr( $op ) . '">';
		foreach ( $fields as $name => $value ) {
			$html .= '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
		}

		return $html . $inner . '</form>';
	}

	private static function submit( string $label, string $variant = 'primary', bool $small = false ): string {
		return '<button type="submit" class="' . esc_attr( Button::classes( $variant, $small ) ) . '">' . esc_html( $label ) . '</button>';
	}

	private static function input( string $name, string $label, string $type = 'text', string $autocomplete = '' ): string {
		$id = 'wpe-license-' . $name;

		return '<p class="wpe-ui-field"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>'
			. '<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="' . esc_attr( $type ) . '" required'
			. ( $autocomplete !== '' ? ' autocomplete="' . esc_attr( $autocomplete ) . '"' : '' ) . '></p>';
	}

	public function render(): void {
		$html  = PageHeader::render( __( 'Account', 'wpessential' ), __( 'Account & subscription', 'wpessential' ), __( 'Sign in with your wpessential.org account. This site is connected to your subscription automatically.', 'wpessential' ) );
		$html .= '<div class="wpe-ui-card-grid wpe-ui-card-grid--two">' . $this->licenseCard() . $this->accountCard() . '</div>';
		$html .= '<p class="wpe-ui-muted">' . esc_html__( 'Your account token and subscription credentials are stored in this site\'s database without encryption and are never sent to the browser.', 'wpessential' ) . '</p>';

		Screen::render( Shell::LICENSE_SLUG, $html, $this->takeFlash() );
	}

	private function licenseCard(): string {
		$data     = $this->license->publicData();
		$active   = $data['status'] === 'active';
		$signedIn = $this->account->signedIn();
		$html     = '<section class="wpe-ui-panel" aria-labelledby="wpe-license-title"><div class="wpe-ui-panel__head"><span class="wpe-ui-icon">' . Icon::render( 'monitor', 20 ) . '</span>';
		$html    .= '<h2 id="wpe-license-title">' . esc_html__( 'This site', 'wpessential' ) . '</h2>';
		$html    .= StatusPill::render( $active ? 'running' : ( $data['status'] === 'expired' ? 'waiting' : 'off' ), $active ? __( 'Connected', 'wpessential' ) : ( $data['status'] === 'expired' ? __( 'Expired', 'wpessential' ) : __( 'Not connected', 'wpessential' ) ) ) . '</div>';

		if ( $active ) {
			$limit = (int) $data['sites_limit'];
			$rows  = [
				__( 'Plan', 'wpessential' )   => esc_html( (string) $data['plan'] ) . ' ' . Badge::edition( $this->license->hasPro() ? 'pro' : 'free' ),
				__( 'Renews', 'wpessential' ) => esc_html( $data['expires'] !== '' ? (string) $data['expires'] : __( 'Never', 'wpessential' ) ),
				__( 'Sites', 'wpessential' )  => esc_html( $limit === 0 ? sprintf( /* translators: %d: sites used. */ __( '%d of unlimited', 'wpessential' ), (int) $data['sites_used'] ) : sprintf( /* translators: 1: sites used, 2: site limit. */ __( '%1$d of %2$d', 'wpessential' ), (int) $data['sites_used'], $limit ) ),
				__( 'Domain', 'wpessential' ) => esc_html( (string) $data['domain'] ),
			];
			$html .= '<dl class="wpe-ui-facts">';
			foreach ( $rows as $label => $value ) {
				$html .= '<dt>' . esc_html( $label ) . '</dt><dd>' . $value . '</dd>';
			}
			$html .= '</dl><div class="wpe-ui-actions">' . self::form( 'refresh', self::submit( __( 'Check now', 'wpessential' ), 'ghost', true ) ) . self::form( 'deactivate', self::submit( __( 'Disconnect this site', 'wpessential' ), 'secondary', true ) ) . '</div>';

			return $html . '</section>';
		}

		if ( $data['message'] !== '' ) {
			$html .= Notice::render( 'warning', (string) $data['message'] );
		}
		$html .= $signedIn
			? '<p>' . esc_html__( 'Connect this site to one of your subscriptions. WPEssential keeps the one already on this domain, or uses a free site slot.', 'wpessential' ) . '</p>' . self::form( 'connect', self::submit( __( 'Connect this site', 'wpessential' ) ) )
			: '<p>' . esc_html__( 'Sign in with your WPEssential account. This site is connected to your subscription automatically, no key needed.', 'wpessential' ) . '</p>';

		return $html . '<p class="wpe-ui-muted">' . esc_html__( 'Free modules work without an account. A Premium subscription unlocks every Premium module.', 'wpessential' ) . '</p></section>';
	}

	private function accountCard(): string {
		$html  = '<section class="wpe-ui-panel" aria-labelledby="wpe-account-title"><div class="wpe-ui-panel__head"><span class="wpe-ui-icon">' . Icon::render( 'user', 20 ) . '</span>';
		$html .= '<h2 id="wpe-account-title">' . esc_html__( 'WPEssential account', 'wpessential' ) . '</h2></div>';

		$me = $this->account->signedIn() ? $this->account->me() : [ 'signed_in' => false ];
		if ( $me instanceof WP_Error ) {
			return $html . Notice::render( 'error', $me->get_error_message() ) . self::form( 'logout', self::submit( __( 'Sign out', 'wpessential' ), 'ghost', true ) ) . '</section>';
		}

		if ( empty( $me['signed_in'] ) ) {
			$html .= '<div class="wpe-ui-card-grid wpe-ui-card-grid--two">';
			$html .= self::form( 'login', '<h3>' . esc_html__( 'Sign in', 'wpessential' ) . '</h3>' . self::input( 'login', __( 'Email or username', 'wpessential' ), 'text', 'username' ) . self::input( 'password', __( 'Password', 'wpessential' ), 'password', 'current-password' ) . self::submit( __( 'Sign in', 'wpessential' ) ) );
			$html .= self::form( 'register', '<h3>' . esc_html__( 'Create account', 'wpessential' ) . '</h3>' . self::input( 'name', __( 'Name', 'wpessential' ), 'text', 'name' ) . self::input( 'email', __( 'Email', 'wpessential' ), 'email', 'email' ) . self::input( 'password', __( 'Password', 'wpessential' ), 'password', 'new-password' ) . self::submit( __( 'Create free account', 'wpessential' ), 'secondary' ) );

			return $html . '</div></section>';
		}

		$user  = is_array( $me['user'] ?? null ) ? $me['user'] : [];
		$html .= '<p>' . esc_html( (string) ( $user['email'] ?? $user['name'] ?? '' ) ) . '</p>';

		$licenses = is_array( $me['licenses'] ?? null ) ? $me['licenses'] : [];
		if ( $licenses !== [] ) {
			$html .= '<ul class="wpe-ui-list">';
			foreach ( $licenses as $item ) {
				$id    = (string) (int) ( $item['id'] ?? 0 );
				$renew = ! empty( $item['auto_renew'] );
				$html .= '<li><code>' . esc_html( (string) ( $item['mask'] ?? '' ) ) . '</code> ' . esc_html( (string) ( $item['plan'] ?? '' ) ) . ' ';
				$html .= ! empty( $item['here'] ) ? StatusPill::render( 'success', __( 'This site', 'wpessential' ) ) : self::form( 'use', self::submit( __( 'Use here', 'wpessential' ), 'secondary', true ), [ 'license' => $id ], 'wpe-ui-inline' );
				$html .= self::form(
					'manage',
					self::submit( $renew ? __( 'Turn off auto-renew', 'wpessential' ) : __( 'Turn on auto-renew', 'wpessential' ), 'ghost', true ),
					[
						'license' => $id,
						'do'      => $renew ? 'auto_renew_off' : 'auto_renew_on',
					],
					'wpe-ui-inline'
				);
				$html .= self::form(
					'checkout',
					self::submit( __( 'Renew', 'wpessential' ), 'ghost', true ),
					[
						'license' => $id,
						'plan'    => (string) ( $item['plan_key'] ?? '' ),
						'mode'    => 'renew',
					],
					'wpe-ui-inline'
				) . '</li>';
			}
			$html .= '</ul>';
		}

		$plans = is_array( $me['plans'] ?? null ) ? $me['plans'] : [];
		if ( $plans !== [] ) {
			$html .= '<h3>' . esc_html__( 'Plans', 'wpessential' ) . '</h3><div class="wpe-ui-actions">';
			foreach ( $plans as $plan ) {
				$label = trim( (string) ( $plan['name'] ?? $plan['key'] ?? '' ) . ' ' . (string) ( $plan['price'] ?? '' ) );
				$html .= self::form( 'checkout', self::submit( $label, 'primary', true ), [ 'plan' => (string) ( $plan['key'] ?? '' ) ], 'wpe-ui-inline' );
			}
			$html .= '</div>';
		}

		return $html . self::form( 'logout', self::submit( __( 'Sign out', 'wpessential' ), 'ghost', true ) ) . '</section>';
	}
}
