<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Closure;
use WP_Error;

/**
 * AI Connect state: the MCP on/off switch, the "Allow AI to change settings" switch and the
 * Application Passwords that connected clients use. Both switches are off by default (ARCHITECTURE §16).
 */
final class AiConnect {

	public const OPTION   = 'wpessential_ai_connect';
	public const APP_NAME = 'WPEssential AI';
	public const CLIENTS  = [
		'claude-ai'      => 'Claude (claude.ai / Claude Desktop connector)',
		'chatgpt'        => 'ChatGPT',
		'claude-code'    => 'Claude Code',
		'claude-desktop' => 'Claude Desktop (config file)',
		'cursor'         => 'Cursor',
		'vscode'         => 'VS Code',
		'windsurf'       => 'Windsurf',
		'gemini'         => 'Gemini CLI',
		'generic'        => 'Other MCP client',
	];

	/** Clients that connect with a secret connector link (they cannot send an Authorization header). */
	public const LINK_CLIENTS = [ 'claude-ai', 'chatgpt' ];

	/** Query parameter that carries a connector link's secret. */
	public const LINK_PARAM = 'wpe_key';

	/** @var string|null Application password UUID authenticated by a connector link on this request. */
	private static ?string $linkUuid = null;

	/** @var array<string,bool> Used when WordPress options are unavailable (unit tests, early boot). */
	private array $memory = [];

	/** @var Closure():bool AI Connect is a Premium module: unavailable when locked or switched off. */
	private readonly Closure $available;

	/** @param (Closure():bool)|null $available */
	public function __construct( ?Closure $available = null ) {
		$this->available = $available ?? static fn (): bool => true;
	}

	public function available(): bool {
		return ( $this->available )();
	}

	/** @return array{enabled:bool,allow_writes:bool} */
	public function settings(): array {
		$stored = function_exists( 'get_option' ) ? get_option( self::OPTION, [] ) : $this->memory;
		$stored = is_array( $stored ) ? $stored : [];

		return [
			'enabled'      => ! empty( $stored['enabled'] ),
			'allow_writes' => ! empty( $stored['allow_writes'] ),
		];
	}

	public function enabled(): bool {
		return $this->available() && $this->settings()['enabled'];
	}

	public function allowWrites(): bool {
		return $this->enabled() && $this->settings()['allow_writes'];
	}

	public function set( string $key, bool $value ): void {
		$settings         = $this->settings();
		$settings[ $key ] = $value;
		if ( $key === 'enabled' && ! $value ) {
			$settings['allow_writes'] = false;
		}
		if ( function_exists( 'update_option' ) ) {
			update_option( self::OPTION, $settings, false );
		} else {
			$this->memory = $settings;
		}
	}

	public static function endpoint(): string {
		return function_exists( 'rest_url' ) ? (string) rest_url( McpServer::NAMESPACE . McpServer::ROUTE ) : '/wp-json/' . McpServer::NAMESPACE . McpServer::ROUTE;
	}

	public static function secure(): bool {
		return function_exists( 'wp_is_application_passwords_available' ) && wp_is_application_passwords_available()
			&& ( ( function_exists( 'is_ssl' ) && is_ssl() ) || ( function_exists( 'wp_get_environment_type' ) && wp_get_environment_type() === 'local' ) );
	}

	/** @return list<array{uuid:string,client:string,created:int,last_used:int}> */
	public function sessions( int $userId ): array {
		if ( ! class_exists( 'WP_Application_Passwords' ) ) {
			return [];
		}
		$sessions = [];
		foreach ( \WP_Application_Passwords::get_user_application_passwords( $userId ) as $item ) {
			$name = (string) ( $item['name'] ?? '' );
			if ( ! str_starts_with( $name, self::APP_NAME ) ) {
				continue;
			}
			$sessions[] = [
				'uuid'      => (string) $item['uuid'],
				'client'    => trim( substr( $name, strlen( self::APP_NAME ) ), ' ·' ),
				'created'   => (int) ( $item['created'] ?? 0 ),
				'last_used' => (int) ( $item['last_used'] ?? 0 ),
			];
		}

		return $sessions;
	}

	/** @return array{password:string,uuid:string}|WP_Error The plain password is returned once and never stored. */
	public function connect( int $userId, string $client ): array|WP_Error {
		if ( ! $this->enabled() ) {
			return new WP_Error( 'wpe_ai_off', __( 'Turn AI Connect on first.', 'wpessential' ) );
		}
		if ( ! self::secure() || ! class_exists( 'WP_Application_Passwords' ) ) {
			return new WP_Error( 'wpe_ai_insecure', __( 'AI Connect needs HTTPS and Application Passwords.', 'wpessential' ) );
		}
		$created = \WP_Application_Passwords::create_new_application_password(
			$userId,
			[ 'name' => self::APP_NAME . ' · ' . ( self::CLIENTS[ $client ] ?? self::CLIENTS['generic'] ) ]
		);
		if ( $created instanceof WP_Error ) {
			return $created;
		}

		return [
			'password' => (string) $created[0],
			'uuid'     => (string) $created[1]['uuid'],
		];
	}

	/**
	 * Secret connector link: the endpoint with "user.uuid.password" of this session's application
	 * password. Revoking the session (or the password) disables the link.
	 */
	public static function link( int $userId, string $uuid, string $password ): string {
		$key = $userId . '.' . $uuid . '.' . str_replace( ' ', '', $password );
		$url = self::endpoint();

		return $url . ( str_contains( $url, '?' ) ? '&' : '?' ) . self::LINK_PARAM . '=' . rawurlencode( $key );
	}

	/**
	 * Filter for determine_current_user: a request to the MCP route that carries a valid connector link signs
	 * in as the link's user. Only that route, only over HTTPS (or a local site), only while AI
	 * Connect is on, and only for WPEssential AI application passwords.
	 *
	 * @param int|false $userId Current user.
	 * @return int|false
	 */
	public function authenticateLink( $userId ) {
		self::$linkUuid = null;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Bearer-style secret, not a form.
		$key = isset( $_GET[ self::LINK_PARAM ] ) ? sanitize_text_field( wp_unslash( (string) $_GET[ self::LINK_PARAM ] ) ) : '';
		if ( $key === '' || ! empty( $userId ) || ! $this->enabled() || ! self::secure() || ! class_exists( 'WP_Application_Passwords' ) ) {
			return $userId;
		}
		$route = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only compared.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Plain-permalink REST route.
		$restRoute = isset( $_GET['rest_route'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['rest_route'] ) ) : '';
		$path      = '/' . McpServer::NAMESPACE . McpServer::ROUTE;
		if ( ! str_contains( $route, $path ) && ! str_contains( $restRoute, $path ) ) {
			return $userId;
		}
		$parts = explode( '.', $key, 3 );
		if ( count( $parts ) !== 3 || ! ctype_digit( $parts[0] ) ) {
			return $userId;
		}
		[ $user, $uuid, $password ] = $parts;
		$item                       = \WP_Application_Passwords::get_user_application_password( (int) $user, $uuid );
		if ( ! is_array( $item ) || ! str_starts_with( (string) ( $item['name'] ?? '' ), self::APP_NAME ) || ! self::passwordMatches( $password, (string) $item['password'], (int) $user ) ) {
			return $userId;
		}
		self::$linkUuid = $uuid;
		\WP_Application_Passwords::record_application_password_usage( (int) $user, $uuid );

		return (int) $user;
	}

	/** WordPress 6.8+ hashes application passwords with wp_fast_hash(); older sites use phpass. */
	private static function passwordMatches( string $password, string $hash, int $userId ): bool {
		// @phpstan-ignore function.alreadyNarrowedType (check_password() is absent before WordPress 6.8)
		if ( method_exists( '\WP_Application_Passwords', 'check_password' ) ) {
			return \WP_Application_Passwords::check_password( $password, $hash );
		}

		return wp_check_password( $password, $hash, $userId );
	}

	/** UUID of the session a connector link signed in with on this request, if any. */
	public static function linkSession(): ?string {
		return self::$linkUuid;
	}

	/** Client label of the session that made a request ('' when unknown). */
	public function clientFor( int $userId, ?string $uuid ): string {
		if ( $userId <= 0 || $uuid === null || $uuid === '' ) {
			return '';
		}
		foreach ( $this->sessions( $userId ) as $session ) {
			if ( $session['uuid'] === $uuid ) {
				return $session['client'];
			}
		}

		return '';
	}

	public function revoke( int $userId, string $uuid ): bool {
		foreach ( $this->sessions( $userId ) as $session ) {
			if ( $session['uuid'] === $uuid ) {
				return \WP_Application_Passwords::delete_application_password( $userId, $uuid ) === true;
			}
		}

		return false;
	}

	public function revokeAll( int $userId ): void {
		foreach ( $this->sessions( $userId ) as $session ) {
			\WP_Application_Passwords::delete_application_password( $userId, $session['uuid'] );
		}
	}

	/** Ready-to-paste client configuration. The password is shown once on the screen that created it. */
	public static function snippet( string $client, string $login, string $password ): string {
		$header = 'Basic ' . base64_encode( $login . ':' . str_replace( ' ', '', $password ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- HTTP Basic credentials.
		$url    = self::endpoint();

		if ( $client === 'claude-desktop' ) {
			// Claude Desktop's config runs local (stdio) servers; mcp-remote bridges to this HTTP endpoint.
			return (string) wp_json_encode(
				[
					'mcpServers' => [
						'wpessential' => [
							'command' => 'npx',
							'args'    => [ '-y', 'mcp-remote', $url, '--header', 'Authorization:${WPE_AUTH}' ],
							'env'     => [ 'WPE_AUTH' => $header ],
						],
					],
				],
				JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
			);
		}
		if ( $client === 'gemini' ) {
			return (string) wp_json_encode(
				[
					'mcpServers' => [
						'wpessential' => [
							'httpUrl' => $url,
							'headers' => [ 'Authorization' => $header ],
						],
					],
				],
				JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
			);
		}
		if ( $client === 'generic' ) {
			return 'URL: ' . $url . "\nAuthorization: " . $header;
		}
		if ( $client === 'claude-code' ) {
			return 'claude mcp add --transport http wpessential ' . $url . ' --header "Authorization: ' . $header . '"';
		}

		$server = [
			'type'    => 'http',
			'url'     => $url,
			'headers' => [ 'Authorization' => $header ],
		];
		$key    = $client === 'vscode' ? 'servers' : 'mcpServers';
		if ( $client === 'windsurf' ) {
			$server = [
				'serverUrl' => $url,
				'headers'   => [ 'Authorization' => $header ],
			];
		}

		return (string) wp_json_encode( [ $key => [ 'wpessential' => $server ] ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}
}
