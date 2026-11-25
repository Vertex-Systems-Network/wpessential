<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;
use WPEssential\Platform\Admin\Core\AdminScreen;
use WPEssential\Platform\Admin\Core\CoreAdminController;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\Admin\Ui\AdminUrl;
use WPEssential\Platform\Admin\Ui\Button;
use WPEssential\Platform\Admin\Ui\Icon;
use WPEssential\Platform\Admin\Ui\Notice;
use WPEssential\Platform\Admin\Ui\OptionGroup;
use WPEssential\Platform\Admin\Ui\PageHeader;
use WPEssential\Platform\Admin\Ui\Screen;
use WPEssential\Platform\Admin\Ui\SectionNav;
use WPEssential\Platform\Admin\Ui\Shell;
use WPEssential\Platform\Admin\Ui\StatusPill;
use WPEssential\Platform\Admin\Ui\Toggle;

/**
 * AI Connect screen (Figma: "AI Connect"). Connects MCP clients to WPEssential's own MCP server.
 */
final class AiConnectScreen implements AdminScreen {

	public const ACTION = 'wpessential_ai_connect';
	private const ONCE  = 'wpessential_ai_once_';

	public function __construct( private readonly AiConnect $connect, private readonly ?AiActivityLog $log = null ) {}

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

		if ( $op === 'download' ) {
			$this->download();
		}

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
					set_transient( self::ONCE . $user, [ $client, $created['password'], $created['uuid'] ], 300 );
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
	private static function form( string $op, string $inner, array $fields = [], string $class = 'wpe-ui-form wpe-ui-inline' ): string {
		$html  = '<form method="post" class="' . esc_attr( $class ) . '" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		$html .= wp_nonce_field( self::ACTION, '_wpnonce', true, false );
		$html .= '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '"><input type="hidden" name="op" value="' . esc_attr( $op ) . '">';
		foreach ( $fields as $name => $value ) {
			$html .= '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
		}

		return $html . $inner . '</form>';
	}

	public function render(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice routing.
		$notice = isset( $_GET['wpe-notice'] ) ? sanitize_key( wp_unslash( (string) $_GET['wpe-notice'] ) ) : '';
		$user   = get_current_user_id();

		$html = PageHeader::render( __( 'AI Connect', 'wpessential' ), __( 'Connect your AI assistant', 'wpessential' ), __( 'Let Claude, Cursor, VS Code and other MCP clients read and, if you allow it, change WPEssential settings on this site.', 'wpessential' ) );

		if ( ! $this->connect->available() ) {
			Screen::render( Shell::AI_SLUG, $html . $this->unavailable() );
			return;
		}

		$settings = [
			'enabled'      => $this->connect->enabled(),
			'allow_writes' => $this->connect->allowWrites(),
		];
		$writes   = $settings['enabled']
			? Toggle::form( self::ACTION, self::ACTION, $settings['allow_writes'], __( 'Allow AI to change settings', 'wpessential' ), [ 'op' => 'writes' ] )
			: StatusPill::render( 'off', __( 'Off', 'wpessential' ) );
		$switches = [
			OptionGroup::row( __( 'AI Connect', 'wpessential' ), __( 'Turns on the WPEssential MCP server. Turning it off disconnects every client.', 'wpessential' ), Toggle::form( self::ACTION, self::ACTION, $settings['enabled'], __( 'Turn AI Connect on or off', 'wpessential' ), [ 'op' => 'power' ] ) ),
			OptionGroup::row( __( 'Allow AI to change settings', 'wpessential' ), __( 'Off: AI can only read. On: AI can switch modules and elements on or off and change your settings. Every call is audited.', 'wpessential' ), $writes ),
		];
		$groups   = OptionGroup::render( 'wpe-ai-switches', __( 'Switches', 'wpessential' ), 'Essential', 'ai.switches', $switches );
		$nav      = [ [ 'wpe-ai-switches', __( 'Switches', 'wpessential' ), count( $switches ) ] ];

		if ( $settings['enabled'] ) {
			$connect  = $this->connectRows( $user );
			$sessions = $this->sessionRows( $user );
			$groups  .= OptionGroup::render( 'wpe-ai-connect', __( 'Connect a client', 'wpessential' ), 'Essential', 'ai.connect', $connect )
				. OptionGroup::render( 'wpe-ai-sessions', __( 'Connected clients', 'wpessential' ), 'Essential', 'ai.sessions', $sessions );
			$nav[]    = [ 'wpe-ai-connect', __( 'Connect a client', 'wpessential' ), count( $connect ) ];
			$nav[]    = [ 'wpe-ai-sessions', __( 'Connected clients', 'wpessential' ), count( $sessions ) ];
		}
		if ( $this->log !== null && $this->log->available() ) {
			$events  = $this->log->recent();
			$groups .= OptionGroup::render( 'wpe-ai-log', __( 'Activity log', 'wpessential' ), 'Essential', 'ai.log', [ $this->activity( $events ) ], true, count( $events ) );
			$nav[]   = [ 'wpe-ai-log', __( 'Activity log', 'wpessential' ), count( $events ) ];
		}
		$html .= SectionNav::layout( __( 'AI Connect', 'wpessential' ), [ __( 'AI Connect', 'wpessential' ) => $nav ], $groups );

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

	/** AI Connect is free; this shows only while the module is switched off in Modules. */
	private function unavailable(): string {
		$locked = false;
		$html   = '<div class="wpe-ui-empty wpe-ui-empty--dashed"><span class="wpe-ui-icon wpe-ui-icon--lg">' . Icon::render( $locked ? 'lock' : 'spark', 26 ) . '</span>';
		$html  .= '<h2>' . esc_html( $locked ? __( 'AI Connect is a Premium module', 'wpessential' ) : __( 'AI Connect is switched off', 'wpessential' ) ) . '</h2>';
		$html  .= '<p>' . esc_html( $locked ? __( 'Activate a Premium subscription to connect AI assistants to WPEssential.', 'wpessential' ) : __( 'Switch the AI Connect module on to use the WPEssential MCP server. While it is off, no AI client can connect.', 'wpessential' ) ) . '</p>';
		$html  .= $locked
			? Button::link( __( 'Go Premium', 'wpessential' ), Shell::UPGRADE_URL, 'primary', false, true )
			: Toggle::form(
				CoreAdminController::TOGGLE_ACTION,
				CoreAdminController::TOGGLE_ACTION,
				false,
				__( 'Turn AI Connect on or off', 'wpessential' ),
				[
					'module' => 'ai-connect',
					'return' => Shell::AI_SLUG,
					'reload' => '1',
				]
			);

		return $html . '</div>';
	}

	/** One-time connection details (shown once after "Create connection"), then the client form. @return list<string> */
	private function connectRows( int $user ): array {
		$rows = [];
		$once = get_transient( self::ONCE . $user );
		delete_transient( self::ONCE . $user );
		if ( is_array( $once ) && count( $once ) >= 2 ) {
			$client = (string) $once[0];
			if ( in_array( $client, AiConnect::LINK_CLIENTS, true ) && isset( $once[2] ) ) {
				$code = AiConnect::link( $user, (string) $once[2], (string) $once[1] );
				$help = $client === 'chatgpt'
					? __( 'In ChatGPT (paid plan, web): Settings → Apps & Connectors → Advanced → turn on Developer mode, then create a connector: paste this link as the MCP server URL and choose "No authentication". The link contains a secret: do not share it.', 'wpessential' )
					: __( 'In Claude (claude.ai or Claude Desktop): Customize (or Settings) → Connectors → Add custom connector: give it a name, paste this link as the remote MCP server URL and leave the OAuth fields empty. The link contains a secret: do not share it.', 'wpessential' );
			} else {
				$login = function_exists( 'wp_get_current_user' ) ? (string) wp_get_current_user()->user_login : '';
				$code  = AiConnect::snippet( $client, $login, (string) $once[1] );
				$help  = $client === 'claude-desktop'
					? __( 'Claude Desktop: Settings → Developer → Edit Config (claude_desktop_config.json), paste this, then restart Claude. Needs Node.js. This contains a password: do not share it.', 'wpessential' )
					: __( 'This contains a password for your account. Paste it into your client and do not share it.', 'wpessential' );
			}
			$rows[] = OptionGroup::row(
				AiConnect::CLIENTS[ $client ] ?? __( 'Connection details', 'wpessential' ),
				$help,
				StatusPill::render( 'waiting', __( 'Shown once', 'wpessential' ) ) . '<pre class="wpe-ui-code" tabindex="0"><code>' . esc_html( $code ) . '</code></pre>'
			);
		}

		$rows[] = OptionGroup::row(
			__( 'Claude and ChatGPT (recommended)', 'wpessential' ),
			__( 'No password to copy. Claude: Customize (or Settings) → Connectors → Add custom connector, paste this URL, leave the OAuth fields empty, then Connect. ChatGPT (paid plan, web): Settings → Apps & Connectors → Advanced → Developer mode, create a connector with this URL and OAuth. Then click Allow on this site.', 'wpessential' ),
			OptionGroup::readonly( AiConnect::endpoint() )
		);
		if ( ! AiConnect::secure() ) {
			$rows[] = OptionGroup::row( __( 'HTTPS and app passwords', 'wpessential' ), '', Notice::render( 'warning', __( 'AI Connect needs HTTPS and WordPress Application Passwords on this site.', 'wpessential' ) ) );
			return $rows;
		}

		$options = '';
		foreach ( AiConnect::CLIENTS as $key => $label ) {
			$options .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</option>';
		}
		$rows[] = self::form(
			'connect',
			OptionGroup::row( __( 'Client', 'wpessential' ), __( 'For clients that need a password or a secret link (Claude Desktop config, Cursor, VS Code, Gemini CLI), or if OAuth is blocked on your host.', 'wpessential' ), '<label class="screen-reader-text" for="wpe-ai-client">' . esc_html__( 'Client', 'wpessential' ) . '</label><select id="wpe-ai-client" name="client">' . $options . '</select>' )
			. OptionGroup::row( __( 'Create connection', 'wpessential' ), __( 'Makes an application password for this client.', 'wpessential' ), '<button type="submit" class="' . esc_attr( Button::classes( 'primary' ) ) . '">' . esc_html__( 'Create connection', 'wpessential' ) . '</button>' ),
			[],
			'wpe-ui-form'
		);

		return $rows;
	}

	/** @return list<string> */
	private function sessionRows( int $user ): array {
		$sessions = $this->connect->sessions( $user );
		if ( $sessions === [] ) {
			return [ OptionGroup::row( __( 'No clients are connected.', 'wpessential' ), __( 'Create a connection above to add one.', 'wpessential' ), '' ) ];
		}
		$rows = [];
		foreach ( $sessions as $session ) {
			$used   = $session['last_used'] > 0 ? wp_date( (string) get_option( 'date_format' ), $session['last_used'] ) : __( 'Never used', 'wpessential' );
			$rows[] = OptionGroup::row(
				$session['client'],
				(string) $used,
				StatusPill::render( $session['last_used'] > 0 ? 'running' : 'off', $session['last_used'] > 0 ? __( 'Connected', 'wpessential' ) : __( 'Never used', 'wpessential' ) )
				. self::form( 'revoke', '<button type="submit" class="' . esc_attr( Button::classes( 'ghost', true ) ) . '">' . esc_html__( 'Disconnect', 'wpessential' ) . '</button>', [ 'session' => $session['uuid'] ] )
			);
		}

		return $rows;
	}

	/** Sends the AI activity log as a CSV download and stops. */
	private function download(): void {
		$csv = $this->log !== null ? $this->log->csv() : '';
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="wpessential-ai-activity-' . gmdate( 'Y-m-d' ) . '.csv"' );
		echo $csv; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSV download, cells neutralised by AiActivityLog::csv().
		exit;
	}

	/** @param list<array{id:string,time:string,tool:string,outcome:string,user_id:int,user:string,client:string,reason:string,details:array<string|int,mixed>}> $events */
	private function activity( array $events ): string {
		$intro = '<p class="wpe-ui-muted">' . esc_html(
			sprintf(
				/* translators: %d: number of events shown. */
				__( 'Every tool call an AI client made on this site, newest first. The latest %d are shown here; the download has up to 5,000.', 'wpessential' ),
				AiActivityLog::PAGE_SIZE
			)
		) . '</p>';
		$download = self::form( 'download', '<button type="submit" class="' . esc_attr( Button::classes( 'secondary', true ) ) . '">' . Icon::render( 'download', 14 ) . ' ' . esc_html__( 'Download CSV', 'wpessential' ) . '</button>' );
		$html     = '<div class="wpe-ui-option"><div class="wpe-ui-option__copy">' . $intro . '</div><div class="wpe-ui-option__meta">' . $download . '</div></div>';

		if ( $events === [] ) {
			return $html . OptionGroup::row( __( 'No AI activity yet.', 'wpessential' ), __( 'Calls appear here once a connected client uses a tool.', 'wpessential' ), '' );
		}

		$tones = [
			'success' => [ 'running', __( 'Success', 'wpessential' ) ],
			'denied'  => [ 'locked', __( 'Denied', 'wpessential' ) ],
			'failed'  => [ 'waiting', __( 'Failed', 'wpessential' ) ],
		];
		$html .= '<div class="wpe-ui-table-wrap"><table class="widefat striped" aria-label="' . esc_attr__( 'AI activity log', 'wpessential' ) . '"><thead><tr>';
		foreach ( [ __( 'Time', 'wpessential' ), __( 'Tool', 'wpessential' ), __( 'User', 'wpessential' ), __( 'Client', 'wpessential' ), __( 'Result', 'wpessential' ), __( 'Details', 'wpessential' ) ] as $heading ) {
			$html .= '<th scope="col">' . esc_html( $heading ) . '</th>';
		}
		$html .= '</tr></thead><tbody>';
		foreach ( $events as $event ) {
			$time    = strtotime( $event['time'] . ' UTC' );
			$when    = $time !== false ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $time ) : $event['time'];
			$tone    = $tones[ $event['outcome'] ] ?? [ 'off', $event['outcome'] ];
			$details = [
				__( 'Event ID', 'wpessential' )   => $event['id'],
				__( 'Time (UTC)', 'wpessential' ) => $event['time'],
				__( 'Reason', 'wpessential' )     => $event['reason'] !== '' ? $event['reason'] : '—',
				__( 'Data', 'wpessential' )       => (string) wp_json_encode( $event['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
			];
			$panel   = '';
			foreach ( $details as $label => $value ) {
				$panel .= '<dt>' . esc_html( (string) $label ) . '</dt><dd>' . ( $label === __( 'Data', 'wpessential' ) ? '<pre class="wpe-ui-code">' . esc_html( $value ) . '</pre>' : esc_html( $value ) ) . '</dd>';
			}
			$html .= '<tr><td>' . esc_html( (string) $when ) . '</td><td><code>' . esc_html( $event['tool'] ) . '</code></td><td>' . esc_html( $event['user'] !== '' ? $event['user'] : '#' . $event['user_id'] ) . '</td><td>' . esc_html( $event['client'] !== '' ? $event['client'] : '—' ) . '</td>';
			$html .= '<td>' . StatusPill::render( $tone[0], $tone[1] ) . '</td>';
			$html .= '<td><details class="wpe-ui-log-details"><summary class="' . esc_attr( Button::classes( 'ghost', true ) ) . '">' . esc_html__( 'Details', 'wpessential' ) . '</summary><dl>' . $panel . '</dl></details></td></tr>';
		}

		return $html . '</tbody></table></div>';
	}
}
