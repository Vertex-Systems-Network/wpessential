<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Platform\Admin\Core\AdminScreen;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\Admin\Ui\AdminUrl;
use WPEssential\Platform\Admin\Ui\Attributes;
use WPEssential\Platform\Admin\Ui\Badge;
use WPEssential\Platform\Admin\Ui\Button;
use WPEssential\Platform\Admin\Ui\Notice;
use WPEssential\Platform\Admin\Ui\OptionGroup;
use WPEssential\Platform\Admin\Ui\PageHeader;
use WPEssential\Platform\Admin\Ui\Screen;
use WPEssential\Platform\Admin\Ui\SectionNav;
use WPEssential\Platform\Admin\Ui\Shell;
use WPEssential\Platform\Admin\Ui\StatusPill;
use WP_Error;

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
		return Button::button( $label, $variant, [ 'type' => 'submit' ], $small );
	}

	/** Input for an option row: the row shows the visible label, so this one is for screen readers. */
	private static function input( string $form, string $name, string $label, string $type = 'text', string $autocomplete = '' ): string {
		$id = 'wpe-license-' . $form . '-' . $name;

		return '<label class="screen-reader-text" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>'
			. '<input' . Attributes::render(
				[
					'type'         => $type,
					'id'           => $id,
					'name'         => $name,
					'required'     => true,
					'autocomplete' => $autocomplete !== '' ? $autocomplete : null,
				]
			) . '>';
	}

	public function render(): void {
		$html  = PageHeader::render( __( 'Account', 'wpessential' ), __( 'Account & subscription', 'wpessential' ), __( 'Sign in with your wpessential.org account. This site is connected to your subscription automatically.', 'wpessential' ) );
		$site  = $this->siteRows();
		$nav   = [ [ 'wpe-license-site', __( 'This site', 'wpessential' ), count( $site ) ] ];
		$body  = OptionGroup::render( 'wpe-license-site', __( 'This site', 'wpessential' ), 'Essential', 'license.site', $site );
		foreach ( $this->accountGroups() as $group ) {
			[ $id, $title, $key, $rows ] = $group;
			$count = $group[4] ?? count( $rows );
			$body .= OptionGroup::render( $id, $title, 'Essential', $key, $rows, true, $count );
			$nav[] = [ $id, $title, $count ];
		}
		$html .= SectionNav::layout( __( 'Account', 'wpessential' ), [ __( 'Account', 'wpessential' ) => $nav ], $body );
		$html .= '<p class="wpe-ui-muted">' . esc_html__( 'Your account token and subscription credentials are stored in this site\'s database without encryption and are never sent to the browser.', 'wpessential' ) . '</p>';

		Screen::render( Shell::LICENSE_SLUG, $html, $this->takeFlash() );
	}

	/** @return list<string> */
	private function siteRows(): array {
		$data   = $this->license->publicData();
		$active = $data['status'] === 'active';
		$rows   = [
			OptionGroup::row(
				__( 'Status', 'wpessential' ),
				__( 'Whether this site is linked to a subscription.', 'wpessential' ),
				StatusPill::render( $active ? 'running' : ( $data['status'] === 'expired' ? 'waiting' : 'off' ), $active ? __( 'Connected', 'wpessential' ) : ( $data['status'] === 'expired' ? __( 'Expired', 'wpessential' ) : __( 'Not connected', 'wpessential' ) ) )
			),
		];

		if ( $active ) {
			$limit = (int) $data['sites_limit'];
			$sites = $limit === 0 ? sprintf( /* translators: %d: sites used. */ __( '%d of unlimited', 'wpessential' ), (int) $data['sites_used'] ) : sprintf( /* translators: 1: sites used, 2: site limit. */ __( '%1$d of %2$d', 'wpessential' ), (int) $data['sites_used'], $limit );

			return array_merge(
				$rows,
				[
					OptionGroup::row( __( 'Plan', 'wpessential' ), __( 'Edition unlocked on this site.', 'wpessential' ), esc_html( (string) $data['plan'] ) . ' ' . Badge::edition( $this->license->hasPro() ? 'pro' : 'free' ) ),
					OptionGroup::row( __( 'Renews', 'wpessential' ), __( 'Next renewal date.', 'wpessential' ), OptionGroup::readonly( $data['expires'] !== '' ? (string) $data['expires'] : __( 'Never', 'wpessential' ) ) ),
					OptionGroup::row( __( 'Sites', 'wpessential' ), __( 'Slots used on this subscription.', 'wpessential' ), OptionGroup::readonly( $sites ) ),
					OptionGroup::row( __( 'Domain', 'wpessential' ), __( 'The domain this slot is tied to.', 'wpessential' ), OptionGroup::readonly( (string) $data['domain'] ) ),
					OptionGroup::row( __( 'Manage', 'wpessential' ), __( 'Refresh the status, or free this slot for another site.', 'wpessential' ), self::form( 'refresh', self::submit( __( 'Check now', 'wpessential' ), 'ghost', true ), [], 'wpe-ui-inline' ) . self::form( 'deactivate', self::submit( __( 'Disconnect this site', 'wpessential' ), 'secondary', true ), [], 'wpe-ui-inline' ) ),
				]
			);
		}

		if ( $data['message'] !== '' ) {
			$rows[] = OptionGroup::row( __( 'Message', 'wpessential' ), '', Notice::render( 'warning', (string) $data['message'] ) );
		}
		$rows[] = $this->account->signedIn()
			? OptionGroup::row( __( 'Connect this site', 'wpessential' ), __( 'Connect this site to one of your subscriptions. WPEssential keeps the one already on this domain, or uses a free site slot.', 'wpessential' ), self::form( 'connect', self::submit( __( 'Connect this site', 'wpessential' ) ), [], 'wpe-ui-inline' ) )
			: OptionGroup::row( __( 'How it works', 'wpessential' ), __( 'No licence key needed.', 'wpessential' ), OptionGroup::readonly( __( 'Sign in with your WPEssential account. This site is connected to your subscription automatically, no key needed.', 'wpessential' ) ) );
		$rows[] = OptionGroup::row( __( 'Free modules', 'wpessential' ), __( 'What works without an account.', 'wpessential' ), OptionGroup::readonly( __( 'Free modules work without an account. A Premium subscription unlocks every Premium module.', 'wpessential' ) ) );

		return $rows;
	}

	/** @return list<array{0:string,1:string,2:string,3:list<string>,4?:int}> [ id, title, contract key, rows, visible row count when a form wraps them ] */
	private function accountGroups(): array {
		$logout = self::form( 'logout', self::submit( __( 'Sign out', 'wpessential' ), 'ghost', true ), [], 'wpe-ui-inline' );
		$me     = $this->account->signedIn() ? $this->account->me() : [ 'signed_in' => false ];
		if ( $me instanceof WP_Error ) {
			return [ [ 'wpe-license-account', __( 'WPEssential account', 'wpessential' ), 'license.account', [ OptionGroup::row( __( 'Account', 'wpessential' ), '', Notice::render( 'error', $me->get_error_message() ) ), OptionGroup::row( __( 'Sign out', 'wpessential' ), '', $logout ) ] ] ];
		}

		if ( empty( $me['signed_in'] ) ) {
			$login    = self::form(
				'login',
				OptionGroup::row( __( 'Email or username', 'wpessential' ), __( 'Your wpessential.org account.', 'wpessential' ), self::input( 'login', 'login', __( 'Email or username', 'wpessential' ), 'text', 'username' ) )
				. OptionGroup::row( __( 'Password', 'wpessential' ), __( 'Stored only as a token on this site after sign-in.', 'wpessential' ), self::input( 'login', 'password', __( 'Password', 'wpessential' ), 'password', 'current-password' ) )
				. OptionGroup::row( __( 'Sign in', 'wpessential' ), __( 'Connects this site when a slot is free.', 'wpessential' ), self::submit( __( 'Sign in', 'wpessential' ) ) )
			);
			$register = self::form(
				'register',
				OptionGroup::row( __( 'Name', 'wpessential' ), __( 'Shown on your account and invoices.', 'wpessential' ), self::input( 'register', 'name', __( 'Name', 'wpessential' ), 'text', 'name' ) )
				. OptionGroup::row( __( 'Email', 'wpessential' ), __( 'Used to sign in and for receipts.', 'wpessential' ), self::input( 'register', 'email', __( 'Email', 'wpessential' ), 'email', 'email' ) )
				. OptionGroup::row( __( 'Password', 'wpessential' ), __( 'For your new account.', 'wpessential' ), self::input( 'register', 'password', __( 'Password', 'wpessential' ), 'password', 'new-password' ) )
				. OptionGroup::row( __( 'Create free account', 'wpessential' ), __( 'Creates the account and connects this site.', 'wpessential' ), self::submit( __( 'Create free account', 'wpessential' ) ) )
			);

			return [
				[ 'wpe-license-sign-in', __( 'Sign in', 'wpessential' ), 'license.account.sign_in', [ $login ], 3 ],
				[ 'wpe-license-create', __( 'Create account', 'wpessential' ), 'license.account.create', [ $register ], 4 ],
			];
		}

		$groups   = [];
		$licenses = is_array( $me['licenses'] ?? null ) ? $me['licenses'] : [];
		if ( $licenses !== [] ) {
			$rows = [];
			foreach ( $licenses as $item ) {
				$id     = (string) (int) ( $item['id'] ?? 0 );
				$renew  = ! empty( $item['auto_renew'] );
				$here   = ! empty( $item['here'] )
					? StatusPill::render( 'running', __( 'This site', 'wpessential' ) )
					: self::form( 'use', self::submit( __( 'Use here', 'wpessential' ), 'secondary', true ), [ 'license' => $id ], 'wpe-ui-inline' );
				$manage = self::form(
					'manage',
					self::submit( $renew ? __( 'Turn off auto-renew', 'wpessential' ) : __( 'Turn on auto-renew', 'wpessential' ), 'ghost', true ),
					[
						'license' => $id,
						'do'      => $renew ? 'auto_renew_off' : 'auto_renew_on',
					],
					'wpe-ui-inline'
				);
				$checkout = self::form(
					'checkout',
					self::submit( __( 'Renew', 'wpessential' ), 'ghost', true ),
					[
						'license' => $id,
						'plan'    => (string) ( $item['plan_key'] ?? '' ),
						'mode'    => 'renew',
					],
					'wpe-ui-inline'
				);
				$rows[]   = OptionGroup::row( (string) ( $item['plan'] ?? '' ), (string) ( $item['mask'] ?? '' ), $here . $manage . $checkout );
			}
			$groups[] = [ 'wpe-license-subscriptions', __( 'Subscriptions', 'wpessential' ), 'license.account.subscriptions', $rows ];
		}

		$plans = is_array( $me['plans'] ?? null ) ? $me['plans'] : [];
		if ( $plans !== [] ) {
			$rows = [];
			foreach ( $plans as $plan ) {
				$rows[] = OptionGroup::row( (string) ( $plan['name'] ?? $plan['key'] ?? '' ), (string) ( $plan['price'] ?? '' ), self::form( 'checkout', self::submit( __( 'Choose', 'wpessential' ), 'primary', true ), [ 'plan' => (string) ( $plan['key'] ?? '' ) ], 'wpe-ui-inline' ) );
			}
			$groups[] = [ 'wpe-license-plans', __( 'Plans', 'wpessential' ), 'license.account.plans', $rows ];
		}

		$user     = is_array( $me['user'] ?? null ) ? $me['user'] : [];
		$groups[] = [
			'wpe-license-account',
			__( 'WPEssential account', 'wpessential' ),
			'license.account',
			[
				OptionGroup::row( __( 'Signed in as', 'wpessential' ), __( 'Your wpessential.org account.', 'wpessential' ), OptionGroup::readonly( (string) ( $user['email'] ?? $user['name'] ?? '' ) ) ),
				OptionGroup::row( __( 'Sign out', 'wpessential' ), __( 'Removes the token from this site; connected sites stay connected.', 'wpessential' ), $logout ),
			],
		];

		return $groups;
	}
}
