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
use WPEssential\Platform\Admin\Ui\Button;
use WPEssential\Platform\Admin\Ui\Icon;
use WPEssential\Platform\Admin\Ui\Notice;
use WPEssential\Platform\Admin\Ui\PageHeader;
use WPEssential\Platform\Admin\Ui\Screen;
use WPEssential\Platform\Admin\Ui\Shell;
use WPEssential\Platform\Admin\Ui\StatusPill;
use WPEssential\Platform\Admin\Ui\Toggle;

/**
 * AI Connect screen (Figma: "AI Connect"). Connects MCP clients to WPEssential's own MCP server.
 */
final class AiConnectScreen implements AdminScreen {

	public const ACTION = 'wpessential_ai_connect';
	private const ONCE  = 'wpessential_ai_once_';

	public function __construct( private readonly AiConnect $connect ) {}

	public function slug(): string {
		return Shell::AI_SLUG;
	}

	public function title(): string {
		return __( 'AI Connect', 'wpessential' );
	}

	public function register(): void {
		if ( function_exists( 'add_action' ) ) {
			add_action( 'admin_post_' . self::ACTION, [ $this, 'handle' ] );
		}
	}

	public function handle(): void {
		if ( ! current_user_can( PlatformAdminController::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to manage AI Connect.', 'wpessential' ), 'WPEssential', [ 'response' => 403 ] );
		}
		check_admin_referer( self::ACTION );

		$op      = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( (string) $_POST['op'] ) ) : '';
		$enabled = isset( $_POST['enabled'] ) && $_POST['enabled'] === '1';
		$user    = get_current_user_id();
		$notice  = 'saved';

		switch ( $op ) {
			case 'power':
				$this->connect->set( 'enabled', $enabled );
				if ( ! $enabled ) {
					$this->connect->revokeAll( $user );
				}
				$notice = $enabled ? 'on' : 'off';
				break;
			case 'writes':
				$this->connect->set( 'allow_writes', $enabled && $this->connect->enabled() );
				break;
			case 'connect':
				$client  = isset( $_POST['client'] ) ? sanitize_key( wp_unslash( (string) $_POST['client'] ) ) : 'generic';
				$client  = isset( AiConnect::CLIENTS[ $client ] ) ? $client : 'generic';
				$created = $this->connect->connect( $user, $client );
				if ( $created instanceof WP_Error ) {
					$notice = 'insecure';
				} else {
					set_transient( self::ONCE . $user, [ $client, $created['password'] ], 300 );
					$notice = 'connected';
				}
				break;
			case 'revoke':
				$uuid   = isset( $_POST['session'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['session'] ) ) : '';
				$notice = $this->connect->revoke( $user, $uuid ) ? 'revoked' : 'invalid';
				break;
			default:
				$notice = 'invalid';
		}

		wp_safe_redirect( AdminUrl::page( Shell::AI_SLUG, [ 'wpe-notice' => $notice ] ) );
		exit;
	}

	/** @param array<string,string> $fields */
	private static function form( string $op, string $inner, array $fields = [] ): string {
		$html  = '<form method="post" class="wpe-ui-form wpe-ui-inline" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		$html .= wp_nonce_field( self::ACTION, '_wpnonce', true, false );
		$html .= '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '"><input type="hidden" name="op" value="' . esc_attr( $op ) . '">';
		foreach ( $fields as $name => $value ) {
			$html .= '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
		}

		return $html . $inner . '</form>';
	}

	public function render(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice routing.
		$notice   = isset( $_GET['wpe-notice'] ) ? sanitize_key( wp_unslash( (string) $_GET['wpe-notice'] ) ) : '';
		$settings = $this->connect->settings();
		$user     = get_current_user_id();

		$html  = PageHeader::render( __( 'AI Connect', 'wpessential' ), __( 'Connect your AI assistant', 'wpessential' ), __( 'Let Claude, Cursor, VS Code and other MCP clients read and, if you allow it, change WPEssential settings on this site.', 'wpessential' ) );
		$html .= '<section class="wpe-ui-panel wpe-ui-panel--flush" aria-label="' . esc_attr__( 'AI Connect switches', 'wpessential' ) . '">';
		$html .= self::row( 'spark', __( 'AI Connect', 'wpessential' ), __( 'Turns on the WPEssential MCP server. Turning it off disconnects every client.', 'wpessential' ), Toggle::form( self::ACTION, self::ACTION, $settings['enabled'], __( 'Turn AI Connect on or off', 'wpessential' ), [ 'op' => 'power' ] ) );
		$writes = $settings['enabled']
			? Toggle::form( self::ACTION, self::ACTION, $settings['allow_writes'], __( 'Allow AI to change settings', 'wpessential' ), [ 'op' => 'writes' ] )
			: StatusPill::render( 'neutral', __( 'Off', 'wpessential' ) );
		$html  .= self::row( 'cog', __( 'Allow AI to change settings', 'wpessential' ), __( 'Off: AI can only read. On: AI can switch modules and elements on or off and change your settings. Every call is audited.', 'wpessential' ), $writes );
		$html  .= '</section>';

		if ( $settings['enabled'] ) {
			$html .= $this->oneTime( $user ) . $this->connectPanel() . $this->sessionsPanel( $user );
		}

		$messages = [
			'on'        => [ 'success', __( 'AI Connect is on. Connect a client below.', 'wpessential' ) ],
			'off'       => [ 'info', __( 'AI Connect is off and every client was disconnected.', 'wpessential' ) ],
			'saved'     => [ 'success', __( 'Saved.', 'wpessential' ) ],
			'connected' => [ 'success', __( 'Client connection created. Copy it now; it is shown only once.', 'wpessential' ) ],
			'revoked'   => [ 'info', __( 'Client disconnected.', 'wpessential' ) ],
			'insecure'  => [ 'error', __( 'AI Connect needs HTTPS and WordPress Application Passwords on this site.', 'wpessential' ) ],
			'invalid'   => [ 'error', __( 'That action is not available.', 'wpessential' ) ],
		];

		Screen::render( Shell::AI_SLUG, $html, isset( $messages[ $notice ] ) ? Notice::render( ...$messages[ $notice ] ) : '' );
	}

	private static function row( string $icon, string $label, string $help, string $control ): string {
		return '<div class="wpe-ui-option"><div class="wpe-ui-option__copy wpe-ui-option__copy--row"><span class="wpe-ui-icon">' . Icon::render( $icon, 20 ) . '</span>'
			. '<span><span class="wpe-ui-option__label">' . esc_html( $label ) . '</span><br><span class="wpe-ui-muted">' . esc_html( $help ) . '</span></span></div>'
			. '<div class="wpe-ui-option__meta">' . $control . '</div></div>';
	}

	private function oneTime( int $user ): string {
		$once = get_transient( self::ONCE . $user );
		delete_transient( self::ONCE . $user );
		if ( ! is_array( $once ) || count( $once ) !== 2 ) {
			return '';
		}
		$login = function_exists( 'wp_get_current_user' ) ? (string) wp_get_current_user()->user_login : '';
		$code  = AiConnect::snippet( (string) $once[0], $login, (string) $once[1] );

		return '<section class="wpe-ui-panel" aria-labelledby="wpe-ai-once"><div class="wpe-ui-panel__head"><span class="wpe-ui-icon">' . Icon::render( 'code', 20 ) . '</span>'
			. '<h2 id="wpe-ai-once">' . esc_html( AiConnect::CLIENTS[ (string) $once[0] ] ?? '' ) . '</h2>' . StatusPill::render( 'warning', __( 'Shown once', 'wpessential' ) ) . '</div>'
			. '<pre class="wpe-ui-code" tabindex="0"><code>' . esc_html( $code ) . '</code></pre>'
			. '<p class="wpe-ui-muted">' . esc_html__( 'This contains a password for your account. Paste it into your client and do not share it.', 'wpessential' ) . '</p></section>';
	}

	private function connectPanel(): string {
		$options = '';
		foreach ( AiConnect::CLIENTS as $key => $label ) {
			$options .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</option>';
		}
		$inner = '<p class="wpe-ui-field"><label for="wpe-ai-client">' . esc_html__( 'Client', 'wpessential' ) . '</label><select id="wpe-ai-client" name="client">' . $options . '</select></p>'
			. '<button type="submit" class="' . esc_attr( Button::classes( 'primary' ) ) . '">' . esc_html__( 'Create connection', 'wpessential' ) . '</button>';

		return '<section class="wpe-ui-panel" aria-labelledby="wpe-ai-connect"><div class="wpe-ui-panel__head"><span class="wpe-ui-icon">' . Icon::render( 'plug', 20 ) . '</span>'
			. '<h2 id="wpe-ai-connect">' . esc_html__( 'Connect a client', 'wpessential' ) . '</h2></div>'
			. '<p>' . esc_html__( 'Server URL', 'wpessential' ) . ': <code>' . esc_html( AiConnect::endpoint() ) . '</code></p>'
			. ( AiConnect::secure() ? self::form( 'connect', $inner ) : Notice::render( 'warning', __( 'AI Connect needs HTTPS and WordPress Application Passwords on this site.', 'wpessential' ) ) )
			. '</section>';
	}

	private function sessionsPanel( int $user ): string {
		$sessions = $this->connect->sessions( $user );
		$html     = '<section class="wpe-ui-panel" aria-labelledby="wpe-ai-sessions"><div class="wpe-ui-panel__head"><span class="wpe-ui-icon">' . Icon::render( 'users', 20 ) . '</span>'
			. '<h2 id="wpe-ai-sessions">' . esc_html__( 'Connected clients', 'wpessential' ) . '</h2></div>';
		if ( $sessions === [] ) {
			return $html . '<p class="wpe-ui-muted">' . esc_html__( 'No clients are connected.', 'wpessential' ) . '</p></section>';
		}
		$html .= '<ul class="wpe-ui-list">';
		foreach ( $sessions as $session ) {
			$used  = $session['last_used'] > 0 ? wp_date( (string) get_option( 'date_format' ), $session['last_used'] ) : __( 'Never used', 'wpessential' );
			$html .= '<li><span><strong>' . esc_html( $session['client'] ) . '</strong> <span class="wpe-ui-muted">' . esc_html( (string) $used ) . '</span></span>'
				. self::form( 'revoke', '<button type="submit" class="' . esc_attr( Button::classes( 'ghost', true ) ) . '">' . esc_html__( 'Disconnect', 'wpessential' ) . '</button>', [ 'session' => $session['uuid'] ] ) . '</li>';
		}

		return $html . '</ul></section>';
	}
}
